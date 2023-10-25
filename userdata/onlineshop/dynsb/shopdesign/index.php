<?php
require("../include/login.check.inc.php");
require_once("class/class.cssedit.php");
$rootPath = '../../';

//***************** Sprachdatei ************************************************/
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


         switch($_REQUEST['site']){
             case 'shopcolors': $inc = 'inc/inc.'.$_REQUEST['site'].'.php';                 
                                $title =  L_dynsb_Colorschema;   
                break;
             case 'fontcolors': $inc = 'inc/inc.'.$_REQUEST['site'].'.php';                 
                                $title =  L_dynsb_FontColors;         
                break;
             case 'carts': $inc = 'inc/inc.'.$_REQUEST['site'].'.php';                 
                                $title =  L_dynsb_Carts;                                   
         }  
$shopdesignPath = 'dynsb/shopdesign/';
$fontFile = $rootPath.'font.css';
$fontBackFile = $rootPath.'back_font.css';
$fontBackFile1 = $rootPath.'back_font1.css';
$mainFile = $rootPath.'main.php';
$mainBackFile = $rootPath.'back_main.php';

  $cssedit = new cssEdit();

if(isset($_REQUEST['neu'])){
   if(file_exists($fontBackFile)) @unlink($fontBackFile);
   if(file_exists($mainBackFile)) @unlink($mainBackFile);
}  
  
if(!file_exists($fontBackFile))
{    
         @copy($rootPath.'cart.png', 'tmp/cart.png');
         @copy($fontFile, $fontBackFile);
           $search = array('cart.png');
           $replace = array($shopdesignPath.'tmp/cart.png');       
         $cssedit->setBackFile($fontBackFile, $search, $replace);
}

if(isset($_REQUEST['replace']))
{
    if(file_exists($fontBackFile)) @copy($fontBackFile, $fontFile); 
    if(file_exists('tmp/cart.png')) @copy('tmp/cart.png', $rootPath.'cart.png');
       $search = array($shopdesignPath.'tmp/cart.png');       
       $replace = array('cart.png');       
     $cssedit->setBackFile($fontFile, $search, $replace);    
}


if(isset($_REQUEST['undo']))
{
    if(file_exists($fontBackFile1)) {
        @copy($fontBackFile1, $fontBackFile);
        @unlink($fontBackFile1);
    }  
}

  
 if(!file_exists($mainBackFile)){
  if(@copy($mainFile, $mainBackFile)){  
       $search = array('font.css', 'INDEX,FOLLOW,ALL', 'content="cache"');
       $replace = array('back_font.css', 'NOINDEX,NOFOLLOW', 'content="no_cache"');      
      $cssedit->setBackFile($mainBackFile, $search, $replace);
  }  
   else {
     echo "File not exists - $mainBackFile";
     exit;
  }      
 }

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
 <meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1" >
  <meta content="de" http-equiv="Language">
  <meta name="author" content="GS Software Solutions GmbH">
  <link rel="stylesheet" type="text/css" href="../css/link.css">
  <link rel="stylesheet" media="screen" type="text/css" href="../css/colorpicker.css" />
 <script type="text/javascript" src="../js/jquery.min.js"></script>
 <script type="text/javascript" src="../js/colorpicker.js"></script>
</head>

 <body>
    <div class="siteheader"><?php echo L_dynsb_Shopdesign.' &nbsp;-&#187; '.$title;?></div>        
     <div style="padding:0 0 15px 15px; border-bottom:15px #006fb4 solid;">   

    <form action="" method="post" enctype="multipart/form-data">
        
        <table width="300" style="background:#ffebb0; border: 1px #c5c5c5 solid; width:400px;">
            <tr>
                <td></td>
                <td><input type="submit" class="button" value="<?php echo L_dynsb_New?>" name="neu"></td>
                <td><input type="submit" class="button" value="<?php echo L_dynsb_Undo?>" name="undo" id="undo"></td> 
                <td></td>               
                <td><input type="submit" class="button" value="<?php echo L_dynsb_Replace;?>" name="replace"></td> 
            </tr>
        </table>
    
             <?php include $inc ?>
            <div style="margin:0 0 5px 0;">
                <a href="<?php echo $mainBackFile;?>?site=<?php echo rand();?>" target="_blank" style="font-size:14px; margin-left:180px;"><?php echo L_dynsb_Preview.' - '.L_dynsb_Copy;?></a>
                <a href="<?php echo $mainFile;?>?site=<?php echo rand();?>" target="_blank" style="font-size:14px; margin-left:200px;"><?php echo L_dynsb_Preview.' - '.L_dynsb_Source;?></a>
            </div>
        <div style="width:900px; overflow: auto">
            <iframe src="<?php echo $mainBackFile;?>?site=<?php echo rand();?>" style="margin:0 10px 0 0; border:4px #c5c5c5 solid" width="400" height="500"></iframe>
            <iframe src="<?php echo $mainFile;?>?site=<?php echo rand();?>" style="border:4px #c5c5c5 solid" width="400" height="500"></iframe>
        </div>
        
    </form>
             
 </div>            
             
<script language="JavaScript" type="text/javascript">

       var undo = '<?php if(!file_exists($fontBackFile1)) echo 1; ?>';
	      if(undo) $('#undo')
		          .attr('disabled','disabled')
				  .css('border','1px #c5c5c5 solid')
				  .css('color','#c5c5c5');
		  
        $('#colorpickerField1, #colorpickerField2, #colorpickerField3').ColorPicker({
            onSubmit: function(hsb, hex, rgb, el) {
                    $(el).val('#'+hex);
                    $(el).css('background-color','#'+hex);
                    $(el).ColorPickerHide();
            },
            onBeforeShow: function () {
                    $(this).ColorPickerSetColor(this.value);
            }
    })
    .bind('keyup', function(){
            $(this).ColorPickerSetColor(this.value);
    });
</script>             
   
    </body>
</html>

