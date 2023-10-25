<?php
/******************************************************************************/
/* File: shoporder.search.php      error_reporting(E_ALL);
ini_set("display_errors","on");                                           */
/******************************************************************************/

// die(var_dump($_GET));
//require("../include/login.check.inc.php");
require_once("../include/functions.inc.php");
require("../../conf/db.const.inc.php");
function gs_mysqli_fetch_object($qry){
	if($qry <> NULL){
		return mysqli_fetch_object($qry);
	} else {
		return NULL;
	}
}
/***************** Sprachdatei ************************************************/
if (!isset($_REQUEST['lang']) || strlen(trim($_REQUEST['lang'])) == 0)
{
    $lang = "deu";
}
else
{
	$lang = $_REQUEST['lang'];
	if(!file_exists("../lang/lang_".$lang.".php"))
  {
    $lang = "deu";
  }
}

include("../lang/lang_".$lang.".php");

/***************** Datenbankverbindung*****************************************/
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase)
  or die("<br />aborted: can�t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");




//if(!$objprop) {


/*} else {

	$ownPrintFlg = $objprop->ownPrintFlg;
    $logoOffsetX = $objprop->logox;
    $logoOffsetY = $objprop->logoy;
    $logoWidth = $objprop->logoWidth;

    $footOffsetX = $objprop->footx;
    $footOffsetY = $objprop->footy;
    $footOffsetCompanyX = $objprop->footCompanyx;
    $footOffsetCompanyY = $objprop->footCompanyy;
    $footOffsetCEOX = $objprop->footCEOx;
    $footOffsetCEOY = $objprop->footCEOy;
    $footOffsetBankX = $objprop->footBankx;
    $footOffsetBankY = $objprop->footBanky;
    $footOffsetTelecomX = $objprop->footTelecomx;
    $footOffsetTelecomY = $objprop->footTelecomy;

    $infoOffsetX = $objprop->infox;
    $infoOffsetY = $objprop->infoy;

    $addressOffsetX = $objprop->toAddressx;
    $addressOffsetY = $objprop->toAddressy;

    $pageNumberOffsetX = $objprop->pageNox;
    $pageNumberOffsetY = $objprop->pageNoy;

    $titleOffsetX = $objprop->titlex;
    $titleOffsetY = $objprop->titley;

    $senderName = $objprop->ownCompanyName;
    $senderStreet = $objprop->ownStreet;
    $senderZipCode = $objprop->ownZipCode;
    $senderCity = $objprop->ownCity;

    $footCompany1 = $objprop->footCompany1;
    $footCompany2 = $objprop->footCompany2;
    $footCompany3 = $objprop->footCompany3;
    $footCompany4 = $objprop->footCompany4;

    $footCEO1 = $objprop->footCEO1;
    $footCEO2 = $objprop->footCEO2;
    $footCEO3 = $objprop->footCEO3;
    $footCEO4 = $objprop->footCEO4;

    $footBank1 = $objprop->footBank1;
    $footBank2 = $objprop->footBank2;
    $footBank3 = $objprop->footBank3;
    $footBank4 = $objprop->footBank4;

    $footTelecom1pre = $objprop->footTelecomPre1;
    $footTelecom2pre = $objprop->footTelecomPre2;
    $footTelecom3pre = $objprop->footTelecomPre3;
    $footTelecom4pre = $objprop->footTelecomPre4;
    $footTelecom1 = $objprop->footTelecom1;
    $footTelecom2 = $objprop->footTelecom2;
    $footTelecom3 = $objprop->footTelecom3;
    $footTelecom4 = $objprop->footTelecom4;

    $textBlockSize = $objprop->fontSizeStandard;
    $posTextSize = $objprop->fontSizeStandard;
    $posTitleSize = $objprop->fontSizeStandard;
    $footTextSize = $objprop->fontSizeFoot;
    $addressTextSize = $objprop->fontSizeStandard;
    $addressTextSizeSmall = 7;
    $infoTextSize = $objprop->fontSizeStandard;
    $pageNumberTextSize = $objprop->fontSizePageNo;
    $titleTextSize = $objprop->fontSizeTitle;
    $aboutTextSize = $objprop->fontSizeTitle;

    $fontIdNo = $objprop->fontIdNo;
}*/
//

$posTitle1 = "Pos.";
$posTitle2 = "ArtikelNr. / Beschreibung";
$posTitle3 = "Menge";
$posTitle4 = "MwSt in %";
$posTitle5 = "E-Preis";
$posTitle6 = "Preis in";
$posTitle7 = "Betrag";
$posTitle8 = "";

$greetings = "Mit freundlichem Gru�";
$signer = "Karl Mustermann";

$date = date("d.m.Y");
 
// DEBUG  mit d =1!
// $d=1;
//$d = 2;

if(!isset($_REQUEST['d'])) {
	$d = 0;
} else {
	$d = intval(trim($_REQUEST['d']));
}
/*
if(!isset($_REQUEST['rt'])) {
    $reportType = "ERROR! missing reportType!";
} else {
    $index = intval($_REQUEST['rt']);
    $typeArray = array(0 => 'Auftragsbestätigung', 1 => 'Lieferschein', 2 => 'Rechnung', 3 => 'Bestellung');
    $reportType = $typeArray[$index];
}
*/
$title = 'Rechnung';//$reportType;
$reportType = 'Rechnung';
$pKey=$_GET['pk'];

// include pdf class
include '../class/class.ezpdf.php';

// define a clas extension to allow the use of a callback to get the table of contents, and to put the dots in the toc
class Creport extends Cezpdf {

    var $reportContents = array();

    function __construct($p,$o){
    	parent::__construct($p,$o);
    }

}

//$pdf = new Cezpdf('a4','portrait');
$pdf = new Creport('a4','portrait');

chdir("../../");
include_once('inc/class.shopengine.php');
include_once('inc/class.order.php');
$order = new Order();
session_start();
$order = unserialize($_SESSION['order']);
$se = new gs_shopengine();
$modInvoiceOK = (($se->demo == 1) || ($se->checkKeyValid('InvoiceModule')));
chdir("dynsb/shoporder/");

/*************************************************************/
if($se->get_setting('chkFooterPrint') == 'False'){
    $foot = 0;
} else {
    $foot = 1;
}
if($se->get_setting('chkLogoPrint') == 'False'){  
    $logo = 0;
} else {
    $logo = 1;
}    
	$ownPrintFlg = 1;
    $logoOffsetX = intval($se->get_setting('edLogoXoffset_Text'));//10;
    $logoOffsetY = intval($se->get_setting('edLogoYoffset_Text'));//0;
    $logoWidth = intval($se->get_setting('edLogoWidth_Text'));//250;
    //die(var_dump($_SESSION['sb_settings']));
    $footOffsetX = intval($se->get_setting('edFooterXoffset_Text'));//0;
    $footOffsetY = intval($se->get_setting('edFooterYoffset_Text'));//10;   
    $footOffsetCompanyX = intval($se->get_setting('edAreaFirmXoffset_Text'));//0;
    $footOffsetCompanyY = intval($se->get_setting('edAreaFirmYoffset_Text'));//0;
    $footOffsetCEOX = intval($se->get_setting('edAreaManXoffset_Text'));//0;
    $footOffsetCEOY = intval($se->get_setting('edAreaManYoffset_Text'));//0;
    $footOffsetBankX = intval($se->get_setting('edAreaBankXoffset_Text'));//0;
    $footOffsetBankY = intval($se->get_setting('edAreaBankYoffset_Text'));//0;
    $footOffsetTelecomX = intval($se->get_setting('edAreaCommXoffset_Text'));//0;
    $footOffsetTelecomY = intval($se->get_setting('edAreaCommYoffset_Text'));//0;

    $infoOffsetX = intval($se->get_setting('edAddInfoXoffset_Text'));//30;
    $infoOffsetY = intval($se->get_setting('edAddInfoYoffset_Text'));//-30;

    $addressOffsetX = intval($se->get_setting('edAddressXoffset_Text'));//0;
    $addressOffsetY = intval($se->get_setting('edAddressYoffset_Text'));//0;

    $pageNumberOffsetX = intval($se->get_setting('edPageNoXoffset_Text'));//0;edPageNoXoffset_Text
    $pageNumberOffsetY = intval($se->get_setting('edPageNoYoffset_Text'));//10;

    $titleOffsetX = intval($se->get_setting('edHeadingXoffset_Text'));//0;
    $titleOffsetY = intval($se->get_setting('edHeadingYoffset_Text'));//0;

    $senderName = $se->get_setting('edShopCompany_Text');//"GS Software AG";
    $senderStreet = $se->get_setting('edShopStreet_Text');//"Johann-Krane-Weg 8";
    $senderZipCode = $se->get_setting('edShopZipCode_Text');//"48149";
    $senderCity = $se->get_setting('edShopCity_Text');//"Münster";

    $footCompany1 = $se->get_setting('edShopCompany_Text');//"GS Software AG";
    $footCompany2 = $se->get_setting('edShopStreet_Text');//"Johann-Krane-Weg 8";
    $footCompany3 = $se->get_setting('edShopZipCode_Text').' '.$se->get_setting('edShopCity_Text');//"48149 Münster";
    $footCompany4 = $se->get_setting('edShopCountry_Text');//"Deutschland";

    $footCEO1 = $se->get_setting('edAreaManLine1_Text');//"Keine Daten";
    $footCEO2 = $se->get_setting('edAreaManLine2_Text');//"Keine Daten";
    $footCEO3 = $se->get_setting('edAreaManLine3_Text');//"Keine Daten";
    $footCEO4 = $se->get_setting('edAreaManLine4_Text');//"Keine Daten";

    $footBank1 = $se->get_setting('edAreaBankLine1_Text');//"Keine Daten";
    $footBank2 = $se->get_setting('edAreaBankLine2_Text');//"Keine Daten";
    $footBank3 = $se->get_setting('edAreaBankLine3_Text');//"Keine Daten";
    $footBank4 = $se->get_setting('edAreaBankLine4_Text');//"Keine Daten";

    $footTelecom1pre = $se->get_setting('edAreaCommBefLine1_Text');//"Telefon";
    $footTelecom2pre = $se->get_setting('edAreaCommBefLine2_Text');//"Telefax";
    $footTelecom3pre = $se->get_setting('edAreaCommBefLine3_Text');//"E-Mail";
    $footTelecom4pre = $se->get_setting('edAreaCommBefLine4_Text');//"Internet";
    $footTelecom1 = $se->get_setting('edAreaCommLine1_Text');//$se->get_setting('edShopTelephone_Text');//"0231-471122";
    $footTelecom2 = $se->get_setting('edAreaCommLine2_Text');//$se->get_setting('edShopFax_Text');//"0231-471124";
    $footTelecom3 = $se->get_setting('edAreaCommLine3_Text');//$se->get_setting('edShopEmail_Text');//"info@mustermann.de";
    $footTelecom4 = $se->get_setting('edAreaCommLine4_Text');//$se->get_setting('edAbsoluteShopPath_Text');//"www.mustermann.de";

    $standardTextSize = $se->get_setting('cbbStdText_Text');
    $standardTextSize = $standardTextSize + 10;   
    $titleTextSize = $se->get_setting('cbbHeadings_Text');
    $titleTextSize = $titleTextSize + 10;
    $footTextSize = $se->get_setting('cbbFooter_Text');
    $footTextSize = $footTextSize + 5;
    $pageNumberTextSize = $se->get_setting('cbbPageNo_Text');
    $pageNumberTextSize = $pageNumberTextSize + 8;
    
    $textBlockSize = $standardTextSize;//12;
    $posTextSize = $standardTextSize;//12;
    $posTitleSize = $standardTextSize;//12;
    //$footTextSize = 8;
    $addressTextSize = $standardTextSize;//12;
    $addressTextSizeSmall = 7;
    $infoTextSize = $standardTextSize;//12;
    //$pageNumberTextSize = 10;
    //$titleTextSize = 14;
    $aboutTextSize = 14;
    
    $fontIdNo = $se->get_setting('cbbFont_Text');//1;
