<?php
/******************************************************************************/
/* File: customer.search.php                                                  */
/******************************************************************************/

require("../../include/login.check.inc.php");
require_once("../../include/functions.inc.php");
require("../../../conf/db.const.inc.php");
require_once("../../include/secure.functions.inc.php");

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
if(isset($_REQUEST['backstart']))
{ $backstart = $_REQUEST['backstart']; }

$chgApplicId = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1)); // script name


if (!isset($_SESSION['SESS_userIdNo']) || strlen(trim($_SESSION['SESS_userIdNo'])) == 0)
{ die ("<br />error: missing session parameter!<br />"); }
else
{ $SESS_userIdNo = $_SESSION['SESS_userIdNo']; }

if (!isset($_SESSION['SESS_userLogin']) || strlen(trim($_SESSION['SESS_userLogin'])) == 0)
{ die ("<br />error: missing session parameter!<br />"); }
else
{ $SESS_userId = $_SESSION['SESS_userId']; }

if (!isset($_SESSION['SESS_languageIdNo']) || strlen(trim($_SESSION['SESS_languageIdNo'])) == 0)
{ die ("<br />error: missing session parameter!<br />"); }
else
{ $SESS_languageIdNo = $_SESSION['SESS_languageIdNo']; }


//-------------------------------------------------------------------Filename---------------
$tmpFilename = "";
$SQLFilename = "";
if (!isset($_POST['s_Filename']) || strlen(trim($_POST['s_Filename'])) == 0)
{ $SQLFilename = ""; }
else
{
  $tmpFilename = addslashes(strip_tags($_POST['s_Filename']));
  $SQLFilename = " AND dlcuFilename LIKE '".$tmpFilename."%'";
}

//-------------------------------------------------------------------Itemno---------------
$tmpItemno = "";
$SQLItemno = "";
if (!isset($_POST['s_Itemno']) || strlen(trim($_POST['s_Itemno'])) == 0)
{ $SQLItemno = ""; }
else
{
  $tmpItemno = addslashes(strip_tags($_POST['s_Itemno']));
  $SQLItemno = " AND dlcuItemNumber LIKE  '".$tmpItemno."%'";
}

//-------------------------------------------------------------------Dlcount---------------
$tmpDlcount = "0";
$SQLDlcount= "";
if (!isset($_POST['s_Dlcount']) || strlen(trim($_POST['s_Dlcount'])) == 0)
{ $SQLDlcount = ""; }
else
{
  $tmpDlcount = $_POST['s_Dlcount'];
  $SQLDlcount = " AND dlcuAllowedDownloads = ".$tmpDlcount;
}


