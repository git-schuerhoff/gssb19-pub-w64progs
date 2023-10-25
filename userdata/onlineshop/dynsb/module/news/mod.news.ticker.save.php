<?php
/******************************************************************************/
/* File: mod.news.ticker.save.php                                             */
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
if(isset($_REQUEST['ntIdNo'])) {
    $ntIdNo = intval($_REQUEST['ntIdNo']);
} else {
    die("error - missing post parameter");
}
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

if(strtolower($act) == "e") 
{
    $SQL = "UPDATE ".DBToken."newsticker SET 
                ntContent = '".$ntContent."',
                ntShowFlg = '".intval($ntShowFlg)."',
                ntScrollSpeed = '".intval($ntScrollSpeed)."',
                ntChgUserIdNo = '".$SESS_userIdNo."',
                ntChgApplicId = '".$chgApplicId."',
                ntChgHistoryFlg = '1'
            WHERE
                ntIdNo = '".$ntIdNo."'";
    $qry = @mysqli_query($link,$SQL);
}

header("Location: mod.news.search.php?start=".$start."&lang=".$lang);
die();
