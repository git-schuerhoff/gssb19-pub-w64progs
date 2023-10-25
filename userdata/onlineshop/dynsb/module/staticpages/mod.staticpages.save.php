<?php
/******************************************************************************/
/* File: mod.staticpages.save.php                                             */
/******************************************************************************/

require("../../include/login.check.inc.php");
require_once("../../include/functions.inc.php");
require("../../../conf/db.const.inc.php");
require_once("../../include/secure.functions.inc.php");
require("mod.staticpages.setup.php");

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
$chgApplicId = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1)); // script name

//------------------------------------------------------------------------------
//
// input validation
//
// needed parameters

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
// create variables with the exact tab-column name
/*foreach($_REQUEST as $key => $value) 
{
    $$key = trim($value);      
}
*/

if( isset($_REQUEST['grouplabeltext']) && isset($_REQUEST['grouplabel_submit']) && $_REQUEST['grouplabel_submit'] != "" 
  )
{  
  $sql = "UPDATE ".DBToken."settings
          SET staticpagesgroupboxlabel = '".mysqli_real_escape_string($link,trim($_REQUEST['grouplabeltext']) )."'
          WHERE setIdNo = 1
         ";         
  $qry = @mysqli_query($link,$sql);
         
  @mysqli_close($link);
  @header("Location: mod.staticpages.search.php?msg=$msg");
  die();
}

$id = trim( $_REQUEST['staticpagesIdNo'] );

if( !isset($id) || $id == '' )
  die('Missing parameter. id = '.$id);  

$title = $_REQUEST["statpgsTitle_".$id];
$url   = $_REQUEST["statpgsUrl_".$id];
$fsp   = $_REQUEST["filesourcepath_".$id];
$msg = "";
 
if (!file_exists($uploadDIR)) {
   @mkdir($uploadDIR,0755);
}

if (!@move_uploaded_file($_FILES["statpgsUpload_".$id]['tmp_name'], $uploadDIR.$_FILES["statpgsUpload_".$id]['name'])) { 
  $msg=L_dynsb_spFileuploadFailed.": ".$uploadDIR.$_FILES["statpgsUpload_".$id]['name'];
}

$SQLtst = "SELECT COUNT(*) FROM ".DBToken."staticpages WHERE staticpagesIdNo = ".$id;
$qrytst = @mysqli_query($link,$SQLtst);
$rowtst = mysqli_fetch_row($qrytst);    

if($rowtst[0] != 1) {
  
  $SQLins = "INSERT INTO ".DBToken."staticpages (staticpagesIdNo, menuentryTitle, menuentryURL, filesourcepath) 
              VALUES
              ( ".$id.", '', '', '')";
  $qryins = @mysqli_query($link,$SQLins);
}
  
$SQLupd = "UPDATE ".DBToken."staticpages 
            SET        
            menuentryTitle = '".mysqli_real_escape_string($link,$title)."',        
            menuentryURL = '".mysqli_real_escape_string($link,$url)."',
            filesourcepath = '".mysqli_real_escape_string($link,$fsp)."'
            WHERE 
            staticpagesIdNo = ".$id;

$qryupd = @mysqli_query($link,$SQLupd);

if($qryupd) {  
  if (!empty($_FILES["statpgsUpload_".$id]['name']))   	
    $msg = L_dynsb_spFileuploadOk . ": " . $uploadDIR . $_FILES["statpgsUpload_".$id]['name'];
  else
    $msg = L_dynsb_spFileuploadOk;
}
else {
  $msg=L_dynsb_spDBsave;
} 

@mysqli_close($link);
@header("Location: mod.staticpages.search.php?msg=$msg");
die();
?>
