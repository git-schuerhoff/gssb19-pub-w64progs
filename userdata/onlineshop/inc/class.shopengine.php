<?php
/*
	GS ShopEngine v1.0 - class.shopengine.php
	Author: Thilo Schuerhoff / Schuerhoff EDV

	(c) 2013 GS Software AG

	this code is NOT open-source or freeware
	you are not allowed to use, copy or redistribute it in any form
*/

class gs_shopengine
{
	var $currPage = '';
	var $content = '';
	var $aTags = array();
	var $dbhost = '';
	var $dbname = '';
	var $dbuser = '';
	var $dbpass = '';
	var $dbtoken = '';
	var $gssbVer = '';
	var $lngID = '';
	var $cntID = '';
	var $iso = '';
	var $shopurl = '';
	var $absurl = '';
	var $dldurl = '';
	var $aLang = array();
	var $crlf = '';
	var $edition = '';
	var $aslcs = array();
	var $acnts = array();
	var $prefix = "GSSE";
	//Variablen f�r die Modulregistrierungspr�fung
	var $gssbinfo;
	var $gssbmodules;
		
	function __construct($page = 'index.html')
	{
		include("conf/db.const.inc.php");
		//A: Werte aus db.const.inc.php
		$this->currPage = $page;
		$this->dbhost = $dbServer;
		$this->dbname = $dbDatabase;
		$this->dbuser = $dbUser;
		$this->dbpass = $dbPass;
		$this->dbtoken = DBToken;
		$this->gssbVer = $gssbVersion;
		$this->demo = $gssbDemo;
		$this->shopurl = $shopURL;
		$this->gssbinfo = $gssbInfo;
		$this->gssbmodules = $gssbModules;
		//E: Werte aus db.const.inc.php
		$this->edition = $this->chk_edition($gssbInfo,$gssbEdition);
		
		
		if((!isset($_SESSION['aslc'])) || (!isset($_SESSION['acnt']))) {
			$this->get_slccnts();
			$_SESSION['aslc'] = $this->aslcs;
			$_SESSION['acnt'] = $this->acnts;
		}
		
		if(($this->edition === false) && ($this->demo === 0)) {
			die("Licencing problem detected!!! Please contact the support!!!");
		}
		//die('Edition: '.$this->edition)
		$_SESSION['gs_edition'] = $this->edition;
		
		if(!isset($_SESSION['slc']))
		{
			if($this->aslcs[0] != '') {
				$_SESSION['slc'] = $this->aslcs[0];
			} else {
				$_SESSION['slc'] = 'deu';
			}
		}
		if(!isset($_SESSION['cnt']))
		{
			if($this->acnts[0] != '') {
				$_SESSION['cnt'] = $this->acnts[0];
			} else {
				$_SESSION['cnt'] = 'deu';
			}
		}
		
		$this->lngID = $_SESSION['slc'];
		$this->cntID = $_SESSION['cnt'];
		
		if(!isset($_SESSION['sb_settings'])) {
			$this->load_settings();
		} else {
			if(empty($_SESSION['sb_settings'])) {
				$this->load_settings();
			}
		}
		
		//$this->lngID = $gssbLang;
		//$this->cntID = $gssbCountry;
		if (!isset($_SESSION['aLang'])){
            $this->aLang = $this->getLng();
            $_SESSION['aLang'] = $this->aLang;
        } else {
            $this->aLang = $_SESSION['aLang'];
        }
		$this->crlf = chr(13) . chr(10);
		
		//$this->absurl = $this->get_setting('edAbsoluteShopPath_Text');
		$this->absurl = '';
		$this->dldurl = $this->shopurl;
	}
	
	function getLng()
	{
		$custlabels = array();
		$dbh = $this->db_connect();
		$sql = "SELECT langtag, customlabel FROM ".$this->dbtoken."customlabels WHERE languageid='" . $this->lngID . "'";
		$erg = mysqli_query($dbh,$sql);
		if(mysqli_errno($dbh) == 0)
		{
			while($z = mysqli_fetch_assoc($erg))
			{	
				$custlabels[$z['langtag']] = $z['customlabel'];
			}	
		}
		//mysqli_free_result($erg);
		//mysqli_close($dbh);
		
		$aMyLang = array();
		$aMyLines = array();
		$aLine = array();
		$cMyLine = '';
		
		$aMyLines = file('shoplang/lt_' . $this->lngID . '.txt');
		foreach($aMyLines as $key => $cMyLine)
		{
			if(substr($cMyLine,0,7) == 'LangTag')
			{
				//echo str_pad($key,4,' ',STR_PAD_LEFT) . ": " . $cMyLine . "<br />";
				$aLine = explode(chr(9),$cMyLine);
				if($aLine[1] == $this->lngID)
				{
					if($aLine[0] == 'LangTagCharset')
					{
						$this->iso = $aLine[2];
					}
					/*array_push($aMyLang,array($aLine[0],$aLine[2]));*/
					if(array_key_exists($aLine[0],$custlabels)){
						$aMyLang[] = array($aLine[0],$custlabels[$aLine[0]]);
					} else {
						$aMyLang[] = array($aLine[0],$aLine[2]);
					}
				}
			}
		}
		return $aMyLang;
	}
	
	function parse_page()
	{
		$this->content = $this->gs_file_get_contents($this->absurl . 'template/' . $this->currPage);
		$this->create_header();
		$this->get_tags();
		$this->parse_tags();
		echo $this->content;
		
	}
	
	function parse_phase5()
	{
		$this->content = $this->gs_file_get_contents($this->absurl . 'template/' . $this->currPage);
		//$this->create_header();
		$this->get_tags();
		$this->parse_tags();
		echo $this->content;
		
	}
	
	function parse_inc()
	{
		$this->content = $this->gs_file_get_contents($this->absurl . 'template/' . $this->currPage);
		$this->get_tags();
		$this->parse_tags();
		return $this->content;
	}
	
	function create_header() {
		$header = '';
		$meta = '';
		$header = $this->gs_file_get_contents($this->absurl . 'template/head.html');
		$meta = $this->gs_file_get_contents($this->absurl . 'template/meta.html');
		$title = trim($this->get_setting('edShopTitle_Text'));
		$shopname = trim($this->get_setting('edShopName_Text'));
		if($title == '') {
			$title = $shopname;
		}
		
		$logowidth = $this->get_setting('edLogo1Width_Text') . 'px';
		$logoheight = $this->get_setting('edLogo1Height_Text') . 'px';
		$header = str_replace('{GSSE_INCL_SHOPLOGO}',$this->get_setting('edLogo1_Text'),$header);
		$header = str_replace('{GSSE_INCL_SHOPLOGOWIDTH}',$logowidth,$header);
		$header = str_replace('{GSSE_INCL_SHOPLOGOHEIGHT}',$logoheight,$header);
		
		$smalllogowidth = $this->get_setting('edLogo2Width_Text') . 'px';
		$smalllogoheight = $this->get_setting('edLogo2Height_Text') . 'px';
		$header = str_replace('{GSSE_INCL_SHOPLOGOSMALL}',$this->get_setting('edLogo2_Text'),$header);
		$header = str_replace('{GSSE_INCL_SHOPLOGOSMALLWIDTH}',$smalllogowidth,$header);
		$header = str_replace('{GSSE_INCL_SHOPLOGOSMALLHEIGHT}',$smalllogoheight,$header);
		
		$metakeywords = $this->db_text_ret('settingmemo|SettingMemo|SettingName|memoMetaTags');
		$metadescription = $this->db_text_ret('settingmemo|SettingMemo|SettingName|memoMetaDataDescription');
		
		$pagename = '';
		
		if($this->currPage == 'productgroup.html') {
			//TS 14.03.2017: Metatitel als Seitentitel
			$metatitle = $this->get_value_by_idx($this->dbtoken . 'productgrouplanguage', 'PgCount', $_GET['idx'], 'pageMetaTitle', 'LanguageId', '');
			$pagename = $this->get_value_by_idx($this->dbtoken . 'productgrouplanguage', 'PgCount', $_GET['idx'], 'ProductGroup', 'LanguageId', '');
			$metakeywords = $this->get_value_by_idx($this->dbtoken . 'productgrouplanguage', 'PgCount', $_GET['idx'], 'MetaKeywords', 'LanguageId', '');
			$metadescription = $this->get_value_by_idx($this->dbtoken . 'productgrouplanguage', 'PgCount', $_GET['idx'], 'MetaDescription', 'LanguageId', '');
			if(trim($metatitle) != '') {
				$title = $metatitle;
			} else {
				$title = $pagename . ' - ' . $shopname;
			}
		}
		
		if($this->currPage == 'detail.html') {
			//TS 14.03.2017: Metatitel als Seitentitel
			$metatitle = $this->get_value_by_idx($this->dbtoken . 'itemdata', 'itemItemId', $_GET['item'], 'pageMetaTitle', 'itemLanguageId', '');
			//TS 27.02.2017: Variantennamen mit in den Titel aufnehmen
			$variantname = $this->get_value_by_idx($this->dbtoken . 'itemdata', 'itemItemId', $_GET['item'], 'itemVariantDescription', 'itemLanguageId', '');
			$pagename = $this->get_value_by_idx($this->dbtoken . 'itemdata', 'itemItemId', $_GET['item'], 'itemItemDescription', 'itemLanguageId', '').' '.$variantname;
			$metakeywords = $this->get_value_by_idx($this->dbtoken . 'itemdata', 'itemItemId', $_GET['item'], 'itemMetaKeywords', 'itemLanguageId', '');
			$metadescription = $this->get_value_by_idx($this->dbtoken . 'itemdata', 'itemItemId', $_GET['item'], 'itemMetaDescription', 'itemLanguageId', '');
			if(trim($metatitle) != '') {
				$title = $metatitle;
			} else {
				$title = $pagename . ' - ' . $shopname;
			}
		}
		
		/*if($pagename != '') {
			//$title = $pagename . ' ' . $metakeywords . ' | ' . $title;
			$title = $pagename . ' - ' . $shopname;
		}*/
		
		$aMetaTags = $this->get_tags_ret($meta);
		$meta = $this->parse_texts($aMetaTags,$meta);
		
		$meta = str_replace('{GSSE_INCL_GSSBVER}',$this->gssbVer,$meta);
		$meta = str_replace('{GSSE_INCL_YEAR}',date("Y"),$meta);
		$meta = str_replace('{GSSE_INCL_METADESCRIPTION}',$metadescription,$meta);
		$meta = str_replace('{GSSE_INCL_METAKEYWORDS}',$metakeywords,$meta);
			
		$header = str_replace('{GSSE_INCL_HTMLTITLE}',$title,$header);
		$header = str_replace('{GSSE_INCL_SHOPURL}',$this->shopurl,$header);
		
		$header = str_replace('{GSSE_INCL_META}',$meta,$header);
		
		$this->content = $header . $this->content;
	}
	
	function parse_tags()
	{
		$prefix;
		$func;
		$param;
		$preload = array();
		if(in_array("{GSSE_FUNC_LOADITEMDATA}",$this->aTags) && in_array("{GSSE_FUNC_MAINMENUNEW}", $this->aTags) && in_array("{GSSE_FUNC_DETAILBOXNEW}", $this->aTags)){
			$preload[0] = "{GSSE_FUNC_LOADITEMDATA}";
			$preload[1] = "{GSSE_FUNC_MAINMENUNEW}";
			$preload[2] = "{GSSE_FUNC_DETAILBOXNEW}";
			$key = array_search("{GSSE_FUNC_LOADITEMDATA}", $this->aTags);
			unset($this->aTags[$key]);
			$key = array_search("{GSSE_FUNC_MAINMENUNEW}", $this->aTags);
			unset($this->aTags[$key]);
			$key = array_search("{GSSE_FUNC_DETAILBOXNEW}", $this->aTags);
			unset($this->aTags[$key]);
		}
		foreach ($preload as $tag){
			$tag = str_replace('__','*#*#',$tag);
			$apos = strpos($tag,'_');
			$prefix = substr($tag,1,$apos - 1);
			$bpos = strpos($tag,'_',$apos + 1);
			$func = substr($tag,$apos + 1,($bpos-$apos)-1);
			$param = substr($tag,$bpos + 1,(strlen($tag)-$bpos)-2);
			$param = str_replace('*#*#','__',$param);
			$tag = str_replace('*#*#','__',$tag);
			$this->exec_func($tag,$param);
		}
		foreach ($this->aTags as $tag)
		{
			$tag = str_replace('__','*#*#',$tag);
			$apos = strpos($tag,'_');
			$prefix = substr($tag,1,$apos - 1);
			$bpos = strpos($tag,'_',$apos + 1);
			$func = substr($tag,$apos + 1,($bpos-$apos)-1);
			$param = substr($tag,$bpos + 1,(strlen($tag)-$bpos)-2);
			$param = str_replace('*#*#','__',$param);
			$tag = str_replace('*#*#','__',$tag);
			switch ($func)
			{
				case "TXDB":
					$this->db_text($tag,$param);
					break;
				case "LANG":
					$this->lngtext($tag,$param);
					break;
				case "IMDB":
					$this->db_img($tag,$param);
					break;
				case "FUNC":
					$this->exec_func($tag,$param);
					break;
				case "ISACTIVE":
					$this->is_active($tag,$param);
					break;
				case "IF":
					$this->gsse_if($tag,$param);
					break;
				case "ENDIF":
					break;
				case "MMLOGIN":
					$this->mm_login($tag,$param);
					break;
				case "NAVI":
					$this->navi($tag);
					break;
				case "JSCR":
					$this->jscr($tag,$param);
					break;
				case "SURL":
					$this->content = str_replace($tag, $this->absurl, $this->content);
					break;
				default:
					//$this->content = str_replace($tag, "Unknown function: " . $func, $this->content);
					break;
			}
		}
	}
	
	function parse_texts($aTags,$content,$ef = 0)
	{
		$prefix;
		$func;
		$param;
		foreach ($aTags as $tag)
		{
			$tag = str_replace('__','*#*#',$tag);
			$apos = strpos($tag,'_');
			$prefix = substr($tag,1,$apos - 1);
			$bpos = strpos($tag,'_',$apos + 1);
			$func = substr($tag,$apos + 1,($bpos-$apos)-1);
			$param = substr($tag,$bpos + 1,(strlen($tag)-$bpos)-2);
			$param = str_replace('*#*#','__',$param);
			$tag = str_replace('*#*#','__',$tag);
			switch ($func)
			{
				case "TXDB":
					$content = str_replace($tag,$this->db_text_ret($param),$content);
					break;
				case "LANG":
					$this->lngtext($tag,$param);
					$content = str_replace($tag,$this->get_lngtext($param),$content);
					break;
				default:
					//Do nothing
					break;
			}
		}
		if($ef == 1)
		{
			$content = $this->email_friendly($content);
		}
		return $content;
	}
	
