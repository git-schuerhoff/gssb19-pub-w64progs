<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$tmplFile = "detailbox.html";
$detailhtml = $this->gs_file_get_contents('template/' . $tmplFile);
$aDBTags = $this->get_tags_ret($detailhtml);
$detailhtml = $this->parse_texts($aDBTags,$detailhtml);

if(file_exists("dynsb/class/class.shoplog.php"))
{
	if(!in_array("shoplog",get_declared_classes()))
	{
		require_once("dynsb/class/class.shoplog.php");
	}
	require_once("dynsb/include/functions.inc.php");
	$sl = new shoplog();
}
else
{
	die("Class shoplog missing!");
}
if(!isset($_SESSION['aitem']['itemItemId'])){
	$this->get_item($_GET['idx']);
}
$aPrices = $this->get_prices($_SESSION['aitem']['itemItemId']);
$_SESSION['aitem']['aprices'] = $aPrices;

$self = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1));
$sl->logItemHistory(session_id(), $_SESSION['aitem']['itemItemNumber'], 
						  base64_encode($_SESSION['aitem']['itemItemDescription']), 
						  $self, 
						  $_SESSION['aitem']['itemSmallImageFile'], 
						  $aPrices['price'], 
						  $_SESSION['aitem']['itemWeight'], 'N');

/*Begin Soon here*/
$soonimg = ($_SESSION['aitem']['itemSoonHereFlag'] == 'Y') ? $this->inc_image('', 'template/images/soonhere_deu.gif', 'Soon here', 'Soon here') : '';
$soontxt = ($_SESSION['aitem']['itemSoonHereFlag'] == 'Y') ? $_SESSION['aitem']['itemSoonHereText'] : '';
$detailhtml = str_replace('{GSSE_DET_SOONHEREIMG}',$soonimg,$detailhtml);
$detailhtml = str_replace('{GSSE_DET_SOONHERETXT}',$soontxt,$detailhtml);
/*End Soon here*/

/*Begin Action*/
$action = 0;
if($_SESSION['aitem']['itemIsAction'] == 'Y')
{
	$action = $this->chk_action($_SESSION['aitem']['itemItemId'],$aPrices);
}
$actionhtml = '';
if($action == 1)
{
	$actionhtml = $this->gs_file_get_contents('template/rabattaktion.html');
	$actionhtml = str_replace('{GSSE_INCL_CURLANG}',$this->lngID,$actionhtml);
	$actiontext = '';
	if($aPrices['actshowperiod'] == 'Y')
	{
		$actiontext = $aPrices['actbegindate'] . " - " . $aPrices['actenddate'];
	}
	$actionhtml = str_replace('{GSSE_INCL_ACTIONTEXT}',$actiontext,$actionhtml);
}
$detailhtml = str_replace('{GSSE_DET_ACTION}',$actionhtml,$detailhtml);
/*End Action*/

/*Begin New*/
$new = ($_SESSION['aitem']['itemIsNewItem'] == 'Y') ? $this->inc_image('', 'template/images/neu.gif', 'New', 'New') : '';
$detailhtml = str_replace('{GSSE_DET_NEWITEM}',$new,$detailhtml);
/*End New*/

/*Begin Video*/
$videohtml = $_SESSION['aitem']['itemVideoLink'];
$detailhtml = str_replace('{GSSE_DET_VIDEO}',$videohtml,$detailhtml);
/*End Video*/

/*Begin Big Image*/
$aPics = $this->get_itempics($_SESSION['aitem']['itemItemId']);
$bigimg = '';
if(count($aPics) > 0)
{
	$bigimg = $this->gs_file_get_contents('template/image_big.html');
	//$bigimg = str_replace('',,$bigimg);
	$bigimg = str_replace('{GSSE_INCL_IMGCLASS}','big_img',$bigimg);
	//A TS 06.10.2014: Wenn im Filename des Bildes http vorkommt, dann diese URL verwenden,
	//ansonsten den relativen Pfad zum Bild
	if(strpos($aPics[0]['ImageName'], 'http') === false)
	{
		$imgname = 'images/big/' . $aPics[0]['ImageName'];
	}
	else
	{
		$imgname = $aPics[0]['ImageName'];
	}
	$bigimg = str_replace('{GSSE_INCL_IMGSRC}',$imgname,$bigimg);
	$bigimg = str_replace('{GSSE_INCL_IMGALT}',$aPics[0]['ImageDesc'],$bigimg);
	$bigimg = str_replace('{GSSE_INCL_IMGTITLE}',$aPics[0]['ImageDesc'],$bigimg);
	$bigimg = str_replace('{GSSE_INCL_BIGIMGMOUSEOVER}','',$bigimg);
	$bigimg = str_replace('{GSSE_INCL_BIGIMGMOUSEOUT}','',$bigimg);
	if($aPics[0]['ImageLink'] == '')
	{
		$bigimg = str_replace('{GSSE_INCL_BIGIMGMOUSECLICK}','',$bigimg);
	}
	else
	{
		$bigimg = str_replace('{GSSE_INCL_BIGIMGMOUSECLICK}','window.open("' . $aPics[0]['ImageLink'] . '");',$bigimg);
	}
	$zoom = '';
	if($this->get_setting('cbImageZoom_Checked') == 'True')
	{
		$this->content = str_replace('{GSSE_ZOOM}','<script type="text/javascript" language="javascript">
	$(function () {
		$("#id_big_image").imageLens({ lensSize: 300 });
	});	
</script>',$this->content);
	} else {
		$this->content = str_replace('{GSSE_ZOOM}','',$this->content);
	}
	$bigimg = str_replace('{GSSE_INCL_ZOOM}',$zoom,$bigimg);
}
$detailhtml = str_replace('{GSSE_DET_BIGIMG}',$bigimg,$detailhtml);
/*End Big Image*/

/*Begin Gallery*/
$gallery = '';
if(count($aPics) > 0)
{
	$gallery = $this->gs_file_get_contents('template/gallery_outer.html');
	$galthumb = $this->gs_file_get_contents('template/gallerythumbnail.html');
	$thumbs = '';
	foreach($aPics as $pic)
	{
		$cur_img = $galthumb;
		//A TS 06.10.2014: Wenn im Filename des Bildes http vorkommt, dann diese URL verwenden,
		//ansonsten den relativen Pfad zum Bild
		if(strpos($pic['ImageName'], 'http') === false)
		{
			if(file_exists('images/small/'.$pic['ImageName']))
			{
				$cur_img = str_replace('{GSSE_INCL_IMGCLASS}','gal_thumb',$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGSRC}','images/small/' . $pic['ImageName'],$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGALT}',$pic['ImageDescr'],$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGTITLE}',$pic['ImageDescr'],$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGMOUSEOVER}','show_bigimage(\'' . $pic['ImageName'] . '\');',$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGMOUSEOUT}','',$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGMOUSECLICK}','',$cur_img);
			}
			elseif (file_exists('images/medium/'.$pic['ImageName']))
			{
				$cur_img = str_replace('{GSSE_INCL_IMGCLASS}','gal_thumb',$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGSRC}','images/medium/' . $pic['ImageName'],$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGALT}',$pic['ImageDescr'],$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGTITLE}',$pic['ImageDescr'],$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGMOUSEOVER}','show_bigimage(\'' . $pic['ImageName'] . '\');',$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGMOUSEOUT}','',$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGMOUSECLICK}','',$cur_img);
			}
			elseif (file_exists('images/big/'.$pic['ImageName']))
			{
				$cur_img = str_replace('{GSSE_INCL_IMGCLASS}','gal_thumb',$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGSRC}','images/big/' . $pic['ImageName'],$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGALT}',$pic['ImageDescr'],$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGTITLE}',$pic['ImageDescr'],$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGMOUSEOVER}','show_bigimage(\'' . $pic['ImageName'] . '\');',$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGMOUSEOUT}','',$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGMOUSECLICK}','',$cur_img);
			}
			else
			{
				$cur_img = '';	
			}
		}
		else
		{
			$cur_img = str_replace('{GSSE_INCL_IMGCLASS}','gal_thumb',$cur_img);
			$cur_img = str_replace('{GSSE_INCL_IMGSRC}',$pic['ImageName'],$cur_img);
			$cur_img = str_replace('{GSSE_INCL_IMGALT}',$pic['ImageDescr'],$cur_img);
			$cur_img = str_replace('{GSSE_INCL_IMGTITLE}',$pic['ImageDescr'],$cur_img);
			$cur_img = str_replace('{GSSE_INCL_IMGMOUSEOVER}','show_bigimage(\'' . $pic['ImageName'] . '\');',$cur_img);
			$cur_img = str_replace('{GSSE_INCL_IMGMOUSEOUT}','',$cur_img);
			$cur_img = str_replace('{GSSE_INCL_IMGMOUSECLICK}','',$cur_img);
		}
		
		$thumbs .= $cur_img;
	}
	$gallery = str_replace('{GSSE_INCL_GALLERYPICS}',$thumbs,$gallery);
}
$detailhtml = str_replace('{GSSE_DET_GALLERY}',$gallery,$detailhtml);
/*End Gallery*/

