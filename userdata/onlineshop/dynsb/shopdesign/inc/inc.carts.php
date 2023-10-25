<?php
    $imgPath = 'images/carts/';
    
 // back_font.css
 if(file_exists('tmp/cart.png')){    
        if(isset($_POST['save']) || isset($_POST['bildupload']))
        {
          @copy($_REQUEST['img_name'], 'tmp/cart.png'); 
          echo $_FILES['upimage']['tmp_name'];
          preg_match('/.png/', $_FILES['upl_image']['name'], $r1);
          if($r1[0]=='.png') move_uploaded_file($_FILES['upl_image']['tmp_name'], $imgPath.$_FILES['upl_image']['name']);
        }

    $images =  $cssedit->scandir($imgPath, 0);
  }  
   else {
     echo "File not exists - $fontBackFile";
     exit;
  }  
  
  
 if(isset($_REQUEST['img'])) {
     unlink($imgPath.basename($_REQUEST['img']));
 }
?>


    <fieldset style="width:810px;  margin: 0 0 20px 0; padding:5px;" class="colors_box">
        <legend style=""> <?php echo L_dynsb_Carts;?> </legend>
        <table width="100%">
            <tr>
              <td width="540">  
                <div style="margin:0 0 15px 0; overflow:auto; height:80px;">
                     <?php 
                        foreach($images as $key => $val){
                             $f = basename($imgPath.$val).'<br />';
                              preg_match('/.png/', $f, $r);
                        if(is_file($imgPath.$val) && $r[0] =='.png') 
                         {
                            echo '<img src="'.$imgPath.$val.'" id="'.$key.'" class="img_box '; 
                              if($_REQUEST['img_name']==$imgPath.$val) echo ' r'; 
                            echo '">';       
                         }   
                     } 
                     echo '<input type="hidden" id="img_name" name="img_name" value="">';
                    ?> 
                </div> 
                  <input type="button" class="button" id="deleteimg" value="<?php echo L_dynsb_DeleteImage;?>">
                   <input type="submit" class="button" value="<?php echo L_dynsb_Save;?>" name="save" style="margin:0 0 0 250px;">                  
                      </td>
                      <td width="220px">
                <div style="display:block; width:250px; border:1px #c5c5c5 solid; padding:5px; min-height: 100px;">
                    <p><?php echo L_dynsb_FormatImage;?>: *.png</p>
                   <input type="file" name="upl_image" size="25" style="width: 100px;">
				   <div>
                   <input type="submit" value="<?php echo L_dynsb_UploadImage;?>" name="bildupload" class="button" style="margin:10px">
				   </div>
                </div>
              </td>
        </table>
    </fieldset>    
    
    
<script language="JavaScript" type="text/javascript">
    $('.img_box').click(function(){
        $('.img_box').removeClass('r');
        $(this).addClass('r');
        $('#img_name').val($(this).attr('src'));
    });
    
    $('#deleteimg').click(function(){
        if($('.r').attr('src')!=undefined && confirm('<?php echo L_dynsb_DeleteImage;?>?')) document.location.href = 'index.php?lang=<?php echo $lang;?>&site=carts&img='+$('.r').attr('src');
    });
</script>    
