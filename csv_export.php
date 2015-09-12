<?php

	// allgemeine Funktionen einbinden
	include_once('./functions.php');

	// Liste der eMail-Adressen holen
	$list = getEmailadressen();

	// Name der zu exportierenden Datei festlegen
	$filename = 'email-adr.txt';

	// Datei zum schreiben anlegen und öffnen
	$fp = fopen(sprintf('./export/%s', $filename), 'w');

	// eMail-Adressen in die CSV schreiben
	foreach ($list as $fields) {
		fprintf($fp, "%s,\n", $fields['email']);
	}

	// Datei nach Beendigung schließen
	fclose($fp);

	// Datei zum lesen anlegen und öffnen
	$fp = fopen(sprintf('./export/%s', $filename), 'r');

	if ($fp) {
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename=' . $filename);
		header('Content-Description: csv File');
		header('Pragma: no-cache');
		header('Expires: 0');
	}
	
	$check = @fpassthru($fp); 

?>