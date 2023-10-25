<?php
header("Content-type: application/json; charset=utf-8");
chdir("../");
include_once("inc/class.shopengine.php");
$lngse = new gs_shopengine();
$aRetLng = array();
foreach($lngse->aLang as $val)
{
	$aRetLng[] = array($val[0],$val[1]);
}
echo json_encode($aRetLng);
?>