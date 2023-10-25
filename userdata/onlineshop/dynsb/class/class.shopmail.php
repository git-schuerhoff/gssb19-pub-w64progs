<?php
/*

		GS-ShopMail v1.0 - class.shopmail.php
		Author: Raimund Kulikowski / GS Software Solutions GmbH

		(c) 2004-2005 GS Software Solutions GmbH

		this code is NOT open-source or freeware
		you are not allowed to use, copy or redistribute it in any form

*/

class shopmail
{
	var $sVersion = "1.1";
	var $sTextTemplateFilename = "customer_text_mail.html";
	var $sHTMLTemplateFilename = "customer_html_mail.html";
	var $sTemplatePWFilename = "customer_pwmail.html";
	var $sEMailLogoFilename = "";

	var $ap = array();
	var $apv = array();
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

	var $aPrepareTemplate = array(	'vat1' => array('LongVatPrct1', 'LongVatValue1'),
																	'vat2' => array('LongVatPrct2', 'LongVatValue2'),
																	'vat3' => array('LongVatPrct3', 'LongVatValue3'),
																	'discount1' => array('DiscountPrct', 'DiscountValue'),
																	'discount2' => array('CashDiscountPrct', 'CashDiscountValue'),
																	'paycharge1' => array('PaymentCharge')
															 );
	var $CustomData = array( 'fta' => 'FormToAddress',
														'fn' => 'FirstName',
														'ln' => 'LastName'
												 );
	var $FirstName = "";
	var $LastName = "";
	var $FormToAddress = "";
	var $sMailHeader = "";
	var $sMailRecipient = "";
	var $sMailSubject = "";
	var $sMailContent = "";
	var $sShopPath = "";
	var $sTemplatePath = "";
	var $sPosQuantity = "";
	var $sPosItem = "";
	var $sPosPrice = "";
	var $sPosVATValue = "";
	var $sPosVATPrct = "";
	var $sPosTotalPrice = "";
	var $slbreak = "\n";
	var $sboundary1 = "";
	var $sboundary2 = "";
	var $sboundary3 = "";
	var $sboundaryMix = "";
	var $mime_boundary = "";
	var $sCID = "";
	var $sOrderURL = "";
	var $sOrderCurrency = "";
	var $sVersionStr = "";
	var $userpass = "";

	var $bCustomerMailSuccess = false;
	var $bMailSend = false;
	var $bUseMail = true;
	var $bNewCus = "";

	// 0 = english with .
	// 1 = germand with ,
	var $iNumberFormat = 0;

	function __construct(&$pass, $bNewCus, $aPost = '', $sPath, $p1 = 'Menge', $p2 = 'Artikel', $p3 = 'Einzelpreis', $p4 = 'MwStWert', $p5 = 'MwStProzent', $p6 = 'Gesamtpreis', $orderURL = '', $cur = 'EUR', $nf = 0, $vstr = '')
	{
		
	 //error_reporting(E_ALL);
		if($aPost == '')
			return false;
		$this->sVersionStr = trim($vstr);
		$this->ap = $aPost;
		$this->sPosQuantity = $p1;
		$this->sPosItem = $p2;
		$this->sPosPrice = $p3;
		$this->sPosVATValue = $p4;
		$this->sPosVATPrct = $p5;
		$this->sPosTotalPrice = $p6;
		$this->sOrderURL = $orderURL;
		$this->sOrderCurrency = trim($cur);
		$this->iNumberFormat = $nf;
		$this->sTemplatePath = $this->removeFilenameFromPath($sPath);
		$this->userpass = $pass;
		$this->bNewCus = $bNewCus;
	}

	function startmail()
	{
		/*echo "Array this->ap in shopmail.startmail():<br /><pre>";
		print_r($this->ap);
		die("</pre>");*/
		$this->cleanUpAllPostData();
		//echo "Array this->apv nach this->cleanUpAllPostData():<br /><pre>";
		//print_r($this->apv);
		//die("</pre>");
		//$this->logProPlusVersion();
		$this->shopmailProcessDefinition();
		return $this->bMailSend;
	}

	function shopmailProcessDefinition()
	{
			
		switch(strtolower(trim($this->ap['_LANGTAGFNFIELDEMAILFORMAT_'])))
		{
			
			case 'text':
				$this->createCustomerTextMail();
				break;
			case 'html':
				$this->createCustomerHTMLMail();
				break;
			default:
				$this->createCustomerTextMail();
				break;
		}
		if($this->bMailSend)
		{
			$this->bCustomerMailSuccess = true;
			$this->bMailSend = false;
		}
		$this->initMail();
		
		if($this->bNewCus==true && $this->ap['loginemail']=="1")
		{
			$this->createPwMail();
			$this->initMail();
		}
		$this->createShopownerMail();
		$this->redirect();
	}

	function initMail()
	{
		$this->sMailHeader = "";
		$this->sMailRecipient = "";
		$this->sMailSubject = "";
		$this->sMailContent = "";
	}

	function removeFilenameFromPath($str)
	{
		//A TS 18.03.2014 "inc" verzeichnis auch mit entfernen
		$atmp = explode("/", $str);
		$nstr = "";
		//for($i = 0; $i < (count($atmp) - 1); $i++)
		for($i = 0; $i < (count($atmp) - 2); $i++)
		{
			$nstr .= $atmp[$i]."/";
		}
		return $nstr;
	}

	function createCustomerTextMail()
	{
		$this->createMailHeader('txt');
		$this->processTextTemplate('text');
		$this->setCustomerSubject();
		if($this->bUseMail)
			$this->sendMail($this->ap['email']);
	}

	function createCustomerHTMLMail()
	{
		$this->createMailHeader('htm',0);
		$this->processHTMLTemplate();
		$this->setCustomerSubject();
		if($this->bUseMail)
			$this->sendMail($this->ap['email']);
	}

	function createShopownerMail()
	{
		$this->sHTMLTemplateFilename = 'shopowner_html_mail.html';
		$this->sTextTemplateFilename = 'shopowner_text_mail.html';
		$this->createMailHeader('htm',1);
		//$this->createOutput4SBGSMailclient();
		$this->processHTMLTemplate();
		//A TS 15.11.2016: Anhang hinzufügen
		if($this->ap['gsAttachment']==1) {
			//$data = chunk_split(base64_encode($this->createAttachment()));
			$data = $this->createAttachment();
			//$content .= "This is a multi-part message in MIME format.\n\n"."--".$this->mime_boundary."\n"."Content-Type: text/plain; charset=\"utf-8\"\n"."Content-Transfer-Encoding: base64\n\n".chunk_split(base64_encode($out))."\n\n";
			$data = chunk_split(base64_encode($data));
			$file = "gsshopbuilder.txt";
			//Ende Alternative-Sektion
			//$content = "--".$this->sboundaryMix."--\n";
			//Beginn Attachment
			$content .= "--".$this->sboundaryMix."\n";
			$content .= "Content-Type: text/plain; charset=\"utf-8\"\n name=\"".$file."\"\nContent-Disposition: attachment;\n filename=\"".$file."\"\nContent-Transfer-Encoding: base64\n\n".$data."\n\n";
			$content .= "--".$this->sboundaryMix."--\n";
			$this->sMailHeader .= $content;
		}
		//E TS 15.11.2016: Anhang hinzufügen
		$this->setShopownerSubject();
		if($this->bUseMail)
			//die($this->sMailContent);
			$this->sendMail($this->ap['recipient']);
	}

	function createPwMail()
	{
		$this->createMailHeader('txt', 0);
		$this->createOutputPwMail();
		$this->setPwMailSubject();
		if($this->bUseMail) {
			$this->sendMail($this->ap['email']);
		}
	}

