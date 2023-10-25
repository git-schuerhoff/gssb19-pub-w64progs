<?php
/******************************************************************************/
/* File: mod.data_import.import.php                                           */
/******************************************************************************/

require("../../include/login.check.inc.php");
require_once("../../include/functions.inc.php");
require("../../../conf/db.const.inc.php");

/***************** Sprachdatei ************************************************/
if (!isset($_REQUEST['lang']) || strlen(trim($_REQUEST['lang'])) == 0)
{
    $lang = "deu";
}
else
{
	$lang = $_REQUEST['lang'];
	if(!file_exists("../../lang/lang_".$lang.".php"))
  {
    $lang = "deu";
  }
}

include("../../lang/lang_".$lang.".php");
/******************************************************************************/

/***************** Datenbankverbindung*****************************************/
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase)
  or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");
if(isset($_REQUEST['start'])) {
    $start = intval($_REQUEST['start']);
}
if(isset($_REQUEST['pk'])) {
    $cnewsIdNo = intval($_REQUEST['pk']);
}
if(isset($_REQUEST['act'])) {
    $act = trim($_REQUEST['act']);
}

$chgApplicId = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1)); // script name

if (!isset($_SESSION['SESS_userIdNo']) || strlen(trim($_SESSION['SESS_userIdNo'])) == 0) {
  die ("<br />error: missing session parameter!<br />");
} else {
	$SESS_userIdNo = $_SESSION['SESS_userIdNo'];
}
if (!isset($_SESSION['SESS_userLogin']) || strlen(trim($_SESSION['SESS_userLogin'])) == 0) {
  die ("<br />error: missing session parameter!<br />");
} else {
	$SESS_userId = $_SESSION['SESS_userId'];
}
if (!isset($_SESSION['SESS_languageIdNo']) || strlen(trim($_SESSION['SESS_languageIdNo'])) == 0) {
  die ("<br />error: missing session parameter!<br />");
} else {
	$SESS_languageIdNo = $_SESSION['SESS_languageIdNo'];
}

foreach($_REQUEST as $key => $value)
{
    $$key = trim($value);
}

$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";

//DB-Tabellen
if ($selectOldVersion == "5")
{
  $selectOldVersion = "";
}
$order_gssb6 = "dsb".$selectOldVersion."_order";
$order_gssb7 = DBToken."order";
$orderpos_gssb6 = "dsb".$selectOldVersion."_orderpos";
$orderpos_gssb7 = DBToken."orderpos";

$customer_gssb6 = "dsb".$selectOldVersion."_customer";
$customer_gssb7 = DBToken."customer";

$DBvariante = 1; // 0=gleiche DB; 1=verschiedene DB
$success = 0;

//** Connect to the old DB, where the data should be imported from*********************
$link = @mysqli_connect($impHost, $impUser, $impPassword, $impDBName)
		or die("<br />aborted: can´t connect to '$impHost' <br />ERR#001");
$link->query("SET NAMES 'utf8'");
//A TS 14.11.2014: mysql_list_tables ist deprecated und in MySQLi nicht enthalten,
//benutze "SHOW TABLES FROM..."
//$rs = mysq_l_list_tables($impDBName);
$sql = "SHOW TABLES FROM " . $impDBName;
$rs = mysqli_query($link,$sql);
//E TS

if (!$rs)
{
  print 'MySQL Fehler: ' . mysqli_error($link);
  exit;
}

$tablist = array();
while ($row = mysqli_fetch_row($rs))
{
  array_push($tablist,$row[0]);
}

$error_msg = "";
if(!in_array($order_gssb6, $tablist))
{
  $error_msg .= "Table '".$order_gssb6."' don't exists!<br />";
}

if(!in_array($orderpos_gssb6, $tablist))
{
  $error_msg .= "Table '".$orderpos_gssb6."' don't exists!<br />";
}

if(!in_array($customer_gssb6, $tablist))
{
  $error_msg .= "Table '".$customer_gssb6."' don't exists!<br />";
}


if($impHost==$dbServer && $impDBName==$dbDatabase
   && $impUser==$dbUser && $impPassword==$dbPass)
{
    $DBvariante = 0;

}