/*Begin Bundles*/
$bundleshtml = '';
$aBundle = $this->get_bundles($_SESSION['aitem']['itemItemNumber']);
if(count($aBundle) > 0)
{
	$bundleshtml = $this->gs_file_get_contents('template/bundles.html');
	$bundleshtml = str_replace('{GSSE_LANG_LangTagBundleConsistsOf}',$this->get_lngtext('LangTagBundleConsistsOf'),$bundleshtml);
	$bundleshtml = str_replace('{GSSE_LANG_LangTagBundleTableHead2}',$this->get_lngtext('LangTagBundleTableHead2'),$bundleshtml);
	$bundleshtml = str_replace('{GSSE_LANG_LangTagBundleTableHead3}',$this->get_lngtext('LangTagBundleTableHead3'),$bundleshtml);
	$bundlesitemhtml = $this->gs_file_get_contents('template/bundlesitem.html');
	$bundelsitems = '';
	$bumax = count($aBundle);
	for($b = 0; $b < $bumax; $b++)
	{
		$cur_bitem = $bundlesitemhtml;
		$burl = 'index.php?page=detail&amp;item=' . $aBundle[$b]['itemID'];
		$cur_bitem = str_replace('{GSSE_INCL_BUNDLEITEMURL}',$burl,$cur_bitem);
		$cur_bitem = str_replace('{GSSE_INCL_BUNDLEITEMPIC}',$aBundle[$b]['itemPic'],$cur_bitem);
		$bamount = $aBundle[$b]['itemAmount'] . " x";
		$cur_bitem = str_replace('{GSSE_INCL_BUNDLEITEMTITLE}',$aBundle[$b]['itemDescription'],$cur_bitem);
		$cur_bitem = str_replace('{GSSE_INCL_BUNDLEITEMAMOUNT}',$bamount,$cur_bitem);
		$cur_bitem = str_replace('{GSSE_INCL_BUNDLEITEMNUMBER}',$aBundle[$b]['itemNumber'],$cur_bitem);
		$bundleitems .= $cur_bitem;
	}
	$bundleshtml = str_replace('{GSSE_INCL_BUNDLESITEMS}',$bundleitems,$bundleshtml);
}
$detailhtml = str_replace('{GSSE_DET_BUNDLES}',$bundleshtml,$detailhtml);
/*End Bundles*/

/*Begin Item-Title & No.*/
$detailhtml = str_replace('{GSSE_DET_ITEMTITLE}',$_SESSION['aitem']['itemItemDescription'],$detailhtml);
$detailhtml = str_replace('{GSSE_DET_LNGITMNO}', $this->get_lngtext('LangTagItemNumber'),$detailhtml);
$detailhtml = str_replace('{GSSE_DET_ITMNO}',$_SESSION['aitem']['itemItemNumber'],$detailhtml);
/*End Item-Title & No.*/

/*Begin Variants*/
$varhtml = '';
$aVarMainItem = $this->get_varmainitem($_SESSION['aitem']['itemItemNumber']);
$detdbh = $this->db_connect();
$vsql = "SELECT varItemCount, varGroupCount, varItemNumber, ShowAsDropDown, " .
		  "(SELECT itemItemDescription FROM " .
		  $this->dbtoken . "itemdata WHERE " . $this->dbtoken . "itemdata.itemItemNumber = " . 
		  $this->dbtoken . "item_to_variant.varItemNumber AND " .
		  $this->dbtoken . "itemdata.itemLanguageId = '" . $this->lngID . "') AS itemName, " .
		  "(SELECT ImageName FROM " .
		  $this->dbtoken . "gallery WHERE " . $this->dbtoken . "gallery.itemId = " . 
		  $this->dbtoken . "item_to_variant.varItemCount AND " .
		  $this->dbtoken . "gallery.imageOrder=1) AS itemPic, " .
		  "(SELECT itemItemId FROM " .
		  $this->dbtoken . "itemdata WHERE " . $this->dbtoken . "itemdata.itemItemNumber = " . 
		  $this->dbtoken . "item_to_variant.varItemNumber AND " .
		  $this->dbtoken . "itemdata.itemLanguageId = '" . $this->lngID . "') AS itemId, " .
		  "(SELECT itemVariantDescription FROM " .
		  $this->dbtoken . "itemdata WHERE " . $this->dbtoken . "itemdata.itemItemNumber = " . 
		  $this->dbtoken . "item_to_variant.varItemNumber AND " .
		  $this->dbtoken . "itemdata.itemLanguageId = '" . $this->lngID . "') AS VarName " .
		  "FROM " . $this->dbtoken . "item_to_variant WHERE " .
		  "varVariantGroup = '" . $aVarMainItem['itemNumber'] . "' ORDER BY varVariantIdNo ASC";
