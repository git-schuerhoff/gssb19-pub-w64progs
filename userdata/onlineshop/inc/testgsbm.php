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

if(file_exists("./class/class.gsbmconnector.php")) {
	require_once("./class/class.gsbmconnector.php");
} elseif(file_exists("./class.gsbmconnector.php")) {
	require_once("./class.gsbmconnector.php");
} else {
	die('-1');
}

chdir("../");
if(file_exists("./inc/class.shopengine.php")) {
	include_once("inc/class.shopengine.php");
} else {
	die('-2');
}
$se = new gs_shopengine();


//$aresult['result'] = $_POST;

$myURL = $se->get_setting('edGSBMUrl_Text');
$myDB = $se->get_setting('edGSBMDBName_Text');
$myUSR = $se->get_setting('edGSBMUserName_Text');
$myPWD = convert_uudecode($se->get_setting('edGSBMPassword_Text'));
$oc = new gsbmConnector($myURL,$myDB,$myUSR,$myPWD,false);
$oc->connect();

echo($oc->uid);
?>