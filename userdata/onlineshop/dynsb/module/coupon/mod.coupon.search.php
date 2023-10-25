<?php
/******************************************************************************/
/* File: mod.coupon.search.php                                                */
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
if(isset($_REQUEST['backstart']))
{ $backstart = $_REQUEST['backstart']; }

$chgApplicId = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1)); // script name

if (!isset($_POST['del_stat']) || strlen(trim($_POST['del_stat'])) == 0)
{ $ds = 0; }
else
{ $ds = $_POST['del_stat']; }

if($ds == "1")
{
  // possible multiple delete ==> parameter is an array
  if(!isset($_POST['pk']))
  {
		//die ("<br />delete error: missing pk[] parameter for this action!<br />");
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

  // deactivate data
  $pka = explode(",", $pkDataListStr);
  foreach($pka as $value)
  {
    $SQL = "DELETE from ".DBToken."coupon WHERE coupId = '".$value."'";
    @mysqli_query($link,$SQL);
  }
}

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

//----------------------------------------------------------------------------------
// optional
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
  $SQLDate = " AND coupCreatedate >= '".$tmpDate."'";
}

//-------------------------------------------------------------------Status---------------
$SQLStatus = "";
if(!isset($_POST['coupStatus']) || strlen(trim($_POST['coupStatus'])) == 0)
{ $SQLStatus = ""; }
else
{
  if($_POST['coupStatus']==0)
  { $SQLStatus = ""; }

  if($_POST['coupStatus']==1)
  { $SQLStatus = " AND coupUsed='1' AND coupAssigned='1'"; }

  if($_POST['coupStatus']==2)
  { $SQLStatus = " AND coupAssigned='1'"; }

  if($_POST['coupStatus']==3)
  { $SQLStatus = " AND coupUsed='0' AND coupAssigned='0'"; }
}

//------------------------------------------------------- End getting parameters -------------------
if($_REQUEST['assign']!=0)
{
  $sqlassign = "UPDATE ".DBToken."coupon set coupAssigned = '1' , coupAssignedDate = '".date('YmdHis')."' where coupId = '".$_REQUEST['assign']."'";
  @mysqli_query($link,$sqlassign);
}

//------------------------------------------------------------------------------
// count number of total records

$sql = "SELECT COUNT(coupId) AS anzahl FROM ".DBToken."coupon WHERE 1 = 1 ".$SQLDate.$SQLStatus;
$resultID = @mysqli_query($link,$sql);

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

// check parameter $start (maybe corrupt parameter in url)
if(abs($total) == 0)
{ $start = 0; }
else
{ $start    = ($start >= $total) ? $total - $limit : $start; }

if($start < 0)
{ $start = 0; }

