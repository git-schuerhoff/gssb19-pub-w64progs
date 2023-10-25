<?php
session_start();

$msdbh = $this->db_connect();
$mssql = "SELECT DISTINCT(itemManufacturer) FROM " . $this->dbtoken . "itemdata WHERE itemLanguageId = '" . $this->lngID . "' AND itemManufacturer != '' ORDER BY itemItemId ASC";
$mserg = mysqli_query($msdbh,$mssql);
$option = $this->gs_file_get_contents('template/option.html');
$opts = '';
if(mysqli_error($msdbh) == 0)
{
	if(mysqli_num_rows($mserg) > 0)
	{
		while($m = mysqli_fetch_assoc($mserg))
		{
			$cur_opt = $option;
			$sel = '';
			$cur_opt = str_replace('{GSSE_OPT_VALUE}',$m['itemManufacturer'],$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_TEXT}',$m['itemManufacturer'],$cur_opt);
			$opts .= $cur_opt;
		}
	}
}
$this->content = str_replace('{GSSE_FUNC_MANUFACTURER}',$opts,$this->content);
?>