<?php

	// Modul für DB-Zugriff einbinden
	require_once('konfiguration.php');

	// Verbindung zur Datenbank herstellen
	// am System mit Host, Benutzernamen und Password anmelden
	@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
	@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
	
	// aktuelles Jahr aus übergebenen Parameter ermitteln
	$jahr = intval($_GET['jahr']);

	// prüfen um welches Jahr es sich handelt um entsprechend den SQL-Befehl anzupassen
	if ($jahr < 2014) {
		// SQL-Befehl zurechtfuddeln,
		// aktuell ausgewähltes Flugzeug entfernen, durch Statusänderung
		$sql = sprintf('UPDATE `hauptflugbuch` SET `geloescht` = "J" WHERE `id` = %d', $_GET['id']);
	} else {
		// SQL-Befehl zurechtfuddeln,
		// aktuell ausgewähltes Flugzeug entfernen, durch Statusänderung
		$sql = sprintf('UPDATE `flugbuch` SET `geloescht` = "J" WHERE `id` = %d', $_GET['id']);
	}
	
	// zuvor definierte SQL-Anweisung ausführen
	mysql_query($sql);
	
	// Skript beenden
	die();

?>