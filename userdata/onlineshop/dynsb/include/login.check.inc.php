<?php

/*
 file : login_check.inc.php
 Desc.: checks user is logged in, if not - redirect to error/login page
*/

//ob_start(); // buffer output
//$currentDir = substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"/dynsb/"));
//require_once($_SERVER["DOCUMENT_ROOT"].$currentDir."/dynsb.path.inc.php");

/*
    REMEMBER: include before(!) session_start();
    require( INC_BASE."class.customer.php");
*/

if(file_exists("class/class.security.php"))
{
  require_once("class/class.security.php");
  require_once("class/class.session.inc.php"); // fallback "php session" class
}
else
{
  if(file_exists("../class/class.security.php"))
  {
    require_once("../class/class.security.php");
    require_once("../class/class.session.inc.php"); // fallback "php session" class
  }
  else
  {
    if(file_exists("../../class/class.security.php"))
    {
      require_once("../../class/class.security.php");
      require_once("../../class/class.session.inc.php"); // fallback "php session" class
    }
    else
    {
      require_once("../../../../class/class.security.php");
      require_once("../../../../class/class.session.inc.php"); // fallback "php session" class
    }
  }
}

$IsLoggedIn = false;
$dtlsSecurity = new Security;
$dtlsSecurity->ExtraFieldNames('sessLanguageIdNo');
$dtlsSecurity->StoreSession_TableName(DBToken.'session');
$dtlsSecurity->Log_TableName(DBToken.'log');
$dtlsSecurity->FieldNames('sessId', 'sessUserIdNo', 'sessUserLogin');
$IsLoggedIn = $dtlsSecurity->IsLoggedIn();
$details = $dtlsSecurity->GetData();

//die();
// if not logged in, then redirect to login page...
if (!$IsLoggedIn) {
    if(file_exists("session_invalid.php"))
    {
      header("Location: session_invalid.php?lang=".$_REQUEST['lang']);
    }
    else
    {
      if(file_exists("../session_invalid.php"))
      {
        header("Location: session_invalid.php?lang=".$_REQUEST['lang']);
      } 
    }
    die();
}

// start session
$Session = new Session();

//ob_end_flush();

// register session variables
// here : user details
if (sizeof($details) > 2) {
    $SESS_userIdNo        = addslashes(strip_tags($details[0]));
    $SESS_userLogin       = addslashes(strip_tags($details[1]));
    $SESS_languageIdNo    = addslashes(strip_tags($details[2]));
    $SESS_Currency        = 'EUR';
    $_SESSION["SESS_userIdNo"]  = $SESS_userIdNo;     // userIdNo
    $_SESSION["SESS_userLogin"] = $SESS_userLogin;       // userId, login name
    $_SESSION["SESS_languageIdNo"] = $SESS_languageIdNo; // languageIdNo, default lang. for the user
    $_SESSION["SESS_Currency"]  = $SESS_Currency; // languageIdNo, default lang. for the user
} else {
    //TODO: die("error, getting user details");
}
?>
