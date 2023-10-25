<?php
    
 // back_font.css
 if(file_exists($fontBackFile)){    
        if(isset($_POST['save']))
        {
            copy($fontBackFile, $fontBackFile1);
           $cssedit->setElemenFontColor($fontBackFile, $_REQUEST['bodyFontColor'], 'body');        
           $cssedit->setElemenFontColor($fontBackFile, $_REQUEST['h1FontColor'], '#rightcol h1');
           $cssedit->setElemenFontColor($fontBackFile, $_REQUEST['h1FontColor'], '#leftcol h1');
           $cssedit->setElemenFontColor($fontBackFile, $_REQUEST['aFontColor'], 'a, a:link,');        
           $cssedit->setElemenFontColor($fontBackFile, $_REQUEST['ahFontColor'], 'a:hover'); 
           $cssedit->setElemenFontColor($fontBackFile, $_REQUEST['atopFontColor'], '#topmenu a,');
           $cssedit->setElemenFontColor($fontBackFile, $_REQUEST['atophFontColor'], '#topmenu a:hover');
           $cssedit->setElemenFontColor($fontBackFile, $_REQUEST['atophFontColor'], '#topmenu .active');
           $cssedit->setElemenFontFamily($fontBackFile, $_REQUEST['FontFamily'], 'body');
           $cssedit->setElemenFontColor($fontBackFile, $_REQUEST['itemFontColor'], '.lastviewed_box a, .item_liste .icols a,  .item_liste .icols_main a');
        }
    $bodyFontColor = $cssedit->getElemenFontColor($fontBackFile, 'body');
    $aFontColor = $cssedit->getElemenFontColor($fontBackFile, 'a, a:link,');
    $ahFontColor = $cssedit->getElemenFontColor($fontBackFile, 'a:hover');
    $h1FontColor = $cssedit->getElemenFontColor($fontBackFile, '#leftcol h1');    
    $atopFontColor = $cssedit->getElemenFontColor($fontBackFile, '#topmenu a,');
    $atophFontColor = $cssedit->getElemenFontColor($fontBackFile, '#topmenu a:hover');
    $FontFamily = $cssedit->getElemenFontFamily($fontBackFile, 'body');
    $itemFontColor = $cssedit->getElemenFontColor($fontBackFile, '.lastviewed_box a, .item_liste .icols a,  .item_liste .icols_main a');    
  }  
   else {
     echo "File not exists - $fontBackFile";
     exit;
  }  
  
?>




    <fieldset style="width:800px;  margin: 0 0 20px 0; padding:5px;" class="fonts_box">
        <legend style=""> <?php echo L_dynsb_FontColors;?> </legend>
        <table>
           <tr valign="top"> 
               <td width="48%">
             <?php  
                  echo '<ul><li><label>Navi top: normal, visited </label><input type="text" maxlength="7" size="7" style="background-color:'.$atopFontColor.';" id="colorpickerField2" name="atopFontColor" value="'.$atopFontColor.'" />
                                     <div><label>:hover, activ </label><input type="text" maxlength="7" size="7" style="background-color:'.$atophFontColor.';" id="colorpickerField3" name="atophFontColor" value="'.$atophFontColor.'" /></div></li>';
                  echo '<li><label>Navi left: a, a:link</label><input type="text" maxlength="7" size="7" style="background-color:'.$aFontColor.';" id="colorpickerField1" name="aFontColor" value="'.$aFontColor.'" />
                       <label>a:hover, a:visited</label><input type="text" maxlength="7" size="7" style="background-color:'.$ahFontColor.';" id="colorpickerField1" name="ahFontColor" value="'.$ahFontColor.'" /></li>';
                if($itemFontColor) echo '<li><label>Items: a, a:link, a:visited</label><input type="text" maxlength="7" size="7" style="background-color:'.$itemFontColor.';" id="colorpickerField1" name="itemFontColor" value="'.$itemFontColor.'" /></li>';
                  echo '</ul>';
            ?>  
               </td>
               <td width="4%"></td>
               <td width="48%">
              <?php  
		  echo '<ul><li><label>Body, td, textarea</label><input type="text" maxlength="7" size="7" style="background-color:'.$bodyFontColor.';" id="colorpickerField1" name="bodyFontColor" value="'.$bodyFontColor.'" /></li>';
                   echo '<li><label>h1</label><input type="text" maxlength="7" size="7" style="background-color:'.$h1FontColor.';" id="colorpickerField2" name="h1FontColor" value="'.$h1FontColor.'" /></li>';
                   echo '<li><label>Font family</label><input type="text" size="50px"  name="FontFamily" value="'.$FontFamily.'" /></li></ul>';
            ?>                    
               </td>
            </tr> 
        </table>    
        <input type="submit" class="button" value="<?php echo L_dynsb_Save;?>" name="save" style="margin:0 0 0 350px;">
    </fieldset>    
    

