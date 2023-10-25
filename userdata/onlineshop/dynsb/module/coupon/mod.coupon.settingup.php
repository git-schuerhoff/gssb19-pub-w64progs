<?php
/******************************************************************************/
/* File: mod.coupon.settingup.php                                             */
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

if(strtolower($act) == "e")
{
    $sql = "SELECT couponText1, couponText2 FROM ".DBToken."settings";
    $qry = @mysqli_query($link,$sql);
    $obj = @mysqli_fetch_object($qry);

    $text1 = $obj->couponText1;
    $text2 = $obj->couponText2;
}

if(strtolower($act) == "b" || strtolower($act) == "c")
{
    $sqlText1 = " couponText1 = '".$_REQUEST['text1']."',";
    $sqlText2 = " couponText2 = '".$_REQUEST['text2']."'";
    $text1 = $_REQUEST['text1'];
    $text2 = $_REQUEST['text2'];

    $sql = "SELECT couponImage, couponImageXsize, couponImageYsize FROM ".DBToken."settings";
    $qry = @mysqli_query($link,$sql);
    $obj = @mysqli_fetch_object($qry);

    $image = $obj->couponImage;
    $imageXsize = $obj->couponImageXsize;
    $imageYsize = $obj->couponImageYsize;

    if(isset($_REQUEST['image'])||strlen($_REQUEST['image'])!=0)
    {
      $sqlImage = ", couponImage = '".$_REQUEST['image']."',";
    }
    else
    {
      $sqlImage = "";
    }

    if(isset($_REQUEST['imageXsize'])||strlen($_REQUEST['imageXsize'])!=0 &&
      isset($_REQUEST['imageYsize'])||strlen($_REQUEST['imageYsize'])!=0)
    {
      $sqlImageSize = " couponImageXsize = '".$_REQUEST['imageXsize']."', "
                    . "couponImageYsize = '".$_REQUEST['imageYsize']."'";
    }
    else
    {
      $sqlImageSize = "";
    }

    $sql = "UPDATE ".DBToken."settings set".$sqlText1.$sqlText2.$sqlImage.$sqlImageSize." WHERE setIdNo='1'";
    $qry = @mysqli_query($link,$sql);
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_Coupons;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
	  <script type="text/javascript" src="../../js/gslib.php"></script>
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
    //--------------------------------------------------------------------------
    MM_reloadPage(true);
    //--------------------------------------------------------------------------
    function uploadPic()
    {
      var iMyWidth;
      var iMyHeight;

      iMyWidth = Math.round((window.screen.width/2) - (400/2 + 10));
      iMyHeight = Math.round((window.screen.height/2) - (180/2 + 40));
      var winNew = window.open('mod.coupon.select.file.php?strcal=<?echo $strcal;?>&lang=<?echo $lang;?>', 'upload',"height=180,width=400,menubar=no,location=no,resizable=no,scrollbars=no,left="+iMyWidth+",top="+iMyHeight+"");
      winNew.focus();
    }
    //--------------------------------------------------------------------------
    function saveData()
    {
      document.frmCoup.act.value='c';
      document.frmCoup.submit();
    }
    </script>
</head>
<body>
<form name="frmCoup" action="mod.coupon.settingup.php" method="post">

<?php
require_once("../../include/page.header.php");
?>

<div id="PGcouponsettingup">
    <input type="hidden" name="lang" value="<?php echo $lang;?>">
    <input type="hidden" name="act" value="b">
    <input type="hidden" name="filled" value="filled">

<h1>&#187;&nbsp;<?php echo L_dynsb_Coupons;?>&nbsp;&#171;</h1>

<?php
if(strtolower($act) != "c")
{
?>
<h2><?php echo L_dynsb_Text1;?></h2>
<p>
	<textarea name='text1' cols="60" rows="15"><?php echo $text1?></textarea>
</p>

<h2><?php echo L_dynsb_Text2;?></h2>
<p>
	<textarea name='text2' cols="60" rows="15"><?php echo $text2?></textarea>
</p>
<?php
	if ($act=="b")
	{
?>
	<p>
		<?php echo L_dynsb_RightsErrorCoupon;?>
	</p>

	<p>
		<?php echo L_dynsb_ImageFile;?>:
		<input type="text" maxlength="96" value="<?php echo $image;?>" name="image" readonly>
	</p>

	<p><input type="button" class="button" onclick="javascript:uploadPic();" name="btn_upload" value="<?php echo L_dynsb_UploadImage;?>"></p>

	<h2>&nbsp;</h2>
	<p>
		<?php echo L_dynsb_ImageSizeX?>:
		<input type="text" class="small" maxlength="3" value="<?php echo $imageXsize;?>" name="imageXsize">
		<?php echo L_dynsb_Pixel;?>
	</p>

	<p>
		<?php echo L_dynsb_ImageSizey;?>:
		<input type="text" class="small" maxlength="3" value="<?php echo $imageYsize;?>" name="imageYsize">
		<?php echo L_dynsb_Pixel;?>
	</p>
<?php
	}
	else
	{
?>
	<p>
		<?php echo L_dynsb_ImageUploadNextStep;?>
	</p>
<?php
	}

	if(strtolower($act) == "e")
	{
?>
	<p>
		<input type="button" class="button" onclick="javascript:submitForm('frmCoup');" name="btn_upload" value="<?php echo L_dynsb_Save;?>">
	</p>
<?php
	}
	elseif(strtolower($act) == "b")
	{
?>
	<p>
 		<input type="button" class="button" onclick="javascript:saveData();" name="btn_upload" value="<?php echo L_dynsb_Save;?>">
	</p>
<?php
	}
}
else
{
?>
	<p>
		<?php echo L_dynsb_DataSaved;?>
	</p>
<?php
}
?>
&nbsp;
</div>
<?php
	require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
