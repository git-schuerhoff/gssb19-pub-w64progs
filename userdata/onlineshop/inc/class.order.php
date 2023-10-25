<?php
/************************************************************/
/*                                                          */
/* Class: Order                                             */
/*                                                          */
/* Klasse für den Bestellvorgang                            */
/*                                                          */
/************************************************************/
//include_once("inc/class.shopengine.php");

class Order{

    private $lngID = 'deu';
    private $cntID = 'deu';
    private $downloaddir = '';
    private $downloadtxt = '';
    private $Basket;
    private $Delivery;
    private $Payment;
	private $Currency;
    private $Customer;
    private $Info;
    private $AreaID;
    private $AreaName;
    public $Discount = 0;
    private $DiscountCheck = false;
    public $DiscountPercent = 0;
    private $PaymentTotal;
    private $VatTotal;
    private $EmailFormat;
    private $vat; // array(array("vatrate" => $aMyVats[$v],"vattotal" => 0));
    private $OrderNumber;
    
    protected $pplink;
	private $pperr;
	private $pptable;
	private $pplanguageIdNo;
	public $InvoiceMail;
	public $ppplus;
	public $ppLastError;
	public $ppLastErrorText;
	public $dbgLastSQL;
	public $ItemCount = 0;
	public $ItemsTotal = 0; // Summe Artikel
	public $ItemsTotalTemp = 0;
	public $ItemsTotalWithRabatt = 0;
	public $BasketWeight = 0;
	public $BasketInvoiceTotal = 0; // Rechnungssumme
	public $isDownloadItems = false;
	public $isMixBasket = false;
	public $guest = false;
    var $se;
    public function __construct() {
        // 
        require_once("inc/class.shopengine.php");
        $this->se = new gs_shopengine();
        
        $this->lngID = $_SESSION['slc'];
		$this->cntID = $_SESSION['cnt'];
		$this->Currency = $this->se->get_setting('edCurrencySymbol_Text');
        $this->setInfo();
		$this->vat = $this->se->get_vats();
        /*$this->setBasket($ap);
        $this->setCustomer($ap);
        */
        // Defaultwerte für Payment und Delivery
        $this->setDefaultArea();
        $this->set_defaultPayment();
        $this->set_defaultDelivery();
        if(isset($_SESSION['login']))
        {
            if($_SESSION['login']['ok'])
            {
                $this->downloaddir = 'customer_' . $_SESSION['login']['cusIdNo'];
                $this->downloadtxt = $this->se->get_lngtext('LangTagLongTextDownload');
            }
        }
    }
    
    public function getOrderNumber(){
    	return $this->OrderNumber;
    }
    
	public function getCurrency(){
		return $this->Currency;
	}
	
	public function setEmailFormat($emailformat){
		$this->EmailFormat = $emailformat;
	}
	
	public function getEmailFormat(){
		return $this->EmailFormat;
	}
	
    public function getAreaName(){
    	return $this->AreaName;
    }
    
    public function setDefaultArea(){
    	$dbh = $this->se->db_connect();
    	$sql = "SELECT AreaID, Text AreaName FROM " . $this->se->dbtoken . "addressarea LIMIT 1";
    	$erg = mysqli_query($dbh,$sql);
    	$res = mysqli_fetch_assoc($erg);
    	$this->AreaID = $res['AreaID'];
    	$this->AreaName =$res['AreaName'];
    }
    
    public function getBasket(){
        return $this->Basket;
    }
    
    public function getBasketItemsTotal(){
        return $this->Basket['ItemsTotal'];
    }
    
    public function getCustomer(){
        return $this->Customer;
    }
    
    public function getCustomerByEmail($email){
        $dbh = $this->se->db_connect();
        $sql = 'SELECT IF(cusFirmname="",0,1) PrivatOderFirma, cusFirmname AS company, cusFirmVATId AS firmVATId, ' .
               'IF(cusTitle="Herr",1,2) AS mrormrs, cusFirstName AS firstname, cusLastName AS lastname, cusStreet AS street, ' .
               'cusStreet2 AS street2, cusZipCode AS zip, cusCity AS city, cusCountry AS areaID, ' .
               'cusEMail AS cust_email, cusPhone AS phone, cusIdNo FROM ' . $this->se->dbtoken . 'customer WHERE cusEmail = "' . $email . '"';
        $erg = mysqli_query($dbh,$sql);
        $customer = mysqli_fetch_assoc($erg);
        mysqli_free_result($erg);
		mysqli_close($dbh);
        if(is_array($customer)){
            //$this->setCustomer($customer);
			$this->getCustomerDiscountPercent();
            return $this->Customer;
        } else {
            return 'NULL';
        }
    }
    
    public function createCustomer($customer){
        $con = $this->se->db_connect();
		if(!isset($customer['cust_pass'])){
			$customer['cust_pass'] == '';
		}
		if($customer['cust_pass'] == ''){
			$randpassword = $this->getRandomCustomerPassword();
			$customer['cust_pass'] = $randpassword;
		}
        // Prüfen, ob einen Kundennummer schon in Bestellungen verarbeitet wurde
        $sqlStartNo = "SELECT MAX(CAST(ordCustomerId AS SIGNED))+1 AS SettingMemo FROM ".DBToken."order";
        $qry = @mysqli_query($con,$sqlStartNo);
        $startNo = mysqli_fetch_assoc($qry);
        if($startNo['SettingMemo'] == '1'){// Startnummer aus Memo nehmen, wenn ein Nummer hinterlegt ist.
        	$sqlStartNo = "SELECT SettingMemo FROM ".DBToken."settingmemo WHERE SettingName = 'memoCustomerStartNo'";
        	$qry = @mysqli_query($con,$sqlStartNo);
        	$startNo = @mysqli_fetch_assoc($qry);
        }
        if($startNo['SettingMemo'] <> '1'){// Nächste Kundennummer aus Ordertable ermitteln.
        	$sqlStartNo = "SELECT MAX(CAST(ordCustomerId AS SIGNED))+1 AS SettingMemo FROM ".DBToken."order";
        	$qry = @mysqli_query($con,$sqlStartNo);
        	$startNo = mysqli_fetch_assoc($qry);
        }
        
        $cusId = $startNo['SettingMemo'];
        $customer['cusId'] = $cusId;
		$this->setCustomer($customer);
        $sql = 'INSERT INTO ' . $this->se->dbtoken . 'customer (cusId, cusFirmname, cusFirmVATId, cusTitle, cusFirstName, cusLastName, cusStreet, cusStreet2, cusZipCode, cusCity, cusCountry, cusEMail, cusPhone, cusPassword, cusEMailFormat, cusDeliverFirmname, cusDeliverTitle, cusDeliverFirstName, cusDeliverLastName, cusDeliverStreet, cusDeliverStreet2, cusDeliverZipCode, cusDeliverCity, cusDeliverCountry, cusData, cusChgTimestamp) VALUES ("'.$cusId.'","'.$customer['company'].'","'.$customer['firmVATId'].'","'.$customer['mrormrsText'].'","'.$customer['firstname'].'","'.$customer['lastname'].'","'.$customer['street'].'","'.$customer['street2'].'","'.$customer['zip'].'","'.$customer['city'].'","'.$customer['areaName'].'","'.$customer['cust_email'].'","'.$customer['phone'].'","'.$customer['cust_pass'].'","html","'.$customer['company'].'","'.$customer['mrormrsText'].'","'.$customer['firstname'].'","'.$customer['lastname'].'","'.$customer['street'].'","'.$customer['street2'].'","'.$customer['zip'].'","'.$customer['city'].'","'.$customer['areaName'].'","",CURRENT_TIMESTAMP)';
        $erg = mysqli_query($con,$sql);
    }
    
    public function getDelivery(){
        return $this->Delivery;
    }
    
