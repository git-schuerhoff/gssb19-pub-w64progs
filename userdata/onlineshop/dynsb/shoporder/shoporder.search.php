<?php
/******************************************************************************/
/* File: shoporder.search.php                                                  */
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
if(isset($_REQUEST['backstart']))
{ $backstart = $_REQUEST['backstart']; }

$chgApplicId = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1)); // script name

if (!isset($_POST['del_stat']) || strlen(trim($_POST['del_stat'])) == 0)
{ $ds = 0; }
else
{ $ds = $_POST['del_stat']; }

if($ds == "1")
{
  if(!isset($_POST['pk']) || !is_array($_POST['pk']))
  {
		$errInput++;
	}
  else
  {
		$pkDataListAry = $_POST['pk'];
		$pkDataListLenAry = sizeof($pkDataListAry);
		if($pkDataListLenAry >= 1)
    {
			for ($x=0; $x < $pkDataListLenAry; $x++)
      {
				$pkDataListAry[$x] = addslashes(strip_tags($pkDataListAry[$x]));
			}
			$pkDataListStr = implode(",", $pkDataListAry);
		}
    else if ($pkDataListLenAry == 1)
    {
			$pkDataListStr = addslashes(strip_tags($_POST['pk']));
		}
		unset ($_POST['pk']);
	}
  $pka = explode(",", $pkDataListStr);
  foreach($pka as $value)
  {
    $SQLo = "DELETE FROM ".DBToken."order WHERE ordIdNo = '".$value."'";
    $SQLp = "DELETE FROM ".DBToken."orderpos WHERE ordpOrdIdNo = '".$value."'";
    @mysqli_query($link, $SQLo);
    @mysqli_query($link, $SQLp);
  }
}

if (!isset($_SESSION['SESS_userIdNo']) || strlen(trim($_SESSION['SESS_userIdNo'])) == 0)
{ die ("<br />error: missing session parameter!<br />"); }
else
{ $SESS_userIdNo = $_SESSION['SESS_userIdNo']; }

if (!isset($_SESSION['SESS_userLogin']) || strlen(trim($_SESSION['SESS_userLogin'])) == 0)
{
  die ("<br />error: missing session parameter!<br />"); }
else
{ $SESS_userId = $_SESSION['SESS_userId']; }

if (!isset($_SESSION['SESS_languageIdNo']) || strlen(trim($_SESSION['SESS_languageIdNo'])) == 0)
{ die ("<br />error: missing session parameter!<br />"); }
else
{ $SESS_languageIdNo = $_SESSION['SESS_languageIdNo']; }

$strSQLSortedBy = "";
$sortNo = 0;
if (isset($_GET['sort']) && strlen(trim($_GET['sort'])) > 0)
{
  $sortNo = abs((int) $_GET['sort']);
	unset ($_GET['sort']);
}
$strSQLSortedBy = "ORDER BY ".$md_inputFields[$sortNo];

//-------------------------------------------------------------------Date---------------
$SQLDate = "";
if (!isset($_POST['s_Date']) || strlen(trim($_POST['s_Date'])) == 0)
{ $SQLDate = ""; }
else
{
  $tmpDateGer = addslashes(strip_tags($_POST['s_Date']));
  $tmpDate = explode(".",$tmpDateGer);
  $tmpDate = $tmpDate[2].$tmpDate[1].$tmpDate[0]."000000";
  $SQLDate = " AND ordDate >= '".$tmpDate."'";
}

//-------------------------------------------------------------------Firm---------------
$SQLFirm = "";
if (!isset($_POST['s_Firm']) || strlen(trim($_POST['s_Firm'])) == 0)
{ $SQLFirm = ""; }
else
{
  $tmpFirm = addslashes(strip_tags($_POST['s_Firm']));
  $SQLFirm = " AND ordFirmname LIKE  '".$tmpFirm."%'";
}

//-------------------------------------------------------------------PLZ---------------
$SQLPLZ= "";
if (!isset($_POST['s_PLZ']) || strlen(trim($_POST['s_PLZ'])) == 0)
{ $SQLPLZ = ""; }
else
{
  $tmpPLZ = addslashes(strip_tags($_POST['s_PLZ']));
  $SQLPLZ = " AND ordZipCode LIKE '".$tmpPLZ."%'";
}

