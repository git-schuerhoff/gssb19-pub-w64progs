<?php
/**
 * Generates a form where the user can input the DHL-Tracking Number
 *
 * Created on 15.11.2006
 *
 * @author Jan Reker
 * @version 1.0
 * @package dhl_carrier
 */

require("../include/login.check.inc.php");
require_once("../include/functions.inc.php");


/**
 * Selects the proper language from the POST/GET-parameter "lang"
 * and includes the right language file.
 *
 * @return string 3 character abbreviation of the language
 */
function selectPageLanguage(){
//***************** Sprachdatei ************************************************/
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
return $lang;
}

/**
 * Connect to db with settings specified in "../../conf/db.const.inc.php"
 * @return resource db-resouce-handler
 */
function connectDb(){
require("../../conf/db.const.inc.php");

$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase)
  or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");
return $link;
}

/**
 * Returns first result row of the query
 *
 */
function selectOrderData($pk,$link){
  $sql = "SELECT * FROM ".DBToken."order WHERE ordIdNo='$pk'";
  $rs = @mysqli_query($link,$sql);
  return @mysqli_fetch_object($rs);
}


/**
 * Updates ordSendCode for pk
 *
 * @param string $dhldata DHL-Tracking number
 * @param integer $pk ID of the order
 * @param resource $link Resource of the database connection
 * @return bool if update was succesful
 */
function updateOrderData($dhldata,$pk,$link){
  $sql = "UPDATE ".DBToken."order " .
  		 "SET ordSendCode='$dhldata' " .
  		 "WHERE ordIdNo='$pk'";
  return @mysqli_query($link,$sql);

}

// Check if the Parameter is correct
if (isset($_REQUEST['pk']) && trim($_REQUEST['pk'])!="" )
   {
     $pk=$_REQUEST['pk'];
   }
   else
   {die ("ProduktID invalid <a href='./mod.carrier.tracking.php?lang=".selectPageLanguage()."'>DHL-Tracking</a>");
   }


//Call the functions to display the page properly
$lang=selectPageLanguage();
$link=connectDb();

//if button was pressed, update DHL data
$updateMessage="";
if (isset($_REQUEST['buttonSubmitDhlData'])){

    $ordSendCode=$_REQUEST['ordSendCode'];

	if (updateOrderData($ordSendCode,$pk,$link)==true)
	   {
	   	$updateMessage = "&nbsp;&#187;&nbsp;".L_dynsb_DHLupdateSuccesful."&#171;&nbsp;";
	   }
	   else{
	   	$updateMessage = "&nbsp;&#187;&nbsp;".L_dynsb_DHLupdateFailed."&#171;&nbsp;";
	   }
    }

//Get data
$oOrder = selectOrderData($pk,$link);

//
//$partner_signature = $partnerID."::".$shipmentID."::".$RequestTimestamp."::".$keyPhase."::".$partnerUserId."::".$userEmail."::".$keyword;
//$partner_sig = substr(md5(trim($partner_signature)),0,16);
//
//$sql = "SELECT * FROM ".DBToken."settings";
//$rs = @mysqli_query($sql);
//$olp = @mysqli_fetch_object($rs);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1" >
  <meta content="de" http-equiv="Language">
  <meta name="author" content="GS Software Solutions GmbH">
  <link rel="stylesheet" type="text/css" href="../css/link.css">
  <script ltype="text/javascript">
    <?php include $dhlLabelURL."dhl_javascript.php"; ?>
  </script>
 <script type="text/javascript" src="../js/gslib.php?lang=<?php echo $SESS_languageIdNo;?>"></script>
</head>

<body>
<?php
require_once("../include/page.header.php");
?>
<div id="PGdhltracking">
<?@include $dhlLabelURL."dhl_logo8.php?url=".$dhlLabelURL; ?>

<h1>&nbsp;&#187;&nbsp;<?php echo L_dynsb_DHLtracking ;?>&nbsp;&#171;</h1>
<h2><?php echo L_dynsb_Sender;?></h2>

