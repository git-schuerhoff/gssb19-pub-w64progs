<?php
/******************************************************************************/
/* File: mod.gssalevalue.show.php                                             */
/******************************************************************************/

require("../../../../include/login.check.inc.php");
require_once("../../../../include/functions.inc.php");
require("../../../../../conf/db.const.inc.php");
require("../../../../class/class.diagram.bar.php");

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
if (!isset($_SESSION['SESS_Currency']) || strlen(trim($_SESSION['SESS_Currency'])) == 0) {
  die ("<br />error: missing session parameter!<br />");
} else {
	$SESS_Currency = $_SESSION['SESS_Currency'];
}


$layout = intval(trim($_REQUEST['layout']));
if($layout === null) $layout = 0;

$barlayout = intval(trim($_REQUEST['bl']));
if($barlayout === null) $barlayout = 0;

$startDate = (trim($_REQUEST['sd']));
if($startDate == "") $startDate = date("Ym")."01000000";

$endDate = (trim($_REQUEST['ed']));
if($endDate == "") $endDate = date("Ym")."31235959";

$startMonth = substr($startDate, 4, 2);
$startYear = substr($startDate, 0, 4);
$endMonth = substr($endDate, 4, 2);
$endYear = substr($endDate, 0, 4);

if(substr($startDate, 0, 6) == substr($endDate, 0, 6)) {
    $strPeriod = L_dynsb_From.": ".getmonth($startMonth,$SESS_languageIdNo).", ".$startYear;
} else {
    $strPeriod = L_dynsb_From.": ".getmonth($startMonth,$SESS_languageIdNo).", ".$startYear." ".L_dynsb_To.": ".getmonth($endMonth,$SESS_languageIdNo).", ".$endYear;
}

if($layout == 0) {
    $xsize = intval(trim($_REQUEST['xsize']));
    $ysize = intval(trim($_REQUEST['ysize']));
    $addRightSpc = 0;
} else {
    $xsize = intval(trim($_REQUEST['ysize']));
    $ysize = intval(trim($_REQUEST['xsize']));
    ($xsize > 600) ? $addRightSpc = 0 : $addRightSpc = 60;
}

$gradientToogleStep = 3;
$dataTextOffset = 1;
$limit = 10;

$c = 1;
$data = array();
for($x = $startYear; $x <= $endYear; $x++) {
    if($startYear == $endYear) {
        $stop = $endMonth;
        $start = $startMonth;
    } else {
        $stop = 12;
        $start = 1;
        if($x == $endYear) $stop = $endMonth;
        if($x == $startYear) $start = $startMonth;
    }
    for($i = $start; $i <= $stop; $i++) {
        //echo $c++." - ".getmonth($i, 1)."/".$x."<br />";
        $zero = "";
        if(strlen($i) < 2) $zero = "0";
        $SQL = "SELECT SUM(ordTotalValueAfterDsc2) AS total FROM ".DBToken."order WHERE
                        ordDate >= '".$x.$zero.$i."01000000' AND
                        ordDate <= '".$x.$zero.$i."31235959' AND
                        ordChgHistoryFlg <> '0'";
        $qry = @mysqli_query($link, $SQL);
        $obj = @mysqli_fetch_object($qry);

        //array_push($data, replPtC(sprintf("%01.2f", $obj->total)));
        array_push($data, $obj->total);
    }
}

// detect data maximum and create scaleval for the bars
$atmp = $data;
sort($atmp);
$atmp = array_reverse($atmp);
$maxval = $atmp[0];
($layout == 0) ? $scaleval = doubleval($maxval / $ysize) : $scaleval = doubleval($maxval / $xsize);
$scalevalend = $scaleval + doubleval(($scaleval / 100) * 10);
// avoid division by zero errors if there is no data
if($scalevalend == 0) $scalevalend = 1;
if($scaleval == 0) $scaleval = 1;

$dia = new dia_bar($xsize, $ysize, 80, 50, 60, 30 + $addRightSpc, $data, $layout);
$dia->setBackgroundColor('white');
$dia->setDataFontColor('darkgrey');

//$dia->setGradientTotal('gslightblue', 'white');
$dia->setGradientToogleStep($gradientToogleStep, 'gslightblue', 'white', 'white', 'lightgrey');
$dia->recalcYStep($scalevalend, $maxval);
$dia->createGrid(20, 'grey');
$dia->createDiaBorder('grey');

if($layout == 0) {
    $spc = ($dia->xstep / 100) * 38;
} else {
    $spc = ($dia->ystep / 100) * 38;
}

if($barlayout == 0) $dia->displayBars($scalevalend, 'gsdarkorange', 'gslightorange', $spc, $dataTextOffset, 1);
if($barlayout == 1) $dia->displayGradientBars($scalevalend, 'white', 'darkgreen', 'darkgrey', $spc, 0, 1);

$ruler = 25;
$rulerval = doubleval($ruler / $scaleval);
if($rulerval < 4) $rulerval = 4;
if($rulerval > 10) $rulerval = 10;

$dia->addRulersX('darkgrey', $rulerval, 'bottom');
$dia->addRulersY('darkgrey', $rulerval, 'left');

if($layout == 0) {
    $dia->addRulersYText('darkgrey', 2, 55);
    $dia->addRulersXDataMonth('darkgrey', 2, $SESS_languageIdNo, $startMonth, $startYear);
} else {
    $dia->addRulersXText('darkgrey', 2, 5);
    $dia->addRulersYDataMonth('darkgrey', 2, 32, $SESS_languageIdNo, $startMonth, $startYear);
}

$dia->setHText(L_dynsb_VolumeOfSales." ".L_dynsb_In." [".$SESS_Currency."]", 3, 20 - ($layout * 10), 15, 'gsdarkblue');
$dia->setHText($strPeriod, 2, 20 - ($layout * 10), 30, 'gsdarkorange');

$dia->createOutput();

?>
