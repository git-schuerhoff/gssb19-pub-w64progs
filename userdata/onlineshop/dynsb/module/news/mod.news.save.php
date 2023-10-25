<?php
/******************************************************************************/
/* File: mod.news.save.php                                                    */
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
if(isset($_REQUEST['newsIdNo'])) {
    $newsIdNo = intval($_REQUEST['newsIdNo']);
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

$aTmp = explode(".", $newsStartDate);
$newsStartDate = $aTmp[2].$aTmp[1].$aTmp[0]."000000";

$aTmp = explode(".", $newsEndDate);
$newsEndDate = $aTmp[2].$aTmp[1].$aTmp[0]."235959";

if(strtolower($act) == "e") 
{
    $SQL = "UPDATE ".DBToken."news SET 
                newsSortIndex = '".intval($newsSortIndex)."',
                newsTitle = '".$newsTitle."',
                newsContent = '".$newsContent."',
                newsStartDate = '".$newsStartDate."',
                newsEndDate = '".$newsEndDate."',
                newsPicLink = '".$newsPicLink."',
                newsPicXSize = '".$newsPicXSize."',
                newsPicYSize = '".$newsPicYSize."',
                newsChgUserIdNo = '".$SESS_userIdNo."',
                newsChgApplicId = '".$chgApplicId."',
                newsChgHistoryFlg = '1'
            WHERE
                newsIdNo = '".$newsIdNo."'";
    $qry = @mysqli_query($link,$SQL);
    
    header("Location: mod.news.search.php?start=".$start."&lang=".$lang);
    die();
}


if(strtolower($act) == "a") 
{
    $SQL = "INSERT INTO ".DBToken."news (
                                        newsSortIndex,
                                        newsTitle,
                                        newsContent,
                                        newsStartDate,
                                        newsEndDate,
                                        newsPicLink,
                                        newsPicXSize,
                                        newsPicYSize,
                                        newsChgUserIdNo,
                                        newsChgApplicId,
                                        newsChgHistoryFlg
                                    ) VALUES (
                                        '".intval($newsSortIndex)."',
                                        '".$newsTitle."',
                                        '".$newsContent."',
                                        '".$newsStartDate."',
                                        '".$newsEndDate."',
                                        '".$newsPicLink."',
                                        '".$newsPicXSize."',
                                        '".$newsPicYSize."',
                                        '".$SESS_userIdNo."',
                                        '".$chgApplicId."',
                                        '2'
                                    )";
    $qry = @mysqli_query($link,$SQL);
    
    $newsIdNo = @mysqli_insert_id($link);
    
    header("Location: mod.news.detail.php?pk=$newsIdNo&act=e&start=".$start."&lang=".$lang);
    die();
}

