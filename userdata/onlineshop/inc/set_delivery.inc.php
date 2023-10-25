<?php
session_start();
if(isset($_SESSION['delivery']))
{
	unset($_SESSION['delivery']);
}
chdir("../");
include_once("inc/class.shopengine.php");
$delse = new gs_shopengine();
$deldbh = $delse->db_connect();
$charge = 0;
$chargepercent = 0;
$usecashdiscount = 'N';
$cashdiscount = 0;
$cashdiscountpercent = 0;

$delsql = "SELECT Charge, ChargePercent, UseCashDiscount, CashDiscount, CashDiscountPercent, " .
			 "(SELECT InternalName FROM " . $delse->dbtoken . "paymentinternalnames WHERE " . $delse->dbtoken . "paymentinternalnames.SortId = '" . $_GET['paymID'] . "' LIMIT 1) AS internalname " .
			 "FROM " . $delse->dbtoken . "paymentcountry WHERE " .
			 "AddressArea = '" . $_GET['areaID'] . "' AND " .
			 "SortId = '" . $_GET['paymID'] . "' AND " .
			 "CountryId = '" . $delse->cntID . "' LIMIT 1";
$delerg = mysqli_query($deldbh,$delsql);
if(mysqli_errno($deldbh) == 0) {
	if(mysqli_num_rows($delerg) > 0) {
		$dl = mysqli_fetch_assoc($delerg);
		$charge = $dl['Charge'];
		$chargepercent = $dl['ChargePercent'];
		$usecashdiscount = $dl['UseCashDiscount'];
		$cashdiscount = $dl['CashDiscount'];
		$cashdiscountpercent = $dl['CashDiscountPercent'];
		$pinternalname = $dl['internalname'];
	}
	mysqli_free_result($delerg);
}

//TS 11.11.2016: Versandkosten ermitteln
$totalnetto = 0;
$totalbrutto = 0;
$taxtotal = 0;
$delse->getBasketTotals($totalnetto,$totalbrutto,$taxtotal);
//SortId,CountryId,AddressArea
//ShippingCost,FromInvoiceAmount1,MaxShippingCharge1,FromInvoiceAmount2,MaxShippingCharge2,FromInvoiceAmount3,MaxShippingCharge3
$shpsql = "SELECT ShippingCost,FromInvoiceAmount1,MaxShippingCharge1,FromInvoiceAmount2,MaxShippingCharge2,FromInvoiceAmount3,MaxShippingCharge3 ".
			 "FROM " . $delse->dbtoken . "deliveryarea WHERE SortId=".$_GET['shipID']." AND CountryId='".$delse->cntID."' AND AddressArea=".$_GET['areaID'];
$shperg = mysqli_query($deldbh,$shpsql);
if(mysqli_errno($deldbh) == 0) {
	if(mysqli_num_rows($shperg) > 0) {
		$sh = mysqli_fetch_object($shperg);
		$shipment = $sh->ShippingCost;
		if(($sh->FromInvoiceAmount1 > 0) AND ($totalbrutto >= $sh->FromInvoiceAmount1)) {
			$shipment = $sh->MaxShippingCharge1;
		}
		if(($sh->FromInvoiceAmount2 > 0) AND ($totalbrutto >= $sh->FromInvoiceAmount2)) {
			$shipment = $sh->MaxShippingCharge2;
		}
		if(($sh->FromInvoiceAmount3 > 0) AND ($totalbrutto >= $sh->FromInvoiceAmount3)) {
			$shipment = $sh->MaxShippingCharge3;
		}
	}
	mysqli_free_result($delerg);
}

$_SESSION['delivery'] = array(
										"area" => array(
															"id" => $_GET['areaID'],
															"name" => $_GET['areaName']) ,
										"ship" => array(
															"id" => $_GET['shipID'],
															"name" => $_GET['shipName'],
															"charge" => $shipment) ,
										"paym" => array(
															 "id" => $_GET['paymID'],
															 "name" => $_GET['paymName'],
															 "internalname" => $pinternalname, 
															 "charge" => $charge,
															 "chargepercent" => $chargepercent,
															 "usecashdiscount" => $usecashdiscount,
															 "cashdiscount" => $cashdiscount,
															 "cashdiscountpercent" => $cashdiscountpercent)
										);
?>