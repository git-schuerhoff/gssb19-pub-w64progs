<?php
/*********************************************************************
*                                                                    *
* GS GSBM-Connector V1.0 - class.gsbmconnector.php                   *
* Author: Thilo Schürhoff / Schürhoff EDV                            *
*                                                                    *
* (C) 2015 GS Software Projects GmbH                                 *
*                                                                    *
* this code is NOT open-source or freeware                           *
* you are not allowed to use, copy or redistribute it in any way     *
*                                                                    *
*********************************************************************/

if(file_exists("ripcord.php")) {
	require_once("ripcord.php");
} else if(file_exists("inc/ripcord.php")){
    require_once("inc/ripcord.php");
} else {
	die("Class ripcord not found!");
}

class Basket {
	var $itemId;
	var $itemQty;
	var $itemName;
	var $itemPrc;
	var $itemVat;
	var $shopUrl;
	
	function __construct($fItemId,$fItemQty,$fshopUrl,$fItemName,$fItemPrc,$fItemVat) {
		$this->itemId = $fItemId;
		$this->itemQty = $fItemQty;
		$this->shopUrl = $fshopUrl;
		$this->itemName = $fItemName;
		$this->itemPrc = $fItemPrc;
		$this->itemVat = $fItemVat;
	}
}

class Delivery {
	var $paymentname;
	var $paymentfee;
	var $shipmentname;
	var $shipmentfee;
	
	function __construct($aOrder) {
		$this->paymentname = $aOrder['paymentname'];
		$this->paymentfee = $aOrder['paymentfee'];
		$this->shipmentname = $aOrder['shipmentname'];
		$this->shipmentfee = $aOrder['shipmentfee'];
	}
}

class Customer {
	var $dear;
	var $email;
	var $title;
	var $company;
	var $name;
	var $street;
	var $city;
	var $zip;
	var $country;
	var $countrycode;
	var $phone;
	var $mobile;
	var $fax;
	var $payment;
	var $bankname;
	var $bic;
	var $iban;
	var $accholder;
	var $vat;
	var $basketcount;
	var $is_company;
	var $contact_id;
	var $company_id;
	var $customer_no;
	var $Basket;
	var $countryId;
	var $currencyId;
	var $ddinvtext;
	var $bank_id;
	var $ddManateId;
	
	
	function __construct($ap) {
		$this->dear = $ap['dear'];
		$this->email = $ap['email'];
		$this->title = $ap['mrormrs'];
		$this->company = $ap['firm'];
		$this->name = $ap['name'];
		$this->street = $ap['street'];
		$this->city = $ap['city'];
		$this->zip = $ap['zip'];
		$this->country = $ap['country'];
		$this->countrycode = $ap['countrycode'];
		$this->phone = $ap['phone'];
		$this->mobile = $ap['mobile'];
		$this->fax = $ap['fax'];
		$this->payment = $ap['payment'];
		$this->bankname = $ap['bankname'];
		$this->bic = $ap['bic'];
		$this->iban = $ap['iban'];
		$this->accholder = $ap['accholder'];
		$this->vat = $ap['vat'];
		$this->basketcount = $ap['basketcount'];
		$this->is_company = false;
		if($this->company != '') {
			$this->is_company = true;
		}
		$this->ddinvtext = base64_decode($ap['ddinvtext']);
		$this->bank_id = 0;
		$this->ddManateId = 0;
		$this->getBasket($ap);
	}
	
	function getBasket($ap) {
		$this->Basket = array();
		/*for($i = 1; $i <= $this->basketcount; $i++) {
			$this->Basket[] = new Basket($ap['id' . $i],
												  $ap['itemQty' . $i],
												  $ap['itemName' . $i],
												  $ap['itemPrc' . $i],
												  $ap['itemVat' . $i],
												  $ap['shopUrl' . $i]);
		}*/
		$this->Basket = json_decode(base64_decode($ap['abasket']),true);
	}
}

class gsbmConnector {
	var $url;
	var $db;
	var $pwd;
	var $usr;
	var $common;
	var $uid;
	var $models;
	var $reports;
	var $Customer;
	var $dataModel;
	var $operation;
	var $orderNo;
	var $lDebug;
	var $Delivery;
	//GSBM-Settings from GSSB
	var $itemDefUOM;
	var $itemDayUOM;
	var $itemWeekUOM;
	var $itemMonthUOM;
	var $itemYearUOM;
	var $reportSaleId;
	var $reportInvId;
	var $stockId;
	var $mrTitleId;
	var $mrsTitleId;
	var $comTitleId;
	var $pricelstId;
	var $sendOrder;
	var $createInvoice;
	var $openInvoice;
	var $sendInvoice;
	var $recurrent;
	
	function __construct($curl,$cdb,$cusr,$cpwd,$flDebug) {
		$this->url = $curl;
		$this->db = $cdb;
		$this->usr = $cusr;
		$this->pwd = $cpwd;
		$this->partner_found = false;
		$this->has_company = false;
		$this->lDebug = $flDebug;
	}
	
