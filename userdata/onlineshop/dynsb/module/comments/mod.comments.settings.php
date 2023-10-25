<?php
/**
 * mod.newsletter.send.php -  asscociate newsletter with mailgroups and send it afterwards
 *
 */

require_once("../../include/login.check.inc.php");
require_once("../../include/functions.inc.php");
require_once("../../../conf/db.const.inc.php");
require_once("class.commentsettings.php");
//SS20010304
require_once("class.shopcommentsettings.php");

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

$oCS = new CommentSettings();
$oSCS = new ShopcommentSettings();

if (isset($_REQUEST["btnsave"])) {
	$v = (int) $_POST['visdef'];
	$oCS->setVisibilityDefault($v);
	$shopv = (int) $_POST['shopvisdef'];
	$oSCS->setVisibilityDefault($shopv);
	if ($oCS->save() && $oSCS->save())
		$noticeText = L_dynsb_SavedNotice;
}

$visDef = $oCS->getVisibilityDefault();
$shopvisDef = $oSCS->getVisibilityDefault();
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
	<h1><?php echo L_dynsb_ModuleComments." - ".L_dynsb_Settings;?></h1>
	
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
			<td style="width: 250px;"><?php echo L_dynsb_CommentsSetVisible;?>:</td>
			<td><input type="checkbox" class="checkbox" name="visdef" value="1" <?php if($visDef == "1") echo " checked "?>></td>
		</tr>
		
		<tr>
			<td style="width: 250px;"><?php echo L_dynsb_CommentsSetVisible_Shop?>:</td>
			<td><input type="checkbox" class="checkbox" name="shopvisdef" value="1" <?php if($shopvisDef == "1") echo " checked "?>></td>
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
