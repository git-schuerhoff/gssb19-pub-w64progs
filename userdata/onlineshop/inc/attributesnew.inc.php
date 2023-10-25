<?php
//Achtung!!! Parameter werden als Array $aParam übergeben! test
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
/*New Templates*/
$aAttr = array();
if($_SESSION['aitem']['itemAttribute1'] != '') $aAttr[] = $_SESSION['aitem']['itemAttribute1'];
if($_SESSION['aitem']['itemAttribute2'] != '') $aAttr[] = $_SESSION['aitem']['itemAttribute2'];
if($_SESSION['aitem']['itemAttribute3'] != '') $aAttr[] = $_SESSION['aitem']['itemAttribute3'];

include_once('inc/attributes.inc.php');

$this->content = str_replace($tag, $attrhtml, $this->content);
?>