    public function getPayment(){
        return $this->Payment;
    }
    
    public function getDiscount(){
    	return $this->Discount;
    }
    
    public function getDiscountPercent(){
    	return $this->DiscountPercent;
    }
    
    public function getInfo(){
        return $this->Info;
    }
    
    public function getFieldnames(){
        return $this->Fieldnames;
    }
    
    public function getAreaID(){
        return $this->AreaID;
    }
    
    public function setAreaID($arID){
        $this->AreaID = $arID;
    }
    
    public function setBasket() {
    	//Nur neu initialisieren, wenn das Array noch nicht existiert
    	if(!is_array($this->Basket)){
			$this->Basket = array();
    	}
	}
    
	public function updateBasket($newBasket){
		$this->Basket = $newBasket;
		$this->updateItemsTotal();
	}
	
	public function delBasket(){
		$this->Basket = array();
		$this->DiscountCheck = false;
	}
	
	public function getItemsTotal(){
		return $this->ItemsTotal;
	}
	
	public function setItemsTotalTemp(){
		$this->ItemsTotalTemp = 0;
		foreach ($this->Basket as $val){
			$this->ItemsTotalTemp+= $val['art_price']*$val['art_count'];
		}
	}
	public function getBasketInvoiceTotal(){
		return $this->BasketInvoiceTotal;
	}
	
	public function getItemsTotalWithRabatt(){
		return $this->ItemsTotalWithRabatt;
	}
	
	public function updateItemsTotal(){
		$con= $this->se->db_connect();
		$this->ItemsTotal=0;
		$this->isDownloadItems = False;
		$this->isMixBasket = False;
		$aprice = 0;
		$defaultprice = 0;
		$this->ItemsTotalWithRabatt = 0;
		$this->BasketWeight = 0;
		//$this->getItemDiscountPercent();
		$this->VatTotal = 0;
		if(is_array($this->Basket)){
			$for_max = count($this->Basket);
		} else {
			$for_max = 0;
		}
		
		for($v = 0; $v < count($this->vat); $v++){
			$this->vat[$v]['vattotal'] = 0;
		}
		
		for($i = 0; $i < $for_max; $i++) {
			if(isset($this->Basket[$i])){
				if($this->Basket[$i]['art_isdownload'] == 'Y'){
					$this->isDownloadItems = True;
				} 
				$aprice = $aprice + $this->Basket[$i]['art_price'];
				if($this->Basket[$i]['art_discountcheck'] === false) {
					$this->Basket[$i]['art_price'] = $this->getDiscountPrice($i);
					$this->Basket[$i]['art_discountcheck'] = true;
				}
				$defaultprice= $defaultprice+ $this->Basket[$i]['art_defprice']*$this->Basket[$i]['art_count'];
				$this->Basket[$i]['art_totalprice'] = $this->Basket[$i]['art_price']*$this->Basket[$i]['art_count'];
				$this->ItemsTotalWithRabatt += $this->Basket[$i]['art_totalprice'];
				$this->ItemsTotal += $this->Basket[$i]['art_totalprice'];
				
				$this->BasketWeight += $this->Basket[$i]['art_weight']*$this->Basket[$i]['art_count'];
				$sql = "SELECT itemInStockQuantity, itemShipmentStatus FROM ".DBToken."itemdata WHERE itemItemNumber = '".$this->Basket[$i]['art_num']."'";
				$rs = @mysqli_query($con,$sql);
				$obj = @mysqli_fetch_object($rs);
				if ($obj<>null) {
					$stock = $obj->itemInStockQuantity;
					$status = $obj->itemShipmentStatus;
				} else {
					$stock=null;
					$status=null;
				}
				
				if($stock<=0 && $stock!=null){
					$stock = '0';
				}
				
				if($status!=null && $status != -1 && $status != "0"){
					$sql = "SELECT * from ".DBToken."availability WHERE avaId='".$status."'";
					$rs = @mysqli_query($con,$sql);
					$obj = @mysqli_fetch_object($rs);
					$avaDescription = $obj->avaDescription;
				} else {
					$avaDescription = "";
				}
				$this->Basket[$i]['art_avail'] = $avaDescription;
				
				// $this->vat
				for($v = 0; $v < count($this->vat); $v++){
					if($this->vat[$v]['vatrate'] == $this->Basket[$i]['art_vatrate']){
						if($this->se->get_setting('cbNetPrice_Checked') == "False"){
							// Brutto
							$vattotal = (($this->Basket[$i]['art_price']*$this->Basket[$i]['art_count'])/(100 + $this->vat[$v]['vatrate']))*$this->vat[$v]['vatrate'];
							$this->vat[$v]['vattotal'] = $this->vat[$v]['vattotal'] + (($this->Basket[$i]['art_price']*$this->Basket[$i]['art_count'])/(100 + $this->vat[$v]['vatrate']))*$this->vat[$v]['vatrate'];
							//Nettopreis = Preis - MwSt.
							$this->Basket[$i]['art_netprice'] = $this->Basket[$i]['art_price'] - $this->vat[$v]['vattotal'];
							//Bruttopreis = Preis
							$this->Basket[$i]['art_brutprice'] = $this->Basket[$i]['art_price'];
						} else {
							// Netto
							$vattotal = (($this->Basket[$i]['art_price']*$this->Basket[$i]['art_count'])/100)*$this->vat[$v]['vatrate'];
							$this->vat[$v]['vattotal'] = $this->vat[$v]['vattotal'] + (($this->Basket[$i]['art_price']*$this->Basket[$i]['art_count'])/100)*$this->vat[$v]['vatrate'];
							//Nettopreis = Preis
							$this->Basket[$i]['art_netprice'] = $this->Basket[$i]['art_price'];
							//Bruttopreis = Preis + MwSt.
							$this->Basket[$i]['art_brutprice'] = $this->Basket[$i]['art_price'] + $this->vat[$v]['vattotal'];
						}
						$this->Basket[$i]['art_vat'] = $vattotal;//$this->vat[$v]['vattotal'];
						
					}
					$this->VatTotal = $this->VatTotal + $vattotal;//$this->vat[$v]['vattotal'];
				}
			}	
		}
		
		for($i = 0; $i < $for_max; $i++) {
			if($this->Basket[$i]['art_isdownload'] == 'N' && $this->isDownloadItems){
				$this->isMixBasket = True;
			} 
		}	
		
		if($for_max > 0){
			//Zahlungsart Geb�hr
			/*if($this->Payment['paymInfo']['paymCashDiscountPercent']>0){
				$this->Payment['paymTotal'] = ($this->ItemsTotal/100)*$this->Payment['paymInfo']['paymCashDiscountPercent'];
				$this->ItemsTotalWithRabatt = $this->ItemsTotalWithRabatt + $this->Payment['paymTotal'];
			}
			if($this->Payment['paymInfo']['paymCharge']>0){
				$this->Payment['paymTotal'] = $this->Payment['paymInfo']['paymCharge'];
				$this->ItemsTotalWithRabatt = $this->ItemsTotalWithRabatt + $this->Payment['paymTotal'];
			}*/
			$handlingamt = 0;
			$discount = 0;
			if($this->Payment['paymInfo']['paymUseCashDiscount'] != 'Y'){
				if($this->Payment['paymInfo']['paymCharge']!= 0) {
					$handlingamt = $this->Payment['paymInfo']['paymCharge'];
				}
				if($this->Payment['paymInfo']['paymChargePercent'] != 0) {
					$handlingamt += round((($this->ItemsTotal) / 100) * $this->Payment['paymInfo']['paymChargePercent'],2);//Auf Bruttowarenwert
				}
			} else {
				if($this->Payment['paymInfo']['paymCashDiscount'] != 0){
					$discount = $this->Payment['paymInfo']['paymCashDiscount']* -1;
				}
				if($this->Payment['paymInfo']['paymCashDiscountPercent'] != 0){
					$discount -= round((($this->ItemsTotal) / 100) * $this->Payment['paymInfo']['paymCashDiscountPercent'],2);//Auf Bruttowarenwert (Rabatt anstatt Skonto!)
				}
			}
			$this->Payment['paymInfo']['handlingamt'] = $handlingamt;
			$this->Payment['paymInfo']['discount'] = $discount;
			$this->Payment['paymTotal'] = $handlingamt + $discount;
			//Versandkosten 
			$this->Delivery['delivTotal'] = $this->se->get_shipcost($this->Delivery['delivID'],$this->Delivery['delivAreaID'],$this->ItemsTotal,$this->BasketWeight);
			$this->ItemsTotalWithRabatt = $this->ItemsTotalWithRabatt + $this->Delivery['delivTotal']+$this->Payment['paymTotal'];
			//TODO: Zwischensumme
			$this->Discount = ($this->ItemsTotal/100)*$this->DiscountPercent; //- $this->ItemsTotalWithRabatt;//$defaultprice - $aprice;
			//TODO: Enthaltene MwSt. 3*
			
			//Rechnungsbetrag
			if($this->se->get_setting('cbNetPrice_Checked') == "False"){
				// Brutto
				$this->BasketInvoiceTotal = $this->ItemsTotalWithRabatt;
			} else {
				// Netto 
				$this->BasketInvoiceTotal = $this->ItemsTotalWithRabatt + $this->VatTotal;
			}
		}
	}
	
