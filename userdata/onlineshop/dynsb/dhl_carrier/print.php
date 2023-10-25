<?php
/******************************************************************************/
/* File: print.php                                                            */
/******************************************************************************/

require("../include/login.check.inc.php");
require_once("../include/functions.inc.php");
require("../../conf/db.const.inc.php");

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
  or die("<br>aborted: can´t connect to '$dbServer' <br>");
$link->query("SET NAMES 'utf8'");
/******************************************************************************/

$sql = "SELECT * FROM ".DBToken."order WHERE ordIdNo='".$_REQUEST['pk']."'";
$rs = @mysqli_query($link,$sql);
$oOrder = @mysqli_fetch_object($rs);

$partnerID = "GSSOF";
$shipmentID = "1";
$keyPhase = "1";
$partnerUserId = "";
$userEmail = "";
$RequestTimestamp = date('dmY-His');
$keyword = "yC8QgXgzSN671Xe13XqaBIh69sPAtG4y";

$partner_signature = $partnerID."::".$shipmentID."::".$RequestTimestamp."::".$keyPhase."::".$partnerUserId."::".$userEmail."::".$keyword;
$partner_sig = substr(md5(trim($partner_signature)),0,16);

$sql = "SELECT * FROM ".DBToken."settings";
$rs = @mysqli_query($link,$sql);
$olp = @mysqli_fetch_object($rs);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
  <meta content="de" http-equiv="Language">
  <meta name="author" content="GS Software Solutions GmbH">
  <link rel="stylesheet" type="text/css" href="../css/link.css">
  <script type="text/javascript">
    <?php //include ("dhl_javascript.php");?>
  </script>
</head>
<body>
<form name='labelprint' action='dhl_url.php' method='POST' target="_blank">
<?php
require_once("../include/page.header.php");
?>

<DIV id="PGcarrierprint">
<?php include ($dhlLabelURL."dhl_logo8.php?url=".$dhlLabelURL) ?>
<h1>&#187;&nbsp;<?php echo L_dynsb_PrintLabel;?>&nbsp;&#171;</h1>


<table>
<tr>
  <th>&nbsp;</th>
  <th align="left"><?php echo L_dynsb_Sender;?></th>
  <th align="left"><?php echo L_dynsb_Recipient;?><br></th>
</tr>
<tr>
  <td align="right"><?php echo L_dynsb_Firm;?>:</td>
  <td><?php echo $olp->lpFirmname;?>&nbsp;</td>
  <td><?php echo $oOrder->ordFirmname;?>&nbsp;</td>
</tr>

<tr>
  <td style="width: 100px;" align="right"><?php echo L_dynsb_Title;?>:</td>
  <td style="width: 160px;"><?php echo $olp->lpSalutation;?>&nbsp;</td>
  <td><?php echo $oOrder->ordTitle;?>&nbsp;</td>
</tr>

<tr>
  <td align="right"><?php echo L_dynsb_Name;?>:</td>
  <td style="font-weight:bold;"><?php echo $olp->lpLastname;?><?php if (!empty($oOrder->lpLastname)) echo ","?> <?php echo $olp->lpFirstname;?>&nbsp;</td>
  <td style="font-weight:bold;"><?php echo $oOrder->ordLastName;?><?php if (!empty($oOrder->ordFirstName)) echo ","?> <?php echo $oOrder->ordFirstName;?>&nbsp;</td>
</tr>
<tr>
  <td align="right"><?php echo L_dynsb_Street;?>:</td>
  <td style="font-weight:bold;"><?php echo $olp->lpStreet;?>&nbsp;</td>
  <td style="font-weight:bold;"><?php echo $oOrder->ordStreet;?>&nbsp;</td>
</tr>

<?php
if (!empty($olp->lpAddress) || !empty($oOrder->ordStreet2)) {
?>
<tr>
  <td align="right"><?php echo L_dynsb_Addition;?>:</td>
  <td><?php echo $olp->lpAddress;?>&nbsp;</td>
  <td><?php echo $oOrder->ordStreet2;?>&nbsp;</td>
</tr>
<?php
}
?>
<tr>
  <td align="right"><?php echo L_dynsb_Zipcode;?> / <?php echo L_dynsb_City;?>:</td>
  <td style="font-weight:bold;"><?php echo $olp->lpZipCode;?> <?php echo $olp->lpCity;?>&nbsp;</td>
  <td style="font-weight:bold;"><?php echo $oOrder->ordZipCode;?> <?php echo $oOrder->ordCity;?>&nbsp;</td>
</tr>

