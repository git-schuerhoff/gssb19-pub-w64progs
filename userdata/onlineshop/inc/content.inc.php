<?php
	//TS: Jetzt noch statisch, später kommt die Info aus der Datenbank
	if(isset($_GET['page'])) {
		$contentFile = $_GET['page'].'.html';
	} else {
		$contentFile = 'main.html';
	}
	$ctse = new gs_shopengine($contentFile);
	$content = $ctse->parse_inc();
	$this->content = str_replace($tag, $content, $this->content);
?>