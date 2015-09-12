<?php

	// Modul für DB-Zugriff einbinden
	require_once('konfiguration.php');

	// Verbindung zur Datenbank herstellen
	// am System mit Host, Benutzernamen und Password anmelden
	@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
	@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
	
	// SQL-Befehl zurechtfuddeln,
	// prüfen ob die aktuell übergebene Mitgliedsnummer bereits vorhanden ist
	$sql = sprintf('SELECT `ameavia` FROM `mitglieder` WHERE `ameavia` = "%s" LIMIT 1', $_POST['ameavia_ref']);
	
	// zuvor definierte SQL-Anweisung ausführen
	$query = mysql_query($sql);
	
	// Rückgabe ob Mitgliedsnummer vorhanden oder nicht
	echo mysql_num_rows($query);

	// Skript beenden
	die();

?>