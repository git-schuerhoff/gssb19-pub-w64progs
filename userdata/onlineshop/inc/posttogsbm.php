<?php
/*********************************************************************
*                                                                    *
* GS Shop to GSBM-Interface V1.0 - posttogsbm.php                    *
* Author: Thilo Schürhoff / Schürhoff EDV                            *
*                                                                    *
* (C) 2015 GS Software Projects GmbH                                 *
*                                                                    *
* this code is NOT open-source or freeware                           *
* you are not allowed to use, copy or redistribute it in any way     *
*                                                                    *
*********************************************************************/
session_start();

//Testdata:
/*$_POST['email']='info@schuerhoff.biz';
$_POST['mrormrs']='Frau';
$_POST['firm']='Musterfrau GmbH';
$_POST['name']='Martina Musterfrau';
$_POST['street']='Testweg 55';
$_POST['city']='Testburg';
$_POST['zip']='54321';
$_POST['country']='Deutschland';
$_POST['countrycode']='DE';
$_POST['phone']='0123-987654321';
$_POST['mobile']='';
$_POST['fax']='';
$_POST['payment']='PaymentInvoice';
$_POST['bankname']='';
$_POST['bic']='';
$_POST['iban']='';
$_POST['accholder']='';
$_POST['vat']='';
$_POST['basketcount']='3';
//$_POST['abasket']='[{"art_isdownload":"N","art_title":"Timemanager","art_vartitle":"","art_id":"551","art_num":"DHB-EXA-ZETM","art_price":"16.76","art_fromQuant":0,"art_sprice":"","art_vatrate":"19.00","art_weight":"0.00","art_count":5,"art_img":"exalyser-packshot.png","art_dpn":"index.php?page=detail&item=551","art_attr0":"Laufzeit: 24 Monate","art_attr1":"","art_attr2":"","art_quants":[],"art_textfeld":"Folgerechnungen alle 6 Monate 100,56 EUR bis zum Ende der Vertragslaufzeit.","art_checkage":"0","art_mustage":"0","art_defprice":"16.76","art_isaction":0,"art_isdecimal":"1","art_hasdetail":"W","art_prices":{"price":"16.76","oldprice":"0","referenceprice":"0","referencequantity":"0","referenceunit":"","actbegindate":"","actbegintime":"","actenddate":"","actendtime":"","actprice":"","actnormprice":"","actshowperiod":"0","actshownormal":"0","abulk":[],"isrental":"1","billingperiod":"3","billingfrequency":"3,6,12","initialprice":"9.95","istrial":"1","trialperiod":"3","trialfrequency":"1","trialprice":"4.95","rentalruntime":"24"},"art_billingfreq":"6","art_billingfreqtext":"6 monatlich","art_isinitprice":0,"art_isttrialitem":0},{"art_isdownload":0,"art_title":"Timemanager","art_vartitle":"","art_id":"551","art_num":"DHB-EXA-ZETM","art_price":"4.95","art_fromQuant":0,"art_sprice":"","art_vatrate":"19.00","art_weight":"0.00","art_count":"1","art_img":"exalyser-packshot.png","art_dpn":"index.php?page=detail&item=551","art_attr0":"Testzeitraum","art_attr1":"","art_attr2":"","art_quants":[],"art_textfeld":"","art_checkage":"0","art_mustage":"0","art_defprice":"16.76","art_isaction":0,"art_isdecimal":"1","art_hasdetail":"W","art_prices":{"price":"16.76","oldprice":"0","referenceprice":"0","referencequantity":"0","referenceunit":"","actbegindate":"","actbegintime":"","actenddate":"","actendtime":"","actprice":"","actnormprice":"","actshowperiod":"0","actshownormal":"0","abulk":[],"isrental":"1","billingperiod":"3","billingfrequency":"3,6,12","initialprice":"9.95","istrial":"1","trialperiod":"3","trialfrequency":"1","trialprice":"4.95","rentalruntime":"24"},"art_billingfreq":"6","art_billingfreqtext":"6 monatlich","art_isinitprice":0,"art_isttrialitem":1},{"art_isdownload":0,"art_title":"Timemanager","art_vartitle":"","art_id":"551","art_num":"DHB-EXA-ZETM","art_price":"9.95","art_fromQuant":0,"art_sprice":"","art_vatrate":"19.00","art_weight":"0.00","art_count":1,"art_img":"exalyser-packshot.png","art_dpn":"index.php?page=detail&item=551","art_attr0":"Einmalige Einrichtung","art_attr1":"","art_attr2":"","art_quants":[],"art_textfeld":"","art_checkage":"0","art_mustage":"0","art_defprice":"16.76","art_isaction":0,"art_isdecimal":"1","art_hasdetail":"W","art_prices":{"price":"16.76","oldprice":"0","referenceprice":"0","referencequantity":"0","referenceunit":"","actbegindate":"","actbegintime":"","actenddate":"","actendtime":"","actprice":"","actnormprice":"","actshowperiod":"0","actshownormal":"0","abulk":[],"isrental":"1","billingperiod":"3","billingfrequency":"3,6,12","initialprice":"9.95","istrial":"1","trialperiod":"3","trialfrequency":"1","trialprice":"4.95","rentalruntime":"24"},"art_billingfreq":"6","art_billingfreqtext":"6 monatlich","art_isinitprice":1,"art_isttrialitem":0}]';
//$_POST['abasket']='[{"art_isdownload":"N","art_title":"Ein- und Ausgabenliste","art_vartitle":"","art_id":"552","art_num":"EX-FIBU-EA001","art_price":"4.95","art_fromQuant":0,"art_sprice":"","art_vatrate":"19.00","art_weight":"0.00","art_count":"1","art_img":"ein-ausgaben0001.jpg","art_dpn":"index.php?page=detail&item=552","art_attr0":"Laufzeit: 7 Tage","art_attr1":"","art_attr2":"","art_quants":[],"art_textfeld":"Folgerechnungen alle 1 Tage 4,95 EUR bis zum Ende der Vertragslaufzeit.","art_checkage":"0","art_mustage":"0","art_defprice":"4.95","art_isaction":0,"art_isdecimal":"0","art_hasdetail":"W","art_prices":{"price":"4.95","oldprice":0,"referenceprice":"4.95","referencequantity":"1","referenceunit":"Stk","actbegindate":"","actbegintime":"","actenddate":"","actendtime":"","actprice":"","actnormprice":"","actshowperiod":"0","actshownormal":"0","abulk":[],"isrental":"1","billingperiod":"1","billingfrequency":"1","initialprice":"0","istrial":"0","trialperiod":"2","trialfrequency":"0","trialprice":"0","rentalruntime":"7"},"art_billingfreq":"1","art_billingfreqtext":"1 t\u00e4glich","art_isinitprice":0,"art_isttrialitem":0}]';
*/

