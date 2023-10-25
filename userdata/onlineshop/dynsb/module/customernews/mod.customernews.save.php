<?php
/******************************************************************************/
/* File: mod.customernews.save.php                                            */
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
if(isset($_REQUEST['cnewsIdNo'])) {
    $cnewsIdNo = intval($_REQUEST['newsIdNo']);
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

foreach($_POST as $key => $value) 
{
    $$key = trim($value);
}

$aTmp = explode(".", $cnewsStartDate);
$cnewsStartDate = $aTmp[2].$aTmp[1].$aTmp[0]."000000";

$aTmp = explode(".", $cnewsEndDate);
$cnewsEndDate = $aTmp[2].$aTmp[1].$aTmp[0]."235959";

if(strtolower($act) == "e") 
{
    $SQL = "UPDATE ".DBToken."customernews SET 
                cnewsTitle = '".$cnewsTitle."',
                cnewsContent = '".$cnewsContent."',
                cnewsStartDate = '".$cnewsStartDate."',
                cnewsEndDate = '".$cnewsEndDate."',
                cnewsPicName = '".$cnewsPicName."',
                cnewsPicLink = '".$cnewsPicLink."',
                cnewsPicXSize = '".$cnewsPicXSize."',
                cnewsPicYSize = '".$cnewsPicYSize."',
                cnewsChgUserIdNo = '".$SESS_userIdNo."',
                cnewsChgApplicId = '".$chgApplicId."',
                cnewsForAll = '".$cnewsForAll."',
                cnewsChgHistoryFlg = '1'
            WHERE
                cnewsIdNo = '".$cnewsIdNo."'";
    $qry = @mysqli_query($link,$SQL);
    header("Location: mod.customernews.search.php?start=".$start."&lang=".$lang);
    die();
}


if(strtolower($act) == "a") 
{
    $SQL = "INSERT INTO ".DBToken."customernews (
                                        cnewsTitle,
                                        cnewsContent,
                                        cnewsStartDate,
                                        cnewsEndDate,
                                        cnewsPicName,
                                        cnewsPicLink,
                                        cnewsPicXSize,
                                        cnewsPicYSize,
                                        cnewsChgUserIdNo,
                                        cnewsChgApplicId,
                                        cnewsForAll,
                                        cnewsChgHistoryFlg
                                    ) VALUES (
                                        '".$cnewsTitle."',
                                        '".$cnewsContent."',
                                        '".$cnewsStartDate."',
                                        '".$cnewsEndDate."',
                                        '".$cnewsPicName."',
                                        '".$cnewsPicLink."',
                                        '".$cnewsPicXSize."',
                                        '".$cnewsPicYSize."',
                                        '".$SESS_userIdNo."',
                                        '".$chgApplicId."',
                                        '".$cnewsForAll."',
                                        '2'
                                    )";
    $qry = @mysqli_query($link,$SQL);
    $newsIdNo = @mysqli_insert_id($link);
    
    header("Location: mod.customernews.detail.php?pk=$newsIdNo&act=e&start=".$start."&lang=".$lang);
    die();
}

