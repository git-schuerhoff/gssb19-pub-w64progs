<?php
header("Content-type: application/json; charset=utf-8");
chdir("../");
include_once("inc/class.shopengine.php");
$prcse = new gs_shopengine();

$aPrices = $prcse->get_prices($_GET['itemid']);

echo json_encode($aPrices);
?>