<?php
/******************************************************************************/
/* File: shoporder.detail.php                                                 */
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
  or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");
if(isset($_REQUEST['start']))
{ $start = intval($_REQUEST['start']); }

if(isset($_REQUEST['pk']))
{ $ordIdNo = intval($_REQUEST['pk']); }

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

if (!isset($_SESSION['SESS_languageIdNo']) || strlen(trim($_SESSION['SESS_languageIdNo'])) == 0)
{ die ("<br />error: missing session parameter!<br />"); }
else
{ $SESS_languageIdNo = $_SESSION['SESS_languageIdNo']; }

if(strtolower($act) == "e")
{
  // start database query
  $qrySQL = "SELECT * FROM ".DBToken."order WHERE ordIdNo = '".$ordIdNo."' AND ordChgHistoryFlg <> '0'";
  $qry = @mysqli_query($link, $qrySQL);
  $obj = @mysqli_fetch_object($qry);

  foreach($obj as $key => $value)
  {
    $$key = trim($value);
  }
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
  <title><?php echo L_dynsb_ShopOrderDetails;?></title>
  <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
  <meta content="de" http-equiv="Language">
  <meta name="author" content="GS Software Solutions GmbH">
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
    { location.reload(); }
  }
  //----------------------------------------------------------------------------
  MM_reloadPage(true);

  </script>
</head>
<body>
<form name="frmShoporderDetail" action="shoporder.save.php" method="post">

<?php
require_once("../include/page.header.php");
?>

<div id="PGshoporderdetail">

	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="start" value="<?php echo $start;?>">
	<input type="hidden" name="ordIdNo" value="<?php echo $ordIdNo;?>">
	<input type="hidden" name="act" value="<?php echo $act;?>">


<h1>&nbsp;&#187;&nbsp;<?php echo L_dynsb_ShopOrderDetails;?>&nbsp;&#171;</h1>


  <table class="deb ug">
  <tr>
    <th colspan="2"><?php echo L_dynsb_PersonData?></th>
    <th align="left"><?php echo L_dynsb_DifferentDeliverAddress;?></th>
  </tr>
  <tr>
    <td align="right" style="width:140px;"><?php echo L_dynsb_OrderNo;?>:</td>
    <td style="width:220px; font-weight:bold;"><?php echo $ordId;?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_OrderDate;?>:</td>
    <td style="font-weight:bold;"><?php echo timestamp_mysql2german($ordDate);?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_CustomerNumber;?>:</td>
    <td style="font-weight:bold;"><?php echo $ordCustomerId;?></td>
    <td>&nbsp;</td>
  </tr>

  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Firm;?>:</td>
    <td><?php echo $ordFirmname;?></td>
    <td><?php echo $ordDeliverFirmname;?></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Title;?>:</td>
    <td><?php echo $ordTitle;?></td>
    <td><?php echo $ordDeliverTitle;?></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Name;?>:</td>
    <td style="font-weight:bold;"><?php echo $ordLastName;?>, <?php echo $ordFirstName;?></td>
    <td>
      <?php echo $ordDeliverLastName;?>
      <?php if (($ordDeliverLastName != "") && ($ordDeliverFirstName != ""))
        {
          echo ",";
        }
        ?>
      <?php echo $ordDeliverFirstName;?>
    </td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Street;?>:</td>
    <td style="font-weight:bold;"><?php echo $ordStreet;?></td>
    <td><?php echo $ordDeliverStreet;?></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Addition;?>:</td>
    <td><?php echo $ordStreet2;?></td>
    <td><?php echo $ordDeliverStreet2;?></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Zipcode;?> / <?php echo L_dynsb_City;?>:</td>
    <td style="font-weight:bold;"><?php echo $ordZipCode;?> <?php echo $ordCity;?></td>
    <td><?php echo $ordDeliverZipCode;?> <?php echo $ordDeliverCity;?></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Country;?>:</td>
    <td><?php echo $ordCountry;?></td>
    <td><?php echo $ordDeliverCountry;?></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Birthdate;?>:</td>
    <td><?php echo $ordBirthdate;?></td>
    <td>&nbsp;</td>
  </tr>   
  <tr>
    <td align="right"><?php echo L_dynsb_Email;?>:</td>
    <td><?php echo $ordEMail;?></td>
    <td>&nbsp;</td>
  </tr>
  <?php if(isset($ordIP) && $ordIP != '') { ?>
  <tr>
    <td align="right"><?php echo L_dynsb_IP;?>:</td>
    <td><?php echo $ordIP?></td>
    <td>&nbsp;</td>
  </tr>

    <tr>
    <td align="right"><?php echo L_dynsb_UserKommentar;?>:</td>
    <td><?php echo $AnmerkungenBestellung;?></td>
    <td>&nbsp;</td>
  </tr>   
  <?php } ?>
  </table>


	<h2><?php echo L_dynsb_billingData?></h2>
  <table class="debu g">
	<tr>
    <td align="right" style="width:140px;"><?php echo L_dynsb_Total;?>:</td>
    <td style="font-weight:bold;"><?php echo replPtC(sprintf("%01.2f", $ordTotalValue))." ".$ordCurrency;?></td>
  </tr>
  <tr>
    <td align="right" style="width:140px;"><?php echo L_dynsb_InvoiceNo;?>:</td>
    <td><input name="ordInvoiceNumber" value="<?php echo $obj->ordInvoiceNumber;?>" style="text-align:right;" size="12"/></td>
  </tr>
  <tr>
    <td align="right" style="width:140px;"><?php echo L_dynsb_InvoiceDate;?>:</td>
    <td><input name="ordInvoiceDate" value="<?php if($obj->ordInvoiceDate != '0000-00-00'){echo date_mysql2german($obj->ordInvoiceDate);}?>" style="text-align:right;" size="12"/></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_1Discount." (".replPtC(sprintf("%01.2f",$ordDiscount1Prct))."%):";?></td>
    <td><?php echo replPtC(sprintf("%01.2f",$ordDiscount1Value))." ".$ordCurrency;?></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_2Discount." (".replPtC(sprintf("%01.2f",$ordDiscount2Prct))."%):";?></td>
    <td><?php echo replPtC(sprintf("%01.2f",$ordDiscount2Value))." ".$ordCurrency;?></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_DispatchType;?>:</td>
    <td><?php echo $ordShippingCond;?></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_ShippingAndHandling;?>:</td>
    <td><?php echo replPtC(sprintf("%01.2f", $ordShippingCost))." ".$ordCurrency;?></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_MethodOfPayment;?>:</td>
    <td><?php echo $ordPaymentCond;?></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_CostsOfPayment;?>:</td>
    <td><?php 
            if(strpos($ordPaymentCond,'%') === false){
                // nichts
            } else {
                $ordPaymentCost = $ordPaymentCost/100;
            }
            echo replPtC(sprintf("%01.2f", $ordPaymentCost))." ".$ordCurrency;
        ?></td>
  </tr>

  <!--
  <tr>
    <td colspan="2">&nbsp;</td>
    <td>&nbsp;</td>
    <td colspan="2"><?php echo L_dynsb_TotalAfter1Discount;?>:</td>
    <td colspan="3"><?php echo replPtC(sprintf("%01.2f", $ordTotalValueAfterDsc1))." ".$ordCurrency;?></td>
  </tr>
  -->
  <tr>
    <td align="right"><?php echo L_dynsb_Total2;?>:</td>
    <td style="font-weight:bold;"><?php echo replPtC(sprintf("%01.2f", $ordTotalValueAfterDsc2))." ".$ordCurrency;?></td>
  </tr>

  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_1VAT." (".replPtC(sprintf("%01.2f",$ordVAT1Prct))."%):";?></td>
    <td><?php echo replPtC(sprintf("%01.2f", $ordVAT1Value))." ".$ordCurrency;?></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_2VAT." (".replPtC(sprintf("%01.2f",$ordVAT2Prct))."%):";?></td>
    <td><?php echo replPtC(sprintf("%01.2f", $ordVAT2Value))." ".$ordCurrency;?></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_3VAT." (".replPtC(sprintf("%01.2f",$ordVAT3Prct))."%):";?></td>
    <td><?php echo replPtC(sprintf("%01.2f", $ordVAT3Value))." ".$ordCurrency;?></td>
  </tr>
  <!--
  <tr>
    <td colspan="2">&nbsp;</td>
    <td>&nbsp;</td>
    <td colspan="2"><?php echo L_dynsb_TotalAfter1Discount;?>:</td>
    <td colspan="3"><?php echo replPtC(sprintf("%01.2f", $ordTotalValueAfterDsc1))." ".$ordCurrency;?></td>
  </tr>
  -->
  </table>

