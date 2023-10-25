<?php
/******************************************************************************/
/* File: login.php                                                            */
/******************************************************************************/

require_once("class/class.security.php");
require_once("include/functions.inc.php");
/***************** Sprachdatei ************************************************/
if (!isset($_REQUEST['lang']) || strlen(trim($_REQUEST['lang'])) == 0) 
{
    $lang = "deu";
} 
else 
{
	$lang = $_REQUEST['lang'];
	if(!file_exists("lang/lang_".$lang.".php"))
  {
    $lang = "deu";
  }
}

include("lang/lang_".$lang.".php");
/******************************************************************************/

$dtlsSecurity = new Security;
$dtlsSecurity->ExtraFieldNames('sessLanguageIdNo');
$dtlsSecurity->StoreSession_TableName(DBToken.'session');
$dtlsSecurity->Log_TableName(DBToken.'log');
$dtlsSecurity->FieldNames('sessId', 'sessUserIdNo', 'sessUserLogin');

$struName = addslashes($_POST["struName"]);
$strPass = trim($_POST["strPass"]);
//$frontLang = trim($_POST["frontLang"]);
$frontLang = trim($lang);
$strMethod = trim($_POST["strMethod"]);


if($strMethod == "")
{ 
  $strMethod = $_GET["strMethod"];
}
if(!$dtlsSecurity->IsLoggedIn()) 
{
  if($strMethod == "check_login") 
  {
    ProcessLogin($frontLang);
  } 
  else 
  {
    GetLogin();
  }
} 
else 
{
  if($strMethod == "logout") 
  {
    ProcessLogout($lang);
  } 
  else 
  {
    ProcessLogin($frontLang);
  }
}

ob_end_flush();

function GetLogin() 
{
  header("Location: index.php");
  die();
}


function ProcessLogin($frontLang) 
{
  global $dtlsSecurity;
  global $struName;
  global $strPass;
  
    
  $strUser = $struName;
  $strPass = md5($strPass); // in the db the md5 crypted password is stored
  $dbVars = new dbVars();
  @$svrConn = mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb);
  $svrConn->query("SET NAMES 'utf8'");
  if($svrConn) 
  {
      $strQuery = "SELECT * FROM ".DBToken."user ";
      $strQuery .= "WHERE userLogin = '".$strUser."' ";
      $strQuery .= " AND userPass = '".$strPass."' ";
      $strQuery .= " AND userChgHistoryFlg <> '0' ";
      $results = @mysqli_query($svrConn,$strQuery);
      $result  = @mysqli_fetch_array($results);
      if($result) 
      {
        // Write to the log file
        $strRemoteAdr = getenv("REMOTE_ADDR");
        $strLog = "Login OK: {$result['userLogin']}, {$result['userIdNo']}, {$strRemoteAdr}, " . date("d/m/Y H:i:m");
        $dtlsSecurity->AddLog($strLog, 9);
        if($dtlsSecurity->StoreSession($result['userIdNo'], $result['userLogin'], $frontLang)) 
        {    
          // You have now logged in.
          $dtlsSecurity->deleteInvalidSessionLogs();  
          $_SESSION['SESS_languageIdNo'] = $frontLang;
          header("Location: start.php?lang=".$frontLang); // start content page
          die();
        } 
        //else 
        //{
        //  ?>error. Couldn't start a new session.<?php
        //}
      } 
      else 
      {
        $strRemoteAdr = getenv("REMOTE_ADDR");
        $strLog = "Login Failed: $strUser, $strPass, {$strRemoteAdr}, " . date("d/m/Y H:i:m");
        $dtlsSecurity->AddLog($strLog, 0);

        // Login Failed
        header("Location: error.php?lang=".$frontLang);
        die();
      }    
   
  } 
  else 
  {
    $strRemoteAdr = getenv("REMOTE_ADDR");
    $strLog = "Database Server Error: Login, $strUser, $strPass, {$strRemoteAdr}, " . mysqli_error($svrConn) . ", " . date("d/m/Y H:i:m");
    $dtlsSecurity->AddLog($strLog, 2);
    ?>database error.<?php
  }
}

ob_end_clean();

// redirect to start page
function ShowLogOut() 
{
  header("Location: start.php");
  die();
}

function ProcessLogOut($frontLang) 
{
  $Session = new Session();
  //@session_start();    
  $dbVars = new dbVars();
  @$svrConn = mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb);
  $svrConn->query("SET NAMES 'utf8'");
  if($svrConn) 
  {
    $strQuery = "DELETE FROM ".DBToken."session ";
    $strQuery .= "WHERE sessId = '" . session_id() . "'";        
    mysqli_query($svrConn,$strQuery);
    
  }

  // select language for logout page
  if (!isset($_SESSION['SESS_languageIdNo']) || strlen(trim($_SESSION['SESS_languageIdNo'])) == 0) 
  {
    $lngIdNo = "deu";
  } 
  else 
  {
    $lngIdNo = $_SESSION['SESS_languageIdNo'];
  }

  // Logout
  header("Location: logout.php?lang=".$frontLang);
  die();
}

?>

