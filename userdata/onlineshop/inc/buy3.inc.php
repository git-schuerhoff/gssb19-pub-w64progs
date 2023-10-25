<?php
session_start();
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$buy3html = '';
/*
print_r($_POST);
echo "<br />*******************************<br />";
print_r($_SESSION);
*/

/*Begin Cookies*/
if(isset($_POST['rememberme']))
{
	if($_POST['rememberme'] == 2)
	{
		foreach ($_POST as $key => $value)
		{
			/*$_COOKIE[$key] = $value;*/
			setcookie($key, $value, time()+(3600*24*365));
		}
	}
}
else
{
	//Falls abgewählt und vorher gewählt
	//zur Sicherheit jedes Cookie zerstören
	foreach ($_POST as $key => $value)
	{
		if(isset($_COOKIE[$key]))
		{
			unset($_COOKIE[$key]);
		}
	}
}
/*End Cookies*/

/*
echo "<br />------------------------------<br />Cookies:<br />";
if(isset($_COOKIE))
{
	print_r($_COOKIE);
}
*/

$this->content = str_replace('{GSSE_INCL_SPECPAYMENT}', $_SESSION['delivery']['paym']['internalname'], $this->content);
$this->content = str_replace('{GSSE_INCL_FORMACTION}', 'inc/gsorder.inc.php', $this->content);

$this->content = str_replace('{GSSE_FUNC_BUY3}', $buy3html, $this->content);
