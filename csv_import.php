<!-- BEGINN: SKRIPT -->
<?php

	/*
	 * getErrorMessage()
	 *
	 * eine Fehlermeldung wird dem Anwender zurückgegeben,
	 * falls er versucht keine CSV-konforme Datei auszulesen
	 *
	 * @return string $html
	 */
	if (!function_exists('getErrorMessage')) {
		function getErrorMessage()
		{
			// wird eine falsche Datei ausgewählt, welche keine CSV-Datei ist,
			// kommt eine entsprechende Fehlermeldung zum Vorschein!
			$html  = '<div class="errorline">';
			$html .= '<h3>Ein Fehler ist aufgetreten!</h3>';
			$html .= 'Die von Dir ausgew&auml;hlte Datei ist keine g&uuml;ltige CSV-Datei.<br />';
			$html .= 'Bitte noch einmal versuchen, und diesmal die richtige Datei ausw&auml;hlen!';
			$html .= '</div>';
			
			// Meldung zurückgeben
			return $html;
		}
	}
	
	/*
	 * getFluegeImportTabelle()
	 *
	 * zeigt die Tabelle an, welche Flüge importiert wurden
	 *
	 * @params array  $datensaetze
	 * @return string $html
	 */
	if (!function_exists('getFluegeImportTabelle')) {
		function getFluegeImportTabelle(array $datensaetze, $z)
		{
			// Meldung an den Anwender, wenn die Daten erfolgreich importiert wurden,
			// anschließend anzeigen die Anzahl der importierten Datensätze
			$html  = '<h1>Daten wurden erfolgreich importiert!<br /><small>(insg. ' . $z . ' Datens&auml;tze)</small></h1>';

			// Tabellenkopf für die Ausgabe vorbereiten
			$html .= '<table cellpadding="2" cellspacing="0" class="fluege_import">';
			
			// die Tabellenüberschriften festlegen
			$html .= '<tr bgcolor="#999999" style="color: #ffffff;">';
			$html .= '<th align="left" style="padding-left: 5px;">Datum</th>';
			$html .= '<th align="left" style="padding-left: 5px;">Startzeit</th>';
			$html .= '<th align="left" style="padding-left: 5px;">Landezeit</th>';
			$html .= '<th align="left" style="padding-left: 5px;">Flugzeit</th>';
			$html .= '<th align="left" style="padding-left: 5px;">Luftfahrzeug</th>';
			$html .= '<th align="left" style="padding-left: 5px;">Besatzung1</th>';
			$html .= '<th align="left" style="padding-left: 5px;">Besatzung2</th>';
			$html .= '<th align="left" style="padding-left: 5px;">Startort</th>';
			$html .= '<th align="left" style="padding-left: 5px;">Landeort</th>';
			$html .= '<th align="left" style="padding-left: 5px;">Preiskategorie</th>';
			$html .= '<th align="left" style="padding-left: 5px;">Startart</th>';
			$html .= '<th align="left" style="padding-left: 5px;">Flugart</th>';
			$html .= '<th align="left" style="padding-left: 5px;">Anteil_1</th>';
			$html .= '<th align="left" style="padding-left: 5px;">AnteilSumme_1</th>';
			$html .= '<th align="left" style="padding-left: 5px;">Anteil_2</th>';
			$html .= '<th align="left" style="padding-left: 5px;">AnteilSumme_2</th>';
			$html .= '</tr>';

			// Datensatz für Datensatz auslesen,
			// und als Tabellenzeile anhängen
			for ($i=0; $i<count($datensaetze); $i++) {
				// prüfen ob die Zeilenzahl gerade oder ungerade ist
				$bgColor = ($i % 2) ? '#eeeeee' : '#cccccc';
			
				$html .= '<tr bgcolor="' . $bgColor . '">';
				$html .= '<td style="border-right: 1px dotted #c4c4c4;">' . $datensaetze[$i]['datum']          . '&nbsp;</td>';
				$html .= '<td style="border-right: 1px dotted #c4c4c4;">' . $datensaetze[$i]['startzeit']      . '&nbsp;</td>';
				$html .= '<td style="border-right: 1px dotted #c4c4c4;">' . $datensaetze[$i]['landezeit']      . '&nbsp;</td>';
				$html .= '<td style="border-right: 1px dotted #c4c4c4;">' . $datensaetze[$i]['flugzeit']       . '&nbsp;</td>';
				$html .= '<td style="border-right: 1px dotted #c4c4c4;">' . $datensaetze[$i]['luftfahrzeug']   . '&nbsp;</td>';
				$html .= '<td style="border-right: 1px dotted #c4c4c4;">' . $datensaetze[$i]['besatzung1']     . '&nbsp;</td>';
				$html .= '<td style="border-right: 1px dotted #c4c4c4;">' . $datensaetze[$i]['besatzung2']     . '&nbsp;</td>';
				$html .= '<td style="border-right: 1px dotted #c4c4c4;">' . $datensaetze[$i]['startort']       . '&nbsp;</td>';
				$html .= '<td style="border-right: 1px dotted #c4c4c4;">' . $datensaetze[$i]['landeort']       . '&nbsp;</td>';
				$html .= '<td style="border-right: 1px dotted #c4c4c4;">' . $datensaetze[$i]['preiskategorie'] . '&nbsp;</td>';
				$html .= '<td style="border-right: 1px dotted #c4c4c4;">' . $datensaetze[$i]['startart']       . '&nbsp;</td>';
				$html .= '<td style="border-right: 1px dotted #c4c4c4;">' . $datensaetze[$i]['flugart']        . '&nbsp;</td>';
				$html .= '<td style="border-right: 1px dotted #c4c4c4;">' . $datensaetze[$i]['anteil_1']       . '&nbsp;</td>';
				$html .= '<td style="border-right: 1px dotted #c4c4c4;">' . $datensaetze[$i]['anteilsumme_1']  . '&nbsp;</td>';
				$html .= '<td style="border-right: 1px dotted #c4c4c4;">' . $datensaetze[$i]['anteil_2']       . '&nbsp;</td>';
				$html .= '<td>' . $datensaetze[$i]['anteilsumme_2']  . '&nbsp;</td>';
				$html .= '</tr>';
			}

			// Tabellenende festlegen
			$html .= '<table><br /><br />';
			
			// Tabellenansicht zurückgeben
			return $html;
		}
	}
	
	/*
	 * getKopfzeile()
	 *
	 * ließt die erste Zeile (Kopfzeile) der CSV-Datei aus
	 * und gibt jene als SQL-Teilbefehl zurück
	 *
	 * @params array  $data
	 * @return string $return
	 */
	if (!function_exists('getKopfzeile')) {
		function getKopfzeile(array $data)
		{
			// Rückgabe-Variable definieren
			$return = '';
		
			// die erste Zeile der CSV-Datei wird als Kopfzeile zusammen gefriemelt
			foreach ($data as $kopf) {
				$return .= sprintf('`%s`, ', strtolower($kopf));
			}
		
			$length = strlen($return);
			$return = substr($return, 0, $length);
			
			// Spalte für den Import-Timestamp anhängen
			$return .= '`import_timestamp`';
			
			// Kopfzeile zurückgeben
			return $return;
		}
	}
	
	/*
	 * datensatzExistiertBereits()
	 *
	 * es wird geprüft, ob der zu importierende Datensatz bereits existiert
	 *
	 * @params string  $datum
	 * @params string  $startzeit
	 * @params string  $landezeit
	 * @params string  $flugzeug
	 * @return boolean $return
	 */
	if (!function_exists('datensatzExistiertBereits')) {
		function datensatzExistiertBereits($datum, $startzeit, $landezeit, $flugzeug)
		{
			// SQL-Befehl zurecht fuddeln,
			// prüfen ob der Datensatz bereits vorhanden ist
			$sql = sprintf('
				SELECT
					COUNT(*) AS `anzahl_datensaetze`
				FROM
					`flugbuch`
				WHERE
					`flugbuch`.`datum` = "%s" AND
					`flugbuch`.`startzeit` = "%s" AND
					`flugbuch`.`landezeit` = "%s" AND
					`flugbuch`.`luftfahrzeug` = "%s"
				LIMIT 1
			',
				$datum,
				$startzeit,
				$landezeit,
				$flugzeug
			);
				
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			if ($zeile = mysql_fetch_object($db_erg)) {
				// prüfen, ob ein Datensatz bereits schon vorhanden ist
				if ($zeile->anzahl_datensaetze > 0) {
					// Datensatz wurde bereits importiert
					return true;
				} else {
					// Datensatz ist noch nicht vorhanden
					return false;
				}
			}
			
			// Verbindung zur Datenbank schließen
			mysql_close($db_erg);
			
			// im Notfall immer FALSE zurückgeben
			return false;
		}
	}
	
	/*
	 * getDatensaetzeInsertSql()
	 *
	 * ließt die vorhandenen, per Parameter übergebenen Datensätze aus
	 * und bastellt diese mit Hilfe von Klammern, Hochkomma etc. zu einen
	 * Teilbefehl zurecht, welcher später Bestandteil des INSERT-Befehl ist
	 *
	 * @params array   $datensaetze
	 * @params integer $timestamp
	 * @return string  $return
	 */
	if (!function_exists('getDatensaetzeInsertSql')) {
		function getDatensaetzeInsertSql(array $datensaetze, $timestamp)
		{
			// Rückgabe-Variable definieren
			$return = '';
			
			// es wird nun Datensatz füt Datensatz durchlaufen, und
			// anschließend der Teilbefehl für die INSERT-Anweisung zusammengebastelt
			for ($i=0; $i<count($datensaetze); $i++) {
				// wurde der aktuelle Datensatz bereits importiert?
				if (
					!datensatzExistiertBereits(
						$datensaetze[$i]['datum'], $datensaetze[$i]['startzeit'],
						$datensaetze[$i]['landezeit'], $datensaetze[$i]['luftfahrzeug'])
				) {
					$return .= '(';
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['datum']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['startzeit']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['landezeit']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['flugzeit']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['luftfahrzeug']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['besatzung1']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['besatzung2']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['besatzung3']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['besatzung4']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['startort']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['landeort']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['preiskategorie']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['startart']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['flugart']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['leistung1']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['mengeleistung1']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['gebuehrenleistung1']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['leistung2']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['mengeleistung2']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['gebuehrenleistung2']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['fluggebuehren']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteil_1']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteilfluggebuehren_1']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteilleistung1_1']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteilleistung2_1']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteilsumme_1']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteil_2']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteilfluggebuehren_2']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteilleistung1_2']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteilleistung2_2']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteilsumme_2']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteil_3']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteilfluggebuehren_3']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteilleistung1_3']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteilleistung2_3']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteilsumme_3']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteil_4']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteilfluggebuehren_4']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteilleistung1_4']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteilleistung2_4']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['anteilsumme_4']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['landungen']);
					$return .= sprintf('\'%s\', ', $datensaetze[$i]['personen']);
					$return .= sprintf('\'%s\', ', $timestamp);
					
					$return = substr($return, 0, strlen($return) - 2). '), ';
				}
			}
			
			// die letzten beiden Stellen werden abgeschnitten, da
			// wir an dieser Stelle keinen ", " mehr brauchen
			$return = substr($return, 0, strlen($return) - 2);
			
			// SQL-Teilbefehl für INSERT zurückgeben
			return $return;
		}
	}
	
	/*
	 * generateUpdateFluggeldkonten()
	 *
	 * es werden die Fluggebühren für Pilot und Begleiter des letzten Imports
	 * summiert und mit den entsprechenden Fluggeldkonten verbucht.
	 * Der Parameter $timestamp hilft dabei den letzten Import zu ermitteln
	 *
	 * @params integer $timestamp
	 */
	if (!function_exists('generateUpdateFluggeldkonten')) {
		function generateUpdateFluggeldkonten($timestamp)
		{
			// Array zum Speichern der Zwischenwerte anlegen
			$datensaetze = array();
			// Zählervariable initialisieren
			$i = 0;
		
			// SQL-Befehl zurecht fuddeln,
			// Summen der letzten importieren Datensätze ermitteln
			// und die zugehörige Mitgliedsnummer ermitteln
			$sql = sprintf('
				SELECT
					`mitglieder`.`id` AS `acb_nr`,
					ROUND(SUM(`flugbuch`.`anteilsumme_1`), 2) AS `AnteilSumme`
				FROM
					`flugbuch`
				INNER JOIN
					`mitglieder` ON `flugbuch`.`besatzung1` LIKE CONCAT("%%", `mitglieder`.`ameavia`, "%%")
				WHERE
					`flugbuch`.`Import_Timestamp` = "%s"
				GROUP BY
					`mitglieder`.`id`
				UNION (
					SELECT
						`mitglieder`.`id` AS `acb_nr`,
						ROUND(SUM(`flugbuch`.`anteilsumme_2`), 2) AS `AnteilSumme`
					FROM
						`flugbuch`
					INNER JOIN
						`mitglieder` ON `flugbuch`.`besatzung2` LIKE CONCAT("%%", `mitglieder`.`ameavia`, "%%")
					WHERE
						`flugbuch`.`import_timestamp` = "%s"
					GROUP BY
						`mitglieder`.`id`
				)
				ORDER BY
					`acb_nr` ASC
			',
				$timestamp,
				$timestamp
			);

			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			while ($zeile = mysql_fetch_object($db_erg)) {
				// prüfen ob bereits für dieses Fluggeldkonto ein Eintrag im Array vorhanden ist
				if (($i > 0) && ($datensaetze[$i - 1]['acb_nr'] == $zeile->acb_nr)) {
					// den letzten Eintrag des Arrays korrigieren
					$datensaetze[$i - 1]['summe'] += $zeile->AnteilSumme;
					
					// Zähler zurücksetzen für nächste Prüfung
					$i--;
				} else {
					// die notwendigen Ergebnisse der Abfrage in das Array schreiben
					$datensaetze[$i]['acb_nr'] = $zeile->acb_nr;
					$datensaetze[$i]['summe']  = $zeile->AnteilSumme;
				}
			
				// Zähler erhöhen
				$i++;
			}
			
			// neue Salden der Fluggeldkonten berechnen
			doUpdateFluggeldkonten($datensaetze);
		}
	}
	
	/*
	 * doUpdateFluggeldkonten()
	 *
	 * die per Parameter übergebenen Datensätze mit den bestehenden
	 * Salden aus den Fluggeldkonten gegen rechnen und aufsummieren
	 *
	 * @params array $datensaetze
	 */
	if (!function_exists('doUpdateFluggeldkonten')) {
		function doUpdateFluggeldkonten(array $datensaetze)
		{
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');

			// die übergebenen Datensätze durchforsten
			foreach ($datensaetze as $zeile) {
				// SQL-Befehl zum Aktualisieren der Fluggeldkonten zurechtfriemeln
				$sql = sprintf('
					UPDATE
						`fluggeldkonto`
					SET
						`saldo` = `saldo` - %7.2f
					WHERE
						`acb_nr` = %d
				',
					$zeile['summe'],
					$zeile['acb_nr']
				);

				// SQL-Befehl ausführen und Fluggeldkonto aktualisieren
				mysql_query($sql);
			}
		}
	}
	
	/*
	 * generateLogbuchEntry()
	 *
	 * es werden, anhand des übergebenen Zeitstempels,
	 * neue Logbucheinträge (nur das Datum) vorgenommen
	 *
	 * @params integer $timestamp
	 */
	if (!function_exists('generateLogbuchEntry')) {
		function generateLogbuchEntry($timestamp)
		{
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');

			// SQL-Befehl zurecht fuddeln,
			// die importierten Flüge in das Logbuch übertragen
			$sql = sprintf('
				INSERT INTO
					`logbuch` (`datum`) (
						SELECT
							DISTINCT `datum`
						FROM
							`flugbuch`
						WHERE
							`import_timestamp` = %s
					)
			',
				$timestamp
			);

			// SQL-Befehl ausführen
			mysql_query($sql);
		}
	}



	/**************************************************************************************************************************/
	/* ------------------------------------------ BEGINN : IMPORT NACH POST-BEFEHL ------------------------------------------ */
	/**************************************************************************************************************************/

	// es wird geprüft, ob der POST-Befehl ausgeführte wurde und
	// eine entsprechende Datei zum Auslesen ausgewählt wurde
	if (isset($_POST['csv_import']) && !empty($_FILES['csv_datei']['tmp_name'])) {
		// Laufzeit des Skriptes setzen
		set_time_limit(1000);

		// Behelfsvariablen für den Import festlegen
		$datensaetze[] = array();
		$kopfzeile     = '';
		$i             = 0;
		$z             = 0;
		$html          = '';
		$erfolgreich   = false;
		
		// Import-Timestamp deklarieren
		$timestamp = time();

		// prüfen, ob es sich um eine gültige CSV- bzw. Excel-Datei handelt
		if (
			($_FILES['csv_datei']['type'] == 'text/csv') ||
			($_FILES['csv_datei']['type'] == 'application/vnd.ms-excel') ||
			($_FILES['csv_datei']['type'] == 'text/comma-separated-values')
		) {
			// die CSV-Datei zeilenweise auslesen
			if (($handle = fopen($_FILES['csv_datei']['tmp_name'], 'r')) !== FALSE) {
				while (($data = fgetcsv($handle, 10000, ';')) !== FALSE) {
					// die Kopfdaten der CSV-Datei für den Import übernehmen
					if ($i == 0) {
						// die Kopfzeile der CSV-Datei auslesen
						$kopfzeile = getKopfzeile($data);
					} else {
						// Zeilenweise auslesen und für den SQL-Befehl vor formatieren
						$datensaetze[$i - 1]['datum']                 = trim(sprintf('%s-%s-%s', substr($data[0], 6, 4), substr($data[0], 3, 2), substr($data[0], 0, 2)));
						$datensaetze[$i - 1]['startzeit']             = trim($data[1] . ':00');
						$datensaetze[$i - 1]['landezeit']             = trim($data[2] . ':00');
						$datensaetze[$i - 1]['flugzeit']              = trim($data[3]);
						$datensaetze[$i - 1]['luftfahrzeug']          = trim($data[4]);
						$datensaetze[$i - 1]['besatzung1']            = trim($data[5]);
						$datensaetze[$i - 1]['besatzung2']            = trim($data[6]);
						$datensaetze[$i - 1]['besatzung3']            = trim($data[7]);
						$datensaetze[$i - 1]['besatzung4']            = trim($data[8]);
						$datensaetze[$i - 1]['startort']              = trim($data[9]);
						$datensaetze[$i - 1]['landeort']              = trim($data[10]);
						$datensaetze[$i - 1]['preiskategorie']        = trim($data[11]);
						$datensaetze[$i - 1]['startart']              = trim($data[12]);
						$datensaetze[$i - 1]['flugart']               = trim($data[13]);
						$datensaetze[$i - 1]['leistung1']             = trim($data[14]);
						$datensaetze[$i - 1]['mengeleistung1']        = trim($data[15]);
						$datensaetze[$i - 1]['gebuehrenleistung1']    = trim(str_replace(',', '.', $data[16]));
						$datensaetze[$i - 1]['leistung2']             = trim($data[17]);
						$datensaetze[$i - 1]['mengeleistung2']        = trim($data[18]);
						$datensaetze[$i - 1]['gebuehrenleistung2']    = trim(str_replace(',', '.', $data[19]));
						$datensaetze[$i - 1]['fluggebuehren']         = trim(str_replace(',', '.', $data[20]));
						$datensaetze[$i - 1]['anteil_1']              = trim($data[21]);
						$datensaetze[$i - 1]['anteilfluggebuehren_1'] = trim(str_replace(',', '.', $data[22]));
						$datensaetze[$i - 1]['anteilleistung1_1']     = trim(str_replace(',', '.', $data[23]));
						$datensaetze[$i - 1]['anteilleistung2_1']     = trim(str_replace(',', '.', $data[24]));
						$datensaetze[$i - 1]['anteilsumme_1']         = trim(str_replace(',', '.', $data[25]));
						$datensaetze[$i - 1]['anteil_2']              = trim($data[26]);
						$datensaetze[$i - 1]['anteilfluggebuehren_2'] = trim(str_replace(',', '.', $data[27]));
						$datensaetze[$i - 1]['anteilleistung1_2']     = trim(str_replace(',', '.', $data[28]));
						$datensaetze[$i - 1]['anteilleistung2_2']     = trim(str_replace(',', '.', $data[29]));
						$datensaetze[$i - 1]['anteilsumme_2']         = trim(str_replace(',', '.', $data[30]));
						$datensaetze[$i - 1]['anteil_3']              = trim($data[31]);
						$datensaetze[$i - 1]['anteilfluggebuehren_3'] = trim(str_replace(',', '.', $data[32]));
						$datensaetze[$i - 1]['anteilleistung1_3']     = trim(str_replace(',', '.', $data[33]));
						$datensaetze[$i - 1]['anteilleistung2_3']     = trim(str_replace(',', '.', $data[34]));
						$datensaetze[$i - 1]['anteilsumme_3']         = trim(str_replace(',', '.', $data[35]));
						$datensaetze[$i - 1]['anteil_4']              = trim($data[36]);
						$datensaetze[$i - 1]['anteilfluggebuehren_4'] = trim(str_replace(',', '.', $data[37]));
						$datensaetze[$i - 1]['anteilleistung1_4']     = trim(str_replace(',', '.', $data[38]));
						$datensaetze[$i - 1]['anteilleistung2_4']     = trim(str_replace(',', '.', $data[39]));
						$datensaetze[$i - 1]['anteilsumme_4']         = trim(str_replace(',', '.', $data[40]));
						$datensaetze[$i - 1]['landungen']             = trim($data[41]);
						$datensaetze[$i - 1]['personen']              = trim($data[42]);
					}
					
					// Zähler erhöhen
					$i++;
				}
				
				// Anzahl der importierten Datensätze neu festlegen
				$z = $i - 1;

				// Datei nach dem Auslesen schließen
				fclose($handle);
				
				// Teilstring für INSERT-Befehl anhand der Datensätze zurechtbasteln
				$datensatz = getDatensaetzeInsertSql($datensaetze, $timestamp);

				// INSERT-Befehl festlegen, mit Hilfe der ermittelten Kopfzeile,
				// sowie des zuvor ermittelten Teilstring mit den zu importierenden Datensätzen
				$sql = sprintf('INSERT INTO `flugbuch` (%s) VALUES %s;', $kopfzeile, $datensatz);

				// Modul für DB-Zugriff einbinden
				require_once('konfiguration.php');

				// Verbindung zur Datenbank herstellen
				// am System mit Host, Benutzernamen und Password anmelden
				@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
				@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');

				// zuvor definierte SQL-Anweisung ausführen
				// INSERT INTO `flugbuch` (...) VALUES (...)
				if (mysql_query($sql)) {
					// alles gut verlaufen und SQL-Befehl konnte ausgeführt werden
					$erfolgreich = true;
					
					// nun können die Flugkosten der importierten Flüge auf die
					// entsprechenden Fluggeldkonten der Mitglieder verbucht werden
					generateUpdateFluggeldkonten($timestamp);
					
					// Logbucheintrag schreiben
					generateLogbuchEntry($timestamp);
				} else {
					// prüfen ob es Datensätze gab, die importiert werden konnten
					if (empty($datensatz)) {
						// alles gut verlaufen, zwar wurden keine neuen
						// Datensätze importiert, dennoch war alles Fehlerfrei
						$erfolgreich = true;
					}
				}
			}
		}
			
		// POST-Variable nach dem Import zurücksetzen
		unset($_POST['csv_import']);
		
		// prüfen ob der Import erfolgreich war
		if ($erfolgreich) {
			if (!empty($datensatz)) {
				// Tabellenansicht der importieren Flüge anzeigen
				$html = getFluegeImportTabelle($datensaetze, $z);
			}
		} else {
			// Fehlermeldung falls Anwender versucht eine falsche Datei auszulesen
			$html = getErrorMessage();
		}
	}

	/**************************************************************************************************************************/
	/* ------------------------------------------- ENDE : IMPORT NACH POST-BEFEHL ------------------------------------------- */
	/**************************************************************************************************************************/

