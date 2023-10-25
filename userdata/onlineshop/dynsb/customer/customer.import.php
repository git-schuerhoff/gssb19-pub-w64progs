<?php
/******************************************************************************/
/* File: customer.import.php                                                  */
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

//------------------------------------------------------------------------------
//
// input validation
//
// needed parameters

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

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_CustomerDataImport;?></title>
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
    function insertField()
    {
      var csvfield_index = document.frmCustomer.csvfields.selectedIndex;
      var dbfield_index = document.frmCustomer.dbfields.selectedIndex;

      if ((csvfield_index > -1) && (dbfield_index > -1)) {
      	var csvfield_text = document.frmCustomer.csvfields.options[csvfield_index].text;
	      var csvfield_value = document.frmCustomer.csvfields.value;
      	var dbfield_text = document.frmCustomer.dbfields.options[dbfield_index].text;
	      var dbfield_value = document.frmCustomer.dbfields.value;

	      var newfield_text =  csvfield_text+" -> "+dbfield_text;
	      var newfield_value =  csvfield_value+"-"+dbfield_value;

	      var newfield = new Option(newfield_text, newfield_value, false, true);
	      document.frmCustomer.assignment.options[document.frmCustomer.assignment.length] = newfield;
	      if(document.frmCustomer.assignment.options[0].value=='<?php echo L_dynsb_Empty;?>')
	      {
	        document.frmCustomer.assignment.options[0] = null;
	      }
      }
    }
    //--------------------------------------------------------------------------
    function delField()
    {
      var selectCust = document.frmCustomer.assignment.selectedIndex;
      document.frmCustomer.assignment.options[selectCust] = null;
    }
    //--------------------------------------------------------------------------
    function resetField()
    {
      for(var a=0; a<document.frmCustomer.assignment.length; a++)
      {
        document.frmCustomer.assignment.length = 0;
      }
    }
    //--------------------------------------------------------------------------
    function submitData()
    {
      for(var a=0; a<document.frmCustomer.assignment.length; a++)
      {
        document.frmCustomer.assignment.options[a].selected = false;
      }
      var custtxt = "";
      var file = document.frmCustomer.file.value;
      var sep = document.frmCustomer.sep.value;

      for(var a=0; a<document.frmCustomer.assignment.length; a++)
      {
        custtxt += "_"+document.frmCustomer.assignment.options[a].value;
      }

      if(checkData(custtxt))
      {
        document.location = 'customer.import.save.php?custtxt='+custtxt+'&file='+file+'&sep='+sep+'&lang=<?php echo $lang;?>';
      }
    }
    //--------------------------------------------------------------------------
    function checkData(txt)
    {
      if(txt.search(/cusLastName/)==-1)
      {
        alert("<?php echo L_dynsb_MandatoryFieldMissing."'".L_dynsb_Lastname."'";?>");
      }
      else
      {
        if(txt.search(/cusStreet/)==-1)
        {
          alert("<?php echo L_dynsb_MandatoryFieldMissing."'".L_dynsb_Street."'";?>");
        }
        else
        {
          if(txt.search(/cusZipCode/)==-1)
          {
            alert("<?php echo L_dynsb_MandatoryFieldMissing."'".L_dynsb_Zipcode."'";?>");
          }
          else
          {
            if(txt.search(/cusCity/)==-1)
            {
              alert("<?php echo L_dynsb_MandatoryFieldMissing."'".L_dynsb_City."'";?>");
            }
            else
            {
              if(txt.search(/cusCountry/)==-1)
              {
                alert("<?php echo L_dynsb_MandatoryFieldMissing."'".L_dynsb_Country."'";?>");
              }
              else
              {
                if(txt.search(/cusEMail/)==-1)
                {
                  alert("<?php echo L_dynsb_MandatoryFieldMissing."'".L_dynsb_Email."'";?>");
                }
                else
                {
                  return true;
                }
              }
            }
          }
        }
      }
    }
    </script>
</head>
<body>
<form name="frmCustomer" enctype="multipart/form-data" method="post" action="customer.import.php">

<?php
require_once("../include/page.header.php");
?>

<div id="PGcustomerimport">
  <input type="hidden" name="start" value="<?PHP echo $start;?>">
  <input type="hidden" name="start" value="<?PHP echo $start;?>">
  <input type="hidden" name="cusIdNo" value="<?PHP echo $cusIdNo;?>">
  <input type="hidden" name="act" value="<?PHP echo $act;?>">

  <h1>&nbsp;&#187;&nbsp;<?php echo L_dynsb_CustomerDataImport;?>&nbsp;&#171;</h1>

