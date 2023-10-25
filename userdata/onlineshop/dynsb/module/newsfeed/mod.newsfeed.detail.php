<?php
/******************************************************************************/
/* File: mod.newsfeed.detail.php                                           */
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
    $nfIdNo = intval($_REQUEST['pk']);
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

/*
if(strtolower($act) == "e")
{
    $qrySQL = "SELECT * FROM dsb6_couponWHERE coupId = '".$coupId."'";
    $qry = @mysqli_query($qrySQL);
    $obj = @mysqli_fetch_object($qry);

    foreach($obj as $key => $value)
    {
        $$key = trim($value);
    }
}
*/
$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo "Newsfeedsearch-title"/*=L_dynsb_DataImport;*/?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2009 GS Software Solutions AG">
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
    </script>
</head>
<body>
<form name="frmEdit" action="mod.newsfeed.save.php" method="post">
<input type="hidden" name="start" value="<?php echo $start; ?>">
<?php
require_once("../../include/page.header.php");
?>
<div id="PGdataimportdetail">
	<input type='hidden' name='lang' value='<?echo $lang;?>'>
	<input type='hidden' name='act' value='<?echo $act;?>'>
	<input type='hidden' name='nfIdNo' value='<?echo $nfIdNo?>'>

<h1>&#187;&nbsp;<?php echo L_dynsb_Newsfeed;?>&nbsp;&#171;</h1>
<h2><?php echo L_dynsb_nfEditDetails;?></h2>


<?php

$nfTitle="";
$nfDescription="";
$nfLink="";
$nfDurationdays="7";  // 7 Tage newsletter anzeigedauer vorschlagen. wenn editmodus, wird das mit db-wert �hrieben

if($act=="e" && $nfIdNo != "")
{
  $qrySQL = "SELECT * FROM ".DBToken."newsfeed
                 WHERE nfIdNo = $nfIdNo 
            ";
                  
  $qry = @mysqli_query($link,$qrySQL);      
  $obj = @mysqli_fetch_object($qry);
      
  $nfTitle = $obj->nfTitle;
  $nfDescription = $obj->nfDescription;
  $nfLink = $obj->nfLink;
  $nfDurationdays = $obj->nfDurationdays;  
}
?>
<table>
	<tr>
	  <td><?php echo L_dynsb_nfTitle;?>:&nbsp;</td>
	  <td><input type="text" size="140" value="<?php echo $nfTitle;?>" name="nfTitle"></td>
	</tr>
	<tr>
	  <td><?php echo L_dynsb_nfDescription;?>:&nbsp;</td>
	  <td><textarea name="nfDescription" wrap="physical" cols="116" rows="10"><?php echo $nfDescription;?></textarea></td>
	</tr>
	<tr>
	  <td><?php echo L_dynsb_nfLink;?>:&nbsp;</td>
	  <td><input type="text" size="140" value="<?php echo $nfLink?>" name="nfLink"></td>
	</tr>
  <tr>
	  <td><?php echo L_dynsb_nfDurationdays;?>:&nbsp;</td>
	  <td><input type="text" size="6" value="<?php echo $nfDurationdays;?>" name="nfDurationdays"></td>
	</tr>
  <tr>
    <td colspan="20">
      <?php echo L_dynsb_nfLinkInfotext;?> 
    </td>
  </tr>		
</table>

<div class="footer">
	<input type="button" class="button" onclick="javascript:submitForm('frmEdit');" name="btn_save" value="<?php echo L_dynsb_Save;?>">
	<input type="button" class="button" onClick="window.location='mod.newsfeed.search.php?start=0';" value="Abbrechen">
</div>

</div>
<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
