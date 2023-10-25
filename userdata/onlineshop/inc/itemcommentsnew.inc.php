<?php
$commenthtml = '';
$aerg = array();
$aPrices = array();
if($this->phpactive() === true)
{
	if($this->get_setting('cbUsePhpUsercomments_Checked') == 'True')
	{
		if($_SESSION['login']['ok'])
		{
			
			$commenthtml = "<script type='text/javascript'>" . $this->crlf .
									"itemcomments();" . $this->crlf .
								"</script>";
		}
		else
		{
			header("Location: index.php?page=createcustomer");
		}
	}
}

$this->content = str_replace($tag, $commenthtml, $this->content);
?>