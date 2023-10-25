<?php

/*

	create bar statistics in png format
	-----------------------------------
	(c) 2004/2005 by Raimund Kulikowski

*/

class dia_bar {

	var $imgWidth = 0;
	var $imgHeight = 0;
	var $imgTopSpc = 0;
	var $imgBottomSpc = 0;
	var $imgLeftSpc = 0;
	var $imgRightSpc = 0;
	var $img = null;
	var $layout = 0;
	var $base = 0;
	var $dataFontSize = 2;
	var $dataFontColor = 'black';
	var $aCols = array();
	var $aRGB = array();
	var $data = array();

	var $imagemapCoords = array ();



	/*
		CONSTRUCTOR
		$imgWidth		= width of the diagram
		$imgHight		= height of the diagram
		$topSpc			= top border
		$bottomSpc		= bottom border
		$leftSpc		= left border
		$rightSpc		= right border
		$data			= array of values for the bars
		$layout			= 0 - bars arranged horizontal
						= 1 - bars arranged vertical
	*/
	function __construct($imgWidth, $imgHeight, $topSpc, $bottomSpc, $leftSpc, $rightSpc, $data, $layout) {
		$this->imgWidth = $imgWidth;
		$this->imgHeight = $imgHeight;
		$this->imgTopSpc = $topSpc;
		$this->imgBottomSpc = $bottomSpc;
		$this->imgLeftSpc = $leftSpc;
		$this->imgRightSpc = $rightSpc;
		$this->data = $data;
		$this->layout = $layout;
		$this->xlines = 0;
		$this->ylines = 0;
		$this->xstep = 0;
		$this->ystep = 0;
		$this->setImage();
		$this->setDefaultColors();

	}

	function setImage() {
		$this->img = imagecreatetruecolor(($this->imgLeftSpc + $this->imgWidth + $this->imgRightSpc), ($this->imgTopSpc + $this->imgHeight + $this->imgBottomSpc));
	}

	function setBackgroundColor($color) {
		imagefill($this->img, 0, 0, $this->aCols[$color]);
	}

	function setDataFontSize($val) {
        $this->dataFontSize = intval($val);
    }

	function setDataFontColor($val) {
        $this->dataFontColor = trim($val);
    }

	function setDefaultColors() {
		$this->addColor('white', 255, 255, 255);
		$this->addColor('black', 0, 0, 0);
		$this->addColor('red', 255, 0, 0);
		$this->addColor('darkred', 128, 0, 0);
		$this->addColor('green', 0, 255, 0);
		$this->addColor('darkgreen', 0, 128, 0);
		$this->addColor('blue', 0, 0, 255);
		$this->addColor('darkblue', 0, 0, 128);
		$this->addColor('grey', 192, 192, 192);
		$this->addColor('lightgrey', 222, 222, 222);
		$this->addColor('darkgrey', 101, 101, 101);
		$this->addColor('gsdarkblue', 104, 157, 228);
		$this->addColor('gslightblue', 184, 212, 250);
		$this->addColor('gsdarkorange', 228, 157, 104);
		$this->addColor('gslightorange', 250, 212, 184);
	}

	function addColor($name, $red, $green, $blue) {
		$this->aRGB[$name] = array('r' => $red, 'g' => $green, 'b' => $blue);
		$this->aCols[$name] = imagecolorallocate($this->img, $red, $green, $blue);
	}

	function createDiaBorder($color) {
		imageline($this->img, (0 + $this->imgLeftSpc), (0 + $this->imgTopSpc), (0 + $this->imgLeftSpc), ($this->imgHeight + $this->imgTopSpc), $this->aCols[$color]);
		imageline($this->img, (0 + $this->imgLeftSpc), (0 + $this->imgTopSpc), ($this->imgWidth + $this->imgLeftSpc), (0 + $this->imgTopSpc), $this->aCols[$color]);
		imageline($this->img, ($this->imgLeftSpc + $this->imgWidth), (0 + $this->imgTopSpc), ($this->imgLeftSpc + $this->imgWidth), ($this->imgHeight + $this->imgTopSpc - 1), $this->aCols[$color]);
		imageline($this->img, (0 + $this->imgLeftSpc), ($this->imgHeight + $this->imgTopSpc - 1), ($this->imgLeftSpc + $this->imgWidth - 1), ($this->imgHeight + $this->imgTopSpc - 1), $this->aCols[$color]);
	}

