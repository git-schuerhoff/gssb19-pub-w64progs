<?php
header("Content-type: application/json; charset=utf-8");
chdir("../");
include_once("inc/class.shopengine.php");
$pgse = new gs_shopengine();
$pgdbh = $pgse->db_connect();
$aerg = array();
$aPrices = array();

$pgisql = "SELECT I.itemItemId, I.itemItemNumber, I.itemItemDescription, I.itemSmallImageFile, " .
			 "I.itemIsNewItem, I.itemHasDetail, I.itemItemPage, I.itemIsCatalogFlg, " .
			 "I.itemIsVariant, I.itemAttribute1, I.itemAttribute2, I.itemAttribute3, I.itemIsTextInput, " .
			 "I.itemInStockQuantity, I.itemAvailabilityId, I.itemDetailText1, " .
			 "I.itemCheckAge, I.itemMustAge, I.itemIsAction, I.itemisDecimal, I.itemItemText, " .
			 "I.itemIsTextHasNoPrice, I.itemShipmentStatus, " .
			 "P.prcPrice " .
			 "FROM " . $pgse->dbtoken . "itemdata I " .
			 "RIGHT JOIN " . $pgse->dbtoken . "price P ON I.itemItemId = P.prcItemCount AND P.prcQuantityFrom = 0 " .
			 "WHERE I.itemIsActive = 'Y' AND I.itemIsVariant = 'N' AND I.itemLanguageId = '" . $pgse->lngID . "' " .
			 "AND ".$_GET['field']."='".$_GET['val']."' ".
			 "ORDER BY I.itemItemId ASC";
$pgierg = mysqli_query($pgdbh,$pgisql);
$iO = 0;
if(mysqli_num_rows($pgierg) > 0) {
	while($z = mysqli_fetch_assoc($pgierg)) {
		$aHelper = array();
		
		foreach($z as $key => $val) {
			$aHelper[$key] = $val;
		}
		
		$aPrices = $pgse->get_prices($z['itemItemId']);
		$aHelper['hasaction'] = $pgse->chk_action($z['itemItemId'],$aPrices);
		$aHelper['aprices'] = $aPrices;
		$aHelper['aimgs'] = $pgse->get_itempics($z['itemItemId']);
		$aHelper['avail'] = rawurlencode($pgse->get_availability($z['itemInStockQuantity'],$z['itemShipmentStatus'],0));
		$aHelper['availtext'] = $pgse->get_availability_text($z['itemInStockQuantity'],$z['itemShipmentStatus'],0);
		$aRat = $pgse->get_av_rating($z['itemItemNumber']);
		$aHelper['rating_avg'] = $aRat[0]['schnitt'];
		$aHelper['rating_cnt'] = $aRat[0]['menge'];
		$aHelper['bestseller'] = $pgse->item_is_bestseller($z['itemItemNumber']);
		$aerg[] = $aHelper;
	}
	echo json_encode($aerg);
}
@mysqli_close($pgdbh);
?>