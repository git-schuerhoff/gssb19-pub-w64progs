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


if(isset($_GET['impdir'])) {
	$impdir = $_GET['impdir'];
//  $impdir = str_replace("\\\\", "\\", $impdir);
} 
else 
$impdir= "C:\\temp\\";

if(isset($_GET['go']) && intval($_GET['go']) == 1) {
    $go = 1;
    unset($_GET['go']);
} else {
    $go = 0;
}

function displayTree($array) {
     $newline = "<br>";
     foreach($array as $key => $value) {    //cycle through each item in the array as key => value pairs
         if (is_array($value) || is_object($value)) {        //if the VALUE is an array, then
            //call it out as such, surround with brackets, and recursively call displayTree.
             $value = "Array()" . $newline . "(<ul>" . displayTree($value) . "</ul>)" . $newline;
         }
        //if value isn't an array, it must be a string. output its' key and value.
        $output .= "[$key] => " . $value . $newline;
     }
     return $output;
}

// hier rein
function checkbackR($string)
{
	
	
	$str1=str_replace("\n", "\r\n", $string);
	$str2=str_replace("&gt;", ">", $str1);
	$str3=str_replace("&lt;", "<", $str2);
	$str4=str_replace("&amp;", "&", $str3);
	
	return($str4);
}

function sqlSafeString($param) {
    return (NULL === $param ? "''" : "'".checkbackR(addslashes($param))."'");
}

function sqlSafeNULL($param) {
    return (NULL === $param ? "NULL" : "'".checkbackR(addslashes($param))."'");
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
	full = (screen.width / 100) * 95;
	parent.progressFrame.setPBar(full);
}

function startImport() {
	if(confirm("<?php echo L_dynsb_DBImport_Sicher;?>") == true) {
		switchButton('btnExport', 1);
		switchButton('btndirset', 1);
		switchButton('btnlogin', 1);
//    changeCursor(1);
  	setTLight('redorange');
		document.location.href = 'importDBFrameTop.php?go=1&impdir='+document.imptop.impdir.value;
//		parent.dataFrame.location.href = 'importDBFrameControl.php?go=1&impdir='+document.imptop.impdir.value;
	} 
}

function showMessageClearDB(msg) {
	alert(msg);
}