<tr>
  <td align="right"><?php echo L_dynsb_Country;?>:</td>
  <td><?php echo $olp->lpCountry;?>&nbsp;</td>
  <td><?php echo $oOrder->ordCountry;?>&nbsp;</td>
</tr>
<tr>
  <td align="right"><?php echo L_dynsb_Phone;?>:</td>
  <td><?php echo $olp->lpPhone;?>&nbsp;</td>
  <td><?php echo $oOrder->ordPhone;?>&nbsp;</td>
</tr>
</table>


<h2>Produktwahl</h2>
<p><?php echo L_dynsb_ChoiceTheProduct;?>:</p>
<p>
  <select size='1' name='SHIPMENT_CARRIER_PRODUCT_ID'>
    <option value='139200210'><?php echo L_dynsb_Parcel;?></option>
    <option value='102000001'><?php echo L_dynsb_SmallParcel;?></option>
    <option value='137400010'><?php echo L_dynsb_DHLparcelBadge;?></option>
 </select>
</p>

<div class="footer">
	
	<input type="submit" class="button" value="<?php echo L_dynsb_Print;?>">

	<input type='hidden' name='PARTNER_ID' value='<?php echo $partnerID;?>'>
	<input type='hidden' name='PARTNER_SIGNATURE' value='<?php echo $partner_sig;?>'>
	<input type='hidden' name='PARAMETERSET_VERSION_NO' value='1.0'>
	<input type='hidden' name='SHIPMENT_ID' value='<?php echo $shipmentID;?>'>
	<input type='hidden' name='FUNCTION_TYPE' value='1'>
	<input type='hidden' name='REQUEST_TIMESTAMP' value='<?php echo $RequestTimestamp;?>'>
	<input type='hidden' name='KEY_PHASE' value='<?php echo $keyPhase;?>'>
	<input type='hidden' name='PARTNER_USER_ID' value='<?php echo $partnerUserId;?>'>
	<input type='hidden' name='USER_EMAIL' value='<?php echo $userEmail;?>'>
	<input type='hidden' name='ADDR_SEND_SALUTATION' value='<?php echo $olp->lpSalutation;?>'>
	<input type='hidden' name='ADDR_SEND_FIRST_NAME' value='<?php echo $olp->lpFirstname;?>'>
	<input type='hidden' name='ADDR_SEND_LAST_NAME' value='<?php echo $olp->lpLastname ;?>'>
	<input type='hidden' name='ADDR_SEND_CORP_NAME' value='<?php echo $olp->lpFirmname;?>'>
	<input type='hidden' name='ADDR_SEND_STREET' value='<?php echo $olp->lpStreet;?>'>
	<input type='hidden' name='ADDR_SEND_STREET_ADD' value='<?php echo $olp->lpAddress;?>'>
	<input type='hidden' name='ADDR_SEND_ZIP' value='<?php echo $olp->lpZipCode;?>'>
	<input type='hidden' name='ADDR_SEND_CITY' value='<?php echo $olp->lpCity;?>'>
	<input type='hidden' name='ADDR_SEND_COUNTRY' value='<?php echo $olp->lpCountry;?>'>
	<input type='hidden' name='ADDR_SEND_TELNR' value='<?php echo $olp->lpPhone;?>'>
	<input type='hidden' name='ADDR_RECV_SALUTATION' value='<?php echo $oOrder->ordTitle;?>'>
	<input type='hidden' name='ADDR_RECV_FIRST_NAME' value='<?php echo $oOrder->ordFirstName;?>'>
	<input type='hidden' name='ADDR_RECV_LAST_NAME' value='<?php echo $oOrder->ordLastName;?>'>
	<input type='hidden' name='ADDR_RECV_CORP_NAME' value='<?php echo $oOrder->ordFirmname;?>'>
	<input type='hidden' name='ADDR_RECV_STREET' value='<?php echo $oOrder->ordStreet;?>'>
	<input type='hidden' name='ADDR_RECV_STREET_ADD' value='<?php echo $oOrder->ordStreet2;?>'>
	<input type='hidden' name='ADDR_RECV_ZIP' value='<?php echo $oOrder->ordZipCode;?>'>
	<input type='hidden' name='ADDR_RECV_CITY' value='<?php echo $oOrder->ordCity;?>'>
	<input type='hidden' name='ADDR_RECV_COUNTRY' value='<?php echo $oOrder->ordCountry;?>'>
	<input type='hidden' name='ADDR_RECV_TELNR' value='<?php echo $oOrder->ordPhone;?>'>
</div>
</DIV>

<?php
require_once("../include/page.footer.php");
?>
</form>
</body>
</html>
