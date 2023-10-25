<?php
	include('mysql.inc.php');
	
	$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbName);
	if(!$link) die('1');
	echo "0";
?>