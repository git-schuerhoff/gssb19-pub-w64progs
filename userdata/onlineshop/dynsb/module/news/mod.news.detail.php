<?php
/******************************************************************************/
/* File: mod.news.detail.php                                                  */
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
    $newsIdNo = intval($_REQUEST['pk']);
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
    $qrySQL = "SELECT * FROM ".DBToken."news WHERE newsIdNo = '".$newsIdNo."'";
    $qry = @mysqli_query($link,$qrySQL);
    $obj = @mysqli_fetch_object($qry);

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
    <title><?php echo L_dynsb_News;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
    <script	type="text/javascript" src="../../js/gslib.php"></script>
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
    //--------------------------------------------------------------------------
    MM_reloadPage(true);
    //--------------------------------------------------------------------------
    function uploadPic()
    {
      var iMyWidth;
      var iMyHeight;

      iMyWidth = Math.round((window.screen.width/2) - (400/2 + 10));
      iMyHeight = Math.round((window.screen.height/2) - (180/2 + 40));
      var winNew = window.open('mod.news.select.file.php?nid='+document.frmNews.newsIdNo.value+'&lang=<?echo $lang;?>', 'upload',"height=180,width=400,menubar=no,location=no,resizable=no,scrollbars=no,left="+iMyWidth+",top="+iMyHeight+"");
      winNew.focus();
    }
    //--------------------------------------------------------------------------
    function checkdate()
    {
      if(document.frmNews.newsStartDate.value=="00.00.0000" || document.frmNews.newsStartDate.value=="00.00.0000")
      {
        alert('<?php echo L_dynsb_NoteNewsDate;?>');
      }
    }
    </script>
</head>
<body>
<form name="frmNews" action="mod.news.save.php" method="post">
<?php
require_once("../../include/page.header.php");
?>

<div id="PGnewsdetail">
	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="start" value="<?php echo $start;?>">
	<input type="hidden" name="newsIdNo" value="<?php echo $newsIdNo;?>">
	<input type="hidden" name="act" value="<?php echo $act;?>">


<h1>&#187;&nbsp;<?php echo L_dynsb_News;?>&nbsp;&#171;</h1>

  <table>
    <tr>
      <td align="right"><?php echo L_dynsb_StartDate;?>:&nbsp;</td>
      <td><input type="text" maxlength="32" value="<?php echo timestamp_mysql2german($newsStartDate);?>" name="newsStartDate" id="newsStartDate" readonly>
      <img src="../../image/calendar.gif" id="newsStartDateTrigger" style="cursor: pointer" alt="<?php echo L_dynsb_Calendar;?>" title="<?php echo L_dynsb_Calendar;?>">
      <script language="JavaScript" type="text/javascript">
			  Calendar.setup(
        {
			    inputField	 :    "newsStartDate",
			    ifFormat     :    "%d.%m.%Y",
					button       :    "newsStartDateTrigger",
			    showsTime	 :    false,
			    singleClick	 :    true,
			    firstDay	 :	  1,
			    align        :    "Bl"
      	});
			</script>
		 </td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_EndDate;?>:&nbsp;</td>
      <td><input type="text" maxlength="16" value="<?php echo timestamp_mysql2german($newsEndDate);?>"  name="newsEndDate" id="newsEndDate" readonly>
      <img src="../../image/calendar.gif" id="newsEndDateTrigger" style="cursor: pointer" alt="<?php echo L_dynsb_Calendar;?>" title="<?php echo L_dynsb_Calendar;?>">
      <script language="JavaScript" type="text/javascript">
			  Calendar.setup(
			              {
			          inputField	 :    "newsEndDate",
			          ifFormat     :    "%d.%m.%Y",
			                  button       :    "newsEndDateTrigger",
			          showsTime	 :    false,
			          singleClick	 :    true,
			          firstDay	 :	  1,
			          align        :    "Bl"
			      });
			</script>
		 </td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_Index;?>:&nbsp;</td>
      <td><input type="text" maxlength="2" value="<?php echo $newsSortIndex; ?>" name="newsSortIndex"></td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_Headline;?>:&nbsp;</td>
      <td><input type="text" class="larger" maxlength="64" value="<?php echo $newsTitle; ?>" class="inputbox300_eingabe" name="newsTitle"></td>
    </tr>


    <tr>
      <th colspan="2">&nbsp;</th>
    </tr>

    <tr>
      <td align="right" valign='top'><?php echo L_dynsb_Content;?>:&nbsp;</td>
      <td colspan="3">
      	<textarea rows="25" cols="80" class="textarea550" name="newsContent"><?php echo trim($newsContent);?></textarea>
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
      <td><input type="text" class="larger" maxlength="96" value="<?php echo $newsPicName;?>" name="newsPicName" readonly>&nbsp;<input type="button" class="button" onclick="javascript:uploadPic();" name="btn_upload" value="<?php echo L_dynsb_UploadImage;?>">
      </td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_ImageLink;?>:&nbsp;</td>
      <td><input type="text" class="larger" value="<?php echo $newsPicLink;?>" name="newsPicLink"></td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_ImageSizeX;?>:&nbsp;</td>
      <td><input type="text" maxlength="3" class="small" value="<?php echo $newsPicXSize;?>" name="newsPicXSize">&nbsp;Pixel</td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_ImageSizeY;?>:&nbsp;</td>
      <td><input type="text" maxlength="3" class="small" value="<?php echo $newsPicYSize;?>" name="newsPicYSize">&nbsp;Pixel</td>
    </tr>
<?php
}
else
{
?>
    <tr>
      <td align="right">&nbsp;</td>
      <td colspan="2"><?php echo L_dynsb_ImageUploadNextStep;?></td>
    </tr>
<?php
}
?>
</table>

<!-- navigation // -->
  <div class="footer">
    <input type="button" class="button" onclick="checkdate();javascript:submitForm('frmNews');" name="btn_save"  value="<?php echo L_dynsb_Save;?>">
    <input type="button" class="button" onclick="javascript:self.location.href='mod.news.search.php?start=<?php echo $start;?>&lang=<?php echo $lang;?>';" name="btn_save" value="<?php echo L_dynsb_Cancel;?>">
  </div>
</div>

<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
