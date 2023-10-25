<?php
$centraltext = '';
$ctid = $_SESSION['aitem']['itemCentralTextNr'];
if($ctid != '' AND $_SESSION['aitem']['itemUseCentralText'] == 'Y') {
	$param = 'settingmemo|SettingMemo|SettingName|memoArticleText' . (strval($ctid) + 1);
	$text = trim($this->db_text_ret($param));
	if($text != '') {
		$centraltext = $this->gs_file_get_contents($this->absurl . 'template/textbox.html');
		$centraltext = str_replace('{GSSE_INCL_HEADLINE}','',$centraltext);
		$centraltext = str_replace('{GSSE_INCL_BODYTEXT}',$text,$centraltext);
	}
}
$this->content = str_replace($tag,$centraltext,$this->content);
?>