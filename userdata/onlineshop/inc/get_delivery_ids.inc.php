<?php
header("Content-type: application/json; charset=utf-8");
chdir("../");
require("inc/class.order.php");
$order = new Order();
session_start();
$order = unserialize($_SESSION['order']);
$delivery = $order->getDelivery();
$payment = $order->getPayment();
$aDelivery = array("ship_id" => "", "paym_id" => "");
if(isset($delivery['delivID']))
{
	$aDelivery['ship_id'] = $delivery['delivID'];
	$aDelivery['paym_id'] = $payment['paymID'];
}
echo json_encode($aDelivery);
?>