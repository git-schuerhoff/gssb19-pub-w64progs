<?php
function get_bulkprice($aBulk,$quant,$defprice,$action)
{
	$price = 0;
	if($action == 0)
	{
		$blkmax = count($aBulk);
		if($blkmax == 0)
		{
			$price = $defprice;
		}
		else
		{
			for($bl = 0; $bl < $blkmax; $bl++)
			{
				if($quant >= $aBulk[$bl][0])
				{
					$price = $aBulk[$bl][1];
					break;
				}
			}
			if($price == 0)
			{
				$price = $defprice;
			}
		}
	}
	else
	{
		$price = $defprice;
	}
	return round($price,2);
}
?>