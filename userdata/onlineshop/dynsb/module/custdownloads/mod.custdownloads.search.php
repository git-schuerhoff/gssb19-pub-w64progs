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

$strSQLSortedBy = "";
$sortNo = 0;
if (isset($_GET['sort']) && strlen(trim($_GET['sort'])) > 0)
{
  $sortNo = abs((int) $_GET['sort']);
	unset ($_GET['sort']);
}
$strSQLSortedBy = "ORDER BY ".$md_inputFields[$sortNo];

//-------------------------------------------------------------------CustomerNo---------------
$SQLCustomerNo = "";
if (!isset($_POST['s_CustomerNo']) || strlen(trim($_POST['s_CustomerNo'])) == 0)
{ $SQLCustomerNo = ""; }
else
{
  $tmpCustomerNo = addslashes(strip_tags($_POST['s_CustomerNo']));
  $SQLCustomerNo = " AND cusId LIKE '".$tmpCustomerNo."%'";
}

//-------------------------------------------------------------------Firm---------------
$SQLFirm = "";
if (!isset($_POST['s_Firm']) || strlen(trim($_POST['s_Firm'])) == 0)
{ $SQLFirm = ""; }
else
{
  $tmpFirm = addslashes(strip_tags($_POST['s_Firm']));
  $SQLFirm = " AND cusFirmname LIKE  '".$tmpFirm."%'";
}

//-------------------------------------------------------------------PLZ---------------
$SQLPLZ= "";
if (!isset($_POST['s_PLZ']) || strlen(trim($_POST['s_PLZ'])) == 0)
{ $SQLPLZ = ""; }
else
{
  $tmpPLZ = addslashes(strip_tags($_POST['s_PLZ']));
  $SQLPLZ = " AND cusZipCode LIKE '".$tmpPLZ."%'";
}

//-------------------------------------------------------------------City---------------
$SQLCity = "";
if (!isset($_POST['s_City']) || strlen(trim($_POST['s_City'])) == 0)
{ $SQLCity = ""; }
else
{
  $tmpCity = addslashes(strip_tags($_POST['s_City']));
  $SQLCity = " AND cusCity          LIKE '".$tmpCity."%'";
}

//-------------------------------------------------------------------Country---------------
$SQLCountry = "";
if (!isset($_POST['s_Country']) || strlen(trim($_POST['s_Country'])) == 0)
{ $SQLCountry = ""; }
else
{
  $tmpCountry = addslashes(strip_tags($_POST['s_Country']));
  $SQLCountry = " AND cusCountry          LIKE '".$tmpCountry."%'";
}

//-------------------------------------------------------------------LastName---------------
$SQLLastName = "";
if (!isset($_POST['s_LastName']) || strlen(trim($_POST['s_LastName'])) == 0)
{ $SQLLastName = ""; }
else
{
  $tmpLastName = addslashes(strip_tags($_POST['s_LastName']));
  $SQLLastName = " AND cusLastName     LIKE '".$tmpLastName."%'";
}