	function connect() {
		//Zu finden in /opt/gsbm/openerp/service :-)
		$this->common = ripcord::client("$this->url/xmlrpc/2/common");
		$this->uid = $this->common->authenticate($this->db, $this->usr, $this->pwd, array());
		$this->models = ripcord::client("$this->url/xmlrpc/2/object");
		$this->reports = ripcord::client("$this->url/xmlrpc/2/report");
	}
	
	function executeKW($aArgs,$aOptions = null) {
		$res;
		if($this->operation == 'search_read') {
			$res = $this->models->execute_kw($this->db, $this->uid, $this->pwd,$this->dataModel,$this->operation, $aArgs,$aOptions);
		} else {
			$res = $this->models->execute_kw($this->db, $this->uid, $this->pwd,$this->dataModel,$this->operation, $aArgs);
		}
		return $res;
	}
	
	function renderReport($cReportModel,$aIDs,$lDecode = true) {
		//'account.report_invoice'
		$res = $this->reports->render_report($this->db, $this->uid, $this->pwd,$cReportModel,$aIDs);
		if($lDecode) {
			$data = base64_decode($res['result']);
		} else {
			$data = $res['result'];
		}
		return $data;
	}
	
	function getData($aClauses,$aOptions) {
		$old_op = $this->operation;
		$this->operation = "search_read";
		return $this->executeKW($aClauses,$aOptions);
		$this->operartion = $old_op;
	}
	
	function getPartner($email = '', $id = 0) {
		$this->dataModel = "res.partner";
		$this->operation = "search_read";
		$aOptions = array('limit'=>1);
		$aRes = array();
		if($email != '') {
			$aClauses = array('email', '=', $email);
		} else {
			if($id != 0) {
				$aClauses = array('id', '=', $id);
			}
		}
		if(isset($aClauses)) {
			$aRes = $this->getData(array(array($aClauses)),$aOptions);
			
		}
		return $aRes;
	}
	
	function getPartnerByMail($email) {
		return $this->getPartner($email);
	}
	
	function getPartnerById($id) {
		return $this->getPartner('',$id);
	}
	
	function getTitleIdByName($title) {
		$tid = 0;
		switch($title) {
			case "Herr":
				$tid = $this->mrTitleId;
				break;
			case "Frau":
				$tid = $this->mrsTitleId;
				break;
			default:
				$tid = $this->mrsTitleId;
				break;
		}
		return $tid;
	}
	
	function setData($dsid,$key,$value) {
		$this->operation = "write";
		$aArgs = array(array($dsid), array($key=>$value));
		$this->executeKW($aArgs);
	}
	
	function addDataMany2Many($dsid,$aValues) {
		//Untested, problems excpected!!!!!!
		$this->operation = "write";
		$many2many_field = array(
			new xmlrpcval(
			array(
				new xmlrpcval(0,"int"),
				new xmlrpcval(0,"int"),
				new xmlrpcval($aValues,"array")
			), "array")
		);
		
		$aArgs = array(array($dsid), array($key=>new xmlrpcval($many2many_field, "array")));
		$this->executeKW($aArgs);
	}
	
	function setManyData($dsid,$aData) {
		$this->operation = "write";
		$aArgs = array(array($dsid), $aData);
		$this->executeKW($aArgs);
	}
	
	function createDataset($aData) {
		$this->operation = "create";
		return $this->executeKW($aData);
	}
	
	function execWorkflow($cAction,$iDsId) {
		$this->models->exec_workflow($this->db, $this->uid, $this->pwd,$this->dataModel,$cAction, $iDsId);
	}
	
	function parseOrderData($aOrder) {
		$this->Customer = new Customer($aOrder);
		$this->Delivery = new Delivery($aOrder);
	}
	
	function createPartner() {
		$old_model = $this->dataModel;
		$this->dataModel = "res.partner";
		
		//First create new private dataset
		$this->Customer->contact_id = $this->createDataset(array(array('name'=>$this->Customer->name)));
		$this->setData($this->Customer->contact_id,'is_company',false);
		$this->setData($this->Customer->contact_id,'customer',true);
		$this->setData($this->Customer->contact_id,'supplier',false);
		//Get new customerno for the contact
		//$contact_cid = $this->getNextCustNo();
		//$this->setData($this->Customer->contact_id,'ref',$contact_cid);
		
		if($this->Customer->is_company) {
			//Create new company dataset
			$this->Customer->company_id = $this->createDataset(array(array('name'=>$this->Customer->company)));
			$this->setData($this->Customer->company_id,'is_company',true);
			$this->setData($this->Customer->company_id,'customer',true);
			$this->setData($this->Customer->company_id,'supplier',false);
			//Get new customerno for the company
			//$company_cid = $this->getNextCustNo();
			//$this->setData($this->Customer->company_id,'ref',$company_cid);
			
			//Update private contact, created company ist related company
			$this->setData($this->Customer->contact_id,'parent_id',$this->Customer->company_id);
		}
		$this->dataModel = $old_model;
	}
	
