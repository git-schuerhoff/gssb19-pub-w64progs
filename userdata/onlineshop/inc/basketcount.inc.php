<?php
$cnt = 0;
if(isset($_SESSION['basket']))
{
	$cnt = count($_SESSION['basket']);
}
$this->content = str_replace($tag, $cnt, $this->content);
?>