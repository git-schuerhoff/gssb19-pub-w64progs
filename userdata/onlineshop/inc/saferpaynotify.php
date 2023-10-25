<?php

// **************************************************
// *
// * PHP-FILE Beispiel zum Test von Saferpay mit https Interface
// *
// * Diese Seite wird nach erfolgreicher Zahlungsautorisierung direkt vom Saferpay-Server
// * (und damit ohne Einflussnahme des Kunden/Kundenbrowsers),
// * parametrisiert durch das Saferpay-Attribut NOTIFYURL, aufgerufen,
// * wobei DATA und SIGNATURE per POST uebermittelt werden.
// *
//
// Der Haendler(-server) kann hiermit direkt ueber eine erfolgreiche Zahlungsautorisierung informiert werden. 
// In dem NOTIFYURL-Skript soll eine Ueberpruefung und Zuweisung auf eine Zahlung gemacht werden.
//
// Dieses Demo-Skript speichert nur die erhaltenen Daten zwecks Anzeige ab.
// Ueberpruefung und Zuweisung sind in einen WebShop o.ae. selbst zu implementieren.
//
// Es ist vorher sicherzustellen, dass dieses Skript Schreibberechtigung im Ordner hat.
//
// **************************************************


	// Pruefe ob POST-Daten von Saferpay vorhanden sind
	if(!isset($_POST["DATA"]) or !isset($_POST["SIGNATURE"]))
		die ("Error:P");
	
	// Sichere Eingangsdaten gegen zu lange Daten
	if((strlen($_POST["DATA"])>8191) or (strlen($_POST["SIGNATURE"])>512))
		die ("Error:L");
	
	// Erstelle Speicherungs-XML und versuche XML aus DATA zu laden
	$data = new DOMDocument('1.0', 'utf-8');
	try {
		$data->loadXML($_POST["DATA"]);
	}
	catch (Exception $ex){
		die ("Error:X");
	}
	
	// Ueberpruefe auf zwingend vorhandenes Attribut : ID
	if (!$data->documentElement->hasAttribute("ID"))
		die ("Error:A");
	
	// Erstelle mit der ID einen einmaligen Filenamen
	$spid = $data->documentElement->getAttribute("ID");
	$filename = "SPID_" . $spid . ".xml";
	
	// Erstelle das zu speichernde XML
	$xml = new DOMDocument('1.0', 'utf-8');
	$node = $xml->appendChild($xml->createElement("NOTIFYULR"));
	
	// Falls Zusatzparameter an NOTIFYURL angehaengt wurde auch diese speichern
	if ($_SERVER["QUERY_STRING"] != "")
		$node->appendChild($xml->createElement("Query", htmlentities($_SERVER["QUERY_STRING"])));
	
	$nodedata = $node->appendChild($xml->createElement("DATA"));
	$nodedata->appendChild($xml->importNode($data->documentElement, true));
	$node->appendChild($xml->createElement("SIGNATURE", $_POST["SIGNATURE"]));
	
	$xml->save($filename);

	// NOTIFYSCRIPT beendet
	echo "OK";
		
?>