	function createMailHeader($type = 'txt', $isfor = 0)
	{
		//TS 27.12.2016: PHP_EOL statt newlines verwenden.
		$eol = PHP_EOL;
		
		if($isfor == 0) {
			//$header = 'From: "'.$this->ap['shopname'].'" <'.$this->ap['recipient'].'>';
			$header = 'From: "'.$this->ap['shopname'].'" <'.$this->ap['recipient'].'>'.$eol;
		} else {
			//$header = 'From: '.$this->ap['email'];
			$header = 'From: '.$this->ap['email'].$eol;
		}
		
		//$header .= '\nMIME-Version: 1.0';
		$header .= 'Reply-To: '.$this->ap['email'].$eol;
		$header .= 'MIME-Version: 1.0'.$eol;

		if($type == 'txt') {
			if($this->ap['gsAttachment']==1 && $isfor == 1) {
				$semi_rand = md5(time());
				$this->mime_boundary = '==Multipart_Boundary_x'.$semi_rand.'x';
				//$header .= '\nContent-Type: multipart/mixed;\n'.' boundary="'.$this->mime_boundary.'"';
				$header .= 'Content-Type: multipart/mixed;'.$eol;
				$header .= 'boundary="'.$this->mime_boundary.'"'.$eol;
				//$header .= "Content-Type: multipart/mixed;".$this->slbreak;
				//$header .= " boundary=\"".$this->mime_boundary."\"".$this->slbreak;
			} else {
				$charset = 'utf-8';
				$trfenc = 'base64';
				/*$header .= '\nContent-type: text/plain; charset="' . $charset . '"\n';
				$header .= 'Content-Transfer-Encoding: ' . $trfenc . '\n';
				$header .= 'X-Mailer: GS-ShopMail V'.$this->sVersion.' [GS ShopBuilder] - www.gs-shopbuilder.com\n';*/
				$header .= 'Content-type: text/plain; charset="' . $charset . '"'.$eol;
				$header .= 'Content-Transfer-Encoding: '.$trfenc.$eol;
				$header .= 'X-Mailer: GS-ShopMail V'.$this->sVersion.' [GS ShopBuilder] - www.gs-shopbuilder.com'.$eol;
			}
		}
		if($type == 'htm') {
			$uniqueid = md5(uniqid(rand()));
			//A TS 15.11.2016: Auch hier Anhang berücksichtigen
			/*$uniqueid = md5(uniqid(rand()));
			$this->sboundary1 = "----GS-=_SBMailPart_00_".$uniqueid;
			$this->sboundary2 = "----GS-=_SBMailPart_01_".$uniqueid;
			$header .= "\nContent-type: multipart/related; type=\"multipart/alternative\"; boundary=\"".$this->sboundary1."\";\n";
			$header .= "X-Mailer: GS-ShopMail V".$this->sVersion." [GS ShopBuilder] - www.gs-shopbuilder.com / www.gs-shopbuilder.de\n";
			$header .= "This is a multi-part message in MIME format.\n\n";*/
			if($this->ap['gsAttachment']==1 && $isfor == 1) {
				//Mit Anhang
				$this->sboundaryMix = '----GS-=_SBMailPart_MIX_'.$uniqueid;
				$this->sboundary1 = '----GS-=_SBMailPart_ALT_'.$uniqueid;
				$this->sboundary2 = '----GS-=_SBMailPart_REL_'.$uniqueid;
				//Mixed-Sektion
				/*$header .= 'X-Mailer: GS-ShopMail V'.$this->sVersion.' [GS ShopBuilder] - www.gs-shopbuilder.com / www.gs-shopbuilder.de\n';
				$header .= 'Content-type: multipart/mixed; boundary="'.$this->sboundaryMix.'"\n\n';
				$header .= 'This is a multi-part message in MIME format.\n';*/
				$header .= 'X-Mailer: GS-ShopMail V'.$this->sVersion.' [GS ShopBuilder] - www.gs-shopbuilder.com / www.gs-shopbuilder.de'.$eol;
				$header .= 'Content-type: multipart/mixed; boundary="'.$this->sboundaryMix.'"'.$eol;
				$header .= 'This is a multi-part message in MIME format'.$eol;
				
				//Alternative-Sektion
				//$header .= '--'.$this->sboundaryMix.'\n';
				$header .= '--'.$this->sboundaryMix.$eol;
				//$header .= "\nContent-type: multipart/related; type=\"multipart/alternative\"; boundary=\"".$this->sboundary1."\";\n";
				//$header .= 'Content-type: multipart/alternative; boundary="'.$this->sboundary1.'"\n\n';
				$header .= 'Content-type: multipart/alternative; boundary="'.$this->sboundary1.'"'.$eol;
			} else {
				//Ohne Anhang
				$this->sboundary1 = '----GS-=_SBMailPart_ALT_'.$uniqueid;
				$this->sboundary2 = '----GS-=_SBMailPart_REL_'.$uniqueid;
				//$header .= "\nContent-type: multipart/related; type=\"multipart/alternative\"; boundary=\"".$this->sboundary1."\";\n";
				/*$header .= 'X-Mailer: GS-ShopMail V'.$this->sVersion.' [GS ShopBuilder] - www.gs-shopbuilder.com / www.gs-shopbuilder.de\n';
				$header .= 'Content-type: multipart/alternative; boundary="'.$this->sboundary1.'"\n\n';
				$header .= 'This is a multi-part message in MIME format.\n';*/
				$header .= 'X-Mailer: GS-ShopMail V'.$this->sVersion.' [GS ShopBuilder] - www.gs-shopbuilder.com / www.gs-shopbuilder.de'.$eol;
				$header .= 'Content-type: multipart/alternative; boundary="'.$this->sboundary1.'"'.$eol;
				$header .= 'This is a multi-part message in MIME format.'.$eol;
			}
			//E TS 15.11.2016: Auch hier Anhang berücksichtigen
		}
		//$header .= "Cc: info@example.com".$this->slbreak;
		//$header .= "Bcc: info@example.com".$this->slbreak;
		$this->sMailHeader = $header;
	}

	function setCustomerSubject()
	{
		$this->sMailSubject = $this->ap['answer_subject'];
	}

	function setShopownerSubject()
	{
		$this->sMailSubject = $this->ap['subject'];
	}

	function setPwMailSubject()
	{
		$this->sMailSubject = $this->ap['shopname']." - ".$this->ap['userdata'];
	}

	function sendMail($to)
	{
		/*echo "<br>to=".$to.", sMailSubject=".$this->sMailSubject."<br> sMailHeader=".$this->sMailHeader;
		echo "<br>|".$this->sMailContent."|<br>";
		die($to);*/
		$cod = mb_detect_encoding($this->sMailContent);
		if($cod != "UTF-8") {
			$this->sMailContent = iconv($cod,'UTF-8',$this->sMailContent);
		}
		$this->bMailSend = mail($to, $this->sMailSubject, $this->sMailContent, $this->sMailHeader);
		$this->bMailSend;
		//die("Fertig");
	}

//A UR 17.2.2011		
	function setReviewLinksIfShopOwnerWished($template,$answerModus)
	{
		$arItemNames = array();
		$arItemLinks = array();
		$lo = new shoplog();
		$obj = $lo->getSettings("reviewLinksInEmail");
		$reviewLinksInEmail = $obj->reviewLinksInEmail;
		$shopurl="";
		$pass = "";
		$strLinks = "";
		if ($reviewLinksInEmail == 1)
		{
			$shopurl = $this->ap['_LANGTAGFNFIELDSHOPURL_'];
			$shopname = $this->ap['shopname'];
			$cd = $lo->getCustomerData2($this->ap['email']);
			$pass = $cd->cusPassword;
			$custId = $cd->cusIdNo;
			foreach($this->ap as $key => $value)
			{
				if (strpos($key,"_LANGTAGFNFIELDTEXTITEM_") !== false) 
				{
					$pos = strpos($value,' ');
					if ($pos >0)
					{
						$arItemNames[] = $value;
						$itemnr = substr($value, 0, $pos);
						$linkvalue = "item=".$itemnr;
						$arItemLinks[] = $linkvalue;
					}
				}
			}			
			$strParameter =	"cusid=".$this->ap['email']."&cuspass=".$pass;
			// Die nachfolgenden 2 Zeilen sind nur, weil unser kostenloser CMailServer (für lokalen Test) 
			// das Gleichheitszeichen '=' nicht ordentlich als Referenz übertragen kann. 
			$strParameter = base64_encode($strParameter);
			$strParameter = str_replace("=","%",$strParameter);
			
			$lf = "\n\n";
			$href="";
			$href2="";
			if ($answerModus =='html')
			{
				$lf = "<br>";
				$href = "&nbsp;<a href=\"";
				$href2 = "\">".$shopname."<a>";
			}
			$strLinks = $this->ap['your_rating'].":".$lf.
								 $this->ap['your_rating_shop'].": ".
								 $href.
									 $shopurl."index.php?page=gs_addshopcomment&cusId=".$custId.$href2.$lf;
								 
			$count = count($arItemLinks);
			for ($i = 0; $i < $count; $i++) 
			{
				$strParameter = $arItemLinks[$i]."&cusid=".$this->ap['email']."&cuspass=".$pass;
				// Die nachfolgenden 2 Zeilen sind nur, weil unser kostenloser CMailServer (für lokalen Test) 
				// das Gleichheitszeichen '=' nicht ordentlich als Referenz übertragen kann. 
				$strParameter = base64_encode($strParameter);
				$strParameter = str_replace("=","%",$strParameter);
								 
				$strLinks .= $this->ap['your_rating_acticle'].":";
				if ($answerModus =='html')
				{
					$strLinks .= $href.$shopurl."index.php?page=gs_addcomment&cusId=".$custId."&itemNo=".$this->ap['itemId'.($i+1)];
					$strLinks .= "\">";
					$strLinks .= trim($arItemNames[$i])."<a>";
				}
				else
				{
					$strLinks .= " \"".trim($arItemNames[$i]);
					$strLinks .= "\" ";
					$strLinks .= $shopurl."index.php?page=gs_addcomment&cusId=".$custId."&itemNo=".$this->ap['itemId'.($i+1)];
				}
				$strLinks .= $lf;
			}
			$strLinks .= "\n\n";
		}
		$template = str_replace("{_LANGTAGFN_REVIEW_LINKS_IN_EMAIL_}",$strLinks,$template);
		
		//Testausgabe
		if ($answerModus =='text')
		{
			$strLinks = str_replace("\n","<br>",$strLinks);
		}
		return $template;
	}
//E UR