	function set_values($aTags,$content)
	{
		$prefix;
		$func;
		$param;
		foreach ($aTags as $tag)
		{
			$tag = str_replace('__','*#*#',$tag);
			$apos = strpos($tag,'_');
			$prefix = substr($tag,1,$apos - 1);
			$bpos = strpos($tag,'_',$apos + 1);
			$func = substr($tag,$apos + 1,($bpos-$apos)-1);
			$param = substr($tag,$bpos + 1,(strlen($tag)-$bpos)-2);
			$param = str_replace('*#*#','__',$param);
			$tag = str_replace('*#*#','__',$tag);
			if(substr($param,0,7) == 'LangTag')
			{
				$field = $this->get_lngtext($param);
			}
			else
			{
				$field = $param;
			}
			switch ($func)
			{
				case "VALUE":
					if(isset($_POST[$field]))
					{
						$value = '';
						if($param == 'LangTagFNFieldGeburtsdatum')
						{
							$value = $this->conv_date($_POST[$field],'E');
						}
						else
						{
							$value = $_POST[$field];
						}
						$content = str_replace($tag,$this->email_friendly($value),$content);
					}
					elseif(isset($_SESSION[$param]))
					{
						$value = '';
						if($param == 'LangTagFNFieldGeburtsdatum')
						{
							$value = $this->conv_date($_SESSION[$param],'E');
						}
						else
						{
							$value = $_SESSION[$param];
						}
						$content = str_replace($tag,$this->email_friendly($value),$content);
					}
					else
					{
						$content = str_replace($tag,'',$content);
					}
					break;
				default:
					//Do nothing
					break;
			}
		}
		return $content;
	}
	
	function get_tags()
	{
		$this->aTags = $this->get_tags_ret($this->content);
	}
	
	function get_tags_ret($content)
	{
		$aMyTags = array();
		$off = 0;
		$opos = 0;
		$cpos = 0;
		$opos = strpos($content, '{' . $this->prefix, $off);
		while($opos !== false )
		{
			//Position des �ffnenden {
			$off = $opos;
			$cpos = strpos($content, '}', $off);
			if($cpos !== false)
			{
				$off = $cpos;
				$aMyTags[] = substr($content,$opos,($cpos-$opos) + 1);
			}
			$opos = strpos($content, '{' . $this->prefix, $off);
		}
		return $aMyTags;
	}
	
	function db_connect()
	{
		$con = new mysqli($this->dbhost,$this->dbuser,$this->dbpass,$this->dbname);
		$con->query("SET NAMES 'utf8'");
		return $con;
	}
	
	function db_text($tag,$param)
	{
		$output = $this->db_text_ret($param);
		$this->content = str_replace($tag, $output, $this->content);
	}
	
	function db_text_ret($param)
	{
		$output = '';
		$dbh = $this->db_connect();
		$aParam = explode('|',$param);
		$lngStr = '';
		switch($aParam[0])
		{
			case 'setting':
				$lngStr = " AND LanguageId = '" . $this->lngID . "' AND CountryId = '" . $this->cntID . "'";
				break;
			case 'settingmemo':
				$lngStr = " AND LanguageId = '" . $this->lngID . "' AND CountryId = '" . $this->cntID . "'";
				break;
			case 'contentpool':
				$lngStr = " AND LanguageId = '" . $this->lngID . "'";
				break;
			default:
				$lngStr = '';
				break;
		}
		
		if(isset($_GET['page']) AND $_GET['page']=='productgroup')
		{
			$sql = "SELECT TextName FROM " . $this->dbtoken . "kattags WHERE KatCount = " . $_GET['idx'] . " AND Tag = '" . $aParam[3] . "' AND LanguageId = '" . $this->lngID . "'";
			$erg = mysqli_query($dbh,$sql);
			if(mysqli_errno($dbh) == 0)
			{
				$z = mysqli_fetch_assoc($erg);
				if (!$z == false)
				{
					$aParam[3] = $z['TextName'];
				}
				
				$sql = "SELECT " . $aParam[1] . " FROM " . $this->dbtoken . $aParam[0] . " WHERE " . $aParam[2] . " = '" . $aParam[3] . "'" . $lngStr . " LIMIT 1";
				$erg = mysqli_query($dbh,$sql);
				if(mysqli_errno($dbh) == 0)
				{
					$z = mysqli_fetch_assoc($erg);
					$output = $z[$aParam[1]];
					
					@mysqli_free_result($erg);
				}
				else
				{
					$output = mysqli_error($dbh);
				}
				@mysqli_free_result($erg);
			}
			else
			{
				$output = mysqli_error($dbh);
			}
		}
		else
		{
			$sql = "SELECT " . $aParam[1] . " FROM " . $this->dbtoken . $aParam[0] . " WHERE " . $aParam[2] . " = '" . $aParam[3] . "'" . $lngStr . " LIMIT 1";
			$erg = mysqli_query($dbh,$sql);
			if(mysqli_errno($dbh) == 0)
			{
				$z = mysqli_fetch_assoc($erg);
				$output = $z[$aParam[1]];
				
				@mysqli_free_result($erg);
			}
			else
			{
				$output = mysqli_error($dbh);
			}
		}
		mysqli_close($dbh);
		//$output = iconv('ISO-8859-1','UTF-8',$output);
		return $output;
	}
	
	function db_img($tag,$param)
	{
		$dbh = $this->db_connect();
		$aParam = explode('|',$param);
		$sql = "SELECT " . $aParam[1] . " FROM " . $this->dbtoken . $aParam[0] . " WHERE " . $aParam[2] . " = '" . $aParam[3] . "' AND LanguageId = '" . $this->lngID . "' AND CountryId = '" . $this->cntID . "' LIMIT 1";
		$erg = mysqli_query($dbh,$sql);
		if(mysqli_errno($dbh) == 0)
		{
			$z = mysqli_fetch_assoc($erg);
			//$output = '<img src="images/' . $z[$aParam[1]] . '" alt="' . $z[$aParam[1]] . '" />';
			$output = $this->gs_file_get_contents($this->absurl . 'template/image.html');
			//<img class="{GSSE_INCL_IMGCLASS}" src="{GSSE_INCL_IMGSRC}" alt="{GSSE_INCL_IMGALT}" title="{GSSE_INCL_IMGTITLE}" />
			$output = str_replace('{GSSE_INCL_IMGSRC}',$this->absurl . 'images/' . $z[$aParam[1]],$output);
			$output = str_replace('{GSSE_INCL_IMGALT}',$z[$aParam[1]],$output);
			$output = str_replace('{GSSE_INCL_IMGTITLE}',$z[$aParam[1]],$output);
			if(count($aParam) == 5)
			{
				$output = str_replace('{GSSE_INCL_IMGCLASS}',$aParam[4],$output);
			}
			else
			{
				$output = str_replace('{GSSE_INCL_IMGCLASS}','',$output);
			}
			mysqli_free_result($erg);
		}
		else
		{
			$output = mysqli_error($dbh);
		}
		$this->content = str_replace($tag, $output, $this->content);
		mysqli_close($dbh);
	}
	
	function exec_func($tag,$param)
	{
		$aParam = explode('|',$param);
		$incfile = strtolower($aParam[0]);
		include('inc/' . $incfile . '.inc.php');
	}
	
	function lngtext($tag,$param)
	{
		$this->content = str_replace($tag, $this->get_lngtext($param), $this->content);
	}
	
	function get_lngtext($lngtag)
	{
		$idx = $this->array_search_multi($lngtag,$this->aLang);
		if($idx !== false)
		{
			$text = $this->aLang[$idx][1];
			//$inText = iconv($this->iso,'ISO-8859-1',$text);
			$inText = $text;
		}
		else
		{
			$inText = $lngtag;
		}
		return $inText;
	}
	
	function array_search_multi($search, $array)
	{
		foreach($array as $key => $values)
		{
			if(in_array($search, $values))
			{
				return $key;
			}
		}
		return false;
	}
	
	function get_currency($fNumber,$nSymbol,$cThousand)
	{
		$cSettingName = ($nSymbol == 1) ? 'edCurrencySymbol1_Text' : 'edCurrencySymbol_Text';
		$cSymbol = $this->get_setting($cSettingName);
		$localNumber = $this->get_number_format($fNumber,$cThousand);
		return $localNumber . ' ' . $cSymbol;
	}
	
	function get_number_format($fNumber,$cThousand)
	{
		$dec = 2;
		$comma = ',';
		
		if($this->cntID == 'gbr' || $this->cntID == 'irl' || $this->cntID == 'usa' ||
			$this->cntID == 'aus' || $this->cntID == 'can' || $this->cntID == 'chn' ||
			$this->cntID == 'mex' || $this->cntID == 'jpn' || $this->cntID == 'twn' ||
			$this->cntID == 'hkg' || $this->cntID == 'kor' || $this->cntID == 'tha' ||
			$this->cntID == 'ind' || $this->cntID == 'sgp' || $this->cntID == 'nzl' ||
			$this->cntID == 'pak' || $this->cntID == 'bgd' || $this->cntID == 'phl' ||
			$this->cntID == 'nic' || $this->cntID == 'nga' || $this->cntID == 'bwa' ||
			$this->cntID == 'zwe'){
			//Sonderfall Kanada
			if($this->cntID == 'can')
			{
				if($this->lngID == 'eng')
				{
					$comma = '.';
				} 
			}
			else
			{
				$comma = '.';
			}
		}
		return number_format(floatval($fNumber),$dec,$comma,$cThousand);
	}
	
	function is_active($tag,$param)
	{
		$menu_page = strtolower($param);
		$curr_page = strtolower($_GET['page']);
		if($menu_page == $curr_page)
		{
			$output = 'active';
		}
		else
		{
			$output = '';
		}
		$this->content = str_replace($tag, $output, $this->content);
	}
	
	function gsse_if($tag,$param)
	{
		$menu_page = strtolower($param);
		$settingName = '';
		$starttag = '<!--';
		$endtag = '-->';
		$res = '';
		switch($menu_page)
		{
			case 'phpfaq':
				$settingName = 'cbUsePhpFAQ_Checked';
				break;
			case 'phpextsearch':
				$settingName = 'cbUsePhpExtendedSearch_Checked';
				break;
			case 'phpnotepad':
				$settingName = 'cbUsePhpNotepad_Checked';
				break;
			case 'phpwishlist':
				$settingName = 'cbUsePhpWishlist_Checked';
				break;
			case 'phpusercomm':
				$settingName = 'cbUsePhpUsercomments_Checked';
				break;
			case 'phpshopcomm':
				$settingName = 'cbUsePhpShopcomments_Checked';
				break;
			case 'phpbonus':
				$settingName = 'cbUsePhpBonusPoints_Checked';
				break;
			case 'phpdirectorder':
				$settingName = 'cbUsePhpDirectOrder_Checked';
				break;
			case 'phpcustomerlogin':
				$settingName = 'cbUsePhpCustomerLogin_Checked';
				if($this->get_setting('cbUsePhpB2BLogin_Checked') == 'True')
				{
					$settingName = 'cbUsePhpB2BLogin_Checked';
				}
				break;
			case 'phone':
				$settingName = 'cb_Phone_Checked';
				break;
			case 'birthdate':
				$settingName = 'cb_birthField_Checked';
				break;
			case 'wishlist':
				$settingName = 'cbUsePhpWishlist_Checked';
				break;
			case 'manufacturer':
				$settingName = 'cbUsePhpManufacturerList_Checked';
				break;
			case 'newsletter':
				$settingName = 'cbUsePhpNewsLetter_Checked';
				break;
			case 'newitems':
				$settingName = 'cbProductGroupNew_Checked';
				break;	
            case 'imagezoom':
                $settingName = 'cbImageZoom_Checked';
                break;
			default:
				break;
		}
		if($this->phpactive() === true)
		{
			if($settingName != '')
			{
				$res = $this->get_setting($settingName);
				if($res == 'True')
				{
					$starttag = '';
					$endtag = '';
				}
			}
		}
		$this->content = str_replace($tag, $starttag, $this->content);
		$this->content = str_replace('{GSSE_ENDIF_' . $param .'}', $endtag, $this->content);
	}
	
	function phpactive()
	{
		$lUsePhp = false;
		$res = $this->get_setting('cbUsePhpExtensions_Checked');
		if($res == 'True')
		{
			$lUsePhp = true;
		}
		return $lUsePhp;
	}
	
	/*function get_setting($settingName)
	{
		$res = '';
		if($settingName != '')
		{
			if($settingName == 'cbUsePhpExtensions_Checked')
			{
				$res = 'True';
			}
			else
			{
				$dbh = $this->db_connect();
				$sql = "SELECT SettingValue FROM " . $this->dbtoken . "setting WHERE SettingName = '" . $settingName . "' AND LanguageId = '" . $this->lngID . "' AND CountryId = '" . $this->cntID . "' LIMIT 1";
				$erg = mysqli_query($dbh,$sql);
				if(mysqli_errno($dbh) == 0)
				{
					$z = mysqli_fetch_assoc($erg);
					$res = $z['SettingValue'];
				}
				mysqli_free_result($erg);
				mysqli_close($dbh);
			}
		}
		return $res;
	}*/
	
	function get_setting($settingName)
	{
		$res = '';
		if($settingName != '') {
			if($settingName == 'cbUsePhpExtensions_Checked') {
				$res = 'True';
			} else {
				if(isset($_SESSION['sb_settings'][$settingName])) {
					$res = base64_decode($_SESSION['sb_settings'][$settingName]);
				} else {
					$res = '';
				}
			}
		}
		return $res;
	}
	
	function get_settingmemo($settingName) {
		$res = '';
		if($settingName != '') {
			$dbh = $this->db_connect();
			$sql = "SELECT SettingMemo FROM " . $this->dbtoken . "settingmemo WHERE SettingName = '" . $settingName . "' AND LanguageId = '" . $this->lngID . "' AND CountryId = '" . $this->cntID . "' LIMIT 1";
			$erg = mysqli_query($dbh,$sql);
			if(mysqli_errno($dbh) == 0) {
				$z = mysqli_fetch_assoc($erg);
				$res = $z['SettingMemo'];
			}
			mysqli_free_result($erg);
			mysqli_close($dbh);
		}
		return $res;
	}
	
	function get_setting_s($cField)
	{
		$dbh = $this->db_connect();
		$sql = "SELECT " . $cField . " FROM ".$this->dbtoken."settings";
		$erg = mysqli_query($dbh,$sql);
		if(mysqli_errno($dbh) == 0)
		{
			$z = mysqli_fetch_assoc($erg);
			$res = $z[$cField];
		}
		mysqli_free_result($erg);
		mysqli_close($dbh);
		return $res;
	}
	