//-------------------------------------------------------------------EMail---------------
$SQLEMail = "";
if (!isset($_POST['s_EMail']) || strlen(trim($_POST['s_EMail'])) == 0)
{ $SQLEMail = ""; }
else
{
  $tmpEMail = addslashes(strip_tags($_POST['s_EMail']));
  $SQLEMail = " AND ordEMail          LIKE '".$tmpEMail."%'";
}

//-------------------------------------------------------------------Street--------------
$SQLStreet = "";
if (!isset($_POST['s_Street']) || strlen(trim($_POST['s_Street'])) == 0)
{ $SQLStreet = ""; }
else
{
  $tmpStreet = addslashes(strip_tags($_POST['s_Street']));
  $SQLStreet = " AND ordStreet          LIKE '".$tmpStreet."%'";
}

//-------------------------------------------------------------------LastName---------------
$SQLLastName = "";
if (!isset($_POST['s_LastName']) || strlen(trim($_POST['s_LastName'])) == 0)
{ $SQLLastName = ""; }
else
{
  $tmpLastName = addslashes(strip_tags($_POST['s_LastName']));
  $SQLLastName = " AND ordLastName     LIKE '".$tmpLastName."%'";
}

//------------------------------------------------------- End getting parameters -------------------

$resultID = @mysqli_query($link, "SELECT COUNT(ordIdNo) AS anzahl FROM ".DBToken."order
                          WHERE 1 = 1 ".$SQLDate."
                                      ".$SQLFirm."
                                      ".$SQLPLZ."
                                      ".$SQLEMail."
                                      ".$SQLStreet."
                                      ".$SQLLastName."
                          AND ordChgHistoryFlg <> '0'");
//A TS 14.11.2014: mysql_result ist deprecated und in MySQLi nicht enthalten,
//verwende alternativen Code stattdessen
//$total    = @mysq_l_result($resultID,0);
$rs = mysqli_fetch_assoc($resultID);
$total = $rs['anzahl'];
//E TS 14.11.2014
if($total == '')
{ $total = 0; }

$start = (isset($_REQUEST['start'])) ? abs((int)$_REQUEST['start']) : 0;
$limit = getentity(DBToken."settings","setRowCount","setIdNo = '1'");     // number of records per page

if(abs($total) == 0)
{ $start = 0; }
else
{ $start    = ($start >= $total) ? $total - $limit : $start; }

if($start < 0)
{ $start = 0; }

$strcal = "de";
if($SESS_languageIdNo == 2)
{ $strcal = "en"; }

chdir("../../");
include_once('inc/class.shopengine.php');
$se = new gs_shopengine();
$modInvoiceOK = (($se->demo == 1) || ($se->checkKeyValid('InvoiceModule')));
chdir("dynsb/shoporder/");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
  <title><?php echo L_dynsb_ShopOrder;?></title>
  <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
  <meta content="de" http-equiv="Language">
  <meta name="author" content="GS Software Solutions GmbH">
  <link rel="stylesheet" type="text/css" href="../css/link.css">
  <link rel="stylesheet" type="text/css" media="all" href="../css/calendar.css" title="dynsb" >
  <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
  <script type="text/javascript" src="../js/gslib.php?lang=<?php echo $SESS_languageIdNo;?>"></script>
	<script type="text/javascript" src="../js/calendar.js"></script>
	<script type="text/javascript" src="../js/calendar-<?php echo $strcal;?>.js"></script>
	<script type="text/javascript" src="../js/calendar-setup.js"></script>
	<script language="JavaScript" type="text/javascript">
  function MM_reloadPage(init)  //reloads the window if Nav4 resized
  {
    if (init==true) with (navigator)
    {
      if ((appName=="Netscape")&&(parseInt(appVersion)==4))
      {
        document.MM_pgW=innerWidth;
        document.MM_pgH=innerHeight;
        onresize=MM_reloadPage;
      }
    }
    else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH)
    {
      location.reload();
    }
  }
  //----------------------------------------------------------------------------
  MM_reloadPage(true);
  //----------------------------------------------------------------------------
  function navigation(val)
  {
    document.frmShoporder.start.value = val;
    document.frmShoporder.submit();
  }
  //----------------------------------------------------------------------------
  function preReset()
  {
    document.frmShoporder.start.value = 0;
    resetSearch('frmShoporder', 's_', true);
  }
  //----------------------------------------------------------------------------
  function startDelete(frm, val)
  {
    document.forms[frm].start.value = val;
    document.forms[frm].del_stat.value = "1";
    deleteIfAnyIsSelected(frm);
  }
  //----------------------------------------------------------------------------
  function deleteIfAnyIsSelected(frm)
  {
    var sFormName = frm;
    if(isDataSelected(sFormName)==true)
    {
      var bCheck = confirm("<?php echo L_dynsb_ReallyDelete;?>?");
      if(bCheck==true) document.forms[sFormName].submit();
    }
    else
    {
      alert("<?php echo L_dynsb_NoDataSelectedDelete;?>.");
    }
  }
  //----------------------------------------------------------------------------
  function sendInvoice(src,opts)
  {
     var bCheck = confirm("<?php echo L_dynsb_SureWantInvSend;?>"); 
     if(bCheck==true)
     {
        document.location.href = src+'?'+opts;
     }
  }
  //----------------------------------------------------------------------------
  function singleDelete(frm, val, pk)
  {
    for(var x = 0; x < document.forms[frm].elements.length; x++)
    {
      var y = document.forms[frm].elements[x];
      if(y.type == 'checkbox' && y.name != 'alldata')
      {
        if(document.forms[frm].elements[x].value == pk)
        {
          document.forms[frm].elements[x].checked = true;
        }
      }
    }
    document.forms[frm].start.value = val;
    document.forms[frm].del_stat.value = "1";
    var bCheck = confirm("<?php echo L_dynsb_SureWantDelete;?>");
    if(bCheck==true)
    {
      document.forms[frm].submit();
    }
    else
    {
      for(var x = 0; x < document.forms[frm].elements.length; x++)
      {
        var y = document.forms[frm].elements[x];
        if(y.type == 'checkbox' && y.name != 'alldata')
        {
          if(document.forms[frm].elements[x].value == pk)
          {
            document.forms[frm].elements[x].checked = false;
          }
        }
      }
      checkAllData(frm);
    }
  }
  </script>
