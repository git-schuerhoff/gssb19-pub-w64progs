<?php
	header("Content-type: application/json; charset=utf-8");
	session_start();
	$aLogin = array();
	if(isset($_SESSION['login']))
	{
		$aLogin = $_SESSION['login'];
	}
	echo json_encode($aLogin);
?>