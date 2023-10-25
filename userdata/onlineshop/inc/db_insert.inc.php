<?php
session_start();
chdir("../");
include_once("inc/class.shopengine.php");
$se = new gs_shopengine();
echo $se->db_insert($_POST['db_table'],$_POST['db_values']);
?>