if(file_exists("./class/class.gsbmconnector.php")) {
	require_once("./class/class.gsbmconnector.php");
} elseif(file_exists("./class.gsbmconnector.php")) {
	require_once("./class.gsbmconnector.php");
} else {
	die("Class gsbmConnector not found!");
}

//A TS 15.04.2016: GS ShopEngine zum Laden der Einstellungen instantiieren
if(file_exists("./class/class.shopengine.php")) {
	require_once("./class/class.shopengine.php");
} elseif(file_exists("./class.shopengine.php")) {
	require_once("./class.shopengine.php");
} else {
	die("Class shopengine not found!");
}
//E TS 15.04.2016: GS ShopEngine zum Laden der Einstellungen instantiieren

$myURL = base64_decode($_POST['gsbmurl']);
$myDB = $_POST['gsbmdb'];
$myUSR = $_POST['gsbmusr'];
$myPWD = convert_uudecode(base64_decode($_POST['gsbmpwd']));

$oc = new gsbmConnector($myURL,$myDB,$myUSR,$myPWD,true);
$oc->connect();

chdir('../');
$se = new gs_shopengine();

//Set Settings from GSSB
$oc->itemDefUOM = intval($se->get_setting('cbbItemUOM_Text'));
$oc->itemDayUOM = intval($se->get_setting('cbbDailyUOM_Text'));
$oc->itemWeekUOM = intval($se->get_setting('cbbWeelyUOM_Text'));
$oc->itemMonthUOM = intval($se->get_setting('cbbMonthlyUOM_Text'));
$oc->itemYearUOM = intval($se->get_setting('cbbYearlUOM_Text'));
$oc->reportSaleId = intval($se->get_setting('cbbReportSaleOrder_Text'));
$oc->reportInvId = intval($se->get_setting('cbbReportAccountInvoice_Text'));
$oc->stockId = intval($se->get_setting('cbbDefaultStorage_Text'));
$oc->mrTitleId = intval($se->get_setting('cbbTitleMr_Text'));
$oc->mrsTitleId = intval($se->get_setting('cbbTitleMrs_Text'));
$oc->comTitleId = intval($se->get_setting('cbbTitleCompany_Text'));
$oc->pricelstId = intval($se->get_setting('cbbSalePriceList_Text'));
$oc->sendOrder = $se->get_setting('cbUseGSBMOrderMail_Checked') == 'True' ? true : false;
$oc->createInvoice = $se->get_setting('cbGSBMCreateInvoice_Checked') == 'True' ? true : false;
$oc->openInvoice = $se->get_setting('cbGSBMOpenInvoice_Checked') == 'True' ? true : false;
$oc->sendInvoice = $se->get_setting('cbGSBMSendInvoice_Checked') == 'True' ? true : false;


