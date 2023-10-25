<?php
/******************************************************************************/
/* File: mod.coupon.save.php                                                  */
/******************************************************************************/

require("../../include/login.check.inc.php");
require_once("../../include/functions.inc.php");
require("../../../conf/db.const.inc.php");

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
if(isset($_REQUEST['act'])) {
    $act = trim($_REQUEST['act']);
} else {
    die("error - missing post parameter");
}

$chgApplicId = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1)); // script name

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

foreach($_REQUEST as $key => $value) 
{
    $$key = trim($value);
}

$coupCount = $_REQUEST['coupCount'];

if(strtolower($act) == "a") 
{
  for($i=0; $i<$coupCount; $i++)
  {
    $code = "";
    for($j=0; $j<12; $j++) 
    {
      srand((double)microtime()*1000000);
      $y = rand(1,2);
      if($y == 1) $cc = rand(48,57);
      if($y == 2) $cc = rand(65,90);
      $code .= chr($cc);
    }
    $coupCreatedate = date('YmdHis');
    $SQL = "INSERT INTO ".DBToken."coupon(coupCode, coupCurrency,coupPrice, coupUsed, coupAssigned, coupCreatedate, coupValid) VALUES('".$code."','".$coupCurrency."','-".$coupPrice."','0','0', $coupCreatedate,'".$valid."');";
    $qry = @mysqli_query($link,$SQL);    
  } 
}

header("Location: mod.coupon.search.php?start=".$start."&lang=".$lang);
die();
