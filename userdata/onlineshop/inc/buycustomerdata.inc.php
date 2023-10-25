<?php 
include_once('class.order.php');
$order = New Order;
//session_start();
$order = unserialize($_SESSION['order']);
$customer = $order->getCustomer();
$payment = $order->getPayment();
$delivery = $order->getDelivery();
$buycustomerdatahtml = $this->gs_file_get_contents('template/buy3userdata.html');
$aBuy3UTags = $this->get_tags_ret($buycustomerdatahtml);
$buycustomerdatahtml= $this->parse_texts($aBuy3UTags,$buycustomerdatahtml);
$buy3uditem = $this->gs_file_get_contents('template/buy3userdataitem.html');

/*Begin CusNo*/
if(isset($customer['cusId'])){
	$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTNUMBER}', $customer['cusId'], $buycustomerdatahtml);
} else {
	$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTNUMBER}', '', $buycustomerdatahtml);
}	
/*End CusNo*/

/*Begin Company*/
if(isset($customer['company'])){
	$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTFIRM}', $customer['company'], $buycustomerdatahtml);
} else {
	$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTFIRM}', '', $buycustomerdatahtml);
}
/*End Company*/

/*Begin VatId*/
if(isset($customer['firmVATId'])){
	$buycustomerdatahtml= str_replace('{GSSE_INCL_VATID}', $customer['firmVATId'], $buycustomerdatahtml);
} else {
	$buycustomerdatahtml= str_replace('{GSSE_INCL_VATID}', '', $buycustomerdatahtml);
}	
/*End VatId*/

/*Begin Mr or Mrs, Firsname, Lastname*/
$buycustomerdatahtml= str_replace('{GSSE_INCL_MRORMRS}', $customer['mrormrsText'], $buycustomerdatahtml);
$buycustomerdatahtml= str_replace('{GSSE_INCL_FIRSTNAME}', $customer['firstname'], $buycustomerdatahtml);
$buycustomerdatahtml= str_replace('{GSSE_INCL_LASTNAME}', $customer['lastname'], $buycustomerdatahtml);
/*End Mr or Mrs, Firsname, Lastname*/

/*Begin Kontakt*/
$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTEMAIL}', $customer['cust_email'], $buycustomerdatahtml);
if(isset($customer['cusBirthday'])){
	$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTBIRTHDAY}', $customer['cusBirthday'], $buycustomerdatahtml);
} else {
	$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTBIRTHDAY}', '', $buycustomerdatahtml);
}
if(isset($customer['cusPhone'])){
	$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTPHONE}', $customer['cusPhone'], $buycustomerdatahtml);
} else {
	$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTPHONE}', '', $buycustomerdatahtml);
}
if(isset($customer['cusMobil'])){
	$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTMOBIL}', $customer['cusMobil'], $buycustomerdatahtml);
} else {
	$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTMOBIL}', '', $buycustomerdatahtml);
}
if(isset($customer['cusFax'])){
	$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTFAX}', $customer['cusFax'], $buycustomerdatahtml);
} else {
	$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTFAX}', '', $buycustomerdatahtml);
}
$newsletter = '';
if(isset($_SESSION['login']['ok'])){
	$kontaktlink = $this->gs_file_get_contents('template/link.html');
	$kontaktlink = str_replace('{GSSE_INCL_LINKURL}', 'index.php?page=addressdata_login', $kontaktlink);
	$kontaktlink = str_replace('{GSSE_INCL_LINKCLASS}', '', $kontaktlink);
	$kontaktlink = str_replace('{GSSE_INCL_LINKTARGET}', '', $kontaktlink);
	$kontaktlink = str_replace('{GSSE_INCL_LINKNAME}', $this->get_lngtext('LangTagTextChgCData'), $kontaktlink);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_KONTAKTLINK}', $kontaktlink, $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_BILLADDRLINK}', $kontaktlink, $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_SHIPADDRLINK}', $kontaktlink, $buycustomerdatahtml);
	
	$passlink = $this->gs_file_get_contents('template/link.html');
	$passlink = str_replace('{GSSE_INCL_LINKURL}', 'index.php?page=password_popup', $passlink);
	$passlink = str_replace('{GSSE_INCL_LINKCLASS}', '', $passlink);
	$passlink = str_replace('{GSSE_INCL_LINKTARGET}', '', $passlink);
	$passlink = str_replace('{GSSE_INCL_LINKNAME}', $this->get_lngtext('LangTagTextChangePassword'), $passlink);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_PASSLINK}', $passlink, $buycustomerdatahtml);
	
	$newsletterlink = $this->gs_file_get_contents('template/link.html');
	$newsletterlink = str_replace('{GSSE_INCL_LINKURL}', 'index.php?page=emailform', $newsletterlink);
	$newsletterlink = str_replace('{GSSE_INCL_LINKCLASS}', '', $newsletterlink);
	$newsletterlink = str_replace('{GSSE_INCL_LINKTARGET}', '', $newsletterlink);
	$newsletterlink = str_replace('{GSSE_INCL_LINKNAME}', $this->get_lngtext('LangTagEdit'), $newsletterlink);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_NEWSLETTERLINK}', $newsletterlink, $buycustomerdatahtml);
} else {
	// Nur registrierte Kunden können die Newsletter empfangen
	$newsletter = $this->get_lngtext('LangTagNewsletterForRegCustomer');
	$buycustomerdatahtml= str_replace('{GSSE_INCL_KONTAKTLINK}', '', $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_PASSLINK}', '', $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_NEWSLETTERLINK}', '', $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_BILLADDRLINK}', '', $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_SHIPADDRLINK}', '', $buycustomerdatahtml);
}
$buycustomerdatahtml= str_replace('{GSSE_INCL_NEWSLETTER}', $newsletter, $buycustomerdatahtml);
/*End Kontakt*/

