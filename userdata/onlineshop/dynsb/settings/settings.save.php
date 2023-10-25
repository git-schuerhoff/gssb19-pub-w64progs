<?php
/******************************************************************************/
/* File: settings.save.php                                                    */
/******************************************************************************/

require("../include/login.check.inc.php");
require_once("../include/functions.inc.php");
require("../../conf/db.const.inc.php");

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
if(isset($_REQUEST['setIdNo'])) {
    $setIdNo = intval($_REQUEST['setIdNo']);
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


$emailBlockedMessage = $_REQUEST['emailBlockedMessage'];
$blacklist_email_regexp = $_REQUEST['blacklist_email_regexp'];
$arrBlackMail = explode("\n", $blacklist_email_regexp); 

$SQL = "DELETE FROM ".DBToken."black_email_list WHERE blackType=1 OR blackType=2";
    $qry = @mysqli_query($link, $SQL);

$SQL = "INSERT INTO ".DBToken."black_email_list ( blackType, blackValues)";
$SQL .= " VALUES (1,'".$emailBlockedMessage."')";
$qry = @mysqli_query($link, $SQL);

foreach($arrBlackMail as $reg_black_mail)
{
  if (strlen(trim($reg_black_mail)) > 0)
  {
    $SQL = "INSERT INTO ".DBToken."black_email_list ( blackType, blackValues)";
    $SQL .= " VALUES (2,'".$reg_black_mail."')";
    $qry = @mysqli_query($link, $SQL);
  }
}

if(strtolower($act) == "e") 
{
    $SQL = "UPDATE ".DBToken."settings SET 
                setRowCount = '".intval($setRowCount)."',
                setDefaultLanguageIdNo = '".$setDefaultLanguageIdNo."',
                setBestsellerCount = '".$setBestsellerCount."',
                setBestsellerPgCount = '".$setBestsellerPgCount."',
                setLastOrderCount = '".$setLastOrderCount."',
                setAutoCrossSellingCount = '".$setAutoCrossSellingCount."',
                
                useOrdOptAutoCross ='".$useOrdOptAutoCross."',
                useOrdOptMain ='".$useOrdOptMain."',
                useOrdOptBestseller = '".$useOrdOptBestseller."',
                useOrdOptBestsellerPg = '".$useOrdOptBestsellerPg."',
                useOrdOptLastViewed = '".$useOrdOptLastViewed."',
                useFormatMailAddress ='".$useFormatMailAddress."',
                secFieldReqCredCard ='".$secFieldReqCredCard."',
				setSaveIp = '".$setSaveIP."',
                reviewLinksInEmail ='".$reviewLinksInEmail."',
                customerreturningticket_adress = '".$customerreturningticket_adress."'
            WHERE
                setIdNo = '".$setIdNo."'";
    $qry = @mysqli_query($link, $SQL);
}

header("Location: settings.detail.php?act=e&lang=".$lang);
die();
