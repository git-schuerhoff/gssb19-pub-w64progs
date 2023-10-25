<?php
	/*A TS 22.10.2015: Downloads nur noch über die Datenbank zählen und händeln*/
	if(file_exists("dynsb/class/class.db.php"))
{
	require_once("dynsb/class/class.db.php");
	require_once("dynsb/include/secure.functions.inc.php");
}
else
{
	if(file_exists("class/class.db.php"))
	{
	require_once("class/class.db.php");
	require_once("include/secure.functions.inc.php");
	}
}
  $file = $_GET['file'];
  /*$dir = $_GET['dir'];*/

  /*$path = getcwd()."/../customerdownloads/".$dir."/";
  
  $dlcount = -1;
  if (!is_dir($path))
  {
    echo "$path is not a directory";
  }
  else
  {
    $dir_handle = @opendir($path) or die("Unable to open ".$path);
    while ($file1 = readdir($dir_handle))
    {
      if (!is_dir($file1))
      {
        $fparts = split(';',$file1);
        if ($fparts[1])
        {
          if ($fparts[1] == $file)
          {
            $dlcount = intval($fparts[0]);
            break;
          }
        }              
      }
    }
    closedir($dir_handle);*/
  
    /*$oldfilename=$dlcount.";".$file;
    $dlcount = $dlcount-1;        
    $newfilename=$dlcount.";".$file;
    */
    $file_extension = strtolower(substr(strrchr($file,"."),1));
  
    switch( $file_extension ) {
      case "pdf": $ctype="application/pdf"; break;
      case "exe": $ctype="application/octet-stream"; break;
      case "zip": $ctype="application/zip"; break;
      case "docx":
      case "doc": $ctype="application/msword"; break;
      case "xlsx":
      case "xls": $ctype="application/vnd.ms-excel"; break;
      case "pptx":
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
    /*rename($path.$oldfilename,$path.$newfilename);    */

	$con = dbconnect();
	$sql = "UPDATE ".DBToken."downloadarticle_customer SET dlcuAllowedDownloads = (dlcuAllowedDownloads - 1) WHERE " .
			"dlcuCusId = '" . $_GET['cid'] . "' AND " .
			"dlcuItemNumber = '" . $_GET['itemnumber'] . "' AND " .
			"dlcuFilename = '" . $_GET['file'] . "' AND " .
			"dlcuCreateTime = '" . $_GET['ordertime'] . "' LIMIT 1";
	@mysqli_query($con,$sql);
	dbclose($con);

	$path = getcwd()."/../download/";
	$dir_handle = @opendir($path) or die("Unable to open ".$path);
	$len = filesize($path.$file);
	closedir($dir_handle);
	
    header("Pragma: public");
    header("Content-Description: File Transfer");
  
    header("Content-Type: $ctype");
      
    $header="Content-Disposition: attachment; filename=".$file.";";
    header($header);
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".$len);
	readfile($path.$file);
	
    //header("Location: ./customerdownloadarea.php?cid="+$_GET['cid']+'&downloaddone='+1);
    //die();                   
 /*E TS 22.10.2015: Downloads nur noch über die Datenbank zählen und händeln*/
  
  function dbconnect()
	{
		$dbVars = new dbVars();
		$con = @mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb);
		$con->query("SET NAMES 'utf8'");
		if($con)
		{
			return $con;
		}
		return false;
	}

	function dbclose($con)
	{
		@mysqli_close($con);
	}
?>
