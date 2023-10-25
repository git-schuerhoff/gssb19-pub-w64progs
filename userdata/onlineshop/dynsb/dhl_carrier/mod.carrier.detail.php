<?php
/******************************************************************************/
/* File: mod.carrier.detail.php                                               */
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

$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";

//$resultID = @mysqli_query("SELECT COUNT(cusIdNo) FROM ".DBToken."customer WHERE 1 = 1");
//
//$total    = @mysql_result($resultID,0);
//if($total == '') $total = 0;
//
//$start = (isset($_REQUEST['start'])) ? abs((int)$_REQUEST['start']) : 0;
//$limit = getentity(DBToken."settings","setRowCount","setIdNo = '1'");     // number of records per page
//
//// check parameter $start (maybe corrupt parameter in url)
//if(abs($total) == 0) $start = 0;
//else $start    = ($start >= $total) ? $total - $limit : $start;
//if($start < 0) $start = 0;
//
$sql = "SELECT * FROM ".DBToken."settings";
$rs = @mysqli_query($link,$sql);
$obj = @mysqli_fetch_object($rs);

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
    var SelectBox = document.getElementsByName("customers[]");
    //--------------------------------------------------------------------------
    function checkValue(ID)
    {
      var exist = false;
      for(var a=0; a<SelectBox[0].length; a++)
      {
        if(SelectBox[0].options[a].value==ID)
        {
          exist =  true;
        }
      }
      return exist;
    }
    <?php include $dhlLabelURL."dhl_javascript.php"; ?>
    </script>
</head>
<body>
<form name="frmCarrier" action="mod.carrier.save.php" method="get">

<?php
require_once("../include/page.header.php");
?>
<div id="PGcarrierdetail">
	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="start" value="<?php echo $start;?>">
	<input type="hidden" name="backstart" value="<?php echo $backstart;?>">
	<input type="hidden" name="next" value="">
	<input type="hidden" name="nav" value="">
	<input type='hidden' name='act' value='a'>

<?php
//nur wenn remote zugriff m�ch ist, inkludiere logo
if (ini_get("allow_url_fopen") == "1")
	@include ($dhlLabelURL."dhl_logo8.php?url=".$dhlLabelURL);
?>
<h1>DHL <?php echo L_dynsb_Set;?></h1>
<h2><?php echo L_dynsb_SenderData;?></h2>

<table>
  <tr>
    <td align="right" style="width:140px;"><?php echo L_dynsb_Firm;?>&nbsp;</td>
    <td>
      <input type="text" class="customer" tabindex=2 maxlength="64" name="Firmname" value='<?php echo  $obj->lpFirmname;?>'>&nbsp;
    </td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Title;?>&nbsp;</td>
    <td><input type="text" class="customer" tabindex=3 maxlength="16" value="<?php echo $obj->lpSalutation;?>" name="Salutation"></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Firstname;?>&nbsp;</td>
    <td><input type="text" class="customer" tabindex=4 maxlength="32" value="<?php echo $obj->lpFirstname;?>" name="FirstName"></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Lastname;?>&nbsp;</td>
    <td><input type="text" class="customer" tabindex=5 maxlength="32" value="<?php echo $obj->lpLastname;?>" name="LastName"></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Street;?>&nbsp;</td>
    <td><input type="text" class="customer" tabindex=6 maxlength="32" value="<?php echo $obj->lpStreet;?>" name="Street"></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Addition;?>&nbsp;</td>
    <td><input type="text" class="customer" tabindex=6 maxlength="32" value="<?php echo $obj->lpAddress;?>" name="Address"></td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Zipcode;?>&nbsp;</td>
    <td><input type="text" class="customer" tabindex=7 maxlength="16" value="<?php echo $obj->lpZipCode;?>" name="ZipCode">&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_City;?>&nbsp;</td>
    <td><input type="text" class="customer" tabindex=8 maxlength="32" value="<?php echo $obj->lpCity;?>" name="City">&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Country;?>&nbsp;</td>
    <td><input type="text" class="customer" tabindex=9 maxlength="32" value="<?php echo $obj->lpCountry;?>" name="Country">&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Phone;?>&nbsp;</td>
    <td><input type="text" class="customer" tabindex=10 maxlength="20" value="<?php echo $obj->lpPhone;?>" name="Phone">&nbsp;</td>
  </tr>
</table>

<div class="footer">
  <input type="button" class="button" onclick="javascript:submitForm('frmCarrier');" name="btn_save" value="<?php echo L_dynsb_Save;?>">
  <input type="button" class="button" onclick="javascript:self.location.href='../help/about.php?lang=<?php echo $lang;?>';" name="btn_save" value="<?php echo L_dynsb_Cancel;?>">
</div>
</div>
<?php
require_once("../include/page.footer.php");
?>
</form>
</body>
</html>

