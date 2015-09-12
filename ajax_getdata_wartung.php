<?php

	// Array für die Rückgabe erzeugen
	$return = array();

	// Modul für DB-Zugriff einbinden
	require_once('konfiguration.php');

	// Verbindung zur Datenbank herstellen
	// am System mit Host, Benutzernamen und Password anmelden
	@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
	@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');

	// SQL-Befehl zurechtfuddeln,
	// eine bestehende Wartung ermitteln anhand der ID
	$sql = sprintf('
		SELECT
			*
		FROM
			`wartungsplan`
		WHERE
			`id` = %d
		LIMIT 1
	',
		$_POST['id']
	);
	
	// zuvor definierte SQL-Anweisung ausführen
	// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
	$db_erg = mysql_query($sql);
	
	// es sind Datensätze vorhanden
	if ($zeile = mysql_fetch_object($db_erg)) {
		// Abfrage war erfolgreich
		$return['result']  = true;
		$return['wartung'] = $zeile->wartung;
		$return['technik'] = $zeile->technik;
	} else {
		// Abfrage war nicht erfolgreich
		$return['result'] = false;
	}

	// die Daten werden im JSON-Format zurückgegeben
	echo json_encode($return);
	// Skript beenden
	die();

?>