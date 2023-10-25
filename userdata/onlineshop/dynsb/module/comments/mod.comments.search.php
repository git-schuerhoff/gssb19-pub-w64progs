<?php
/******************************************************************************/
/* File: mod.data_import.detail.php                                           */
/******************************************************************************/

require_once("../../include/login.check.inc.php");
require_once("../../include/functions.inc.php");
require_once("../../../conf/db.const.inc.php");
require_once("class.comment.php");

/***************** Sprachdatei ************************************************/
if (!isset($_REQUEST['lang']) || strlen(trim($_REQUEST['lang'])) == 0) {
    $lang = "deu";
} 
else {
	$lang = $_REQUEST['lang'];
	if(!file_exists("../../lang/lang_".$lang.".php")) {
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

//******************************************************************************
 
/// DELETE ////////////////////////////////////////////////////////////////////
if (!isset($_POST['del_stat']) || strlen(trim($_POST['del_stat'])) == 0)
	$ds = 0;
else
	$ds = $_POST['del_stat'];

//*** DELETE ***
if($ds == "1") {
  if(!empty($_POST['pk'])) {
	  foreach($_POST['pk'] as $value) {
		$c = new Comment($value);
	  	$b = $c->delete($value);
		}
  }
}
///////////////////////////////////////////////////////////////////////////////

//**************************
//*** UPDATE or INSERT
//**************************

if (isset($_REQUEST["btnsave"])) {
	if (isset($_POST["pk"])) {
	
		$aPK = $_POST["pk"];
		foreach($aPK as $id) {
			if($_POST['vis'][$id] == "")
				$vis = 0;
			else
				$vis = 1;
			
			$c = new Comment($id);
			$c->setVisible($vis);
			$c->save();
		}
	}
}
///////////////////////////////////////////////////////////////////////////////


$tmpDateGer = '';
if (isset($_REQUEST['s_Date']))
	$tmpDateGer = $_REQUEST['s_Date'];

$tmpCustomerNo = '';
if (isset($_REQUEST['s_CustomerNo']))
	$tmpCustomerNo = $_REQUEST['s_CustomerNo'];

$tmpItemNo = '';
if (isset($_REQUEST['s_ItemNo']))
	$tmpItemNo = $_REQUEST['s_ItemNo'];

$tmpIsVisible = '';
if (isset($_POST['s_isVisible']))
	$tmpIsVisible = $_POST['s_isVisible'];

$limit = getentity(DBToken."settings","setRowCount","setIdNo = '1'");     // number of records per page

//alle Kommentare f� Count holen
$c = new Comment();
$aComments = $c->getAllComments();
$total =  count($aComments);

$start = (isset($_REQUEST['start'])) ? abs((int)$_REQUEST['start']) : 0;

// check parameter $start (maybe corrupt parameter in url)
if(abs($total) == 0)
	$start = 0;
else
	$start	= ($start >= $total) ? $total - $limit : $start;

if($start < 0)
	$start = 0;

$aFilter = array();
$aFilter['itemNumber']= $tmpItemNo;	
$aFilter['cusId'] 		= $tmpCustomerNo;	
$aFilter['date'] 			= $tmpDateGer;	
$aFilter['visible']		= $tmpIsVisible;

//Nur die relevanten Kommentare holen
$aComments = $c->getAllComments($start, $limit, $aFilter);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title></title>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
	  <script type="text/javascript" src="../../js/gslib.php"></script>
		<script type="text/javascript" src="../../js/calendar.js"></script>
		<script type="text/javascript" src="../../js/calendar-<?php echo $strcal;?>.js"></script>
		<script type="text/javascript" src="../../js/calendar-setup.js"></script>
    <script language="JavaScript" type="text/javascript">
    function MM_reloadPage(init) {  
      //reloads the window if Nav4 resized
      if (init==true) with (navigator) {
        if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
          document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage;
        }
      }
      else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
    }
    //--------------------------------------------------------------------------
    MM_reloadPage(true);

		function deleteRecord(frm, val, pk) {
      if (pk) {
	      for(var x = 0; x < document.forms[frm].elements.length; x++)
	      {
	        var y = document.forms[frm].elements[x];
	        if(y.type == 'checkbox' && y.name != 'alldata') {
	          if(document.forms[frm].elements[x].value == pk) {
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
      if(bCheck==true) {
        document.forms[frm].submit();
      }
      else {
        for(var x = 0; x < document.forms[frm].elements.length; x++) {
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

	function navigation(val) {
    document.frmComments.start.value = val;
    document.frmComments.submit();
  }

	function preReset() {
    document.frmComments.start.value = 0;
    resetSearch('frmComments', 's_', true);
  }

  function resizeComment(id) {
		var e = document.getElementById('txta' + id);
		if (e) {
			if (e.style.height == '7em')
				e.style.height = '25em';
			else
				e.style.height = '7em';
		}
  }
	  
</script>
</head>


<body>
<form name="frmComments" action="mod.comments.search.php" method="post">

<?php
require_once("../../include/page.header.php");
?>
<div id="PGcommentssearch">
	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="start" value="<?php echo $start;?>">
	<input type="hidden" name="del_stat" value="0">

<h1>&#187;&nbsp;<?php echo L_dynsb_Comments;?> - <?php echo L_dynsb_Search;?>#&nbsp;&#171;</h1>

<h2>Filter</h2>
<div style="height:1%;"> <!-- height-> hack for ie6: peekaboo bug-->
	<div class="filter">
	  <?php echo L_dynsb_Isvisible;?>:<br />
	  <select name="s_isVisible">
	   <option value="--"> -- </option>
	   <option value="1" <?php if($tmpIsVisible=="1")echo " selected "; ?>><?php echo L_dynsb_Yes;?></option>
	   <option value="0" <?php if($tmpIsVisible=="0")echo " selected "; ?>><?php echo L_dynsb_No;?></option>
	  </select>
	</div>
	
	<div class="filter">
    <?php echo L_dynsb_Date;?> =
    <br />
    <input type="text" maxlength="16" value="<?php echo $tmpDateGer; ?>" name="s_Date" id="s_Date" readonly>
    <img src="../../image/calendar.gif" id="s_DateTrigger" style="cursor: pointer;" title="<?php echo L_dynsb_Calendar;?>" alt="<?php echo L_dynsb_Calendar;?>">

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
  	<?php echo L_dynsb_ArticleNo?>:<br />
    <input type="text" maxlength="20" value="<?php echo $tmpItemNo; ?>" name="s_ItemNo" id="s_ItemNo">
	</div>
	<div class="filter">
	  <?php echo L_dynsb_Customers;?>-ID:<br />
	  <input type="text" maxlength="16" value="<?php echo $tmpCustomerNo; ?>" name="s_CustomerNo" id="s_CustomerNo">
	</div>
	
</div>
<p class="clear">
	<input type="button" class="button" onclick="javascript:navigation(<?php echo $start;?>);" name="btn_startSearch" value="<?php echo L_dynsb_StartSearch;?>">
	<input type="button" class="button" onclick="javascript:preReset();" name="btn_resetSearch" value="<?php echo L_dynsb_Reset;?>">
</p>

<h2><?php echo L_dynsb_Searchresult;?></h2>
<table class="searchresult2">
	<tr>
		<th>&nbsp;</th>
		<th align="left"><?php echo L_dynsb_Isvisible;?></th>
		<th align="left"><?php echo L_dynsb_Date;?></th>
		<th align="left"><?php echo L_dynsb_ArticleNo?></th>
		<th align="left">ID/<?php echo L_dynsb_CustomerNo?></th>
		<th width="70%" align="left"><?php echo L_dynsb_Subject;?></th>
	</tr>
<?php
$x = 0;
foreach ($aComments as $comm) {
	
  //schnell noch die Kundennummer holen...
  $qry = "SELECT cusId FROM " . DBToken . "customer WHERE cusIdNo = '" . $comm->getCusId(). "'";
  $res = mysqli_query($link,$qry);
  $row = mysqli_fetch_array($res);
  
  $x++;
	if ($x % 2 != 0)
		$rowStyle = " odd ";
	else
		$rowStyle = " even ";
?>
    <tr id="d<?php echo $comm->getIdNo();?>" class="<?php echo $rowStyle;?>" ondblclick="javascript:getElementById('chk<?php echo $comm->getIdNo();?>').click();">
      <td style="width:85px;">
        <input id='chk<?php echo $comm->getIdNo();?>' type="checkbox" class="checkbox" style="cursor:pointer" name="pk[]" value="<?php echo $comm->getIdNo();?>" onclick="javascript:checkAllData('frmComments');">
        <a href="javascript:deleteRecord('frmComments','<?php echo $start;?>','<?php echo $comm->getIdNo();?>');"><img src="../../image/del.gif" alt="<?php echo L_dynsb_DeleteData;?>"></a>
      	<a href="javascript:resizeComment('<?php echo $comm->getIdNo();?>')" title="<?php echo L_dynsb_Visible;?> / <?php echo L_dynsb_Invisible;?>">
					<img src="../../image/view.gif">
				</a>
      </td>
      <td>
       <input title="<?php echo L_dynsb_Isvisible;?>" type="checkbox" name="vis[<?php echo $comm->getIdNo();?>]" <?php if ($comm->getVisible() > "0") echo " checked ";?> onchange="javascript:getElementById('chk<?php echo $comm->getIdNo();?>').checked=true;checkAllData('frmComments');">
      </td>
      <td><a href="javascript:document.getElementById('s_Date').value='<?php echo $comm->getDate(1);?>'; document.forms.frmComments.submit();"><?php echo $comm->getDate(1);?></a></td>
      <td><a href="javascript:document.getElementById('s_ItemNo').value='<?php echo $comm->getItemNumber();?>'; document.forms.frmComments.submit();"><?php echo $comm->getItemNumber();?></a></td>
      <td><a href="javascript:document.getElementById('s_CustomerNo').value='<?php echo $comm->getCusId();?>'; document.forms.frmComments.submit();"><?php echo $comm->getCusId();?></a> (<?php echo $row["cusId"];?>)</td>
      <td><b><?php echo $comm->getSubject();?></b></td>
    </tr>
    <tr>
    
    <tr id="da<?php echo $comm->getIdNo();?>" class="<?php echo $rowStyle;?>" ondblclick="javascript:getElementById('chk<?php echo $comm->getIdNo();?>').click();">
    	<td valign="top">
    		<img src="rating<?php echo $comm->getRating();?>.gif" alt="# <?php echo $comm->getRating();?>">
				
			</td>
      <td colspan="5">
       <textarea readonly style="height:7em;" id="txta<?php echo $comm->getIdNo();?>" ondblclick="javascript:resizeComment('<?php echo $comm->getIdNo();?>');getElementById('chk<?php echo $comm->getIdNo();?>').click();"><?php echo $comm->getBody();?></textarea><br />&nbsp;
      </td>
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
    <input type="checkbox" class="checkbox" name="alldata" value="alldata" onClick="selectAllData('frmComments');"> <?php echo L_dynsb_All;?>
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
  </td>
</tr>
<tr>
  <td><?php if ($total > 0) { ?><img src="../../image/arrow.gif" alt=""><?php } else echo "&nbsp;"; ?></td>
  <td><?php if ($total > 0) { ?><input type="button" class="button" onclick="javascript:deleteRecord('frmComments',<?php echo $start;?>);" name="btn_del" value="<?php echo L_dynsb_Delete;?>"><?php } else echo "&nbsp;"; ?></td>
</tr>
</table>

 <div class="footer">
  <input type="submit" class="button" name="btnsave" value="<?php echo L_dynsb_Save;?>">
</div>
</div>
<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
