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
$ups = '';
/*Begin Upsselling*/
$up = $sl->getUpselling($_SESSION['aitem']['itemItemId']);
$upmax2 = count($up);
if($upmax2 > 0)
{
	$ups = file_get_contents($this->absurl . 'template/crossup_outer.html');
	$item_box = file_get_contents($this->absurl . 'template/crossup_item.html');
	$ups = str_replace('{GSSE_INCL_CROSSUPHEAD}',$this->get_lngtext('LangTagUpSellingText'),$ups);
	$ups = str_replace('{GSSE_INCL_BLOCKID}','block_upselling_products',$ups);
	for($u = 0; $u < $upmax2; $u++)
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
			include('./inc/fill_items_box.inc.php');
			$ups = str_replace('{GSSE_INCL_CROSSUPITEMS}',$all_items,$ups);
		}
	}
	mysqli_free_result($erg);
	unset($erg);	
}

/*End Upselling*/

$this->content = str_replace($tag,$ups,$this->content);
?>