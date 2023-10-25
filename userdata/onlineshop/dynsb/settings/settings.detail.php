<?php
/******************************************************************************/
/* File: settings.detail.php                                                  */
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

if(strtolower($act) == "e")
{
//A UR 18.1.211
    $qrySQL1 = "SELECT * FROM ".DBToken."black_email_list";
    $qry1 = @mysqli_query($link, $qrySQL1);
    $emailBlockedMessage = "";
    $blacklist_email_regexp = "";
    while($obj1 = mysqli_fetch_object($qry1))
    {
        if ($obj1->blackType == 1)
        {
          $emailBlockedMessage = $obj1->blackValues;
        }
        else if ($obj1->blackType == 2)
        {
          if (strlen($blacklist_email_regexp) > 0)
          {
            $blacklist_email_regexp = $blacklist_email_regexp."\n";
          }
          $blacklist_email_regexp = $blacklist_email_regexp.$obj1->blackValues;
        }
    }
    @mysqli_free_result($qry1);
//E UR

    $qrySQL = "SELECT * FROM ".DBToken."settings WHERE setIdNo = '".$setIdNo."'";
    $qry = @mysqli_query($link, $qrySQL);
    $obj = @mysqli_fetch_object($qry);

    if($obj)
    {
        foreach($obj as $key => $value)
        {
            $$key = trim($value);
        }
    }
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_Settings;?></title>
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
    function showLoginEdit()
    {
      var x = 0;
	    var y = 0;
	    var winBreite = 350;
	    var winHoehe = 300;
	    x = Math.round((screen.width-winBreite)/2);
	    y = Math.round((screen.height-winHoehe)/2);

	    var helpwin = window.open('settings.change.php?lang=<?echo $lang;?>','help','left='+x+',top='+y+',width='+winBreite+',height='+winHoehe+',scrollbars=no,resizable');
	    helpwin.focus();
    }
    </script>
</head>
<body>
<form name="frmSettings" action="settings.save.php" method="post">

<?php
require_once("../include/page.header.php");
?>

<input type="hidden" name="lang" value="<?php echo $lang;?>">
<input type="hidden" name="start" value="<?php echo $start;?>">
<input type="hidden" name="setIdNo" value="<?php echo $setIdNo;?>">
<input type="hidden" name="act" value="<?php echo $act;?>">

<div id="PGSettingsdetail">
<h1><?php echo L_dynsb_Settings;?></h1>

<h2><?php echo L_dynsb_StandardFrontendLanguage;?></h2>
<p>
	<select name="setDefaultLanguageIdNo" tabindex="3">
        <?php
        /*if(L_dynsb_ShopLang!="{L_dynsb_ShopLang}"
          && L_dynsb_ShopLang!="{L_dynsb_ShopLang_deu}"
          && L_dynsb_ShopLang!="{L_dynsb_ShopLang_eng}")
        {*/
        ?>
        <!--<option value="gs_" <?php if(setDefaultLanguageIdNo=="gs_") echo "selected";?>><?php echo L_dynsb_ShopLang;?></option>-->
        <?//}?>
        <option value="deu" <?php if($setDefaultLanguageIdNo=="deu") echo "selected";?>><?php echo L_dynsb_German;?></option>
        <option value="eng" <?php if($setDefaultLanguageIdNo=="eng") echo "selected";?>><?php echo L_dynsb_English;?></option>
    </select>
</p>

<h2><?php echo L_dynsb_OrderOptions;?></h2>
	<p><input class="checkbox" name="useOrdOptAutoCross" type="checkbox" value="1"<?php if($useOrdOptAutoCross==1) echo " checked";?>>&nbsp;<?php echo L_dynsb_AutoCrossSelling;?></p>
	<p><input class="checkbox" name="useOrdOptBestseller" type="checkbox" value="1"<?php if($useOrdOptBestseller==1) echo " checked";?>>&nbsp;<?php echo L_dynsb_Bestseller;?></p>
	<p><input class="checkbox" name="useOrdOptBestsellerPg" type="checkbox" value="1"<?php if($useOrdOptBestsellerPg==1) echo " checked";?>>&nbsp;<?php echo L_dynsb_BestsellerProdgroup;?></p>
	<p><input class="checkbox" name="useOrdOptLastViewed" type="checkbox" value="1"<?php if($useOrdOptLastViewed==1) echo " checked";?>>&nbsp;<?php echo L_dynsb_HistoryOfLastViewedItems;?></p>
	<p><input class="checkbox" name="useOrdOptMain" type="checkbox" value="1"<?php if($useOrdOptMain==1) echo " checked";?>>&nbsp;<?php echo L_dynsb_ArticleMainPage;?></p>
	<p><input class="checkbox" name="useFormatMailAddress" type="checkbox" value="1"<?php if($useFormatMailAddress==1) echo " checked";?>>&nbsp;<?php echo L_dynsb_OrderAdrHtmlFormat;?></p>
	<p><input class="checkbox" name="secFieldReqCredCard" type="checkbox" value="1"<?php if($secFieldReqCredCard==1) echo " checked";?>>&nbsp;<?php echo L_dynsb_SecFieldReqCredCard;?></p>
	<p><input class="checkbox" name="setSaveIP" type="checkbox" value="1"<?php if($setSaveIP==1) echo " checked";?>>&nbsp;<?php echo L_dynsb_SaveIP;?></p>
	<p><input class="checkbox" name="reviewLinksInEmail" type="checkbox" value="1"<?php if($reviewLinksInEmail==1) echo " checked";?>>&nbsp;<?php echo L_dynsb_reviewLinksInEmail;?></p>

<h2><?php echo L_dynsb_NumberOfRows;?></h2>
	<p><input type="text" class="small" maxlength="2" value="<?php echo $setRowCount; ?>" name="setRowCount">&nbsp;<?php echo L_dynsb_AdminCentre;?></p>
	<p><input type="text" class="small" name="setBestsellerCount" maxlength="2" value="<?php echo $setBestsellerCount;?>">&nbsp;<?php echo L_dynsb_Bestseller;?></p>
	<p><input type="text" class="small" name="setBestsellerPgCount" maxlength="2" value="<?php echo $setBestsellerPgCount;?>">&nbsp;<?php echo L_dynsb_BestsellerProdgroup;?></p>
	<p><input type="text" class="small" name="setLastOrderCount" maxlength="2" value="<?php echo $setLastOrderCount;?>">&nbsp;<?php echo L_dynsb_Shoporder;?></p>
	<p><input type="text" class="small" name="setAutoCrossSellingCount" maxlength="2" value="<?php echo $setAutoCrossSellingCount;?>">&nbsp;<?php echo L_dynsb_AutoCrossSelling;?></p>

<h2><?php echo L_dynsb_UserPassword;?></h2>
	<p><input type="button" class="button" onclick="javascript:showLoginEdit();" name="btn_login" value="<?php echo L_dynsb_Edit;?>"></p>

<h2><?php echo L_dynsb_customerreturningticket_adress;?></h2>
  <p><textarea rows="12" cols="80" name="customerreturningticket_adress"><?php echo $customerreturningticket_adress;?></textarea></p>

<h2><?php echo L_dynsb_blacklist_email_regexp;?></h2>
  <p><textarea rows="12" cols="80" name="blacklist_email_regexp"><?php echo $blacklist_email_regexp?></textarea></p>
  <p><?php echo L_dynsb_BlockedMessage;?>&nbsp; <input type="text" class="customer2" maxlength="255" value="<?php echo $emailBlockedMessage;?>" name="emailBlockedMessage"></p>

<div class="footer">
	<input type="submit" class="button" name="btn_save" value="<?php echo L_dynsb_Save;?>">
	<input type="button" class="button" onclick="javascript:self.location.href='../help/about.php?lang=<?php echo $lang;?>';" name="btn_save" value="<?php echo L_dynsb_Cancel;?>">
</div>

</div>
<?php
require_once("../include/page.footer.php");
?>

</form>
</body>
</html>
