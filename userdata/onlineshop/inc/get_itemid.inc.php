<?php
session_start();
chdir("../");
include_once("inc/class.shopengine.php");
$ausg = -1;
$idse = new gs_shopengine();
$iddbh = $idse->db_connect();
$idsql = "SELECT itemItemId FROM " . $idse->dbtoken . "itemdata WHERE itemItemNumber = '" . $_GET['itemno'] . "' AND itemLanguageId = '" . $idse->lngID . "' LIMIT 1";
$iderg = mysqli_query($iddbh,$idsql);
if(mysqli_errno($iddbh) == 0)
{
	if(mysqli_num_rows($iderg) > 0)
	{
		$id = mysqli_fetch_assoc($iderg);
		$ausg = $id['itemItemId'];
	}
	mysqli_free_result($iderg);
}
mysqli_close($iddbh);
echo $ausg;
?>