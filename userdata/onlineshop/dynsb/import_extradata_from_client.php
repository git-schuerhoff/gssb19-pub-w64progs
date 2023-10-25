<?php

require("./../conf/db.const.inc.php");
require_once("./include/functions.inc.php");

echo "Beginn des Imports<br />\n";

// connect to database server or die
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");


//     echo "<b>Datenbank: ".$dbDatabase."</b><br/>";

//define the path as relative
$path = ".";

//using the opendir function
$dir_handle = @opendir($path) or die("Unable to open $path");

//running the while loop
while ($file = readdir($dir_handle)) 
{
	if (preg_match('~extra_data_from_client_(.*)\.dat~i', $file, $matches)) 
	{    
		$lines = file($file);
		$lasttbl = "";
		for($i=0;$i<count($lines);$i++)
		{
			/*echo $lines[$i] . "<br />\n";*/
			$aCmd = explode("|",$lines[$i]);
			if($aCmd[0] == "d")
			{
				$cmd = "DELETE FROM " . $aCmd[1];
				$esql = "DELETE FROM " . $aCmd[1] . " WHERE " . $aCmd[2];
				$sql = $esql;
			}
			else
			{
				$cmd = "INSERT INTO " . $aCmd[1];
				$esql = "INSERT INTO " . $aCmd[1] . " VALUES(";
				$sql = $esql;
				//Werte in Array teilen
				$avalues = explode(",",$aCmd[2]);
				$adecvals = array();
				$decstr;
				$avals = array();
				$str;
				for($v = 0; $v < count($avalues); $v++)
				{
					array_push($adecvals,"'" . addslashes(base64_decode($avalues[$v])) . "'");
					array_push($avals,"'" . $avalues[$v] . "'");
				}
				$decstr = implode(",",$adecvals);
				$str = implode(",",$avals);
				$esql .= $str . ")";
				$sql .= $decstr . ")";
				
			}
			
			//echo $esql . "<br />";
			//echo $sql ."<br />";
			$erg = mysqli_query($link,$sql);
			if(mysqli_errno($link) == 0)
			{
				echo $cmd . " => OK<br />";
				writeLogFile($sql . " => OK");
			}
			else
			{
				echo $sql . ":<br />" . mysqli_error($link) . ":<br />" . $sql . "<br />";
				writeLogFile($sql . ":\n" . mysqli_error($link) . ":\n" . $sql);
			}
		}
	}
}

closedir($dir_handle);

function writeLogFile($str)
{
  if(file_exists("gssb_to_dynsb_log/readme.txt"))
  {
    $perms = substr(decoct(fileperms("gssb_to_dynsb_log")),sizeof(decoct(fileperms("gssb_to_dynsb_log")))-4,3);
    if($perms=="777")
    {
      $filename = "gssb_to_dynsb_log/ED".date('Ymd').".log";
      $handle = fopen($filename, "a");
      $content = date('Y-m-d H:i:s')." -> ".$str. chr(13) . chr(10);
      fwrite($handle, $content);
      fclose($handle);
    }
  }

}
	
?>