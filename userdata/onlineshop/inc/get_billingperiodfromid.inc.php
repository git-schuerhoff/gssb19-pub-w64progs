<?php
chdir("../");
include_once("inc/class.shopengine.php");
$bpse = new gs_shopengine();
$lPer = false;
$lPlural = false;
$lAdj = false;
if($_GET['iper'] == 1) { $lPer = true; }
if($_GET['iplural'] == 1) { $lPlural = true; }
if($_GET['iadj'] == 1) { $lAdj = true; }

echo $bpse->no_umlauts($bpse->get_billingperiodfromid($_GET['ikey'],$lPer,$lPlural,$lAdj));
?>