<?php 

require("../include/login.check.inc.php");
require_once("../include/functions.inc.php");
require("../../conf/db.const.inc.php");

/***************** Sprachdatei ************************************************/
if (!isset($_REQUEST['lang']) || strlen(trim($_REQUEST['lang'])) == 0)
{
    $lang = "deu";
}
else
{
	$lang = $_REQUEST['lang'];
	if(!file_exists("../lang/lang_".$lang.".php"))
  {
    $lang = "deu";
  }
}

include("../lang/lang_".$lang.".php");

// connect to database server or die
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase)
   or die("<br>aborted: can´t connect to '$dbServer' <br>");
$link->query("SET NAMES 'utf8'");

$chgApplicId = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1)); // script name
if (!isset($_SESSION['SESS_userIdNo']) || strlen(trim($_SESSION['SESS_userIdNo'])) == 0) {
  die ("<br>error: missing session parameter!<br>");
} else {
	$SESS_userIdNo = $_SESSION['SESS_userIdNo'];
}
if (!isset($_SESSION['SESS_userLogin']) || strlen(trim($_SESSION['SESS_userLogin'])) == 0) {
  die ("<br>error: missing session parameter!<br>");
} else {
	$SESS_userId = $_SESSION['SESS_userId'];
}
if (!isset($_SESSION['SESS_languageIdNo']) || strlen(trim($_SESSION['SESS_languageIdNo'])) == 0) {
  die ("<br>error: missing session parameter!<br>");
} else {
	$SESS_languageIdNo = $_SESSION['SESS_languageIdNo'];
}

$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";


if(isset($_GET['expdir'])) {
	$expdir = $_GET['expdir'];
//  $expdir = str_replace("\\\\", "\\", $expdir);

//	die ("xxx=".$expdir);
} 
else 
$expdir= "C:\\temp\\";


if(isset($_GET['go']) && intval($_GET['go']) == 1) {
    $go = 1;
    unset($_GET['go']);
} else {
    $go = 0;
}

// Dateien erzeugen 
function put2file($record, $file, $recordnum = 0) {
	if($recordnum == 0) {
		$wf = fopen($file, "w");
		fclose($wf);
	}
    $wf = fopen($file, "a");
    fputs($wf, $record);
    fclose($wf);
    return $recordnum + 1;
}

// XML entitys austauschen  
function chg_entity($str)
{
	// ersetzen von < in &lt; und >in &gt;		
	$str1=str_replace("<", "&lt;", $str);
	$str2=str_replace(">", "&gt;", $str1);	
	$str3=$str2;
	$str4=str_replace("&","&amp;",$str3);
	$str = $str4;
	return ($str);
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_Carrier;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software AG">
    <link rel="stylesheet" type="text/css" href="../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2012 GS Software AG">
    <script type="text/javascript">
<!--
//A UR  26.11.2012 (3)
var gmastertablewith= 950;
var gFull = (gmastertablewith / 100) * 95;
var gStateAct = 0;
var gError = 0;
var gProgressStatus = 0;
var gDatabase = 0;   // nicht importiert
var gMySQL = 1;
//E UR

function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);

function refreshPBar() {
//	full = (screen.width / 100) * 95;
//	setPBar(full);
}

function startExport() {	
		switchButton('btnExport', 1);
  	setTLight('redorange');
//A UR 26.11.2012  	
//		parent.dataFrame.location.href = 'exportDBFrameControl.php?go=1&expdir='+document.exptop.expdir.value;
//    alert (document.exptop.expdir.value);
		document.location.href = 'exportDBFrameTop.php?go=1&expdir='+document.exptop.expdir.value;
//E UR
}


function showMessageClearDB(msg) {
	alert(msg);
}

function setExportDir2() {
    var iMyWidth;
    var iMyHeight;   
    iMyWidth = Math.round((window.screen.width/2) - (400/2 + 10));
    iMyHeight = Math.round((window.screen.height/2) - (230/2 + 40));   
        var winNew = window.open('expdirsel.php?lang=<?echo $lang;?>', 'Directory',"height=230,width=400,menubar=no,location=no,resizable=no,scrollbars=no,left="+iMyWidth+",top="+iMyHeight+"");
 
//    var winNew = window.open('expdirsel.php', 'ExportVerzeichnis',"height=180,width=400,menubar=no,location=no,resizable=no,scrollbars=no,left="+iMyWidth+",top="+iMyHeight+"");
    winNew.focus();
}


