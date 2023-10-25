<?php
error_reporting(E_ALL);
ini_set("display_errors","on");

require("../include/login.check.inc.php");
require_once("../include/functions.inc.php");
require("../../conf/db.const.inc.php");
chdir("../../");
include_once('inc/class.shopengine.php');
$linkDB = mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase)
   or die("<br>aborted: can´t connect to '$dbServer' <br>");
$linkDB->set_charset("utf8");

$se = new gs_shopengine();
$dir    = $se->absurl.'dynsb/exportdatabase/';
$fileok = '';
$importok = '';

if(isset($_POST['import']) && ($_POST['import'] == '1')){
    ## dump einspielen 
    $dump = $dir.$_POST['file'];
    system(sprintf("zcat $dump | mysql -u $dbUser -p'$dbPass' -h $dbServer $dbDatabase"));
    $importok = "<h5><font color='green'>Import wurde erfolgreich abgeschlossen!</font></h5><br/>";
}

if(sizeof($_FILES) > 0){
    if($_FILES['userfile']['name'] <> ''){
        $uploadfile = $dir . basename($_FILES['userfile']['name']);
        //var_dump($uploadfile);
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
            $fileok = "<h5><font color='green'>Datei ist valide und wurde erfolgreich hochgeladen!</font></h5><br/>";
        }
    }
}

// Create a Dumpfile List
$files = scandir($dir, 1);
//var_dump($_POST);

?>
<html>
<head>
<title>Import DB</title>
<?php //echo getmeta(); ?>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software AG">
    <link rel="stylesheet" type="text/css" href="../css/link.css">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2017 GS Software AG">
</head>
<body>
<DIV id="PGcarrierdetail">
<h1>Datenbank Import</h1>
<?php 
if (sizeof($files) > 3){
    echo '<p>Bitte, wählen Sie eine Sicherung aus, oder laden Sie eine Sicherungsdatei hoch.</p>';
?>
<form action="importDB.inc.php" method="POST">
<input type="hidden" name="import" value="1" />
    <fieldset>
    <?php if($importok<>''){ echo $importok;} ?>
    <div id="importwait" style="display:none"><h5><font color="red">Bitte haben Sie einen Moment Geduld. Datei wird importiert.</font></h5></div>
    <?php
    foreach($files as $file){
        $file_parts = pathinfo($file);
        if($file_parts['extension'] == 'gz' OR $file_parts['extension'] == 'zip' OR $file_parts['extension'] == 'sql'){
            echo '<input type="radio" id="'.$file.'" name="file" value="'.$file.'">';
            echo '&nbsp;&nbsp;<b>'.$file.'</b><br> <br>';
        }
    }
    ?><br/><input class="buttonhead125" type="submit" value="Import starten" onclick="this.disabled=1;document.getElementById('importwait').style.display='block';return true"/></fieldset>
</form>
<br/><br/>
<?php
} ?>

<form enctype="multipart/form-data" action="importDB.inc.php" method="POST">
<fieldset>
<?php if($fileok<>''){ echo $fileok;} ?>
<div id="pleasewait" style="display:none"><h5><font color="red">Bitte haben Sie einen Moment Geduld. Datei wird hoch geladen.</font></h5></div>
    <!-- MAX_FILE_SIZE muss vor dem Dateiupload Input Feld stehen -->
    <input type="hidden" name="MAX_FILE_SIZE" value="30000000" />
    <!-- Der Name des Input Felds bestimmt den Namen im $_FILES Array -->
    Sicherungsdatei hochladen: <input name="userfile" type="file" /><br/><br/>
    <input class="buttonhead125" type="submit" value="Übertragen" onclick="this.disabled=1;document.getElementById('pleasewait').style.display='block';return true"/>
</fieldset>
</form>
</div>
</body>
</html>