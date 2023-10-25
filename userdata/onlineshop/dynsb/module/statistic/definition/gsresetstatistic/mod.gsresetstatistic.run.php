<?php
/******************************************************************************/
/* File: mod.gsresetstatistic.run.php                                         */
/******************************************************************************/

require("../../../../include/login.check.inc.php");
require_once("../../../../include/functions.inc.php");
require("../../../../../conf/db.const.inc.php");

/***************** Sprachdatei ************************************************/
if (!isset($_REQUEST['lang']) || strlen(trim($_REQUEST['lang'])) == 0)
{
    $lang = "deu";
}
else
{
	$lang = $_REQUEST['lang'];
	if(!file_exists("../../../../lang/lang_".$lang.".php"))
  {
    $lang = "deu";
  }
}

include("../../../../lang/lang_".$lang.".php");
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

if(isset($_REQUEST['alldata']))
{
  $SQL = "DELETE FROM ".DBToken."monitorlog;";
  @mysqli_query($link, $SQL);
  $SQL = "DELETE FROM ".DBToken."monitorpageviews;";
  @mysqli_query($link, $SQL);
  $SQL = "DELETE FROM ".DBToken."monitoruserdetails;";
  @mysqli_query($link, $SQL);
  $SQL = "DELETE FROM ".DBToken."monitoruserclicks;";
  @mysqli_query($link, $SQL);
}

if(isset($_REQUEST['strEndDate'])&&isset($_REQUEST['strStartDate'])) {
 
  $aTmp = explode(".", $_REQUEST['strStartDate']);
  $strStartDate = $aTmp[2].$aTmp[1].$aTmp[0] . "000000";

  $aTmp = explode(".", $_REQUEST['strEndDate']);
  $strEndDate = $aTmp[2].$aTmp[1].$aTmp[0] . "235959";

  $sql = "DELETE FROM ".DBToken."monitorlog WHERE monChgTimestamp > ".$strStartDate." AND monChgTimestamp < ".$strEndDate;
  @mysqli_query($link, $sql);
  $sql = "DELETE FROM ".DBToken."monitorpageviews WHERE monPageVisitTimestamp > ".$strStartDate." AND monPageVisitTimestamp < ".$strEndDate;
  @mysqli_query($link, $sql);
  $sql = "DELETE FROM ".DBToken."monitoruserdetails WHERE monUserTimestamp > ".$strStartDate." AND monUserTimestamp < ".$strEndDate;
  @mysqli_query($link, $sql);
  $sql = "DELETE FROM ".DBToken."monitoruserclicks WHERE moucDatetimeFirst > " . $strStartDate." AND moucDatetimeFirst < " . $strEndDate;
  @mysqli_query($link, $sql); 
}

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

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_StatisticReset;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../../../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
    <script type="text/javascript" src="../../../../js/gslib.php"></script>
		<script type="text/javascript" src="../../../../js/calendar.js"></script>
		<script type="text/javascript" src="../../../../js/calendar-<?php echo $strcal;?>.js"></script>
		<script type="text/javascript" src="../../../../js/calendar-setup.js"></script>
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
		  function deleteAll()
		  {
		    var bCheck = confirm("<?php echo L_dynsb_ReallyDeleteStatistic;?>");
		    if(bCheck==true)
		      document.frmStReset.submit();
		  }
	  </script>
</head>

<body>
<form name="frmStReset" action="mod.gsresetstatistic.run.php" method="post">
<?php
require_once("../../../../include/page.header.php");
?>
<div id="PGresetstatistics">
	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="start" value="<?php echo $start;?>">
	<input type="hidden" name="act" value="<?php echo $act;?>">

<h1>&#187;&nbsp;<?php echo L_dynsb_StatisticReset;?>&nbsp;&#171;</h1>

<table style="width:auto;">
  <tr>
    <td align="right"><?php echo L_dynsb_StartDate;?>:&nbsp;</td>
    <td>
    	<input type="text" maxlength="32" value="<?php echo timestamp_mysql2german($strStartDate);?>" name="strStartDate" id="strStartDate" readonly>&nbsp;
    	<img src="../../../../image/calendar.gif" id="strStartDateTrigger" style="cursor: pointer" title="<?php echo L_dynsb_Calendar;?>" alt="<?php echo L_dynsb_Calendar;?>">
      <script language="JavaScript" type="text/javascript">
		    Calendar.setup(
                    {
		            inputField	 :    "strStartDate",
		            ifFormat     :    "%d.%m.%Y",
                        button       :    "strStartDateTrigger",
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
    	<input type="text" maxlength="16" value="<?php echo timestamp_mysql2german($strEndDate);?>" name="strEndDate" id="strEndDate" readonly>&nbsp;
    	<img src="../../../../image/calendar.gif" id="strEndDateTrigger" style="cursor: pointer" title="<?php echo L_dynsb_Calendar;?>" alt="<?php echo L_dynsb_Calendar;?>">
	      <script language="JavaScript" type="text/javascript">
			    Calendar.setup(
	                    {
			            inputField	 :    "strEndDate",
			            ifFormat     :    "%d.%m.%Y",
									button       :    "strEndDateTrigger",
			            showsTime		 :    false,
			            singleClick	 :    true,
			            firstDay	   :	  1,
			            align        :    "Bl"
			        });
			</script>
    </td>
  </tr>

  <tr>
    <td align="right"><?php echo L_dynsb_DeleteAll;?>:</td>
    <td><input type="checkbox" class="checkbox" name="alldata" value="alldata"></td>
  </tr>
</table>
<br />

<!-- navigation // -->
<div class="footer">
  <input type="button" class="button" onclick="javascript:deleteAll();" name="btn_save" value="<?php echo L_dynsb_Delete;?>">&nbsp;
</div>
</div>
<?php
require_once("../../../../include/page.footer.php");
?>

</form>
</body>
</html>
