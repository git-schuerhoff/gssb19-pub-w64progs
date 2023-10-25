<?php
$faqhtml = '';
$faqouter = $this->gs_file_get_contents('template/faq_outer.html');
$faqheadline = $this->gs_file_get_contents('template/faq_headline.html');
$faqline = $this->gs_file_get_contents('template/faq_line.html');
$faqimg = $this->gs_file_get_contents('template/faq_image.html');
if(file_exists("dynsb/class/class.shoplog.php"))
{
	if(!in_array("shoplog",get_declared_classes()))
	{
		require_once("dynsb/class/class.shoplog.php");
	}
	$sl = new shoplog();
	$faqs = $sl->getFAQs();
	$i=1;
	if(@mysqli_num_rows($faqs) > 0)
	{
		$cur_outer = $faqouter;
		$headlines = '';
		while($obj = @mysqli_fetch_object($faqs))
		{
			$cur_headline = $faqheadline;
			$cur_headline = str_replace('{GSSE_INCL_POS}',$i,$cur_headline);
			$cur_headline = str_replace('{GSSE_INCL_FAQTITLE}',$obj->faqTitle,$cur_headline);
			$headlines .= $cur_headline;
			$i++;
		}
		$cur_outer = str_replace('{GSSE_INCL_FAQLINES}',$headlines,$cur_outer);
		$faqhtml .= $cur_outer;
		mysqli_data_seek ($faqs,0);
		$cur_outer = $faqouter;
		$lines = '';
		$i=1;
		while($obj = @mysqli_fetch_object($faqs))
		{
			$text = str_replace("\n","<br />",$obj->faqText);
			$cur_line = $faqline;
			$cur_line = str_replace('{GSSE_INCL_POS}','id="'.$i.'"',$cur_line);
			$cur_line = str_replace('{GSSE_INCL_FAQTITLE}',$obj->faqTitle,$cur_line);
			$cur_line = str_replace('{GSSE_INCL_FAQSUBTITLE}',$obj->faqSubtitle,$cur_line);
			$cur_line = str_replace('{GSSE_INCL_FAQTEXT}',$text,$cur_line);
			$cur_img = '';
			if($obj->faqImage != "")
			{
				$cur_img = $faqimg;
				$cur_img = str_replace('{GSSE_INCL_FAQIMG}',$obj->faqImage,$cur_img);
				$cur_img = str_replace('{GSSE_INCL_FAQIMGWIDTH}',$obj->faqImageXSize,$cur_img);
				$cur_img = str_replace('{GSSE_INCL_FAQIMGHEIGHT}',$obj->faqImageYSize,$cur_img);
			}
			$cur_line = str_replace('{GSSE_INCL_IMGLINE}',$cur_img,$cur_line);
			$lines .= $cur_line;
			$i++;
		}
		$cur_outer = str_replace('{GSSE_INCL_FAQLINES}',$lines,$cur_outer);
		$faqhtml .= $cur_outer;
	}
}
else
{
	$faqhtml = 'Error FAQs';
}

$this->content = str_replace($tag, $faqhtml, $this->content);
?>