<table>
 <tr>
  <td align="right" style="width:160px;"><?php echo L_dynsb_DHLtrackingNumber;?>:</td>
  <td>
<?php
    $dhlLink="";
    if (trim($oOrder->ordSendCode)!="")
    {
			$dhlLink=	"http://nolp.dhl.de/nextt-online-public/set_identcodes.do?" .
     		"lang=de&amp;zip=$oOrder->ordZipCode" .
     		"&amp;idc=$oOrder->ordSendCode";
			echo "<a href='javascript:popUpWindow(\"$dhlLink\",\"dhlPopUp\",505,600);'>";
			echo  "$oOrder->ordSendCode";
			echo "</a>";
		}
?>
&nbsp;
    </td>
  </tr>

  <tr>
    <td align="right">&nbsp;</td>
    <td> &nbsp;</td>
  </tr>

  <tr>
    <td align="right"><?php echo L_dynsb_Date;?>:</td>
    <td style="font-weight:bold;"><?php echo timestamp_mysql2german($oOrder->ordDate);?>&nbsp;</td>
  </tr>

  <tr>
    <td align="right"><?php echo L_dynsb_Firm;?>:</td>
    <td><?php echo $oOrder->ordFirmname;?>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Title;?>:</td>
    <td><?php echo $oOrder->ordTitle;?>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Name;?>:</td>
    <td style="font-weight:bold;"><?php echo $oOrder->ordLastName;?>, <?php echo $oOrder->ordFirstName;?>&nbsp;</td>
  </tr>

  <tr>
    <td align="right"><?php echo L_dynsb_Street;?>:</td>
    <td style="font-weight:bold;"><?php echo $oOrder->ordStreet;?>&nbsp;</td>
  </tr>

<?php if (!empty($oOrder->ordStreet2)) { ?>
  <tr>
    <td align="right"><?php echo L_dynsb_Addition;?>:</td>
    <td><?php echo $oOrder->ordStreet2;?>&nbsp;</td>
  </tr>
<?php } ?>
  <tr>
    <td align="right"><?php echo L_dynsb_Zipcode;?> / <?php echo L_dynsb_City;?>:</td>
    <td style="font-weight:bold;"><?php echo $oOrder->ordZipCode;?> <?php echo $oOrder->ordCity;?>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Country;?>:</td>
    <td><?php echo $oOrder->ordCountry;?>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_Phone;?>:</td>
    <td><?php echo $oOrder->ordPhone;?>&nbsp;</td>
  </tr>
</table>


<!--Spacer-Element with status message-->
<div style="padding:8px;">
   <?php echo $updateMessage;?>
</div>


<!--margin:0px deletes space after </form>-tag -->
<form name="formSubmitDhlData" action=""<?php echo $SERVER['PHP_SELF'];?>" method="post">

<table>
  <tr>
    <td align="right" style="width:160px;"><?php echo L_dynsb_DHLtrackingNumber; ?>:</td>
    <td>
     <input type="text" name="ordSendCode" value="<?php echo $oOrder->ordSendCode;?>"/>&nbsp;
     <input type="submit" class="button" name="buttonSubmitDhlData" value="<?php echo L_dynsb_DHLbuton;?>"/>
   </td>
  </tr>
  <tr>
  <td>&nbsp;</td>
		<td>
<?php
  //if (trim($oOrder->ordSendCode)!="")
  //{
   echo "<input type=\"button\" class=\"button large\" onclick=\"javascript:popUpWindow('$dhlLink','dhlPopUp', '505', '600');\" value=\"".L_dynsb_DHLtest."\">";
  //}
?>
    </td>
  </tr>
</table>
</form>

<div class="footer">
 <input type="button" class="button" onclick="javascript:self.location.href='./mod.carrier.tracking.php'" value="<?php echo L_dynsb_Cancel;?>">
</div>

</div>
<?php
require_once("../include/page.footer.php");
?>
</body>
</html>