	function createGrid($resultlines, $color) {
		if($this->layout == 0) {
			$this->xlines = intval($resultlines);
			$this->ylines = count($this->data);
            $this->xstep = ($this->imgWidth / $this->ylines);
		} else {
			$this->xlines = count($this->data);
			$this->ylines = intval($resultlines);
            $this->ystep = ($this->imgHeight / $this->xlines);
		}

		for($x = 1; $x < $this->xlines; $x++) {
            if(($x * $this->ystep) <= $this->imgHeight) {
                imageline($this->img, (0 + $this->imgLeftSpc), (0 + $this->imgTopSpc + $this->imgHeight - ($this->ystep * $x)), ($this->imgWidth + $this->imgLeftSpc), (0 + $this->imgTopSpc + $this->imgHeight - ($this->ystep * $x)),  $this->aCols[$color]);
            }
		}

		for($y = 1; $y < $this->ylines; $y++) {
            if(($y * $this->xstep) <= $this->imgWidth) {
                imageline($this->img, (0 + $this->imgLeftSpc + $this->xstep * $y), (0 + $this->imgTopSpc), (0 + $this->imgLeftSpc + $this->xstep * $y), ($this->imgHeight + $this->imgTopSpc),  $this->aCols[$color]);
            }
		}

	}

	function setGradientTotal($col1, $col2) {
		if($this->layout == 0) {
			$this->setHGradient($this->imgLeftSpc, $this->imgTopSpc, $this->imgWidth, $this->imgHeight, $col1, $col2);
		} else {
			$this->setVGradient($this->imgLeftSpc, $this->imgTopSpc, $this->imgWidth, $this->imgHeight, $col1, $col2);
		}
	}

	function setGradientToogleStep($step, $col1, $col2, $col3, $col4) {
		if($step <= 0 ||$step >= count($this->data)) {
			$this->setGradientTotal($col1, $col2);
		} else {
			$xwidth = $this->imgWidth / count($this->data);
			$yheight = $this->imgHeight / count($this->data);
			$counter = 0;
			$chk = 0;
			for($i = 0; $i < count($this->data); $i++) {
				if($chk == 0) {
					$newcol1 = $col1;
					$newcol2 = $col2;
					$counter++;
					if($counter == $step) {
						$chk = 1;
						$counter = 0;
					}
				} else {
					$newcol1 = $col3;
					$newcol2 = $col4;
					$counter++;
					if($counter == $step) {
						$chk = 0;
						$counter = 0;
					}
				}
				if($this->layout == 0) {
					$this->setHGradient($this->imgLeftSpc + ($i * $xwidth), $this->imgTopSpc, $xwidth , $this->imgHeight, $newcol1, $newcol2);
				} else {
					$this->setVGradient($this->imgLeftSpc, $this->imgTopSpc + ($i * $yheight), $this->imgWidth , $yheight, $newcol1, $newcol2);
				}
			}
		}
	}

	function setHGradient($x, $y, $xlen, $ylen, $col1, $col2) {
		$rr = (($this->aRGB[$col1]['r'] - $this->aRGB[$col2]['r']) * -1);
		$gr = (($this->aRGB[$col1]['g'] - $this->aRGB[$col2]['g']) * -1);
		$br = (($this->aRGB[$col1]['b'] - $this->aRGB[$col2]['b']) * -1);
		if($ylen == 0) $ylen = 1; // avoid division by zero if there is no data
		$rq = $rr / $ylen;
		$gq = $gr / $ylen;
		$bq = $br / $ylen;
		for($i = 0; $i < $ylen; $i++) {
			imageline($this->img, $x, $y + $i, $x + $xlen, $y + $i, imagecolorallocate($this->img, ($this->aRGB[$col1]['r'] + ($rq * $i)), ($this->aRGB[$col1]['g'] + ($gq * $i)), ($this->aRGB[$col1]['b'] + ($bq * $i))));
		}
	}

	function setVGradient($x, $y, $xlen, $ylen, $col1, $col2) {
		$rr = (($this->aRGB[$col1]['r'] - $this->aRGB[$col2]['r']) * -1);
		$gr = (($this->aRGB[$col1]['g'] - $this->aRGB[$col2]['g']) * -1);
		$br = (($this->aRGB[$col1]['b'] - $this->aRGB[$col2]['b']) * -1);
		if($xlen == 0) $xlen = 1; // avoid division by zero if there is no data
		$rq = $rr / $xlen;
		$gq = $gr / $xlen;
		$bq = $br / $xlen;
		for($i = 0; $i < $xlen; $i++) {
			imageline($this->img, $x + $i, $y, $x + $i, $y + $ylen, imagecolorallocate($this->img, ($this->aRGB[$col2]['r'] - ($rq * $i)), ($this->aRGB[$col2]['g'] - ($gq * $i)), ($this->aRGB[$col2]['b'] - ($bq * $i))));
		}
	}

	function setHText($text, $texttyp, $x, $y, $color) {
		imagestring($this->img, $texttyp, $x, $y, trim($text), $this->aCols[$color]);
	}

	function setVText($text, $texttyp, $x, $y, $color) {
		imagestringup($this->img, $texttyp, $x, $y, trim($text), $this->aCols[$color]);
	}

