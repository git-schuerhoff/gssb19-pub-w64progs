<?php
session_start();
include_once('get_bulkprice.inc.php');
$menge = str_replace(',','.',$_GET['menge']);

chdir("../");
include_once("inc/class.shopengine.php");
include_once("inc/class.order.php");
$ubskse = new gs_shopengine();
$order = New Order();
$order = unserialize($_SESSION['order']);
$basket = $order->getBasket();

//A TS 27.06.2014 Maximale Bestellmenge berücksichtigen
$maxItems = trim($ubskse->get_setting('edMaxQuantity_Text'));
if($maxItems != "" && $maxItems > 0)
{
	if($menge > $maxItems)
	{
		die("-2|" . $ubskse->get_lngtext('LangTagTextMaxQuantity') . " " . $maxItems . "|" . $order->Basket[$_GET['idx']]['art_count'] . "~");
	}
	else
	{
		//$_SESSION['basket'][$_GET['idx']]['art_count'] = $menge;
		$order->updateItem($_GET['idx'],'art_count',$menge);
		//$preis = get_bulkprice($_SESSION['basket'][$_GET['idx']]['art_quants'],$menge,$_SESSION['basket'][$_GET['idx']]['art_defprice'],$_SESSION['basket'][$_GET['idx']]['art_isaction']);
		//$preis = get_bulkprice($order->Basket[$_GET['idx']]['art_quants'],$menge,$order->Basket[$_GET['idx']]['art_defprice'],$order->Basket[$_GET['idx']]['art_isaction']);
		//$_SESSION['basket'][$_GET['idx']]['art_price'] = $preis;
		//$order->updateItem($_GET['idx'],'art_price',$preis);
		$basket = $order->getBasket();
		$gp = $basket[$_GET['idx']]['art_totalprice'];// $preis * $menge;
		echo '0|' . $basket[$_GET['idx']]['art_price'] . '|' . $gp . '~';
	}
}
else
{
	//$_SESSION['basket'][$_GET['idx']]['art_count'] = $menge;
	$order->updateItem($_GET['idx'],'art_count',$menge);
	//$preis = get_bulkprice($_SESSION['basket'][$_GET['idx']]['art_quants'],$menge,$_SESSION['basket'][$_GET['idx']]['art_defprice'],$_SESSION['basket'][$_GET['idx']]['art_isaction']);
	//$preis = get_bulkprice($order->Basket[$_GET['idx']]['art_quants'],$menge,$order->Basket[$_GET['idx']]['art_defprice'],$order->Basket[$_GET['idx']]['art_isaction']);
	//$_SESSION['basket'][$_GET['idx']]['art_price'] = $preis;
	//$order->updateItem($_GET['idx'],'art_price',$preis);
	$basket = $order->getBasket();
	$gp = $basket[$_GET['idx']]['art_totalprice'];//$gp = $preis * $menge;
	echo '0|' . $basket[$_GET['idx']]['art_price'] . '|' . $gp . '~';
}

//$anz = count($order->Basket);
/*$total = 0;
for($p = 0; $p < $anz; $p++) {
	$total += $_SESSION['basket'][$p]['art_count'] * $_SESSION['basket'][$p]['art_price'];
}*/
//$total = $order->getItemsTotal();
/*TS 07.03.2016: Gesamtrabatt (Kundenrabatt + Warenwertrabatt) ermitteln*/
//Rabatt Warenwert
/*$discountpercent = 0;
if($ubskse->get_setting('edDisamount1_Text') <> '') {
	$discountamount1 = floatval(str_replace(',', '.',$ubskse->get_setting('edDisamount1_Text')));
	if($total >= $discountamount1) {
		$discountpercent = floatval(str_replace(',', '.',$ubskse->get_setting('edDiscount1_Text')));
	}
	if($ubskse->get_setting('edDisamount2_Text') <> '') {
		$discountamount2 = floatval(str_replace(',', '.',$ubskse->get_setting('edDisamount2_Text')));
		if($total >= $discountamount2) {
			$discountpercent = floatval(str_replace(',', '.',$ubskse->get_setting('edDiscount2_Text')));
		}
	}
}*/
//Rabatt Kunde
if(isset($_SESSION['login'])) {
	if($_SESSION['login']['ok']) {
		if($_SESSION['login']['cusDiscount'] <> '0') {
			$discountpercent += floatval(str_replace(',', '.',$_SESSION['login']['cusDiscount']));
		}
	}
}

/*for($p = 0; $p < $anz; $p++) {
	$order->updateItem($p,'art_discount',$discountpercent);
}*/
$_SESSION['order'] = serialize($order);
?>