$verg = mysqli_query($detdbh,$vsql);
if(mysqli_errno($detdbh) == 0)
{
	if(mysqli_num_rows($verg) > 0)
	{
		$aVars = array();
		$aImages = $this->get_itempics($_SESSION['aitem']['itemItemId']);
		$showDropDown = 0;
		$cvarname = ($_SESSION['aitem']['itemVariantDescription'] != "") ? $_SESSION['aitem']['itemVariantDescription'] : $_SESSION['aitem']['itemItemDescription'];
		if($_SESSION['aitem']['itemItemNumber'] == $aVarMainItem['itemNumber'])
		{
			/*array_push($aVars,array("ItemNumber" => $_SESSION['aitem']['itemItemNumber'],"ItemName" => $cvarname, "ItemPic" => $_SESSION['aitem']['itemSmallImageFile'], "ItemId" => $_SESSION['aitem']['itemItemId']));*/
			$aVars[] = array("ItemNumber" => $_SESSION['aitem']['itemItemNumber'],"ItemName" => $cvarname, "ItemPic" => $aImages[0]['ImageName'], "ItemId" => $_SESSION['aitem']['itemItemId']);
		}
		else
		{
			/*array_push($aVars,array("ItemNumber" => $aVarMainItem['itemNumber'],"ItemName" => $aVarMainItem['itemName'], "ItemPic" => $aVarMainItem['itemPic'], "ItemId" => $aVarMainItem['itemId']));*/
			$aVars[] = array("ItemNumber" => $aVarMainItem['itemNumber'],"ItemName" => $aVarMainItem['itemName'], "ItemPic" => $aVarMainItem['itemPic'], "ItemId" => $aVarMainItem['itemId']);
		}
		while($v = mysqli_fetch_assoc($verg))
		{
			$cvarname = ($v['VarName'] != "") ? $v['VarName'] : $v['itemName'];
			/*array_push($aVars,array("ItemNumber" => $v['varItemNumber'],
											"ItemName" => $cvarname,
											"ItemPic" => $v['itemPic'],
											"ItemId" => $v['itemId']));*/
			$aVars[] = array("ItemNumber" => $v['varItemNumber'],
											"ItemName" => $cvarname,
											"ItemPic" => $v['itemPic'],
											"ItemId" => $v['itemId']);
			if($v['ShowAsDropDown'] == 'Y')
			{
				$showDropDown = 1;
			}
		}
		mysqli_free_result($verg);
		$varhtml = $this->gs_file_get_contents('template/variants_outer.html');
		$varhtml = str_replace('{GSSE_LANG_VARTXT}',$this->get_lngtext('LangTagTextVariantsTitle'),$varhtml);
		if($showDropDown == 0)
		{
			$varitemouter = $this->gs_file_get_contents('template/variants_itemsouter.html');
			$varitem = $this->gs_file_get_contents('template/variants_item.html');
		}
		else
		{
			$varitemouter = $this->gs_file_get_contents('template/variants_itemsouter_dd.html');
			$varitem = $this->gs_file_get_contents('template/variants_item_dd.html');
		}
		
		$varallitems = '';
		$varmax = count($aVars);
		for($cv = 0; $cv < $varmax; $cv++)
		{
			if($aVars[$cv]['ItemId'] == $_SESSION['aitem']['itemItemId'])
			{
				$sel = " selected";
			}
			else
			{
				$sel = "";
			}
			$cur_var_item = $varitem;
			$cur_var_item = str_replace('{GSSE_VAR_VARID}',$cv,$cur_var_item);
			$cur_var_item = str_replace('{GSSE_VAR_VARNAME}',$aVars[$cv]['ItemName'],$cur_var_item);
			$cur_var_item = str_replace('{GSSE_VAR_VARPIC}',$aVars[$cv]['ItemPic'],$cur_var_item);
			$cur_var_item = str_replace('{GSSE_VAR_VARITEMID}',$aVars[$cv]['ItemId'],$cur_var_item);
			$cur_var_item = str_replace('{GSSE_VAR_SELECTED}',$sel,$cur_var_item);
			$varallitems .= $cur_var_item;
		}
		$varitemouter = str_replace('{GSSE_DET_VARITEMS}', $varallitems, $varitemouter);
		$varhtml = str_replace('{GSSE_DET_VARITEMSOUTER}',$varitemouter,$varhtml);
	}
}
else
{
	$varhtml = mysqli_error($detdbh) . ":<br />" . $vsql;
}
$detailhtml = str_replace('{GSSE_DET_VARIANTS}',$varhtml,$detailhtml);
/*End Variants*/

/*Begin Attributes*/
//Attribute zusammenstellen
$aAttr = array();
if($_SESSION['aitem']['itemAttribute1'] != '') $aAttr[] = $_SESSION['aitem']['itemAttribute1'];
if($_SESSION['aitem']['itemAttribute2'] != '') $aAttr[] = $_SESSION['aitem']['itemAttribute2'];
if($_SESSION['aitem']['itemAttribute3'] != '') $aAttr[] = $_SESSION['aitem']['itemAttribute3'];
include_once('inc/attributes.inc.php');
$detailhtml = str_replace('{GSSE_DET_ATTR}',$attrhtml,$detailhtml);
/*End Attributes*/

/*Begin Prices*/
//oldprice
$oldprice = '';
if($aPrices['oldprice'] > 0 && $action == 0)
{
	$pcontent = $this->get_lngtext('LangTagOldPrice') . ': ' . $this->get_currency($aPrices['oldprice'],0,'.');
	$oldprice = $this->inc_pcontent('oldprice',$pcontent);
}

if($action == 1 && $aPrices['actshownormal'] == 'Y' && $aPrices['actnormprice'] != 0)
{
	$pcontent = $this->get_lngtext('LangTagOldPrice') . ': ' . $this->get_currency(str_replace(',','.',$aPrices['actnormprice']),0,'.');
	$oldprice = $this->inc_pcontent('oldprice',$pcontent);
}
$detailhtml = str_replace('{GSSE_INCL_ITEMOLDPRICE}',$oldprice,$detailhtml);
//Referenceprice
if($aPrices['referenceprice'] > 0 && $_SESSION['aitem']['itemIsCatalogFlg'] == 'N' && $_SESSION['aitem']['itemIsTextHasNoPrice'] == 'N')
{
	$pcontent = $this->get_lngtext('LangTagReferencePrice') . ': ' . $aPrices['referencequantity'] . ' ' . $aPrices['referenceunit'] . ' = ' . $this->get_currency($aPrices['referenceprice'],0,'.');
	$detailhtml = str_replace('{GSSE_INCL_ITEMREFPRICE}',$this->inc_pcontent('referenceprice',$pcontent),$detailhtml);
}
else
{
	$detailhtml = str_replace('{GSSE_INCL_ITEMREFPRICE}','',$detailhtml);
}
//Bulkprices
if(count($aPrices['abulk']) > 0 && $_SESSION['aitem']['itemIsCatalogFlg'] == 'N' && $_SESSION['aitem']['itemIsTextHasNoPrice'] == 'N' && $action == 0)
{
	$bulkprices = '';
	$bulkmax = count($aPrices['abulk']);
	for($s = 0; $s < $bulkmax; $s++)
	{
		$pcontent = $this->get_lngtext('LangTagFromNew') . ' ' .
						$aPrices['abulk'][$s][0] . ' ' .
						$this->get_lngtext('LangTagUnits') . ' ' .
						$this->get_currency($aPrices['abulk'][$s][1],0,'.');
		$bulkprices .= $this->inc_pcontent('',$pcontent);
	}
	$detailhtml = str_replace('{GSSE_INCL_ITEMBULKPRICE}',$bulkprices,$detailhtml);
}
else
{
	$detailhtml = str_replace('{GSSE_INCL_ITEMBULKPRICE}','',$detailhtml);
}
//Itemprice
if($_SESSION['aitem']['itemIsCatalogFlg'] == 'N' && $_SESSION['aitem']['itemIsTextHasNoPrice'] == 'N')
{
	$price = $this->get_currency($aPrices['price'],0,'.');
	if(isset($aPrices['isrental'])) {
		if($aPrices['isrental'] == 'Y') {
			if($aPrices['billingperiod'] != '0') {
				$price .= " " . $this->get_billingperiodfromid($aPrices['billingperiod'],true);
			}
		}
	}
	$detailhtml = str_replace('{GSSE_INCL_ITEMPRICE}',$price,$detailhtml);
}
else
{
	$detailhtml = str_replace('{GSSE_INCL_ITEMPRICE}','',$detailhtml);
}

