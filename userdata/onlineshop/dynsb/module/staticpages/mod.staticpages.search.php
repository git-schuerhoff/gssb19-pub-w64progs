<?php
/******************************************************************************/
/* File: staticpages.search.php                                                  */
/******************************************************************************/

//require("../include/login.check.inc.php");
require("../../include/login.check.inc.php");
require_once("../../include/functions.inc.php");
require("../../../conf/db.const.inc.php");
require("mod.staticpages.setup.php");

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

if (!isset($_SESSION['SESS_userIdNo']) || strlen(trim($_SESSION['SESS_userIdNo'])) == 0)
{ die ("<br />error: missing session parameter!<br />"); }
else
{ $SESS_userIdNo = $_SESSION['SESS_userIdNo']; }

if (!isset($_SESSION['SESS_userLogin']) || strlen(trim($_SESSION['SESS_userLogin'])) == 0)
{ die ("<br />error: missing session parameter!<br />"); }
else
{ $SESS_userId = $_SESSION['SESS_userId']; }

if (!isset($_SESSION['SESS_languageIdNo']) || strlen(trim($_SESSION['SESS_languageIdNo'])) == 0)
{ die ("<br />error: missing session parameter!<br />"); }
else
{ $SESS_languageIdNo = $_SESSION['SESS_languageIdNo']; }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title><?php echo L_dynsb_staticpages;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="../../css/link.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../css/calendar.css" title="dynsb">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2009 GS Software Solutions AG">
    <script type="text/javascript" src="../../js/gslib.php?lang=<?php echo $SESS_languageIdNo;?>"></script>
    <script language="JavaScript" type="text/javascript">
    function MM_reloadPage(init)  //reloads the window if Nav4 resized
    {
      if (init==true) with (navigator)
      {
        if ((appName=="Netscape")&&(parseInt(appVersion)==4))
        {
          document.MM_pgW=innerWidth;
          document.MM_pgH=innerHeight;
          onresize=MM_reloadPage;
        }
      }
      else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH)
      {
        location.reload();
      }
    }
    //--------------------------------------------------------------------------
    MM_reloadPage(true);
    //--------------------------------------------------------------------------
    function navigation(val)
    {
      document.frmstaticpages.start.value = val;
      document.frmstaticpages.submit();
    }
    //--------------------------------------------------------------------------
    function preReset()
    {
      document.frmstaticpages.start.value = 0;
      resetSearch('frmstaticpages', 's_', true);
    }
    //--------------------------------------------------------------------------
    function startDelete(frm, val)
    {
      document.forms[frm].start.value = val;
      document.forms[frm].del_stat.value = "1";
      deleteIfAnyIsSelected(frm);
    }
    //--------------------------------------------------------------------------
    function deleteIfAnyIsSelected(frm)
    {
      var sFormName = frm;
      if(isDataSelected(sFormName)==true)
      {
        var bCheck = confirm("<?php echo L_dynsb_ReallyDelete;?>");
        if(bCheck==true) document.forms[sFormName].submit();
      }
      else
      {
        alert("<?php echo L_dynsb_NoDataSelectedDelete;?>");
      }
    }
    //--------------------------------------------------------------------------
    function singleDelete(frm, val, pk)
    {
      for(var x = 0; x < document.forms[frm].elements.length; x++)
      {
        var y = document.forms[frm].elements[x];
        if(y.type == 'checkbox' && y.name != 'alldata')
        {
          if(document.forms[frm].elements[x].value == pk)
          {
            document.forms[frm].elements[x].checked = true;
          }
        }
      }
      document.forms[frm].start.value = val;
      document.forms[frm].del_stat.value = "1";
      var bCheck = confirm("<?php echo L_dynsb_SureWantDelete;?>");
      if(bCheck==true)
      {
        document.forms[frm].submit();
      }
      else
      {
        for(var x = 0; x < document.forms[frm].elements.length; x++)
        {
          var y = document.forms[frm].elements[x];
          if(y.type == 'checkbox' && y.name != 'alldata')
          {
            if(document.forms[frm].elements[x].value == pk)
            {
              document.forms[frm].elements[x].checked = false;
            }
          }
        }
        checkAllData(frm);
      }
    }
    
    
    function GetFileName(pfad)
    {
      if ((pfad.indexOf('\\')!=-1))
      {
        var items = pfad.split('\\');
      }
      else
      {
         var items = pfad.split('/');
      }
      filename=items[items.length-1];
      //ext=filename.split('.');
      //filename=filename.replace('.'+ext[ext.length-1],'');
      return filename;
    }
    
    function statpgs_BuildUrl(frmstatpgs, input_uploadURL, input_linkURL, input_title, input_save, input_filesourcepath)
    { 
      if(document.forms[frmstatpgs].elements[input_uploadURL].value!='')
      { 
        var uploadURL=GetFileName( document.forms[frmstatpgs].elements[input_uploadURL].value );
      }
      else
      {
        var uploadURL=GetFileName( document.forms[frmstatpgs].elements[input_filesourcepath].value );
      }
    
      title=document.forms[frmstatpgs].elements[input_title].value;      
      
      link="<a href='<?php echo $uploadDIR_ahref?>"+uploadURL+"' target='_blank'>"+title+"</a>";
      document.forms[frmstatpgs].elements[input_linkURL].value = link;      
            
      document.forms[frmstatpgs].elements[input_filesourcepath].value = uploadURL;
      document.forms[frmstatpgs].elements[input_save].disabled = false;      
    }
    </script>
