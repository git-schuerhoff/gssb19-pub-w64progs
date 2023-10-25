<?php
    chdir("../");
    require_once("inc/class.shopengine.php");
    $cus = new gs_shopengine('cusmenu.html');
	$cus->parse_inc();
    echo json_encode($cus->content);
?>