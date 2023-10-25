<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title>GS Software - Dynamic GS ShopBuilder Extensions</title>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
	<meta content="de" http-equiv="Language">
	<meta name="author" content="GS Software Solutions GmbH" >
	<link rel="stylesheet" type="text/css" href="../css/link.css">
	<link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
</head>	
<body bgcolor="#FFFFF">
	<div align=center>
		<table width=500 >
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tbody>
				<tr>
					<td>
						<div align=center>
						<p><img src="../image/logo_small.png" width="64" height="64" border="0" alt="GS Sofware Dynamic GS ShopBuilder Extensions"><h2>dynamic GS ShopBuilder Extensions</h2></p>
						<p><br />
						</p>
						<?php
						echo $_GET['msg'];
						?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	</body>
 </html>
