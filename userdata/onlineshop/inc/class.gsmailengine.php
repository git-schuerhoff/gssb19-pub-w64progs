<?php
/*
	GS MailShopEngine v1.0 - class.gsmailshopengine.php
	Author: Thilo Schuerhoff / Schuerhoff EDV

	(c) 2014 - 2015 GS Software AG

	this code is NOT open-source or freeware
	you are not allowed to use, copy or redistribute it in any form
*/
header("Content-Type: text/html; charset=utf-8");
class gs_mailengine
{
	var $content;
	var $order;
	var $delivery = array();
	var $payment = array();
	var $basket = array();
	var $customer = array();
	var $info = array();
	var $aTags = array();
	var $prefix = "GSME";
	var $itemhtml = '';
	var $aImages = array();
	var $smtphost;
	var $smtpsecure;
	var $smtpdebug = 0;
	var $smtpauth = true;
	var $smtpport;
	var $smtpusername;
	var $smtppassword;
	var $error = '';
	var $from;
	var $fromname;
	var $shoppath;
	var $aAdditionalDoNotSend = array();
	var $adays = array('Monday' => 'Montag', 
										 'Tuesday' => 'Dienstag', 
										 'Wednesday' => 'Mittwoch', 
										 'Thursday' => 'Donnerstag', 
										 'Friday' => 'Freitag', 
										 'Saturday' => 'Samstag', 
										 'Sunday' => 'Sonntag');
	var $amonths = array('January' => 'Januar', 
											 'February' => 'Februar', 
											 'March' => 'März', 
											 'April' => 'April', 
											 'May' => 'Mai', 
											 'June' => 'Juni', 
											 'July' => 'Juli', 
											 'August' => 'August', 
											 'September' => 'September', 
											 'October' => 'Oktober', 
											 'November' => 'November', 
											 'December' => 'Dezember');
	
	function __construct($mailtmpl)
	{
		session_start();
		require_once('class.order.php');
		$order = new Order();
		$order = unserialize($_SESSION['order']);
		if($mailtmpl != '') {
			$this->content = file_get_contents($mailtmpl);
		}
		$this->order = $order;
		$this->InvoiceMail = $this->order->InvoiceMail;
		$this->delivery = $this->order->getDelivery();
		$this->delivery['delivTotal'] = $this->order->se->get_number_format($this->delivery['delivTotal'],'.');
		$this->payment = $this->order->getPayment();
		$this->payment['paymTotal'] = $this->order->se->get_number_format($this->payment['paymTotal'],'.');
		$this->customer = $this->order->getCustomer();
		if(isset($this->customer['custDiscount'])){
			$this->customer['custDiscount'] = $this->order->se->get_number_format($this->customer['custDiscount'],'.');
		} else {
			$this->customer['custDiscount'] = $this->order->se->get_number_format(0,'.');
		}
		if(!isset($this->customer['UseShippingAddress'])){
			$this->customer['delivercompany'] = $this->customer['company'];
			$this->customer['delivermrormrs'] = $this->customer['mrormrsText'];
			$this->customer['deliverfirstname'] = $this->customer['firstname'];
			$this->customer['deliverlastname'] = $this->customer['lastname'];
			$this->customer['deliverstreet'] = $this->customer['street'];
			if(isset($this->customer['street2'])){
				$this->customer['deliverstreet2'] = $this->customer['street2'];
			}
			$this->customer['deliverzip'] = $this->customer['zip'];
			$this->customer['delivercity'] = $this->customer['city'];
		}
		$this->basket = $this->order->getBasket();
		for($i = 0; $i < count($this->basket);$i++){
			$this->basket[$i]['art_vat'] = $this->order->se->get_number_format($this->basket[$i]['art_vat'],'.');
			$this->basket[$i]['art_price'] = $this->order->se->get_number_format($this->basket[$i]['art_price'],'.');
			$this->basket[$i]['art_totalprice'] = $this->order->se->get_number_format($this->basket[$i]['art_totalprice'],'.');
		}
		$this->info = $this->order->getInfo();
		$this->info['DiscountPrct'] = $this->order->getDiscountPercent();//$this->order->se->get_number_format(($this->order->getItemsTotal()/100)*$this->order->getDiscount(),'.');
		if($this->info['DiscountPrct'] == 0){
			$this->content = str_replace('(-{GSME|DATA|DiscountPrct}%&nbsp;{GSME|TXT|_GSSBTXTFNFIELDLOCDISCOUNT_})', '', $this->content);
		}
		$this->info['ItemsTotal'] = $this->order->se->get_number_format($this->order->getItemsTotal(),'.');
		$this->info['ItemsTotalWithRabatt'] = $this->order->se->get_number_format($this->order->getItemsTotalWithRabatt(),'.');
		$this->info['BasketInvoiceTotal'] = $this->order->se->get_number_format($this->order->getBasketInvoiceTotal(),'.');		
		$this->info['DiscountValue'] = $this->order->se->get_number_format($this->order->getDiscount(),'.');
		$vats = $this->order->getVat();
		for($i = 0; $i < count($vats);$i++){
			$this->info['LongVatPrct'.strval($i+1)] = $vats[$i]['vatrate'];
			$this->info['LongVatValue'.strval($i+1)] = $this->order->se->get_number_format($vats[$i]['vattotal'],'.');
		}
		$this->info['OrderNumber'] = $this->order->getOrderNumber();
		return;
	}
	
