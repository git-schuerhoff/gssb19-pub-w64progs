<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$limit = '';
$pgitems = '';
$pgtmpl = $this->get_pgtemplate($_GET['idx']);

$num = $this->get_setting('edBreakPageAfter_Text');
if($num > 0 && $pgtmpl != 'shoppage_simplelist.htm')
{
	if(isset($_GET['start']))
	{
		$limit = " LIMIT " . $_GET['start'] . ", " . $num;
	}
	else
	{
		$limit = " LIMIT 0, " . $num;
	}
}

$pgidbh = $this->db_connect();
$pgisql = "SELECT ItemID FROM " . $this->dbtoken . "items2group LEFT JOIN " . $this->dbtoken . "itemdata ON " . $this->dbtoken . "items2group.ItemID = " . $this->dbtoken . "itemdata.itemItemId WHERE " . $this->dbtoken . "items2group.ProductGroup = '" . $_GET['idx'] . "' AND " . $this->dbtoken . "itemdata.itemIsActive = 'Y' AND " . $this->dbtoken . "itemdata.itemIsVariant = 'N' ORDER BY OrderIDX ASC, ItemID ASC" . $limit;
$pgierg = mysqli_query($pgidbh,$pgisql);
$iO = 0;
$sql = "SELECT itemItemId, itemItemNumber, itemItemDescription, itemSmallImageFile, " .
		 "(SELECT prcPrice FROM " . $this->dbtoken . "price WHERE " . $this->dbtoken . "price.prcItemCount = " . $this->dbtoken . "itemdata.itemItemId AND " . $this->dbtoken . "price.prcQuantityFrom = '0' LIMIT 1) AS ItemPrice, " .
		 "itemIsNewItem, itemHasDetail, itemItemPage, itemIsCatalogFlg, " .
		 "itemIsVariant, itemAttribute1, itemAttribute2, itemAttribute3, itemIsTextInput, " .
		 "itemInStockQuantity, itemAvailabilityId, itemDetailText1, " .
		 "itemCheckAge, itemMustAge, itemIsAction, itemisDecimal, itemItemText " .
		 "FROM " . $this->dbtoken . "itemdata WHERE itemIsNewItem = 'Y' AND itemIsVariant = 'N' AND itemIsActive = 'Y' AND itemLanguageId = '" . $this->lngID . "' ";

$erg = mysqli_query($pgidbh,$sql);
if(mysqli_errno($pgidbh) == 0) {
	if(mysqli_num_rows($erg) > 0) {
		switch($pgtmpl) {
			case 'shoppage.htm':
				include('inc/items_boxed.inc.php');
				$pgitems = str_replace('{GSSE_INCL_ITEMSBOXEDLINES}',$this_inner,$outer);
				break;
			case 'shoppage_simplelist.htm':
				include('inc/items_simplelist.inc.php');
				$pgitems = str_replace('{GSSE_INCL_ITEMSSIMPLELIST}',$this_inner,$outer);
				break;
			case 'shoppage_list.htm':
				$delbutton = '';
				$ratingimg = '';
				$ratingsubj = '';
				$ratingbody = '';
				$ratingdate = '';
				include('inc/items_list.inc.php');
				$pgitems = str_replace('{GSSE_INCL_ITEMSLISTLINES}',$this_inner,$outer);
				break;
			case 'shoppage_overview.htm':
				$delbutton = '';
				$ratingimg = '';
				$ratingsubj = '';
				$ratingbody = '';
				$ratingdate = '';
				include('inc/items_overview.inc.php');
				$pgitems = str_replace('{GSSE_INCL_ITEMSOVERVIEWLINES}',$this_inner,$outer);
				break;
			default:
				include('inc/items_boxed.inc.php');
				$pgitems = str_replace('{GSSE_INCL_ITEMSBOXEDLINES}',$this_inner,$outer);
				break;
		}
	}
} else {
	$pgitems = mysqli_error($pgidbh) . ":<br />" . $sql;
}
$this->content = str_replace($tag, $pgitems, $this->content);
?>