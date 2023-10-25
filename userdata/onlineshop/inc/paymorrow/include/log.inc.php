<?php
$PERTH_LOG_DIR = dirname("../../../dynsb/logs/.");


function log_output($filename, $text) {
	global $PERTH_LOG_DIR;

	$fout = fopen($PERTH_LOG_DIR."/".$filename, "a+");
	fwrite($fout, $text);
	fclose($fout);
}

?>