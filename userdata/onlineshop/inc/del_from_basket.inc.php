<?php
session_start();

chdir("../");
include_once("inc/class.shopengine.php");
$ubskse = new gs_shopengine();
include_once('inc/class.order.php');
$order = New Order;
$order = unserialize($_SESSION['order']);
$basket = $order->getBasket();
//A TS 07.08.2014: Wenn -1 als Index übergeben wird, kompletten Warenkorb löschen
if($_GET['idx'] != -1)
{
	$idx = $_GET['idx'];
	//$order->deleteItem($idx);
	$aHelp = array();
	$bmax = count($basket);
	$isrental = $basket[$idx]['art_prices']['isrental'];
	$itemId = $basket[$idx]['art_id'];
	for($a = 0; $a < $bmax; $a++)
	{
		if($a != $_GET['idx'])
		{
			/*array_push($aHelp,$_SESSION['basket'][$a]);*/
			if($isrental == 'N') {
				$aHelp[] = $basket[$a];
			} else {
				if($basket[$a]['art_id'] != $itemId) {
					$aHelp[] = $basket[$a];
				}
			}
		}
	}
	$order->updateBasket($aHelp);
}
else
{
	$order->delBasket();
}
$basket = $order->getBasket();

$_SESSION['order'] = serialize($order);
echo "0";
?>