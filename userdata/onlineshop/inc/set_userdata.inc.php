<?php
session_start();
if(isset($_SESSION['userdata']))
{
	unset($_SESSION['userdata']);
}
chdir("../");
include_once("inc/class.shopengine.php");
$se = new gs_shopengine();

$_SESSION['userdata'] = array(
							"privorbusiness" => $_POST['privorbusiness'],
							"company" => $_POST['company'],
							"cusnumber" => $_POST['cusnumber'],
							"firmvatid" => $_POST['firmvatid'],
							"mrormrs" => $_POST['mrormrs'],
							"firstname" => urldecode($_POST['firstname']),
							"lastname" => urldecode($_POST['lastname']),
							"address" => urldecode($_POST['address']),
							"address2" => urldecode($_POST['address2']),
							"city" => urldecode($_POST['city']),
							"zip" => $_POST['zip'],
							"state" => urldecode($_POST['state']),
							"email" => urldecode($_POST['email']),
							"phone" => $_POST['phone'],
							"fax" => $_POST['fax'],
							"mobil" => $_POST['mobil'],
							"birth" => $_POST['birth'],
							"actionkey" => urldecode($_POST['actionkey']),
							"wantnewsletter" => urldecode($_POST['wantnewsletter']),
							"accepttermsancond" => urldecode($_POST['accepttermsancond']),
							"emailformat" => $_POST['emailformat'],
							"acceptror" => urldecode($_POST['acceptror']));
							
// Additional fields													
for($f = 1; $f <= 5; $f++)
{
	//We have 5 additional fields
	if($se->get_setting('cb_activ' . $f . '_Checked') == 'True')
	{
		$fieldtitle = $se->get_setting('ed_name' . $f . '_Text');
		$fieldname = $se->formfriendly($fieldtitle);
		if(isset($_POST[$fieldname]))
		{
			$_SESSION['userdata'][$fieldname] = urldecode($_POST[$fieldname]);
		}
	}
}
?>