<?php
/******************************************************************************/
/* File: mod.faq.search.php                                                   */
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
  if(!isset($_POST['pk']) || strlen(trim($_POST['pk'])) == 0)
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

  $pka = explode(",", $pkDataListStr);
  foreach($pka as $value)
  {
    $SQL = "DELETE from ".DBToken."faq WHERE faqId = '".$value."'";
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
$SQLTitle = "";
if (!isset($_POST['s_Title']) || strlen(trim($_POST['s_Title'])) == 0)
{ $SQLTitle = ""; }
else
{
  $tmpTitle = addslashes(strip_tags($_POST['s_Title']));
  $SQLTitle = " AND faqTitle LIKE '%".$tmpTitle."%'";
}

//-------------------------------------------------------------------Content---------------
$SQLContent = "";
if (!isset($_POST['s_Content']) || strlen(trim($_POST['s_Content'])) == 0)
{ $SQLContent = ""; }
else
{
  $tmpContent = addslashes(strip_tags($_POST['s_Content']));
  $SQLContent = " AND faqText LIKE '%".$tmpContent."%'";
}
//------------------------------------------------------- End getting parameters -------------------

//------------------------------------------------------------------------------
// count number of total records

$resultID = @mysqli_query($link,"SELECT COUNT(faqId) AS anzahl FROM ".DBToken."faq WHERE 1 = 1 ".$SQLDate."
                                      ".$SQLTitle."
                                      ".$SQLContent);

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
if($SESS_languageIdNo == 2) $strcal = "en";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
  <title><?php echo L_dynsb_Faq;?></title>
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
  //------------------------------------------------------------------------------
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
  //----------------------------------------------------------------------------
  MM_reloadPage(true);
  //----------------------------------------------------------------------------
  function navigation(val)
  {
    document.frmCnews.start.value = val;
    document.frmCnews.submit();
  }
  //----------------------------------------------------------------------------
  function preReset()
  {
    document.frmCnews.start.value = 0;
    resetSearch('frmCnews', 's_', true);
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
        var bCheck = confirm("<?php echo L_dynsb_ReallyDelete;?>");
        if(bCheck==true) document.forms[sFormName].submit();
      }
      else
      {
        alert("<?php echo L_dynsb_NoDataSelectedDelete;?>");
      }
    }
    //------------------------------------------------------------------------------
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
<form name="frmCnews" action="mod.faq.search.php" method="post">
<?php
require_once("../../include/page.header.php");
?>

<div id="PGfaqsearch">
	<input type="hidden" name="lang" value="<?php echo $lang; ?>">
	<input type="hidden" name="start" value="<?php echo $start; ?>">
	<input type="hidden" name="backstart" value="<?php echo $backstart; ?>">
	<input type="hidden" name="next" value="">
	<input type="hidden" name="nav" value="">
	<input type="hidden" name="del_stat" value="0">



<h1>&#187;&nbsp;<?php echo L_dynsb_Faq;?>&nbsp;&#171;</h1>
<h2><?php echo L_dynsb_Filter;?></h2>


<div style="height:1%;"> <!-- height-> hack for ie6: peekaboo bug-->
	<div class="filter">
		<?php echo L_dynsb_Headline;?><br />
      <input type="text" maxlength="32" value="<?php echo $tmpTitle; ?>" name="s_Title">
	</div>

	<div class="filter">
 		<?php echo L_dynsb_Content;?><br />
		<input type="text" maxlength="20" value="<?php echo $tmpContent; ?>" name="s_Content">
	</div>
</div>

<p class="clear">
	<input type="button" class="button" onclick="javascript:navigation(<?php echo $start;?>);" name="btn_startSearch" value="<?php echo L_dynsb_StartSearch;?>">
	<input type="button" class="button" onclick="javascript:preReset();" name="btn_resetSearch" value="<?php echo L_dynsb_Reset;?>">
</p>


<h2><?php echo L_dynsb_Searchresult;?> </h2>

<table class="searchresult">
<tr>
	<th>&nbsp;</th>
	<th align="left"><?php echo L_dynsb_Headline;?></th>
	<th align="left"><?php echo L_dynsb_Content;?></th>
</tr>
<?php
  // start database query
  $qrySQL = "SELECT * FROM ".DBToken."faq
             WHERE 1 = 1 ".$SQLDate."
                         ".$SQLTitle."
                         ".$SQLContent."
             ORDER BY faqTitle ASC LIMIT ".$start.",".$limit;
  $qry = @mysqli_query($link,$qrySQL);
  $i = 0;
  while ($obj = @mysqli_fetch_object($qry))
  {
  	$i++;
		if ($i % 2 != 0)
			$rowStyle = " odd ";
		else
			$rowStyle = " even ";

    if(strlen(strip_tags(trim($obj->faqText))) > 44){
      $faqText = strip_tags(substr($obj->faqText, 0, 180))." [...]";
    }
    else{
      $faqText = strip_tags(trim($obj->faqText));
    }

    $faqTitle = trim($obj->faqTitle);
    if($faqText == ""){
    	$faqText = "&nbsp;"; }

    if($faqTitle == ""){
    	$faqTitle = "&nbsp;"; }
?>
  <tr class="<?php echo $rowStyle;?>" id='d<?php echo $obj->faqId;?>' ondblclick="javascript:getElementById('chk<?php echo $obj->faqId;?>').click();">
    <td>
      <input id='chk<?php echo $obj->faqId;?>' type="checkbox" class="checkbox" style="cursor:pointer" name="pk[]" value="<?php echo $obj->faqId;?>" onclick="javascript:checkAllData('frmCnews');">

    <a href="mod.faq.detail.php?<?php echo "pk=".$obj->faqId."&amp;act=e&amp;start=".$start."&amp;lang=".$lang; ?>">
      <img src="../../image/edit.gif"  alt="<?php echo L_dynsb_EditData;?>">
    </a>
    <a href="javascript:singleDelete('frmCnews',<?php echo $start.",".$obj->faqId;?>);">
      <img src="../../image/del.gif" alt="<?php echo L_dynsb_DeleteData;?>">
    </a>
<?php
		if($obj->faqActive=='1')	{
	    echo '<a href="mod.faq.activate.php?start='.$start.'&amp;lang='.$lang.'&amp;pk='.$obj->faqId.'&amp;action=d">';
	    echo '<img src="../../image/enable.gif" title="'.L_dynsb_Disable.'" alt="'.L_dynsb_Disable.'"></a>';
	  }
	  else  {
	    echo '<a href="mod.faq.activate.php?start='.$start.'&amp;lang='.$lang.'&amp;pk='.$obj->faqId.'&amp;action=e">';
	    echo '<img src="../../image/disable.gif" title="'.L_dynsb_Enable.'" alt="'.L_dynsb_Enable.'"></a>';
	  }
?>
	  </td>
	  <td><?php echo $faqTitle;?></td>
	  <td><?php echo $faqText;?></td>
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
if ($total < 1) {
	$strTmp = $strTmp.strval(0); }
else {
	$strTmp = $strTmp.strval($start + 1); }

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
    <input type="checkbox" class="checkbox" name="alldata" value="alldata"  onClick="selectAllData('frmCnews');"> <?php echo L_dynsb_All;?>
  </td>
  <td>
<?php
 	if ($start > 0)
  {
    $cnewStartPrev = ($start - $limit < 0) ? 0 : ($start-$limit);
    $bStatus = "";
	}
	else
		$bStatus = " disabled ";
?>
  <input type="button" class="button small<?php echo $bStatus;?>" onclick="javascript:navigation('0');" name="btn_next" value="|<--"<?php echo $bStatus;?>>
  <input type="button" class="button small<?php echo $bStatus;?>" onclick="javascript:navigation(<?php echo $cnewStartPrev;?>);" name="btn_end" value="<--"<?php echo $bStatus;?>>

<?php
  if ($start + $limit < $total)
  {
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
  <td>
  	<?php if ($total > 0) { ?><input type="button" class="button" onclick="javascript:startDelete('frmCnews',<?php echo $start;?>);" name="btn_del" value="<?php echo L_dynsb_Delete;?>"><?php } else echo "&nbsp;"; ?>
  </td>
</tr>
</table>

<div class="footer">
  <input type="button" class="button" onclick="javascript:self.location.href='mod.faq.detail.php?act=a&amp;start=<?php echo $start;?>&amp;lang=<?php echo $lang;?>';" name="btn_add" value="<?php echo L_dynsb_Add;?>">
</div>
</div>
<?php
require_once("../../include/page.footer.php");
?>

</form>
</body>
</html>
