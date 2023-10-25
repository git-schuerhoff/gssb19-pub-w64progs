<?php
session_start();
chdir("../");
include_once("inc/class.shopengine.php");
$se = new gs_shopengine();
$pdbh = $se->db_connect();
$sql = "INSERT INTO " . $se->dbtoken . "availmail VALUE ('".$_GET['email']."','".$_SESSION['aitem']['itemItemNumber']."')";
$perg = mysqli_query($pdbh,$sql);
mysqli_close($pdbh);
?>