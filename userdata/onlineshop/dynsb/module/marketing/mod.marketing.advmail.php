<?php
/******************************************************************************/
/* File: mod.availability.categories.php                                      */
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
$chgApplicId = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1)); // script name

if (!isset($_SESSION['SESS_userIdNo']) || strlen(trim($_SESSION['SESS_userIdNo'])) == 0) {
  die ("<br />error: missing session parameter!<br />");
} else {
	$SESS_userIdNo = $_SESSION['SESS_userIdNo'];
}
if (!isset($_SESSION['SESS_userLogin']) || strlen(trim($_SESSION['SESS_userLogin'])) == 0) {
  die ("<br />error: missing session parameter!<br />");
} else {
	$SESS_userId = $_SESSION['SESS_userId'];
}
if (!isset($_SESSION['SESS_languageIdNo']) || strlen(trim($_SESSION['SESS_languageIdNo'])) == 0) {
  die ("<br />error: missing session parameter!<br />");
} else {
	$SESS_languageIdNo = $_SESSION['SESS_languageIdNo'];
}

foreach($_REQUEST as $key => $value)
{
    $$key = trim($value);
}

$strcal = "de";
if($SESS_languageIdNo == 2) $strcal = "en";

if (isset($_REQUEST['cus_selection']))
{
	$cust_from = $_REQUEST['cust_from'];
	$cust_to = $_REQUEST['cust_to'];
	$cust_group1 = $_REQUEST['cust_group1'];
	$cust_group2 = $_REQUEST['cust_group2'];
	$cust_group3 = $_REQUEST['cust_group3'];
	
	$cust_arr = array();
	if(is_numeric($cust_from) && is_numeric($cust_to)) {
		if($cust_from < $cust_to) {
			for($i=$cust_from;$i<=$cust_to;$i++) {
				if(isCustExists($i)) {
					$cust_arr[] = $i;
				}
			}	
		} else {
			if(isCustExists($i)) {
				$cust_arr[] = $cust_from;
			}
			if(isCustExists($i)) {
				$cust_arr[] = $cust_to;
			}
		}	
	}
	if(!empty($cust_group1)) {
		$query = "SELECT custId FROM ".DBToken."cust_to_group WHERE cgId = '".$cust_group1."'";
		$ret = @mysqli_query($link,$query);
		$arr_group = array();
		while($obj = mysqli_fetch_object($ret)) {
			$arr_group[] = $obj->custId;
		}
		$cust_arr = array_merge($cust_arr, $arr_group);
	}
	if(!empty($cust_group2)) {
		$query = "SELECT custId FROM ".DBToken."cust_to_group WHERE cgId = '".$cust_group2."'";
		$ret = @mysqli_query($link,$query);
		$arr_group = array();
		while($obj = mysqli_fetch_object($ret)) {
			$arr_group[] = $obj->custId;
		}
		$cust_arr = array_merge($cust_arr, $arr_group);
	}
	if(!empty($cust_group3)) {
		$query = "SELECT custId FROM ".DBToken."cust_to_group WHERE cgId = '".$cust_group3."'";
		$ret = @mysqli_query($link,$query);
		$arr_group = array();
		while($obj = mysqli_fetch_object($ret)) {
			$arr_group[] = $obj->custId;
		}
		$cust_arr = array_merge($cust_arr, $arr_group);
	}
	if(empty($cust_arr)) {
		$cust_notice = L_dynsb_NoCustFound;
	}
	$cust_arr = array_unique($cust_arr);
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_Marketing;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
	 <script type="text/javascript"src="../../js/gslib.php"></script>
	 <script type="text/javascript" src="../../js/calendar.js"></script>
	<script type="text/javascript" src="../../js/calendar-<?php echo $strcal;?>.js"></script>
	<script type="text/javascript" src="../../js/calendar-setup.js"></script>
    <script language="JavaScript" type="text/javascript">
    function MM_reloadPage(init)
    {  //reloads the window if Nav4 resized
      if (init==true) with (navigator)
      {
        if ((appName=="Netscape")&&(parseInt(appVersion)==4))
        {
          document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage;
        }
      }
      else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
    }
    //--------------------------------------------------------------------------
    MM_reloadPage(true);
    //--------------------------------------------------------------------------
	function expandTextarea(ta) {
		var obj;
		obj = document.getElementById(ta);
		if (obj) {
			if (obj.rows < '50')
				obj.rows = 50;
			else
				obj.rows = 10;
		}
	}
    </script>
</head>
<body>
<?php
require_once("../../include/page.header.php");
?>
<div id="PGavailabilitycategories">
<h1>&#187;&nbsp;<?php echo L_dynsb_Marketing;?>&nbsp;&#171;</h1>

<?php

if(!isset($_REQUEST['cus_selection']) || empty($cust_arr))
{
?>
<p><?php if($cust_notice != '') echo $cust_notice; ?></p>
<form name="cusselect" action="mod.marketing.advmail.php" method="post">
<input type="hidden" name="lang" value="<?php echo $lang;?>">	
<table style="width: 400px;"> 	
	<tr>
		<td><?php echo L_dynsb_AktivitiesCustFromTo; ?></td>
  		<td><input type="text" id="cust_from" name="cust_from">
  		&nbsp;<br /><input type="text" id="cust_to" name="cust_to"></td>
  	</tr>
  	
  	<tr>
		<td><?php echo L_dynsb_AktivitiesCustGroup;?> 1</td>
  		<td><select name="cust_group1">
  		<option value="" selected>&nbsp;</option>
  		<?php
  			$SQL = "SELECT * FROM ".DBToken."custgroup";
			$qry = @mysqli_query($link,$SQL);
			while($obj = @mysqli_fetch_object($qry)) { 
		?>
		<option value="<?php echo $obj->cgId;?>"><?php echo $obj->cgName;?></option>		
		<?php } ?>
  		</select></td>
  	</tr>
  	<tr>
		<td><?php echo L_dynsb_AktivitiesCustGroup;?> 2</td>
  		<td><select name="cust_group2">
  		<option value="" selected>&nbsp;</option>
  		<?php
  			$SQL = "SELECT * FROM ".DBToken."custgroup";
			$qry = @mysqli_query($link,$SQL);
			while($obj = @mysqli_fetch_object($qry)) { 
		?>
		<option value="<?php echo $obj->cgId;?>"><?php echo $obj->cgName;?></option>		
		<?php } ?>
  		</select></td>
  	</tr>
  	<tr>
		<td><?php echo L_dynsb_AktivitiesCustGroup;?> 3</td>
  		<td><select name="cust_group3">
  		<option value="" selected>&nbsp;</option>
  		<?php
  			$SQL = "SELECT * FROM ".DBToken."custgroup";
			$qry = @mysqli_query($link,$SQL);
			while($obj = @mysqli_fetch_object($qry)) { 
		?>
		<option value="<?php echo $obj->cgId;?>"><?php echo $obj->cgName;?></option>		
		<?php } ?>
  		</select></td>
  	</tr>
 </table>
	<p><input type="submit" class="button" tabindex=29 name="cus_selection" value="<?php echo L_dynsb_Next;?>">&nbsp;</p>
</form>

<?php
}
?>

<?php
if(isset($_REQUEST['cus_selection']) && !empty($cust_arr))
{ ?>
	<form name="sendform" action="mod.marketing.advmail.send.php" method="post">
		
	<h2><?php echo L_dynsb_Sender;?></h2>
	<p>
  	<input type="text" class="xx-large" name="sender" maxlength="150">
	</p>
	
	<h2><?php echo L_dynsb_Subject;?></h2>
	<p>
  	<input type="text" class="xx-large" name="subject" maxlength="150">
	</p>

	<h2 onclick="javascript:expandTextarea('conttext')"><?php echo L_dynsb_Text;?></h2>
	<p>
  	<textarea name="conttext" id="conttext" cols="120" rows="10"></textarea>
	</p>
	<h2><?php echo L_dynsb_MarkKey;?></h2>
  	<p><select name="akt_key">
  		<?php
  			$SQL = "SELECT * FROM ".DBToken."marketingkey WHERE mkType in (1, 2)";
			$qry = @mysqli_query($link,$SQL);
			while($obj = @mysqli_fetch_object($qry)) { 
		?>
		<option value="<?php echo $obj->mkKey;?>"><?php echo $obj->mkDesc;?></option>		
		<?php } ?>
  	</select></p>
	<table> 
	<tr>
		<th>&nbsp;</th>
		<th><?php echo L_dynsb_CustomerNo;?></th>
		<th><?php echo L_dynsb_Firm;?></th>
		<th><?php echo L_dynsb_Name;?></th>
  		<th><?php echo L_dynsb_Email;?></th> 		
	</tr>

<?php
	//display found customer here
	foreach($cust_arr as $cust) {
		$SQL = "SELECT * FROM ".DBToken."customer WHERE cusIdNo = '".$cust."'";		
		$qry = @mysqli_query($link,$SQL);
		$obj = @mysqli_fetch_object($qry); 
?>	
	<tr>
		<td><input name="cust_checked[]" value="<?php echo $obj->cusIdNo;?>" type="checkbox" checked></td>
	    <td align="center" style="font-weight:bold;"><?php echo $obj->cusIdNo;?></td>
	    <td align="center" style="font-weight:bold;"><?php echo $obj->cusFirmname;?></td>
	    <td align="center" style="font-weight:bold;"><?php echo $obj->cusLastName;?>, <?php echo $obj->cusFirstName;?></td>
	    <td align="center" style="font-weight:bold;"><?php echo $obj->cusEMail;?></td>
	</tr>
<?php } ?>

</table>
</form>

<p>
<input type="button" class="button" value="<?php echo L_dynsb_Back;?>" onclick="javascript:self.location.href='mod.marketing.advmail.php'">
<input type="button" class="button large" value="<?php echo L_dynsb_SendAdvMail;?>" onclick="javascript:submitForm('sendform')">
</p>
<?php } ?>

</div>

<?php
require_once("../../include/page.footer.php");
?>
</body>
</html>