/*Begin Address*/
$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTSTREET}', $customer['street'], $buycustomerdatahtml);
$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTSTREET2}', $customer['street2'], $buycustomerdatahtml);
$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTZIP}', $customer['zip'], $buycustomerdatahtml);
$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTCITY}', $customer['city'], $buycustomerdatahtml);
$buycustomerdatahtml= str_replace('{GSSE_INCL_CUSTSTATE}', $customer['areaName'], $buycustomerdatahtml);
/*End Address*/

/*Begin Lieferaddresse*/
if(isset($customer['delivermrormrs'])){
	$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVMRORMRS}', $customer['delivermrormrs'], $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVFIRSTNAME}', $customer['deliverfirstname'], $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVLASTNAME}', $customer['deliverlastname'], $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVSTREET}', $customer['deliverstreet'], $buycustomerdatahtml);
	if(isset($customer['deliverstreet2'])){
		$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVSTREET2}', $customer['deliverstreet2'], $buycustomerdatahtml);
	} else {
		$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVSTREET2}', '', $buycustomerdatahtml);
	}
	$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVZIP}', $customer['deliverzip'], $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVCITY}', $customer['delivercity'], $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVSTATE}', $customer['areaName'], $buycustomerdatahtml);
} else {
	$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVMRORMRS}', $customer['mrormrsText'], $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVFIRSTNAME}', $customer['firstname'], $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVLASTNAME}', $customer['lastname'], $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVSTREET}', $customer['street'], $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVSTREET2}', $customer['street2'], $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVZIP}', $customer['zip'], $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVCITY}', $customer['city'], $buycustomerdatahtml);
	$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVSTATE}', $customer['areaName'], $buycustomerdatahtml);
}
/*End Lieferaddresse*/

/*Begin Delivery*/
	$buycustomerdatahtml= str_replace('{GSSE_INCL_DELIVNAME}',$delivery['delivName'],$buycustomerdatahtml);
/*End Delivery*/	
	
/*Begin Payment*/
	$buycustomerdatahtml= str_replace('{GSSE_INCL_PAYMNAME}',$payment['paymName'],$buycustomerdatahtml);
/*End Payment*/
	
$this->content = str_replace('{GSSE_FUNC_BUYCUSTOMERDATA}', $buycustomerdatahtml, $this->content);
?>