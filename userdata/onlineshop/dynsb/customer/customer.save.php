<?php
/******************************************************************************/
/* File: customer.save.php                                                  */
/******************************************************************************/

require("../include/login.check.inc.php");
require_once("../include/functions.inc.php");
require("../../conf/db.const.inc.php");
require_once("../include/secure.functions.inc.php");

/***************** Sprachdatei ************************************************/
if (!isset($_REQUEST['lang']) || strlen(trim($_REQUEST['lang'])) == 0) 
{
    $lang = "deu";
} 
else 
{
	$lang = $_REQUEST['lang'];
	if(!file_exists("../lang/lang_".$lang.".php"))
  {
    $lang = "deu";
  }
}

include("../lang/lang_".$lang.".php");
/******************************************************************************/

/***************** Datenbankverbindung*****************************************/
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) 
  or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");
if(isset($_REQUEST['start'])) 
{
    $start = intval($_REQUEST['start']);
} else {
    die("error - missing post parameter");
}
if(isset($_REQUEST['cusIdNo'])) {
    $cusIdNo = intval($_REQUEST['cusIdNo']);
} else {
    die("error - missing post parameter");
}
if(isset($_REQUEST['act'])) {
    $act = trim($_REQUEST['act']);
} else {
    die("error - missing post parameter");
}

$chgApplicId = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1)); // script name

//------------------------------------------------------------------------------
//
// input validation
//
// needed parameters

if (!isset($_SESSION['SESS_userIdNo']) || strlen(trim($_SESSION['SESS_userIdNo'])) == 0) {
  die ("<br />error: missing session parameter!<br />");
} else {
	$SESS_userIdNo = $_SESSION['SESS_userIdNo'];
}
if (!isset($_SESSION['SESS_userLogin']) || strlen(trim($_SESSION['SESS_userLogin'])) == 0) {
  die ("<br />error: missing session parameter!<br />");
} else {
	$SESS_userId = $_SESSION['SESS_userId'];
}
if (!isset($_SESSION['SESS_languageIdNo']) || strlen(trim($_SESSION['SESS_languageIdNo'])) == 0) {
  die ("<br />error: missing session parameter!<br />");
} else {
	$SESS_languageIdNo = $_SESSION['SESS_languageIdNo'];
}


// create variables with the exact tab-column name
foreach($_REQUEST as $key => $value) 
{
//A UR 23.2.2011                        
    if($key == 'cusBirthdate')
    {
      $teile = explode('.',$value);
      $value = $teile[2]."-".$teile[1]."-".$teile[0];  
    }
//E UR                      
    $$key = trim($value);
}

// create random 4k data blob
$blob = "";
for($i = 0; $i < 4096; $i++) {
    srand((double)microtime()*1000000);
    $y = rand(1,2);
    if($y == 1) $cc = rand(48,57);
    if($y == 2) $cc = rand(65,90);
    $blob .= chr($cc);
}

if(strlen($cusBank) > 0) {
    gshide(strtoupper($cusBank), 'cusBank', $blob);
} else {
    $blob[getColumnLengthIndex('cusBank')] = "L";
}

if(strlen($cusBLZ) > 0) {
    gshide(strtoupper($cusBLZ), 'cusBLZ', $blob);
} else {
    $blob[getColumnLengthIndex('cusBLZ')] = "L";
}

if(strlen($cusAccountNo) > 0) {
    gshide(strtoupper($cusAccountNo), 'cusAccountNo', $blob);
} else {
    $blob[getColumnLengthIndex('cusAccountNo')] = "L";
}

if(strlen($cusAccountOwner) > 0) {
    gshide(strtoupper($cusAccountOwner), 'cusAccountOwner', $blob);
} else {
    $blob[getColumnLengthIndex('cusAccountOwner')] = "L";
}

if(strlen($cusCreditCard) > 0) {
    gshide(strtoupper($cusCreditCard), 'cusCreditCard', $blob);
} else {
    $blob[getColumnLengthIndex('cusCreditCard')] = "L";
}

if(strlen($cusCreditValidMonth) > 0) {
    gshide($cusCreditValidMonth, 'cusCreditValidMonth', $blob);
} else {
    $blob[getColumnLengthIndex('cusCreditValidMonth')] = "L";
}

if(strlen($cusCreditValidYear) > 0) {
    gshide($cusCreditValidYear, 'cusCreditValidYear', $blob);
} else {
    $blob[getColumnLengthIndex('cusCreditValidYear')] = "L";
}

if(strlen($cusCreditNo) > 0) {
    gshide(strtoupper($cusCreditNo), 'cusCreditNo', $blob);
} else {
    $blob[getColumnLengthIndex('cusCreditNo')] = "L";
}

if(strlen($cusCreditChk1) > 0) {
    gshide(strtoupper($cusCreditChk1), 'cusCreditChk1', $blob);
} else {
    $blob[getColumnLengthIndex('cusCreditChk1')] = "L";
}

if(strlen($cusCreditChk2) > 0) {
    gshide(strtoupper($cusCreditChk2), 'cusCreditChk2', $blob);
} else {
    $blob[getColumnLengthIndex('cusCreditChk2')] = "L";
}

