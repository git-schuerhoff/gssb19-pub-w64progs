<?php
/******************************************************************************/
/* File: customer.detail.php                                                  */
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
if(isset($_REQUEST['start']))
{ $start = intval($_REQUEST['start']); }

if(isset($_REQUEST['pk']))
{ $cusIdNo = intval($_REQUEST['pk']); }

if(isset($_REQUEST['act']))
{ $act = trim($_REQUEST['act']); }

$chgApplicId = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1)); // script name

if (!isset($_SESSION['SESS_userIdNo']) || strlen(trim($_SESSION['SESS_userIdNo'])) == 0)
{ die ("<br />error: missing session parameter!<br />"); }
else
{ $SESS_userIdNo = $_SESSION['SESS_userIdNo']; }

if (!isset($_SESSION['SESS_userLogin']) || strlen(trim($_SESSION['SESS_userLogin'])) == 0)
{ die ("<br />error: missing session parameter!<br />"); }
else
{ $SESS_userId = $_SESSION['SESS_userId']; }

if (!isset($_SESSION['SESS_languageIdNo']) || strlen(trim($_SESSION['SESS_languageIdNo'])) == 0) {
  die ("<br />error: missing session parameter!<br />");
}
else
{ $SESS_languageIdNo = $_SESSION['SESS_languageIdNo']; }

if(strtolower($act) == "e")
{
  $qrySQL = "SELECT * FROM ".DBToken."customer WHERE cusIdNo = '".$cusIdNo."'";
  $qry = @mysqli_query($link,$qrySQL);
  $obj = @mysqli_fetch_object($qry);
  if($obj)
  {
    foreach($obj as $key => $value)
    {
//A UR 23.2.2011                        
      if($key == 'cusBirthdate')
      {
        $teile = explode('-',$value);
        $value = $teile[2].".".$teile[1].".".$teile[0];  
      }
//E UR                      
      $$key = trim($value);
    }

    $idWarning = "";
    if($obj->cusId != "")
    {
      $SQLchk = "SELECT COUNT(cusId) AS qty FROM ".DBToken."customer WHERE cusId = '".$obj->cusId."'";
      $qrychk = @mysqli_query($link,$SQLchk);
      $objchk = @mysqli_fetch_object($qrychk);
      if($objchk->qty > 1)
      {
        $idWarning = $objchk->qty.L_dynsb_XtimesExistent;
      }
    }
  }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_Customerdetails;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software AG">
    <link rel="stylesheet" type="text/css" href="../css/link.css">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
    <script type="text/javascript" src="../js/gslib.php"></script>
    <script language="JavaScript" type="text/javascript">
    function MM_reloadPage(init)  //reloads the window if Nav4 resized
    {
      if (init==true) with (navigator)
      {
        if ((appName=="Netscape")&&(parseInt(appVersion)==4))
        {
          document.MM_pgW=innerWidth;
          document.MM_pgH=innerHeight;
          onresize=MM_reloadPage;
        }
      }
      else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH)
      {
        location.reload();
      }
    }
    //--------------------------------------------------------------------------
    MM_reloadPage(true);
    //--------------------------------------------------------------------------
    function save()
    {
      iSave = 0;
      iSave = iSave + markField(document.frmCustomer.cusLastName);
      iSave = iSave + markField(document.frmCustomer.cusStreet);
      iSave = iSave + markField(document.frmCustomer.cusZipCode);
      iSave = iSave + markField(document.frmCustomer.cusCity);
      iSave = iSave + markField(document.frmCustomer.cusCountry);
      iSave = iSave + markField(document.frmCustomer.cusEMail);
      (iSave == 0) ? submitForm('frmCustomer') : alert('<?php echo L_dynsb_CheckMandatoryFields;?>');
    }
    
    
    function setDefaultBlockedMessage()
    {
      if (document.frmCustomer.cusBlockedMessage.value.length == 0)
      {
        if (document.frmCustomer.cusBlocked.checked)
        {      
        document.frmCustomer.cusBlockedMessage.value = "You are locked.";
        }
      }
    }
    
    </script>
</head>
<body>
<form name="frmCustomer" action="customer.save.php" method="post">
 

<?php
require_once("../include/page.header.php");
?>

