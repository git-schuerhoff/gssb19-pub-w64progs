<?php
/******************************************************************************/
/* File: mod.pageviews.run.php                                                */
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
//$ps->setDbVariables($dbServer,$dbUser,$dbPass,$dbDatabase);

//Set start and end date
$ps->setStatStartDate($_REQUEST['statStartDate']);
$ps->setStatEndDate($_REQUEST['statEndDate']);

//horizontal 0, vertical 1;
$ps->setLayout($_REQUEST['layout']);

//barlayout
$ps->setBarlayout($_REQUEST['barlayout']);

//...
$ps->setUserDetailMode($_REQUEST['userdetailmode']);
$viewmode=$_REQUEST['userdetailmode'];

if ($viewmode==0)
  $outHeading = L_dynsb_monuserclicksminutes; 
elseif($viewmode==1)
  $outHeading = L_dynsb_monuserclicksviews;
 

//Get Statistics
$res = $ps->queryGetUserclicks();


//Choose DiagramSize
$ps->switchDiagramSize($_REQUEST['picsize']);

//get size of the diagram
$xsize = $ps->getXsize();
$ysize = $ps->getYsize();

//Get start and end dates
$statStartDate=$ps->getStatStartDate();
$statEndDate=$ps->getStatEndDate();

//Choose how many results should be displayed
//$ps->switchDiagramViewmode($_REQUEST['viewmode']);
$limit=$ps->getLimit();

//add the pagestatistics objekt to the session
$_SESSION['pageviews']=$ps;



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo $ps->getDiagramName(); ?></title>
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
			//------------------------------------------------------------------------------
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

<div id="PGgsvisitors">
 <input type="hidden" name="lang" value="<?php echo $lang;?>">
 <input type="hidden" name="setIdNo" value="<?php echo $setIdNo;?>">
 <input type="hidden" name="act" value="<?php echo $act;?>">

<h1>&#187;&nbsp;<?php echo $ps->getDiagramName();?>&nbsp;&#171;</h1>

<?php
	require_once("../../../../include/inc.statistics.parameters.forobject.php");
?>


<h2><?php echo L_dynsb_Diagram;?></h2>

<div class="diagram">
	<!-- new one, with use of sessions -->
	<img src="mod.gsuserclicks.show.php" usemap="#stat" class="dia" alt="<?php echo L_dynsb_Diagram;?>">

	<!-- imagemap-->
	<map name="stat">
<?php
	$i = 0;
	$layout=$ps->getLayout();

	if($layout == 0) {
	    //horizontal
	    $stepx = $xsize / count($res);
	    $offsetx = 60;
	    $offsety = 80;
	    $y1 = 0 + $offsety;
	    $y2 = $ysize + $offsety;
	} else {
	    //vertical
	    $stepy = $ysize / count($res);
	    $offsetx = 60;
	    $offsety = 80;
	    $x1 = 0 + $offsetx;
	    $x2 = $xsize + $offsetx;
	}

	//Berechnung der Spaltenbreite für die JavaScript überlagerung
	while($res[$i]) {
	    if($layout == 0) {
	        $x1 = ($i * $stepx) + $offsetx;
	        $x2 = (($i+1) * $stepx) + $offsetx;
	    } else {
	        $y1 = $offsety + $ysize - ($i * $stepy);
	        $y2 = $offsety + $ysize - (($i+1) * $stepy);
	    }
	    echo "<area shape=\"rect\" coords=\"$x1,$y1,$x2,$y2\" " .
	    		"onMouseOver=\"javascript:this.T_SHADOWCOLOR='#777788';this.T_SHADOWWIDTH=3;this.T_TITLE='&nbsp;';" .
	    		"return escape('<table><tr><td align=right>".$outHeading.":</td>" .
	    		"<td><b>".$res[$i][1]."</b></td></tr>  " .
	    		"<tr><td align=right>".L_dynsb_monuserclicksnumvisitors.":</td>" .
	    		"<td><b>".$res[$i][2]."</b></td></tr> " .
	    		"<tr><td align=right>".L_dynsb_statUserPercentage.":</td>" .
	    		"<td><b>".$res[$i][3]."%</b></td>" .
	    		"</tr></table>');\">\n";
	    $i++;
		}
		//rücksetzten des pointers im data-objekt
		// if($i > 0) mysqli_data_seek($res, 0);
?>
	</map>
</div>

<h2>&nbsp;</h2>
<table>
	<tr>
    <th><?php echo $outHeading;?></th>
    <th><?php echo L_dynsb_monuserclicksnumvisitors;?></th>
		<th><?php echo L_dynsb_statUserPercentage;?></th>
	</tr>
<?php
	$k = 0;
	$hitsOverall=0;
	while($res[$k])
	{
		$res2 = $res[$k];

		if ($k % 2 != 0)
			$rowStyle = " odd ";
		else
			$rowStyle = " even ";

		echo "<tr class=\"$rowStyle\">
			<td align=\"center\">$res2[1]</td>
		  <td align=\"right\">$res2[2]</td>
		  <td align=\"right\">$res2[3]%</td>
		</tr>";

		$hitsOverall += $res2[2];
		$k++;
  }
?>
	<tr class="<?php echo $rowStyle;?>">
    <th align="right"><?php echo L_dynsb_statTotal;?>:</th>
    <th align="right"><?php echo $hitsOverall;?></th>
		<th align="right">100.0%</th>
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

</form>
<script type="text/javascript" src="../../../../js/wz_tooltip.js"></script>
</body>
</html>
