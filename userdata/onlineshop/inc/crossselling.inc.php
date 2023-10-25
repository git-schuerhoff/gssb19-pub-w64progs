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
$cross = '';
/*Begin Crossselling*/
$cs = $sl->getCrossselling($_SESSION['aitem']['itemItemNumber']);
if(count($cs) > 0)
{
	$cross = $this->gs_file_get_contents($this->absurl . 'template/crossup_outer.html');
	$item_box = $this->gs_file_get_contents($this->absurl . 'template/crossup_item.html');
	$cross = str_replace('{GSSE_INCL_CROSSUPHEAD}',$this->get_lngtext('LangTagCrossSellingText'),$cross);
	$cross = str_replace('{GSSE_INCL_BLOCKID}','block_related_products',$cross);
	$csmax = count($cs);
	for($c = 0; $c < $csmax; $c++)
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
			include('./inc/fill_items_box.inc.php');
			$cross = str_replace('{GSSE_INCL_CROSSUPITEMS}',$all_items,$cross);
		}
	}
	mysqli_free_result($erg);
	unset($erg);	
}

/*End Crossselling*/

$this->content = str_replace($tag,$cross,$this->content);
?>