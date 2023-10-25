<?php
/*

    Function Library - functions.inc.php
    Author: Raimund Kulikowski / GS Software Solutions GmbH
    
    (c) 2004-2005 GS Software Solutions GmbH
    
    this code is NOT open-source or freeware
    you are not allowed to use, copy or redistribute it in any form
 
*/
/*if(!strpos($_SERVER["PHP_SELF"],"/dynsb/"))
{
  $currentDir = substr($_SERVER["PHP_SELF"],0,strrpos($_SERVER["PHP_SELF"],"/"));
}
else
{
  $currentDir = substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"/dynsb/"));
}

ire_once($_SERVER["DOCUMENT_ROOT"].$currentDir."/dynsb.path.inc.php");*/


//$path = substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"dynsb/"));
if(file_exists("dynsb/class/class.db.php"))
{
  require_once("dynsb/class/class.db.php");
}
elseif(file_exists("class/class.db.php"))
{
  require_once("class/class.db.php");
}
else
{
  //$path = substr($_SERVER["PHP_SELF"],0,strrpos($_SERVER["PHP_SELF"],"/"));
  if(file_exists("../../class/class.db.php"))
  {
    require_once("../../class/class.db.php");
  }
}


// dummy implementation for nls-support
function getCap($str, $languageIdNo = 1) {
  $dbVars = new dbVars();
	// connect to database server or die
	$svrConn = @mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb) or die("<br />aborted: can´t connect to '$dbServer'<br />");
	$svrConn->query("SET NAMES 'utf8'");
    /*
        read from db
    */    
    $SQL = "SELECT capName".$languageIdNo." AS caption FROM ".DBToken."caption WHERE capName1 = '".trim($str)."'";
    $qry = @mysqli_query($svrConn,$SQL);
    $num = @mysqli_num_rows($qry);    
    if($num != 0) {
        $obj = @mysqli_fetch_object($qry);
        $str = trim($obj->caption);
    }

    return $str;
}

// usefull for testing scripts
// display all GET variables, available for php script
function displayGETvars() {
    global $_GET;
	if (!empty($_GET)) {
        foreach($_GET as $key=>$value) {
            echo $key." = ".$value."<br />";
        }
	}
}



// usefull for testing scripts
// display all POST variables, available for php script
function displayPOSTvars() {
    global $_POST;
	if (!empty($_POST)) {
        foreach($_POST as $key=>$value) {
            echo $key." = ".$value."<br />";
        }
	}
}


// usefull for testing scripts
// display all SESSION variables, available for php script
//A TS 19.02.2014: $_SESSION ist global und muss/darf nicht als Funktions-Parameter verwendet werden
//function displaySessionVars($_SESSION)  {
function displaySessionVars()  {
    if (!empty($_SESSION)) {
        reset($_SESSION);
        foreach($_SESSION as $key=>$value) {
            echo $key." = ".$value."<br />";
            if (is_object($_SESSION[$key])) { // if object show all object vars
                $obj_name =  $_SESSION[$key];
                $array = get_object_vars($obj_name);
                echo "&nbsp;&nbsp;&nbsp; * ".sizeof($array)." Fields * <br />";
                while (list($key1, $val1) = each($array)) {
                    echo "&nbsp;&nbsp;&nbsp; $key1 => $val1";
                    echo "<br />";
                }
	  	    }
            if (is_array($_SESSION[$key])) { // if array show all array vars
                $array = $_SESSION[$key];
                echo "&nbsp;&nbsp;&nbsp; * ".sizeof($array)." Fields * <br />";
                while (list($key1, $val1) = each($array)) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp; $key1 => $val1";
                    echo "<br />";
    				if (is_object($_SESSION[$key][$key1])) { // if object show all object vars
    				    $obj_name1 =  $_SESSION[$key][$key1];
    					$array2 = get_object_vars($obj_name1);
    					echo "&nbsp;&nbsp;&nbsp; * ".sizeof($array2)." Fields * <br />";
    					while (list($key3, $val3) = each($array2)) {
                            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $key3 => $val3";
                            echo "<br />";
    					}
    				}
                }
            }
        }
    }
}



