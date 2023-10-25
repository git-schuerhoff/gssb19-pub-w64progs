<?php
	header('Content-Type: application/javascript; charset=utf-8');
	session_start();
	chdir("../");
	include_once("inc/class.shopengine.php");
	$prese = new gs_shopengine();
	$settings = 'var g_aSettings = {'.PHP_EOL;
	$setcount = count($_SESSION['sb_settings']) - 1;
	$s = 0;
	foreach($_SESSION['sb_settings'] as $key => $val) {
		if((strpos($key,'Pass')===false) && (strpos($key,'User')===false) &&
			(strpos($key,'PPW')===false) && (strpos($key,'clb')===false) && (strpos($key,'Trusted')===false)) {
				$settings .= chr(9).chr(9).chr(9).chr(9).$key.':"'.$val.'"';
				if($s < $setcount) {
					$settings .= ','.PHP_EOL;
				} else {
					$settings .= PHP_EOL;
				}
		}
		$s++;
	}
	$settings .= chr(9).chr(9).chr(9).'}'.PHP_EOL;
	//TS 15.03.2017: Und auch die Templates
	//Alle
	//$settings .= 'var g_aTemplate = {'.PHP_EOL;
	//$setcount = count($_SESSION['template']) - 1;
	//$s = 0;
	//foreach($_SESSION['template'] as $key => $val) {
	//	$settings .= chr(9).chr(9).chr(9).chr(9).$key.':"'.base64_encode($val).'"';
	//	if($s < $setcount) {
	//		$settings .= ','.PHP_EOL;
	//	} else {
	//		$settings .= PHP_EOL;
	//	}
	//	$s++;
	//}
	//$settings .= chr(9).chr(9).chr(9).'}'.PHP_EOL;*/
	//Einzeln
	//Login
	$aLogin = array();
	if(isset($_SESSION['login'])) {
		$aLogin = $_SESSION['login'];
	}
	$settings .= 'var g_errbox_enc = "'.base64_encode($_SESSION['template']['errorbox.html']).'";'.PHP_EOL.
					 'var g_itemlink_enc = "'.base64_encode($_SESSION['template']['link.html']).'";'.PHP_EOL.
					 'var g_itemimagelink_enc = "'.base64_encode($_SESSION['template']['imagelink.html']).'";'.PHP_EOL.
					 'var g_itemimage_enc = "'.base64_encode($_SESSION['template']['image.html']).'";'.PHP_EOL.
					 'var g_pcontent_enc = "'.base64_encode($_SESSION['template']['pcontent.html']).'";'.PHP_EOL.
					 'var g_mbouter_enc = "'.base64_encode($_SESSION['template']['mini_basketitems_outer.html']).'";'.PHP_EOL.
					 'var g_mbitem_enc = "'.base64_encode($_SESSION['template']['mini_basketitems_item.html']).'";'.PHP_EOL.
					 'var g_cntID = "'.$prese->cntID.'";'.PHP_EOL.
					 'var g_lngID = "'.$prese->lngID.'";'.PHP_EOL.
					 'var g_cmp_outer = "'.base64_encode($_SESSION['template']['itemcompare_items_outer.html']).'";'.PHP_EOL.
					 'var g_cmp_item = "'.base64_encode($_SESSION['template']['itemcompare_items_item.html']).'";'.PHP_EOL.
					 'var g_mbasket_empty = "'.base64_encode($_SESSION['template']['mini_basket_empty.html']).'";'.PHP_EOL.
					 'var g_strlogin = "'.base64_encode(json_encode($aLogin)).'";'.PHP_EOL.
					 'var g_sbedition = "'.$prese->edition.'";'.PHP_EOL;
	$settings .= 'var g_alng = {';
	$s = 0;
	$setcount = count($prese->aLang) - 1;
	foreach($prese->aLang as $val) {
		//$aRetLng[] = array($val[0],$val[1]);
		//echo '<br>'.
		//$settings .= chr(9).chr(9).chr(9).$val[0].':"'.base64_encode($val[1]).'"';
		$settings .= chr(9).chr(9).chr(9).$val[0].':"'.rawurlencode($val[1]).'"';
		if($s < $setcount) {
			$settings .= ','.PHP_EOL;
		} else {
			$settings .= '}'.PHP_EOL;
		}
		$s++;
	}
	echo $settings;
?>