	function get_tags()
	{
		$this->aTags = $this->get_tags_ret();
		return;
	}
	
	function get_tags_ret()
	{
		$aMyTags = array();
		$cTag = '';
		$off = 0;
		$opos = 0;
		$cpos = 0;
		$opos = strpos($this->content, '{' . $this->prefix, $off);
		while($opos !== false )
		{
			//Position des öffnenden {
			$off = $opos;
			$cpos = strpos($this->content, '}', $off);
			if($cpos !== false)
			{
				$off = $cpos;
				$cTag = substr($this->content,$opos,($cpos-$opos) + 1);
				$aMyTags[] = $cTag;
			}
			$opos = strpos($this->content, '{' . $this->prefix, $off);
		}
		return $aMyTags;
	}
	
	function parse_tags()
	{
		$prefix;
		$func;
		$param;
		foreach ($this->aTags as $tag)
		{
			$apos = strpos($tag,'|');
			$prefix = substr($tag,1,$apos - 1);
			$bpos = strpos($tag,'|',$apos + 1);
			$func = substr($tag,$apos + 1,($bpos-$apos)-1);
			$param = substr($tag,$bpos + 1,(strlen($tag)-$bpos)-2);
			switch ($func)
			{
				case "TXT":
					$this->em_text($tag,$param,$this->content,0);
					break;
				case "IMG":
					//$this->lngtext($tag,$param);
					break;
				case "DLNK":
					$this->dl_text($tag,$param,$this->content);
					break;
				default:
					//$this->content = str_replace($tag, "Unknown function: " . $func, $this->content);
					break;
			}
		}
		return;
	}
	
	function dl_text($tag,$param,&$content_loc) {
		$dl_cont = '';
		if(strpos($this->delivery['delivName'],'Download') !== false) {
			if($param == 'downloadtxt') {
				$dl_cont = $this->info['downloadtxt'];
			}
			if($param == 'downloadurl') {
				$dl_cont = '<br /><a href="' . $this->info['shopurl'] . 'index.php?page=customerdownloadarea" target="_blank">' . $this->info['shopurl'] . 'index.php?page=customerdownloadarea</a>';
			}
		}
		$content_loc = str_replace($tag,iconv('ISO-8859-1','UTF-8',$dl_cont),$content_loc);
		return;
	}
	
	function parse_itemdata($content_loc,$i)
	{
		$prefix;
		$func;
		$param;
		foreach ($this->aTags as $tag)
		{
			$apos = strpos($tag,'|');
			$prefix = substr($tag,1,$apos - 1);
			$bpos = strpos($tag,'|',$apos + 1);
			$func = substr($tag,$apos + 1,($bpos-$apos)-1);
			$param = substr($tag,$bpos + 1,(strlen($tag)-$bpos)-2);// . $i;
			switch ($func)
			{
				case "DATA":
					$this->em_text($tag,$param,$content_loc,$i);
					break;
				case "IMG":
					$this->add_img($tag,$param,$content_loc,$i);
					break;
				default:
					//
					break;
			}
		}
		return $content_loc;
	}
	
