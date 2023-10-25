<?php
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
  or die("<br />aborted: can't connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");
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
$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";

//*****************************************************************************
//*****************************************************************************
//*****************************************************************************

//if id is given, get details from database
if(isset($_SESSION["nlIdNo"])) {
  $nldoIdNo = $_SESSION["nlIdNo"];

	//*** GET DOCUMENT ***
  $qryNlDoc = "SELECT * FROM ".DBToken."nl_documents WHERE nldoIdNo = '$nldoIdNo'";
  $resNlDoc = @mysqli_query($link,$qryNlDoc);

  $obj = mysqli_fetch_object($resNlDoc);

  if (is_object($obj)) {
    $nlSubject		= $obj->nldoSubject;
    $nlContText		= stripslashes($obj->nldoContentText);
    $nlContHtml		= stripslashes($obj->nldoContentHtml);
    $nlChangeDate	= date_mysql2german($obj->nldoChangeDate);
    $nlSendDate		= date_mysql2german($obj->nldoSendDate);
		$statusFlg		= $obj->nldoStatusFlg;
  }
}

//if statusflg is empty -> 0
$statusFlg = intval($statusFlg);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
  <title><?php echo L_dynsb_Newsletter;?></title>
  <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
  <meta content="de" http-equiv="Language">
  <meta name="author" content="GS Software Solutions GmbH">
  <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
</head>
<body ondblclick="javascript:window.close();" style="background-color: #EEE;">
<p>
 <input type="button" onclick="javascript:window.close();" value="<?php echo L_dynsb_Close;?>">
</p>

<div style="background-color: #000; color: #FFF; padding: 3px; font-weight:bold; text-align:center;">
	Text
</div>
<pre style="border: 1px solid #000;padding: 5px; margin:0px; background-color: #FFF;"><?php echo $nlContText;?>
</pre>

<br />

<div style="background-color: #000; color: #FFF; padding: 3px; font-weight:bold; text-align:center;">
HTML
</div>
<div style="border: 1px solid #000;padding: 5px; background-color: #FFF;"">
	<?php echo $nlContHtml;?>
</div>

<p>
<input type="button" onclick="javascript:window.close();" value="<?php echo L_dynsb_Close;?>">
</p>
</body>
</html>