	public function getVat(){
		return $this->vat;
	}
	
	public function getCustDiscount(){
		$con = $this->se->db_connect();
		$sql = "SELECT cusDiscount FROM ".DBToken."customer WHERE cusEMail='".$this->Customer['cust_email']."'";
		$erg = mysqli_query($con,$sql);
		$res = mysqli_fetch_assoc($erg);
		return $res['cusDiscount'];
	}
	
	public function addItem($menge,$defprice,$stdImg,$dpn,$aPrices,$isinitprice,$istrialitem,$billingfreq,$billingfreqtext){
		// Wenn Artikel schon im Warenkorb ist, muss nur Menge aktualisiert werden
		$lFound = false;
		$for_max = count($this->Basket);
		$itemPrice = 0;
		for($i = 0; $i < $for_max; $i++) {
			if($this->Basket[$i]['art_id'] == $_SESSION['aitem']['itemItemId'] && $this->Basket[$i]['art_attr0'] == $_POST['cattr0'] && $this->Basket[$i]['art_attr1'] == $_POST['cattr1'] && $this->Basket[$i]['art_attr2'] == $_POST['cattr2']) {
				//Alten Gesmatpreis abziehen
				$this->ItemsTotal -= round($this->Basket[$i]['art_count']*$this->Basket[$i]['art_price'],2);
				$this->Basket[$i]['art_count'] += $menge;
				//Und neuen Gesmatpreis addieren
				$this->ItemsTotal += round($this->Basket[$i]['art_count']*$this->Basket[$i]['art_price'],2);
				$lFound = True;
			}
		}
		if($lFound === false){
			$itemPrice = $this->get_bulkprice($_SESSION['aitem']['aprices']['abulk'], $menge, $defprice, $_SESSION['aitem']['itemIsAction']);
			$this->Basket[] = array(
				"art_isdownload" => $_SESSION['aitem']['itemIsDownloadArticle'],
				"art_title" => $_SESSION['aitem']['itemItemDescription'],
				"art_vartitle" => $_SESSION['aitem']['itemVariantDescription'],
				"art_id" => $_SESSION['aitem']['itemItemId'],
				"art_num" => $_SESSION['aitem']['itemItemNumber'],
				"art_price" => $itemPrice,
				"art_fromQuant" => 0,
				"art_sprice" => '',
				"art_vatrate" => $this->se->getVatRateFromKey($_SESSION['aitem']['itemVATRate']),
				"art_weight" => $_SESSION['aitem']['itemWeight'],
				"art_count" => $menge,
				"art_img" => $stdImg,
				"art_dpn" => $dpn,
				"art_attr0" => $_POST['cattr0'],
				"art_attr1" => $_POST['cattr1'],
				"art_attr2" => $_POST['cattr2'],
				"art_quants" => $_SESSION['aitem']['aprices']['abulk'],
				"art_textfeld" => $_POST['ctext'],
				"art_checkage" => $_SESSION['aitem']['itemCheckAge'],
				"art_mustage" => $_SESSION['aitem']['itemMustAge'],
				"art_defprice" => $_SESSION['aitem']['aprices']['price'],
				"art_isaction" => $_SESSION['aitem']['itemIsAction'],
				"art_isdecimal" => $_SESSION['aitem']['itemisDecimal'],
				"art_hasdetail" => $_SESSION['aitem']['itemHasDetail'],
				"art_prices" => $aPrices,
				"art_isinitprice" => $isinitprice,
				"art_isttrialitem" => $istrialitem,
				"art_instockqty" => $_SESSION['aitem']['itemInStockQuantity'],
				"art_discount" => 0,
				"art_discountcheck" => false,
				"art_vatkey" => intval($_SESSION['aitem']['itemVATRate']),
				"art_billingfreq" => $billingfreq,
				"art_billingfreqtext" => $billingfreqtext,
				"art_totalprice" => 0,
				"art_avail" => ""
			);

			$this->ItemCount++;
			$this->ItemsTotal += round($menge * $itemPrice,2);
		}
		$this->updateItemsTotal();
	}
	
	public function updateItem($idx,$key,$val){
		$this->Basket[$idx][$key] = $val;
		$this->updateItemsTotal();
	}
	
	public function addCoupon($code,$price){
		$aprices = array("price" => $price,
				"oldprice" => 0,
				"referenceprice" => 0,
				"referencequantity" => 0,
				"referenceunit" => '',
				"actbegindate" => '',
				"actbegintime" => '',
				"actenddate" => '',
				"actendtime" => '',
				"actprice" => '',
				"actnormprice" => '',
				"actshowperiod" => '',
				"actshownormal" => '',
				"abulk" => '',
				"isrental" => '',
				"billingperiod" => '',
				"billingfrequency" => '',
				"initialprice" => '',
				"istrial" => '',
				"trialperiod" => '',
				"trialfrequency" => '',
				"trialprice" => '',
				"rentalruntime" => ''
		);
		$this->Basket[] = array(
				"art_isdownload" => 'N',
				"art_title" => $this->se->get_lngtext('LangTagCoupon') . ' (' . $code . ')',
				"art_vartitle" => '', 
				"art_id" => 0, 
				"art_num" => $code, 
				"art_price" => $price,
				"art_fromQuant" => 0,
				"art_sprice" => '',
				"art_vatrate" => 0, 
				"art_weight" => 0,
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
				"art_defprice" => $price,
				"art_isaction" => '',
				"art_isdecimal" => 0,
				"art_hasdetail" => 'N',
				"art_isinitprice" => '',
				"art_isttrialitem" => '',
				"art_instockqty" => '',
				"art_discount" => 0,
				"art_vatkey" => 0,
				"art_billingfreq" => '',
				"art_billingfreqtext" => '',
				"art_prices" => $aprices
			);
		$this->updateItemsTotal();
		// Coupon Used 
		$con = $this->se->db_connect();
		$sql = "UPDATE ".DBToken."coupon SET coupUsed='Y' WHERE coupCode='".$code."' AND coupValid<>'unlimited'";
		$erg = mysqli_query($con,$sql);
	}
	
	public function deleteItem($idx){
		unset($this->Basket[$idx]);
		$this->updateItemsTotal();
	}
	
