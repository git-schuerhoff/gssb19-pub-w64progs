<?php
	error_reporting(E_ALL);
	ini_set("display_errors","on");
	$updatefile = $_GET['updatefile'];
	//$importfile = "itemdownloads.inc.php";
	require("../../conf/db.const.inc.php");
	require_once("../include/functions.inc.php");
	
	$mysqli = mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
	$mysqli->query("SET NAMES 'utf8'");
	
	$updsql = file_get_contents('mysqlupdate/'.$updatefile);
	$mysqli->query($updsql);
	if($mysqli->errno != 0) {
		//1060: Duplicate Columnname
		if($mysqli->errno != 1060) {
			echo $mysqli->errno . ': ' . $mysqli->error;
		}
	}
	$mysqli->close();
	unlink('mysqlupdate/'.$updatefile);
?>