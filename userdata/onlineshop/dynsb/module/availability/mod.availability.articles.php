<?php
/******************************************************************************/
/* File: mod.availability.articles.php                                        */
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
if(isset($_REQUEST['act']) && $_REQUEST['act']=="save")
{ 
  for($i=0;$i<sizeof($_REQUEST['status']);$i++)
  {
 
    if(isset($_REQUEST['pk']))
    { 
      if(in_array($_REQUEST['itemnumber'][$i],$_REQUEST['pk']))
      {
        if($_REQUEST['quant'][$i]=="")
        {
          $_REQUEST['quant'][$i] = "NULL";
        }
        else
        {
          $_REQUEST['quant'][$i] = "'".$_REQUEST['quant'][$i]."'";
        }
        //A TS 01.08.2014 Status aus in itemAvailabilityId speichern
        //Dies sollte das eigentliche Feld f� Status sein
        $sql = "UPDATE ".DBToken."itemdata set itemShipmentStatus='".$_REQUEST['status'][$i]."', itemInStockQuantity=".$_REQUEST['quant'][$i].", " .
        			" itemAvailabilityId='" . $_REQUEST['status'][$i] . "'" .
               " WHERE itemItemNumber='".$_REQUEST['itemnumber'][$i]."'";

        @mysqli_query($link,$sql);
        if(mysqli_errno($link) != 0)
        {
        		die(mysqli_error($link) . "<br />" . $sql);
        }
      }
    }
  }
  $_REQUEST['act'] = "";
}

if(isset($_REQUEST['backstart']))
{ $backstart = $_REQUEST['backstart']; }
$chgApplicId = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1)); // script name

if (!isset($_POST['del_stat']) || strlen(trim($_POST['del_stat'])) == 0)
{ $ds = 0; }
else
{ $ds = $_POST['del_stat']; }

