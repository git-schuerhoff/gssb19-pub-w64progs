<?php
chdir("../");
include_once("inc/class.shopengine.php");
$se = new gs_shopengine();
echo $se->get_custemail($_GET['cemail']);
?>