<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$cmpitems = '';


if(isset($_SESSION['aitems_compare']) && count($_SESSION['aitems_compare']) > 0)
{
	$itemsin = implode(',',$_SESSION['aitems_compare']);

	$cmpdbh = $this->db_connect();
	$sql = "SELECT itemItemId, itemItemNumber, itemItemDescription, itemSmallImageFile, " .
			 "(SELECT prcPrice FROM " . $this->dbtoken . "price WHERE " . $this->dbtoken . "price.prcItemCount = " . $this->dbtoken . "itemdata.itemItemId AND " . $this->dbtoken . "price.prcQuantityFrom = '0' LIMIT 1) AS ItemPrice, (SELECT OrderIDX FROM " . $this->dbtoken . "items2group WHERE " . $this->dbtoken . "items2group.ItemID = " . $this->dbtoken . "itemdata.itemItemId LIMIT 1) AS OrderID," .
			 "itemIsNewItem, itemHasDetail, itemItemPage, itemIsCatalogFlg, " .
			 "itemIsVariant, itemAttribute1, itemAttribute2, itemAttribute3, itemIsTextInput, " .
			 "itemInStockQuantity, itemAvailabilityId, itemDetailText1, " .
			 "itemCheckAge, itemMustAge, itemIsAction, itemisDecimal, itemItemText " .
			 "FROM " . $this->dbtoken . "itemdata WHERE itemIsVariant = 'N' AND itemIsActive = 'Y' AND itemItemId IN (" . $itemsin . ") AND itemLanguageId = '" . $this->lngID . "' ORDER BY OrderID ASC";		 
	$erg = mysqli_query($cmpdbh,$sql);
	if(mysqli_errno($cmpdbh) == 0)
	{
		if(mysqli_num_rows($erg) > 0)
		{
			$compare_items = 1;
			include('inc/items_boxed.inc.php');
			$cmpitems = str_replace('{GSSE_INCL_ITEMSBOXEDLINES}',$this_inner,$outer);
		}
	}
	else
	{
		$cmpitems = mysqli_error($cmpdbh) . ":<br />" . $sql;
	}
}
else
{
	$cmpitems = $this->get_lngtext('LangTagTitleNoArticleForComparison');
}
$this->content = str_replace($tag, $cmpitems, $this->content);
?>