<?php
if (file_exists("../image/containerbottomleft.gif"))
	$path = "..";
elseif (file_exists("../../image/containerbottomleft.gif"))
	$path = "../..";
elseif (file_exists("../../../image/containerbottomleft.gif"))
	$path = "../../..";
elseif (file_exists("../../../../image/containerbottomleft.gif"))
	$path = "../../../..";
elseif (file_exists("../../../../../image/containerbottomleft.gif"))
	$path = "../../../../..";
?>
<!--
<table class="containerbottom"  cellpadding="0" cellspacing="0">
  <tr>
   <td valign="bottom"><img src="<?php echo $path;?>/image/containerbottomleft.gif" alt=""></td>
   <td style="background-color:#006fb4; width:100%;"></td>
   <td><img src="<?php echo $path;?>/image/containerbottomright.gif" alt=""></td>
 </tr>
</table>
-->

<!-- closing divs shadow-->
<!--
</div>
</div>
</div>
</div>
</div>
<div style="clear:left;"></div>
-->
<!-- // closing divs shadow-->

<div style="background-color:#006fb4; width: 100%; height:15px;">
</div>