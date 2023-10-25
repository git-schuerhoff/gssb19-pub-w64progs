<?php
/******************************************************************************/
/* File: customer.import.save.php                                             */
/******************************************************************************/

require("../include/login.check.inc.php");
require_once("../include/functions.inc.php");
require("../../conf/db.const.inc.php");
require_once("../include/secure.functions.inc.php");
require_once("../class/class.shoplog.php");

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
$fields = split("_",substr($custtxt,1));

$firstRow=0;
$countCus=0;
$countCusSuccess=0;
$handle = fopen($_REQUEST['file'],"r");
if($_REQUEST['sep']=="tab")
{
  $sep = "\t";
}
else
{
  $sep = $_REQUEST['sep'];
}
$aIgnoreKeys = array("cusBank", "cusBLZ", "cusAccountNo",
                     "cusAccountOwner", "cusCreditCard",
                     "cusCreditValidMonth", "cusCreditValidYear",
                     "cusCreditNo", "cusCreditChk1",
                     "cusCreditChk2", "cusCreditOwner");
$aCus = array();

while ($line = fgets ($handle, 20000))
{
  $data = explode($sep, $line);
  if($firstRow!=0)
  {
    $sqlInsert = "INSERT INTO ".DBToken."customer ";
    foreach($fields as $key => $value)
    {
      $pos = strpos($value,"-");
      $Cols = substr($value,$pos+1);
      $Values = $data[substr($value,0,$pos)];

      if(in_array($Cols, $aIgnoreKeys))
      {
        $aCus[$Cols] = $Values;
      }
      else
      {
        $sqlCols .= $Cols.", ";
        $sqlValue .= "'".$Values."', ";
      }
    }
    $cusData = new shoplog();
    $blob = $cusData->getRandom4KBlob();
    $blob = $cusData->createSecureCustomerData($aCus, $blob);
    $sql = $sqlInsert."(".$sqlCols."cusData ) values (".$sqlValue."'".$blob."')";
    $countCus++;
    if(@mysqli_query($link,$sql))
    {
      $countCusSuccess++;
    }
  }
  else
  {
    $firstRow++;
  }
  unset($data);
  unset($sqlCols);
  unset($sqlValue);
}
fclose($handle);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_CustomerDataImport;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software AG">
    <link rel="stylesheet" type="text/css" href="../css/link.css">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2003 GS Software AG">
</head>
<body>
<?php
require_once("../include/page.header.php");
?>

<div id="PGcustomerimportsave">
<h1>&nbsp;&#187;&nbsp;<?php echo L_dynsb_CustomerDataImport;?>&nbsp;&#171;</h1>
 <p>
 	<?php echo $countCusSuccess."&nbsp;".L_dynsb_Of."&nbsp;".$countCus."&nbsp;".L_dynsb_CreatedNewCustomers; ?>
 </p>
 <br />
</div>
<?php
require_once("../include/page.footer.php");
?>
