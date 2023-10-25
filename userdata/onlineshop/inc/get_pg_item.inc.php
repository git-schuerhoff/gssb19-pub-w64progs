<?php
header("Content-type: application/json; charset=utf-8");
chdir("../");
include_once("inc/class.shopengine.php");
$pgse = new gs_shopengine();
$pgdbh = $pgse->db_connect();
$aerg = array();
$aPrices = array();

//TS 09.01.2017: Ermitteln, ob der Artikel Varianten hat
$pgisql = "SELECT I.itemItemId, I.itemItemNumber, I.itemItemDescription, I.itemVariantDescription, I.itemSmallImageFile, " .
			 "I.itemIsNewItem, I.itemHasDetail, I.itemItemPage, I.itemIsCatalogFlg, " .
			 "I.itemIsVariant, I.itemAttribute1, I.itemAttribute2, I.itemAttribute3, I.itemIsTextInput, " .
			 "I.itemInStockQuantity, I.itemAvailabilityId, I.itemDetailText1, " .
			 "I.itemCheckAge, I.itemMustAge, I.itemIsAction, I.itemisDecimal, I.itemItemText, " .
			 "I.itemIsTextHasNoPrice, I.itemShipmentStatus, " .
			 "(SELECT IFNULL(AVG(R.itcoRating),0) FROM " . $pgse->dbtoken . "itemcomments R WHERE R.itcoItemNumber = I.itemItemNumber AND R.itcoVisible ='Y') AS rating_avg, " .
			 "(SELECT COUNT(R.itcoIdNo) FROM " . $pgse->dbtoken . "itemcomments R WHERE R.itcoItemNumber = I.itemItemNumber AND R.itcoVisible ='Y') AS rating_cnt, " .
			 "(SELECT IFNULL(COUNT(V.varGroupCount),0) FROM " . $pgse->dbtoken . "item_to_variant V WHERE V.varGroupCount = I.itemItemId) AS has_variants " .
			 "FROM " . $pgse->dbtoken . "itemdata I " .
			 "WHERE I.itemItemId = " . $_GET['idx'];
//echo $pgisql;
$pgierg = mysqli_query($pgdbh,$pgisql);
$iO = 0;
if(mysqli_num_rows($pgierg) > 0) {
	$z = mysqli_fetch_assoc($pgierg);
	$aHelper = array();
		
	foreach($z as $key => $val) {
		$aHelper[$key] = $val;
	}
		
	$aPrices = $pgse->get_prices($z['itemItemId']);
	if(is_array($aPrices)) {
		$aHelper['aprices'] = $aPrices;
	} else {
		$aHelper['aprices'] = array();
	}
	$aHelper['hasaction'] = $pgse->chk_action($z['itemItemId'],$aPrices);
	$aHelper['aimgs'] = $pgse->get_itempics($z['itemItemId']);
	//$aHelper['avail'] = rawurlencode($pgse->get_availability($z['itemInStockQuantity'],$z['itemShipmentStatus'],0));
	$aHelper['availtext'] = $pgse->get_availability_text($z['itemInStockQuantity'],$z['itemShipmentStatus'],0);
	//TS 04.01.2017: Ist jetzt in der Abfrage mit drin
	/*$aRat = $pgse->get_av_rating($z['itemItemNumber']);
	$aHelper['rating_avg'] = $aRat[0]['schnitt'];
	$aHelper['rating_cnt'] = $aRat[0]['menge'];*/
	
	//TODO: Abfragen, ob das überhaupt nötig ist. Wenn nicht, dann false übergeben
	$aHelper['bestseller'] = $pgse->item_is_bestseller($z['itemItemNumber']);
	//$aHelper['bestseller'] = false;
	//$aerg[] = $aHelper;
}
echo json_encode($aHelper);
/*echo '<pre>';
print_r($aHelper);*/
@mysqli_close($pgdbh);
?>