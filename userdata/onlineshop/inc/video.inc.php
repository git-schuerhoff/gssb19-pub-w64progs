<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$video = $_SESSION['aitem']['itemVideoLink'];
$this->content = str_replace($tag, $video, $this->content);
?>
