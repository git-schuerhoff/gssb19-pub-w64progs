<?php
error_reporting(E_ALL);
ini_set("display_errors","on");
//session_start();
require("../include/login.check.inc.php");
require_once("../include/functions.inc.php");
require("../../conf/db.const.inc.php");
chdir("../../");
include_once('inc/class.shopengine.php');
$linkDB = mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase)
   or die("<br>aborted: can´t connect to '$dbServer' <br>");
$linkDB->set_charset("utf8");
$message='';
$exportok='';
$se = new gs_shopengine();
$dir    = $se->absurl.'dynsb/exportdatabase/';
// Ausgewählte Dateien löschen
if(isset($_POST['del_stat']) && ($_POST['del_stat'] == '1')){
    if (!isset($_POST['pk'])) {
		$errInput++;
	} else {
		$pkDataListAry = $_POST['pk'];
		$pkDataListLenAry = sizeof($pkDataListAry);

		if ($pkDataListLenAry >= 1) {
			for ($x=0; $x < $pkDataListLenAry; $x++) {
				$pkDataListAry[$x] = addslashes(strip_tags($pkDataListAry[$x]));
				 //echo $pkDataListAry[$x]."<br>";
                unlink($dir.$pkDataListAry[$x]);
			}
			//$pkDataListStr = implode(",", $pkDataListAry);
		} else if ($pkDataListLenAry == 1) {
			$pkDataListStr = addslashes(strip_tags($_POST['pk']));
            unlink($dir.$pkDataListStr);
		}
		unset ($_POST['pk']);
	}
    //var_dump($pkDataListStr);
}

if(isset($_POST['export']) && ($_POST['export'] == '1')){
    $bestaetigungsmail_adresse = $se->get_setting('edShopEmail_Text');
    $bestaetigungsmail_senden = "1";
    $downloadlink_erstellen = "1";
    $bestaetigungsmail_betreff = "Ihre Datenbank Backup.";

    $sql_file = "dynsb/exportdatabase/dump_" . $dbDatabase . "_" . date('Ymd_Hi') . ".sql";

    ## dump erstellen
    shell_exec("mysqldump -h $dbServer -u $dbUser -p'$dbPass' --quick --allow-keywords --add-drop-table --complete-insert --quote-names $dbDatabase > $sql_file");
    shell_exec("gzip $sql_file");

    ### größe ermitteln
    $datei = $sql_file . ".gz";
    $size = filesize($datei);
    $i = 0;
    while ( $size > 1024 )
    {
        $i++;
        $size = $size / 1024;
    }
    $fileSizeNames = array(" Bytes", " KiloBytes", " MegaBytes", " GigaBytes", " TerraBytes");
    $size = round($size,2);
    $size = str_replace(".", ",", $size);
    $groesse = "$size $fileSizeNames[$i]";

    ### nachricht erstellen
    $message = "<br/><br/>Ihr Backup der Datenbank <b>" . $dbDatabase . "</b> wurde durchgef&uuml;hrt.<br>";
    $message .= "Die Gr&ouml;&szlig;e des erstellten Dumps betr&auml;gt <b>" . $groesse . "</b>.<br>";

    if ($downloadlink_erstellen == "yes" or $downloadlink_erstellen == "ja" or $downloadlink_erstellen == "1")
    {
        $link = $se->shopurl.$datei;
        //$link = str_replace(basename(__FILE__),$datei,$link);
        $message .= "Downloadlink: <a href=" . $link . ">" . $datei. "</a>";
    }

    ### mail versenden
    if ($bestaetigungsmail_senden == "yes" or $bestaetigungsmail_senden == "ja" or $bestaetigungsmail_senden == "1"){
        if(!preg_match( '/^([a-zA-Z0-9])+([.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-]+)+/' , $bestaetigungsmail_adresse)){
            $message .= "<br>FEHLER: Mail konnte nicht versendet werden, da die Adresse ung&uuml;ltig ist!";
        } else {
            mail($bestaetigungsmail_adresse, $bestaetigungsmail_betreff,
            $message,"From: backupscript@{$_SERVER['SERVER_NAME']}\r\n" . "Reply-To: backupscript@{$_SERVER['SERVER_NAME']}\r\n" . "Content-Type: text/html\r\n")
            or die("FEHLER: Mail konnte wegen eines unbekannten Fehlers nicht versendet werden");
            $message .= "<br>Best&auml;tigungsmail wurde erfolgreich versandt!";
        }
    }
    $exportok = "<h5><font color='green'>Export ist erfolgreich abgeschlossen!</font></h5>";
}
?>
<html>
<head>
<title>Export DB</title>
<?php //echo getmeta(); ?>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software AG">
    <link rel="stylesheet" type="text/css" href="../css/link.css">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2015 GS Software AG">
