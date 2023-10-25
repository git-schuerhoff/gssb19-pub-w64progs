<?php
//Erwartet $aAttr (array)
$attrhtml = '';

$attr_max = count($aAttr);
if($attr_max > 0)
{
	$tmplFile = "attributes.html";
	$attrhtml = $this->gs_file_get_contents($this->absurl . 'template/' . $tmplFile);
	$selecthtml = $this->gs_file_get_contents($this->absurl . 'template/select.html');
	$opthtml = $this->gs_file_get_contents($this->absurl . 'template/option.html');
	$attrhtml = str_replace('{GSSE_ATTR_CHOOSE}',$this->get_lngtext('LangTagTextPleaseChoose'),$attrhtml);
	$sel_style = '';
	$sel_size = '1';
	$sel_multi = '';
	$odbh = $this->db_connect();
	$selects = '';
	for($a = 0; $a < $attr_max; $a++)
	{
		$sel_id = 'attr' . $a;
		$cur_sel = $selecthtml;
		$cur_opts = '';
		$cur_sel = str_replace('{GSSE_SEL_STYLE}',$sel_style,$cur_sel);
		$cur_sel = str_replace('{GSSE_SEL_ID}',$sel_id,$cur_sel);
		$cur_sel = str_replace('{GSSE_SEL_NAME}',$sel_id,$cur_sel);
		$cur_sel = str_replace('{GSSE_SEL_SIZE}',$sel_size,$cur_sel);
		$cur_sel = str_replace('{GSSE_SEL_MULTIPLE}',$sel_multi,$cur_sel);
		$osel = "SELECT value FROM " . $this->dbtoken . "attributes WHERE name = '" . $aAttr[$a] . "'";
		$oerg = mysqli_query($odbh,$osel);
		$io = 0;
		while($o = mysqli_fetch_assoc($oerg))
		{
			$sel = ($io == 0) ? "selected" : "";
			$cur_opt = $opthtml;
			$cur_opt = str_replace('{GSSE_OPT_VALUE}',$o['value'],$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_TEXT}',$o['value'],$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
			$cur_opts .= $cur_opt;
			$io++;
		}
		$cur_sel = str_replace('{GSSE_SEL_OPTIONS}',$cur_opts,$cur_sel);
		mysqli_free_result($oerg);
		$selects .= $cur_sel;
	}
	$attrhtml = str_replace('{GSSE_ATTR_SELECTS}',$selects,$attrhtml);
	mysqli_close($odbh);
}
//"Rückgabe" $attrhtml
?>