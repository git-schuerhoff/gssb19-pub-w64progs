<?php
    
 // back_font.css
 if(file_exists($fontBackFile)){    
        if(isset($_POST['save']))
        {
            copy($fontBackFile, $fontBackFile1);
           $cssedit->setColors($fontBackFile, $_REQUEST['bcolors'], $_REQUEST['colors']);        
        }

    $bcolors = $cssedit->getBasisColors($fontBackFile);
  }  
   else {
     echo "File not exists - $fontBackFile";
     exit;
  }  
  
?>




    <fieldset style="width:500px;  margin: 0 0 20px 0; padding:5px 15px;" class="colors_box">
        <legend style=""> <?php echo L_dynsb_Colorschema?> </legend>
        <div style="margin:0 0 15px 0">
             <?php 
                foreach($bcolors as $key => $val){
		  echo '<div class="box"><label>'.L_dynsb_Color.($key+1).'</label><input type="text" maxlength="7" size="7" style="background-color:'.$val[1].';" id="colorpickerField1" name="colors[]" value="'.$val[1].'" />
                        <input type="hidden" value="'.$val[1].'" name="bcolors[]"></div>';
             } 
            ?>  
        </div>   
        <input type="submit" class="button" value="<?php echo L_dynsb_Save;?>" name="save" style="margin:0 0 0 350px;">
    </fieldset>    
    

