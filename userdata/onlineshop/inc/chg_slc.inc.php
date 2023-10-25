<?php
	session_start();
	$rel = 0;
	if($_SESSION['slc'] != $_SESSION['aslc'][$_GET['idx']]) {
		$rel = 1;
	}
	$_SESSION['slc'] = $_SESSION['aslc'][$_GET['idx']];
	$_SESSION['cnt'] = $_SESSION['acnt'][$_GET['idx']];
	echo $rel;
?>