//Kundendaten
  if(isset($impCustomerdata)&& $impCustomerdata=='1')
  {
    if($DBvariante==1)
    {
      $link = @mysqli_connect($impHost, $impUser, $impPassword, $impDBName) or die("<br />aborted: can´t connect to '$impHost' <br />");
    }
    else if($DBvariante==0)
    {
      $link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
    }
	$link->query("SET NAMES 'utf8'");
    $SQLcus = "SELECT * FROM ".$customer_gssb6.";";
    $qrycus = @mysqli_query($link,$SQLcus) or $success = 1;
    while($objcus = @mysqli_fetch_object($qrycus))
    {
      @mysqli_close($link);
      $link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
	  $link->query("SET NAMES 'utf8'");
      $SQLcusNew = "INSERT INTO ".$customer_gssb7." (cusId
					  , cusFirmname
					  , cusFirmVATId
					  , cusTitle
					  , cusFirstName
					  , cusLastName
					  , cusStreet
					  , cusStreet2
					  , cusZipCode
					  , cusCity
					  , cusCountry
					  , cusPhone
					  , cusFax
					  , cusEMail
					  , cusEMailFormat
					  , cusPassword
					  , cusDeliverFirmname
					  , cusDeliverTitle
					  , cusDeliverFirstName
					  , cusDeliverLastName
					  , cusDeliverStreet
					  , cusDeliverStreet2
					  , cusDeliverZipCode
					  , cusDeliverCity
					  , cusDeliverCountry
					  , cusData
					  , cusChgTimestamp
					  , cusChgUserIdNo
					  , cusChgApplicId
					  , cusChgHistoryFlg";					  
    				if 	($selectOldVersion != "")
            {
              //erst ab Version 6
              $SQLcusNew = $SQLcusNew."
					  , cusDiscount
					  , cusCustomerNews  ";
            }  
            $SQLcusNew = $SQLcusNew."
					  	)
                 VALUES (\"".$objcus->cusId."\"
					  , \"".$objcus->cusFirmname."\"
					  , \"".$objcus->cusFirmVATId."\"
					  , \"".$objcus->cusTitle."\"
					  , \"".$objcus->cusFirstName."\"
					  , \"".$objcus->cusLastName."\"
					  , \"".$objcus->cusStreet."\"
					  , \"".$objcus->cusStreet2."\"
					  , \"".$objcus->cusZipCode."\"
					  , \"".$objcus->cusCity."\"
					  , \"".$objcus->cusCountry."\"
					  , \"".$objcus->cusPhone."\"
					  , \"".$objcus->cusFax."\"
					  , \"".$objcus->cusEMail."\"
					  , \"".$objcus->cusEMailFormat."\"
					  , \"".$objcus->cusPassword."\"
					  , \"".$objcus->cusDeliverFirmname."\"
					  , \"".$objcus->cusDeliverTitle."\"
					  , \"".$objcus->cusDeliverFirstName."\"
					  , \"".$objcus->cusDeliverLastName."\"
					  , \"".$objcus->cusDeliverStreet."\"
					  , \"".$objcus->cusDeliverStreet2."\"
					  , \"".$objcus->cusDeliverZipCode."\"
					  , \"".$objcus->cusDeliverCity."\"
					  , \"".$objcus->cusDeliverCountry."\"
					  , \"".$objcus->cusData."\"
					  , \"".$objcus->cusChgTimestamp."\"
					  , \"".$objcus->cusChgUserIdNo."\"
					  , \"".$objcus->cusChgApplicId."\"
					  , \"".$objcus->cusChgHistoryFlg."\"";					  
    				if 	($selectOldVersion != "")
            {
              //erst ab Version 6
              $SQLcusNew = $SQLcusNew."
 
				      , \"".$objcus->cusDiscount."\"
					  , \"".$objcus->cusCustomerNews."\" ";
            }  
            $SQLcusNew = $SQLcusNew."
					    )";


      $qrycusNew = @mysqli_query($link,$SQLcusNew) or $success = 1;
    }
    @mysqli_close($link);
  }