<script type="text/javascript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);

function checkAllData(frm)
{
    var iCountHits = 0;
    var iCountAvailable = 0;
    var sFormName = frm;

    for(var x = 0; x < document.forms[sFormName].elements.length; x++){
        var y = document.forms[sFormName].elements[x];
        if(y.type == 'checkbox' && y.name != 'alldata') {
            iCountAvailable++;
            if(document.forms[sFormName].elements[x].checked == true) {
                iCountHits++;
            }
        }
    }

    if(iCountHits == iCountAvailable) {
        document.forms[sFormName].alldata.checked = true;
    } else {
        document.forms[sFormName].alldata.checked = false;
    }
}

function selectAllData(frm)
{
    var sFormName = frm;
    for(var x = 0; x < document.forms[sFormName].elements.length; x++){
        var y = document.forms[sFormName].elements[x];
        if(y.name != 'alldata') y.checked = document.forms[sFormName].alldata.checked;
    }
}

function isDataSelected(frm)
{
    var sFormName = frm;
    var bSelected = new Boolean(false);
    for(var x=0; x < document.forms[sFormName].elements.length; x++)  {
        var y = document.forms[sFormName].elements[x];
        if(y.name != 'alldata')  {
            if(y.checked) return bSelected = true;
        }
    }
    return bSelected;
}

function deleteIfAnyIsSelected(frm)
{
    var sFormName = frm;
    if(isDataSelected(sFormName)==true)  {
        var bCheck = confirm("Wollen Sie die ausgewählte Daten wirklich löschen?");
        if(bCheck==true) document.forms[sFormName].submit();
    } else  {
        alert("Bitte wählen Sie mindestens einen Datensatz aus!");
    }
}

function resetSearch(frm, pre, reloadFlg)
{
    var sFormName = frm;
    var sPrefix = pre;
    var iPreLength = sPrefix.length;
    var rFlg = new Boolean(reloadFlg);
    for(var x=0; x < document.forms[sFormName].elements.length; x++)  {
        var y = document.forms[sFormName].elements[x];
        var name = y.name;
        if(name.substr(0, iPreLength) == sPrefix)  {
            document.forms[sFormName].elements[x].value = "";
        }
    }
    if(rFlg == true) {
        document.forms[sFormName].submit();
    }
}



function startDelete(val) {
    document.dumps.start.value = val;
    document.dumps.del_stat.value = "1";
    deleteIfAnyIsSelected('dumps');
}

// -->
</script>
</head>
<body>
<DIV id="PGcarrierdetail">
<h1>Datenbank Export</h1>
<fieldset>
<form action="exportDB.inc.php" method="POST">
<?php if($exportok<>''){ echo $exportok.$message.'<br/>';} ?>
<input type='hidden' value='1' name='export'/>
<div id="exportwait" style="display:none"><h5><font color="red">Bitte haben Sie einen Moment Geduld. Datei wird exportiert.</font></h5></div>
<input class="buttonhead125" type="submit" value="Export starten" onclick="this.disabled=1;document.getElementById('exportwait').style.display='block';return true"/>
</form>
</fieldset><br/><br/>

<?php
## Backups Verwaltung
$files = scandir($dir, 1);
if (sizeof($files) > 5){ ?>
<h2>Vorhandene Sicherungen</h2>

<form name="dumps" action="exportDB.inc.php" method="POST">
<input type="hidden" name="del_stat" value="0">
<input type="hidden" name="start" value="1">
<fieldset>
    <table border="0" class="frame" cellspacing="0" cellpadding="0" width="750">
        <tr>
        <td>
          <table width="100%" border="0" cellspacing="2">
          <?php foreach($files as $file){ 
            $file_parts = pathinfo($file);
            if($file_parts['extension'] == 'gz' OR $file_parts['extension'] == 'zip' OR $file_parts['extension'] == 'sql'){?>
              <tr>
                <td width="5%" class="tablecolor2">
                 <input type="checkbox" name="pk[]" value="<?php echo $file;?>">
                </td>
                <td width="87%" class="tablecolor2">
                    <?php echo '<b>'.$file.'</b><br><br>';?>
                </td>
              </tr>
          <?php }} ?>
          </table>
        </td>
        </tr>
    </table>
    </fieldset>
    <input type="checkbox" name="alldata" value="alldata" align="bottom" onClick="selectAllData('dumps');"> alle auswählen
    <input type="button" name="btn_del" value="Löschen" onClick="javascript:startDelete(1);" class="buttonhead100">

<?php }
?>
</div>
</body>
</html>