	function processTextTemplate($answerModus)
	{
		$this->cleanUpTextData('answer_greeting_text');
		$this->cleanUpTextData('answer_subject');
		$this->cleanUpTextData('answer_customer_infos');
		$this->cleanUpTextData('answer_provider');
		$this->cleanUpTextData('answer_text_end');
		$filename = $this->sTemplatePath.'template/'.$this->sTextTemplateFilename;
		if(!@$handle = fopen($filename, "rb"))
		{
			$this->useOtherMailScript();
		}
		else
		{
			$template = fread($handle, filesize($filename));
			fclose($handle);
		}
	
	if( (strlen($this->ap['_LANGTAGFNFIELDSHIPPINGFIRSTNAME_']) == 0) ||
		(strlen($this->ap['_LANGTAGFNFIELDSHIPPINGLASTNAME_']) ==	0) || 
		(strlen($this->ap['_LANGTAGFNFIELDSHIPPINGSTREET_']) ==	0) ) 
		{
			//Dann keine Lieferadresse in die Mail ...
			$suchmuster = '/Lieferanschrift.*{_LANGTAGFNFIELDSHIPPINGCITY_}\s.*-/sx';
			$ersetzung = '';
			/*$template = preg_replace($suchmuster, $ersetzung, $template);*/
			$template = preg_replace_callback($suchmuster, function ($m) { return ''; }, $template);
		
		}
		$template = $this->prepareTemplate($template);
		$exception = array("answer_subject",
											 "answer_greeting_text",
											 "answer_customer_infos",
											 "answer_provider",
											 "answer_text_end",
											 "email");
		//echo "ap-Array in processTextTemplate:<pre>";
		//print_r($this->ap);
		//die("</pre>");
		foreach($this->ap as $key => $value)
		{
			//echo "key=$key, value=$value<br>";
			if(!in_array($key, $exception))
			{
				$keysize = strlen($key);
				$key = substr($key,0,$keysize);
			}	
			$template = str_replace("{".$key."}", $value, $template);
		}
		//A TS 02.09.2014: Zusätzliche Felder
		$template = str_replace("{GSSE_INCL_ADDFIELDS}", $this->place_additional_fields(), $template);
		//E TS
		$template = str_replace("{_LANGTAGFNFIELDPAYMENTCHARGE_}","0.00",$template);

//A UR 14.2.2011		
		$template = $this->setReviewLinksIfShopOwnerWished($template,$answerModus);
//E UR
	//die("Codierung: " . mb_detect_encoding($template));
	//$this->sMailContent = iconv('UTF-8','ISO-8859-2',$template);
		$this->sMailContent = chunk_split(base64_encode($template));
	}

//A UR
	function gibPostLaenderKuerzelVonIntern($land_shopintern)
	{
		$landPost = $land_shopintern;
		if ($land_shopintern == 'AT')
		{
			$landPost = 'A';
		}
		else if ($land_shopintern == 'AD')
		{
			$landPost = 'AND';
		}
		else if ($land_shopintern == 'BE')
		{
			$landPost = 'B';
		}
		else if ($land_shopintern == 'DE')
		{
			$landPost = 'D';
		}
		else if ($land_shopintern == 'EE')
		{
			$landPost = 'EST';
		}
		else if ($land_shopintern == 'FI')
		{
			$landPost = 'FIN';
		}
		else if ($land_shopintern == 'FR')
		{
			$landPost = 'F';
		}
		else if ($land_shopintern == 'HU')
		{
			$landPost = 'H';
		}
		else if ($land_shopintern == 'IT')
		{
			$landPost = 'I';
		}
		else if ($land_shopintern == 'IE')
		{
			$landPost = 'IRL';
		}
		else if ($land_shopintern == 'LU')
		{
			$landPost = 'L';
		}
		else if ($land_shopintern == 'MT')
		{
			$landPost = 'M';
		}
		else if ($land_shopintern == 'NO')
		{
			$landPost = 'N';
		}
		else if ($land_shopintern == 'PT')
		{
			$landPost = 'P';
		}
		else if ($land_shopintern == 'SE')
		{
			$landPost = 'S';
		}
		else if ($land_shopintern == 'SI')
		{
			$landPost = 'SLO';
		}
	
		return	$landPost;	
	}

//E UR

