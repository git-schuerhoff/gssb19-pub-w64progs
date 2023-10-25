<?php
/******************************************************************************/
/* File: mod.discount.report.php                                              */
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
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_CustomerDiscount;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
	  <script type="text/javascript" src="../../js/gslib.php?lang=<?php echo $SESS_languageIdNo;?>"></script>
</head>
<body>
<?php
require_once("../../include/page.header.php");
?>

<div id="PGdiscountreport">
<h1>&#187;&nbsp;<?php echo L_dynsb_CustomerDiscount;?>&nbsp;&#171;</h1>

<table>
  <?php
   foreach($_REQUEST as $key => $value)
  {
      $$key = trim($value);
  }

  $customers = split("_",$cusIds);
  echo "<tr><th colspan=\"4\" align=\"left\">".L_dynsb_FollowingCustomersDiscount." ".$r."%:</th></tr>";
  echo "<tr><th>".L_dynsb_CustomerNo."</th>"
     . "<th>".L_dynsb_Firm."</th>"
     . "<th>".L_dynsb_Name."</th>"
     . "<th>".L_dynsb_Email."</th>"
     . "</tr>";
  for($i=1; $i<sizeof($customers); $i++)
  {
    $sql = "SELECT * from ".DBToken."customer where cusIdNo = '".$customers[$i]."'";
    $qry = @mysqli_query($link,$sql);
    $obj = @mysqli_fetch_object($qry);

    if(strlen($obj->cusId)==0)
    { $cusId = "-------"; }
    else
    { $cusId = $obj->cusId; }

    if(strlen($obj->cusFirmname)==0)
    { $cusFirmname = "--------------"; }
    else
    { $cusFirmname = $obj->cusFirmname; }

    echo "<tr><td>".$cusId."</td><td>".$cusFirmname."</td><td>".$obj->cusTitle." ".$obj->cusFirstName." ".$obj->cusLastName."</td><td>".$obj->cusEMail."</td></tr>";
  }
  ?>
</table>

<div class="footer">
  <input type="button" class="button" name="btnprint" value="<?php echo L_dynsb_Print;?>" onclick="javascript:print()">
</div>

</div>
<?php
require_once("../../include/page.footer.php");
?>
</body>
</html>