function switchButton(btn, mode) {
	
	elem = document.getElementById(btn);
	elem.disabled = mode;
    if(mode == 1) {elem.style.cursor="wait";} else {elem.style.cursor="default";}
}

function addLog(str, eol, stat) {
	rowlen = 58;
	dots = "";
	linebreak = "\n";
	if(eol == 0) linebreak = "";
	if(stat == 1) {
		strlen = str.length;
		dotlen = rowlen - strlen;
		for(i = 0; i < dotlen; i++ ) {
			dots = dots + ".";
		}
	} else {
		dots = "";
	}
	elem = document.getElementById('taLog');
	val = elem.value;
	elem.value = val + str + dots + linebreak;
	end = elem.scrollHeight;
	elem.scrollTop = end;
}

function clearLog() {
	elem = document.getElementById('taLog');
	elem.value = "";
	end = elem.scrollHeight;
	elem.scrollTop = end;
}

function setTLight(name) {
	elem = document.getElementById('tlight');
	switch(name) {
		case "red":
			elem.src = '../image/ampel_rot.gif';
		break;
		case "redorange":
			elem.src = '../image/ampel_rot_orange.gif';
		break;
		case "orange":
			elem.src = '../image/ampel_orange.gif';
		break;
		case "green":
			elem.src = '../image/ampel_gruen.gif';
		break;				
		default:
			elem.src = '../image/ampel_rot.gif';
		break;
	}
}
//A UR 26.11.2012(2)
function setState(val) {
	gStateAct = val;
}

function setError(val) {
	gError = val;
}

function setDatabase(val) {
	gDatabase = val;
}

function setMySQL(val) {
	gMySQL = val;
}

function calcBar(gStateAll) {		
	val = 100 * (gStateAct/ gStateAll);
	elem = document.getElementById('ptext');
	elem.value = parseInt(val);
	setPBar(val);
	gProgressStatus = val;
	if(val >= 100) {
		if(gError == 0) {		
			alert('<?php echo L_dynsb_DBExport_ok;?>');
			switchButton('btnExport', 1);
			switchButton('btnLogin', 0);
			setTLight('green');
			document.getElementById('pinfo').value = "";
		} else {
			alert('<?php echo L_dynsb_DBExport_Err;?>');
			switchButton('btnExport', 1);
			switchButton('btnLogin', 1);
			setTLight('red');
		}
	}
}

function setPBar(val) {
	newval = (gFull / 100) * val;
	elem = document.getElementById('pbar');
	elem.width = newval;
}

function setPInfo(str) {
	elem = document.getElementById('pinfo');
	elem.value = str;
	addLog(str, 0, 1);
}

//E UR
// -->
</script>
</head>

<body bgcolor="#FFFFFF" text="#000000">
<form name="exptop">
<?php
require_once("../include/page.header.php");
?>
<DIV id="PGcarrierdetail">
	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="start" value="<?php echo $start;?>">
	<input type="hidden" name="backstart" value="<?php echo $backstart;?>">
	<input type="hidden" name="next" value="">
	<input type="hidden" name="nav" value="">
	<input type='hidden' name='act' value='a'>

  <h1><?php echo L_dynsb_ExportDatabaseHead?></h1>
  <h2><?php echo L_dynsb_ExportDatabaseHead2?></h2>
  <br />
  <br />
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td align="center">
            </td>
          </tr>
          <tr>
            <td class="tablecolor1">&nbsp;
            </td>
          </tr>
          <tr>
            <td class="tablecolor2">&nbsp;
            </td>
          </tr>
          <tr>
            <td class="tablecolor2" align="center">
              <table width="100%">
                <tr>
<?php
     if ($go == 1)
     {
			echo "<td class=\"tablecolor2\" width=\"20%\" align=\"center\"><img src=\"../image/ampel_rot_orange.gif\" id=\"tlight\">&nbsp;</td>";
     }
     else
			echo "<td class=\"tablecolor2\" width=\"20%\" align=\"center\"><img src=\"../image/ampel_rot.gif\" id=\"tlight\">&nbsp;</td>";
