<?php
header("Content-type: application/json; charset=utf-8");
chdir("../");
include_once("inc/class.shopengine.php");
$atse = new gs_shopengine();
$atdbh = $atse->db_connect();
$aAttr = array();
$asel = "SELECT value FROM " . $atse->dbtoken . "attributes WHERE name = '" . $_GET['attr'] . "' ORDER BY attributeOrder ASC";
$aerg = mysqli_query($atdbh,$asel);
while($a = mysqli_fetch_assoc($aerg)) {
	$aAttr[] = $a['value'];
}
echo json_encode($aAttr);
@mysqli_close($atdbh);
?>