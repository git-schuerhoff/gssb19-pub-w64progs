<?php

require("../include/login.check.inc.php");
require_once("../include/functions.inc.php");

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

<html>
<head>
<title><?php echo L_dynsb_TitleExportVerz_ausw;?></title>
      <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
      <meta content="de" http-equiv="Language">
      <meta name="author" content="GS Software AG">
      <link rel="stylesheet" type="text/css" href="../css/link.css">
      <link rel="copyright" href="http://www.gs-software.de" title="(c) 2012 GS Software AG">
<script language="JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);

function startUpload() {
    val= document.dirpath.expdir.value;
    val = val+"\\ ";
	opener.document.exptop.expdir.value=val;
    window.close();
}

// -->
</script>

</head>
<form name="dirpath"  method="post">

  <br />
  <br />

<table border="0" class="frame" cellspacing="2" cellpadding="2" width="480">
<tr><td colspan="2" height="22" class="tablecolor1">
<?php echo L_dynsb_ExportVerzeichnis_eingeben;?>
</td></tr>
<tr><td colspan="2" height="44" class="tablecolor2">

</td></tr>
<tr><td colspan="2" class="tablecolor2">
<input class="xx-large" name="expdir">
</td></tr>
<tr><td height="22" colspan="2" class="tablecolor2">
&nbsp;
  <br />
  <br />
  <br />
  <br />
</td></tr>
<tr><td class="tablecolor1" align="left">
<input type="button" class="button" onclick="javascript:startUpload();" value="<?php echo L_dynsb_Uebertragen;?>">
</td><td class="tablecolor1" align="right" >
<input type="button" class="button" onclick="javascript:window.close();" value="<?php echo L_dynsb_Abbrechen;?>">
</td></tr>
</table>
</form>
</html>
