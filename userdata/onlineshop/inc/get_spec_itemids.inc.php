<?php
header("Content-type: application/json; charset=utf-8");
chdir("../");
include_once("inc/class.shopengine.php");
$pgse = new gs_shopengine();
$pgdbh = $pgse->db_connect();
$aerg = array();
$aPrices = array();

$pgisql = "SELECT I.itemItemId " .
			 "FROM " . $pgse->dbtoken . "itemdata I " .
			 "WHERE I.itemIsActive = 'Y' AND I.itemIsVariant = 'N' AND I.itemLanguageId = '" . $pgse->lngID . "' " .
			 "AND ".$_GET['field']."='".$_GET['val']."' ".
			 "ORDER BY I.itemItemId ASC";
$pgierg = mysqli_query($pgdbh,$pgisql);
$iO = 0;
if(mysqli_num_rows($pgierg) > 0) {
	while($z = mysqli_fetch_assoc($pgierg)) {
		//if(!in_array($z['itemItemId'], $aerg)) {
			$aerg[] = array('ID' => $z['itemItemId']);
		//}
	}
	echo json_encode($aerg);
}
@mysqli_close($pgdbh);
?>