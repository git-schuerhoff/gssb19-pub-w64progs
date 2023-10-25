<?php
/******************************************************************************/
/* File: mod.gssalevalue.run.php                                              */
/******************************************************************************/

require("../../../../include/login.check.inc.php");
require_once("../../../../include/functions.inc.php");
require("../../../../../conf/db.const.inc.php");

/***************** Sprachdatei ************************************************/
if (!isset($_REQUEST['lang']) || strlen(trim($_REQUEST['lang'])) == 0)
{
    $lang = "deu";
}
else
{
	$lang = $_REQUEST['lang'];
	if(!file_exists("../../../../lang/lang_".$lang.".php"))
  {
    $lang = "deu";
  }
}

include("../../../../lang/lang_".$lang.".php");
/******************************************************************************/

/***************** Datenbankverbindung*****************************************/
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase)
  or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");
if(isset($_REQUEST['start'])) {
    $start = intval($_REQUEST['start']);
}
if(isset($_REQUEST['pk'])) {
    $setIdNo = intval($_REQUEST['pk']);
} else {
    $setIdNo = 1;
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
if (!isset($_SESSION['SESS_Currency']) || strlen(trim($_SESSION['SESS_Currency'])) == 0) {
  die ("<br />error: missing session parameter!<br />");
} else {
	$SESS_Currency = $_SESSION['SESS_Currency'];
}

// create variables
foreach($_POST as $key => $value) {
    $$key = trim($value);
}

if(!isset($xsize) || !isset($ysize)) {
    $picsize = '400x100';
    $xsize = 400;
    $ysize = 100;
    $layout = 0;
    $viewmode = 0;
}

if(!isset($statStartDate)) {
    $statStartDate = date("Ym")."01000000";
    $startYear = date("Y");
    $startMonth = date("m");
} else {
    $aTmp = explode(".", $statStartDate);
    $statStartDate = $aTmp[2].$aTmp[1]."01000000";
    $startYear = $aTmp[2];
    $startMonth = $aTmp[1];
}
if(!isset($statEndDate)) {
    $statEndDate = date("Ym")."31235959";
    $endYear = date("Y");
    $endMonth = date("m");
} else {
    $aTmp = explode(".", $statEndDate);
    $statEndDate = $aTmp[2].$aTmp[1]."31235959";
    $endYear = $aTmp[2];
    $endMonth = $aTmp[1];
}


$limit = 10;

$data = array();
$ordercount = array(); // number of orders per month
$c = 1;
for($x = $startYear; $x <= $endYear; $x++) {
    if($startYear == $endYear) {
        $stop = $endMonth;
        $start = $startMonth;
    } else {
        $stop = 12;
        $start = 1;
        if($x == $endYear) $stop = $endMonth;
        if($x == $startYear) $start = $startMonth;
    }
    for($i = $start; $i <= $stop; $i++) {
        //echo $c++." - ".getmonth($i, 1)."/".$x."<br />";
        $zero = "";
        if(strlen($i) < 2) $zero = "0";
        $SQL = "SELECT SUM(ordTotalValueAfterDsc2) AS total FROM ".DBToken."order WHERE
                        ordDate >= '".$x.$zero.$i."01000000' AND
                        ordDate <= '".$x.$zero.$i."31235959' AND
                        ordChgHistoryFlg <> '0'";

        $qry = @mysqli_query($link, $SQL);
        $obj = @mysqli_fetch_object($qry);
        $data[$x.$zero.$i] = $obj->total;

        $SQLo = "SELECT COUNT(ordIdNo) AS orderqty FROM ".DBToken."order WHERE
                        ordDate >= '".$x.$zero.$i."01000000' AND
                        ordDate <= '".$x.$zero.$i."31235959' AND
                        ordChgHistoryFlg <> '0'";

        $qryo = @mysqli_query($link, $SQLo);
        $objo = @mysqli_fetch_object($qryo);
        $ordercount[$x.$zero.$i] = $objo->orderqty;

    }
}
$thisYear = date("Y");

$months = array (L_dynsb_month01,
				 L_dynsb_month02,
				 L_dynsb_month03,
				 L_dynsb_month04,
				 L_dynsb_month05,
				 L_dynsb_month06,
				 L_dynsb_month07,
				 L_dynsb_month08,
				 L_dynsb_month09,
				 L_dynsb_month10,
				 L_dynsb_month11,
				 L_dynsb_month12);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_VolumeOfSales;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../../../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
    <script type="text/javascript" src="../../../../js/gslib.php"></script>
    <script type="text/javascript" src="../../../../js/gsdate.php"></script>
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
		  //----------------------------------------------------------------------------
		  MM_reloadPage(true);
		  //----------------------------------------------------------------------------
		  function refreshLayout()
		  {
		    aSize = document.frmGSsalevalue.picsize.value.split('x');
		    document.frmGSsalevalue.xsize.value = aSize[0];
		    document.frmGSsalevalue.ysize.value = aSize[1];
		    updateStartDate();
		    updateEndDate();
		    iError = checkDateFilter();
		    switch(iError)
		    {
		        case 0:
		            submitForm('frmGSsalevalue');
		        break;
		        case 1:
		            alert('<?php echo L_dynsb_EndWithSmallerStartFrom;?>');
		        break;
		        case 2:
		            alert('<?php echo L_dynsb_MaximumViewableLimited36Months;?>');
		        break;
		        default:
		            alert('<?php echo L_dynsb_UnknownError;?>');
		        break;
		    }
		  }
		  //----------------------------------------------------------------------------
		  function showDetail(t)
		  {
			 var x = 0;
			 var y = 0;
			 var winBreite = 550;
			 var winHoehe = 600;
			 x = Math.round((screen.width-winBreite)/2);
			 y = Math.round((screen.height-winHoehe)/2);
		   var helpwin=window.open('mod.gssalevalue.detail.php?t='+t+'&lang=<?echo $lang;?>','help','left='+x+',top='+y+',width='+winBreite+',height='+winHoehe+',scrollbars=no,resizable');
			 helpwin.focus();
		  }
	  </script>
</head>
<body>
<form name="frmGSsalevalue" action="mod.gssalevalue.run.php" method="post">
<?php
require_once("../../../../include/page.header.php");
?>

<div id="PGgsalevalue">
	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="start" value="<?php echo $start;?>">
	<input type="hidden" name="setIdNo" value="<?php echo $setIdNo;?>">
	<input type="hidden" name="act" value="<?php echo $act;?>">

<h1>&#187;&nbsp;<?php echo L_dynsb_VolumeOfSales;?>&nbsp;&#171;</h1>

<table>
  <tr>
    <td align="right"><?php echo L_dynsb_ImageSize;?>&nbsp;</td>
    <td>
	    <select name="picsize">
	        <option value="400x100" <?php if($picsize=='400x100') echo "selected";?>><?php echo "400 x 100 ".L_dynsb_Pixel;?></option>
	        <option value="500x180" <?php if($picsize=='500x180') echo "selected";?>><?php echo "500 x 180 ".L_dynsb_Pixel;?></option>
	        <option value="630x240" <?php if($picsize=='630x240') echo "selected";?>><?php echo "630 x 240 ".L_dynsb_Pixel;?></option>
	        <option value="630x630" <?php if($picsize=='630x630') echo "selected";?>><?php echo "630 x 630 ".L_dynsb_Pixel;?></option>
	    </select>
	    <input type="hidden" value="<?php echo $xsize;?>" name="xsize">
	    <input type="hidden" value="<?php echo $ysize;?>" name="ysize">
    </td>
    <td align="right"><?php echo L_dynsb_StartFrom;?>:&nbsp;</td>
    <td>
	    <input type="hidden" value="<?php echo timestamp_mysql2german($statStartDate);?>" name="statStartDate">
	    <select name="startMonth">
<?php
				for($i = 1; $i <= 12; $i++) {
				  if ($startMonth == $i) {
				  	$selected = "selected";
				  }
				  else {
				  	$selected = "";
				  }
				  echo "<option value=\"".($i)."\" ".$selected.">".$months[$i-1]."</option>";
				}
?>
	    </select>
	    <select name="startYear">
<?php
			for($i = ($thisYear-5); $i <= ($thisYear+5); $i++) {
				($startYear == $i) ? $selected = "selected" : $selected = "";
				echo "<option value=\"".($i)."\" ".$selected.">".$i."</option>";
			}
?>
	    </select>
    </td>
  </tr>
  <tr>
    <td align="right"><?php echo L_dynsb_PictureLayout;?>:&nbsp;</td>
    <td>
	    <select name="layout">
	        <option value="0" <?php if($layout==0) echo "selected";?>><?php echo L_dynsb_Horizontal;?></option>
	        <option value="1" <?php if($layout==1) echo "selected";?>><?php echo L_dynsb_Vertical;?></option>
	    </select>
    </td>
    <td align="right"><?php echo L_dynsb_EndWith;?>:&nbsp;</td>
    <td>
	    <input type="hidden" value="<?php echo timestamp_mysql2german($statEndDate);?>" name="statEndDate">
			<select name="endMonth">
<?php
				for($i = 1; $i <= 12; $i++) {
	      	(($endMonth) == $i) ? $selected = "selected" : $selected = "";
	        	echo "<option value=\"".($i)."\" ".$selected.">".$months[$i-1]."</option>";
				}
?>
    	</select>

    <select name="endYear">
<?php
		  for($i = ($thisYear-5); $i <= ($thisYear+5); $i++) {
		      ($endYear == $i) ? $selected = "selected" : $selected = "";
		      echo "<option value=\"".($i)."\" ".$selected.">".$i."</option>";
		  }
?>
		</select>
	</td>
</tr>
<tr>
	<td align="right"><?php echo L_dynsb_BarLayout;?>&nbsp;</td>
	<td>
		<select name="barlayout">
			<option value="0" <?php if($barlayout==0) echo "selected";?>><?php echo L_dynsb_SimpleShaded;?></option>
			<option value="1" <?php if($barlayout==1) echo "selected";?>><?php echo L_dynsb_Gradient;?></option>
		</select>
	</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
		<td>&nbsp;</td>
		<td><input type="button" class="button" onclick="javascript:refreshLayout();" value="<?php echo L_dynsb_Refresh;?>"></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>

<h2><?php echo L_dynsb_Diagram;?></h2>
<div class="diagram">
	<img src="mod.gssalevalue.show.php?xsize=<?php echo $xsize."&amp;ysize=".$ysize."&amp;layout=".$layout."&amp;bl=".$barlayout."&amp;sd=".$statStartDate."&amp;ed=".$statEndDate."&amp;lang=".$lang;?>" usemap="#stat" class="dia" alt="<?php echo L_dynsb_Diagram;?>">
	<map name="stat">
<?php
	if($layout == 0) {
		$stepx = $xsize / count($data);
		$offsetx = 60;
		$offsety = 80;
		$y1 = 0 + $offsety;
		$y2 = $ysize + $offsety;
	} else {
		$stepy = $xsize / count($data);
		$offsetx = 60;
		$offsety = 80;
		$x1 = 0 + $offsetx;
		$x2 = $ysize + $offsetx;
	}
	$cm = $startMonth;
	$cy = $startYear;
	$i = 0;
	foreach($data as $key => $value) {
		if($layout == 0) {
			$x1 = ($i * $stepx) + $offsetx;
			$x2 = (($i+1) * $stepx) + $offsetx;
		} else {
			$y1 = $offsety + $xsize - ($i * $stepy);
			$y2 = $offsety + $xsize - (($i+1) * $stepy);
		}
		$mon = getmonth(substr($key, 4, 2), $SESS_languageIdNo);
		$mon = str_replace("ä","%E4",$mon);
		$year = substr($key, 0, 4);
		echo "<area shape=\"rect\" coords=\"$x1,$y1,$x2,$y2\" href=\"javascript:showDetail('".$key."');\" onMouseOver=\"javascript:this.T_SHADOWCOLOR='#777788';this.T_SHADOWWIDTH=3;this.T_TITLE='".$mon." ".$year."';return escape('<table><tr><td align=right>".L_dynsb_NumberOfOrder.":</td><td><b>".$ordercount[$key]."</b></td><td>&nbsp;</td></tr><tr><td align=right>".L_dynsb_Total.":</td><td><b>".$value."</b></td><td>".$SESS_Currency."</td></tr></table>');\" alt=\"\">\n";
		$i++;
	}
?>
	</map>
</div>
<h2>&nbsp;</h2>

<table>
<tr>
	<th align="right"><?php echo L_dynsb_NumberOfOrder;?></th>
	<th align="right"><?php echo L_dynsb_Total;?></th>
	<th><?php echo L_dynsb_Period;?></th>

<?php
$x = 0;
foreach($data as $key => $value) {
	$x++;

  if ($x % 2 != 0)
		$rowStyle = " odd ";
	else
		$rowStyle = " even ";

  $mon = getmonth(substr($key, 4, 2), $SESS_languageIdNo);
  $year = substr($key, 0, 4);
  echo "<tr class=\"$rowStyle\">" .
  		"	<td align=\"right\">".$ordercount[$key]."</td>" .
  		"	<td align=\"right\">".replPtC(sprintf("%01.2f",$value))."</td>" .
  		"	<td	><a href=\"javascript:showDetail('".$key."');\">".$mon." ".$year."</a></td>" .
  		"</tr>\n";
}
?>
</table>
<br />

<!-- navigation // -->
<div class="footer">
  <input type="button" class="button" onclick="javascript:refreshLayout();" value="<?php echo L_dynsb_Refresh;?>">
</div>
</div>
<?php
require_once("../../../../include/page.footer.php");
?>
<script type="text/javascript" src="../../../../js/wz_tooltip.js"></script>
</form>
</body>
</html>
