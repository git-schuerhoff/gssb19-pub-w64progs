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

$limit				= $ps->getLimit();
$gradientToogleStep = $ps->getGradientToogleStep();
$dataTextOffset		= $ps->getDataTextOffset();

//get get get
$ysize = $ps->getYsize();
$xsize = $ps->getXsize();
($ysize > 600) ? $addRightSpc = 0 : $addRightSpc =  60; //TODO ?needed for what?

$layout    = $ps->getLayout();
$barlayout = $ps->getBarlayout();

$endDate = $ps->getStatEndDate();
$startDate = $ps->getStatStartDate();


//Get Statistics
$qry = $ps->queryGetUserDetails();
$data = $ps->sqlResultset2array($qry,"monUserHits");

$scaleval = $ps->calculateScaleval($data);
$scalevalend = $ps->calculateScalevalEnd($scaleval);


$qry = $ps->queryGetUserDetails("",true);



//this is shown in the diagram
if($startDate == $endDate) {
    $strPeriod = L_dynsb_From.": ".timestamp_mysql2german($startDate);
} else {
    $strPeriod = L_dynsb_From.": ".timestamp_mysql2german($startDate)." ".L_dynsb_To.": ".timestamp_mysql2german($endDate);
}


//create DIA object (pie)
$dia = new dia_bar($xsize, $ysize, 80, 50, 60, 30 + $addRightSpc, $qry, $layout);
$dia->setBackgroundColor('white');
$dia->setDataFontColor('darkgrey');
$dia->setHText(L_dynsb_statUserUsedBrowser, 3, 20 - ($layout * 10), 15, 'gsdarkblue');
$dia->setHText($strPeriod, 2, 20, 30, 'gsdarkorange');
$dia->createPie();


?>