	function processHTMLTemplate()
	{
		$this->sCID = "cid_".md5(uniqid(rand()));
		$this->cleanUpHTMLData('answer_greeting_text');
		$this->cleanUpHTMLData('answer_subject');
		$this->cleanUpHTMLData('answer_customer_infos');
		$this->cleanUpHTMLData('answer_provider');
		$this->cleanUpHTMLData('answer_text_end');
		$filename = $this->sTemplatePath.'template/'.$this->sHTMLTemplateFilename;
		//die($filename);
		if(!@$handle = fopen($filename, "rb"))
		{
			$this->useOtherMailScript();
		}
		else
		{
			$template = fread($handle, filesize($filename));
			fclose($handle);
		}
		//var_dump($template);
		$template = $this->prepareTemplate($template);
		
		
		$exception = array("answer_subject",
											 "answer_greeting_text",
											 "answer_customer_infos",
											 "answer_provider",
											 "answer_text_end",
											 "email");
		/*echo "<pre>";
		print_r($this->apv);
		die("</pre>");*/
		foreach($this->apv as $key => $value)
		{
			if(!in_array($key, $exception))
			{
				$keysize = strlen($key);
				$key = substr($key,0,$keysize);
			}
			//echo "key=$key, value=$value<br>";
		
			//A TS 27.11.2012 Quoted Printable in HTML
			if(strpos($key,"_LANGTAGFNFIELDTEXTITEM_") !== false)
			{
				$value = $this->qp2HTML($value);
		
			//echo "Decoded: key=$key, value=$value<br>";
			}
			$template = str_replace("{".$key."}", $value, $template);
		}
		
		//A TS 03.09.2014: Zusätzliche Felder
		$template = str_replace("{GSSE_INCL_ADDFIELDS}", str_replace("\n","<br />",$this->place_additional_fields()), $template);
		//E TS
	
		$template = str_replace("{_LANGTAGFNFIELDPAYMENTCHARGE_}","0.00",$template);
//A UR 18.2.2011		
		$template = $this->setReviewLinksIfShopOwnerWished($template,'html');
//E UR			
		
		$pos = strrpos($_SERVER['PHP_SELF'],"/");
		$path = substr($_SERVER['PHP_SELF'],0,$pos+1);

		$template = str_replace("{url}", "http://".$_SERVER['SERVER_NAME'].$path, $template);
		$template = $this->setEmailLayout($template);

		//$htmlpart = iconv('iso-8859-1','UTF-8',str_replace("{cid}", $this->sCID, $template));
		$htmlpart = str_replace("{cid}", $this->sCID, $template);
		$this->processTextTemplate('html');
		$textpart = $this->sMailContent;
		//TS 22.07.2016: Image nicht aus DYNSB laden, sondern aus dem images-Ordner,
		//weil das E-Mail-Logo dem kleinen Logo aus den Einstellungen entsprechen soll
		//$img = $this->sTemplatePath."dynsb/image/upload/".$this->sEMailLogoFilename;
		$img = $this->sTemplatePath."images/".$this->sEMailLogoFilename;
		if(!@$fd = fopen($img, "rb"))
		{
			#$imgpart = $this->getDefaultSB5Gif();
		}
		else
		{
			$fbuffer = fread($fd, filesize($img));
			$imgpart = chunk_split(base64_encode($fbuffer));
			fclose($fd);
		}
		//1. Textteil
		$mail = "--".$this->sboundary1."\n";
		$mail .= "Content-Type: text/plain; charset=UTF-8\n";
		$mail .= "Content-Transfer-Encoding: base64\n\n";
		$mail .= chunk_split(base64_encode($textpart));
		$mail .= "\n";
		//2. Relative-Teil
		$mail .= "--".$this->sboundary1."\n";
		$mail .= "Content-Type: multipart/related; boundary=\"".$this->sboundary2."\"\n\n";
		//2.a. HTML-Teil
		$mail .= "--".$this->sboundary2."\n";
		$mail .= "Content-Type: text/html; charset=UTF-8\n";
		$mail .= "Content-Transfer-Encoding: base64\n\n";
		$mail .= chunk_split(base64_encode($htmlpart));
		$mail .= "\n";
		//2.b. Bild
		$mail .= "--".$this->sboundary2."\n";
		$mail .= "Content-Type: image/gif; name=\"".$this->sEMailLogoFilename."\"\n";
		$mail .= "Content-Transfer-Encoding: base64\n";
		$mail .= "Content-ID: <".$this->sCID.">\n";
		$mail .= "Content-Disposition: inline; filename=\"".$this->sEMailLogoFilename."\"\n\n";
		$mail .= $imgpart;
		$mail .= "\n";
		//Ende 2.
		$mail .= "--".$this->sboundary2."--\n\n";
		//Ende 1.
		$mail .= "--".$this->sboundary1."--\n\n";
		/*$mail = "--".$this->sboundary1."\n";
		$mail .= "Content-Type: multipart/alternative; boundary=\"".$this->sboundary2."\"\n";
		//$mail .= "Content-Type: multipart/mixed; boundary=\"".$this->sboundary2."\"\n";
		$mail .= "\n\n";
		$mail .= "--".$this->sboundary2."\n";
		$mail .= "Content-Type: text/plain; charset=\"utf-8\"\n";
		$mail .= "Content-Transfer-Encoding: base64\n";
		$mail .= "\n";
		$mail .= chunk_split(base64_encode($textpart));
		$mail .= "\n";
		$mail .= "--".$this->sboundary2."\n";
		$mail .= "Content-Type: text/html; charset=\"utf-8\"\n";
		$mail .= "Content-Transfer-Encoding: base64\n";
		$mail .= "\n";
		$mail .= chunk_split(base64_encode($htmlpart));
		//$mail .= $htmlpart;
		$mail .= "\n";
		$mail .= "--".$this->sboundary2."--\n";
		$mail .= "\n";
		$mail .= "--".$this->sboundary1."\n";
		$mail .= "Content-Type: image/gif; name=\"".$this->sEMailLogoFilename."\"\n";
		$mail .= "Content-Transfer-Encoding: base64\n";
		$mail .= "Content-ID: <".$this->sCID.">\n";
		$mail .= "\n";
		$mail .= $imgpart;
		$mail .= "\n";
		$mail .= "--".$this->sboundary1."--\n";*/
		$this->sMailContent = "";
		$this->sMailHeader .= $mail;
	}