<?php
  if(!isset($_REQUEST['act']))
  {
?>
  <h2><?php echo L_dynsb_Separator;?></h2>

  <p><input type="radio" class="radio" value=";" name="separator" checked>&nbsp;<?php echo L_dynsb_Semicolon;?></p>
  <p><input type="radio" class="radio" value="tab" name="separator">&nbsp;<?php echo L_dynsb_Tabstopp;?></p>

  <h2><?php echo L_dynsb_FileName;?></h2>
  <p>
    <input type="hidden" name="act" value="a">
   
    <input type="file" class="file" name="txtfile" size="45">
  </p>
  <p>
    <input type="button" class="button" onClick="javascript:document.frmCustomer.submit();" value="<?php echo L_dynsb_LoadFile;?>">
  </p>
  <br />


<?php
  }
  elseif($_REQUEST['act'] == "a")  {
    $sep = $_REQUEST['separator'];

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
    $uploaddir = 'upload/';
    $filename = $_FILES['txtfile']['name'];
 
    if (move_uploaded_file($_FILES['txtfile']['tmp_name'], $uploaddir . $_FILES['txtfile']['name']))
    {
     $fp = fopen($uploaddir.$filename,"r");
?>
     <table>
     <tr>
       <input type="hidden" name="file" value="<?php echo $uploaddir.$filename;?>">
       <input type="hidden" name="sep" value="<?php echo $sep;?>">
       <th align='left'><?php echo L_dynsb_FieldNameFromCsvFile;?></th>
       <th><?php echo L_dynsb_DatabaseFields;?></th>
       <th><?php echo L_dynsb_AssignmentCsvDatabase;?></th>
     </tr>
     <tr>
       <td valign="top">
         <?php
         if ($fp)
         {
            $zeile = fgets($fp, 20000);
            if($sep=="tab") {
              $sep = "\t";
            }

            $fieldname = explode($sep,$zeile);
            if($sep == "\t") {
              $sep = "tab";
            }
            fclose($fp);
            echo "<select name='csvfields' size='10' class=\"larger\">";
            foreach($fieldname as $key => $value) {
              echo "<option value='".$key."'>".$value."</option>";
            }
            echo "</select>";
          }
          ?>

					<br /><br />
          <p style="font-weight:bold;"><?php echo L_dynsb_MandatoryFields;?>:</p>
          <ul>
            <li><?php echo L_dynsb_Lastname;?></li>
            <li><?php echo L_dynsb_Street;?></li>
            <li><?php echo L_dynsb_Zipcode;?></li>
            <li><?php echo L_dynsb_City;?></li>
            <li><?php echo L_dynsb_Country;?></li>
            <li><?php echo L_dynsb_Email;?></li>
          </ul>
        </td>

        <td valign="top" align="center">
<?php
         echo "<select name=\"dbfields\" class=\"larger\" size=\"20\" ondblclick=\"javascript:insertField();\">";
          $start = 0;
          $t = 2;
          foreach($db_fields as $key => $value) {
            if($start == 0) {
              echo "<optgroup label='".$value."'>";
              $start = 1;
            }
            else {
              if($key=="t".$t && $start == 1) {
                echo "</optgroup>";
                echo "<optgroup label='".$value."'>";
                $t++;
              }
              else {
                echo "<option value=\"$key\">$value</option>";
              }
            }
          }
          echo "</optgroup></select>";
?>

          <p>
        		<input type="button" class="button" onClick="javascript:insertField();" value="<?php echo L_dynsb_Add;?>">
						<input type="button" class="button" onClick="javascript:delField();" value="<?php echo L_dynsb_Delete;?>">
          </p>
        </td>

        <td valign="top" align="center">
          <select name="assignment" size="20" class="larger" multiple ondblclick="javascript:delField();">
            <option value="<?php echo L_dynsb_Empty;?>"><?php echo L_dynsb_Empty;?></option>
          </select>
          <p><input type="button" class="button" onClick="javascript:resetField();" value="<?php echo L_dynsb_Reset2;?>"></p>

        </td>
      </tr>
     </table>

     <div class="footer">
  		<input type="button" class="button large" onClick="javascript:submitData();" value="<?php echo L_dynsb_ReadCustomerData;?>">
     </div>
<?php
    }
    else {
      echo L_dynsb_FileNoUploaded;
    }
  }
?>
</div>

<?php
require_once("../include/page.footer.php");
?>

</form>
</body>
</html>
