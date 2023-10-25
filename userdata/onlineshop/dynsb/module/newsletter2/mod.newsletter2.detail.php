<?php
/******************************************************************************/
/* File: mod.customernews.detail.php                                          */
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
if(isset($_REQUEST['pk']) && is_numeric($_REQUEST['pk']))
  $_SESSION["nlIdNo"] = intval($_REQUEST['pk']);
#else
#	unset($_SESSION["nlIdNo"]);

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
$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";

//*****************************************************************************
//*****************************************************************************
//*****************************************************************************
$timestamp = date("Y-m-d H:i:s");

//*** CREATE newsletter from template
if ($act == 1) {
	$nldoIdNo = $_SESSION["nlIdNo"];

	$qryNlDoc = "SELECT * FROM ".DBToken."nl_documents WHERE nldoIdNo = '$nldoIdNo'";
  $resNlDoc = @mysqli_query($link,$qryNlDoc);
  $obj = mysqli_fetch_object($resNlDoc);

	$insNlDoc = "INSERT INTO ".DBToken."nl_documents" .
              "	(nldoStatusFlg, nldoContentText, nldoContentHtml, nldoSubject, nldoChangeDate)" .
              " VALUES " .
              "	('1', '$obj->nldoContentText', '$obj->nldoContentHtml', '$obj->nldoSubject', '$timestamp')"
              ;
  @mysqli_query($link,$insNlDoc);

  $insIdNo = mysqli_insert_id($link);
  if ($insIdNo) {
    $_SESSION["nlIdNo"] = $insIdNo;
    header("Location:" . $_SERVER["PHP_SELF"]); //dump GET-Parameter String
  	die();
  }
  else
		die("#");
}
//*** END CREATE ***

//**** INSERT a new Template *****
if (isset($_REQUEST["btnnew"])) {
	$contHtml = addslashes($_REQUEST["conthtml"]);
	$contText = strip_tags(addslashes($_REQUEST["conttext"]));
	$subject 	= strip_tags(addslashes($_REQUEST["subject"]));

  $insNlDoc = "INSERT INTO ".DBToken."nl_documents" .
              "	(nldoStatusFlg, nldoContentText, nldoContentHtml, nldoSubject, nldoChangeDate)" .
              " VALUES " .
              "	('0', '$contText', '$contHtml', '$subject', '$timestamp')"
              ;
  @mysqli_query($link,$insNlDoc);

  $insIdNo = mysqli_insert_id($link);
  if ($insIdNo)
    $_SESSION["nlIdNo"] = $insIdNo;
}
//********************************

//if id is given, get details from database
if(isset($_SESSION["nlIdNo"])) {
  #$noticeText = "";
  $nldoIdNo = $_SESSION["nlIdNo"];

  //*** UPDATE ***
  if (isset($_REQUEST["btnsave"])) {
		$contHtml = addslashes($_REQUEST["conthtml"]);
		$contText = strip_tags(addslashes($_REQUEST["conttext"]));
		$subject 	= strip_tags(addslashes($_REQUEST["subject"]));

    $updNlDoc = "UPDATE ".DBToken."nl_documents " .
                "		SET nldoContentText = '$contText'" .
                "			, nldoContentHtml = '$contHtml'" .
                "			, nldoSubject 		= '$subject'" .
                "			, nldoChangeDate	= '$timestamp'" .
                "	WHERE nldoIdNo = '$nldoIdNo' "
                ;
    if (@mysqli_query($link,$updNlDoc))
    	$noticeText = L_dynsb_SavedNotice;
  }
	//*** END UPDATE ***

	//*** GET DOCUMENT ***
  $qryNlDoc = "SELECT * FROM ".DBToken."nl_documents WHERE nldoIdNo = '$nldoIdNo'";
  $resNlDoc = @mysqli_query($link,$qryNlDoc);

  $obj = mysqli_fetch_object($resNlDoc);

  if (is_object($obj)) {
    $nlSubject		= $obj->nldoSubject;
    $nlContText		= stripslashes($obj->nldoContentText);
    $nlContHtml		= stripslashes($obj->nldoContentHtml);
    $nlChangeDate	= date_mysql2german($obj->nldoChangeDate);
    $nlSendDate		= date_mysql2german($obj->nldoSendDate);
		$statusFlg		= $obj->nldoStatusFlg;

    switch ($obj->nldoStatusFlg) {
    	case 0:	$title = L_dynsb_Templates;
    					$bReadonly = "";
    				break;
    	case 1:	$title = L_dynsb_NotSend;
    					$bReadonly = "";
    				break;
    	case 2:	$title = L_dynsb_Archiv;
							$bReadonly = " readonly ";
    				break;
    }
  }
}

//if statusflg is empty -> 0
$statusFlg = intval($statusFlg);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
  <title><?php echo L_dynsb_Newsletter;?></title>
  <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
  <meta content="de" http-equiv="Language">
  <meta name="author" content="GS Software Solutions GmbH">
  <link rel="stylesheet" type="text/css" href="../../css/link.css">
  <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb">
  <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
  <script type="text/javascript" src="../../js/gslib.php"></script>
  <script type="text/javascript" src="../../js/calendar.js"></script>
  <script type="text/javascript" src="../../js/calendar-<?php echo $strcal;?>.js"></script>
  <script type="text/javascript" src="../../js/calendar-setup.js"></script>
  <script type="text/javascript" src="../../js/editor.js"></script>
  <script type="text/javascript" src="../../js/text_format.js"></script>
  <script language="JavaScript" type="text/javascript">
	function expandTextarea(ta) {
		var obj;
		obj = document.getElementById(ta);
		if (obj) {
			if (obj.rows < '50')
				obj.rows = 50;
			else
				obj.rows = 10;
		}
	}

	function preview(pk)
  {
    var iMyWidth;
    var iMyHeight;

    iMyWidth = Math.round((window.screen.width/2) - (900/2 + 10));
    iMyHeight = Math.round((window.screen.height/2) - (600/2 + 40));
    var winNew = window.open('mod.newsletter2.preview.php?pk='+ pk +'=de&lang=deu', 'preview',"height=600,width=900,menubar=no,location=no,resizable=yes,scrollbars=yes,left="+iMyWidth+",top="+iMyHeight+"");
    winNew.focus();
  }
  </script>