//Add to cart button
if($_SESSION['aitem']['itemIsCatalogFlg'] == 'N' && $_SESSION['aitem']['itemIsTextHasNoPrice'] == 'N')
{
	$output = $this->gs_file_get_contents('template/addtocart.html');
	$output = str_replace('{GSSE_INCL_ITEMINDEX}',$_SESSION['aitem']['itemItemId'],$output);
	$output = str_replace('{GSSE_INCL_COUNTER}','1',$output);
	$output = str_replace('{GSSE_INCL_ISDECIMAL}',$_SESSION['aitem']['itemisDecimal'],$output);
}
else
{
	$output = $this->gs_file_get_contents('template/basketbox.html');
	$output = str_replace('{GSSE_INCL_BASKETBOX}',$this->get_lngtext('LangTagCatalogueItem'),$output);
}

$detailhtml = str_replace('{GSSE_INCL_ADDTOCARTBUTTON}', $output,$detailhtml);

//Text priceinformation
if($_SESSION['aitem']['itemIsCatalogFlg'] == 'N' && $_SESSION['aitem']['itemIsTextHasNoPrice'] == 'N')
{
	$pinfo = $this->get_setting('edPriceInformation_Text');
	$detailhtml = str_replace('{GSSE_INCL_ITEMPRICEINFO}',$pinfo,$detailhtml);
}
else
{
	$detailhtml = str_replace('{GSSE_INCL_ITEMPRICEINFO}','',$detailhtml);
}
/*End Prices*/

/*Begin Itemweight*/
$itemweighthtml = '';
if($_SESSION['aitem']['itemWeight'] > 0)
{
	$itemweighthtml = $this->gs_file_get_contents('template/itemweight.html');
	$itemweighthtml = str_replace('{GSSE_LANG_LangTagTextItemWeight}',$this->get_lngtext('LangTagTextItemWeight'),$itemweighthtml);
	$itemweighthtml = str_replace('{GSSE_INCL_WEIGHT}',$this->get_number_format($_SESSION['aitem']['itemWeight'],'.'),$itemweighthtml);
	$itemweighthtml = str_replace('{GSSE_INCL_WEIGHTUNIT}',$this->get_setting('edWeightUnit_Text'),$itemweighthtml);
}
$detailhtml = str_replace('{GSSE_INCL_ITEMWEIGHT}',$itemweighthtml,$detailhtml);
/*End Itemweight*/

/*Begin minimum age*/
$minimumagehtml = '';
if($_SESSION['aitem']['itemMustAge'] > 0)
{
	$minimumagehtml = $this->gs_file_get_contents('template/minimumage.html');
	$minimumagehtml = str_replace('{GSSE_LANG_LangTagMinimumAge}',$this->get_lngtext('LangTagMinimumAge'),$minimumagehtml);
	$minimumagehtml = str_replace('{GSSE_INCL_MINIMUMYEARS}',$_SESSION['aitem']['itemMustAge'],$minimumagehtml);
	$minimumagehtml = str_replace('{GSSE_LANG_LangTagYears}',$this->get_lngtext('LangTagYears'),$minimumagehtml);
}
$detailhtml = str_replace('{GSSE_INCL_MINIMUMAGE}',$minimumagehtml,$detailhtml);
/*Begin minimum age*/

/*Begin availability*/
if($_SESSION['aitem']['itemIsCatalogFlg'] == 'N' && $_SESSION['aitem']['itemIsTextHasNoPrice'] == 'N')
{
	$detailhtml = str_replace('{GSSE_INCL_AVAILABILITY}',$this->get_availability($_SESSION['aitem']['itemInStockQuantity'],$_SESSION['aitem']['itemAvailabilityId'],1),$detailhtml);
	/*Begin availabilitymailbox*/
	$avamailbox = '';
	if($this->phpactive() === true)
	{
		$res = $this->get_setting('cbUsePhpAvailability_Checked');
		$res2 = $this->get_setting('cbUsePhpAvailMail_Checked');
		if($res == 'True' && $res2 == 'True')
		{
			if(isset($_SESSION['aitem']['curAvaId']) && $_SESSION['aitem']['curAvaId'] == 3)
			{
				$avamailbox = $this->gs_file_get_contents('template/availmailbox.html');
				$aAVTags = $this->get_tags_ret($avamailbox);
				$avamailbox = $this->parse_texts($aAVTags,$avamailbox);
				$avamailbox = str_replace('{GSSE_INCL_ITEM}', $_SESSION['aitem']['itemItemNumber'] . ' ' . $_SESSION['aitem']['itemItemDescription'],$avamailbox);
			}
		}
	}
	$detailhtml = str_replace('{GSSE_INCL_AVAILMAILBOX}',$avamailbox,$detailhtml);
}
else
{
	$detailhtml = str_replace('{GSSE_INCL_AVAILABILITY}','',$detailhtml);
	$detailhtml = str_replace('{GSSE_INCL_AVAILMAILBOX}','',$detailhtml);
}
/*Begin availabilitymailbox*/
/*End availability*/

/*Begin itemtextfield*/
$itfhtml = '';
if($_SESSION['aitem']['itemIsTextInput'] == 'Y')
{
	$itfhtml = $this->gs_file_get_contents('template/itemtextfield.html');
	$itfhtml = str_replace('{GSSE_LANG_LangTagTextTextField}',$this->get_lngtext('LangTagTextTextField'),$itfhtml);
}
$detailhtml = str_replace('{GSSE_INCL_ITEMTEXTFIELD}',$itfhtml,$detailhtml);
/*End itemtextfield*/

/*Begin Item comparison*/
$itemcompare = '';
if($this->get_setting('cbArticleCompare_Checked') == 'True')
{
	$itemcompare = $this->gs_file_get_contents('template/item_comparison.html');
	$itemcompare = str_replace('{GSSE_LANG_LangTagArticleCompare}',$this->get_lngtext('LangTagArticleCompare'),$itemcompare);
	$itemcompare = str_replace('{GSSE_INCL_ICID}',$_SESSION['aitem']['itemItemId'],$itemcompare);
	$itemcompare = str_replace('{GSSE_INCL_ICCHECKED}',($this->is_marked_for_comparison($_SESSION['aitem']['itemItemId'])) ? 'checked="checked"' : '',$itemcompare);
}
$detailhtml = str_replace('{GSSE_INCL_COMPAREITEM}',$itemcompare,$detailhtml);
/*Begin Item comparison*/