/*************************************************************/
if($fontIdNo == 0) {
    $mainFont = './fonts/Times-Roman.afm';
    $boldFont = './fonts/Times-Bold.afm';
} else {
    $mainFont = './fonts/Helvetica.afm';
    $boldFont = './fonts/Helvetica-Bold.afm';
}
// select a font
$pdf->selectFont($mainFont);

// top, bottom, left, right
//$pdf -> ezSetMargins(230,70,70,50);
$pdf->ezSetMargins(300,90,70,50);

// start head & foot object
$all = $pdf->openObject();
$pdf->saveState();
$pdf->setStrokeColor(0,0,0,1);

if($foot == 1) {
     
    // bottom line
    $pdf->line(20-$footOffsetX,60-$footOffsetY,578-$footOffsetX,60-$footOffsetY);
    // bottom text
    $pdf->addText(30-$footOffsetX-$footOffsetCompanyX,50-$footOffsetY-$footOffsetCompanyY,$footTextSize,$footCompany1);
    $pdf->addText(30-$footOffsetX-$footOffsetCompanyX,50-$pdf->getFontHeight($footTextSize)-$footOffsetY-$footOffsetCompanyY,$footTextSize,$footCompany2);
    $pdf->addText(30-$footOffsetX-$footOffsetCompanyX,50-$pdf->getFontHeight($footTextSize*2)-$footOffsetY-$footOffsetCompanyY,$footTextSize,$footCompany3);
    $pdf->addText(30-$footOffsetX-$footOffsetCompanyX,50-$pdf->getFontHeight($footTextSize*3)-$footOffsetY-$footOffsetCompanyY,$footTextSize,$footCompany4);
    $pdf->addText(160-$footOffsetX-$footOffsetCEOX,50-$footOffsetY-$footOffsetCEOY,$footTextSize,$footCEO1);
    $pdf->addText(160-$footOffsetX-$footOffsetCEOX,50-$pdf->getFontHeight($footTextSize)-$footOffsetY-$footOffsetCEOY,$footTextSize,$footCEO2);
    $pdf->addText(160-$footOffsetX-$footOffsetCEOX,50-$pdf->getFontHeight($footTextSize*2)-$footOffsetY-$footOffsetCEOY,$footTextSize,$footCEO3);
    $pdf->addText(160-$footOffsetX-$footOffsetCEOX,50-$pdf->getFontHeight($footTextSize*3)-$footOffsetY-$footOffsetCEOY,$footTextSize,$footCEO4);
    $pdf->addText(320-$footOffsetX-$footOffsetBankX,50-$footOffsetY-$footOffsetBankY,$footTextSize,$footBank1);
    $pdf->addText(320-$footOffsetX-$footOffsetBankX,50-$pdf->getFontHeight($footTextSize)-$footOffsetY-$footOffsetBankY,$footTextSize,$footBank2);
    $pdf->addText(320-$footOffsetX-$footOffsetBankX,50-$pdf->getFontHeight($footTextSize*2)-$footOffsetY-$footOffsetBankY,$footTextSize,$footBank3);
    $pdf->addText(320-$footOffsetX-$footOffsetBankX,50-$pdf->getFontHeight($footTextSize*3)-$footOffsetY-$footOffsetBankY,$footTextSize,$footBank4);
    $pdf->addText(470-$footOffsetX-$footOffsetTelecomX,50-$footOffsetY-$footOffsetTelecomY,$footTextSize,$footTelecom1pre);
    $pdf->addText(470-$footOffsetX-$footOffsetTelecomX,50-$pdf->getFontHeight($footTextSize)-$footOffsetY-$footOffsetTelecomY,$footTextSize,$footTelecom2pre);
    $pdf->addText(470-$footOffsetX-$footOffsetTelecomX,50-$pdf->getFontHeight($footTextSize*2)-$footOffsetY-$footOffsetTelecomY,$footTextSize,$footTelecom3pre);
    $pdf->addText(470-$footOffsetX-$footOffsetTelecomX,50-$pdf->getFontHeight($footTextSize*3)-$footOffsetY-$footOffsetTelecomY,$footTextSize,$footTelecom4pre);
    $pdf->addText(500-$footOffsetX-$footOffsetTelecomX,50-$footOffsetY-$footOffsetTelecomY,$footTextSize,$footTelecom1);
    $pdf->addText(500-$footOffsetX-$footOffsetTelecomX,50-$pdf->getFontHeight($footTextSize)-$footOffsetY-$footOffsetTelecomY,$footTextSize,$footTelecom2);
    $pdf->addText(500-$footOffsetX-$footOffsetTelecomX,50-$pdf->getFontHeight($footTextSize*2)-$footOffsetY-$footOffsetTelecomY,$footTextSize,$footTelecom3);
    $pdf->addText(500-$footOffsetX-$footOffsetTelecomX,50-$pdf->getFontHeight($footTextSize*3)-$footOffsetY-$footOffsetTelecomY,$footTextSize,$footTelecom4);
}

function convertImage($originalImage, $outputImage, $quality){
    // jpg, png, gif or bmp?
    $exploded = explode('.',$originalImage);
    $ext = $exploded[count($exploded) - 1]; 
    if (preg_match('/png/i',$ext)){
        $imageTmp=imagecreatefrompng($originalImage);
    } else if (preg_match('/gif/i',$ext)){$imageTmp=imagecreatefromgif($originalImage);}
    else if (preg_match('/bmp/i',$ext)){$imageTmp=imagecreatefromwbmp($originalImage);}
    else    {    return false;}
    // quality is a value from 0 (worst) to 100 (best)
    imagejpeg($imageTmp, $outputImage, $quality);
    imagedestroy($imageTmp);
    return true;
}

// Firmenlogo
if($logo == 1) {
    $img = $se->get_setting('edLogoFileName_Text');
    $img = '../../images/'.$img;
    $pdf->addJpegFromFile($img,360-$logoOffsetX,760-$logoOffsetY,$logoWidth);       
}

$pdf->restoreState();
$pdf->closeObject();

// add object-content to all Pages
$pdf->addObject($all,'all');

$start = 0;

//
// process document(s) by the amount of given pk's
//

for($i = 0; $i < 1; $i++)
{
if($start != 0) $pdf->ezNewPage();

$SQL = "SELECT * from ".DBToken."order WHERE ordIdNo = ".$pKey;
$qry = mysqli_query($link, $SQL);
$obj = gs_mysqli_fetch_object($qry);

/* Rechnungsanschrift */
//$toName = $obj->contactPersFirstName." ".$obj->contactPersMiddleName." ".$obj->contactPersLastName;
$toName=$obj->ordTitle.' '.$obj->ordFirstName.' '.$obj->ordLastName;
$toName2 ="";
$toFirm =$obj->ordFirmname;//'';// $obj->invAddrContactPersonName;
$toDepartment = "";
$toStreet = $obj->ordStreet.' '.$obj->ordStreet2;
$toZipCode = $obj->ordZipCode;
$toCity = $obj->ordCity;
$toCountry = $obj->ordCountry;
$toMail = $obj->ordEMail;

$orderNo=$obj->ordId;
$bookingDate = $obj->ordDate;
$bookingDate = substr( $bookingDate, 6, 2 ) . "." . substr( $bookingDate, 4, 2 ) . "." . substr( $bookingDate, 0, 4 );
$reason = L_dynsb_OrderFrom.$bookingDate;
$customerNo = $obj->ordCustomerId;
$vendorNo = '';
$invoiceNo = $obj->ordInvoiceNumber;
$invoiceDate = date_mysql2german($obj->ordInvoiceDate);

if($invoiceNo == ''){
    if($se->get_settingmemo('memoInvoiceStartNo') != ''){
        $sql = "SELECT count(ordInvoiceNumber) as invCount FROM ".DBToken."order where ordInvoiceNumber <> ''";
        $qry = mysqli_query($link, $sql);
        $invCount = mysqli_fetch_assoc($qry);
        if($invCount['invCount'] == 0){
            $invoiceNo = $se->get_settingmemo('memoInvoiceStartNo');
        } else {
            $sql = "SELECT max(ordInvoiceNumber) +1 as invId FROM ".DBToken."order where ordInvoiceNumber <> ''";
            $qry = mysqli_query($link, $sql);
            $invId = mysqli_fetch_assoc($qry);
            $invoiceNo = $invId['invId'];
        }
    } else {
        $invoiceNo = $orderNo;
    }
    $invoiceDate = $bookingDate;
}
$bookingNo=$orderNo;
//$bookingDate=$orderDate;

$taxnumber=$se->get_setting('edFirmVatNo_Text');//'HRB Münster Nr: 16300';//$objprop->ownTaxNo;
$Ust_ID=$se->get_setting('edFirmVatID_Text');//'DE 2017 3856';//$objprop->ownTaxNoId;
//$deliver_id = getentity("orderacthead","actionCurNo","orderActHeadIdNo = '".$obj->superActHeadIdNo."'");
$r_text = L_dynsb_PleaseIndicateWithPayment;//"Bitte bei Zahlung angeben";


$pdf->addText(390-$infoOffsetX,688-$infoOffsetY,$infoTextSize,L_dynsb_Date);
$pdf->addText(490-$infoOffsetX,688-$infoOffsetY,$infoTextSize,$date);
$n=1;
$pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_InvoiceNo);
$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$invoiceNo);
$n++;
$pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_InvoiceDate);
$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$invoiceDate);
//$n++;
//$pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$footTextSize+2,$r_text);
$n++;
if(($customerNo != 'false') and ($customerNo != '')){
    $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_CustomerNo);
    $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$customerNo);
    $n++;
}
//$pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,"Liefernummer");
//$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,$deliver_id);
$pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_TaxNo);
$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$taxnumber);
$n++;
$pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_VatIdNo);
$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$Ust_ID);

