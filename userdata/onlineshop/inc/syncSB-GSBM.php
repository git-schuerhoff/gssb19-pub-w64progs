<?php
header('Content-type: text/html; charset=UTF-8');
/*********************************************************************
*                                                                    *
* GSBM to GSSB-Interface V1.0 - syncSB-GSBM.php                      *
* Author: Thilo Schürhoff / Schürhoff EDV                            *
*                                                                    *
* (C) 2016 GS Software AG                                            *
*                                                                    *
* this code is NOT open-source or freeware                           *
* you are not allowed to use, copy or redistribute it in any way     *
*                                                                    *
*********************************************************************/
/*Debug Settings*/
/*
$_POST['limit'] = '0';
$_POST['dataModel'] = 'product.template';
$_POST['fieldName'] = 'id';
$_POST['condition'] = '=';
$_POST['value'] = '11';
$_POST['resFields'] = 'display_name,lst_price,__last_update';
error_reporting(E_ALL);
ini_set("display_errors","on");
echo "<pre>";
*/
include_once("gsbm.errorcodes.inc.php");
$errno = 0;
$error = 'OK';

$aresult = array("errno" => $errno,
					  "error" => $error,
					  "result" => array());

if(file_exists("./class/class.gsbmconnector.php")) {
	require_once("./class/class.gsbmconnector.php");
} elseif(file_exists("./class.gsbmconnector.php")) {
	require_once("./class.gsbmconnector.php");
} else {
	$aresult['errno'] = REQGSBMCLASS_NO;
	$aresult['error'] = REQGSBMCLASS;
	die(json_encode($aresult));
}

chdir("../");
if(file_exists("./inc/class.shopengine.php")) {
	include_once("inc/class.shopengine.php");
} else {
	$aresult['errno'] = REQSECLASS_NO;
	$aresult['error'] = REQSECLASS;
	die(json_encode($aresult));
}

$se = new gs_shopengine();


//$aresult['result'] = $_POST;

$myURL = $se->get_setting('edGSBMUrl_Text');
$myDB = $se->get_setting('edGSBMDBName_Text');
$myUSR = $se->get_setting('edGSBMUserName_Text');
$myPWD = convert_uudecode($se->get_setting('edGSBMPassword_Text'));

$oc = new gsbmConnector($myURL,$myDB,$myUSR,$myPWD,false);
$oc->connect();

$aOptions = array();
//array('fields'=>array('name', 'country_id', 'comment'), 'limit'=>5)

if($_POST['resFields'] != '') {
	$aresFields = explode(',',$_POST['resFields']);
	$aOptions['fields'] = $aresFields;
}

if($_POST['limit'] != '0') {
	$aOptions['limit'] = intVal($_POST['limit']);
}

/*print_r($_POST);
print_r($aOptions);*/

$oc->dataModel = $_POST['dataModel'];
$aGSBMRes = $oc->getData(array(array(array($_POST['fieldName'], $_POST['condition'], $_POST['value']))),$aOptions);

if(isset($aGSBMRes['faultCode'])) {
	$aresult['errno'] = $aGSBMRes['faultCode'];
	$aresult['error'] = $aGSBMRes['faultString'];
} else {
	$aEncRes = encodeValues($aGSBMRes);
	$aresult['result'] = $aEncRes;
}



/*if(!empty($aGSBMRes)) {
	for($i = 0; $i < $aGSBMRes; $i++) {
		$aRes[] = $aGSBMRes[$i]['name_template'];
	}
}*/


//$aresult['result'] = $_POST;

/*print_r($aresult);
echo "</pre><br>";*/

echo json_encode($aresult);

function encodeValues($aArr) {
	$aEnc = array();
	foreach($aArr as $key => $value) {
		if(is_array($value)) {
			$aEnc[$key] = encodeValues($value);
		} else {
			if(is_string($value)) {
				if($key == '__last_update') {
					if($value != '') {
						$format = 'Y-m-d H:i:s';
						$date = DateTime::createFromFormat($format, $value);
						$value = $date->getTimestamp();
					}
				}
				$value = base64_encode(utf8_encode($value));
			}
		$aEnc[$key] = $value;
		}
	}
	return $aEnc;
}

?>