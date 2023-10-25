<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$contentpool = '';

$content = $this->db_text_ret('contentpool|Text|Name|' . $aParam[1]);
if(trim($content) != '')
{
	$contentpool = $this->gs_file_get_contents('template/content.html');
	$contentpool = str_replace('{GSSE_INCL_CONTENT}',$content,$contentpool);
}

$this->content = str_replace($tag, $contentpool, $this->content);

?>
