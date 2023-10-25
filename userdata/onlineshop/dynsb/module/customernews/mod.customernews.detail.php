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

if(strtolower($act) == "e")
{
    // start database query
    $qrySQL = "SELECT * FROM ".DBToken."customernews WHERE cnewsIdNo = '".$cnewsIdNo."'";
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
  <title><?php echo L_dynsb_CustomerNews;?></title>
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
    var winNew = window.open('mod.customernews.select.file.php?nid='+document.frmCnews.cnewsIdNo.value+'&lang=<?echo $lang;?>', 'upload',"height=180,width=400,menubar=no,location=no,resizable=no,scrollbars=no,left="+iMyWidth+",top="+iMyHeight+"");
    winNew.focus();
  }
  //----------------------------------------------------------------------------
  function checkdate()
  {
    if(document.frmCnews.cnewsStartDate.value=="00.00.0000" || document.frmCnews.cnewsStartDate.value=="00.00.0000")
    {
      alert('<?php echo L_dynsb_NoteNewsDate;?>');
    }
  }
  </script>
</head>
<body>
<form name="frmCnews" action="mod.customernews.save.php" method="post">
<?php
require_once("../../include/page.header.php");
?>

<div id="PGcustomernewsdetail">

	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="start" value="<?php echo $start;?>">
	<input type="hidden" name="cnewsIdNo" value="<?php echo $cnewsIdNo;?>">
	<input type="hidden" name="act" value="<?php echo $act;?>">


<h1>&#187;&nbsp;<?php echo L_dynsb_CustomerNews;?>&nbsp;&#171;</h1>

  <table>
    <tr>
      <td align="right"><?php echo L_dynsb_StartDate;?>:&nbsp;</td>
      <td>
	      <input type="text" maxlength="32" value="<?php echo timestamp_mysql2german($cnewsStartDate);?>" name="cnewsStartDate" id="cnewsStartDate" readonly>&nbsp;
	      <img src="../../image/calendar.gif" id="cnewsStartDateTrigger" style="cursor: pointer" width="16" height="16" alt="Kalender" title="Kalender">
	      <script language="JavaScript" type="text/javascript">
			    Calendar.setup(
			                {
			            inputField	 :    "cnewsStartDate",
			            ifFormat     :    "%d.%m.%Y",
			           	button       :    "cnewsStartDateTrigger",
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
      <td>
      	<input type="text" maxlength="16" value="<?php echo timestamp_mysql2german($cnewsEndDate);?>" name="cnewsEndDate" id="cnewsEndDate" readonly>&nbsp;
      	<img src="../../image/calendar.gif" id="cnewsEndDateTrigger" style="cursor: pointer" width="16" height="16" alt="Kalender" title="Kalender">
        <script language="JavaScript" type="text/javascript">
			    Calendar.setup(
		    	{
	          inputField	 	:    "cnewsEndDate",
	          ifFormat     	:    "%d.%m.%Y",
	       	  button       	:    "cnewsEndDateTrigger",
	          showsTime	 		:    false,
	          singleClick	 	:    true,
	          firstDay	 		:	  1,
	          align        	:    "Bl"
	        });
				</script>
 			</td>
    </tr>

    <tr>
      <td align="right"><?php echo L_dynsb_ForAllCustomers;?>?:&nbsp;</td>
      <td align='left'>
        <?php
        if($cnewsForAll==1)
        {
          echo "<input type='checkbox' class='checkbox' value='1' name='cnewsForAll' checked>";
        }
        else
        {
          echo "<input type='checkbox' class='checkbox' value='1' name='cnewsForAll'>";
        }
        ?>
      </td>
    </tr>
    <tr>
      <th colspan="2">&nbsp;</th>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_Headline;?>:&nbsp;</td>
      <td>
        <input type="text" maxlength="64" class="larger" value="<?php echo $cnewsTitle; ?>"  name="cnewsTitle">
      </td>
    </tr>
    <tr>
      <td valign="top" align="right"><?php echo L_dynsb_Content;?>:&nbsp;</td>
      <td colspan="3">
        <textarea rows="15" cols="40" name="cnewsContent"><?php echo trim($cnewsContent);?></textarea>
      </td>
    </tr>

<?php
if ($act=="e")
{
?>
		<tr><th colspan="2">&nbsp;</th></tr>

    <tr>
      <td align="right"><?php echo L_dynsb_FileName;?>:&nbsp;</td>
      <td colspan="2">
        <input type="text" class="larger" maxlength="96" value="<?php echo $cnewsPicName;?>" name="cnewsPicName" readonly>&nbsp;
        <input type="button" class="button" onclick="javascript:uploadPic();" name="btn_upload" value="<?php echo L_dynsb_UploadImage;?>">
        <br /><?php echo L_dynsb_RightsErrorImageUpload;?>
      </td>
      <td align="right">&nbsp;</td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_ImageLink;?>:&nbsp;</td>
      <td colspan="2">
        <input type="text" class="larger" maxlength="96" value="<?php echo $cnewsPicLink;?>" name="cnewsPicLink">
      </td>
      <td align="right">&nbsp;</td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_ImageSizeX;?>:&nbsp;</td>
      <td><input type="text" class="small" maxlength="3" value="<?php echo $cnewsPicXSize;?>" name="cnewsPicXSize">&nbsp;pixel</td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_ImageSizeY;?>:&nbsp;</td>
      <td><input type="text" class="small" maxlength="3" value="<?php echo $cnewsPicYSize;?>" name="cnewsPicYSize">&nbsp;pixel</td>
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
    ?>
  </table>


<!-- navigation // -->

<div class="footer">
  <input type="button" class="button" onclick="javascript:checkdate();submitForm('frmCnews');" name="btn_save" value="<?php echo L_dynsb_Save;?>">
  <input type="button" class="button" onclick="javascript:this.location.href='mod.customernews.search.php?start=<?php echo $start;?>&lang=<?echo $lang;?>';" name="btn_save" value="<?php echo L_dynsb_Cancel;?>">
</div>
</div>

<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