<div id="PGcustomerdetail">
  <input type="hidden" name="lang" value="<?php echo $lang;?>" />
  <input type="hidden" name="start" value="<?php echo $start;?>" />
  <input type="hidden" name="cusIdNo" value="<?php echo $cusIdNo;?>" />
  <input type="hidden" name="act" value="<?php echo $act;?>" />

  <h1>&#187;&nbsp;<?php echo L_dynsb_Customerdetails;?>&nbsp;&#171;</h1>

  <!--personal data-->
  <table>
    <tr>
      <th colspan="2"><?php echo L_dynsb_PersonData;?></th>
      <th align="left"><?php echo L_dynsb_DifferentDeliverAddress;?></th>
    </tr>

    <tr>
      <td align="right" style="width:140px;"><?php echo L_dynsb_CustomerNo;?>&nbsp;</td>
      <td style="width:140px;">
        <input type="text" class="customer" tabindex=1 maxlength="32" value="<?php echo $cusId; ?>"name="cusId" />
        <span class="warning"><?php echo "&nbsp;".$idWarning;?></span>
      </td>
      <td>&nbsp;</td>
    </tr>

    <tr>
      <td align="right"><?php echo L_dynsb_Firm;?>&nbsp;</td>
      <td>
        <input type="text" class="customer" tabindex=2 maxlength="64" value="<?php echo $cusFirmname;?>" name="cusFirmname" />
      </td>
      <td>
        <input type="text" class="customer" tabindex=15 maxlength="32" value="<?php echo $cusDeliverFirmname;?>" name="cusDeliverFirmname" />
      </td>
    </tr>

    <tr>
      <td align="right"><?php echo L_dynsb_Title;?>&nbsp;</td>
      <td>
        <input type="text" class="customer" tabindex=3 maxlength="16" value="<?php echo $cusTitle;?>" name="cusTitle" />
      </td>
      <td>
        <input type="text" class="customer" tabindex=16 maxlength="16" value="<?php echo $cusDeliverTitle;?>" name="cusDeliverTitle" />
      </td>
    </tr>

    <tr>
      <td align="right"><?php echo L_dynsb_Firstname;?>&nbsp;</td>
      <td>
        <input type="text" class="customer" tabindex=4 maxlength="32" value="<?php echo $cusFirstName; ?>" name="cusFirstName" />
      </td>
      <td>
        <input type="text" class="customer" tabindex=17 maxlength="32" value="<?php echo $cusDeliverFirstName;?>" name="cusDeliverFirstName" />
      </td>
    </tr>

    <tr>
      <td align="right"><?php echo L_dynsb_Lastname;?>*&nbsp;</td>
      <td>
        <input type="text" class="customer" tabindex=5 maxlength="32" value="<?php echo $cusLastName; ?>" name="cusLastName" />
      </td>
      <td>
        <input type="text" class="customer" tabindex=18 maxlength="32" value="<?php echo $cusDeliverLastName;?>" name="cusDeliverLastName" />
      </td>
    </tr>

    <tr>
      <td align="right"><?php echo L_dynsb_Street;?>*&nbsp;</td>
      <td>
        <input type="text" class="customer" tabindex=6 maxlength="32" value="<?php echo $cusStreet;?>" name="cusStreet" />
      </td>
      <td>
        <input type="text" class="customer" tabindex=19 maxlength="32" value="<?php echo $cusDeliverStreet;?>" name="cusDeliverStreet" />
      </td>
    </tr>

    <tr>
      <td align="right"><?php echo L_dynsb_Addition;?>&nbsp;</td>
      <td>
        <input type="text" class="customer" tabindex=7 maxlength="32" value="<?php echo $cusStreet2;?>" name="cusStreet2" />
      </td>
      <td>
        <input type="text" class="customer" tabindex=20 maxlength="32" value="<?php echo $cusDeliverStreet2;?>" name="cusDeliverStreet2" />
      </td>
    </tr>


    <tr>
      <td align="right"><?php echo L_dynsb_Zipcode;?>*&nbsp;</td>
      <td>
        <input type="text" class="customer" tabindex=8 maxlength="16" value="<?php echo $cusZipCode; ?>" class="inputbox75_eingabe" name="cusZipCode" />
      </td>
      <td>
        <input type="text" class="customer" tabindex=21 maxlength="32" value="<?php echo $cusDeliverZipCode;?>" class="inputbox75_eingabe" name="cusDeliverZipCode" />
      </td>
    </tr>

    <tr>
      <td align="right"><?php echo L_dynsb_City;?>*&nbsp;</td>
      <td>
        <input type="text" class="customer" tabindex=9 maxlength="32" value="<?php echo $cusCity; ?>" name="cusCity" />
      </td>
      <td>
        <input type="text" class="customer" tabindex=22 maxlength="32" value="<?php echo $cusDeliverCity;?>" name="cusDeliverCity" />
      </td>
    </tr>


    <tr>
      <td align="right"><?php echo L_dynsb_Country;?>*&nbsp;</td>
      <td>
        <input type="text" class="customer" tabindex=10 maxlength="32" value="<?php echo $cusCountry;?>"name="cusCountry" />
      </td>
       <td>&nbsp;</td>
    </tr>

    <tr>
      <td align="right"><?php echo L_dynsb_Phone;?>&nbsp;</td>
      <td>
        <input type="text" class="customer" tabindex=11 maxlength="20" value="<?php echo $cusPhone;?>" class="inputbox150_eingabe" name="cusPhone" />
      </td>
      <td>&nbsp;</td>
    </tr>

    <tr>
      <td align="right"><?php echo L_dynsb_Fax;?>&nbsp;</td>
      <td>
        <input type="text" class="customer" tabindex=12 maxlength="20" value="<?php echo $cusFax;?>" name="cusFax" />
      </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_Mobil;?>&nbsp;</td>
      <td>
        <input type="text" class="customer" tabindex=13 maxlength="20" value="<?php echo $cusMobil;?>" name="cusMobil" />
      </td>
      <td>&nbsp;</td>
    </tr>

     <tr>
      <td align="right"><?php echo L_dynsb_Email;?>&nbsp;(=&nbsp;<?php echo L_dynsb_Username;?>)*&nbsp;</td>
      <td>
        <input type="text" class="customer" tabindex=14 maxlength="64" value="<?php echo $cusEMail;?>" name="cusEMail" />
      </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_EmailFormat;?>&nbsp;</td>
      <td>
          <select tabindex="15" name="cusEMailFormat" style="width:60px">
            <option value="text" <?php if(strtolower($cusEMailFormat) == "text") echo " selected ";?>><?php echo L_dynsb_EmailFormatText;?></option>
            <option value="html" <?php if(strtolower($cusEMailFormat) == "html") echo " selected ";?>><?php echo L_dynsb_EmailFormatHtml;?></option>
          </select>
      </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_Birthdate;?>&nbsp;</td>
      <td>
        <input type="text" class="customer" tabindex=16 maxlength="10" value="<?php echo $cusBirthdate;?>" name="cusBirthdate" />
      </td>
      <td>&nbsp;</td>
    </tr>
     <tr>
      <td align="right"><?php echo L_dynsb_Blocked;?>&nbsp;</td>
      <td>
        <input class="checkbox" name="cusBlocked" type="checkbox" value="1"<?php if($cusBlocked==1) echo " checked";?> onClick="setDefaultBlockedMessage()" />
      </td>
      <td>&nbsp;</td>
    </tr>
     <tr>
      <td align="right"><?php echo L_dynsb_BlockedMessage;?>&nbsp;</td>
      <td  colspan="2">
        <input type="text" class="customer2" maxlength="255" value="<?php echo $cusBlockedMessage;?>" name="cusBlockedMessage" />
      </td>
    </tr>
    <!-- //END personal data -->

    <!-- stuff -->
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>

    <tr>
        <th colspan="2"><?php echo L_dynsb_furtherInformations;?></th>
        <th>&nbsp;</th>
    </tr>

    <tr>
      <td align="right"><?php echo L_dynsb_TaxNumber;?>&nbsp;</td>
      <td colspan="2">
        <input type="text" class="customer" tabindex=24 maxlength="32" value="<?php echo $cusFirmVATId;?>" name="cusFirmVATId" />
      </td>
    </tr>


    <tr>
      <td align="right"><?php echo L_dynsb_Password;?>&nbsp;</td>
      <td colspan="2">
        <input type="text" class="customer" tabindex=25 maxlength="32" value="<?php echo $cusPassword;?>" name="cusPassword" />
      </td>
    </tr>


    <tr>
      <td align="right"><?php echo L_dynsb_CustomerNews;?>&nbsp;</td>
      <td colspan="2" >
        <select name="cusCustomerNews" size="1" tabindex=26 style="width:180px">
        <?php
          if ($cusCustomerNews==0)
          {
            echo "<option value='".$cnews->cnewsIdNo."' selected>".$cnews->cnewsTitle."</option>";
          }
          else
          {
            echo "<option value='".$cnews->cnewsIdNo."'>".$cnews->cnewsTitle."</option>";
          }

          $sql = "select * from ".DBToken."customernews where cnewsForAll <> '1'";
          $qry = @mysqli_query($link,$sql);
          while($cnews = @mysqli_fetch_object($qry))
          {
            if(strlen($cnews->cnewsTitle) > 28){
              $cnewsTitle = strip_tags(substr($cnews->cnewsTitle, 0, 23))." [...]";
            }
            else {
              $cnewsTitle = strip_tags($cnews->cnewsTitle);
            }

            if(($cnews->cnewsIdNo)==$cusCustomerNews) {
              echo "<option value='".$cnews->cnewsIdNo."' selected>".$cnewsTitle."</option>";
            }
            else {
              echo "<option value='".$cnews->cnewsIdNo."'>".$cnewsTitle."</option>";
            }
          }
        ?>
        </select>
      </td>
    </tr>
	
    <tr>
      <td align="right"><?php echo L_dynsb_CustomerDiscount;?>&nbsp;</td>
      <td colspan="2">
        <input type="text" class="customer" class="customer" size="2" tabindex=27 maxlength="32" value="<?php echo $cusDiscount;?>" name="cusDiscount" />
      </td>
    </tr>
	
	<!-- BONUS POINTS -->
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
     </tr>
    <tr>
     <th colspan="2"><?php echo L_dynsb_BonusPoints;?></th>
     <th>&nbsp;</th>
    </tr>

    <tr>
      <td align="right"><?php echo L_dynsb_BonusPoints;?>&nbsp;</td>
      <td colspan="2">
        <input type="text" class="customer" tabindex=32 maxlength="25" value="<?php echo $cusBonusPoints; ?>" name="cusBonusPoints" />
      </td>
    </tr>

    <!-- BANK DATA -->

    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
     </tr>

    <tr>
        <th colspan="2"><?php echo L_dynsb_BankaccountData;?></th>
        <th>&nbsp;</th>
    </tr>

    <tr>
      <td align="right"><?php echo L_dynsb_FinancialInstitution;?>&nbsp;</td>
      <td colspan="2">
        <input type="text" class="customer" tabindex=28 maxlength="32" value="<?php gsshow('cusBank',$cusData); ?>" class="inputbox200_eingabe" name="cusBank" />
      </td>
    </tr>

    <tr>
      <td align="right"><?php echo L_dynsb_BankCode;?>&nbsp;</td>
      <td colspan="2">
        <input type="text" class="customer" tabindex=29 maxlength="16" value="<?php gsshow('cusBLZ',$cusData);?>" class="inputbox200_eingabe" name="cusBLZ" />
      </td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_AccountNumber;?>&nbsp;</td>
      <td colspan="2">
        <input type="text" class="customer" tabindex=30 maxlength="40" value="<?php gsshow('cusAccountNo',$cusData);?>" class="inputbox200_eingabe" name="cusAccountNo" />
      </td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_AccountHolder;?>&nbsp;</td>
      <td colspan="2">
        <input type="text" class="customer" tabindex=31 maxlength="25" value="<?php gsshow('cusAccountOwner',$cusData);?>" class="inputbox200_eingabe" name="cusAccountOwner" />
      </td>
    </tr>


    <!-- CREDIT CARD DATA -->
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
     </tr>
    <tr>
     <th colspan="2"><?php echo L_dynsb_CreditcardData;?></th>
     <th>&nbsp;</th>
    </tr>

    <tr>
      <td align="right"><?php echo L_dynsb_Creditcard;?>&nbsp;</td>
      <td colspan="2">
        <input type="text" class="customer" tabindex=32 maxlength="25" value="<?php gsshow('cusCreditCard',$cusData);?>" name="cusCreditCard" />
      </td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_ValidToMonth;?>&nbsp;</td>
      <td colspan="2">
        <input type="text" class="customer" tabindex=33 maxlength="2" value="<?php gsshow('cusCreditValidMonth',$cusData);?>" name="cusCreditValidMonth" />
      </td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_ValidToYear;?>&nbsp;</td>
      <td colspan="2">
        <input type="text" class="customer" tabindex=34 maxlength="4" value="<?php gsshow('cusCreditValidYear',$cusData);?>" name="cusCreditValidYear" />
      </td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_CreditcardNo;?>&nbsp;</td>
      <td colspan="2">
        <input type="text" class="customer" tabindex=35 maxlength="16" value="<?php gsshow('cusCreditNo',$cusData);?>"name="cusCreditNo" />
      </td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_CheckId1;?>&nbsp;</td>
      <td colspan="2">
        <input type="text" class="customer" tabindex=36 maxlength="16" value="<?php gsshow('cusCreditChk1',$cusData);?>"name="cusCreditChk1" />
      </td>
    </tr>
    <tr>
      <td align="right"><?php echo L_dynsb_CheckId2;?>&nbsp;</td>
      <td colspan="2">
        <input type="text" class="customer" tabindex=37 maxlength="16" value="<?php gsshow('cusCreditChk2',$cusData);?>"name="cusCreditChk2" />
      </td>
    </tr>

    <tr>
      <td align="right"><?php echo L_dynsb_CreditcardHolder;?>&nbsp;</td>
      <td colspan="2">
        <input type="text" class="customer" tabindex=38 maxlength="16" value="<?php gsshow('cusCreditOwner',$cusData);?>" name="cusCreditOwner" />
      </td>
    </tr>

    <tr>
      <td colspan="3" align="right">&nbsp;</td>
    </tr>
  </table>


  <div class="footer">
    <input type="button" class="button" onClick="save()" value="<?php echo L_dynsb_Save;?>" />&nbsp;
    <input type="button" class="button" onClick="window.location='customer.search.php?start=<?php echo $start;?>';" value="<?php echo L_dynsb_Cancel;?>" />
  </div>
</div>

<?php
require_once("../include/page.footer.php");
?>

</form>
</body>
</html>
