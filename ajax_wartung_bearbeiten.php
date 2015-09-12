<?php
	
	// allgemeine Funktionen einbinden
	include_once('./functions.php');

	// Modul für DB-Zugriff einbinden
	require_once('konfiguration.php');

	// Verbindung zur Datenbank herstellen
	// am System mit Host, Benutzernamen und Password anmelden
	@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
	@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');

	// SQL-Befehl zurechtfuddeln,
	// eine bestehende Wartung wird aktualisiert anhand der übergebenen ID
	$sql = sprintf('
		UPDATE
			`wartungsplan`
		SET
			`wartung` = %s,
			`datum` = %s,
			`flugstunden` = %s,
			`landungen` = %s,
			`technik` = %s,
			`bemerkungen` = %s
		WHERE
			`id` = %d
	',
		getDbValue($_POST['wartung'],     T_NUMERIC),
		getDbValue($_POST['datum'],       T_DATE),
		getDbValue($_POST['flugstunden'], T_NUMERIC),
		getDbValue($_POST['landungen'],   T_NUMERIC),
		getDbValue($_POST['technik'],     T_NUMERIC),
		utf8_decode(getDbValue($_POST['bemerkungen'], T_STR)),
		$_POST['id']
	);
	
	// zuvor definierte SQL-Anweisung ausführen
	mysql_query($sql);

	// Skript beenden
	die();

?>