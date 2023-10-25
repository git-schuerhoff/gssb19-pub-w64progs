<?php

/*
file: mod.gssearchword.show.php
*/

require("../../../../include/login.check.inc.php");
require_once("../../../../include/functions.inc.php");
require("../../../../../conf/db.const.inc.php");
require("../../../../class/class.diagram.bar.php");

/***************** Sprachdatei ************************************************/
if (!isset($_REQUEST['lang']) || strlen(trim($_REQUEST['lang'])) == 0) {
    $lang = "deu";
}
else {
	$lang = $_REQUEST['lang'];
	if(!file_exists("../../../../lang/lang_".$lang.".php")) {
    $lang = "deu";
  }
}
include("../../../../lang/lang_".$lang.".php");
/******************************************************************************/

// connect to database server or die
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");

if (!isset($_SESSION['SESS_languageIdNo']) || strlen(trim($_SESSION['SESS_languageIdNo'])) == 0) {
  die ("<br />error: missing session parameter!<br />");
} else {
	$SESS_languageIdNo = $_SESSION['SESS_languageIdNo'];
}

$layout = intval(trim($_REQUEST['layout']));
if($layout === null) $layout = 0;

$viewmode = intval(trim($_REQUEST['vm']));
if($viewmode === null) $viewmode = 0;

$barlayout = intval(trim($_REQUEST['bl']));
if($barlayout === null) $barlayout = 0;

$startDate = (trim($_REQUEST['sd']));
if($startDate == "") $startDate = date("Ymd")."000000";

$endDate = (trim($_REQUEST['ed']));
if($endDate == "") $endDate = date("Ymd")."235959";

if($startDate == $endDate) {
    $strPeriod = L_dynsb_From.": ".timestamp_mysql2german($startDate);
} else {
    $strPeriod = L_dynsb_From.": ".timestamp_mysql2german($startDate)." ".L_dynsb_To.": ".timestamp_mysql2german($endDate);
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

switch($viewmode) {
    case 0:
        // top 10
        $data = array(100,200,300,900,800,12700,600,500,400,300);
        $gradientToogleStep = 2;
        $dataTextOffset = 1;
        $limit = 10;
    break;

    case 1:
        // top 20
        $data = array(100,200,300,163,284,234,656,234,343,53,645,234,324,100,200,300,324,100,200,300);
        $gradientToogleStep = 4;
        $dataTextOffset = 1;
        $limit = 20;
    break;

    case 2:
        // top 50
        $data = array(100,200,300,163,284,234,656,234,343,53,645,234,324,100,200,300,324,100,200,300,100,20,143,163,284,234,656,234,343,53,645,234,324,100,200,300,324,100,200,300,645,234,324,100,200,300,324,100,200,300);
        $gradientToogleStep = 10;
        $dataTextOffset = 1;
        $limit = 50;
    break;
}

$SQL = "SELECT monActionIdNo, monValue,  COUNT(*) AS qty FROM ".DBToken."monitorlog WHERE
            monChgTimestamp >= '".$startDate."' AND
            monChgTimestamp <= '".$endDate."' AND
            monActionIdNo = '3'
        GROUP BY monActionIdNo, monValue
        ORDER BY qty DESC LIMIT 0,".$limit;
$qry = @mysqli_query($link, $SQL);

$data = array();
while($obj = @mysqli_fetch_object($qry)) {
    array_push($data, $obj->qty);
}

if(count($data) < $limit) {
    $diff = $limit - count($data);
    for($i = 0; $i < $diff; $i++) {
        array_push($data, 0);
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

if($layout == 0)
{
    $spc = ($dia->xstep / 100) * 38;
} else {
    $spc = ($dia->ystep / 100) * 38;
}

if($barlayout == 0) $dia->displayBars($scalevalend, 'gsdarkorange', 'gslightorange', $spc, $dataTextOffset);
if($barlayout == 1) $dia->displayGradientBars($scalevalend, 'white', 'darkgreen', 'darkgrey', $spc, 0);

$ruler = 25;
$rulerval = doubleval($ruler / $scaleval);
if($rulerval < 4) $rulerval = 4;
if($rulerval > 10) $rulerval = 10;

$dia->addRulersX('darkgrey', $rulerval, 'bottom');
$dia->addRulersY('darkgrey', $rulerval, 'left');

if($layout == 0) {
    $dia->addRulersYText('darkgrey', 2, 55);
    $dia->addRulersXData('darkgrey', 2);
} else {
    $dia->addRulersXText('darkgrey', 2, 5);
    $dia->addRulersYData('darkgrey', 2, 52);
}

$dia->setHText(L_dynsb_MostSearchedWord." / Top ".count($data), 3, 20 - ($layout * 10), 15, 'gsdarkblue');
$dia->setHText($strPeriod, 2, 20 - ($layout * 10), 30, 'gsdarkorange');

$dia->createOutput();

?>
