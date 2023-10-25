<?php

/*
 file: checkversion.inc.php
*/

$miniPhpVersion = '5.3.0';
$curPhpVersion = phpversion();

$agd = gd_info();
$res = version_compare($curPhpVersion, $miniPhpVersion);

if($res < 0) die("<B>Notice:</B><p>To run GSdynSB, PHP version ".$miniPhpVersion." or greater is required (with GD2.0 library).<p>Your PHP version is: ".$curPhpVersion.".<br />Your GD version is: ".$agd['GD Version'].".");

?>
