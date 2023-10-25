<?php
/******************************************************************************/
/* File: customer.export.php                                                  */
/******************************************************************************/

require("../include/login.check.inc.php");
require_once("../include/functions.inc.php");
require("../../conf/db.const.inc.php");
require_once("../include/secure.functions.inc.php");

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
/******************************************************************************/

/***************** Datenbankverbindung*****************************************/
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) 
  or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");
if(isset($_REQUEST['start'])) {
    $start = intval($_REQUEST['start']);
}
if(isset($_REQUEST['pk'])) {
    $cusIdNo = intval($_REQUEST['pk']);
}
if(isset($_REQUEST['act'])) {
    $act = trim($_REQUEST['act']);
}

$chgApplicId = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1)); // script name

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

$db_fields = array("t1" => L_dynsb_PersonData,
                   "cusId" => L_dynsb_CustomerNo,
                   "cusPassword" => L_dynsb_Password,
                   "cusFirmVATId" => L_dynsb_TaxNumber,
                   "cusFirmname" => L_dynsb_Firm,
                   "cusTitle" => L_dynsb_Title,
                   "cusFirstName" => L_dynsb_Firstname,
                   "cusLastName" => L_dynsb_Lastname,
                   "cusStreet" => L_dynsb_Street,
                   "cusStreet2" => L_dynsb_Addition,
                   "cusZipCode" => L_dynsb_Zipcode,
                   "cusCity" => L_dynsb_City,
                   "cusCountry" => L_dynsb_Country,
                   "cusPhone" => L_dynsb_Phone,
                   "cusFax" => L_dynsb_Fax,
                   "cusMobil" => L_dynsb_Mobil,
                   "cusBirthdate" => L_dynsb_Birthdate,
                   "cusEMail" => L_dynsb_Email,
                   "cusDiscount" => L_dynsb_CustomerDiscount,
                   "cusCustomerNews" => L_dynsb_CustomerNews,
                   "cusBonusPoints" => L_dynsb_BonusPoints,
                   "cusBlocked" => L_dynsb_Blocked,
                   "cusBlockedMessage" => L_dynsb_BlockedMessage,
                   "t2" => L_dynsb_DeliverAddress,
                   "cusDeliverFirmname" => L_dynsb_Firm,
                   "cusDeliverTitle" => L_dynsb_Title,
                   "cusDeliverFirstName" => L_dynsb_Firstname,
                   "cusDeliverLastName" => L_dynsb_Lastname,
                   "cusDeliverStreet" => L_dynsb_Street,
                   "cusDeliverStreet2" => L_dynsb_Addition,
                   "cusDeliverZipCode" => L_dynsb_Zipcode,
                   "cusDeliverCity" => L_dynsb_City,
                   "t3" => L_dynsb_BankaccountData,
                   "cusBank" => L_dynsb_FinancialInstitution,
                   "cusBLZ" => L_dynsb_BankCode,
                   "cusAccountNo" => L_dynsb_AccountNumber,
                   "cusAccountOwner" => L_dynsb_AccountHolder,
                   "t4" => L_dynsb_CreditcardData,
                   "cusCreditCard" => L_dynsb_Creditcard,
                   "cusCreditValidMonth" => L_dynsb_ValidToMonth,
                   "cusCreditValidYear" => L_dynsb_ValidToYear,
                   "cusCreditNo" => L_dynsb_CreditcardNo,
                   "cusCreditChk1" => L_dynsb_CheckId1,
                   "cusCreditChk2" => L_dynsb_CheckId2,
                   "cusCreditOwner" => L_dynsb_CreditcardHolder
                  );
$extraFields = array ("cusBank", "cusBLZ", "cusAccountNo", "cusAccountOwner",
                      "cusCreditCard", "cusCreditValidMonth", "cusCreditValidYear",
                      "cusCreditNo", "cusCreditChk1", "cusCreditChk2", "cusCreditOwner"
                     );                            

$expTxt = "";
if($_REQUEST['separator']=="tab")
{
  $sep = "\t";
}
else
{
  $sep = $_REQUEST['separator'];
}
$choice = $_REQUEST['choice'];
for($i=0; $i<sizeof($choice); $i++)
{
  if($i==sizeof($choice)-1)
  {
    $line .= $db_fields[$choice[$i]];
  }
  else
  {
    $line .= $db_fields[$choice[$i]].$sep;
  }
}
$expTxt .= $line."\n";
$line = "";
          
$sql = "SELECT * FROM ".DBToken."customer";
$qry = @mysqli_query($link,$sql);
$num = @mysqli_num_rows($qry);

while($obj = @mysqli_fetch_object($qry))
{
  for($i=0; $i<sizeof($choice); $i++)
  {
    if(in_array($choice[$i], $extraFields))
    {
      $value = gsget($choice[$i], $obj->cusData, 1);
    }
    else
    {
      $value = $obj->$choice[$i];
    }
    
    if($i==sizeof($choice)-1)
    {
      $line .= $value;
    }
    else
    {
      $line .= $value.$sep;
    }
  } 
  $expTxt .= $line."\n";
  $line = "";
}

$filename = $_REQUEST['txtfile'];
$uploaddir = 'upload/';
if(file_exists($uploaddir.$filename))
{
  unlink ($uploaddir.$filename);
}
$handle = fopen($uploaddir.$filename,"w+");
fwrite($handle, $expTxt);
fclose($handle);

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"".$filename."\"");
readfile($uploaddir.$filename);
          
?>        
