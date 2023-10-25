<?php
/******************************************************************************/
/* File: mod.gsarticleviews.run.php                                           */
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

foreach($_REQUEST as $key => $value)
{
    $$key = trim($value);
}

if(!isset($xsize) || !isset($ysize))
{
    $picsize = '400x100';
    $xsize = 400;
    $ysize = 100;
    $layout = 0;
    $viewmode = 0;
}

if(!isset($statStartDate)) {
    $statStartDate = date("Ymd")."000000";
} else {
    $aTmp = explode(".", $statStartDate);
    $statStartDate = $aTmp[2].$aTmp[1].$aTmp[0]."000000";
}
if(!isset($statEndDate)) {
    $statEndDate = date("Ymd")."235959";
} else {
    $aTmp = explode(".", $statEndDate);
    $statEndDate = $aTmp[2].$aTmp[1].$aTmp[0]."235959";
}

switch($viewmode) {
    case 0:
        // top 10
        $limit = 10;
    break;

    case 1:
        // top 20
        $limit = 20;
    break;

    case 2:
        // top 50
        $limit = 50;
    break;
}

$SQL = "SELECT monActionIdNo, monValue,  COUNT(*) AS qty FROM ".DBToken."monitorlog WHERE
            monChgTimestamp >= '".$statStartDate."' AND
            monChgTimestamp <= '".$statEndDate."' AND
            monActionIdNo = '2'
        GROUP BY monActionIdNo, monValue
        ORDER BY qty DESC LIMIT 0,".$limit;
$qry = @mysqli_query($link, $SQL);

$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_ArticleView;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../../../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
    <script type="text/javascript" src="../../../../js/gslib.php"></script>
		<script type="text/javascript" src="../../../../js/calendar.js"></script>
		<script type="text/javascript" src="../../../../js/calendar-<?php echo $strcal;?>.js"></script>
		<script type="text/javascript" src="../../../../js/calendar-setup.js"></script>

<script language="JavaScript" type="text/javascript">
<!--

//------------------------------------------------------------------------------
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);

function refreshLayout() {
    aSize = document.frmGSarticleviews.picsize.value.split('x');
    document.frmGSarticleviews.xsize.value = aSize[0];
    document.frmGSarticleviews.ysize.value = aSize[1];
    submitForm('frmGSarticleviews');
}

// -->
</script>
</head>
<body>
<form name="frmGSarticleviews" action="mod.gsarticleviews.run.php" method="post">
<?php
require_once("../../../../include/page.header.php");
?>

<div id="PGgsarticleviews">
	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="start" value="<?php echo $start;?>">
	<input type="hidden" name="setIdNo" value="<?php echo $setIdNo;?>">
	<input type="hidden" name="act" value="<?php echo $act;?>">


<h1>&#187;&nbsp;<?php echo L_dynsb_ArticleView;?>&nbsp;&#171;</h1>

<?php
	//Parameterwahl inkludieren
	require_once("../../../../include/inc.statistics.parameters.php");
?>

<h2><?php echo L_dynsb_Diagram;?></h2>

<div class="diagram">
	<img src="mod.gsarticleviews.show.php?xsize=<?php echo $xsize."&amp;ysize=".$ysize."&amp;layout=".$layout."&amp;vm=".$viewmode."&amp;bl=".$barlayout."&amp;sd=".$statStartDate."&amp;ed=".$statEndDate."&amp;lang=".$lang;?>" usemap="#stat" class="dia" alt="<?php echo L_dynsb_Diagram;?>">
	<map name="stat">
<?php
  $i = 0;
  if($layout == 0) {
      $stepx = $xsize / $limit;
      $offsetx = 60;
      $offsety = 80;
      $y1 = 0 + $offsety;
      $y2 = $ysize + $offsety;
  } else {
      $stepy = $xsize / $limit;
      $offsetx = 60;
      $offsety = 80;
      $x1 = 0 + $offsetx;
      $x2 = $ysize + $offsetx;
  }
  while($obj = @mysqli_fetch_object($qry)) {
      if($layout == 0) {
          $x1 = ($i * $stepx) + $offsetx;
          $x2 = (($i+1) * $stepx) + $offsetx;
      } else {
          $y1 = $offsety + $xsize - ($i * $stepy);
          $y2 = $offsety + $xsize - (($i+1) * $stepy);
      }
      echo "<area shape=\"rect\" coords=\"$x1,$y1,$x2,$y2\" onMouseOver=\"javascript:this.T_SHADOWCOLOR='#777788';this.T_SHADOWWIDTH=3;this.T_TITLE='Top ".($i+1)."';return escape('<table><tr><td align=right>".L_dynsb_Article.":</td><td><b>".$obj->monValue."</b></td></tr><tr><td align=right>".L_dynsb_HitQuantity.":</td><td><b>".$obj->qty."</b></td></tr></table>');\">\n";
      $i++;
  }
  if($i > 0) mysqli_data_seek($qry, 0);
  ?>
		</map>
</div>


<h2>&nbsp;</h2>
<table>
  <tr>
  	<th align="right"><?php echo L_dynsb_Rank;?></th>
  	<th><?php echo L_dynsb_Article;?></th>
  	<th align="right"><?php echo L_dynsb_HitQuantity;?></th>
  </tr>

<?php
  $x = 1;
  while($obj = @mysqli_fetch_object($qry)) {
		if ($x % 2 != 0)
			$rowStyle = " odd ";
		else
			$rowStyle = " even ";

		echo "<tr class=\"$rowStyle\">" .
				 "	<td align=\"right\">".$x++."&nbsp;</td>" .
				 "	<td>".$obj->monValue."</td>" .
				 "	<td align=\"right\">".$obj->qty."</td>" .
				 "</tr>\n";
  }
  ?>
</table>
<br />

<!-- navigation // -->
<div class="footer">
	<input type="button" class="button" onclick="javascript:refreshLayout();" name="btn_refresh2" value="<?php echo L_dynsb_Refresh;?>">
</div>
</div>
<?php
require_once("../../../../include/page.footer.php");
?>

</form>
<script type="text/javascript" src="../../../../js/wz_tooltip.js"></script>
</body>
</html>
