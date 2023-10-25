<?php
/******************************************************************************/
/* File: mod.availability.categories.php                                      */
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

?>
		
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_Marketing;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
	 <script type="text/javascript"src="../../js/gslib.php"></script>
	 <script type="text/javascript" src="../../js/calendar.js"></script>
	<script type="text/javascript" src="../../js/calendar-<?php echo $strcal;?>.js"></script>
	<script type="text/javascript" src="../../js/calendar-setup.js"></script>
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
    //--------------------------------------------------------------------------
    </script>
</head>
<body>
<?php
require_once("../../include/page.header.php");
?>
<div id="PGavailabilitycategories">
<h1>&#187;&nbsp;<?php echo L_dynsb_Marketing;?>&nbsp;&#171;</h1>

<?php
#print_r($_REQUEST);
$cust_checked = $_REQUEST['cust_checked'];
if(!empty($cust_checked) && !empty($sender) && !empty($subject) && !empty($conttext) && !empty($akt_key)) {	
	$header = "MIME-Version: 1.0 \n";
	$header .="From: ".$sender." <noreply@".$_SERVER['SERVER_NAME'].">\n";
	$header .= "X-Mailer: PHP\n";
	$header .= "X-Sender-IP: ".$_SERVER['REMOTE_ADDR']."\n";
	$header .= "X-Priority: 3\n"; //1 UrgentMessage, 3 Normal
	$header .= "Content-type: text/html; charset=\"UTF-8\"\n";
	
	foreach($cust_checked as $cust) {
		$SQL = "SELECT cusEMail FROM ".DBToken."customer WHERE cusIdNo = '".$cust."'";
		$qry = mysqli_query($link,$SQL);
		$obj = mysqli_fetch_object($qry);
		
		
		if(@mail($obj->cusEMail, $subject, nl2br(stripslashes($conttext)), $header)) {
			$SQL = "INSERT INTO ".DBToken."aktivities(custId, mkKey, aktText, aktDate) 
					VALUES('".$cust."', '".$akt_key."', '".$subject."\n\n".$conttext."', NOW()+0);";
			$qry = mysqli_query($link,$SQL);	
			echo "<b>+ ".$obj->cusEMail." - ".L_dynsb_MailWasSent."</b><br />"; 		
		} else {
			echo "<b>- ".$obj->cusEMail." - ".L_dynsb_MailWasNotSent."</b><br />";
		}
	}
}
?> 


<div class="footer">
	<input type="button" class="button" onclick="javascript:location.href='mod.marketing.advmail.php?lang=<?php echo $lang;?>'" value="<?php echo L_dynsb_Back;?>">
</div>


</div>

<?php
require_once("../../include/page.footer.php");
?>
</body>
</html>

