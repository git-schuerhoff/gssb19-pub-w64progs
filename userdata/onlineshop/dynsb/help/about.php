<?php
/*
 file: about.php
*/

// checks authorization of the user
require("../include/login.check.inc.php");
require("../../conf/db.const.inc.php");
require_once("../include/functions.inc.php");

// connect to database server or die
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");
// select database or die

if (!isset($_REQUEST['lang']) || strlen(trim($_REQUEST['lang'])) == 0)
{
    $lang = "deu";
}
else
{
	$lang = $_REQUEST['lang'];
	if(!file_exists("../lang/lang_".$lang.".php"))
  {
    $lang = "deu";
  }
}

include("../lang/lang_".$lang.".php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
  <title>about</title>
  <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
  <meta content="de" http-equiv="Language">
  <meta name="author" content="GS Software AG">
  <link rel="stylesheet" type="text/css" href="../css/link.css">
  <link rel="copyright" href="http://www.gs-software.de" title="(c) 2003 GS Software AG">
  <script language="javascript" src="../js/gshide.js"></script>
</head>
<BODY style="background-image:url(../image/hint.jpg); background-repeat:no-repeat; background-color:white">


<div style="position:absolute; left:582px; top:270px; width:450px; height:20px; z-index:12">
	  <div class="kontakt">
      <div class="boldtext10"><b><?php echo L_dynsb_Contact;?>:</b></div>
      GS Software AG <br />
      Johann-Krane-Weg 8<br />
	  D-48149 Münster<br />
      Tel. +49 (0)231 975077-0<br />
      Fax. +49 (0)231 975077-14<br />
      eMail: <a href="mailto:info@gs-software.de">info@gs-software.de</a><br />
      <a href="http://www.gs-software.de" target="_blank">http://www.gs-software.de</a><br />
      <br />
      <div class="boldtext10"><b><?php echo  L_dynsb_Domain;?>:</b></div>
      <?php echo $_SERVER['HTTP_HOST'];?><br />
    </div>
</div>


<div style="position:absolute; left:352px; top:110px; width:420px; height:20px; z-index:12">
<table width="100%" border="0" class='frame' cellspacing="0" cellpadding="2">
<tr class="about_bgr">
  <td align="center">
    <img src="../image/logo_small.png" width="64" height="64" align="middle">
  </td>
  <td align="center">
    <h2>GS Software dynamic ShopBuilder Extensions</h2>
  </td>
</tr>
</table>
</div>


</body>
</html>