$resultID = @mysqli_query($link,"SELECT COUNT(dlcuIdNo) AS anzahl FROM ".DBToken."downloadarticle_customer
                                 WHERE 1 = 1 ".$SQLFilename."
                                             ".$SQLItemno."
                                             ".$SQLDlcount."
                                 AND dlcuCusId = " . $_REQUEST['pk']);
//A TS 14.11.2014: mysql_result ist deprecated und in MySQLi nicht enthalten,
//verwende alternativen Code stattdessen
//$total    = @mysq_l_result($resultID,0);
$rs = mysqli_fetch_assoc($resultID);
$total = $rs['anzahl'];
//E TS 14.11.2014
if($total == '')
{
  $total = 0;
}

$start = (isset($_REQUEST['start'])) ? abs((int)$_REQUEST['start']) : 0;
$limit = getentity(DBToken."settings","setRowCount","setIdNo = '1'");     // number of records per page

// check parameter $start (maybe corrupt parameter in url)
if(abs($total) == 0)
{ $start = 0; }
else
{ $start    = ($start >= $total) ? $total - $limit : $start; }

if($start < 0)
{ $start = 0; }

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_Customers;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
    <script type="text/javascript" src="../../js/gslib.php?lang=<?php echo $SESS_languageIdNo;?>"></script>
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
    //--------------------------------------------------------------------------
    MM_reloadPage(true);
    //--------------------------------------------------------------------------
    function navigation(val)
    {
      document.frmCustomer.start.value = val;
      document.frmCustomer.submit();
    }
    //--------------------------------------------------------------------------
    function preReset()
    {
      document.frmCustomer.start.value = 0;
      resetSearch('frmCustomer', 's_', true);
    }
    //--------------------------------------------------------------------------
    
    //--------------------------------------------------------------------------
    function singleDelete(pk,cid)
    {
      var bCheck = confirm("<?php echo L_dynsb_SureWantDelete;?>");
      if(bCheck==true)
      {
        self.location.href = 'mod.custdownloads.delete.php?pk=' + pk + '&cid=' + cid + '&lang=<?php echo $lang;?>';
      }
    }
    </script>
</head>


<body>
<form name="frmCustomerDld" action="mod.custdownloads.view.php" method="post">

<?php
require_once("../../include/page.header.php");
?>

  <div id="PGcustomersearch">

    <input type="hidden" name="lang" value="<?php echo $lang; ?>">
    <input type="hidden" name="start" value="<?php echo $start; ?>">
    <input type="hidden" name="backstart" value="<?php echo $backstart; ?>">
    <input type="hidden" name="next" value="">
    <input type="hidden" name="nav" value="">
    <input type="hidden" name="del_stat" value="0">
    <input type="hidden" name="pk" value="<?php echo $_REQUEST['pk']; ?>">

  <h1>&#187;&nbsp;<?php echo L_dynsb_Downloads;?>&nbsp;&#171;</h1>
  <h2>Filter</h2>

<div style="height:1%;"> <!-- height-> hack for ie6: peekaboo bug-->
	<div class="filter">
	  <?php echo L_dynsb_DownloadFilename;?>:<br />
	  <input type="text" maxlength="16" value="<?php echo $tmpFilename; ?>" name="s_Filename">
	</div>
	<div class="filter">
  	<?php echo L_dynsb_DownloadItemno;?>:<br />
		<input type="text" maxlength="32" value="<?php echo $tmpItemno; ?>" name="s_Itemno">
	</div>
	<div class="filter">
  	<?php echo L_dynsb_DownloadCount;?>:<br />
    <input type="text" maxlength="20" value="<?php echo $tmpDlcount; ?>" name="s_Dlcount">
	</div>

</div>

<p class="clear">
 <input type="button" class="button" onClick="javascript:navigation(<?php echo $start;?>);" name="btn_startSearch" value="<?php echo L_dynsb_StartSearch;?>">
 <input type="button" class="button" onClick="javascript:preReset();" name="btn_resetSearch" value="<?php echo L_dynsb_Reset;?>">
</p>


<h2>
  <?php echo L_dynsb_Searchresult;?>
</h2>

<?php
      $qrySQL =  "SELECT * FROM ".DBToken."downloadarticle_customer
                                 WHERE 1 = 1 ".$SQLFilename."
                                             ".$SQLItemno."
                                             ".$SQLDlcount."
                                 AND dlcuCusId = " . $_REQUEST['pk'] . "
                                 ORDER BY dlcuCreateTime DESC LIMIT ".$start.",".$limit;
      $qry = @mysqli_query($link,$qrySQL);
?>

  <table class="searchresult">
  	<tr>
  		<th>&nbsp;</th>
  		<th><?php echo L_dynsb_DownloadFilename;?></th>
  		<th><?php echo L_dynsb_DownloadItemno?></th>
  		<th><?php echo L_dynsb_DownloadCount;?></th>
  		<th><?php echo L_dynsb_DownloadTime;?></th>
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
?>

    <tr id='d<?php echo $obj->dlcuIdNo;?>' class="<?php echo $rowStyle;?>" ondblclick="javascript:getElementById('chk<?php echo $obj->dlcuIdNo?>').click();">
      <td>
        <a href="mod.custdownloads.detail.php?<?php echo "pk=".$obj->dlcuIdNo; ?>&lang=<?php echo $lang;?>&cid=<?php echo $_REQUEST['pk'];?>">
          <img src="../../image/edit.gif" alt="<?php echo L_dynsb_EditData;?>">
        </a>
        <a href="javascript:singleDelete(<?php echo "pk=".$obj->dlcuIdNo; ?>,<?php echo $_REQUEST['pk'];?>);">
          <img src="../../image/del.gif" alt="<?php echo L_dynsb_DeleteData;?>">
        </a>
      </td>
      <td><?php echo $obj->dlcuFilename;?></td>
      <td><?php echo $obj->dlcuItemNumber;?></td>
      <td><?php echo $obj->dlcuAllowedDownloads;?></td>
      <td><?php echo getDateFromString($obj->dlcuCreateTime);?></td>
     </tr>
<?php
	} // end of while
