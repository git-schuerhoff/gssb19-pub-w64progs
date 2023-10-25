<?php
/******************************************************************************/
/* File: mod.coupon.upload.file.php                                           */
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
$uploaddir = '../../image/upload/';
$wrongMimeType = 0;

echo L_dynsb_PleaseWait.$uploaddir;

if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploaddir.$_FILES['userfile']['name'])) {

    extract($_FILES['userfile'], EXTR_PREFIX_ALL, 'file');

    if( $file_type == "image/pjpeg" ||
        $file_type == "image/jpeg" ||
        $file_type == "image/jpe" ||
        $file_type == "image/jpg") {
        // do nothing...
        echo $_FILES['userfile']['name'];
        
        $sql = "UPDATE ".DBToken."settings set couponImage = '".$_FILES['userfile']['name']."'";
       
        //$SQL = "UPDATE ".DBToken."news SET newsPicName = '".$_FILES['userfile']['name']."' WHERE newsIdNo = '".$nid."'";
        $qry = @mysqli_query($link,$sql);
    } else {
        $wrongMimeType = 1;
        echo "<br />".L_dynsb_InvalidImageFile;
?>
<script language="JavaScript">
<!--
alert('<?echo L_dynsb_InvalidImageFile;?>\n<?echo L_dynsb_ChooseAnotherOne;?>');
// -->
</script>
<?php

    }
} else {
    print "ERROR!\n";
    print_r($_FILES);
}


@mysqli_close($link);

?>

<script language="JavaScript">
<!--

opener.location.reload();
window.close();

// -->
</script>
