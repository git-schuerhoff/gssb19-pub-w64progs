<?php
	include('../conf/db.const.inc.php');
	$link = new mysqli($dbServer, $dbUser, $dbPass, $dbDatabase);
	if(!$link) die('-1');
	$sql = "SHOW TABLES LIKE '".DBToken."%'";
	$erg = $link->query($sql);
	if($link->errno != 0)
	{
		die('-3');
	}
	else
	{
		echo $erg->num_rows;
	}
	$link->close();
?>