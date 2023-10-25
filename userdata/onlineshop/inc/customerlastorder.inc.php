<?php
$lohtml = '';
$lorder = $this->gs_file_get_contents('template/lastorders.html');
$aLOTags = $this->get_tags_ret($lorder);
$lorder = $this->parse_texts($aLOTags,$lorder);
$loitem = $this->gs_file_get_contents('template/lastorderitems.html');
$aLOITags = $this->get_tags_ret($loitem);
$loitem = $this->parse_texts($aLOITags,$loitem);
$items = '';
$retour = $this->gs_file_get_contents('template/retourlink.html');
$cid = $_SESSION['login']['cusIdNo'];
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
$showvat = ($this->get_setting('cbShowVAT_Checked') == 'True') ? 1 : 0;
$co = new shoplog();
$orderList = $co->getOrderByCustom($cid);
$codbh = $this->db_connect();
$qrySQL = "SELECT COUNT(*) FROM ".$this->dbtoken."settings WHERE setIdNo = 1 AND (customerreturningticket_adress<>'' AND customerreturningticket_adress IS NOT NULL)";
$qry = mysqli_query($codbh,$qrySQL);
$row = mysqli_fetch_row($qry);  
$ruecksendeadresse_count=$row[0];
//mysqli_free_result($qry);

$num = mysqli_num_rows($orderList);
if($num!=0)
{
	while($order = mysqli_fetch_assoc($orderList))
	{
		$cur_order = $lorder;
		//$cur_order = str_replace('',,$cur_order);
		$cur_order = str_replace('{GSSE_INCL_ODERDATE}',timestamp_mysql2german($order['ordDate']),$cur_order);
		if($order['ordSendCode'] != '')
		{
			$cur_order = str_replace('{GSSE_INCL_SHOWTRAC}','',$cur_order);
			$cur_order = str_replace('{GSSE_INCL_ORDERZIP}',$order['ordZipCode'],$cur_order);
			$cur_order = str_replace('{GSSE_INCL_ORDERCODE}',$order['ordSendCode'],$cur_order);
		}
		else
		{
			$cur_order = str_replace('{GSSE_INCL_SHOWTRAC}','displaynone',$cur_order);
		}
		$cur_order = str_replace('{GSSE_INCL_ORDERDISCOUNT1}',replPtC(sprintf("%01.2f",$order['ordDiscount1Prct'])),$cur_order);
		$cur_order = str_replace('{GSSE_INCL_ORDERDISCOUNTAMOUNT1}',replPtC(sprintf("%01.2f",$order['ordDiscount1Value']))." ".$order['ordCurrency'],$cur_order);
		$cur_order = str_replace('{GSSE_INCL_PAYMENTNAME}',$order['ordPaymentCond'],$cur_order);
		$cur_order = str_replace('{GSSE_INCL_ORDERDISCOUNT2}',replPtC(sprintf("%01.2f",$order['ordDiscount2Prct'])),$cur_order);
		$cur_order = str_replace('{GSSE_INCL_ORDERDISCOUNTAMOUNT2}',replPtC(sprintf("%01.2f",$order['ordDiscount2Value']))." ".$order['ordCurrency'],$cur_order);
		$cur_order = str_replace('{GSSE_INCL_CHARGE}',replPtC(sprintf("%01.2f", $order['ordPaymentCost']))." ".$order['ordCurrency'],$cur_order);
		//$cur_order = str_replace('',,$cur_order);
		$cur_order = str_replace('{GSSE_INCL_SHIPPING}',$order['ordShippingCond'],$cur_order);
		$cur_order = str_replace('{GSSE_INCL_TOTALAMOUNT}',replPtC(sprintf("%01.2f", $order['ordTotalValueAfterDsc2']))." ".$order['ordCurrency'],$cur_order);
		$cur_order = str_replace('{GSSE_INCL_POSTAGE}',replPtC(sprintf("%01.2f", $order['ordShippingCost']))." ".$order['ordCurrency'],$cur_order);
		$cur_order = str_replace('{GSSE_INCL_VAT1PRCT}',replPtC(sprintf("%01.2f",$order['ordVAT1Prct'])),$cur_order);
		$cur_order = str_replace('{GSSE_INCL_VAT1AMNT}',replPtC(sprintf("%01.2f", $order['ordVAT1Value']))." ".$order['ordCurrency'],$cur_order);
		$OrderposList = $co->getOrderposByOrder($order['ordIdNo']);
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
			$cur_item = str_replace('{GSSE_INCL_ITEMNAME}',htmlentities(base64_decode($ItemDesc),ENT_QUOTES),$cur_item);
			$cur_item = str_replace('{GSSE_INCL_ITEMCOUNT}',replPtC(sprintf("%01.2f", $orderpos['ordpQty'])),$cur_item);
			$cur_item = str_replace('{GSSE_INCL_ITEMPRICE}',$this->get_currency($orderpos['ordpPrice'],0,'.'),$cur_item);
			$cur_item = str_replace('{GSSE_INCL_ITEMVATVALUE}',$this->get_currency($orderpos['ordpVATValue'],0,'.'),$cur_item);
			$cur_item = str_replace('{GSSE_INCL_ITEMTOTAL}',$this->get_currency($orderpos['ordpPriceTotal'],0,'.'),$cur_item);
			$cur_retour = '';
			if($ruecksendeadresse_count)
			{
				$cur_retour = $retour;
				//$cur_retour = str_replace('',,$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_CID}',$cid,$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_ORDERID}',$order['ordIdNo'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_ORDERPOSNO}',$orderpos['ordpPosNo'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_ITEMNO}',$orderpos['ordpItemId'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_ITEMDESCR}',$orderpos['ordpItemDesc'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_ITEMPRC}',$orderpos['ordpPrice'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_ITEMQTY}',$orderpos['ordpQty'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_CURRENCY}',$order['ordCurrency'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_SHIPMENT}',$order['ordShippingCond'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_ORDERDATE}',timestamp_mysql2german($order['ordDate']),$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_CUSTNO}',$order['ordCustomerId'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_COMPANY}',$order['ordFirmname'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_PTITLE}',$order['ordTitle'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_FNAME}',$order['ordFirstName'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_LNAME}',$order['ordLastName'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_STREET}',$order['ordStreet'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_STREET2}',$order['ordStreet2'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_ZIP}',$order['ordZipCode'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_CITY}',$order['ordCity'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_CNTRY}',$order['ordCountry'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_PHONE}',$order['ordPhone'],$cur_retour);
				$cur_retour = str_replace('{GSSE_INCL_EMAIL}',$order['ordEMail'],$cur_retour);
			}
			$cur_item = str_replace('{GSSE_INCL_RETOUR}',$cur_retour,$cur_item);
			$items .= $cur_item;
		}
		$cur_order = str_replace('{GSSE_INCL_LASTORTDERITEMS}',$items,$cur_order);
		$lohtml .= $cur_order;
	}
}

$this->content = str_replace('{GSSE_FUNC_CUSTOMERLASTORDER}',$lohtml,$this->content);

?>