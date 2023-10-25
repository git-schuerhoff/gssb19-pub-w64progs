<?php
session_start();
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
$_SESSION['transferlog'] = __DIR__ . '/logs/transfer.log';
//TS 08.04.2016: Settings löschen
if(isset($_SESSION['sb_settings'])) {
	unset($_SESSION['sb_settings']);
}
if(isset($_SESSION['template'])) {
	unset($_SESSION['template']);
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title>GS Software - Dynamic GS ShopBuilder Extensions</title>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
	<meta content="de" http-equiv="Language">
	<meta name="author" content="GS Software Solutions GmbH" >
	<link rel="stylesheet" type="text/css" href="../css/link.css">
	<style rel="stylesheet" type="text/css">
		table { max-width:550px; width:550px; margin:4px auto 4px auto; border: 1px solid black; border-collapse: collapse;}
		td { border: 1px solid #cccccc; vertical-align: top; }
		.hfinale {
			color: #FFF;
			background-color: #006FB4;
			margin: 0px;
			padding: 4px;
			font-size: 20px;
			font-weight: bold;
			margin-bottom: 6px;
		}
		.progress_o{
			min-width: 250px;
			width: 250px;
			height: 26px;
			min-height: 26px;
			border: 1px solid black;
			padding: 0;
		}
	</style>
	<link rel="copyright" href="http://www.gs-software.de" title="(c) 2014 GS Software Solutions GmbH">
</head>
<?php
$slc = '';
$cnt = '';
$imp = 0;
$bmi = 0;
$del = 0;
if(isset($_GET['slc'])) {
	$slc = $_GET['slc'];
}
if(isset($_GET['cnt'])) {
	$cnt = $_GET['cnt'];
}
if(isset($_GET['imp'])) {
	$imp = $_GET['imp'];
}
if(isset($_GET['bmi'])) {
	$bmi = $_GET['bmi'];
}
if(isset($_GET['del'])) {
	$del = $_GET['del'];
}

?>
<body bgcolor="#FFFFF" onLoad="do_import('<?php echo $slc; ?>','<?php echo $cnt; ?>',<?php echo $imp; ?>,<?php echo $bmi; ?>,<?php echo $del; ?>)">
	<div style="min-width: 100%;">
		<table>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tbody>
				<tr>
					<td colspan="2">
						<div align=center>
						<p><img src="../image/logo_small.png" width="64" height="64" border="0" alt="GS Sofware Dynamic GS ShopBuilder Extensions"><h2>dynamic GS ShopBuilder Extensions</h2></p>
						<p><br />
						</p>
						Willkommen. Ihr Shop wird ver&ouml;ffentlicht!!!<br />
						Datenexport SB 19.0.0.0 Version 1.0.0
					</td>
				</tr>
				<tr>
					<td>Aufgabe</td>
					<td><div id='aufgabe'></div></td>
				</tr>
				<tr>
					<td>Ergebnis</td>
					<td><div id='ergebnis'></div></td>
				</tr>
				<tr>
					<td>Fortschritt Aufgabe</td>
					<td><div class='progress_o'><div id='progrpart' style='width:0px; height:25px; min-height:25px; background-color:#ffff00;'></div></div></td>
				</tr>
				<tr>
					<td>Gesamtfortschritt</td>
					<td><div class='progress_o'><div id='progrtotal' style='width:0px; height:25px; min-height:25px; background-color:#00ee00;'></div></div></td>
				</tr>
			</tbody>
		</table>
		<div id='finale' style='max-width:100%; text-align: center;'>
		</div>
	<noscript>
	<h3>Bitte aktivieren Sie JavaScript in Ihrem Browser! <br />Ohne JavaScript k&ouml;nnen Sie dieses System nicht nutzen!</h1>
	</noscript>

  </body>
  <script language='JavaScript' type='text/javascript' src='./script/import.js?stamp=<?php echo time(); ?>'>
	</script>
 </html>
