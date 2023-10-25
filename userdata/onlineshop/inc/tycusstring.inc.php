<?php
	$tycusstring = '';
	if(isset($_SESSION['cus_string'])) {
		$tycusstring = $this->gs_file_get_contents($this->absurl . 'template/tycusstring.html');
		$tycusstring = str_replace('{GSSE_LANG_LangTagCustPassword}', $this->get_lngtext('LangTagCustPassword'), $tycusstring);
		$tycusstring = str_replace('{GSSE_LANG_LangTagCustPasswordRemember}', $this->get_lngtext('LangTagCustPasswordRemember'), $tycusstring);
		$tycusstring = str_replace('{GSSE_INCL_CUSSTRING}', base64_decode($_SESSION['cus_string']), $tycusstring);
		unset($_SESSION['cus_string']);
	}
	$this->content = str_replace($tag, $tycusstring, $this->content);
?>