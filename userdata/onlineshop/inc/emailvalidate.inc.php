<?php
	session_start();
	$valhtml = $this->gs_file_get_contents($this->absurl . 'template/emailvalidate.html');
	$aValTags = $this->get_tags_ret($valhtml);
	$valhtml = $this->parse_texts($aValTags,$valhtml);
	
	
	$this->content = str_replace('{GSSE_FUNC_EMAILVALIDATE}', $valhtml, $this->content);
?>