	function em_text($tag,$param,&$content_loc,$i) {
		if($this->info != null && array_key_exists($param, $this->info)) {
			$content_loc = str_replace($tag,$this->info[$param],$content_loc);
		}elseif($this->customer != null && array_key_exists($param, $this->customer)) {
			$content_loc = str_replace($tag,$this->customer[$param],$content_loc);
		}elseif($this->payment != null && array_key_exists($param, $this->payment)) {
			$content_loc = str_replace($tag,$this->payment[$param],$content_loc);
		}elseif($this->delivery != null && array_key_exists($param, $this->delivery)) {
			$content_loc = str_replace($tag,$this->delivery[$param],$content_loc);
		}elseif($this->basket[$i] != null && array_key_exists($param, $this->basket[$i])) {
			$content_loc = str_replace($tag,$this->basket[$i][$param],$content_loc);
		}elseif($this->InvoiceMail != null && array_key_exists($param, $this->InvoiceMail)) {
			$content_loc = str_replace($tag,$this->InvoiceMail[$param],$content_loc);
		}else{
			$content_loc = str_replace($tag,'',$content_loc);
		}
		return;
	}
	
	function add_img($tag,$param,&$content_loc,$i) {
		if(isset($this->basket[$i][$param])){
			$content_loc = str_replace($tag,'itemlogo' . $i,$content_loc);
			$aPath = explode("/",$this->basket[$i][$param]);
			$iPath = count($aPath) - 1;
			$name = $aPath[$iPath];
			$aExt = explode(".",$name);
			$iExt = count($aExt) - 1;
			$type = strtolower($aExt[$iExt]);
			if($type == "jpg") { $type = "jpeg"; }
			$shopsubdir = $this->shoppath;
			if($shopsubdir != "") {
				$shopsubdir = $shopsubdir . "/";
			}
			
			$this->aImages[] = array("cid" => "itemlogo" . $i,
											 "path" => $_SERVER['DOCUMENT_ROOT'] . '/' . $shopsubdir . 'images/medium/' . $this->basket[$i][$param],
											 "name" => $name,
											 "type" => "image/" . $type);
			
		}
		return;
	}
	
	function parse_data() {
		$start = strpos($this->content,'{GSME|DATABAND|BEGIN}');
		$end = strpos($this->content,'{GSME|DATABAND|END}');
		$istart = $start + strlen('{GSME|DATABAND|BEGIN}');
		$iend = $end - $istart;
		$this->itemhtml = substr($this->content,$istart,$iend);
		$first = substr($this->content,0,$start);
		$last = substr($this->content,$end);
		$last = str_replace('{GSME|DATABAND|END}','{GSME|INCL|ITEMDATA}',$last);
		$this->content = $first. $last;
		$allitems = '';
		for($i = 0; $i < count($this->basket); $i++) {
			$cur_item = $this->itemhtml;
			$cur_item = $this->parse_itemdata($cur_item,$i);
			$allitems .= $cur_item;
		}
		$this->content = str_replace('{GSME|INCL|ITEMDATA}',$allitems,$this->content);
		return;
	}
	
	function sendmail($to, $subject, $attach, $file, $ishtml)
	{
		$succ = false;
		$mail = new PHPMailer();
		$mail->CharSet = "UTF-8";
		$mail->IsSMTP(); // telling the class to use SMTP
		$mail->Host       = $this->smtphost; // sets the SMTP server
		$mail->SMTPSecure = $this->smtpsecure;
		$mail->SMTPDebug  = $this->smtpdebug; // enables SMTP debug information (for testing)
		$mail->SMTPAuth   = $this->smtpauth;                  // enable SMTP authentication
		$mail->Port       = $this->smtpport;                    // set the SMTP port for the GMAIL server
		$mail->Username   = $this->smtpusername; // SMTP account username
		$mail->Password   = convert_uudecode ($this->smtppassword);        // SMTP account password
		$mail->SetFrom($this->from, $this->fromname);
		$mail->AddReplyTo($this->from, $this->fromname);
		$mail->Subject    = $subject;
		$mail->AddAddress($to);
		$ext = '';
		$coding = '';
		$mime = '';
		
		if($file != "") {
			$ext = pathinfo($file, PATHINFO_EXTENSION);
			switch($ext) {
				case "txt":
					$coding = 'quoted-printable';
					$mime = 'text/plain';
					break;
				case "pdf":
					$coding = 'base64';
					$mime = 'application/pdf';
					break;
				case "html":
					$coding = '8bit';
					$mime = 'text/html';
					break;
				case "htm":
					$coding = '8bit';
					$mime = 'text/html';
					break;
				case "xml":
					$coding = 'base64';
					$mime = 'text/xml';
					break;
				default:
					$coding = 'quoted-printable';
					$mime = 'text/plain';
					break;
			}
		}
		//$mail->SMTPDebug = true;
		if($ishtml === true) {
			$mail->AltBody = $this->msg;
			$nimg = count($this->aImages);
			if($nimg > 0) {
				for($i = 0; $i < $nimg; $i++) {
					$mail->AddEmbeddedImage($this->aImages[$i]['path'],
													$this->aImages[$i]['cid'],
													$this->aImages[$i]['name'],
													"base64",
													$this->aImages[$i]['type']);
				}
			}
			if($file != "") {
				$mail->AddStringAttachment($attach,$file,$coding,$mime);
			}
			$mail->IsHTML(true);
			$mail->CharSet = "utf-8";
			$mail->MsgHTML($this->htmlmsg);
		} else {
			$mail->IsHTML(false);
			$mail->Body = $this->msg;
			if($file != "") {
				//$mail->ContentType = 'multipart/mixed';
				$mail->AddStringAttachment($attach,$file,$coding,$mime);
			} else {
				//$mail->ContentType = 'text/plain';
			}
		}
		$succ = $mail->Send();
		if(!succ) {
			$this->error = $mail->ErrorInfo;
		}
		return $succ;
	}
	
