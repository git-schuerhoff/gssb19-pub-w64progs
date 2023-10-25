<?php
/******************************************************************************/
/* File: mod.data_import.import.php                                           */
/******************************************************************************/

require_once("../../include/login.check.inc.php");
require_once("../../include/functions.inc.php");
require_once("../../../conf/db.const.inc.php");
require_once("class.newsletter2.php");

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

$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";


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
<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post" enctype="multipart/form-data">


<?php
require_once("../../include/page.header.php");
?>
<div id="PGnewsletterimport">
<h1>&#187;&nbsp;<?php echo L_dynsb_EmailAddresses;?> <?php echo L_dynsb_Import;?>&nbsp;&#171;</h1>
<h2><?php echo L_dynsb_FileName;?></h2>

  <p>
    <input type="file" name="nlimportfile" size="50" maxlength="100000" accept="text/*"><br />
  </p>
  <p>
    <input type="submit" name="btnimport" class="button" value="<?php echo L_dynsb_Import;?>">
  </p>
<br />
<h2>&nbsp;</h2>
  <pre>
<?php
if (isset($_REQUEST["btnimport"])) {

  $fn = $_FILES["nlimportfile"]["tmp_name"];

  if (file_exists($fn)) {
    if (is_file($fn)) {
      if (is_uploaded_file($fn)) {
        $aImportData = file($fn);

        //for every line
        foreach ($aImportData as $key => $value) {
          $value = trim($value);

          //split row
          $aRow  = explode(";", $value);

          //get value of one line of importfile
          $insAddress 	= $aRow[0];
          $insFormat		= $aRow[1];
          $insMG				= $aRow[2];

          //create new newsletter object
          $nl = new newsletter2();

          //disable double opt in for import!
          $nl->setDoubleOptIn("0");

          //get mailgroup id by name
          if ($mgId = $nl->getMailgroupId($insMG)) {
            $aMgId = array($mgId);

						//insert record into database
            if ($nl->signIn($insAddress, $insFormat, $aMgId, true) == 1)
              echo "+ $insFormat | $insMG | $insAddress\n";
            else
              echo "- $insFormat | $insMG | $insAddress\n";
            flush();
          }
          //if mailgroup not found
          else {
            echo "~ $insFormat | $insMG | $insAddress\n";
            flush();
          }
        }
      }
    }
  }
}
?>
Format import.txt:

example1@example.com;T;mailgroup1
example2@example.com;T;mailgroup2
example3@example.com;H;mailgroup2
^                    ^ ^--- mailgroup name
|                    '----- H: HTML, T: Text
'-------------------------- EMail-Address
</pre>
<br />
</div>
<?php
require_once("../../include/page.footer.php");
?>
</form>
</body>
</html>
