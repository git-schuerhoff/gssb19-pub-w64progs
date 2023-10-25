<?php 
chdir('../');
include_once('inc/class.order.php');
$order = New Order();
session_start();
$order = unserialize($_SESSION['order']);
$paym = explode('|',$_POST['paym']);
$order->setAreaID($_SESSION['AreaID']);
$order->setPayment($paym[2],$paym[1],$paym[0]);
$_SESSION['order'] = serialize($order);
?>