</head>


<body>
<form name="frmShoporder" action="shoporder.search.php" method="post">

<?php
require_once("../include/page.header.php");
?>

<div id="PGshopordersearch">
	<input type="hidden" name="lang" value="<?php echo $lang; ?>">
	<input type="hidden" name="start" value="<?php echo $start; ?>">
	<input type="hidden" name="backstart" value="<?php echo $backstart; ?>">
	<input type="hidden" name="next" value="">
	<input type="hidden" name="nav" value="">
	<input type="hidden" name="del_stat" value="0">

<h1>&#187;&nbsp;<?php echo L_dynsb_ShopOrder;?>&nbsp;&#171;</h1>

<h2>Filter</h2>

<div style="height:1%;"> <!-- height-> hack for ie6: peekaboo bug-->
	<div class="filter">
    <?php echo L_dynsb_Date;?> >=
    <br />
    <input type="text" maxlength="16" value="<?php echo $tmpDateGer; ?>" name="s_Date" id="s_Date" readonly>
    <img src="../image/calendar.gif" id="s_DateTrigger" style="cursor: pointer;" title="<?php echo L_dynsb_Calendar;?>" alt="<?php echo L_dynsb_Calendar;?>">

		<script language="JavaScript" type="text/javascript">
		  Calendar.setup({
		          inputField	: "s_Date",
		          ifFormat    : "%d.%m.%Y",
		          button      : "s_DateTrigger",
		          showsTime	  : false,
		          singleClick	: true,
		          align       : "Bl"  });
		</script>
	</div>

	<div class="filter">
    <?php echo L_dynsb_Firm;?><br />
    <input type="text" maxlength="32" value="<?php echo  $tmpFirm; ?>"  name="s_Firm">
	</div>

	<div class="filter">
    <?php echo L_dynsb_Lastname;?><br />
    <input type="text"  maxlength="20" value="<?php echo  $tmpLastName; ?>" name="s_LastName">
  </div>

	<div class="filter">
    <?php echo L_dynsb_Email;?><br />
    <input type="text" maxlength="16" value="<?php echo  $tmpEMail; ?>" name="s_EMail">
  </div>

  <div class="filter">
    <?php echo L_dynsb_Street;?><br />
    <input type="text" maxlength="16" value="<?php echo  $tmpStreet; ?>" name="s_Street">
  </div>

	<div class="filter">
    <?php echo L_dynsb_Zipcode;?><br />
    <input type="text"  maxlength="10" value="<?php echo  $tmpPLZ; ?>"  name="s_PLZ" >
 </div>