	function getFormattedAddresses($LanguageSettings)
	{

		$arrWords = explode("\n", $LanguageSettings);
		$arrWords2 = array();
		for($i = 0; $i < count($arrWords); $i++){
			//echo strlen($arrWords[$i])." Piece $i = $arrWords[$i] <br />";
			$pos4 = strpos($arrWords[$i], '=');
			if ($pos4 !== false)
			{
				 $key = substr($arrWords[$i],0,$pos4); 
				 $value = substr($arrWords[$i],$pos4+1,strlen($arrWords[$i])-$pos4-2); 
				 $arrWords2[$key] = $value;
			}					
		}
			
		$arrAnschrift = array();
		
		$bShippingAddress = 0;
		if ($this->apv['_LANGTAGFNFIELDSHIPPINGCITY_'] != NULL &&
		strlen($this->apv['_LANGTAGFNFIELDSHIPPINGCITY_']) > 0 &&
		strlen($this->apv['_LANGTAGFNFIELDSHIPPINGLASTNAME_']) > 0 &&
		strlen($this->apv['_LANGTAGFNFIELDSHIPPINGZIPCODE_']) > 0 )
		{ 
			$arrAnschrift[] = "<b>".$arrWords2['LangTagBillingAddress'].":</b>";
			$bShippingAddress = 1;
			$arrShipping = array();
			$arrShipping[] = "<b>".$arrWords2['LangTagShippingAddress'].":</b>";
			$arrShipping[] = ' ';
			if (strlen($this->apv['_LANGTAGFNFIELDSHIPPINGCOMPANY_']) > 0)
			{
				$arrShipping[] = $arrWords2['LangTagFieldCompany'].' '.$this->apv['_LANGTAGFNFIELDSHIPPINGCOMPANY_'];
				$arrShipping[] = ' ';
				$strTemp = $this->apv['_LANGTAGFNFIELDSHIPPINGFORMTOADDRESS_']." ".$this->apv['_LANGTAGFNFIELDSHIPPINGFIRSTNAME_']." ".$this->apv['_LANGTAGFNFIELDSHIPPINGLASTNAME_'];
				$arrShipping[] = $strTemp;
			}
			else
			{
				$arrShipping[] = $this->apv['_LANGTAGFNFIELDSHIPPINGFORMTOADDRESS_'];
				$strTemp = $this->apv['_LANGTAGFNFIELDSHIPPINGFIRSTNAME_']." ".$this->apv['_LANGTAGFNFIELDSHIPPINGLASTNAME_'];
				$arrShipping[] = $strTemp;
			}
			if (strlen($this->apv['_LANGTAGFNFIELDSHIPPINGADDRESS2_']) > 0)
			{				
				$arrShipping[] = $this->apv['_LANGTAGFNFIELDSHIPPINGADDRESS2_'];
			}
			$arrShipping[] = $this->apv['_LANGTAGFNFIELDSHIPPINGSTREET_'];

			$strTemp = $this->gibPostLaenderKuerzelVonIntern($this->apv['_LANGTAGFNFIELDSTATE_']);
			$strTemp = $strTemp.'-'.$this->apv['_LANGTAGFNFIELDSHIPPINGZIPCODE_'].' '.$this->apv['_LANGTAGFNFIELDSHIPPINGCITY_'];
			$arrShipping[] = $strTemp;
			$arrShipping[] = ' ';
		}
		else
		{
			$arrAnschrift[] = "<b>".$arrWords2['LangTagBillingAddress']." & ".$arrWords2['LangTagShippingAddress'].":</b>";
		}
		$arrAnschrift[] = ' ';
		if ((strlen($this->apv['_LANGTAGFNFIELDCUSTOMERNR_']) > 0) || (strlen($this->apv['_LANGTAGFNFIELDFIRMVATID_']) > 0))
		{
			if (strlen($this->apv['_LANGTAGFNFIELDCUSTOMERNR_']) > 0)
			{
				$arrAnschrift[] = $arrWords2['LangTagFieldCustomerNR'].": &nbsp;".$this->apv['_LANGTAGFNFIELDCUSTOMERNR_'];
			}
			if (strlen($this->apv['_LANGTAGFNFIELDFIRMVATID_']) > 0)
			{
				$arrAnschrift[] = $arrWords2['LangTagFieldFirmVATId'].": &nbsp;".$this->apv['_LANGTAGFNFIELDFIRMVATID_'];
			}
			$arrAnschrift[] = ' ';
		} 
		if (strlen($this->apv['_LANGTAGFNFIELDCOMPANY_']) > 0)
		{
				$arrAnschrift[] = $arrWords2['LangTagFieldCompany'].' '.$this->apv['_LANGTAGFNFIELDCOMPANY_'];
				$strTemp = $this->apv['_LANGTAGFNFIELDFORMTOADDRESS_']." ".$this->apv['_LANGTAGFNFIELDFIRSTNAME_']." ".$this->apv['_LANGTAGFNFIELDLASTNAME_'];
				$arrAnschrift[] = $strTemp;
		}
		else
		{
				$arrAnschrift[] = $this->apv['_LANGTAGFNFIELDFORMTOADDRESS_'];
				$strTemp = $this->apv['_LANGTAGFNFIELDFIRSTNAME_']." ".$this->apv['_LANGTAGFNFIELDLASTNAME_'];
				$arrAnschrift[] = $strTemp;
		}
		if (strlen($this->apv['_LANGTAGFNFIELDADDRESS2_']) > 0)
		{				
			$arrAnschrift[] = $this->apv['_LANGTAGFNFIELDADDRESS2_'];
		}
		$arrAnschrift[] = $this->apv['_LANGTAGFNFIELDADDRESS_'];
		
		$strTemp = $this->gibPostLaenderKuerzelVonIntern($this->apv['_LANGTAGFNFIELDSTATE_']);
		$strTemp = $strTemp.'-'.$this->apv['_LANGTAGFNFIELDZIPCODE_'].' '.$this->apv['_LANGTAGFNFIELDCITY_'];
		$arrAnschrift[] = $strTemp;

		$arrAnschrift[] = ' ';

		$arrAnschrift[] = $arrWords2['LangTagFieldEmail'].": &nbsp;".$this->apv['email'];
		$arrAnschrift[] = 'Tel.: '.$this->apv['_LANGTAGFNFIELDPHONE_'];
		if (strlen($this->apv['_LANGTAGFNFIELDFAX_']) > 0)
		{				
			$arrAnschrift[] = $arrWords2['LangTagFieldFax'].": &nbsp;".$this->apv['_LANGTAGFNFIELDFAX_'];
		}
		$arrAnschrift[] = $arrWords2['AdditionalField1HiddenValue'].": &nbsp;".$this->apv['_LANGTAGFNFIELDGEBURTSDATUM_'];

		if (strlen($this->apv['_LANGTAGFNFIELDMESSAGE_']) > 0)
		{
			$arrAnschrift[] = ' ';
			$arrAnschrift[] = $this->apv['_LANGTAGFNFIELDMESSAGE_'];
		}
		$arrAnschrift[] = ' ';

		if (bShippingAddress != 0 && count($arrAnschrift)<count($arrShipping))
		{
			$arrAnschrift[] = ' ';
		}

		$strContent = $strContent.'<table border="0" cellpadding="2" cellspacing="0" width="80%">';
		$TdKlasseLinks = 'tabheadbordertopleftright';
		$TdKlasseRechts = '';
		if ($bShippingAddress != 0)
		{
			$TdKlasseLinks = 'tabheadbordertopleft';
			$TdKlasseRechts = 'tabheadbordertopright';
		}
		for($i=0; $i < count($arrAnschrift); $i++)
		{
			$strContent = $strContent.'<tr	class="tabbody">';
			$strContent = $strContent.'<td	class="'.$TdKlasseLinks.'">';
			$strContent = $strContent."&nbsp;&nbsp;&nbsp;".$arrAnschrift[$i]."&nbsp;&nbsp;&nbsp;"."<br>";
			$strContent = $strContent."</td>";
			if ($bShippingAddress != 0)
			{
				$strContent = $strContent.'<td	class="'.$TdKlasseRechts.'">';
				if ($i < count($arrShipping))
				{
					$strContent = $strContent."&nbsp;&nbsp;&nbsp;".$arrShipping[$i]."&nbsp;&nbsp;&nbsp;"."<br>";
				}
				else
				{
					$strContent = $strContent."&nbsp<br>";
				}
				$strContent = $strContent."</td>";
				$TdKlasseLinks = 'tabborderleft';
				$TdKlasseRechts = 'tabborderright';
				if ($i == count($arrAnschrift)-2)
				{
					$TdKlasseLinks = 'tabborderbottomleft';
					$TdKlasseRechts = 'tabborderbottomright';
				}
				else if ($i == 0)
				{
					$TdKlasseLinks = 'tabheadbordertopleft';
					$TdKlasseRechts = 'tabheadbordertopright';
				}
			}
			else
			{
				$TdKlasseLinks = 'tabborderleftright';
				if ($i == count($arrAnschrift)-2)
				{
					$TdKlasseLinks = 'tabborderbottomleftright';
				}
				else if ($i == 0)
				{
					$TdKlasseLinks = 'tabheadbordertopleftright';
				}				
			}
			$strContent = $strContent."</tr>";
		}
		$strContent = $strContent.'</table>';
		
		return $strContent;
	}

	function setEmailLayout($template)
	{
			 
		$lo = new shoplog();
		$layout = $lo->getOrderEmailLayout();

		if($layout->ordEmailImageXsize!="0")
		{
			$ImageXsize = "width='".$layout->ordEmailImageXsize."'";
		}
		else
		{
			$ImageXsize = "";
		}

		if($layout->ordEmailImageYsize!="0")
		{
			$ImageYsize = "height='".$layout->ordEmailImageYsize."'";
		}
		else
		{
			$ImageYsize = "";
		}

		if($layout->ordEmailImage!="" && $layout->ordEmailImage!=null)
		{
			$Image = " ".$ImageXsize." ".$ImageYsize;
		}
		else
		{
			$Image = "";
		}

		$this->sEMailLogoFilename = $layout->ordEmailImage;
		
//A UR
		$pos1 = strpos($template, '(IFUSEFORMATMAILADDRESS)');
		$pos2 = strpos($template, '(ELSEIFUSEFORMATMAILADDRESS)');
		$pos3 = strpos($template, '(ENDIFUSEFORMATMAILADDRESS)');

		if ($pos1 !== false && $pos2 !== false && $pos3 !== false )
		{
			if ($layout->useFormatMailAddress=='1')
			{
				$LanguageSettings = substr($template,$pos1+24,$pos2-$pos1-24);
				$strContent = $this->getFormattedAddresses($LanguageSettings);				
				$template = substr_replace($template,$strContent,$pos1,$pos3-$pos1+27);
				
			}
			else
			{
				$template = str_replace('(ENDIFUSEFORMATMAILADDRESS)', '', $template);	
				$template = substr_replace($template,"",$pos1,$pos2-$pos1+28);
			}
		}
		//echo $template;
//E UR											 
		
		$repl_char = array("{logoimage_size}"=>$Image,	
											 "{color_bground}"=>$layout->ordEmailBground,
											 "{color_text}"=>$layout->ordEmailText,
											 "{color_title}"=>$layout->ordEmailTitle,
											 "{color_tabhead_bg}"=>$layout->ordEmailTabheadBg,
											 "{color_tabhead_text}"=>$layout->ordEmailTabheadText,
											 "{color_tab_border}"=>$layout->ordEmailTabBorder,
											 "{color_tabbody_bg}"=>$layout->ordEmailTabbodyBg,
											 "{color_tabbody_text}"=>$layout->ordEmailTabbodyText );
		foreach($repl_char as $key => $value)
		{
			$template = str_replace($key, $value, $template);
		} 
		$template = "<tr><td>".$template."</td></tr>";
		return $template;
	}

