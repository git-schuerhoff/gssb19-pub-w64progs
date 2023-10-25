<?php
/******************************************************************************/
/* File: mod.coupon.detail.php                                                */
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

if(strtolower($act) == "e")
{
    $qrySQL = "SELECT * FROM dsb6_couponWHERE coupId = '".$coupId."'";
    $qry = @mysqli_query($link,$qrySQL);
    $obj = @mysqli_fetch_object($qry);

    foreach($obj as $key => $value)
    {
        $$key = trim($value);
    }
}

$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_Coupons;?></title>
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
<form name="frmCoup" action="mod.coupon.save.php" method="post">

<?php
require_once("../../include/page.header.php");
?>

<div id="PGcoupondetail">
    <input type='hidden' name='act' value='a'>
    <input type='hidden' name='lang' value='<?echo $lang;?>'>

<h1>&#187;&nbsp;<?php echo L_dynsb_Coupons;?>&nbsp;&#171;</h1>

<h2><?php echo L_dynsb_CouponValue;?></h2>
<p><input type="text"value="<?php echo $coupPrice; ?>" name="coupPrice"></p>

<h2><?php echo L_dynsb_Currency;?></h2>
<p><input type="text" value="<?php echo $coupCurrency; ?>" name="coupCurrency"></p>

<h2><?php echo L_dynsb_NumberOfCoupons;?></h2>
<p><input type="text" maxlength="96" value="<?php echo $coupCount;?>" name="coupCount"></p>
<p><input type="radio" class="radio" value="once" name="valid" checked>&nbsp;<?php echo L_dynsb_vouchercodeUnique;?></p>
<p><input type="radio" class="radio" value="unlimited" name="valid">&nbsp;<?php echo L_dynsb_vouchercodePermanent;?></p>

<div class="footer">
	<input type="button" class="button" onclick="javascript:submitForm('frmCoup');" name="btn_upload" value="<?php echo L_dynsb_Create;?>">
</div>
</div>
<?php
require_once("../../include/page.footer.php");
?>

</form>
</body>
</html>
