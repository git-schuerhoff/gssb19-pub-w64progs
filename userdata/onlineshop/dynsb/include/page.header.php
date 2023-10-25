<?php
if (file_exists("../image/containertopleft.gif"))
	$path = "..";
elseif (file_exists("../../image/containertopleft.gif"))
	$path = "../..";
elseif (file_exists("../../../image/containertopleft.gif"))
	$path = "../../..";
elseif (file_exists("../../../../image/containertopleft.gif"))
$path = "../../../..";
elseif (file_exists("../../../../../image/containertopleft.gif"))
$path = "../../../../..";
?>
<!--
<div class="shadow5">
<div class="shadow4">
<div class="shadow3">
<div class="shadow2">
<div class="shadow">


<table class="containertop" cellpadding="0" cellspacing="0">
  <tr>
   <td valign="bottom" style="margin:0px; padding:0px; border:0px;"><img src="<?php echo $path;?>/image/containertopleft.gif" alt="" style="display:block;"></td>
   <td style="background-color:#006fb4; width: 100%;">&nbsp;</td>
   <td><img src="<?php echo $path;?>/image/containertopright.gif" alt="" style="display:block;"></td>
 </tr>
</table>
-->

<div style="background-color:#006fb4; width: 100%; height:10px;">
</div>