//Pass POST-Data to GSBM-Connector and examine it

$oc->parseOrderData($_POST);

/*echo "Basket in class:<pre>";
print_r($oc->Customer->Basket);
die("</pre>");*/

//Set dataModel and operation to res.partner and search_read
$oc->dataModel = "res.partner";
$oc->operation = "search_read";

if($oc->uid === false) {
	die("Could not connect to GSBM!<br />Please check your credentials!");
}

$aPartnerRes = $oc->getPartnerByMail($_POST['email']);
$aPartner = $aPartnerRes[0];

if(is_array($aPartner)) {
	//Partner found
	$oc->Customer->contact_id = $aPartner['id'];
	$oc->Customer->customer_no = $aPartner['ref'];
	if(is_array($aPartner['parent_id'])) {
		//Partner has related company
		$aCompanyRes = $oc->getPartnerById($aPartner['parent_id'][0]);
		$aCompany = $aCompanyRes[0];
		if(is_array($aCompany)) {
			$oc->Customer->is_company = true;
			$oc->Customer->company_id = $aCompany['id'];
			$oc->Customer->countryId = $aCompany['country_id'][0];
			if (is_array($aCompany['bank_ids'])) {
                $oc->Customer->bank_id = $aCompany['bank_ids'][0];
            } else {
                $oc->createBank($aPartner['id']);
                $oc->Customer->bank_id = $oc->bank_id;
            }
		}
	} else {
		$oc->Customer->countryId = $aPartner['country_id'][0];
		if (is_array($aCompany['bank_ids'])) {
            $oc->Customer->bank_id = $aCompany['bank_ids'][0];
        } else {
            $oc->createBank($aPartner['id']);
            $oc->Customer->bank_id = $oc->bank_id;
        }
	}
	$old_model = $oc->dataModel;
	$oc->dataModel = "res.country";
	$aCnt = $oc->getData(array(array(array('id', '=', $oc->Customer->countryId))),array('limit'=>1));
	$oc->Customer->currencyId = $aCnt[0]['currency_id'][0];
	$oc->dataModel = $old_model;
} else {
	//No Partner found
	$oc->createAndWritePartner();
}

$oid = $oc->writeBasket();

//Send Orderconfirmation, when checked
if($oc->sendOrder) {
	$oc->sendMail(array($oc->Customer->contact_id),'sale.order',$oc->reportSaleId,'comment',$oid);
}

if($oc->createInvoice) {
	$invoiceId = $oc->createAndOpenInvoiceFromOrder($oid,time());
	if($oc->sendInvoice && $oc->openInvoice) {
		if($invoiceId != 0 && !empty($invoiceId)) {
			$aTmpl = $oc->sendMail(array($oc->Customer->contact_id),'account.invoice',$oc->reportInvId,'comment',$invoiceId);
		}
	}
}