</head>
<?php  
  if(!@ini_get('file_uploads'))
    die(L_dynsb_sp_error_IniForbidsFileupload);  
?>
<body>
  <?php
  require_once("../../include/page.header.php");
  ?>
  <div id="PGstaticpages">
    <h1>&#187;&nbsp;<?php echo L_dynsb_staticpages;?>&nbsp;&#171;</h1>
 
<?php
      if(isset($_GET['msg']) && $_GET['msg']!="") {
        echo "<p class=\"notice\">".$_GET['msg']."</p>";
      }  
?>
    </p>
    
    <?php echo L_dynsb_statpgsUsingInfotext;?> 
    <br /><?php echo L_dynsb_statpgsmaxfilesize;?>   
    
    <div>
      <table>
        <tr> 
          <td colspan="4">
            <?php
              $sql="SELECT staticpagesgroupboxlabel FROM ".DBToken."settings WHERE setIdNo = 1"; 
              $qry = @mysqli_query($link,$sql); $row = @mysqli_fetch_row( $qry );
              $staticpagesgroupboxlabel = stripslashes( $row[0] );
            ?>
          
            <form name="frmstaticpagesGrouplabel" action="mod.staticpages.save.php" method="post">
                <?php echo L_dynsb_sp_admin_staticpagesgroupboxlabel;?>  
                <input type="text" name="grouplabeltext" value="<?php echo $staticpagesgroupboxlabel;?>">
                <input type="submit" name="grouplabel_submit" value="<?php echo L_dynsb_Save;?>">
            </form>
          </td>
        </tr>
        
        <tr>
          <th width="5%">&nbsp;</th>
          <th width="30%"><?php echo L_dynsb_staticpages_navtitel;?></th>
          <th width="30%"><?php echo L_dynsb_staticpages_navurl;?> / <?php echo L_dynsb_staticpages_destfile;?></th>
 
        </tr>        
        <?php
          for($i=0; $i<$maxLinksCount; $i++)
          {
            $qrySQL = "SELECT * FROM ".DBToken."staticpages WHERE staticpagesIdNo = $i                    
                        ORDER BY staticpagesIdNo ASC";
            $qry = @mysqli_query($link,$qrySQL);            
            $statpgsDBdata = mysqli_fetch_array($qry, MYSQLI_ASSOC);
            
            $title = stripslashes( $statpgsDBdata['menuentryTitle'] );
            $url = stripslashes( $statpgsDBdata['menuentryURL'] );                        
            $id = $statpgsDBdata['staticpagesIdNo'];
            $fsp = stripslashes( $statpgsDBdata['filesourcepath'] );
                        
            ?> 
            <form name="frmstaticpages_<?php echo $i?>" 
                action="mod.staticpages.save.php" 
                method="post" 
                enctype="multipart/form-data">
                
                <input type="hidden" name="lang" value="<?php echo $lang; ?>">
                <input type="hidden" name="maxLinksCount" value="<?php echo $maxLinksCount;?>">                
                <input type="hidden" name="staticpagesIdNo" value="<?php echo $i?>">
                <input type="hidden" name="statpgs_do" value="updStatpgsData">  
                <input type="hidden" name="filesourcepath_<?php echo $i?>" value="<?php echo $fsp?>">             
                <tr>
                  <td><img src="../../image/del.gif" 
                           alt="<?php echo L_dynsb_DeleteData;?>" 
                           onclick="javscript:frmstaticpages_<?php echo $i?>.statpgsTitle_<?php echo $i?>.value='';
                                               frmstaticpages_<?php echo $i?>.statpgsUrl_<?php echo $i?>.value='';
                                               frmstaticpages_<?php echo $i?>.statpgsUpload_<?php echo $i?>.value=''; 
                                               frmstaticpages_<?php echo $i?>.statpgsSubmit_<?php echo $i?>.disabled = false;">
                  </td>
                  <td><input type="text" 
                             name="statpgsTitle_<?php echo $i?>" 
                             size="40" 
                             onchange="statpgs_BuildUrl('frmstaticpages_<?php echo $i?>', 'statpgsUpload_<?php echo $i?>','statpgsUrl_<?php echo $i?>','statpgsTitle_<?php echo $i?>', 'statpgsSubmit_<?php echo $i?>', 'filesourcepath_<?php echo $i?>')"
                             value="<?php echo $title;?>">
                  </td>
                  <td><input type="text" 
                             name="statpgsUrl_<?php echo $i?>" 
                             size="90"
                             value="<?php echo $url;?>"
                             onchange="frmstaticpages_<?php echo $i?>.statpgsSubmit_<?php echo $i?>.disabled=false;">
                  </td>
                 </tr> 
                  
                 <tr> 
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td><input type="file" 
                             size="45"
                             name="statpgsUpload_<?php echo $i?>" 
                             onchange="statpgs_BuildUrl('frmstaticpages_<?php echo $i?>', 'statpgsUpload_<?php echo $i?>','statpgsUrl_<?php echo $i?>','statpgsTitle_<?php echo $i?>', 'statpgsSubmit_<?php echo $i?>', 'filesourcepath_<?php echo $i?>')">
                  
                    <input disabled type="button" name="statpgsSubmit_<?php echo $i?>" value="<?php echo L_dynsb_Save;?>" onclick="javascript:frmstaticpages_<?php echo $i?>.submit();">
                    <br /><br />&nbsp;
                  </td>                  
                </tr>
            </form>
         <?}?>          
     </table>
    </div>           

</div>
<?php
require_once("../../include/page.footer.php");
?>
</body>
</html>
