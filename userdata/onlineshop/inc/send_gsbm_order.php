<?php
session_start();
//$_POST = $_GET;

chdir("../");
include_once("class.shopengine.php");
$ose = new gs_shopengine();

$gsbmurl = base64_encode($ose->get_setting('edGSBMUrl_Text'));
$gsbmdb = $ose->get_setting('edGSBMDBName_Text');
$gsbmusr = $ose->get_setting('edGSBMUserName_Text');
$gsbmpwd = base64_encode($ose->get_setting('edGSBMPassword_Text'));
//$shopurl = $ose->get_setting('edAbsoluteShopPath_Text');
$shopurl = $ose->shopurl;

$ddinvtext = $ose->db_text_ret('contentpool|Text|Name|DirectDebitInvoiceText');

//$ddinvtext = str_replace('{GSSE_INCL_INVTOTAL}',$_POST['_LANGTAGFNFIELDTOTALAMOUNT_'],$ddinvtext);
$ddinvtext = str_replace('{GSSE_INCL_CURRENCY}',$_POST['currency'],$ddinvtext);
$ddinvtext = str_replace('{GSSE_INCL_CREDITORID}',$ose->get_setting('edCreditorIdentifier_Text'),$ddinvtext);
$ddinvtext = str_replace('{GSSE_INCL_IBAN}',$ose->hidebankinfo($_POST['_LANGTAGFNACCOUNTNUMBER_']),$ddinvtext);
$ddinvtext = str_replace('{GSSE_INCL_BANKNAME}',$_POST['_LANGTAGFNFINANCIALINSTITUTION_'],$ddinvtext);
$ddinvtext = str_replace('{GSSE_INCL_BIC}',$ose->hidebankinfo($_POST['_LANGTAGFNBANKCODENUMBER_']),$ddinvtext);

//die($ddinvtext);

/*echo "<pre>";
print_r($_SESSION['basket']);
echo("</pre>");*/
$ares = array();
$ares['errno'] = 0;
$ares['error'] = '';
$ares['result'] = array();
$iOrd = 1;
//$iCount = $_POST['qtyofpos'];
$iCount = count($_SESSION['basket']);

$cParam = "email=" . $_POST['email'] .
			 "&dear=" . $_POST['dear'] .
			 "&mrormrs=" . $_POST['_LANGTAGFNFIELDFORMTOADDRESS_'] . 
			 "&firm=" . $_POST['_LANGTAGFNFIELDCOMPANY_'] . 
			 "&name=" . $_POST['_LANGTAGFNFIELDFIRSTNAME_'] . ' ' .
							$_POST['_LANGTAGFNFIELDLASTNAME_'] .
			 "&street=" . $_POST['_LANGTAGFNFIELDADDRESS_'] .
			 "&city=" . $_POST['_LANGTAGFNFIELDCITY_'] .
			 "&zip=" . $_POST['_LANGTAGFNFIELDZIPCODE_'] .
			 "&country=" . $_POST['_LANGTAGFNFIELDSTATE_'] . 
			 "&countrycode=" . $_POST['_LANGTAGFNFIELDSTATEID_'] .
			 "&phone=" . $_POST['_LANGTAGFNFIELDPHONE_'] . 
			 "&mobile=" . $_POST['_LANGTAGFNFIELDMOBIL_'] . 
			 "&fax=" . $_POST['_LANGTAGFNFIELDFAX_'] . 
			 "&payment=" . $_POST['_LANGTAGFNFIELDPAYMENTINTERNALNAME_'] . 
			 "&bankname=" . $_POST['_LANGTAGFNFINANCIALINSTITUTION_'] . 
			 "&bic=" . $_POST['_LANGTAGFNBANKCODENUMBER_'] . 
			 "&iban=" . $_POST['_LANGTAGFNACCOUNTNUMBER_'] . 
			 "&accholder=" . $_POST['_LANGTAGFNACCOUNTHOLDER_'] . 
			 "&vat=" . $_POST['_LANGTAGFNFIELDFIRMVATID_'] . 
			 "&paymentname=" . $_POST['_LANGTAGFNFIELDPAYMENT_'] .
			 "&paymentfee=" . $_POST['_LANGTAGFNFIELDPAYMENTCHARGE_'] .
			 "&shipmentname=" . $_POST['_GSSBTXTFNFIELDPOSTAGE_'] . " " . $_SESSION['delivery']['ship']['name'] .
			 "&shipmentfee=" . str_replace(',','.',$_POST['_LANGTAGFNFIELDPOSTAGE_']) .
			 "&basketcount=" . $iCount . 
			 "&gsbmurl=" . $gsbmurl .
			 "&gsbmdb=" . $gsbmdb .
			 "&gsbmusr=" . $gsbmusr .
			 "&gsbmpwd=" . $gsbmpwd .
			 "&ddinvtext=" . base64_encode($ddinvtext);

//TS 14.12.2015: Gesamten Warenkorb übergeben
$cParam .= "&abasket=" . base64_encode(json_encode($_SESSION['basket']));

//echo($cParam);
//die();

$url = $shopurl . 'inc/posttogsbm.php';

$ch = curl_init();
// setze die URL und andere Optionen
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $cParam);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_TIMEOUT, 5);
//curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
// führe die Aktion aus und gebe die Daten an den Browser weiter
$gsbmerg = curl_exec($ch);

//die("Erg: " . $gsbmerg);


if(curl_errno($ch))
{
	$ares['errno'] = -1;
	$ares['error'] = curl_error($ch);
	die(json_encode($ares));
}
// schließe den cURL-Handle und gebe die Systemresourcen frei
curl_close($ch);
$aResGSBM = json_decode($gsbmerg, true);
$ares['result'] = $aResGSBM;
die(json_encode($ares));

/*$aResGSBM = json_decode($gsbmerg, true);
echo '<pre>';
print_r($aResGSBM);
die();*/

/*if($_POST['GS_useJSONResult'] == 1 && $_POST['GS_jsonObject'] != '') {
	if($_POST['GS_overwriteOID'] == 1 && $_POST['GS_oidKeyName'] != '') {
		$ares[$_POST['GS_jsonObject']][$_POST['GS_oidKeyName']] = $aResGSBM['oid'];
	}
	if($_POST['GS_overwriteCID'] == 1 && $_POST['GS_cidKeyName'] != '') {
		$ares[$_POST['GS_jsonObject']][$_POST['GS_cidKeyName']] = $aResGSBM['cid'];
	}
	if(count($ares) > 0) {
		echo json_encode($ares);
	}
}*/

?>