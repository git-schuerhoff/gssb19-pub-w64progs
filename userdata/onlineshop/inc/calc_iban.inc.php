<?php
chdir("../");
include_once("inc/class.shopengine.php");
$ibanse = new gs_shopengine();
$prefix = "DE00";
$accountno = str_pad($_GET['account'],10,"0",STR_PAD_LEFT);

$pre_iban = $prefix . $_GET['bankcode'] . $accountno;

$iban = $ibanse->aprove_iban($pre_iban);

echo $iban;


?>