?>			
                  <td class="tablecolor2" width="70%" align="left"><textarea id="taLog" class="textareaSetup" name="logtext" rows="20" readonly></textarea></td>
            			<td class="tablecolor2">&nbsp;</td>
                </tr>
		          </table>
      		  </td>
        </tr>
        <tr>
          <td class="tablecolor2">&nbsp;
          </td>
        </tr>   
        <tr>
          <table width="100%">
           <td class="tablecolor2">
			&nbsp;
          </td>
          
          <tr>
	 	 	<td align="right" width="30%"><?php echo L_dynsb_ExportDirectory;?> :</td>
			<td class="tablecolor2" width="30%"><input type="text" id="textdir" class="inputbox300" disabled=1 name="expdir" onchange="javascript:setExportDir();" value="<?php echo str_replace("\\\\", "\\", $expdir);?>"></td>	
			<td class="tablecolor2" >&nbsp;</td>
       
	 	 </tr>
	 	 </tr>
     <tr><td><br /></td>
	 	 </tr>
     <tr>
			<td align="center"><input type="button" id="btnExport" class="button200" name="btn_startExport" onclick="javascript:startExport();" value="<?php echo L_dynsb_ButStartExport;?>"></td>
			<td align="center"><input type="button" id="btndirset" class="button200" name="btn_dir" onclick="javascript:setExportDir2();" value="<?php echo L_dynsb_ButAdjustExportDirectory;?>"></td>
			<td align="center"><input type="button" id="btnlogin" class="button200" name="btn_login" onclick="javascript:self.location.href='../help/about.php?lang=<?php echo $lang;?>';" value="<?php echo L_dynsb_ButToMainPage;?>"></td>

			
			</tr>
		  </table>
    </td>
        </tr>
        <tr>
          <td class="tablecolor2">
			&nbsp;
          </td>
        </tr>        

        </table>

</DIV>


<table border="0" class="frame" cellspacing="0" cellpadding="0" width="770">
<tr>
<td>

	<table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td class="tablecolor2">&nbsp;</td>
        </tr>
        <tr>
          <td class="tablecolor2">
          <table width="100%">
			<tr><td>&nbsp;</td><td class="progresstext70" align="center"><input type="text" id="ptext" name="ptextPrct" value="0" class="progresstext70">%</td><td>&nbsp;</td></tr>
		  </table>
          </td>
        </tr>
        <tr>
          <td class="tablecolor2">
          <table width="100%">
			<tr><td width="0%">&nbsp;</td><td align="center" bgcolor="#334499"><img src="blind.gif" id="pbar" width="1" height="0"></td><td width="950">&nbsp;</td></tr>
			<tr><td>&nbsp;</td><td align="center"></td><td>&nbsp;</td></tr>
		  </table>
          </td>
        </tr>
        <tr>
          <td class="tablecolor2">
          <table width="100%">
			<tr><td>&nbsp;</td><td align="center"><input type="text" id="pinfo" name="ptextInfo" value="" class="progressinfo500"></td><td>&nbsp;</td></tr>
		  </table>
          </td>
        </tr>
        <tr>
          <td class="tablecolor2">&nbsp;</td>
        </tr>        
	</table>

