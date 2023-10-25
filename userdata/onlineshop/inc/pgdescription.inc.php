<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$pgdhtml = file_get_contents('template/pgdescription.html');
$pgdcont = '';
$pgdbh = $this->db_connect();
$pgsql = "SELECT GroupHint FROM " . $this->dbtoken . "productgrouplanguage WHERE PgCount = '" . $_GET['idx'] . "' AND LanguageId = '" . $this->lngID . "' LIMIT 1";
$pgerg = mysqli_query($pgdbh,$pgsql);
if(mysqli_errno($pgdbh) == 0)
{
	if(mysqli_num_rows($pgerg) == 1)
	{
		$pg = mysqli_fetch_assoc($pgerg);
		$pgdcont = $pg['GroupHint'];
	}
}
else
{
	$pgdcont = mysqli_error($pgdbh);
}
mysqli_free_result($pgerg);
mysqli_close($pgdbh);
$pgdhtml = str_replace('{GSSE_PG_DESCRIPTION}', $pgdcont, $pgdhtml);
$this->content = str_replace($tag, $pgdhtml, $this->content);
?>