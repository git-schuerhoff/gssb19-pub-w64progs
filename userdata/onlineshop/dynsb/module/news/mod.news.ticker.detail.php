<?php
/******************************************************************************/
/* File: mod.news.ticker.detail.php                                              */
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
}
if(isset($_REQUEST['pk'])) {
    $ntIdNo = intval($_REQUEST['pk']);
} else {
    $ntIdNo = 1;
}
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

if(strtolower($act) == "e")
{
    $qrySQL = "SELECT * FROM ".DBToken."newsticker WHERE ntIdNo = '".$ntIdNo."'";
    $qry = @mysqli_query($link,$qrySQL);
    $obj = @mysqli_fetch_object($qry);

    if($obj)
    {
        foreach($obj as $key => $value)
        {
            $$key = trim($value);
        }
    }
}

$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_Newsticker;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb" >
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
    <script type="text/javascript" src="../../js/gslib.php"></script>
	<script type="text/javascript" src="../../js/calendar.js"></script>
	<script type="text/javascript" src="../../js/calendar-<?php echo $strcal;?>.js"></script>
	<script type="text/javascript" src="../../js/calendar-setup.js"></script>
	<script language="JavaScript" type="text/javascript">
  function MM_reloadPage(init)
  {  //reloads the window if Nav4 resized
    if (init==true) with (navigator)
    {
      if ((appName=="Netscape")&&(parseInt(appVersion)==4))
      {
        document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage;
      }
    }
    else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
  }
  //----------------------------------------------------------------------------
  MM_reloadPage(true);
  </script>
</head>
<body>
<form name="frmNewsticker" action="mod.news.ticker.save.php" method="post">
<?php
require_once("../../include/page.header.php");
?>

<div id="PGnewstickerdetail">
	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="start" value="<?php echo $start;?>">
	<input type="hidden" name="ntIdNo" value="<?php echo $ntIdNo;?>">
	<input type="hidden" name="act" value="<?php echo $act;?>">

<h1>&#187;&nbsp;<?php echo L_dynsb_Newsticker;?>&nbsp;&#171;</h1>
<table style="width:auto;">

<tr>
  <td align="right"><?php echo L_dynsb_Activ;?>:&nbsp;</td>
  <td>
  	<select name="ntShowFlg">
      <option value="0" <?php if($ntShowFlg == 0) echo "selected";?>><?echo L_dynsb_No;?></option>
      <option value="1" <?php if($ntShowFlg == 1) echo "selected";?>><?echo L_dynsb_Yes;?></option>
  	</select>
  </td>
</tr>
<tr>
  <td align="right"><?php echo L_dynsb_ScrollSpeed;?>:&nbsp;</td>
  <td>
  	<input type="text" name="ntScrollSpeed" value="<?php echo intval($ntScrollSpeed);?>">
  </td>
</tr>

<tr>
  <th colspan="2">&nbsp;</th>
</tr>
<tr>
  <td valign="top" align="right"><?php echo L_dynsb_Content;?>:&nbsp;</td>
  <td><textarea rows="25" cols="80" name="ntContent"><?php echo trim($ntContent);?></textarea></td>
</tr>
</table>
<br />

<!-- navigation // -->
<div class="footer">
  <input type="button" class="button" onclick="javascript:submitForm('frmNewsticker');" name="btn_save" value="<?php echo L_dynsb_Save;?>">
</div>
</div>

<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
