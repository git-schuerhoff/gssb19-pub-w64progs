<?php

 class cssEdit
 {
   
  var $filePath;
  var $fileText;
   
   
   function getBasisColors($path)
   {
       $this->filePath = $path;
       $this->fileText = $this->fileRead();
       preg_match('/<COLORS>(.*)<\/COLORS>/isUe', $this->fileText, $result);
        $arr = explode(';',$result[1]);
        foreach($arr as $v) $arr1[] = explode(':', $v); 
        unset($arr1[count($arr1)-1]);       
        
       return $arr1; 
   }
   
   
   function setColors($path, $scolors, $rcolors)
   {
       $this->filePath = $path;
       $this->fileText = $this->fileRead();
       $this->fileText = str_replace($scolors, $rcolors, $this->fileText);   
       $this->fileWrite();   
   }
    
 
   function getElemenFontColor($path, $search_str)
   {
       $this->filePath = $path;
       $this->fileText = $this->fileRead();
       $element = $this->getCssElement($search_str); 
       if($element) {
           $pos = strpos($element[0], 'color');
           $last=0;
          if($pos)  for($i=$pos; $i<strlen($element[0]); $i++) 
            { 
              $last++;
              if($element[0][$i]==';')  break;
            } 
        preg_match('/color:(.*);/', $element[0], $result);
       }
        return trim($result[1]);
   }   
     

   function setElemenFontColor($path, $color, $search_str)
   {
       $this->filePath = $path;
       $this->fileText = $this->fileRead();
       $element = $this->getCssElement($search_str); 

       if($element[1]) {
          $pos = strpos($element[0], 'color');
          $last=0;
           if($pos) for($i=$pos; $i<strlen($element[0]); $i++) 
            { 
              $last++;
              if($element[0][$i]==';')  break;
            }   

      $tmp1 = substr($this->fileText, 0, $element[1]+$pos);
      $tmp2 = substr($this->fileText, $element[1]+$pos+$last, strlen($this->fileText));
      $this->fileText = $tmp1.'color: '.$color.';'.$tmp2;
      $this->fileWrite();
       }
   }
   
   
   function getElemenFontFamily($path, $search_str)
   {
       $this->filePath = $path;
       $this->fileText = $this->fileRead();
       $element = $this->getCssElement($search_str);  
       if($element) {
          $pos = strpos($element[0], 'font-family');
          $last=0;
          if($pos)  for($i=$pos; $i<strlen($element[0]); $i++) 
            { 
              $last++;
              if($element[0][$i]==';')  break;
            } 
        preg_match('/font-family:(.*);/', $element[0], $result);
       }
        return trim($result[1]);
   }   

   
   function setElemenFontFamily($path, $font_family, $search_str)
   {
       $this->filePath = $path;
       $this->fileText = $this->fileRead();
       $element = $this->getCssElement($search_str); 
       if($element) {
            $pos = strpos($element[0], 'font-family');
            $last=0;
           if($pos) for($i=$pos; $i<strlen($element[0]); $i++) 
            { 
              $last++;
              if($element[0][$i]==';')  break;
            }   

      $tmp1 = substr($this->fileText, 0, $element[1]+$pos);
      $tmp2 = substr($this->fileText, $element[1]+$pos+$last, strlen($this->fileText));
      $this->fileText = $tmp1.'font-family: '.str_replace('"', "'", $font_family).';'.$tmp2;
      $this->fileWrite();
       } 
   }
   
   
   function getCssElement($search_str)
   {
       $pos = strpos($this->fileText, $search_str);
        if($pos>0) {
            $last=0;
            for($i=$pos; $i<strlen($this->fileText); $i++) 
            { 
              $last++;
              if($this->fileText[$i]=='}')  break;
            }  
        }   
        return array(substr($this->fileText, $pos, $last), $pos);       
   }
   
   
    function setBackFile($path, $search, $replace)
    {
       $this->filePath = $path;
       $this->fileText = $this->fileRead();  
       $this->fileText = str_replace($search, $replace, $this->fileText);
       $this->fileWrite();        
    }
   
    
    function fileRead()
    {
      $handle = fopen($this->filePath, 'r');
      $text = fread($handle, filesize($this->filePath));
      fclose($handle);
      
      return $text;
     }
     
     
    function fileWrite()
    {
       if (is_writable($this->filePath)) 
       {
          $handle = fopen($this->filePath, 'w');
          $this->fileText = fwrite($handle, $this->fileText);
          fclose($handle);    
       } 
       else print "The file $this->filePath is not writable";
     }     
 
 
    function scandir($directory, $sorting_order=0) {
            if(!is_dir($directory)) {
                    return false; 
            }
            $files = array();
            $handle = opendir($directory);
            while (false !== ($filename = readdir($handle))) {
                    $files[] = $filename; 
            }
            closedir($handle);

            if($sorting_order == 1) {
                    rsort($files); 
            } else {
                    sort($files); 
            }
            return $files;
    } 
 }    
?>