	function addDoNotSend($val)
	{
		array_push($this->aAdditionalDoNotSend, $val);
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
		$text = "email: ".$this->ap['email']."\n";
		$text .= "weekday: ".date('w')."\n";
		$text .= "date: ".date('Y-m-d')."\n";
		$text .= "time: ".date('H:i:s')."\n";
		$text .= "germandate: ".$this->getDay($day).", ".date('d').".".$this->getMonth($month)." ".date('Y')." um ".date('H:i:s')."\n";
		$text .= "englishdate: ".$day.", ".$month." ".date('d').", ".date('Y')." at ".date('H:i:s')."\n";
		$text .= $sep;
		$text .= "\n";
		foreach($this->ap as $key => $value)
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

	function createOutputPwMail()
	{
		$loginPage = 'createcustomer';
		if(isset($this->ap['CUST_LOGIN_PAGE'])) {
			if($this->ap['CUST_LOGIN_PAGE'] != '') {
				$loginPage = $this->ap['CUST_LOGIN_PAGE'];
			}
		}
		$this->cleanUpTextData('logindata_email_text1');
		$this->cleanUpTextData('logindata_email_text2');
		$mailtxt	= $this->ap['dear']." ".$this->ap['_LANGTAGFNFIELDFORMTOADDRESS_']." ".$this->ap[$this->FirstName]." ".$this->ap[$this->LastName].",\n";
		$mailtxt .= "\n".$this->ap['logindata_email_text1']."\n\n";
		$mailtxt .= "\n".$this->ap['user'].": ".$this->ap['email']."\n";
		$mailtxt .= $this->ap['password'].": ".$this->userpass."\n";
		$mailtxt .= $this->ap['_LANGTAGFNFIELDSHOPURL_'] . "index.php?page=" . $loginPage . "\n";
		$mailtxt .= "\n".$this->ap['logindata_email_text2']."\n\n".$this->ap['shopname'];
		//die(mb_detect_encoding($mailtxt));
		//$this->sMailContent = urlencode($mailtxt);
		$this->sMailContent = chunk_split(base64_encode($mailtxt));
	}

	function createOutput4SBGSMailclient()
	{
		$day = date("l");
		$month = date("F");
		$out = "";
		$sep = "---------------------------------------------------------------------------\n";
		$aDoNot = array('sid', 'recipient', 'answer_customer_infos', 'password', 'user',
										'dear', 'logindata_email_text1', 'logindata_email_text2',
										'answer_greeting_text', 'answer_customer_text', 'answer_provider',
										'answer_text', 'answer_text_end', 'subject', 'userdata',
										'qtyofpos', 'shopname', 'redirect', 'answer_subject',
										'SumDiscount2', 'CashDiscountValue', 'CashDiscountPrct',
										'emailsender', 'rememberme', 'button', 'button2',
										'DiscountPrct', 'DiscountValue', 'SumDiscount1', 'PaymentCharge',
										'LongVatPrct1', 'LongVatPrct2', 'LongVatPrct3', 'pid',
										'LongVatValue1', 'LongVatValue2', 'LongVatValue3', '_LANGTAGFNFIELDEMAILFORMAT_',
										'position', 'currency', 'encluded', 'billingAddress', 'shippingAddress',
										'goodsvalue', 'MailScriptURL', 'VersionString', 'gsAttachment',
										'Charset',
										'gsremember_orderform__LANGTAGFNFIELDCITY_', 'gsremember_orderform__LANGTAGFNFIELDZIPCODE_',
										'gsremember_orderform__LANGTAGFNFIELDSTATE_', 'gsremember_orderform_email',
										'gsremember_orderform__LANGTAGFNFIELDPHONE_', 'gsremember_orderform__LANGTAGFNFIELDFAX_',
										'gsremember_orderform__LANGTAGFNFIELDMESSAGE_', 'gsremember_orderform__LANGTAGFNTERMSANDCOND_',
										'gsremember_orderform_rememberme', 'gsremember_orderform__LANGTAGFNFIELDEMAILFORMAT_',
										'gsremember_orderform_', 'gsremember_customerlogin_returnpath',
										'gsremember_customerlogin_cusDiscount', 'gsremember_customerlogin_userid',
										'gsremember_customerlogin_password', 'gsremember_customerlogin_',
										'gsremember_newsletter_form_email', 'gsremember_newsletter_form_subscribe',
										'gsremember_newsletter_form_unsubscribe', 'loginemail', 'secFieldReqCredCard',	
										'_LANGTAGFNTERMSANDCONDNEWSLETTER2_', '_BtnInvoiceAddress_',
										'LangTagTextYourRating', 'LangTagTextYourRatingShop', 'LangTagTextYourRatingArticle'
									 );
		if(count($this->aAdditionalDoNotSend) > 0)
			$aDoNot = array_merge($aDoNot, $this->aAdditionalDoNotSend);
		$out .= $this->ap['emailsender'].":\n";
		$out .= "(".$this->ap['email'].") ".$this->getDay($day).", ".date("j").".".$this->getMonth($month)." ".date("Y")." um ".date("H:i:s")."\n";
		$out .= $sep;
		$out .= "\n";
		foreach($this->ap as $key => $value)
		{
			if(substr($key, 0, strlen('ShortVatPrct')) != 'ShortVatPrct' &&
				substr($key, 0, strlen('ShortVatValue')) != 'ShortVatValue'
				)
			{
				if(!in_array($key, $aDoNot) && (strpos($key, 'DOWNLOADITM_') === false))
				{
					if(substr($key,0,8)!="_GSSBTXT")
					{
						if(substr($key,0,21)!= '_LANGTAGFNFIELDIMAGE_')
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
							if(substr($key,0,8)=="_LANGTAG")
							{
								$key = str_replace("_LANGTAG","_GSSBTXT",$key);
								$lastCharPos = strrpos($key, "_");
								$lastChar = substr($key, $lastCharPos+1);
								if($lastChar!="")
								{
									$tmp = $lastChar;
									settype($tmp,"integer");
									if($tmp!=0)
									{
										$key = substr($key, 0, $lastCharPos+1);
									}
									else
									{
										$pos = strrpos($key, "_");
										$lastChar = substr($key, $pos-1, 1);
										$lastChar = $lastChar.substr($key,$pos,strlen($key));
										$key = substr($key, 0, $pos-1);
									}
								}

								if(isset($this->ap[$key]))
								{
									if($this->ap[$key]!="")
									{
										$txt = $this->ap[$key];
									}
									else
									{
										$txt = $key;
									}
								}
								else
								{
									$txt = $key;
								}
							
								if($lastChar!="_")
								{ $txt = $txt." ".$lastChar; }
								//echo "<br>lastChar: ".$lastChar;
							}
							else
							{ $txt = $key; }
							//$out .= str_pad(iconv('UTF-8','ISO-8859-2',$txt), 30, " ", STR_PAD_RIGHT).": ".iconv('UTF-8','ISO-8859-2',$value)."\n";
							$out .= str_pad($txt, 30, " ", STR_PAD_RIGHT).": ".$value."\n";
						}
					}
				}
			}
		}
		if($this->ap['gsAttachment']==1)
		{
			//$data = chunk_split(base64_encode($this->createAttachment()));
			$data = $this->createAttachment();

			$content .= "This is a multi-part message in MIME format.\n\n"."--".$this->mime_boundary."\n"."Content-Type: text/plain; charset=\"utf-8\"\n"."Content-Transfer-Encoding: base64\n\n".chunk_split(base64_encode($out))."\n\n";

			$data = chunk_split(base64_encode($data));

			$file = "gsshopbuilder.txt";
			$content .= "--".$this->mime_boundary."\n"."Content-Type: text/plain; charset=\"utf-8\"\n name=\"".$file."\"\nContent-Disposition: attachment;\n filename=\"".$file."\"\nContent-Transfer-Encoding: base64\n\n".$data."\n\n--".$this->mime_boundary."--\n";

			/*$content .= "This is a multi-part message in MIME format.".$this->slbreak.$this->slbreak;
			$content .= "--".$this->mime_boundary.$this->slbreak;
			$content .= "Content-Type: text/plain; charset=".$this->ap['Charset'].$this->slbreak;
			$content .= "X-Mailer: [GS ShopBuilder] - www.gs-shopbuilder.com / www.gs-shopbuilder.de".$this->slbreak;
			$content .= "Content-Transfer-Encoding: 8bit".$this->slbreak.$this->slbreak;
			$content .= $out.$this->slbreak;
			$content .= "--".$this->mime_boundary.$this->slbreak;
			$content .= "Content-Type: text/plain; name=\"".$file."\"".$this->slbreak;
			$content .= "Content-Transfer-Encoding: quoted-printable".$this->slbreak;
			$content .= "Content-Disposition: inline; filename=\"".$file."\"".$this->slbreak;
			$content .= chr(13).chr(10).$data.$this->slbreak;*/

			$this->sMailContent = $content;
		}
		else
		{
			$this->sMailContent = chunk_split(base64_encode($out));
		}
	}

