<?php
/******************************************************************************/
/* File: newsfeed.search.php                                                  */
/******************************************************************************/

//require("../include/login.check.inc.php");
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
    $SQL = "DELETE FROM ".DBToken."newsfeed WHERE nfIdNo = '".$value."'";
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

$strSQLSortedBy = "";
$sortNo = 0;
if (isset($_GET['sort']) && strlen(trim($_GET['sort'])) > 0)
{
  $sortNo = abs((int) $_GET['sort']);
	unset ($_GET['sort']);
}
$strSQLSortedBy = "ORDER BY ".$md_inputFields[$sortNo];

//-------------------------------------------------------------------newsfeed IdNo---------------
$SQLNfIdNo = "";
if (!isset($_POST['s_nfIdNo']) || strlen(trim($_POST['s_nfIdNo'])) == 0)
{ $SQLNfIdNo = ""; }
else
{
  $tmpNfIdNo = addslashes(strip_tags($_POST['s_nfIdNo']));
  $SQLNfIdNo = " AND nfIdNo LIKE '%".$tmpNfIdNo."%'";
}

//-------------------------------------------------------------------Title---------------
$SQLNfTitle = "";
if (!isset($_POST['s_nfTitle']) || strlen(trim($_POST['s_nfTitle'])) == 0)
{ $SQLNfTitle = ""; }
else
{
  $tmpNfTitle = addslashes(strip_tags($_POST['s_nfTitle']));
  $SQLNfTitle = " AND nfTitle LIKE  '%".$tmpNfTitle."%'";
}

//-------------------------------------------------------------------Description---------------
$SQLNfDescription= "";
if (!isset($_POST['s_nfDescription']) || strlen(trim($_POST['s_nfDescription'])) == 0)
{ $SQLNfDescription = ""; }
else
{
  $tmpNfDescription = addslashes(strip_tags($_POST['s_nfDescription']));
  $SQLNfDescription = " AND nfDescription LIKE '%".$tmpNfDescription."%'";
}

//-------------------------------------------------------------------Link---------------
$SQLNfLink = "";
if (!isset($_POST['s_nfLink']) || strlen(trim($_POST['s_nfLink'])) == 0)
{ $SQLNfLink = ""; }
else
{
  $tmpNfLink = addslashes(strip_tags($_POST['s_nfLink']));
  $SQLNfLink = " AND nfLink          LIKE '%".$tmpNfLink."%'";
}

//-------------------------------------------------------------------nfChgTimestamp---------------
$SQLNfChgTimestamp = "";
if (!isset($_POST['s_nfChgTimestamp']) || strlen(trim($_POST['s_nfChgTimestamp'])) == 0)
{ $SQLNfChgTimestamp = ""; }
else
{
  $tmpNfChgTimestamp = addslashes(strip_tags($_POST['s_nfChgTimestamp']));
  $SQLNfChgTimestamp = " AND nfChgTimestamp          LIKE '%".$tmpNfChgTimestamp."%'";
}

//-------------------------------------------------------------------Durationdays---------------
$SQLNfDurationdays = "";
if (!isset($_POST['s_nfDurationdays']) || strlen(trim($_POST['s_nfDurationdays'])) == 0)
{ $SQLNfDurationdays = ""; }
else
{
  $tmpNfDurationdays = addslashes(strip_tags($_POST['s_nfDurationdays']));
  $SQLNfDurationdays = " AND nfDurationdays          LIKE '%".$tmpNfDurationdays."%'";
}

