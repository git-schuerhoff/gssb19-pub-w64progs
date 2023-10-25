<?php
/*
    Script - gssbdl_to_dynsbdl.php
    Author: Uwe Reuschel / GS Software Solutions GmbH

    File: gssbdl_to_dynsbdl.php
    
    Übertragung der Downloadartikel
*/


if(file_exists("../conf/db.const.inc.php"))
{
  require("../conf/db.const.inc.php");
  require_once("include/functions.inc.php");
}

foreach($_REQUEST as $key => $value)
{
  $value = str_replace(" ","+",$value);
  $$key = trim($value);

}

$ItemNumber = base64_decode($ItemNumber);
$LanguageId = base64_decode($LanguageId);

if(isset($session) && base64_decode($session)==$gssbTimeId.$shopURL)
{
    $link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
	$link->query("SET NAMES 'utf8'");
    $downloadarticle_tab = DBToken."downloadarticle";

    $insertSQL1 = "downloadItemNumber, downloadLanguageId, ";
    $insertSQL2 = "'".$ItemNumber."', '".$LanguageId."', ";
    $updateSQL = "";

    if($action == "i")
    {
      if(isset($LanguageId) && isset($ItemNumber))
      {
        if(isset($Filename)&&$Filename!="") 
        {                    
          $Filename = base64_decode($Filename);
          $insertSQL1 .= "downloadFilename, ";    
          $insertSQL2 .= "'".$Filename."', ";
          $updateSQL .= "downloadFilename = '".$Filename."', ";
        }
        if(isset($NumberDownloads)&&$NumberDownloads!="") 
        {                   
          $NumberDownloads = base64_decode($NumberDownloads); 
          $insertSQL1 .= "downloadAllowedDownloads, ";                   
          $insertSQL2 .= "'".$NumberDownloads."', "; 
          $updateSQL .= "downloadAllowedDownloads = '".$NumberDownloads."', "; 
        }
        if(isset($Watermarktext)&&$Watermarktext!="") 
        {                    
          $Watermarktext = base64_decode($Watermarktext);
          $insertSQL1 .= "downloadWatermarktext, ";    
          $insertSQL2 .= "'".addslashes($Watermarktext)."', ";
          $updateSQL .= "downloadWatermarktext = '".addslashes($Watermarktext)."', ";
        }
        
        if(isset($WatermarkPosition)&&$WatermarkPosition!="") 
        {                   
          $WatermarkPosition = base64_decode($WatermarkPosition); 
          $insertSQL1 .= "downloadWatermarkPosition, ";                   
          $insertSQL2 .= "'".$WatermarkPosition."', "; 
          $updateSQL .= "downloadWatermarkPosition = '".$WatermarkPosition."', "; 
        }
        if(isset($WatermarkIntensity)&&$WatermarkIntensity!="") 
        {                   
          $WatermarkIntensity = base64_decode($WatermarkIntensity); 
          $insertSQL1 .= "downloadWatermarkIntensity, ";                   
          $insertSQL2 .= "'".$WatermarkIntensity."', "; 
          $updateSQL .= "downloadWatermarkIntensity = '".$WatermarkIntensity."', "; 
        }
        $SQL = "SELECT * FROM ".$downloadarticle_tab." where downloadItemNumber='".$ItemNumber."' AND downloadLanguageId='".$LanguageId."';";
        $qry = @mysqli_query($link,$SQL);
        $num = @mysqli_num_rows($qry);
        if($num==0)
        {
      
          $insertSQL1 = substr($insertSQL1,0,sizeof($insertSQL1)-3);
          $insertSQL2 = substr($insertSQL2,0,sizeof($insertSQL2)-3);
  
          $SQL = "INSERT INTO ".$downloadarticle_tab." (".$insertSQL1.", downloadCreateTime, downloadUpdateTime) values(".$insertSQL2.", '".date("YmdHis")."', '".date("YmdHis")."');";
          writeLogFile($SQL);
          $qry = @mysqli_query($link,$SQL);
        }
        else
        {
          $updateSQL = substr($updateSQL,0,sizeof($updateSQL)-3);
  
          $SQL = "UPDATE ".$downloadarticle_tab." set ".$updateSQL.", downloadUpdateTime = '".date("YmdHis")."' where downloadItemNumber='".$ItemNumber."' AND downloadLanguageId='".$LanguageId."';";
          writeLogFile($SQL);
          $qry = @mysqli_query($link,$SQL);
        }
      }
    }
    else if($action == "d")
    {  
      $SQL = "DELETE FROM ".$downloadarticle_tab." where downloadItemNumber='".$ItemNumber."' AND downloadLanguageId = '".$LanguageId."'";
      writeLogFile($SQL);
      $qry = @mysqli_query($link,$SQL);
      if($qry)
      {
        //experemintell. schickt die artikelnummer zurück wenn es erfolgreich gelöscht wurde
        echo base64_decode($ItemNumber."=".$LanguageId);
      }
    }     
}
else
{
    writeLogFile("Error 101 - permission denied");
}

function writeLogFile($str)
{

  if(file_exists("gssb_to_dynsb_log/readme.txt"))
  {
    $perms = substr(decoct(fileperms("gssb_to_dynsb_log")),sizeof(decoct(fileperms("gssb_to_dynsb_log")))-4,3);
    if($perms=="777")
    {
      $filename = "gssb_to_dynsb_log/".date('Ymd')."DL.log";
      $handle = fopen($filename, "a");
      $content = date('Y-m-d H:i:s')." -> ".$str."\n";
      fwrite($handle, $content);
      fclose($handle);
    }
  }

}
?>