$strcal = "de";
if($SESS_languageIdNo == 2)
{ $strcal = "en"; }

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
  <title><?php echo L_dynsb_Coupons;?></title>
  <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
  <meta content="de" http-equiv="Language">
  <meta name="author" content="GS Software Solutions GmbH">
  <link rel="stylesheet" type="text/css" href="../../css/link.css">
  <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb" >
  <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
  <script type="text/javascript" src="../../js/gslib.php"></script>
	<script type="text/javascript" src="../../js/calendar.js"></script>
	<script type="text/javascript" src="../../js/calendar-<?php echo $strcal;?>.js"></script>
	<script type="text/javascript" src="../../js/calendar-setup.js"></script>
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
    { location.reload(); }
  }
  MM_reloadPage(true);

  //----------------------------------------------------------------------------
  function navigation(val)
  {
    document.frmCoup.start.value = val;
    document.frmCoup.submit();
  }
  //----------------------------------------------------------------------------
  function preReset()
  {
    document.frmCoup.start.value = 0;
    resetSearch('frmCoup', 's_', true);
  }
  //----------------------------------------------------------------------------
  function assignCoupn(coupId)
  {
    document.frmCoup.assign.value = coupId;
    document.frmCoup.submit();
  }
  //----------------------------------------------------------------------------
  function printPDF(coupID)
  {
  	var x = 0;
  	var y = 0;
  	var winBreite = 640;
  	var winHoehe = 460;
  	x = Math.round((screen.width-winBreite)/2);
  	y = Math.round((screen.height-winHoehe)/2);

  	var dis = window.open('mod.coupon.pdf.php?coupId='+coupID+'&lang=<?echo $lang;?>','my','left='+x+',top='+y+',width='+winBreite+',height='+winHoehe+',scrollbars=no,resizable');
  	dis.focus();
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
    } else  {
        alert("<?php echo L_dynsb_NoDataSelectedDelete;?>");
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
<form name="frmCoup" action="mod.coupon.search.php" method="post">
<?php
require_once("../../include/page.header.php");
?>
<div id="PGcouponsearch">

	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="start" value="<?php echo $start;?>">
	<input type="hidden" name="backstart" value="<?php echo $backstart;?>">
	<input type="hidden" name="next" value="">
	<input type="hidden" name="nav" value="">
	<input type="hidden" name="assign" value="0">
	<input type="hidden" name="del_stat" value="0">

<h1>&#187;&nbsp;<?php echo L_dynsb_Coupons;?>&nbsp;&#171;</h1>
<h2><?php echo L_dynsb_Filter;?></h2>

<p>
	<?php echo L_dynsb_Date;?>:
  <input type="text" maxlength="16" value="<?php echo $tmpDateGer; ?>" name="s_Date" id="s_Date" readonly>
	<img src="../../image/calendar.gif" id="s_DateTrigger" style="cursor: pointer" alt="Kalender" title="Kalender">
  <script language="JavaScript" type="text/javascript">
    Calendar.setup({
            inputField	: "s_Date",
            ifFormat    : "%d.%m.%Y",
            button      : "s_DateTrigger",
            showsTime	  : false,
            singleClick	: true,
            firstDay	  :	1,
            align       : "Bl" });
  </script>

	&nbsp;&nbsp;
  <?php echo L_dynsb_Status;?>:
  <select name="coupStatus">
    <option value='0'><?echo L_dynsb_All;?></option>
    <option value='1'><?echo L_dynsb_Cashed;?></option>
    <option value='2'><?echo L_dynsb_Assigned;?></option>
    <option value='3'><?echo L_dynsb_Free;?></option>
  </select>
</p>

<p>
	<input type="button" class="button" onclick="javascript:navigation(<?php echo $start;?>);" name="btn_startSearch" value="<?php echo L_dynsb_StartSearch;?>">
	<input type="button" class="button" onclick="javascript:preReset();" name="btn_resetSearch" value="<?php echo L_dynsb_Reset;?>">
</p>



<h2><?php echo L_dynsb_Searchresult;?></h2>
<table class="searchresult">
  <tr>
    <th>&nbsp;</th>
    <th><?php echo L_dynsb_CreatedOn;?></th>
    <th><?php echo L_dynsb_Value;?></th>
    <th><?php echo L_dynsb_Code;?></th>
    <th><?php echo L_dynsb_Cashed?></th>
    <th><?php echo L_dynsb_Assigned?></th>
    <th><?php echo L_dynsb_CashedOn;?></th>
    <th><?php echo L_dynsb_AssignedOn;?></th>
    <th><?php echo L_dynsb_Valid?></th>
  </tr>
<?php
  // start database query
  $qrySQL = "SELECT * FROM ".DBToken."coupon
             WHERE 1 = 1 ".$SQLDate."
                         ".$SQLStatus."
             ORDER BY coupCreatedate DESC LIMIT ".$start.",".$limit;
  $qry = @mysqli_query($link,$qrySQL);

  $x = 0;
  while ($obj = @mysqli_fetch_object($qry))
  {
  	$x++;

  	if ($x % 2 != 0)
			$rowStyle = " odd ";
		else
			$rowStyle = " even ";

    if($obj->coupUsed==1) {
    	$coupUsed = "x"; }
    else {
    	$coupUsed = "-"; }

    if($obj->coupAssigned==1){
    	$coupAssigned = "x"; }
    else {
    	$coupAssigned = "-"; }

    if($obj->coupUseddate!=""){
    	$coupUseddate = timestamp_mysql2german($obj->coupUseddate); }
    else {
    	$coupUseddate = "&nbsp;"; }

    if($obj->coupAssignedDate!="") {
    	$coupAssigneddate = timestamp_mysql2german($obj->coupAssignedDate); }
    else {
    	$coupAssigneddate = "&nbsp;"; }
?>
    <tr id="d<?php echo $obj->coupId;?>" class="<?php echo $rowStyle;?>" ondblclick="javascript:getElementById('chk<?php echo $obj->coupId?>').click();">
      <td>
				<input id='chk<?php echo $obj->coupId;?>' type="checkbox" class="checkbox" name="pk[]" value="<?php echo $obj->coupId;?>" onclick="javascript:checkAllData('frmCoup');">

        <a href="javascript:singleDelete('frmCoup',<?php echo $start.",".$obj->coupId;?>);"><img src="../../image/del.gif" alt="<?php echo L_dynsb_DeleteCoupon;?>"></a>
        <img src="../../image/pdf.gif" onclick="printPDF(<?php echo $obj->coupId;?>)" style="cursor:pointer" alt="<?php echo L_dynsb_CreatePDF;?>">
<?php
	      if($obj->coupAssigned!=1)
	        echo "<a href=\"javascript:assignCoupn(".$obj->coupId.");\" style=\"cursor:pointer\"><img src=\"../../image/assign.gif\" style=\"cursor:pointer\" alt=\"".L_dynsb_AssignCoupon."\"></a>";
	      else
	        echo "<img src=\"../../image/blind.gif\" width=\"13\" height=\"13\" alt=\"".L_dynsb_AssignCoupon."\">";
?>
      </td>
      <td><?php echo timestamp_mysql2german($obj->coupCreatedate);?></td>
      <td><?php echo number_format($obj->coupPrice,2,",",".")." ".$obj->coupCurrency;?></td>
      <td><?php echo $obj->coupCode;?></td>
      <td><?php echo $coupUsed;?></td>
      <td><?php echo $coupAssigned;?></td>
      <td><?php echo $coupUseddate;?></td>
      <td><?php echo $coupAssigneddate;?></td>
      <?php
      if($obj->coupValid=="once") {
        $coupValid = L_dynsb_Once;
      }
      else if($obj->coupValid=="unlimited") {
        $coupValid = L_dynsb_Unlimited;
      }
?>
      <td><?php echo $coupValid;?></td>
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
		<input type="checkbox" class="checkbox" name="alldata" value="alldata" onClick="selectAllData('frmCoup');">&nbsp;
		<?php echo L_dynsb_All;?>
  </td>
  <td>
<?php
	if ($start > 0) {
		$cnewStartPrev = ($start - $limit < 0) ? 0 : ($start-$limit);
		$bStatus = "";
	}
  else
		$bStatus = " disabled ";
?>
		<input type="button" class="button small<?php echo $bStatus;?>" onclick="javascript:navigation('0');" name="btn_next" value="|<--"<?php echo $bStatus;?>>
		<input type="button" class="button small<?php echo $bStatus;?>" onclick="javascript:navigation(<?php echo $cnewStartPrev;?>);" name="btn_end" value="<--"<?php echo $bStatus;?>>
<?php
	if ($start + $limit < $total) {
	 $cnewStartNext = $start + $limit;
	 $cnewStartLast = (truncate($total/$limit) * $limit);
	 $bStatus = "";
	}
  else
		$bStatus = " disabled ";
?>
  	<input type="button" class="button small<?php echo $bStatus;?>" onclick="javascript:navigation(<?php echo $cnewStartNext;?>);" name="btn_next" value="-->"<?php echo $bStatus;?>>
  	<input type="button" class="button small<?php echo $bStatus;?>" onclick="javascript:navigation(<?php echo $cnewStartLast;?>);" name="btn_end" value="-->|"<?php echo $bStatus;?>>

<?php echo $strTmp ?>
	</td>
</tr>

	<tr>
		<td><?php if ($total > 0) { ?><img src="../../image/arrow.gif" alt=""><?php } else echo "&nbsp;"; ?></td>
		<td><?php if ($total > 0) { ?><input type="button" class="button" onclick="javascript:startDelete('frmCoup',<?php echo $start;?>);" name="btn_del" value="<?php echo L_dynsb_Delete;?>"><?php } else echo "&nbsp;"; ?></td>
	</tr>
</table>
</div>

<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
