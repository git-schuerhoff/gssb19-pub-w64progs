<?php
    //error_reporting(E_ALL);
	//ini_set('display_errors','on');
	require __DIR__ . '/paypalapi/vendor/autoload.php';
	use PayPal\Api\Payer;
	use PayPal\Api\PayerInfo;
	use PayPal\Api\Payment;
	use PayPal\Api\PaymentExecution;
	use PayPal\Exception\PayPalInvalidCredentialException;
	use PayPal\Exception\PayPalConnectionException;
    chdir("../");
    require_once("inc/class.shopengine.php");
    require("inc/class.order.php");
	$pp = new gs_shopengine();
    $order = new Order();
    session_start();
    if (!isset($_SESSION)) {
        session_start();
    }
    
    // Order Object erzeugen
    if(!isset($_SESSION['order'])){
        //session_start();       
        $order = new Order();
        $_SESSION['order'] = serialize($order);
		$test = 'order';
    } else {
        $order = unserialize($_SESSION['order']);
		$test = 'test';
    }
    
    if(isset($_SESSION['pp_status'])){
    	if($_SESSION['pp_status'] == 'ok'){
    		$_POST['step'] = 'cardstepfour';
    	}
    }
 	
	if($order->ItemCount == 0){
		//header("Location: ".$pp->get_setting('edAbsoluteShopPath_Text')."index.php?page=main");
		die("redirect");
	}
	
	if(($_POST['step'] == 'cardstepthree') && ($order->BasketInvoiceTotal == 0)){
		$_POST['step'] = 'cardstepfour';
		$_POST['payment'] = '';
		$_SESSION['buyerinfo']['paymenttype'] = '';
		$cusArray = array();
		foreach($_POST as $key=>$value){
			if($key != 'step'){
				$cusArray[$key] = $value;
			}
		}
		if(isset($_POST['newcustomer']) && ($_POST['newcustomer'] == 'true')){
			$order->createCustomer($cusArray);
		}
		$order->setCustomer($cusArray);
		$_SESSION['buyerinfo'] = $order->Customer;
		if(isset($_POST['EmailFormat'])){
			$order->setEmailFormat($_POST['EmailFormat']);
		}
		
		$dbh = $pp->db_connect();
		if(isset($_POST['stateISO'])){
			$sql = 'SELECT addressareaid id from ' . $pp->dbtoken . 'countriesareas where countryid= "' . $_POST['stateISO'] . '"';
			$erg = mysqli_query($dbh,$sql);
			$areaID = mysqli_fetch_assoc($erg);
			$_SESSION['AreaID']=$areaID['id'];
			$order->setAreaID($areaID['id']);
			$order->set_defaultDelivery();
			$order->set_defaultPayment();
			$order->setPayment(0,'','');
			$_SESSION['order'] = serialize($order);
		}
	}
	
    // Logicprocessor
    switch($_POST['step']){
        case 'cardstepone':
            if(isset($_SESSION['login']['ok'])){
                $_POST['step'] = 'cardsteptwo';
            }
            break;
        case 'cardsteptwo':
            if(isset($_POST['guest'])){
            	$order->guest = True;
            } else {
            	$order->guest = False;
            }
            $_SESSION['order'] = serialize($order);
            break;
        case 'cardstepthree':
            $cusArray = array();
            foreach($_POST as $key=>$value){
                if($key != 'step'){
                    $cusArray[$key] = $value;
                }
            }
            if(isset($_POST['newcustomer']) && ($_POST['newcustomer'] == 'true')){
                $order->createCustomer($cusArray);
            }
            $order->setCustomer($cusArray);
            $_SESSION['buyerinfo'] = $order->Customer;
            $_SESSION['buyerinfo']['paymenttype'] = 'PaymentPayPal';
			if(isset($_POST['EmailFormat'])){
				$order->setEmailFormat($_POST['EmailFormat']);
			}
			$se = new gs_shopengine();
			$dbh = $se->db_connect();
			if(isset($_POST['stateISO'])){
				$sql = 'SELECT addressareaid id from ' . $se->dbtoken . 'countriesareas where countryid= "' . $_POST['stateISO'] . '"';
				$erg = mysqli_query($dbh,$sql);
				$areaID = mysqli_fetch_assoc($erg);
				$_SESSION['AreaID']=$areaID['id'];
				$order->setAreaID($areaID['id']);
				$order->set_defaultDelivery();
				$order->set_defaultPayment();
				$_SESSION['order'] = serialize($order);
			}
            break;
        case 'cardstepfour':
        	if(isset($_POST['financialinstitution'])){
        		$customer = $order->getCustomer();
        		$customer['financialinstitution'] = $_POST['financialinstitution'];
        		$customer['iban'] = $_POST['iban'];
        		$customer['bic'] = $_POST['bic'];
        		$customer['AccountHolderFirstName'] = $_POST['AccountHolderFirstName'];
        		$customer['AccountHolderLastName'] = $_POST['AccountHolderLastName'];
        		$order->setCustomer($customer);
        	}
        	if(isset($_POST['UseShippingAddress']) && $_POST['UseShippingAddress'] == 'Y'){
        		$customer = $order->getCustomer();
        		$customer['UseShippingAddress'] = $_POST['UseShippingAddress'];
        		$customer['delivercompany'] = $_POST['delivercompany'];
        		$customer['delivermrormrs'] = $_POST['delivermrormrs'];
        		$customer['deliverfirstname'] = $_POST['deliverfirstname'];
        		$customer['deliverlastname'] = $_POST['deliverlastname'];
        		$customer['deliverstreet'] = $_POST['deliverstreet'];
        		if(isset($_POST['deliverstreet2'])){
        			$customer['deliverstreet2'] = $_POST['deliverstreet2'];
        		}
        		$customer['deliverzip'] = $_POST['deliverzip'];
        		$customer['delivercity'] = $_POST['delivercity'];
        		$order->setCustomer($customer);
        	}
			if(isset($order->ppplus['paymentid'])){
				if($order->ppplus['paymentid'] <> ''){
					// Versandadresse von PayPal
					$customer = $order->getCustomer();
					$customer['UseShippingAddress']='Y';	
					$clientId = $pp->get_setting('edPPPClientId_Text');
					$secret = $pp->get_setting('edPPPSecret_Text');		
					if($pp->get_setting('edPPPMode_Text') == 'live') {
						$mode = 'live';
					} else {
						$mode = 'sandbox';
					}
					$auth = new \PayPal\Auth\OAuthTokenCredential($clientId, $secret);
					$apiContext = new \PayPal\Rest\ApiContext($auth);
					$apiContext->setConfig(
						array(
							'mode' => $mode
						)
					);
					$pppayment = Payment::get($order->ppplus['paymentid'], $apiContext);
					if(is_object($pppayment)){
						$payer = $pppayment->getPayer();
						if(is_object($payer)){
							$payerInfo = $payer->getPayerInfo();
							$shippingaddress = $payerInfo->getShippingAddress();
							$customer['delivermrormrs'] = $payerInfo->getSalutation();
							$customer['deliverfirstname'] = $shippingaddress->getRecipientName();
							$customer['deliverlastname'] = '';
							$customer['deliverstreet'] = $shippingaddress->getLine1();
							$customer['deliverzip'] = $shippingaddress->getPostalCode();
							$customer['delivercity'] = $shippingaddress->getCity();
							$order->setCustomer($customer);
						}
					} 
				}
			}
			
        	$_SESSION['order'] = serialize($order);
			// Redirect to PayPal
        	if(strpos($_POST['payment'],'PaymentPayPal') === 0 && ($order->se->get_setting('rbUsePPClassic_Checked') == 'True')){
        		include('inc/pp_setexpresscheckout.inc.php');
        		die();
        	}
			// Redirect to Paymorrow
			if(strpos($_POST['payment'],'PaymentPaymorrow') === 0 && ($order->se->get_setting('cbUsePaymorrow_Checked') == 'True')){
				include('inc/paymorrow/paymorrow.inc.php');
				die();
			}
            break;
    }
    
    
    // Wenn der Kunde eingeloggt ist, dann direkt zu Step 3