</head>
<body>
<form name="frmCnews" action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
<?php
require_once("../../include/page.header.php");
?>

<div id="PGnewsletterdetail">
  <input type="hidden" name="lang" value="<?php echo $lang;?>">


<h1>&#187;&nbsp;<?php echo L_dynsb_Newsletter;?> - <?php echo $title;?>&#171;</h1>

<?php
if (!empty($noticeText)) {
?>
	<p class="notice">
		<?php echo $noticeText;?>
	</p>
<?php	}	?>


<h2><?php echo L_dynsb_Subject;?></h2>
<p>
  <input type="text" class="xx-large" name="subject" value="<?php echo $nlSubject;?>" maxlength="150" <?php echo $bReadonly;?>>
</p>

<h2 onclick="javascript:expandTextarea('conttext')"><?php echo L_dynsb_Text;?></h2>
<p>
  <textarea name="conttext" id="conttext" cols="120" rows="10"<?php echo $bReadonly;?>><?php echo $nlContText;?></textarea>
</p>

<h2 onclick="javascript:expandTextarea('conthtml')">HTML</h2>
<p>
	<button type="button" class="button small" onclick='setFormat("b")' title='<?php echo L_dynsb_Bold?>'><img src='../../image/bold.gif' alt='<?php echo L_dynsb_Bold?>'></button>&nbsp;
	<button type="button" class="button small" onclick='setFormat("i")' title='<?php echo L_dynsb_Italic;?>'><img src='../../image/italic.gif' alt='<?php echo L_dynsb_Italic;?>'></button>&nbsp;
	<button type="button" class="button small" onclick='setFormat("s")' title='<?php echo L_dynsb_StrikeThrough;?>'><img src='../../image/strike.gif' alt='<?php echo L_dynsb_StrikeThrough;?>'></button>&nbsp;
	<button type="button" class="button small" onclick='setFormat("u")' title='<?php echo L_dynsb_Underline;?>'><img src='../../image/underline.gif' alt='<?php echo L_dynsb_Underline;?>'></button>&nbsp;
	<button type="button" class="button small" onclick='setFormat("h1")' title='<?php echo L_dynsb_Heading1;?>'><img src='../../image/h1.gif' alt='<?php echo L_dynsb_Heading1;?>'></button>&nbsp;
	<button type="button" class="button small" onclick='setFormat("h2")' title='<?php echo L_dynsb_Heading2;?>'><img src='../../image/h2.gif' alt='<?php echo L_dynsb_Heading2;?>'></button>&nbsp;
	<button type="button" class="button small" onclick='setFormat("h3")' title='<?php echo L_dynsb_Heading3;?>'><img src='../../image/h3.gif' alt='<?php echo L_dynsb_Heading3;?>'></button>&nbsp;
	<button type="button" class="button small" onclick='setFormat("h4")' title='<?php echo L_dynsb_Heading4;?>'><img src='../../image/h4.gif' alt='<?php echo L_dynsb_Heading4;?>'></button>&nbsp;
	<button type="button" class="button small" onclick='setFormat("h5")' title='<?php echo L_dynsb_Heading5;?>'><img src='../../image/h5.gif' alt='<?php echo L_dynsb_Heading5;?>'></button>&nbsp;
	<button type="button" class="button small" onclick='setFormat("hr")' title='<?php echo L_dynsb_HorizontalRule;?>'><img src='../../image/hr.gif' alt='<?php echo L_dynsb_HorizontalRule;?>'></button>&nbsp;
	<button type="button" class="button small" onclick='setFormat("email")' title='<?php echo L_dynsb_EmailLink;?>'><img src='../../image/mail.gif' alt='<?php echo L_dynsb_EmailLink;?>'></button>&nbsp;
	<button type="button" class="button small" onclick='setFormat("link")' 	title='<?php echo L_dynsb_Link;?>'><img src='../../image/link.gif' alt='<?php echo L_dynsb_Link;?>'></button>
</p>
<p>
  <textarea name="conthtml" id="conthtml" cols="120" rows="10"<?php echo $bReadonly;?>><?php echo $nlContHtml;?></textarea>
</p>

<div class="footer">
<?php
  if(isset($nldoIdNo))
  {
?>
  <input type="submit" class="button" name="btnsave" value="<?php echo L_dynsb_Save;?>">
  <input type="button" class="button" value="<?php echo L_dynsb_Preview?>" onclick="javascript:preview('<?php echo $nldoIdNo?>')">
<?php
  } else {
?>
  <input type="submit" class="button" name="btnnew" value="<?php echo L_dynsb_Save;?>">
<?php
  }

	//if a newsletter document, display send button
  if ($statusFlg == 1) {
?>
  <input type="button" class="button large" value="<?php echo L_dynsb_SendNewsletter;?>" onclick="javascript:self.location.href='mod.newsletter2.send.php'">
<?php
  }
?>
  <input type="button" class="button" value="<?php echo L_dynsb_Back?>" onclick="javascript:self.location.href='mod.newsletter2.search.php?status=<?php echo $statusFlg;?>'">
</div>
</div>
<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
