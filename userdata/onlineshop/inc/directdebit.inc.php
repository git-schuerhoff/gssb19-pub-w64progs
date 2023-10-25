<?php
if(!isset($_SESSION['order'])){
	session_start();
}
include_once('class.order.php');
$order = New Order;
$order = unserialize($_SESSION['order']);
$customer = $order->getCustomer();
$tmplFile = "directdebit.html";
include('parse_func.inc.php');
	
	$this->content = str_replace('{GSSE_INCL_BANK}', '', $this->content);
	
    $this->content = str_replace('{GSSE_INCL_IBAN}', '', $this->content);
	
    $this->content = str_replace('{GSSE_INCL_BIC}', '', $this->content);
	
    $this->content = str_replace('{GSSE_INCL_HOLDERFIRST}', $customer['firstname'], $this->content); 
    $this->content = str_replace('{GSSE_INCL_HOLDERLAST}', $customer['lastname'], $this->content);
?>