$pdf->selectFont($boldFont);
$pdf->addText(70-$titleOffsetX,590-$titleOffsetY,$titleTextSize+2,$title);
//$pdf->addText(70-$titleOffsetX,590-($titleOffsetY+$titleTextSize+2),$titleTextSize-1,L_dynsb_InvoiceNo.": ".$invoiceNo);
$pdf->selectFont($mainFont);

if($reason != "") {
//    $pdf->selectFont($boldFont);
    $pdf->addText(70-$titleOffsetX,590-($titleOffsetY+$titleTextSize),$infoTextSize-1,L_dynsb_YourOrderNo.$orderNo." / ".$reason);
//    $pdf->selectFont($mainFont);
}

// paper-marker
$pdf->line(0,566,17,566);

$cc = "";
//if($toCountry != $fromCountry) $cc = $pcc."-";

if($ownPrintFlg == 1) $pdf->addText(70-$addressOffsetX,723.5-$addressOffsetY,$addressTextSizeSmall,$senderName." * ".$senderStreet." * ".$cc.$senderZipCode." ".$senderCity);
//Firma
if($toFirm != "" && $toFirm != $toName) {
    $row++;
    $pdf->addText(70-$addressOffsetX,723.5-($addressOffsetY+($addressTextSize*2)),$addressTextSize,$toFirm);
}
$pdf->addText(70-$addressOffsetX,723.5-($addressOffsetY+($addressTextSize*3)),$addressTextSize,$toName);
if($toName2 != "") {
    $pdf->addText(70-$addressOffsetX,723.5-($addressOffsetY+($addressTextSize*4)),$addressTextSize,$toName2);
    $row = 4;
} else {
    $row = 3;
}
/*if($toHandsOf != "" && $toHandsOf != $toName) {
    $row++;
    $pdf->addText(70-$addressOffsetX,723.5-($addressOffsetY+($addressTextSize*$row)),$addressTextSize,$toHandsOf);
}*/
if($toDepartment != "") {
    $row++;
    $pdf->addText(70-$addressOffsetX,723.5-($addressOffsetY+($addressTextSize*$row)),$addressTextSize,$toDepartment);
}
$pdf->addText(70-$addressOffsetX,723.5-($addressOffsetY+($addressTextSize*($row+1))),$addressTextSize,$toStreet);
$pdf->addText(70-$addressOffsetX,723.5-($addressOffsetY+($addressTextSize*($row+2))),$addressTextSize,trim($toZipCode." ".$toCity));
//if($toCountry != $fromCountry) $pdf->addText(70-$addressOffsetX,723.5-($addressOffsetY+($addressTextSize*($row+3))),$addressTextSize,$toCountry);

$pn = $pdf->ezStartPageNumbers(570-$pageNumberOffsetX,65-$pageNumberOffsetY,$pageNumberTextSize,'',L_dynsb_PageNo,1,$boldFont);

// always open on the first page
if($start == 0) {
    $pdf->openHere('Fit');
}
$start++;


//$pdf->rectangle(0,0,200,90);
// debug-grid
/*
for($y = 0; $y < 84; $y++) {
    $pos = 10 * $y;
    $pdf->line(10,$pos,40,$pos);
    $pdf->addText(40,$pos-2,8,$pos);
}



$pdf->ezSetDy(-$pdf->getFontHeight($textBlockSize));
$pdf->ezText(trim($obj->txtBlkStart),$textBlockSize,array('justification'=>'left'));*/
//$pdf->ezSetDy(-$pdf->getFontHeight($textBlockSize));


// text before 1st pos
/*if(chkvt("order_order_text")){
	if(trim($obj->firstPosRemark) != '') {
		$pdf->ezText(trim($obj->firstPosRemark),$textBlockSize,array('justification'=>'left'));
		$pdf->ezSetDy(-$pdf->getFontHeight($textBlockSize*2));
	}
}*/


$SQL_pos = "SELECT * FROM ".DBToken."orderpos  WHERE  ordpOrdIdNo = ".$pKey." ORDER BY ordpPosNo";
$qry_pos = mysqli_query($link, $SQL_pos);

$once = 0;