if($ds == "1")
{
	if(!isset($_POST['pk']) || strlen(trim($_POST['pk'])) == 0)
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
    $SQL = "DELETE from ".DBToken."itemdata WHERE itemItemNumber = '".$value."'";
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

//-------------------------------------------------------------------Artikelnr.-------
$SQLItemNo = "";
if (!isset($_POST['s_itemnumber']) || strlen(trim($_POST['s_itemnumber'])) == 0)
{ $SQLItemNo = ""; }
else
{
  $tmpItemNo = addslashes(strip_tags($_POST['s_itemnumber']));
  $SQLItemNo = " AND itemItemNumber     LIKE  '%".$tmpItemNo."%'";
}

//-------------------------------------------------------------------Artikelname---------
$SQLItemName = "";
if (!isset($_POST['s_name']) || strlen(trim($_POST['s_name'])) == 0)
{ $SQLItemName = ""; }
else
{
  $tmpItemName = addslashes(strip_tags($_POST['s_name']));
  $SQLItemName = " AND itemItemDescription      LIKE '%".$tmpItemName."%'";
}
//------------------------------------------------------- End getting parameters -------------------


//------------------------------------------------------------------------------
// count number of total records

$resultID = @mysqli_query($link,"SELECT COUNT(itemItemNumber) AS anzahl FROM ".DBToken."itemdata
                          WHERE 1 = 1 ".$SQLItemNo."
                                      ".$SQLItemName);

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
  <title><?php echo L_dynsb_Availability;?></title>
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
  <!--
  //------------------------------------------------------------------------------
  function MM_reloadPage(init)   //reloads the window if Nav4 resized
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

  //-----------------------------------------------------------------------------
  function navigation(val)
  {
    document.frmAvail.start.value = val;
    document.frmAvail.submit();
  }
  //------------------------------------------------------------------------------
  function preReset()
  {
    document.frmAvail.start.value = 0;
    resetSearch('frmAvail', 's_', true);
  }
  //------------------------------------------------------------------------------
function startDelete(frm, val)
    {
      document.forms[frm].start.value = val;
      document.forms[frm].del_stat.value = "1";
      deleteIfAnyIsSelected(frm);
    }

    function deleteIfAnyIsSelected(frm) {
    var sFormName = frm;
    if(isDataSelected(sFormName)==true)  {
        var bCheck = confirm("<?php echo L_dynsb_ReallyDelete;?>");
        if(bCheck==true) document.forms[sFormName].submit();
    } else  {
        alert("<?php echo L_dynsb_NoDataSelectedDelete;?>");
    }
}

function singleDelete(frm, val, pk) {
    for(var x = 0; x < document.forms[frm].elements.length; x++){
        var y = document.forms[frm].elements[x];
        if(y.type == 'checkbox' && y.name != 'alldata') {
            if(document.forms[frm].elements[x].value == pk) {
                document.forms[frm].elements[x].checked = true;
            }
        }
    }
    document.forms[frm].start.value = val;
    document.forms[frm].del_stat.value = "1";
    var bCheck = confirm("<?php echo L_dynsb_SureWantDelete;?>");
    if(bCheck==true) {
        document.forms[frm].submit();
    } else {
        for(var x = 0; x < document.forms[frm].elements.length; x++){
            var y = document.forms[frm].elements[x];
            if(y.type == 'checkbox' && y.name != 'alldata') {
                if(document.forms[frm].elements[x].value == pk) {
                    document.forms[frm].elements[x].checked = false;
                }
            }
        }
        checkAllData(frm);
    }
}
  // -->
  </script>
</head>
<body>
<form name="frmAvail" action="mod.availability.articles.php" method="post">

<?php
require_once("../../include/page.header.php");
?>

<div id="PGavailabilityarticles">
	<input type="hidden" name="lang" value="<?php echo $lang; ?>">
	<input type="hidden" name="start" value="<?php echo $start; ?>">
	<input type="hidden" name="backstart" value="<?php echo $backstart; ?>">
	<input type="hidden" name="next" value="">
	<input type="hidden" name="nav" value="">
	<input type="hidden" name="act" value="save">
	<input type="hidden" name="del_stat" value="0">
<h1>&#187;&nbsp;<?php echo L_dynsb_Availability." - ".L_dynsb_Article;?>&nbsp;&#171;</h1>

<h2><?php echo L_dynsb_Filter;?></h2>

<div style="height:1%;"> <!-- height-> hack for ie6: peekaboo bug-->
	<div class="filter">
	  <?php echo L_dynsb_ArticleNo;?>: <input type="text" maxlength="32" value="<?php echo $tmpItemNo; ?>" name="s_itemnumber">
	</div>
	<div class="filter">
  	<?php echo L_dynsb_ArticleName;?>: <input type="text" maxlength="20" value="<?php echo $tmpItemName; ?>" name="s_name">
	</div>
</div>

<p class="clear">
	<input type="button" class="button" onclick="javascript:navigation(<?php echo $start;?>);" name="btn_startSearch" value="<?php echo L_dynsb_StartSearch;?>">
	<input type="button" class="button" onclick="javascript:preReset();" name="btn_resetSearch" value="<?php echo L_dynsb_Reset;?>">
</p>

<h2><?php echo L_dynsb_Searchresult;?></h2>
<?php
  // start database query
$qrySQL = "SELECT * FROM ".DBToken."itemdata
           WHERE 1 = 1 ".$SQLItemNo."
                       ".$SQLItemName." AND ItemLanguageId = '".$lang."'
           ORDER BY itemItemNumber ASC LIMIT ".$start.",".$limit;
$qry = @mysqli_query($link,$qrySQL);
?>

<table class="searchresult">
<tr>
	<th>&nbsp;</th>
	<th><?php echo L_dynsb_ArticleNo?></th>
	<th><?php echo L_dynsb_Description2?></th>
	<th><?php echo L_dynsb_Status;?></th>
	<th><?php echo L_dynsb_Quantity;?></th>
</tr>
<?php
$x = 0;
while ($obj = @mysqli_fetch_object($qry)) {
	$x++;

	if ($x % 2 != 0)
		$rowStyle = " odd ";
	else
		$rowStyle = " even ";

  $ItemDescription = trim($obj->itemItemDescription);
  if($ItemDescription == "") $ItemDescription = "&nbsp;";
?>

  <tr id="d<?php echo $obj->itemItemNumber;?>" class="<?php echo $rowStyle;?>" ondblclick="javascript:getElementById('chk<?php echo $obj->itemItemNumber;?>').click()">
    <td>
      <input id='chk<?php echo $obj->itemItemNumber;?>' type="checkbox" class="checkbox" name="pk[]" value="<?php echo $obj->itemItemNumber;?>" onclick="javascript:checkAllData('frmAvail');">
      <a href="javascript:singleDelete('frmAvail',<?php echo $start.",".$obj->itemItemNumber;?>);">
        <img src="../../image/del2.gif" alt="<?php echo L_dynsb_DeleteData;?>">
      </a>
    </td>
    <td>
      <?php echo $obj->itemItemNumber;?>
    </td>
    <td>
      <?php echo $ItemDescription;?>
    </td>
    <td>
      <select name="status[]" size="1" class="larger">
        <option value="0"></option>
        <?php
        //îderung 2008-06-06 JR
        //Status -1 eingef�Wenn dieser Status gesetzt ist, wird 
        //der Artikelstatus automatisch auf Basis der minQty und maxQty
        //berechnet. Auswertung erfolgt in shared/availability.php
        
        if ($obj->itemShipmentStatus == "-1")
          echo "<option value=\"-1\" selected> (" . L_dynsb_Automatic . ") </option>";
        else 
          echo "<option value=\"-1\"> (" . L_dynsb_Automatic . ") </option>";
        
        
        $status_query = "SELECT * FROM  ".DBToken."availability ORDER BY avaId";
        $status_result = mysqli_query($link,$status_query) or die(L_dynsb_Quantity . mysqli_error($link));
        while($rs = @mysqli_fetch_object($status_result))
        {
          if($rs->avaId==$obj->itemShipmentStatus) {
            $selected = "selected";
          }
          else {
            $selected = "";
          }
          echo "<option value=\"".$rs->avaId."\" $selected>".$rs->avaDescription."</option>";
        }
        ?>
      </select>
    </td>
    <td>
      <input type="text" size="3" value="<?php echo $obj->itemInStockQuantity;?>" name="quant[]">
      <input type="hidden" size="3" value="<?php echo $obj->itemItemNumber;?>" name="itemnumber[]">
    </td>
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
  	<input type="checkbox" class="checkbox" name="alldata" value="alldata" onClick="selectAllData('frmAvail');">&nbsp;
  	<?php echo L_dynsb_All;?>
  </td>
  <td>
<?php
	if ($start > 0) {
		$newStartPrev = ($start - $limit < 0) ? 0 : ($start-$limit);
		$bStatus = "";
	}
  else
		$bStatus = " disabled ";
?>
	<input type="button" class="button small <?php echo $bStatus;?>" onclick="javascript:navigation('0');" name="btn_next" value="|<--" <?php echo $bStatus;?>>
	<input type="button" class="button small <?php echo $bStatus;?>" onclick="javascript:navigation(<?php echo $newStartPrev;?>);" name="btn_end" value="<--" <?php echo $bStatus;?>>
<?php
	if ($start + $limit < $total) {
		$newStartNext = $start + $limit;
		$newStartLast = (truncate($total/$limit) * $limit);
		$bStatus = "";
	}
  else
		$bStatus = " disabled ";
?>
  	<input type="button" class="button small <?php echo $bStatus;?>" onclick="javascript:navigation(<?php echo $newStartNext;?>);" name="btn_next" value="-->"<?php echo $bStatus;?>>
  	<input type="button" class="button small <?php echo $bStatus;?>" onclick="javascript:navigation(<?php echo $newStartLast;?>);" name="btn_end" value="-->|"<?php echo $bStatus;?>>
		&nbsp;<?php echo $strTmp ?>
	</td>
</tr>

<tr>
  <td>
<?php
	if ($total > 0) { ?><img src="../../image/arrow.gif" alt=""><?php } else echo "&nbsp;";
?>
	</td>
  <td>

<?php	if ($total > 0) { ?>
      <input type="submit" class="button" name="btn_save" value="<?php echo L_dynsb_Save;?>">
      <input type="button" class="button" onclick="javascript:startDelete('frmAvail',<?php echo $start;?>);" name="btn_del" value="<?php echo L_dynsb_Delete;?>">
<?php 	}
		else
			echo "&nbsp;";
?>
	</td>
 </tr>
</table>
</div>

<?php
require_once("../../include/page.footer.php");
?>

</form>
</body>
</html>
