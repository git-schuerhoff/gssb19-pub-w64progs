<?php
/**
 * mod.newsletter.send.php -  asscociate newsletter with mailgroups and send it afterwards
 *
 */

require("../../include/login.check.inc.php");
require_once("../../include/functions.inc.php");
require("../../../conf/db.const.inc.php");
require("../../class/class.mailservice.php");
require("../../class/class.logfilewriter.php");

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
if(isset($_REQUEST['pk']) && is_numeric($_REQUEST['pk']))
  $_SESSION["nlIdNo"] = intval($_REQUEST['pk']);


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
$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";

//*****************************************************************************
//*****************************************************************************
//*****************************************************************************
$timestamp = date("Y-m-d H:i:s");

//if id is given, get details from database
if(isset($_SESSION["nlIdNo"])) {
  $nldoIdNo = $_SESSION["nlIdNo"];

	//*** GET DOCUMENT ***
  $qryNlDoc = "SELECT * FROM ".DBToken."nl_documents WHERE nldoIdNo = '$nldoIdNo' AND nldoStatusFlg = 1";
  $resNlDoc = @mysqli_query($link,$qryNlDoc);

  $obj = mysqli_fetch_object($resNlDoc);

  if (is_object($obj)) {
    $nlSubject		= $obj->nldoSubject;
    $nlContText		= stripslashes($obj->nldoContentText);
    $nlContHtml		= stripslashes($obj->nldoContentHtml);
    $nlChangeDate	= date_mysql2german($obj->nldoChangeDate);
    $nlSendDate		= date_mysql2german($obj->nldoSendDate);
		$statusFlg		= $obj->nldoStatusFlg;
  }

  //*** UPDATE status to send***
  if (isset($_REQUEST["btnsend"])) {

 		$aMG = $_REQUEST["chkmg"];
 		if (is_array($aMG)) {

			//Get SETTINGS
			$qrySet = "SELECT * FROM ".DBToken."nl_settings";
			$resSet = @mysqli_query($link,$qrySet);

			if ($rowSet = mysqli_fetch_array($resSet))	{
				$nlSender = $rowSet["nlseSenderAddress"];
				$limit 		= $rowSet["nlseLimit"];
				$pause 		= $rowSet["nlsePause"];
			}

			if (empty($nlSender)) {
				$noticeText = L_dynsb_EnterSender;
			}
			else {

				$send = true;
				unset($_SESSION["nlIdNo"]);

				//make string for query
				$mgIds = implode(", ",$aMG);

				//GET all mail adresses (TEXT)
				$qryAddrText = "
				SELECT ".DBToken."nl_addresses.* FROM ".DBToken."nl_mailgroups
				  JOIN ".DBToken."nl_addr2mg   ON nlmgIdNo = admgNlmgIdNo
				  JOIN ".DBToken."nl_addresses ON nladIdNo = admgNladIdNo AND nladActiveFlg = 1 AND nladFormat = 'T'

				  WHERE nlmgIdNo IN ($mgIds)
				  GROUP BY nladAddress
				";
				$resAddrText = @mysqli_query($link,$qryAddrText);

				//GET all mail adresses (HTML)
				$qryAddrHtml = "
				SELECT ".DBToken."nl_addresses.* FROM ".DBToken."nl_mailgroups
				  JOIN ".DBToken."nl_addr2mg   ON nlmgIdNo = admgNlmgIdNo
				  JOIN ".DBToken."nl_addresses ON nladIdNo = admgNladIdNo AND nladActiveFlg = 1 AND nladFormat = 'H'

				  WHERE nlmgIdNo IN ($mgIds)
				  GROUP BY nladAddress
				";
				$resAddrHtml = @mysqli_query($link,$qryAddrHtml);

				//UPD document status -> Archiv
		    $updNlDoc = "UPDATE ".DBToken."nl_documents " .
		                "		SET nldoStatusFlg 	= '2'" .
		                "	WHERE nldoIdNo = '$nldoIdNo' "
		                ;
				@mysqli_query($link,$updNlDoc);
			}
 		}
  }
	//*** END UPDATE ***

}