	function writePartnerData() {
		$old_model = $this->dataModel;
		$this->dataModel = "res.partner";
		
		$aCnt = $this->getCountryIdByCode($this->Customer->countrycode);
		if(!empty($aCnt)) {
			$this->Customer->countryId = $aCnt[0]['id'];
			$this->Customer->currencyId = $aCnt[0]['currency_id'][0];
		}
		
		//First write private Data
		$title_id = $this->getTitleIdByName($this->Customer->title);
		$this->setData($this->Customer->contact_id,'title',$title_id);
		//Save phonenos and email at personal contact always
		$this->setData($this->Customer->contact_id,'phone',$this->Customer->phone);
		$this->setData($this->Customer->contact_id,'fax',$this->Customer->fax);
		$this->setData($this->Customer->contact_id,'mobile',$this->Customer->mobile);
		$this->setData($this->Customer->contact_id,'email',$this->Customer->email);
		
		//Address as company-address, if customer is a company or as private address, if contact is private
		if($this->Customer->is_company) {
			$partner_id = $this->Customer->company_id;
			//In this case set use_parent_address true at personal contact
			$this->setData($this->Customer->contact_id,'use_parent_address',true);
			//Set companytitle (e.g. in german 'Firma')
			$title_id = $this->comTitleId;
			$this->setData($this->Customer->company_id,'title',$title_id);
			//Set Vat-ID
			$this->setData($this->Customer->company_id,'vat',$this->Customer->vat);
			//Set Country-ID
			$this->setData($this->Customer->company_id,'country_id',$this->Customer->countryId);
		} else {
			$partner_id = $this->Customer->contact_id;
			//Set Country-ID
			$this->setData($this->Customer->contact_id,'country_id',$this->Customer->countryId);
		}
		$this->setData($partner_id,'street',$this->Customer->street);
		$this->setData($partner_id,'city',$this->Customer->city);
		$this->setData($partner_id,'zip',$this->Customer->zip);
		$this->dataModel = $old_model;
		return $partner_id;
	}
	
	function createAndWritePartner() {
		$this->createPartner();
		$partner_id = $this->writePartnerData();
		$this->createBank($partner_id);
		return $partner_id;
	}
	
	function createBank($partner_id) {
		$old_model = $this->dataModel;
		if($this->Customer->bic != '' && $this->Customer->iban != '') {
			$aBank = array ('bank_name' => $this->Customer->bankname,
					'state' => 'iban',
					'partner_id' => $partner_id,
					'bank_bic '=> $this->Customer->bic,
					'acc_number' => $this->Customer->iban,
					'owner_name' => $this->Customer->accholder
					);
			$this->dataModel = "res.partner.bank";
			$this->bank_id = $this->createDataset(array($aBank));
			$this->dataModel = $old_model;
		}
	}
	
	function writeBasket() {
		$oid = $this->createOrder();
		if($oid != 0) {
			$this->writePositionData($oid);
			$old_model = $this->dataModel;
			$this->dataModel = "sale.order";
			$this->execWorkflow("order_confirm",$oid);
			$this->dataModel = $old_model;
			//$this->sendNewOrderMessage($oid);
		}
		return $oid;
	}
	
	function createOrder() {
		$old_model = $this->dataModel;
		$this->dataModel = 'sale.order';
		$oid = 0;
		if($this->Customer != NULL) {
			if($this->Customer->contact_id != NULL) {
				$buyer_id = $this->Customer->contact_id;
			} elseif($this->Customer->company_id != NULL) {
				$buyer_id = $this->Customer->company_id;
			} else {
				$buyer_id = 0;
			}
			if($buyer_id != 0) {
				$order = array(
						'state'=>'draft',
						'date_order'=>date('Y-m-d H:i:s'),
						'currency_id'=>$this->Customer->currencyId,
						'user_id'=>$this->uid, 
						'partner_id'=>$buyer_id,
						'partner_invoice_id'=> $buyer_id,
						'partner_shipping_id'=> $buyer_id,
						'picking_policy'=> 'direct',
						'order_policy'=> 'manual',
						'pricelist_id'=> $this->pricelstId,
						'message_unread'=>true);
				$oid = $this->createDataset(array($order));
				$aOrder = $this->getData(array(array(array('id', '=', $oid))),array('limit'=>1));
				$this->orderNo = $aOrder[0]['display_name'];
				//Set Orderno as client_ref
				$this->setData($oid,'client_order_ref',$this->orderNo);
			}
		}
		$this->dataModel = $old_model;
		return $oid;
	}
	
