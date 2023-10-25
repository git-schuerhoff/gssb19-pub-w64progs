<?php
session_start();
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
if(isset($_GET['pp_status']) && $_GET['pp_status'] == 'ok'){
	$_POST['step'] = 'cardstepfour';
	$_SESSION['pp_status']='ok';
	$this->content = str_replace('processCheckout(1);', 'processCheckout(4);', $this->content);
} else {
	$_SESSION['pp_status']='';
	$this->content = str_replace('processCheckout(4);', 'processCheckout(3);', $this->content);
}
?>
