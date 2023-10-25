<?php
/*
    Mail Script - gsorder6.php
    Author: Sabine Salzsiedler / GS Software Solutions GmbH
*/

class gsorder6
{

  var $sVersion = "1.1";
  var $sOrderURL = "";
  var $sVersionStr = "";
  var $slbreak = "\r\n";
  var $ap = array();
  var $aAdditionalDoNotSend = array();
  var $adays = array('Monday' => 'Montag', 'Tuesday' => 'Dienstag', 'Wednesday' => 'Mittwoch', 'Thursday' => 'Donnerstag', 'Friday' => 'Freitag', 'Saturday' => 'Samstag', 'Sunday' => 'Sonntag');
  var $amonths = array('January' => 'Januar', 'February' => 'Februar', 'March' => 'März', 'April' => 'April', 'May' => 'Mai', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'August', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Dezember');
    
  
  function mail_attach($to, $from, $subject, $message, $attach, $file) 
  {
    if($file!="")
    {
      $semi_rand = md5(time());
      $mime_boundary = "==Multipart_Boundary_x".$semi_rand."x";
      
      $header = "From: ".$from;
      $header .= "\nMIME-Version: 1.0\n"."Content-Type: multipart/mixed;\n"." boundary=\"".$mime_boundary."\"";

      $data = $attach;
      
      $content .= "This is a multi-part message in MIME format.\n\n"."--".$mime_boundary."\n"."Content-Type: text/plain charset=".$this->ap['Charset']."\n"."Content-Transfer-Encoding: 7bit\n\n".$message."\n\n";
      
      //$data = chunk_split(base64_encode($attach));
      $data = $attach;
           
      $content .= "--".$mime_boundary."\n"."Content-Type: text/plain\n name=\"".$file."\"\nContent-Disposition: attachment;\n filename=\"".$file."\"\nContent-Transfer-Encoding: quoted-printable\n\n".$data."\n\n--".$mime_boundary."--\n";
                
      $sendMail = mail($to, "Local|" . $subject, $content, $header);
    }
    else
    {
      $header = "From: ".$from."\n";
      $header .= "Content-type: text/plain; charset=".$this->ap['Charset']."\n";
      $header .= "Content-Transfer-Encoding: quoted-printable"."\n";
      $header .= "X-Mailer: GS-ShopMail V".$this->sVersion." [GS ShopBuilder] - www.gs-shopbuilder.com"."\n";
      
      $sendMail = mail($to, "Local|" . $subject, $message."\n", $header);
    }
    return $sendMail;
  }

  function cleanUpTextData($val) 
  {
    if(array_key_exists($val, $this->ap)) 
    {
      $str = str_replace("<br />", "\n", $this->ap[$val]);
      $str = str_replace("<br>", "", $this->ap[$val]);
      $this->ap[$val] = $str;
    }
  }
  
  function mailIni($aREQUEST)
  {
    $this->ap = $aREQUEST;
    $this->sVersionStr = $this->ap['VersionString'];
    $this->sOrderURL = $this->ap['MailScriptURL'];
    $this->cleanUpTextData('answer_subject');
    $this->cleanUpTextData('answer_greeting_text');
    $this->cleanUpTextData('answer_provider');
    $this->cleanUpTextData('answer_text_end');
    $this->cleanUpTextData('answer_customer_infos');
  }

  function customerOrderEmail()
  {
    //  Customer Order Email
    $subject = $this->ap['answer_subject'];
    $sender = $this->ap['recipient'];
    $email = $this->ap['email'];
    $text = $this->customerEmailText();
    $this->mail_attach($email, $sender, $subject, $text, "", "");
  }

  function customerEmailText()  
  {
    $text .= $this->ap['answer_subject'].$this->slbreak.$this->slbreak.$this->ap['answer_greeting_text'].$this->slbreak.$this->slbreak;
    $text .= $this->ap['answer_provider'].$this->slbreak.$this->slbreak;
    $text .= $this->getOrderData();
    $text .= $this->getCustData();
    $text .= $this->ap['answer_text_end'].$this->slbreak.$this->slbreak.$this->slbreak.$this->slbreak;
    $text .= $this->ap['answer_customer_infos'];
    
    return $text;
  }
  
  function getOrderData()
  {
    $text .= $this->ap['pid'].": ".$this->ap['_LANGTAGFNFIELDPID_'].$this->slbreak.$this->slbreak;
    for($i = 0; $i < $this->ap['qtyofpos']; $i++)
    {
      $text .= ($i+1).". ".$this->ap['position'].$this->slbreak
            ."-----------------------------------------------------------------".$this->slbreak;
      $text .= $this->ap['_LANGTAGFNFIELDQUANTITY_'.($i+1)]." x ".$this->ap['_LANGTAGFNFIELDTEXTITEM_'.($i+1)];
      $text .= $this->slbreak.$this->ap['_GSSBTXTFNFIELDTEXTUNITPRICE_'].": ".$this->ap['_LANGTAGFNFIELDTEXTUNITPRICE_'.($i+1)];
      $text .= $this->slbreak."-----------------------------------------------------------------".$this->slbreak;
      $text .= $this->ap['_GSSBTXTFNFIELDTOTALPRICE_'].": ".$this->ap['_LANGTAGFNFIELDTOTALPRICE_'.($i+1)];
      if(!isset($this->ap['_LANGTAGFNFIELDSHORTVAT_'.($i+1)]))
      {
      	$text .= $this->slbreak;
      }
      else
      {
      	$text .= ", ".$this->ap['_GSSBTXTFNFIELDSHORTVAT_']." ".$this->ap['_LANGTAGFNFIELDSHORTVAT_'.($i+1)].$this->slbreak.$this->slbreak;
      }
    }
    $text .= "=================================================================".$this->slbreak;
    $text .= $this->ap['_GSSBTXTFNFIELDLOCTOTAL_'].": ".$this->ap['_LANGTAGFNFIELDLOCTOTAL_'].$this->slbreak.$this->slbreak;
    if(array_key_exists('DiscountPrct', $this->ap))
    {
      $text .= $this->ap['_GSSBTXTFNFIELDLOCDISCOUNT_']."(".$this->ap['goodsvalue'].") ";
      $text .= $this->ap['DiscountPrct']."% : ".$this->ap['currency']." - ".$this->ap['DiscountValue'].$this->slbreak;
    }
  
    if(array_key_exists('CashDiscountPrct', $this->ap))
    {
      $text .= $this->ap['_GSSBTXTFNFIELDLOCDISCOUNT_']."(".$this->ap['_LANGTAGFNFIELDPAYMENT_'].") ";
      $text .= $this->ap['CashDiscountPrct']."% : ".$this->ap['currency']." - ".$this->ap['CashDiscountValue'].$this->slbreak;
    }
    $text .= $this->ap['_GSSBTXTFNFIELDPAYMENT_']." (".$this->ap['_LANGTAGFNFIELDPAYMENT_'].")".$this->slbreak;
  
    if(array_key_exists('_LANGTAGFNFIELDPAYMENTCHARGE_', $this->ap))
    {
      $text .= $this->ap['_GSSBTXTFNFIELDPAYMENTCHARGE_']." (".$this->ap['_LANGTAGFNFIELDPAYMENT_']."): ";
      $text .= $this->ap['_LANGTAGFNFIELDPAYMENTCHARGE_'].$this->slbreak;
    }
  
    $text .= $this->ap['_GSSBTXTFNFIELDPOSTAGE_']." (".$this->ap['_LANGTAGFNFIELDDELIVERY_']."): ";
    $text .= $this->ap['_LANGTAGFNFIELDPOSTAGE_'].$this->slbreak;
    $text .= "=================================================================".$this->slbreak;
    $text .= $this->ap['_GSSBTXTFNFIELDTOTALAMOUNT_'].": ".$this->ap['_LANGTAGFNFIELDTOTALAMOUNT_'].$this->slbreak.$this->slbreak.$this->slbreak;
  
    if(array_key_exists('LongVatPrct1', $this->ap))
    {
      $text .= $this->ap['encluded']." ".$this->ap['_GSSBTXTFNFIELDSHORTVAT_']." (";
      $text .= $this->ap['LongVatPrct1']."%): ".$this->ap['currency']." ".$this->ap['LongVatValue1'].$this->slbreak.$this->slbreak.$this->slbreak;
    }
  
    if(array_key_exists('LongVatPrct2', $this->ap))
    {
      $text .= $this->ap['encluded']." ".$this->ap['_GSSBTXTFNFIELDSHORTVAT_']." (";
      $text .= $this->ap['LongVatPrct2']."%): ".$this->ap['currency']." ".$this->ap['LongVatValue2'].$this->slbreak.$this->slbreak.$this->slbreak;
    }
  
    if(array_key_exists('LongVatPrct3', $this->ap))
    {
      $text .= $this->ap['encluded']." ".$this->ap['_GSSBTXTFNFIELDSHORTVAT_']." (";
      $text .= $this->ap['LongVatPrct3']."%): ".$this->ap['currency']." ".$this->ap['LongVatValue3'].$this->slbreak.$this->slbreak.$this->slbreak;
    }  
    return $text;
  }
  
  function getCustData()
  {
    if(array_key_exists('_LANGTAGFNFIELDSHIPPINGSTREET_', $this->ap))
    {
      $text .= $this->ap['billingAddress'].$this->slbreak;
    }
    else
    {
      $text .= $this->ap['billingAddress']." & ".$this->ap['shippingAddress'].$this->slbreak;
    }
  
    $text .= "-------------------------------------------------------".$this->slbreak;
    $text .= $this->ap['_GSSBTXTFNFIELDCOMPANY_'].": ".$this->ap['_LANGTAGFNFIELDCOMPANY_'].$this->slbreak;
    $text .= $this->ap['_GSSBTXTFNFIELDCUSTOMERNR_'].": ".$this->ap['_LANGTAGFNFIELDCUSTOMERNR_'].$this->slbreak;
    $text .= $this->ap['_GSSBTXTFNFIELDFIRMVATID_'].": ".$this->ap['_LANGTAGFNFIELDFIRMVATID_'].$this->slbreak;
    $text .= $this->ap['_GSSBTXTFNFIELDEMAIL_'].": ".$this->ap['email'].$this->slbreak;
    $text .= $this->ap['_GSSBTXTFNFIELDPHONE_'].": ".$this->ap['_LANGTAGFNFIELDPHONE_'].$this->slbreak;
    $text .= $this->ap['_GSSBTXTFNFIELDFAX_'].": ".$this->ap['_LANGTAGFNFIELDFAX_'].$this->slbreak.$this->slbreak;
    $text .= $this->ap['_LANGTAGFNFIELDFORMTOADDRESS_']." ".$this->ap['_LANGTAGFNFIELDFIRSTNAME_']." ".$this->ap['_LANGTAGFNFIELDLASTNAME_'].$this->slbreak;
    $text .= $this->ap['_LANGTAGFNFIELDADDRESS_'].$this->slbreak;
    $text .= $this->ap['_LANGTAGFNFIELDADDRESS2_'].$this->slbreak;
    $text .= $this->ap['_LANGTAGFNFIELDZIPCODE_']." ".$this->ap['_LANGTAGFNFIELDCITY_'].$this->slbreak;
    $text .= $this->ap['_LANGTAGFNFIELDSTATE_'].$this->slbreak;
    $text .= "-------------------------------------------------------".$this->slbreak.$this->slbreak;
  
    if(array_key_exists('_LANGTAGFNFIELDSHIPPINGSTREET_', $this->ap))
    {
      $text .= $this->ap['shippingAddress'].$this->slbreak;
      $text .= "-------------------------------------------------------".$this->slbreak;
      $text .= $this->ap['_GSSBTXTFNFIELDCOMPANY_'].": ".$this->ap['_LANGTAGFNFIELDSHIPPINGCOMPANY_'].$this->slbreak.$this->slbreak;
      $text .= $this->ap['_LANGTAGFNFIELDSHIPPINGFORMTOADDRESS_']." ".$this->ap['_LANGTAGFNFIELDSHIPPINGFIRSTNAME_']." ".$this->ap['_LANGTAGFNFIELDSHIPPINGLASTNAME_'].$this->slbreak;
      $text .= $this->ap['_LANGTAGFNFIELDSHIPPINGSTREET_'].$this->slbreak;
      $text .= $this->ap['_LANGTAGFNFIELDSHIPPINGADDRESS2_'].$this->slbreak;
      $text .= $this->ap['_LANGTAGFNFIELDSHIPPINGZIPCODE_']." ".$this->ap['_LANGTAGFNFIELDSHIPPINGCITY_'].$this->slbreak;
$text .= $this->ap['_LANGTAGFNFIELDSHIPPINGSTATE_'].$this->slbreak;
    }
    
    $text .= $this->ap['_GSSBTXTFNFIELDMESSAGE_'].": ".$this->slbreak.$this->ap['_LANGTAGFNFIELDMESSAGE_'].$this->slbreak.$this->slbreak.$this->slbreak.$this->slbreak.$this->slbreak;
    return $text;
  }
  
  function shopOwnerOrderEmail()
  {
    //  Customer Order Email
    $subject = $this->ap['subject'];
    $sender = $this->ap['email'];
    $email = $this->ap['recipient'];
    $text = $this->shopOwnerEmailText();
    if($this->ap['gsAttachment']==1)
    {
      $attach = $this->createAttachment();
      $this->mail_attach($email, $sender, $subject, $text, $attach, "gsshopbuilder.txt");  
    }
    else
    {
      $this->mail_attach($email, $sender, $subject, $text, "", "");  
    }
  }
  
  function shopOwnerEmailText()
  {
    $text .= $this->slbreak;
    $text .= $this->getOrderData();
    $text .= $this->getCustData();

    return $text;
  }

  function createAttachment()
  {
    $day = date("l");
    $month = date("F");
    $sep = "---------------------------------------------------------------------------------------------------------".$this->slbreak;
    $aDoNot = array('sid', 'recipient', 'answer_customer_infos', 'password', 'user', 
                    'dear', 'logindata_email_text1', 'logindata_email_text2', 
                    'answer_greeting_text', 'answer_customer_text', 'answer_provider',
                    'answer_text', 'answer_text_end', 'subject', 'goodsvalue',
                    'qtyofpos', 'shopname', 'redirect', 'answer_subject', 'gsAttachment',
                    'SumDiscount2', 'CashDiscountValue', 'CashDiscountPrct',
                    'emailsender', 'rememberme', 'button', 'button2', 'dsbID',
                    'DiscountPrct', 'DiscountValue', 'SumDiscount1', 'Charset',
                    '_LANGTAGFNFIELDEMAILFORMAT_', 'MailScriptURL', 'VersionString',
                    'position', 'currency', 'encluded', 'billingAddress', 'shippingAddress',
                    'userdata', 'pid', '_LANGTAGFNCREDITCARD_', '_LANGTAGFNMONTHEXPIRATIONDATE_',
                    '_LANGTAGFNYEAREXPIRATIONDATE_', '_LANGTAGFNCREDITCARDNUMBER_',
                    '_LANGTAGFNISSUENUMBER_', '_LANGTAGFNSECURITYCODE_', '_LANGTAGFNSECURITYCODE_',
                    '_LANGTAGFNCREDITCARDHOLDER_','_LANGTAGFNFINANCIALINSTITUTION_','_LANGTAGFNBANKCODENUMBER_',
                    '_LANGTAGFNACCOUNTNUMBER_','_LANGTAGFNACCOUNTHOLDER_'
                   );
		if(count($this->aAdditionalDoNotSend) > 0) 
    {
      $aDoNot = array_merge($aDoNot, $this->aAdditionalDoNotSend);
    }
    $text .= "email: ".$this->ap['email'].$this->slbreak;
    $text .= "weekday: ".date('w').$this->slbreak;
    $text .= "date: ".date('Y-m-d').$this->slbreak;
    $text .= "time: ".date('H:i:s').$this->slbreak;
    $text .= "germandate: ".$this->getDay($day).", ".date('d').".".$this->getMonth($month)." ".date('Y')." um ".date('H:i:s').$this->slbreak;
    $text .= "englishdate: ".$day.", ".$month." ".date('d').", ".date('Y')." at ".date('H:i:s').$this->slbreak;
    $text .= $sep;
    $text .= $this->slbreak;
    foreach($this->ap as $key => $value) 
    {
      if( substr($key, 0, strlen('itemId')) != 'itemId' &&
          substr($key, 0, strlen('ShortVatPrct')) != 'ShortVatPrct' &&
          substr($key, 0, strlen('ShortVatValue')) != 'ShortVatValue'
        ) 
      {
        if(!in_array($key, $aDoNot)) 
        {
          $text .= str_pad($key, 30, " ", STR_PAD_RIGHT).": ".$value.$this->slbreak;
        }
      }
    }
    $text .= $sep;
    return $text;
  }
  
  function redirect() 
  {
    header("Location: ".trim($this->ap['redirect']));
    die();
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
  
  function logProPlusVersion() 
  {
		if($this->sVersionStr == "strato_medien_ag") 
    { $this->logSTRATO(); } 
    else 
    { $this->logGSSoftware(); }			
	}
	
	function logSTRATO() 
  {
		$res = $this->db_transfer('http://pm-strato-devel01.de/gs_post/gs5ppuebergabe.php', 6, 'Strato ProPlus', $_SERVER['SERVER_NAME'], $_SERVER['REMOTE_ADDR']);
	}
	
	function logGSSoftware() 
  {
		$data = "www=".$_SERVER["HTTP_HOST"]."&vstr=".$this->sVersionStr."&vdsb=".$this->sVersion;
		$res = $this->postToHost("www.gs-shopbuilder.de", "/gssbpropluslog/gssbpropluslog.php", $_SERVER["HTTP_HOST"], $data);
	}
	
	function db_transfer($target, $version = 6, $art = 'Strato ProPlus', $domain, $ip) 
  {	
		$parts = parse_url($target);
		// den Query String zusammensetzen //	
		$query    = '?version='.urlencode($version).'&art='.urlencode($art).'&domain='.md5($domain).'&ip='.urlencode($ip);
		// Daten an Übergabescript senden //
		$fp = fsockopen($parts['host'], '80', $errno , $errstr , 20);
		$query  = 'GET '.$parts['path'].$query." HTTP/1.0\r\n";
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
		return true;
	}
	
	function postToHost($host, $path, $referer, $data_to_send) 
  	{
		$fp = fsockopen($host, 80, $errno, $errstr, 20);
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
		return $res;
	}
	
	function writeLogFile($str)
	{

  		if(file_exists("email_log/readme.txt"))
  		{
    		$perms = substr(decoct(fileperms("email_log")),sizeof(decoct(fileperms("email_log")))-4,3);
    		if($perms=="777")
    		{
      			$filename = "email_log/".date('Ymd')."_gsorder6.txt";
      			$handle = fopen($filename, "a");
      			$content = date('Y-m-d H:i:s')." -> ".$str."\n";
      			fwrite($handle, $content);
      			fclose($handle);
    		}
  		}
	}
  
}
$gsEmail = new gsorder6();
$gsEmail->writeLogFile('--------'.date('Y-m-d').'-------------------------------------------');
$gsEmail->writeLogFile('redirect: '.$_REQUEST['redirect']);
$gsEmail->writeLogFile('subject: '.$_REQUEST['subject']);
$gsEmail->writeLogFile('recipient: '.$_REQUEST['recipient']);
$gsEmail->writeLogFile('shopname: '.$_REQUEST['shopname']);
$gsEmail->writeLogFile('MailScriptURL: '.$_REQUEST['MailScriptURL']);
$gsEmail->writeLogFile('-------------------------------------------------------------');
$gsEmail->writeLogFile('-------------------------------------------------------------');
$gsEmail->mailIni($_REQUEST);
$gsEmail->customerOrderEmail();
$gsEmail->shopOwnerOrderEmail();
$gsEmail->redirect();
?>