</div>

<p class="clear">
	<input type="button" class="button" onclick="javascript:navigation(<?php echo $start;?>);" name="btn_startSearch" value="<?php echo L_dynsb_StartSearch;?>">
	<input type="button" class="button" onclick="javascript:preReset();" name="btn_resetSearch" value="<?php echo L_dynsb_Reset;?>">
</p>

<h2><?php echo L_dynsb_Searchresult;?></h2>

<?php
  $qrySQL = "SELECT * FROM ".DBToken."order
             WHERE 1 = 1 ".$SQLDate."
                         ".$SQLFirm."
                         ".$SQLPLZ."
                         ".$SQLEMail."
                         ".$SQLStreet."
                         ".$SQLLastName."
             AND ordChgHistoryFlg <> '0'
             ORDER BY ordDate DESC, ordFirmname ASC, ordLastName ASC LIMIT ".$start.",".$limit;

  $qry = @mysqli_query($link,$qrySQL);
  ?>

<table class="searchresult">
	<tr>
		<th>&nbsp;</th>
		<th><?php echo L_dynsb_OrderNo?></th>
		<th><?php echo L_dynsb_Date;?></th>
        <th>Rechnungsnummer</th>
        <th>Rechnungsdatum</th>
		<th><?php echo L_dynsb_Firm;?></th>
		<th><?php echo L_dynsb_Lastname;?></th>
		<th><?php echo L_dynsb_Email;?></th>
		<th><?php echo L_dynsb_Street;?></th>
		<th><?php echo L_dynsb_Zipcode;?></th>
        <th><?php echo L_dynsb_SendDate;?></th>
	</tr>

<?php
	$x = 0;
  while ($obj = @mysqli_fetch_object($qry))
  {
  	$x++;
  	if ($x % 2 != 0)
			$rowStyle = " odd ";
		else
			$rowStyle = " even ";

    if(trim($obj->ordFirmname) == "") {
      $cusFirmname = "&nbsp;";
    }
    else {
      $cusFirmname = trim($obj->ordFirmname);
    }
?>
  <tr id="d<?php echo $obj->ordIdNo;?>" class="<?php echo $rowStyle;?>"  ondblclick="javascript:getElementById('chk<?php echo $obj->ordIdNo?>').click();">
    <td>
      <input id='chk<?php echo $obj->ordIdNo;?>' type="checkbox" class="checkbox" style="cursor:pointer" name="pk[]" value="<?php echo $obj->ordIdNo;?>" onclick="javascript:checkAllData('frmShoporder');">
      <a href="shoporder.detail.php?<?php echo "pk=".$obj->ordIdNo."&amp;act=e&amp;start=".$start."&amp;lang=".$lang; ?>" title="<?php echo L_dynsb_showData;?>">
        <img src="../image/edit.gif" alt="<?php echo L_dynsb_showData;?>">
      </a>
      <!--A TS 24.03.2017: E-Mail-Versand der Rechnung-->
      <!--a href="invoice.php?<?php //echo "pk=".$obj->ordIdNo."&amp;lang=".$lang; ?>&d=2" title="<?php //echo L_dynsb_MailInvoice;?>"-->
      <?php
      if($modInvoiceOK) {
         ?>
         <a href="invoice.php?<?php echo "pk=".$obj->ordIdNo."&amp;lang=".$lang; ?>" title="<?php echo L_dynsb_PrintInvoice;?>" target="_blank">
            <img src="../image/print.gif" alt="<?php echo L_dynsb_PrintInvoice;?>">
         </a>
         <a href="javascript:sendInvoice('invoice.php','<?php echo "pk=".$obj->ordIdNo."&amp;lang=".$lang; ?>&d=2')" title="<?php echo L_dynsb_MailInvoice;?>">
            <img src="../image/mail.gif" alt="<?php echo L_dynsb_MailInvoice;?>">  
         </a>
         <?php
      }
      ?>
      <!--E TS 24.03.2017: E-Mail-Versand der Rechnung-->
      <a href="javascript:singleDelete('frmShoporder',<?php echo $start.",".$obj->ordIdNo;?>);" title="<?php echo L_dynsb_DeleteData;?>">
        <img src="../image/del.gif" alt="<?php echo L_dynsb_DeleteData;?>">
      </a>
    </td>
    <td><?php echo $obj->ordId;?></td>
    <td align="center"><?php echo timestamp_mysql2german($obj->ordDate);?></td>
    <td><?php echo $obj->ordInvoiceNumber;?></td>
    <td><?php if($obj->ordInvoiceDate != '0000-00-00'){echo date_mysql2german($obj->ordInvoiceDate);}?></td>
    <td><?php echo $cusFirmname;?></td>
    <td><?php echo $obj->ordLastName;?></td>
    <td><?php echo $obj->ordEMail;?></td>
    <td><?php echo $obj->ordStreet; ?></td>
    <td><?php echo $obj->ordZipCode;?></td>
    <td><?php if($obj->ordInvSendDate != '0000-00-00 00:00:00'){echo $obj->ordInvSendDate;}?></td>
  </tr>
<?php
  } // end of while
