<?php
// Prüfen, ob PayPal überhaupt eingestellt ist
if($this->get_setting('cbUsePayPal_Checked') == 'True'){
	// Prüfen, ob PayPalPlus eingestellt ist
	if($this->get_setting('rbUsePPPlus_Checked') == 'True'){
		$tmplFile = 'buypaymentpaypalplus.html';
		include('parse_func.inc.php');
	} else {
	// Klassisches PayPal
	
	}
	
}
?>