$resultID = @mysqli_query($link,"SELECT COUNT(nfIdNo) AS anzahl FROM ".DBToken."newsfeed
                                 WHERE 1 = 1 ".$SQLNfIdNo."
                                             ".$SQLNfTitle."
                                             ".$SQLNfDescription."
                                             ".$SQLNfLink."
                                             ".$SQLNfChgTimestamp."
                                             ".$SQLNfDurationdays."                                                                                          
                                 AND nfChgHistoryFlg <> '0'");
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
    <title><?php echo L_dynsb_Newsfeed;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2009 GS Software Solutions AG">
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
      document.frmNewsfeed.start.value = val;
      document.frmNewsfeed.submit();
    }
    //--------------------------------------------------------------------------
    function preReset()
    {
      document.frmNewsfeed.start.value = 0;
      resetSearch('frmNewsfeed', 's_', true);
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
<form name="frmNewsfeed" action="mod.newsfeed.search.php" method="post">

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

  <h1>&#187;&nbsp;<?php echo L_dynsb_Newsfeed;?>&nbsp;&#171;</h1>
  <?php echo L_dynsb_nfUsingInfotext;?>
  <h2>Filter</h2>

<div style="height:1%;"> <!-- height-> hack for ie6: peekaboo bug-->
	<!-- <div class="filter">
	  <?/*=L_dynsb_nfNewsfeedNo*/?>:<br />
	  <input type="text" maxlength="16" value="<?/*=$tmpNfIdNo; */?>" name="s_nfIdNo">
	</div>
	-->
	<div class="filter">
  	<?php echo L_dynsb_nfTitle;?>:<br />
		<input type="text" maxlength="255" value="<?php echo $tmpNfTitle; ?>" name="s_nfTitle">
	</div>
	<div class="filter">
  	<?php echo L_dynsb_nfDescription;?>:<br />
    <input type="text" maxlength="255" value="<?php echo $tmpNfDescription; ?>" name="s_nfDescription">
	</div>
	<div class="filter">
		<?php echo L_dynsb_nfLink?>:<br />
    <input type="text" maxlength="255" value="<?php echo $tmpNfLink; ?>" name="s_nfLink" >
	</div>
	<div class="filter">
  	<?php echo L_dynsb_nfTimestamp?>:<br />
    <input type="text" maxlength="19" value="<?php echo $tmpNfChgTimestamp; ?>" name="s_nfChgTimestamp">
	</div>	
	<div class="filter">
  	<?php echo L_dynsb_nfDurationdays;?>:<br />
    <input type="text" maxlength="6" value="<?php echo $tmpNfDurationdays; ?>" name="s_nfDurationdays">
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
      $qrySQL = "SELECT * FROM ".DBToken."newsfeed
                 WHERE 1 = 1 ".$SQLNfIdNo."
                             ".$SQLNfTitle."
                             ".$SQLNfDescription."
                             ".$SQLNfLink."
                             ".$SQLNfChgTimestamp."
                             ".$SQLNfDurationdays."                             
                 AND nfChgHistoryFlg <> '0'
                 ORDER BY nfChgTimestamp DESC, nfIdNo DESC LIMIT ".$start.",".$limit;
      $qry = @mysqli_query($link,$qrySQL);
?>

  <table class="searchresult">
  	<tr>  		
  		<th>&nbsp;</th>
      <!-- <th><?/*=L_dynsb_nfNewsfeedNo*//*=L_dynsb_CustomerNo*/?></th> -->
  		<th><?php echo L_dynsb_nfTitle/*=L_dynsb_Firm*/?></th>
  		<!-- <th><?/*=L_dynsb_nfDescription*//*=L_dynsb_Lastname*/?></th> -->
  		<!-- <th><?/*=L_dynsb_nfLink*//*=L_dynsb_Zipcode*/?></th> -->
  		<th><?php echo L_dynsb_nfTimestamp/*=L_dynsb_City*/?></th>
      <th><?php echo L_dynsb_nfDurationdays/*=L_dynsb_Firm*/?></th>  		
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

      /*cusFirmname*/
      if(trim($obj->nfIdNo) == "")
      	$nfIdNo = "&nbsp;";
      else
      	$nfIdNo = trim($obj->nfIdNo);

      /*CusId*/
      if(trim($obj->nfTitle) == "")
      	$nfTitle = "&nbsp;";
      else
      	$nfTitle = trim($obj->nfTitle);

      /*cusCountry*/
      if(trim($obj->nfDescription) == "")
      	$nfDescription = "&nbsp;";
      else
      	$nfDescription = trim($obj->nfDescription);
      	
      if(trim($obj->nfDurationdays) == "")
      	$nfDurationdays = "&nbsp;";
      else
      	$nfDurationdays = trim($obj->nfDurationdays); 	
?>

    <tr id='d<?php echo $obj->nfIdNo;?>' class="<?php echo $rowStyle;?>" ondblclick="javascript:getElementById('chk<?php echo $obj->nfIdNo?>').click();">
      <td>
       <input id='chk<?php echo $obj->nfIdNo?>' type="checkbox" class="checkbox" style="cursor:pointer" name="pk[]" value="<?php echo $obj->nfIdNo;?>" onclick="javascript:checkAllData('frmNewsfeed');">
        <a href=mod.newsfeed.detail.php?<?php echo "pk=".$obj->nfIdNo."&amp;act=e&amp;start=".$start."&amp;lang=".$lang; ?>">
          <img src="../../image/edit.gif" alt="<?php echo L_dynsb_EditData;?>">
        </a>
        <a href="javascript:singleDelete('frmNewsfeed',<?php echo $start.",".$obj->nfIdNo;?>);">
          <img src="../../image/del.gif" alt="<?php echo L_dynsb_DeleteData;?>">
        </a>
      </td>
      <!-- <td><?/*=$nfIdNo;*/?></td> -->
      <td align="left"><?php echo $nfTitle;?></td>
      <!-- <td><?/*=$obj->nfDescription;*/?></td> -->
      <!-- <td><?/*=$obj->nfLink;*/?></td> -->
      <td align="center"><?php echo $obj->nfChgTimestamp;?></td>
      <td align="right"><?php echo $obj->nfDurationdays;?></td>            
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
    <input type="checkbox" class="checkbox" name="alldata" value="alldata" onClick="selectAllData('frmNewsfeed');">&nbsp;<?php echo L_dynsb_All;?>
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



<tr>
  <td>
<?php
    if ($total > 0) {
?>
    <img src="../../image/arrow.gif" width="35" height="15" alt="">
<?php
    }
    else {
      echo "&nbsp;";
    }
?>
  </td>
  <td>
<?php
    if ($total > 0) {
?>
    <input type="button" class="button" onClick="javascript:startDelete('frmNewsfeed',<?php echo $start;?>);" name="btn_del" value="<?php echo L_dynsb_Delete;?>">
<?php
    }
    else {
      echo "&nbsp;";
    }
?>
  </td>
</tr>
</table>

<div class="footer">
	<input type="button" class="button" onClick="javascript:self.location.href='mod.newsfeed.detail.php?<?php echo "act=a&amp;start=".$start."&amp;lang=".$lang;?>';" name="btn_add" value="<?php echo L_dynsb_Add;?>">
</div>

</div>

<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
