<?php
/******************************************************************************/
/* File: mod.customernews.select.file.php                                     */
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
if(isset($_REQUEST['nid'])) {
    $nid = intval($_REQUEST['nid']);
} else {
    die("ERROR - missing parameter!");
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title>
      <?php echo L_dynsb_ChooseImage;?>
    </title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
    <script language="JavaScript" type="text/javascript">
    function MM_reloadPage(init)
    {  //reloads the window if Nav4 resized
      if (init==true) with (navigator) {
        if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
          document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage;
        }
      }
      else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
    }
    //--------------------------------------------------------------------------
    MM_reloadPage(true);
    //--------------------------------------------------------------------------
    function startUpload()	{
      document.frmLogo.submit();
    }
    </script>
    </head>
<form name="frmLogo" enctype="multipart/form-data" action="mod.customernews.upload.file.php" method="post">
<?php
require_once("../../include/page.header.php");
?>

<div id="PGcustomernewsfile">
  <input type="hidden" name="lang" value="<?php echo $lang;?>">
  <input type="hidden" name="MAX_FILE_SIZE" value="30000000">
  <input type="hidden" name="nid" value="<?php echo $nid;?>">

<h1><?php echo L_dynsb_FileUpload?></h1>
<p><?php echo L_dynsb_PleaseChooseImageJPGorGIF?></p>
<p><input name="userfile" type="file"></p>

<div class="footer">
	<input type="button" class="button" onclick="javascript:startUpload();" value="<?php echo L_dynsb_Transfer;?>">
	<input type="button" class="button" onclick="javascript:window.close();" value="<?php echo L_dynsb_Cancel;?>">
</div>
</div>

<?php
require_once("../../include/page.footer.php");
?>
</form>
</html>
