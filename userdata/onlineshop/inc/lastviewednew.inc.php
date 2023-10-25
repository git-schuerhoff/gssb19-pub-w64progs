<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$aPrices = array();
if($this->phpactive() === true)
{
	$res = $this->get_setting('cbUsePhpLastViewed_Checked');
	if($res == 'True')
	{
		$tmplFile = "lastviewed.html";
		$lasthtml = $this->gs_file_get_contents('template/' . $tmplFile);
		$lastbox = $this->gs_file_get_contents('template/itemslastview_box.html');
		$sessid = session_id();
		//$sessid = 'f6hdkj7qpoasg1inqo38m7vj80';
		$dbh = $this->db_connect();
		$lsql = "SELECT hisData1 FROM ".$this->dbtoken."history WHERE hisSessionId = '".$sessid."'";
		$lerg = mysqli_query($dbh,$lsql);
		$l = mysqli_fetch_assoc($lerg);
		$ahis = unserialize($l['hisData1']);
		if($ahis != "") 
		{
			$lasthtml = str_replace('{GSSE_LANG_LangTagLastViewedItem}',$this->get_lngtext('LangTagLastViewedItem'),$lasthtml);
			$ahis = array_reverse($ahis);
			$hismax2 = count($ahis);
			for($lv = 0; $lv < $hismax2; $lv++)
			{
				if($lv == 0)
				{
					$ItemNumbers = '"' . $ahis[$lv][0] . '"';
				}
				else
				{
					$ItemNumbers .= ',"' . $ahis[$lv][0] . '"';
				}
			}
			$sql = "SELECT itemItemId, itemItemNumber, itemItemDescription, itemSmallImageFile, " .
					 "(SELECT prcPrice FROM " . $this->dbtoken . "price WHERE " . $this->dbtoken . "price.prcItemCount = " . $this->dbtoken . "itemdata.itemItemId AND " . $this->dbtoken . "price.prcQuantityFrom = '0' LIMIT 1) AS ItemPrice, " .
					 "itemIsNewItem, itemHasDetail, itemItemPage, itemIsCatalogFlg, " .
					 "itemIsVariant, itemAttribute1, itemAttribute2, itemAttribute3, itemIsTextInput, " .
					 "itemInStockQuantity, itemAvailabilityId, itemDetailText1, " .
					 "itemCheckAge, itemMustAge, itemIsAction, itemisDecimal " .
					 "FROM " . $this->dbtoken . "itemdata WHERE itemIsActive = 'Y' AND itemItemNumber IN (" . $ItemNumbers . ") AND itemLanguageId = '" . $this->lngID . "'";
			$erg = mysqli_query($dbh,$sql);
			$allitems = '';
			if(mysqli_errno($dbh) == 0)
			{
				$num = mysqli_num_rows($erg);
				if($num > 0)
				{
					$p = 0;
					while($z = mysqli_fetch_assoc($erg))
					{
						$p++;
						if($p == 1)
						{
							$fol = ' first';
						}
						else
						{
							if($p == $num)
							{
								$fol = ' last';
							}
							else
							{
								$fol = '';
							}
							
						}
						$aPrices = $this->get_prices($z['itemItemId']);
						$itemPrice = $this->get_currency($aPrices['price'],0,'.');
						$saleperiod = '';
						$action = 'N';
						if($z['itemIsAction'] == 'Y')
						{
							$action = $this->chk_action($z['itemItemId'],$aPrices);
						}
						$oldprice = '';
						$priceclass = 'price';
						if($aPrices['oldprice'] > 0 && $action != 'Y')
						{
							$priceclass = 'special-price';
							$oldpriceclass = 'old-price';
							$oldprice = $this->gs_file_get_contents('template/oldpricenew.html');
							$oldprice = str_replace('{GSSE_INCL_OLDPRICECLASS}',$oldpriceclass,$oldprice);
							$oldprice = str_replace('{GSSE_INCL_ITEMOLDPRICENEW}',$this->get_currency($aPrices['oldprice'],0,'.'),$oldprice);
						}
						
						if(($action == 1) or ($action == 'Y'))
						{
							$itemPrice = $this->get_currency(str_replace(',','.',$aPrices['actprice']),0,'.');
							if($aPrices['actshowperiod'] == 'Y')
							{
								$saleperiod = substr($aPrices['actbegindate'],8,2).'.'.substr($aPrices['actbegindate'],5,2).'.'.substr($aPrices['actbegindate'],0,4) . " - " . substr($aPrices['actenddate'],8,2).'.'.substr($aPrices['actenddate'],5,2).'.'.substr($aPrices['actenddate'],0,4);
							}
							
							if($aPrices['actshownormal'] == 'Y')
							{
								$oldprice_val = $aPrices['price'];
								$priceclass = 'special-price';
								$oldpriceclass = 'old-price';
								$oldprice = $this->gs_file_get_contents('template/oldpricenew.html');
								$oldprice = str_replace('{GSSE_INCL_OLDPRICECLASS}',$oldpriceclass,$oldprice);
								$oldprice = str_replace('{GSSE_INCL_ITEMOLDPRICENEW}',$this->get_currency($oldprice_val,0,'.'),$oldprice);
							}
						}
						
						if($action == 'N')
						{
							if(count($aPrices['abulk']) > 0)
							{
								$itemPrice = $this->get_lngtext('LangTagFromNew') . ' ' . $this->get_currency($aPrices['abulk'][0][1],0,'.');
							}
						}
						
						$detailurl = 'index.php?page=detail&amp;item=' . $z['itemItemId'] . '&amp;d=' . $z['itemItemPage'];
						/*A TS 09.12.2014: Permalink verwenden, wenn verfügbar*/
						if($this->edition == 13) {
							if($this->get_setting('cbUsePermalinks_Checked') == 'True') {
								if($z['itemItemPage'] != '') {
									$detailurl = $z['itemItemPage'];
								}
							}
						}
						
						$cur_box = $lastbox;
						//$cur_box = str_replace('',,$cur_box);
						$cur_box = str_replace('{GSSE_INCL_FIRSTLAST}',$fol,$cur_box);
						/* Bild online oder lokal?*/
						/*Bild aus gallerie*/
						$aImgs = $this->get_itempics($z['itemItemId']);
						$imgsrc = $aImgs[0]['ImageName'];
						if($imgsrc != "") {
							if(strpos($imgsrc,"http") === false && strpos($imgsrc,"://") === false) {
								if(file_exists('images/medium/' . $imgsrc)) {
									$imgsrc = 'images/medium/' . $imgsrc;
								} else {
									$imgsrc = 'template/images/no_pic_sma.png';
								}
							}
						} else {
							$imgsrc = 'template/images/no_pic_sma.png';
						}
						$cur_box = str_replace('{GSSE_INCL_ITEMIMG}',$imgsrc,$cur_box);
						$cur_box = str_replace('{GSSE_INCL_ITEMNAME}',$z['itemItemDescription'],$cur_box);
						//New Template link to detail
						if($z['itemHasDetail'] == 'Y')
						{
							$cur_box = str_replace('{GSSE_SURL_}',$this->absurl,$cur_box);
							$cur_box = str_replace('{GSSE_INCL_ITEMURL}',$detailurl,$cur_box);
						}
						$cur_box = str_replace('{GSSE_INCL_SALEPERIOD}',$saleperiod,$cur_box);
						$cur_box = str_replace('{GSSE_INCL_OLDPRICENEW}',$oldprice,$cur_box);
						$cur_box = str_replace('{GSSE_INCL_ITEMNUMBER}',$z['itemItemNumber'],$cur_box);
						//Itemprice
						$cur_box = str_replace('{GSSE_INCL_ITEMPRICE}',$itemPrice,$cur_box);
						//Text priceinformation
                        if($_SESSION['aitem']['itemIsCatalogFlg'] == 'N' && $_SESSION['aitem']['itemIsTextHasNoPrice'] == 'N')
                        {
                            $pinfo = $this->get_setting('edPriceInformation_Text');
                            $cur_box = str_replace('{GSSE_INCL_ITEMPRICEINFO}',$pinfo,$cur_box);
                        }
                        else
                        {
                            $cur_box = str_replace('{GSSE_INCL_ITEMPRICEINFO}','',$cur_box);
                        }
						/*Begin Exalyser specific*/
						$cur_box = str_replace('{GSSE_LANG_LangTagExaPricePerMonth}',$this->get_lngtext('LangTagExaPricePerMonth'),$cur_box);
						/*Begin Exalyser specific*/
						
						
						//New template
						$cur_box = str_replace('{GSSE_INCL_PRICECLASS}',$priceclass,$cur_box);
						
						$allitems .= $cur_box;
					}
				}
			}
			$lasthtml = str_replace('{GSSE_INCL_ITEMSLASTVIEWLINES}',$allitems,$lasthtml);
			@mysqli_free_result($erg);
			$this->content = str_replace($tag, $lasthtml, $this->content);
		}
		else
		{
			$this->content = str_replace($tag, '', $this->content);
		}
		mysqli_free_result($lerg);
	}
	else
	{
		$this->content = str_replace($tag, '', $this->content);
	}
}
else
{
	$this->content = str_replace($tag, '', $this->content);
}