    public function setCustomer($Customer){
        //Nur neu initialisieren, wenn das Array noch nicht existiert
        if(!is_array($this->Customer)) {
            $this->Customer = array();
        }
        //Nur Werte setzen, die auch übergeben wurden
        foreach($Customer as $key => $val) {
            $this->Customer[$key] = $val;
        }
    }
    
    public function setDelivery($delivID,$delivName){
        $this->Delivery = array();
        $this->Delivery['delivID'] = $delivID;
        $this->Delivery['delivAreaID'] = $this->AreaID;
        $this->Delivery['delivName'] = $delivName;
        $this->Delivery['delivInfo'] = $this->se->get_shipcosttable($delivID,$this->AreaID);
        $this->Delivery['delivTotal'] = 0;
        $this->updateItemsTotal();
    }
    
    public function set_defaultDelivery(){
    	if($this->AreaID == null){
    		$this->setAreaID(1);
    	}
    	$deliv = $this->se->get_shipment($this->AreaID);
    	$this->setDelivery($deliv[0]['sortid'], $deliv[0]['name']);
    }
    
    public function setPayment($paymID,$paymName,$paymInternalName){
        $this->Payment = array();
        $this->Payment['paymID'] = $paymID;
        $this->Payment['paymAreaID'] = $this->AreaID;
        $this->Payment['paymName'] = $paymName;
        $this->Payment['paymInternalName'] = $paymInternalName;
        $this->Payment['paymInfo'] = $this->get_paymInfo();// array('Charge','ChargePercent','CashDiscount','CashDiscountPercent','UseCashDiscount')
        $this->Payment['paymTotal'] = 0;
        $this->updateItemsTotal();
    }
    
    public function get_paymInfo() {
    	$dbh = $this->se->db_connect();
    	
    	$sql = 'SELECT c.Charge, c.ChargePercent, c.CashDiscount, c.CashDiscountPercent, c.UseCashDiscount ';
    	$sql .= 'FROM ' . $this->se->dbtoken . 'paymentcountry c ';
    	$sql .= 'left join ' . $this->se->dbtoken . 'paymentlanguage l on l.SortId=c.SortId ';
    	$sql .= 'left join ' . $this->se->dbtoken . 'paymentinternalnames n on n.SortId=c.SortId ';
    	$sql .= 'WHERE c.SortId='.$this->Payment['paymID'].' LIMIT 1';
    	
    	$erg = mysqli_query($dbh,$sql);
    	$res = mysqli_fetch_assoc($erg);
    	$paymInfo = Array();
    	
    	$paymInfo['paymCharge'] = $res['Charge'];
    	$paymInfo['paymChargePercent'] = $res['ChargePercent'];
    	$paymInfo['paymCashDiscount'] = $res['CashDiscount'];
    	$paymInfo['paymCashDiscountPercent'] = $res['CashDiscountPercent'];
    	$paymInfo['paymUseCashDiscount'] = $res['UseCashDiscount'];
    	
    	return $paymInfo;
    }
    
    public function set_defaultPayment(){
    	$dbh = $this->se->db_connect();
    	
    	$sql = 'SELECT c.SortId, l.Text1, n.InternalName ';
    	$sql .= 'FROM ' . $this->se->dbtoken . 'paymentcountry c ';
    	$sql .= 'left join ' . $this->se->dbtoken . 'paymentlanguage l on l.SortId=c.SortId ';
    	$sql .= 'left join ' . $this->se->dbtoken . 'paymentinternalnames n on n.SortId=c.SortId ';
    	$sql .= 'WHERE c.UseThisPayment="Y" LIMIT 1';
    	$erg = mysqli_query($dbh,$sql);
    	$res = mysqli_fetch_assoc($erg);
    	$this->setPayment($res['SortId'],$res['Text1'],$res['InternalName']);
    }
    
    public function setPaymemtReturnInfos($paymKey, $paymValue){
        $this->Payment[$paymKey] = $paymValue;
    }
    
