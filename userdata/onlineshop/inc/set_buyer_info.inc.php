<?php
	session_start();
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':
			$aLocal = &$_GET;
			break;
		case 'POST':
			$aLocal = &$_POST;
			break;
		default:
			$aLocal = &$_POST;
			break;
	}
	
	$aBuyerInfo = json_decode($aLocal['buyerinfostr'],true);
	$_SESSION['buyerinfo'] = $aBuyerInfo;

?>