<?php
//session_start();
//chdir("../../");
//include_once("inc/class.shopengine.php");
//include_once("inc/class.order.php");
//$orderOrd = new Order();
//$orderOrd = unserialize($_SESSION['order']);
$basket = $order->getBasket();
$delivery = $order->getDelivery();
$payment = $order->getPayment();
$su = new gs_shopengine();
//ob_start();
if(count($basket) == 0)
{
	echo json_encode("Basket empty");
}

function splitStreet($street)
{
   preg_match("/^([^0-9]+)[ \t]*([-\w^.]+)[, \t]*([^0-9]+.*)?\$/", $street, $matches);
   unset($matches[0]); // Erstes Element entfernen
   $parts = array_reverse($matches);

	$current = 'care_of';
	$splittedStreet = array(
		'street_name'   => '',
		'home_number' => '',
		'care_of'       => ''
	);
	foreach ($parts as $value) {
		if ('care_of' == $current) {
			if (is_numeric(substr($value, 0, 1))) {
				$current = 'home_number';
			}
		}
		if ('home_number' == $current && false === is_numeric(substr($value, 0, 1))) {
			$current = 'street_name';
		}
		$splittedStreet[$current] = trim($value . ' ' . $splittedStreet[$current]);
	}
	return $splittedStreet;
}

