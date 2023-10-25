<?php
$oid=$_GET['ordid'];
if(file_exists("dynsb/class/class.shoplog.php"))
{
	if(!in_array("shoplog",get_declared_classes()))
	{
		require_once("dynsb/class/class.shoplog.php");
	}
	require_once("dynsb/include/functions.inc.php");
}
else
{
	echo($this->get_lngtext('LangTagErrorMissingRootPathFile'));
	echo "customerlastorder";
}

$co = new shoplog();
$cid = $_SESSION['login']['cusIdNo'];
$codbh = $this->db_connect();
$qrySQL = "SELECT COUNT(*) FROM ".$this->dbtoken."settings WHERE setIdNo = 1 AND (customerreturningticket_adress<>'' AND customerreturningticket_adress IS NOT NULL)";
$qry = mysqli_query($codbh,$qrySQL);
$row = mysqli_fetch_row($qry);  
$ruecksendeadresse_count=$row[0];
$retour = file_get_contents('template/retourlink.html');
$loitem = file_get_contents('template/lastorderitems.html');
$aLOITags = $this->get_tags_ret($loitem);
$loitem = $this->parse_texts($aLOITags,$loitem);
$subtotal = 0;
$items = '';
if($_SESSION['login']['ok'])
{
	$qrySQL = "SELECT * FROM ".$this->dbtoken."order WHERE ordId = '".$oid."'";
	$qry = mysqli_query($codbh,$qrySQL);
	$row = mysqli_fetch_assoc($qry);
	
	$order = file_get_contents('template/orderviewdetail.html');
	$aOTags = $this->get_tags_ret($order);
	$order = $this->parse_texts($aOTags,$order);
	$order = str_replace('{GSSE_INCL_ORDERID}', $oid, $order);
	$order = str_replace('{GSSE_INCL_ODERDATE}',timestamp_mysql2german($row['ordDate']),$order);
	// Lieferanschrift
	$order = str_replace('{GSSE_INCL_NAME}', $row['ordTitle'].' '.$row['ordFirstName'].' '.$row['ordLastName'], $order);
	$order = str_replace('{GSSE_INCL_STREET}', $row['ordStreet'], $order);
	$order = str_replace('{GSSE_INCL_ZIPCITY}', $row['ordCity'].', '.$row['ordZipCode'], $order);
	// Versandart
	$order = str_replace('{GSSE_INCL_SHIPPING}', $row['ordShippingCond'], $order);
	$order = str_replace('{GSSE_INCL_SHIPMENTCOST}', $this->get_currency($row['ordShippingCost'],0,'.'), $order);
	// Rechnungsanschrift
	$order = str_replace('{GSSE_INCL_DELNAME}', $row['ordDeliverTitle'].' '.$row['ordDeliverFirstName'].' '.$row['ordDeliverLastName'], $order);
	$order = str_replace('{GSSE_INCL_DELSTREET}', $row['ordDeliverStreet'], $order);
	$order = str_replace('{GSSE_INCL_DELZIPCITY}', $row['ordDeliverCity'].' '.$row['ordDeliverZipCode'], $order);
	// Zahlungsart
	$order = str_replace('{GSSE_INCL_PAYMENT}', $row['ordPaymentCond'], $order);
	$order = str_replace('{GSSE_INCL_PAYMENTCOST}', $this->get_currency($row['ordPaymentCost'],0,'.'), $order);
	// MwSt.
	$order = str_replace('{GSSE_INCL_VAT}', $this->get_currency($row['ordVAT1Value'],0,'.'), $order);
	
	// Rechnungsbetrag
	$order = str_replace('{GSSE_INCL_TOTAL}', $this->get_currency($row['ordTotalValueAfterDsc2'],0,'.'), $order);
	
	// Order Positionen
	$OrderposList = $co->getOrderposByOrder($row['ordIdNo']);
	
		while($orderpos = @mysqli_fetch_assoc($OrderposList))
		{
		
			$kl = strpos($orderpos['ordpItemDesc'],"(");
			if(substr($orderpos['ordpItemDesc'],0,$kl-1)!="{Coupon}")
			{
				$spacePos = strpos($orderpos['ordpItemDesc']," ");
				$ItemDesc = substr($orderpos['ordpItemDesc'], $spacePos);
			}
			else
			{
				$ItemDesc = $orderpos['ordpItemDesc'];
			}
			$cur_item = $loitem;
			//$cur_item = str_replace('',,$cur_item);
			$cur_item = str_replace('{GSSE_INCL_ORDERPOS}',$orderpos['ordpPosNo'],$cur_item);
			$cur_item = str_replace('{GSSE_INCL_ITEMNO}',$orderpos['ordpItemId'],$cur_item);
			$cur_item = str_replace('{GSSE_INCL_ITEMNAME}',htmlspecialchars(base64_decode($ItemDesc),ENT_QUOTES),$cur_item);
			$cur_item = str_replace('{GSSE_INCL_ITEMCOUNT}',replPtC(sprintf("%01.2f", $orderpos['ordpQty'])),$cur_item);
			$cur_item = str_replace('{GSSE_INCL_ITEMPRICE}',$this->get_currency($orderpos['ordpPrice'],0,'.'),$cur_item);
			$cur_item = str_replace('{GSSE_INCL_ITEMVATVALUE}',$this->get_currency($orderpos['ordpVATValue'],0,'.'),$cur_item);
			$cur_item = str_replace('{GSSE_INCL_ITEMTOTAL}',$this->get_currency($orderpos['ordpPriceTotal'],0,'.'),$cur_item);
			$subtotal = $subtotal + $orderpos['ordpPrice'];
			$cur_retour = '';
			if($ruecksendeadresse_count)
			{
				$cur_retour = $retour;
				//$cur_retour = str_replace('',,$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_CID}',$cid,$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_ORDERID}',$row['ordIdNo'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_ORDERPOSNO}',$orderpos['ordpPosNo'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_ITEMNO}',$orderpos['ordpItemId'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_ITEMDESCR}',$orderpos['ordpItemDesc'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_ITEMPRC}',$orderpos['ordpPrice'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_ITEMQTY}',$orderpos['ordpQty'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_CURRENCY}',$row['ordCurrency'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_SHIPMENT}',$row['ordShippingCond'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_ORDERDATE}',timestamp_mysql2german($row['ordDate']),$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_CUSTNO}',$row['ordCustomerId'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_COMPANY}',$row['ordFirmname'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_PTITLE}',$row['ordTitle'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_FNAME}',$row['ordFirstName'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_LNAME}',$row['ordLastName'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_STREET}',$row['ordStreet'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_STREET2}',$row['ordStreet2'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_ZIP}',$row['ordZipCode'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_CITY}',$row['ordCity'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_CNTRY}',$row['ordCountry'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_PHONE}',$row['ordPhone'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_EMAIL}',$row['ordEMail'],$cur_retour);
			}
			$cur_item = str_replace('{GSSE_INCL_RETOUR}',$cur_retour,$cur_item);
			$items .= $cur_item;
		}
		// Order Positionen
		$order = str_replace('{GSSE_INCL_LASTORTDERITEMS}', $items, $order);
		$order = str_replace('{GSSE_INCL_SUBTOTAL}', $this->get_currency($subtotal,0,'.'), $order);
	// Endergebniss
	$this->content = str_replace('{GSSE_FUNC_ORDERVIEWDETAIL}', $order, $this->content);
}
else
{
	$this->content = str_replace('{GSSE_FUNC_ORDERVIEWDETAIL}', '', $this->content);
}

?>
