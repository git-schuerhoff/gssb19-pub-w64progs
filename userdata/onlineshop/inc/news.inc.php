<?php
require_once("dynsb/class/class.shoplog.php");
$sl = new shoplog();
$news = $sl->getNewsData();
$newshtml = file_get_contents('template/news.html');
$newsdata = '';

if($news) {
	while($o = @mysqli_fetch_object($news)) {
		$newsitem = '';
		$ed = $o->newsEndDate;
    	$ad = date("YmdHis");
		if(substr($ed, 0, 8) == "00000000" || ($ad < $ed)) {
			$newsitem = file_get_contents('template/newsitem.html');
			$newsitem= str_replace('{GSSE_INCL_NEWSITEMTITLE}',trim($o->newsTitle),$newsitem);
			$imagedata = '';
			if(trim($o->newsPicName) != "") {
	            $link1 = "";
	            $link2 = "";
	            if(trim($o->newsPicLink) != "") {
	                $link1 = "<a href=\"".trim($o->newsPicLink)."\">\n";
	                $link2 = "</a>\n";
	            }
	            $imagedata .= $link1;
	            $imagedata .= "<img src=\"dynsb/image/upload/".trim($o->newsPicName)."\"  align=left border=\"0\" alt=\"dynsb/image/upload/".trim($o->newsPicName)."\" />";
	            $imagedata .= $link2;
	        }
	        $newsitem= str_replace('{GSSE_INCL_NEWSITEMPICTURE}',$imagedata,$newsitem);
	        $newsitem= str_replace('{GSSE_INCL_NEWSITEMTEXT}',trim($o->newsContent),$newsitem);
		}
		$newsdata .= $newsitem;
	}	
}
$newshtml=str_replace('{GSSE_INCL_NEWSITEMS}',$newsdata,$newshtml);
$this->content=str_replace('{GSSE_FUNC_NEWS}',$newshtml,$this->content);
?>