<?php
session_start();
chdir("../");
include_once("inc/class.shopengine.php");
include_once("inc/class.order.php");
$cpse = new gs_shopengine();
$order = new Order();
$order = unserialize($_SESSION['order']);
$basket = $order->getBasket();
$cpdbh = $cpse->db_connect();
$baskmax2 = count($basket);
for($b = 0; $b < $baskmax2; $b++)
{
	if(preg_match('/'.$cpse->get_lngtext('LangTagCoupon').'/', $basket[$b]['art_title']))
	{
		$code = $basket[$b]['art_num'];
		$cpsql = "SELECT * FROM " . $cpse->dbtoken . "coupon WHERE " .
			"coupCode = '" . $code . "' LIMIT 1";
		$cperg = mysqli_query($cpdbh,$cpsql);
		if(mysqli_error($cpdbh) == 0)
		{
			if(mysqli_num_rows($cperg) > 0)
			{
				//$this->get_lngtext('LangTagCoupon')
				$cp = mysqli_fetch_assoc($cperg);
				$coupid = $cp['coupId'];
				$coupvalid = $cp['coupValid'];
				if($coupvalid == 'once')
				{
					$dsql = "UPDATE " . $cpse->dbtoken . "coupon SET coupUsed = 'Y' WHERE coupId = '" . $coupid . "' LIMIT 1";
					mysqli_query($cpdbh,$dsql);
				}		
			}
			mysqli_free_result($cperg);
		}
		break;
	}
}

?>