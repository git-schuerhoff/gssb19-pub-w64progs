<?php 
include_once('class.order.php');
$order = New Order;
$order = unserialize($_SESSION['order']);
$payment = $order->getPayment();
$basket = $order->getBasket();
$customer = $order->getCustomer();
$rentals = false;
$download = false;
if($payment['paymInternalName'] == 'PaymentDirectDebit'){
	$tmplFile = 'sepamandat.html';
	include_once('parse_func.inc.php');
	$bmax = count($basket);
	if($bmax > 0)
	{
		for($b = 0; $b < $bmax; $b++)
		{
			if($basket[$b]['art_isdownload'] == 'Y') {
				$download = true;
			}
			if($basket[$b]['art_prices']['isrental'] == 'Y') {
				$rentals = true;
			}
		}
	}
	
	if($rentals) {
		$this->content = str_replace('{GSSE_INCL_LangTagCreditorAccept}',$this->get_lngtext('LangTagCreditorAcceptMultiple'), $this->content);
		$this->content= str_replace('{GSSE_INCL_SEPAACCEPT}','<br/><br/><h2>'.$this->get_lngtext('LangTagSepaMandat').'</h2><br/><br/>'.$this->get_lngtext('LangTagCreditorAcceptMultiple').'<br/><br/>'.$this->get_lngtext('LangTagLetterAutomatic').'<br/><br/>'.$this->get_lngtext('LangTagSepaMandatIssue'), $this->content);
	} else {
		$this->content= str_replace('{GSSE_INCL_LangTagCreditorAccept}',$this->get_lngtext('LangTagCreditorAccept'), $this->content);
		$this->content= str_replace('{GSSE_INCL_SEPAACCEPT}','<br/><br/><h2>'.$this->get_lngtext('LangTagSepaMandat').'</h2><br/><br/>'.$this->get_lngtext('LangTagCreditorAccept').'<br/><br/>'.$this->get_lngtext('LangTagLetterAutomatic').'<br/><br/>'.$this->get_lngtext('LangTagSepaMandatIssue'), $this->content);
	}
	$this->content= str_replace('{GSSE_INCL_CREDITOR}', $this->get_setting('edShopCompany_Text'), $this->content);
	$this->content= str_replace('{GSSE_INCL_CREDITORNUMBER}', $this->get_setting('edCreditorIdentifier_Text'), $this->content);
	$this->content= str_replace('{GSSE_INCL_CITYDATEFIRM}', $this->get_setting('edShopCity_Text').', '.date('d.m.Y').' {GSSE_INCL_ACCOUNTHOLDER1}', $this->content); 

	$this->content = str_replace('{GSSE_INCL_ACCOUNTHOLDER}', $customer['firstname'].' '.$customer['lastname'], $this->content);
	$this->content = str_replace('{GSSE_INCL_STREET}', $customer['street'].' '.$customer['street2'], $this->content);
	$this->content = str_replace('{GSSE_INCL_ZIP}', $customer['zip'], $this->content);
	$this->content = str_replace('{GSSE_INCL_CITY}', $customer['city'], $this->content);
	$this->content = str_replace('{GSSE_INCL_STATE}', $customer['areaName'], $this->content);
	$this->content = str_replace('{GSSE_INCL_ACCOUNTHOLDER1}', $customer['firstname'].' '.$customer['lastname'], $this->content);
	$this->content = str_replace('{GSSE_INCL_FinancialInstitution}', $customer['financialinstitution'], $this->content);
	$this->content = str_replace('{GSSE_INCL_ACCOUNTNUMBER}', $customer['iban'], $this->content);
	//$this->content = str_replace('{GSSE_INCL_BANKNUMBER}', $customer['bic'], $this->content);
} else {
	$this->content = str_replace('{GSSE_FUNC_SEPAMANDAT}', '', $this->content);
}
?>