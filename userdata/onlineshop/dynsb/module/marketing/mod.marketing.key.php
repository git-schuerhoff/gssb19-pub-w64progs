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
  or die("<br />aborted: can't connect to '$dbServer' <br />");
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
$sourcetable= DBToken."marketingkey";

if (isset($_REQUEST['new_key']))
{
	$query_new = "INSERT INTO $sourcetable (mkKey, mkDesc, mkType, canDelete) 
	VALUES ('".$_REQUEST['new_key']."', '".$_REQUEST['new_desc']."', '".$_REQUEST['new_type']."', 1)";
	$result_new = mysqli_query($link,$query_new) or die("Einfügen fehlgeschlagen: " . mysqli_error($link));
}

if (isset($_REQUEST['editId']))
{
	$query_edit = "UPDATE $sourcetable set mkKey= '".$_REQUEST['edit_key']."', mkDesc='".$_REQUEST['edit_desc']."', mkType='".$_REQUEST['edit_type']."' where mkKey='".$_REQUEST['editId']."';";
	$result_edit = mysqli_query($link,$query_edit) or die("Einfügen fehlgeschlagen: " . mysqli_error($link));
}

if(isset($_REQUEST['delete']))
{
  $sql = "DELETE FROM ".$sourcetable." where mkKey='".$_REQUEST['delete']."'";
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
//NEW
if(isset($_REQUEST['show_new']))
{
?>

<form name="neuform" action="mod.marketing.key.php" method="post">
<table>
<tr>
	<td valign="top">
		<input type="hidden" name="lang" value="<?php echo $lang;?>">
  	<h2><?php echo L_dynsb_MarkKey;?></h2>
    <p>
 		<input name="new_key" maxlength="10" value="">
    </p>
	<h2><?php echo L_dynsb_MarkType;?></h2>
	<p>
		<select name="new_type">
		<option value="1" selected><?php echo L_dynsb_MarkTypeContact;?></option>
		<option value="2"><?php echo L_dynsb_MarkTypeAdvertising;?></option>
		</select>
	</p>

    <h2><?php echo L_dynsb_MarkDesc;?></h2>
		<p>
		  <textarea name="new_desc" rows="5" cols="40"></textarea>
		</p>

		<p>
		<input type="button" class="button" onclick="javascript:submitForm('neuform');" tabindex=29 name="btn_save" value="<?php echo L_dynsb_Save;?>">&nbsp;
      	<input type="button" class="button" onclick="javascript:self.location.href='mod.marketing.key.php?lang=<?php echo $lang;?>';" tabindex=30 value="<?php echo L_dynsb_Cancel;?>">
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
  $SQL = "SELECT * FROM ".$sourcetable." where mkKey = '".$_REQUEST['show_edit']."'";
    $qry = @mysqli_query($link,$SQL);
    $obj = @mysqli_fetch_object($qry);
?>

 <form name="editform" action="mod.marketing.key.php" method="post">
  <table>
  <tr>
    <td valign="top">
		<input type="hidden" name="lang" value="<?echo $lang;?>">
    	<h2><?php echo L_dynsb_MarkKey;?></h2>
		<p><input type="hidden" name="editId" value='<?php echo $obj->mkKey;?>'>
	 	   <input name="edit_key" maxlength="10" value="<?php echo $obj->mkKey;?>">
	    </p>
		
		<h2><?php echo L_dynsb_MarkType;?></h2>
		<p>
			<select name="edit_type">
			<option value="1" <?php if($obj->mkType == 1) echo "selected" ?>><?php echo L_dynsb_MarkTypeContact;?></option>
			<option value="2" <?php if($obj->mkType == 2) echo "selected" ?>><?php echo L_dynsb_MarkTypeAdvertising;?></option>
			</select>
		</p>
	    

	    <h2><?php echo L_dynsb_MarkDesc;?></h2>
			<p>
			  <textarea name="edit_desc" rows="5" cols="40"><?php echo $obj->mkDesc;?></textarea>
			</p>


		<p>
		<input type="button" class="button" onclick="javascript:submitForm('editform');" tabindex=29 name="btn_save" value="<?php echo L_dynsb_Save;?>">&nbsp;
	     <input type="button" class="button" onclick="javascript:self.location.href='mod.marketing.key.php?lang=<?php echo $lang;?>';" tabindex=30 name="btn_save" value="<?php echo L_dynsb_Cancel;?>">
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
  <th><?php echo L_dynsb_MarkKey;?></th>
  <th><?php echo L_dynsb_MarkType;?></th>
  <th><?php echo L_dynsb_MarkDesc;?></th>
</tr>
<?php
$SQL = "SELECT * FROM $sourcetable";
$qry = @mysqli_query($link,$SQL);
while($obj = @mysqli_fetch_object($qry))
{
  ?>
<tr>
	<td align="center">
	 <?php if($obj->canDelete) { ?>
	 <a href="javascript:location.href='mod.marketing.key.php?show_edit=<?php echo $obj->mkKey;?>&lang=<?php echo $lang;?>'" name="btn_next" value="<?php echo L_dynsb_Edit;?>">
	    <img src="../../image/edit.gif" alt="<?php echo L_dynsb_EditData;?>">
	 </a>
	 <?php } ?>
		<?php if($obj->canDelete) { ?>
		<a href="javascript: if (confirm('<?php echo L_dynsb_QuestionDeleteEntry;?> ')){location.href='mod.marketing.key.php?delete=<?php echo $obj->mkKey;?>&lang=<?php echo $lang;?>'}" value="<?php echo L_dynsb_Delete;?>">
		  <img src="../../image/del.gif" alt="<?php echo L_dynsb_DeleteData;?>">
		</a>
		<?php } ?>
	</td>
  <td style="font-weight: bold; background-color: <?php echo $obj->mkKey;?>;" align="center"><?php echo $obj->mkKey;?></td>
  <td>
    <?php switch($obj->mkType) { 
  	case 1: echo L_dynsb_MarkTypeContact; break;
  	case 2: echo L_dynsb_MarkTypeAdvertising; break; 
  	case 3: echo L_dynsb_MarkTypeOrder; break; } ?>			
  </td>
  <td><?php echo $obj->mkDesc;?></td>
</tr>
<?php
 }
?>
</table>


<div class="footer">
	<input type="button" class="button" onclick="javascript:location.href='mod.marketing.key.php?show_new=1&lang=<?php echo $lang;?>'" value="<?php echo L_dynsb_New;?>">
</div>

<?php
####### L�best㳩gung #######
if ($result_delete)
{
?>
	<script language="JavaScript" type="text/javascript">
		alert("<?php echo L_dynsb_KeyDeleteSuccessful;?>");
	</script>
<?php
}// Ende if


###### Editierbest㳩gung ######
if ($result_edit)
{
?>
	<script language="JavaScript" type="text/javascript">
		alert("<?php echo L_dynsb_KeyEditSuccessful;?>");
	</script>
<?php
}// Ende if


###### Einf�t㳩gung ######
if ($result_new)
{
?>
	<script language="JavaScript" type="text/javascript">
		alert("<?php echo L_dynsb_KeyAddSuccessful;?>");
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

