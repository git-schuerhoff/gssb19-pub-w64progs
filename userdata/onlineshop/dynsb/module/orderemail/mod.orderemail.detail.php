<?php
/******************************************************************************/
/* File: mod.orderemail.detail.php                                            */
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
    $cnewsIdNo = intval($_REQUEST['pk']);
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

    $qrySQL = "SELECT * FROM ".DBToken."settings";

    $qry = @mysqli_query($link,$qrySQL);
    $obj = @mysqli_fetch_object($qry);

    foreach($obj as $key => $value)
    {
        $$key = trim($value);
    }

$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_OrderEmail;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
    <script type="text/javascript" src="../../js/gslib.php"></script>
    <script language="JavaScript" type="text/javascript">
    function uploadPic()
    {
      var iMyWidth;
      var iMyHeight;

      iMyWidth = Math.round((window.screen.width/2) - (400/2 + 10));
      iMyHeight = Math.round((window.screen.height/2) - (180/2 + 40));
      var winNew = window.open('mod.orderemail.select.file.php?lang=<?echo $lang;?>', 'upload',"height=180,width=400,menubar=no,location=no,resizable=no,scrollbars=no,left="+iMyWidth+",top="+iMyHeight+"");
      winNew.focus();
    }
    //--------------------------------------------------------------------------
    function MM_reloadPage(init)  //reloads the window if Nav4 resized
  {
    if (init==true) with (navigator)
    {
      if ((appName=="Netscape")&&(parseInt(appVersion)==4))
      {
        document.MM_pgW=innerWidth;
        document.MM_pgH=innerHeight;
        onresize=MM_reloadPage;
      }
    }
    else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH)
    { location.reload(); }
  }
  //----------------------------------------------------------------------------
  MM_reloadPage(true);
  //----------------------------------------------------------------------------
  function setColor(color)
    {
      var Obj = document.frmOemail.choice;
      for (var i=0; i<Obj.length; i++)
      {
        if(Obj[i].checked)
        {
            var field = Obj[i].value;
        }
      }
      document.getElementById(field).value=color;
      document.getElementById(field+"View").style.backgroundColor = color;
    }
  </script>
</head>


<body>
<form name="frmOemail" action="mod.orderemail.save.php" method="post">
<?php
require_once("../../include/page.header.php");
?>

<div id="PGorderemaildetail">
	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="start" value="<?php echo $start;?>">
	<input type="hidden" name="act" value="<?php echo $act;?>">

<h1>&#187;&nbsp;<?php echo L_dynsb_OrderEmail;?>&nbsp;&#171;</h1>

