<?php
/******************************************************************************/
/* File: mod.data_import.detail.php                                           */
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

$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";

//***********************************************************************************************
unset($_SESSION["nlIdNo"]);

if (!isset($_REQUEST["status"]) || !is_numeric($_REQUEST["status"])) {
	die("Parameter missing.");
} else {
	$statusFlg = $_REQUEST["status"];
}

switch($statusFlg) {
	case 0: $titleAppendix = L_dynsb_Templates; break;
	case 1: $titleAppendix = L_dynsb_NotSend; break;
	case 2: $titleAppendix = L_dynsb_Archiv; break;
}

$limit = getentity(DBToken."settings","setRowCount","setIdNo = '1'");     // number of records per page

// count number of total records
$resultID = @mysqli_query($link,"SELECT COUNT(*) AS anzahl FROM ".DBToken."nl_documents
                          WHERE nldoStatusFlg = '$statusFlg'"
                          );
//A TS 14.11.2014: mysql_result ist deprecated und in MySQLi nicht enthalten,
//verwende alternativen Code stattdessen
//$total    = @mysq_l_result($resultID,0);
$rs = mysqli_fetch_assoc($resultID);
$total = intval($rs['anzahl']);
//E TS 14.11.2014

$start = (isset($_REQUEST['start'])) ? abs((int)$_REQUEST['start']) : 0;

// check parameter $start (maybe corrupt parameter in url)
if(abs($total) == 0)
	$start = 0;
else
	$start	= ($start >= $total) ? $total - $limit : $start;

if($start < 0)
	$start = 0;



if (!isset($_POST['del_stat']) || strlen(trim($_POST['del_stat'])) == 0)
	$ds = 0;
else
	$ds = $_POST['del_stat'];

//*** DELETE ***
if($ds == "1")
{
  if(!empty($_POST['pk']))
  {
	  foreach($_POST['pk'] as $value)
	  {
	    if (is_numeric($value)) {
		    $SQL = "DELETE from ".DBToken."nl_documents WHERE nldoIdNo = '".$value."'";
		    @mysqli_query($link,$SQL);
	    }
	  }
  }
}

//**************************
//** Get all Templates
//**************************
$qryTemlates = "
		SELECT *
			FROM ".DBToken."nl_documents
		 WHERE nldoStatusFlg = '$statusFlg'
	ORDER BY nldoChangeDate DESC
	   LIMIT $start, $limit"
	   ;
