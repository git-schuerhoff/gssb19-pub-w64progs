<?php
/******************************************************************************/
/* File: mod.orderemail.select.file.php                                       */
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
  or die("<br />aborted: can�t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");
?>
<html>
    <head>
        <title>
            <?echo L_dynsb_ChooseImage;?>
        </title>
        <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
        <meta content="de" http-equiv="Language">
        <meta name="author" content="GS Software Solutions GmbH">
        <link rel="stylesheet" type="text/css" href="../../css/link.css">
        <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
<script language="JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
function startUpload() {
document.frmLogo.submit();
}
// -->
</script>
    </head>
    <form name="frmLogo" enctype="multipart/form-data" action="mod.orderemail.upload.file.php" method="post">
        <input type="hidden" name="MAX_FILE_SIZE" value="30000000">
        <table border="0" class="frame" cellspacing="0" cellpadding="0" width="380">
            <tr>
                <td colspan="2" height="22" class="tablecolor1_bb">
                    <?echo L_dynsb_FileUpload;?>
                </td>
            </tr>
            <tr>
                <td colspan="2" height="44" class="select_normal_title2">
                    &nbsp;&nbsp;<?echo L_dynsb_PleaseChooseImageJPGorGIForPNG;?>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="tablecolor2">
                    &nbsp;&nbsp;<input class="inputbox350_eingabe" name="userfile" type="file">
                </td>
            </tr>
            <tr>
                <td height="22" colspan="2" class="tablecolor2">
                    &nbsp;
                </td>
            </tr>
            <tr>
                <td class="tablecolor1_tb" align="left">
                    <a href="javascript:startUpload();" name="btn_upload" class="button100"><?php echo L_dynsb_Transfer;?></a>
                </td>
                <td class="tablecolor1_tb" align="right" >
                    <a href="javascript:window.close();" name="btn_close" class="button100"><?php echo L_dynsb_Cancel;?></a>
                </td>
            </tr>
        </table>
    </form>
</html>