	function displayBars($div, $color1, $color2, $spc, $dataTextOffset = null, $textMode = 0) {
		if($this->layout == 0) {


			//old sub //
			//$sub = $this->xstep / 5;
			$sub = floor($this->xstep / 5);

			for($i = 0; $i < $this->ylines; $i++) {
			    if($textMode == 0) $textdata = $this->data[$i];
                if($textMode == 1) $textdata = str_replace(".",",", sprintf("%01.2f",$this->data[$i]));

//old bars
//			    imagefilledrectangle($this->img, ($spc /2) + $this->imgLeftSpc + ($i * $this->xstep), ($this->imgHeight + $this->imgTopSpc)-($this->data[$i] / $div), $this->imgLeftSpc + (($i+1) * $this->xstep) - ($spc / 2), ($this->imgHeight + $this->imgTopSpc), $this->aCols[$color1]);
//				imagefilledrectangle($this->img, ($spc / 2) + $this->imgLeftSpc + ($i * $this->xstep)+1, ($this->imgHeight + $this->imgTopSpc)-($this->data[$i] / $div) + 2, $this->imgLeftSpc + (($i+1) * $this->xstep) - $sub - ($spc / 2), ($this->imgHeight + $this->imgTopSpc) - 2, $this->aCols[$color2]);

			 //==new bars==
				$x1=($spc / 2) + $this->imgLeftSpc + ($i * $this->xstep)+1;
				$y1=($this->imgHeight + $this->imgTopSpc)-($this->data[$i] / $div) + 2;
				$x2=$this->imgLeftSpc + (($i+1) * $this->xstep) - $sub - ($spc / 2)+2;
				$y2= ($this->imgHeight + $this->imgTopSpc) - 2;

				if ($y1>$y2){$y1=$y2;}

				imagefilledrectangle($this->img,$x1+2,$y1+3,$x2+3,$y2+2,$this->aCols[$color1]);

				imagerectangle($this->img,$x1,$y1,$x2,$y2,$this->aCols[$color1]);
				imagefilledrectangle($this->img,$x1+1,$y1+1,$x2-1,$y2,$this->aCols[$color2]);
			//==end new bars==

				if($dataTextOffset !== null) $this->setVText($textdata, $this->dataFontSize, (0 + $this->imgLeftSpc + ($this->xstep * $i) + ($this->xstep / 2) - $this->dataFontSize*3) - 1 , ($this->imgHeight + $this->imgTopSpc)-($this->data[$i] / $div) - 3, $this->dataFontColor);
			}
		} else {
			$sub = $this->ystep / 5;
			for($i = 0; $i < $this->xlines; $i++) {
			    if($textMode == 0) $textdata = $this->data[$i];
                if($textMode == 1) $textdata = str_replace(".",",", sprintf("%01.2f",$this->data[$i]));

                //Old bars vertical
                //imagefilledrectangle($this->img, $this->imgLeftSpc, ($this->imgTopSpc + $this->imgHeight) + ($spc / 2) - ($i * $this->ystep) - $this->ystep, $this->imgLeftSpc + ($this->data[$i] / $div), ($this->imgTopSpc + $this->imgHeight) - ($spc / 2) - ($i * $this->ystep), $this->aCols[$color1]);
                //imagefilledrectangle($this->img, $this->imgLeftSpc +1, ($this->imgTopSpc + $this->imgHeight) + ($spc / 2) - ($i * $this->ystep) - $this->ystep + 1, $this->imgLeftSpc + ($this->data[$i] / $div) - 1, ($this->imgTopSpc + $this->imgHeight) - ($i * $this->ystep) - $sub - ($spc / 2) , $this->aCols[$color2]);

				//==new bars vertical==


				$x1=$this->imgLeftSpc;
				$x2=$this->imgLeftSpc + ($this->data[$i] / $div);
				$y1=($this->imgTopSpc + $this->imgHeight) + ($spc / 2) - ($i * $this->ystep) - $this->ystep;
				$y2=($this->imgTopSpc + $this->imgHeight) - ($spc / 2) - ($i * $this->ystep);

				imagefilledrectangle($this->img, $x1  , $y1 , $x2 -4   , $y2  +1       , $this->aCols[$color1]);

                imagefilledrectangle($this->img, $x1 , $y1  , $x2 , $y2 -3 , $this->aCols[$color2]);
                imagerectangle      ($this->img, $x1 , $y1  , $x2 , $y2 -3, $this->aCols[$color1]);


				//==end new bars vertical==


				if($dataTextOffset !== null) $this->setHText($textdata, $this->dataFontSize, (0 + $this->imgLeftSpc + ($this->data[$i] / $div) + 4) , $this->imgTopSpc + $this->imgHeight - ($i * $this->ystep) - ($this->ystep / 2) - $this->dataFontSize*3 - 1, $this->dataFontColor);
			}
		}
	}

