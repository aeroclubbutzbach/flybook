<?php

	// Modul für DB-Zugriff einbinden
	require_once('konfiguration.php');

	// Verbindung zur Datenbank herstellen
	// am System mit Host, Benutzernamen und Password anmelden
	@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
	@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
	
	// SQL-Befehl zurechtfuddeln,
	// prüfen ob das aktuell übergebene Flugzeugkennzeichen bereits vorhanden ist
	$sql = sprintf('SELECT `kennzeichen` FROM `flugzeuge` WHERE `kennzeichen` = "%s" LIMIT 1', $_POST['kennzeichen']);
	
	// zuvor definierte SQL-Anweisung ausführen
	$query = mysql_query($sql);
	
	// Rückgabe ob Flugzeugkennzeichen vorhanden oder nicht
	echo mysql_num_rows($query);

	// Skript beenden
	die();

?>