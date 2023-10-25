<?php
//******************************************************************************/
//* File: mod.gsarticleviews.show.php                                          */
//******************************************************************************/

//Class class.pageStatistics.php has to be initalised before the session
//start in "login.check.inc.php"
require("../../../../class/class.pagestatistics.php");

require("../../../../include/login.check.inc.php");
require_once("../../../../include/functions.inc.php");

require("../../../../class/class.diagram.bar.php");

$ps = $_SESSION['pageviews'];
$lang=$ps->getLang();

/***************** Sprachdatei ************************************************/
if (!isset($lang) || strlen(trim($lang)) == 0)
{
    $lang = "deu";
}
else
{
	if(!file_exists("../../../../lang/lang_".$lang.".php"))
  {
    $lang = "deu";
  }
}

include("../../../../lang/lang_".$lang.".php");
/******************************************************************************/


if (!isset($_SESSION['SESS_languageIdNo']) || strlen(trim($_SESSION['SESS_languageIdNo'])) == 0)
{
  die ("<br />error: missing session parameter!<br />");
} else {
	$SESS_languageIdNo = $_SESSION['SESS_languageIdNo'];
}

 

//get get get
$ysize = $ps->getYsize();
$xsize = $ps->getXsize();

 $gradientToogleStep = $ps->getGradientToogleStep();
$dataTextOffset		= $ps->getDataTextOffset();
$gradientToogleStep=10;

($ysize > 600) ? $addRightSpc = 0 : $addRightSpc =  60; //TODO ?needed for what?

$layout    = $ps->getLayout();
$barlayout = $ps->getBarlayout();

$endDate = $ps->getStatEndDate();
$startDate = $ps->getStatStartDate();


//Get Statistics
$qry = $ps->queryGetUserclicks();


//reduce array to one column
$data=array();
for ($i=0;$i<count($qry);$i++)
{
 $data[]=$qry[$i][2];
}

$values=array();
for ($i=0;$i<count($qry);$i++)
{
 $values[]=$qry[$i][0];
}


$scaleval = $ps->calculateScaleval($data);
$scalevalend = $ps->calculateScalevalEnd($scaleval);


//this is shown in the diagram
if($startDate == $endDate) {
    $strPeriod = L_dynsb_From.": ".timestamp_mysql2german($startDate);
} else {
    $strPeriod = L_dynsb_From.": ".timestamp_mysql2german($startDate)." ".L_dynsb_To.": ".timestamp_mysql2german($endDate);
}




//create DIA object
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
    $dia->addRulersXDataValues('darkgrey', 2,$values);
} else {
    $dia->addRulersXText('darkgrey', 2, 5);
    $dia->addRulersYDataValues('darkgrey', 2, 52,$values);
}

$dia->setHText($ps->getDiagramName(), 3, 20 - ($layout * 10), 15, 'gsdarkblue');
$dia->setHText($strPeriod, 2, 20 - ($layout * 10), 30, 'gsdarkorange');

$dia->createOutput();

?>