	function displayGradientBars($div, $colorTop, $colorBottom, $colorBorder, $spc, $dataTextOffset = null, $textMode = 0) {
		if($this->layout == 0) {
			for($i = 0; $i < $this->ylines; $i++) {
			    if($textMode == 0) $textdata = $this->data[$i];
                if($textMode == 1) $textdata = str_replace(".",",", sprintf("%01.2f",$this->data[$i]));
				imagefilledrectangle($this->img, ($spc / 2) + $this->imgLeftSpc + ($i * $this->xstep), ($this->imgHeight + $this->imgTopSpc)-($this->data[$i] / $div), $this->imgLeftSpc + (($i+1) * $this->xstep) - ($spc / 2), ($this->imgHeight + $this->imgTopSpc), $this->aCols[$colorBorder]);
				$this->setHGradient(($spc / 2) + $this->imgLeftSpc + ($i * $this->xstep)+1, ($this->imgHeight + $this->imgTopSpc)-($this->data[$i] / $div) + 1, ($this->xstep) - $spc -2 , ($this->data[$i] / $div), $colorTop, $colorBottom);
				if($dataTextOffset !== null) $this->setVText($textdata, $this->dataFontSize, (0 + $this->imgLeftSpc + ($this->xstep * $i) + ($this->xstep / 2) - $this->dataFontSize*3) - 1 , ($this->imgHeight + $this->imgTopSpc)-($this->data[$i] / $div) - 3, $this->dataFontColor);
			}
		} else {
			for($i = 0; $i < $this->xlines; $i++) {
			    if($textMode == 0) $textdata = $this->data[$i];
                if($textMode == 1) $textdata = str_replace(".",",", sprintf("%01.2f",$this->data[$i]));
				imagefilledrectangle($this->img, $this->imgLeftSpc, ($this->imgTopSpc + $this->imgHeight) + ($spc / 2) - ($i * $this->ystep) - $this->ystep, $this->imgLeftSpc + ($this->data[$i] / $div), ($this->imgTopSpc + $this->imgHeight) - ($spc / 2) - ($i * $this->ystep), $this->aCols[$colorBorder]);
				$this->setVGradient($this->imgLeftSpc + 1, ($spc / 2) + ($this->imgTopSpc + $this->imgHeight) - ($i * $this->ystep) - $this->ystep + 1, ($this->data[$i] / $div) - 2 , ($this->ystep - $spc - 2), $colorTop, $colorBottom);
				if($dataTextOffset !== null) $this->setHText($textdata, $this->dataFontSize, (0 + $this->imgLeftSpc + ($this->data[$i] / $div) + 4) , $this->imgTopSpc + $this->imgHeight - ($i * $this->ystep) - ($this->ystep / 2) - $this->dataFontSize*3 - 1, $this->dataFontColor);
			}
		}
	}

	function addRulersX($color, $longmark, $ypos = 'bottom') {
		$sub = $longmark / 2;
		$yval = $this->imgHeight;
		if($ypos == 'bottom') $yval = $this->imgHeight;
		if($ypos == 'top') $yval = 0;
		if($ypos == 'both') $yval = 0;
		for($i = 0; $i <= $this->ylines; $i++) {
            if(($i * $this->xstep) <= $this->imgWidth) {
    			if($ypos == 'both') imageline($this->img, $this->imgLeftSpc + ($i * $this->xstep), ($this->imgHeight + $this->imgTopSpc) - $sub, $this->imgLeftSpc + ($i * $this->xstep), ($this->imgHeight + $this->imgTopSpc) + $sub, $this->aCols[$color]);
    			imageline($this->img, $this->imgLeftSpc + ($i * $this->xstep), ($yval + $this->imgTopSpc) - $sub, $this->imgLeftSpc + ($i * $this->xstep), ($yval + $this->imgTopSpc) + $sub, $this->aCols[$color]);
    		}
		}
		if($ypos == 'both') imageline($this->img, (0 + $this->imgLeftSpc), (0 + $this->imgTopSpc), ($this->imgWidth + $this->imgLeftSpc), (0 + $this->imgTopSpc), $this->aCols[$color]);
		imageline($this->img, (0 + $this->imgLeftSpc), ($this->imgHeight + $this->imgTopSpc), ($this->imgLeftSpc + $this->imgWidth - 1), ($this->imgHeight + $this->imgTopSpc), $this->aCols[$color]);
	}

	function addRulersY($color, $longmark, $xpos = 'left') {
		$sub = $longmark / 2;
		$xval = $this->imgWidth;
		if($xpos == 'left') $xval = 0;
		if($xpos == 'right') $xval = $this->imgWidth;
		if($xpos == 'both') $xval = $this->imgWidth;
		for($i = 0; $i <= $this->xlines; $i++) {
            if(($i * $this->ystep) <= $this->imgHeight) {
    			if($xpos == 'both') imageline($this->img, $this->imgLeftSpc - $sub , ($this->imgHeight + $this->imgTopSpc) - ($i * $this->ystep), $this->imgLeftSpc + $sub, ($this->imgHeight + $this->imgTopSpc) - ($i * $this->ystep), $this->aCols[$color]);
    			imageline($this->img, $this->imgLeftSpc + $xval - $sub , ($this->imgHeight + $this->imgTopSpc) - ($i * $this->ystep), $this->imgLeftSpc + $xval + $sub, ($this->imgHeight + $this->imgTopSpc) - ($i * $this->ystep), $this->aCols[$color]);
    		}
		}
		if($xpos == 'both') imageline($this->img, ($this->imgLeftSpc + $this->imgWidth), (0 + $this->imgTopSpc), ($this->imgLeftSpc + $this->imgWidth), ($this->imgHeight + $this->imgTopSpc - 1), $this->aCols[$color]);
		imageline($this->img, (0 + $this->imgLeftSpc), (0 + $this->imgTopSpc), (0 + $this->imgLeftSpc), ($this->imgHeight + $this->imgTopSpc), $this->aCols[$color]);
	}

