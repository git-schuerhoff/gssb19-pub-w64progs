<?php
	$cus = new gs_shopengine('customerlogin.html'); 
	$this->content = str_replace($tag, $cus->parse_inc(), $this->content);
?>