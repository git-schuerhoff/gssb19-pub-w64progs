<?php
/******************************************************************************/
/* File: mod.discount.cusdetail.php                                              */
/******************************************************************************/

require("../../include/login.check.inc.php");
require_once("../../include/functions.inc.php");
require("../../../conf/db.const.inc.php");
require_once("../../include/secure.functions.inc.php");

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

$sql = "SELECT * from ".DBToken."customer where cusIdNo = '".$_REQUEST['cusID']."'";
$qry = @mysqli_query($link,$sql);
$obj = @mysqli_fetch_object($qry);

if($obj)
{
  foreach($obj as $key => $value)
  {
    $$key = trim($value);
  }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_Customerdetails;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
    <script type="text/javascript" src="../../js/gslib.php"></script>
    <script language="JavaScript" type="text/javascript">
    function MM_reloadPage(init)
    {  //reloads the window if Nav4 resized
      if (init==true) with (navigator)
      {
        if ((appName=="Netscape")&&(parseInt(appVersion)==4))
        {
          document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage;
        }
      }
      else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
    }
    //--------------------------------------------------------------------------
    MM_reloadPage(true);
    </script>
</head>
<body>

<?php
require_once("../../include/page.header.php");
?>

<div id="PGdiscountcusdetails">
	<input type="hidden" name="start" value="<?php echo $lang;?>">
	<input type="hidden" name="lang" value="<?php echo $start;?>">
	<input type="hidden" name="cusIdNo" value="<?php echo $cusIdNo;?>">
	<input type="hidden" name="act" value="<?php echo $act;?>">

<h1>&#187;&nbsp;<?php echo L_dynsb_Customerdetails;?>&nbsp;&#171;</h1>

<table>
	<tr>
		<th style="width:160px;">&nbsp;</th>
		<th><?php echo L_dynsb_PersonData?></th>
		<th align="left"><?php echo L_dynsb_DeliverAddress;?></th>
	</tr>
  <tr>
    <td align="right"><?php echo L_dynsb_CustomerNo;?>: </td>
    <td><?php echo $cusId; ?><span class="warning"><?php echo "&nbsp;".$idWarning;?></span></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Firm;?>: </td>
    <td><?php echo $cusFirmname;?></td>
    <td><?php echo $cusDeliverFirmname;?></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Title;?>: </td>
    <td><?php echo $cusTitle;?></td>
     <td><?php echo $cusDeliverTitle;?></td>
  </tr>

  <tr>
    <td align="right"><?php echo L_dynsb_Name;?>: </td>
    <td style="font-weight: bold;"><?php echo $cusLastName;?>, <?php echo $cusFirstName;?></td>
    <td style="font-weight: bold;"><?php echo $cusDeliverLastName;?>, <?php echo $cusDeliverStreet;?></td>
  </tr>

  <tr>
    <td align="right"><?php echo L_dynsb_Street;?>: </td>
    <td style="font-weight: bold;"><?php echo $cusStreet;?></td>
    <td style="font-weight: bold;"><?php echo $cusDeliverStreet;?></td>
  </tr>
<?php if (!empty($cusStreet2) || !empty($cusDeliverStreet2)) { ?>
  <tr>
    <td align="right"><?php echo L_dynsb_Addition;?>: </td>
    <td><?php echo $cusStreet2;?></td>
    <td><?php echo $cusDeliverStreet2;?></td>
  </tr>
<?php } ?>
  <tr>
    <td align="right"><?php echo L_dynsb_Zipcode;?> <?php echo L_dynsb_City;?>: </td>
    <td style="font-weight: bold;"><?php echo $cusZipCode; ?> <?php echo $cusCity; ?></td>
    <td style="font-weight: bold;"><?php echo $cusDeliverZipCode;?><?php echo $cusDeliverCity;?></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Country;?>: </td>
    <td><?php echo $cusCountry;?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Phone;?>: </td>
    <td><?php echo $cusPhone;?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Fax;?>: </td>
    <td><?php echo $cusFax;?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Email;?>: </td>
    <td><?php echo $cusEMail;?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_EmailFormat;?>: </td>
    <td><?strtolower($cusEMailFormat);?></td>
    <td>&nbsp;</td>
  </tr>

  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>

  <tr>
    <td align="right"><?php echo L_dynsb_TaxNumber;?>:</td>
    <td><?php echo $cusFirmVATId;?></td>
     <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Password;?>:</td>
    <td><?php echo $cusPassword;?></td>
		<td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>

  <tr>
    <td align="right"><?php echo L_dynsb_CustomerDiscount;?>:</td>
    <td style="font-weight: bold;"><?php echo $cusDiscount;?> %</td>
		<td>&nbsp;</td>
  </tr>
  <tr>
    <th>&nbsp;</th>
    <th><?php echo L_dynsb_BankaccountData;?>:</th>
    <th>&nbsp;</th>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_FinancialInstitution;?>:</td>
    <td><?php gsshow('cusBank', $cusData); ?></td>
     <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_BankCode;?>:</td>
    <td><?php gsshow('cusBLZ', $cusData);?></td>
		<td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_AccountNumber;?>:</td>
    <td><?php gsshow('cusAccountNo', $cusData);?></td>
		<td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_AccountHolder;?>:</td>
    <td><?php gsshow('cusAccountOwner', $cusData);?></td>
    <td>&nbsp;</td>
  </tr>

  <tr>
    <th>&nbsp;</th>
		<th><?php echo L_dynsb_CreditcardData;?>&nbsp;</th>
    <th>&nbsp;</th>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Creditcard;?>:</td>
    <td><?php gsshow('cusCreditCard', $cusData);?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_ValidToMonth;?>:</td>
    <td><?php gsshow('cusCreditValidMonth', $cusData);?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_ValidToYear;?>:</td>
    <td><?php gsshow('cusCreditValidYear', $cusData);?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_CreditcardNo;?>:</td>
    <td><?php gsshow('cusCreditNo', $cusData);?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_CheckId1;?>:</td>
    <td><?php gsshow('cusCreditChk1', $cusData);?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_CheckId2;?>:</td>
    <td><?php gsshow('cusCreditChk2', $cusData);?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_CreditcardHolder;?>:</td>
    <td><?php gsshow('cusCreditOwner', $cusData);?></td>
    <td>&nbsp;</td>
  </tr>
</table>

<!-- navigation // -->
<div class="footer">
	<input type="button" class="button" onclick="javascript:window.close();" name="btn_save" value="<?php echo L_dynsb_Close;?>">
</div>

</div>
<?php
require_once("../../include/page.footer.php");
?>
</html>
