<?php
/******************************************************************************/
/* File: settings.change.php                                                  */
/******************************************************************************/

require("../include/login.check.inc.php");
require_once("../include/functions.inc.php");
require("../../conf/db.const.inc.php");

/***************** Sprachdatei ************************************************/
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

/******************************************************************************/

/***************** Datenbankverbindung*****************************************/
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase)
  or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");
$chgApplicId = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1)); // script name

if (!isset($_SESSION['SESS_userIdNo']) || strlen(trim($_SESSION['SESS_userIdNo'])) == 0) {
  die ("<br />error1: missing session parameter!<br />");
} else {
	$SESS_userIdNo = $_SESSION['SESS_userIdNo'];
}
if (!isset($_SESSION['SESS_userLogin']) || strlen(trim($_SESSION['SESS_userLogin'])) == 0) {
  die ("<br />error2: missing session parameter!<br />");
} else {
	$SESS_userId = $_SESSION['SESS_userId'];
}
if (!isset($_SESSION['SESS_languageIdNo']) || strlen(trim($_SESSION['SESS_languageIdNo'])) == 0) {
  die ("<br />error3: missing session parameter!<br />");
} else {
	$SESS_languageIdNo = $_SESSION['SESS_languageIdNo'];
}

$startDate = $strtime."01000000";
$endDate = $strtime."31235959";

$SQL = "SELECT * FROM ".DBToken."user WHERE userIdNo = '".$SESS_userIdNo."'";
$qry = @mysqli_query($link, $SQL);
$obj = @mysqli_fetch_object($qry);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_UserData;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../css/link.css">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
    <script type="text/javascript" src="../js/gslib.php"></script>

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
    function startCheck()
    {
      var iError = 0;
      if(document.frmChangeLogin.newuname.value == "") iError = 1;
      if(document.frmChangeLogin.newpw.value == "") iError = 2;
      if(document.frmChangeLogin.newpwr.value == "") iError = 3;
      if(document.frmChangeLogin.newpw.value != document.frmChangeLogin.newpwr.value) iError = 4;
      if(document.frmChangeLogin.actpw.value == "") iError = 5;
      if(iError == 0)
      {
        document.frmChangeLogin.submit();
      }
      else
      {
        displayError(iError);
      }
    }
    //--------------------------------------------------------------------------
    function displayError(val)
    {
      switch(val)
      {
        case 1:
            alert("<?php echo L_dynsb_NoUserData;?>");
        break;
        case 2:
            alert("<?php echo L_dynsb_NoPassword;?>");
        break;
        case 3:
            alert("<?php echo L_dynsb_NoPasswordRepeat;?>");
        break;
        case 4:
            alert("<?php echo L_dynsb_NewPasswordNoEqualPasswordRepeat;?>");
        break;
        case 5:
            alert("<?php echo L_dynsb_NoCurrentPassword;?>");
        break;
      }
    }
    </script>
</head>
<body>
<form name="frmChangeLogin" action="settings.user.save.php" method="post">
<?php
require_once("../include/page.header.php");
?>

<div id="PGsettingschange">
<input type="hidden" name="lang" value="<?php echo $lang;?>">
<input type="hidden" name="userIdNo" value="<?php echo $SESS_userIdNo;?>">
<h1><?php echo L_dynsb_UserData;?></h1>

<table class="de bug">
   <tr>
    <td align="right"><?php echo L_dynsb_Username;?>:</td>
    <td><?php echo trim($obj->userLogin);?></td>
  </tr>
   <tr>
    <td align="right"><?php echo L_dynsb_NewUsername;?>:</td>
    <td><input type="text" maxlength="16" value="<?php echo trim($obj->userLogin);?>" name="newuname"></td>
  </tr>
   <tr>
    <td align="right"><?php echo L_dynsb_NewPassword;?>:</td>
    <td><input type="password"  maxlength="16" value="" name="newpw"></td>
  </tr>
   <tr>
    <td align="right"><?php echo L_dynsb_PasswordRepeat;?>:</td>
    <td><input type="password" maxlength="16" value="" name="newpwr"></td>
  </tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td align="right"><?php echo L_dynsb_OldPassword;?>:</td>
		<td><input type="password" maxlength="16" value="" name="actpw"></td>
	</tr>
</table>

<div class="footer">
	<input type="button" class="button" onclick="javascript:startCheck();" name="btn_refresh" value="<?php echo L_dynsb_Save;?>">
	<input type="button" class="button" onclick="javascript:window.self.close();" name="btn_close" value="<?php echo L_dynsb_Cancel;?>">
</div>
</div>
<?php
require_once("../include/page.footer.php");
?>
</form>
</body>
</html>
