<?php
chdir("../");
include_once("inc/class.shopengine.php");
$se = new gs_shopengine();
if($_GET['type'] == 'cnt'){
	echo $se->cntID;
} else {
	echo $se->lngID;
}
?>