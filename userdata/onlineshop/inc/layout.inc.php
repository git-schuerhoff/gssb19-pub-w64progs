<?php
	//TS: Jetzt noch statisch, später kommt die Info aus der Datenbank
	$layoutFile = 'layout-1-2-1.html';
	$lose = new gs_shopengine($layoutFile);
	$layout = $lose->parse_inc();
	$this->content = str_replace($tag, $layout, $this->content);
?>