	function writePositionData($oid) {
		$retMsg = '';
		$itemName = '';
		$itemPrc = '';
		$itemUOM = $this->itemDefUOM;
		if($this->Customer != NULL) {
			if($this->Customer->Basket != NULL) {
				if(count($this->Customer->Basket) > 0) {
					$old_model = $this->dataModel;
					$this->dataModel = 'sale.order.line';
					for($b = 0; $b < $this->Customer->basketcount; $b++) {
						$aItem = $this->getItemfromGSBM($this->Customer->Basket[$b]['art_num']);
						if(empty($aItem)) {
							//Create new item
							$itemId = $this->createItem($this->Customer->Basket[$b]);
							$this->dataModel = 'sale.order.line';
							$aItem = $this->getItemfromGSBM($this->Customer->Basket[$b]['art_num']);
						}
						$this->dataModel = 'sale.order.line';
						if($this->Customer->Basket[$b]['art_prices']['isrental'] == 'Y') {
							switch($this->Customer->Basket[$b]['art_prices']['billingperiod']) {
								case 1:
									$itemUOM = $this->itemDayUOM;
									break;
								case 2:
									$itemUOM = $this->itemWeekUOM;
									break;
								case 3:
									$itemUOM = $this->itemMonthUOM;
									break;
								case 4:
									$itemUOM = $this->itemYearUOM;
									break;
								default:
									$itemUOM = $this->itemMonthUOM;
									break;
							}
							
							$itemPrc = $this->Customer->Basket[$b]['art_price'];
							if($this->Customer->Basket[$b]['art_isinitprice'] == 1 || $this->Customer->Basket[$b]['art_isttrialitem'] == 'Y') {
								$itemName = $this->Customer->Basket[$b]['art_title'] . " - " . $this->Customer->Basket[$b]['art_attr0'];
							} else {
								$itemName = $this->Customer->Basket[$b]['art_title'];
							}
							if($this->Customer->Basket[$b]['art_isinitprice'] == 1) {
								$itemUOM = $this->itemDefUOM;
							}
						} else {
							$itemName = $this->Customer->Basket[$b]['art_title'];
							$itemPrc = $this->Customer->Basket[$b]['art_defprice'];
						}
						$aOrderLine = array('order_id' => $oid, 
												  'delay' =>7, 
												  'product_id' =>$aItem[0]['id'], 
												  'name' => $itemName, 
												  'price_unit' => $itemPrc,
												  'discount' => $this->Customer->Basket[$b]['art_discount'],
												  'product_uom' => $itemUOM,
												  'product_uom_qty' => $this->Customer->Basket[$b]['art_count'],
												  'tax_id' => array(array(6,false,array($this->Customer->Basket[$b]['art_vatkey']))),
												  'state' => 'draft');
						$this->createDataset(array($aOrderLine));
						
					}
					//Create Paymentcost und Shipmentcost lines
					if($this->Delivery->paymentfee != 0 && $this->Delivery->paymentfee != '') {
						$this->createShipPaymentLine($oid,false);
					}
					if($this->Delivery->shipmentfee != 0 && $this->Delivery->shipmentfee != '') {
						$this->createShipPaymentLine($oid,true);
					}
					$this->dataModel = $old_model;
				} else {
					//$this->logit(__FUNCTION__ . " - Basket is empty");
				}
			} else {
				//$this->logit(__FUNCTION__ . " - Basket is null");
			}
		} else {
			//$this->logit(__FUNCTION__ . " - Customer is null");
		}
	}
	
	function getItemfromGSBM($cShopItem, $aFields = array('id')) {
		$old_model = $this->dataModel;
		$this->dataModel = "product.product";
		$aItem = $this->getData(array(array(array('default_code', '=', $cShopItem))),array('fields'=>$aFields,'limit'=>1));
		$this->dataModel = $old_model;
		return $aItem;
	}
	
	function sendNoItemMessage($oItem) {
		//
	}
	
	function sendNewOrderMessage($oid) {
		$text = "Es ist eine neue Bestellung herein gekommen. Sie hat die Nummer: " . $this->orderNo;
		$subject = "NeueBestellung";
		$ref_model = "sale.order";
		$parent_id = $this->orderNo;
		$res_id = $oid;
		$this->sendMessage($subject,$text,$ref_model,$parent_id,$res_id);
		
	}
	
	function sendMessage($subject,$text,$ref_model,$parent_id,$res_id) {
		$old_model = $this->dataModel;
		$this->dataModel = "mail.message";
		/*author_id = uid
		body = text
		model = datamodel
		to_read = true
		subject = text
		type = notification
		parent_id = orderno
		res_id = oid
		date*/
		$aMsg = array(
				 "author_id" => $this->uid,
				 "body" => $text,
				 "model" => $ref_model,
				 "to_read" => true,
				 "subject" => $subject,
				 "type" => "notification",
				 "parent_id" => $parent_id,
				 "res_id" => $res_id,
				 "date" => date('Y-m-d H:i:s'));
		$msgId = $this->createDataset(array($aMsg));
		$this->datamodel = $old_model;
	}
	
	function getCountryIdByCode($cCode) {
		$old_model = $this->dataModel;
		$this->dataModel = "res.country";
		$aCountry = $this->getData(array(array(array('code', '=', $cCode))),array('limit'=>1));
		$this->dataModel = $old_model;
		return $aCountry;
	}
	