// positionsbeschreibungsloop   innere Block 
while ($obj_pos = gs_mysqli_fetch_object($qry_pos))
{
    //die(var_dump($obj_pos));
  if($obj_pos->ordpQty > 0) {
	  $pdf->transaction('start');
	  $ok = 0;
	  while (!$ok)
	  {
	    $thisPageNum = $pdf->ezPageCount;
	    if($once == 0)
		 { // Überschrift
	      $pdf->selectFont($boldFont);
	      $pdf->addText(70,$pdf->y,$posTitleSize,$posTitle1);
	      $pdf->addText(100,$pdf->y,$posTitleSize,$posTitle2);
	      $pdf->addText(310,$pdf->y,$posTitleSize,$posTitle3);
	      $pdf->addText(350,$pdf->y,$posTitleSize, $posTitle8);
			//$pdf->addText(380,$pdf->y,$posTitleSize,$posTitle5); // E-Preis
			$pdf->addTextWrap(390,$pdf->y,40,$posTextSize,$posTitle5,'right');
	      $pdf->addText(440,$pdf->y,$posTitleSize,$posTitle4); // MWST
	      $pdf->addTextWrap(465,$pdf->y,80,$posTextSize,$posTitle7,'right');
	      $space = ($pdf->getFontHeight($posTitleSize)+6);
	      $pdf->ezSetDy(-($space/4));
	      $pdf->line(70,$pdf->y,555,$pdf->y);
	      $pdf->ezSetDy(-($space-($space/4)));
	      $pdf->selectFont($mainFont);
	      $once++;
	    } // Überschrift ende

		/* $SQL_creditgranted = "SELECT SUM(grantedQty) FROM creditgranted WHERE newInvoiceNo='".$invoiceNo."' AND newPosCurNo=".$obj_pos->posCurNo;
	    $valCredGrant = mysqli_fetch_assoc(mysqli_query($link, $SQL_creditgranted));
	    $valCredGrant = $valCredGrant['SUM(grantedQty)'];*/

		/* $posDiscount1 = $obj_pos->discount1;
	    $posDiscount2 = $obj_pos->discount2;
	 	 $posDiscount3 = $obj_pos->discount3;
	 	if(chkvt("order_pos_text") ){
	    	$prePosText = "".$obj_pos->posRemarkBefore."";
	    	$postPosText = "".$obj_pos->posRemarkAfter."";
	 	}*/

		 // neue logik
		 $posId = trim($obj_pos->ordpPosNo);
	    $posText = base64_decode($obj_pos->ordpItemDesc);
	    $posItemIdNo = $obj_pos->ordpItemId;
	    /*$itemText1 = trim($obj_pos->itemText1);
	    $itemText2 = trim($obj_pos->itemText2);
	    $itemText3 = trim($obj_pos->itemText3);
	    $itemText4 = trim($obj_pos->itemText4);*/
		 $posAmount = $obj_pos->ordpQty;
		 $posUOM = 'Stück';//$obj_pos->itemUomId ;

		 // $posTaxValue = $obj_pos->taxCodeId;
		 $posTaxValue = $obj_pos->ordpVATPrct;
	    if ($posTaxValue > '0') $posTaxValue = getGermanValue2($posTaxValue); else $posTaxValue = "  - ";
	    $posPrice = round(($obj_pos->ordpPrice * $obj_pos->ordpQty),2);
	    /*if($prePosText != "")
		 {
	      //$pdf->addText(100,$pdf->y,$posTextSize,trim($prePosText));
	      $pdf->ezText(trim($prePosText),$posTextSize,array('left'=>30,'justification'=>'left'));
	      $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
		 }*/
	    // Position
	    $pdf->selectFont($boldFont);
	    $pdf->addTextWrap(70,$pdf->y,20,$posTextSize,$obj_pos->ordpPosNo,'right');

		// Artikelnummer, -bezeichung
        $pdf->addText(100,$pdf->y,$posTextSize,$posItemIdNo);
        $pdf->selectFont($mainFont); 

		 // Menge
		 $pdf->addTextWrap(300,$pdf->y,40,$posTextSize,getGermanValue2($posAmount),'right');

		 // ME
		 //$pdf->addText(350,$pdf->y,$posTextSize,$posUOM);

		   // Preis
		   $pdf->addTextWrap(380,$pdf->y,50,$posTextSize,getGermanValue2($obj_pos->ordpPrice),'right');
	      // $pdf->addText(380,$pdf->y,$posTextSize,trim($obj_pos->salePrice));

		   // Mwst
		   $ptv = $posTaxValue;
	      if($ptv == 0) $ptv = "-/- ";
		   $pdf->addTextWrap(440,$pdf->y,40,$posTextSize,$ptv,'right');

		   // Positionsbetrag
		   $pdf->addTextWrap(485,$pdf->y,60,$posTextSize,getGermanValue2($posPrice),'right');

	    // Artikelbeschreibung in der nächste Zeile, falls die Beschreibung zu lang ist wird der Text umgebrochen
		$pdf->ezSetDy(-$pdf->getFontHeight($posTextSize)); // new line
        $rest = $pdf->addTextWrap(100,$pdf->y,350,$posTextSize,$posText);
        $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize)); // new line
        $setCursorToNextLine = 0;
        while($rest != ''){
            $rest = $pdf->addTextWrap(100,$pdf->y,350,$posTextSize,$rest);
            $setCursorToNextLine = 1;
            if ($rest != ''){
                $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize)); // new line
            }
        }
        $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize)); // new line
		/*if($itemText1 != "") {
            $pdf->addText(110,$pdf->y,$posTextSize,$itemText1);
            $setCursorToNextLine = 1;
            if($itemText2 != "" && $obj->itemPrintFlg == 1) {
                $setCursorToNextLine = 0;
                $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
            }
        }
		if($obj->itemPrintFlg == 1) {
			 // extra beschreibung 2
		    if($itemText2 != "")
			 {
		      $pdf->addText(110,$pdf->y,$posTextSize,$itemText2);
		      $setCursorToNextLine = 1;
		      if($itemText3 != "")
				{
		        $setCursorToNextLine = 0;
		        $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
		      }
		    }

			 // extra beschreibung 3
		    if($itemText3 != "")
			 {
		      if($setCursorToNextLine == 1) $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
		      $pdf->addText(110,$pdf->y,$posTextSize,$itemText3);
		      $setCursorToNextLine = 1;
		      if($itemText4 != "")
				{
		        $setCursorToNextLine = 0;
		        $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
		      }
		    }

			 // extra beschreibung 4
		    if($itemText4 != "")
			 {
		      if($setCursorToNextLine == 1) $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
		      $pdf->addText(110,$pdf->y,$posTextSize,$itemText4);
		      $setCursorToNextLine = 0;
		      $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
		    }
		}
		
		if(chkvt("item_addit_text")) {
			if($obj_pos->printAdditTextFlg == 1 && trim($obj_pos->itemAdditText) != '') {
	    		if($setCursorToNextLine == 1) $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
				$pdf->ezText($obj_pos->itemAdditText,$posTextSize,array('left'=>40,'justification'=>'left'));
		        if($setCursorToNextLine == 1) $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
			}
		}*/
		
		 // Mengengutschrift
		/* if ($valCredGrant)
		 {
	      $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize)/4);
	      // Auflistung Gutschriften
	  		$SQL_creditgranted = "SELECT grantedQty, actionCurNo FROM creditgranted WHERE newInvoiceNo=".$invoiceNo." AND newPosCurNo=".$obj_pos->posCurNo;
			$qry_SQL_creditgranted = mysqli_query($link, $SQL_creditgranted);
			while($objCredGrat=gs_mysqli_fetch_object($qry_SQL_creditgranted))
			{
			  $pdf->addText(110,$pdf->y,$posTextSize,"Gutschrift");
			  $pdf->addText(162,$pdf->y,$footTextSize+1,"(aus Gutschrift-Nr. ".$objCredGrat->actionCurNo.")");
	        $pdf->addText(280,$pdf->y,$posTextSize,"-");
	        $pdf->addTextWrap(300,$pdf->y,40,$posTextSize,getGermanValue2($objCredGrat->grantedQty),'right');
	        // ME
		     $pdf->addText(350,$pdf->y,$posTextSize,trim($posUOM));
		     $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
	      }
			$space = ($pdf->getFontHeight($posTitleSize)+6);
	      //$pdf->ezSetDy(-($space/4));
	      $pdf->line(280,$pdf->y,375,$pdf->y);
	      $pdf->ezSetDy(-($space-($space/4)));
	      $pdf->addText(110,$pdf->y,$posTextSize,"Berechnete Menge");
	      // Berechnete Menge
	      $pdf->addTextWrap(300,$pdf->y,40,$posTextSize,getGermanValue2($posAmount),'right');
			// ME
			$pdf->addText(350,$pdf->y,$posTextSize,trim($posUOM));
		   // Preis
		   $pdf->addTextWrap(380,$pdf->y,40,$posTextSize,trim($obj_pos->salePrice),'right');
	      // $pdf->addText(380,$pdf->y,$posTextSize,trim($obj_pos->salePrice));
		   // Mwst
		   $ptv = $posTaxValue;
	      if($ptv == 0) $ptv = "-/- ";
		   $pdf->addTextWrap(425,$pdf->y,40,$posTextSize,$ptv,'right');
		   // Positionsbetrag
		   $pdf->addTextWrap(485,$pdf->y,60,$posTextSize,getGermanValue2($posPrice),'right');
	      $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));

			$pdf->selectFont($mainFont);
		 }*/

		 // leere Zeile
	    if($setCursorToNextLine == 1) $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));

	    // Rabatte der einzelnen Positionen
		/* $posDiscount1 = $obj_pos->discount1;
	    $posDiscount2 = $obj_pos->discount2;
		 //$posDiscount3 = $obj_pos->discount3;
		 $discountType1Name= ($obj_pos->discountType1Name !="")? $obj_pos->discountType1Name : "Rabatt ";
		 $discountType2Name= ($obj_pos->discountType2Name !="")? $obj_pos->discountType2Name : "Rabatt ";
		 $discountType3Name= ($obj_pos->discountType3Name !="")? $obj_pos->discountType3Name : "Rabatt ";

		 // fetchen der discountbezeichnungen
	    // insert Discounts if available...
	    // Rabatt 1
	    if($posDiscount1 != "0")
		 {
	      if($obj_pos->discount1Flg == '1')
			{
	        // for %
	        $pdf->addText(110,$pdf->y,$posTextSize,$discountType1Name." ".getGermanValue2($posDiscount1)."%");
	        $pdf->addText(480,$pdf->y,$posTextSize,"-");
	        $result1 = round ((($obj_pos->qty * $obj_pos->salePrice) * ($posDiscount1 / 100)),2);
	        $pdf->addTextWrap(485,$pdf->y,60,$posTextSize,getGermanValue2($result1),'right');
	      }
	      if($obj_pos->discount1Flg == '2')
			{
	        // for value
	        $pdf->addText(110,$pdf->y,$posTextSize,$discountType1Name." ".getGermanValue2($posDiscount1)." ".$obj_pos->currencyId);
	        $pdf->addText(480,$pdf->y,$posTextSize,"-");
	        $result1 = $posDiscount1;
	        $pdf->addTextWrap(485,$pdf->y,60,$posTextSize,getGermanValue2($posDiscount1),'right');
	      }
			$pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
	    } // DISCOUNT NUMMER 1

		 // Rabatt 2
	    if($posDiscount2 != "0")
		 {
	      if($obj_pos->discount2Flg == '1')
			{
	        // for %
	        $pdf->addText(110,$pdf->y,$posTextSize,$discountType2Name." ".getGermanValue2($posDiscount2)."%");
	        $pdf->addText(480,$pdf->y,$posTextSize,"-");
	        $result2 = round ((($obj_pos->qty * $obj_pos->salePrice - $result1) * ($posDiscount2 / 100)),2);
	        $pdf->addTextWrap(485,$pdf->y,60,$posTextSize,getGermanValue2($result2),'right');
	      }
	      if($obj_pos->discount2Flg == '2')
			{
	        // for value
	        $pdf->addText(110,$pdf->y,$posTextSize,$discountType2Name." ".getGermanValue2($posDiscount2)." ".$obj_pos->currencyId);
			  $pdf->addText(480,$pdf->y,$posTextSize,"-");
	        $result2 = $posDiscount2;
	        $pdf->addTextWrap(485,$pdf->y,60,$posTextSize,getGermanValue2($posDiscount2),'right');
	      }
		   $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
	    } // DISCOUNT NUMMER 2

		 // Rabatt 3
	    if($posDiscount3 != "0")
		 {
	      if($obj_pos->discount3Flg == '1')
			{
	        // for %
	        $pdf->addText(110,$pdf->y,$posTextSize,$discountType3Name." ".getGermanValue2($posDiscount3)."%");
	        $pdf->addText(480,$pdf->y,$posTextSize,"-");
	        $result3 = round ((($obj_pos->qty * $obj_pos->salePrice - $result1 - $result2) * ($posDiscount3 / 100)),2);
	        $pdf->addTextWrap(485,$pdf->y,60,$posTextSize,getGermanValue2($result3),'right');
	      }
	      if($obj_pos->discount3Flg == '2')
			{
	        // for value
	        $pdf->addText(110,$pdf->y,$posTextSize,$discountType3Name." ".getGermanValue2($posDiscount3)." ".$obj_pos>currencyId);
	        $pdf->addText(480,$pdf->y,$posTextSize,"-");
	        $result3 = $posDiscount3;
	        $pdf->addTextWrap(485,$pdf->y,60,$posTextSize,getGermanValue2($posDiscount3),'right');
	      }
			$pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
	    } // DISCOUNT NUMMER 3

		 // Liefertermin
		 if(chkvt("order_pos_deldate")){
		 	if ($obj_pos->wishDelivDate !="0000-00-00" ){
		   		$pdf->addText(110,$pdf->y,$posTextSize,"Liefertermin: ".datetodot($obj_pos->wishDelivDate));
	      		$pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
		 	}
		 }
	
		 
		 if($posDiscount1 >0 || $posDiscount2 > 0  || $posDiscount3 > 0)
		 {
	      $space = ($pdf->getFontHeight($posTitleSize)+2);
	      $pdf->ezSetDy(-($space/4));
	      $pdf->line(480,$pdf->y,555,$pdf->y);
	      $pdf->ezSetDy(-($space-($space/4)));
	      $pdf->ezText2(getGermanValue2($obj_pos->saleValue),$posTextSize,array('justification'=>'right'));
	      $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
	    }

		 // Bemerkung nach Position
	    if($postPosText != "")
		 {
	      //$pdf->addText(100,$pdf->y,$posTextSize,trim($postPosText));
	      $pdf->ezText(trim($postPosText),$posTextSize,array('left'=>30,'justification'=>'left'));
	      $pdf->ezSetDy(-($pdf->getFontHeight($posTextSize)*2));
	    }
		 else
		 {
	      $pdf->ezSetDy(-($pdf->getFontHeight($posTextSize)));
	    }*/

		 // Seitenwechsel ?
	    if ($pdf->ezPageCount==$thisPageNum)
		 {
	      $pdf->transaction('commit');
	      $ok = 1;
	    }
		 else
		 {
		   // Überschrift wieder einfügen
	      $pdf->transaction('rewind');
	      $pdf->ezNewPage();
	      // Überschrift
	      $pdf->selectFont($boldFont);
	      $pdf->addText(70,$pdf->y,$posTitleSize,$posTitle1);
	      $pdf->addText(100,$pdf->y,$posTitleSize,$posTitle2);
	      $pdf->addText(310,$pdf->y,$posTitleSize,$posTitle3);
	      $pdf->addText(350,$pdf->y,$posTitleSize, $posTitle8);
			//$pdf->addText(380,$pdf->y,$posTitleSize,$posTitle5); // E-Preis
			$pdf->addTextWrap(380,$pdf->y,40,$posTextSize,$posTitle5,'right');
	      $pdf->addText(430,$pdf->y,$posTitleSize,$posTitle4); // MWST
	      $pdf->addTextWrap(465,$pdf->y,80,$posTextSize,$posTitle7,'right');
	      $space = ($pdf->getFontHeight($posTitleSize)+6);
	      $pdf->ezSetDy(-($space/4));
	      $pdf->line(70,$pdf->y,555,$pdf->y);
	      $pdf->ezSetDy(-($space-($space/4)));
	      $pdf->selectFont($mainFont);
			// Ende Überschrift

			// add HeadInfos again
			/*$pdf->addText(400-$infoOffsetX,688-$infoOffsetY,$infoTextSize,"Datum");
			$pdf->addText(490-$infoOffsetX,688-$infoOffsetY,$infoTextSize,$date);
			$pdf->addText(400-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,"Kunden-Nr.");
			$pdf->addText(490-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,$customerNo);
			$pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*2)-$infoOffsetY),$infoTextSize,"Rechnungs-Nr.");
			$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*2)-$infoOffsetY),$infoTextSize,$invoiceNo);
			$pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*3)-$infoOffsetY),$infoTextSize,"Rechnungsdatum");
			$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*3)-$infoOffsetY),$infoTextSize,$invoiceDate);
			$pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*4)-$infoOffsetY),$footTextSize+2,$r_text);
			//$pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,"Liefernummer");
			//$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,$deliver_id);
			$pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,"Steuernummer");
			$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,$taxnumber);
			$pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,"USt-ID");
			$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,$Ust_ID);*/
            
            $n=1;
            $pdf->addText(390-$infoOffsetX,688-$infoOffsetY,$infoTextSize,L_dynsb_Date);
            $pdf->addText(490-$infoOffsetX,688-$infoOffsetY,$infoTextSize,$date);
            if(($customerNo != 'false') and ($customerNo != '')){
                $pdf->addText(390-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,L_dynsb_CustomerNo);
                $pdf->addText(490-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,$customerNo);
                $n++;
            }
            $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_InvoiceNo);
            $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$invoiceNo);
            $n++;
            $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_InvoiceDate);
            $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$invoiceDate);
            $n++;
            $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$footTextSize+2,$r_text);
            $n++;
            //$pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,"Liefernummer");
            //$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,$deliver_id);
            $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_TaxNo);
            $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$taxnumber);
            $n++;
            $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_VatIdNo);
            $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$Ust_ID);
		 }
	  }
  }
}
//Rabatte, Zahlungskosten und Versandkosten als Positionen hinzufügen
//Zahlungskosten
if($obj->ordPaymentCost != 0){
    $posId++;
    // Position
    $pdf->selectFont($boldFont);
    $pdf->addTextWrap(64,$pdf->y,20,$posTextSize,$posId,'right');
    // Beschreibung
    $pdf->selectFont($mainFont);
    $pdf->addTextWrap(100,$pdf->y,350,$posTextSize,$obj->ordPaymentCond);
    if(strpos($obj->ordPaymentCond,'%') === false){
        // nichts
    } else {
        $obj->ordPaymentCost = $obj->ordPaymentCost/100;
    }
    // Menge
    $pdf->addTextWrap(300,$pdf->y,40,$posTextSize,getGermanValue2(1),'right');
    // Preis
    $pdf->addTextWrap(380,$pdf->y,50,$posTextSize,getGermanValue2($obj->ordPaymentCost),'right');
    // Mwst
    $ptv = $posTaxValue;
    if($ptv == 0) $ptv = "-/- ";
    $pdf->addTextWrap(440,$pdf->y,40,$posTextSize,$ptv,'right');
    // Positionsbetrag
    $pdf->addTextWrap(485,$pdf->y,60,$posTextSize,getGermanValue2($obj->ordPaymentCost),'right');
    $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
    $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
}
//Versandkosten
if($obj->ordShippingCost != 0){
    $posId++;
    // Position
    $pdf->selectFont($boldFont);
    $pdf->addTextWrap(64,$pdf->y,20,$posTextSize,$posId,'right');
    // Beschreibung
    $pdf->selectFont($mainFont);
    $pdf->addTextWrap(100,$pdf->y,350,$posTextSize,$obj->ordShippingCond);
    // Menge
    $pdf->addTextWrap(300,$pdf->y,40,$posTextSize,getGermanValue2(1),'right');
    // Preis
    $pdf->addTextWrap(380,$pdf->y,50,$posTextSize,getGermanValue2($obj->ordShippingCost),'right');
    // Mwst
    $ptv = $posTaxValue;
    if($ptv == 0) $ptv = "-/- ";
    $pdf->addTextWrap(440,$pdf->y,40,$posTextSize,$ptv,'right');
    // Positionsbetrag
    $pdf->addTextWrap(485,$pdf->y,60,$posTextSize,getGermanValue2($obj->ordShippingCost),'right');
    $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
    $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
}
/*
$SQL_foot = "SELECT *  FROM orderactfoot  WHERE  orderActHeadIdNo = ".$obj->orderActHeadIdNo." AND (chgHistoryFlg <> '0')";
$qry_foot = mysqli_query($link, $SQL_foot);
$obj_foot = gs_mysqli_fetch_object($qry_foot);

// text after last pos
if(chkvt("order_order_text")){
	if(trim($obj_foot->footRemark) != '') {
		$pdf->ezSetDy(+$pdf->getFontHeight($textBlockSize));
		$pdf->ezText(trim($obj_foot->footRemark),$textBlockSize,array('justification'=>'left'));
		$pdf->ezSetDy(-$pdf->getFontHeight($textBlockSize));
	}
}

*/
// check if there is enough space to start next action
if($pdf->y < 90)
{
  $pdf->ezNewPage();
  // add HeadInfos again
  /*$pdf->addText(400-$infoOffsetX,688-$infoOffsetY,$infoTextSize,"Datum");
  $pdf->addText(490-$infoOffsetX,688-$infoOffsetY,$infoTextSize,$date);
  $pdf->addText(400-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,"Kunden-Nr.");
  $pdf->addText(490-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,$customerNo);
  $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*2)-$infoOffsetY),$infoTextSize,"Rechnungs-Nr.");
  $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*2)-$infoOffsetY),$infoTextSize,$invoiceNo);
  $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*3)-$infoOffsetY),$infoTextSize,"Rechnungsdatum");
  $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*3)-$infoOffsetY),$infoTextSize,$invoiceDate);
  $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*4)-$infoOffsetY),$footTextSize+2,$r_text);
  //$pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,"Liefernummer");
  //$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,$deliver_id);
  $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,"Steuernummer");
  $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,$taxnumber);
  $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,"USt-ID");
  $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,$Ust_ID);*/
  
    $n=1;
    $pdf->addText(390-$infoOffsetX,688-$infoOffsetY,$infoTextSize,L_dynsb_Date);
    $pdf->addText(490-$infoOffsetX,688-$infoOffsetY,$infoTextSize,$date);
    if(($customerNo != 'false') and ($customerNo != '')){
        $pdf->addText(390-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,L_dynsb_CustomerNo);
        $pdf->addText(490-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,$customerNo);
        $n++;
    }
    $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_InvoiceNo);
    $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$invoiceNo);
    $n++;
    $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_InvoiceDate);
    $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$invoiceDate);
    $n++;
    $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$footTextSize+2,$r_text);
    $n++;
    //$pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,"Liefernummer");
    //$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,$deliver_id);
    $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_TaxNo);
    $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$taxnumber);
    $n++;
    $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_VatIdNo);
    $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$Ust_ID);
}

