<?php
	include('mysql.inc.php');
	
	$link = new mysqli($dbServer, $dbUser, $dbPass, $dbName);
	if ($link->connect_errno) {
		die('1');
	}

	
	/*A TS 20.08.2015: Zeichens�tze des MySQL pr�fen und zur�ckgeben*/
	$linkCharSet = '';
	$linkCharSet = $link->character_set_name();
	echo "Charset of connection: " . $linkCharSet . "<br />";
	$sql = "SHOW VARIABLES LIKE 'character_set%'";
	$res = $link->query($sql);
	if($res) {
		while($r = $res->fetch_object()) {
			print_r($r);
			echo "<br>---------------<br>";
		}
		$res->close();
	} else {
		die($link->error);
	}
	
	$sql = "SHOW VARIABLES LIKE 'collation%'";
	$res = $link->query($sql);
	if($res) {
		while($r = $res->fetch_object()) {
			print_r($r);
			echo "<br>---------------<br>";
		}
		$res->close();
	} else {
		die($link->error);
	}
	/*E TS 20.08.2015: Zeichens�tze des MySQL pr�fen und zur�ckgeben*/
	/*A TS 20.08.2015: Zeichens�tze von PHP pr�fen und zur�ckgeben (sp�ter in test.php einf�gen)*/
	echo "PHP internal encoding: " . mb_internal_encoding() . "<br>";
	$aInputs = mb_http_input ("I");
	echo "PHP-Input-Charsets:<pre>";
	print_r($aInputs);
	echo "</pre><br>";
	echo "PHP-Output-Charsets: " . mb_http_output() . "<br>";
	/*E TS 20.08.2015: Zeichens�tze von PHP pr�fen und zur�ckgeben (sp�ter in test.php einf�gen)*/
	/*A TS 20.08.2015: Zeichens�tze des Apache pr�fen und zur�ckgeben (sp�ter in test.php einf�gen)*/
	
	/*E TS 20.08.2015: Zeichens�tze des Apache pr�fen und zur�ckgeben (sp�ter in test.php einf�gen)*/
?>