?>
</table>

<h2>&nbsp;</h2>
<?php
$strDatasets = L_dynsb_Rows;
$strOf = L_dynsb_Of;

// display records intervall
$strTmp = $strDatasets." "; // Datens&auml;tze
if ($total < 1) $strTmp = $strTmp.strval(0);
 else $strTmp = $strTmp.strval($start + 1);
$strTmp = $strTmp."-";
if ($start + $limit > $total)
  $strTmp = $strTmp.strval($total);
else
 $strTmp = $strTmp.strval($start + $limit);
$strTmp = $strTmp." ".$strOf." ".strval($total); // .. von ...

?>


<!-- navigation // -->
<table>
  <tr>
    <td>
			<input type="checkbox" class="checkbox" name="alldata" value="alldata" onClick="selectAllData('frmShoporder');">&nbsp;<?php echo L_dynsb_All;?>
    </td>
    <td><?php echo $strTmp ?>
<?php
	if ($start > 0) {
	 $newStartPrev = ($start - $limit < 0) ? 0 : ($start-$limit);
	 $btnStatus = "";
  } else {
    $btnStatus = " disabled ";
  }
?>
		<input type="button" class="button small<?php echo $btnStatus;?>" onclick="javascript:navigation('0');" name="btn_next" value="|<--" <?php echo $btnStatus;?>>
		<input type="button" class="button small<?php echo $btnStatus;?>" onclick="javascript:navigation(<?php echo $newStartPrev;?>);" name="btn_end" value="<--" <?php echo $btnStatus;?>>
<?php

	if ($start + $limit < $total) {
	 $newStartNext = $start + $limit;
	 $newStartLast = (truncate($total/$limit) * $limit);
	 $btnStatus = "";
  } else {
   $btnStatus = " disabled ";
  }
?>
		<input type="button" class="button small<?php echo $btnStatus;?>" onclick="javascript:navigation(<?php echo $newStartNext;?>);" name="btn_next" value="-->" <?php echo $btnStatus;?>>
		<input type="button" class="button small<?php echo $btnStatus;?>" onclick="javascript:navigation(<?php echo $newStartLast;?>);" name="btn_end" value="-->|" <?php echo $btnStatus;?>>

		</td>
	</tr>
<?php
	if ($total > 0) {
?>
   <tr>
    <td><img src="../image/arrow.gif" width="35" height="15" alt=""> </td>
    <td><input type="button" class="button" onclick="javascript:startDelete('frmShoporder',<?php echo $start;?>);" name="btn_del" value="<?php echo L_dynsb_Delete;?>"></td>
  </tr>
<?php
	}
?>
 </table>

</div>

<?php
require_once("../include/page.footer.php");
?>
</form>
</body>
</html>
