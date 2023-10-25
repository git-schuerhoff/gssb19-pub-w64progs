<?php

/*
 file: mod.carrier.save.php
*/

require("../include/login.check.inc.php");
require_once("../include/functions.inc.php");
require("../../conf/db.const.inc.php");

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

// connect to database server or die
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");
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


$sql = "UPDATE ".DBToken."settings SET lpFirmname='".$Firmname."',lpSalutation='"
      .$Salutation."',lpFirstname='".$FirstName."',lpLastname='"
      .$LastName."',lpStreet='".$Street."',lpAddress='".$Address."',lpZipCode='"
      .$ZipCode."',lpCity='".$City."',lpCountry='".$Country."',lpPhone='".$Phone."'";

$qry = @mysqli_query($link,$sql);


//header("Location: mod.allowance.detail.php?start=".$start);
header("Location: mod.carrier.detail.php?lang=".$lang);
die();
