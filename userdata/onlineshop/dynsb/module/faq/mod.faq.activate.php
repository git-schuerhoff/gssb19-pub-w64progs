<?php
/******************************************************************************/
/* File: mod.faq.activate.php                                                 */
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
if(isset($_REQUEST['start'])) {
    $start = intval($_REQUEST['start']);
} else {
    die("error - missing post parameter");
}
if(isset($_REQUEST['pk'])) {
    $pk = intval($_REQUEST['pk']);
} else {
    die("error - missing post parameter");
}
if(isset($_REQUEST['action'])) {
    $act = trim($_REQUEST['action']);
} else {
    die("error - missing post parameter");
}

if($act=='e')
{
  $sql = "UPDATE ".DBToken."faq SET 
                faqActive = '1' 
            WHERE
                faqId = '".$pk."'";
}
else if($act=='d')
{
  $sql = "UPDATE ".DBToken."faq SET 
                faqActive = '0' 
            WHERE
                faqId = '".$pk."'";
}

$qry = @mysqli_query($link,$sql);

header("Location: mod.faq.search.php?lang=".$lang."&start=".$start);

?>
