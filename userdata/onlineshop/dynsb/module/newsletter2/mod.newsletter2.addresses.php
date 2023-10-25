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
$limit = getentity(DBToken."settings","setRowCount","setIdNo = '1'");     // number of records per page

$timestamp = date("Y-m-d H:i:s");

if ($_REQUEST["mgid"])
	$_SESSION["mgid"] = $_REQUEST["mgid"];
$nlmgIdNo = $_SESSION["mgid"];

// count number of total records
$resultID = @mysqli_query($link,"SELECT COUNT(*) AS anzahl FROM ".DBToken."nl_addresses JOIN ".DBToken."nl_addr2mg ON nladIdNo = admgNladIdNo AND admgNlmgIdNo = '$nlmgIdNo'");
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


//**************************
//*** DELETE ***
//**************************
if($ds == "1")
{
  if(!empty($_POST['pk']))
  {
	  foreach($_POST['pk'] as $value)
	  {
	    if (is_numeric($value)) {

				//check how many mailgroups contain the address
				$qry = "SELECT count(*) AS count FROM ".DBToken."nl_addr2mg WHERE admgNladIdNo = '".$value."'";
				$res = @mysqli_query($link,$qry);
				$row = mysqli_fetch_array($res);

				//if in only one, delete address and assignment
				if ($row["count"] == "1") {

			    $SQL = "DELETE from ".DBToken."nl_addr2mg WHERE admgNladIdNo = '$value'";
			    @mysqli_query($link,$SQL);

			    $SQL = "DELETE from ".DBToken."nl_addresses WHERE nladIdNo = '$value'";
			    @mysqli_query($link,$SQL);
				}
				//if in other mailgroups, delete only assignment
				else {
					$SQL = "DELETE from ".DBToken."nl_addr2mg WHERE admgNladIdNo = '$value' AND admgNlmgIdNo = '$nlmgIdNo'";
					@mysqli_query($link,$SQL);
				}
				#echo $SQL;
	    }
	  }
  }
}



//**************************
//*** UPDATE or INSERT
//**************************

if (isset($_REQUEST["btnsave"])) {
	if (isset($_REQUEST["pk"])) {

		$aPK = $_REQUEST["pk"];
		foreach($aPK as $nladIdNo) {
			$address	=	strip_tags( stripslashes(trim($_REQUEST["address"][$nladIdNo])));
			$format		=	$_REQUEST["format"][$nladIdNo];
			$active		=	$_REQUEST["active"][$nladIdNo];

			//NEW entry
			if ($nladIdNo == "-1") {

				//only if address is filled
				if (!empty($address)) {

					//if already in database use existing ID
					$qry = "SELECT * FROM ".DBToken."nl_addresses WHERE nladAddress = '$address'";
					$res = @mysqli_query($link,$qry);
					if ($row = mysqli_fetch_array($res)) {
						$nladIdNo = $row["nladIdNo"];
					}
					else {
						$sql = "INSERT INTO ".DBToken."nl_addresses (nladAddress, nladFormat, nladActiveFlg) VALUES ('$address', '$format', '$active')";
						@mysqli_query($link,$sql);
						$nladIdNo = mysqli_insert_id($link);
					}

					//insert into address2mailgroup
					$sql = "INSERT INTO ".DBToken."nl_addr2mg (admgNladIdNo, admgNlmgIdNo, admgChgDate) VALUES ('$nladIdNo', '$nlmgIdNo', '$timestamp')";
					@mysqli_query($link,$sql);
				}
			}
			//UPDATE old ones
			else {
				$sql = "UPDATE ".DBToken."nl_addresses SET nladAddress = '$address', nladFormat = '$format', nladActiveFlg = '$active' WHERE nladIdNo = '$nladIdNo'";
				@mysqli_query($link,$sql);
			}
		}
	}
}


//**************************
//** Get all Adresses
//**************************

//name of mailgroup
$qryMG = "SELECT * FROM ".DBToken."nl_mailgroups WHERE nlmgIdNo = $nlmgIdNo";
$resMG = @mysqli_query($link,$qryMG);
$rowMG = mysqli_fetch_object($resMG);

//addresses
$qryAddr = "
		SELECT *
			FROM ".DBToken."nl_addresses
 			JOIN ".DBToken."nl_addr2mg ON nladIdNo = admgNladIdNo AND admgNlmgIdNo = $nlmgIdNo
	ORDER BY nladAddress ASC
	   LIMIT $start, $limit"
	   ;
$resNlAddr = @mysqli_query($link,$qryAddr);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_EmailAddresses;?></title>
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
    document.frmAddresses.start.value = val;
    document.frmAddresses.submit();
  }
</script>
</head>
<body>
<form name="frmAddresses" action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">

<?php
require_once("../../include/page.header.php");
?>
<div id="PGnewsletteraddresses">
	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="start" value="<?php echo $start;?>">
	<input type="hidden" name="del_stat" value="0">

<h1>&#187;&nbsp;<?php echo L_dynsb_EmailAddresses;?>&nbsp;&#171;</h1>

<h2><?php echo L_dynsb_MailingGroup?>: <?php echo $rowMG->nlmgName;?></h2>
<table class="searchresult">
	<tr>
		<th>&nbsp;</th>
		<th><?php echo L_dynsb_Email;?></th>
		<th><?php echo L_dynsb_Format;?></th>
		<th><?php echo L_dynsb_Activ;?></th>
	</tr>
