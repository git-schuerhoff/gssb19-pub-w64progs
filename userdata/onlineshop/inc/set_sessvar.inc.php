<?php
	session_start();
	$cname = $_GET['cname'];
	$xvalue = $_GET['xvalue'];
	if($cname != "") {
		if($xvalue != "") {
			$_SESSION[$cname] = $xvalue;
		} else {
			if(isset($_SESSION[$cname])) {
				unset($_SESSION[$cname]);
			}
		}
	}
?>