	function mm_login($tag,$param)
	{
		$res = '';
		$html = '';
		if($this->phpactive() === true)
		{
			$res = $this->get_setting('cbUsePhpB2BLogin_Checked');
			if($res == 'True')
			{
				$html = 'mm_logout.html';
			}
			else
			{
				$res = $this->get_setting('cbUsePhpCustomerLogin_Checked');
				if($res == 'True')
				{
					if(isset($_SESSION['login']))
					{
						if(!$_SESSION['login']['ok'])
						{
							$html = 'mm_login.html';
						}
						else
						{
							$html = 'mm_logout.html';
						}
					}
					else
					{
						$html = 'mm_login.html';
					}
				}
			}
			
			if($html != '')
			{
				$lnk = new gs_shopengine($html);
				$this->content = str_replace($tag, $lnk->parse_inc(), $this->content);
			}
		}
		$this->content = str_replace($tag, $html, $this->content);
	}
	
	function navi($tag)
	{
		$this->content = str_replace($tag, $this->get_subnavi(0,0), $this->content);
		/*print_r($_SESSION['anavi']);*/
	}
	
	function get_subnavi($parent,$level)
	{
		if(!isset($_SESSION['anavi']))
		{
			$_SESSION['anavi'] = array();
		}
		
		$navi = $this->gs_file_get_contents($this->absurl . 'template/navi_head.html');
		$line = $this->gs_file_get_contents($this->absurl . 'template/navi_line.html');
		$dbh = $this->db_connect();
		$sql = "SELECT ObjectCount, ProductGroup, GroupHint, ImageFile FROM " . $this->dbtoken . "productgroups WHERE Parent = '" . $parent . "' ORDER BY Sequence ASC";
		$erg = mysqli_query($dbh,$sql);
		if(mysqli_errno($dbh) == 0)
		{
			if(mysqli_num_rows($erg) > 0)
			{
				while($z = mysqli_fetch_assoc($erg))
				{
					$pgroup = $z['ProductGroup'];
					$phint = $z['GroupHint'];
					$pimage = $z['ImageFile'];
					$sql2 = "SELECT ProductGroup, GroupHint, ImageFile FROM " . $this->dbtoken . "productgrouplanguage WHERE PgCount = '" . $z['ObjectCount'] . "' AND LanguageId = '" . $this->lngID . "' LIMIT 1";
					$erg2 = mysqli_query($dbh,$sql2);
					if(mysqli_errno($dbh) == 0)
					{
						if(mysqli_num_rows($erg2) > 0)
						{
							$pl = mysqli_fetch_assoc($erg2);
							$pgroup = $pl['ProductGroup'];
							$phint = $pl['GroupHint'];
							$pimage = $pl['ImageFile'];
						}
						mysqli_free_result($erg2);
					}
					
					if($phint != '')
					{
						$hint = $phint;
					}
					else
					{
						$hint = $pgroup;
					}
					
					if($z['ImageFile'] != '')
					{
						$cur_img = '<img class="navi_img' . $level . '" src="images/groups/small/' . $pimage . '" alt="' . $hint . '" title="' . $hint . '" />';
					}
					else
					{
						if(file_exists("template/images/navi_no_pg.gif"))
						{
							$cur_img = '<img class="navi_img' . $level . '" src="template/images/navi_no_pg.gif" alt="' . $hint . '" title="' . $hint . '" />';
						}
						else
						{
							$cur_img = '';
						}
					}
					
					$psql = "SELECT COUNT(ObjectCount) AS childs FROM " . $this->dbtoken . "productgroups WHERE Parent = '" . $z['ObjectCount'] . "'";
					$perg = mysqli_query($dbh,$psql);
					$p = mysqli_fetch_assoc($perg);
					$childs = $p['childs'];
					if($childs > 0)
					{
						$hassub = '&nbsp;&gt;&gt;';
					}
					else
					{
						$hassub = '';
					}
					mysqli_free_result($perg);
					
					$showsub = ' onclick="gsse_showsub(' . $z['ObjectCount'] . ',\'pgid_' . $z['ObjectCount'] . '\',' . $level . ',' . $parent . ',\'' . $pgroup . '\',' . $childs . ')"';
					$IDX = $this->array_search_multi($z['ObjectCount'], $_SESSION['anavi']);
					if($IDX !== false)
					{
						$active = $_SESSION['anavi'][$IDX]['active'];
					}
					else
					{
						$active = '';
					}
					$groupname = $this->email_friendly($pgroup);
					$groupname = str_replace(' ','_',$groupname);
					$groupname = str_replace('"','',$groupname);
					$groupname = str_replace("'",'',$groupname);
					$groupname = str_replace('<','',$groupname);
					$groupname = str_replace('>','',$groupname);
					
					$cur_line = str_replace('{GSSE_PG_LINK}','index.php?page=productgroup&amp;idx=' . $z['ObjectCount'] . '&amp;childs=' . $childs . '&amp;start=0&amp;name='.$groupname ,$line);
					$cur_line = str_replace('{GSSE_PG_SHOWSUB}',$showsub,$cur_line);
					$cur_line = str_replace('{GSSE_PG_LEVEL}',$level . $active,$cur_line);
					$cur_line = str_replace('{GSSE_PG_IMG}',$cur_img,$cur_line);
					$cur_title = $pgroup;
					$cur_line = str_replace('{GSSE_PG_TITLE}',$cur_title . $hassub,$cur_line);
					$cur_line = str_replace('{GSSE_PG_ID}',$z['ObjectCount'],$cur_line);
					$submenu = '';
					$subclass = 'subnavi_hide';
					if(isset($_SESSION['anavi']))
					{
						if(count($_SESSION['anavi']) > 0)
						{
							$n = $this->array_search_multi($z['ObjectCount'], $_SESSION['anavi']);
							if($n !== false)
							{
								$submenu = $this->get_subnavi($_SESSION['anavi'][$n]['group'],$_SESSION['anavi'][$n]['level']);
								$subclass = 'subnavi_show';
							}
						}
					}
					$cur_line = str_replace('{GSSE_PG_SUBCLASS}',$subclass,$cur_line);
					$cur_line = str_replace('{GSSE_PG_SUBMENU}',$submenu,$cur_line);
					$navi .= $cur_line;
				}
			}
		}
		else
		{
			$navi = 'Could&apos;nt load navigation';
		}
		mysqli_free_result($erg);
		//mysqli_close($dbh);
		$navi .= $this->gs_file_get_contents($this->absurl . 'template/navi_foot.html');
		return $navi;
	}
	
	function jscr($tag,$param)
	{
		
        // SM 24.05.2017 - Zoomfunktion abh�ngig von Einstellungen hinzuf�gen
        // TS 22.08.2019: Zu kurzsichtig gedacht. Wenn zoom.js nicht eingebunden wird,
        //funktioniert die Bild-Umschaltung ebenfalls nicht.
        //Daher wird, wenn die Zoomfunktion deaktiviert wurde, das no-zoom.js-Skript
        //eingebunden, welches noch die Bildumschaltung erlaubt
        /*$res = $this->get_setting('cbImageZoom_Checked');
        if(($res == 'False') && ($param =='zoom.js')){
            $html = '';
        }*/
		if($param =='zoom.js'){
			$res = $this->get_setting('cbImageZoom_Checked');
			if($res == 'False') {
				$param = 'no-zoom.js';
			}
		}
		$html = '<script type="text/javascript" src="js/' . $param . '"></script>';
		$this->content = str_replace($tag, $html, $this->content);
	}
	
	function inc_link($lnkclass, $lnkurl, $lnktarget, $lnkname)
	{
		$alink = $this->gs_file_get_contents($this->absurl . 'template/link.html');
		$alink = str_replace('{GSSE_INCL_LINKCLASS}',$lnkclass,$alink);
		$alink = str_replace('{GSSE_INCL_LINKURL}',$lnkurl,$alink);
		$alink = str_replace('{GSSE_INCL_LINKTARGET}',$lnktarget,$alink);
		$alink = str_replace('{GSSE_INCL_LINKNAME}',$lnkname,$alink);
		return $alink;
	}
	
	function inc_image($imgclass, $imgsrc, $imgalt, $imgtitle)
	{
		$aimage = $this->gs_file_get_contents($this->absurl . 'template/image.html');
		$aimage = str_replace('{GSSE_INCL_IMGCLASS}',$imgclass,$aimage);
		$aimage = str_replace('{GSSE_INCL_IMGSRC}',$imgsrc,$aimage);
		$aimage = str_replace('{GSSE_INCL_IMGALT}',$imgalt,$aimage);
		$aimage = str_replace('{GSSE_INCL_IMGTITLE}',$imgtitle,$aimage);
		return $aimage;
	}
	
	function inc_imglink($lnkclass, $lnkurl, $lnktarget, $imgclass, $imgsrc, $imgalt, $imgtitle)
	{
		$aimglnk = $this->gs_file_get_contents($this->absurl . 'template/imagelink.html');
		$aimglnk = str_replace('{GSSE_INCL_LINKCLASS}',$lnkclass,$aimglnk);
		$aimglnk = str_replace('{GSSE_INCL_LINKURL}',$lnkurl,$aimglnk);
		$aimglnk = str_replace('{GSSE_INCL_LINKTARGET}',$lnktarget,$aimglnk);
		$aimglnk = str_replace('{GSSE_INCL_IMGCLASS}',$imgclass,$aimglnk);
		$aimglnk = str_replace('{GSSE_INCL_IMGSRC}',$imgsrc,$aimglnk);
		$aimglnk = str_replace('{GSSE_INCL_IMGALT}',$imgalt,$aimglnk);
		$aimglnk = str_replace('{GSSE_INCL_IMGTITLE}',$imgtitle,$aimglnk);
		return $aimglnk;
	}
	
	function inc_pcontent($pclass,$pcontent)
	{
		$apcont = $this->gs_file_get_contents($this->absurl . 'template/pcontent.html');
		$apcont = str_replace('{GSSE_INCL_PCLASS}',$pclass,$apcont);
		$apcont = str_replace('{GSSE_INCL_PCONTENT}',$pcontent,$apcont);
		return $apcont;
	}
	
	function inc_input($inp_class,$inp_type,$inp_name,$inp_id,$inp_value,$inp_size,$inp_maxlength,$inp_readonly)
	{
		$ainput = $this->gs_file_get_contents($this->absurl . 'template/input.html');
		$ainput = str_replace('{GSSE_INCL_INPCLASS}',$inp_class,$ainput);
		$ainput = str_replace('{GSSE_INCL_INPTYPE}',$inp_type,$ainput);
		$ainput = str_replace('{GSSE_INCL_INPNAME}',($inp_name != '') ? ' name="' . $inp_name . '"' : '',$ainput);
		$ainput = str_replace('{GSSE_INCL_INPID}',($inp_id != '') ? ' id="' . $inp_id . '"' : '',$ainput);
		$ainput = str_replace('{GSSE_INCL_INPVALUE}',($inp_value != '') ? ' value="' . $inp_value . '"' : '',$ainput);
		$ainput = str_replace('{GSSE_INCL_INPSIZE}',($inp_size > 0) ? ' size="' . $inp_size . '"' : '',$ainput);
		$ainput = str_replace('{GSSE_INCL_INPMAXLEN}',($inp_maxlength > 0) ? ' maxlength="' . $inp_maxlength . '"' : '',$ainput);
		$ainput = str_replace('{GSSE_INCL_INPREADONLY}',($inp_readonly === true) ? ' readonly="readonly"': '',$ainput);
		return $ainput;
	}
	
	function get_prices($itemId)
	{
		$aprices = array();
		$abulk = array();
		$oldprice = 0;
		$price = 0;
		$pdbh = $this->db_connect();
		$psql = "SELECT P.*, A.*, I.itemIsTextHasNoPrice FROM " . $this->dbtoken . "price P, ". $this->dbtoken . "itemdata I, " . $this->dbtoken . "action A WHERE P.prcItemCount = '" . $itemId . "' AND P.prcItemCount = A.itemId AND I.itemItemId = A.itemId ORDER BY prcPrice ASC";
		$perg = mysqli_query($pdbh,$psql);
		if(mysqli_errno($pdbh) == 0)
		{
			if(mysqli_num_rows($perg) > 0)
			{
				while($p = mysqli_fetch_assoc($perg))
				{
					if($p['prcQuantityFrom'] == 0)
					{
						if($p['itemIsTextHasNoPrice'] == 'N') {
							$price = $p['prcPrice'];
						} else {
							$price = 0.0;
						}
						$oldprice = $p['prcOldPrice'];
						if($oldprice == $price)
						{
							$oldprice = 0;
						}
						$referenceprice = $p['prcReferencePrice'];
						$referencequantity = $p['prcReferenceQuantity'];
						$referenceunit = $p['prcReferenceUnit'];
						$actbegindate = $p['action_begindate'];
						$actbegintime = $p['action_begintime'];
						$actenddate =  $p['action_enddate'];
						$actendtime = $p['action_endtime'];
						$actprice = $p['action_price'];
						//$actnormprice = $p['action_pricenormal'];
						$actnormprice = $p['prcPrice'];
						$actshowperiod = $p['action_showperiod'];
						$actshownormal = $p['action_shownormalprice'];
						$isrental = $p['prcIsRental'];
						$billingperiod = $p['prcBillingPeriod'];
						$billingfrequency = $p['prcBillingFrequency'];
						$initialprice = $p['prcInitialPrice'];
						$istrial = $p['prcIsTrial'];
						$trialperiod = $p['prcTrialPeriod'];
						$trialfrequency = $p['prcTrialFrequency'];
						$trialprice = $p['prcTrialPrice'];
						$rentalruntime = $p['prcRentalRuntime'];
					}
					else
					{
						/*array_push($abulk,array($p['prcQuantityFrom'],$p['prcPrice']));*/
						$abulk[] = array($p['prcQuantityFrom'],$p['prcPrice']);
					}
				}
				$aprices = array("price" => $price,
									  "oldprice" => $oldprice, 
									  "referenceprice" => $referenceprice, 
									  "referencequantity" => $referencequantity,
									  "referenceunit" => $referenceunit,
									  "actbegindate" => $actbegindate,
									  "actbegintime" => $actbegintime,
									  "actenddate" => $actenddate,
									  "actendtime" => $actendtime,
									  "actprice" => $actprice,
									  "actnormprice" => $actnormprice, 
									  "actshowperiod" => $actshowperiod,
									  "actshownormal" => $actshownormal,
									  "abulk" => $abulk,
									  "isrental" => $isrental,
									  "billingperiod" => $billingperiod,
									  "billingfrequency" => $billingfrequency,
									  "initialprice" => $initialprice,
									  "istrial" => $istrial,
									  "trialperiod" => $trialperiod,
									  "trialfrequency" => $trialfrequency,
									  "trialprice" => $trialprice,
									  "rentalruntime" => $rentalruntime
				);
			}
		}
		mysqli_free_result($perg);
		mysqli_close($pdbh);
		return $aprices;
	}
	