function setImportDir() {
    var iMyWidth;
    var iMyHeight;   
    iMyWidth = Math.round((window.screen.width/2) - (400/2 + 10));
    iMyHeight = Math.round((window.screen.height/2) - (230/2 + 40));   
        var winNew = window.open('impdirsel.php?lang=<?echo $lang;?>', 'Directory',"height=230,width=400,menubar=no,location=no,resizable=no,scrollbars=no,left="+iMyWidth+",top="+iMyHeight+"");
 
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
// Aenderung GW 20.04.2006 Sanduhr setzen setzen
function changeCursor(mode) {
	// setzt das Cursorsymbol auf normal Mode =0 oder Sanduhr Mode =1
	elem = document.getElementById("frimptop");
    if(mode == 1) {elem.style.cursor="wait";} else {elem.style.cursor="default";}
    elem1 = document.getElementById('taLog');
    if(mode == 1) {elem1.style.cursor="wait";} else {elem1.style.cursor="default";}

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
			alert('<?php echo L_dynsb_DBImport_ok;?>');
			switchButton('btnExport', 1);
//			switchButton('btnLogin', 0);
			setTLight('green');
			document.getElementById('pinfo').value = "";
		} else {
			alert('<?php echo L_dynsb_DBImport_Err;?>');
			switchButton('btnExport', 1);
			switchButton('btnLogin', 1);
			setTLight('red');
		}
//    changeCursor(0);
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

<body bgcolor="#FFFFFF" text="#000000" style="cursor : default" id="frimptop">
<form name="imptop">
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

  <h1><?php echo L_dynsb_ImportDatabaseHead?></h1>
  <h2><?php echo L_dynsb_ImportDatabaseHead2?></h2>
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
            <td class="tablecolor2" width="70%" align="left"><textarea style="cursor : default" id="taLog" class="textareaSetup" name="logtext" rows="20" readonly></textarea></td>
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
      <td class="tablecolor2">
        <table width="100%">
          <tr>
	 	 	<td align="right" width="30%"><?php echo L_dynsb_ImportDirectory;?> :</td>
			<td class="tablecolor2" width="30%"><input type="text" id="textdir" class="inputbox300" disabled=1 name="impdir" onchange="javascript:setIMportDir2();" value="<?php echo str_replace("\\\\", "\\", $impdir);?>"></td>	
			<td class="tablecolor2">&nbsp;</td> 
	 	 </tr>
     <tr><td><br /></td>
	 	 </tr>
     <tr>
			<td align="center"><input type="button" style="cursor : default" id="btnExport" class="button200" name="btn_startImport" onclick="javascript:startImport();" value="<?php echo L_dynsb_ButStartImport;?>"></td>
			<td align="center"><input type="button" style="cursor : default" id="btndirset" class="button200" name="btn_dir" onclick="javascript:setImportDir();" value="<?php echo L_dynsb_ButAdjustImportDirectory;?>"></td>
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

  // Z㧬e alle zu importierenden Tabellen !
  //TS 01.03.2017: Aber nur GS ShopBuilder-Tabellen!!!
  //$mainSQL = "show tables";
  $mainSQL = "SHOW TABLES LIKE '".DBToken."%'";
  $mainqry = @mysqli_query($link,$mainSQL);
  // Maximale Tabellenanzahl zum export
  // -6  ( ausgelassene Tabellen !)
  $max_tab = @mysqli_num_rows($mainqry);
  
  // lade array 
  $tabnr=0;
  
  //ermitteln um welche Tabelle es sich handelt
  while ($mainrow=@mysqli_fetch_row($mainqry))
  {
  	$tabnr++;
  	$TAB[$tabnr]=	$mainrow[0];
  
  }
  
  $tabnum = 1;

  // Beginn oberste for-Schleife
	for ($tabnum=1;$tabnum<=$max_tab;$tabnum++)
//	for ($tabnum=1;$tabnum<=2;$tabnum++)
  {
  
  $currentTable= $TAB[$tabnum];
  
  if($tabnum == 1) 
  {
      // Aenderung GW 24.04.2006 Sanduhr setzen 
//      echo "changeCursor(1);\n";     
//      echo "changeCursor(1);\n";
  
      echo "setPInfo('Import: Tabelle ".$currentTable."');\n";
      echo "setState(".($tabnum).");\n";
      //echo "parent.importDBFrame.addLog(statusStr, 1, 0);\n";
  
  }
  
  //initialisiere 
  $sum_imp_ds=0;
  $sum_imp_tab=0;
  $sum_imp_tab_ds=0;
  $err=0;
  
  $logf = "log/import_log_".date("m.d.y").".log";
  $logs = "log/import_sql_".date("m.d.y").".log";
  if ($tabnum <=1){
  $file_log = fopen ($logf, "w");
  $file_sql = fopen ($logs, "w");
  }else 
  {
  $file_log = fopen ($logf, "a");
  $file_sql = fopen ($logs, "a");	
  }
  // Eingefuegt GW 20.04.2006
  fputs($file_log,"===========================================================================\n");
  fputs($file_log,"-----> Import der Tabelle ".$currentTable." begonnen!\n");
  
  //TS 01.03.20127: Nur GSSB-Tabellen!!!
  //$mainSQL = "show tables";
  $mainSQL = "SHOW TABLES LIKE '".DBToken."%'";
  $mainqry = @mysqli_query($link,$mainSQL);
  
  
  $break=false;
  
  $endpart=0;
  $part=0;
  
  while($endpart==0){        // oberste While Schleife
  $part++;
  $spart=sprintf("%04d",$part);
  	
  	//if ($err != 0) { $IMP_ERR =1 ; break;}
  	
  	$tab= $currentTable;
  	$file = $impdir."".$tab."_".$spart.".xml";
      // Aenderung GW 13.04.2006: Alte Importdateien haben den Part nicht in dem Namen
  
      $flag_keine_datei=false;    // Aenderung GW 13.04.2006
  	if (!file_exists($file)) 
      { 
          // Aenderung GW 13.04.2006: Alte Importdateien haben den Part nicht in dem Namen
          // deshalb muss im Fall Part = 1 evtl. der alte Dateiname verwendet werden
          if($part==1)
          {
              $file = $impdir."".$tab.".xml";
              if (!file_exists($file)) 
              { 
                  $file=$file_neu;
                  $flag_keine_datei=true;
                  break;
              } else
              {
                  // Ueberpruefung, ob Datei zu gross ist: 
                  // Wenn ja, muss sie gesplittet und Uebersetzt werden
                  $datei_max_gr=200000;
                  $groesse=filesize ( $file );
                  if ($groesse>=$datei_max_gr) 
                  {
                      fputs($file_log,"Uebersetze Alt-Datei       : ".$file."!\n");
                      $anzahl=split_xml_file($file, $datei_max_gr);
                      $file=$file_neu;
                      //echo "\nanzahl=$anzahl\n";
                      if ($anzahl == 0) 
                      {
                          fputs($file_log,"Datei konnte nicht Uebersetzt werden!\n");
                          echo "Datei konnte nicht Uebersetzt werden!\n";
                          $flag_keine_datei=true;
                          break;
                      } else
                      {
                          fputs($file_log,"Datei wurde in $anzahl Teile aufgesplittet.\n");
                      }
                  } 
  
              } // else if altes File existiert nicht
          } else // part != 1
          {
                  break;
          }
      }  // if neues File existiert nicht
  	$data="";
  	$handle = @fopen ($file, "r");
  
  	if ($handle == false) $break=true;
  
    //if (!$break)	
    //{
    fputs($file_log,"Lade                       : ".$file."!\n");
  
  	while (!feof($handle)) {
  	     $line= fgets($handle, 4096);
  	    
  	     $data.=$line;
           // Aenderung GW 13.04.2006: Alte Importdateien sind nicht in der Groesse beschraenkt
           // Der Parser kann keine Dateien groesser als (ca.) 5MByte verarbeiten
           if (strlen($data) > 5000000) 
           {
              $break=true;
              echo " Datei ".$file." ist zu gross";
              break;
           }
  	}
  	fclose ($handle);
  
    if ($break)    { break; }
      
    fputs($file_log,"Parse                      : ".$file."!\n");
    
    
    $parser = xml_parser_create();
    
    //xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
    xml_parser_set_option($parser,XML_OPTION_TARGET_ENCODING,'ISO-8859-1');
    xml_parse_into_struct($parser,$data,$vals,$tags);
  	
    xml_parser_free($parser);
    $data="";// Aenderung GW 13.04.2006
    
    fputs($file_log,"Importiere Daten aus Datei : ".$file."!\n");
    
    $sum_imp_tab++;
    $IMP_ERR +=$err;
    $tabflg=false;
    
    //setze $sum_imp_tab_ds zur�   if ($part==1)
    	$sum_imp_tab_ds=0;
    
//A UR Test
      //    // so kann der XML-Baum untersucht weren.
      //    $output = displaytree($vals);
      //    fputs($file_log,$output);
//E  Test
    foreach($vals as $key=>$entity )
    {		
      // Aenderung GW 13.04.2006: Alte Importdateien haben den Part nicht in dem Namen
      //  und auch keinen Eintrag in den XML-Dateien
      //echo "<br />\n-----Anfang foreach-------<br />\npart=$part<br />\n--------<br />\n";
//  echo "setPInfo('xxx:  ".$entity[tag]."yyy: ".$entity[type]."',0,1);\n";
     if ($file_neu == $file) 
      {   // neue XML-Dateien
      if ($entity[tag] == "ENDPART") 
      		$endpart= $entity[value];
          
      } else
      {   // alte XML-Dateien
          $endpart=1;
      }	
      if ($err != 0) { $IMP_ERR =1 ; break;}
  		//print_r($entity);
// echo "setPInfo('xxx:  ".$entity[tag]."',0,1);\n";
  		if (($entity[tag] == "TABLE") && (!$tabflg) &&
  		($entity[type] == "open" || $entity[type] == "complete" )) 
  		{						
  			$tabflg=true;
  			$tabname= $entity[value];
  			$sql_ORG_struct="SHOW COLUMNS FROM ".$tabname;
  			$qry = @mysqli_query($link,$sql_ORG_struct);
  			
  			$colnum=0;
  			
  			fputs($file_log,"\t->Starte mit dem Import der Tabelle ".$tabname."!\n");
  			fputs($file_sql,"\t->Starte SQL-Statements der Tabelle ".$tabname."!\n");
  			
  			// LADE Orginal Tabellen Struktur
  			while($ORG_db = @mysqli_fetch_object($qry))	
  			{												
  				$colnum++;
  				$ORG_STRUCT[$colnum]= strtolower($ORG_db->Field);
  				$ORG_NULL[$colnum]=strtolower($ORG_db->Null);
  				$ORG_TYPE[$colnum]=strtolower($ORG_db->Type);							
  			}			
  			// unterscheide zwischen update oder Datenaustausch
  			if ($tabname=="tabsequence")
  			{
  				$sql_up =  "UPDATE 	".$tabname." SET ";			
  			}	
  			elseif($part==1){		
  				// leere Datenbanktabelle
  				$sql_trunc = " TRUNCATE TABLE ".$tabname." ";
  				
  				fputs($file_sql,"\t\t->SQL: ".$sql_trunc."\n");
  				
  				$result = mysqli_query($link,$sql_trunc);			
  				if (!$result) 
  				{ 
  					$err++;
  					fputs($file_log,"\t\t->FEHLER beim Leeren der Tabelle ".$tabname."!\n");
  					fputs($file_sql,"\t\t->SQL_ERROR: ".mysqli_error($link)."\n");
  					
  				}
  				$sql_start = " INSERT INTO ".$tabname." ";						
  			}
  			else{
  				$sql_start = " INSERT INTO ".$tabname." ";	
  			}
  		}								
  		if( ($entity[tag] == "STRUCT") && ($tabflg) && 
  			($entity[type] == "open" || $entity[type] == "complete")) 
  		{
          	$dataflg=true;
          	//echo 2;      
  		}
  		
  		if( ($entity[tag] == "COL") && ($tabflg) && ($dataflg) && 
  			($entity[type] == "open" || $entity[type] == "complete")) 
  		{       	
  			$IMP_STRUCT[$entity[attributes][NUM]]=strtolower($entity[value]);	
  		}
  		
  		if( ($entity[tag] == "DATA") && ($tabflg) && 
  			($entity[type] == "open" || $entity[type] == "complete")) 
  		{
  		//vergleichen			
  			$diff_struct=array_diff($ORG_STRUCT,$IMP_STRUCT);						
  			$difflg =true;
  			$rowflg = false;
  		}		
  			
  		if(($entity[tag] == "ROW") && ($dataflg) && ($difflg) && (!$rowflg) &&
  		($entity[type] == "open" || $entity[type] == "complete")) 
  		{			
  		   $rownum = $entity[attributes][NUM];
  		   $sql_out1 = "values ( ";
  		    $sql_out = "";
  		   $rowflg=true;
  		}
 
   		if(($entity[tag] == "DS") && ($dataflg) && ($difflg) && ($rowflg) &&
  		($entity[type] == "open" || $entity[type] == "complete")) 
  		{			
  			$Spaltennummer = $entity[attributes][NUM];
  			if ($diff_struct[$Spaltennummer])
  			{ //hier k㬥 eine spalte dazu !!!!
  				$sql_out .= "'',";
  				$chg_pos++;
  			} 
  			
  			if (($tabname == "tabsequence" )&& ($IMP_STRUCT[$Spaltennummer] == "tablename"))
  			{
  				$sql_where = " WHERE tableName = ".sqlSafeString($entity["value"]); }
  			if (($tabname == "tabsequence" )&& ($IMP_STRUCT[$Spaltennummer] == "lastusedidno"))
  			{
  				$sql_up1=" lastUsedIdNo = ".sqlSafeString($entity["value"]).",chgApplicId = 'Altdaten-Import', chgDate = curdate(), chgTime = curtime() ";
  			}
  			
  			if (
  				($ORG_NULL[$Spaltennummer]=="yes")&&
  				(($ORG_TYPE[$Spaltennummer][0]=='c')||
  				($ORG_TYPE[$Spaltennummer][0]=='v') )  )
  			{  
  				//$sql_out .= sqlSafeNULL(($entity[value])).",";
  				$sql_out .= sqlSafeString(($entity[value])).",";
  			}else {
  				if ($entity[value]=="") {$sql_out .= "'',"; }
  				else { $sql_out .= sqlSafeString(($entity[value])).","; }
  				
  			$last_ds = 	$Spaltennummer + $chg_pos;							
  			$dsflg=true;
  			}
  		}	
		
  		if(($entity[tag] == "ROW") && ($rowflg) &&
  		($entity[type] == "close" ) )
  		{	
  			// r�tialisierung
  			$chg_pos = 0;
  					
  			//��b nicht am ende der tabelle Spalten hinzugef�rden !!!
  			while ($diff_struct[$last_ds+1])
  			{
  				$sql_out .= "'',";
  				$last_ds++;
  			}			
  			// das letzte "," entfernen !
  			$len = strlen($sql_out);
  			$sql_out[$len-1]=" ";
  			$sql_out .=" ) ";
  			
  			$dsflg = false;		
  			$rowflg = false;
  			$endsqlflg = true;			
  		}
			
  		if (	$endsqlflg )
  		{			
  			if ($tabname=="tabsequence")			
  				$sql_voll = $sql_up.$sql_up1.$sql_where; 				
  			else
  				$sql_voll= $sql_start.$sql_out1.$sql_out;
  			
  				
  
  			fputs($file_sql,"\t\t-> SQL: ".$sql_voll."\n");	
  			fputs($file_log,"\t\t-> Schreibe Datensatz Nr. ".($sum_imp_tab_ds+1)." in die Tabelle ".$tabname."!\n");
  
  			
    		// Hier geht's weiter
  			$result = mysqli_query($link,$sql_voll);						
  			if ($result==0) 
  				{ 
  					$err++;
  					
  					fputs($file_log,"\t\t->FEHLER beim Einschreiben des Datensatzes Nr. ".($sum_imp_tab_ds+1)." in die Tabelle ".$tabname."!\n");
  					fputs($file_sql,"\t\t-->SQL_ERROR: ".mysqli_error($link)."\n");
  			}
  			
  			
  			$sum_imp_tab_ds++;
  			$sum_imp_ds++;
  			$endsqlflg =false;
  			$sql_voll ="";$sql_up1 ="";$sql_where ="";
  			$sql_out1 ="";$sql_out ="";
  		}				

    }
      
    fputs($file_log,"---------------------------------------------------------------------------\n");
   if ($sum_imp_tab==1)
    	fputs($file_log,"REPORT: Es wurde insgesamt ".$sum_imp_tab." Tabelle importiert!\n");
    else 
    	fputs($file_log,"REPORT: Es wurden insgesamt ".$sum_imp_tab." Tabellen importiert!\n");
    if ($sum_imp_ds==1)
    	fputs($file_log,"REPORT: Es wurde insgesamt ".$sum_imp_ds." Datensatz importiert!\n");
    else 
    	fputs($file_log,"REPORT: Es wurden insgesamt ".$sum_imp_ds." Datens㳺e importiert!\n");
    
    // Eingefuegt GW 20.04.2006
    fputs($file_log,"------------------------   " 
                        . date("Y-m-d - H:i:s") . "   ------------------------\n");
    fputs($file_log,"---------------------------------------------------------------------------\n");
  // fclose($file);  // auskommentiert GW 20.4.2006
  }       // oberste while Schleife

  // Eingefuegt GW 20.04.2006
  fputs($file_log,"-----> Import der Tabelle ".$currentTable." beendet!\n");
  
  fclose($file_log);
  fclose($file_sql);
  
  if($flag_keine_datei) // Aenderung GW 13.04.2006 
  {
      echo "setPInfo('\\n".L_dynsb_Datei." ".$file_neu.": ".L_dynsb_NotFound."!');\n";
      echo "setState(".($tabnum).");\n";
  } else 
  {
      echo "setPInfo('\\n".L_dynsb_Datei." ".$file." ".L_dynsb_Gelesen."!');\n";
      echo "setState(".($tabnum).");\n";
  }
  
  //echo "parent.progressFrame.setPInfo('\\nImport: Tabelle ".$currentTable."');\n";
  //echo "parent.progressFrame.setState(".($tabnum).");\n";
  echo "setPInfo('\\n".L_dynsb_WrittenRecords.": ".$sum_imp_tab_ds."');\n";
  echo "setState(".($tabnum).");\n";
  echo "calcBar($max_tab);\n";
  //echo "changeCursor(0)";
  } // Ende oberste For-Schleife
}
echo "</script>";
?>
</html>