    public function setInfo(){
        // Alle hidden Fields werden als Info hier gesetzt
        $this->Info = array();
        $this->Info['shopurl'] = $this->se->get_setting('edAbsoluteShopPath_Text');
        $this->Info['redirect'] = $this->se->get_setting('edAbsoluteShopPath_Text'). 'index.php?page=thankyou';
		$this->Info['redirect_local'] = 'index.php?page=thankyou';
        $this->Info['subject'] = $this->se->get_lngtext('LangTagOrderGSShopBuilder');
        $this->Info['recipient'] = $this->se->db_text_ret('setting|SettingValue|SettingName|edOrderEmail_Text');
        $this->Info['answer_subject'] = $this->se->db_text_ret('setting|SettingValue|SettingName|e_emailsubject_Text');
        $this->Info['logindata_email_text1'] = $this->se->db_text_ret('contentpool|Text|Name|LoginDataEmailText1');
        $this->Info['logindata_email_text2'] = $this->se->db_text_ret('contentpool|Text|Name|LoginDataEmailText2');
        $this->Info['userdata'] = $this->se->get_lngtext('LangTagUserdata');
        $this->Info['dear'] = $this->Customer['dear'];
        $this->Info['user'] = $this->se->get_lngtext('LangTagTextUser');
        $this->Info['password'] = $this->se->get_lngtext('LangTagTextPassword');
        $this->Info['position'] = $this->se->get_lngtext('LangTagTextPosition');
        $this->Info['answer_greeting_text'] = $this->se->db_text_ret('settingmemo|SettingMemo|SettingName|memoEmailGreetingText');
        $this->Info['answer_customer_infos'] = $this->se->db_text_ret('contentpool|Text|Name|PaymentText') . $this->se->db_text_ret('settingmemo|SettingMemo|SettingName|memoCustomerInfos');
        $this->Info['your_rating'] = $this->se->get_lngtext('LangTagTextYourRating');
        $this->Info['your_rating_shop'] = $this->se->get_lngtext('LangTagTextYourRatingShop');
        $this->Info['your_rating_acticle'] = $this->se->get_lngtext('LangTagTextYourRatingArticle');
        $this->Info['answer_provider'] = $this->se->db_text_ret('settingmemo|SettingMemo|SettingName|memoProvider');
        $this->Info['answer_text_end'] = $this->se->db_text_ret('settingmemo|SettingMemo|SettingName|memoEmailTextEnd');
        $this->Info['emailsender'] = $this->se->get_lngtext('LangTagFieldSender');
        $this->Info['currency'] = $this->se->db_text_ret('setting|SettingValue|SettingName|edCurrencySymbol_Text');
        $this->Info['encluded'] = $this->se->get_lngtext('LangTagTextEncludedVAT');
        $this->Info['billingAddress'] = $this->se->get_lngtext('LangTagBillingAddress');
        $this->Info['shippingAddress'] = $this->se->get_lngtext('LangTagShippingAddress');
        $this->Info['pid'] = $this->se->get_lngtext('LangTagFieldPIDLong');
        $this->Info['goodsvalue'] = $this->se->get_lngtext('LangTagTextGoodsValue');
        $this->Info['shopname'] = $this->se->db_text_ret('setting|SettingValue|SettingName|edShopTitle_Text');
        $this->Info['downloaddir'] = $this->downloaddir;
        $this->Info['downloadtxt'] = $this->downloadtxt;
        $this->Info['MailScriptURL'] = $this->se->db_text_ret('setting|SettingValue|SettingName|edMailScriptURL_Text');
        $this->Info['VersionString'] = 'gs_software_ag';
        $useAttach = ($this->se->get_setting('cbUseMailClientAttachment_Checked') == 'True') ? '1' : '0';
        $this->Info['gsAttachment'] = $useAttach;
        $this->Info['Charset'] = $this->se->get_lngtext('LangTagCharset');
        $this->Info[$this->se->get_lngtext('LangTagItemData')] = "---------------------------------------------------------------------------";
        $this->Info[$this->se->get_lngtext('LangTagPersonalData')] = "---------------------------------------------------------------------------";
        $this->Info["_GSSBTXTFNFIELDQUANTITY_"] = $this->se->get_lngtext("LangTagFNFieldQuantity");
        $this->Info["_GSSBTXTFNFIELDITEMNUMBER_"] = $this->se->get_lngtext("LangTagItemNumberLong");
        $this->Info["_GSSBTXTFNFIELDIMAGE_"] = $this->se->get_lngtext("LangTagFNFieldImage");
        $this->Info["_GSSBTXTFNFIELDTEXTITEM_"] = $this->se->get_lngtext("LangTagFNFieldTextItem");
        $this->Info["_GSSBTXTFNFIELDDOWNLOADTXT_"] = $this->se->get_lngtext("LangTagFNFieldDownloadTxt");
        $this->Info["_GSSBTXTFNFIELDTEXTUNITPRICE_"] = $this->se->get_lngtext("LangTagFNFieldTextUnitPrice");
        $this->Info["_GSSBTXTFNFIELDSHORTVAT_"] = $this->se->get_lngtext("LangTagFNFieldShortVat");
        $this->Info["_GSSBTXTFNFIELDTOTALPRICE_"] = $this->se->get_lngtext("LangTagFNFieldTotalPrice");
        $this->Info["_GSSBTXTFNFIELDLOCTOTAL_"] = $this->se->get_lngtext("LangTagFNFieldLocTotal");
        $this->Info["_GSSBTXTFNFIELDLOCDISCOUNT_"] = $this->se->get_lngtext("LangTagFNFieldLocDiscount");
        $this->Info["_GSSBTXTFNFIELDCUSTDISCOUNT_"] = $this->se->get_lngtext("LangTagFieldDiscount");
        $this->Info["_GSSBTXTFNFIELDPAYMENT_"] = $this->se->get_lngtext("LangTagFNFieldPayment");
        $this->Info["_GSSBTXTFNFIELDCASHDISCOUNT_"] = $this->se->get_lngtext("LangTagFNFieldCashDiscountPercent");
        $this->Info["_GSSBTXTFNFIELDPAYMENTCHARGE_"] = $this->se->get_lngtext("LangTagFNFieldPaymentCharge");
        $this->Info["_GSSBTXTFNFIELDSHIPPINGADDRESSAREA_"] = $this->se->get_lngtext("LangTagFNFieldShippingAddressArea");
        $this->Info["_GSSBTXTFNFIELDDELIVERY_"] = $this->se->get_lngtext("LangTagFNFieldDelivery");
        $this->Info["_GSSBTXTFNFIELDPOSTAGE_"] = $this->se->get_lngtext("LangTagFNFieldPostage");
        $this->Info["_GSSBTXTFNFIELDSUBTOTAL_"] = $this->se->get_lngtext("LangTagFNSubTotal");
        $this->Info["_GSSBTXTFNFIELDTOTALAMOUNT_"] = $this->se->get_lngtext("LangTagFNTotalAmount");
        $this->Info["_GSSBTXTFNFIELDVAT_"] = $this->se->get_lngtext("LangTagFNFieldShortVat");
        $this->Info["_GSSBTXTFNFIELDLONGVAT_"] = $this->se->get_lngtext("LangTag__FieldLongVat");
        $this->Info["_GSSBTXTFNFIELDAVAIL_"] = $this->se->get_lngtext("LangTagFNFieldAvail");
        $this->Info["_GSSBTXTFNFIELDCOMPANY_"] = $this->se->get_lngtext("LangTagFNFieldCompany");
        $this->Info["_GSSBTXTFNFIELDCUSTOMERNR_"] = $this->se->get_lngtext("LangTagFNFieldCustomerNR");
        $this->Info["_GSSBTXTFNFIELDFIRMVATID_"] = $this->se->get_lngtext("LangTagFNFieldFirmVATId");
        $this->Info["_GSSBTXTFNFIELDFORMTOADDRESS_"] = $this->se->get_lngtext("LangTagFNFieldFormToAddress");
        $this->Info["_GSSBTXTFNFIELDFIRSTNAME_"] = $this->se->get_lngtext("LangTagFNFieldFirstName");
        $this->Info["_GSSBTXTFNFIELDLASTNAME_"] = $this->se->get_lngtext("LangTagFNFieldLastName");
        $this->Info["_GSSBTXTFNFIELDADDRESS_"] = $this->se->get_lngtext("LangTagFNFieldAddress");
        $this->Info["_GSSBTXTFNFIELDADDRESS2_"] = $this->se->get_lngtext("LangTagFNFieldAddress2");
        $this->Info["_GSSBTXTFNFIELDCITY_"] = $this->se->get_lngtext("LangTagFNFieldCity");
        $this->Info["_GSSBTXTFNFIELDZIPCODE_"] = $this->se->get_lngtext("LangTagFNFieldZipCode");
        $this->Info["_GSSBTXTFNFIELDSTATE_"] = $this->se->get_lngtext("LangTagFNFieldState");
        $this->Info["_GSSBTXTFNFIELDEMAIL_"] = $this->se->get_lngtext("LangTagFNFieldEmail");
        $this->Info["_GSSBTXTFNFIELDPHONE_"] = $this->se->get_lngtext("LangTagFNFieldPhone");
        $this->Info["_GSSBTXTFNFIELDPID_"] = $this->se->get_lngtext("LangTagFNFieldPID");
        $this->Info["_GSSBTXTFNFIELDSHOPURL_"] = $this->se->get_lngtext("LangTagFNFieldShopURL");
        $this->Info["_GSSBTXTFNFIELDMESSAGE_"] = $this->se->get_lngtext("LangTagFNFieldMessage");
        $this->Info["_GSSBTXTFNFIELDFAX_"] = $this->se->get_lngtext("LangTagFNFieldFax");
        $this->Info["_GSSBTXTFNFIELDMOBIL_"] = $this->se->get_lngtext("LangTagFNFieldMobil");
        $this->Info["_GSSBTXTFNTERMSANDCOND_"] = $this->se->get_lngtext("LangTagFNTermsAndCond");
        $this->Info["_GSSBTXTFNFIELDSHIPPINGCOMPANY_"] = $this->se->get_lngtext("LangTagFNFieldShippingCompany");
        $this->Info["_GSSBTXTFNFIELDSHIPPINGFORMTOADDRESS_"] = $this->se->get_lngtext("LangTagFNFieldShippingFormToAddress");
        $this->Info["_GSSBTXTFNFIELDSHIPPINGFIRSTNAME_"] = $this->se->get_lngtext("LangTagFNFieldShippingFirstName");
        $this->Info["_GSSBTXTFNFIELDSHIPPINGLASTNAME_"] = $this->se->get_lngtext("LangTagFNFieldShippingLastName");
        $this->Info["_GSSBTXTFNFIELDSHIPPINGSTREET_"] = $this->se->get_lngtext("LangTagFNFieldShippingStreet");
        $this->Info["_GSSBTXTFNFIELDSHIPPINGADDRESS2_"] = $this->se->get_lngtext("LangTagFNFieldShippingAddress2");
        $this->Info["_GSSBTXTFNFIELDSHIPPINGCITY_"] = $this->se->get_lngtext("LangTagFNFieldShippingCity");
        $this->Info["_GSSBTXTFNFIELDSHIPPINGZIPCODE_"] = $this->se->get_lngtext("LangTagFNFieldShippingZipCode");
        $this->Info["_GSSBTXTFNTERMSANDCONDWITHDRAWAL_"] = $this->se->get_lngtext("LangTagFNTermsAndCondWithdrawal");
        $this->Info["_GSSBTXTFNTERMSANDCONDNEWSLETTER_"] = $this->se->get_lngtext("LangTagFNTermsAndCondNewsletter");
        $this->Info["_GSSBTXTFNFIELDGEBURTSDATUM_"] = $this->se->get_lngtext("LangTagFNFieldGeburtsdatum");
        $this->Info["_GSSBTXTFNFIELDAKTKEY_"] = $this->se->get_lngtext("LangTagFieldAktKey");
        $this->Info["_LANGTAGFNFIELDIMAGE_"] = $this->se->get_lngtext("LangTagFNFieldImage");
        $this->Info["_LANGTAGFNTERMSANDCOND_"] = $this->se->get_lngtext("LangTagTermsAndCondAcceptWithdrawal1");
    }
    
