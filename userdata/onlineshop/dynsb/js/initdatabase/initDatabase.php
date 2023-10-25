<?php
/******************************************************************************/
/* File: initDatabase.php                                               */
/******************************************************************************/

require("../include/login.check.inc.php");
require_once("../include/functions.inc.php");
require("../../conf/db.const.inc.php");

/***************** Sprachdatei ************************************************/
if (!isset($_REQUEST['lang']) || strlen(trim($_REQUEST['lang'])) == 0)
{
    $lang = "deu";
}
else
{
	$lang = $_REQUEST['lang'];
	if(!file_exists("../lang/lang_".$lang.".php"))
  {
    $lang = "deu";
  }
}

include("../lang/lang_".$lang.".php");
/******************************************************************************/

/***************** Datenbankverbindung*****************************************/
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase)
  or die("<br>aborted: can´t connect to '$dbServer' <br>");
$link->query("SET NAMES 'utf8'");
$chgApplicId = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1)); // script name
if (!isset($_SESSION['SESS_userIdNo']) || strlen(trim($_SESSION['SESS_userIdNo'])) == 0) {
  die ("<br>error: missing session parameter!<br>");
} else {
	$SESS_userIdNo = $_SESSION['SESS_userIdNo'];
}
if (!isset($_SESSION['SESS_userLogin']) || strlen(trim($_SESSION['SESS_userLogin'])) == 0) {
  die ("<br>error: missing session parameter!<br>");
} else {
	$SESS_userId = $_SESSION['SESS_userId'];
}
if (!isset($_SESSION['SESS_languageIdNo']) || strlen(trim($_SESSION['SESS_languageIdNo'])) == 0) {
  die ("<br>error: missing session parameter!<br>");
} else {
	$SESS_languageIdNo = $_SESSION['SESS_languageIdNo'];
}

$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";

//$sql = "SELECT * FROM ".DBToken."settings";
//$rs = @mysqli_query($sql);
//$obj = @mysqli_fetch_object($rs);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_Carrier;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
	  <script type="text/javascript" src="../js/gslib.php?lang=<?php echo $SESS_languageIdNo;?>"></script>
    <script type="text/javascript">
    function starten()
    {
      var bCheck = confirm("<?php echo L_dynsb_SureWantDelete;?>");
      if(bCheck==true)
      {
        document.frmInitDB.submit();
      }
    }
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
<form name="frmInitDB" action="clearDatabase.php" method="GET">

<?php
require_once("../include/page.header.php");
?>
<DIV id="PGcarrierdetail">
	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="start" value="<?php echo $start;?>">
	<input type="hidden" name="backstart" value="<?php echo $backstart;?>">
	<input type="hidden" name="next" value="">
	<input type="hidden" name="nav" value="">
	<input type='hidden' name='act' value='a'>

<h1><?php echo L_dynsb_InitDatabaseHead?></h1>
<h2><?php echo L_dynsb_InitDatabaseHead2;?></h2>
<br />
<br />
<table>
  <tr>
    <td align="right" style="width:800px;"><?php echo L_dynsb_InitDatabaseBody;?>&nbsp;<?php echo ' <b>'.$dbDatabase.'</b> '?><?php echo L_dynsb_InitDatabaseBody2;?></td>
  </tr>
</table>
<br />
<br />

<div class="footer">
  <input type="button" class="button" onclick="starten();" name="btn_save" value="<?php echo L_dynsb_InitStart;?>">
  <input type="button" class="button" onclick="javascript:self.location.href='../help/about.php?lang=<?php echo $lang;?>';" name="btn_save" value="<?php echo L_dynsb_Cancel;?>">
</div>
</DIV>
<?php
require_once("../include/page.footer.php");
?>
</form>
</body>
</html>