// usefull for testing scripts
// display all variables in a given class, available for php script
function displayVarsOfClass($obj) {
    $arr = get_object_vars($obj);
    while(list($prop, $val) = each($arr)) {
        echo "\t$prop = $val<br />\n";
    }
}



//
// return userName for a given userIdNo
function getUserName($usrIdNo = 0) {
	$dbVars = new dbVars();
	// connect to database server or die
	$svrConn = @mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb) or die("<br />aborted: can´t connect to '$dbServer'<br />");
	$svrConn->query("SET NAMES 'utf8'");
	$num = 0;
	$usrName = "";

	$fSQL = "SELECT userName FROM ".DBToken."user WHERE userIdNo = '".$usrIdNo."'";
	$qry =  @mysqli_query($svrConn,$fSQL);
    $num =  @mysqli_num_rows($qry);

    if ($num > 0) {
    	$obj =  @mysqli_fetch_object($qry);
		if ($obj->userName != NULL) {
			$usrName = $obj->userName;
		}
    }
	return $usrName;
}



// provide the real number, and the number of
// digits right of the decimal you want to keep.
function truncate ($num, $digits = 0) {
   $shift = pow(10 , $digits);
   return ((floor($num * $shift)) / $shift);
}



// replace commas through points
function replCtP($str) {
    $returnVal = $str;
    if (strlen(trim($str)) > 0) {
        $returnVal = str_replace(",",".", $str);
    }
    return $returnVal;
}



// replace points through commas
function replPtC($str) {
    $returnVal = $str;
    if (strlen(trim($str)) > 0) {
        $returnVal = str_replace(".",",", $str);
    }
    return $returnVal;
}



// replace spaces th. "html nonebreakingspaces"
function printnbsp($val) {
    $returnVal = "";
    if(strlen(trim($val)) > 0) {
        $returnVal= str_replace(" ","&nbsp;",$val);
    }
    return $returnVal;
}



// if empty, return "&nbsp;"
function dbFieldVal($val) {
 $returnVal = "&nbsp;";  // default
 if ((strlen(trim($val)) > 0)) {
     $returnVal= printnbsp($val);
 }
 return $returnVal;
}



// display SQL query string
function showSQL($val) {
    if(!empty($val)) echo "<br /><span class=\"text\" style=\"color: #FF0000\">$val</span><br />";
}


###
#
# date_mysql2german - wandelt ein MySQL-DATE (ISO-Date)
#                     in ein traditionelles deutsches Datum um.
#
function date_mysql2german($datum = '') {
	$ret = $datum;
	$pos = strpos ($datum, "-");
	if ($pos > 0) {
		// found...
		list($jahr, $monat, $tag) = explode("-", $datum);
		return sprintf("%02d.%02d.%04d", $tag, $monat, $jahr);
	}
	return $ret;
}

###
#
# date_german2mysql - wandelt ein traditionelles deutsches Datum
#                     nach MySQL (ISO-Date).
#
function date_german2mysql($datum = '') {
	$ret = $datum;
	$pos = strpos ($datum, ".");
	if ($pos > 0) {
		// found...
		list($tag, $monat, $jahr) = explode(".", $datum);
		return sprintf("%04d-%02d-%02d", $jahr, $monat, $tag);
	}
	return $ret;
}

###
#
# timestamp_mysql2german - wandelt ein MySQL-Timestamp
#                          in ein traditionelles deutsches Datum um.
#
function timestamp_mysql2german($t) {
    return sprintf("%02d.%02d.%04d",substr($t, 6, 2),substr($t, 4, 2),substr($t, 0, 4));
}


//
// returns array with avail. languages in database
function getLanguages() {
	$avLng = array();
	$dbVars = new dbVars();
	// connect to database server or die
	$svrConn = @mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb) or die("<br />aborted: can´t connect to '$dbServer'<br />");
	$svrConn->query("SET NAMES 'utf8'");
    $num = 0;
	$fSQL = "SELECT langIdNo FROM dsb_6language";
	$qry =  @mysqli_query($svrConn,$fSQL);
	while ($obj = @mysqli_fetch_object($qry)) {
	    if ($obj->languageIdNo != NULL) {
			$avLng[$num] = $obj->languageIdNo;
			$num++;
		}
	}
	return $avLng;
}



