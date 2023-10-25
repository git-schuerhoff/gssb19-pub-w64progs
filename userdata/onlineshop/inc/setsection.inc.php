<?php
$setsction = '';
echo "<pre>";
print_r($_SESSION);
print_r($_GET);
echo "</pre>";

$this->content = str_replace($tag, $setsction, $this->content);
?>