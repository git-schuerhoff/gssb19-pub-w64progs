<?php
/******************************************************************************/
/* File: settings.user.save.php                                               */
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
if(isset($_REQUEST['userIdNo'])) {
    $userIdNo = intval($_REQUEST['userIdNo']);
} else {
    die("error - missing post parameter");
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

$apw = md5($actpw);
$npw = md5($newpw);
$SQLchk = "SELECT * FROM ".DBToken."user WHERE userPass = '".$apw."' AND userIdNo = '".$userIdNo."'";
$qrychk = @mysqli_query($link, $SQLchk);
$numchk = @mysqli_num_rows($qrychk);


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
    </script>
</head>
<body>
<form name="frmChangeLogin" action="settings.user.save.php" method="post">
<?php
require_once("../include/page.header.php");
?>
<div id="PGsettingsusersave">
<h1>&#187;&nbsp;<?php echo L_dynsb_UserData;?>&nbsp;&#171;</h1>
<input type="hidden" name="lang" value="<?php echo $lang;?>">
<input type="hidden" name="userIdNo" value="<?php echo $SESS_userIdNo;?>">

<p>
<?php
if($numchk > 0) {
    $SQL = "UPDATE ".DBToken."user SET
                userLogin = '".$newuname."',
                userPass = '".$npw."'
            WHERE
                userIdNo = '".$userIdNo."'";
    $qry = @mysqli_query($link, $SQL);
    echo L_dynsb_UserdateUpdatedSuccessfull."\n";
	} else {
    echo L_dynsb_CurrentPasswordFalse."\n";
	}
?>
</p>

<div class="footer">
<?php
if($numchk > 0) {
?>
    <input type="button" class="button" onclick="javascript:window.self.close();" name="btn_close" value="<?php echo L_dynsb_Cancel;?>">
<?php
} else {
?>
    <input type="button" class="button" onclick="javascript:window.history.back();" name="btn_back" value="<?php echo L_dynsb_Back;?>">
<?php
}
?>
</div>

</div>
<?php
require_once("../include/page.footer.php");
?>
</form>
</body>
</html>
