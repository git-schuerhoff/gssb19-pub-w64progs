<?php
header('Content-Type: image/png');
require("conf/db.const.inc.php");
require("dynsb/class/class.diagram.bar.php");
$phdbh = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
$dates = array();
$maxval = 0;
$phsql = "SELECT * FROM ".DBToken."pricehistory WHERE
			prchItemNumber = '".$pchItemNumber."' AND 
			prchLanguageId = '".$gssbLang."'
			ORDER BY prchDateTime DESC 
			LIMIT 4";
$pherg = mysqli_query($phdbh,$phsql);
$prices = array();
$dates = array();
$maxval = 0;
if(mysqli_errno($phdbh) == 0)
{
	if(mysqli_num_rows($pherg) > 1)
	{
		while($obj = mysqli_fetch_object($pherg))
		{
			/*array_push($prices, $obj->prchPrice);*/
			$prices[] = $obj->prchPrice;
			/*array_push($dates, date('d.m.Y', strtotime($obj->prchDateTime)));*/
			$dates[] = date('d.m.Y', strtotime($obj->prchDateTime));
			if($maxval < $obj->prchPrice)
			{
				$maxval = $obj->prchPrice;
			}
		}

		if(count($prices) == 0)
		{
			/*array_push($prices, 0);*/
			$prices[] = 0;
		}
		if(count($dates) == 0)
		{
			/*array_push($dates, 0);*/
			$dates[] = 0;
		}
		$gradientToogleStep = 2;
		$dataTextOffset = 1;
		//get get get
		$xsize = 400;
		$ysize = 100;
		//($ysize > 600) ? $addRightSpc = 0 : $addRightSpc =  60; //TODO ?needed for what?

		$layout    = 0;
		$barlayout = 0;

		$prices = array_reverse($prices);
		$dates = array_reverse($dates);

		$scaleval = doubleval($maxval / $ysize);

		//to avoid divison by zero
		if($scaleval == 0) $scaleval = 1;

		$scalevalend = $scaleval + doubleval(($scaleval / 100) * 10);
		if($scalevalend == 0) $scalevalend = 1;

		//create DIA object
		$dia = new dia_bar($xsize, $ysize, 80, 50, 60, 30 + $addRightSpc, $prices, $layout);

		$dia->setBackgroundColor('white');
		$dia->setDataFontColor('darkgrey');

		//$dia->setGradientTotal('gslightblue', 'white');
		$dia->setGradientToogleStep($gradientToogleStep, 'gslightblue', 'white', 'white', 'lightgrey');
		$dia->recalcYStep($scalevalend, $maxval);
		$dia->createGrid(20, 'grey');
		$dia->createDiaBorder('grey');

		if($layout == 0) {
			$spc = ($dia->xstep / 100) * 38;
		} else {
			$spc = ($dia->ystep / 100) * 38;
		}

		if($barlayout == 0) $dia->displayBars($scalevalend, 'gsdarkblue', 'gslightblue', $spc, $dataTextOffset);
		if($barlayout == 1) $dia->displayGradientBars($scalevalend, 'white', 'darkgreen', 'darkgrey', $spc, 0);

		$ruler = 25;
		$rulerval = doubleval($ruler / $scaleval);
		if($rulerval < 4) $rulerval = 4;
		if($rulerval > 10) $rulerval = 10;

		$dia->addRulersX('darkgrey', $rulerval, 'bottom');
		$imgLeftSpcTemp = $dia->imgLeftSpc;
		$dia->imgLeftSpc = 35;
		$dia->addRulersXDataValues('darkgrey', 8, $dates);
		$dia->imgLeftSpc = $imgLeftSpcTemp;
		$dia->addRulersY('darkgrey', $rulerval, 'left');

		if($layout == 0) {
			$dia->addRulersYText('darkgrey', 2, 55);
			//$dia->addRulersXData('darkgrey', 2);
		} else {
			$dia->addRulersXText('darkgrey', 2, 5);
			$dia->addRulersYData('darkgrey', 2, 52);
		}

		$dia->setHText("Preisentwicklung", 3, 20 - ($layout * 10), 15, 'gsdarkblue');
		$dia->setHText($strPeriod, 2, 20 - ($layout * 10), 30, 'gsdarkorange');

		imagepng($dia->img);
		imagedestroy($dia->img);
	}
}
else
{
	die(mysqli_error($phdbh) . "<br />" . $phsql);
}
?>