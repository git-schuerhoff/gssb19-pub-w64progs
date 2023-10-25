<?php 
//session_start();
$optionhtml = $this->gs_file_get_contents('template/radio.html');
$deldbh = $this->db_connect();
$order = unserialize($_SESSION['order']);
$areaID = $order->getAreaID();
//$areaID++;
$shipmentitems="";
$aShip = $this->get_shipment($areaID);
$smmax2 = count($aShip);
if($smmax2 > 0)
{
    $buyshipment = $this->gs_file_get_contents('template/shipment.html');
    $buyshipment = str_replace('{GSSE_CLASS_SHIPMENT}','list-paymenttypes list-unstyled',$buyshipment);
	if($order->isMixBasket){
		for($p = 0; $p < $smmax2; $p++)
		{
			$cur_opt = $optionhtml;
			
			if($p == 0){
				$checked=" checked='checked'";
			} else {
				$checked = "";
			}
			
			switch($aShip[$p]['name'])
			{
				case "DHL":
					$iconclass = "sprite sprite-dhl-color-big margr10 pull-right";
					$icontitle = "DHL";
					break;
				case "UPS":
					$iconclass = "sprite sprite-ups-color-big margr10 pull-right";
					$icontitle = "UPS";
					break;
				case "DHL EU":
					$iconclass = "sprite sprite-dhl-color-big margr10 pull-right";
					$icontitle = "DHL EU";
					break;
				case "UPS EU":
					$iconclass = "sprite sprite-ups-color-big margr10 pull-right";
					$icontitle = "UPS EU";
					break;    
				default:
					$iconclass = "";
					$icontitle = "";
					break;    
			}
			
			if($order->isDownloadItems){
				$aShip[$p]['name'] = $aShip[$p]['name'].' + Download';
			}
			
			$cur_opt = str_replace('{GSSE_OPT_VALUE}',$aShip[$p]['sortid'].'|'.$aShip[$p]['name'],$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_CHECKED}',$checked,$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_ONCLICK}','onclick="radioToggle(shipmentfields,'.$p.')"',$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_CLASS}','kor-label w100p js-radio-trigger',$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_CLASSDIV}','type paymentServiceCC',$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_CLASSINPUT}','js-radio-target',$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_TEXT}',$aShip[$p]['name'],$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_ICONCLASS}',$iconclass,$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_ICONTITLE}',$icontitle,$cur_opt);
			$shipmentitems .= $cur_opt;
		} 
	} 
	if(!$order->isMixBasket && !$order->isDownloadItems){
		for($p = 0; $p < $smmax2; $p++)
		{
			$cur_opt = $optionhtml;
			
			if($p == 0){
				$checked=" checked='checked'";
			} else {
				$checked = "";
			}
			
			switch($aShip[$p]['name'])
			{
				case "DHL":
					$iconclass = "sprite sprite-dhl-color-big margr10 pull-right";
					$icontitle = "DHL";
					break;
				case "UPS":
					$iconclass = "sprite sprite-ups-color-big margr10 pull-right";
					$icontitle = "UPS";
					break;
				case "DHL EU":
					$iconclass = "sprite sprite-dhl-color-big margr10 pull-right";
					$icontitle = "DHL EU";
					break;
				case "UPS EU":
					$iconclass = "sprite sprite-ups-color-big margr10 pull-right";
					$icontitle = "UPS EU";
					break;    
				default:
					$iconclass = "";
					$icontitle = "";
					break;    
			}
			
			$cur_opt = str_replace('{GSSE_OPT_VALUE}',$aShip[$p]['sortid'].'|'.$aShip[$p]['name'],$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_CHECKED}',$checked,$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_ONCLICK}','onclick="radioToggle(shipmentfields,'.$p.')"',$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_CLASS}','kor-label w100p js-radio-trigger',$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_CLASSDIV}','type paymentServiceCC',$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_CLASSINPUT}','js-radio-target',$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_TEXT}',$aShip[$p]['name'],$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_ICONCLASS}',$iconclass,$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_ICONTITLE}',$icontitle,$cur_opt);
			$shipmentitems .= $cur_opt;
		}
	}
	if(!$order->isMixBasket && $order->isDownloadItems){
		$cur_opt = $optionhtml;
		$cur_opt = str_replace('{GSSE_OPT_VALUE}','0|Download',$cur_opt);
		$cur_opt = str_replace('{GSSE_OPT_CHECKED}'," checked='checked'",$cur_opt);
		$cur_opt = str_replace('{GSSE_OPT_ONCLICK}','onclick="radioToggle(shipmentfields,0)"',$cur_opt);
		$cur_opt = str_replace('{GSSE_OPT_CLASS}','kor-label w100p js-radio-trigger',$cur_opt);
		$cur_opt = str_replace('{GSSE_OPT_CLASSDIV}','type paymentServiceCC',$cur_opt);
		$cur_opt = str_replace('{GSSE_OPT_CLASSINPUT}','js-radio-target',$cur_opt);
		$cur_opt = str_replace('{GSSE_OPT_TEXT}','Download',$cur_opt);
		$cur_opt = str_replace('{GSSE_OPT_ICONCLASS}','',$cur_opt);
		$cur_opt = str_replace('{GSSE_OPT_ICONTITLE}','',$cur_opt);
		$shipmentitems = $cur_opt;
	}
    $buyshipment = str_replace('{GSSE_INCL_SHIPMENT}',$shipmentitems,$buyshipment);
}    
$_SESSION['order'] = serialize($order);
$this->content = str_replace($tag, $buyshipment , $this->content);
?>