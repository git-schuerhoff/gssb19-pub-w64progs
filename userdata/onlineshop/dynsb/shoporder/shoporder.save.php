<?php
/******************************************************************************/
/* File: shoporder.save.php                                                  */
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

if(isset($_REQUEST['ordIdNo'])) {
    $cusIdNo = intval($_REQUEST['ordIdNo']);
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
    if($key == 'ordInvoiceDate')
    {
      $teile = explode('.',$value);
      $value = $teile[2]."-".$teile[1]."-".$teile[0];  
    }
//E UR                      
    $$key = trim($value);
}

    $SQL = "UPDATE ".DBToken."order SET 
            ordInvoiceNumber = '".$ordInvoiceNumber."',
            ordInvoiceDate = '".date_german2mysql($ordInvoiceDate)."'
            WHERE ordIdNo = '".$ordIdNo."'";
    $qry = @mysqli_query($link,$SQL);
    
header('Location: shoporder.search.php?lang='.$_REQUEST['lang']);
?>