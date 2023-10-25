<?php
/******************************************************************************/
/* File: mod.gssalevalue.detail.php                                           */
/******************************************************************************/

require("../../../../include/login.check.inc.php");
require_once("../../../../include/functions.inc.php");
require("../../../../../conf/db.const.inc.php");

/***************** Sprachdatei ************************************************/
if (!isset($_REQUEST['lang']) || strlen(trim($_REQUEST['lang'])) == 0)
{
    $lang = "deu";
}
else
{
	$lang = $_REQUEST['lang'];
	if(!file_exists("../../../../lang/lang_".$lang.".php"))
  {
    $lang = "deu";
  }
}

include("../../../../lang/lang_".$lang.".php");
/******************************************************************************/

/***************** Datenbankverbindung*****************************************/
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase)
  or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");
if(isset($_REQUEST['t'])) {
    $strtime = intval(trim($_REQUEST['t']));
} else {
    die('ERROR - missing parameter');
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
if (!isset($_SESSION['SESS_Currency']) || strlen(trim($_SESSION['SESS_Currency'])) == 0) {
  die ("<br />error: missing session parameter!<br />");
} else {
	$SESS_Currency = $_SESSION['SESS_Currency'];
}

$startDate = $strtime."01000000";
$endDate = $strtime."31235959";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_VolumeOfSales." ".L_dynsb_From." ".getmonth(substr($strtime, 4, 2), $SESS_languageIdNo).", ".substr($strtime, 0, 4);?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../../../css/link.css">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
    <script type="text/javascript" src="../../../../js/gslib.php"></script>
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
require_once("../../../../include/page.header.php");
?>

<div id="PGgssalevaluedetail">
	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="start" value="<?php echo $start;?>">
	<input type="hidden" name="ordIdNo" value="<?php echo $ordIdNo;?>">
	<input type="hidden" name="act" value="<?php echo $act;?>">

<h1><?php echo L_dynsb_VolumeOfSales." ".L_dynsb_From.": ".getmonth(substr($strtime, 4, 2), $SESS_languageIdNo).", ".substr($strtime, 0, 4);?></h1>

<table>
	<tr>
		<th><?php echo L_dynsb_ArticleNo;?></th>
		<th><?php echo L_dynsb_Quantity;?></th>
		<th><?php echo L_dynsb_Price."<br />in ".$SESS_Currency;?></th>
		<th><?php echo L_dynsb_Total."<br />in ".$SESS_Currency;?></th>
</tr>
<?php
	// start database query
	$SQL =  "SELECT op.ordpItemId AS itemId, op.ordpPrice AS price, SUM(op.ordpQty) AS qty, SUM(op.ordpPriceTotal) AS total
	            FROM ".DBToken."order o, ".DBToken."orderpos op
	            WHERE
	                o.ordIdNo = op.ordpOrdIdNo AND
	                o.ordDate >= '".$startDate."' AND
	                o.ordDate <= '".$endDate."' AND
	                o.ordChgHistoryFlg <> '0'
	            GROUP BY op.ordpItemId ORDER BY total DESC";

	$qry = @mysqli_query($link, $SQL);

	$x = 0;
	$sumall = 0;
	while($obj = @mysqli_fetch_object($qry)) {

	if ($x % 2 != 0)
		$rowStyle = " odd ";
	else
		$rowStyle = " even ";
?>
	<tr id="d<?php echo $x;?>" class="<?php echo $rowStyle;?>">
	  <td><?php echo $obj->itemId;?></td>
	  <td align="right"><?php echo replPtC(sprintf("%01.2f",$obj->qty));?></td>
	  <td align="right"><?php echo replPtC(sprintf("%01.2f",$obj->price));?></td>
	  <td align="right"><?php echo replPtC(sprintf("%01.2f",$obj->total));?></td>
	</tr>
<?php
		$x++;
		$sumall = $sumall + doubleval($obj->total);
	} // end of while

	$SQL = "SELECT SUM(ordTotalValueAfterDsc2) AS total FROM ".DBToken."order WHERE
	        ordDate >= '".$startDate."' AND
	        ordDate <= '".$endDate."' AND
	        ordChgHistoryFlg <> '0'";
	$qry = @mysqli_query($link, $SQL);
	$obj = @mysqli_fetch_object($qry);
	$total = doubleval($obj->total);

	$diff = $total - $sumall;
?>
	<tr>
	    <td colspan="4"><img src="../../../../image/blank.gif" height="1" alt=""></td>
	</tr>
	<tr>
	    <th colspan="3">&nbsp;</th>
	    <th align="right"><?php echo replPtC(sprintf("%01.2f",$sumall));?></th>
	</tr>
	<tr>
	    <th colspan="3" align="right"><?php echo L_dynsb_DifferenceRatesDiscounts;?>:</th>
	    <th align="right"><?php echo replPtC(sprintf("%01.2f",$diff));?></th>
	</tr>
	<tr>
	    <th colspan="3" align="right"><?php echo L_dynsb_Total3?></th>
	    <th align="right"><?php echo replPtC(sprintf("%01.2f",$total));?></th>
	</tr>
</table>
<br />

<!-- navigation // -->
<div class="footer">
  <input type="button" class="button" onclick="javascript:window.self.close();" name="btn_close" value="<?php echo L_dynsb_Cancel;?>">
</div>
</div>
<?php
require_once("../../../../include/page.footer.php");
?>
</body>
</html>







