<?php
/**
 * This file generates the page where the User Details and visitor statistics
 * are displayed
 *
 */
/******************************************************************************/
/* File: mod.gsuserdetails.run.php                                                */
/******************************************************************************/
require("../../../../class/class.pagestatistics.php");
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


//trim() every parameter in the $_REQUEST array
foreach($_REQUEST as $key => $value)
{
    $$key = trim($value);
}

$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";


//----------------------------------------------------------

//Create Statistics Object
$ps = new pageStatistics();
$ps->setLang($lang);


//Set start and end date
$ps->setStatStartDate($_REQUEST['statStartDate']);
$ps->setStatEndDate($_REQUEST['statEndDate']);

////horizontal 0, vertical 1;
//$ps->setLayout($_REQUEST['layout']);
//
////barlayout
//$ps->setBarlayout($_REQUEST['barlayout']);

//Get Statistics
$res = $ps->queryGetUserDetails($_REQUEST['userdetailmode']);


//Choose DiagramSize
$ps->switchDiagramSize($_REQUEST['picsize']);

//get size of the diagram
$xsize = $ps->getXsize();
$ysize = $ps->getYsize();

//Get start and end dates
$statStartDate=$ps->getStatStartDate();
$statEndDate=$ps->getStatEndDate();

//Choose how many results should be displayed
$ps->switchDiagramViewmode($_REQUEST['viewmode']);
$limit=$ps->getLimit();

//add the pagestatistics objekt to the session
$_SESSION['pageviews']=$ps;
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title><?php echo L_dynsb_statUserUsedBrowser;?> </title>
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
		function MM_reloadPage(init) {  //reloads the window if Nav4 resized
		  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
		    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
		  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
		}
		MM_reloadPage(true);
	</script>
</head>

<body>
<form  action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
<?php
require_once("../../../../include/page.header.php");
?>

<div id="PGgsuserdetails">
 <input type="hidden" name="lang" value="<?php echo $lang;?>">
 <input type="hidden" name="setIdNo" value="<?php echo $setIdNo;?>">
 <input type="hidden" name="act" value="<?php echo $act;?>">

<h1>&#187;&nbsp;<?php echo L_dynsb_statUserUsedBrowser ;?>&nbsp;&#171;</h1>

<table>
<tr>
	<td align="right"><?php echo L_dynsb_ImageSize;?>:&nbsp;</td>
	<td>
    <select name="picsize">
<?php
		//Get available diagram sizes
		$diagramSizes=$ps->getDiagramSizes();

		for ($i=0;$i< count($diagramSizes);$i++)
		{
			// to keep a value selected
			if ($picsize==$i){$sel=" selected ";} else {$sel=" ";}

			//Generate options
			echo "<option value='$i' $sel> $diagramSizes[$i] ".L_dynsb_Pixel."</option>";
		}
?>
		</select>
	</td>

  <td align="right">
    <?php echo L_dynsb_StartDate;?>;&nbsp;
  </td>

 <!--start date chooser -->
  <td>
   <input type="text" maxlength="32" value="<?php echo timestamp_mysql2german($statStartDate);?>" name="statStartDate" id="statStartDate" readonly>
   <img src="../../../../image/calendar.gif" id="statStartDateTrigger" style="cursor: pointer" alt="<?php echo L_dynsb_Calendar;?>" title="<?php echo L_dynsb_Calendar;?>">
    <script language="JavaScript" type="text/javascript">
		Calendar.setup(
			{
      inputField	 	:    "statStartDate",
      ifFormat     	:    "%d.%m.%Y",
			button       	:    "statStartDateTrigger",
      showsTime	 		:    false,
      singleClick	 	:    true,
      firstDay	 		:	  1,
      align        	:    "Bl"
		  });
		</script>
  </td>
</tr>

<tr>
	<!-- analysis  -->
  <td align="right"><?php echo L_dynsb_Result;?>:&nbsp;</td>
  <td>

 	<!-- analysis chooser-->
	<select name="userdetailmode">
<?php
	 //Get available modes
	 $diagramViewmodes=$ps->getUserDetailModes();

	 	for ($i=0;$i< count($diagramViewmodes);$i++)
		{
	  	// to keep a value selected
			if ($userdetailmode==$i){$sel=" selected ";} else {$sel=" ";}

			//Generate options
			echo "<option value='$i' $sel>$diagramViewmodes[$i]</option>";
		}