    //Getter und Setter mit magischen Funktionen
	// get variable
	public function __get($Key = '') {
		if(isset($this->$Key)){
			return $this->$Key;
		} else {
			return '';
		}
	}
	// set variable
	public function __set($Key = '', $Val = '') {
		//Datums-Felder ggf. konvertieren
		if($key != '') {
			if(strtolower($this->aFields[$key]) == 'date') {
				$val = $this->conv->date2mysql($val);
			}
			$this->$Key = $Val;
		}
	} 

    //Magische Methoden __sleep und __wakeup implementieren
	//Diese werden ausgeführt, bevor bzw. nachdem eine Instanz der Klasse serialisiert (__sleep) oder
	//deserialisiert wird (__wakeup)
	//In diesen Funktionen können dann noch nötige Operationen durchgeführt werden, bspw. Daten speichern oder
	//Auf jeden Fall MUSS __sleep ein Array mit den Namen aller Klassen-Eigenschaften zurückgeben und
	//in __wakeup muss die Datenbankverbindung wiederhergestellt werden
	public function __sleep() {
		//Hier können noch einige Dinge erledigt werden, bevor die Instanz serialisiert wird
		
		//Alle Eigenschaften zurückgeben, außer die Datenbankverbindung
		return array_diff(array_keys(get_object_vars($this)), array('pplink'));
	}
	
	public function __wakeup() {
		//Hier können Dinge nach der Deserialisierung erledigt werden
		
		//Auf jeden Fall mit der Datenbank verbinden
		$this->se->db_connect();
	}  
	
	public function writeOrder(){
		$aPosData = array();
		for($x = 1; $x <= count($this->Basket); $x++)
		{
			if($this->Basket[$x-1]['art_num'] != null){
				$aPosData[$x]['ordpItemId'] = $this->Basket[$x-1]['art_num'];
				$aPosData[$x]['ordpItemDesc'] = base64_encode($this->Basket[$x-1]['art_title']);
				$aPosData[$x]['ordpQty'] = $this->Basket[$x-1]['art_count'];
				$aPosData[$x]['ordpPrice'] = $this->Basket[$x-1]['art_price'];
				$aPosData[$x]['ordpPriceTotal'] = $this->Basket[$x-1]['art_totalprice'];
				$aPosData[$x]['ordpVATPrct'] = $this->Basket[$x-1]['art_vatrate'];
				if($this->se->get_setting('cbNetPrice_Checked') == "False"){
					// Brutto
					$aPosData[$x]['ordpVATValue']= (($this->Basket[$x-1]['art_price']*$this->Basket[$x-1]['art_count'])/(100 + $this->Basket[$x-1]['art_vatrate']))*$this->Basket[$x-1]['art_vatrate'];
				} else {
					// Netto
					$aPosData[$x]['ordpVATValue']= (($this->Basket[$x-1]['art_price']*$this->Basket[$x-1]['art_count'])/100)*$this->Basket[$x-1]['art_vatrate'];
				}
			}
		}
		
		$this->writeOrderData($aPosData, $ordIdNo);
		$this->writeDownloadData($ordIdNo);
		return "True";
	}
	
	function getCustomerIdByEmail(){
		$con = $this->se->db_connect();
		$sql = "SELECT cusIdNo FROM ".$this->se->dbtoken."customer WHERE cusEMail ='".$this->Customer['cust_email']."'";
		$qry = @mysqli_query($con,$sql);
		$obj = @mysqli_fetch_object($qry);
		return $obj->cusIdNo;
	}
	
	function getDownloadfilename($itemNo,&$AllowedDownloads)
	{ 
		$con = $this->se->db_connect();
		$Downloadfilename = "";
		$AllowedDownloads = 0;
		if($con){
			$sql = "SELECT downloadFilename,downloadAllowedDownloads FROM ".$this->se->dbtoken."downloadarticle WHERE
					downloadItemNumber = '".$itemNo."'
					AND downloadLanguageId = '".$this->lngID."'";	
			
			$qry = @mysqli_query($con,$sql);
			$num = @mysqli_num_rows($qry);
			if ($num > 0)
			{
				$obj = @mysqli_fetch_object($qry);
				$Downloadfilename = $obj->downloadFilename;
				$AllowedDownloads = $obj->downloadAllowedDownloads;
			}		
		}		
		return $Downloadfilename;
	}
	
	function writeDownloadData($ordIdNo){
		$con = $this->se->db_connect();
		for($x = 0; $x < count($this->Basket); $x++) {
			$Item = $this->Basket[$x]['art_num'];
			$ItemDesc = $this->Basket[$x]['art_title'];
			$sDownload = $this->Basket[$x]['art_isdownload'];
			$nQuantity = (int)$this->Basket[$x]['art_count'];
			if ($sDownload == "Y") {
				$dldTime = date("YmdHis", time());
				$strDateiname = $this->getDownloadfilename($Item,$AllowedDownloads);
				$SQLord = "INSERT INTO ".$this->se->dbtoken."downloadarticle_customer (dlcuItemNumber,dlcuSLC,dlcuFilename,dlcuAllowedDownloads,dlcuOrdId, dlcuCusId,dlcuCreateTime )".
			" VALUES ('".$Item."','".$this->lngID."','".$strDateiname."',".$AllowedDownloads.",".$ordIdNo.",".$this->Customer['cusIdNo'].",'" . $dldTime . "')";
				$qryord = @mysqli_query($con,$SQLord);
				$ordIdNo = @mysqli_insert_id($con);
			}
		}//for
		//$this->se->dbclose($con);
	}
	