	function createAndOpenInvoiceFromOrder($oid,$time) {
		$date = date('Y-m-d',$time);
		$old_model = $oc->dataModel;
		$this->dataModel = "sale.order";
		//Create invoice draft
		$this->execWorkflow("manual_invoice",$oid);
		//Get invoice-id from order
		$iid = 0;
		$aOrderRes = $this->getData(array(array(array('id', '=', $oid))),array('limit'=>1));
		$aOrder = $aOrderRes[0];
		$aInvoices = $aOrder['invoice_ids'];
		if(!empty($aInvoices)) {
			//An order could have multiple invoices, but in our there should be only one invoice
			//If there are more than one invoives, we have a problem
			//For the moment we use the first invoice found
			$iid = $aInvoices[0];
			//Set account.invoice as Data-Model
			$this->dataModel = "account.invoice";
			//Set invoice-date
			$this->setData($iid,'date_invoice',$date);
			//Determine paymentterm by paymenttype
			//Mega-Problem: Same problem as the title-problem. Default paymenterms are saved in english and will
			//be translated when displayed. If we want to search, we have to search the english name. BUT if the
			//gsbm-user deletes a payment-term and creates a new one, we have to search the name in the users language!!!
			//So, for the moment this part is static!!!!!
			$this->logit(__FUNCTION__ . " -Payment: " . $this->Customer->payment);
			switch($this->Customer->payment) {
				case 'PaymentInvoice':
					$ptId = 5;
					$pMode = 0;
					$ptText = 'Immediate Payment';
					$textafterpos = '';
					$lCreateDDMandate = false;
					break;
				case 'PaymentPayPal':
					$ptId = 4;
					$pMode = 2;
					$ptText = 'PayPal';
					$textafterpos = '';
					$lCreateDDMandate = false;
					break;
				case 'PaymentDirectDebit':
					$ptId = 12;
					$pMode = 1;
					$ptText = 'DirectDebit';
					$textafterpos = $this->Customer->ddinvtext;
					$lCreateDDMandate = true;
					break;
				default:
					$ptId = 1;
					$pMode = 0;
					$ptText = 'Immediate Payment';
					$textafterpos = '';
					$lCreateDDMandate = false;
					break;
			}
			
			$this->logit(__FUNCTION__ . " -Text after pos.: " . $textafterpos);
			//Set paymentterm
			$this->setData($iid,'payment_term',$ptId);
			
			//Set paymentmode
			if($pMode != 0) {
				$this->setData($iid,'payment_mode_id',$pMode);
			}
			
			//In Paymentmode DirectDebit create mandate
			if($lCreateDDMandate) {
				$this->createDDMandate($date);
				if($this->Customer->ddManateId != 0 && $this->Customer->ddManateId != '') {
					$this->setData($iid,'mandate_id',$this->Customer->ddManateId);
				}
			}
			
			//Set text before positions
			//not yet
			//$oc->setData($iid,'sale_header_note',$textbp);
			
			//Confirm invoice
			if($this->openInvoice) {
				$this->execWorkflow("invoice_open",$iid);
				$this->logit(__FUNCTION__ . " - Invoice opened " . $iid);
			}
			
			//Set followers, customer not need to be a follower
			$this->setData($iid,'message_follower_ids',array(array(6,false,array($this->uid))));
			
			//Get Invoice-data
			$aInvRes = $this->getData(array(array(array('id', '=', $iid))),array('limit'=>1));
			$aInv = $aInvRes[0];
			
			//Set text after positions
			//not yet, NOW!!
			$yesterday = $time - (60 * 60 * 24);
			$tomorrow = $time + (60 * 60 * 24);
			$debitDate = date("d.m.Y",$tomorrow);
			//$textafterpos = iconv('ISO-8859-1','UTF-8',$textafterpos);
			if($textafterpos != '') {
				$textafterpos = str_replace('{GSSE_INCL_INVTOTAL}',str_replace('.',',',$aOrder['amount_total']),$textafterpos);
				$textafterpos = str_replace('{GSSE_INCL_INVOICENO}','(Rechnungsnummer ' . $aInv['number'] . ')',$textafterpos);
				$textafterpos = str_replace('{GSSE_INCL_CUSTNO}',$this->Customer->customer_no,$textafterpos);
				$textafterpos = str_replace('{GSSE_INCL_DATE}','zum ' . $debitDate,$textafterpos);
				/*$aencodings = mb_detect_order();
				$encoding = mb_detect_encoding($textafterpos, "auto");
				$this->logit(__FUNCTION__ . " -set text after pos: " . $textafterpos);
				$this->logit(__FUNCTION__ . " -text after pos encoding: " . $encoding);
				$this->logit(__FUNCTION__ . " -php encodings: " . implode(',',$aencodings));*/
				$this->setData($iid,'comment',$textafterpos);
			}
		}
		$this->dataModel = $old_model;
		return $iid;
	}
	
	function sendMail($aContactId,$model,$templId,$compMode,$resId) {
		$old_model = $this->dataModel;
		$this->dataModel = 'mail.compose.message';
		$aCompose = array('model'=>$model,
								'res_id'=>$resId,
								'composition_mode'=>$compMode,
								'author_id'=>$this->uid);
		$mcId = $this->createDataset(array($aCompose));
		
		//ids, template_id, composition_mode, model, res_id,
		$aArgs = array(array($mcId),$templId, $compMode, $model, $resId);
		$this->operation = 'onchange_template_id';
		$aTmplRes = $this->executeKW($aArgs);
		$aTmpl = $aTmplRes['value'];
		
		$this->setData($mcId,'mail_server_id',$aTmpl['mail_server_id']);
		$this->setData($mcId,'body',$aTmpl['body']);
		$this->setData($mcId,'subject',$aTmpl['subject']);
		
		$aAtt = array(
					array(6,
							false,
							array($aTmpl['attachment_ids'][0])
						)
			);
		
		$aPart = array(
					array(6,
							false,
							$aContactId
						)
			);

		$this->setData($mcId,'attachment_ids',$aAtt);
		$this->setData($mcId,'template_id',$templId);
		$this->setData($mcId,'partner_ids',$aPart);
		$this->setData($mcId,'mail_from',$aTmpl['email_from]']);
	
		$aArgs = array(array($mcId));
		$this->operation = 'send_mail';
		$aMail = $this->executeKW($aArgs);
		
		$this->dataModel = $old_model;
		return $aTmpl;
	}
	
