<?php
session_start();
chdir("../");
include_once("inc/class.shopengine.php");
include_once("inc/class.order.php");
$order = new Order();
$order = unserialize($_SESSION['order']);
$basket = $order->getBasket();
$se_smpm = new gs_shopengine();
$download = false;
if($_GET['mode'] == 'ship' || $_GET['mode'] == 'pay')
{
	if($_GET['mode'] == 'ship')
	{
		$aErg = $se_smpm->get_shipment($_GET['area']);
	}
	else
	{
		// Wenn Downloadartikel im Warenkorb sind -> Rechnung, Nachnahme und Vorkasse ausblenden
		$bmax = count($basket);
		if($bmax > 0) {
			foreach($basket as $basketitem) {
				if($basketitem['art_isdownload'] == 'Y') {
					$download = true;
					break;
				}
			}
		}
		
		
		$aErg = $se_smpm->get_payment($_GET['area'],$download);
	}
	$ergmax2 = count($aErg);
	if($ergmax2 > 0)
	{
		$ausg = '';
		for($a = 0; $a < $ergmax2; $a++)
		{
			$ausg .= '0|' . $aErg[$a]['sortid'] . '|' . $aErg[$a]['name'] . '~';
		}
		echo $ausg;
	}
	else
	{
		echo '-1| | ~';
	}
	
}
else
{
	echo '-1| | ~';
}
?>