$pdf->transaction('start');
$ok = 0;

while (!$ok)
{
  $thisPageNum = $pdf->ezPageCount;

  /************************ BLOCK GESAMT-NETTO ***********************************/
  //$pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
  $pdf->selectFont($boldFont);
  $pdf->addText(100,$pdf->y,$posTitleSize,"Gesamt");
  $pdf->addTextWrap(485,$pdf->y,60,$posTitleSize,getGermanValue2($obj->ordTotalValue + $obj->ordPaymentCost + $obj->ordShippingCost),'right');
  $pdf->selectFont($mainFont);
  //$pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));

  //insert Discounts if available...
  // 1.Rabatt
/*  if($obj->ordDiscount1Value != "0" && $obj_foot->discountType1Name != "")
  {
    if($obj_foot->discount1Flg == '1')
    {
      // for %
      $pdf->addText(110,$pdf->y,$posTextSize,$obj_foot->discountType1Name." ".getGermanValue2($obj_foot->discount1)."%");
      $pdf->addText(480,$pdf->y,$posTextSize,"-");
      $result1 = ($obj_foot->discount1 * $obj_foot->NtValueBefDsc1) / 100;
      $pdf->addTextWrap(485,$pdf->y,60,$posTextSize,getGermanValue2($result1),'right');
    }
    if($obj_foot->discount1Flg == '2')
	 {
      // for value
      $pdf->addText(110,$pdf->y,$posTextSize,$obj_foot->discountType1Name." ".getGermanValue2($obj_foot->discount1)." ".$obj->currencyId);
      $pdf->addText(480,$pdf->y,$posTextSize,"-");
      $result1 = $obj_foot->discount1;
      $pdf->addTextWrap(485,$pdf->y,60,$posTextSize,getGermanValue2($obj_foot->discount1),'right');
    }
	 $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
  }// DISCOUNT NUMMER 1

  // 2.Rabatt
  if($obj_foot->discount2 != "0" && $obj_foot->discountType2Name != "")
  {
    // Gesamt nach der 1. 
	 $pdf->selectFont($boldFont);
 	 $pdf->addText(100,$pdf->y,$posTitleSize,"Gesamt - Nettowert  nach Abzug des 1. Rabatts");
	 $pdf->addTextWrap(485,$pdf->y,60,$posTitleSize,getGermanValue2($obj_foot->NtValueBefDsc2),'right');
	 $pdf->selectFont($mainFont);
	 $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
    if($obj_foot->discount2Flg == '1')
	 {
      // for %
      $pdf->addText(110,$pdf->y,$posTextSize,$obj_foot->discountType2Name." ".getGermanValue2($obj_foot->discount2)."%");
      $pdf->addText(480,$pdf->y,$posTextSize,"-");
      $result2 = ($obj_foot->discount2 * ($obj_foot->NtValueBefDsc1 - $result1) / 100);
      $pdf->addTextWrap(485,$pdf->y,60,$posTextSize,getGermanValue2($result2),'right');
    }
    if($obj_foot->discount2Flg == '2')
	 {
      // for value
      $pdf->addText(110,$pdf->y,$posTextSize,$obj_foot->discountType2Name." ".getGermanValue2($obj_foot->discount2)." ".$obj->currencyId);
		$pdf->addText(480,$pdf->y,$posTextSize,"-");
      $result2 = $obj_foot->discount2;
      $pdf->addTextWrap(485,$pdf->y,60,$posTextSize,getGermanValue2($obj_foot->discount2),'right');
    }
	 $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
  }// DISCOUNT NUMMER 2
*/
  // 3.Rabatt
  /*
  if($obj_foot->discount3 != "0")
  {
    if($obj_foot->discount3Flg == '1')
	 {
      // for %
      $pdf->addText(110,$pdf->y,$posTextSize,$obj_foot->discountType3Name." ".getGermanValue2($obj_foot->discount3)."%");
      $pdf->addText(480,$pdf->y,$posTextSize,"-");
		if ($result2 != 0 && $result2 !="")
		{
		  $result3 = ($obj_foot->discount3 * ($obj_foot->NtValueBefDsc1 - $result2 - $result1) / 100);
		}
		else
		{
		  $result3 = ($obj_foot->discount3 * ($obj_foot->NtValueBefDsc1 - $result1) / 100);
		}
      $pdf->addTextWrap(485,$pdf->y,60,$posTextSize,getGermanValue2($result3),'right');
    }
    if($obj_pos->discount3Flg == '2')
	 {
      // for value
      $pdf->addText(110,$pdf->y,$posTextSize,$obj_foot->discountType3Name." ".getGermanValue2($obj_foot->discount3)." ".$obj>currencyId);
      $pdf->addText(480,$pdf->y,$posTextSize,"-");
      $result3 = $obj_foot->discount3;
      $pdf->addTextWrap(485,$pdf->y,60,$posTextSize,getGermanValue2($obj_foot->discount3),'right');
    }
	 $pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
  }// DISCOUNT NUMMER 3
  
  if ( ($obj_foot->discount1 != "0") || ($obj_foot->discount2 != "0"))
  {
    $pdf->selectFont($boldFont);
	 $pdf->line(480,$pdf->y,555,$pdf->y);
	 $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
	 $pdf->addText(100,$pdf->y,$posTitleSize,"Gesamt - Nettowert nach Abzug aller Rabatte");
	 $pdf->addTextWrap(485,$pdf->y,60,$posTitleSize,getGermanValue2($obj_foot->NtValueBefDsc3),'right');
	 $pdf->selectFont($mainFont);
  }*/
  /*********************************** NETTO KOMPLETT ***********************************/
    $disp = 1;
  if ($obj->ordVAT1Value >0) $disp++;
  if ($obj->ordVAT2Value >0) $disp++;
  if ($obj->ordVAT3Value >0) $disp++;

  //$pdf->ezSetDy(-$pdf->getFontHeight($posTextSize));
  $istax=0;
  $brutto = 0;
  $mwSt="";
  if($obj->ordVAT1Value > 0 && $obj->ordVAT1Prct > 0)
  {
    $istax=1;
	 $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
    $mwSt=" ".getGermanValue2($obj->ordVAT1Prct)." % MwSt.";
    //if($disp>1) $mwSt=$mwSt." auf ";
    if($se->get_setting('cbNetPrice_Checked') == 'False'){
        $pdf->addText(100,$pdf->y,$posTitleSize,"incl.".$mwSt);
        $ordVAT1 = $obj->ordVAT1Prct*(($obj->ordTotalValue + $obj->ordShippingCost + ($obj->ordPaymentCost))/119);
    } else {
        $pdf->addText(100,$pdf->y,$posTitleSize,"zzgl.".$mwSt);
        $ordVAT1 = $obj->ordVAT1Prct*(($obj->ordTotalValue + $obj->ordShippingCost + ($obj->ordPaymentCost))/100);
    }
	 if($disp>1) $pdf->addTextWrap(260,$pdf->y,60,$posTitleSize,getGermanValue2($ordVAT1)."",'right');
	 //$pdf->addText(480,$pdf->y,$posTitleSize,"+");
	 //$pdf->addTextWrap(485,$pdf->y,60,$posTitleSize,getGermanValue2($obj->ordVAT1Value),'right');
  }

  if($obj->ordVAT2Value > 0 && $obj->ordVAT2Prct > 0)
  {
 	 $istax=1;
	 $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
	 $mwSt=" ".getGermanValue2($obj->ordVAT2Prct)." % MwSt.";
	 //if($disp>1) $mwSt=$mwSt." auf ";
     if($se->get_setting('cbNetPrice_Checked') == 'False'){
        $pdf->addText(100,$pdf->y,$posTitleSize,"incl.".$mwSt);
        $ordVAT2 = $obj->ordVAT2Prct*(($obj->ordTotalValue + $obj->ordShippingCost + ($obj->ordPaymentCost))/119); 
     } else {
        $pdf->addText(100,$pdf->y,$posTitleSize,"zzgl.".$mwSt);
        $ordVAT2 = $obj->ordVAT2Prct*(($obj->ordTotalValue + $obj->ordShippingCost + ($obj->ordPaymentCost))/100);
    }
    if($disp>1) $pdf->addTextWrap(260,$pdf->y,60,$posTitleSize,getGermanValue2($obj->ordVAT2Value)."",'right');
	 //$pdf->addText(480,$pdf->y,$posTitleSize,"+");
	 //$pdf->addTextWrap(485,$pdf->y,60,$posTitleSize,getGermanValue2($obj->ordVAT2Value),'right');
  }

  if($obj->ordVAT3Value > 0 && $obj->ordVAT3Prct > 0)
  {
    $istax=1;
	 $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
	 $mwSt=" ".getGermanValue2($obj->ordVAT3Prct)." % MwSt.";
	 //if($disp>1) $mwSt=$mwSt." auf ";
	 $pdf->addText(100,$pdf->y,$posTitleSize,"incl.".$mwSt);
	 if($disp>1) $pdf->addTextWrap(260,$pdf->y,60,$posTitleSize,getGermanValue2($obj->ordVAT3Value)."",'right');
	 //$pdf->addText(480,$pdf->y,$posTitleSize,"+");
	 //$pdf->addTextWrap(485,$pdf->y,60,$posTitleSize,getGermanValue2($obj->ordVAT3Value),'right');
  }
/*	
  if(($obj_foot->taxCode4IdNo != ""|| $obj_foot->brtVAT4Value > 0)  && $obj_foot->VAT4Value>0  && $obj_foot->taxCode4Prct > 0 )
  {
    $istax=1;
	 $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
	 $mwSt=" ".getGermanValue2($obj_foot->taxCode4Prct)." % MwSt.";
	 if($disp>1) $mwSt=$mwSt." auf ";
	 $pdf->addText(100,$pdf->y,$posTitleSize,"zzgl.".$mwSt);
	 if($disp>1) $pdf->addTextWrap(260,$pdf->y,60,$posTitleSize,getGermanValue2($obj_foot->brtVAT4Value)."",'right');
	 $pdf->addText(480,$pdf->y,$posTitleSize,"+");
	 $pdf->addTextWrap(485,$pdf->y,60,$posTitleSize,getGermanValue2($obj_foot->VAT4Value),'right');
  }

  if(($obj_foot->taxCode5IdNo != ""|| $obj_foot->brtVAT5Value > 0)  && $obj_foot->VAT5Value>0  && $obj_foot->taxCode5Prct > 0 )
  {
    $istax=1;
	 $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
	 $mwSt=" ".getGermanValue2($obj_foot->taxCode5Prct)." % MwSt.";
	 if($disp>1) $mwSt=$mwSt." auf ";
	 $pdf->addText(100,$pdf->y,$posTitleSize,"zzgl.".$mwSt);
	 if($disp>1) $pdf->addTextWrap(260,$pdf->y,60,$posTitleSize,getGermanValue2($obj_foot->brtVAT5Value)."",'right');
	 $pdf->addText(480,$pdf->y,$posTitleSize,"+");
	 $pdf->addTextWrap(485,$pdf->y,60,$posTitleSize,getGermanValue2($obj_foot->VAT5Value),'right');
  }*/
	
 // if ($istax!=1) $pdf->addText(100,$pdf->y,$posTitleSize,"Es fließt keine MwSt ein!");
  //   $brutto = $obj_foot->VAT1Value + $obj_foot->VAT2Value + $obj_foot->VAT3Value + $obj_foot->VAT4Value + $obj_foot->VAT5Value + $nettoAfterAllDiscount;


	// Versandkosten ---> kulikowski
	/*
	$shipping = 0;
	if($obj_foot->shipNtValue > 0 && $obj_foot->shipNtValue != '') {
		$shipping = 1;
		$pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
		$shiptax = getentity("taxcode","taxPrct","taxCodeIdNo = '".$obj_foot->shipTaxCodeIdNo."'");
		$mwSt = "/ ".getGermanValue2($shiptax)." % MwSt.";
		$pdf->addText(100,$pdf->y,$posTitleSize,"zzgl. Versandkosten");
		$pdf->addTextWrap(260,$pdf->y,60,$posTitleSize,getGermanValue2($obj_foot->shipNtValue)."",'right');
		$pdf->addTextWrap(305,$pdf->y,100,$posTitleSize,$mwSt,'right');
		$pdf->addText(480,$pdf->y,$posTitleSize,"+");
		$pdf->addTextWrap(485,$pdf->y,60,$posTitleSize,getGermanValue2($obj_foot->shipBrtValue),'right');
	}
	*/





  /************************ Gesamtbetrag *********************************/
  $space = ($pdf->getFontHeight($posTitleSize)+2);
  $pdf->ezSetDy(-($space/4));
  $pdf->line(480,$pdf->y,555,$pdf->y);
  $pdf->ezSetDy(-($space+3));
  $pdf->selectFont($boldFont);
  $pdf->addText(100,$pdf->y,$posTitleSize,"Gesamtbetrag in ".$obj->ordCurrency);//.$currency);
  $just = array();
  $just['justification']='right';
  $just['aleft']='485';
  $ordTotal = $obj->ordTotalValue + $obj->ordShippingCost + ($obj->ordPaymentCost);
  if($ordTotal != $obj->ordTotalValueAfterDsc1){$ordTotal=$obj->ordTotalValueAfterDsc1;}
  if($ordTotal != $obj->ordTotalValueAfterDsc2){$ordTotal=$obj->ordTotalValueAfterDsc2;}
  $pdf->ezText2(getGermanValue2($ordTotal),$posTitleSize,$just);
  $pdf->selectFont($mainFont);
  /*$SQL_creditgranted = "SELECT grantedSum, actionCurNo FROM creditgranted WHERE newInvoiceNo=".$invoiceNo." and newPosCurNo=0";
  $SQL_grantsum = mysqli_query($link, $SQL_creditgranted);
  $sumGrantValue=0;
  while($objGrantSum = gs_mysqli_fetch_object($SQL_grantsum))
  {
    if(doubleval($objGrantSum->grantedSum) > 0) {
        $sumGrantValue=$sumGrantValue+$objGrantSum->grantedSum;
        $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
        $pdf->addText(100,$pdf->y,$posTitleSize,"Gutschrift aus Gutschrift-Nr. ".$objGrantSum->actionCurNo);
        $pdf->addText(480,$pdf->y,$posTitleSize,"-");
        $pdf->addTextWrap(485,$pdf->y,60,$posTitleSize,getGermanValue2($objGrantSum->grantedSum),'right');
    }
  }
  if ($sumGrantValue>0)
  {
    $space = ($pdf->getFontHeight($posTitleSize)+2);
    $pdf->ezSetDy(-($space/4));
    $pdf->line(480,$pdf->y,555,$pdf->y);
    $pdf->ezSetDy(-($space+3));
    $pdf->selectFont($boldFont);
    $pdf->addText(100,$pdf->y,$posTitleSize,"Endbetrag in ".$currency);
    $pdf->ezText2(getGermanValue2($obj_foot->brtValue - $sumGrantValue),$posTitleSize,array('justification'=>'right'));
    $pdf->selectFont($mainFont);
  }*/
  $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize/2));
  $pdf->line(480,$pdf->y,555,$pdf->y);
  $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize/2));
  $pdf->line(480,$pdf->y,555,$pdf->y);
  $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
  /***********************************************************************/
  
  if ($pdf->ezPageCount==$thisPageNum)
  {
    $pdf->transaction('commit');
    $ok = 1;
  } else {
    $pdf->transaction('rewind');
    $pdf->ezNewPage();
    // add HeadInfos again
	/* $pdf->addText(400-$infoOffsetX,688-$infoOffsetY,$infoTextSize,"Datum");
  	 $pdf->addText(490-$infoOffsetX,688-$infoOffsetY,$infoTextSize,$date);
	 $pdf->addText(400-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,"Kunden-Nr.");
	 $pdf->addText(490-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,$customerNo);
	 $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*2)-$infoOffsetY),$infoTextSize,"Rechnungs-Nr.");
	 $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*2)-$infoOffsetY),$infoTextSize,$invoiceNo);
	 $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*3)-$infoOffsetY),$infoTextSize,"Rechnungsdatum");
	 $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*3)-$infoOffsetY),$infoTextSize,$invoiceDate);
	 $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*4)-$infoOffsetY),$footTextSize+2,$r_text);
	 //$pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,"Liefernummer");
	 //$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,$deliver_id);
	 $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,"Steuernummer");
	 $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,$taxnumber);
	 $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,"USt-ID");
	 $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,$Ust_ID);*/
     
    $n=1;
    $pdf->addText(390-$infoOffsetX,688-$infoOffsetY,$infoTextSize,L_dynsb_Date);
    $pdf->addText(490-$infoOffsetX,688-$infoOffsetY,$infoTextSize,$date);
    if(($customerNo != 'false') and ($customerNo != '')){
        $pdf->addText(390-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,L_dynsb_CustomerNo);
        $pdf->addText(490-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,$customerNo);
        $n++;
    }
    $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_InvoiceNo);
    $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$invoiceNo);
    $n++;
    $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_InvoiceDate);
    $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$invoiceDate);
    $n++;
    $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$footTextSize+2,$r_text);
    $n++;
    //$pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,"Liefernummer");
    //$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,$deliver_id);
    $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_TaxNo);
    $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$taxnumber);
    $n++;
    $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_VatIdNo);
    $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$Ust_ID);
  }
}