    function sendMailwUml($aContactId,$model,$templId,$compMode,$resId) {
		$old_model = $this->dataModel;
		$this->dataModel = 'mail.compose.message';
		$aCompose = array('model'=>$model,
								'res_id'=>$resId,
								'composition_mode'=>$compMode,
								'author_id'=>$this->uid);
		$mcId = $this->createDataset(array($aCompose));
		
		//ids, template_id, composition_mode, model, res_id,
		$aArgs = array(array($mcId),$templId, $compMode, $model, $resId);
		$this->operation = 'onchange_template_id';
		$aTmplRes = $this->executeKW($aArgs);
		$aTmpl = $aTmplRes['value'];
		
		$this->setData($mcId,'mail_server_id',$aTmpl['mail_server_id']);
		$this->setData($mcId,'body',$aTmpl['body']);
        // Probleme mit Umlauten im Betreff
		$this->setData($mcId,'subject',iconv('ISO-8859-1','UTF-8',$aTmpl['subject']));
		
		$aAtt = array(
					array(6,
							false,
							array($aTmpl['attachment_ids'][0])
						)
			);
		
		$aPart = array(
					array(6,
							false,
							$aContactId
						)
			);

		$this->setData($mcId,'attachment_ids',$aAtt);
		$this->setData($mcId,'template_id',$templId);
		$this->setData($mcId,'partner_ids',$aPart);
		$this->setData($mcId,'mail_from',$aTmpl['email_from]']);
	
		$aArgs = array(array($mcId));
		$this->operation = 'send_mail';
		$aMail = $this->executeKW($aArgs);
		
		$this->dataModel = $old_model;
		return $aTmpl;
	}
    
	function createInvoice($invoiceTime,$origin = '') {
		$invoiceDate = date("Y-m-d",$invoiceTime);
		$old_model = $this->dataModel;
		$invoiceId = 0;
		$buyer_id = 0;
		$this->dataModel = "account.invoice";
		if($this->Customer != NULL) {
			if($this->Customer->contact_id != NULL) {
				$buyer_id = $this->Customer->contact_id;
			} elseif($this->Customer->company_id != NULL) {
				$buyer_id = $this->Customer->company_id;
			} else {
				$buyer_id = 0;
			}
			if($buyer_id != 0) {
				$aInvoice = array(
								'state'=>'draft',
								'origin'=>$origin,
								'date_invoice'=>$invoiceDate,
								'account_id'=>965,
								'journal_id'=>1,
								'user_id'=>$this->uid, 
								'partner_id'=>$buyer_id
								);
				$invoiceId = $this->createDataset(array($aInvoice));
				switch($this->Customer->payment) {
					case 'PaymentInvoice':
						$ptId = 5;
						$pMode = 0;
						$ptText = 'Immediate Payment';
						$textafterpos = '';
						break;
					case 'PaymentPayPal':
						$ptId = 4;
						$pMode = 2;
						$ptText = 'PayPal';
						$textafterpos = '';
						break;
					case 'PaymentDirectDebit':
						$ptId = 12;
						$pMode = 1;
						$ptText = 'DirectDebit';
						$textafterpos = $this->Customer->ddinvtext;
						break;
					default:
						$ptId = 1;
						$pMode = 0;
						$ptText = 'Immediate Payment';
						$textafterpos = '';
						break;
				}
				//Set paymentterm
				$this->setData($invoiceId,'payment_term',$ptId);
				//Set paymentmode
				if($pMode != 0) {
					$this->setData($invoiceId,'payment_mode_id',$pMode);
				}
			}
		}
		$this->dataModel = $old_model;
		return $invoiceId;
	}
	
	function createInvoicePosition($invoiceId,$itemName,$itemNo,$itemUOM,$itemPrc,$amount) {
		$old_model = $this->dataModel;
		$this->dataModel = 'account.invoice.line';
		$aItem = $this->getItemfromGSBM($itemNo);
		if(empty($aItem)) {
			//$this->logit(__FUNCTION__ . " - Item not found in GSBM: " . $itemNo);
			//Message to seller
			$this->sendNoItemMessage($this->Customer->Basket[$b]);
		} else {
			$aInvPosition = array('invoice_id' => $invoiceId, 
										 'product_id' =>$aItem[0]['id'],
										 'account_id'=>281,
										 'name' => $itemName, 
										 'price_unit' => $itemPrc, 
										 'uos_id'=> $itemUOM,
										 'invoice_line_tax_id'=>array(array(4, 12,false)),
										 'quantity'=>$amount);
			$this->createDataset(array($aInvPosition));
		}
		$this->dataModel = $old_model;
	}
	