?>
</table>

<?php
$strDatasets = L_dynsb_Rows;
$strOf = L_dynsb_Of;

// display records intervall
$strTmp = $strDatasets." "; // Datens&auml;tze
if ($total < 1)
{ $strTmp = $strTmp.strval(0); }
else
{ $strTmp = $strTmp.strval($start + 1); }

$strTmp = $strTmp."-";

if ($start + $limit > $total)
{ $strTmp = $strTmp.strval($total); }
else
{ $strTmp = $strTmp.strval($start + $limit); }

$strTmp = $strTmp." ".$strOf." ".strval($total); // .. von ...

?>

<h2>&nbsp;</h2>
<!-- navigation // -->
<table>
<tr>
  <td>
    &nbsp;
  </td>
  <td>
  <?php echo $strTmp ?>&nbsp;&nbsp;
<?php
  if ($start > 0) {
    $newStartPrev = ($start - $limit < 0) ? 0 : ($start-$limit);
    $btnStatus = "";
  } else {
    $btnStatus = " disabled ";
  }
?>
   <input type="button" class="button<?php echo $btnStatus;?> small"  value="|<--" onClick="javascript:navigation('0');" <?php echo $btnStatus;?>>
   <input type="button" class="button<?php echo $btnStatus;?> small"  value="<--" onClick="javascript:navigation('<?php echo $newStartPrev;?>');" <?php echo $btnStatus;?>>

<?php
    if ($start + $limit < $total) {
      $newStartNext = $start + $limit;
      $newStartLast = (truncate($total/$limit) * $limit);
      $btnStatus = "";
    } else {
      $btnStatus = " disabled ";
    }
?>
    <input type="button" class="button<?php echo $btnStatus;?> small"  value="-->" onClick="javascript:navigation('<?php echo $newStartNext;?>');"<?php echo $btnStatus;?>>
    <input type="button" class="button<?php echo $btnStatus;?> small"  value="-->|" onClick="javascript:navigation('<?php echo $newStartLast;?>');"<?php echo $btnStatus;?>>
  </td>

 </tr>

</table>

<div class="footer">
	&nbsp;
</div>

</div>

<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
<?php
function getDateFromString($datstr) {
	global $lang;
	$cFormDat = '';
	if($datstr != '') {
		$year = substr($datstr,0,4);
		$mon = substr($datstr,4,2);
		$day = substr($datstr,6,2);
		$hour = substr($datstr,8,2);
		$min = substr($datstr,10,2);
		$sec = substr($datstr,12,2);
		if($lang == 'deu') {
			$cFormDat = $day . "." . $mon . "." . $year . " " . $hour . ":" . $min . ":" . $sec;
		} else {
			$cFormDat = $year . "-" . $mon . "-"  . $day . " " . $hour . ":" . $min . ":" . $sec;
		}
	}
	return $cFormDat;
}
?>