?>
<!-- ENDE: SKRIPT -->
<!-- BEGINN: AUSGABE -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

    <head>

        <title>Fl&uuml;ge importieren</title>

        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <meta http-equiv="Content-Script-Type" content="text/javascript" />
        <meta http-equiv="Content-Style-Type" content="text/css" />
        <meta http-equiv="content-language" content="de" />
        <meta name="author" content="Benjamin Stopfkuchen" />
		
		<script type="text/javascript" src="./js/jquery-1.10.2.js"></script>
		<script type="text/javascript" src="./js/jquery-1.10.2.min.js"></script>

        <link rel="Stylesheet" type="text/css" href="./css/stylish.css" />
		
		<script type="text/javascript" language="JavaScript">
		<!--
		
			$(document).ready(function() {
				$('#csv_select').click(function() {
					$('input:file').click();
				});
				
				$('input:file').change(function() {
					$('#fakeupload').val($('input:file').val());
				});
			});
		
		//-->
		</script>
		
    </head>

	<body style="margin-top: 0px; margin-left: 0px;">
	
		<table style="border: 1px solid #000000; background-color: #f7f7f7;" width="100%" height="100%">
			<tr>
				<td valign="top" style="padding:20px;">
				
					<h2>Fl&uuml;ge importieren</h2>
					
					<div class="helpline">
						Hier kannst Du die zuvor aus AmeAvia exportierte CSV-Datei ausw&auml;hlen und
						die enthaltenen Fl&uuml;ge anschlie&szlig;end in das Hauptflugbuch importieren.
					</div>
					
					<br />

					<form action="csv_import.php" method="POST" enctype="multipart/form-data" style="width:500px;">

						<fieldset style="width: 530px;">
							<legend>CSV-Datei ausw&auml;hlen</legend>
							
							<input type="button" id="csv_select" name="csv_select" value="Durchsuchen..." />
							<input type="text" id="fakeupload" name="fakeupload" readonly="readonly" />
							<input type="file" id="csv_datei" name="csv_datei" accept="text/csv" style="visibility:hidden;" />
							<br />
							<input type="submit" value="Flugbuch importieren" name="csv_import" id="csv_import" style="margin-top: -15px;" />
							<br />
						</fieldset>

					</form>
					
					<br />
					
					<?php if (isset($html)) { echo $html; } ?>

				</td>
			</tr>
		</table>

	</body>

</html>
<!-- ENDE: AUSGABE -->