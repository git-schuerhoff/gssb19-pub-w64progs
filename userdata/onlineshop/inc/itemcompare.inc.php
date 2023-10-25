<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$itemcompare = '';
$itemstocompare = $this->gs_file_get_contents('template/pcontent.html');
$itemstocompare = str_replace('{GSSE_INCL_PCLASS}','empty',$itemstocompare);
$itemstocompare = str_replace('{GSSE_INCL_PCONTENT}',$this->get_lngtext('LangTagItemCompareNoItems'),$itemstocompare);

if($this->get_setting('cbArticleCompare_Checked') == 'True')
{
	$itemcompare = $this->gs_file_get_contents('template/itemcompare_outer.html');
	$itemcompare = str_replace('{GSSE_LANG_LangTagItemCompare}',$this->get_lngtext('LangTagItemCompare'),$itemcompare);
	
	if(isset($_SESSION['aitems_compare']))
	{
		if(count($_SESSION['aitems_compare']) > 0)
		{
			$itemstocompare = '<script language="javascript">' . $this->crlf .
									'pre_update_compare_box(\'' . json_encode($_SESSION['aitems_compare']) . '\');' . $this->crlf .
									'</script>' . $this->crlf;
		}
	}
	$itemcompare = str_replace('{GSSE_INCL_ITEMSTOCOMPARECONT}',$itemstocompare,$itemcompare);
}
$this->content = str_replace($tag, $itemcompare, $this->content);

?>
