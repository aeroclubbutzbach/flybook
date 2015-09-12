<?php

	// allgemeine Funktionen einbinden
	include_once('./functions.php');

	// Liste der Mitgliederbestände holen
	$list = getMitgliederbestand();

	// Name der zu exportierenden Datei festlegen
	$filename = 'ebe-lsbh.xls';

	// Datei zum schreiben anlegen und öffnen
	$fp = fopen(sprintf('./export/%s', $filename), 'w');
	
	// Überschriften der Excel-Tabelle festlegen
	fprintf($fp, "Name\tVorname\tGeburtsjahr\tGeschlecht\tVerbandsbezeichnung(en)\n");

	// Mitgliederbestand in die XLS schreiben
	foreach ($list as $fields) {
		fprintf($fp, "%s\t%s\t%s\t%s\tLuftsport\n",
			$fields['nachname'],
			$fields['vorname'],
			$fields['geburtsjahr'],
			$fields['geschlecht']
		);
	}

	// Datei nach Beendigung schließen
	fclose($fp);

	// Datei zum lesen anlegen und öffnen
	$fp = fopen(sprintf('./export/%s', $filename), 'r');

	if ($fp) {
		header('Content-Type: application/vnd.ms-excel; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $filename);
		header('Pragma: no-cache');
		header('Expires: 0');
	}
	
	$check = @fpassthru($fp); 

?>