<table>
<?php
if(($act!="b"))
{
?>
  <tr>
    <td align="right"><?php echo L_dynsb_Background;?>&nbsp;</td>
    <td>
      <input type="text" class="small" value="<?php echo $ordEmailBground;?>" name="bground" id="bground">&nbsp;
      <input type="text" class="small" style='background-color: <?php echo $ordEmailBground;?>'  name="bgroundView" id="bgroundView">&nbsp;
      <input type='radio' class="radio" id='choice' name='choice' value='bground' checked>
    </td>
    <td valign="top" rowspan="8">
      <?include "color.php"; ?>
    </td>
  </tr>

  <tr>
    <td align="right"><?php echo L_dynsb_Text;?>:&nbsp;</td>
    <td>
      <input type="text" class="small" value="<?php echo $ordEmailText;?>" name="text" id="text">&nbsp;
      <input type="text" class="small" style='background-color: <?php echo $ordEmailText;?>' name="textView" id="textView">&nbsp;
      <input type="radio" class="radio" name='choice' value='text'>
    </td>
  </tr>

  <tr>
    <td align="right"><?php echo L_dynsb_Headline;?>:&nbsp;</td>
    <td>
      <input type="text" class="small" value="<?php echo $ordEmailTitle;?>" name="title" id="title">&nbsp;
      <input type="text" class="small" style='background-color: <?php echo $ordEmailTitle;?>' name="titleView" id="titleView">&nbsp;
      <input type='radio' class="radio" id='choice2' name='choice' value='title'>
    </td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_TableHeadBackground;?>:&nbsp;</td>
    <td>
      <input type="text" class="small" value="<?php echo $ordEmailTabheadBg;?>" name="tabhead_bg" id="tabhead_bg">&nbsp;
      <input type="text" class="small" style='background-color: <?php echo $ordEmailTabheadBg;?>' name="tabhead_bgView" id="tabhead_bgView">&nbsp;
      <input type='radio' class="radio"  name='choice' value='tabhead_bg'>
    </td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_TableHeadText;?>:&nbsp;</td>
    <td>
      <input type="text" class="small" value="<?php echo $ordEmailTabheadText;?>" name="tabhead_text" id="tabhead_text">&nbsp;
      <input type="text" class="small" style='background-color: <?php echo $ordEmailTabheadText;?>' name="tabhead_textView" id="tabhead_textView">&nbsp;
      <input type='radio' class="radio"  name='choice' value='tabhead_text'>
    </td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_TableBodyBackground;?>:&nbsp;</td>
    <td>
      <input type="text" class="small" value="<?php echo $ordEmailTabbodyBg;?>" name="tabbody_bg" id="tabbody_bg">&nbsp;
      <input type="text" class="small" style='background-color: <?php echo $ordEmailTabbodyBg;?>' name="tabbody_bgView" id="tabbody_bgView">&nbsp;
      <input type='radio' class="radio"  name='choice' value='tabbody_bg'>
    </td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_TableBodyText;?>:&nbsp;</td>
    <td>
      <input type="text" class="small" value="<?php echo $ordEmailTabbodyText;?>" name="tabbody_text" id="tabbody_text">&nbsp;
      <input type="text" class="small" style='background-color: <?php echo $ordEmailTabbodyText;?>' name="tabbody_textView" id="tabbody_textView">&nbsp;
      <input type='radio' class="radio"  name='choice' value='tabbody_text'>
    </td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_TableBorder;?>:&nbsp;</td>
    <td>
      <input type="text" class="small" value="<?php echo $ordEmailTabBorder;?>" name="tab_border" id="tab_border">&nbsp;
      <input type="text" class="small" style='background-color: <?php echo $ordEmailTabBorder;?>' name="tab_borderView" id="tab_borderView">&nbsp;
      <input type='radio' class="radio"  name='choice' value='tab_border'>
    </td>
  </tr>


<?php
	if ($act=="e")
	{
?>
    <tr>
      <td align="right">&nbsp;</td>
      <td colspan="2"><?php echo L_dynsb_RightsErrorImageUpload;?></td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_FileName;?>:&nbsp;</td>
      <td colspan="2"><input type="text" maxlength="96" value="<?php echo $ordEmailImage;?>" name="Image" readonly>&nbsp;
        <input type="button" class="button" onclick="javascript:uploadPic();" name="btn_upload" value="<?php echo L_dynsb_UploadImage;?>">
      </td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_ImageSizeX;?>:&nbsp;</td>
      <td><input type="text" maxlength="3" value="<?php echo $ordEmailImageXsize;?>" name="ImageXsize">&nbsp;Pixel</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_ImageSizeY;?>:&nbsp;</td>
      <td><input type="text" maxlength="3" value="<?php echo $ordEmailImageYsize;?>" name="ImageYsize">&nbsp;Pixel</td>
      <td>&nbsp;</td>
    </tr>
<?php
	}
	else
	{
?>
    <tr>
      <td align="right">&nbsp;</td>
      <td colspan='2'><?php echo L_dynsb_ImageUploadNextStep;?></td>
    </tr>
<?php
	}
}
else
{
?>
  <tr>
    <td>
      <?php echo L_dynsb_DataSaved;?>
    </td>
  </tr>
<?php
}
?>
</table>
<br />

<?php
if(($act!="b"))
{
?>
<!-- navigation // -->
<div class="footer">
  <input type="button" class="button" onclick="javascript:submitForm('frmOemail');" name="btn_save" value="<?php echo L_dynsb_Save;?>">
  <input type="button" class="button" onclick="javascript:self.location.href='../../help/about.php?lang=<?$lang;?>';" name="btn_save" value="<?php echo L_dynsb_Cancel;?>">
</div>
<?php
}
?>
</div>
<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>







