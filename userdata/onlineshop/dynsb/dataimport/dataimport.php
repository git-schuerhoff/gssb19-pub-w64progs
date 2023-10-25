<?php
	error_reporting(E_ALL);
	ini_set("display_errors","on");
	$importfile = $_GET['importfile'];
	//$importfile = "itemdownloads.inc.php";
	require("../../conf/db.const.inc.php");
	require_once("../include/functions.inc.php");
	
	$mysqli = mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
	$mysqli->query("SET NAMES 'utf8'");
	
	include_once("importdata/" . $importfile);
	//echo "<pre>";
	
	//Delete operation (first!!!)
	if(count($deletedata) > 0) {
		performOp($deletedata,$deletesql,$deletetypemask);
	}
	
	//Insert operation
	if(count($insertdata) > 0) {
		performOp($insertdata,$insertsql,$inserttypemask);
	}
	
	//Update operation
	if(count($updatedata) > 0) {
		performOp($updatedata,$updatesql,$updatetypemask);
	}
	
	$mysqli->close();
	unlink("importdata/" . $importfile);
	
	function performOp($aData,$cSQL,$cMask) {
		global $mysqli;
		global $importfile;
		$stmt = $mysqli->prepare($cSQL);
		if($mysqli->errno == 0) {
			$mysqli->query("START TRANSACTION");
			foreach ($aData as $line) {
				$a_param = array();
				$a_param[] = $cMask;
				$a_dec = array();
				$max = count($line);
				for($i = 0; $i < $max; $i++) {
					$a_dec[] = base64_decode($line[$i]);
					$a_param[] = &$a_dec[$i];
					//$a_param[] = &$line[$i];
				}
				//print_r($a_param);
				if(call_user_func_array(array($stmt, 'bind_param'), $a_param) === false) {
					die($importfile.' Bind:'.$cSQL.': '.implode('->',$a_param));
				}
				$stmt->execute();
				if($mysqli->errno != 0) {
					die($importfile.' Exec:'.$mysqli->error);
				}
			}
		} else {
			die($importfile.' Prep:'.$mysqli->error);
		}
		$stmt->close();
		$mysqli->query("COMMIT");
		return;
	}
	
?>