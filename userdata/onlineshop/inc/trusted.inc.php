<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]

if($this->get_setting('cbUseTrustedShops_Checked') == 'True')
{
	$tmplFile = file_get_contents('template/trusted.html');
	$tmplFile = str_replace('{GSSE_tsCheckoutOrderNr}', $_SESSION['trusted']['tsCheckoutOrderNr'], $tmplFile);
    $tmplFile = str_replace('{GSSE_tsCheckoutBuyerEmail}', $_SESSION['trusted']['tsCheckoutBuyerEmail'], $tmplFile);
    $tmplFile = str_replace('{GSSE_tsCheckoutOrderAmount}', $_SESSION['trusted']['tsCheckoutOrderAmount'], $tmplFile);
    $tmplFile = str_replace('{GSSE_tsCheckoutOrderCurrency}', $_SESSION['trusted']['tsCheckoutOrderCurrency'], $tmplFile);
    $tmplFile = str_replace('{GSSE_tsCheckoutOrderPaymentType}', $_SESSION['trusted']['tsCheckoutOrderPaymentType'], $tmplFile);
    $tmplFile = str_replace('{GSSE_tsCheckoutOrderEstDeliveryDate}', $_SESSION['trusted']['tsCheckoutOrderEstDeliveryDate'], $tmplFile);
    $this->content = str_replace($tag, $tmplFile, $this->content);
} else {
    $this->content = str_replace($tag, '', $this->content);
}
?>