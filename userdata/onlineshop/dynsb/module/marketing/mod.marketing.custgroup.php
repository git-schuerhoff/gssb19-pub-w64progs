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
if(isset($_REQUEST['start'])) {
    $start = intval($_REQUEST['start']);
}
if(isset($_REQUEST['pk'])) {
    $cnewsIdNo = intval($_REQUEST['pk']);
}
if(isset($_REQUEST['act'])) {
    $act = trim($_REQUEST['act']);
}

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

// Definieren der zu lesenden Tabelle
$sourcetable= DBToken."custgroup";

if (isset($_REQUEST['set_cust_to_group']))
{
	//print_r($_REQUEST['cust_checked']);
	$cust_group = $_REQUEST['cust_group'];
	$query = "DELETE FROM ".DBToken."cust_to_group WHERE cgId = '".$cust_group."';";
	$result = mysqli_query($link,$query) or die("L�en fehlgeschlagen: " . mysqli_error($link));
	$cust_checked_arr = $_REQUEST['cust_checked'];
	if(!empty($cust_checked_arr) && is_array($cust_checked_arr)) {
		foreach($cust_checked_arr as $elem) {
			$query = "INSERT INTO ".DBToken."cust_to_group(cgId, custId) VALUES ('".$cust_group."', '".$elem."')"; 
			$result = mysqli_query($link,$query) or die("Einf�ehlgeschlagen: " . mysqli_error($link));
		}
	}
}

if (isset($_REQUEST['new_desc']))
{
	$query_new = "INSERT INTO $sourcetable (cgName) 
	VALUES ('".$_REQUEST['new_desc']."')";
	$result_new = mysqli_query($link,$query_new) or die("Einf�ehlgeschlagen: " . mysqli_error($link));
}

if (isset($_REQUEST['editId']))
{
	$query_edit = "UPDATE $sourcetable set cgName= '".$_REQUEST['edit_desc']."';";
	$result_edit = mysqli_query($link,$query_edit) or die("Einf�ehlgeschlagen: " . mysqli_error($link));
}

