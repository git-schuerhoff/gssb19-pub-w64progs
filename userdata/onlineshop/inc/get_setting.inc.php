<?php
session_start();
$settingName = $_GET['cname'];
/*chdir("../");
include_once("inc/class.shopengine.php");
$setse = new gs_shopengine();
$erg = $setse->no_umlauts(base64_decode($_SESSION['sb_settings'][$settingName]));
echo $erg;*/

echo loc_no_umlauts(base64_decode($_SESSION['sb_settings'][$settingName]));

function loc_no_umlauts($str) {
	$str = str_replace('ä', '**ae**', $str);
	$str = str_replace('Ä', '**Ae**', $str);
	$str = str_replace('ö', '**oe**', $str);
	$str = str_replace('Ö', '**Oe**', $str);
	$str = str_replace('ü', '**ue**', $str);
	$str = str_replace('Ü', '**Ue**', $str);
	$str = str_replace('ß', '**ss**', $str);
	return $str;
}

?>