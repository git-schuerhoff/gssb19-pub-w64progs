<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
//A TS 21.06.2012
$afiles = array();
$path = "../../";
$dir_handle = @opendir($path);
$ausg = "";
if(!$dir_handle)
{
  echo "-1|Unable to open $path|~";
  exit;
}
//A TS 06.02.2013 eindeutige Dateinamen verwenden
//while ($file = readdir($dir_handle)) 
//{
	//if (strpos($file, $_GET['filename']) !== false) 
	//{
		$file = $_GET['filename'];
		$fh = fopen($path . $file,"r") or die("-1|Konnte " . $path . $file . " nicht öffnen!|~");
		$i = 0;
		$dateinr = 1;
		$ziel = $_GET['splitfilename'] . "_" . $dateinr . ".dat";
		$fh2 = fopen($path . $ziel,"w") or die ("-1|Teildatei konnte nicht erzeugt werden!|~");
		$ausg = "0|" . $ziel . "|~";
		while(!feof($fh))
		{
			$zeile = fgets($fh);
			if(strlen($zeile) > 0 && $zeile != "")
			{
				fputs($fh2,$zeile);
				$i++;
			}
			if($i >= $_GET['zeilen'])
			{
				$i = 0;
				fclose($fh2);
				$dateinr++;
				$ziel = $_GET['splitfilename'] . "_" . $dateinr . ".dat";
				array_push($afiles,$ziel);
				if($fh2 = fopen($path . $ziel,"w"))
				{
					$ausg .= "0|" . $ziel . "|~";
				}
				else
				{
					echo "-1|Teildatei konnte nicht erzeugt werden!|~";
					exit;
				}
			}
		}
		fclose($fh);
		fclose($fh2);
	//}
//}
closedir($dir_handle);
echo $ausg;
?>