$resultID = @mysqli_query($link,"SELECT COUNT(cusIdNo) AS anzahl FROM ".DBToken."customer
                                 WHERE 1 = 1 ".$SQLCustomerNo."
                                             ".$SQLFirm."
                                             ".$SQLPLZ."
                                             ".$SQLCity."
                                             ".$SQLCountry."
                                             ".$SQLLastName."
                                 AND cusChgHistoryFlg <> '0'");
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
    function startDelete(frm, val)
    {
      document.forms[frm].start.value = val;
      document.forms[frm].del_stat.value = "1";
      deleteIfAnyIsSelected(frm);
    }
    //--------------------------------------------------------------------------
    function deleteIfAnyIsSelected(frm)
    {
      var sFormName = frm;
      if(isDataSelected(sFormName)==true)
      {
        var bCheck = confirm("<?php echo L_dynsb_ReallyDelete;?>");
        if(bCheck==true) document.forms[sFormName].submit();
      }
      else
      {
        alert("<?php echo L_dynsb_NoDataSelectedDelete;?>");
      }
    }
    //--------------------------------------------------------------------------
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
<form name="frmCustomer" action="mod.custdownloads.search.php" method="post">

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

  <h1>&#187;&nbsp;<?php echo L_dynsb_Customers;?>&nbsp;&#171;</h1>
  <h2>Filter</h2>

<div style="height:1%;"> <!-- height-> hack for ie6: peekaboo bug-->
	<div class="filter">
	  <?php echo L_dynsb_CustomerNo;?>:<br />
	  <input type="text" maxlength="16" value="<?php echo $tmpCustomerNo; ?>" name="s_CustomerNo">
	</div>
	<div class="filter">
  	<?php echo L_dynsb_Firm;?>:<br />
		<input type="text" maxlength="32" value="<?php echo $tmpFirm; ?>" name="s_Firm">
	</div>
	<div class="filter">
  	<?php echo L_dynsb_Lastname;?>:<br />
    <input type="text" maxlength="20" value="<?php echo $tmpLastName; ?>" name="s_LastName">
	</div>
	<div class="filter">
		<?php echo L_dynsb_Zipcode;?>:<br />
    <input type="text" maxlength="10" value="<?php echo $tmpPLZ; ?>" name="s_PLZ" >
	</div>

	<div class="filter">
  	<?php echo L_dynsb_City;?>:<br />
    <input type="text" maxlength="16" value="<?php echo $tmpCity; ?>" name="s_City">
	</div>

	<div class="filter">
		<?php echo L_dynsb_Country;?>:<br />
		<input type="text" maxlength="16" value="<?php echo $tmpCountry; ?>" name="s_Country">
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
      $qrySQL = "SELECT * FROM ".DBToken."customer
                 WHERE 1 = 1 ".$SQLCustomerNo."
                             ".$SQLFirm."
                             ".$SQLPLZ."
                             ".$SQLCity."
                             ".$SQLCountry."
                             ".$SQLLastName."
                 AND cusChgHistoryFlg <> '0'
                 ORDER BY cusIdNo DESC LIMIT ".$start.",".$limit;
      $qry = @mysqli_query($link,$qrySQL);
?>

  <table class="searchresult">
  	<tr>
  		<th>&nbsp;</th>
  		<th><?php echo L_dynsb_CustomerNo?></th>
  		<th><?php echo L_dynsb_Firm;?></th>
  		<th><?php echo L_dynsb_Lastname;?></th>
  		<th><?php echo L_dynsb_Zipcode;?></th>
  		<th><?php echo L_dynsb_City;?></th>
  		<th><?php echo L_dynsb_Country;?></th>
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

      if(trim($obj->cusFirmname) == "")
      	$cusFirmname = "&nbsp;";
      else
      	$cusFirmname = trim($obj->cusFirmname);

      if(trim($obj->cusId) == "")
      	$cusId = "&nbsp;";
      else
      	$cusId = trim($obj->cusId);

      if(trim($obj->cusCountry) == "")
      	$cusCountry = "&nbsp;";
      else
      	$cusCountry = trim($obj->cusCountry);
?>

    <tr id='d<?php echo $obj->cusIdNo;?>' class="<?php echo $rowStyle;?>" ondblclick="javascript:getElementById('chk<?php echo $obj->cusIdNo?>').click();">
      <td>
        <a href="mod.custdownloads.view.php?<?php echo "pk=".$obj->cusIdNo."&amp;act=e&amp;start=".$start."&amp;lang=".$lang; ?>">
          <img src="../../image/edit.gif" alt="<?php echo L_dynsb_EditData;?>">
        </a>
        
<?php
        if ($obj->cusBlocked == '1')
        {
?>        
          <img src="../../image/blocked.gif" alt="<?php echo L_dynsb_Blocked;?>">
<?php       
        }
?>        
      </td>
      <td><?php echo $cusId;?></td>
      <td><?php echo $cusFirmname;?></td>
      <td><?php echo $obj->cusLastName;?></td>
      <td><?php echo $obj->cusZipCode;?></td>
      <td><?php echo $obj->cusCity;?></td>
      <td><?php echo $cusCountry;?></td>
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
