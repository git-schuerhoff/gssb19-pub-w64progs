<?php
/******************************************************************************/
/* File: mod.availability.articles.php                                        */
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
$sql = "Delete from ".DBToken."itemdata where itemItemNumber='".$_REQUEST['id']."'";
@mysqli_query($link,$sql);

$sql = "Delete from ".DBToken."price where prcItemNumber='".$_REQUEST['id']."'";
@mysqli_query($link,$sql);

header("Location: mod.availability.articles.php?lang=".$lang);
?>
