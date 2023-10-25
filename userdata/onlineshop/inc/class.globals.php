<?php

$myshoppath = (empty($_SERVER['HTTPS'])) ? 'http' : 'https';
$myshoppath .= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; 
$myshoppath = dirname($myshoppath);
if (isset($_SESSION['shoppath']))
{
	if (strcmp($_SESSION['shoppath'], $myshoppath) != 0)
	{
		unset($_SESSION['basket']);
	}
}
$_SESSION['shoppath'] = $myshoppath;

class Globals
{
	var $w;
	var $h;
	var $useAmpel='';

	function Globals()
	{
		$this->w=185;
		$this->h=135;
	}

	function imgSize($img, $w, $h)
	{
		$max_width = $w;
		$max_height = $h;
		$size=@getimagesize($img);
		$x=$size[0];
		$y=$size[1];
		if($x>$y)
		{
			if($x>$max_width)
			{
				$y=ceil($y/($x/$max_width));
				$x=$max_width;
			}
		}
		else
		{
			if($y>$max_height)
			{
				$x=ceil($x/($y/$max_height));
				$y=$max_height;
			}
		}
		$w = $x;
		$h = $y;
		return 'height="'.$y.'px" width="'.$x.'px"';
	}
}
?>