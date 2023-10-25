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
$sourcetable= DBToken."availability";

if (isset($_REQUEST['new_col']))
{
	$query_new = "INSERT INTO $sourcetable (avaColor,avaDescription, avaMinQty, avaMaxQty, avaPos) VALUES ('".$_REQUEST['new_col']."', '".$_REQUEST['new_desc']."', '".$_REQUEST['edit_fromqty']."', '".$_REQUEST['edit_toqty']."', '".$_REQUEST['avaPos']."')";
	$result_new = mysqli_query($link,$query_new) or die("Einf�ehlgeschlagen: " . mysqli_error($link));
}

if (isset($_REQUEST['edit_col']))
{
	$query_edit = "UPDATE $sourcetable set avaColor= '".$_REQUEST['edit_col']."', avaDescription='".$_REQUEST['edit_desc']."', avaMinQty='".$_REQUEST['edit_fromqty']."', avaMaxQty='".$_REQUEST['edit_toqty']."', avaPos='".$_REQUEST['avaPos']."' where avaId='".$_REQUEST['editId']."';";
	$result_edit = mysqli_query($link,$query_edit) or die("Einf�ehlgeschlagen: " . mysqli_error($link));
}

if(isset($_REQUEST['delete']))
{
  $sql = "DELETE FROM ".$sourcetable." where avaId='".$_REQUEST['delete']."'";
  $qry = @mysqli_query($link,$sql);
}

if(isset($_REQUEST['set_ampel']))
{
  $tmp = "0"; 
  if (trim($_REQUEST['set_ampel'])==0)
  {
    $tmp = "1";
  }
  setentity(DBToken."settings","useAmpel", $tmp,"setIdNo=1");
}
$useAmpel = getentity(DBToken."settings","useAmpel","setIdNo=1");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_Status;?></title>
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
    function setColor(color)
    {
      if(document.getElementById('new_col'))
      {
        document.getElementById('new_col').value = color;
      }
      if(document.getElementById('col_preview'))
      {
        document.getElementById('col_preview').style.backgroundColor = color;
      }
      if(document.getElementById('edit_col'))
      {
        document.getElementById('edit_col').value = color;
      }
      if(document.getElementById('edit_col_preview'))
      {
        document.getElementById('edit_col_preview').style.backgroundColor = color;
      }
    }
	function CheckMinMax()
	{
		var from = parseInt(document.getElementById('edit_fromqty').value);
		var to = parseInt(document.getElementById('edit_toqty').value);
		if(from > to)
		{
			alert("<?php echo L_dynsb_FromToWarning_Part1?>"+" "+from+" <?php echo L_dynsb_FromToWarning_Part2?> "+to);
		}
	}
    </script>
</head>
<body>
<?php
require_once("../../include/page.header.php");
?>
<div id="PGavailabilitycategories">
<h1>&#187;&nbsp;<?php echo L_dynsb_Status;?>&nbsp;&#171;</h1>

<?php
//NEW
if(isset($_REQUEST['show_new']))
{
?>

<form name="neuform" action="mod.availability.categories.php" method="post">
<table>
<tr>
	<td valign="top">
		<input type="hidden" name="lang" value="<?php echo $lang;?>">
  	<h2><?php echo L_dynsb_Color;?></h2>
    <p>
    	<input type="hidden" name="editId" value='<?php echo $obj->avaId;?>'>
 			<input type="text" id="col_preview" readonly>
 			<input type="text" id="new_col" name="new_col"  maxlength="7" value="">
    </p>
	<h2><?php echo L_dynsb_avaPos;?></h2>
	<p>
		<select name="avaPos">
		<option value="13" selected><?php echo L_dynsb_avaPosTop?></option>
		<option value="34"><?php echo L_dynsb_avaPosMiddle;?></option>
		<option value="54"><?php echo L_dynsb_avaPosBottom;?></option>
		</select>
	</p>
    <h2><?php echo L_dynsb_Quantity;?></h2>
      <p>
    	<?php echo L_dynsb_From;?>
      <input type="text" id="edit_fromqty" value='<?echo $obj->avaMinQty;?>' name="edit_fromqty" size="3" onchange="CheckMinMax()">
      <?php echo L_dynsb_To?>
      <input type="text" id="edit_toqty" value='<?echo $obj->avaMaxQty;?>' name="edit_toqty" size="3" onchange="CheckMinMax()">
    </p>

    <h2><?php echo L_dynsb_Description2;?></h2>
		<p>
		  <textarea name="new_desc" rows="5" cols="40"></textarea>
		</p>

		<p>
			<input type="button" class="button" onclick="javascript:submitForm('neuform');" tabindex=29 name="btn_save" value="<?php echo L_dynsb_Save;?>">&nbsp;
      <input type="button" class="button" onclick="javascript:self.location.href='mod.availability.categories.php?lang=<?php echo $lang;?>';" tabindex=30 value="<?php echo L_dynsb_Cancel;?>">
  	</p>
  </td>
  <td valign="top">
 		<?php include("color.php"); ?>
  </td>
</table>
</form>

<?php
}