	function writeOrderData($aPos, &$ordIdNo)
	{
		$ordIdNo = 0;
		$d = ",";
		$sNull = "''";
		$con = $this->se->db_connect();
		$sql = "SELECT cusId FROM ".$this->se->dbtoken."customer WHERE cusEMail='".$this->Customer['cust_email']."'";
		$erg = mysqli_query($con,$sql);
		$res = mysqli_fetch_assoc($erg);
        $ordCustomerId = $res['cusId'];
        $this->Customer['cusId'] = $ordCustomerId;
        $ordId = date('dmYH').mt_rand(1000,9999);
        $this->OrderNumber = $ordId;
		if($con)
		{
			$ordCusIdNo = $this->getCustomerIdByEmail();
			//IP-Adresse speichern
			$cusIP = '';
			$sql = "SELECT * FROM ".$this->se->dbtoken."settings";
			$rs = @mysqli_query($con,$sql);
			
			$obj = @mysqli_fetch_object($rs);
			if($obj->setSaveIP !== 'N') {
				$cusIP = $_SERVER['REMOTE_ADDR'];
			}
			if(isset($this->Customer['UseShippingAddress'])){
				$sOrdKeys = "ordId,ordCusIdNo,ordCustomerId,ordFirmname,ordFirmVATId,ordTitle,ordFirstName,ordLastName,ordStreet,ordStreet2,ordZipCode,ordCity,ordCountry,ordPhone,ordFax,ordShippingCond,ordShippingCost,ordDiscount1Value,ordDiscount1Prct,ordDiscount2Value,ordDiscount2Prct,ordPaymentCond,ordPaymentCost,ordDeliverFirmname,ordDeliverTitle,ordDeliverFirstName,ordDeliverLastName,ordDeliverStreet,ordDeliverStreet2,ordDeliverCity,ordDeliverZipCode,ordDeliverCountry,ordEMail,ordVAT1Value,ordVAT1Prct,ordVAT2Value,ordVAT2Prct,ordVAT3Value,ordVAT3Prct,ordTotalValue,ordTotalValueAfterDsc1,ordTotalValueAfterDsc2,ordCurrency,ordShippingID,ordPaymentID";
			} else {
				$sOrdKeys = "ordId,ordCusIdNo,ordCustomerId,ordFirmname,ordFirmVATId,ordTitle,ordFirstName,ordLastName,ordStreet,ordStreet2,ordZipCode,ordCity,ordCountry,ordPhone,ordFax,ordShippingCond,ordShippingCost,ordDiscount1Value,ordDiscount1Prct,ordDiscount2Value,ordDiscount2Prct,ordPaymentCond,ordPaymentCost,ordEMail,ordVAT1Value,ordVAT1Prct,ordVAT2Value,ordVAT2Prct,ordVAT3Value,ordVAT3Prct,ordTotalValue,ordTotalValueAfterDsc1,ordTotalValueAfterDsc2,ordCurrency,ordShippingID,ordPaymentID";
			}
			$sOrdValues  = "'".$ordId."'".$d;
			$sOrdValues .= "'".$ordCusIdNo."'".$d;
			$sOrdValues .= "'".$ordCustomerId."'".$d;
			$sOrdValues .= "'".$this->Customer['company']."'".$d;
			$sOrdValues .= "'".$this->Customer['firmVATId']."'".$d;
			$sOrdValues .= "'".$this->Customer['mrormrsText']."'".$d;
			$sOrdValues .= "'".$this->Customer['firstname']."'".$d;
			$sOrdValues .= "'".$this->Customer['lastname']."'".$d;
			$sOrdValues .= "'".$this->Customer['street']."'".$d;
			$sOrdValues .= "'".$this->Customer['street2']."'".$d;
			$sOrdValues .= "'".$this->Customer['zip']."'".$d;
			$sOrdValues .= "'".$this->Customer['city']."'".$d;
			$sOrdValues .= "'".$this->Customer['areaName']."'".$d;
			if(isset($this->Customer['phone'])){
				$sOrdValues .= "'".$this->Customer['phone']."'".$d;
			} else {
				$sOrdValues .= "''".$d;
			}
			if(isset($this->Customer['fax'])){
				$sOrdValues .= "'".$this->Customer['fax']."'".$d;
			} else {
				$sOrdValues .= "''".$d;
			}
			// ordShippingCond, ordShippingCost
			$sOrdValues .= "'".$this->Delivery['delivName']."'".$d;
			$sOrdValues .= $this->Delivery['delivTotal'].$d;
			// ordDiscount1Value,ordDiscount1Prct,ordDiscount2Value,ordDiscount2Prct
			
			$sOrdValues .= $this->Discount.$d;
			$sOrdValues .= $this->DiscountPercent.$d;
			if(isset($this->Customer['custDiscount'])){
				$sOrdValues .= $this->Customer['custDiscount'].$d;
			} else {
				$sOrdValues .= "'0'".$d;
			}
			if(isset($this->Customer['custDiscountPercent'])){
				$sOrdValues .= $this->Customer['custDiscountPercent'].$d;
			} else {
				$sOrdValues .= "'0'".$d;
			}
			// ordPaymentCond,ordPaymentCost
			$sOrdValues .= "'".$this->Payment['paymName']."'".$d;
			$sOrdValues .= $this->Payment['paymTotal'].$d;
			// ordDeliverFirmname,ordDeliverTitle,ordDeliverFirstName,ordDeliverLastName,ordDeliverStreet,ordDeliverStreet2,ordDeliverCity,ordDeliverZipCode,ordDeliverCountry
			if(isset($this->Customer['UseShippingAddress'])){
				$sOrdValues .= "''".$d;// ordDeliverFirmname
				$sOrdValues .= "'".$this->Customer['delivermrormrs']."'".$d;
				$sOrdValues .= "'".$this->Customer['deliverfirstname']."'".$d;
				$sOrdValues .= "'".$this->Customer['deliverlastname']."'".$d;
				$sOrdValues .= "'".$this->Customer['deliverstreet']."'".$d;
				$sOrdValues .= "'".$this->Customer['deliverstreet2']."'".$d;
				$sOrdValues .= "'".$this->Customer['delivercity']."'".$d;
				$sOrdValues .= "'".$this->Customer['deliverzip']."'".$d;
				$sOrdValues .= "'".$this->Customer['areaName']."'".$d;
			}
			// ordEMail
			$sOrdValues .= "'".$this->Customer['cust_email']."'".$d;
			// ordVAT1Value,ordVAT1Prct,ordVAT2Value,ordVAT2Prct,ordVAT3Value,ordVAT3Prct
			$sOrdValues .= $this->vat[0]['vattotal'].$d;
			$sOrdValues .= $this->vat[0]['vatrate'].$d;
			$sOrdValues .= $this->vat[1]['vattotal'].$d;
			$sOrdValues .= $this->vat[1]['vatrate'].$d;
			$sOrdValues .= $this->vat[2]['vattotal'].$d;
			$sOrdValues .= $this->vat[2]['vatrate'].$d;
			// ordTotalValue,ordTotalValueAfterDsc1,ordTotalValueAfterDsc2,ordCurrency
			$sOrdValues .= $this->ItemsTotal.$d;
			$sOrdValues .= ($this->ItemsTotal - $this->Discount).$d;
			$sOrdValues .= $this->BasketInvoiceTotal.$d;
			$sOrdValues .= "'".$this->Currency."'".$d;
			// ordShippingID,ordPaymentID
			$sOrdValues .= $this->Delivery['delivID'].$d;
			$sOrdValues .= $this->Payment['paymID'];
			
			$SQLord = "INSERT INTO ".$this->se->dbtoken."order (ordDate,".$sOrdKeys.",ordSLC,ordChgHistoryFlg, ordIP ) VALUES (NOW()+0,".$sOrdValues.",'".$this->slc."','1','".$cusIP."')";
            //die(var_dump($SQLord));
			$qryord = @mysqli_query($con,$SQLord);
			if(mysqli_errno($con) != 0)
			{
				echo("Error description: " . mysqli_error($con));
			}
			$ordIdNo = @mysqli_insert_id($con);
			/* insert the order aktivity */
			$aktKey = "ord001";
			$SQLakt = "INSERT INTO ".$this->se->dbtoken."aktivities(custId, mkKey, aktText, aktDate, ordIdNo, aktKey)
						VALUES('".$this->cusIdNo."', 'ord001', 'Bestellung', NOW()+0, '".$ordIdNo."', '".$aktKey."');";
			$qryakt = @mysqli_query($con,$SQLakt);			
			/* insert the order aktivity */
			for($x = 1; $x <= count($aPos); $x++)
			{
				$sPosKeys = "";
				$sPosValues = "";
				foreach($aPos[$x] as $key => $value)
				{
					$sPosKeys .= $key.",";
					if ($key == "ordpPriceTotal" OR $key == "ordpPrice" OR $key == "ordpVATValue")
					{
						//$value = str_replace('.','',$value);
						//$value = str_replace(',','.',$value);
						$sPosValues .= "'".trim($value)."',";
					}
					else
					{
						$sPosValues .= "'".trim($value)."',";
					}
					/*if($this->UseBonusPoints && $this->PerGoodsValue != 0)
					{
						//SES 20100204 Artikel ist kein Gutschein
						if($aPos[$x]['ordpItemId'] != '000000')
						{
							if($key == "ordpPriceTotal")
							{
								$bPoints = ($value / $this->PerGoodsValue) * $this->BonusPointsPerGoodsValue;
								$bPoints = ceil($bPoints);
								$this->addCustomerBonusPoints($bPoints);
							}
						}
					}*/
				}
				if($aPos[$x]['ordpItemId'] == '000000')
				{
					$start = strpos($aPos[$x]['ordpItemDesc'],"(");
					$end = strpos($aPos[$x]['ordpItemDesc'],")");
					$length = $end - $start - 1;
					$code = substr ($aPos[$x]['ordpItemDesc'], $start+1, $length);

					$sqlC = "SELECT * FROM ".$this->se->dbtoken."coupon where coupCode='".$code."'";
					$rsC = @mysqli_query($con,$sqlC);
					$objC = @mysqli_fetch_object($rsC);
					if($objC->coupValid=="once")
					{
						$sqlCoupon = "UPDATE ".$this->se->dbtoken."coupon set coupUsed = '1', coupUseddate='".date('YmdHis')."' where coupCode = '".$code."'";
					$qryCoupon = @mysqli_query($con,$sqlCoupon);
					}
				}
				$SQLitem = "SELECT itemInStockQuantity, itemShipmentStatus, itemItemNumber FROM ".$this->se->dbtoken."itemdata where itemItemNumber = '".$aPos[$x]['ordpItemId']."';";
				$qryitem = @mysqli_query($con,$SQLitem);
				$objitem = @mysqli_fetch_object($qryitem);

				$stock = $objitem->itemInStockQuantity-$aPos[$x]['ordpQty'];
				$status = $objitem->itemShipmentStatus-$aPos[$x]['ordpQty'];

				$SQLava = "Update ".$this->se->dbtoken."itemdata set itemInStockQuantity = '".$stock."' where itemItemNumber='".$objitem->itemItemNumber."'";
				$qryava = @mysqli_query($con,$SQLava);

				$SQLpos = "INSERT INTO ".$this->se->dbtoken."orderpos (ordpOrdIdNo,ordpPosNo,".$this->removeLastChr($sPosKeys).",ordpChgHistoryFlg,ordpChgTimestamp) VALUES ('".$ordIdNo."','".$x."',".$this->removeLastChr($sPosValues).",'1',CURRENT_TIMESTAMP)";
				$qrypos = @mysqli_query($con,$SQLpos);
			}
            
			if(function_exists('curl_version')){
	            // create curl resource 
	            $ch = curl_init(); 
	            $shopurl = base64_decode($_SESSION['sb_settings']['edAbsoluteShopPath_Text']);
	            // set url 
	            curl_setopt($ch, CURLOPT_URL, $shopurl."dynsb/shoporder/invoice.php?pk=".$ordIdNo."&lang=deu&d=3"); 
	
	            //return the transfer as a string 
	            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	
	            // $output contains the output string 
	            $output = curl_exec($ch); 
	
	            // close curl resource to free up system resources 
	            curl_close($ch);
			}
		}
	}
	
