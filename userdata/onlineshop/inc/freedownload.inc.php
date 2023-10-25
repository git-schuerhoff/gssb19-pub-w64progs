<?php
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") ." GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0
	chdir("../");
	$file = $_GET['cfile'];
	$path = getcwd()."/freedownload/";
	if (!is_dir($path))
	{
		echo "Kein Ordner: " . $path;
	}
	else
	{
		if(file_exists($path.$file))
		{
			$file_extension = strtolower(substr(strrchr($file,"."),1));
			switch( $file_extension ) {
				case "pdf": $ctype="application/pdf"; break;
				case "exe": $ctype="application/octet-stream"; break;
				case "zip": $ctype="application/zip"; break;
				case "doc": $ctype="application/msword"; break;
				case "xls": $ctype="application/vnd.ms-excel"; break;
				case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
				case "gif": $ctype="image/gif"; break;
				case "png": $ctype="image/png"; break;
				case "jpeg":
				case "jpg": $ctype="image/jpg"; break;
				case "mp3": $ctype="audio/mpeg"; break;
				case "wav": $ctype="audio/x-wav"; break;
				case "mpeg":
				case "mpg":
				case "mpe": $ctype="video/mpeg"; break;
				case "mov": $ctype="video/quicktime"; break;
				case "avi": $ctype="video/x-msvideo"; break;
				default: $ctype="application/force-download";
			}
			$len = filesize($path.$file);
			header("Pragma: public");
			header("Content-Description: File Transfer");
			header("Content-Type: " . $ctype);
			$header="Content-Disposition: attachment; filename=".$file.";";
			header($header);
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".$len);
			readfile($path.$file);
		}
		else
		{
			die("Keine Datei: " . $path.$file);
		}
	} 
?>