/*if(isset($_SESSION['login'])){
        if($_SESSION['login']['ok'] && ($_POST['step'] != 'cardstepfour')){
            $se = new gs_shopengine('cardstepthree.html'); 
            $_POST['step'] = 'cardstepthree';
        } else {
            $se = new gs_shopengine($_POST['step'].'.html');
        }
    } else {*/
        $se = new gs_shopengine($_POST['step'].'.html');
    //}
    
    $se->parse_inc();  
    //$se->content = str_replace('class="top_cart"', 'class="top_cart" style="display:none"', $se->content);
	switch($_POST['step']){ 
        case 'cardstepone':
            break;
        case 'cardsteptwo':
			/*Begin Newsletter*/
			$nlhtml = '';
			if($se->get_setting('cbTermsAndConditionsNewsletter_Checked') == 'True')
			{
				$nlhtml = $se->gs_file_get_contents('template/activate_newsletter.html');
				$aNLTags = $se->get_tags_ret($nlhtml);
				$nlhtml = $se->parse_texts($aNLTags,$nlhtml);
				$nlhtml .= '<br/>';
			}
			$se->content = str_replace('{GSSE_INCL_NEWSLETTER}',$nlhtml,$se->content);
			/*End Newsletter*/

			/*Begin accept terms and conds*/
			$tachtml = '';
			if($se->get_setting('cbTermsAndConditions_Checked') == 'True' && $se->get_setting('cbTermsAndConditionsExtra_Checked') == 'False')
			{
				$tachtml = $se->gs_file_get_contents('template/accepttermsandcond.html');
				$aTACTags = $se->get_tags_ret($tachtml);
				$tachtml = $se->parse_texts($aTACTags,$tachtml);
				$tachtml .= '<br/>';
			}
			$se->content = str_replace('{GSSE_INCL_ACCEPTTAC}',$tachtml,$se->content);
			/*End accept terms and conds*/

			/*Begin accept right of revocation*/
			$rorhtml = '';
			if($se->get_setting('cbTermsAndConditionsExtra_Checked') == 'True' && $se->get_setting('cbTermsAndConditions_Checked') == 'False')
			{
				$rorhtml = $se->gs_file_get_contents('template/acceptrightofrevocation.html');
				$aRORTags = $se->get_tags_ret($rorhtml);
				$rorhtml = $se->parse_texts($aRORTags,$rorhtml);
				$rorhtml .= '<br/>';
			}
			$se->content = str_replace('{GSSE_INCL_ACCEPTROR}',$rorhtml,$se->content);
			/*End accept right of revocation*/

			/*Begin all conditions*/
			$allcondhtml = '';
			if($se->get_setting('cbTermsAndConditionsExtra_Checked') == 'True' && $se->get_setting('cbTermsAndConditions_Checked') == 'True')
			{
				$allcondhtml = $se->gs_file_get_contents('template/acceptallcond.html');
				$aACOTags = $se->get_tags_ret($allcondhtml);
				$allcondhtml = $se->parse_texts($aACOTags,$allcondhtml);
				$allcondhtml .= '<br/>';
			}
			$se->content = str_replace('{GSSE_INCL_ACCEPTALL}',$allcondhtml,$se->content);
			/*End all conditions*/
			
			/*Begin E-Mailformat*/
			$emfhtml = '';
			$opts = '';
			$opthtml = $se->gs_file_get_contents('template/option.html');
			if($se->get_setting('cbUsePhpEmailExtension_Checked') == 'True')
			{
				$emfhtml = $se->gs_file_get_contents('template/emailformat.html');
				$aEMFTags = $se->get_tags_ret($emfhtml);
				$emfhtml = $se->parse_texts($aEMFTags,$emfhtml);
				$cur_opt = $opthtml;
				$cur_val = 'HTML';
				$cur_opt = str_replace('{GSSE_OPT_VALUE}',$cur_val,$cur_opt);
				$cur_opt = str_replace('{GSSE_OPT_TEXT}',$cur_val,$cur_opt);
				if(isset($_SESSION['login']['ok']))
				{
					$sel = ($_SESSION['login']['cusEMailFormat'] == $cur_val) ? 'selected' : '';
				}
				else
				{
					$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldEmailFormat|text}';
				}
				$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
				$opts .= $cur_opt;
				
				$cur_opt = $opthtml;
				$cur_val = 'TEXT';
				$cur_opt = str_replace('{GSSE_OPT_VALUE}',$cur_val,$cur_opt);
				$cur_opt = str_replace('{GSSE_OPT_TEXT}',$cur_val,$cur_opt);
				if(isset($_SESSION['login']['ok']))
				{
					$sel = ($_SESSION['login']['cusEMailFormat'] == $cur_val) ? 'selected' : '';
				}
				else
				{
					$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldEmailFormat|html}';
				}
				$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
				$opts .= $cur_opt;
				$emfhtml = str_replace('{GSSE_INCL_OPTSEMAILFORMAT}',$opts,$emfhtml);
			}
			$se->content = str_replace('{GSSE_INCL_EMAILFORMAT}',$emfhtml,$se->content);
			/*End E-Mailformat*/
            break;
        case 'cardstepthree':
			if($order->isDownloadItems && !$order->isMixBasket){
				$se->content = str_replace('{GSSE_INCL_STYLE}', 'style="display:none"', $se->content);	
			} else {
				$se->content = str_replace('{GSSE_INCL_STYLE}', '', $se->content);
			}
            break;
        case 'cardstepfour':
        	$emailformat = $order->getEmailFormat();
        	$customer = $order->getCustomer();
        	$payment = $order->getPayment();
			$se->content = str_replace('{GSSE_INCL_EMAILFORMAT}',$order->getEmailFormat(),$se->content);
			for ($i = 1; $i < 6; $i++){
				if($se->get_setting('cb_activ' . strval($i) . '_Checked') == 'True'){
					$addfieldname = $se->get_setting('ed_name' . strval($i) . '_Text');
					if(isset($customer[$addfieldname])) {
						$addfieldvalue = $customer[$addfieldname];
					} else {
						$addfieldvalue = '';
					}
					$se->content = str_replace('{GSSE_INCL_ADDFIELD' . strval($i) . '}', $addfieldname . ': ' . $addfieldvalue, $se->content);
				} else {
					$se->content = str_replace('{GSSE_INCL_ADDFIELD' . strval($i) . '}', '', $se->content);
				}
			}
			if(isset($customer['cusBirthday'])){
				$se->content = str_replace('<span id="cusBirthday"></span><br />', '<span id="cusBirthday">'.$se->get_lngtext('LangTagFNFieldGeburtsdatum').': '.$customer['cusBirthday'].'</span><br />', $se->content);
			} else {
				$se->content = str_replace('<span id="cusBirthday"></span><br />', '', $se->content);
			}
			if(isset($customer['cusPhone'])){
				$se->content = str_replace('<span id="cusPhone"></span><br />', '<span id="cusPhone">'.$se->get_lngtext('LangTagFNFieldPhone').': '.$customer['cusPhone'].'</span><br />', $se->content);
			} else {
				$se->content = str_replace('<span id="cusPhone"></span><br />', '', $se->content);
			}
			if(isset($customer['cusFax'])){
				$se->content = str_replace('<span id="cusFax"></span><br />', '<span id="cusFax">'.$se->get_lngtext('LangTagFieldFax').': '.$customer['cusFax'].'</span><br />', $se->content);
			} else {
				$se->content = str_replace('<span id="cusFax"></span><br />', '', $se->content);
			}
			if(isset($customer['cusMobil'])){
				$se->content = str_replace('<span id="cusMobil"></span><br />', '<span id="cusMobil">'.$se->get_lngtext('LangTagFieldMobil').': '.$customer['cusMobil'].'</span><br />', $se->content);
			} else {
				$se->content = str_replace('<span id="cusMobil"></span><br />', '', $se->content);
			}
			if(isset($customer['cusAktKey'])){
				$se->content = str_replace('{GSSE_INCL_AKTKEY}', $customer['cusAktKey'], $se->content);
			} else {
				$se->content = str_replace('{GSSE_INCL_AKTKEY}', '', $se->content);
			}
			if(isset($customer['cusNextMessage'])){
				$se->content = str_replace('{GSSE_INCL_CUSTMESSAGE}', $customer['cusNextMessage'], $se->content);
			} else {
				$se->content = str_replace('{GSSE_INCL_CUSTMESSAGE}', '', $se->content);
			}
			$se->content = str_replace('{GSSE_INCL_PAYMINTNAME}', $payment['paymInternalName'], $se->content);
			if(isset($customer['rememberme'])){
				$se->content = str_replace('{GSSE_INCL_REMEMBERME}', $customer['rememberme'], $se->content);
			} else {
				$se->content = str_replace('{GSSE_INCL_REMEMBERME}', 'N', $se->content);
			}
			
			if(isset($_SESSION['pp_status'])){
				if($_SESSION['pp_status'] == 'ok'){
					$se->content = str_replace('{GSSE_INCL_PPSTATE}', 'ok', $se->content);
					$se->content = str_replace('{GSSE_INCL_PPTOKEN}', $_SESSION['pp_token'], $se->content);
					unset($_SESSION['pp_status']);
					unset($_SESSION['pp_token']);
				}
			} else {
				$se->content = str_replace('{GSSE_INCL_PPSTATE}', '', $se->content);
				$se->content = str_replace('{GSSE_INCL_PPTOKEN}', '', $se->content);
			}
            break;
    }
    // Anhand von übergebenen Parameter werden hier zusätzliche Formen eingebaut
    if(isset($_POST['regform'])){
        $rfse = new gs_shopengine('regform.html');
        $rfse->parse_inc(); 
        $se->content = str_replace('id="divcustemail"', 'id="divcustemail" style="display:none;"',$se->content);
        $se->content = str_replace('{GSSE_REGFORM}',$rfse->content,$se->content); 
    } else {
    	if($_POST['step'] <> 'cardstepfour'){
        	$se->content = str_replace('{GSSE_REGFORM}','',$se->content);
    	}
    }

    $_SESSION['order'] = serialize($order);
    echo json_encode($se->content);
    //echo json_encode($pppayment);
?>