	function sendmail2($to, $subject, $attach, $file, $ishtml)
	{
		$succ = false;
		$mail = new PHPMailer();
		$mail->CharSet = "UTF-8";
		/*$mail->IsSMTP(); // telling the class to use SMTP
		$mail->Host       = $this->smtphost; // sets the SMTP server
		$mail->SMTPSecure = $this->smtpsecure;
		$mail->SMTPDebug  = $this->smtpdebug; // enables SMTP debug information (for testing)
		$mail->SMTPAuth   = $this->smtpauth;                  // enable SMTP authentication
		$mail->Port       = $this->smtpport;                    // set the SMTP port for the GMAIL server
		$mail->Username   = $this->smtpusername; // SMTP account username
		$mail->Password   = convert_uudecode ($this->smtppassword);        // SMTP account password*/
		$mail->SetFrom($this->from, $this->fromname);
		$mail->AddReplyTo($this->from, $this->fromname);
		$mail->Subject    = $subject;
		$mail->AddAddress($to);
		$ext = '';
		$coding = '';
		$mime = '';
		
		if($file != "") {
			$ext = pathinfo($file, PATHINFO_EXTENSION);
			switch($ext) {
				case "txt":
					$coding = 'quoted-printable';
					$mime = 'text/plain';
					break;
				case "pdf":
					$coding = 'base64';
					$mime = 'application/pdf';
					break;
				case "html":
					$coding = '8bit';
					$mime = 'text/html';
					break;
				case "htm":
					$coding = '8bit';
					$mime = 'text/html';
					break;
				case "xml":
					$coding = 'base64';
					$mime = 'text/xml';
					break;
				default:
					$coding = 'quoted-printable';
					$mime = 'text/plain';
					break;
			}
		}
		//$mail->SMTPDebug = true;
		if($ishtml === true) {
			$mail->AltBody = $this->msg;
			$nimg = count($this->aImages);
			if($nimg > 0) {
				for($i = 0; $i < $nimg; $i++) {
					$mail->AddEmbeddedImage($this->aImages[$i]['path'],
													$this->aImages[$i]['cid'],
													$this->aImages[$i]['name'],
													"base64",
													$this->aImages[$i]['type']);
				}
			}
			if($file != "") {
				$mail->AddStringAttachment($attach,$file,$coding,$mime);
			}
			$mail->IsHTML(true);
			$mail->CharSet = "utf-8";
			$mail->MsgHTML($this->htmlmsg);
		} else {
			$mail->IsHTML(false);
			$mail->Body = $this->msg;
			if($file != "") {
				//$mail->ContentType = 'multipart/mixed';
				$mail->AddStringAttachment($attach,$file,$coding,$mime);
			} else {
				//$mail->ContentType = 'text/plain';
			}
		}
		$succ = $mail->Send();
		if(!$succ) {
			$this->error = $mail->ErrorInfo;
       }
		return $succ;
	}
	
