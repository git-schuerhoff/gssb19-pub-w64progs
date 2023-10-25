<?php
//error_reporting(E_ALL);
//ini_set('display_errors','on');

chdir("../");
include_once("inc/class.shopengine.php");
require("inc/class.order.php");
$order = new Order();
session_start();
$se = new gs_shopengine();
if($se->exist_customer($_REQUEST['cemail'], $_REQUEST['cpass']) == '1'){
	if(!isset($_SESSION['order'])){		
		//$order = unserialize($_SESSION['order']);
		$_SESSION['Customer'] = $order->getCustomerByEmail($_REQUEST['cemail']);
		$_SESSION['order'] = serialize($order);
	} else {
		$order = unserialize($_SESSION['order']);
		$_SESSION['Customer'] = $order->getCustomerByEmail($_REQUEST['cemail']);
		$_SESSION['order'] = serialize($order);
	}
}
$_SESSION['buyerinfo'] = $_SESSION['Customer'];
$_SESSION['buyerinfo']['paymenttype'] = 'PaymentPayPal';
echo $se->exist_customer($_REQUEST['cemail'], $_REQUEST['cpass']);
//echo $test;
?>