<?php
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

/*
$detailboxnew = $this->gs_file_get_contents($this->absurl . 'template/detailboxnew.html');
$detailboxnew = $this->parse_texts($this->get_tags_ret($detailboxnew),$detailboxnew);
*/
if(!isset($_SESSION['aitem']['itemItemId'])){
	$this->get_item($_GET['idx']);
}
if(isset($_GET['idx'])){
	$itemID = $_GET['idx'];
}else{
	$itemID = $_GET['item'];
}
$detse = new gs_shopengine('detailboxnew.html');
$detailboxnew = $detse->parse_inc();

$aPrices = $this->get_prices($itemID);

$aImgs = $this->get_itempics($_GET['item']);
$action = 0;

/*Add to history*/
$self = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1));
$sl->logItemHistory(session_id(), $_SESSION['aitem']['itemItemNumber'], 
						  base64_encode($_SESSION['aitem']['itemItemDescription']), 
						  $self, 
						  $_SESSION['aitem']['itemSmallImageFile'], 
						  $aPrices['price'], 
						  $_SESSION['aitem']['itemWeight'], 'N');
/*Add to Detailviewmonitor*/
$sl->actid = 2;
$sl->strlog = $_SESSION['aitem']['itemItemNumber'].' '.$_SESSION['aitem']['itemItemDescription'];
$sl->logDetailpage($_SESSION['aitem']['itemItemNumber']);
$sl->actid = '';
$sl->strlog = '';
/*Images*/
/*Standard-Image*/
if(strpos($aImgs[0]['ImageName'],"http") === false && strpos($aImgs[0]['ImageName'],"://") === false) {
	if($aImgs[0]['ImageName'] != '' && file_exists('images/small/' . $aImgs[0]['ImageName'])) {
		$smallimg = $this->absurl . 'images/small/' . $aImgs[0]['ImageName'];
	} else {
		$smallimg = $this->absurl . 'template/images/no_pic_sma.png';
	}
	if($aImgs[0]['ImageName'] != '' && file_exists('images/big/' . $aImgs[0]['ImageName'])) {
		$bigimg = $this->absurl . 'images/big/' . $aImgs[0]['ImageName'];
	} else {
		$bigimg = $this->absurl . 'template/images/no_pic_big.png';
	}
	if($aImgs[0]['ImageName'] != '' && file_exists('images/big/' . $aImgs[0]['ImageName'])) {
		$hugeimg = $this->absurl . 'images/huge/' . $aImgs[0]['ImageName'];
	} else {
		$hugeimg = $this->absurl . 'template/images/no_pic_big.png';
	}
}
else {
	$smallimg = $aImgs[0]['ImageName'];
	$bigimg = $aImgs[0]['ImageName'];
	$hugeimg = $aImgs[0]['ImageName'];
}

//$detailboxnew = str_replace('',,$detailboxnew);
$detailboxnew = str_replace('{GSSE_INCL_BIGIMG}',$bigimg,$detailboxnew);
$itemname = $_SESSION['aitem']['itemItemDescription'];
if($_SESSION['aitem']['itemVariantDescription'] != '') {
	$itemname .= ' '.$_SESSION['aitem']['itemVariantDescription'];
}
$detailboxnew = str_replace('{GSSE_INCL_ITEMNAME}',$itemname,$detailboxnew);
$detailboxnew = str_replace('{GSSE_INCL_HUGEIMG}',$hugeimg,$detailboxnew);
$detailboxnew = str_replace('{GSSE_INCL_SMALLIMG}',$smallimg,$detailboxnew);

/*Gallery*/
$gallery = '';
$imgmax = count($aImgs);
if($imgmax > 1)
{
	$gallery = $this->gs_file_get_contents($this->absurl . 'template/gallery_outer.html');
	$galitem = $this->gs_file_get_contents($this->absurl . 'template/gallery_item.html');
	$all_items = '';
	for($p = 0; $p < $imgmax; $p++)
	{
		$lImgFound = false;
		/*Bilder Online oder lokal*/
		if(strpos($aImgs[$p]['ImageName'],"http") === false && strpos($aImgs[$p]['ImageName'],"://") === false) {
			$smallimg = $this->absurl . 'images/small/' . $aImgs[$p]['ImageName'];
			$medimg = $this->absurl . 'images/medium/' . $aImgs[$p]['ImageName'];
			$bigimg = $this->absurl . 'images/big/' . $aImgs[$p]['ImageName'];
			$hugeimg = $this->absurl . 'images/huge/' . $aImgs[$p]['ImageName'];
		}
		else {
			$smallimg = $aImgs[$p]['ImageName'];
			$bigimg = $aImgs[$p]['ImageName'];
			$hugeimg = $aImgs[$p]['ImageName'];
		}
		$cur_item = $galitem;
		//$cur_item = str_replace('',,$cur_item);
		if(strpos($aImgs[$p]['ImageName'],"http") === false && strpos($aImgs[$p]['ImageName'],"://") === false) {
			if (file_exists($smallimg)) {
				$cur_item = str_replace('{GSSE_INCL_GALSMALLIMG}',$smallimg,$cur_item);
				$lImgFound = true;
			} else if (file_exists($medimg)) {
				$cur_item = str_replace('{GSSE_INCL_GALSMALLIMG}',$medimg,$cur_item);
				$lImgFound = true;
			} else if (file_exists($bigimg)) {
				$cur_item = str_replace('{GSSE_INCL_GALSMALLIMG}',$bigimg,$cur_item);
				$lImgFound = true;
			}
		} else {
			if ($aImgs[$p]['ImageName']) {
				$cur_item = str_replace('{GSSE_INCL_GALSMALLIMG}',$smallimg,$cur_item);
				$lImgFound = true;
			}
		}
		
		if($lImgFound) {
			$cur_item = str_replace('{GSSE_INCL_GALITEMNAME}',$_SESSION['aitem']['itemItemDescription'],$cur_item);
			$cur_item = str_replace('{GSSE_INCL_GALHUGEIMG}',$hugeimg,$cur_item);
			$cur_item = str_replace('{GSSE_INCL_GALBIGIMG}',$bigimg,$cur_item);
			$all_items .= $cur_item;
		}
	}
	$gallery = str_replace('{GSSE_INCL_GALLERYITEMS}',$all_items,$gallery);
}
$detailboxnew = str_replace('{GSSE_INCL_GALLERY}',$gallery,$detailboxnew);