$pdf->transaction('start');
$ok = 0;

while (!$ok)
{
    $thisPageNum = $pdf->ezPageCount;
  
    //$payConditionIdNo = $obj_foot->payConditionIdNo;
    $sqlPaymentTerm = "SELECT PAYMENTTERM FROM ".DBToken."paymentlanguage WHERE SortId = '".$obj->ordPaymentMode."' and LanguageId = '".$_SESSION['slc']."'";
    $qryPaymentTerm = mysqli_query($link,$sqlPaymentTerm);
    $erg = mysqli_fetch_assoc($qryPaymentTerm);
    $payCondition = $erg['PAYMENTTERM'];
    //$payCondition ='Zahlbar sofort nach Erhalt der Rechnung.';// $obj_foot->payConditionRemark;
    $deliveryCond ='';// $obj_foot->delivCondName;
    $payType = $obj->ordPaymentCond;//$obj_foot->payTypeName;
    $sendType = $obj->ordShippingCond;//$obj_foot->sendTypeName;
    //$sendTypeId = $obj_foot->sendTypeId;
    
    /*$sqlpc = "SELECT * FROM paycondition WHERE payConditionIdNo = '".$payConditionIdNo."' AND languageIdNo = '1'";
    $qrypc = mysqli_query($link, $sqlpc);
    $objpc = gs_mysqli_fetch_object($qrypc);
    
    $sqlpd = "SELECT DATE_ADD('".$obj->orderDate."',INTERVAL ".$objpc->nettoDays." DAY) AS paydate";
    $qrypd = mysqli_query($link, $sqlpd);
    $objpd = gs_mysqli_fetch_object($qrypd);
    */

  if($payCondition != "")
  {
    $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
    //$pdf->addText(100,$pdf->y,$posTitleSize,"Zahlungsziel");
    //$pdf->addText(250,$pdf->y,$posTitleSize,trim($conv->mysql2date($objpd->paydate)));
    //$pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
    $pdf->addText(100,$pdf->y,$posTitleSize,"Zahlungsbedingung:");
    $pdf->addText(250,$pdf->y,$posTitleSize,trim($payCondition));
    $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
  }/* else {
    $pdf->addText(100,$pdf->y,$posTitleSize,"Zahlungsbedingung");
    $pdf->addText(250,$pdf->y,$posTitleSize,"Nicht angegeben");
    $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
  }*/

  if($deliveryCond != "")
  {
    $pdf->addText(100,$pdf->y,$posTitleSize,"Lieferbedingung:");
    $pdf->addText(250,$pdf->y,$posTitleSize,trim($deliveryCond));
    $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
  } /*else {
    $pdf->addText(100,$pdf->y,$posTitleSize,"Lieferbedingung");
    $pdf->addText(250,$pdf->y,$posTitleSize,"Nicht angegeben");
    $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
  }*/

  if($payType != "")
  {
    $pdf->addText(100,$pdf->y,$posTitleSize,"Zahlungsart:");
    $pdf->addText(250,$pdf->y,$posTitleSize,trim($payType));
    $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
  }/* else {
    $pdf->addText(100,$pdf->y,$posTitleSize,"Zahlungsart");
    $pdf->addText(250,$pdf->y,$posTitleSize,"Nicht angegeben");
    $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
  }*/

  if($sendType != "")
  {
    $pdf->addText(100,$pdf->y,$posTitleSize,"Versandart:");
    $pdf->addText(250,$pdf->y,$posTitleSize,trim($sendType));
    $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
  } /*else {
    $pdf->addText(100,$pdf->y,$posTitleSize,"Versandartart");
    $pdf->addText(250,$pdf->y,$posTitleSize,"Nicht angegeben");
    $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize));
  }*/

  if ($pdf->ezPageCount==$thisPageNum)
  {
    $pdf->transaction('commit');
    $ok = 1;
  } else {
    $pdf->transaction('rewind');
    $pdf->ezNewPage();
    // add HeadInfos again
	/* $pdf->addText(400-$infoOffsetX,688-$infoOffsetY,$infoTextSize,"Datum");
  	 $pdf->addText(490-$infoOffsetX,688-$infoOffsetY,$infoTextSize,$date);
	 $pdf->addText(400-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,"Kunden-Nr.");
	 $pdf->addText(490-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,$customerNo);
	 $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*2)-$infoOffsetY),$infoTextSize,"Rechnungs-Nr.");
	 $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*2)-$infoOffsetY),$infoTextSize,$invoiceNo);
	 $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*3)-$infoOffsetY),$infoTextSize,"Rechnungsdatum");
	 $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*3)-$infoOffsetY),$infoTextSize,$invoiceDate);
	 $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*4)-$infoOffsetY),$footTextSize+2,$r_text);
	 //$pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,"Liefernummer");
	 //$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,$deliver_id);
	 $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,"Steuernummer");
	 $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,$taxnumber);
	 $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,"USt-ID");
	 $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,$Ust_ID);*/
     
    $n=1;
    $pdf->addText(390-$infoOffsetX,688-$infoOffsetY,$infoTextSize,L_dynsb_Date);
    $pdf->addText(490-$infoOffsetX,688-$infoOffsetY,$infoTextSize,$date);
    if(($customerNo != 'false') and ($customerNo != '')){
        $pdf->addText(390-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,L_dynsb_CustomerNo);
        $pdf->addText(490-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,$customerNo);
        $n++;
    }
    $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_InvoiceNo);
    $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$invoiceNo);
    $n++;
    $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_InvoiceDate);
    $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$invoiceDate);
    $n++;
    $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$footTextSize+2,$r_text);
    $n++;
    //$pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,"Liefernummer");
    //$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,$deliver_id);
    $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_TaxNo);
    $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$taxnumber);
    $n++;
    $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_VatIdNo);
    $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$Ust_ID);
  }
}