if($oc->createInvoice) {
	for($i = 0; $i < $oc->Customer->basketcount; $i++) {
		if($oc->Customer->Basket[$i]['art_prices']['isrental'] == 'Y' && $oc->Customer->Basket[$i]['art_isinitprice'] == 0 && $oc->Customer->Basket[$i]['art_isttrialitem'] == 'N') {
			$itemName = $oc->Customer->Basket[$i]['art_title'] . " - " . $oc->Customer->Basket[$i]['art_attr0'];
			switch($oc->Customer->Basket[$b]['art_prices']['billingperiod']) {
				case 1:
					$itemUOM = $oc->itemDayUOM;
					break;
				case 2:
					$itemUOM = $oc->itemWeekUOM;
					break;
				case 3:
					$itemUOM = $oc->itemMonthUOM;
					break;
				case 4:
					$itemUOM = $oc->itemYearUOM;
					break;
				default:
					$itemUOM = $oc->itemMonthUOM;
					break;
			}
			$itemPrc = $oc->Customer->Basket[$i]['art_prices']['price'];
			$itemNo = $oc->Customer->Basket[$i]['art_num'];
			$rentalruntime = $oc->Customer->Basket[$i]['art_prices']['rentalruntime'];
			$itembillingfreq = $oc->Customer->Basket[$i]['art_billingfreq'];
			$itembillingperiod = $oc->Customer->Basket[$i]['art_prices']['billingperiod'];
			$now = time();
			$oneday = (24 * 60 * 60);
			$step = 0;
			$intervalType = '';
			switch($itembillingperiod) {
				case 1:
					//Daily
					$step = $oneday;
					$intervalType = 'days';
					break;
				case 2:
					//Weekly
					$step = (7 * $oneday);
					$intervalType = 'weeks';
					break;
				case 3:
					//Monthly
					$step = (30 * $oneday);
					$intervalType = 'months';
					break;
				case 4:
					//Yearly
					$step = (365 * $oneday);
					$intervalType = '';
					break;
			}
			
			if($step > 0) {
				$next = $now + ($step * $itembillingfreq);
				$nextdat = date('Y-m-d',$next);
				$invCount = ($rentalruntime / $itembillingfreq) - 1;
				//$invoiceId = $oc->createInvoice($nextdat,$oc->orderNo);
				//Timestamp übergeben!!!
				$invoiceId = $oc->createInvoice($next,$oc->orderNo);
				$oc->createInvoicePosition($invoiceId,$itemName,$itemNo,$itemUOM,$itemPrc,$itembillingfreq,$i+1);
				
				if($oc->Customer->payment == 'PaymentDirectDebit') {
					//Set text after positions
					//not yet, NOW!!
					$oc->dataModel = 'account.invoice';
					$aInvoiceRes = $oc->getData(array(array(array('id', '=', $invoiceId))),array('limit'=>1));
					$aInvoice = $aInvoiceRes[0];
					$yesterday = $next - (60 * 60 * 24);
					$tomorrow = $next + (60 * 60 * 24);
					$debitDate = date("d.m.Y",$tomorrow);
					$textafterpos = $oc->Customer->ddinvtext;
					//$textafterpos = iconv('ISO-8859-1','UTF-8',$textafterpos);
					if($textafterpos != '') {
						$textafterpos = str_replace('{GSSE_INCL_INVTOTAL}',str_replace('.',',',$aInvoice['amount_total']),$textafterpos);
						$textafterpos = str_replace('{GSSE_INCL_INVOICENO}','(Rechnungsnummer ---RECHNUNGSNR HIER EINSETZEN---)',$textafterpos);
						$textafterpos = str_replace('{GSSE_INCL_CUSTNO}',$oc->Customer->customer_no,$textafterpos);
						$textafterpos = str_replace('{GSSE_INCL_DATE}','zum ' . $debitDate,$textafterpos);
						$oc->setData($invoiceId,'comment',$textafterpos);
					}
					
					//Add invoice to sepamandate
					if($oc->Customer->ddManateId != 0 && $oc->Customer->ddManateId != '') {
						$oc->setData($invoiceId,'mandate_id',$oc->Customer->ddManateId);
					}
				}
				
                if($oc->Customer->payment == 'PaymentPayPal') {
					//Set text after positions
					//not yet, NOW!!
					$oc->dataModel = 'account.invoice';
					$aInvoiceRes = $oc->getData(array(array(array('id', '=', $invoiceId))),array('limit'=>1));
					$aInvoice = $aInvoiceRes[0];
					$yesterday = $next - (60 * 60 * 24);
					$tomorrow = $next + (60 * 60 * 24);
					$debitDate = date("d.m.Y",$tomorrow);
					//$textafterpos = $oc->Customer->ddinvtext;
					//$textafterpos = iconv('ISO-8859-1','UTF-8',$textafterpos);
					//if($textafterpos != '') {
						$textafterpos = str_replace('{GSSE_INCL_INVTOTAL}',str_replace('.',',',$aInvoice['amount_total']),$textafterpos);
						$textafterpos = str_replace('{GSSE_INCL_INVOICENO}','(Rechnungsnummer ---RECHNUNGSNR HIER EINSETZEN---)',$textafterpos);
						$textafterpos = str_replace('{GSSE_INCL_CUSTNO}',$oc->Customer->customer_no,$textafterpos);
						$textafterpos = str_replace('{GSSE_INCL_DATE}','zum ' . $debitDate,$textafterpos);
						$oc->setData($invoiceId,'comment',$textafterpos);
					//}
					
					//Add invoice to sepamandate
					/*if($oc->Customer->ddManateId != 0 && $oc->Customer->ddManateId != '') {
						$oc->setData($invoiceId,'mandate_id',$oc->Customer->ddManateId);
					}*/
				}
                
				if($intervalType != '') {
					$invCount--;
					if($invCount > 0) {
						$next = $next + ($step * $itembillingfreq);
						$nextdat = date('Y-m-d H:i:s',$next);
                        // Set Autoflag
                        //$oc->setData($invoiceId,'invoice_auto_sent',$oc->sendInvoice);
						$res = $oc->createRecurringInvoices($invoiceId,$invCount,$nextdat,$itemName,$itembillingfreq,$intervalType,true);
					}
				}
				
				//Create Invoice Notice, if payment is directdebit
				/*$oc->logit("posttogsbm - payment: " . $oc->Customer->payment);
				if($oc->Customer->payment == 'PaymentDirectDebit') {
					$oc->logit("posttogsbm - new letter");
					$oc->dataModel = 'letter_post';
					$aLetterTemplateRes = $oc->getData(array(array(array('letter_subject', '=', 'InvoiceNotice'))),array('limit'=>1));
					$aLetterTemplate = $aLetterTemplateRes[0];
					if(!empty($aLetterTemplate)) {
						$oc->logit("posttogsbm - letter template loaded ");
						if($oc->Customer->contact_id != NULL) {
							$buyer_id = $oc->Customer->contact_id;
						} elseif($oc->Customer->company_id != NULL) {
							$buyer_id = $oc->Customer->company_id;
						} else {
							$buyer_id = 0;
						}
						
						if($buyer_id != 0) {
							$textafterpos = $oc->Customer->ddinvtext;
							//$textafterpos = iconv('ISO-8859-1','UTF-8',$textafterpos);
							if($textafterpos != '') {
								$textafterpos = str_replace('{GSSE_INCL_INVTOTAL}',str_replace('.',',',$aInvoice['amount_total']),$textafterpos);
								$textafterpos = str_replace('{GSSE_INCL_INVOICENO}','',$textafterpos);
								$textafterpos = str_replace('{GSSE_INCL_CUSTNO}',$oc->Customer->customer_no,$textafterpos);
								$textafterpos = str_replace('{GSSE_INCL_DATE}','in Kürze',$textafterpos);
							}
							$cLetterText = iconv('ISO-8859-1','UTF-8',$aLetterTemplate['letter_body']);
							$cLetterText = str_replace('{GSBM_INCL_DEAR}',$oc->Customer->dear,$cLetterText);
							$cLetterText = str_replace('{GSBM_INCL_NAME}',$oc->Customer->title . ' ' . $oc->Customer->name,$cLetterText);
							$cLetterText = str_replace('{GSBM_INCL_ORDERNO}',$oc->orderNo,$cLetterText);
							$cLetterText = str_replace('{GSBM_INCL_INVOICETEXT}',$textafterpos,$cLetterText);
							$cLetterText = str_replace('{GSBM_INCL_POSITIONS}',$itemName.' - '.number_format($itemPrc,2,',',',').'€',$cLetterText);
							//$cLetterText = addslashes($cLetterText);
							//$cLetterText = urlencode($cLetterText);
							$oc->logit("posttogsbm - letterbody: " . $cLetterText);
							
							/*$aLetterTemplate['letter_body'] = $cLetterText;
							$aLetterTemplate['letter_subject'] = 'Rechnungsvorankündigung: Ihr Vertrag ' . $oc->orderNo;
							$aLetterTemplate['company_id'] = array($buyer_id,$oc->Customer->name);*/
							//$cLetterText = 'TestTestTest';
					/*		$subject = 'Rechnungsvorankündigung: Ihr Vertrag ' . $oc->orderNo;
							$aLetter = array(
									'partner_id'=>$buyer_id,
									'letter_subject'=>$subject,
									'letter_body'=>$cLetterText,
									'user_id'=>$oc->uid,
                                    'letter_post_auto_sent'=>$oc->sendInvoice,
									'letter_name'=>$subject
									);
							$letterId = $oc->createDataset(array($aLetter));
							$oc->logit("posttogsbm - letter-id: " . $letterId);
							
							//Send 1st letter
							//$oc->sendMail(array($oc->Customer->contact_id),'letter_post',20,'comment',$letterId);
							
							//Create subscription and cron
							$next = $next - (3 * $oneday);
							$nextdat = date('Y-m-d H:i:s',$next);
							$oc->createSubscription('letter_post',$letterId,$subject,$buyer_id,$itembillingfreq,$invCount,$intervalType,$nextdat,true);
						}
					} else {
						$oc->logit("posttogsbm - no letter template found ");
					}
				}*/
				
			}
		}
	}//For
}//if

$aresult = array();
$aresult['oid'] = $oc->orderNo;
$aresult['cid'] = $oc->Customer->customer_no;
echo json_encode($aresult);

?>