	function get_availability($itemsInStock,$avaId,$showText2) {
		$availhtml = '';
		if($this->phpactive() === true) {
			$res = $this->get_setting('cbUsePhpAvailability_Checked');
			if($res == 'True') {
				$adbh = $this->db_connect();
				//A TS 01.08.2014:
				//Je nach abgefragtem Teld (itemAvailabilityId oder itemShipmentStatus) sind
				//mehrere Default-Werte m�glich
				if($avaId == -1 || $avaId == '-1' || $avaId == '' || $avaId == 0 || $avaId == '0') {
					if($itemsInStock < 0) $itemsInStock = 0;
					$asql = "SELECT * FROM " . $this->dbtoken . "availability WHERE " .
							  "avaMinQty<=" . $itemsInStock . " AND " .
							  "avaMaxQty>=" . $itemsInStock . " LIMIT 1";
				} else {
					$asql = "SELECT * FROM " . $this->dbtoken . "availability WHERE avaId = '" . $avaId . "'";
				}
				//echo $asql . "<br />";
				$aerg = mysqli_query($adbh,$asql);
				if(mysqli_errno($adbh) == 0) {
					//echo mysqli_num_rows($aerg) . "<br />";
					if(mysqli_num_rows($aerg) == 1) {
						$a = mysqli_fetch_assoc($aerg);
						$avaId = $a['avaId'];
						$avacolor = $a['avaColor'];
						$avadescr = $a['avaDescription'];
						//mysqli_free_result($aerg);
					} else {
						//Wird kein passender Datensatz gefunden,
						//kann das nur 2 Ursachen haben:
						//1. Mengenangaben weisen l�cken auf oder
						//2. der Lagerbestand ist gr��er als der 
						//gr��te Grenzwert
						//Beides liegt in der Verantwortung des ShopBetreibers
						//Dieses Problem muss noch diskutiert werden
						//Vorerst Artikel auf verf�gbar setzen
						$asql2 = "SELECT * FROM " . $this->dbtoken . "availability WHERE 1 " .
									"ORDER BY avaMaxQty DESC LIMIT 1";
						$aerg2 = mysqli_query($adbh,$asql2);
						$a2 = mysqli_fetch_assoc($aerg2);
						$avaId = $a2['avaId'];
						$avacolor = $a2['avaColor'];
						$avadescr = $a2['avaDescription'];
						//mysqli_free_result($aerg2);
					}
				}
				$_SESSION['aitem']['curAvaId'] = $avaId;
				//echo "avaId: " . $avaId . "<br />";
				$ampel = $this->get_setting_s('useAmpel');
				//echo "Ampel: " . $ampel . "<br />";
				if($ampel == 'Y') {
					$availhtml = $this->gs_file_get_contents($this->absurl . 'template/availlight.html');
					switch($avaId) {
						case 1:
							$avalight = 'gruen';
							break;
						case 2:
							$avalight = 'rot_orange';
							break;
						case 3:
							$avalight = 'rot';
							break;
						default:
							$avalight = 'orange';
							break;
					}
					$availhtml = str_replace('{GSSE_AVA_AMPEL}',$avalight,$availhtml);
					if($showText2 == 1) {
						$availhtml = str_replace('{GSSE_AVA_TEXT}',$avadescr,$availhtml);
					} else {
						$availhtml = str_replace('{GSSE_AVA_TEXT}','',$availhtml);
					}
					
				} else {
					$availhtml = $this->gs_file_get_contents($this->absurl . 'template/availbox.html');
					$availhtml = str_replace('{GSSE_AVA_COLOR}',$avacolor,$availhtml);
					$availhtml = str_replace('{GSSE_AVA_TEXT}',$avadescr,$availhtml);
					if($showText2 == 1) {
						$availhtml = str_replace('{GSSE_AVA_TEXT2}',$avadescr,$availhtml);
					} else {
						$availhtml = str_replace('{GSSE_AVA_TEXT2}','',$availhtml);
					}
				}
				//echo "Result: " . var_dump($availhtml) . "<br />";
				//mysqli_close($adbh);
			}
		}
		return $availhtml;
	}
	
	function get_availability_text($itemsInStock,$avaId,$showText2)
	{
		$avadescr = '';
		if($this->phpactive() === true) {
			$res = $this->get_setting('cbUsePhpAvailability_Checked');
			if($res == 'True') {
				$adbh = $this->db_connect();
				//A TS 01.08.2014:
				//Je nach abgefragtem Teld (itemAvailabilityId oder itemShipmentStatus) sind
				//mehrere Default-Werte m�glich
				if($avaId == -1 || $avaId == '-1' || $avaId == '' || $avaId == 0 || $avaId == '0') {
					if($itemsInStock < 0) $itemsInStock = 0;
					$asql = "SELECT * FROM " . $this->dbtoken . "availability WHERE " .
							  "avaMinQty<=" . $itemsInStock . " AND " .
							  "avaMaxQty>=" . $itemsInStock . " LIMIT 1";
				} else {
					$asql = "SELECT avaDescription FROM " . $this->dbtoken . "availability WHERE avaId = '" . $avaId . "'";
				}
				//echo $asql . "<br />";
				$aerg = mysqli_query($adbh,$asql);
				if(mysqli_errno($adbh) == 0) {
					//echo mysqli_num_rows($aerg) . "<br />";
					if(mysqli_num_rows($aerg) == 1) {
						$a = mysqli_fetch_assoc($aerg);
						$avadescr = $a['avaDescription'];
						//mysqli_free_result($aerg);
					} else {
						//Wird kein passender Datensatz gefunden,
						//kann das nur 2 Ursachen haben:
						//1. Mengenangaben weisen l�cken auf oder
						//2. der Lagerbestand ist gr��er als der 
						//gr��te Grenzwert
						//Beides liegt in der Verantwortung des ShopBetreibers
						//Dieses Problem muss noch diskutiert werden
						//Vorerst Artikel auf verf�gbar setzen
						$asql2 = "SELECT avaDescription FROM " . $this->dbtoken . "availability WHERE 1 " .
									"ORDER BY avaMaxQty DESC LIMIT 1";
						$aerg2 = mysqli_query($adbh,$asql2);
						$a2 = mysqli_fetch_assoc($aerg2);
						$avadescr = $a2['avaDescription'];
						//mysqli_free_result($aerg2);
					}
				}
				$_SESSION['aitem']['curAvaId'] = $avaId;
				//echo "avaId: " . $avaId . "<br />";
				//echo "Result: " . var_dump($availhtml) . "<br />";
				//mysqli_close($adbh);
			}
		}
		return $avadescr;
	}
	
	function get_variants($itemNumber)
	{
		$aVariants = array();
		$vdbh = $this->db_connect();
				
		$vsql = "SELECT * FROM " . $this->dbtoken . "item_to_variant WHERE varVariantGroup = '" . $itemNumber . "' ORDER BY varVariantIdNo ASC";
		$verg = mysqli_query($vdbh,$vsql);
		if(mysqli_errno($vdbh) == 0)
		{
			while($v = mysqli_fetch_assoc($verg))
			{
				/*array_push($aVariants,array(
							'ItemNumber' => $v['varItemNumber'],
							'VariantGroup' => $v['varVariantGroup'],
							'Order' => $v['varVariantIdNo'],
							'ShowAsDropDown' => $v['ShowAsDropDown']));*/
				$aVariants[] = array(
							'ItemNumber' => $v['varItemNumber'],
							'VariantGroup' => $v['varVariantGroup'],
							'Order' => $v['varVariantIdNo'],
							'ShowAsDropDown' => $v['ShowAsDropDown']);
			}
		}
		else
		{
			die(mysqli_error($vdbh) . "<br />" . $vsql);
		}
		mysqli_free_result($verg);
		mysqli_close($vdbh);
		return $aVariants;
	}
	
	function get_itemdownloads($itemNumber)
	{
		$aDown = array();
		$addbh = $this->db_connect();
				
		$adsql = "SELECT title, fileName FROM " . $this->dbtoken . "itemdownloads WHERE itemNumber = '" . $itemNumber . "' AND languageId = '" . $this->lngID . "' ORDER BY title ASC";
		$aderg = mysqli_query($addbh,$adsql);
		if(mysqli_errno($addbh) == 0)
		{
			while($ad = mysqli_fetch_assoc($aderg))
			{
				/*array_push($aDown,array(
							'title' => $ad['title'],
							'filename' => $ad['fileName']));*/
				$aDown[] = array(
							'title' => $ad['title'],
							'filename' => $ad['fileName']);
			}
		}
		else
		{
			die(mysqli_error($addbh) . "<br />" . $adsql);
		}
		mysqli_free_result($aderg);
		mysqli_close($addbh);
		return $aDown;
	}
	
	function get_bundles($itemNumber)
	{
		$aBundles = array();
		$bdbh = $this->db_connect();
		
		$bsql = "SELECT itemNumber, amount, " .
		"(SELECT itemItemId FROM " . $this->dbtoken . "itemdata WHERE " . $this->dbtoken . "itemdata.itemItemNumber = " . $this->dbtoken . "bundles.itemNumber AND ". $this->dbtoken . "itemdata.itemLanguageId = '" . $this->lngID . "' LIMIT 1) AS itemID, " .
		"(SELECT itemItemDescription FROM " . $this->dbtoken . "itemdata WHERE " . $this->dbtoken . "itemdata.itemItemNumber = " . $this->dbtoken . "bundles.itemNumber AND ". $this->dbtoken . "itemdata.itemLanguageId = '" . $this->lngID . "' LIMIT 1) AS itemDescription, " .
		"(SELECT itemSmallImageFile FROM " . $this->dbtoken . "itemdata WHERE " . $this->dbtoken . "itemdata.itemItemNumber = " . $this->dbtoken . "bundles.itemNumber AND ". $this->dbtoken . "itemdata.itemLanguageId = '" . $this->lngID . "' LIMIT 1) AS itemPic, " .
		"(SELECT itemItemPage FROM " . $this->dbtoken . "itemdata WHERE " . $this->dbtoken . "itemdata.itemItemNumber = " . $this->dbtoken . "bundles.itemNumber AND ". $this->dbtoken . "itemdata.itemLanguageId = '" . $this->lngID . "' LIMIT 1) AS itemPage, " .
		"(SELECT prcPrice FROM " . $this->dbtoken . "price WHERE " . $this->dbtoken . "price.prcItemCount = itemID AND ". $this->dbtoken . "price.prcCountryId = '" . $this->cntID . "' AND ". $this->dbtoken . "price.prcQuantityFrom = '0' LIMIT 1) AS itemPrice " .
		"FROM " . $this->dbtoken . "bundles WHERE bundleGroup='" . 
		$itemNumber . "' AND bundleLanguageId='" . $this->lngID . "' ORDER BY amount DESC";
		$berg = @mysqli_query($bdbh,$bsql);
		if(mysqli_errno($bdbh) == 0)
		{
			if(mysqli_num_rows($berg) > 0)
			{
				while($b = @mysqli_fetch_assoc($berg))
				{
					/*array_push($aBundles,array("itemID" => $b['itemID'],
														"itemAmount" => $b['amount'],
														"itemNumber" => $b['itemNumber'], 
														"itemDescription" => $b['itemDescription'],
														"itemPic" => $b['itemPic'],
														"itemPage" => $b['itemPage'],
														"itemPrice" => $b['itemPrice']
					));*/
					$aBundles[] = array("itemID" => $b['itemID'],
											"itemAmount" => $b['amount'],
											"itemNumber" => $b['itemNumber'], 
											"itemDescription" => $b['itemDescription'],
											"itemPic" => $b['itemPic'],
											"itemPage" => $b['itemPage'],
											"itemPrice" => $b['itemPrice']);
				}
			}
			mysqli_free_result($berg);
		}
		else
		{
			die(mysqli_error($bdbh) . ":<br />" . $bsql);
		}
		mysqli_close($bdbh);
		return  $aBundles;
	}
	
	function get_pricehistory($itemNumber)
	{
		require("dynsb/class/class.diagram.bar.php");
		$pricehist = '';
		$prices = array();
		$dates = array();
		$maxval = 0;
		$phdbh = $this->db_connect();
		
		$phsql = "SELECT * FROM ".$this->dbtoken."pricehistory WHERE
					prchItemNumber = '".$itemNumber."' AND 
					prchLanguageId = '".$this->lngID."'
					ORDER BY prchDateTime DESC 
					LIMIT 4";
		$pherg = mysqli_query($phdbh,$phsql);
		if(mysqli_errno($pphdbh) == 0)
		{
			if(mysqli_num_rows($pherg) > 1)
			{
				while($obj = mysqli_fetch_object($pherg))
				{
					/*array_push($prices, $obj->prchPrice);*/
					$prices[] = $obj->prchPrice;
					/*array_push($dates, date('d.m.Y', strtotime($obj->prchDateTime)));*/
					$dates[] = date('d.m.Y', strtotime($obj->prchDateTime));
					if($maxval < $obj->prchPrice)
					{
						$maxval = $obj->prchPrice;
					}
				}

				if(count($prices) == 0)
				{
					/*array_push($prices, 0);*/
					$prices[] = 0;
				}
				if(count($dates) == 0)
				{
					/*array_push($dates, 0);*/
					$dates[] = 0;
				}
				$gradientToogleStep = 2;
				$dataTextOffset = 1;
				//get get get
				$xsize = 400;
				$ysize = 100;
				//($ysize > 600) ? $addRightSpc = 0 : $addRightSpc =  60; //TODO ?needed for what?

				$layout    = 0;
				$barlayout = 0;

				$prices = array_reverse($prices);
				$dates = array_reverse($dates);

				$scaleval = doubleval($maxval / $ysize);

				//to avoid divison by zero
				if($scaleval == 0) $scaleval = 1;

				$scalevalend = $scaleval + doubleval(($scaleval / 100) * 10);
				if($scalevalend == 0) $scalevalend = 1;

				//create DIA object
				$dia = new dia_bar($xsize, $ysize, 80, 50, 60, 30 + $addRightSpc, $prices, $layout);

				$dia->setBackgroundColor('white');
				$dia->setDataFontColor('darkgrey');

				//$dia->setGradientTotal('gslightblue', 'white');
				$dia->setGradientToogleStep($gradientToogleStep, 'gslightblue', 'white', 'white', 'lightgrey');
				$dia->recalcYStep($scalevalend, $maxval);
				$dia->createGrid(20, 'grey');
				$dia->createDiaBorder('grey');

				if($layout == 0) {
					$spc = ($dia->xstep / 100) * 38;
				} else {
					$spc = ($dia->ystep / 100) * 38;
				}

				if($barlayout == 0) $dia->displayBars($scalevalend, 'gsdarkblue', 'gslightblue', $spc, $dataTextOffset);
				if($barlayout == 1) $dia->displayGradientBars($scalevalend, 'white', 'darkgreen', 'darkgrey', $spc, 0);

				$ruler = 25;
				$rulerval = doubleval($ruler / $scaleval);
				if($rulerval < 4) $rulerval = 4;
				if($rulerval > 10) $rulerval = 10;

				$dia->addRulersX('darkgrey', $rulerval, 'bottom');
				$imgLeftSpcTemp = $dia->imgLeftSpc;
				$dia->imgLeftSpc = 35;
				$dia->addRulersXDataValues('darkgrey', 8, $dates);
				$dia->imgLeftSpc = $imgLeftSpcTemp;
				$dia->addRulersY('darkgrey', $rulerval, 'left');

				if($layout == 0) {
					$dia->addRulersYText('darkgrey', 2, 55);
					//$dia->addRulersXData('darkgrey', 2);
				} else {
					$dia->addRulersXText('darkgrey', 2, 5);
					$dia->addRulersYData('darkgrey', 2, 52);
				}

				$dia->setHText("Preisentwicklung", 3, 20 - ($layout * 10), 15, 'gsdarkblue');
				$dia->setHText($strPeriod, 2, 20 - ($layout * 10), 30, 'gsdarkorange');

				$pricehist = $dia->createOutput();
			}
		}
		return $pricehist;
	}
	
