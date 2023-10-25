<?php
/**
 * mod.newsletter.send.php -  asscociate newsletter with mailgroups and send it afterwards
 *
 */

require("../../include/login.check.inc.php");
require_once("../../include/functions.inc.php");
require("../../../conf/db.const.inc.php");
require("../../class/class.mailservice.php");

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
/******************************************************************************/

if(isset($_REQUEST['pk']) && is_numeric($_REQUEST['pk']))
  $_SESSION["nlIdNo"] = intval($_REQUEST['pk']);


if(isset($_REQUEST['act'])) {
    $act = trim($_REQUEST['act']);
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
$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";

//*****************************************************************************
//*****************************************************************************

if (isset($_REQUEST["btnsave"])) {
	$sender				= $_REQUEST["sender"];
	$pause				= $_REQUEST["pause"];
	$limit				= $_REQUEST["limit"];

	$multiplemg	  = intval($_REQUEST["multiplemg"]);
	$doubleoptin  = intval($_REQUEST["duobleoptin"]);

	$actMailSubj	= $_REQUEST["actmailsubj"];
	$actMailBody	= $_REQUEST["actmailbody"];

	$updSet = "UPDATE ".DBToken."nl_settings " .
			      "   SET nlseSenderAddress='$sender'" .
			      "     , nlsePause='$pause'" .
			      "     , nlseLimit='$limit' " .
			      "     , nlseMultipleMg='$multiplemg' " .
			      "     , nlseDoubleOptIn='$doubleoptin' " .
			      "     , nlseActMailSubj='$actMailSubj' " .
			      "     , nlseActMailBody='$actMailBody' " .
			      "  WHERE nlseIdNo=1";

	if (@mysqli_query($link,$updSet))
		$noticeText = L_dynsb_SavedNotice;
}

$qrySet = "SELECT * FROM ".DBToken."nl_settings";
$resSet = @mysqli_query($link,$qrySet);
$rowSet = mysqli_fetch_array($resSet);

$nlSender 			= $rowSet["nlseSenderAddress"];
$nlPause				= $rowSet["nlsePause"];
$nlLimit 				= $rowSet["nlseLimit"];

$nlMultipleMg		= $rowSet["nlseMultipleMg"];
$nlDoubleOptIn	= $rowSet["nlseDoubleOptIn"];

$nlActMailSubj 	= $rowSet["nlseActMailSubj"];
$nlActMailBody 	= $rowSet["nlseActMailBody"];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
  <title><?php echo L_dynsb_CustomerNews;?></title>
  <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
  <meta content="de" http-equiv="Language">
  <meta name="author" content="GS Software Solutions GmbH">
  <link rel="stylesheet" type="text/css" href="../../css/link.css">
  <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
  <script type="text/javascript" src="../../js/gslib.php"></script>
</head>
<body>
<form name="frmNlSettings" action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
<?php
require_once("../../include/page.header.php");
?>

<div id="PGnewslettersettings">
  <input type="hidden" name="lang" value="<?php echo $lang;?>">
	<h1><?php echo L_dynsb_NewsletterSettings;?></h1>

<?php
if (!empty($noticeText)) {
?>
	<p class="notice">
		<?php echo $noticeText;?>
	</p>
<?php	}	?>

	<table>
		<tr>
			<th colspan="2" align="left"><?php echo L_dynsb_general;?></th>
		</tr>
		
		<tr>
			<td style="width: 180px;"></td>
			<td><?php echo L_dynsb_newsletternoticetext;?></td>
		</tr>
		
		<tr>
			<td style="width: 180px;"><?php echo L_dynsb_Sender;?>:</td>
			<td><input type="text" name="sender" value="<?php echo $nlSender;?>" style="width:200px"></td>
		</tr>

		<tr>
			<td><?php echo L_dynsb_SendingLimit;?>:</td>
			<td><input type="text" name="limit" value="<?php echo $nlLimit;?>" style="width:15px"></td>
		</tr>

		<tr>
			<td><?php echo L_dynsb_Timeout;?>:</td>
			<td><input type="text" name="pause" value="<?php echo $nlPause;?>" style="width:15px"></td>
		</tr>

		<tr>
			<td><?php echo L_dynsb_multiplemailgr;?>:</td>
			<td><input type="checkbox" class="checkbox" name="multiplemg" value="1" <?php if($nlMultipleMg == "1") echo " checked "?>></td>
		</tr>

		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<th colspan="2" align="left"><?php echo L_dynsb_activationmail;?> (Double Opt-In)</th>
		</tr>

		<tr>
			<td><?php echo L_dynsb_activationmail;?>:</td>
			<td><input type="checkbox" class="checkbox" name="duobleoptin" value="1" <?php if($nlDoubleOptIn == "1") echo " checked "?>></td>
		</tr>


		<tr>
			<td><?php echo L_dynsb_Subject;?>:</td>
			<td><input type="text" class="xx-large" name="actmailsubj" value="<?php echo $nlActMailSubj;?>"></td>
		</tr>

		<tr>
			<td valign="top"><?php echo L_dynsb_Content;?>:</td>
			<td><textarea cols="80" rows="20" name="actmailbody"><?php echo $nlActMailBody;?></textarea></td>
		</tr>
	</table>
<br />

	<div class="footer">
	  <input type="submit" class="button" name="btnsave" value="<?php echo L_dynsb_Save;?>">
	</div>

</div>
<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
