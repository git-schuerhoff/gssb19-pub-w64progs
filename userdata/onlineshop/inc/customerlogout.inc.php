<?php
unset($_SESSION['login']);
$this->content = str_replace('{GSSE_FUNC_CUSTOMERLOGOUT}', '', $this->content);
if($this->get_setting('cbUsePhpB2BLogin_Checked') == 'True')
{
	header("Location: index.php?page=main");
}
else
{
	header("Location: index.php?page=main");
}
?>