/*
// only if there is an end textblock
if($obj->txtBlkEnd != '') {

    $pdf->ezSetDy(-$pdf->getFontHeight($textBlockSize));

    // check if there is enough space to start next action
    $actualy = $pdf->y;
    if($actualy-($textBlockSize*2) < 90) {
        $pdf->ezNewPage();
        // add HeadInfos again
        $pdf->addText(400-$infoOffsetX,688-$infoOffsetY,$infoTextSize,"Datum");
        $pdf->addText(490-$infoOffsetX,688-$infoOffsetY,$infoTextSize,$date);
        $pdf->addText(400-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,"Kunden-Nr.");
        $pdf->addText(490-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,$customerNo);
        $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*2)-$infoOffsetY),$infoTextSize,"Rechnungs-Nr.");
        $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*2)-$infoOffsetY),$infoTextSize,$invoiceNo);
        $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*3)-$infoOffsetY),$infoTextSize,"Rechnungsdatum");
        $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*3)-$infoOffsetY),$infoTextSize,$invoiceDate);
        $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*4)-$infoOffsetY),$footTextSize+2,$r_text);
        $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,"Liefernummer");
        $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,$deliver_id);
        $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,"Steuernummer");
        $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,$taxnumber);
        $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*7)-$infoOffsetY),$infoTextSize,"USt-ID");
        $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*7)-$infoOffsetY),$infoTextSize,$Ust_ID);
    }

    $pdf->ezText(utf8_decode($obj->txtBlkStart),$textBlockSize,array('justification'=>'left'));

} // end of if

*/

