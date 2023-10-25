<?php
header("Content-type: application/json; charset=utf-8");
chdir("../");
include_once("inc/class.shopengine.php");
$pgse = new gs_shopengine();
$aerg = array();
$aPrices = array();
$tmpText = str_replace(" ", "%", trim(addslashes(strip_tags($_GET['search']))));
$SQLText  = " AND (lower(itemItemDescription) LIKE '%".strtolower($tmpText)."%'";
$SQLText .= " OR lower(itemVariantDescription) LIKE '%".strtolower($tmpText)."%'";
$SQLText .= " OR lower(itemItemNumber) LIKE '%".strtolower($tmpText)."%'";
$SQLText .= " OR lower(itemItemText) LIKE '%".strtolower($tmpText)."%'";
$SQLText .= " OR lower(itemManufacturer) LIKE '%".strtolower($tmpText)."%'";
$SQLText .= " OR lower(itemManufacturerProductCode) LIKE '%".strtolower($tmpText)."%'";
$SQLText .= " OR lower(itemEAN_ISBN) LIKE '%".strtolower($tmpText)."%'";
$SQLText .= " OR lower(itemBrand) LIKE '%".strtolower($tmpText)."%'";
$SQLText .= " OR lower(itemSoonHereText) LIKE '%".strtolower($tmpText)."%'";
$SQLText .= " OR lower(itemHtmlText1) LIKE '%".strtolower($tmpText)."%'";
$SQLText .= " OR lower(itemHtmlText2) LIKE '%".strtolower($tmpText)."%'";
$SQLText .= " OR lower(itemDetailText1) LIKE '%".strtolower($tmpText)."%'";
$SQLText .= " OR lower(itemDetailText2) LIKE '%".strtolower($tmpText)."%')";

$pgidbh = $pgse->db_connect();
$sql = "SELECT itemItemId, itemItemNumber, itemItemDescription, itemSmallImageFile, " .
	 "(SELECT prcPrice FROM " . $pgse->dbtoken . "price WHERE " . $pgse->dbtoken . "price.prcItemCount = " . 
	 $pgse->dbtoken . "itemdata.itemItemId AND " . $pgse->dbtoken . "price.prcQuantityFrom = '0') AS ItemPrice, " .
	 "(SELECT OrderIDX FROM " . $pgse->dbtoken . 
	 "items2group WHERE " . $pgse->dbtoken . "items2group.ItemID = " . $pgse->dbtoken . 
	 "itemdata.itemItemId LIMIT 1) AS OrderID," .
	 "itemIsNewItem, itemHasDetail, itemItemPage, itemIsCatalogFlg, itemShipmentStatus, " .
	 "itemIsVariant, itemAttribute1, itemAttribute2, itemAttribute3, itemIsTextInput, " .
	 "itemInStockQuantity, itemAvailabilityId, itemDetailText1, " .
	 "itemCheckAge, itemMustAge, itemIsAction, itemisDecimal, itemItemText, itemIsTextHasNoPrice " .
	 "FROM " . $pgse->dbtoken . "itemdata WHERE itemIsActive = 'Y' AND itemLanguageId = '" . $pgse->lngID . "' " . $SQLText;
$erg = mysqli_query($pgidbh,$sql);

if(mysqli_errno($pgidbh) == 0)
{
	if(mysqli_num_rows($erg) > 0)
	{
		while($z = mysqli_fetch_assoc($erg))
		{
			$aHelper = array();
			
			foreach($z as $key => $val)
			{
				$aHelper[$key] = $val;
			}
			$aPrices = $pgse->get_prices($z['itemItemId']);
			$aHelper['aprices'] = $aPrices;
			$aHelper['aimgs'] = $pgse->get_itempics($z['itemItemId']);
			$aerg[] = $aHelper;
		}
	}
}
@mysqli_close($pgidbh);
echo json_encode($aerg);
?>