/*Begin customertools*/
$dbglogin = 1;
$custoolshtml = '';
$custoolshtml = $this->gs_file_get_contents('template/custools.html');
$custoolsitems = '';
$custoolsitem = $this->gs_file_get_contents('template/custoolsitem.html');
if($this->phpactive())
{
	/*Begin loggedin-functions*/
	if($_SESSION['login']['ok'])
	{
		$cid = $_SESSION['login']['cusIdNo'];
		$itemNo = $_SESSION['aitem']['itemItemNumber'];
		$itemName = $_SESSION['aitem']['itemItemDescription'];
		$date = date("Ymd");
		/*Begin Notepad*/
		if($this->get_setting('cbUsePhpNotepad_Checked') == 'True')
		{
			
			$cur_custoolsitem = $custoolsitem;
			$cur_custoolsitem = str_replace('{GSSE_INCL_CUSTOOLNAME}','note',$cur_custoolsitem);
			$cur_custoolsitem = str_replace('{GSSE_LANG_CUSTOOLTITLE}',$this->get_lngtext('LangTagNote'),$cur_custoolsitem);
			$cusfunc = "insert_np('" . $itemNo . "','" . $cid . "','" . $date . "');";
			$cur_custoolsitem = str_replace('{GSSE_INCL_CUSTOOLFUNC}',$cusfunc,$cur_custoolsitem);
			$custoolsitems .= $cur_custoolsitem;
		}
		/*End Notepad*/
		/*Begin Wishlist*/
		if($this->get_setting('cbUsePhpWishlist_Checked') == 'True')
		{
			$cur_custoolsitem = $custoolsitem;
			$cur_custoolsitem = str_replace('{GSSE_INCL_CUSTOOLNAME}','wishlist',$cur_custoolsitem);
			$cur_custoolsitem = str_replace('{GSSE_LANG_CUSTOOLTITLE}',$this->get_lngtext('LangTagMoveToWishList'),$cur_custoolsitem);
			$cusfunc = "insert_wl('" . $itemNo . "','" . $cid . "','" . $date . "');";
			$cur_custoolsitem = str_replace('{GSSE_INCL_CUSTOOLFUNC}',$cusfunc,$cur_custoolsitem);
			$custoolsitems .= $cur_custoolsitem;
		}
		/*End Wishlist*/
		/*Begin Usercomments*/
		if($this->get_setting('cbUsePhpUsercomments_Checked') == 'True')
		{
			$itemImg = $_SESSION['aitem']['itemSmallImageFile'];
			$cur_custoolsitem = $custoolsitem;
			$show_comm = $this->db_text_ret('itemcomments_settings|itseVisDef|itseIdNo|Y');
			$cur_custoolsitem = str_replace('{GSSE_INCL_CUSTOOLNAME}','usercomments',$cur_custoolsitem);
			$cur_custoolsitem = str_replace('{GSSE_LANG_CUSTOOLTITLE}',$this->get_lngtext('LangTagTextUserCommentsAdd'),$cur_custoolsitem);
			$cusfunc = "self.location.href='index.php?page=gs_addcomment&show_comm=" . $show_comm . "';";
			$cur_custoolsitem = str_replace('{GSSE_INCL_CUSTOOLFUNC}',$cusfunc,$cur_custoolsitem);
			$custoolsitems .= $cur_custoolsitem;
		}
		/*End Usercomments*/
	}
}
/*End loggedin-functions*/
/*Begin customerfuncs without PHP-Extensions*/
/*Begin Findcheaper*/
if($this->get_setting('cbFindCheaper_Checked') == 'True')
{
	$cur_custoolsitem = $custoolsitem;
	$cur_custoolsitem = str_replace('{GSSE_INCL_CUSTOOLNAME}','findcheaper',$cur_custoolsitem);
	$cur_custoolsitem = str_replace('{GSSE_LANG_CUSTOOLTITLE}',$this->get_lngtext('LangTagFindCheaper'),$cur_custoolsitem);
	$cusfunc = "self.location.href='index.php?page=tell2'";
	$cur_custoolsitem = str_replace('{GSSE_INCL_CUSTOOLFUNC}',$cusfunc,$cur_custoolsitem);
	$custoolsitems .= $cur_custoolsitem;
}
/*End Findcheaper*/
/*Begin Tellafriend*/
if($this->get_setting('cbTellAFriend_Checked') == 'True')
{
	$cur_custoolsitem = $custoolsitem;
	$cur_custoolsitem = str_replace('{GSSE_INCL_CUSTOOLNAME}','tellafriend',$cur_custoolsitem);
	$cur_custoolsitem = str_replace('{GSSE_LANG_CUSTOOLTITLE}',$this->get_lngtext('LangTagTellAFriend'),$cur_custoolsitem);
	$cusfunc = "self.location.href='index.php?page=tell'";
	$cur_custoolsitem = str_replace('{GSSE_INCL_CUSTOOLFUNC}',$cusfunc,$cur_custoolsitem);
	$custoolsitems .= $cur_custoolsitem;
}
/*End Tellafriend*/
/*Begin Questonproduct*/
if($this->get_setting('cbQuestOnProduct_Checked') == 'True')
{
	$cur_custoolsitem = $custoolsitem;
	$cur_custoolsitem = str_replace('{GSSE_INCL_CUSTOOLNAME}','questonproduct',$cur_custoolsitem);
	$cur_custoolsitem = str_replace('{GSSE_LANG_CUSTOOLTITLE}',$this->get_lngtext('LangTagQuestToProduct'),$cur_custoolsitem);
	$cusfunc = "self.location.href='index.php?page=questonproduct'";
	$cur_custoolsitem = str_replace('{GSSE_INCL_CUSTOOLFUNC}',$cusfunc,$cur_custoolsitem);
	$custoolsitems .= $cur_custoolsitem;
}
/*End Questonproduct*/
/*Begin HasInquiry*/
if($_SESSION['aitem']['itemHasInquiry'] == 'Y')
{
	$cur_custoolsitem = $custoolsitem;
	$cur_custoolsitem = str_replace('{GSSE_INCL_CUSTOOLNAME}','inquiry',$cur_custoolsitem);
	$cur_custoolsitem = str_replace('{GSSE_LANG_CUSTOOLTITLE}',$this->get_lngtext('LangTagInquiry'),$cur_custoolsitem);
	$cusfunc = "self.location.href='index.php?page=inquiry'";
	$cur_custoolsitem = str_replace('{GSSE_INCL_CUSTOOLFUNC}',$cusfunc,$cur_custoolsitem);
	$custoolsitems .= $cur_custoolsitem;
}
/*End HasInquiry*/
/*Begin Go Back-Button*/
$gobackhtml = $this->gs_file_get_contents('template/input_goback.html');
$gobackhtml = str_replace('{GSSE_LANG_LangTagButtonBack}',$this->get_lngtext('LangTagButtonBack'),$gobackhtml);
$custoolsitems .= $gobackhtml;
/*End Go Back-Button*/
/*End customerfuncs without PHP-Extensions*/
/*Begin Socialnetwork Items*/
$cussocitems = '';
$detailurl = $this->shopurl . '/index.php?page=detail&amp;item=' . $_SESSION['aitem']['itemItemId'] . '&amp;d=' . $_SESSION['aitem']['itemItemPage'];
/*Begin Twitter*/
if($this->get_setting('cbWeb20Twitter_Checked') == 'True')
{
	$sochtml = $this->gs_file_get_contents('template/cussoctwitter.html');
	$sochtml = str_replace('{GSSE_INCL_DETAILURL}',$detailurl,$sochtml);
	$cussocitems .= $sochtml;
}
/*End Twitter*/

