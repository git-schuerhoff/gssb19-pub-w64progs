<?php
/******************************************************************************/
/* File: mod.newsfeed.save.php                                                  */
/******************************************************************************/

require("../../include/login.check.inc.php");
require_once("../../include/functions.inc.php");
require("../../../conf/db.const.inc.php");
require_once("../../include/secure.functions.inc.php");

/***************** Sprachdatei ************************************************/
if (!isset($_REQUEST['lang']) || strlen(trim($_REQUEST['lang'])) == 0) 
{
    $lang = "deu";
} 
else 
{
	$lang = $_REQUEST['lang'];
	if(!file_exists("../../lang/lang_".$lang.".php"))
  {
    $lang = "deu";
  }
}

include("../../lang/lang_".$lang.".php");
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

if(isset($_REQUEST['act'])) {
    $act = trim($_REQUEST['act']);
} else {
    die("error - missing post parameter");
}

if($act=="e")
{ 
  if(isset($_REQUEST['nfIdNo'])) 
  {
    $nfIdNo = intval($_REQUEST['nfIdNo']);
  } else {
    die("error - missing post parameter");
  }
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
    $$key = trim($value);    
}

/*
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
    gshide(strtoupper($cusBank), 'cusBank', &$blob);
} else {
    $blob[getColumnLengthIndex('cusBank')] = "L";
}

if(strlen($cusBLZ) > 0) {
    gshide(strtoupper($cusBLZ), 'cusBLZ', &$blob);
} else {
    $blob[getColumnLengthIndex('cusBLZ')] = "L";
}

if(strlen($cusAccountNo) > 0) {
    gshide(strtoupper($cusAccountNo), 'cusAccountNo', &$blob);
} else {
    $blob[getColumnLengthIndex('cusAccountNo')] = "L";
}

if(strlen($cusAccountOwner) > 0) {
    gshide(strtoupper($cusAccountOwner), 'cusAccountOwner', &$blob);
} else {
    $blob[getColumnLengthIndex('cusAccountOwner')] = "L";
}

if(strlen($cusCreditCard) > 0) {
    gshide(strtoupper($cusCreditCard), 'cusCreditCard', &$blob);
} else {
    $blob[getColumnLengthIndex('cusCreditCard')] = "L";
}

if(strlen($cusCreditValidMonth) > 0) {
    gshide($cusCreditValidMonth, 'cusCreditValidMonth', &$blob);
} else {
    $blob[getColumnLengthIndex('cusCreditValidMonth')] = "L";
}

if(strlen($cusCreditValidYear) > 0) {
    gshide($cusCreditValidYear, 'cusCreditValidYear', &$blob);
} else {
    $blob[getColumnLengthIndex('cusCreditValidYear')] = "L";
}

if(strlen($cusCreditNo) > 0) {
    gshide(strtoupper($cusCreditNo), 'cusCreditNo', &$blob);
} else {
    $blob[getColumnLengthIndex('cusCreditNo')] = "L";
}

if(strlen($cusCreditChk1) > 0) {
    gshide(strtoupper($cusCreditChk1), 'cusCreditChk1', &$blob);
} else {
    $blob[getColumnLengthIndex('cusCreditChk1')] = "L";
}

if(strlen($cusCreditChk2) > 0) {
    gshide(strtoupper($cusCreditChk2), 'cusCreditChk2', &$blob);
} else {
    $blob[getColumnLengthIndex('cusCreditChk2')] = "L";
}

if(strlen($cusCreditOwner) > 0) {
    gshide(strtoupper($cusCreditOwner), 'cusCreditOwner', &$blob);
} else {
    $blob[getColumnLengthIndex('cusCreditOwner')] = "L";
}
*/

if(strtolower($act) == "e") {

    if($nfTitle=="")
      die("L_ Title darf nicht leer sein");
    
    $SQL = "UPDATE ".DBToken."newsfeed SET                
                nfTitle = '".$nfTitle."',
                nfDescription = '".$nfDescription."',
                nfLink = '".$nfLink."',
                nfChgUserIdNo = '".$SESS_userIdNo."',
                nfChgApplicId = '".$chgApplicId."',
                nfChgHistoryFlg = '1',
                nfDurationdays = '".$nfDurationdays."'  
            WHERE
                nfIdNo = '".$nfIdNo."'";
    $qry = @mysqli_query($link,$SQL);
}


if(strtolower($act) == "a") {
    
    if($nfTitle=="")
      die("L_ Title darf nicht leer sein");
      
    $SQL = "INSERT INTO ".DBToken."newsfeed (
                                        nfTitle,
                                        nfDescription,
                                        nfLink,                                        
                                        nfChgUserIdNo,
                                        nfChgApplicId,
                                        nfChgHistoryFlg,
                                        nfDurationdays
                                    ) VALUES (
                                        '".$nfTitle."',
                                        '".$nfDescription."',
                                        '".$nfLink."',                                                                                
                                        '".$SESS_userIdNo."',
                                        '".$chgApplicId."',
                                        '2',
                                        '".$nfDurationdays."'
                                    )";    
    $qry = @mysqli_query($link,$SQL);
}

header("Location: mod.newsfeed.search.php?start=".$start."&lang=".$lang);
die();