<?php
while ($obj = @mysqli_fetch_object($resNlAddr))
{
	$x++;
	if ($x % 2 != 0)
		$rowStyle = " odd ";
	else
		$rowStyle = " even ";

	$adIdNo			= $obj->nladIdNo;
	$adAddress	= $obj->nladAddress;
	$adFormat		= $obj->nladFormat;
	$adActive		= $obj->nladActiveFlg;
?>

    <tr class="<?php echo $rowStyle;?>" id="d<?php echo $adIdNo?>"  ondblclick="javascript:getElementById('chk<?php echo $adIdNo?>').click();">
      <td style="width:70px;">
        <input id='chk<?php echo $adIdNo?>' type="checkbox" class="checkbox" style="cursor:pointer" name="pk[]" value="<?php echo $adIdNo?>" onclick="javascript:checkAllData('frmAddresses');">
        <a href="javascript:deleteRecord('frmAddresses','<?php echo $start;?>','<?php echo $adIdNo?>');"><img src="../../image/del.gif" alt="<?php echo L_dynsb_DeleteData;?>"></a>
      </td>
      <td align="center"><input name="address[<?php echo $adIdNo?>]" class="larger" value="<?php echo $adAddress;?>"  onchange="javascript:o = getElementById('chk<?php echo $adIdNo?>'); if (!o.checked) o.click();"></td>
      <td align="center">
      	<select name="format[<?php echo $adIdNo?>]" class="medium" onchange="javascript:o = getElementById('chk<?php echo $adIdNo?>'); if (!o.checked) o.click();">
      		<option value="T"<?php if ($adFormat=="T") echo " selected ";?>>Text</option>
      		<option value="H"<?php if ($adFormat=="H") echo " selected ";?>>HTML</option>
      	</select>
      </td>
      <td align="center">
				<input type="checkbox" class="checkbox" name="active[<?php echo $adIdNo?>]" value="1" <?php if ($adActive == 1) echo " checked ";?> onchange="javascript:o = getElementById('chk<?php echo $adIdNo?>'); if (!o.checked) o.click();"	>
			</td>
    </tr>
<?php
	} // end of while
?>

	<tr id="d-1">
      <td style="width:70px;">
      	<input id="chk-1" type="checkbox" class="checkbox" style="cursor:pointer" name="pk[]" value="-1" onclick="javascript:checkAllData('frmAddresses');"> <?php echo L_dynsb_New?>:
      </td>
      <td align="center"><input name="address[-1]" class="larger" value="" onchange="javascript:o = getElementById('chk-1'); if (!o.checked) o.click();"></td>
      <td align="center">
      	<select name="format[-1]" class="medium">
      		<option value="T">Text</option>
      		<option value="H">HTML</option>
      	</select>
      </td>
      <td align="center">
				<input type="checkbox" class="checkbox" name="active[-1]" value="1" checked">
			</td>
    </tr>
</table>
<h2>&nbsp;</h2>

<!-- navigation // -->
<table>
<tr>
  <td style="width:60px;">
    <input type="checkbox" class="checkbox" name="alldata" value="alldata" onClick="selectAllData('frmAddresses');"> <?php echo L_dynsb_All;?>
  </td>
  <td>
<?php
 	if ($start > 0)
  {
    $nlmgStartPrev = ($start - $limit < 0) ? 0 : ($start-$limit);
    $bStatus = "";
	}
  else
		$bStatus = " disabled ";
?>
	<input type="button" class="button small <?php echo $bStatus;?>" onclick="javascript:navigation('0');" name="btn_next" value="|<--"<?php echo $bStatus;?>>
  <input type="button" class="button small <?php echo $bStatus;?>" onclick="javascript:navigation(<?php echo $nlmgStartPrev;?>);" name="btn_end" value="<--"<?php echo $bStatus;?>>
<?php
  if ($start + $limit < $total)
  {
    $nlmgStartNext = $start + $limit;
    $nlmgStartLast = (truncate($total/$limit) * $limit);
    $bStatus = "";
	}
  else
		$bStatus = " disabled ";
?>
    <input type="button" class="button small <?php echo $bStatus;?>" onclick="javascript:navigation(<?php echo $nlmgStartNext;?>);" name="btn_next" value="-->"<?php echo $bStatus;?>>
    <input type="button" class="button small <?php echo $bStatus;?>" onclick="javascript:navigation(<?php echo $nlmgStartLast;?>);" name="btn_end" value="-->|"<?php echo $bStatus;?>>
<?php echo $strTmp ?>
  </td>
</tr>
<tr>
  <td><?php if ($total > 0) { ?><img src="../../image/arrow.gif" alt=""><?php } else echo "&nbsp;"; ?></td>
  <td><?php if ($total > 0) { ?><input type="button" class="button" onclick="javascript:deleteRecord('frmAddresses',<?php echo $start;?>);" name="btn_del" value="<?php echo L_dynsb_Delete;?>"><?php } else echo "&nbsp;"; ?></td>
</tr>
</table>

<div class="footer">
  <input type="submit" class="button" name="btnsave" value="<?php echo L_dynsb_Save;?>">
  <input type="button" class="button" value="<?php echo L_dynsb_Back?>" onclick="javascript:self.location.href='mod.newsletter2.mailgroups.php'">
</div>
</div>
<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