/*Navigation*/
$prev = '';
$next = '';
if(isset($_SESSION['active_group'])) {
	$aPGItems = $this->get_groupitems_by_group($_SESSION['active_group']);
	$link = $this->gs_file_get_contents($this->absurl . 'template/link.html');
	if(count($aPGItems) > 1)
	{
		$pgidx = $this->array_search_multi($itemID, $aPGItems);
		if($pgidx !== false)
		{
			if($pgidx == 0)
			{
				$next = $link;
				$detailurl = $this->absurl . 'index.php?page=detail&amp;item=' . $aPGItems[$pgidx + 1]['ItemID'] . '&amp;d=' . $aPGItems[$pgidx + 1]['ItemPage'];
				$next = str_replace('{GSSE_INCL_LINKCLASS}','next',$next);
				$next = str_replace('{GSSE_INCL_LINKURL}',$detailurl,$next);
				$next = str_replace('{GSSE_INCL_LINKTARGET}','_self',$next);
				$next = str_replace('{GSSE_INCL_LINKNAME}',$this->get_lngtext('LangTagNextPageLinkName'),$next);
			}
			elseif($pgidx == (count($aPGItems) - 1))
			{
				$prev = $link;
				$detailurl = $this->absurl . 'index.php?page=detail&amp;item=' . $aPGItems[$pgidx - 1]['ItemID'] . '&amp;d=' . $aPGItems[$pgidx - 1]['ItemPage'];
				$prev = str_replace('{GSSE_INCL_LINKCLASS}','prev',$prev);
				$prev = str_replace('{GSSE_INCL_LINKURL}',$detailurl,$prev);
				$prev = str_replace('{GSSE_INCL_LINKTARGET}','_self',$prev);
				$prev = str_replace('{GSSE_INCL_LINKNAME}',$this->get_lngtext('LangTagPrevPageLinkName'),$prev);
			}
			else
			{
				$next = $link;
				$detailurl = $this->absurl . 'index.php?page=detail&amp;item=' . $aPGItems[$pgidx + 1]['ItemID'] . '&amp;d=' . $aPGItems[$pgidx + 1]['ItemPage'];
				$next = str_replace('{GSSE_INCL_LINKCLASS}','next',$next);
				$next = str_replace('{GSSE_INCL_LINKURL}',$detailurl,$next);
				$next = str_replace('{GSSE_INCL_LINKTARGET}','_self',$next);
				$next = str_replace('{GSSE_INCL_LINKNAME}',$this->get_lngtext('LangTagNextPageLinkName'),$next);
				
				$prev = $link;
				$detailurl = $this->absurl . 'index.php?page=detail&amp;item=' . $aPGItems[$pgidx - 1]['ItemID'] . '&amp;d=' . $aPGItems[$pgidx - 1]['ItemPage'];
				$prev = str_replace('{GSSE_INCL_LINKCLASS}','prev',$prev);
				$prev = str_replace('{GSSE_INCL_LINKURL}',$detailurl,$prev);
				$prev = str_replace('{GSSE_INCL_LINKTARGET}','_self',$prev);
				$prev = str_replace('{GSSE_INCL_LINKNAME}',$this->get_lngtext('LangTagPrevPageLinkName'),$prev);
			}
		}
	}
}
$detailboxnew = str_replace('{GSSE_INCL_PREV}',$prev,$detailboxnew);
$detailboxnew = str_replace('{GSSE_INCL_NEXT}',$next,$detailboxnew);

//Text priceinformation
if($_SESSION['aitem']['itemIsCatalogFlg'] == 'N' && $_SESSION['aitem']['itemIsTextHasNoPrice'] == 'N')
{
	$pinfo = $this->get_setting('edPriceInformation_Text');
	$detailboxnew = str_replace('{GSSE_INCL_ITEMPRICEINFO}',$pinfo,$detailboxnew);
}
else
{
	$detailboxnew = str_replace('{GSSE_INCL_ITEMPRICEINFO}','',$detailboxnew);
}
/*ItemNumber*/
$detailboxnew = str_replace('{GSSE_INCL_ITEMNUMBER}',$_SESSION['aitem']['itemItemNumber'],$detailboxnew);

/*Shortdescription*/
$detailboxnew = str_replace('{GSSE_INCL_ITEMTEXT}',$_SESSION['aitem']['itemItemText'],$detailboxnew);

/*Zoomfuntion per Einstellugen abschaltbar*/
if ($this->get_setting('cbImageZoom_Checked') == 'False'  || !$this->get_setting('cbImageZoom_Checked')){
	$detailboxnew = str_replace('class="zoomWrapper"','class="zoomWrapper" style="display:none;"',$detailboxnew);
}

$this->content = str_replace($tag, $detailboxnew, $this->content);
?>