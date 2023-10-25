<?php
session_start();
chdir("../");
include_once("inc/class.shopengine.php");
$se = new gs_shopengine();
echo $se->db_num_rows($_GET['db_table'],$_GET['db_key'],$_GET['db_where']);
?>