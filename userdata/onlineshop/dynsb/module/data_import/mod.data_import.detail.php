<?php
/******************************************************************************/
/* File: mod.data_import.detail.php                                           */
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
    $qrySQL = "SELECT * FROM dsb8_coupon WHERE coupId = '".$coupId."'";
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
<form name="frmImport" action="mod.data_import.import.php" method="post">

<?php
require_once("../../include/page.header.php");
?>
<div id="PGdataimportdetail">
	<input type='hidden' name='lang' value='<?echo $lang;?>'>
	<input type='hidden' name='act' value='a'>

<h1>&#187;&nbsp;<?php echo L_dynsb_DataImport;?>&nbsp;&#171;</h1>
<h2><?php echo L_dynsb_DBfromGSSB5;?></h2>

<table>
	<tr>
	  <td><?php echo L_dynsb_FromGSSBVersion;?>:&nbsp;</td>
	  <td>
      <select name="selectOldVersion" width="40" style="width: 50px">
          <option value="5">5</option>
          <option value="6">6</option>
          <option value="7">7</option>
          <option value="8">8</option>
          <option value="9">9</option>
		  <option value="10">10</option>
          <option value="11">11</option>
		  <option value="12">12</option>
		  <option value="14">14</option>
		  <option value="15">15</option>
          <option value="16">16</option>
		  <option value="17">17</option>
		  <option value="18" selected>18</option>
      </select>
    </td>
	</tr>
	<tr>
	  <td><?php echo L_dynsb_Host;?>:&nbsp;</td>
	  <td><input type="text" value="localhost" name="impHost"></td>
	</tr>
	<tr>
	  <td><?php echo L_dynsb_DatabaseName;?>:&nbsp;</td>
	  <td><input type="text" value="" name="impDBName"></td>
	</tr>
	<tr>
	  <td><?php echo L_dynsb_Username;?>:&nbsp;</td>
	  <td><input type="text" value="" name="impUser"></td>
	</tr>
	<tr>
	  <td><?php echo L_dynsb_Password;?>:&nbsp;</td>
	  <td><input type="text" value="" name="impPassword"></td>
	</tr>
</table>

<br />
<p>
  <input type="checkbox" class="checkbox" value="1" name="impCustomerdata">  <?php echo L_dynsb_ImportCustomerData;?>
</p>

<p>
	<input type="checkbox" class="checkbox" value="1" name="impOrderdata"> <?php echo L_dynsb_ImportOrderData;?>
</p>

<div class="footer">
	<input type="button" class="button" onclick="javascript:submitForm('frmImport');" name="btn_upload" value="<?php echo L_dynsb_StartImport;?>">
</div>

</div>
<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