	function createPositionData($str)
	{
		$allpos = "";
		$sStartTag = '{posdata_start}';
		$sEndTag = '{posdata_end}';
		$atmp = explode($sStartTag, $str);
		$atmp2 = explode($sEndTag, $atmp[1]);
		$strpos = ltrim($atmp2[0]);
		
		for($x = 0; $x < intval($this->ap['qtyofpos']); $x++)
		{
			$cur_str = $strpos;
			$cur_str = str_replace("{_GSSBTXTFNFIELDITEMNUMBER_}", $this->ap['_GSSBTXTFNFIELDITEMNUMBER_'], $cur_str);
			$cur_str = str_replace("{_ITEMNUMBER_}", $this->ap['itemId'.($x+1)], $cur_str);
			$cur_str = str_replace("{_GSSBTXTFNFIELDQUANTITY_}", $this->ap['_GSSBTXTFNFIELDQUANTITY_'], $cur_str);
			$cur_str = str_replace("{_GSSBTXTFNFIELDTEXTUNITPRICE_}", $this->ap['_GSSBTXTFNFIELDTEXTUNITPRICE_'], $cur_str);
			$cur_str = str_replace("{_GSSBTXTFNFIELDSHORTVAT_}", $this->ap['_GSSBTXTFNFIELDSHORTVAT_'], $cur_str);
			$cur_str = str_replace("{_GSSBTXTFNFIELDTOTALPRICE_}", $this->ap['_GSSBTXTFNFIELDTOTALPRICE_'], $cur_str);
			$cur_str = str_replace("{currency}", $this->ap['currency'], $cur_str);
			$tmpstr = str_replace("{posno}", $x+1, $cur_str);
			$tmppos = str_replace("}", ($x+1)."}", $tmpstr);
			$tmppos = str_replace("url".($x+1)."}", "url}", $tmppos);
			$tmppos = str_replace("_LANGTAGFNFIELDSHOPURL_".($x+1)."}","_LANGTAGFNFIELDSHOPURL_}", $tmppos);
			$allpos .= $tmppos;
		}
		$newstr = $atmp[0].$allpos.$atmp2[1];
		return $newstr;

	}

	function prepareTemplate($tpl)
	{
		 foreach($this->aPrepareTemplate as $ptag => $aval)
		{
			$tpl = $this->checkTemplateEntry($tpl, $ptag, $aval);
		}
		$tpl = $this->createPositionData($tpl);
		return $tpl;
		
	}

	function checkTemplateEntry($tpl, $ptag, $aval)
	{
		 $sStartTag = "{".$ptag."_start}";
		$sEndTag = "{".$ptag."_end}";
		if(strpos($tpl, $sStartTag) === false)
			return $tpl;
		if(strpos($tpl, $sEndTag) === false)
			return $tpl;
		$atmp = explode($sStartTag, $tpl);
		$atmp2 = explode($sEndTag, $atmp[1]);
		$strcon = trim($atmp2[0]);
		if(isset($this->ap[$aval[0]]))
		{
			foreach($aval as $val)
			{
				$strcon = str_replace("{".strtolower($val)."}", $this->apv[$val], $strcon);
			}
			$newtpl = $atmp[0].$strcon.$atmp2[1];
		}
		else
		{
			$newtpl = $atmp[0].$atmp2[1];
		}
		return $newtpl;
	}

	function cleanUpAllPostData()
	{
		$this->apv = $this->ap;
		$curlen = strlen($this->sOrderCurrency);
		foreach($this->apv as $key => $val)
		{
			if(substr(trim($val), 0, $curlen) == $this->sOrderCurrency)
			{
				$this->apv[$key] = $this->getNumberFormat(trim(substr($val, $curlen)));
			}
		}
	}

	function getNumberFormat($str)
	{
		$str = sprintf("%01.2f", $str);
		if($this->iNumberFormat == 1)
			$str = str_replace(".", ",", $str);
		return $str;
	}

	function cleanUpTextData($val)
	{
		if(array_key_exists($val, $this->ap))
		{
			$str = str_replace("<br />", "\n", $this->ap[$val]);
			$text = str_replace("<br>", "\n", $str);
			$this->ap[$val] = $text;
		}
	}

