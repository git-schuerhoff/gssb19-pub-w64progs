<?php
//error_reporting(E_ALL);
//ini_set('display_errors','on');
chdir("../");
require("inc/class.order.php");
$order = new Order();

session_start();
$data = array();
if(isset($_POST['step'])){
	if($_POST['step'] == 'cardsteptwo'){
		if(isset($_SESSION['order'])){
			$order = unserialize($_SESSION['order']);
			$data = $order->getCustomer();
		} else {
			$data = "SESSION['order'] ist leer";
		}
	}
	
	if($_POST['step'] == 'cardstepthree'){
		$order = unserialize($_SESSION['order']);
		$customer = $order->getCustomer();
		$payment = $order->getPayment();
		$delivery = $order->getDelivery();
		$data['customer'] = $customer;
		$data['payment'] = $payment;
		$data['delivery'] = $delivery;
	}
}
echo json_encode($data);
?>