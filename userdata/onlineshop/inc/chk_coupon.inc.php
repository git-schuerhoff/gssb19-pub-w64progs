<?php
session_start();
chdir("../");
include_once("inc/class.shopengine.php");
include_once('inc/class.order.php');
$order = New Order;
$order = unserialize($_SESSION['order']);
$cpse = new gs_shopengine();
$cpdbh = $cpse->db_connect();
$ausg = -1;
$cpsql = "SELECT * FROM " . $cpse->dbtoken . "coupon WHERE " .
			"coupCode = '" . $_GET['code'] . "' AND " .
			"coupUsed = '0' AND " .
			"coupAssigned = '1' LIMIT 1";
$cperg = mysqli_query($cpdbh,$cpsql);
if(mysqli_error($cpdbh) == 0)
{
	if(mysqli_num_rows($cperg) > 0)
	{
		$cp = mysqli_fetch_assoc($cperg);
		if($cp['coupPrice'] > 0)
		{
			$cpprice = $cp['coupPrice'] * -1;
		}
		else
		{
			$cpprice = $cp['coupPrice'];
		}
		
		$order->setBasket();
		$order->addCoupon($_GET['code'],$cpprice);

		$_SESSION['order'] = serialize($order);
		
		/*$_SESSION['basket'][] = array(
				"art_isdownload" => 'N',
				"art_title" => $cpse->get_lngtext('LangTagCoupon') . ' (' . $_GET['code'] . ')',
				"art_vartitle" => '', 
				"art_id" => 0, 
				"art_num" => $_GET['code'], 
				"art_price" => $cpprice,
				"art_fromQuant" => 0,
				"art_sprice" => '',
				"art_vatrate" => 0, 
				"art_weight" => '',
				"art_count" => 1, 
				"art_img" => '',
				"art_dpn" => '',
				"art_attr0" => '', 
				"art_attr1" => '', 
				"art_attr2" => '', 
				"art_quants" => '',
				"art_textfeld" => '', 
				"art_checkage" => 'N',
				"art_mustage" => 0,
				"art_defprice" => $cpprice,
				"art_isaction" => '',
				"art_isdecimal" => 0,
				"art_hasdetail" => 'N'
			);*/
		$ausg = 0;
	}
	mysqli_free_result($cperg);
}

echo $ausg;

?>