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
	$str = str_replace('�', '**ae**', $str);
	$str = str_replace('�', '**Ae**', $str);
	$str = str_replace('�', '**oe**', $str);
	$str = str_replace('�', '**Oe**', $str);
	$str = str_replace('�', '**ue**', $str);
	$str = str_replace('�', '**Ue**', $str);
	$str = str_replace('�', '**ss**', $str);
	return $str;
}

?>