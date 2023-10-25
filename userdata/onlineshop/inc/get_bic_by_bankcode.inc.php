<?php
chdir("../");
include_once("inc/class.shopengine.php");
$bicse = new gs_shopengine();
$bic = "";
$mycon = $bicse->db_connect();

$sql = "SELECT bic FROM " . $bicse->dbtoken . "banks WHERE countryId = '" . $_GET['countryid'] . "' AND BankCode = '" . $_GET['bankcode'] . "' LIMIT 1";
$erg = mysqli_query($mycon,$sql);
if(mysqli_errno($mycon) == 0) {
	if(mysqli_num_rows($erg) > 0) {
		$z = mysqli_fetch_assoc($erg);
		$bic = $z['bic'];
	}
}

echo $bic;

?>