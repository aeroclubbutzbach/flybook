<?php

	// Array für die Rückgabe erzeugen
	$return = array();
	
	// allgemeine Funktionen einbinden
	include_once('./functions.php');

	// Modul für DB-Zugriff einbinden
	require_once('konfiguration.php');

	// Verbindung zur Datenbank herstellen
	// am System mit Host, Benutzernamen und Password anmelden
	@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
	@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');

	// SQL-Befehl zurechtfuddeln,
	// eine neue Wartung wird angelegt
	$sql = sprintf('
		INSERT INTO
			`wartungsplan` (
				`kennzeichen`,
				`wartung`,
				`datum`,
				`flugstunden`,
				`landungen`,
				`technik`,
				`bemerkungen`
			) VALUES (
				%s, %s, %s, %s, %s, %s, %s
			)
	',
		getDbValue($_POST['kennzeichen'], T_STR),
		getDbValue($_POST['wartung'],     T_NUMERIC),
		getDbValue($_POST['datum'],       T_DATE),
		getDbValue($_POST['flugstunden'], T_NUMERIC),
		getDbValue($_POST['landungen'],   T_NUMERIC),
		getDbValue($_POST['technik'],     T_NUMERIC),
		utf8_decode(getDbValue($_POST['bemerkungen'], T_STR))
	);
	
	// zuvor definierte SQL-Anweisung ausführen
	if (mysql_query($sql)) {
		// die ID des neuen Datensatzes ermitteln
		$return['id'] = mysql_insert_id();
	} else {
		// kein neuer Datensatz eingefügt
		$return['id'] = 0;
	}

	// die Daten werden im JSON-Format zurückgegeben
	echo json_encode($return);
	// Skript beenden
	die();

?>