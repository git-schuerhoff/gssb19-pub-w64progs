<?php
$lohtml = '';
$orders = $this->gs_file_get_contents('template/lastorders.html');
$aOTags = $this->get_tags_ret($orders);
$orders = $this->parse_texts($aOTags,$orders);

$lorder = $this->gs_file_get_contents('template/lastordersnew.html');
$aLOTags = $this->get_tags_ret($lorder);
$lorder = $this->parse_texts($aLOTags,$lorder);
$cid = $_SESSION['login']['cusIdNo'];
if(file_exists("dynsb/class/class.shoplog.php"))
{
	if(!in_array("shoplog",get_declared_classes()))
	{
		require_once("dynsb/class/class.shoplog.php");
	}
	require_once("dynsb/include/functions.inc.php");
}
else
{
	echo($this->get_lngtext('LangTagErrorMissingRootPathFile'));
	echo "customerlastorder";
}
$co = new shoplog();
$orderList = $co->getOrderByCustom($cid);
$allorders ='';
$num = mysqli_num_rows($orderList);
if($num!=0)
{
	while($order = mysqli_fetch_assoc($orderList))
	{
		$cur_order = $lorder;
		//var_dump($order);
		$cur_order = str_replace('{GSSE_INCL_ODERDATA}',$order['ordId'],$cur_order);
		$cur_order = str_replace('{GSSE_INCL_ODERDATE}',timestamp_mysql2german($order['ordDate']),$cur_order);
		$cur_order = str_replace('{GSSE_INCL_Address}',$order['ordFirstName'].' '.$order['ordLastName'].', '.$order['ordStreet'].', '.$order['ordZipCode'].' '.$order['ordCity'],$cur_order);
		$cur_order = str_replace('{GSSE_INCL_TotalPrice}',replPtC(sprintf("%01.2f", $order['ordTotalValueAfterDsc2']))." ".$order['ordCurrency'],$cur_order);
		$cur_order = str_replace('{GSSE_INC_ORDERID}',$order['ordId'],$cur_order);
		$allorders .= $cur_order;
	}
}
$orders = str_replace('{GSSE_INCL_LASTORDERSNEW}',$allorders, $orders);

$this->content = str_replace('{GSSE_FUNC_CUSTOMERLASTORDERNEW}',$orders,$this->content);
?>