	function get_varmainitem($ItemNumber)
	{
		$amainvaritemno = array('itemNumber' => $ItemNumber,'itemName' => "",'itemPic' => "",'itemId' => "",'itemItemPage' => "");
		$vdbh = $this->db_connect();
		
		/*Artikelnummer ist Hauptnummer*/
		$sql1 = "SELECT varItemNumber FROM " . $this->dbtoken . "item_to_variant WHERE varVariantGroup = '" . $ItemNumber . "' LIMIT 1";
		$erg1 = mysqli_query($vdbh,$sql1);
		if(mysqli_num_rows($erg1) == 0)
		{
			//Kein Fund, Hauptartikelnummer ermitteln
			$sql2 = "SELECT varVariantGroup FROM " . $this->dbtoken . "item_to_variant WHERE varItemNumber = '" . $ItemNumber . "' LIMIT 1";
			$erg2 = mysqli_query($vdbh,$sql2);
			if(mysqli_num_rows($erg2) > 0)
			{
				$v = mysqli_fetch_assoc($erg2);
				$amainvaritemno['itemNumber'] = $v['varVariantGroup'];
				$sql3 = "SELECT itemItemDescription, itemVariantDescription, itemSmallImageFile, itemItemId, itemItemPage FROM " . $this->dbtoken . "itemdata WHERE itemItemNumber = '" . $v['varVariantGroup'] . "' AND itemLanguageId = '" . $this->lngID . "' LIMIT 1";
				$erg3 = mysqli_query($vdbh,$sql3);
				$v2 = mysqli_fetch_assoc($erg3);
				if($v2['itemVariantDescription'] != '')
				{
					$amainvaritemno['itemName'] = $v2['itemVariantDescription'];
				}
				else
				{
					$amainvaritemno['itemName'] = $v2['itemItemDescription'];
				}
				$amainvaritemno['itemPic'] = $v2['itemSmallImageFile'];
				$amainvaritemno['itemId'] = $v2['itemItemId'];
				$amainvaritemno['itemItemPage'] = $v2['itemItemPage'];
				mysqli_free_result($erg3);
			}
			mysqli_free_result($erg2);
		}
		mysqli_free_result($erg1);
		return $amainvaritemno;
	}
	
	function get_shipment($iAreaId)
	{
		$aShipm = array();
		$smdbh = $this->db_connect();
		//A SM 06.04.2017 - Spezialversand ber�cksichtigen
        $withSpecShip = False;
        $withoutSpecShip = False;
        $specShipLabel = $this->get_setting('edSpecShipLabel_Text');
        if(isset($_SESSION['basket'])){
            $basket_count = count($_SESSION['basket']);
            for($b = 0; $b < $basket_count; $b++)
            {
               $sql = "SELECT ShippingPrice FROM " . $this->dbtoken . "specshipprices WHERE ItemNumber = '" . $_SESSION['basket'][$b]['art_num'] . "' AND AreaId = ".$iAreaId;
                $erg = mysqli_query($smdbh,$sql);
                if(mysqli_errno($smdbh) == 0)
                {
                    if(mysqli_num_rows($erg) > 0)
                    {
                       $withSpecShip = True; 
                    } else {
                        $withoutSpecShip = True;
                    }
                }
                mysqli_free_result($erg);
            }    
        }
        if(($withSpecShip === True) && ($withoutSpecShip === False)){
            $aShipm[] = array("sortid" => $dl['SortId'], "name" => $specShipLabel);
        } else { 
            $smsql = "SELECT SortId FROM " . $this->dbtoken . "deliveryarea WHERE AddressArea = '" . $iAreaId . "' AND CountryId = '" . $this->cntID . "' ORDER BY SortId ASC";
            $smerg = mysqli_query($smdbh,$smsql);
            if(mysqli_errno($smdbh) == 0)
            {
                if(mysqli_num_rows($smerg) > 0)
                {
                    while($sm = mysqli_fetch_assoc($smerg))
                    {
                        $dlsql = "SELECT SortId, ShippingName FROM " . $this->dbtoken . "deliverylanguage WHERE SortId = '" . $sm['SortId'] . "' AND LanguageId = '" . $this->lngID . "' AND ShippingName != '' LIMIT 1";
                        $dlerg = mysqli_query($smdbh,$dlsql);
                        if(mysqli_errno($smdbh) == 0)
                        {
                            if(mysqli_num_rows($dlerg) > 0)
                            {
                                $dl = mysqli_fetch_assoc($dlerg);
                                /*array_push($aShipm,array("sortid" => $dl['SortId'],
                                                                 "name" => $dl['ShippingName']));*/
                                if($withSpecShip === False){                                 
                                    $aShipm[] = array("sortid" => $dl['SortId'],
                                                        "name" => $dl['ShippingName']);
                                } else if(($withSpecShip === True) && ($withoutSpecShip === True)){
                                    $aShipm[] = array("sortid" => $dl['SortId'],
                                                        "name" => $dl['ShippingName'].' + '.$specShipLabel);
                                } else {
                                    $aShipm[] = array("sortid" => $dl['SortId'],
                                                        "name" => $specShipLabel);
                                }
                            }
                            mysqli_free_result($dlerg);
                        }
                    }
                }
                mysqli_free_result($smerg);
            }
        }
		return $aShipm;
	}
	
	function get_payment($iAreaId, $download, $rentals)
	{
		$aPaym = array();
		$pmdbh = $this->db_connect();
		
		$pmsql = "SELECT SortId FROM " . $this->dbtoken . "paymentcountry WHERE AddressArea = '" . $iAreaId . "' AND CountryId = '" . $this->cntID . "' ORDER BY SortId ASC";
		$pmerg = mysqli_query($pmdbh,$pmsql);
		if(mysqli_errno($pmdbh) == 0)
		{
			if(mysqli_num_rows($pmerg) > 0)
			{
				while($pm = mysqli_fetch_assoc($pmerg))
				{
					$plsql = "SELECT " . $this->dbtoken . "paymentlanguage.SortId, " . $this->dbtoken . "paymentlanguage.Text1, " . $this->dbtoken . "paymentinternalnames.InternalName FROM " . $this->dbtoken . "paymentlanguage JOIN " . $this->dbtoken . "paymentinternalnames ON " . $this->dbtoken . "paymentlanguage.SortId = " . $this->dbtoken . "paymentinternalnames.SortId WHERE " . $this->dbtoken . "paymentlanguage.SortId = '" . $pm['SortId'] . "' AND " . $this->dbtoken . "paymentlanguage.LanguageId = '" . $this->lngID . "' AND " . $this->dbtoken . "paymentlanguage.Text1 != '' LIMIT 1";
					
					$plerg = mysqli_query($pmdbh,$plsql);
					if(mysqli_errno($pmdbh) == 0)
					{
						if(mysqli_num_rows($plerg) > 0)
						{
							$pl = mysqli_fetch_assoc($plerg);
							
							if($download == true && $rentals == false)
							{								 
								if(($pl['InternalName'] <> 'PaymentCashOnDelivery') AND ($pl['InternalName'] <> 'PaymentInAdvance') AND ($pl['InternalName'] <> 'PaymentInvoice'))
								{
									$aPaym[] = array("sortid" => $pl['SortId'],
												  "name" => $pl['Text1'],
												  "internalname" => $pl['InternalName']);
								}
							}
							else if($rentals == true)
							{
								if(($pl['InternalName'] == 'PaymentDirectDebit'))
								{
									$aPaym[] = array("sortid" => $pl['SortId'],
												  "name" => $pl['Text1'],
												  "internalname" => $pl['InternalName']);
								}
							}
							else
							{
								$aPaym[] = array("sortid" => $pl['SortId'],
												  "name" => $pl['Text1'],
												  "internalname" => $pl['InternalName']);
							}
						}
						//mysqli_free_result($plerg);
					}
				}
			}
			//mysqli_free_result($pmerg);
		}
		return $aPaym;
	}
	
	function get_countries($areaID)
	{
		$aState = array();
		$ctdbh = $this->db_connect();
		
		$ctsql = "SELECT countryid, " .
					"(SELECT cntLangTag FROM " . $this->dbtoken . "countries WHERE " . $this->dbtoken . "countries.cntCountryCode = " . $this->dbtoken . "countriesareas.countryid) AS country " .
					"FROM " . $this->dbtoken . "countriesareas " .
					"WHERE addressareaid = '" . $areaID . "' ORDER BY countryid ASC";
		$cterg = mysqli_query($ctdbh,$ctsql);
		if(mysqli_errno($ctdbh) == 0)
		{
			if(mysqli_num_rows($cterg) > 0)
			{
				while($ct = mysqli_fetch_assoc($cterg))
				{
					/*array_push($aState,array("oval" => $ct['countryid'], "otext" => $this->get_lngtext($ct['country'])));*/
					$aState[] = array("oval" => $ct['countryid'], "otext" => $this->get_lngtext($ct['country']));
				}
				if(count($aState) > 1)
				{
					foreach ($aState as $key => $row) {
							$CNM[$key] = $row['otext'];
					}
					array_multisort($CNM, SORT_ASC, $aState);
				}
			}
			mysqli_free_result($cterg);
		}
		else
		{
			die(mysqli_error($ctdbh) . "<br />" . $ctsql);
		}
		return $aState;
	}
	
	function formfriendly($cstr)
	{
		$cres = str_replace('�','Ae',$cstr);
		$cres = str_replace('�','Oe',$cres);
		$cres = str_replace('�','Ue',$cres);
		$cres = str_replace('�','ae',$cres);
		$cres = str_replace('�','oe',$cres);
		$cres = str_replace('�','ue',$cres);
		$cres = str_replace('�','sz',$cres);
		$cres = preg_replace_callback ('#[^a-z0-9]#i', function ($m) { return ''; }, $cres);
		return $cres;
	}
	
	function set_userdata($content)
	{
		include_once('inc/customerdefs.inc.php');
		foreach($aDefs as $key => $val)
		{
			if(stripos($val,'_INP|') !== false)
			{
				if($key == 'cusBirthdate')
				{
					if($this->lngID == 'deu')
					{
						$cval = $this->conv_date($_SESSION['login'][$key],'D');
					}
					else
					{
						$cval = $_SESSION['login'][$key];
					}
				}
				else
				{
					$cval = $_SESSION['login'][$key];
				}
				$content = str_replace($val, $cval,$content);
			}
		}
		return $content;
	}
	
	function parse_cookies($cont)
	{
		$aCTags = $this->get_tags_ret($cont);
		foreach ($aCTags as $tag)
		{
			$tag = str_replace('__','*#*#',$tag);
			$apos = strpos($tag,'_');
			$prefix = substr($tag,1,$apos - 1);
			$bpos = strpos($tag,'_',$apos + 1);
			$func = substr($tag,$apos + 1,($bpos-$apos)-1);
			$param = substr($tag,$bpos + 1,(strlen($tag)-$bpos)-2);
			$param = str_replace('*#*#','__',$param);
			$tag = str_replace('*#*#','__',$tag);
			if($func == 'COOKIE')
			{
				$cookie = $this->get_cookie($param);
				$cont = str_replace($tag,$cookie,$cont);
			}
		}
		return $cont;
	}
	
	function get_cookie($params)
	{
		$cookie = '';
		$aParams = explode('|',$params);
		$numparam = count($aParams);
		if($numparam > 0)
		{
			if(substr($aParams[1],0,7) == 'LangTag')
			{
				$cookiename = $this->get_lngtext($aParams[1]);
			}
			else
			{
				$cookiename = $aParams[1];
			}
			if($numparam == 2)
			{
				//INPUT,TEXTAREA
				if(isset($_COOKIE[$cookiename]))
				{
					$cookie = $_COOKIE[$cookiename];
				}
			}
			if($numparam == 3)
			{
				//SELECT, CHECKBOX, RADIOBUTTON
				switch($aParams[0])
				{
					case "SEL":
						$ret = "selected";
						break;
					case "CHK":
						$ret = "checked";
						break;
					case "RAD":
						$ret = "checked";
						break;
					default:
						$ret = "";
						break;
				}
				if(isset($_COOKIE[$cookiename]))
				{
					if(substr($aParams[2],0,7) == 'LangTag')
					{
						$cookieval = $this->get_lngtext($aParams[2]);
					}
					else
					{
						$cookieval = $aParams[2];
					}
					if($_COOKIE[$cookiename] == $cookieval)
					{
						$cookie = $ret;
					}
				}
			}
		}
		return urldecode($cookie);
	}
	