	function removeLastChr($str){
		return substr($str, 0, strlen($str)-1);
	}
	
	function get_bulkprice($aBulk,$quant,$defprice,$action) {
		$price = 0;
		if ($defprice<0) {
			return round($defprice,2);
		}
		$test=strpos($defprice,'-');
		if($action == 0) {
			$blkmax = count($aBulk);
			if($blkmax == 0) {
				$price = $defprice;
			} else {
				for($bl = 0; $bl < $blkmax; $bl++) {
					if($quant >= $aBulk[$bl][0]) {
						$price = $aBulk[$bl][1];
						break;
					}
				}
				if($price == 0) {
					$price = $defprice;
				}
			}
		} else {
			$price = $defprice;
		}
		return round($price,2);
	}
	
	function getDiscountPrice($idx) {
		$discountPrice = $this->Basket[$idx]['art_price'];
		if($this->Basket[$idx]['art_defprice']<0){
			return $discountPrice;
		}
		//1. Staffelpreis berücksichtigen
		$discountPrice = $this->get_bulkprice($this->Basket[$idx]['art_quants'],$this->Basket[$idx]['art_count'],$discountPrice,$this->Basket[$idx]['art_isaction']);
		
		//Bei Bruttopreisen zuerst die MwSt. herausziehen
		if($this->se->get_setting('cbNetPrice_Checked') == "False"){
			// Brutto
			/*$discountPrice = round($discountPrice/(1 + ($this->Basket[$idx]['art_vatrate']/100)),2);
			$discountPrice2 = number_format($discountPrice2/(1 + ($this->Basket[$idx]['art_vatrate']/100)),2);*/
			$discountPrice = $discountPrice/(1 + ($this->Basket[$idx]['art_vatrate']/100));
		}
		
		//2. Rabatte auf Gesamtwarenwert
		$this->setItemsTotalTemp();
		$discount = 0;
		if($this->se->get_setting('edDiscount1_Text') <> ''){
			if($this->ItemsTotalTemp >= $this->se->get_setting('edDisamount1_Text')){
				$discount = $this->se->get_setting('edDiscount1_Text');
			}
		}
		if($this->se->get_setting('edDiscount2_Text') <> ''){
			if($this->ItemsTotalTemp >= $this->se->get_setting('edDisamount2_Text')){
				$discount = $this->se->get_setting('edDiscount2_Text');
			}
		}
		$this->DiscountPercent = $discount;
		$this->Basket[$idx]['art_discount'] = $discount;
		if($discount > 0) {
			$discountPrice = $discountPrice - round(($discountPrice/100) * $this->Basket[$idx]['art_discount'],2);
		}
		
		
		//3. Kundenrabatt
		if(isset($this->Customer['custDiscountPercent'])) {
			if($this->Customer['custDiscountPercent'] > 0) {
				$discountPrice = $discountPrice - round(($discountPrice/100) * $this->Customer['custDiscountPercent'],2);
			}
		} else {
			$this->Customer['custDiscountPercent']=0;
		}
		
		//Bei Bruttopreisen die MwSt. nach der Rabattierung wieder einrechnen
		if($this->se->get_setting('cbNetPrice_Checked') == "False"){
			// Brutto
			$discountPrice = round($discountPrice*(1 + ($this->Basket[$idx]['art_vatrate']/100)),2);
		}
		
		return $discountPrice;
	}
	
	function getItemDiscountPercent() {
		$discount = 0;
		if($this->se->get_setting('edDiscount1_Text') <> ''){
			if($this->ItemsTotal >= $this->se->get_setting('edDisamount1_Text')){
				$discount = $this->se->get_setting('edDiscount1_Text');
			}
		}
		if($this->se->get_setting('edDiscount2_Text') <> ''){
			if($this->ItemsTotal >= $this->se->get_setting('edDisamount2_Text')){
				$discount = $this->se->get_setting('edDiscount2_Text');
			}
		}
		$this->DiscountPercent = $discount;
	}
	
	function getCustomerDiscountPercent() {
		if(isset($this->Customer['cust_email'])){
			$custDiscountPercent = $this->getCustDiscount();
			if($custDiscountPercent> 0){
				$this->Customer['custDiscountPercent'] = $custDiscountPercent;
			} else {
				$this->Customer['custDiscountPercent'] = 0;
			}
		}
	}
	
	function getRandomCustomerPassword()
	{
		$pwd = "";
		for($i = 0; $i < 8; $i++)
		{
			srand((double)microtime()*1000000);
			$y = rand(1,2);
			if($y == 1) $cc = rand(48,57);
			if($y == 2) $cc = rand(65,90);
			$pwd .= chr($cc);
		}
		return $pwd;
	}
	
} // end of class
?>