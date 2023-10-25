<?php
/******************************************************************************/
/* File: gsb_show_dynsb.php                                                   */
/******************************************************************************/

require("../conf/db.const.inc.php");
require_once("include/functions.inc.php");

/***************** Sprachdatei ************************************************/
if (!isset($_REQUEST['lang']) || strlen(trim($_REQUEST['lang'])) == 0) 
{
    $lang = "deu";
} 
else 
{
	$lang = $_REQUEST['lang'];
	if(!file_exists("lang/lang_".$lang.".php"))
  {
    $lang = "deu";
  }
}

include("lang/lang_".$lang.".php");
/******************************************************************************/

/***************** Datenbankverbindung*****************************************/
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) 
  or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="css/link.css">
</head>
<body>
<center>
<?php

$itemdata_tab = DBToken."itemdata";
$price_tab = DBToken."price";
$dsb7_orderpos = DBToken."orderpos";
$dsb7_monitorlog = DBToken."monitorlog";

$current_itemNumber  = $_REQUEST['ItemNumber'];
$current_LanguageId  = $_REQUEST['LanguageId'];

$coding = $_REQUEST['coding'];

if($coding==1)
{
  $current_itemNumber = base64_decode($current_itemNumber);
}

$SQL = "SELECT itemItemDescription, itemUpdateTime, itemCreateTime FROM ".$itemdata_tab." where itemItemNumber = '".$current_itemNumber."' AND itemLanguageId = '".$current_LanguageId."'";

$qry = @mysqli_query($link,$SQL);
$obj = @mysqli_fetch_object($qry);


$itemItemDescription = $obj->itemItemDescription;
$itemCreateTime = getCreateTime($current_LanguageId, $obj->itemCreateTime);
$itemUpdateTime = getUpdateTime($current_LanguageId, $obj->itemUpdateTime);
$languageIdNo = getLanguageIdNo($current_LanguageId);
$OrderCount = getOrderCount($dsb7_orderpos, $current_itemNumber);
$ItemViews = getItemViews($dsb7_monitorlog, $current_itemNumber);
$BestsellerPos =  getBestsellerPosition($current_itemNumber);
?>
<br />
<table border='0' width='60%' border='0' class="login">
  <tr><td colspan='2'><?echo $current_itemNumber." - ".$itemItemDescription;?></td></tr>
  <tr><td style="background-color:white"><?echo L_dynsb_Language;?>:</td><td style="background-color:white" align='center'><?echo $current_LanguageId;?></td></tr>
  <tr><td><?echo L_dynsb_CreatedOn;?>:</td><td align='center'><?echo $itemCreateTime;?></td></tr>
  <tr><td style="background-color:white"><?echo L_dynsb_UpdatedOn;?>:</td><td style="background-color:white" align='center'><?echo $itemUpdateTime;?></td></tr>
  <tr><td><?echo L_dynsb_PositionOfBestsellerList;?>:</td><td align='center'><?echo $BestsellerPos;?></td></tr>
  <tr><td style="background-color:white"><?echo L_dynsb_NumberOfOrder;?>:</td><td style="background-color:white" align='center'><?echo $OrderCount;?></td></tr>
  <tr><td><?echo L_dynsb_ArticleViews;?>:</td><td align='center'><?echo $ItemViews;?></td></tr>
</table>
</center>
</body>
<?php
function getLanguageIdNo($LanguageId)
{
  if($LanguageId=="deu")
  {
    $SESS_languageIdNo = 1;
  }
  else
  {
    $SESS_languageIdNo = 2;
  }
  return $SESS_languageIdNo;
}

function getBestsellerPosition($itemNumber)
{
  $path = substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"dynsb/"));
  $BestsellerPos = 0;
  $count = 1;
  
  if(file_exists("class/class.shoplog.php"))
  {
    require_once("class/class.shoplog.php");
  }

  $bs = new shoplog();
  $bsqry = $bs->getBestsellerList("0");
  while($o = @mysqli_fetch_object($bsqry))
  { 
    if($o->ordpItemId==$itemNumber)
    {
      $BestsellerPos = $count;
    }
    $count++;
  }
  return $BestsellerPos;
}

function getItemViews($dsb7_monitorlog, $itemNumber)
{
	global $link;
  $SQL = "SELECT count(monItemNumber) as num FROM ".$dsb7_monitorlog." where monItemNumber = '".$itemNumber."'";
  $qry = @mysqli_query($link,$SQL);  
  $obj = @mysqli_fetch_object($qry);
  $ItemViews = $obj->num;
  if($ItemViews=="")
  { $ItemViews = 0; }
  return $ItemViews;
}

function getOrderCount($dsb7_orderpos, $itemNumber)
{
	global $link;
  $SQL = "select ordpQty  from ".$dsb7_orderpos." where ordpItemId = '".$itemNumber."'";
  $qry = @mysqli_query($link,$SQL);
  $OrderCount = 0;
  while($obj = @mysqli_fetch_object($qry))
  {
    $OrderCount = $OrderCount + $obj->ordpQty;
  }
  return $OrderCount;
}

function getCreateTime($LanguageId, $CreateTime)
{
  if($LanguageId=="deu")
  {
    $itemCreateTime = substr($CreateTime,6,2).".".substr($CreateTime,4,2).".".substr($CreateTime, 0,4);
    $SESS_languageIdNo = 1;
  }
  else
  {
    $itemCreateTime = substr($CreateTime,0,4)."-".substr($CreateTime,4,2)."-".substr($CreateTime, 6,2);
    $SESS_languageIdNo = 2;
  }
  return $itemCreateTime;
}

function getUpdateTime($LanguageId, $UpdateTime)
{
  if($LanguageId=="deu")
  {
    $itemUpdateTime = substr($UpdateTime,6,2).".".substr($UpdateTime,4,2).".".substr($UpdateTime, 0,4);
    $SESS_languageIdNo = 1;
  }
  else
  {
    $itemUpdateTime = substr($UpdateTime,0,4)."-".substr($UpdateTime,4,2)."-".substr($UpdateTime, 6,2);
    $SESS_languageIdNo = 2;
  }
  return $itemUpdateTime;
}


?>
