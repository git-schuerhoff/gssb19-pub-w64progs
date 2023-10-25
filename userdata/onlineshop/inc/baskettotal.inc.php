<?php
$total = 0.0;
if(is_array($_SESSION['basket']))
{
	foreach($_SESSION['basket'] as $val)
	{
		$total += $val['art_price']*$val['art_count'];
	}
}

$this->content = str_replace($tag, number_format($total,2,",",""), $this->content);
?>