    function recalcYStep($div, $maxval) {
        $ypix = 0;
        $this->detectRulerBase($maxval);
        $ypix = ($this->base / $div);
        if($this->layout == 0) {
            $this->ystep = $ypix;
        } else {
            $this->xstep = $ypix;
        }
    }

    function detectRulerBase($maxval) {
        if($maxval > 5) $this->base = 2;
        if($maxval > 10) $this->base = 5;
        if($maxval > 25) $this->base = 10;
        if($maxval > 50) $this->base = 15;
        if($maxval > 100) $this->base = 25;
        if($maxval > 250) $this->base = 50;
        if($maxval > 500) $this->base = 100;
        if($maxval > 1000) $this->base = 250;
        if($maxval > 2500) $this->base = 500;
        if($maxval > 5000) $this->base = 1000;
        if($maxval > 10000) $this->base = 2500;
        if($maxval > 25000) $this->base = 5000;
        if($maxval > 50000) $this->base = 10000;
        if($maxval > 100000) $this->base = 25000;
        if($maxval > 250000) $this->base = 50000;
        if($maxval > 500000) $this->base = 100000;
        if($maxval > 1000000) $this->base = 250000;
        if($maxval > 2500000) $this->base = 500000;
        if($maxval > 5000000) $this->base = 1000000;
        if($maxval > 10000000) $this->base = 2500000;
    }

	function addRulersYText($color, $fontSize, $leftOffset) {
		for($x = 0; $x < $this->xlines; $x++) {
            if(($x * $this->ystep) <= $this->imgHeight) {
                $slen = strlen($x * $this->base);
                $this->setHText("".($x * $this->base)."", $fontSize, $leftOffset - ($slen * ($fontSize+4)), (0 + $this->imgTopSpc + $this->imgHeight - ($this->ystep * $x) - $fontSize*5), $color);
            }
		}
	}

	function addRulersXText($color, $fontSize, $bottomOffset) {
		for($x = 0; $x < $this->ylines; $x++) {
            if(($x * $this->xstep) <= $this->imgWidth) {
                $slen = strlen($x * $this->base);
                $this->setVText("".($x * $this->base)."", $fontSize, (0 + $this->imgLeftSpc + ($this->xstep * $x) - $fontSize*5), $this->imgHeight + $this->imgTopSpc + $bottomOffset + ($slen * ($fontSize+4)), $color);
            }
		}
	}

    function addRulersXData($color, $fontSize) {
        if(count($this->data) < 50) {
    		for($x = 1; $x <= count($this->data); $x++) {
                $slen = strlen($x);
                $this->setHText("".$x."", $fontSize, $this->imgLeftSpc + ($this->xstep * $x) - ($this->xstep / 2) - ($slen * 2), (0 + $this->imgTopSpc + $this->imgHeight + $fontSize), $color);
            }
        } else {
    		for($x = 1; $x <= count($this->data); $x++) {
                $slen = strlen($x);
                if($x == 1) $this->setHText("".$x."", $fontSize, $this->imgLeftSpc + ($this->xstep * $x) - ($this->xstep / 2) - ($slen * 2), (0 + $this->imgTopSpc + $this->imgHeight + $fontSize), $color);
                if(($x % 5) == 0) {
                    $this->setHText("".$x."", $fontSize, $this->imgLeftSpc + ($this->xstep * $x) - ($this->xstep / 2) - ($slen * 2), (0 + $this->imgTopSpc + $this->imgHeight + $fontSize), $color);
                } else {
                    if($x != 1) imagesetpixel($this->img, $this->imgLeftSpc + ($this->xstep * $x) - ($this->xstep / 2), (0 + $this->imgTopSpc + $this->imgHeight + $fontSize) + ($fontSize * 3), $color);
                }
            }
        }
    }


