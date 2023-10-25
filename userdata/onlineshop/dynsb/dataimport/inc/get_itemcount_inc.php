<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
chdir("../../../");
include_once("inc/class.shopengine.php");
$se = new gs_shopengine();
$mysqli = $se->db_connect();
$result = $mysqli->query("SELECT COUNT(itemItemId) AS cnt FROM " . $se->dbtoken . "itemdata WHERE itemLanguageId = '" . $se->lngID . "'");
if($mysqli->errno == 0) {
	$r = $result->fetch_object();
	echo $r->cnt;
} else {
	echo -1;
}
$mysqli->close();
?>