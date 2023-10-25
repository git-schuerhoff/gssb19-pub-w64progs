<?php
/******************************************************************************/
/* File: mod.coupon.pdf.php                                                   */
/******************************************************************************/

require("../../include/login.check.inc.php");
require_once("../../include/functions.inc.php");
require("../../../conf/db.const.inc.php");
require_once("../../class/class.ezpdf.php");
require_once("../../class/class.shoplog.php");

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
  or die("<br />aborted: can't connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");
$chgApplicId = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1)); // script name

if (!isset($_SESSION['SESS_userIdNo']) || strlen(trim($_SESSION['SESS_userIdNo'])) == 0) {
  die ("<br />error: missing session parameter!<br />");
} else {
	$SESS_userIdNo = $_SESSION['SESS_userIdNo'];
}
if (!isset($_SESSION['SESS_userLogin']) || strlen(trim($_SESSION['SESS_userLogin'])) == 0) {
  die ("<br />error: missing session parameter!<br />");
} else {
	$SESS_userId = $_SESSION['SESS_userLogin'];
}
if (!isset($_SESSION['SESS_languageIdNo']) || strlen(trim($_SESSION['SESS_languageIdNo'])) == 0) {
  die ("<br />error: missing session parameter!<br />");
} else {
	$SESS_languageIdNo = $_SESSION['SESS_languageIdNo'];
}

$coupId = $_REQUEST['coupId'];

$CD = new shoplog();
$CouponData = $CD->getPDFCouponData($coupId);


$currentDate = date('Ymd');
$pdf = new Cezpdf('A5','portrait');
$maxHeight = "590,5";

$ImagePath = "../../image/upload/";

$ts = file_exists("../../image/upload/");


$perms = substr(decoct(fileperms($ImagePath)),sizeof(decoct(fileperms($ImagePath)))-4,3);
if($perms!="777")
{
  echo L_dynsb_RightsErrorCoupon;
}
else
{
  $isImage = file_exists($ImagePath.$CouponData["Image"]);
  
  if($isImage)
  {
    $size = getimagesize ($ImagePath.$CouponData["Image"]);
  
    if($CouponData["ImageXsize"]==0)
    {
      $maxHeight=$maxHeight-$size[1]-10;
      $pdf->addJpegFromFile($ImagePath.$CouponData["Image"],20,$maxHeight);  
    }
    else
    {
      if($CouponData["ImageYsize"]==0)
      {
        $maxHeight=$maxHeight-$size[1]-10;
        $pdf->addJpegFromFile($ImagePath.$CouponData["Image"],20,$maxHeight,$CouponData["ImageXsize"]);  
      }
      else
      {
        $maxHeight=$maxHeight-$CouponData["ImageYsize"]-10;  
        $pdf->addJpegFromFile($ImagePath.$CouponData["Image"],20,$maxHeight,$CouponData["ImageXsize"],$CouponData["ImageYsize"]);  
      }
    }
  }
  else
  { 
    $error_nopic = ">> ".L_dynsb_NoImageFileOnWebserver." <<";
    $pdf->setColor(255,0,0);
    $pdf->ezText($error_nopic,10);
  }
  $pdf->setColor(0,0,0);
  $pdf->ezSetDy(-50); 
  $pdf->selectFont('fonts/helvetica.afm');
  $pdf->ezText(utf8_decode($CouponData["Text1"]),12);
  $pdf->ezSetDy(-20);
  $pdf->ezText("Code: ".$CouponData["Code"]."",12);
  $pdf->ezSetDy(-10);
  $Price = number_format($CouponData["Price"]*(-1),2,",",".");
  $pdf->ezText("Wert: ".$Price." ".$CouponData["Currency"]."",12);
  $pdf->ezSetDy(-20);
  $pdf->ezText(utf8_decode($CouponData["Text2"]),12);
  $pdf->ezSetDy(-10);
  $pdf->ezStream();
}
?>
