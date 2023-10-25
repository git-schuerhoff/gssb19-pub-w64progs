<?php
/******************************************************************************/
/* File: mod.availability.categories.php                                      */
/******************************************************************************/

require("../../include/login.check.inc.php");
require_once("../../include/functions.inc.php");
require("../../../conf/db.const.inc.php");

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

foreach($_REQUEST as $key => $value)
{
    $$key = trim($value);
}

$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";

function date_to_mysql_date($date) {
	$date = explode(" ", $date);
	$t_date = $date[0];
	$t_time = $date[1];
	
	$t_date = explode(".", $t_date); 
	
	$ret = $t_date[2]."-".$t_date[1]."-".$t_date[0];
	if($t_time != '') $ret = $ret." ".$t_time;
	return $ret; 	
}
// Definieren der zu lesenden Tabelle
$sourcetable= DBToken."aktivities";
if (isset($_REQUEST['new_akt']))
{
	$cust_from = $_REQUEST['cust_from'];
	$cust_to = $_REQUEST['cust_to'];
	$cust_group = $_REQUEST['cust_group'];
	$act_key = $_REQUEST['akt_key'];
	$act_date = $_REQUEST['akt_date'];
	$act_desc = $_REQUEST['akt_desc'];

	$act_date = date_to_mysql_date($act_date);
	#echo $act_date;
	$cust_arr = array();
	if(is_numeric($cust_from) && is_numeric($cust_to)) {
		if($cust_from < $cust_to) {
			for($i=$cust_from;$i<=$cust_to;$i++) {
				if(isCustExists($i)) {
					$cust_arr[] = $i;
				}
			}	
		} else {
			if(isCustExists($i)) {
				$cust_arr[] = $cust_from;
			}
			if(isCustExists($i)) {
				$cust_arr[] = $cust_to;
			}
		}	
	}
	if(!empty($cust_group)) {
		$query = "SELECT custId FROM ".DBToken."cust_to_group WHERE cgId = '".$cust_group."'";
		$ret = @mysqli_query($link,$query);
		$arr_group = array();
		while($obj = mysqli_fetch_object($ret)) {
			$arr_group[] = $obj->custId;
		}
		$cust_arr = array_merge($cust_arr, $arr_group);
	}
	$cust_arr = array_unique($cust_arr);
	foreach($cust_arr as $cust) {
		$query = "INSERT INTO ".DBToken."aktivities(custId, mkKey, aktText, aktDate) 
		VALUES ('".$cust."', '".$akt_key."', '".$act_desc."', '".$act_date."')"; 
		#echo $query;
		$result = mysqli_query($link,$query) or die("Einf�ehlgeschlagen: " . mysqli_error($link));
	}
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_Marketing;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
	 <script type="text/javascript"src="../../js/gslib.php"></script>
	 <script type="text/javascript" src="../../js/calendar.js"></script>
	<script type="text/javascript" src="../../js/calendar-<?php echo $strcal;?>.js"></script>
	<script type="text/javascript" src="../../js/calendar-setup.js"></script>
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
    </script>
</head>
<body>
<?php
require_once("../../include/page.header.php");
?>
<div id="PGavailabilitycategories">
<h1>&#187;&nbsp;<?php echo L_dynsb_Marketing;?>&nbsp;&#171;</h1>

<?php

if(isset($_REQUEST['show_new']))
{
?>

<form name="neuform" action="mod.marketing.aktivities.php" method="post">
<input type="hidden" name="lang" value="<?php echo $lang;?>">
<input type="hidden" name="new_akt"> 	
<table style="width: 400px;"> 	
	<tr>
		<td><?php echo L_dynsb_AktivitiesCustFromTo; ?></td>
  		<td><input type="text" id="cust_from" name="cust_from">
  		&nbsp;<input type="text" id="cust_to" name="cust_to"></td>
  	</tr>
  	
  	<tr>
		<td><?php echo L_dynsb_AktivitiesCustGroup;?></td>
  		<td><select name="cust_group">
  		<option value="" selected>&nbsp;</option>
  		<?php
  			$SQL = "SELECT * FROM ".DBToken."custgroup";
			$qry = @mysqli_query($link,$SQL);
			while($obj = @mysqli_fetch_object($qry)) { 
		?>
		<option value="<?php echo $obj->cgId;?>"><?php echo $obj->cgName;?></option>		
		<?php } ?>
  		</select></td>
  	</tr>
  	<tr>
		<td><?php echo L_dynsb_MarkKey;?></td>
  		<td><select name="akt_key">
  		<?php
  			$SQL = "SELECT * FROM ".DBToken."marketingkey WHERE mkType in (1, 2)";
			$qry = @mysqli_query($link,$SQL);
			while($obj = @mysqli_fetch_object($qry)) { 
		?>
		<option value="<?php echo $obj->mkKey;?>"><?php echo $obj->mkDesc;?></option>		
		<?php } ?>
  		</select></td>
  	</tr>
  	<tr>
  		<td><?php echo L_dynsb_Date;?></td>
  		<td>
  			<input type="text" maxlength="32" value="<?php echo date("d.m.Y H:i:s", time());?>" name="akt_date" id="akt_date" readonly>&nbsp;
    		<img src="../../image/calendar.gif" id="dateTrigger" style="cursor: pointer" alt="<?php echo L_dynsb_Calendar;?>" title="<?php echo L_dynsb_Calendar;?>">
	    	<script language="JavaScript" type="text/javascript">
		    	Calendar.setup(
		                {
		            inputField	 :    "akt_date",
		            ifFormat     :    "%d.%m.%Y",
					button       :    "dateTrigger",
		            showsTime	 :    false,
		            singleClick	 :    true,
		            firstDay	 :	  1,
		            align        :    "Bl"
		        });
			</script>
  		</td>
  	</tr>
  	<tr>
  		<td><?php echo L_dynsb_AktivitiesRem;?></td>
  		<td><textarea name="akt_desc" rows="5" cols="40"></textarea></td>
  	</tr>
 </table>

    <p>
		<input type="button" class="button" onclick="javascript:submitForm('neuform');" tabindex=29 name="btn_save" value="<?php echo L_dynsb_Save;?>">&nbsp;
      	<input type="button" class="button" onclick="javascript:self.location.href='mod.marketing.custgroup.php?lang=<?php echo $lang;?>';" tabindex=30 value="<?php echo L_dynsb_Cancel;?>">
  	</p>
</form>

<?php
}
?>

<?php
if(isset($_REQUEST['view']))
{
	$qryAkt = "SELECT cusLastName, cusFirstName, m.mkKey, m.mkDesc, aktText, 
			   ordIdNo, aktKey, m1.mkDesc AS aktKeyDesc, DATE_FORMAT(aktDate, '%d.%m.%Y %H:%i:%s') AS aktDate FROM ".DBToken."aktivities a
			   LEFT JOIN ".DBToken."marketingkey m ON m.mkKey = a.mkKey
			   LEFT JOIN ".DBToken."customer c ON c.cusIdNo = a.custId
			   LEFT JOIN ".DBToken."marketingkey m1 ON m1.mkKey = a.aktKey
			   WHERE aktId = '".$_REQUEST['view']."'";
	
	#echo $qryAkt;
	
	$retAkt = @mysqli_query($link,$qryAkt);
	$obj = @mysqli_fetch_object($retAkt);
	
	?>
	<h1>&nbsp;&#187;&nbsp;<?php echo L_dynsb_AktivitiesDetails;?>&nbsp;&#171;</h1>
	<table>
	<tr>
		<td><?php echo L_dynsb_Name;?>:</td>
    	<td><?php echo $obj->cusLastName;?>, <?php echo $obj->cusFirstName;?></td>
	</tr>
	<tr>
		<td><?php echo L_dynsb_MarkKey;?></td>
		<td><?php echo $obj->mkKey." - ".$obj->mkDesc; ?></td>
	</tr>
	<tr>
		<td><?php echo L_dynsb_AktivitiesRem;?></td>
  		<td><?php echo nl2br($obj->aktText); ?></td>
	</tr>
	<tr>
		<td><?php echo L_dynsb_Date;?></td>
		<td><?php echo $obj->aktDate; ?></td>
	</tr>
	<?php if(isset($obj->aktKey)) { ?>
	<tr>
		<td><?php echo L_dynsb_AktivitiesOrdKey;?></td>
		<td><?php echo $obj->aktKey." - ".$obj->aktKeyDesc; ?></td>
	</tr>	
	<?php } ?>
	</table>
	<?php
	if(isset($obj->ordIdNo) && ($obj->ordIdNo != '0')) {
		$qrySQL = "SELECT * FROM ".DBToken."order WHERE ordIdNo = '".$obj->ordIdNo."' AND ordChgHistoryFlg <> '0'";
  		$qry = @mysqli_query($link,$qrySQL);
  		$obj = @mysqli_fetch_object($qry);
  		
		foreach($obj as $key => $value)
  		{
    		$$key = trim($value);
  		}
  		
	?>

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
	    <td align="right"><?php echo L_dynsb_CustomerNumber;?>:</td>
	    <td style="font-weight:bold;"><?php echo $ordCusIdNo;?></td>
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
    <td align="right"><?php echo L_dynsb_Email;?>:</td>
    <td><?php echo $ordEMail;?></td>
    <td>&nbsp;</td>
  </tr>
  </table>


	<h2><?php echo L_dynsb_billingData?></h2>
  <table class="debu g">
	<tr>
    <td align="right" style="width:140px;"><?php echo L_dynsb_Total;?>:</td>
    <td style="font-weight:bold;"><?php echo replPtC(sprintf("%01.2f", $ordTotalValue))." ".$ordCurrency;?></td>
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
    <td><?php echo replPtC(sprintf("%01.2f", $ordPaymentCost))." ".$ordCurrency;?></td>
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
    $qry = @mysqli_query($link,$SQL);
    while($obj = @mysqli_fetch_object($qry))
    {
  ?>
    <tr id='d<?php echo $obj->ordpPosNo;?>' onMouseOver="javascript:changeRowColorHilightWithoutChkbox('d<?php echo $obj->ordpPosNo;?>');" onMouseout="javascript:changeRowColorNormalWithoutChkbox('d<?php echo $obj->ordpPosNo;?>');">
      <td align="center"><?php echo $obj->ordpPosNo;?></td>
      <td><?php echo $obj->ordpItemId;?></td>
      <td><?php echo base64_decode($obj->ordpItemDesc);?></td>
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

	<?php } } ?>

<h2>Filter</h2>
<form name="filterform" action="mod.marketing.aktivities.php" method="post">
<div style="height:1%;">
	<div class="filter"><?php echo L_dynsb_CustomerNo;?><br />
	<input type="text" name="f_cust_from" maxlength="16" value="<?php echo $f_cust_from;?>">
	<input type="text" name="f_cust_to" maxlength="16" value="<?php echo $f_cust_to;?>">
	</div>
	<div class="filter"><?php echo L_dynsb_AktivitiesCustGroup;?><br />
	<select name="f_group">
  		<option value="" selected>&nbsp;</option>
  		<?php
  			$SQL = "SELECT * FROM ".DBToken."custgroup";
			$qry = @mysqli_query($link,$SQL);
			while($obj = @mysqli_fetch_object($qry)) { 
		?>
		<option value="<?php echo $obj->cgId;?>" <?php if($f_group == $obj->cgId) echo "selected"; ?>><?php echo $obj->cgName;?></option>		
		<?php } ?>
  	</select>
	</div>
	<div class="filter"><?php echo L_dynsb_MarkKey;?><br />
	<select name="f_key">
		<option value="" selected>&nbsp;</option>
  		<?php
  			$SQL = "SELECT * FROM ".DBToken."marketingkey";
			$qry = @mysqli_query($link,$SQL);
			while($obj = @mysqli_fetch_object($qry)) { 
		?>
		<option value="<?php echo $obj->mkKey;?>" <?php if($f_key == $obj->mkKey) echo "selected"; ?>><?php echo $obj->mkDesc;?></option>		
		<?php } ?>
  		</select>
  	</div>
  	<div class="filter"><?php echo L_dynsb_Date;?><br />
  		<?php if($f_date_from == '') 
  				$f_date_from = date("d.m.Y", time() - (86400));
  		   if($f_date_to == '')
  		   		$f_date_to = date("d.m.Y", time() + (86400));
  		?>
  		<input type="text" maxlength="32" value="<?php echo $f_date_from;?>" name="f_date_from" id="f_date_from" readonly>&nbsp;
    	<img src="../../image/calendar.gif" id="date_trigger_from" style="cursor: pointer" alt="<?php echo L_dynsb_Calendar;?>" title="<?php echo L_dynsb_Calendar;?>">&nbsp;
    	<input type="text" maxlength="32" value="<?php echo $f_date_to;?>" name="f_date_to" id="f_date_to" readonly>&nbsp;
    	<img src="../../image/calendar.gif" id="date_trigger_to" style="cursor: pointer" alt="<?php echo L_dynsb_Calendar;?>" title="<?php echo L_dynsb_Calendar;?>">
	    <script language="JavaScript" type="text/javascript">
		    	Calendar.setup(
		                {
		            inputField	 :    "f_date_from",
		            ifFormat     :    "%d.%m.%Y",
					button       :    "date_trigger_from",
		            showsTime	 :    false,
		            singleClick	 :    true,
		            firstDay	 :	  1,
		            align        :    "Bl"
		        });
		    	Calendar.setup(
		                {
		            inputField	 :    "f_date_to",
		            ifFormat     :    "%d.%m.%Y",
					button       :    "date_trigger_to",
		            showsTime	 :    false,
		            singleClick	 :    true,
		            firstDay	 :	  1,
		            align        :    "Bl"
		        });
		</script>		
  	</div>
  	<p class="clear"><input type="submit" value="<?php echo L_dynsb_StartSearch;?>"></p>
</div>
</form>
<table>
<tr>
	<th>&nbsp;</th>
	<th><?php echo L_dynsb_CustomerNo;?></th>
	<th><?php echo L_dynsb_MarkKey;?></th>
  	<th><?php echo L_dynsb_AktivitiesRem;?></th>
  	<th><?php echo L_dynsb_Date;?></th>
</tr>

<?php
$SQL = "SELECT aktId, custId, mkKey, aktText, DATE_FORMAT(aktDate, '%d.%m.%Y %H:%i:%s') AS aktDate FROM $sourcetable WHERE 1=1";
$cust_arr = array();
$cust_from_ok = !empty($f_cust_from) && is_numeric($f_cust_from);
$cust_to_ok = !empty($f_cust_to) && is_numeric($f_cust_to);

if($cust_from_ok) {
	$cust_arr[] = $f_cust_from;	
}

if($cust_to_ok) {
	$cust_arr[] = $f_cust_to;
}

if($cust_from_ok && $cust_to_ok) {
	for($i=$f_cust_from + 1;$i < $f_cust_to;$i++) {
		$cust_arr[] = $i;
	}		
}

if(!empty($f_group)) {
	#echo "!empty(f_group)";
	$query = "SELECT custId FROM ".DBToken."cust_to_group WHERE cgId = '".$f_group."'";
	#echo $query;
	$ret = mysqli_query($link,$query);
	$arr_group = array();
	while($obj = mysqli_fetch_object($ret)) {
		$arr_group[] = $obj->custId;
	}
	#print_r($arr_group);
	$cust_arr = array_merge($cust_arr, $arr_group);
	
}

if(!empty($cust_arr)) {
	$cust_arr = array_unique($cust_arr);
	$SQL .= " AND custId IN (".implode(',', $cust_arr).")";
}

if($f_key != '') {
	$SQL .= " AND mkKey = '".$f_key."'";
}

if($f_date_from != '') {
	$SQL .= " AND aktDate >= '".date_to_mysql_date($f_date_from)."'";
} 
else {
	$SQL .= " AND aktDate >= '".date("Y-m-d", time() - (86400))."'";	
}

if($f_date_to != '') {
	$SQL .= " AND aktDate <= '".date_to_mysql_date($f_date_to)."'";
} 
else {
	$SQL .= " AND aktDate <= '".date("Y-m-d", time() + (86400))."'";	
}

#echo $SQL;
$qry = mysqli_query($link,$SQL);
while($obj = @mysqli_fetch_object($qry))
{
  	$akt_text = $obj->aktText;
	if(strlen($akt_text) > 75) {
  		$akt_text = substr($akt_text, 0, 75)."...";
  	}
  	
	?>
	<tr>
		<td>
	 		<?php $link = "f_cust_from=$f_cust_from&f_cust_to=$f_cust_to&f_group=$f_group&f_key=$f_key&f_date_from=$f_date_from&f_date_to=$f_date_to&view=$obj->aktId&lang=$lang"; ?>
	 		<a href="javascript:location.href='mod.marketing.aktivities.php?<?php echo $link;?>'" name="btn_next" value="<?php echo L_dynsb_Edit;?>">
	    	<img src="../../image/view.gif" alt="">
	 		</a>
		</td>
  		<td style="font-weight: bold; " align="center"><?php echo $obj->custId;?></td>
  		<td style="font-weight: bold; " align="center"><?php echo $obj->mkKey;?></td>
  		<td style="font-weight: bold; " align="center"><?php echo $akt_text;?></td>
  		<td style="font-weight: bold; " align="center"><?php echo $obj->aktDate;?></td>
	</tr>
<?php
 }
?>
</table>

<div class="footer">
	<input type="button" class="button" onclick="javascript:location.href='mod.marketing.aktivities.php?show_new=1&lang=<?php echo $lang;?>'" value="<?php echo L_dynsb_New;?>">
</div>


</div>

<?php
require_once("../../include/page.footer.php");
?>
</body>
</html>

