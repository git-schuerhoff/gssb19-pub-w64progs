<?php
$notepadhtml = '';
$aerg = array();
$aPrices = array();
if($this->phpactive() === true)
{
	if($this->get_setting('cbUsePhpNotepad_Checked') == 'True')
	{
		if($_SESSION['login']['ok'])
		{
			
			$notepadhtml = "<script type='text/javascript'>" . $this->crlf .
									"notepad();" . $this->crlf .
								"</script>";
		}
		else
		{
			header("Location: index.php?page=createcustomer");
		}
	}
}

$this->content = str_replace($tag, $notepadhtml, $this->content);
?>