	function createRecurringInvoices($invoiceId,$invCount,$startDate,$itemName,$itembillingfreq,$intervalType,$lRun = false) {
		if($this->Customer != NULL) {
			if($this->Customer->contact_id != NULL) {
				$buyer_id = $this->Customer->contact_id;
			} elseif($this->Customer->company_id != NULL) {
				$buyer_id = $this->Customer->company_id;
			} else {
				$buyer_id = 0;
			}
			if($buyer_id != 0) {
				$subScriptId = $this->createSubscription('account.invoice',
																	  $invoiceId,
																	  $this->orderNo . " " . $itemName,
																	  $buyer_id,
																	  $itembillingfreq,
																	  $invCount,
																	  $intervalType,
																	  $startDate,
																	  $lRun);
			}
		}
		return $cronId;
	}
	
	function createSubscription($model,$docId,$name,$partnerId,$intervalNo,$execInit,$intervalType,$dateInit,$lRun) {
		$old_model = $this->dataModel;
		$this->dataModel = 'subscription.subscription';
		$subScriptId = 0;
		$cronId = 0;
		$aSubScriptData = array('doc_source' => $model . ',' . $docId,
												'name' =>$name,
												'user_id'=>$this->uid,
												'partner_id'=>$partnerId,
												'interval_number' => $intervalNo,
												'exec_init'=> $execInit,
												'interval_type'=>$intervalType,
												'date_init'=>$dateInit);
		$subScriptId = $this->createDataset(array($aSubScriptData));
		if($lRun) {
			/*$aCrondata = array('function' => ,
								'args'=>'[[' . $recDocId . ']]',
								'user_id'=>$this->uid,
								'name'=>$this->orderNo . " " . $itemName,
								'interval_type'=>$intervalType,
								'numbercall'=>$invCount,
								'nextcall'=>$startDate,
								'priority'=>6,
								'model'=>'subscription.subscription',
								'active'=>1,
								'interval_number' => $itembillingfreq,
								'display_name'=>$this->orderNo . " " . $itemName);*/
			$cronId = $this->createCron('model_copy',
												 $subScriptId,
												 $name,
												 $intervalType,
												 $execInit,
												 $intervalNo,
												 $dateInit,
												 6,
												 'subscription.subscription',
												 1);
			$this->setData($subScriptId,'cron_id',$cronId);
			$this->setData($subScriptId,'state','running');
		}
		$this->dataModel = $old_model;
		return $subScriptId;
	}
	
	function createCron($func,$subScriptId,$name,$intervalType,$execInit,$intervalNo,$startDate,$prior,$model,$active) {
		$old_model = $this->dataModel;
		$this->dataModel = 'ir.cron';
		$cronId = 0;
		$aCrondata = array('function' => $func,
								'args'=>'[[' . $subScriptId . ']]',
								'user_id'=>$this->uid,
								'name'=>$name,
								'interval_type'=>$intervalType,
								'numbercall'=>$execInit,
								'nextcall'=>$startDate,
								'priority'=>$prior,
								'model'=>$model,
								'active'=>$active,
								'interval_number'=>$intervalNo,
								'display_name'=>$name);
		$cronId = $this->createDataset(array($aCrondata));
		$this->dataModel = $old_model;
		return $cronId;
	}
	
	
	function createDDMandate($date) {
		$old_model = $this->dataModel;
		$this->dataModel = 'account.banking.mandate';
		if($this->Customer->contact_id != NULL) {
			$buyer_id = $this->Customer->contact_id;
		} elseif($this->Customer->company_id != NULL) {
			$buyer_id = $this->Customer->company_id;
		} else {
			$buyer_id = 0;
		}
		$this->logit(__FUNCTION__ . " - Partner-ID: " . $buyer_id);
		if($buyer_id != 0) {
			$this->logit(__FUNCTION__ . " - Bank-ID: " . $this->Customer->bank_id);
			if($this->recurrent != ''){
				$aMandateData = array('type'=>'recurrent',
										 'partner_id'=>$buyer_id,
										 'user_id'=>$this->uid,
										 'recurrent_sequence_type'=>'recurring',
										 'unique_mandate_reference'=>'BM' . $this->Customer->customer_no . $this->orderNo,
										 'display_name'=>'BM' . $this->Customer->customer_no . $this->orderNo,
										 'signature_date'=>$date
										);
			} else {
				$aMandateData = array('type'=>'oneoff',
										 'partner_id'=>$buyer_id,
										 'user_id'=>$this->uid,
										 'recurrent_sequence_type'=>'recurring',
										 'unique_mandate_reference'=>'BM' . $this->Customer->customer_no . $this->orderNo,
										 'display_name'=>'BM' . $this->Customer->customer_no . $this->orderNo,
										 'signature_date'=>$date
										);
			}							
			$this->Customer->ddManateId = $this->createDataset(array($aMandateData));
			//Add mandate to Partnerbank
			$this->dataModel = 'res.partner.bank';
			$this->setData($this->Customer->bank_id,'mandate_ids',array(array(6,false,array($this->Customer->ddManateId))));
			$this->dataModel = 'account.banking.mandate';
			$this->setData($this->Customer->ddManateId,'partner_bank_id',$this->Customer->bank_id);
			$this->setData($this->Customer->ddManateId,'state','valid');
			
			$this->logit(__FUNCTION__ . " - Mandate-ID: " . $this->Customer->ddManateId);
		}
		$this->dataModel = $old_model;
		return;
	}
	