	function createAttachment()
	{
		$day = date("l");
		$month = date("F");
		$sep = "---------------------------------------------------------------------------------------------------------\n";
		$aDoNot = array('sid', 'recipient', 'answer_customer_infos', 'password', 'user',
										'dear', 'logindata_email_text1', 'logindata_email_text2',
										'answer_greeting_text', 'answer_customer_text', 'answer_provider',
										'answer_text', 'answer_text_end', 'subject', 'goodsvalue',
										'qtyofpos', 'shopname', 'redirect', 'answer_subject', 'gsAttachment',
										'SumDiscount2', 'CashDiscountValue', 'CashDiscountPrct', 'userdata',
										'emailsender', 'rememberme', 'button', 'button2', 'dsbID', 'pid',
										'DiscountPrct', 'DiscountValue', 'SumDiscount1', 'Charset',
										'_LANGTAGFNFIELDEMAILFORMAT_', 'MailScriptURL', 'VersionString',
										'position', 'currency', 'encluded', 'billingAddress', 'shippingAddress',
										'gsremember_orderform__LANGTAGFNFIELDCITY_', 'gsremember_orderform__LANGTAGFNFIELDZIPCODE_',
										'gsremember_orderform__LANGTAGFNFIELDSTATE_', 'gsremember_orderform_email',
										'gsremember_orderform__LANGTAGFNFIELDPHONE_', 'gsremember_orderform__LANGTAGFNFIELDFAX_',
										'gsremember_orderform__LANGTAGFNFIELDMESSAGE_', 'gsremember_orderform__LANGTAGFNTERMSANDCOND_',
										'gsremember_orderform_rememberme', 'gsremember_orderform__LANGTAGFNFIELDEMAILFORMAT_',
										'gsremember_orderform_', 'gsremember_customerlogin_returnpath',
										'gsremember_customerlogin_cusDiscount', 'gsremember_customerlogin_userid',
										'gsremember_customerlogin_password', 'gsremember_customerlogin_',
										'gsremember_newsletter_form_email', 'gsremember_newsletter_form_subscribe',
										'gsremember_newsletter_form_unsubscribe', 'loginemail',	'secFieldReqCredCard', 
										'_LANGTAGFNTERMSANDCONDNEWSLETTER2_', '_BtnInvoiceAddress_',
										'LangTagTextYourRating', 'LangTagTextYourRatingShop', 'LangTagTextYourRatingArticle'
									 );
		if(count($this->aAdditionalDoNotSend) > 0)
		{
			$aDoNot = array_merge($aDoNot, $this->aAdditionalDoNotSend);
		}
		//echo "POST-Daten in createAttachment:<pre>";
		//print_r($this->ap);
		//die("</pre>");
		$text = "email: ".$this->customer['cust_email']."\n";
		$text .= "weekday: ".date('w')."\n";
		$text .= "date: ".date('Y-m-d')."\n";
		$text .= "time: ".date('H:i:s')."\n";
		$text .= "germandate: ".$this->getDay($day).", ".date('d').".".$this->getMonth($month)." ".date('Y')." um ".date('H:i:s')."\n";
		$text .= "englishdate: ".$day.", ".$month." ".date('d').", ".date('Y')." at ".date('H:i:s')."\n";
		$text .= $sep;
		$text .= "\n";
		foreach($this->order as $key => $value)
		{
			if(substr($key, 0, strlen('ShortVatPrct')) != 'ShortVatPrct' &&
				substr($key, 0, strlen('ShortVatValue')) != 'ShortVatValue')
			{
				if(!in_array($key, $aDoNot) && (strpos($key, 'DOWNLOADITM_') === false))
				{
//A UR 28.1.2011				
							if(substr($key,0,23)=="_LANGTAGFNFIELDLONGVAT_")
							{

								$pos = strpos($key, '(');
								$pos2 = strpos($key, ')');
								if ($pos > 0)
								{
									if ($pos2 > $pos)
									{
										$tmp = substr($key, $pos, $pos2-$pos+1);
										$tmp = str_replace("_",".",$tmp); //für krumme Mwst.
										$value .=	" ".$tmp;
									}
									$key = substr($key, 0, $pos-2);
								}
							}
//E UR							
					$text .= str_pad($key, 30, " ", STR_PAD_RIGHT).": ".$value."\n";
				}
			}
		}
		$text .= $sep;
		return $text;
	}
	
	function getDay($str)
	{
		if(array_key_exists($str, $this->adays))
		{
			$str = $this->adays[$str];
		}
		return $str;
	}

	function getMonth($str)
	{
		if(array_key_exists($str, $this->amonths))
		{
			$str = $this->amonths[$str];
		}
		return $str;
	}
}
?>