//*** Get all MAILGROUPS ***
$qryMG = "SELECT * FROM ".DBToken."nl_mailgroups ORDER BY nlmgIdNo";
$resMG = @mysqli_query($link,$qryMG);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
  <title><?php echo L_dynsb_SendNewsletter;?></title>
  <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
  <meta content="de" http-equiv="Language">
  <meta name="author" content="GS Software Solutions GmbH">
  <link rel="stylesheet" type="text/css" href="../../css/link.css">
  <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
  <script type="text/javascript" src="../../js/gslib.php"></script>
</head>
<body>
<form name="frmCnews" action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
<?php
require_once("../../include/page.header.php");
?>

<div id="PGnewsletterdetail">
  <input type="hidden" name="lang" value="<?php echo $lang;?>">


<h1>&#187;&nbsp;<?php echo L_dynsb_SendNewsletter;?>&#171;</h1>
<?php //notice
if (!empty($noticeText)) {
?>
	<p class="notice">
		<?php echo $noticeText;?>
	</p>
<?php	}

	if (!$_REQUEST["btnsend"]) {
?>
<h2><?php echo L_dynsb_MailingGroups;?></h2>
<p>
<?php
while ($aMg	 = mysqli_fetch_array($resMG)) {
?>
	<input type="checkbox" class="checkbox" name="chkmg[]" value="<?php echo $aMg["nlmgIdNo"];?>"> <?php echo $aMg["nlmgName"];?><br />
<?php
}
?>
</p>
<p>
<input type="submit" class="button large" value="<?php echo L_dynsb_SendNewsletter;?>" name="btnsend">
</p>

<br />
<h2><?php echo L_dynsb_Newsletter;?></h2>
<p>
  <input type="text" name="subject" value="<?php echo $nlSubject;?>" maxlength="150" readonly>
</p>

<p>
  <textarea name="conttext" id="conttext" cols="120" rows="10" readonly><?php echo $nlContText;?></textarea>
</p>

<p>
  <textarea name="conthtml" id="conthtml" cols="120" rows="10" readonly><?php echo $nlContHtml;?></textarea>
</p>

<div class="footer">
	<input type="submit" class="button large" value="<?php echo L_dynsb_SendNewsletter;?>" name="btnsend">
  <input type="button" class="button" value="<?php echo L_dynsb_Back?>" onclick="javascript:self.location.href='mod.newsletter2.detail.php'">
</div>
<?php
	} else {
?>
<pre>
<?php
if ($send) {

	$oLog = new logfilewriter("newsletter");

//Textmails
	$mail = new mailservice($nlSender, "", $nlSubject, $nlContText);
	$mail->setLimit($limit);
	$mail->setPause($pause);

	//text header
	$mailHeader = $mail->createHeader();

	echo "Text:\r\n";
	$oLog->write("Text:");

	while ($rowAddrText = mysqli_fetch_array($resAddrText)) {

		$recipient = $rowAddrText["nladAddress"];
		$mail->setRecipient($recipient);
		$bSend = $mail->sendMail();

		if ($bSend) {
			echo "+ $recipient\r\n";
			$oLog->write("+ $recipient");
		}
		else {
			echo "- $recipient\r\n";
			$oLog->write("- $recipient");
		}
		flush();
	}

//HTML-Mails
	//html header
	$mail->createHtmlHeader();
	//html-content
	$mail->setMessage($nlContHtml);

	echo "\r\nHTML:\r\n";
	$oLog->write("", false);
	$oLog->write("HTML:");

	while ($rowAddrHtml = mysqli_fetch_array($resAddrHtml)) {

		$recipient = $rowAddrHtml["nladAddress"];
		$mail->setRecipient($recipient);
		$bSend = $mail->sendMail();

		if ($bSend) {
			echo "+ $recipient\r\n";
			$oLog->write("+ $recipient");
		}
		else {
			echo "- $recipient\r\n";
			$oLog->write("- $recipient");
		}
	}

	$oLog->write("", false);
	$oLog->write("-----------------", false);
	$oLog->write($nlContText);
	$oLog->write("-----------------", false);
	$oLog->write($nlContHtml);
}
?>
</pre>
<br />
<?php
	}
?>
</div>
<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
