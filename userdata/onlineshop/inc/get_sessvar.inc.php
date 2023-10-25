<?php
	session_start();
	$sessval = '';
	if($_GET['cname'] != "") {
		$cname = $_GET['cname'];
		if(isset($_SESSION[$cname])) {
			$sessval = $_SESSION[$cname];
		}
	}
	echo $sessval;
?>