<h2><?php echo L_dynsb_invoiceLineItems;?></h2>
 <table class="deb ug">
  <tr>
    <th><?php echo L_dynsb_Pos;?></th>
    <th><?php echo L_dynsb_ArticleNo;?></th>
    <th><?php echo L_dynsb_Description;?></th>
    <th><?php echo L_dynsb_Quantity;?></th>
    <th><?php echo L_dynsb_Price;?></th>
    <th><?php echo L_dynsb_VAT;?></th>
    <th><?php echo L_dynsb_VATvalue;?></th>
    <th><?php echo L_dynsb_Total3;?></th>
  </tr>

  <?php
    $SQL = "SELECT * FROM ".DBToken."orderpos WHERE ordpOrdIdNo = '".$ordIdNo."'";
    $qry = @mysqli_query($link, $SQL);
    while($obj = @mysqli_fetch_object($qry))
    {
  ?>
    <tr id='d<?php echo $obj->ordpPosNo;?>' onMouseOver="javascript:changeRowColorHilightWithoutChkbox('d<?php echo $obj->ordpPosNo;?>');" onMouseout="javascript:changeRowColorNormalWithoutChkbox('d<?php echo $obj->ordpPosNo;?>');">
      <td align="center"><?php echo $obj->ordpPosNo;?></td>
      <td><?php echo $obj->ordpItemId;?></td>
	  <!--A TS 27.12.2012 Sonderzeichen -->
      <td><?php echo htmlspecialchars(base64_decode($obj->ordpItemDesc),ENT_QUOTES);?></td>
      <td align="right"><?php echo replPtC(sprintf("%01.2f", $obj->ordpQty));?></td>
      <td align="right"><?php echo replPtC(sprintf("%01.2f", $obj->ordpPrice));?></td>
      <td align="right"><?php echo replPtC(sprintf("%01.2f", $obj->ordpVATPrct))."%";?></td>
      <td align="right"><?php echo replPtC(sprintf("%01.2f", $obj->ordpVATValue));?></td>
      <td align="right"><?php echo replPtC(sprintf("%01.2f", $obj->ordpPriceTotal));?></td>
    </tr>
  <?php
    } // end of while
  ?>
  </table>


<!-- navigation // -->
<div class="footer">
	<input type="button" class="button" onclick="javascript:print()" value="<?php echo L_dynsb_Print;?>">
	<input type="button" class="button" onclick="javascript:location.href='shoporder.search.php?start=<?php echo $start."&lang=".$lang;?>';" name="btn_save" value="<?php echo L_dynsb_Cancel;?>">
    <input type="submit" class="button" value="Speichern">
</div>
</div>

<?php
require_once("../include/page.footer.php");
?>
</form>
</body>
</html>
