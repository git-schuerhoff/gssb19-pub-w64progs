<?php

/*
 file: mod.orderemail.upload.file.php
*/


require("../../include/login.check.inc.php");
require_once("../../include/functions.inc.php");
require("../../../conf/db.const.inc.php");

// connect to database server or die

$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase)
   or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");

$uploaddir = '../../image/upload/';
$wrongMimeType = 0;

echo "Bitte warten...".$uploaddir;

if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploaddir.$_FILES['userfile']['name'])) {

    extract($_FILES['userfile'], EXTR_PREFIX_ALL, 'file');

    if( $file_type == "image/pjpeg" ||
        $file_type == "image/jpeg" ||
        $file_type == "image/jpe" ||
        $file_type == "image/jpg" ||
        $file_type == "image/gif" ||
        $file_type == "image/png") {
        // do nothing...
        echo $_FILES['userfile']['name'];
        $SQL = "UPDATE ".DBToken."settings SET ordEmailImage = '".$_FILES['userfile']['name']."' WHERE setIdNo = '1'";
        $qry = @mysqli_query($link,$SQL);
    } else {
        $wrongMimeType = 1;
        echo "<br />Die ausgewählte Datei ist keine gültige Bilddatei!";
?>
<script language="JavaScript">
<!--
alert('Die ausgewählte Datei ist keine gültige Bilddatei.\nBitte wählen Sie eine andere Datei aus.');
// -->
</script>
<?php

    }
} else {
    print "ERROR! - Possible file upload attack!  Here's some debugging info:\n";
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
