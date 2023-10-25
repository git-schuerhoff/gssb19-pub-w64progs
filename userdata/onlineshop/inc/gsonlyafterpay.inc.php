<?php
header('Content-Type: text/html; charset=utf-8');
/*echo "<pre>";
print_r($_SESSION);
echo "</pre>";*/

/*$exaHost = 'http://127.0.0.1:8080/';*/
/*$exaHost = 'http://exalyser.jelastic.dogado.eu/';*/
$exaHost = 'http://test-exalyser.jelastic.dogado.eu/';
$gsonly = '';
$lExa = false;
$lPayPal = false;
$maxbasket = count($_SESSION['basket']);
for($i = 0; $i < $maxbasket; $i++) {
	if(strpos($_SESSION['basket'][$i]['art_num'],'GS-EXA-') !== false) {
		$lExa = true;
		break;
	}
}

if(strtolower($_SESSION['delivery']['paym']['internalname']) == 'paymentpaypal') {
	$lPayPal = true;
}

$aExaInfo = array();
$aExaInfo['formofaddress'] = base64_encode(utf8_encode($_SESSION['CustData']['_LANGTAGFNFIELDFORMTOADDRESS_']));
$aExaInfo['firstname'] = base64_encode(utf8_encode($_SESSION['CustData']['_LANGTAGFNFIELDFIRSTNAME_']));
$aExaInfo['lastname'] = base64_encode(utf8_encode($_SESSION['CustData']['_LANGTAGFNFIELDLASTNAME_']));
$aExaInfo['company'] = base64_encode(utf8_encode($_SESSION['CustData']['_LANGTAGFNFIELDCOMPANY_']));
$aExaInfo['email'] = base64_encode(utf8_encode($_SESSION['CustData']['email']));
$aExaInfo['itemno'] = base64_encode(utf8_encode($_SESSION['basket'][$i]['art_num']));
$aExaInfo['item'] = base64_encode(utf8_encode($_SESSION['basket'][$i]['art_title']));
$aExaInfo['payment'] = base64_encode($_SESSION['delivery']['paym']['name']);

if($lExa && $lPayPal) {
	$aExaInfo['redirect'] = base64_encode($_SESSION['CustData']['_LANGTAGFNFIELDSHOPURL_'] . "index.php?page=thankyou_exa");
	$cexainfo = json_encode($aExaInfo);
	header("Location: " . $exaHost . "confirm.jsp?exainfo=" . $cexainfo);
	die();
	
} else {
	if($lExa && !$lPayPal) {
		$aExaInfo['redirect'] = base64_encode($_SESSION['CustData']['_LANGTAGFNFIELDSHOPURL_'] . "index.php?page=thankyou_exa_pre");
		$cexainfo = json_encode($aExaInfo);
		header("Location: " . $exaHost . "prepare.jsp?exainfo=" . $cexainfo);
		die();
		/*echo "<pre>";
		print_r($_SESSION);
		echo "</pre>";*/
	} else {
		header("Location: index.php?page=thankyou");
		die();
	}
}

$this->content = str_replace($tag,$gsonly,$this->content);
?>