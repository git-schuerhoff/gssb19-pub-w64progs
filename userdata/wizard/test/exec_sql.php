<?php
	include('mysql.inc.php');
	$link = new mysqli($dbServer, $dbUser, $dbPass, $dbName);
	$link->query("SET NAMES 'utf8'");
	if(!$link) die('-1');
	$sql = base64_decode($_POST['sqlcmd']);
	$erg = $link->query($sql);
	if($link->errno != 0){
		die($link->errno);
	} else {
		die('0');
	}
	$link->close();
?>