</td>
</tr>
</table>
<?php
require_once("../include/page.footer.php");
?>
</form>
</body>
<?php
echo "<script type=\"text/javascript\">\n";
if ($go == 1)
{
  // generelle Initialisierung
  $version = 14;
  $ec = 0;   //errorcode 
  
  // HEADER
  $xmlheader1 = "";
  $xmlheader1 .= "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"yes\"?>\n";
  $xmlheader1 .= "<gssbexport name=\"SHOP-DB\" type=\"struct\">\n";
  $xmlheader1 .= "\t<version>".$version."</version>\n";
  
  // FOOTER
  $xmlfooter = "";
  $xmlfooter = "</gssbexport>\n";		

  // Z㧬e alle zu exportierenden Tabellen !
  //TS 01.03.2017: Aber nur GS ShopBuilder-Tabellen
  //$mainSQL = "SHOW TABLES";
  $mainSQL = "SHOW TABLES LIKE '".DBToken."%'";
  $mainqry = @mysqli_query($link,$mainSQL);
  // Maximale Tabellenanzahl zum export
  // -6  ( ausgelassene Tabellen !)
  $max_tab = @mysqli_num_rows($mainqry);
		
  if ($tabnum > $max_tab)
		die;

  // lade array 
  $tabnr=0;
  while ($mainrow=@mysqli_fetch_row($mainqry))
  {
  	$tabnr++;
  	$TAB[$tabnr]=	$mainrow[0];
  }
  $tabnum = 1;
  $break=false;
	for ($tabnum=1;$tabnum<=$max_tab;$tabnum++)
  {
  	$xmlbody1 ="";
  	$str1 ="";	
  	$str2 ="";
  	$i=0;
    $currentTable= $TAB[$tabnum];

    if (!$break)
    {	
    	// max anzahl der parts
    	$anz= getentity($currentTable,"count(*) as ANZAHL","");
  
      //Structur
    	$xmlstruct="";
    	$xmlstruct .="\t<table>".$currentTable."</table>\n"; 
    	$xmlstruct .="\t<struct>\n";	

    	//struktur
    	$structsql="SHOW COLUMNS FROM ".$currentTable;
    	$structqry = @mysqli_query($link,$structsql);
    	while($structobj = @mysqli_fetch_object($structqry))
    	{
    		$i++;
    		$str1 .= "\t\t\t<col num=\"".$i."\">".$structobj->Field."</col>\n";
    	}
    	$xmlstruct .=$str1;
    	$xmlstruct .="\t</struct>\n";

      //global
      $limit=500;
      $start=0;	
      $exit=0;
      $part=0;	

      while($exit==0){	
      $part++;
      $spart=sprintf("%04d",$part);
      
      	$fname1 = $expdir."".$currentTable."_".$spart.".xml";
      	
      		$xmlbody1 ="";
      		$xmlbody1 ="\t<part>$spart</part>\n" ;
      		
      	if ($part*$limit>=$anz) {
      		$xmlbody1 .="\t<endpart>1</endpart>\n";
      		$exit=1;
      	}
      	else {
      		$xmlbody1 .="\t<endpart>0</endpart>\n";
      	}
      	
      	
      	$str2="";
      	
      	//daten 
      	$xmlbody1 .="\t<data>\n";
      	$datasql="SELECT * FROM ".$currentTable." limit ".$start.", ".$limit ;
      	$dataqry = @mysqli_query($link,$datasql);
      	while($datarow = @mysqli_fetch_row($dataqry))
      	{
      		$rw++;
      		$str2 .= "\t\t\t<row num=\"".$rw."\">\n";
      		foreach ($datarow as $key => $value)
      		{$str2 .= "\t\t\t\t<ds num=\"".($key+1)."\">".chg_entity($value)."</ds>\n";		
      		}
      		$str2 .= "\t\t\t</row>\n";
      	}
      
      	$start +=$limit;	
      	
      	$xmlbody1 .=$str2;
      	$xmlbody1 .="\t</data>\n";
      		
      $num = put2file($xmlheader1, $fname1, 0);
      $num = put2file($xmlstruct, $fname1, $num);
      $num = put2file($xmlbody1, $fname1, $num);
      $num = put2file($xmlfooter, $fname1, $num);
      $num=0;	
      
      }  //while

      echo "setPInfo('Export: ".L_dynsb_Tabelle." ".$currentTable."');\n";
      echo "statusStr = 'OK';";
      //echo "if(errFlg > 0) statusStr = 'ERROR'\n";
      echo "addLog(statusStr, 1, 0);\n";
  
  
      echo "setState(".($tabnum).");\n";
      echo "calcBar($max_tab);\n";
      echo "addLog(' ".L_dynsb_Datei." ".$fname1." ".L_dynsb_Geschrieben."!');\n";

    }
  } // for
//      echo "setState(".($tabnum).");\n";
}
else
{
//  setPBar(0);
}
echo "</script>";
?>
</html>