	function get_shipcost($shipID,$areaID,$basketvalue,$basketweight)
	{
		$shipcost = 0;
		$aShipCost = $this->get_shipcosttable($shipID,$areaID);
		//print_r($aShipCost);
		$idx = 0;
		if($basketweight > 0)
		{
			$max = count($aShipCost) - 1;
			for($b = $max; $b >= 0; $b--)
			{
				if($basketweight >= $aShipCost[$b]['weight'])
				{
					$idx = $b;
					break;
				}
			}
		}
		
		if($aShipCost[$idx]['fromvalue1'] == 0 && $aShipCost[$idx]['fromvalue2'] == 0)
		{
			$shipcost = $aShipCost[$idx]['cost'];
		}
		
		if($aShipCost[$idx]['fromvalue1'] > 0 && $aShipCost[$idx]['fromvalue2'] == 0)
		{
			if($basketvalue < $aShipCost[$idx]['fromvalue1'])
			{
				$shipcost = $aShipCost[$idx]['cost'];
			}
			else
			{
				$shipcost = $aShipCost[$idx]['cost1'];
			}
		}
		
		if($aShipCost[$idx]['fromvalue1'] > 0 && $aShipCost[$idx]['fromvalue2'] > 0)
		{
			if($aShipCost[$idx]['fromvalue1'] <= $aShipCost[$idx]['fromvalue2'])
			{
				if($basketvalue >= $aShipCost[$idx]['fromvalue1'] && $basketvalue < $aShipCost[$idx]['fromvalue2'])
				{
					$shipcost = $aShipCost[$idx]['cost1'];
				}
				else
				{
					if($basketvalue < $aShipCost[$idx]['fromvalue1'] && $basketvalue < $aShipCost[$idx]['fromvalue2'])
					{
						$shipcost = $aShipCost[$idx]['cost'];
					}
					else
					{
						$shipcost = $aShipCost[$idx]['cost2'];
					}
				}
			}
			else
			{
				if($basketvalue >= $aShipCost[$idx]['fromvalue2'] && $basketvalue < $aShipCost[$idx]['fromvalue1'])
				{
					$shipcost = $aShipCost[$idx]['cost2'];
				}
				else
				{
					$shipcost = $aShipCost[$idx]['cost1'];
				}
			}
		}
		
		return $shipcost;
	}
	
	function get_shipcosttable($shipID,$areaID)
	{
		$aShipC = array();
		$scdbh = $this->db_connect();
		
		//Basic shipping costs
		$bscsql = "SELECT * FROM " . $this->dbtoken . "deliveryarea WHERE AddressArea = '" . $areaID . "' AND SortId = '" . $shipID ."' AND CountryId = '" . $this->cntID . "' LIMIT 1";
		$bscerg = mysqli_query($scdbh,$bscsql);
		if(mysqli_errno($scdbh) == 0)
		{
			if(mysqli_num_rows($bscerg) > 0)
			{
				$bsc = mysqli_fetch_assoc($bscerg);
				/*array_push($aShipC,array("weight" => 0,
												 "cost" => $bsc['ShippingCost'],
												 "fromvalue1" => $bsc['FromInvoiceAmount1'],
												 "cost1" => $bsc['MaxShippingCharge1'],
												 "fromvalue2" => $bsc['FromInvoiceAmount2'],
												 "cost2" => $bsc['MaxShippingCharge2']));*/
				$aShipC[] = array("weight" => 0,
										"cost" => $bsc['ShippingCost'],
										"fromvalue1" => $bsc['FromInvoiceAmount1'],
										"cost1" => $bsc['MaxShippingCharge1'],
										"fromvalue2" => $bsc['FromInvoiceAmount2'],
										"cost2" => $bsc['MaxShippingCharge2']);
			} else {
				//Download
				$aShipC[] = array("weight" => 0,
						"cost" => 0,
						"fromvalue1" => 0,
						"cost1" => 0,
						"fromvalue2" =>0,
						"cost2" => 0);
			}
			mysqli_free_result($bscerg);
		}
		
		//Shipping costs by weight
		$wscsql = "SELECT * FROM " . $this->dbtoken . "deliverycountry WHERE SortId = '" . $shipID ."' AND CountryId = '" . $this->cntID . "' ORDER BY ShippingToWeight ASC";
		$wscerg = mysqli_query($scdbh,$wscsql);
		if(mysqli_errno($scdbh) == 0)
		{
			if(mysqli_num_rows($wscerg) > 0)
			{
				while($wsc = mysqli_fetch_assoc($wscerg))
				{
					/*array_push($aShipC,array("weight" => $wsc['ShippingToWeight'],
													 "cost" => $wsc['ShippingCost'],
													 "fromvalue1" => $wsc['FromInvoiceAmount1'],
													 "cost1" => $wsc['MaxShippingCharge1'],
													 "fromvalue2" => $wsc['FromInvoiceAmount2'],
													 "cost2" => $wsc['MaxShippingCharge2']));*/
					$aShipC[] = array("weight" => $wsc['ShippingToWeight'],
											"cost" => $wsc['ShippingCost'],
											"fromvalue1" => $wsc['FromInvoiceAmount1'],
											"cost1" => $wsc['MaxShippingCharge1'],
											"fromvalue2" => $wsc['FromInvoiceAmount2'],
											"cost2" => $wsc['MaxShippingCharge2']);
					
				}
			}
			mysqli_free_result($wscerg);
		}
		return $aShipC;
	}
	
	function get_vats()
	{
		$aRetVats = array();
		//Der Steuersatz im Element 0 ist der Standard-Steuersatz und
		//wird f�r Zusatzkosten (Geb�hren/Versandkosten) verwendet
		$cVats = $this->db_text_ret('settingmemo|SettingMemo|SettingName|memoVatRates');
		$aMyVats = explode(chr(13),$cVats);
		$for_max = count($aMyVats) - 1;
		for($v = 0; $v < $for_max; $v++)
		{
			/*array_push($aRetVats,array("vatrate" => $aMyVats[$v],"vattotal" => 0));*/
			$aRetVats[] = array("vatrate" => $aMyVats[$v],"vattotal" => 0);
		}
		return $aRetVats;
	}
	