	function cleanUpHTMLData($val)
	{
		if(array_key_exists($val, $this->ap))
		{
			$str = str_replace("<br />", "<br>", $this->ap[$val]);
			$this->ap[$val] = nl2br($str);
		}
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

	function redirect()
	{
		$_SESSION['CustData'] = $this->apv;
		header("Location: ".trim($this->ap['redirect']));
		die();
	}

	function useOtherMailScript()
	{
		$this->bUseMail = false;
		echo "<html>\n";
		echo "<head><title>dynsb orderform passthrough</title></head>";
		echo "<body>\n";
		echo "<form name=\"orderform\" method=\"POST\" action=\"".$this->sOrderURL."\">\n";
		foreach($this->ap as $key => $value)
		{
			echo "<input type=\"hidden\" name=\"".$key."\" value=\"".$value."\" />\n";
		}
		echo "</form>\n";
		echo "<script language=javascript>\n";
		echo "document.orderform.submit();\n";
		echo "</script>\n";
		echo "</body>\n";
		echo "</html>\n";
	}

	function getDefaultSB5Gif()
	{
		$s	= "R0lGODlhQABAAPcAAHuEjHuMjHuMlISUnIyUnJSlpZylnKWtpaW1pbW9rcbOtdbW3t7nvefv7+/v\n";
		$s .= "7+/v9+/39/eUEPecEPecIfelKfe1GPe9GPe9Iff39/+1Qv+1Uv+1Wv+9GP+9Mf+9Wv+9Y/+9a//G\n";
		$s .= "GP/GIf/GKf/GMf/GOf/Ga//Ge//OOf/OQv/OSv/Oe//OhP/OjP/WSv/WUv/WWv/WY//WjP/WlP/e\n";
		$s .= "Y//ea//ec//elP/enP/ne//nhP/njP/npf/nrf/vjP/vlP/vnP/vpf/vrf/vtf/3pf/3rf/3tf/3\n";
		$s .= "vf/3xv//vf//xv//zv//////////////////////////////////////////////////////////\n";
		$s .= "////////////////////////////////////////////////////////////////////////////\n";
		$s .= "////////////////////////////////////////////////////////////////////////////\n";
		$s .= "////////////////////////////////////////////////////////////////////////////\n";
		$s .= "////////////////////////////////////////////////////////////////////////////\n";
		$s .= "////////////////////////////////////////////////////////////////////////////\n";
		$s .= "////////////////////////////////////////////////////////////////////////////\n";
		$s .= "////////////////////////////////////////////////////////////////////////////\n";
		$s .= "////////////////////////////////////////////////////////////////////////////\n";
		$s .= "/////////////////////////////////////////////////////ywAAAAAQABAAAAI/gCZCBxI\n";
		$s .= "sKDBgwgTKlzoYIHDhxAjSpxIsaLFhw0wCMRAAIDHjyBDihxJsqTJjwEcbAxwgIHLlzBjypxJs6ZN\n";
		$s .= "BggANFiZAMkSJT59Al2CRMkRIUGSGjkiNOhPp0ObEn06NSpVnwp0asQQIIGSJWB/gj2i4wWJCxcs\n";
		$s .= "oFU7QkWNH0fEhq3xAsaLu3br4q0bwwaQuGG/Lsm6kwlXr2AFG4mxNq1jtZAdq9ghF8Vjx5cjq41h\n";
		$s .= "RPBXwlu7Cia6o7HmzI9TyC2B+nRkxz8CE+bp2Ybp1qhRJF6SwjXu10UEgzYsGmzp379TeE6B3Lfa\n";
		$s .= "F2JnE0eMhITrETV2BCkS5IcNFaZV/gtmrXlE3hcqRuD+qmT44a+lT7soulvskRxnLSgPSx6zisA/\n";
		$s .= "6eBbEWBJ994SMbhmRH2eJZbDBeKB1dtl/432lQquETiYVtNdiNqCDDJoBGVi9QfZCw3+VANqSnym\n";
		$s .= "E21LgHcaDOwBGKJcE0b2n1w/wZCZCsJxeKCPrpEQw19ypdhgjpKJeJsFQCRmYHECIkcCDDkEkSSD\n";
		$s .= "TKqVQlJAALHDirfZIJd7xR3xJG4jHMnjeMihJoIOSU6J2BIrOtdaClEuqadvNSw4Gpp3IiFjc5fZ\n";
		$s .= "0CBriJ72gqAbFnbgT0rQsOafZorVZaNpjaBlpKHdKVYQL1yKm4ZKMHcaCiWwisJZ/q2NEJedKf50\n";
		$s .= "X6mN1gCnZtDVuEQSO5TgWg2gdmijhUWMyRxuun216Y48LlEEaiS096Kxb95IlnqnXVDijzem2m0S\n";
		$s .= "hH4VBGAWAviVEE+OINiz9eGI2hG0LkFCCZ8qCaBlmlULlolpoRgvUPldRq6QxeXHWbo8IsHtY73y\n";
		$s .= "dtqOKepArbWSJozZCzugu5sRL7hGmbMTt1hUUUEw5hoNxR5YcGQpwGDDzDGAV8HNOFdAwhBHHDFE\n";
		$s .= "CTnfjJZ6FxDt2wUE1vsyao5JEMHTETgdwQcnVM3CBFBLLQFmyOXgYkbGLu2c01JHMMEGVldNAdRs\n";
		$s .= "5xrdtQeq2pwEZVMAwgksVK02/ttkN0dCbEGCfSASQdAg9mVSU4C23mljHXXbR6egQ4u71SuWEsnS\n";
		$s .= "oEIKJIzgeQkpZKDB3XrnjXfVG2ig+uoa6OD66zv8EATlDJZrI4BCgSXD6afj0MPvwAcPfFgD32i5\n";
		$s .= "vvHunvYJQ2zZIPLQt1xcuDa2YLrezWdbPPV1wj09w1suobzpLGQf/Y3hBx4qw7UKZn3pzG/fvvyV\n";
		$s .= "e38n+Ckqz/v1eLfgu0/po99PbMc9ubyPccuDHw5yF0Dj2W9+NxpfAnmXNhbE5XxbImADDXi9DsKP\n";
		$s .= "cRbcXvgG+EAR5u+DFPRg1WYAQfZpEHkcZMEMeNCDIQzhdzhoAQrzlr0R1up4/rdLEVN287wh8M90\n";
		$s .= "OAhi8TC2vmOZEH1fGcIEZ+DD4gFRgPirzxFP0AIoDuyFXiyg+xIoAwF2L2P3U2IVQ4QEFhyRhWZU\n";
		$s .= "H7Y2+BUc8ACA85PiB5MYRs9cUXtf0SEL7sg+PVIwfoC8HRirqEOrtYCGNuwBDxp5xBawz4kkRCME\n";
		$s .= "t9RIBCJwi/G7pAMFNz06LkGQy8sb/yjIxzWm64+XpCQK96c3PsKwfYuc3wEnuMoWNA+DPyyh9pw3\n";
		$s .= "hByC0moz6EELBZhLET7FZ5LkAQ17MMRE9jFSKzGAArbJzW5685vgDKc4x6mAA3CICQU4iTrXyc6R\n";
		$s .= "BGAAKtlIAxzQgHnWk572GcwnPvd5z37q05/8/KdANbKQghr0oAgNCAAAOw==\n";
		return $s;
	}

	function postToHost($host, $path, $referer, $data_to_send)
	{
		$res = '';
		//A TS 18.02.2013
		//Evtl. Fehlermeldungen unterdrücken
		$fp = @fsockopen($host, 80, $errno, $errstr, 20);
		//Nur ausführen, wenn die Vebindung steht
		if($fp)
		{
			fputs($fp, "POST $path HTTP/1.1\r\n");
			fputs($fp, "Host: $host\r\n");
			fputs($fp, "Referer: $referer\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: ". strlen($data_to_send) ."\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $data_to_send);
			while(!feof($fp))
			{
				$res .= fgets($fp, 128);
			}
			fclose($fp);
		}
		return $res;
	}

	function logProPlusVersion()
	{
		if($this->sVersionStr == "strato_medien_ag")
		{
				//A TS nicht mehr loggen
			//$this->logSTRATO();
		}
		else
		{
			//A TS nicht mehr loggen
			//$this->logGSSoftware();
		}
	}

	/*
		remember to change the version number for new SB releases !!!
	*/
	function logSTRATO()
	{
			//A TS nicht mehr loggen
		//$res = $this->db_transfer('http://pm-strato-devel01.de/gs_post/gs5ppuebergabe.php', 5, 'Strato ProPlus', $_SERVER['SERVER_NAME'], $_SERVER['REMOTE_ADDR']);
	}

	function logGSSoftware()
	{
		//A TS nicht mehr loggen
		//$data = "www=".$_SERVER["HTTP_HOST"]."&vstr=".$this->sVersionStr."&vdsb=".$this->sVersion;
		//$res = $this->postToHost("www.gs-shopbuilder.de", "/gssbpropluslog/gssbpropluslog.php", $_SERVER["HTTP_HOST"], $data);
	}

	/*
		function from strato ag / 2005-04-14 (Tobias Zadow)
	*/
	function db_transfer($target, $version = 5, $art = 'Strato ProPlus', $domain, $ip)
	{
		/*
		$parts = parse_url($target);
		// den Query String zusammensetzen //
		$query		= '?version='.urlencode($version).'&art='.urlencode($art).'&domain='.md5($domain).'&ip='.urlencode($ip);
		// Daten an Übergabescript senden //
		$fp = fsockopen($parts['host'], '80', $errno , $errstr , 20);
		$query	= 'GET '.$parts['path'].$query." HTTP/1.0\r\n";
 		$query .= 'Host: '.$parts['host'];
 		$query .= "\r\n\r\n";
 		fwrite($fp, $query);
 		$header = '';
		while (!feof($fp) && ($line = fgets($fp, 4096)) && ($line != "\r\n"))
		{
			$header .= $line;
		}
		if(!preg_match('!HTTP/1\.[01]\ 200!', $header)) echo $header;
 		fclose($fp);
		// Resultat zurückgeben //
		*/
		return true;
	}
	
	//A TS 27.11.2012 Quoted printable in HTML
	function qp2HTML($str)
	{
		$erg = "";
		for($i = 0; $i < strlen($str); $i++)
		{
			$char = substr($str,$i,1);
			$code = ord($char);
			if($code != 61)
			{
				$erg .= $char;
			}
			else
			{
				$hex = substr($str,$i + 1,2);
				$dec = hexdec($hex);
				$erg .= htmlentities(chr($dec),ENT_QUOTES);
				$i += 2;
			}
		}
		//echo "Umgewandelt: " . $erg . "<br />";
		return $erg;
	}
	
	function place_additional_fields()
	{
		//echo "ap-Array in place_additional_fields:<pre>";
		//print_r($this->ap);
		//die("</pre>");
		$addfiels = '';
		for($f = 1; $f <= 5; $f++)
		{
			$fname = '_GSSBTXTFNFIELDADD_' . $f;
			$fvalue = 'additionalval' . $f;
			if(trim($this->ap[$fname]) != '' && trim($this->ap[$fvalue]) != '')
			{
				$addfields .= $this->ap[$fname] . ": " . $this->ap[$fvalue] . "\n";
			}
		}
		if($addfields != '')
		{
			$addfields .= "-------------------------------------------------------\n";
		}
		return $addfields;
	}
	
}

?>