if(isset($_REQUEST['delete']))
{
  	$query = "DELETE FROM ".DBToken."cust_to_group WHERE cgId = '".$_REQUEST['delete']."';";
	$result = mysqli_query($link,$query) or die("L�en fehlgeschlagen: " . mysqli_error($link));
	$sql = "DELETE FROM ".$sourcetable." where cgId='".$_REQUEST['delete']."'";
  	$qry = @mysqli_query($link,$sql);
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
    </script>
</head>
<body>
<?php
require_once("../../include/page.header.php");
?>
<div id="PGavailabilitycategories">
<h1>&#187;&nbsp;<?php echo L_dynsb_Marketing;?>&nbsp;&#171;</h1>

<?php

if(isset($_REQUEST['show_new']))
{
?>

<form name="neuform" action="mod.marketing.custgroup.php" method="post">
<table>
<tr>
	<td valign="top">
		<input type="hidden" name="lang" value="<?php echo $lang;?>">
  	<h2>

    <h2><?php echo L_dynsb_CustGroupName;?></h2>
		<p>
		  <input type="text" maxlength="25" name="new_desc"></textarea>
		</p>

		<p>
		<input type="button" class="button" onclick="javascript:submitForm('neuform');" tabindex=29 name="btn_save" value="<?php echo L_dynsb_Save;?>">&nbsp;
      	<input type="button" class="button" onclick="javascript:self.location.href='mod.marketing.custgroup.php?lang=<?php echo $lang;?>';" tabindex=30 value="<?php echo L_dynsb_Cancel;?>">
  		</p>
  	</td>
</tr>
</table>
</form>

<?php
}
//kunden zuordnen
if(isset($_REQUEST['set_cust']))
{
	$SQL = "SELECT * FROM ".$sourcetable." where cgId = '".$_REQUEST['set_cust']."'";
    $qry_group = @mysqli_query($link,$SQL);
    $group = @mysqli_fetch_object($qry_group);
?>

<form name="setcustform" action="mod.marketing.custgroup.php" method="post">
<input type="hidden" name="lang" value="<?php echo $lang;?>">
<input type="hidden" name="cust_group" value="<?php echo $group->cgId;?>">
<input type=hidden"  name="set_cust_to_group">
<h2><?php echo $group->cgName;?></h2>
<p><img src="../../image/disable.gif" ><?php echo L_dynsb_SetCustToGroupHint;?></p>
<table style="width:600px;">
	<?php
		$SQL = "SELECT * FROM ".DBToken."customer";
    	$qry_cust = @mysqli_query($link,$SQL);
    	while($cust = @mysqli_fetch_object($qry_cust)) {
    		$SQL = "SELECT * FROM ".DBToken."cust_to_group WHERE custId = '".$cust->cusIdNo."'";
    		$qry_custgroup = @mysqli_query($link,$SQL);
    		$in_group = false;
    		$in_other_groups = false;
    		while($custgroup = @mysqli_fetch_object($qry_custgroup)) {
    			if($custgroup->cgId == $group->cgId){
					$in_group = true;
    			} else {
    				$in_other_groups = true;
    			}
    		}
    		
	?>
		<tr>
		<td width="5%">
			<input name="cust_checked[]" value="<?php echo $cust->cusIdNo;?>" type="checkbox" <?php if($in_group) echo "checked"?>>
		</td>	
		<td width="30%"><?php echo $cust->cusLastName;?></td>
		<td width="30%"><?php echo $cust->cusFirstName;?></td>
		<td width="30%"><?php echo $cust->cusEMail;?></td>
		<td width="5%"><?php if ($in_other_groups) { ?><img src="../../image/disable.gif" ><?php } ?></td>
		</tr>
	<?php } ?>
	<tr>
		<td colspan="3">
			<p>
			<input type="button" class="button" onclick="javascript:submitForm('setcustform');" tabindex=29 name="btn_save" value="<?php echo L_dynsb_Save;?>">&nbsp;
	      	<input type="button" class="button" onclick="javascript:self.location.href='mod.marketing.custgroup.php?lang=<?php echo $lang;?>';" tabindex=30 value="<?php echo L_dynsb_Cancel;?>">
	  		</p>
	  	</td>
	</tr>
</table>
</form>

<?php
}

//EDIT
if (isset($_REQUEST['show_edit']))
{
  $SQL = "SELECT * FROM ".$sourcetable." where cgId = '".$_REQUEST['show_edit']."'";
    $qry = @mysqli_query($link,$SQL);
    $obj = @mysqli_fetch_object($qry);
?>

 <form name="editform" action="mod.marketing.custgroup.php" method="post">
  <table>
  <tr>
    <td valign="top">
		<input type="hidden" name="lang" value="<?echo $lang;?>">
		<p><input type="hidden" name="editId" value='<?php echo $obj->cgId;?>'>
	    </p>
		
	    

	    <h2><?php echo L_dynsb_CustGroupName;?></h2>
			<p>
			  <input type="text" maxlength="25" name="edit_desc" value="<?php echo $obj->cgName;?>"></input>
			</p>


		<p>
		<input type="button" class="button" onclick="javascript:submitForm('editform');" tabindex=29 name="btn_save" value="<?php echo L_dynsb_Save;?>">&nbsp;
	     <input type="button" class="button" onclick="javascript:self.location.href='mod.marketing.custgroup.php?lang=<?php echo $lang;?>';" tabindex=30 name="btn_save" value="<?php echo L_dynsb_Cancel;?>">
    	</p>
    </td>
  </tr>
 </table>
</form>
<?php
  }
?>

<table>
<tr>
	<th>&nbsp;</th>
  <th><?php echo L_dynsb_CustGroupName;?></th>
</tr>
<?php
$SQL = "SELECT * FROM $sourcetable";
$qry = @mysqli_query($link,$SQL);
while($obj = @mysqli_fetch_object($qry))
{
  ?>
<tr>
	<td align="center">
	 <a href="javascript:location.href='mod.marketing.custgroup.php?show_edit=<?php echo $obj->cgId;?>&lang=<?php echo $lang;?>'" name="btn_next" value="<?php echo L_dynsb_Edit;?>">
	    <img src="../../image/edit.gif" alt="<?php echo L_dynsb_EditData;?>">
	 </a>
	 <a href="javascript: if (confirm('<?php echo L_dynsb_QuestionDeleteEntry;?> ')){location.href='mod.marketing.custgroup.php?delete=<?php echo $obj->cgId;?>&lang=<?php echo $lang;?>'}" value="<?php echo L_dynsb_Delete;?>">
		 <img src="../../image/del.gif" alt="<?php echo L_dynsb_DeleteData;?>">
	 </a>
	 <a href="javascript:location.href='mod.marketing.custgroup.php?set_cust=<?php echo $obj->cgId;?>&lang=<?php echo $lang;?>'" name="btn_next" value="<?php echo L_dynsb_Edit;?>">
	    <img src="../../image/view.gif" alt="<?php echo L_dynsb_SetCustToGroup;?>">
	 </a>
	</td>
  <td style="font-weight: bold; " align="center"><?php echo $obj->cgName;?></td>
</tr>
<?php
 }
?>
</table>


<div class="footer">
	<input type="button" class="button" onclick="javascript:location.href='mod.marketing.custgroup.php?show_new=1&lang=<?php echo $lang;?>'" value="<?php echo L_dynsb_New;?>">
</div>

<?php
####### L�best㳩gung #######
if ($result_delete)
{
?>
	<script language="JavaScript" type="text/javascript">
		alert("<?php echo L_dynsb_GroupDeleteSuccessful;?>");
	</script>
<?php
}// Ende if


###### Editierbest㳩gung ######
if ($result_edit)
{
?>
	<script language="JavaScript" type="text/javascript">
		alert("<?php echo L_dynsb_GroupEditSuccessful;?>");
	</script>
<?php
}// Ende if


###### Einf�t㳩gung ######
if ($result_new)
{
?>
	<script language="JavaScript" type="text/javascript">
		alert("<?php echo L_dynsb_GroupAddSuccessful;?>");
	</script>
<?php
}// Ende if
?>
</div>

<?php
require_once("../../include/page.footer.php");
?>
</body>
</html>