	function get_custemail($custEmail)
	{
		$ctdbh = $this->db_connect();
		
		$custsql = "SELECT cusIdNo FROM " . $this->dbtoken . "customer WHERE " . $this->dbtoken . "customer.cusEMail = '" . $custEmail . "'";
		$cterg = mysqli_query($ctdbh,$custsql);
		if(mysqli_errno($ctdbh) == 0)
		{
			if(mysqli_num_rows($cterg) > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
			mysqli_free_result($cterg);
		}
		else
		{
			die(mysqli_error($ctdbh) . "<br />" . $custsql);
		}
	}

	function get_item($item)
	{
		$adbh = $this->db_connect();
		
		$asql = "SELECT i.*, p.* FROM " . $this->dbtoken . "itemdata i " .
				  "JOIN " . $this->dbtoken . "price p ON i.itemItemId = p.prcItemCount " .
				  "WHERE i.itemItemId = '" . $item . "'";
		$aerg = mysqli_query($adbh,$asql);
		if(mysqli_errno($adbh) == 0)
		{
			if(mysqli_num_rows($aerg) > 0)
			{
				$aitem = mysqli_fetch_assoc($aerg);
				$_SESSION['aitem'] = $aitem;
			}
		}
		mysqli_free_result($aerg);
		mysqli_close($adbh);
	}
	
	function get_itempics($itemId)
	{
		$aMyPics = array();
		$pdbh = $this->db_connect();
		
		$psql = "SELECT * FROM " . $this->dbtoken . "gallery WHERE ItemId = '" . $itemId . "' ORDER BY ImageOrder ASC, ImageName ASC";
		$perg = mysqli_query($pdbh,$psql);
		if(mysqli_errno($pdbh) == 0)
		{
			while($p = mysqli_fetch_assoc($perg))
			{
				/*array_push($aMyPics,array("ImageName" => $p['ImageName'],
												  "ImageLink" => $p['ImageLink'],
												  "ImageDesc" => $p['ImageDescr'],
												  "IsStandard" => $p['IsStandard']));*/
				//if(file_exists('images/medium/'.$p['ImageName'])) {
					$aMyPics[] = array("ImageName" => $p['ImageName'],
											 "ImageLink" => $p['ImageLink'],
											 "ImageDesc" => $p['ImageDescr'],
											 "IsStandard" => $p['IsStandard'],
											 "SmallExists" => (file_exists('images/small/'.$p['ImageName']) ? 1 : 0),
											 "MediumExists" => (file_exists('images/medium/'.$p['ImageName']) ? 1 : 0),
											 "BigExists" => (file_exists('images/big/'.$p['ImageName']) ? 1 : 0),
											 "HugeExists" => (file_exists('images/huge/'.$p['ImageName']) ? 1 : 0)
											 );
				//}
			}
			mysqli_free_result($perg);
		}
		mysqli_close($pdbh);
		return $aMyPics;
	}
	
	function get_pgtemplate($pgIDX)
	{
		$pgtmpl = '';
		$pgtdbh = $this->db_connect();
		
		$pgtsql = "SELECT TemplateFile FROM " . $this->dbtoken . "productgroups WHERE ObjectCount = '" . $pgIDX . "' LIMIT 1";
		$pgterg = mysqli_query($pgtdbh,$pgtsql);
		if(mysqli_errno($pgtdbh) == 0)
		{
			if(mysqli_num_rows($pgterg) > 0)
			{
				$pgt = mysqli_fetch_assoc($pgterg);
				$pgtmpl = $pgt['TemplateFile'];
			}
			mysqli_free_result($pgterg);
		}
		mysqli_close($pgtdbh);
		return $pgtmpl;
	}
	
	function chk_action($iItemId,$aMyPrices)
	{
		$act = 'N';
		if(isset($aMyPrices['actbegintime'])) {
			/*$bh = intval(substr($aMyPrices['actbegintime'],0,2));
			$bn = intval(substr($aMyPrices['actbegintime'],3,2));
			$bs = intval(substr($aMyPrices['actbegintime'],6,2));
			$bd = intval(substr($aMyPrices['actbegindate'],0,2));
			$bm = intval(substr($aMyPrices['actbegindate'],3,2));
			$by = intval(substr($aMyPrices['actbegindate'],6,4));*/
			$aBeginDate = explode('-',$aMyPrices['actbegindate']);
			$aBeginTime = explode(':',$aMyPrices['actbegintime']);
			//$begin =  mktime($bh, $bn, $bs, $bm, $bd, $by);
			$begin =  mktime(intval($aBeginTime[0]), intval($aBeginTime[1]), intval($aBeginTime[0]), intval($aBeginDate[1]), intval($aBeginDate[2]), intval($aBeginDate[0]));
			
			/*$eh = intval(substr($aMyPrices['actendtime'],0,2));
			$en = intval(substr($aMyPrices['actendtime'],3,2));
			$es = intval(substr($aMyPrices['actendtime'],6,2));
			$ed = intval(substr($aMyPrices['actenddate'],0,2));
			$em = intval(substr($aMyPrices['actenddate'],3,2));
			$ey = intval(substr($aMyPrices['actenddate'],6,4));*/
			$aEndDate = explode('-',$aMyPrices['actenddate']);
			$aEndTime = explode(':',$aMyPrices['actendtime']);
			//$end =  mktime($eh, $en, $es, $em, $ed, $ey);
			$end =  mktime(intval($aEndTime[0]), intval($aEndTime[1]), intval($aEndTime[0]), intval($aEndDate[1]), intval($aEndDate[2]), intval($aEndDate[0]));
			
			$actuel = time();
		
			if($actuel>=$end) {
				//Aktion austragen
				$actdbh = $this->db_connect();
				
				$actsql = "UPDATE " . $this->dbtoken . "itemdata SET itemIsAction='N' WHERE itemItemId='" . $iItemId . "' and itemLanguageId='" . $this->lngID . "' LIMIT 1";
				mysqli_query($actdbh,$actsql);
				mysqli_close($actdbh);
			}
		
			if($actuel >= $begin && $actuel < $end) {
				$act = 'Y';
			}
		}
		return $act;
	}

	
	function exist_customer($useremail, $password)
	{
		$pdbh = $this->db_connect();
		
		$sql = "SELECT * FROM " . $this->dbtoken . "customer WHERE cusEMail = '" . $useremail . "' AND cusPassword = '".$password."'";
		$perg = mysqli_query($pdbh,$sql);
		if(mysqli_errno($pdbh) == 0)
		{
			if(mysqli_num_rows($perg) > 0)
			{
				$_SESSION['login']['ok'] = true;
				$c = mysqli_fetch_assoc($perg);
				//$i = 0;
				while ($f = mysqli_fetch_field($perg))
				{
					//$f = mysqli_fetch_field($perg, $i);
					$_SESSION['login'][$f->name] = $c[$f->name];
					//$i++;
				}
				return true;
			}
			else
			{
				unset($_SESSION['login']);
				return false;
			}
			mysqli_free_result($perg);
		}
		else
		{
			die(mysqli_error($pdbh) . "<br />" . $sql);
		}
		mysqli_close($pdbh);
	}

	
	function db_insert($db_table,$db_values)
	{
		$res = true;
		$idbh = $this->db_connect();
		
		$isql = "INSERT INTO " . $this->dbtoken . $db_table . " VALUES(" . rawurldecode($db_values) . ")";
		mysqli_query($idbh,$isql);
		if(mysqli_errno($idbh) != 0 || mysqli_affected_rows($idbh) == 0) {
			$res = mysqli_error($idbh);
		}
		mysqli_close($idbh);
		return $res;
	}
	
	function db_delete($db_table,$db_key,$db_value)
	{
		$res = 1;
		$idbh = $this->db_connect();
		
		$isql = "DELETE FROM " . $this->dbtoken . $db_table . " WHERE " . $db_key . " = '" . $db_value . "' LIMIT 1";
		mysqli_query($idbh,$isql);
		if(mysqli_errno($idbh) != 0 || mysqli_affected_rows($idbh) == 0)
		{
			$res = 0;
		}
		mysqli_close($idbh);
		return $res;
	}
	
	function db_num_rows($db_table,$db_key,$db_where)
	{
		$res = true;
		$idbh = $this->db_connect();
		
		$isql = "SELECT COUNT(" . $db_key . ") AS row_count FROM " . $this->dbtoken . $db_table . " WHERE " . $db_where;
		$ierg = mysqli_query($idbh,$isql);
		if(mysqli_errno($idbh) != 0)
		{
			$res = false;
		}
		else
		{
			$c = mysqli_fetch_assoc($ierg);
			$res = $c['row_count'];
			mysqli_free_result($ierg);
		}
		mysqli_close($idbh);
		return $res;
	}
	
	function conv_date( $datum, $dir )
	{
		if( $dir == "E")
		{
			$ret_dat = substr( $datum, 6, 4 ) . "-" . substr( $datum, 3, 2 ) . "-" . substr( $datum, 0, 2 );
		}
		else
		{
			$ret_dat = substr($datum, 8, 2 ) . "." . substr( $datum, 5, 2 ) . "." . substr( $datum, 0, 4 );
		}
		return $ret_dat;
	}
	


	
	function set_new_password($usermail, $password)
	{
		$pdbh = $this->db_connect();
		
		$sql = "UPDATE " . $this->dbtoken . "customer SET cusPassword = '".$password."' WHERE cusEMail = '".$usermail."'";
		$perg = mysqli_query($pdbh,$sql);
		if(mysqli_errno($pdbh) == 0)
		{
			return true;
		}
		else
		{
			die(mysqli_error($pdbh) . "<br />" . $sql);
			return false;
		}
		mysqli_close($pdbh);
	}
	
	function show_shopcomments($cusId)
	{
		$aMyComment = array();
		$pdbh = $this->db_connect();
		
		$psql = "SELECT * FROM " . $this->dbtoken . "shopcomments WHERE itcoCusId = '" . $cusId . "' AND itcoVisible = 'Y' ORDER BY itcoIdNo ASC";
		$perg = mysqli_query($pdbh,$psql);
		if(mysqli_errno($pdbh) == 0)
		{
			while($p = mysqli_fetch_assoc($perg))
			{
				/*array_push($aMyComment,array("itcoIdNo" => $p['itcoIdNo'],
												  "itcoRating" => $p['itcoRating'],
												  "itcoSubject" => $p['itcoSubject'],
												  "itcoBody" => $p['itcoBody'],
												  "itcoDate" => $p['itcoDate']));*/
				$aMyComment[] = array("itcoIdNo" => $p['itcoIdNo'],
											 "itcoRating" => $p['itcoRating'],
											 "itcoSubject" => $p['itcoSubject'],
											 "itcoBody" => $p['itcoBody'],
											 "itcoDate" => $p['itcoDate']);
			}
			mysqli_free_result($perg);
		}
		mysqli_close($pdbh);
		return $aMyComment; 
	}
	
	function get_customerdata($cusEMail)
	{
		$cusData = array();
		$pdbh = $this->db_connect();
		
		$sql = "SELECT * FROM " . $this->dbtoken . "customer WHERE cusEMail = '" . $cusEMail . "' LIMIT 1";
		$perg = mysqli_query($pdbh,$sql);
		if(mysqli_errno($pdbh) == 0)
		{
			$p = mysqli_fetch_assoc($perg);
			/*array_push($cusData,$p);*/
			$cusData[] = $p;
			return $cusData;
		}	
	}
	
	function get_registered_langs()
	{
		$aLangs = array();
		$regurl = $this->shopurl;
		if(substr($regurl,-1) == '/')
		{
			$regurl = substr($regurl, 0, -1);
		}
		$ldbh = $this->db_connect();
		
		$lsql = "SELECT shoplng, cntlng, shopurl FROM " . $this->dbtoken . "generalinfo WHERE shopurl LIKE '" . $regurl . "%'";
		$lerg = mysqli_query($ldbh,$lsql);
		if(mysqli_errno($ldbh) == 0)
		{
			while($l = mysqli_fetch_assoc($lerg))
			{
				if(substr($l['shopurl'],-1) == '/')
				{
					$shopurl = substr($l['shopurl'], 0, -1);
				}
				else
				{
					$shopurl = $l['shopurl'];
				}
				if($regurl == $shopurl)
				{
					/*array_push($aLangs,array("slc" => $l['shoplng'], "cnt" => $l['cntlng']));*/
					$aLangs[] = array("slc" => $l['shoplng'], "cnt" => $l['cntlng']);
				}
			}
			mysqli_free_result($lerg);
		}
		mysqli_close($ldbh);
		return $aLangs;
	}
	
	function email_friendly($str)
	{
		//Wandelt Sonderzeichen in E-Mail-freundliches Quoted Printable um
		$erg = str_replace("�","Ae", $str);
		$erg = str_replace("�","Oe", $erg);
		$erg = str_replace("�","Ue", $erg);
		$erg = str_replace("�","ae", $erg);
		$erg = str_replace("�","oe", $erg);
		$erg = str_replace("�","ue", $erg);
		$erg = str_replace("�","ss", $erg);
		$erg = str_replace("&Auml;","Ae", $erg);
		$erg = str_replace("&Ouml;","Oe", $erg);
		$erg = str_replace("&Uuml;","Ue", $erg);
		$erg = str_replace("&auml;","ae", $erg);
		$erg = str_replace("&ouml;","oe", $erg);
		$erg = str_replace("&uuml;","ue", $erg);
		$erg = str_replace("&szlig;","ss", $erg);
		return $erg;
	}
	
	function place_ganalytics()
	{
		echo $this->ret_place_ganalytics();
		return;
	}
	
	function ret_place_ganalytics()
	{
		$gac = '';
		if($this->get_setting('cbGoogleanalytics_Checked') == 'True')
		{
			$gac = $this->db_text_ret('settingmemo|SettingMemo|SettingName|memoGoogleanalytics');
		}
		return $gac;
	}
	
	function place_etracker()
	{
		echo $this->ret_place_etracker();
		return;
	}
	
	function ret_place_etracker()
	{
		$etr = '';
		if($this->get_setting('cbEtracker_Checked') == 'True')
		{
			$etr = $this->db_text_ret('settingmemo|SettingMemo|SettingName|memoEtracker');
		}
		return $etr;
	}
	
	function is_marked_for_comparison($itemId)
	{
		if(isset($_SESSION['aitems_compare']) && count($_SESSION['aitems_compare']) > 0)
		{
			if(array_search( $itemId, $_SESSION['aitems_compare'], true) !== false)
			{
				return true;
			}
		}
		return false;
	}
	
	function get_value_by_idx($table, $index_field, $index, $result_field, $fieldLng, $fieldCnt)
	{
		$gvdbh = $this->db_connect();
		
		$text = '';
		$language = '';
		if($fieldLng != '')
		{
			$language = ' ' . $fieldLng . ' = "' . $this->lngID . '" AND';
		}
		$country = '';
		if($fieldCnt != '')
		{
			$country = ' ' . $fieldCnt . ' = "' . $this->cntID . '" AND';
		}
		$ffsql = 'SELECT ' . $result_field . ' FROM ' . $table . ' WHERE' . $language . $country . ' ' . $index_field . ' = "' . $index . '" LIMIT 1';
		$fferg = mysqli_query($gvdbh,$ffsql);
		if(mysqli_errno($gvdbh) == 0)
		{
			if(mysqli_num_rows($fferg) > 0)
			{
				$ff = mysqli_fetch_assoc($fferg);
				$text = $ff[$result_field];
				@mysqli_free_result($fferg);
			}
		}
		else
		{
			die(mysqli_error($gvdbh) . ":<br />" . $ffsql);
		}
		mysqli_close($gvdbh);
		return $text;
	}
	
	function get_shipmpaym($name,$isShip)
	{
		$res = false;
		$spdbh = $this->db_connect();
		
		if($isShip == 1)
		{
			$table = $this->dbtoken . 'deliverylanguage';
			$lookupfield = 'ShippingName';
			$countfield = 'SortId';
		}
		else
		{
			$table = $this->dbtoken . 'paymentlanguage';
			$lookupfield = 'Text1';
			$countfield = 'SortId';
		}
		$spsql = "SELECT COUNT(" . $countfield . ") AS anz FROM " . $table . 
					" WHERE " . $lookupfield . " LIKE '%" . $name . "%'" .
					" AND LanguageId = '" . $this->lngID . "'";
		$sperg = mysqli_query($spdbh,$spsql);
		if(mysqli_errno($spdbh) == 0)
		{
			$sp = mysqli_fetch_assoc($sperg);
			if($sp['anz'] > 0)
			{
				$res = true;
			}
			@mysqli_free_result($sperg);
		}
		mysqli_close($spdbh);
		return $res;
	}
	
	function has_childgroups($pgid)
	{
		$lres = false;
		$spdbh = $this->db_connect();
		
		$sql = "SELECT COUNT(ObjectCount) as anz FROM " . $this->dbtoken . "productgroups WHERE Parent = '" . $pgid . "'";
		$erg = mysqli_query($spdbh,$sql);
		if(mysqli_errno($spdbh) == 0)
		{
			if(mysqli_num_rows($erg) > 0)
			{
				$c = mysqli_fetch_assoc($erg);
				if($c['anz'] > 0)
				{
					$lres = true;
				}
			}
			@mysqli_free_result($erg);
		}
		mysqli_close($spdbh);
		return $lres;
	}
	
	function get_groupitems_by_group($groupID)
	{
		$aGRItems = array();
		$spdbh = $this->db_connect();
		
		$sql = "SELECT g.ItemID, d.itemItemPage AS ItemPage ".
               "FROM " . $this->dbtoken . "items2group g ".
               "LEFT JOIN " . $this->dbtoken . "itemdata d ON d.itemItemId = g.ItemID ".
               "WHERE g.ProductGroup = '" . $groupID . "' AND d.itemIsActive = 'Y' AND d.itemIsVariant = 'N' ORDER BY g.OrderIDX ASC";
		$erg = mysqli_query($spdbh,$sql);
		if(mysqli_errno($spdbh) == 0)
		{
			if(mysqli_num_rows($erg) > 0)
			{
				while($c = mysqli_fetch_assoc($erg))
				{
					/*array_push($aGRItems,array("ItemID" => $c['ItemID'], "ItemPage" => $c['ItemPage']));*/
					$aGRItems[] = array("ItemID" => $c['ItemID'], "ItemPage" => $c['ItemPage']);
				}
			}
			@mysqli_free_result($erg);
		}
		mysqli_close($spdbh);
		return $aGRItems;
	}
	
	function item_mustbeordered($fInStock)
	{
		//Pr�ft, ob der Lagerbestand im Bereich 0 .. ? der Verf�gbarkeiten liegt
		//In diesem Bereich wird der Artikel vom H�ndler bestellt und das Verf�gbarkeits-
		//Anfrage-Formular wird gezeigt
		//R�ckgabe: ID des Bereiches (avaId) oder -1
		$iRes = -1;
		$stdbh = $this->db_connect();
		
		$sql = "SELECT avaId, avaMaxQty FROM " . $this->dbtoken . "availability WHERE avaMinQty = '0'";
		$erg = mysqli_query($stdbh,$sql);
		if(mysqli_errno($stdbh) == 0)
		{
			if(mysqli_num_rows($erg) > 0)
			{
				$s = mysqli_fetch_assoc($erg);
				if($fInStock <= $s['avaMaxQty'])
				{
					$iRes = $s['avaId'];
				}
			}
			@mysqli_free_result($erg);
		}
		mysqli_close($stdbh);
		return $iRes;
	}
	
	function get_av_rating($itemNo)
	{
		$aRat = array();
		$ratdbh = $this->db_connect();
		
		$sql = "SELECT AVG(itcoRating) AS schnitt, COUNT(itcoIdNo) AS menge FROM " . $this->dbtoken . "itemcomments WHERE itcoItemNumber = '" . $itemNo . "' AND itcoVisible ='Y'";
		$erg = mysqli_query($ratdbh,$sql);
		if(mysqli_errno($ratdbh) == 0)
		{
			if(mysqli_num_rows($erg) > 0)
			{
				$r = mysqli_fetch_assoc($erg);
				if($r['schnitt'] == '')
				{
					$schnitt = 0;
				}
				else
				{
					$schnitt = round($r['schnitt'],0);
				}
				$aRat[] = array("schnitt" => $schnitt, "menge" => $r['menge']);
			}
		}
		mysqli_close($ratdbh);
		return $aRat;
	}

	function get_bestseller()
	{
		$aBest = array();
		$count = $this->get_setting_s('setBestsellerCount');
		$dbh = $this->db_connect();
		
		$osql = "SELECT op.ordpItemId, op.ordpPrice, op.ordpItemDesc, o.ordCurrency, SUM(op.ordpQty) AS qty " .
				 "FROM ".$this->dbtoken."orderpos op, ".$this->dbtoken."order o, ".$this->dbtoken."itemdata itm " .
				 "WHERE o.ordIdNo = op.ordpOrdIdNo " .
				 "AND itm.itemItemNumber=op.ordpItemId " .
				 "AND op.ordpItemId <> '000000' AND op.ordpItemId <> '' " .
				 "GROUP BY op.ordpItemId " .
				 "ORDER BY qty DESC LIMIT 0,".$count;
		$oerg = mysqli_query($dbh,$osql);
		if(mysqli_num_rows($oerg) > 0)
		{
			while($o = mysqli_fetch_assoc($oerg))
			{
				$aBest[] = "'" . $o['ordpItemId'] . "'";
			}
		}
		return $aBest;
	}
	
	function item_is_bestseller($itemNumber)
	{
		//TS TODO: Beschleunigungs-Potential!!!!
		//Statt ALLE Bestseller zu laden und dann im Ergebnis den Artikel zu suchen,
		//kann man auch eine sinnvolle Abfrage formulieren. Das wäre ein eine gute Anwendung
		//für eine User-Function
		$lres = false;
		$aBest = $this->get_bestseller();
		$bestmax = count($aBest);
		if($bestmax > 0)
		{
			for($b = 0; $b < $bestmax; $b++) {
				$val = str_replace("'","",$aBest[$b]);
				if($itemNumber == $val) {
					$lres = true;
					break;
				}
			}
		}
		return $lres;
	}
	
	function get_url_array($cUrl)
	{
		$aErg = array();
		$aHelp1 = explode('&',$cUrl);
		for($c = 0; $c < count($aHelp1); $c++)
		{
			$aHelp2 = explode('=',$aHelp1[$c]);
			$aErg[$aHelp2[0]] = $aHelp2[1];
		}
		return $aErg;
	}
	
	function get_itemnumber($itemid)
	{
		$itemnr = '';
		$idbh = $this->db_connect();
		$isql = "SELECT itemItemNumber FROM " . $this->dbtoken . "itemdata WHERE itemItemId = '" . $itemid . "'";
		$ierg = mysqli_query($idbh,$isql);
		if(mysqli_errno($idbh) == 0)
		{
			if(mysqli_num_rows($ierg) > 0)
			{
				$i = mysqli_fetch_assoc($ierg);
				$itemnr = $i['itemItemNumber'];
			}
		}
		mysqli_close($idbh);
		return $itemnr;
	}
	
	function get_itemId($itemNo)
	{
		$itemid = '';
		$idbh = $this->db_connect();
		$isql = "SELECT itemItemId FROM " . $this->dbtoken . "itemdata WHERE itemItemNo = '" . $itemNo . "'";
		$ierg = mysqli_query($idbh,$isql);
		if(mysqli_errno($idbh) == 0)
		{
			if(mysqli_num_rows($ierg) > 0)
			{
				$i = mysqli_fetch_assoc($ierg);
				$itemid = $i['itemItemId'];
			}
		}
		mysqli_close($idbh);
		return $itemid;
	}
	
	function chk_edition($gssbInfo,$gssbEdition)
	{
		$res = false;
		$dinfo = base64_decode($gssbInfo);
		$dedit = intval(base64_decode($gssbEdition));
		$kinfo = intval(substr($dinfo,16,2));
		
		//TS 09.01.2017: Im Demo-Modus nicht die Lizenznummer gegenpr�fen
		if($this->demo === 0) {
			if(($dedit === $kinfo) && ($this->demo === 0)) {
				$res = $dedit;
			} else {
				$res = false;//die("Licencing problem detected!!! Please contact the support!!!");
			}
		} else {
			$res = $dedit;
		}
		return $res;
	}
	
	function get_pglanguageinfo($pgID,$lang)
	{
		$apglang = array();
		
		$pgdbh = $this->db_connect();
		if($lang == '') {
			$pgsql = "SELECT * FROM " . $this->dbtoken . "productgrouplanguage WHERE PgCount = '" . $pgID . "'";
		} else {
			$pgsql = "SELECT * FROM " . $this->dbtoken . "productgrouplanguage WHERE PgCount = '" . $pgID . "' AND LanguageId = '" . $lang . "'";
		}
		$pgerg = mysqli_query($pgdbh,$pgsql);
		if(mysqli_errno($pgdbh) == 0)
		{
			if(mysqli_num_rows($pgerg) > 0)
			{
				while($p = mysqli_fetch_assoc($pgerg)) {
					$apglang[] = array("productgroup" => $p['ProductGroup'],
											 "template" => $p['TemplateFile'],
											 "image" => $p['ImageFile'],
											 "metadesc" => $p['MetaDescription'],
											 "metakeyw" => $p['MetaKeywords'],
											 "xmlextra" => $p['XmlExtra'],
											 "grouphint" => $p['GroupHint'],
											 "permalink" => $p['Permalink'],
											 "published" => $p['Published']);
				}
				mysqli_free_result($pgerg);
			}
		}
		mysqli_close($pgdbh);
		return $apglang;
	}
	
	function get_slccnts() {
		$this->aslcs = array();
		$this->acnts = array();
		$sldbh = $this->db_connect();
		$slsql = "SELECT shoplng, cntlng FROM " . $this->dbtoken . "generalinfo group by shoplng";
		
		$slerg = mysqli_query($sldbh,$slsql);
		if(mysqli_errno($sldbh) == 0)
		{
			if(mysqli_num_rows($slerg) > 0)
			{
				while($s = mysqli_fetch_assoc($slerg)) {
					$this->aslcs[] = $s['shoplng'];
					$this->acnts[] = $s['cntlng'];
				}
				mysqli_free_result($slerg);
			}
		}
		mysqli_close($sldbh);
		return;
	}
	
	function aprove_iban( $iban ) {
		$iban = str_replace( ' ', '', $iban );
		$iban1 = substr( $iban,4 )
			. strval( ord( $iban[0] )-55 )
			. strval( ord( $iban[1] )-55 )
			. substr( $iban, 2, 2 );

		$rest=0;
		for ( $pos=0; $pos<strlen($iban1); $pos+=7 ) {
			$part = strval($rest) . substr($iban1,$pos,7);
			$rest = intval($part) % 97;
		}
		$pz = sprintf("%02d", 98-$rest);

		if ( substr($iban,2,2)=='00')
			return substr_replace( $iban, $pz, 2, 2 );
		else
		return ($rest==1) ? true : false;
	}
	
	function get_file_icon($filename) {
		$icon = 'icon_unknown.png';
		$aExt = array(
			"jpg" => "icon_graphics.png",
			"jpeg" => "icon_graphics.png",
			"gif" => "icon_graphics.png",
			"png" => "icon_graphics.png",
			"bmp" => "icon_graphics.png",
			"pcx" => "icon_graphics.png",
			"tif" => "icon_graphics.png",
			"tiff" => "icon_graphics.png",
			"iff" => "icon_graphics.png",
			"psd" => "icon_graphics.png",
			"cdr" => "icon_graphics.png",
			"xcf" => "icon_graphics.png",
			"eps" => "icon_vector.png",
			"svg" => "icon_vector.png",
			"ai" => "icon_vector.png",
			"dwg" => "icon_vector.png",
			"dxf" => "icon_vector.png",
			"exe" => "icon_executable.png",
			"dll" => "icon_executable.png",
			"mp3" => "icon_audio.png",
			"wav" => "icon_audio.png",
			"wma" => "icon_audio.png",
			"ogg" => "icon_audio.png",
			"zip" => "icon_archive.png",
			"gzip" => "icon_archive.png",
			"tar" => "icon_archive.png",
			"rar" => "icon_archive.png",
			"lha" => "icon_archive.png",
			"lhz" => "icon_archive.png",
			"gz" => "icon_archive.png",
			"bz2" => "icon_archive.png",
			"7z" => "icon_archive.png",
			"doc" => "icon_document.png",
			"docx" => "icon_document.png",
			"dot" => "icon_document.png",
			"dotx" => "icon_document.png",
			"odt" => "icon_document.png",
			"rtf" => "icon_document.png",
			"ott" => "icon_document.png",
			"xls" => "icon_spreadsheet.png",
			"xlsx" => "icon_spreadsheet.png",
			"ods" => "icon_spreadsheet.png",
			"ots" => "icon_spreadsheet.png",
			"xlt" => "icon_spreadsheet.png",
			"xltx" => "icon_spreadsheet.png",
			"csv" => "icon_spreadsheet.png",
			"ppt" => "icon_presentation.png",
			"pptx" => "icon_presentation.png",
			"pot" => "icon_presentation.png",
			"potx" => "icon_presentation.png",
			"odp" => "icon_presentation.png",
			"otp" => "icon_presentation.png",
			"mdb" => "icon_database.png",
			"odb" => "icon_database.png",
			"dbf" => "icon_database.png",
			"db" => "icon_database.png",
			"accdb" => "icon_database.png",
			"pdf" => "icon_pdf.png",
			"xps" => "icon_pdf.png",
			"ps" => "icon_pdf.png",
			"vcf" => "icon_contact.png",
			"ics" => "icon_calendar.png",
			"ttf" => "icon_font.png",
			"eot" => "icon_font.png",
			"woff" => "icon_font.png",
			"otf" => "icon_font.png",
			"pfb" => "icon_font.png",
			"afm" => "icon_font.png",
			"txt" => "icon_plain_text.png",
			"xml" => "icon_plain_text.png",
			"mov" => "icon_video.png",
			"avi" => "icon_video.png",
			"mkv" => "icon_video.png",
			"divx" => "icon_video.png",
			"mpg" => "icon_video.png",
			"mpeg" => "icon_video.png",
			"wmv" => "icon_video.png",
			"mp4" => "icon_video.png"
			);
		if($pos = strrpos($filename,".")) {
			$ext = substr($filename, $pos + 1);
			if(array_key_exists ($ext, $aExt)) {
				$icon = $aExt[$ext];
			}
		}
		
		return $icon;
	}
	
	function get_billingperiodfromid($bpid,$lPer = false,$lPlural = false,$lAdj = false) {
		$s = '';
		if($lPlural) { $s = 's'; }
		$period = '';
		if(!$lAdj) {
			switch($bpid) {
				case "1":
					$lngtime = "LangTagRentalDay" . $s;
					break;
				case "2":
					$lngtime = "LangTagRentalWeek" . $s;
					break;
				case "3":
					$lngtime = "LangTagRentalMonth" . $s;
					break;
				case "4":
					$lngtime = "LangTagRentalYear" . $s;
					break;
				default:
					$lngtime = "LangTagUnknownValue" . $s;
					break;
			}
		} else {
			switch($bpid) {
				case "1":
					$lngtime = "LangTagRentalDaily";
					break;
				case "2":
					$lngtime = "LangTagRentalWeekly";
					break;
				case "3":
					$lngtime = "LangTagRentalMonthly";
					break;
				case "4":
					$lngtime = "LangTagRentalYearly";
					break;
				default:
					$lngtime = "LangTagUnknownValue";
					break;
			}
		}
		$period = $this->get_lngtext($lngtime);
		if(!$lAdj && !$lPlural && $lPer) {
			$period = $this->get_lngtext('LangTagPerSomething') . " " . $period;
		}
		return $period;
	}
	
	function no_umlauts($str) {
		$str = str_replace('�', '**ae**', $str);
		$str = str_replace('�', '**Ae**', $str);
		$str = str_replace('�', '**oe**', $str);
		$str = str_replace('�', '**Oe**', $str);
		$str = str_replace('�', '**ue**', $str);
		$str = str_replace('�', '**Ue**', $str);
		$str = str_replace('�', '**ss**', $str);
		return $str;
	}
	
	function hidebankinfo($str) {
		$res = '';
		if(strlen($str) == 22) {
			//IBAN
			$firstPos = 2;
			$hideLen = 16;
		} else {
			//BIC
			$firstPos = -4;
			$hideLen = 4;
		}
		
		if($firstPos > 0) {
			$res = substr_replace($str, str_pad('*',$hideLen,'*'), $firstPos, $hideLen);
		} else {
			$res = substr_replace($str, str_pad('*',$hideLen,'*'), $firstPos);
		}
		
		return $res;
	}
	
	function getVatRateFromKey($fVatKey) {
		$vatrate = 0;
		$vdbh = $this->db_connect();
		  $vsql = "SELECT VATRate FROM " . $this->dbtoken . "salestax WHERE SalesTaxNo = " . intval($fVatKey) . " AND CountryId = '" . $this->cntID . "' LIMIT 1";
        //$vsql = "SELECT SettingValue  FROM " . $this->dbtoken . "setting WHERE SettingName = 'lbVatRates_item".intval($fVatKey)."' AND CountryId = '" . $this->cntID . "' LIMIT 1";
		$verg = mysqli_query($vdbh,$vsql);
		if(mysqli_errno($vdbh) == 0)
		{
			$vat = mysqli_fetch_assoc($verg);
			  $vatrate = $vat['VATRate'];
           //$vatrate = intval($vat['SettingValue']);
		}
		mysqli_close($vdbh);
		return $vatrate;
	}
	
	function calcItemDiscount($fPrice,$fDiscount,$iDecimals = 2) {
		$fNewPrice = $fPrice;
		//$fNewPrice = round($fPrice / ((100+$fDiscount)/100),$iDecimals);
		$fNewPrice = $fPrice - round((($fPrice/100)*$fDiscount),$iDecimals);
		return $fNewPrice;
	}
	
	function load_settings() {
		$dbh = $this->db_connect();
		$sql = "SELECT SettingName, SettingValue FROM " . $this->dbtoken . "setting WHERE LanguageId = '" . $this->lngID . "' AND CountryId = '" . $this->cntID . "' ORDER BY SettingName ASC";
		$erg = mysqli_query($dbh,$sql);
		if(mysqli_errno($dbh) == 0) {
			$_SESSION['sb_settings'] = array();
			while($z = mysqli_fetch_assoc($erg)) {
				$key = $z['SettingName'];
				$val = base64_encode($z['SettingValue']);
				$_SESSION['sb_settings'][$key] = $val;
			}
		}
		mysqli_free_result($erg);
		mysqli_close($dbh);
	}

	function gs_file_get_contents($filename){
		$entry = str_replace('template/','',$filename);
		if (isset($_SESSION['template'][$entry])){
			return $_SESSION['template'][$entry];
		} else {
			$_SESSION['template'][$entry] = file_get_contents($filename);
			return $_SESSION['template'][$entry];
		}
	}
	
	function getBasketTotals(&$totalnetto,&$totalbrutto,&$taxtotal) {
		$maxbasket = count($_SESSION['basket']);
		$usenetto = ($this->get_setting('cbNetPrice_Checked') == 'True') ? 1 : 0;
		for($r = 0; $r < $maxbasket; $r++) {
			//Trialartikel nicht �bermitteln
			/*if($_SESSION['basket'][$r]['art_isttrialitem'] == 1) {
				continue;
			}*/
			if($usenetto == 1) {
				$netto = round($_SESSION['basket'][$r]['art_price'],2);
				$brutto = round($_SESSION['basket'][$r]['art_price'] * (1 + ($_SESSION['basket'][$r]['art_vatrate'] / 100)),2);
				$tax = $brutto - $netto;
			} else {
				$netto = round($_SESSION['basket'][$r]['art_price'] / (1 + ($_SESSION['basket'][$r]['art_vatrate'] / 100)),2);
				$brutto = round($_SESSION['basket'][$r]['art_price'],2);
				$tax = round($_SESSION['basket'][$r]['art_price'],2) - $netto;
			}
			$totalnetto += ($netto * $_SESSION['basket'][$r]['art_count']);
			$totalbrutto += ($brutto * $_SESSION['basket'][$r]['art_count']);
			$taxtotal += round(($tax * $_SESSION['basket'][$r]['art_count']),2);
		}
	}
	
	function getPPCreditCardType($cardName) {
		switch($cardName) {
			case 'Visa':
				return 'VISA';
				break;
			case 'Eurocard':
				return 'MASTERCARD';
				break;
			case 'American Express':
				return 'AMEX';
				break;
			case 'Diners':
				return '';
				break;
			default:
				return '';
				break;
		}
	}
	
	function date2mysql($cDate) {
		if($cDate != '') {
			return implode('-',array_reverse(explode('.',$cDate)));
		}
	}
	
	function date2paypal($cDate) {
		$engDate = $this->date2mysql($cDate);
		return $engDate.'T00:00:00Z';
	}
	
	function checkKeyValid($cModName) {
		$savedHash = trim($this->get_setting('mod'.$cModName.'_Text'));
		$modIDX = -1;
		if($savedHash != '') {
			$modIDX = $this->getModuleIDX($cModName);
			if($modIDX >= 0) {
				//$modOne = base64_decode($this->gssbinfo);
				$modOne = $this->shopurl;
				$modTwo = trim($this->gssbmodules[$modIDX]['ID']);
				if($modTwo != '') {
					$calcHash = strtoupper(md5($modOne.$modTwo));
					if($calcHash === $savedHash) {
						return true;
					} else {
						return false;
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	function getModuleIDX($cModName) {
		$modIDX = -1;
		$modCount = count($this->gssbmodules);
		for($m = 0; $m < $modCount; $m++) {
			if($this->gssbmodules[$m]['Name'] == $cModName) {
				$modIDX = $m;
				break;
			}
		}
		return $modIDX;
	}

}
?>