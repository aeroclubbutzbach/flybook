<?php

	// Modul für DB-Zugriff einbinden
	require_once('konfiguration.php');

	// Verbindung zur Datenbank herstellen
	// am System mit Host, Benutzernamen und Password anmelden
	@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
	@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
	
	// SQL-Befehl zurechtfuddeln,
	// aktuell ausgewählte Wartung entfernen, durch Statusänderung
	$sql = sprintf('UPDATE `wartungsplan` SET `in_abrechn` = "N" WHERE `id` = %d', $_POST['id']);
	
	// zuvor definierte SQL-Anweisung ausführen
	mysql_query($sql);
	
	// Skript beenden
	die();

?>