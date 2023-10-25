<?php
/******************************************************************************/
/* File: mod.faq.detail.php                                                   */
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
    $faqId = intval($_REQUEST['pk']);
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
    // start database query
    $qrySQL = "SELECT * FROM ".DBToken."faq WHERE faqId = '".$faqId."'";
    $qry = @mysqli_query($link,$qrySQL);
    $obj = @mysqli_fetch_object($qry);

    // create variables with the exact tab-column name
    foreach($obj as $key => $value)
    {
        $$key = trim($value);
    }
}

$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
  <title><?php echo L_dynsb_Faq;?></title>
  <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
  <meta content="de" http-equiv="Language">
  <meta name="author" content="GS Software Solutions GmbH">
  <link rel="stylesheet" type="text/css" href="../../css/link.css">
  <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb">
  <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
  <script type="text/javascript" src="../../js/gslib.php"></script>
	<script type="text/javascript" src="../../js/calendar.js"></script>
	<script type="text/javascript" src="../../js/calendar-<?php echo $strcal;?>.js"></script>
	<script type="text/javascript" src="../../js/calendar-setup.js"></script>
	<script type="text/javascript" src="../../js/text_format.js"></script>

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
  //----------------------------------------------------------------------------
  function uploadPic()
  {
    var iMyWidth;
    var iMyHeight;

    iMyWidth = Math.round((window.screen.width/2) - (400/2 + 10));
    iMyHeight = Math.round((window.screen.height/2) - (180/2 + 40));
    var winNew = window.open('mod.faq.select.file.php?nid='+document.frmGStempEdit.faqId.value+'&lang=<?echo $lang;?>', 'upload',"height=200,width=450,menubar=no,location=no,resizable=no,scrollbars=no,left="+iMyWidth+",top="+iMyHeight+"");
    winNew.focus();
  }
  </script>
</head>
<body>
<form name="frmGStempEdit" action="mod.faq.save.php" method="post">
<?php
require_once("../../include/page.header.php");
?>

<div id="PGfaqdetail">
	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="start" value="<?php echo $start;?>">
	<input type="hidden" name="faqId" value="<?php echo $faqId;?>">
	<input type="hidden" name="act" value="<?php echo $act;?>">


<h1>&#187;&nbsp;<?php echo L_dynsb_Faq;?>&nbsp;&#171;</h1>

<table>
  <tr>
    <td align="right"><?php echo L_dynsb_Headline;?>:*&nbsp;</td>
    <td>
      <input class="larger" type="text" maxlength="255" value="<?php echo $faqTitle; ?>" name="faqTitle">
    </td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Subtitle;?>:&nbsp;</td>
    <td>
      <input class="larger" type="text" maxlength="400" value="<?php echo $faqSubtitle; ?>" name="faqSubtitle">
    </td>
  </tr>

  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right">&nbsp;</td>
    <td>
      <div id='FormatButtons' style='display:block'>
        <button type="button" class="button small" onclick='setFormat("b")' title='<?php echo L_dynsb_Bold;?>'><img src='../../image/bold.gif' alt="bold"></button>&nbsp;
        <button type="button" class="button small" onclick='setFormat("i")' title='<?php echo L_dynsb_Italic;?>'><img src='../../image/italic.gif' alt="italic"></button>&nbsp;
        <button type="button" class="button small" onclick='setFormat("s")' title='<?php echo L_dynsb_StrikeThrough;?>'><img src='../../image/strike.gif' alt="strike"></button>&nbsp;
        <button type="button" class="button small" onclick='setFormat("u")' title='<?php echo L_dynsb_Underline;?>'><img src='../../image/underline.gif' alt="underline"></button>&nbsp;
        <button type="button" class="button small" onclick='setFormat("h1")' title='<?php echo L_dynsb_Heading1;?>'><img src='../../image/h1.gif' alt="h1"></button>&nbsp;
        <button type="button" class="button small" onclick='setFormat("h2")' title='<?php echo L_dynsb_Heading2;?>'><img src='../../image/h2.gif' alt="h2"></button>&nbsp;
        <button type="button" class="button small" onclick='setFormat("h3")' title='<?php echo L_dynsb_Heading3;?>'><img src='../../image/h3.gif' alt="h3"></button>&nbsp;
        <button type="button" class="button small" onclick='setFormat("h4")' title='<?php echo L_dynsb_Heading4;?>'><img src='../../image/h4.gif' alt="h4"></button>&nbsp;
        <button type="button" class="button small" onclick='setFormat("h5")' title='<?php echo L_dynsb_Heading5;?>'><img src='../../image/h5.gif' alt="h5"></button>&nbsp;
        <button type="button" class="button small" onclick='setFormat("hr")'  title='<?php echo L_dynsb_HorizontalRule;?>'><img src='../../image/hr.gif' alt="hr"></button>&nbsp;
        <button type="button" class="button small" onclick='setFormat("email")' title='<?php echo L_dynsb_EmailLink;?>'><img src='../../image/mail.gif' alt="mail"></button>&nbsp;
        <button type="button" class="button small" onclick='setFormat("link")' title='<?php echo L_dynsb_Link;?>'><img src='../../image/link.gif' alt="link"></button>
      </div>
    </td>
  </tr>
  <tr>
    <td valign="top" align="right"><?php echo L_dynsb_Content;?>*&nbsp;</td>
    <td>
      <textarea rows="25" cols="80" name="tempContent" id="conthtml"><?php echo trim($faqText);?></textarea>
    </td>
  </tr>
  <?php
  if ($act=="e")
  {
  ?>
  <tr>
    <td align="right">&nbsp;</td>
    <td><?php echo L_dynsb_RightsErrorImageUpload;?></td>
  </tr>

  <tr>
    <td align="right"><?php echo L_dynsb_FileName;?>:&nbsp;</td>
    <td>
      <input type="text" maxlength="96" value="<?php echo $faqImage;?>" name="faqImage" readonly>&nbsp;
      <input type="button" class="button" onclick="javascript:uploadPic();" name="btn_upload" value="<?php echo L_dynsb_UploadImage;?>">
    </td>
  </tr>

  <tr>
    <td align="right"><?php echo L_dynsb_ImageSizeX;?>:&nbsp;</td>
    <td><input type="text" maxlength="3" value="<?php echo $faqImageXSize;?>" name="faqImageXSize">&nbsp;Pixel</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_ImageSizeY;?>:&nbsp;</td>
    <td><input type="text" maxlength="3" value="<?php echo $faqImageYSize;?>" name="faqImageYSize">&nbsp;Pixel</td>
  </tr>
  <?php
  }
  else
  {
  ?>
  <tr>
    <td align="right">&nbsp;</td>
    <td><?php echo L_dynsb_ImageUploadNextStep;?></td>
  </tr>
  <?php
  }
  ?>
</table>



<!-- navigation // -->

<div class="footer">
  <input type="button" class="button" onclick="javascript:submitForm('frmGStempEdit');" name="btn_save" value="<?php echo L_dynsb_Save;?>">
  <input type="button" class="button" onclick="javascript:this.location.href='mod.faq.search.php?start=<?php echo $start;?>&lang=<?echo $lang;?>';" name="btn_save" value="<?php echo L_dynsb_Cancel;?>">
</div>

</div>
<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
