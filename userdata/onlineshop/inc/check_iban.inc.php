<?php
chdir("../");
include_once("inc/class.shopengine.php");
$ibanse = new gs_shopengine();

echo $ibanse->aprove_iban($_GET['iban']);
?>