<?php
/******************************************************************************/
/* File: mod.orderemail.save.php                                              */
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
if(isset($_REQUEST['start'])) {
    $start = intval($_REQUEST['start']);
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
if(strtolower($act) == "a") 
{
    $SQL = "UPDATE ".DBToken."settings SET 
                ordEmailBground = '".$bground."',
                ordEmailText = '".$text."',
                ordEmailTitle = '".$title."',
                ordEmailTabheadBg = '".$tabhead_bg."',
                ordEmailTabheadText = '".$tabhead_text."',
                ordEmailTabbodyBg = '".$tabbody_bg."',
                ordEmailTabbodyText = '".$tabbody_text."',
                ordEmailTabBorder = '".$tab_border."'
            WHERE setIdNo = '1'";
    $qry = @mysqli_query($link,$SQL);
    header("Location: mod.orderemail.detail.php?act=e&lang=".$lang);
    die();
}

if(strtolower($act) == "e") 
{
    $SQL = "UPDATE ".DBToken."settings SET 
                ordEmailBground = '".$bground."',
                ordEmailText = '".$text."',
                ordEmailTitle = '".$title."',
                ordEmailTabheadBg = '".$tabhead_bg."',
                ordEmailTabheadText = '".$tabhead_text."',
                ordEmailTabbodyBg = '".$tabbody_bg."',
                ordEmailTabbodyText = '".$tabbody_text."',
                ordEmailTabBorder = '".$tab_border."',
                ordEmailImage = '".$Image."',
                ordEmailImageXsize = '".$ImageXsize."',
                ordEmailImageYsize = '".$ImageYsize."'
            WHERE setIdNo = '1'";
    $qry = @mysqli_query($link,$SQL);
    header("Location: mod.orderemail.detail.php?act=b&lang=".$lang);
    die();
}
?>
