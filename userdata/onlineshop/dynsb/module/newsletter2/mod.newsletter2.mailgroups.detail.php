<?php
/******************************************************************************/
/* File: mod.customernews.detail.php                                          */
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
if(isset($_REQUEST['pk']) && is_numeric($_REQUEST['pk']))
  $_SESSION["nlmgIdNo"] = intval($_REQUEST['pk']);

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
//*****************************************************************************
$timestamp = date("Y-m-d H:i:s");

//**** INSERT a new Mailgroup *****
if (empty($_SESSION["nlmgIdNo"]) && isset($_REQUEST["btnsave"])) {
	$name = strip_tags(addslashes($_REQUEST["name"]));
	$desc	= strip_tags(addslashes($_REQUEST["desc"]));

  $insNlDoc = "INSERT INTO ".DBToken."nl_mailgroups" .
              "	(nlmgName, nlmgDesc)" .
              " VALUES " .
              "	('$name', '$desc')"
              ;
  @mysqli_query($link,$insNlDoc);

  $insIdNo = mysqli_insert_id($link);
  if ($insIdNo)
    $_SESSION["nlmgIdNo"] = $insIdNo;
}
//********************************

//if id is given, get details from database
if(isset($_SESSION["nlmgIdNo"])) {
  $nlmgIdNo = $_SESSION["nlmgIdNo"];

  //*** UPDATE ***
  if (isset($_REQUEST["btnsave"])) {
		$name = strip_tags(addslashes($_REQUEST["name"]));
		$desc	= strip_tags(addslashes($_REQUEST["desc"]));

    $updMg = "UPDATE ".DBToken."nl_mailgroups " .
                "		SET nlmgName = '$name'" .
                "			, nlmgDesc = '$desc'" .
                "	WHERE nlmgIdNo = '$nlmgIdNo' "
                ;
    if (@mysqli_query($link,$updMg))
    	$noticeText = L_dynsb_SavedNotice;
  }
	//*** END UPDATE ***

	//*** GET DOCUMENT ***
  $qryMg = "SELECT * FROM ".DBToken."nl_mailgroups WHERE nlmgIdNo = '$nlmgIdNo'";
  $resMg = @mysqli_query($link,$qryMg);

  $obj = mysqli_fetch_object($resMg);

  if (is_object($obj)) {
		$mgIdNo	= $obj->nlmgIdNo;
		$mgName	= $obj->nlmgName;
		$mgDesc	= $obj->nlmgDesc;
  }
}

//if statusflg is empty -> 0
$statusFlg = intval($statusFlg);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
  <title><?php echo L_dynsb_MailingGroups;?></title>
  <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
  <meta content="de" http-equiv="Language">
  <meta name="author" content="GS Software Solutions GmbH">
  <link rel="stylesheet" type="text/css" href="../../css/link.css">
  <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb">
  <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
  <script type="text/javascript" src="../../js/gslib.php"></script>
</head>
<body>
<form name="frmCnews" action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
<?php
require_once("../../include/page.header.php");
?>

<div id="PGnewsletterdetail">
  <input type="hidden" name="lang" value="<?php echo $lang;?>">

<h1>&#187;&nbsp;<?php echo L_dynsb_MailingGroups;?> - <?php echo L_dynsb_Edit;?>&#171;</h1>

<?php //notice
if (!empty($noticeText)) {
?>
	<p class="notice">
		<?php echo $noticeText;?>
	</p>
<?php	}	?>


<table>
 <tr>
 	<td style="width:120px;">
	 <?php echo L_dynsb_Name;?>:
	</td>
 	<td>
	 <input type="text" name="name" class="xx-large" value="<?php echo $mgName;?>" maxlength="150">
	</td>
</tr>
<tr>
 	<td>
		<?php echo L_dynsb_Description2?>:
	</td>
 	<td>
  	<input type="text" name="desc" class="xx-large" value="<?php echo $mgDesc?>" maxlength="150">
	</td>
</tr>
</table>
<br />
<div class="footer">
  <input type="submit" class="button" name="btnsave" value="<?php echo L_dynsb_Save;?>">
  <input type="button" class="button" value="<?php echo L_dynsb_Back?>" onclick="javascript:self.location.href='mod.newsletter2.mailgroups.php?status=<?php echo $statusFlg;?>'">
</div>
</div>
<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
