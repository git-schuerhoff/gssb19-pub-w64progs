<?php
session_start();
chdir('../');
include_once('inc/class.order.php');
$order = New Order;
$order = unserialize($_SESSION['order']);
$basket = $order->getBasket();
$age = 0;
$bsmax = count($basket);
if($bsmax > 0)
{
	for($a = 0; $a < $bsmax; $a++)
	{
		
		if($basket[$a]['art_checkage'] == 'Y' && $basket[$a]['art_mustage'] > $age)
		{
			$age = $basket[$a]['art_mustage'];
		}
	}
}
echo $age;
?>