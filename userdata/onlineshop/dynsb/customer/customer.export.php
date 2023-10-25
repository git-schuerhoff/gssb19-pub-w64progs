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

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_CustomerDataExport;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software AG">
    <link rel="stylesheet" type="text/css" href="../css/link.css">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
    <script type="text/javascript" src="../js/gslib.php"></script>
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
    //--------------------------------------------------------------------------
    function delField()
    {
      var selectCust = document.getElementById('s1').selectedIndex;
      document.getElementById('s1').options[selectCust] = null;
    }
    //--------------------------------------------------------------------------
    function resetField()
    {
      for(var a=0; a<document.getElementById('s1').length; a++)
      {
        document.getElementById('s1').length = 0;
      }
    }
    //--------------------------------------------------------------------------
    function insertField()
    {
      var length = document.frmCustomer.dbfields.length;
      for(i=0; i<length; i++)
      {
        if(document.frmCustomer.dbfields.options[i].selected == true)
        {
          var dbfield_value = document.frmCustomer.dbfields.options[i].value;
          var dbfield_text = document.frmCustomer.dbfields.options[i].text;
          var newfield = new Option(dbfield_text, dbfield_value, false, true);
          document.getElementById('s1').options[document.getElementById('s1').length] = newfield;

          if(document.getElementById('s1').options[0].value=='<?php echo L_dynsb_Empty;?>')
          {
            document.getElementById('s1').options[0] = null;
          }
        }
      }
    }
    //--------------------------------------------------------------------------
    function submitData()
    {
      document.frmCustomer.submit();
    }
    </script>
</head>
<body>
<form name="frmCustomer" method="get" action="download.php">

<?php
require_once("../include/page.header.php");
?>

<div id="PGcustomerexport">
	<input type="hidden" name="lang" value="<?php echo $lang;?>" />
	<input type="hidden" name="start" value="<?php echo $start;?>" />
	<input type="hidden" name="cusIdNo" value="<?php echo $cusIdNo;?>" />
	<input type="hidden" name="act" value="1" />

<h1>&nbsp;&#187;&nbsp;<?php echo L_dynsb_CustomerDataExport;?>&nbsp;&#171;</h1>

<h2><?php echo L_dynsb_Separator;?></h2>
 <p><input type="radio" class="radio" value=";" name="separator" checked />&nbsp;<?php echo L_dynsb_Semicolon;?></p>
 <p><input type="radio" class="radio" value="tab" name="separator" />&nbsp;<?php echo L_dynsb_Tabstopp;?></p>

<h2><?php echo L_dynsb_FileName;?></h2>
 <p><input type="text" name="txtfile" value="customers<?php echo date("_Y_m_d")?>.csv" style="width:280px" /></p>


<table>
<tr>
  <th><?php echo L_dynsb_DatabaseFields;?></th>
  <th>&nbsp;</th>
  <th><?php echo L_dynsb_Choice;?></th>
</tr>

<tr>
  <td align="center">
<?php
  echo "<select name=\"dbfields\" size=\"20\" class=\"larger\" ondblclick=\"javascript:insertField();\" multiple>";
  $start = 0;
  $t = 2;
  foreach($db_fields as $key => $value)
  {
    if($start == 0)
    {
      echo "<optgroup label='".$value."'>";
      $start = 1;
    }
    else
    {
      if($key=="t".$t && $start == 1)
      {
        echo "</optgroup>";
        echo "<optgroup label='".$value."'>";
        $t++;
      }
      else
      {
        echo "<option value='".$key."'>".$value."</option>";
      }
    }
  }
  echo "</optgroup></select>";
?>
  </td>
  <td align="center" valign="top">
  	<br />
    <p><input type="button" class="button" onclick="javascript:insertField();" value="<?php echo L_dynsb_Add;?>" /></p>
    <p><input type="button" class="button" onclick="javascript:delField();" value="<?php echo L_dynsb_Delete;?>" /></p>
    <p><input type="button" class="button" onclick="javascript:resetField();" value="<?php echo L_dynsb_Reset2;?>" /></p>
  </td>
  <td align="center">
    <select id="s1" name="choice[]" size="20" class="larger" ondblclick="javascript:delField();" multiple>
      <option value='<?php echo L_dynsb_Empty;?>'><?php echo L_dynsb_Empty;?></option>
    </select>
  </td>
</tr>
</table>
<br />

<div class="footer">
 <input type="button" class="button large" onclick="javascript:submitData();" value="<?php echo L_dynsb_CustomerDataExportBtn;?>" />
</div>
</div>

<?php
require_once("../include/page.footer.php");
?>

<p>

</p>
</form>
</body>
</html>
