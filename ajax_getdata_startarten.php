<?php

	// Array für die Rückgabe erzeugen
	$return = array();

	// Rückgabe-Variable definieren
	$html = '';

	// Modul für DB-Zugriff einbinden
	require_once('konfiguration.php');

	// Verbindung zur Datenbank herstellen
	// am System mit Host, Benutzernamen und Password anmelden
	@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
	@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
	@mysql_query("SET NAMES 'utf8'");

	// SQL-Befehl zurechtfuddeln,
	// hinterlegte Startarten für das ausgewählte Flugzeug ermitteln
	$sql = sprintf('
		SELECT
			`startart`
		FROM
			`flugzeuge`
		WHERE
			`kennzeichen` = "%s"
		LIMIT 1
	',
		$_POST['kennzeichen']
	);
	
	// zuvor definierte SQL-Anweisung ausführen
	// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
	$db_erg = mysql_query($sql);
	
	// es sind Datensätze vorhanden
	if ($zeile = mysql_fetch_object($db_erg)) {
		// die Startarten werden ermittelt
		$startarten = str_replace(',', '","', $zeile->startart);
		$startarten = sprintf('"%s"', $startarten);
		
		// SQL-Befehl zurechtfuddeln,
		// prüfen um welches Jahr es sich handelt um entsprechend den SQL-Befehl anzupassen
		if (intval($_POST['jahr']) < 2014) {
			// SQL-Befehl zurechtfuddeln,
			// Startarten im Klartext ermitteln
			$sql = sprintf('
				SELECT
					`kbez` AS `id`,
					`bezeichnung` AS `bezeichnung`
				FROM
					`startarten`
				WHERE
					`startart` IN (%s) AND
					`projekt` = "flybook"
				ORDER BY
					`kbez` ASC
			',
				$startarten
			);
		} else {
			// SQL-Befehl zurechtfuddeln,
			// Startarten im Klartext ermitteln
			$sql = sprintf('
				SELECT
					`id` AS `id`,
					`bezeichnung` AS `bezeichnung`
				FROM
					`startarten`
				WHERE
					`startart` IN (%s) AND
					`projekt` = "ameavia"
				ORDER BY
					`id` ASC
			',
				$startarten
			);
		}
		
		// zuvor definierte SQL-Anweisung ausführen
		// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
		$db_erg = mysql_query($sql);
		
		// es sind Datensätze vorhanden
		while ($zeile = mysql_fetch_object($db_erg)) {
			// Daten übernehmen wie hinterlegt
			// und Startartenliste zusammenstellen
			$html .= sprintf(
				'<option value="%s" style="width: 300px;">%s</option>\n',
				$zeile->id, $zeile->bezeichnung
			);
		}

		// Verbindung zur Datenbank schließen
		mysql_free_result($db_erg);
	} else {
		// SQL-Befehl zurechtfuddeln,
		// prüfen um welches Jahr es sich handelt um entsprechend den SQL-Befehl anzupassen
		if (intval($_POST['jahr']) < 2014) {
			// SQL-Befehl zurechtfuddeln,
			// Startarten im Klartext ermitteln
			$sql = sprintf('
				SELECT
					`kbez` AS `id`,
					`bezeichnung` AS `bezeichnung`
				FROM
					`startarten`
				WHERE
					`startart` IN ("W","F","E") AND
					`projekt` = "flybook"
				ORDER BY
					`kbez` ASC
			');
		} else {
			// SQL-Befehl zurechtfuddeln,
			// Startarten im Klartext ermitteln
			$sql = sprintf('
				SELECT
					`id` AS `id`,
					`bezeichnung` AS `bezeichnung`
				FROM
					`startarten`
				WHERE
					`startart` IN ("W","F","E") AND
					`projekt` = "ameavia"
				ORDER BY
					`id` ASC
			');
		}
		
		// zuvor definierte SQL-Anweisung ausführen
		// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
		$db_erg = mysql_query($sql);
		
		// es sind Datensätze vorhanden
		while ($zeile = mysql_fetch_object($db_erg)) {
			// Daten übernehmen wie hinterlegt
			// und Startartenliste zusammenstellen
			$html .= sprintf(
				'<option value="%s" style="width: 300px;">%s</option>\n',
				$zeile->id, $zeile->bezeichnung
			);
		}

		// Verbindung zur Datenbank schließen
		mysql_free_result($db_erg);
	}
	
	// Rückgabe der Listenenträge
	$return['html_options'] = $html;

	// die Daten werden im JSON-Format zurückgegeben
	echo json_encode($return);
	// Skript beenden
	die();

?>