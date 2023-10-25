<?php
	//error_reporting(E_ALL);
	//ini_set('display_errors','on');
	
	session_start();
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") ." GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0
	
	if(isset($_GET['killsession'])) {
		if($_GET['killsession'] == 1) {
			if(isset($_SESSION)) {
				session_destroy();
				session_start();
			}
		}
	}
	
	if(!isset($_SESSION['template'])) {
		$d = dir("template");
		while (false !== ($entry = $d->read())) {
			if(strpos(strtolower($entry),'.html') !== false) {
				$_SESSION['template'][$entry] = file_get_contents('template/' . $entry);
			}
		}
		$d->close();
	}
	
	include_once("router.php");
	if(isset($_GET['url'])) {
		$request = parse_url($_SERVER["REQUEST_URI"]);
		$url = str_replace('url=','',$request['query']);
		$query = parse_url($rewrite['index.php?url='.$url]);
		$page = explode('&', $query['query']);
		$_GET['page']=str_replace('page=','',$page[0]);
		if(strpos($page[1],'item=') == 0){
			$_GET['item']=str_replace('item=','',$page[1]);
			unset($_GET['idx']);
		}
		if(strpos($page[1],'idx=') == 0){
			$_GET['idx']=str_replace('idx=','',$page[1]);
		}
	}
	
	include_once("inc/class.shopengine.php");
	//$se = new gs_shopengine($page);
	$se = new gs_shopengine();
	if(isset($_GET['pp_status'])){
		if($_GET['pp_status'] == 'ok'){
			$_SESSION['pp_status']='ok';
			$_SESSION['pp_token'] = $_GET['token'];
		}
	}
	
	include_once('inc/class.order.php');
	if(!isset($_SESSION['order'])) {
		$order = new Order();//new Order() setzt Standard-Werte für Versand- und Zahlungsart und leere Arrays, Objekte und Variablen für ALLE weiteren Angaben, die für die Bestellung nötig sind
		$_SESSION['order'] = serialize($order);
	} else {
		$order = unserialize($_SESSION['order']);
	}
	
	if(isset($_GET['page'])) {
		if(strpos($_GET['page'],'thankyou') !== false) {
            // A SM 27.01.2017 - Infos für Trustedshops, diese sollen auf Thankyou Seite vorhanden sein.
            $_SESSION['trusted']['tsCheckoutOrderNr'] = $_SESSION['pid'];
            $_SESSION['trusted']['tsCheckoutBuyerEmail'] = $_SESSION['buyerinfo']['email'];
            $_SESSION['trusted']['tsCheckoutOrderAmount'] = $_SESSION['invoicetotal'];
            $_SESSION['trusted']['tsCheckoutOrderCurrency'] = $se->get_setting('edCurrencySymbol_Text');
            $_SESSION['trusted']['tsCheckoutOrderPaymentType'] = $_SESSION['delivery']['paym']['name'];
            $_SESSION['trusted']['tsCheckoutOrderEstDeliveryDate'] = date('Y-m-d',strtotime('+2 days'));
            // E SM 27.01.2017
            $order->delBasket();
			if(isset($_SESSION['basket'])) {
				unset($_SESSION['basket']);
				unset($_SESSION['pid']);
				unset($_SESSION['invoicetotal']);
				unset($_SESSION['shipcost']);
				if(isset($_SESSION['CustData'])) {
					unset($_SESSION['CustData']);
				}
			}
			if(isset($_SESSION['valcode'])) { unset($_SESSION['valcode']); }
			if(isset($_COOKIE['valcode'])) { unset($_COOKIE['valcode']); }
			if(isset($_SESSION['validated'])) { unset($_SESSION['validated']); }
			if(isset($_SESSION['valmailsend'])) { unset($_SESSION['valmailsend']); }
			if(isset($_COOKIE['valmailsend'])) { unset($_COOKIE['valmailsend']); }
			if(isset($_SESSION['pp-plus'])) { unset($_SESSION['pp-plus']); }
		}

		
		$page = $_GET['page'] . '.html';
		
		
		if($page != 'productgroup.html' && $page != 'detail.html') {
			$_SESSION['anavi'] = array();
		}
	} else {
		$_GET['page'] = 'main';
		$page = 'index.html';
	} 
	
	if($se->get_setting('cbUsePhpB2BLogin_Checked') == 'True') {
		if(!isset($_SESSION['login']['ok'])) {
			$page = 'index.html';
		} else {
			if(!$_SESSION['login']['ok']) {
				$page = 'index.html';
			}
		}
	}
	
	if($page == 'index.html') {
		$wl = $se->get_setting('RadioGroupIndexPage_ItemIndex');
		switch($wl) {
			case 0:
				$_GET['page'] = 'main';
				if($se->get_setting('cbUsePhpB2BLogin_Checked') == 'False') {
					$page = 'main.html';
				}
				break;
			case 1:
				$_GET['page'] = 'index';
				$page = 'index.html';
				break;
			default:
				$_GET['page'] = 'index';
				$page = 'index.html';
				break;
		}
	}
	
	//A TS 17.11.2014: letzte Seite ermitteln und merken
	//Bei Login/Logout soll auf die letzte Seite navigiert werden
	if(isset($_SERVER['HTTP_REFERER'])) {
		$_SESSION['lastpage'] = $_SERVER['HTTP_REFERER'];
		$_COOKIE['lastpage'] = $_SERVER['HTTP_REFERER'];
	}
	
	//A SM 28.04.2017 - Statistik
	if(file_exists("dynsb/class/class.pagestatistics.php")){
		require_once("dynsb/class/class.pagestatistics.php");
		$insert = new pagestatistics();
		$insert->querySetUserclicks( session_id() );
		$insert->querySetUserDetails(session_id(), $_SERVER['HTTP_USER_AGENT'],null);
		//TS 21.06.2017: die Variable $_SERVER['HTTP_REFERER'] existiert nur dann, wenn es auch einen
		//Referer (d. h. eine andere Webseite von der aus auf den Shop verlinkt wurde (z. B. google) gibt.
		//Bei einem direkten Aufruf des Shops über den Browser gibt es keinen Referer, dann schmeißt PHP eine
		//Notice und das wollen wir nicht.
		if(isset($_SERVER['HTTP_REFERER'])) {
			$insert->querySetPageVisits($_SERVER['HTTP_REFERER']);
		} else {
			$insert->querySetPageVisits('Direct');
		}
	}
	// E SM 28.04.2017 - Statistik

	$se->currPage = $page;
	$se->parse_page();
	$se->place_ganalytics();
	$se->place_etracker();
?>
<script type="text/javascript" src="js/effects.js" charset="UTF-8"></script><!--!!!-->
<script type="text/javascript" src="js/form.js" charset="utf-8"></script><!--!!!-->
<script type="text/javascript" src="js/jquery-noconflict.js" charset="utf-8"></script><!--!!!-->
<script type="text/javascript" src="js/jquery.ba-hashchange.min.js" charset="utf-8"></script><!--!!!-->
<script type="text/javascript" src="js/ios-orientationchange-fix.js" charset="utf-8"></script><!--!!!-->
<script type="text/javascript" src="js/selectul.js" charset="utf-8"></script><!--!!!-->
<script type="text/javascript" src="js/chameleon.js" charset="utf-8"></script><!--!!!-->
<script type="text/javascript" src="js/adapt.js" charset="utf-8"></script><!--!!!-->
<script type="text/javascript" src="js/em_menu.js" charset="utf-8"></script><!--!!!-->
<script type="text/javascript" src="js/jquery.fancybox.pack.js" charset="utf-8"></script><!--!!!-->
<script type="text/javascript" src="js/csslider_1.1.js" charset="utf-8"></script><!--!!!-->
<script type="text/javascript" src="js/hammer.min.js" charset="utf-8"></script><!--!!!-->