//EDIT
if (isset($_REQUEST['show_edit']))
{
  $SQL = "SELECT * FROM ".$sourcetable." where avaId = '".$_REQUEST['show_edit']."'";
    $qry = @mysqli_query($link,$SQL);
    $obj = @mysqli_fetch_object($qry);
?>

 <form name="editform" action="mod.availability.categories.php" method="post">
  <table>
  <tr>
    <td valign="top">
		<input type="hidden" name="lang" value="<?echo $lang;?>">
    	<h2><?php echo L_dynsb_Color;?></h2>
		<p><input type="hidden" name="editId" value='<?php echo $obj->avaId;?>'>
	 	<input type="text" id="edit_col_preview" 	name="col_preview" readonly>
	     <input type="text" id="edit_col" 					name="edit_col" maxlength="7" value="">
	    </p>
		
		<h2><?php echo L_dynsb_avaPos;?></h2>
		<p>
			<select name="avaPos">
			<option value="13" <?php if($obj->avaPos == 13) echo "selected" ?>><?php echo L_dynsb_avaPosTop?></option>
			<option value="34" <?php if($obj->avaPos == 34) echo "selected" ?>><?php echo L_dynsb_avaPosMiddle;?></option>
			<option value="54" <?php if($obj->avaPos == 54) echo "selected" ?>><?php echo L_dynsb_avaPosBottom;?></option>
			</select>
		</p>
	    
	    <h2><?php echo L_dynsb_Quantity;?></h2>
      <p>
      	<?php echo L_dynsb_From;?>
        <input type="text" id="edit_fromqty" value='<?echo $obj->avaMinQty;?>' name="edit_fromqty" size="3" onchange="CheckMinMax()">
        <?php echo L_dynsb_To?>
        <input type="text" id="edit_toqty" value='<?echo $obj->avaMaxQty;?>' name="edit_toqty" size="3" onchange="CheckMinMax()">
 
	    </p>

	    <h2><?php echo L_dynsb_Description2;?></h2>
			<p>
			  <textarea name="edit_desc" rows="5" cols="40"><?php echo $obj->avaDescription;?></textarea>
			</p>


			<p>
				<input type="button" class="button" onclick="javascript:submitForm('editform');" tabindex=29 name="btn_save" value="<?php echo L_dynsb_Save;?>">&nbsp;
	      <input type="button" class="button" onclick="javascript:self.location.href='mod.availability.categories.php?lang=<?php echo $lang;?>';" tabindex=30 name="btn_save" value="<?php echo L_dynsb_Cancel;?>">
    	</p>
    </td>
    <td valign="top">
   		<?php include("color.php"); ?>
    </td>
  </tr>
 </table>
</form>

<script language="JavaScript" type="text/javascript">
  document.getElementById('edit_col').value = '<?echo $obj->avaColor;?>';
  document.getElementById('edit_col_preview').style.backgroundColor = '<?echo $obj->avaColor;?>';
</script>
<?php
  }
?>

<table>
<tr>
	<th>&nbsp;</th>
  <th><?php echo L_dynsb_Color;?></th>
  <th><?php echo L_dynsb_Quantity;?></th>
  <th><?php echo L_dynsb_Description2;?></th>
</tr>
<?php
$SQL = "SELECT * FROM ".DBToken."availability";
$qry = @mysqli_query($link,$SQL);
while($obj = @mysqli_fetch_object($qry))
{
  ?>
<tr>
	<td align="center">
	 <a href="javascript:location.href='mod.availability.categories.php?show_edit=<?php echo $obj->avaId;?>&lang=<?php echo $lang;?>'" name="btn_next" value="<?php echo L_dynsb_Edit;?>">
	    <img src="../../image/edit.gif" alt="<?php echo L_dynsb_EditData;?>">
	 </a>

		<a href="javascript: if (confirm('<?php echo L_dynsb_QuestionDeleteEntry;?> ')){location.href='mod.availability.categories.php?delete=<?php echo $obj->avaId;?>&lang=<?php echo $lang;?>'}" value="<?php echo L_dynsb_Delete;?>">
		  <img src="../../image/del.gif" alt="<?php echo L_dynsb_DeleteData;?>">
		</a>
	</td>
  <td style="font-weight: bold; background-color: <?php echo $obj->avaColor;?>;" align="center"><?php echo $obj->avaColor;?></td>
  <td><?php echo L_dynsb_From;?> <?echo $obj->avaMinQty;?> <?php echo L_dynsb_To?> <?echo $obj->avaMaxQty;?></td>
  <td><?php echo $obj->avaDescription;?></td>
</tr>
<?php
 }
?>
</table>


<div class="footer">
	<input type="button" class="button" onclick="javascript:location.href='mod.availability.categories.php?show_new=1&lang=<?php echo $lang;?>'" value="<?php echo L_dynsb_New;?>">
	<input class="checkbox" onclick="javascript:location.href='mod.availability.categories.php?set_ampel=<?php echo $useAmpel;?>&lang=<?php echo $lang;?>'" name="useAmpel" type="checkbox" value="1"<?php if($useAmpel==1) echo " checked";?>>&nbsp;<?php echo L_dynsb_UseAmpelText;?>
</div>

<?php
####### L�best㳩gung #######
if ($result_delete)
{
?>
	<script language="JavaScript" type="text/javascript">
		alert("<?php echo L_dynsb_StatusDeleteSuccessful;?>");
	</script>
<?php
}// Ende if


###### Editierbest㳩gung ######
if ($result_edit)
{
?>
	<script language="JavaScript" type="text/javascript">
		alert("<?php echo L_dynsb_StatusEditSuccessful;?>");
	</script>
<?php
}// Ende if


###### Einf�t㳩gung ######
if ($result_new)
{
?>
	<script language="JavaScript" type="text/javascript">
		alert("<?php echo L_dynsb_StatusAddSuccessful;?>");
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