    /**
     * Adds the text to the X-Axis. Uses the values
     * which are passed in the $values
     *
     * @param $color
     * @param $fontSize
     * @param array $values The text that should be displayed
     *
     */
    function addRulersXDataValues($color, $fontSize, $values) {
        if(count($values) < 50) {
    		for($x = 1; $x <= count($values); $x++) {

    		    $text=$values[$x-1];
    		    $slen = strlen($text);
                $this->setHText("".$text."", $fontSize, $this->imgLeftSpc + ($this->xstep * $x) - ($this->xstep / 2) - ($slen * 2), (0 + $this->imgTopSpc + $this->imgHeight + $fontSize), $color);
            }
        } else {
    		for($x = 1; $x <= count($values); $x++) {

                $text=$values[$x-1];
                $slen = strlen($text);
                if($x == 1) $this->setHText("".$x."", $fontSize, $this->imgLeftSpc + ($this->xstep * $x) - ($this->xstep / 2) - ($slen * 2), (0 + $this->imgTopSpc + $this->imgHeight + $fontSize), $color);
                if(($x % 5) == 0) {
                    $this->setHText("".$x."", $fontSize, $this->imgLeftSpc + ($this->xstep * $x) - ($this->xstep / 2) - ($slen * 2), (0 + $this->imgTopSpc + $this->imgHeight + $fontSize), $color);
                } else {
                    if($x != 1) imagesetpixel($this->img, $this->imgLeftSpc + ($this->xstep * $x) - ($this->xstep / 2), (0 + $this->imgTopSpc + $this->imgHeight + $fontSize) + ($fontSize * 3), $color);
                }
            }
        }
    }

  /**
     * Adds the text to the Y-Axis. Uses the values
     * which are passed in the $values
     *
     * @param $color
     * @param $fontSize
     * @param array $values The text that should be displayed
     *
     */
    function addRulersYDataValues($color, $fontSize, $leftOffset,$values) {
        if(count($values) < 50) {
    		for($x = 1; $x <= count($values); $x++) {
                $text = $values[$x-1];
                $slen = strlen($text);
                $this->setHText("".$text."", $fontSize, $leftOffset - ($slen * ($fontSize+4)), (0 + $this->imgTopSpc + $this->imgHeight - ($this->ystep * $x) + ($this->ystep / 2) - $fontSize*3), $color);
            }
        } else {
    		for($x = 1; $x <= count($values); $x++) {

                $text = $values[$x-1];
                $slen = strlen($text);
                if($x == 1) $this->setHText("".$x."", $fontSize, $leftOffset - ($slen * ($fontSize+4)), (0 + $this->imgTopSpc + $this->imgHeight - ($this->ystep * $x) + ($this->ystep / 2) - $fontSize*3), $color);
                if(($x % 5) == 0) {
                    $this->setHText("".$text."", $fontSize, $leftOffset - ($slen * ($fontSize+4)), (0 + $this->imgTopSpc + $this->imgHeight - ($this->ystep * $x) + ($this->ystep / 2) - $fontSize*3), $color);
                } else {
                    if($x != 1) imagesetpixel($this->img, $leftOffset - $fontSize * 2, (0 + $this->imgTopSpc + $this->imgHeight - ($this->ystep * $x) + ($this->ystep / 2)), $color);
                }
            }
        }
    }


      function addRulersYData($color, $fontSize, $leftOffset) {
        if(count($this->data) < 50) {
    		for($x = 1; $x <= count($this->data); $x++) {
                $slen = strlen($x);
                $this->setHText("".$x."", $fontSize, $leftOffset - ($slen * ($fontSize+4)), (0 + $this->imgTopSpc + $this->imgHeight - ($this->ystep * $x) + ($this->ystep / 2) - $fontSize*3), $color);
            }
        } else {
    		for($x = 1; $x <= count($this->data); $x++) {
                $slen = strlen($x);
                if($x == 1) $this->setHText("".$x."", $fontSize, $leftOffset - ($slen * ($fontSize+4)), (0 + $this->imgTopSpc + $this->imgHeight - ($this->ystep * $x) + ($this->ystep / 2) - $fontSize*3), $color);
                if(($x % 5) == 0) {
                    $this->setHText("".$x."", $fontSize, $leftOffset - ($slen * ($fontSize+4)), (0 + $this->imgTopSpc + $this->imgHeight - ($this->ystep * $x) + ($this->ystep / 2) - $fontSize*3), $color);
                } else {
                    if($x != 1) imagesetpixel($this->img, $leftOffset - $fontSize * 2, (0 + $this->imgTopSpc + $this->imgHeight - ($this->ystep * $x) + ($this->ystep / 2)), $color);
                }
            }
        }
    }

    function addRulersXDataMonth($color, $fontSize, $langIdNo, $startMonth, $startYear) {
    	for($x = 1; $x <= count($this->data); $x++) {
            if($startMonth == 13) {
                $startMonth = 1;
                $startYear++;
            }
            $mon = getmonth($startMonth, $langIdNo);
            $mtmp = substr($mon, 0, 3);
            $slen = strlen($mtmp);
            $this->setVText("".$mtmp.".".substr($startYear, 2, 2), $fontSize, $this->imgLeftSpc + ($this->xstep * $x) - ($this->xstep / 2) - ($slen * 2), (0 + $this->imgTopSpc + $this->imgHeight + ($fontSize*4*($slen+2))), $color);
            $startMonth++;
        }
    }

