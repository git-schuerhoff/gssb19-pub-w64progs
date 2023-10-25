<?php
/*

    GS-MailService v1.0 - class.mailservice.php
    Author: Sigrid Reimann / GS Software Solutions GmbH

    (c) 2005 GS Software AG

    this code is NOT open-source or freeware
    you are not allowed to use, copy or redistribute it in any form

*/

// -------------------------------------------------------------------------
   class mailservice {
// -------------------------------------------------------------------------
  var $sender;
  var $helo;
  var $server;
  var $port;
  var $recipient;
  var $subject;
  var $message;
  var $status;
  var $error;
  var $errstr;
  var $xmailer;
  var $mailHeader;
  var $crtLinefeed;

  var $counter;
  var $timeout;
  var $limit;

// -------------------------------------------------------------------------
  function __construct($sender, $recipient, $subject, $message) {
// -------------------------------------------------------------------------
     $this->sender = $sender;
     $this->recipient = $recipient;
     $this->subject = $subject;
     $this->message = $message;// $this->quoted_printable_encode($message);
     $this->xmailer = "X-Mailer: GS ShopBuilder Mailservice";
     $this->crtLinefeed = "\n";

     $this->counter	= 0;
     $this->pause	= 0;
     $this->limit 	= 0;

     $this->counterSend	= 0;
     $this->counterFailed	= 0;
   }

// -------------------------------------------------------------------------
   function sendMail() {
// -------------------------------------------------------------------------
    //pause n seconds after x mails send
    if (($this->counter != 0) && ($this->counter % $this->limit == 0)) {
      sleep($this->pause);
    }

    $send = @mail($this->recipient, $this->subject, $this->message, $this->mailHeader);

    $this->counter++;

    if ($send)
      $this->counterSend++;
    else
      $this->counterFailed++;

    return $send;
   }

// -------------------------------------------------------------------------
   function createHeader() {
// -------------------------------------------------------------------------
     $mHeader  = "MIME-Version: 1.0".$this->crtLinefeed;
     $mHeader .= "From: ".$this->sender.$this->crtLinefeed;
     $mHeader .= "Content-type: text/plain; charset=UTF-8".$this->crtLinefeed;
     $mHeader .= "Content-Transfer-Encoding: quoted-printable".$this->crtLinefeed;
     $mHeader .= "X-Mailer: GS ShopBuilder Mailservive".$this->crtLinefeed;

     $this->mailHeader = $mHeader;
     return $mHeader;
   }

// -------------------------------------------------------------------------
   function createHtmlHeader() {
// -------------------------------------------------------------------------
     $mHeader  = "From: ".$this->sender;
     $mHeader .= "\nMIME-Version: 1.0";
     $mHeader .= "\nContent-Type: text/html; charset=\"UTF-8\"\n";
     $mHeader .= "Content-Transfer-Encoding: quoted-printable".$this->crtLinefeed;
     $mHeader .= "X-Mailer: GS ShopBuilder Mailservice\n";

     $this->mailHeader = $mHeader;
     return $mHeader;
   }

  /**
   * Set Recipient
   */
   function setRecipient($recipient) {
       $this->recipient = $recipient;
   }

  function setPause($pause) {
       $this->pause = $pause;
  }

  function setLimit($limit) {
       $this->limit = $limit;
  }

  function setMessage($message) {
       $this->message = $message;//$this->quoted_printable_encode($message);
  }

  function quoted_printable_encode($sString) {
   /*instead of replace_callback i used e modifier for regex rule, which works as eval php function*/
   /*$sString = preg_replace( '/[^\x21-\x3C\x3E-\x7E\x09\x20]/e', 'sprintf( "=%02X", ord ( "$0" ) ) ;',$sString );*/
   $sString = preg_replace_callback( '/[^\x21-\x3C\x3E-\x7E\x09\x20]/', function ($m) { return 'sprintf( "=%02X", ord ( "$0" ) ) ;'; },$sString );
   
   /*now added to this rule one or more chars which lets last line to be matched and included in results*/
   preg_match_all( '/.{1,73}([^=]{0,3})?/', $sString, $aMatch );
   return implode("=\r\n", $aMatch[0] );
	}

}


