<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$slideshow = '';
if($this->get_setting('cbSlideShow_Checked') == 'True')
{
	if(is_dir('./images/slideshow/'))// Bei Basic und Standard gibt es kein 'slideshow'-Verzeichnis
	{
		$aImages = scandir('./images/slideshow/');
		if(count($aImages) > 2)// "." und ".." berücksichtigen!!!
		{
			$slidewidth = $this->get_setting('edSliderWidth_Text');
			$slideheight = $this->get_setting('edSliderHeight_Text');
			$slideshow = file_get_contents('template/slideshow_outer.html');
			$slideshow = str_replace('{GSSE_INCL_SLIDEWIDTH}',$slidewidth,$slideshow);
			$slideshow = str_replace('{GSSE_INCL_SLIDEHEIGHT}',$slideheight,$slideshow);
			$slideshow_image = file_get_contents('template/slideshow_image.html');
			$allimages = '';
			$imgmax2 = count($aImages);
			for($p = 0; $p < $imgmax2; $p++)
			{
				if($aImages[$p] != '.' && $aImages[$p] != '..')
				{
					$cur_image = $slideshow_image;
					$cur_image = str_replace('{GSSE_INCL_IMAGENAME}','images/slideshow/' . $aImages[$p],$cur_image);
                    
                    $dbh = $this->db_connect();
                    $imgsql = "SELECT slideshowImageLink FROM " . $this->dbtoken . "slideshow WHERE slideshowImageName = '" . $aImages[$p] . "'";
                    $imgerg = mysqli_query($dbh,$imgsql);
                    if(mysqli_num_rows($imgerg) > 0)
                    {
                        $imgurl = mysqli_fetch_assoc($imgerg);            
                        $cur_image = str_replace('<div>','<div><a u="image" href="'.$imgurl['slideshowImageLink'].'" target="_blanc">',$cur_image);
                        $cur_image = str_replace('</div>','</a></div>',$cur_image);
                    }
					$allimages .= $cur_image;
				}
			}
			$slideshow = str_replace('{GSSE_INCL_IMAGESTOSLIDE}',$allimages,$slideshow);
		}
	}
}

$this->content = str_replace($tag, $slideshow, $this->content);
?>