$resNlDocuments = @mysqli_query($link,$qryTemlates);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_DataImport;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
	  <script type="text/javascript" src="../../js/gslib.php"></script>
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

		function deleteRecord(frm, val, pk)
    {
      if (pk) {
	      for(var x = 0; x < document.forms[frm].elements.length; x++)
	      {
	        var y = document.forms[frm].elements[x];
	        if(y.type == 'checkbox' && y.name != 'alldata')
	        {
	          if(document.forms[frm].elements[x].value == pk)
	          {
	            document.forms[frm].elements[x].checked = true;
	          }
	          else {
	          	document.forms[frm].elements[x].checked = false;
	          }
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

	function navigation(val) {
    document.frmNewsletter.start.value = val;
    document.frmNewsletter.submit();
  }
</script>
</head>
<body>
<form name="frmNewsletter" action="mod.newsletter2.search.php" method="post">

<?php
require_once("../../include/page.header.php");
?>
<div id="PGnewslettersearch">
	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="start" value="<?php echo $start;?>">
	<input type="hidden" name="del_stat" value="0">
	<input type="hidden" name="status" value="<?php echo $statusFlg;?>">

<h1>&#187;&nbsp;<?php echo L_dynsb_Newsletter;?> - <?php echo $titleAppendix?>&nbsp;&#171;</h1>

<h2><?php echo L_dynsb_Searchresult;?></h2>
<table class="searchresult">
	<tr>
		<th>&nbsp;</th>
		<th><?php echo L_dynsb_Date;?></th>
		<th><?php echo L_dynsb_Subject;?></th>
		<th><?php echo L_dynsb_Text;?></th>
		<th>HTML</th>
	</tr>
<?php
while ($obj = @mysqli_fetch_object($resNlDocuments))
{
	$x++;
	if ($x % 2 != 0)
		$rowStyle = " odd ";
	else
		$rowStyle = " even ";

	$nlIdNo				= $obj->nldoIdNo;
	$nlSubject		= $obj->nldoSubject;
	$nlContText		= nl2br(stripslashes(substr($obj->nldoContentText, 0, 100)));
	$nlContHtml		= nl2br(stripslashes(strip_tags(substr($obj->nldoContentHtml,0, 100))));
	$nlChangeDate	= date_mysql2german($obj->nldoChangeDate);
	$nlSendDate		= date_mysql2german($obj->nldoSendDate);
?>

    <tr id="d<?php echo $nlIdNo?>" class="<?php echo $rowStyle;?>" ondblclick="javascript:getElementById('chk<?php echo $nlIdNo?>').click();">
      <td style="width:85px;">
        <input id='chk<?php echo $nlIdNo?>' type="checkbox" class="checkbox" style="cursor:pointer" name="pk[]" value="<?php echo $nlIdNo?>" onclick="javascript:checkAllData('frmNewsletter');">
        <a href="mod.newsletter2.detail.php?pk=<?php echo $nlIdNo?>&amp;lang=<?php echo $lang;?>"><img src="../../image/edit.gif" alt="<?php echo L_dynsb_EditData;?>"></a>
        <a href="javascript:deleteRecord('frmNewsletter','<?php echo $start;?>','<?php echo $nlIdNo?>');"><img src="../../image/del.gif" alt="<?php echo L_dynsb_DeleteData;?>"></a>
<?php
	if ($statusFlg == 0) {
?>
        <a href="mod.newsletter2.detail.php?pk=<?php echo $nlIdNo?>&amp;act=1&amp;lang=<?php echo $lang;?>"><img src="../../image/assign.gif" alt="<?php echo L_dynsb_createNewsletter;?>"></a>
<?php	}	?>
      </td>
      <td align="center"><?php echo $nlChangeDate;?></td>
      <td valign="top"><?php echo $nlSubject;?></td>
      <td valign="top"><?php echo $nlContText;?></td>
      <td><?php echo $nlContHtml;?></td>
    </tr>
<?php
	} // end of while
?>
</table>
<h2>&nbsp;</h2>

<!-- navigation // -->
<table>
<tr>
  <td style="width:60px;">
    <input type="checkbox" class="checkbox" name="alldata" value="alldata" onClick="selectAllData('frmNewsletter');"> <?php echo L_dynsb_All;?>
  </td>
  <td>
<?php
 	if ($start > 0)
  {
    $nldoStartPrev = ($start - $limit < 0) ? 0 : ($start-$limit);
    $bStatus = "";
	}
  else
		$bStatus = " disabled ";
?>
	<input type="button" class="button small <?php echo $bStatus;?>" onclick="javascript:navigation('0');" name="btn_next" value="|<--"<?php echo $bStatus;?>>
  <input type="button" class="button small <?php echo $bStatus;?>" onclick="javascript:navigation(<?php echo $nldoStartPrev;?>);" name="btn_end" value="<--"<?php echo $bStatus;?>>
<?php
  if ($start + $limit < $total)
  {
    $nldoStartNext = $start + $limit;
    $nldoStartLast = (truncate($total/$limit) * $limit);
    $bStatus = "";
	}
  else
		$bStatus = " disabled ";
?>
    <input type="button" class="button small <?php echo $bStatus;?>" onclick="javascript:navigation(<?php echo $nldoStartNext;?>);" name="btn_next" value="-->">
    <input type="button" class="button small <?php echo $bStatus;?>" onclick="javascript:navigation(<?php echo $nldoStartLast;?>);" name="btn_end" value="-->|">
<?php echo $strTmp ?>
  </td>
</tr>
<tr>
  <td><?php if ($total > 0) { ?><img src="../../image/arrow.gif" alt=""><?php } else echo "&nbsp;"; ?></td>
  <td><?php if ($total > 0) { ?><input type="button" class="button" onclick="javascript:deleteRecord('frmNewsletter',<?php echo $start;?>);" name="btn_del" value="<?php echo L_dynsb_Delete;?>"><?php } else echo "&nbsp;"; ?></td>
</tr>
</table>

<?php
	if ($statusFlg == 0) {
?>
<div class="footer">
  <input type="button" class="button" value="<?php echo L_dynsb_New?>" onclick="javascript:self.location.href='mod.newsletter2.detail.php'">
</div>
<?php
	}
?>
</div>
<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