function getFormatedPersonName($title='', $firstName='', $middleName='', $lastName='') {
    $str = "";
    $x1 = " ";
    $x2 = " ";
    $x3 = " ";
    $title = "";
    if($title == "") $x1 = "";
    if($firstName == "") $x2 = "";
    if($middleName == "") $x3 = "";
    $str = trim($title.$x1.$firstName.$x2.$middleName.$x3.$lastName);
    return $str;
}


function getmonth($number,$lmode) {
	// hier muß für multilingual eine translation hin
	if($lmode == 'deu' ) $months = array (Januar,Februar,März,April,Mai,Juni,Juli,August,September,Oktober,November,Dezember);
	if($lmode == 'eng' ) $months = array (January,February,March,April,May,June,July,August,September,October,November,December);
	if($lmode == 'rus') $months = array (ßíâàðü, Ôåâðàëü, Ìàðò, Àïðåëü, Ìàé, Èþíü, Èþëü, Àâãóñò, Ñåíòÿáðü, Îêòÿáðü, Íîÿáðü, Äåêàáðü);
	return($months[$number-1]);
}



function getentity($table,$entity,$clause) {
	//init
	$res="";
	$dbVars = new dbVars();
	// connect to database server or die
	$svrConn = @mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb) or die("<br />aborted: can´t connect to '$dbServer'<br />");
	$svrConn->query("SET NAMES 'utf8'");
	$sql="SELECT ".$entity." FROM  ".$table;
	if($clause != "") $sql = $sql." WHERE ".$clause;

	$qry =  @mysqli_query($svrConn,$sql);
	$row = @mysqli_fetch_row($qry);

	$res = $row[0];
	//echo $sql;
	return($res);
}



function datetodot($datum) {
	$year = strtok($datum,"-");
	$month = strtok("-");
	$day = strtok("-");
	$out = $day.".".$month.".".$year;
	return($out);

}



//BT 18.11.03
function setentity($table,$entity,$value,$clause="") {
	//init
	$res="";
	$dbVars = new dbVars();
	// connect to database server or die
	$svrConn = @mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb) or die("<br />aborted: can´t connect to '$dbServer'<br />");
	$svrConn->query("SET NAMES 'utf8'");
	$sql="UPDATE ".$table." SET ".$entity." = ".$value;	
	if ($clause != "") $sql = $sql." WHERE ".$clause;
	$qry = @mysqli_query($svrConn,$sql);
	return($res);
}

function isCustExists($custId) {
	$dbVars = new dbVars();
	// connect to database server or die
	$svrConn = @mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb) or die("<br />aborted: can´t connect to '$dbServer'<br />");
	$svrConn->query("SET NAMES 'utf8'");
	$sql = "SELECT * FROM ".DBToken."customer WHERE cusIdNo = '".$custId."';";
	$qry = @mysqli_query($svrConn,$sql);
	$count = @mysqli_num_rows($qry);
	return($count > 0);	
}


/*
    do HTTP POST request via PHP --> kulikowski
    
    how to prepare the poststring:
    ------------------------------------------------------------
    foreach($formdata AS $key => $val) { 
        $poststring .= urlencode($key)."=".urlencode($val)."&"; 
    }
    $data = substr($poststring, 0, -1);
    post2Host('www.somewhere.com', 'include/check.php', 'www.whatever.de', $data);
*/
function post2Host($host, $path, $referer, $data) {
/*
    $fp = fsockopen($host, 80);
    fputs($fp, "POST $path HTTP/1.1\r\n");
    fputs($fp, "Host: $host\r\n");
    fputs($fp, "Referer: $referer\r\n");
    fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
    fputs($fp, "Content-length: ". strlen($data) ."\r\n");
    fputs($fp, "Connection: close\r\n\r\n");
    fputs($fp, $data);
    while(!feof($fp)) {
        $str .= fgets($fp, 128);
    }
    fclose($fp);

    return $str;
*/
}

?>
