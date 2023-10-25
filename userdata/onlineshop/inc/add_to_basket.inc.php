<?php
session_start();
include_once('get_bulkprice.inc.php');
chdir('../');
include_once('inc/class.order.php');
$order = New Order;
$order = unserialize($_SESSION['order']);
if(!isset($_POST['imenge']))
{
	die('-1| | ~');
}

$dpn = '';
if(isset($_SESSION['aitem']))
{
	if($_SESSION['aitem']['itemHasDetail'] == 'Y')
	{
		$dpn = 'index.php?page=detail&item=' . $_SESSION['aitem']['itemItemId'];
	}
}

//chdir("../");
include_once("inc/class.shopengine.php");
$artse = new gs_shopengine();
$aImgs = array();

if($_POST['item'] > 0) {
	if(isset($_SESSION['aitem'])) {
		unset($_SESSION['aitem']);
	}
	$artse->get_item($_POST['item']);
	$aPrices = $artse->get_prices($_SESSION['aitem']['itemItemId']);
	$_SESSION['aitem']['aprices'] = $aPrices;
	//A TS 05.08.2014
	$_SESSION['aitem']['itemIsAction'] = $artse->chk_action($_SESSION['aitem']['itemItemId'],$aPrices);
	if($_SESSION['aitem']['itemIsAction'] == 'Y') {
		$defprice = str_replace(',','.',$_SESSION['aitem']['aprices']['actprice']);
	} else {
		$defprice = $_SESSION['aitem']['aprices']['price'];
	}
	$dpn = $artse->absurl . 'index.php?page=detail&item=' . $_POST['item'];
	
	/*A TS 09.12.2014: Permalink verwenden, wenn verf�gbar*/
	if($artse->edition == 13) {
		if($artse->get_setting('cbUsePermalinks_Checked') == 'True') {
			if($_SESSION['aitem']['itemItemPage'] != '') {
				$dpn = $_SESSION['aitem']['itemItemPage'];
			}
		}
	}
}

/*A TS 19.06.2015:Get Standard-Image for basket*/
$aImgs = $artse->get_itempics($_SESSION['aitem']['itemItemId']);
$stdImg = $aImgs[0]['ImageName'];
/*E TS 19.06.2015:Get Standard-Image for basket*/

/*A TS 11.12.2015: Bei Mietpreisen, ist die Menge die Anzahl der mit der ersten Rechnung abgerechneten Einheiten*/
$menge = str_replace(',','.',$_POST['imenge']);
$isinitprice = 0;
$istrialitem = 'N';
if(isset($aPrices['isrental'])) {
	if($aPrices['isrental'] == 'Y') {
		if($_POST['istrial'] == 'Y') {
			$defprice = str_replace(',','.',$aPrices['trialprice']);
			$menge = $aPrices['trialfrequency'];
			$istrialitem = 'Y';
		} else {
			if($aPrices['istrial'] == 'Y') {
				$menge = $_POST['billingfreq'] - $aPrices['trialfrequency'];
			} else {
				$menge = $_POST['billingfreq'];
			}
		}
	}
	if(isset($_POST['isinitprice'])) {
		if($_POST['isinitprice'] != 0) {
			$menge = 1;
			$defprice = str_replace(',','.',$_POST['isinitprice']);
			$isinitprice = 1;
		}
	}
}
/*E TS 11.12.2015: Bei Mietpreisen, ist die Menge die Anzahl der mit der ersten Rechnung abgerechneten Einheiten*/
if(isset($_POST['billingfreq'])){
	$billingfreq = $_POST['billingfreq'];
	$billingfreqtext = $_POST['billingfreqtext'];
} else {
	$billingfreq = '';
	$billingfreqtext = '';
}

//$bulkprice = get_bulkprice($_SESSION['aitem']['aprices']['abulk'],$menge,$defprice,$_SESSION['aitem']['itemIsAction']);
$order->setBasket();

// Check max ItemCount
$maxItems = trim($artse->get_setting('edMaxQuantity_Text'));
if($maxItems <> ''){
	if(count($order->Basket) < $maxItems){
		$order->addItem($menge,$defprice,$stdImg,$dpn,$aPrices,$isinitprice,$istrialitem,$billingfreq,$billingfreqtext);
		$_SESSION['order'] = serialize($order);
		$total = $order->ItemsTotal;
		echo '0|' . $_SESSION['aitem']['itemItemDescription'] . '|' . $order->ItemCount . '|' . $artse->get_currency($total,0,'.') . '~';
	} else {
		$_SESSION['order'] = serialize($order);
		$total = $order->ItemsTotal;
		echo '-2|' . $_SESSION['aitem']['itemItemDescription'] . '|' . $order->ItemCount . '|' . $artse->get_currency($total,0,'.') . '~';
	}
} else {
	$order->addItem($menge,$defprice,$stdImg,$dpn,$aPrices,$isinitprice,$istrialitem,$billingfreq,$billingfreqtext);
		$_SESSION['order'] = serialize($order);
		$total = $order->ItemsTotal;
		echo '0|' . $_SESSION['aitem']['itemItemDescription'] . '|' . $order->ItemCount . '|' . $artse->get_currency($total,0,'.') . '~';
}
//$_SESSION['order'] = serialize($order);

//$_SESSION['order'] = serialize($order);
//$total = $order->ItemsTotal;
//echo '0|' . $_SESSION['aitem']['itemItemDescription'] . '|' . $order->ItemCount . '|' . $artse->get_currency($total,0,'.') . '~';

function getIntFromBool($cMixed)
{
	if($cMixed == 'W' || $cMixed == 'Wahr' || $cMixed == 'True' || $cMixed == 'J' || $cMixed == '1' ||
		$cMixed == 'T' || $cMixed == 'Ja' || $cMixed == 'Y' || $cMixed == 'Yes')
	{
		return 1;
	}
	else
	{
		return 0;
	}
}


?>