if(strlen($cusCreditOwner) > 0) {
    gshide(strtoupper($cusCreditOwner), 'cusCreditOwner', $blob);
} else {
    $blob[getColumnLengthIndex('cusCreditOwner')] = "L";
}

if(strtolower($act) == "e") {
    $SQL = "UPDATE ".DBToken."customer SET 
                cusId = '".$cusId."',
                cusFirmname = '".$cusFirmname."',
                cusFirmVATId = '".$cusFirmVATId."',
                cusTitle = '".$cusTitle."',
                cusFirstName = '".$cusFirstName."',
                cusLastName = '".$cusLastName."',
                cusStreet = '".$cusStreet."',
                cusStreet2 = '".$cusStreet2."',
                cusZipCode = '".$cusZipCode."',
                cusCity = '".$cusCity."',
                cusCountry = '".$cusCountry."',
                cusPhone = '".$cusPhone."',
                cusFax = '".$cusFax."',
                cusMobil = '".$cusMobil."',
                cusBirthdate = '".$cusBirthdate."',
                cusEMail = '".$cusEMail."',
                cusEMailFormat = '".$cusEMailFormat."',
                cusPassword = '".$cusPassword."',
                cusDeliverFirmname = '".$cusDeliverFirmname."',
                cusDeliverTitle = '".$cusDeliverTitle."',
                cusDeliverFirstName = '".$cusDeliverFirstName."',
                cusDeliverLastName = '".$cusDeliverLastName."',
                cusDeliverStreet = '".$cusDeliverStreet."',
                cusDeliverStreet2 = '".$cusDeliverStreet2."',
                cusDeliverZipCode = '".$cusDeliverZipCode."',
                cusDeliverCity = '".$cusDeliverCity."',
                cusData = '".$blob."',
                cusChgUserIdNo = '".$SESS_userIdNo."',
                cusChgApplicId = '".$chgApplicId."',
                cusChgHistoryFlg = '1',
                cusDiscount = '".$cusDiscount."',
                cusCustomerNews = '".$cusCustomerNews."',
        				cusBonusPoints = '".$cusBonusPoints."',
        				cusBlocked = '".$cusBlocked."',
				        cusBlockedMessage = '".$cusBlockedMessage."'
            WHERE
                cusIdNo = '".$cusIdNo."'";
    $qry = @mysqli_query($link,$SQL);
}


if(strtolower($act) == "a") {
    $SQL = "INSERT INTO ".DBToken."customer (
                                        cusId,
                                        cusFirmname,
                                        cusTitle,
                                        cusFirstName,
                                        cusLastName,
                                        cusStreet,
                                        cusStreet2,
                                        cusZipCode,
                                        cusCity,
                                        cusCountry,
                                        cusPhone,
                                        cusFax,
                                        cusMobil,
                                        cusBirthdate,
                                        cusEMail,
                                        cusEMailFormat,
                                        cusPassword,
                                        cusDeliverFirmname,
                                        cusDeliverTitle,
                                        cusDeliverFirstName,
                                        cusDeliverLastName,
                                        cusDeliverStreet,
                                        cusDeliverStreet2,
                                        cusDeliverZipCode,
                                        cusDeliverCity,
                                        cusData,
                                        cusChgUserIdNo,
                                        cusChgApplicId,
                                        cusChgHistoryFlg,
                                        cusDiscount,
                                        cusCustomerNews,
										                    cusBonusPoints,
										                    cusBlocked,
										                    cusBlockedMessage
                                    ) VALUES (
                                        '".$cusId."',
                                        '".$cusFirmname."',
                                        '".$cusTitle."',
                                        '".$cusFirstName."',
                                        '".$cusLastName."',
                                        '".$cusStreet."',
                                        '".$cusStreet2."',
                                        '".$cusZipCode."',
                                        '".$cusCity."',
                                        '".$cusCountry."',
                                        '".$cusPhone."',
                                        '".$cusFax."',
                                        '".$cusMobil."',
                                        '".$cusBirthdate."',
                                        '".$cusEMail."',
                                        '".$cusEMailFormat."',
                                        '".$cusPassword."',
                                        '".$cusDeliverFirmname."',
                                        '".$cusDeliverTitle."',
                                        '".$cusDeliverFirstName."',
                                        '".$cusDeliverLastName."',
                                        '".$cusDeliverStreet."',
                                        '".$cusDeliverStreet2."',
                                        '".$cusDeliverZipCode."',
                                        '".$cusDeliverCity."',
                                        '".$blob."',
                                        '".$SESS_userIdNo."',
                                        '".$chgApplicId."',
                                        '2',
                                        '".$cusDiscount."',
                                        '".$cusCustomerNews."',
										                    '".$cusBonusPoints."',
										                    '".$cusBlocked."',
				                                '".$cusBlockedMessage."'
                                    )";
    $qry = @mysqli_query($link,$SQL);
}

header("Location: customer.search.php?start=".$start."&lang=".$lang);
die();
