<?php
	$this->get_item($_GET['item']);
	$this->content = str_replace($tag, '', $this->content);
?>