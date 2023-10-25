<?php
/******************************************************************************/
/* File: mod.discount.detail.php                                              */
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
if(isset($_REQUEST['start'])) {
    $start = intval($_REQUEST['start']);
}
if(isset($_REQUEST['pk'])) {
    $cnewsIdNo = intval($_REQUEST['pk']);
}
if(isset($_REQUEST['act'])) {
    $act = trim($_REQUEST['act']);
}

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


$resultID = @mysqli_query($link,"SELECT COUNT(cusIdNo) AS anzahl FROM ".DBToken."customer
                                 WHERE 1 = 1");

//A TS 14.11.2014: mysql_result ist deprecated und in MySQLi nicht enthalten,
//verwende alternativen Code stattdessen
//$total    = @mysq_l_result($resultID,0);
$rs = mysqli_fetch_assoc($resultID);
$total = $rs['anzahl'];
//E TS 14.11.2014
if($total == '') $total = 0;

$start = (isset($_REQUEST['start'])) ? abs((int)$_REQUEST['start']) : 0;
$limit = getentity(DBToken."settings","setRowCount","setIdNo = '1'");     // number of records per page

if(abs($total) == 0) $start = 0;
else $start    = ($start >= $total) ? $total - $limit : $start;
if($start < 0) $start = 0;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_CustomerDiscount;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
	  <script type="text/javascript" src="../../js/gslib.php?lang=<?php echo $SESS_languageIdNo;?>"></script>
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
    //--------------------------------------------------------------------------
    var SelectBox = document.getElementsByName("customers[]");
    //--------------------------------------------------------------------------
    function selectCust(limit)
    {
      for(var i=1; i<=limit; i++)
      {
        var CustCheck = document.getElementById(i+"Check");
        if (CustCheck.checked==true)
        {
          var CustID = document.getElementById(i+"ID").value;
          var Check = checkValue(CustID);
          if (Check!=true)
          {
            var CustFirstName = document.getElementById(i+"FN").value;
            var CustLastName = document.getElementById(i+"LN").value;
            var CustCity = document.getElementById(i+"City").value;
            //value = ID; text = "Nachname, Vorname (Ort)"
            var newCustomer = new Option(CustLastName+", "+CustFirstName+" ("+CustCity+")", CustID, false, true);
            SelectBox[0].options[SelectBox[0].length] = newCustomer;
            if(SelectBox[0].options[0].value==0)
            {
              SelectBox[0].options[0] = null;
            }
          }
        }
      }
    }
    //--------------------------------------------------------------------------
    function checkValue(ID)
    {
      var exist = false;
      for(var a=0; a<SelectBox[0].length; a++)
      {
        if(SelectBox[0].options[a].value==ID)
        {
          exist =  true;
        }
      }
      return exist;
    }
    //--------------------------------------------------------------------------
    function DelCustomer()
    {
      var selectCust = SelectBox[0].selectedIndex;
      SelectBox[0].options[selectCust] = null;
    }
    //--------------------------------------------------------------------------
    function ResetCustomer()
    {
      for(var a=0; a<SelectBox[0].length; a++)
      {
        SelectBox[0].length = 0;
      }
    }
    //--------------------------------------------------------------------------
    function submitData()
    {
      for(var a=0; a<SelectBox[0].length; a++)
      {
        SelectBox[0].options[a].selected = false;
      }
      var custtxt = "";
      var discount = document.frmDis.cDis.value;
      for(var a=0; a<SelectBox[0].length; a++)
      {
        custtxt = custtxt+"_"+SelectBox[0].options[a].value;
      }
      document.location = 'mod.discount.save.php?custtxt='+custtxt+'&cDis='+discount+'&lang=<?echo $lang;?>';
    }
    //--------------------------------------------------------------------------
    function navigation(val)
    {
      document.frmDis.start.value = val;
      var custtxt = "";
      for(var a=0; a<SelectBox[0].length; a++)
      {
        custtxt = custtxt+"#"+SelectBox[0].options[a].value;
      }
      document.frmDis.custtxt.value = custtxt;
      document.frmDis.submit();
    }
    //--------------------------------------------------------------------------
    function displayCusDetails(cusID)
    {
      dis = window.open('mod.discount.cusdetails.php?cusID='+cusID+'&lang=<?echo $lang;?>','my','toolbar=0,scrollbars,resizable=1,status=no,width=700,height=720');
      dis.focus();
    }
    </script>
</head>


<body>
<form name="frmDis" action="mod.discount.detail.php" method="post">
<?php
require_once("../../include/page.header.php");
?>

<div id="PGdiscountdetail">
	<input type="hidden" name="lang" value="<?php echo $lang; ?>">
	<input type="hidden" name="start" value="<?php echo $start; ?>">
	<input type="hidden" name="backstart" value="<?php echo $backstart; ?>">
	<input type="hidden" name="next" value="">
	<input type="hidden" name="nav" value="">
	<input type="hidden" name="custtxt" value="">
	<input type='hidden' name='act' value='a'>

<h1>&#187;&nbsp;<?php echo L_dynsb_CustomerDiscount;?>&nbsp;&#171;</h1>
<h2><?php echo L_dynsb_SelectedCustomers;?></h2>

<p>
<select name='customers[]' style='width:400px' size="10" multiple>
<?php
if(isset($_REQUEST['custtxt'])&&$_REQUEST['custtxt']!='leer')
{
  echo $_REQUEST['custtxt'];
  $custtxt = split("#",$_REQUEST['custtxt']);
  for($z=1; $z<sizeof($custtxt); $z++)
  {
    $sql = "SELECT * from ".DBToken."customer where cusIdNo = '".$custtxt[$z]."'";

    $qry = @mysqli_query($link,$sql);
    $num = @mysqli_num_rows($qry);
    $obj = @mysqli_fetch_object($qry);
    if($num!=0)
    {
      echo "<option value='".$obj->cusIdNo."'>".$obj->cusLastName.", ".$obj->cusFirstName." (".$obj->cusCity.")</option>";
    }
    else
    {
      echo "<option value='0'>leer</option>";
    }
  }
}
else
{
  echo "<option value='0'>leer</option>";
}
?>
</select>
<br />
<p>
	<input type="button" class="button" onclick="javascript:DelCustomer();" name="btn_upload" value="<?php echo L_dynsb_Delete;?>">
	<input type="button" class="button" onclick="javascript:ResetCustomer();" name="btn_upload"  value="<?php echo L_dynsb_Reset2?>">
</p>

<h2><?php echo L_dynsb_Discount;?></h2>
<p>
	<input type="text" class="small" value="<?php echo $cDis; ?>" name="cDis">&nbsp;%&nbsp;&nbsp;
	<input type="button" class="button" onclick="javascript:submitData();" name="btn_upload" value="<?php echo L_dynsb_Set;?>">
</p>



<h2><?php echo L_dynsb_Customers;?></h2>

<table class="searchresult">
<tr>
	<th>&nbsp;</th>
	<th><?php echo L_dynsb_CustomerNo?></th>
	<th><?php echo L_dynsb_Firm;?></th>
	<th><?php echo L_dynsb_Lastname;?></th>
	<th><?php echo L_dynsb_Firstname;?></th>
	<th><?php echo L_dynsb_City;?></th>
	<th><?php echo L_dynsb_Discount;?></th>
	<th><?php echo L_dynsb_Country;?></th>
</tr>
<?php
 $SQL = "SELECT * FROM ".DBToken."customer WHERE 1 = 1
             ORDER BY cusLastName ASC, cusFirstName ASC LIMIT ".$start.",".$limit;
 $qry = @mysqli_query($link,$SQL);
 $count=1;
 $i = 0;
 while ($obj = @mysqli_fetch_object($qry))
 {
    $i++;

    if(trim($obj->cusFirmname) == "")
    { $cusFirmname = "&nbsp;"; }
    else
    { $cusFirmname = trim($obj->cusFirmname); }
    if(trim($obj->cusDiscount)==""||trim($obj->cusDiscount)==null)
    { $cusDiscount = "0"; }
    else
    { $cusDiscount = trim($obj->cusDiscount); }
    if(trim($obj->cusId) == "")
    { $cusId = "&nbsp;"; }
    else
    { $cusId = trim($obj->cusId); }
    if(trim($obj->cusCountry) == "")
    { $cusCountry = "&nbsp;"; }
    else
    { $cusCountry = trim($obj->cusCountry); }

		if ($i % 2 != 0)
			$rowStyle = " odd ";
		else
			$rowStyle = " even ";
?>
  <tr id="d<?php echo $obj->cusIdNo;?>" class="<?php echo $rowStyle;?>" ondblclick="javascript:getElementById('<?php echo $count;?>Check').click()">
    <td>
      <input id='<?php echo $count;?>Check' type="checkbox" class="checkbox" style="cursor:pointer" name="pk[]" value="<?php echo $obj->cusIdNo;?>" onclick="javascript:checkAllData('frmDis');">
      <img src="../../image/view.gif" style="cursor:pointer" onclick='displayCusDetails(<?php echo $obj->cusIdNo; ?>);'  alt="<?php echo L_dynsb_ShowDetails;?>">
    </td>
    <td>
      <input type='hidden' id='<?php echo $count;?>ID' value='<?php echo $obj->cusIdNo;?>'>
      <?php echo $cusId;?>
    </td>
    <td><?php echo $cusFirmname;?></td>
    <td>
      <input type='hidden' id='<?php echo $count;?>FN' value='<?php echo $obj->cusFirstName;?>'>
      <?php echo $obj->cusLastName;?>
    </td>
    <td>
      <input type='hidden' id='<?php echo $count;?>LN' value='<?php echo $obj->cusLastName;?>'>
      <?php echo $obj->cusFirstName;?>
    </td>
    <td><?php echo $obj->cusCity;?></td>
    <td align='right'>
      <input type='hidden' id='<?php echo $count;?>City' value='<?php echo $obj->cusCity;?>'>
      <?php echo $obj->cusDiscount;?>%
    </td>
    <td><?php echo $cusCountry;?></td>
  </tr>
  <?php
  $count++;
  } // end of while
  ?>
</table>

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
<br />
<h2>&nbsp;</h2>

<!-- navigation // -->
<table>
  <tr>
    <td>
        <input type="checkbox" class="checkbox" name="alldata" value="alldata" onClick="selectAllData('frmDis');"> <?php echo L_dynsb_All;?>
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
	<input type="button" class="button small<?php echo $bStatus;?>" onclick="javascript:navigation('0');" name="btn_next" value="|<--"<?php echo $bStatus;?>>
	<input type="button" class="button small<?php echo $bStatus;?>" onclick="javascript:navigation(<?php echo $newStartPrev;?>);" name="btn_end" value="<--"<?php echo $bStatus;?>>

<?php
	if ($start + $limit < $total) {
		$newStartNext = $start + $limit;
		$newStartLast = (truncate($total/$limit) * $limit);
		}
	else
		$bStatus = " disabled ";
?>
		<input type="button" class="button small<?php echo $bStatus;?>" onclick="javascript:navigation(<?php echo $newStartNext;?>);" name="btn_next" value="-->"<?php echo $bStatus;?>>
		<input type="button" class="button small<?php echo $bStatus;?>" onclick="javascript:navigation(<?php echo $newStartLast;?>);" name="btn_end" value="-->|"<?php echo $bStatus;?>>
<?php echo $strTmp ?>
		</td>
  </tr>

	<tr>
    <td><?php if ($total > 0) { ?><img src="../../image/arrow.gif" width="35" height="15" alt=""><?php } else echo "&nbsp;"; ?></td>
    <td><?php if ($total > 0) { ?><input type="button" class="button" onclick="javascript:selectCust(<?php echo $limit;?>);" name="btn_del" value="<?php echo L_dynsb_Select;?>"><?php } else echo "&nbsp;"; ?></td>
  </tr>
</table>

</div>
<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