/*Begin Facebook*/
if($this->get_setting('cbWeb20Facebook_Checked') == 'True')
{
	$sochtml = $this->gs_file_get_contents('template/cussocfacebook.html');
	$sochtml = str_replace('{GSSE_INCL_DETAILURL}',$detailurl,$sochtml);
	$cussocitems .= $sochtml;
}
/*End Facebook*/

/*Begin StudiVZ*/
if($this->get_setting('cbWeb20StudiVZ_Checked') == 'True')
{
	$sochtml = $this->gs_file_get_contents('template/cussocstudivz.html');
	$sochtml = str_replace('{GSSE_INCL_DETAILURL}',$detailurl,$sochtml);
	$cussocitems .= $sochtml;
}
/*End StudiVZ*/

/*Begin Delicious*/
if($this->get_setting('cbWeb20Delicious_Checked') == 'True')
{
	$sochtml = $this->gs_file_get_contents('template/cussocdelicious.html');
	$sochtml = str_replace('{GSSE_INCL_DETAILURL}',$detailurl,$sochtml);
	$cussocitems .= $sochtml;
}
/*End Delicious*/

/*Begin MrWong*/
if($this->get_setting('cbWeb20MisterWrong_Checked') == 'True')
{
	$sochtml = $this->gs_file_get_contents('template/cussocmrwong.html');
	$sochtml = str_replace('{GSSE_INCL_DETAILURL}',$detailurl,$sochtml);
	$cussocitems .= $sochtml;
}
/*End MrWong*/

/*Begin LinkedIn*/
if($this->get_setting('cbWeb20Linked_Checked') == 'True')
{
	$sochtml = $this->gs_file_get_contents('template/cussoclinkedin.html');
	$sochtml = str_replace('{GSSE_INCL_DETAILURL}',$detailurl,$sochtml);
	$cussocitems .= $sochtml;
}
/*End LinkedIn*/

/*Begin Google*/
if($this->get_setting('cbWeb20Google_Checked') == 'True')
{
	$sochtml = $this->gs_file_get_contents('template/cussocgoogle.html');
	$sochtml = str_replace('{GSSE_INCL_DETAILURL}',$detailurl,$sochtml);
	$cussocitems .= $sochtml;
}
/*End Google*/

/*Begin MySpace*/
if($this->get_setting('cbWeb20MySpace_Checked') == 'True')
{
	$sochtml = $this->gs_file_get_contents('template/cussocmyspace.html');
	$sochtml = str_replace('{GSSE_INCL_DETAILURL}',$detailurl,$sochtml);
	$cussocitems .= $sochtml;
}
/*End MySpace*/


/*End Socialnetwork Items*/
$custoolshtml = str_replace('{GSSE_INCL_CUSTOOLITEMS}',$custoolsitems,$custoolshtml);
$custoolshtml = str_replace('{GSSE_INCL_CUSSOCITEMS}',$cussocitems,$custoolshtml);
$detailhtml = str_replace('{GSSE_INCL_CUSTOOLS}',$custoolshtml,$detailhtml);
/*End customertools*/

/*Begin HTML-Links*/
$detailhtml = str_replace('{GSSE_INCL_HTMLLINKS}',$_SESSION['aitem']['itemURLsToTestreports'],$detailhtml);
/*End HTML-Links*/

/*Begin Articledownloads*/
$artdownloadshtml = '';
$aDownloads = $this->get_itemdownloads($_SESSION['aitem']['itemItemNumber']);
$dlmax = count($aDownloads);
if($dlmax > 0)
{
	//print_r($aDownloads);
	$artdownloadshtml = $this->gs_file_get_contents('template/articledownloads.html');
	$artdownloadshtml = str_replace('{GSSE_LANG_LangTagTextArticleDownloads}',$this->get_lngtext('LangTagTextArticleDownloads'),$artdownloadshtml);
	$artdownloadsitems = '';
	$artdownloadsitem = $this->gs_file_get_contents('template/articledownloadsitem.html');
	for($ad = 0; $ad < $dlmax; $ad++)
	{
		$cur_dlitem = $artdownloadsitem;
		$cur_dlitem = str_replace('{GSSE_INCL_DOWNLOADFILE}',$aDownloads[$ad]['filename'],$cur_dlitem);
		$cur_dlitem = str_replace('{GSSE_INCL_DOWNLOADTITLE}',$aDownloads[$ad]['title'],$cur_dlitem);
		$artdownloadsitems .= $cur_dlitem;
	}
	$artdownloadshtml = str_replace('{GSSE_INCL_DOWNLOADITEMS}',$artdownloadsitems,$artdownloadshtml);
}
$detailhtml = str_replace('{GSSE_INCL_ARTICLEDOWNLOADS}',$artdownloadshtml,$detailhtml);
/*End Articledownloads*/

/*Begin Productinfos*/
$pinfohtml = '';
if($_SESSION['aitem']['itemManufacturer'] != '' || $_SESSION['aitem']['itemBrand'] != '' || $_SESSION['aitem']['itemManufacturerProductCode'] != '' || $_SESSION['aitem']['itemEAN_ISBN'] != '')
{
	$pinfohtml = $this->gs_file_get_contents('template/productinfos.html');
	$pinfoitemhtml = $this->gs_file_get_contents('template/productinfositem.html');
	$pinfoitems = '';
	if($_SESSION['aitem']['itemManufacturer'] != '')
	{
		$cur_pinfoitem = $pinfoitemhtml;
		$cur_pinfoitem = str_replace('{GSSE_LANG_PINFOTYPE}',$this->get_lngtext('LangTagManufacturer'),$cur_pinfoitem);
		$cur_pinfoitem = str_replace('{GSSE_INCL_PINFO}',$_SESSION['aitem']['itemManufacturer'],$cur_pinfoitem);
		$pinfoitems .= $cur_pinfoitem;
	}
	if($_SESSION['aitem']['itemBrand'] != '')
	{
		$cur_pinfoitem = $pinfoitemhtml;
		$cur_pinfoitem = str_replace('{GSSE_LANG_PINFOTYPE}',$this->get_lngtext('LangTagBrand'),$cur_pinfoitem);
		$cur_pinfoitem = str_replace('{GSSE_INCL_PINFO}',$_SESSION['aitem']['itemBrand'],$cur_pinfoitem);
		$pinfoitems .= $cur_pinfoitem;
	}
	if($_SESSION['aitem']['itemManufacturerProductCode'] != '')
	{
		$cur_pinfoitem = $pinfoitemhtml;
		$cur_pinfoitem = str_replace('{GSSE_LANG_PINFOTYPE}',$this->get_lngtext('LangTagManufacturerProductCode'),$cur_pinfoitem);
		$cur_pinfoitem = str_replace('{GSSE_INCL_PINFO}',$_SESSION['aitem']['itemManufacturerProductCode'],$cur_pinfoitem);
		$pinfoitems .= $cur_pinfoitem;
	}
	if($_SESSION['aitem']['itemEAN_ISBN'] != '')
	{
		$cur_pinfoitem = $pinfoitemhtml;
		$cur_pinfoitem = str_replace('{GSSE_LANG_PINFOTYPE}',$this->get_lngtext('LangTagEAN') . " / " . $this->get_lngtext('LangTagISBN'),$cur_pinfoitem);
		$cur_pinfoitem = str_replace('{GSSE_INCL_PINFO}',$_SESSION['aitem']['itemEAN_ISBN'],$cur_pinfoitem);
		$pinfoitems .= $cur_pinfoitem;
	}
	$pinfohtml = str_replace('{GSSE_INCL_PRODUCTINFOSITEMS}',$pinfoitems,$pinfohtml);
}
$detailhtml = str_replace('{GSSE_INCL_PRODUCTINFOS}',$pinfohtml,$detailhtml);
/*End Productinfos*/

