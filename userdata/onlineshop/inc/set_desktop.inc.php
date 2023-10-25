<?php
	session_start();
	if(isset($_SESSION['desktop']))
	{
		unset($_SESSION['desktop']);
	}
	$_SESSION['desktop'] = array();
	$_SESSION['desktop']['s_width'] = intval($_GET['s_width']);
	$_SESSION['desktop']['s_height'] = intval($_GET['s_height']);
	$_SESSION['desktop']['w_width'] = intval($_GET['w_width']);
	$_SESSION['desktop']['w_height'] = intval($_GET['w_height']);
	$_SESSION['desktop']['is_mobile'] = intval($_GET['is_mobile']);
	$_SESSION['desktop']['is_phone'] = intval($_GET['is_phone']);
?>