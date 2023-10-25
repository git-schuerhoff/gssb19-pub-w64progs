<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$itemcompare = '';
$aComp = $_SESSION['aitems_compare'];
$iCols = count($aComp);

$allprecol = '';
$all_rmitems = '';
$all_itemboxes = '';
$all_itemdescr = '';
$all_itemno = '';
$all_itemmanu = '';
/*For GSShop-Template only!!!!*/
$all_itemfeatures = '';

$addtocartsmall = $this->gs_file_get_contents($this->absurl . 'template/ic_addtocartsmall.html');
$gotodetailhtml = $this->gs_file_get_contents($this->absurl . 'template/ic_gotodetail.html');

if($iCols > 0)
{
	$precol = $this->gs_file_get_contents('template/ic_colwidth.html');
	$cwidth = 100 / $iCols;
	$precol = str_replace('{GSSE_INCL_ICCOLWIDTH}',$cwidth,$precol);
	
	$rmitem = $this->gs_file_get_contents('template/ic_removeitem.html');
	$itembox = $this->gs_file_get_contents('template/ic_itembox.html');
	$itemval = $this->gs_file_get_contents('template/ic_values.html');
	
	$valclass = 'std';
	for($pc = 0; $pc < $iCols; $pc++)
	{
		/*Additional Iteminfo in $_SESSION['aitem']*/
		$this->get_item($aComp[$pc]['idx']);
		/*Predefined Cols*/
		$allprecol .= $precol;
		
		$detailurl = $this->absurl . 'index.php?page=detail&amp;item=' . $aComp[$pc]['idx'] . '&amp;d=' . $_SESSION['aitem']['itemItemPage'];
		
		/*Remove Links*/
		$cur_rmitem = $rmitem;
		//$cur_rmitem = str_replace('',,$cur_rmitem);
		$cur_rmitem = str_replace('{GSSE_LANG_LangTagRemoveFromCompare}',$this->get_lngtext('LangTagRemoveFromCompare'),$cur_rmitem);
		$cur_rmitem = str_replace('{GSSE_INCL_ICITEMID}',$aComp[$pc]['idx'],$cur_rmitem);
		$cur_rmitem = str_replace('{GSSE_INCL_ICITEMNAME}',$aComp[$pc]['name'],$cur_rmitem);
		$all_rmitems .= $cur_rmitem;
		
		/*Itemboxes*/
		$cur_itembox = $itembox;
		$aPrices = array();
		$aImgs = array();
		$aPrices = $this->get_prices($aComp[$pc]['idx']);
		$action = 0;
		if($_SESSION['aitem']['itemIsAction'] == 'Y')
		{
			$action = $this->chk_action($aComp[$pc]['idx'],$aPrices);
		}
		$saleperiod = '';//New Template
		if($action == 1)
		{
			if($aPrices['actshowperiod'] == 'Y')
			{
				$saleperiod = $aPrices['actbegindate'] . " - " . $aPrices['actenddate'];
			}
		}
		$aImgs = $this->get_itempics($aComp[$pc]['idx']);
		//$cur_itembox = str_replace('',,$cur_itembox);
		$cur_itembox = str_replace('{GSSE_INCL_ICITEMNAME}',$aComp[$pc]['name'],$cur_itembox);
		//A TS 04.12.2014 Online-Image
		if(strpos($aImgs[0]['ImageName'],"http") === false && strpos($aImgs[0]['ImageName'],"://") === false) {
			if($aImgs[0]['ImageName'] != '' && file_exists('images/medium/' . $aImgs[0]['ImageName'])) {
				$cur_itembox = str_replace('{GSSE_INCL_ICITEMIMAGE}','images/medium/' . $aImgs[0]['ImageName'],$cur_itembox);
			} else {
				$cur_itembox = str_replace('{GSSE_INCL_ICITEMIMAGE}',$this->absurl . 'template/images/no_pic_mid.png',$cur_itembox);
			}
		} else {
			$cur_itembox = str_replace('{GSSE_INCL_ICITEMIMAGE}',$aImgs[0]['ImageName'],$cur_itembox);
		}
		$cur_itembox = str_replace('{GSSE_INCL_SALEPERIOD}',$saleperiod,$cur_itembox);
		$oldprice = '';
		$priceclass = 'price';
		if($aPrices['oldprice'] > 0 && $action == 0)
		{
			$priceclass = 'special-price';
			$oldprice = $this->gs_file_get_contents('template/oldpricenew.html');
			$oldprice = str_replace('{GSSE_INCL_ITEMOLDPRICENEW}',$this->get_currency($aPrices['oldprice'],0,'.'),$oldprice);
		}
		if($action == 1 && $aPrices['actshownormal'] == 'Y' && $aPrices['actnormprice'] != 0)
		{
			$priceclass = 'special-price';
			$oldprice = $this->gs_file_get_contents('template/oldpricenew.html');
			$oldprice = str_replace('{GSSE_INCL_ITEMOLDPRICENEW}',$this->get_currency(str_replace(',','.',$aPrices['actnormprice']),0,'.'),$oldprice);
		}
		$cur_itembox = str_replace('{GSSE_INCL_OLDPRICENEW}',$oldprice,$cur_itembox);
		$cur_itembox = str_replace('{GSSE_INCL_ITEMPRICE}',$this->get_currency($aPrices['price'],0,'.'),$cur_itembox);
		$cur_itembox = str_replace('{GSSE_INCL_PRICECLASS}',$priceclass,$cur_itembox);
		$cur_itembox = str_replace('{GSSE_LANG_LangTagAddToBasket}',$this->get_lngtext('LangTagAddToBasket'),$cur_itembox);
		$cur_itembox = str_replace('{GSSE_INCL_ITEMID}',$aComp[$pc]['idx'],$cur_itembox);
		
		/*Begin Exalyser specific*/
		$cur_itembox = str_replace('{GSSE_LANG_LangTagExaPricePerMonthShort}',$this->get_lngtext('LangTagExaPricePerMonthShort'),$cur_itembox);
		/*Begin Exalyser specific*/
		
		/*Add-To-Cart-Button*/
		/*Add-to-cart*/
		$addtocart = '';
		if($_SESSION['aitem']['itemIsCatalogFlg'] == 'N' && $_SESSION['aitem']['itemIsTextInput'] == 'N' && $_SESSION['aitem']['itemAttribute1'] == '' && $_SESSION['aitem']['itemAttribute2'] == '' && $_SESSION['aitem']['itemAttribute3'] == '')
		{
			$addtocart = $addtocartsmall;
			$addtocart = str_replace('{GSSE_LANG_LangTagAddToBasket}',$this->get_lngtext('LangTagAddToBasket'),$addtocart);
			$addtocart = str_replace('{GSSE_INCL_ITEMID}',$aComp[$pc]['idx'],$addtocart);
		}
		else
		{
			if($_SESSION['aitem']['itemHasDetail'] == 'Y')
			{
				$addtocart = $gotodetailhtml;
				$addtocart = str_replace('{GSSE_LANG_LangTagViewDetails}',$this->get_lngtext('LangTagViewDetails'),$addtocart);
				$addtocart = str_replace('{GSSE_INCL_LINKURL}',$detailurl,$addtocart);
			}
		}
		$cur_itembox = str_replace('{GSSE_INCL_ADDTOCARTSMALL}',$addtocart,$cur_itembox);
		
		/*Wishlist & Notepad*/
		$wishlist = '';
		$notepad = '';
		if($this->phpactive())
		{
			if(isset($_SESSION['login']))
			{
				if($_SESSION['login']['ok'])
				{
					$cid = $_SESSION['login']['cusIdNo'];
					$itemNo = $_SESSION['aitem']['itemItemNumber'];
					$date = date("Ymd");
					/*Wishlist*/
					if($this->get_setting('cbUsePhpWishlist_Checked') == 'True')
					{
						$wishlist = $this->gs_file_get_contents($this->absurl . 'template/item_towishlist.html');
						$wishlist = str_replace('{GSSE_INCL_ITEMNO}',$itemNo,$wishlist);
						$wishlist = str_replace('{GSSE_INCL_CUSID}',$cid,$wishlist);
						$wishlist = str_replace('{GSSE_INCL_DATE}',$date,$wishlist);
						$wishlist = str_replace('{GSSE_LANG_LangTagMoveToWishList}',$this->get_lngtext('LangTagMoveToWishList'),$wishlist);
					}
					
					/*Notepad*/
					if($this->get_setting('cbUsePhpNotepad_Checked') == 'True')
					{
						$notepad = $this->gs_file_get_contents($this->absurl . 'template/item_tonotepad.html');
						$notepad = str_replace('{GSSE_INCL_ITEMNO}',$itemNo,$notepad);
						$notepad = str_replace('{GSSE_INCL_CUSID}',$cid,$notepad);
						$notepad = str_replace('{GSSE_INCL_DATE}',$date,$notepad);
						$notepad = str_replace('{GSSE_LANG_LangTagNote}',$this->get_lngtext('LangTagNote'),$notepad);
					}
				}
			}
		}
		$cur_itembox = str_replace('{GSSE_INCL_WISHLIST}',$wishlist,$cur_itembox);
		$cur_itembox = str_replace('{GSSE_INCL_NOTEPAD}',$notepad,$cur_itembox);
		$all_itemboxes .= $cur_itembox;
		
		/*Itemvalues Description*/
		$cur_itemdescr = $itemval;
		//$cur_itemdescr = str_replace('',,$cur_itemdescr);
		$cur_itemdescr = str_replace('{GSSE_INCL_ICATTRCLASS}',$valclass,$cur_itemdescr);
		$cur_itemdescr = str_replace('{GSSE_INCL_ICATTRVALUE}',$_SESSION['aitem']['itemItemText'],$cur_itemdescr);
		$all_itemdescr .= $cur_itemdescr;
		
		/*Itemvalues Features*/
		/*This is a future function and works only with GSShop-Template for the moment*/
		$cur_itemfeature = $itemval;
		$cur_itemfeature = str_replace('{GSSE_INCL_ICATTRCLASS}',$valclass,$cur_itemfeature);
		$cur_itemfeature = str_replace('{GSSE_INCL_ICATTRVALUE}',$_SESSION['aitem']['itemHtmlText1'],$cur_itemfeature);
		$all_itemfeatures .= $cur_itemfeature;
		
		/*Itemvalues ItemNumber*/
		$cur_itemno = $itemval;
		//$cur_itemdescr = str_replace('',,$cur_itemdescr);
		$cur_itemno = str_replace('{GSSE_INCL_ICATTRCLASS}',$valclass,$cur_itemno);
		$cur_itemno = str_replace('{GSSE_INCL_ICATTRVALUE}',$_SESSION['aitem']['itemItemNumber'],$cur_itemno);
		$all_itemno .= $cur_itemno;
		
		/*Itemvalues ItemManufacturer*/
		$cur_itemmanu = $itemval;
		//$cur_itemdescr = str_replace('',,$cur_itemdescr);
		$cur_itemmanu = str_replace('{GSSE_INCL_ICATTRCLASS}',$valclass,$cur_itemmanu);
		$cur_itemmanu = str_replace('{GSSE_INCL_ICATTRVALUE}',$_SESSION['aitem']['itemManufacturer'],$cur_itemmanu);
		$all_itemmanu .= $cur_itemmanu;
		
	}
}
$this->content = str_replace('{GSSE_INCL_ICPREDEFCOLS}',$allprecol,$this->content);
$this->content = str_replace('{GSSE_INCL_ICREMOVEITEM}',$all_rmitems,$this->content);
$this->content = str_replace('{GSSE_INCL_ICITEMBOXES}',$all_itemboxes,$this->content);
$this->content = str_replace('{GSSE_INCL_ICITEMDESCR}',$all_itemdescr,$this->content);
$this->content = str_replace('{GSSE_INCL_ICITEMNO}',$all_itemno,$this->content);
$this->content = str_replace('{GSSE_INCL_ICITEMMANU}',$all_itemmanu,$this->content);
/*For GSShop-Template only*/
$this->content = str_replace('{GSSE_INCL_ICITEMFEATURES}',$all_itemfeatures,$this->content);

$this->content = str_replace($tag, $itemcompare, $this->content);
?>