?>
	</select>
	</td>

	<!-- end date-->
	<td align="right"><?php echo L_dynsb_EndDate;?>:&nbsp;</td>

	<!-- end date chooser-->
	<td>
		<input type="text" maxlength="32" value="<?php echo timestamp_mysql2german($statEndDate);?>" name="statEndDate" id="statEndDate" readonly>
		<img src="../../../../image/calendar.gif" id="statEndDateTrigger" style="cursor: pointer" title="<?php echo L_dynsb_Calendar;?>" alt="<?php echo L_dynsb_Calendar;?>">
		<script language="JavaScript" type="text/javascript">
			Calendar.setup(
       {
        inputField	 	:    "statEndDate",
        ifFormat     	:    "%d.%m.%Y",
				button       	:    "statEndDateTrigger",
        showsTime	 		:    false,
        singleClick	 	:    true,
        firstDay	 		:	  1,
        align        	:    "Bl"
		    });
		</script>
	</td>
</tr>

<!--START choose period of time-->
<tr>
  <td align="right">&nbsp;<?php echo L_dynsb_statPeriod?>:</td>

	<!-- choose period of time-->
	<td>
  	<select name="datechooser"
    		onchange="document.getElementById('statStartDate').value=datechooser.value;
    		  				document.getElementById('statEndDate').value='<?php echo date("d.m.Y")?>';">

		<option value='<?php echo date("d.m.Y")?>'selected><?php echo L_dynsb_statChooseTime; ?></option>

<?php
		//1 week
		echo "<option value='". date("d.m.Y",mktime()-mktime(1,0,0,1,8,1970))."' >
		".L_dynsb_statOneWeek." </option>";

		//1 month
		echo "<option value='". date("d.m.Y",mktime()-mktime(1,0,0,2,1,1970))."' >
		".L_dynsb_statOneMonth."</option>";

		//3 month
		echo "<option value='". date("d.m.Y",mktime()-mktime(1,0,0,4,1,1970))."' >
		".L_dynsb_statThreeMonths." </option>";

		//6month
		echo "<option value='". date("d.m.Y",mktime()-mktime(1,0,0,7,1,1970))."' >
		".L_dynsb_statSixMonths." </option>";

		//1year
		echo "<option value='". date("d.m.Y",mktime()-mktime(1,0,0,1,1,1971))."' >
		".L_dynsb_statOneYear." </option>";

		//1year
		echo "<option value='". date("d.m.Y",mktime()-mktime(1,0,0,1,1,1999))."' >
		".L_dynsb_statAll." </option>";
?>
		</select>
	</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
  </tr>

  <tr>
    <td>&nbsp;</td>
    <!-- refresh button-->
    <td><input type="submit" class="button" name="refreshLayoutButton" value="<?php echo L_dynsb_Refresh;?>"></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>

<h2><?php echo L_dynsb_Diagram;?></h2>

<div class="diagram">
	<img src="mod.gsuserdetails.show.php" usemap="#stat" class="dia" alt="<?php echo L_dynsb_Diagram;?>">
</div>

<h2>&nbsp;</h2>
<table>
<tr>
  <th align="right"><?php echo L_dynsb_Rank?></th>
  <th><?php echo L_dynsb_statUserBrowser;?></th>
  <th><?php echo L_dynsb_HitQuantity;?></th>
  <th><?php echo L_dynsb_statUserPercentage;?></th>
 </tr>

<?php
//Generate Top-list
$x = 1;
$hitsOverall=0;
while($obj = @mysqli_fetch_object($res))
{

	if ($x % 2 != 0)
		$rowStyle = " odd ";
	else
		$rowStyle = " even ";

	echo "<tr class=\"$rowStyle\">
	 <td align=\"right\">$x</td>
	 <td>$obj->monUserBrowser</td>
	 <td align=\"center\">$obj->monUserHits</td>
	 <td align=\"center\">$obj->monUserPercentage %</td>
	</tr>";

	$x++;
	$hitsOverall+=$obj->monUserHits;
}
?>
	<tr>
		<th align="right" colspan="2"><?php echo L_dynsb_statTotal;?>:&nbsp;</th>
		<th><?php echo $hitsOverall;?></th>
		<th>100.00 %</th>
	</tr>
</table>
<br />

 <!-- navigation // -->
<div class="footer">
	<input type="submit" class="button" name="refreshLayoutButton" value="<?php echo L_dynsb_Refresh;?>">
</div>
</div>
<?php
require_once("../../../../include/page.footer.php");
?>

<script type="text/javascript" src="../../../../js/wz_tooltip.js"> </script>
</form>
</body>
</html>