	function logit($text) {
		if($this->lDebug) {
			$fh = fopen("class/oclog.txt","a+");
			if($fh) {
				$erg = "File openede";
				fwrite($fh, date("Y-m-d H:i:s") . ": " . $text . chr(13));
			} else {
				$erg = "File not opened";
			}
			fclose($fh);
		} else {
			$erg = "No debug";
		}
		return $erg;
	}
	
	function createShipPaymentLine($oid,$lIsShip = false) {
		$old_model = $this->dataModel;
		if($lIsShip) {
			$name = $this->Delivery->shipmentname;
			$fee = $this->Delivery->shipmentfee;
		} else {
			$name = $this->Delivery->paymentname;
			$fee = $this->Delivery->paymentfee;
		}
		
		$aItem = $this->getItemfromGSBM($name);
		if(empty($aItem)) {
			$aItemData = array('art_num' => $name,
									 'art_defprice' => $fee,
									 'art_instockqty' => 1,
									 'art_title' => $name,
									 'art_weight' => 0.0,
									 'art_vatkey' => 2);
			
			$this->createItem($aItemData,1,'manual_periodic','standard',1,'consu',$this->itemDefUOM,true);
			$aItem = $this->getItemfromGSBM($this->Customer->Basket[$b]['art_num']);
		}
		
		$this->dataModel = 'sale.order.line';
		$aOrderLine = array('order_id' => $oid, 
								  'delay' =>7, 
								  'product_id' =>$aItem[0]['id'], 
								  'name' => $name, 
								  'price_unit' => $fee, 
								  'product_uom'=> $this->itemDefUOM,
								  'product_uom_qty'=>1,
								  'tax_id' => array(array(6,false,array(2))),
								  'state' => 'draft');
		$this->createDataset(array($aOrderLine));
		
		$this->dataModel = $old_model;
	}
	
	function createItem($aItemData,$saleok = 1,$valuation = 'manual_periodic',$costmethod = 'standard',$categid = 1,$type = 'product',$itemUOM = 0,$active = true) {
		$itemId = 0;
		$old_model = $this->dataModel;
		if($itemUOM == 0) { $itemUOM = $this->itemDefUOM; }
		//Create Product-Template
		$this->dataModel = 'product.template';
		$aNewItemTmpl = array(
							"default_code" => $aItemData['art_num'],
							"list_price" => $aItemData['art_defprice'],
							"qty_available" => $aItemData['art_instockqty'],
							"lst_price" => $aItemData['art_defprice'],
							"virtual_available" => $aItemData['art_instockqty'],
							"sale_ok" => $saleok,
							"valuation" => $valuation,
							"display_name" => $aItemData['art_title'],
							"cost_method" => $costmethod,
							"categ_id" => $categid,
							"name" => $aItemData['art_title'],
							"type" => $type,
							"uom_id" => $itemUOM,
							"uom_po_id" => $itemUOM,
							"weight" => $aItemData['art_weight'],
							"active" => $active,
							"taxes_id" => array(array(6,false,array($aItemData['art_vatkey'])))
		);
		$itemTmplId = $this->createDataset(array($aNewItemTmpl));
		//Create Item
		$this->dataModel = 'product.product';
		$aNewItem = array("categ_id" => array($categid),
								"cost_method" => $costmethod,
								"name" => $aItemData['art_title'],
								"product_tmpl_id" => array(array(6,false,array($itemTmplId))),
								"type" => $type,
								"qty_available" => $aItemData['art_instockqty']
							 );
		$itemId = $this->createDataset(array($aNewItem));
		
		//Create stockentry
		/*if($aItemData['art_instockqty'] != '') {
			if($aItemData['art_instockqty'] > 0) {
				$this->changeStockQty($itemId,12,floatval($aItemData['art_instockqty']),$aItemData['art_title'],'product',$itemUOM);
			}
		}*/
		
		$this->dataModel = $old_model;
		return $itemId;
	}
	
	function changeStockQty($itemId,$stockId,$newQty,$itemTitle,$filter,$itemUOM) {
		$old_model = $this->dataModel;
		$this->dataModel = 'stock.inventory';
		//Inventureintrag
		$aInvent = array("name" => "INV " . $itemTitle,
							  "filter" => $stockId,
							  "product_id" => $itemId,
							  "location_id" => $stockId);
		$inventId = $this->createDataset(array($aInvent));
		//Inventurzeile
		$this->dataModel = 'stock.inventory.line';
		//Inventureintrag
		$aInventLine = array("inventory_id" => $inventId,
								   "product_qty" => $newQty,
								   "location_id" => $stockId,
								   "product_id" => $itemId,
								   "product_uom_id" => $itemUOM,
								   "theoretical_qty" => $newQty);
		$inventLineId = $this->createDataset(array($aInventLine));
		
		$this->dataModel = $old_model;
	}
}
?>