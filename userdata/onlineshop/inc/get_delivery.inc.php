<?php
chdir("../");
require_once("inc/class.shopengine.php");
require("inc/class.order.php");
$order = new Order();
session_start();
$order = unserialize($_SESSION['order']);
$payment = $order->getPayment();
$pname = $payment['paymName'];
$charge = $payment['paymInfo']['paymCharge'];
$chargepercent = $payment['paymInfo']['paymChargePercent'];
$usecashdiscount = $payment['paymInfo']['paymUseCashDiscount'];
$cashdiscount = $payment['paymInfo']['paymCashDiscount'];
$cashdiscountpercent = $payment['paymInfo']['paymCashDiscountPercent'];
$pinternalname = $payment['paymInternalName'];
$_SESSION['order'] = serialize($order);
echo '0|' . $pname.'|'.$charge .'|'.$chargepercent.'|'.$usecashdiscount.'|'.$cashdiscount.'|'.$cashdiscountpercent.'|'.$pinternalname.'~';
?>