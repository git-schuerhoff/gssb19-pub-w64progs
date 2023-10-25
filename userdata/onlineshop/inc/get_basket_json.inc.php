<?php
//header("Content-type: application/json; charset=utf-8");
session_start();
chdir("../");
include_once("inc/class.order.php");
$order = new Order();
$order = unserialize($_SESSION['order']);
$basket = $order->getBasket();
echo json_encode($basket);
?>