/*Begin Articletexts*/
$detailhtml = str_replace('{GSSE_INCL_itemDetailText1}',$_SESSION['aitem']['itemDetailText1'],$detailhtml);
$detailhtml = str_replace('{GSSE_INCL_ItemText}',$_SESSION['aitem']['itemItemText'],$detailhtml);
$detailhtml = str_replace('{GSSE_INCL_itemDetailText2}',$_SESSION['aitem']['itemDetailText2'],$detailhtml);
$detailhtml = str_replace('{GSSE_INCL_HtmlText1}',$_SESSION['aitem']['itemHtmlText1'],$detailhtml);
$detailhtml = str_replace('{GSSE_INCL_HtmlText2}',$_SESSION['aitem']['itemHtmlText2'],$detailhtml);
/*End Articletexts*/

/*Begin Centraltext*/
$centraltext = '';
if($_SESSION['aitem']['itemUseCentralText'] == 'Y' && $_SESSION['aitem']['itemCentralTextNr'] != '')
{
	$centraltext = stripslashes($this->db_text_ret('settingmemo|SettingMemo|SettingName|memoArticleText' . $_SESSION['aitem']['itemCentralTextNr']));
}
$detailhtml = str_replace('{GSSE_INCL_CENTRALTEXT}',$centraltext,$detailhtml);
/*End Centraltext*/

/*Begin Pricehistory*/
$prichist = '';
/*if($this->phpactive())
{
	if($this->get_setting('cbUsePhpPriceHistory_Checked') == 'True')
	{
		$pchItemNumber = $_SESSION['aitem']['itemItemNumber'];
		$prichist = $this->gs_file_get_contents('template/pricehistory.html');
		$prichist = str_replace('{GSSE_INCL_PHDIAGRAM}',require_once("pricehist.inc.php"),$prichist);
	}
}
*/
$detailhtml = str_replace('{GSSE_INCL_PRICEHISTORY}',$prichist,$detailhtml);
/*End Pricehistory*/

/*Begin Comments*/
$commenthtml = '';
if($this->phpactive())
{
	if($this->get_setting('cbUsePhpUsercomments_Checked') == 'True')
	{
		require_once './dynsb/module/comments/class.comment.php';
		$avgRating = Comment::getAvgRatingByItemNumberVisible($_SESSION['aitem']['itemItemNumber']);
		$aComments = Comment::getAllCommentsByItemNumberVisible($_SESSION['aitem']['itemItemNumber']);
		if (count($aComments) > 0)
		{
			$commenthtml = $this->gs_file_get_contents('template/usercomments.html');
			$commenthtml = str_replace('{GSSE_LANG_LangTagTextUserCommentsAvg}',$this->get_lngtext('LangTagTextUserCommentsAvg'),$commenthtml);
			$commimg = './dynsb/module/comments/rating' . substr(str_replace(',', '', $avgRating), 0, 1) . '.gif';
			$commalt = 'rating' . substr(str_replace(',', '', $avgRating), 0, 2) . '.gif';
			$commenthtml = str_replace('{GSSE_INCL_USERCOMMIMG}',$commimg,$commenthtml);
			$commenthtml = str_replace('{GSSE_INCL_USERCOMMALT}',$commalt,$commenthtml);
			$commentsitems = '';
			$commentsitem = $this->gs_file_get_contents('template/usercommentsitem.html');
			foreach ($aComments as $comment)
			{
				$rating = $comment->getRating();
				$comimg = './dynsb/module/comments/rating' . $rating . '.gif';
				$comalt = 'rating' . $rating;
				$comsub = $comment->getSubject();
				$comdat = $comment->getDate(1);
				$comtxt = $comment->getBody(true);
				$cur_comm = $commentsitem;
				$cur_comm = str_replace('{GSSE_INCL_USERCOMMENTSIMG}',$comimg,$cur_comm);
				$cur_comm = str_replace('{GSSE_INCL_USERCOMMENTSALT}',$comalt,$cur_comm);
				$cur_comm = str_replace('{GSSE_INCL_USERCOMMENTSSUBJECT}',$comsub,$cur_comm);
				$cur_comm = str_replace('{GSSE_INCL_USERCOMMENTSDATE}',$comdat,$cur_comm);
				$cur_comm = str_replace('{GSSE_INCL_USERCOMMENTSBODY}',$comtxt,$cur_comm);
				$commentsitems .= $cur_comm;
			}
			$commenthtml = str_replace('{GSSE_INCL_USERCOMMENTSITEMS}',$commentsitems,$commenthtml);
		}
	}
}
$detailhtml = str_replace('{GSSE_INCL_USERCOMMENTS}',$commenthtml,$detailhtml);
/*End Comments*/

/*Begin Crossselling*/
$crosshtml = '';
$cs = $sl->getCrossselling($_SESSION['aitem']['itemItemNumber']);
$crossmax = count($cs);
if($crossmax > 0)
{
	$crosshtml = $this->gs_file_get_contents('template/crossselling.html');
	$crosshtml = str_replace('{GSSE_LANG_LangTagCrossSellingText}',$this->get_lngtext('LangTagCrossSellingText'),$crosshtml);
	for($c = 0; $c < $crossmax; $c++)
	{
		if($c == 0)
		{
			$csItemNumbers = '"' . $cs[$c]['crsCrossSelingItem'] . '"';
		}
		else
		{
			$csItemNumbers .= ',"' . $cs[$c]['crsCrossSelingItem'] . '"';
		}
	}
	$csdbh = $this->db_connect();
	$sql = "SELECT itemItemId, itemItemNumber, itemItemDescription, itemSmallImageFile, " .
			 "(SELECT prcPrice FROM " . $this->dbtoken . "price WHERE " . $this->dbtoken . "price.prcItemCount = " . $this->dbtoken . "itemdata.itemItemId AND " . $this->dbtoken . "price.prcQuantityFrom = '0') AS ItemPrice, " .
			 "itemIsNewItem, itemHasDetail, itemItemPage, itemIsCatalogFlg, " .
			 "itemIsVariant, itemAttribute1, itemAttribute2, itemAttribute3, itemIsTextInput, " .
			 "itemInStockQuantity, itemAvailabilityId, itemisDecimal " .
			 "FROM " . $this->dbtoken . "itemdata WHERE itemIsActive = 'Y' AND itemItemNumber IN (" . $csItemNumbers . ")";
	$erg = mysqli_query($csdbh,$sql);
	if(mysqli_errno($csdbh) == 0)
	{
		if(mysqli_num_rows($erg) > 0)
		{
			include('inc/items_boxed.inc.php');
			$html = str_replace('{GSSE_INCL_ITEMSBOXEDLINES}',$this_inner,$outer);
			$crosshtml = str_replace('{GSSE_INCL_CROSSSELLINGITEMS}',$html,$crosshtml);
		}
		else
		{
			$crosshtml = str_replace('{GSSE_INCL_CROSSSELLINGITEMS}','Keine Daten:<br />' . $sql,$crosshtml);
		}
	}
	else
	{
		$crosshtml = str_replace('{GSSE_INCL_CROSSSELLINGITEMS}',mysqli_error($csdbh) . ":<br />" . $sql,$crosshtml);
	}
	mysqli_free_result($erg);
	unset($erg);
	//$crosshtml = str_replace('{GSSE_INCL_CROSSSELLINGITEMS}',$sql,$crosshtml);
}
$detailhtml = str_replace('{GSSE_INCL_CROSSSELLING}',$crosshtml,$detailhtml);
/*End Crossselling*/