    function addRulersYDataMonth($color, $fontSize, $leftOffset, $langIdNo, $startMonth, $startYear) {
		for($x = 1; $x <= count($this->data); $x++) {
            if($startMonth == 13) {
                $startMonth = 1;
                $startYear++;
            }
            $mon = getmonth($startMonth, $langIdNo);
            $mtmp = substr($mon, 0, 3);
            $slen = strlen($mtmp);
            $this->setHText("".$mtmp.".".substr($startYear, 2, 2), $fontSize, $leftOffset - (($slen+3) * $fontSize), (0 + $this->imgTopSpc + $this->imgHeight - ($this->ystep * $x) + ($this->ystep / 2) - $fontSize*3), $color);
            $startMonth++;
        }
    }

	function createOutput() {
		header('Content-Type: image/png');
		//imagepng($this->img,'ph_diagramm.png');
		imagepng($this->img);
		imagedestroy($this->img);
	}

/**
 * Function creates a pie diagram with Agenda
 *
 *  data set in the constructor has to be a
 *  2dimensional array (a[$i][2]) with the entries:
 *
 *  - entry1
 *   ~ Name (will be displayed in Agenda)
 *   ~ Hits (could be empty, not used her)
 *   ~ Percentage (needed to calculate pie pieces and to display values)
 *
 * - entry2
 * 	 ~ Name
 * 	 ~ ...
 *
 *
 */
function createPie(){

//if antialasiaing is possible, it will be used
if (function_exists("imageantialias"))
{
 imageantialias($this->img,true);
}

// allocate some colors
//$white    = imagecolorallocate($this->img, 0xFF, 0xFF, 0xFF);
//$darkgrey    = imagecolorallocate($this->img, 80, 80, 80);

$c11 = imagecolorallocate($this->img,250,212,184); //light brown orange
$c12 = imagecolorallocate($this->img,220,182,154);

$c21 = imagecolorallocate($this->img,228,157,104); //brown orange
$c22 = imagecolorallocate($this->img,198,127,74);

$c31 = imagecolorallocate($this->img,118,177,177); //green blue
$c32 = imagecolorallocate($this->img,88,147,147);

$c41 = imagecolorallocate($this->img,220,225,229); //light blue
$c42 = imagecolorallocate($this->img,190,195,195);

$c51 = imagecolorallocate($this->img,255,82,22); //red
$c52 = imagecolorallocate($this->img,221,52,0);

$c61 = imagecolorallocate($this->img,255,235,123);//yellow
$c62 = imagecolorallocate($this->img,225,205,93);

$c71 = imagecolorallocate($this->img,255,135,173); //green
$c72 = imagecolorallocate($this->img,235,105,143);

$c81 = imagecolorallocate($this->img,211,147,255); //magenta
$c82 = imagecolorallocate($this->img,181,135,255);

$c91 = imagecolorallocate($this->img,64,167,255); //blue
$c92 = imagecolorallocate($this->img,34,137,225);

$c101 = imagecolorallocate($this->img,147,234,149); //green
$c102 = imagecolorallocate($this->img,117,204,139);

$c111 = imagecolorallocate($this->img,93,93,93); //gray
$c112 = imagecolorallocate($this->img,63,63,63);

$c121 = imagecolorallocate($this->img,63,93,123); //dark blue
$c122 = imagecolorallocate($this->img,33,63,93);

$c131 = imagecolorallocate($this->img,195,225,255); //light blue
$c132 = imagecolorallocate($this->img,165,195,225);

$c141 = imagecolorallocate($this->img,225,255,195); //light green
$c142 = imagecolorallocate($this->img,195,225,165);

$c151 = imagecolorallocate($this->img,255,195,225); //light blue
$c152 = imagecolorallocate($this->img,225,168,195);

$c161 = imagecolorallocate($this->img,155,95,125); //light blue
$c162 = imagecolorallocate($this->img,125,68,95);

//aColorsLight = array($c11,$c21,$c31,$c41,$c51,$c61,$c71,$c101,$c81,$c91,$c101,$c111,$c121,$c131,$c141);

$aColorsLight=array();
$aColorsLight[]=$c11;

$aColorsLight[]=$c31;
$aColorsLight[]=$c21;

$aColorsLight[]=$c41;

$aColorsLight[]=$c71;
$aColorsLight[]=$c51;
$aColorsLight[]=$c61;
$aColorsLight[]=$c101;
$aColorsLight[]=$c81;

$aColorsLight[]=$c111;
$aColorsLight[]=$c91;

$aColorsLight[]=$c161;
$aColorsLight[]=$c121;
$aColorsLight[]=$c131;
$aColorsLight[]=$c141;
$aColorsLight[]=$c151;

$aColorsLight[]=$c71;
$aColorsLight[]=$c51;
$aColorsLight[]=$c61;
$aColorsLight[]=$c101;


//$aColorsDark =  array($c12,$c22,$c32,$c42,$c52,$c62,$c72,$c102,$c82,$c92,$c102,$c112,$c122,$c132,$c142);

$aColorsDark=array();
$aColorsDark[]=$c12;

$aColorsDark[]=$c32;
$aColorsDark[]=$c22;

$aColorsDark[]=$c42;

$aColorsDark[]=$c72;
$aColorsDark[]=$c52;
$aColorsDark[]=$c62;
$aColorsDark[]=$c102;
$aColorsDark[]=$c82;

$aColorsDark[]=$c112;
$aColorsDark[]=$c92;

$aColorsDark[]=$c162;
$aColorsDark[]=$c122;
$aColorsDark[]=$c132;
$aColorsDark[]=$c142;
$aColorsDark[]=$c152;

$aColorsDark[]=$c72;
$aColorsDark[]=$c52;
$aColorsDark[]=$c62;
$aColorsDark[]=$c102;



//Generate a degree offset
 $pieOffset=rand(150,180);

  //Create start point, adding Spacer
  $pieXStart=$this->imgLeftSpc+$this->imgWidth*0.33;
  $pieYStart=$this->imgTopSpc+$this->imgHeight*0.5-3;

  //substracting spacer, calculating width, height
  $pieXSize=($pieXStart-$this->imgLeftSpc )*1.8;
  $pieYSize=($pieYStart-$this->imgTopSpc )*1.52;

  //dynamic agenda padding to the pie
  $colorBoxXStart=($pieXStart + ($pieXSize/2)*1.5);
  $textXStart=$colorBoxXStart+20;

  //difference from top to agenda start
  $textYStart=($pieYStart-($pieYSize*0.5))*0.36;


// make the 3D effect
for ($i = $pieYStart+10; $i > ($pieYStart); $i--) {

  $pieSizeAlt=$pieOffset;

  //for every data in array
  for ($j=0;$j<count($this->data);$j++){

	//choose a color from the dark colorset to generate 3D look
    $color=$aColorsDark[$j];

    //calculates the size of the pie piece: 25% of 360 = 0,25 * 360, adding the old value
	//Use 99.9 to compensate rounding errors ;-)
    $pieSize=360*($this->data[$j][2]/99.9)+$pieSizeAlt;

	//--Draw the piece of pie
	imagefilledarc($this->img, $pieXStart, $i,  $pieXSize, $pieYSize, $pieSizeAlt, $pieSize, $color, IMG_ARC_PIE);

    //the next starting point of the forthcoming pie piece
    $pieSizeAlt=$pieSize;
  }

}


  //by setting a value greater 0, you can rotate the diagram
  $pieSizeAlt=$pieOffset;

  for ($j=0;$j<count($this->data);$j++){

    $color=$aColorsLight[$j];
    $colorDark = $aColorsDark[$j];

    //Use 99.9 to compensate rounding errors ;-)
    $pieSize=(360*($this->data[$j][2]/99.9)+$pieSizeAlt);


   imagefilledarc($this->img, $pieXStart, $pieYStart, $pieXSize, $pieYSize, $pieSizeAlt, $pieSize, $color, IMG_ARC_PIE);

   //lines with dark colors
   imagefilledarc($this->img, $pieXStart, $pieYStart, $pieXSize, $pieYSize, $pieSizeAlt, $pieSize, $colorDark, IMG_ARC_EDGED+IMG_ARC_NOFILL);


	//--calculate position to place text at the border of the Pie

	//calculate the half of every angle, to place the text in the middle of each pie piece
	$pieSizeHalf=$pieSize-(($pieSize-$pieSizeAlt)/2);

	//the x,y coordinates of the point on the pie ellipse
	//addition of '25' to get a bigger ellipse, because there should be
	//space between text and pie
	$xo=(($pieXSize/2+30)*cos(deg2rad($pieSizeHalf)));
	$yo=(($pieYSize/2+25)*sin(deg2rad($pieSizeHalf)));


	// add the center of the pie diagram to the coordinates and multiply
	// adjustments (-20,-5) to keep the text out of the diagram
	$xo1=$xo + $pieXStart-25;
	$yo1=$yo + $pieYStart-5;


   //To small values don't have enough space in the diagram an would become unredable
   //1% and more will be shown
   if($this->data[$j][2]>=1){
      imagestring($this->img,2,$xo1,$yo1," (".$this->data[$j][2]."%)",$this->aCols[$this->dataFontColor]);
   }

   	//--end of calculating


   //Create Agenda right to the pie
   //for each entry 16pix space
   $agendaSpacer=$textYStart+16*$j;

 //show only that entries, that fit into the given image height
 if($agendaSpacer < $this->imgHeight*2)
   {
   imagefilledrectangle($this->img,$colorBoxXStart+1,$agendaSpacer+2,$colorBoxXStart+11,$agendaSpacer+12,$colorDark);
   imagefilledrectangle($this->img,$colorBoxXStart,$agendaSpacer+1,$colorBoxXStart+10,$agendaSpacer+11,$color);

    //agenda text
   $text=$this->data[$j][0]." (".$this->data[$j][2]."%)";
   imagestring($this->img,3,$textXStart,$agendaSpacer,$text,$this->aCols[$this->dataFontColor]);
   }

   $pieSizeAlt=$pieSize;

  }


// flush image
header('Content-type: image/png');
imagepng($this->img);
imagedestroy($this->img);

	}

}

?>
