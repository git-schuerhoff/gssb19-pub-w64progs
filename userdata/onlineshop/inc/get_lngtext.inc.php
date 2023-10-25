<?php
chdir("../");
include_once("inc/class.shopengine.php");
$lngse = new gs_shopengine();
echo $lngse->get_lngtext($_GET['ctag']);
?>