/*Begin Upselling*/
$upshtml = '';
$up = $sl->getUpselling($_SESSION['aitem']['itemItemId']);
$upmax = count($up);
if($upmax > 0)
{
	$upshtml = $this->gs_file_get_contents('template/upselling.html');
	$upshtml = str_replace('{GSSE_LANG_LangTagUpsellingText}',stripslashes($this->get_lngtext('LangTagUpSellingText')),$upshtml);
	for($u = 0; $u < $upmax; $u++)
	{
		if($u == 0)
		{
			$upItemIds = $up[$u]['upsUpsellingObjectCount'];
		}
		else
		{
			$upItemIds .= ',' . $up[$u]['upsUpsellingObjectCount'];
		}
	}
	$updbh = $this->db_connect();
	$upsql = "SELECT itemItemId, itemItemNumber, itemItemDescription, itemSmallImageFile, " .
			 "(SELECT prcPrice FROM " . $this->dbtoken . "price WHERE " . $this->dbtoken . "price.prcItemCount = " . $this->dbtoken . "itemdata.itemItemId AND " . $this->dbtoken . "price.prcQuantityFrom = '0') AS ItemPrice, " .
			 "itemIsNewItem, itemHasDetail, itemItemPage, itemIsCatalogFlg, " .
			 "itemIsVariant, itemAttribute1, itemAttribute2, itemAttribute3, itemIsTextInput, " .
			 "itemInStockQuantity, itemAvailabilityId, itemisDecimal " .
			 "FROM " . $this->dbtoken . "itemdata WHERE itemIsActive = 'Y' AND itemItemId IN (" . $upItemIds . ")";
	$erg = mysqli_query($updbh,$upsql);
	if(mysqli_errno($updbh) == 0)
	{
		if(mysqli_num_rows($erg) > 0)
		{
			include('inc/items_boxed.inc.php');
			$html = str_replace('{GSSE_INCL_ITEMSBOXEDLINES}',$this_inner,$outer);
			$upshtml = str_replace('{GSSE_INCL_UPSELLINGITEMS}',$html,$upshtml);
		}
		else
		{
			$upshtml = str_replace('{GSSE_INCL_UPSELLINGITEMS}','Keine Daten:<br />' . $upsql,$upshtml);
		}
	}
	else
	{
		$upshtml = str_replace('{GSSE_INCL_UPSELLINGITEMS}',mysqli_error($updbh) . ":<br />" . $upsql,$upshtml);
	}
	mysqli_free_result($erg);
	//$upshtml = str_replace('{GSSE_INCL_UPSELLINGITEMS}',$sql,$upshtml);
}
$detailhtml = str_replace('{GSSE_INCL_UPSELLING}',$upshtml,$detailhtml);
/*End Upselling*/

/*Begin Autocrossselling*/
$autocross = '';
if($this->get_setting('cbAutoCrossSelling_Checked') == 'True')
{
	$autocross = $this->gs_file_get_contents('template/autocrossselling.html');
	$au = $sl->getAutoCrossSellingList($_SESSION['aitem']['itemItemNumber'], $num);
	//var_dump($au);
	if(count($au) > 0)
	{
		$autocross = str_replace('{GSSE_LANG_LangTagAutoCrossSelling}',$this->get_lngtext('LangTagAutoCrossSelling'),$autocross);
		$c = 0;
		foreach($au as $aItems)
		{
			if($c == 0)
			{
				$csItemIds = '"' . $aItems[0]->itemItemId . '"';
			}
			else
			{
				$csItemIds .= ',"' . $aItems[0]->itemItemId . '"';
			}
			$c++;
		}
		$csdbh = $this->db_connect();
		$sql = "SELECT itemItemId, itemItemNumber, itemItemDescription, itemSmallImageFile, " .
				 "(SELECT prcPrice FROM " . $this->dbtoken . "price WHERE " . $this->dbtoken . "price.prcItemCount = " . $this->dbtoken . "itemdata.itemItemId AND " . $this->dbtoken . "price.prcQuantityFrom = '0') AS ItemPrice, " .
				 "itemIsNewItem, itemHasDetail, itemItemPage, itemIsCatalogFlg, " .
				 "itemIsVariant, itemAttribute1, itemAttribute2, itemAttribute3, itemIsTextInput, " .
				 "itemInStockQuantity, itemAvailabilityId " .
				 "FROM " . $this->dbtoken . "itemdata WHERE itemIsActive = 'Y' AND itemItemId IN (" . $csItemIds . ")";
		$erg = mysqli_query($csdbh,$sql);
		if(mysqli_errno($csdbh) == 0)
		{
			if(mysqli_num_rows($erg) > 0)
			{
				include('inc/items_boxed.inc.php');
				$html = str_replace('{GSSE_INCL_ITEMSBOXEDLINES}',$this_inner,$outer);
				$autocross = str_replace('{GSSE_INCL_AUTOCROSSSELLINGITEMS}',$html,$autocross);
			}
			else
			{
				$autocross = str_replace('{GSSE_INCL_AUTOCROSSSELLINGITEMS}','Keine Daten: <br />' . $sql,$autocross);
			}
		}
		else
		{
			$autocross = str_replace('{GSSE_INCL_AUTOCROSSSELLINGITEMS}',mysqli_error($csdbh) . ":<br />" . $sql,$autocross);
		}
		mysqli_free_result($erg);
		unset($erg);
		//$autocross = str_replace('{GSSE_INCL_AUTOCROSSSELLINGITEMS}',$sql,$autocross);
	}
	else
	{
		$autocross = str_replace('{GSSE_LANG_LangTagAutoCrossSelling}','',$autocross);
		$autocross = str_replace('{GSSE_INCL_AUTOCROSSSELLINGITEMS}','',$autocross);
	}
}
$detailhtml = str_replace('{GSSE_INCL_AUTOCROSSSELLING}',$autocross,$detailhtml);
/*End Autocrossselling*/

$this->content = str_replace($tag, $detailhtml, $this->content);
