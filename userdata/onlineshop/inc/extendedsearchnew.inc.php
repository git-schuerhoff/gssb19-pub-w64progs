<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$search_items = '';

$jsonstr = json_encode($_POST);

$search_items = "<script type='text/javascript'>" . $this->crlf .
			  "extd_search_itemsnew('" . $jsonstr . "');" . $this->crlf .
			  "</script>";
			  
$this->content = str_replace($tag, $search_items, $this->content);
?>