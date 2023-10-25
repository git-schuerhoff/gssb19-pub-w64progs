<?php 
session_start();
chdir('../');
include_once('inc/class.order.php');
$order = New Order();
$order = unserialize($_SESSION['order']);
$ship = explode('|',$_POST['shipment']);
$order->setAreaID($_SESSION['AreaID']);
$order->setDelivery($ship[0],$ship[1]);
$_SESSION['order'] = serialize($order);
?>