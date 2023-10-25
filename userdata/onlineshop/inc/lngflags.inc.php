<?php
$flagimg = $this->gs_file_get_contents('template/imagelink.html');
$langshtml = '';
$ilngs = count($this->aslcs);
for($l = 0; $l < $ilngs; $l++) {
	$cur_img = $flagimg;
	$cur_img = str_replace('{GSSE_INCL_LINKCLASS}','',$cur_img);
	$cur_img = str_replace('{GSSE_INCL_LINKURL}','javascript:chg_slc(' . $l . ')',$cur_img);
	$cur_img = str_replace('{GSSE_INCL_LINKTARGET}','_self',$cur_img);
	$cur_img = str_replace('{GSSE_INCL_IMGCLASS}','',$cur_img);
	$cur_img = str_replace('{GSSE_INCL_IMGSRC}','template/images/flag_' . $this->aslcs[$l] . '.gif',$cur_img);
	$cur_img = str_replace('{GSSE_INCL_IMGALT}',$this->aslcs[$l],$cur_img);
	$cur_img = str_replace('{GSSE_INCL_IMGTITLE}',$this->aslcs[$l],$cur_img);
	$langshtml .= $cur_img;
}
$this->content = str_replace($tag, $langshtml, $this->content);
?>