//Bestellungen
  if(isset($impOrderdata)&& $impOrderdata=='1')
  {
    if($DBvariante==1)
    {
      $link = @mysqli_connect($impHost, $impUser, $impPassword, $impDBName) or die("<br />aborted: can´t connect to '$impHost' <br />");
    }
    else if($DBvariante==0)
    {
      $link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
    }
	$link->query("SET NAMES 'utf8'");
    $SQLord = "SELECT * FROM ".$order_gssb6.";";
    $qryord = @mysqli_query($link,$SQLord) or $success = 1;
    while($objord = @mysqli_fetch_object($qryord))
    {
      @mysqli_close($link);
      $link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
      $link->query("SET NAMES 'utf8'");

  $SQLordNew = "INSERT INTO ".$order_gssb7."
			( ordId
			, ordDate";					  
    				if 	($selectOldVersion != "" && $selectOldVersion != "6")
            {
              //erst ab Version 7
              $SQLordNew = $SQLordNew."
			, ordSendCode ";
            }  
            $SQLordNew = $SQLordNew."
			, ordCusIdNo
			, ordCustomerId
			, ordFirmname
			, ordFirmVATId
			, ordTitle
			, ordFirstName
			, ordLastName
			, ordStreet
			, ordStreet2
			, ordZipCode
			, ordCity
			, ordCountry
			, ordPhone
			, ordFax
			, ordShippingCond
			, ordShippingCost
			, ordDiscount1Value
			, ordDiscount1Prct
			, ordDiscount2Value
			, ordDiscount2Prct
			, ordPaymentCond
			, ordPaymentCost
			, ordDeliverFirmname
			, ordDeliverTitle
			, ordDeliverFirstName
			, ordDeliverLastName
			, ordDeliverStreet
			, ordDeliverStreet2
			, ordDeliverCity
			, ordDeliverZipCode
			, ordDeliverCountry
			, ordEMail
			, ordVAT1Value
			, ordVAT1Prct
			, ordVAT2Value
			, ordVAT2Prct
			, ordVAT3Value
			, ordVAT3Prct
			, ordTotalValue
			, ordTotalValueAfterDsc1
			, ordTotalValueAfterDsc2
			, ordCurrency
		    , ordFlg
			, ordSLC
			, ordChgTimestamp
			, ordChgUserIdNo
			, ordChgApplicId
			, ordChgHistoryFlg)

       VALUES (\"".$objord->ordId."\"
	        , \"".$objord->ordDate."\"";					  
    				if 	($selectOldVersion != "" && $selectOldVersion != "6")
            {
              //erst ab Version 7
              $SQLordNew = $SQLordNew."
	        , \"".$objord->ordSendCode."\" ";
            }  
            $SQLordNew = $SQLordNew."
			, \"".$objord->ordCusIdNo."\"
			, \"".$objord->ordCustomerId."\"
			, \"".$objord->ordFirmname."\"
			, \"".$objord->ordFirmVATId."\"
			, \"".$objord->ordTitle."\"
			, \"".$objord->ordFirstName."\"
			, \"".$objord->ordLastName."\"
			, \"".$objord->ordStreet."\"
			, \"".$objord->ordStreet2."\"
			, \"".$objord->ordZipCode."\"
			, \"".$objord->ordCity."\"
			, \"".$objord->ordCountry."\"
			, \"".$objord->ordPhone."\"
			, \"".$objord->ordFax."\"
			, \"".$objord->ordShippingCond."\"
			, \"".$objord->ordShippingCost."\"
			, \"".$objord->ordDiscount1Value."\"
			, \"".$objord->ordDiscount1Prct."\"
			, \"".$objord->ordDiscount2Value."\"
			, \"".$objord->ordDiscount2Prct."\"
			, \"".$objord->ordPaymentCond."\"
			, \"".$objord->ordPaymentCost."\"
			, \"".$objord->ordDeliverFirmname."\"
			, \"".$objord->ordDeliverTitle."\"
			, \"".$objord->ordDeliverFirstName."\"
			, \"".$objord->ordDeliverLastName."\"
			, \"".$objord->ordDeliverStreet."\"
			, \"".$objord->ordDeliverStreet2."\"
			, \"".$objord->ordDeliverCity."\"
			, \"".$objord->ordDeliverZipCode."\"
			, \"".$objord->ordDeliverCountry."\"
			, \"".$objord->ordEMail."\"
			, \"".$objord->ordVAT1Value."\"
			, \"".$objord->ordVAT1Prct."\"
			, \"".$objord->ordVAT2Value."\"
			, \"".$objord->ordVAT2Prct."\"
			, \"".$objord->ordVAT3Value."\"
			, \"".$objord->ordVAT3Prct."\"
			, \"".$objord->ordTotalValue."\"
			, \"".$objord->ordTotalValueAfterDsc1."\"
			, \"".$objord->ordTotalValueAfterDsc2."\"
			, \"".$objord->ordCurrency."\"
			, \"".$objord->ordFlg."\"
			, \"".$objord->ordSLC."\"
			, \"".$objord->ordChgTimestamp."\"
			, \"".$objord->ordChgUserIdNo."\"
			, \"".$objord->ordChgApplicId."\"
			, \"".$objord->ordChgHistoryFlg."\"
			  )";



      $qryord6 = @mysqli_query($link,$SQLordNew) or $success = 1;

      $currentordNewID = @mysqli_insert_id($link);

      //Bestellpositionen
      if($DBvariante==1)
      {
        $link = @mysqli_connect($impHost, $impUser, $impPassword, $impDBName) or die("<br />aborted: can´t connect to '$impHost' <br />");
      }
      else if($DBvariante==0)
      {
        $link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
      }
	  $link->query("SET NAMES 'utf8'");
      $SQLordpos = "SELECT * FROM ".$orderpos_gssb6." WHERE ordpOrdIdNo = '".$objord->ordIdNo."';";
      $qryordpos = @mysqli_query($link,$SQLordpos) or $success = 1;
      while($objordpos = @mysqli_fetch_object($qryordpos))
      {
        @mysqli_close($link);
        $link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
		$link->query("SET NAMES 'utf8'");
    //A SM 19.12.2013
	// Die Versionen bis 11 speichern "ordpItemDesc" unverschl�
	if (intval($selectOldVersion) < 12){
		$ordpItemDesc = base64_encode($objordpos->ordpItemDesc);
	}else{
		$ordpItemDesc = $objordpos->ordpItemDesc;
	}
	//E SM
    $SQLordpos6 = "INSERT INTO ".$orderpos_gssb7."
	                    ( ordpOrdIdNo
						, ordpPosNo
						, ordpItemId
						, ordpItemDesc
						, ordpQty
						, ordpPrice
						, ordpPriceTotal
						, ordpVATPrct
						, ordpVATValue
						, ordpChgTimestamp
						, ordpChgUserIdNo
						, ordpChgApplicId
						, ordpChgHistoryFlg";					  
    				if 	($selectOldVersion != "")
            {
              //erst ab Version 6
              $SQLordpos6 = $SQLordpos6."

					    , ordpImage ";
            }  
            $SQLordpos6 = $SQLordpos6.")

                  VALUES (\"".$currentordNewID."\"
				        , \"".$objordpos->ordpPosNo."\"
						, \"".$objordpos->ordpItemId."\"
						, \"".$ordpItemDesc."\"
						, \"".$objordpos->ordpQty."\"
						, \"".$objordpos->ordpPrice."\"
						, \"".$objordpos->ordpPriceTotal."\"
						, \"".$objordpos->ordpVATPrct."\"
						, \"".$objordpos->ordpVATValue."\"
						, \"".$objordpos->ordpChgTimestamp."\"
						, \"".$objordpos->ordpChgUserIdNo."\"
						, \"".$objordpos->ordpChgApplicId."\"
						, \"".$objordpos->ordpChgHistoryFlg."\"";					  
    				if 	($selectOldVersion != "")
            {
              //erst ab Version 6
              $SQLordpos6 = $SQLordpos6."

					    , \"".$objordpos->ordpImage."\" ";
            }  
            $SQLordpos6 = $SQLordpos6."
						  )";



        $qryordpos6 = @mysqli_query($link,$SQLordpos6) or $success = 1;
      }
    }
  }
  @mysqli_close($link);


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_DataImport;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
	  <script type="text/javascript" src="../../js/gslib.php"></script>
    <script language="JavaScript" type="text/javascript">
    function MM_reloadPage(init)
    {  //reloads the window if Nav4 resized
      if (init==true) with (navigator)
      {
        if ((appName=="Netscape")&&(parseInt(appVersion)==4))
        {
          document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage;
        }
      }
      else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
    }
    //--------------------------------------------------------------------------
    MM_reloadPage(true);
    </script>
</head>
<body>
<?php
require_once("../../include/page.header.php");
?>
<div id="PGdataimport">
<h1>&#187;&nbsp;<?php echo L_dynsb_DataImport;?>&nbsp;&#171;</h1>
<br />
<?php
if($success != 1)
{
?>
<p><?php echo L_dynsb_ImportSuccessful;?></p>
<?php
}

if($error_msg != "")
{
?>
 <p><?php echo $error_msg;?></p>
<?php
}
?>
<br />
</div>
<?php
require_once("../../include/page.footer.php");
?>
</body>
</html>