$pdf->transaction('start');
$ok = 0;

while (!$ok) {
    $thisPageNum = $pdf->ezPageCount;
/*    $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize*4));
    $pdf->addText(70,$pdf->y,$posTitleSize,$greetings);
    $pdf->ezSetDy(-$pdf->getFontHeight($posTitleSize*4));
    $pdf->addText(70,$pdf->y,$posTitleSize,$senderName);
*/
    if ($pdf->ezPageCount==$thisPageNum){
        $pdf->transaction('commit');
        $ok = 1;
    } else {
        $pdf->transaction('rewind');
        $pdf->ezNewPage();
        // add HeadInfos again
        /*$pdf->addText(400-$infoOffsetX,688-$infoOffsetY,$infoTextSize,"Datum");
        $pdf->addText(490-$infoOffsetX,688-$infoOffsetY,$infoTextSize,$date);
        $pdf->addText(400-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,"Kunden-Nr.");
        $pdf->addText(490-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,$customerNo);
        $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*2)-$infoOffsetY),$infoTextSize,"Rechnungs-Nr.");
        $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*2)-$infoOffsetY),$infoTextSize,$invoiceNo);
        $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*3)-$infoOffsetY),$infoTextSize,"Rechnungsdatum");
        $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*3)-$infoOffsetY),$infoTextSize,$invoiceDate);
        $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*4)-$infoOffsetY),$footTextSize+2,$r_text);
        //$pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,"Liefernummer");
        //$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,$deliver_id);
        $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,"Steuernummer");
        $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*5)-$infoOffsetY),$infoTextSize,$taxnumber);
        $pdf->addText(400-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,"USt-ID");
        $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,$Ust_ID);*/
        
        $n=1;
        $pdf->addText(390-$infoOffsetX,688-$infoOffsetY,$infoTextSize,L_dynsb_Date);
        $pdf->addText(490-$infoOffsetX,688-$infoOffsetY,$infoTextSize,$date);
        if(($customerNo != 'false') and ($customerNo != '')){
            $pdf->addText(390-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,L_dynsb_CustomerNo);
            $pdf->addText(490-$infoOffsetX,(688-$pdf->getFontHeight($infoTextSize)-$infoOffsetY),$infoTextSize,$customerNo);
            $n++;
        }
        $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_InvoiceNo);
        $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$invoiceNo);
        $n++;
        $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_InvoiceDate);
        $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$invoiceDate);
        $n++;
        $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$footTextSize+2,$r_text);
        $n++;
        //$pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,"Liefernummer");
        //$pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*6)-$infoOffsetY),$infoTextSize,$deliver_id);
        $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_TaxNo);
        $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$taxnumber);
        $n++;
        $pdf->addText(390-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,L_dynsb_VatIdNo);
        $pdf->addText(490-$infoOffsetX,(688-($pdf->getFontHeight($infoTextSize)*$n)-$infoOffsetY),$infoTextSize,$Ust_ID);
	}
}

$pdf->ezStopPageNumbers(1,1,$pn);


} // end of foreach !!!



function getGermanValue2($val = '') {
    $tmp = number_format(trim(sprintf("%01.2f",$val)), 2, ',', '.');
    return $tmp;
}

$savedate = date("Ymd");

if(isset($d) && $d == 1) {
    // debug output only
    $pdfcode = $pdf->ezOutput(1);
    $pdfcode = str_replace("\n","\n<br>",htmlspecialchars($pdfcode));
    echo '<html><body>';
    echo trim($pdfcode);
    echo '</body></html>';
} 

if(isset($d) && $d == 3 && $modInvoiceOK && ($se->get_Setting('chkInvoiceAutoSend') == 'True')) {
// Automatisches Versand von Rechnungen. Wird von function 'writeOrderData' aus class.shoplog.php per curl aufgerufen.   
    // write on disc and send mail
    $pdfcode = $pdf->ezOutput();
    //$fp = fopen($savedate."_".$reportType."_".$invoiceNo.".pdf",'wb');
    $fp = fopen($reportType."_".$invoiceNo.".pdf",'wb');
    fwrite($fp,$pdfcode);
    fclose($fp);
    $dear = 'Sehr geehrte';
    if(trim($obj->ordTitle) == 'Herr') {
    	$dear = 'Sehr geehrter';
    }
    //Text für Rechnungsmail
    $invText = $se->get_settingmemo('memoInvoiceMail');
    $invText = str_replace('{INVOICENUMBER}',$invoiceNo,$invText);
    $invText = str_replace('{INVOICEDATE}',$invoiceDate,$invText);
    $invText = str_replace('{ORDERNUMBER}',$orderNo,$invText);
    $invText = str_replace('{ORDERDATE}',$bookingDate,$invText);
    $invText = str_replace('{CUSTITLE}',$obj->ordTitle,$invText);
    $invText = str_replace('{CUSFIRSTNAME}',$obj->ordFirstName,$invText);
    $invText = str_replace('{CUSLASTNAME}',$obj->ordLastName,$invText);
    
    $ap = array(
    				'dear' => $dear,
    				'_LANGTAGFNFIELDFORMTOADDRESS_' => $obj->ordTitle,
    				'_LANGTAGFNFIELDLASTNAME_' => $obj->ordFirstName.' '.$obj->ordLastName,
    				'invoice_for_you' => $invText,
    				'answer_text_end' => $se->get_settingmemo('memoEmailTextEnd')
    );
    $order->InvoiceMail = $ap;
    $_SESSION['order'] = serialize($order);
    chdir("../../");
    require_once("inc/class.smtp.php");
    require_once("inc/class.phpmailer.php");
    include_once("inc/class.gsmailengine.php");
    $me = new gs_mailengine('template/gs_invoicemail_customer_text.html');
    $me->from = $se->get_setting('edShopEmail_Text');
    $me->fromname = $se->get_setting('edShopName_Text');
    $me->get_tags();
    $me->parse_tags();
    $me->msg = $me->content;
    
    if($me->sendmail2($toMail,$reportType."_".$invoiceNo,$pdfcode,$reportType."_".$invoiceNo.".pdf",false) == 1) {
        // Eine Kopie für Shopbetreiber
        $me->sendmail2($me->from,$reportType."_".$invoiceNo,$pdfcode,$reportType."_".$invoiceNo.".pdf",false);
        // Versanddatum aktualisieren
        $SQL = "UPDATE ".DBToken."order SET ordInvSendDate=now() WHERE ordIdNo = ".$pKey;
        $qry = mysqli_query($link, $SQL);
        $SQL = "UPDATE ".DBToken."order SET ordInvoiceNumber='".$invoiceNo."' WHERE ordIdNo = ".$pKey;
        $qry = mysqli_query($link, $SQL);
        // Weiterleitung zu Shopbestellungen
    	//header('Location: shoporder.search.php?lang='.$_REQUEST['lang']);
    } else {
    	die(L_dynsb_MailInvoiceError.$me->error);
    }
}

if(isset($d) && $d == 2) {
    // write on disc only
    $pdfcode = $pdf->ezOutput();
    //$fp = fopen($savedate."_".$reportType."_".$invoiceNo.".pdf",'wb');
    $fp = fopen($reportType."_".$invoiceNo.".pdf",'wb');
    fwrite($fp,$pdfcode);
    fclose($fp);
    $dear = 'Sehr geehrte';
    if(trim($obj->ordTitle) == 'Herr') {
    	$dear = 'Sehr geehrter';
    }
    //Text für Rechnungsmail
    $invText = $se->get_settingmemo('memoInvoiceMail');
    $invText = str_replace('{INVOICENUMBER}',$invoiceNo,$invText);
    $invText = str_replace('{INVOICEDATE}',$invoiceDate,$invText);
    $invText = str_replace('{ORDERNUMBER}',$orderNo,$invText);
    $invText = str_replace('{ORDERDATE}',$bookingDate,$invText);
    $invText = str_replace('{CUSTITLE}',$obj->ordTitle,$invText);
    $invText = str_replace('{CUSFIRSTNAME}',$obj->ordFirstName,$invText);
    $invText = str_replace('{CUSLASTNAME}',$obj->ordLastName,$invText);
    
    $ap = array(
    				'dear' => $dear,
    				'_LANGTAGFNFIELDFORMTOADDRESS_' => $obj->ordTitle,
    				'_LANGTAGFNFIELDLASTNAME_' => $obj->ordFirstName.' '.$obj->ordLastName,
    				'invoice_for_you' => $invText,
    				'answer_text_end' => $se->get_settingmemo('memoEmailTextEnd')
    );
    $order->InvoiceMail = $ap;
    $_SESSION['order'] = serialize($order);
    chdir("../../");
    require_once("inc/class.smtp.php");
    require_once("inc/class.phpmailer.php");
    include_once("inc/class.gsmailengine.php");
    $me = new gs_mailengine('template/gs_invoicemail_customer_text.html');
    $me->from = $se->get_setting('edShopEmail_Text');
    $me->fromname = $se->get_setting('edShopName_Text');
    $me->get_tags();
    $me->parse_tags();
    $me->msg = $me->content;
    
    if($me->sendmail2($toMail,$reportType."_".$invoiceNo,$pdfcode,$reportType."_".$invoiceNo.".pdf",false) == 1) {
        // Eine Kopie für Shopbetreiber
        $me->sendmail2($me->from,$reportType."_".$invoiceNo,$pdfcode,$reportType."_".$invoiceNo.".pdf",false);
        // Versanddatum aktualisieren
        $SQL = "UPDATE ".DBToken."order SET ordInvSendDate=now() WHERE ordIdNo = ".$pKey;
        $qry = mysqli_query($link, $SQL);
        // Weiterleitung zu Shopbestellungen
    	header('Location: shoporder.search.php?lang='.$_REQUEST['lang']);
    } else {
    	die(L_dynsb_MailInvoiceError.$me->error);
    }
} else {
	if(!isset($_GET['d'])){
		// stream to browser only
		$pdf->ezStream();
	}
}
?>
