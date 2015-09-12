<?php

	// Fehlerberichterstattung ein
	error_reporting(E_ERROR);

	// Parameter zum Aufbau der Verbindung zur Datenbank
	define('MYSQL_HOST',      'localhost');
	define('MYSQL_BENUTZER',  'web97');
	define('MYSQL_KENNWORT',  'ACE0fM0v');
	define('MYSQL_DATENBANK', 'usr_web97_4');

	// Verbindung zur Datenbank aufbauen
	$db_link = mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT);

	// prüfen ob die Verbindung zur Datenbank erfolgreich hergestellt werden konnte
	if (is_resource($db_link)) {
		// alles OK
		//echo 'Verbindung erfolgreich: ';
		//echo $db_link;
	} else {
		// hier sollte dann später dem Programmierer eine
		// E-Mail mit dem Problem zukommen gelassen werden
		die('keine Verbindung möglich: ' . mysql_error());
	}

	// Verbindung zur Datenbank beenden
	mysql_close($db_link);

?>