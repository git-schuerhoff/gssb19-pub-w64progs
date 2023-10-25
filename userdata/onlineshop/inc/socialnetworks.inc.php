<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$social = file_get_contents('template/social_outer.html');
$socialitem = file_get_contents('template/social_item.html');
$onechecked = false;

$allitems = '';

/*Begin Twitter*/
if($this->get_setting('cbWeb20Twitter_Checked') == 'True')
{
	$onechecked = true;
	$sochtml = $socialitem;
	$sochtml = str_replace('{GSSE_INCL_SOCCLASS}','twitter',$sochtml);
	$sochtml = str_replace('{GSSE_INCL_SOCNAME}','Twitter',$sochtml);
	//$sochtml = str_replace('{GSSE_INCL_SOCURL}','http://twitter.com/home?status=' . $this->get_setting('edAbsoluteShopPath_Text'),$sochtml);
	$sochtml = str_replace('{GSSE_INCL_SOCURL}','http://twitter.com/home?status=' . $this->shopurl,$sochtml);
	$allitems .= $sochtml;
}
/*End Twitter*/

/*Begin Facebook*/
if($this->get_setting('cbWeb20Facebook_Checked') == 'True')
{
	$onechecked = true;
	$sochtml = $socialitem;
	$sochtml = str_replace('{GSSE_INCL_SOCCLASS}','facebook',$sochtml);
	$sochtml = str_replace('{GSSE_INCL_SOCNAME}','Facebook',$sochtml);
	//$sochtml = str_replace('{GSSE_INCL_SOCURL}','http://www.facebook.com/share.php?u=' . $this->get_setting('edAbsoluteShopPath_Text'),$sochtml);
	$sochtml = str_replace('{GSSE_INCL_SOCURL}','http://www.facebook.com/share.php?u=' . $this->shopurl,$sochtml);
	$allitems .= $sochtml;
}
/*End Facebook*/
/*Begin Google*/
if($this->get_setting('cbWeb20Google_Checked') == 'True')
{
	$onechecked = true;
	$sochtml = $socialitem;
	$sochtml = str_replace('{GSSE_INCL_SOCCLASS}','google',$sochtml);
	$sochtml = str_replace('{GSSE_INCL_SOCNAME}','Google',$sochtml);
	//$sochtml = str_replace('{GSSE_INCL_SOCURL}','https://accounts.google.com/ServiceLogin?passive=1209600&continue=https%3A%2F%2Fwww.google.com%2Fbookmarks%2Fmark%3Fop%3Dadd%26bkmk%3D' . $this->get_setting('edAbsoluteShopPath_Text'),$sochtml);
	$sochtml = str_replace('{GSSE_INCL_SOCURL}','https://accounts.google.com/ServiceLogin?passive=1209600&continue=https%3A%2F%2Fwww.google.com%2Fbookmarks%2Fmark%3Fop%3Dadd%26bkmk%3D' . $this->shopurl,$sochtml);
	$allitems .= $sochtml;
}
/*End Google*/

if($onechecked === false)
{
	$social = '';
}
else
{
	$social = str_replace('{GSSE_INCL_SOCIALITEMS}',$allitems,$social);
}

$this->content = str_replace($tag, $social, $this->content);
?>