require_once 'inc/paymorrow/include/paymorrowProxyUtil.php';

	$todayDateTime = date("c");
	$todayDate = date("1980-m-d");

	$request = new paymorrowOrderRequestType(); //t.XMLtoObject("");
	$request->requestMerchantId = $su->get_setting('paymorrowMerchantID_Text');//"gssoftwaretest"; //
	$request->requestId=time();
	$request->requestTimestamp=$todayDateTime;
	$request->requestLanguageCode="de";

	$orderPaymorrow = new OrderType();
	$orderPaymorrow->orderId=time();
	$orderPaymorrow->orderTimestamp=$todayDateTime;
	#$order->orderShoppingDuration=50;
	#$order->orderCheckoutDuration=50;
	#$order->orderSalesChannelId="web sales";

	$customerPaymorrow = new customerType();
	// Customer ID
	if(isset($_SESSION['login']))
	{
		$customerPaymorrow->customerId = $_SESSION['login']['cusIdNo'];
	}
	else
	{
		$customerPaymorrow->customerId = "";
	}
	
	// Customer IP-Adresse
	if (! isset($_SERVER['HTTP_X_FORWARDED_FOR'])) 
	{
		$client_ip = $_SERVER['REMOTE_ADDR'];
	}
	else 
	{
		$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	$customerPaymorrow->customerIPAddress = $client_ip;
	$customerPaymorrow->customerGroupId = "1";
	// Person or Firm
	if(isset($_POST['company']))
	{
		$customerPaymorrow->orderCustomerType="COMPANY";
	}
	else
	{
		$customerPaymorrow->orderCustomerType="PERSON";
	}
	
	$customerPaymorrow->customerPreferredLanguage="de";

	$customerPersonalDetails = new customerPersonalDetailsType();
	$customerPersonalDetails->customerGivenName = $customer['firstname'];//"Michael";
	$customerPersonalDetails->customerSurname = $customer['lastname'];//"Smith";
	$customerPersonalDetails->customerEmail = $customer['cust_email'];//"mic.smith@domain.com";
	
	if(!isset($customer['cusBirthday']))
	{
		$birthday = "01.01.1970";
	}
	else
	{
		if($customer['cusBirthday'] <> ''){
			$birthday = $customer['cusBirthday'];
		} else {
			$birthday = "01.01.1970";
		}
	}
	$format_birthday = explode('.',$birthday);
	//Datumformat muss so sein "1971-01-05"
	$customerPersonalDetails->customerDateOfBirth = $format_birthday[2].'-'.$format_birthday[1].'-'.$format_birthday[0];// $_GET['birth'];//$todayDate;
	if(isset($customer['cusPhone'])){
		$customerPersonalDetails->customerPhoneNo = $customer['cusPhone'];
	} else {
		$customerPersonalDetails->customerPhoneNo = '';
	}
	
	$customerPaymorrow->customerPersonalDetails=$customerPersonalDetails;

	$customerAddress = new AddressType();
	$address = splitStreet($customer['street']);
	$customerAddress->addressStreet = $address['street_name'];//"Augustaanlage";
	$customerAddress->addressHouseNo = $address['home_number'] . $address['care_of'];//"95";
	//activate the following line to get declined transaction
	//$customerAddress->addressDepartment="decline";
	$customerAddress->addressPostalCode = $customer['zip'];//"68165";
	$customerAddress->addressLocality = $customer['city'];//"Mannheim";
	$customerAddress->addressCountryCode = $customer['stateISO'];//"DE";

	$customerPaymorrow->customerAddress=$customerAddress;

	// Abweichende Lieferadresse
	if(isset($customer['UseShippingAddress']))
	{
		$addressShipmentType = new AddressType();
		$deladdress = splitStreet($customer['deliverstreet']);
		$addressShipmentType->addressStreet = $deladdress['street_name'];//"Hafenstr.";
		$addressShipmentType->addressHouseNo = $deladdress['home_number'] . $deladdress['care_of'];//"41";
		$addressShipmentType->addressPostalCode = $customer['deliverzip'];//"68159";
		$addressShipmentType->addressLocality = $customer['delivercity'];//"Mannheim";
		$addressShipmentType->addressCountryCode = $customer['stateISO'];//"DE";
		$orderPaymorrow->orderShippingAddress=$addressShipmentType;
	}

	$orderShipmentDetails = new OrderShipmentDetailType();
	$orderShipmentDetails->shipmentMethod = "STANDARD";
	$orderShipmentDetails->shipmentProvider = $delivery['delivName'];//"DHL";
	$orderPaymorrow->orderShipmentDetails=$orderShipmentDetails;

	// Bestellte Artikel
	if(isset($basket))
	{		
		$bmax = count($basket);
		if($bmax > 0)
		{
			for($b = 0; $b < $bmax; $b++)
			{
				$vat_rate = explode('.',$basket[$b]['art_vatrate']);
				$itemsArray[] = array(
					"itemQuantitiy"			 => $basket[$b]['art_count'],
					"itemArticleId"			 => $basket[$b]['art_num'],
					"itemDescription"		 => $basket[$b]['art_title'],
					"itemCategory"			 => "MISC",
					"itemUnitPrice"			 => $basket[$b]['art_price'],
					"itemVatRate"			 => $vat_rate[0],
					"itemAmountInclusiveVAT" => true
				);
			}
		}
	}
	
	//Versandkosten als Artikel hinzufügen
	if($delivery['delivTotal'] > 0){
			$itemsArray[] = array(
				"itemQuantitiy"			 => 1,
				"itemArticleId"			 => "Versandkosten",
				"itemDescription"		 => $delivery['delivName'],
				"itemCategory"			 => "MISC",
				"itemUnitPrice"			 => $delivery['delivTotal'],
				"itemVatRate"			 => $vat_rate[0],
				"itemAmountInclusiveVAT" => true
			);
	}
	
	// Paymorrow Gebühr als Artikel hinzufügen
	if($payment['paymTotal'] > 0){
			$itemsArray[] = array(
				"itemQuantitiy"			 => 1,
				"itemArticleId"			 => "Zuahlungsgebühren",
				"itemDescription"		 => $payment['paymName'],
				"itemCategory"			 => "MISC",
				"itemUnitPrice"			 => $payment['paymTotal'],
				"itemVatRate"			 => $vat_rate[0],
				"itemAmountInclusiveVAT" => true
			);
	}

	$vatAmount19 = 0;
	$vatAmount7  = 0;
	//$vatAmount0  = 0;
	$j = 0;
	$orderItemsArray = array();
	$orderAmountVATArray = array();
	
	while($itemsArray[$j] != NULL)
	{
		//compilation of ordered items (orderItems)
		$orderItems = new orderItemType();
		$orderItems->itemId 				= $j+1;
		$orderItems->itemQuantity			= $itemsArray[$j]['itemQuantitiy'];  // quantity
		$orderItems->itemArticleId			= $itemsArray[$j]['itemArticleId'];  // product id
		$orderItems->itemDescription		= $itemsArray[$j]['itemDescription']; // product description 
		$orderItems->itemCategory			= $itemsArray[$j]['itemCategory']; // paymorrow category type
		$orderItems->itemUnitPrice			= $itemsArray[$j]['itemUnitPrice']; // product unit price
		$orderItems->itemCurrencyCode		= "EUR"; // currency
		$orderItems->itemVatRate			= $itemsArray[$j]['itemVatRate']; // vat rate
		$orderItems->itemExtendedAmount		= $itemsArray[$j]['itemQuantitiy'] * $itemsArray[$j]['itemUnitPrice']; // total price of the same article
		$orderItems->itemAmountInclusiveVAT	= $itemsArray[$j]['itemAmountInclusiveVAT']; // product incl. oder excl. vat?
		$orderItemsArray[$j] 				= $orderItems;
		//calculation of VAT values (orderVatRate)
		if($orderItems->itemVatRate	== 19){
			$orderPaymorrow->orderAmountVATTotal 	   += round((($orderItems->itemExtendedAmount*$orderItems->itemVatRate)/(100+$orderItems->itemVatRate)), 2);
			$vatAmount19 					   += round((($orderItems->itemExtendedAmount*$orderItems->itemVatRate)/(100+$orderItems->itemVatRate)), 2);
		}elseif($orderItems->itemVatRate == 7){
			$orderPaymorrow->orderAmountVATTotal 	   += round((($orderItems->itemExtendedAmount*$orderItems->itemVatRate)/(100+$orderItems->itemVatRate)), 2);
			$vatAmount7 					   += round((($orderItems->itemExtendedAmount*$orderItems->itemVatRate)/(100+$orderItems->itemVatRate)), 2);
		}elseif($orderItems->itemVatRate == 0){
			$orderPaymorrow->orderAmountVATTotal 	   += round((($orderItems->itemExtendedAmount*$orderItems->itemVatRate)/(100+$orderItems->itemVatRate)), 2);
			$vatAmount0 					   += round((($orderItems->itemExtendedAmount*$orderItems->itemVatRate)/(100+$orderItems->itemVatRate)), 2);
		}
		//calculation of gross and net order value
		$orderPaymorrow->orderAmountNet		+= round((($orderItems->itemExtendedAmount*100)/(100+$orderItems->itemVatRate)), 2); // Nettopreis der gesammten Bestellung
		$orderPaymorrow->orderAmountGross	+= $orderItems->itemExtendedAmount; // Bruttopreis der gesammten Bestellung
		$j++;
	}
	
	
	$orderPaymorrow->orderItems=$orderItemsArray;
	
	//compilation of VAT values (orderVatRate)
	$k = 0;
	if ($vatAmount19 != 0){
		$orderVatRateType19 = new orderVatRate();
		$orderVatRateType19->vatRate			= 19;
		$orderVatRateType19->orderVatAmount = $vatAmount19;
		$orderAmountVATArray[$k] = $orderVatRateType19;		
		$k++;
	}
	if($vatAmount7 != 0){
		$orderVatRateType7 = new orderVatRate();
		$orderVatRateType7->vatRate			= 7;
		$orderVatRateType7->orderVatAmount = $vatAmount7;
		$orderAmountVATArray[$k] = $orderVatRateType7;	
		$k++;
	}
	if($vatAmount0 == 0){
		$orderVatRateType0 = new orderVatRate();
		$orderVatRateType0->vatRate		    = 0;
		$orderVatRateType0->orderVatAmount = 0;
		//$orderAmountVATArray[$k] = $orderVatRateType0;	
		$k++;
	}
	$orderAmountVAT = new orderAmountVatType();
	$orderAmountVAT->orderVatRate=$orderAmountVATArray;
	$orderPaymorrow->orderAmountVAT=$orderAmountVAT;	

	$orderPaymorrow->orderCurrencyCode = $su->get_setting('edCurrencySymbol_Text');//"EUR";
	
	$requestMerchantUrls = new requestMerchantUrlType();
	//$requestMerchantUrls->merchantSuccessUrl = $su->get_setting('edAbsoluteShopPath_Text') . "index.php?page=thankyou";//"www.merchantshop.com\\orderConfirm.php";
	$requestMerchantUrls->merchantSuccessUrl = $su->shopurl . "index.php?page=buy&pp_status=ok";//"www.merchantshop.com\\orderConfirm.php";
	//$requestMerchantUrls->merchantErrorUrl=$su->get_setting('edAbsoluteShopPath_Text') . "index.php?page=buy&su_status=cancel";//"www.yahoo.com";
	$requestMerchantUrls->merchantErrorUrl=$su->shopurl . "index.php?page=buy&pp_status=cancel";//"www.yahoo.com";
	//$requestMerchantUrls->merchantPaymentMethodChangeUrl=$su->get_setting('edAbsoluteShopPath_Text') . "index.php?page=buy";//"www.paymentchange.com";
	$requestMerchantUrls->merchantPaymentMethodChangeUrl=$su->shopurl . "index.php?page=buy";//"www.paymentchange.com";
	$requestMerchantUrls->merchantNotificationUrl="www.notify.com";
	$request->requestMerchantUrls=$requestMerchantUrls;
	$orderPaymorrow->orderCustomer=$customerPaymorrow;

	$request->order=$orderPaymorrow;

	//echo"<pre>";
	//print_r($request);
	//echo"</pre>";
	$paymorrowOrderResponse = sendRequestToPaymorrow($request, $su->get_setting('paymorrowPasswort_Text'));//'gssoftwarekey');
	//print_r($paymorrowOrderResponse);
	//echo"</pre>";
	//echo "RESPONSE STATUS:<br>";
	//echo 'responseResultCode'.$paymorrowOrderResponse->responseResultCode;
	
	//ob_end_clean();

	// Redirect zu Paymorrow
	if($paymorrowOrderResponse->responseResultCode == 'ACCEPTED')
	{		
		echo json_encode("Location: " . $paymorrowOrderResponse->responseResultURL);		
	}
	else
	{
		if ($paymorrowOrderResponse->responseError) 
		{
			echo " error type=".$paymorrowOrderResponse->responseError->responseErrorType.
				",error no=".$paymorrowOrderResponse->responseError->responseErrorNo.
				",erro msg=".$paymorrowOrderResponse->responseError->responseErrorMessage;
		}	
	}
?>