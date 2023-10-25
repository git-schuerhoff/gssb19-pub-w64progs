<?php
//session_start();
chdir("../");
include_once("inc/class.shopengine.php");
$ffse = new gs_shopengine();
echo $ffse->formfriendly($_GET['cstr']);
?>