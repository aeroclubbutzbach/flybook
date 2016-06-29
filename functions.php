<?php

	// Konstanten definieren
	define('FILTER_MOTORFLUG', '"M1", "M2", "M3", "MS", "UL"');
	define('FILTER_SEGELFLUG', '"S1", "S2"');
	define('STAT_KENNZEICHEN', 0);
	define('STAT_FLUGZEITEN',  1);
	define('STAT_LANDUNGEN',   2);
	
	define('T_NUMERIC', 0);
	define('T_STR',     1);
	define('T_BOOL',    2);
	define('T_DATE',    3);
	define('T_TIME',    4);
	define('T_FLOAT',   5);

	/*
	 * getFluggeldkonto()
	 *
	 * das Fluggeldkonto des ausgewählten Mitglieds, welches anhand
	 * des übergebenen Parameters ermittelt wird, wird geladen
	 *
	 * @params integer $acb_nr
	 * @params integer $monat
	 * @params integer $jahr
	 * @return array   $return
	 */
	if (!function_exists('getFluggeldkonto')) {
		function getFluggeldkonto($acb_nr, $monat = null, $jahr = null)
		{
			// Rückgabe-Variable definieren
			$return = '';
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			if (!(empty($monat)) && !(empty($jahr))) {
				// Zeitraum festlegen
				$datum_von = $jahr . '-' . $monat . '-01';
				$datum_bis = sprintf('%d-%d-%d', $jahr, $monat, date('t', strtotime($datum_von)));
				
				// Filter / Zeitraum setzen
				$filter = sprintf('AND (`datum` BETWEEN "%s" AND "%s")', $datum_von, $datum_bis);
			} else {
				// kein Filter vorhanden
				$filter = '';
			}

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			mysql_set_charset('utf8');
			
			// SQL-Befehl zurecht fuddeln,
			// aktuelles Fluggeldkonto für ausgewähltes Mitglied ermitteln
			$sql = sprintf('
				SELECT
					*
				FROM (
					SELECT
						`flugbuch`.`datum`,
						`flugzeuge`.`flugzeugtyp`,
						`flugbuch`.`luftfahrzeug`,
						`mitglieder_1`.`nachname` AS `pilot`,
						`mitglieder_2`.`nachname` AS `begleiter`,
						`flugbuch`.`startart`,
						CASE
							WHEN (`flugplaetze_1`.`name` IS NULL) THEN
								`flugbuch`.`startort`
							ELSE
								`flugplaetze_1`.`name`
						END AS `startort`,
						CASE
							WHEN (`flugplaetze_2`.`name` IS NULL) THEN
								`flugbuch`.`landeort`
							ELSE
								`flugplaetze_2`.`name`
						END AS `landeort`,
						`flugbuch`.`startzeit`,
						`flugbuch`.`landezeit`,
						`flugbuch`.`flugzeit`,
						`flugbuch`.`preiskategorie`,
						`flugbuch`.`anteilsumme_1` AS `Anteil_Summe`
					FROM
						`flugbuch`
					LEFT JOIN
						`flugzeuge` ON `flugbuch`.`luftfahrzeug` = `flugzeuge`.`kennzeichen`
					LEFT JOIN
						`mitglieder` AS `mitglieder_2` ON `flugbuch`.`besatzung2` LIKE CONCAT("%%", `mitglieder_2`.`ameavia`, "%%")
					LEFT JOIN
						`mitglieder` AS `mitglieder_1` ON `flugbuch`.`besatzung1` LIKE CONCAT("%%", `mitglieder_1`.`ameavia`, "%%")
					LEFT JOIN
						`flugplaetze` AS `flugplaetze_1` ON `flugbuch`.`startort` = `flugplaetze_1`.`ameavia`
					LEFT JOIN
						`flugplaetze` AS `flugplaetze_2` ON `flugbuch`.`landeort` = `flugplaetze_2`.`ameavia`
					WHERE
						(`mitglieder_1`.`id` = %d) %s
					UNION (
						SELECT
							`flugbuch`.`datum`,
							`flugzeuge`.`flugzeugtyp`,
							`flugbuch`.`luftfahrzeug`,
							`mitglieder_1`.`nachname` AS `pilot`,
							`mitglieder_2`.`nachname` AS `begleiter`,
							`flugbuch`.`startart`,
							CASE
								WHEN (`flugplaetze_1`.`name` IS NULL) THEN
									`flugbuch`.`startort`
								ELSE
									`flugplaetze_1`.`name`
							END AS `startort`,
							CASE
								WHEN (`flugplaetze_2`.`name` IS NULL) THEN
									`flugbuch`.`landeort`
								ELSE
									`flugplaetze_2`.`name`
							END AS `landeort`,
							`flugbuch`.`startzeit`,
							`flugbuch`.`landezeit`,
							`flugbuch`.`flugzeit`,
							`flugbuch`.`preiskategorie`,
							`flugbuch`.`anteilsumme_2` AS `Anteil_Summe`
						FROM
							`flugbuch`
						LEFT JOIN
							`flugzeuge` ON `flugbuch`.`luftfahrzeug` = `flugzeuge`.`kennzeichen`
						LEFT JOIN
							`mitglieder` AS `mitglieder_2` ON `flugbuch`.`besatzung2` LIKE CONCAT("%%", `mitglieder_2`.`ameavia`, "%%")
						LEFT JOIN
							`mitglieder` AS `mitglieder_1` ON `flugbuch`.`besatzung1` LIKE CONCAT("%%", `mitglieder_1`.`ameavia`, "%%")
						LEFT JOIN
							`flugplaetze` AS `flugplaetze_1` ON `flugbuch`.`startort` = `flugplaetze_1`.`ameavia`
						LEFT JOIN
							`flugplaetze` AS `flugplaetze_2` ON `flugbuch`.`landeort` = `flugplaetze_2`.`ameavia`
						WHERE
							(`mitglieder_2`.`id` = %d) %s
					)
				) AS `t`
				ORDER BY
					`t`.`datum` DESC,
					`t`.`startzeit` DESC
			',
				$acb_nr,
				$filter,
				$acb_nr,
				$filter
			);
		
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// Startarten prüfen und entsprechend zuweisen
			$startart = array(1 => 'W', 2 => 'F', 3 => 'E');
			
			while ($zeile = mysql_fetch_object($db_erg)) {
				// kompletten Datensatz für Fluggeldkonto aus dem Flugbuch zurechtfuddeln
				// und anschließend Semikolon-Separiert an das Rückgabe-Array anhängen
				$return[] .= sprintf('%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s',
					sprintf('%s %s', $zeile->datum, $zeile->startzeit), 
					$zeile->datum, $zeile->flugzeugtyp, $zeile->luftfahrzeug, $zeile->pilot, $zeile->begleiter,
					$startart[$zeile->startart], $zeile->startort, $zeile->landeort, substr($zeile->startzeit, 0, 5),
					substr($zeile->landezeit, 0, 5), $zeile->flugzeit, $zeile->preiskategorie,
					str_replace(',', '.', ($zeile->Anteil_Summe * -1))
				);
			}
			
			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// SQL-Befehl zurechtfuddeln,
			// Einzahlungen und Buchungen für das ausgewählte Mitglied ermitteln
			$sql = sprintf('
				SELECT
					`zahlungen`.*
				FROM
					`zahlungen`
				WHERE
					(`zahlungen`.`acb_nr`) = %d %s
				ORDER BY
					`zahlungen`.`datum` DESC'
			,
				$acb_nr,
				$filter
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			while ($zeile = mysql_fetch_object($db_erg)) {
				// kompletten Datensatz für angelegte Zahlungen und Buchungen zurecht-
				// fuddeln und anschließend Semikolon-Separiert an das Rückgabe-Array anhängen
				$return[] .= sprintf('%s;%s;;;;;;%s;;;;;;%s',
					sprintf('%s 00:00:00', $zeile->datum), $zeile->datum, $zeile->bemerkungen, $zeile->summe
				);
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Fluggeldkonto sortieren vor der Rückgabe
			sort($return);
			
			// Rückgabe des Fluggeldkontos
			return $return;
		}
	}

	/*
	 * getFlugstatistik()
	 *
	 * ermittelt Flugstunden und -bewegungen anhand der übergebenen
	 * Parameter und gibt diese Flugstatistik als Array zurück
	 *
	 * @params integer $jahr
	 * @params string  $filter
	 * @return array   $return
	 */
	if (!function_exists('getFlugstatistik')) {
		function getFlugstatistik($jahr, $filter)
		{
			// Rückgabe-Variable definieren
			$return = array();
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// Liste mit den Flugzeugen holen
			$flugzeuge = getFlugzeuge($filter);

			foreach ($flugzeuge as $flugzeug) {
				// SQL-Befehl zurecht fuddeln,
				// die Flugstunden und -bewegungen anhand der übergebenen Parameter ermitteln
				$sql = sprintf('
					SELECT
						`flugbuch`.`luftfahrzeug` AS `Flugzeug`,
						ROUND(SUM(`flugbuch`.`flugzeit`) / 60) AS `Flugstunden`,
						COUNT(*) AS `Flugbewegungen`
					FROM
						`flugbuch`
					WHERE
						`flugbuch`.`luftfahrzeug` = "%s"  AND
						`flugbuch`.`geloescht` = "N" AND
						`flugbuch`.`datum` BETWEEN "%d-01-01" AND "%d-12-31"
					GROUP BY
						`flugbuch`.`luftfahrzeug`
					ORDER BY
						`flugbuch`.`luftfahrzeug` ASC
				',
					$flugzeug, $jahr, $jahr
				);
				
				// alternativen SQL-Befehl zurecht fuddeln,
				// wenn das Datum vor 2014 ist ...
				// es wird auf eine alte Tabelle mit Hauptflugbuchdaten zugegriffen
				if ($jahr < 2014) {
					$sql = sprintf('
						SELECT
							`hauptflugbuch`.`kennzeichen` AS `Flugzeug`,
							ROUND(SUM(((HOUR(`hauptflugbuch`.`flugzeit`) * 60) + MINUTE(`hauptflugbuch`.`flugzeit`)) / 60)) AS `Flugstunden`,
							SUM(`hauptflugbuch`.`landungen`) AS `Flugbewegungen`
						FROM
							`hauptflugbuch`
						WHERE
							`hauptflugbuch`.`kennzeichen` = "%s" AND
							`hauptflugbuch`.`geloescht` = "N" AND
							`hauptflugbuch`.`datum` BETWEEN "%d-01-01" AND "%d-12-31"
						GROUP BY
							`hauptflugbuch`.`kennzeichen`
						ORDER BY
							`hauptflugbuch`.`kennzeichen` ASC
					',
						$flugzeug, $jahr, $jahr
					);
				}
			
				// zuvor definierte SQL-Anweisung ausführen
				// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
				$db_erg = mysql_query($sql);

				if ($zeile = mysql_fetch_object($db_erg)) {
					// Flugstunden und -bewegungen hinzufügen
					$return[] = array(
						$zeile->Flugzeug,
						$zeile->Flugstunden,
						$zeile->Flugbewegungen
					);
				} else {
					// Keine Daten gefunden oder vorhanden
					$return[] = array($flugzeug, 0, 0);
				}
				
				// Verbindung zur Datenbank schließen
				mysql_free_result($db_erg);
			}
				
			// Rückgabe der Flugstunden und -bewegungen
			return $return;
		}
	}
	
	/*
	 * getFlugstundenUmsatz()
	 *
	 * die Flugstundenanzahl von Beginn an der Zeitrechnung wird,
	 * entsprechend der Flugzeugklasse und der Jahreszahl, ermittelt
	 *
	 * @return array $return
	 */
	if (!function_exists('getFlugstundenUmsatz')) {
		function getFlugstundenUmsatz()
		{
			// Rückgabe-Variable definieren
			$return = array();

			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
		
			// SQL-Befehl zurecht fuddeln,
			// die Flugstunden, die Flugzeugklassen und die Jahreszahlen ermitteln
			$sql = sprintf('
				SELECT
					`flugzeuge`.`typ` AS `flugzeugklasse`,
					YEAR(`hauptflugbuch`.`datum`) AS `jahr`,
					ROUND(SUM(((HOUR(`hauptflugbuch`.`flugzeit`) * 60) + MINUTE(`hauptflugbuch`.`flugzeit`)) / 60)) AS `flugstunden`
				FROM
					`hauptflugbuch`
				INNER JOIN
					`flugzeuge` ON `flugzeuge`.`kennzeichen` = `hauptflugbuch`.`kennzeichen`
				WHERE
					YEAR(`hauptflugbuch`.`datum`) <= (YEAR(NOW())) AND
					`flugzeuge`.`vereinsflugzeug` = "J" AND
					`hauptflugbuch`.`geloescht` = "N"
				GROUP BY
					YEAR(`hauptflugbuch`.`datum`),
					`flugzeuge`.`typ`
				UNION (
					SELECT
						`flugzeuge`.`typ` AS `flugzeugklasse`,
						YEAR(`flugbuch`.`datum`) AS `jahr`,
						ROUND(SUM(`flugbuch`.`flugzeit`) / 60) AS `flugstunden`
					FROM
						`flugbuch`
					INNER JOIN
						`flugzeuge` ON `flugzeuge`.`kennzeichen` = `flugbuch`.`luftfahrzeug`
					WHERE
						YEAR(`flugbuch`.`datum`) <= (YEAR(NOW())) AND
						`flugzeuge`.`vereinsflugzeug` = "J" AND
						`flugbuch`.`geloescht` = "N"
					GROUP BY
						YEAR(`flugbuch`.`datum`),
						`flugzeuge`.`typ`
				)
			');
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Checkvariable für das Jahr
			$jahr = 0;

			while ($zeile = mysql_fetch_object($db_erg)) {
				if ($jahr != $zeile->jahr) {
					$return[$zeile->jahr]['segelflug'] = 0;
					$return[$zeile->jahr]['dimona']    = 0;
					$return[$zeile->jahr]['falke']     = 0;
					$return[$zeile->jahr]['ul']        = 0;
					
					// Jahreszahl speichern
					$jahr = $zeile->jahr;
				}

				switch ($zeile->flugzeugklasse) {
					case '1sv' :
					case '2sv' :
						$return[$zeile->jahr]['segelflug'] += $zeile->flugstunden;
						break;
					case 'Div' :
						$return[$zeile->jahr]['dimona'] += $zeile->flugstunden;
						break;
					case 'Fav' :
						$return[$zeile->jahr]['falke'] += $zeile->flugstunden;
						break;
					case 'Ulv' :
						$return[$zeile->jahr]['ul'] += $zeile->flugstunden;
						break;
				}
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);

			// Rückgabe der Flugstunden nach Jahren
			return $return;
		}
	}

	/*
	 * getFlugstunden()
	 *
	 * die Flugstundenanzahl entsprechend der übergebenen Statistik ermitteln
	 *
	 * @params array $flugstatistik
	 * @return array $return
	 */
	if (!function_exists('getFlugstunden')) {
		function getFlugstunden($flugstatistik)
		{
			// Rückgabe-Variable definieren
			$return = array();

			// die Flugstunden auslesen uns in ein
			// separates Rückgabe-Array füllen
			foreach ($flugstatistik as $data) {
				$return[] = $data[STAT_FLUGZEITEN];
			}
			
			// Rückgabe der Flugstunden
			return $return;
		}
	}
	
	/*
	 * getFlugbewegungen()
	 *
	 * die Anzahl der Flugbewegungen entsprechend der übergebenen Statistik ermitteln
	 *
	 * @params array $flugstatistik
	 * @return array $return
	 */
	if (!function_exists('getFlugbewegungen')) {
		function getFlugbewegungen($flugstatistik)
		{
			// Rückgabe-Variable definieren
			$return = array();

			// die Flugstunden auslesen uns in ein
			// separates Rückgabe-Array füllen
			foreach ($flugstatistik as $data) {
				$return[] = $data[STAT_LANDUNGEN];
			}
			
			// Rückgabe der Flugstunden
			return $return;
		}
	}
	
	/*
	 * getFlugzeuge()
	 *
	 * alle in der Tabelle `flugzeuge` hinterlegten Flugzeuge
	 * werden anhand der übergebenen Filters ermittelt
	 *
	 * @params string  $filter
	 * @return array   $return
	 */
	if (!function_exists('getFlugzeuge')) {
		function getFlugzeuge($filter)
		{
			// Rückgabe-Variable definieren
			$return = array();

			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
		
			// SQL-Befehl zurecht fuddeln,
			// die Flugzeuge anhand der übergebenen Parameter ermitteln
			$sql = sprintf('
				SELECT
					`flugzeuge`.`kennzeichen`
				FROM
					`flugzeuge`
				WHERE
					`flugzeuge`.`typ1` IN (%s) AND `flugzeuge`.`status` = 2
				ORDER BY
					`flugzeuge`.`kennzeichen` ASC
			',
				$filter
			);

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			while ($zeile = mysql_fetch_object($db_erg)) {
				$return[] = $zeile->kennzeichen;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);

			// Rückgabe der Flugzeuge
			return $return;
		}
	}
	
	/*
	 * getEmailadressen()
	 *
	 * die aktuellen Mitglieder mit vorhandener eMail-Adresse
	 * werden ermittelt und wird als Array zurückgegeben
	 *
	 * @params boolean $filter
	 * @return array   $return
	 */
	if (!function_exists('getEmailadressen')) {
		function getEmailadressen($filter = false)
		{
			// Rückgabe-Variable definieren
			$return = array();

			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
		
			if (!$filter) {
				// SQL-Befehl zurechtfuddeln,
				// die aktuellen Mitglieder mit vorhandener eMail-Adresse werden ermittelt
				$sql = sprintf('
					SELECT
						`mitglieder`.`id`,
						`mitglieder`.`nachname`,
						`mitglieder`.`vorname`,
						`mitglieder`.`email`
					FROM
						`mitglieder`
					WHERE
						NOT `mitglieder`.`email` IS NULL AND
						`mitglieder`.`rundmail` = "J"
					ORDER BY
						`mitglieder`.`email` ASC
				');
			} else {
				// SQL-Befehl zurechtfuddeln,
				// nur die aktuellen Flugschüler mit vorhandener eMail-Adresse werden ermittelt
				$sql = sprintf('
					SELECT
						`mitglieder`.`id`,
						`mitglieder`.`nachname`,
						`mitglieder`.`vorname`,
						`mitglieder`.`email`
					FROM
						`mitglieder`
					WHERE
						NOT `mitglieder`.`email` IS NULL AND
						`mitglieder`.`rundmail` = "J" AND
						`mitglieder`.`status` = "S"
					ORDER BY
						`mitglieder`.`email` ASC
				');
			}

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Zählervariable initialisieren
			$i = 0;

			while ($zeile = mysql_fetch_object($db_erg)) {
				// Name kürzen falls nötig
				if (
					(substr($zeile->nachname, strlen($zeile->nachname) - 2, 1) == ' ') ||
					(substr($zeile->nachname, strlen($zeile->nachname) - 3, 1) == ' ')
				) {
					// Name einkürzen, bsp: aus Koch C wird Koch
					$zeile->nachname = substr($zeile->nachname, 0, strlen($zeile->nachname) - 2);
					// führende und endende Leerzeichen entfernen falls vorhanden
					$zeile->nachname = trim($zeile->nachname);
				}
					
				$return[$i]['acb_nr']   = $zeile->id;
				$return[$i]['nachname'] = $zeile->nachname;
				$return[$i]['vorname']  = $zeile->vorname;
				$return[$i]['email']    = $zeile->email;
				
				// Zähler erhöhen
				$i++;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);

			// Rückgabe der eMail-Adressen
			return $return;
		}
	}
	
	/*
	 * toSqlDatum()
	 *
	 * konvertiert ein Datum vom Format TT.MM.JJJJ
	 * in das SQL-Format JJJJ-MM-TT
	 *
	 * @params string $datum
	 * @return string $return
	 */
	if (!function_exists('toSqlDatum')) {
		function toSqlDatum($datum)
		{
			// Rückgabe-Variable initialisieren
			$return = '';
		
			// Datum umbauen
			$datum_arr = explode('.', $datum);
			
			// das Datum wird auf die korrekte Länge geprüft
			// d.h. es müssen 3 Werte vorhanden sein, Tag - Monat - Jahr
			if (count($datum_arr) == 3) {
				// Datum nun in das Format JJJJ-MM-TT bringen
				$return = sprintf('%s-%s-%s', $datum_arr[2], $datum_arr[1], $datum_arr[0]);
			}
			
			// Datum im Format JJJJ-MM-TT zurückgeben
			return $return;
		}
	}
	
	/*
	 * fromSqlDatum()
	 *
	 * konvertiert ein Datum im umgekehrten Sinne
	 * vom SQL-Format JJJJ-MM-TT in das Format TT.MM.JJJJ
	 *
	 * @params string $datum
	 * @return string $return
	 */
	if (!function_exists('fromSqlDatum')) {
		function fromSqlDatum($datum)
		{
			// Rückgabe-Variable initialisieren
			$return = '';
		
			// Datum umbauen
			$datum_arr = explode('-', $datum);
			
			// das Datum wird auf die korrekte Länge geprüft
			// d.h. es müssen 3 Werte vorhanden sein, Jahr - Monat - Tag
			if (count($datum_arr) == 3) {
				// Datum nun in das Format TT.MM.JJJJ bringen
				$return = sprintf('%s.%s.%s', $datum_arr[2], $datum_arr[1], $datum_arr[0]);
			}
			
			// Datum im Format TT.MM.JJJJ zurückgeben
			return $return;
		}
	}
	
	/*
	 * minutesToTime()
	 *
	 * konvertiert eine Minutenangabe in das Format H:MM
	 *
	 * @params integer $zeit
	 * @return string  $return
	 */
	if (!function_exists('minutesToTime')) {
		function minutesToTime($zeit)
		{
			// Stunden und Minuten ausrechnen
			$stunden = intval($zeit / 60);
			$minuten = ($zeit % 60);
			// Führende Nullen an die Minutenanzahl anhängen
			$minuten = str_pad($minuten, 2 ,'0', STR_PAD_LEFT);
		
			// Zeit im Format H:MM zurückgeben
			return sprintf('%s:%s', $stunden, $minuten);
		}
	}
	
	/*
	 * getDbValue()
	 *
	 * prüft den übergebenen Wert und formatiert ihn entsprechend für die
	 * MySQL-Datenbank, um diesen dann an einen SQL-Befehl anhängen zu können
	 *
	 * @params string $val
	 * @params string $datentyp
	 * @return string $data
	 */
	if (!function_exists('getDbValue')) {
		function getDbValue($val, $datentyp = T_STR)
		{
			// Rückgabe-Variable definieren
			$return = '';
		
			// zunächst überprüfen, ob der Wert leer ist oder nicht
			if (!empty($val)) {
				// als nächstes Prüfen um welchen Datentyp es sich handelt
				switch ($datentyp) {
					case T_NUMERIC :
						// es handelt sich um einen numerischen Wert deshalb
						// wird dieser nicht in Anführungszeichen zurückgegeben
						$return = sprintf('%d', $val);
						break;
					case T_FLOAT :
						// es handelt sich um einen numerischen Wert mit Kommastellen
						// deshalb wird dieser nicht in Anführungszeichen zurückgegeben
						$return = sprintf('%01.2f', str_replace(',', '.', $val));
						break;
					case T_DATE :
						// es handelt sich um einen Datums-Wert deshalb muss
						// dieser in das Format JJJJ-MM-TT gebracht werden
						$return = sprintf('"%s"', toSqlDatum($val));
						break;
					case T_TIME :
						// es handelt sich um einen Zeit-Wert deshalb muss
						// dieser in das Format HH:MM:SS gebracht werden
						$return = sprintf('"%s"', $val . ':00');
						break;
					case T_BOOL :
						// es handelt sich um einen booleschen Wert deshalb
						// wird entweder "J" für Ja oder "N" für Nein zurückgegeben
						$return = '"J"';						
						break;
					case T_STR :
					default :
						// in allen anderen Fällen handelt es sich um eine Zeichen-
						// kette, diese wird generell in Anführungszeichen zurückgegeben
						$return = sprintf('"%s"', utf8_decode($val));
						break;
				}
			} else {
				// ist der Wert leer oder ungültig,
				// wird DB-NULL zurückgegeben
				if ($datentyp == T_BOOL) {
					$return = '"N"';
				} else if ($datentyp == T_NUMERIC) {
					$return = '0';
				} else if ($datentyp == T_FLOAT) {
					$return = '0.00';
				} else {
					$return = 'NULL';
				}
			}
			
			// Rückgabe des formatierten Feldinhaltes
			return $return;
		}
	}
	
	/*
	 * getFluggeldkontoSaldo()
	 *
	 * der Stand des Fluggeldkontos des übergebenenen Mitglieds
	 * anhand der Parameter und des Monates ermitteln
	 *
	 * @params integer $acb_nr
	 * @params integer $monat
	 * @params integer $jahr
	 * @return float   $return
	 */
	if (!function_exists('getFluggeldkontoSaldo')) {
		function getFluggeldkontoSaldo($acb_nr, $monat, $jahr)
		{
			// Rückgabevariable anlegen und initialsieren
			$return = 0.0;
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');
			
			if (!(empty($monat)) && !(empty($jahr))) {
				// Zeitraum festlegen
				$datum_von = $jahr . '-' . $monat . '-01';
				$datum_bis = sprintf('%d-%d-%d', $jahr, $monat, date('t', strtotime($datum_von)));
				
				// Filter / Zeitraum setzen
				$filter = sprintf('AND (`datum` > "%s")', $datum_bis);
			} else {
				// kein Filter vorhanden
				$filter = '';
			}

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurecht fuddeln,
			// die Flugkosten anhand der übergebenen Parameter ermitteln
			$sql = sprintf('(
				SELECT
					SUM(`flugbuch`.`anteilsumme_1` * -1) AS `Anteil_Summe`
				FROM
					`flugbuch`
				LEFT JOIN
					`mitglieder` ON `flugbuch`.`besatzung1` LIKE CONCAT("%%", `mitglieder`.`ameavia`, "%%")
				WHERE
					(`mitglieder`.`id` = %d) %s
				) UNION (
				SELECT
					SUM(`flugbuch`.`anteilsumme_2` * -1) AS `Anteil_Summe`
				FROM
					`flugbuch`
				LEFT JOIN
					`mitglieder` ON `flugbuch`.`besatzung2` LIKE CONCAT("%%", `mitglieder`.`ameavia`, "%%")
				WHERE
					(`mitglieder`.`id` = %d) %s
				) UNION (
				SELECT
					SUM(`summe`)
				FROM
					`zahlungen`
				WHERE
					(`acb_nr` = %d) %s
				)
			',
				$acb_nr,
				$filter,
				$acb_nr,
				$filter,
				$acb_nr,
				$filter
			);

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			while ($zeile = mysql_fetch_object($db_erg)) {
				$return += $zeile->Anteil_Summe;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// SQL-Befehl zurecht fuddeln,
			// Saldo des Fluggeldkonte ermitteln
			$sql = sprintf('
				SELECT
					`fluggeldkonto`.`saldo`
				FROM
					`fluggeldkonto`
				WHERE
					`fluggeldkonto`.`acb_nr` = %d
				LIMIT 1
			',
				$acb_nr
			);

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			while ($zeile = mysql_fetch_object($db_erg)) {
				$return = $zeile->saldo - $return;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);

			// Rückgabe des Saldo
			return $return;
		}
	}
	
	/*
	 * getFluggeldkontoUmsatz()
	 *
	 * die Umsätze für das übergebenenen Mitglieds
	 * anhand der Parameter und des Monates ermitteln
	 *
	 * @params integer $acb_nr
	 * @params integer $monat
	 * @params integer $jahr
	 * @return float   $return
	 */
	if (!function_exists('getFluggeldkontoUmsatz')) {
		function getFluggeldkontoUmsatz($acb_nr, $monat, $jahr)
		{
			// Rückgabevariable anlegen und initialsieren
			$return = 0.0;
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');
			
			// Zeitraum festlegen
			$datum_von = $jahr . '-' . $monat . '-01';
			$datum_bis = sprintf('%d-%d-%d', $jahr, $monat, date('t', strtotime($datum_von)));

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurecht fuddeln,
			// die Flugkosten anhand der übergebenen Parameter ermitteln
			$sql = sprintf('(
				SELECT
					SUM(`flugbuch`.`anteilsumme_1`) AS `Anteil_Summe`
				FROM
					`flugbuch`
				LEFT JOIN
					`mitglieder` ON `flugbuch`.`besatzung1` LIKE CONCAT("%%", `mitglieder`.`ameavia`, "%%")
				WHERE
					(`mitglieder`.`id` = %d) AND (`datum` >= "%s" AND `datum` <= "%s")
				) UNION (
				SELECT
					SUM(`flugbuch`.`anteilsumme_2`) AS `Anteil_Summe`
				FROM
					`flugbuch`
				LEFT JOIN
					`mitglieder` ON `flugbuch`.`besatzung2` LIKE CONCAT("%%", `mitglieder`.`ameavia`, "%%")
				WHERE
					(`mitglieder`.`id` = %d) AND (`datum` >= "%s" AND `datum` <= "%s")
				)
			',
				$acb_nr,
				$datum_von,
				$datum_bis,
				$acb_nr,
				$datum_von,
				$datum_bis
			);

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			while ($zeile = mysql_fetch_object($db_erg)) {
				$return += $zeile->Anteil_Summe;
			}

			// Rückgabe des Umsatzes
			return $return;
		}
	}
	
	/*
	 * getListeMonate()
	 *
	 * listet alle zwölf Monate als ComboBox-Items auf und
	 * stellt den aktuellen Monat als selektiert dar
	 *
	 * @params string $monat
	 * @return string $html
	 */
	if (!function_exists('getListeMonate')) {
		function getListeMonate($monat = null)
		{
			// Rückgabe-Variable definieren
			$html = '';
			
			// Array mit Monatsnamen anlegen
			$monate = array(
				'01' => 'Januar',  '02' => 'Februar',  '03' => 'M&auml;rz',
				'04' => 'April',   '05' => 'Mai',      '06' => 'Juni',
				'07' => 'Juli',    '08' => 'August',   '09' => 'September',
				'10' => 'Oktober', '11' => 'November', '12' => 'Dezember'
			);
			
			foreach ($monate as $key => $value) {
				// prüfen, ob es sich um die aktuellen Monat handelt
				if ($monat == $key) {
					// Eintrag als selektiert darstellen
					$html .= sprintf('<option value="%s" selected="selected">%s</option>', $key, $value);
				} else {
					// Eintrag normal darstellen
					$html .= sprintf('<option value="%s">%s</option>', $key, $value);
				}
			}
			
			// Rückgabe der Liste der Monate
			return $html;
		}
	}
	
	/*
	 * getListeJahre()
	 *
	 * listet einige Jahre als ComboBox-Items auf und
	 * stellt das aktuelle Jahr als selektiert dar
	 *
	 * @params string $jahr
	 * @return string $html
	 */
	if (!function_exists('getListeJahre')) {
		function getListeJahre($jahr = null)
		{
			// Rückgabe-Variable definieren
			$html = '';
			
			// Jahre durchlaufen von 1998 bis 2038
			for ($i=1998; $i<=2038; $i++) {
				// prüfen, ob es sich um das aktuelle Jahr handelt
				if ($jahr == $i) {
					// Eintrag als selektiert darstellen
					$html .= sprintf('<option value="%s" selected="selected">%s</option>', $i, $i);
				} else {
					// Eintrag normal darstellen
					$html .= sprintf('<option value="%s">%s</option>', $i, $i);
				}
			}
			
			// Rückgabe der Liste der Jahre
			return $html;
		}
	}
	
	/*
	 * getMitgliederbestand()
	 *
	 * die aktuellen Mitgliederzahlen werden für eine Excel-Liste (der
	 * Bestandserhebung des LSB) ermittelt und als Array zurückgegeben
	 *
	 * @return array $return
	 */
	if (!function_exists('getMitgliederbestand')) {
		function getMitgliederbestand()
		{
			// Rückgabe-Variable definieren
			$return = array();

			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
		
			// SQL-Befehl zurechtfuddeln,
			// die aktuellen Mitglieder werden ermittelt
			$sql = sprintf('
				SELECT
					`mitglieder`.`nachname` AS `nachname`,
					`mitglieder`.`vorname` AS `vorname`,
					YEAR(`mitglieder`.`geburtsdatum`) AS `geburtsjahr`,
					`mitglieder`.`anrede` AS `anrede`
				FROM
					`mitglieder`
				WHERE
					NOT `mitglieder`.`geburtsdatum` IS NULL AND
					`mitglieder`.`anrede` IN ("F", "H") AND
					`mitglieder`.`status` != "X"
				ORDER BY
					`mitglieder`.`nachname` ASC,
					`mitglieder`.`vorname` ASC
			');

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Zählervariable initialisieren
			$i = 0;

			while ($zeile = mysql_fetch_object($db_erg)) {
				// Name kürzen falls nötig
				if (
					(substr($zeile->nachname, strlen($zeile->nachname) - 2, 1) == ' ') ||
					(substr($zeile->nachname, strlen($zeile->nachname) - 3, 1) == ' ')
				) {
					// Name einkürzen, bsp: aus Koch C wird Koch
					$zeile->nachname = substr($zeile->nachname, 0, strlen($zeile->nachname) - 2);
					// führende und endende Leerzeichen entfernen falls vorhanden
					$zeile->nachname = trim($zeile->nachname);
				}
					
				$return[$i]['nachname']    = $zeile->nachname;
				$return[$i]['vorname']     = $zeile->vorname;
				$return[$i]['geburtsjahr'] = $zeile->geburtsjahr;
				$return[$i]['geschlecht']  = ($zeile->anrede == 'F') ? 'w' : 'm';
				
				// Zähler erhöhen
				$i++;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);

			// Rückgabe des Mitgliederbestandes
			return $return;
		}
	}
	
	/*
	 * setMonatssalden()
	 *
	 * schreibt die aktuellen Monatssalden (Anfangs- und Endsaldo),
	 * der übergebenen Parameter, in eine gesonderte Datenbanktabelle
	 * um später schneller bei Fehlern bei der Abrechnung handeln zu können
	 *
	 * @params array $params
	 * @return array $return
	 */
	if (!function_exists('setMonatssalden')) {
		function setMonatssalden($params)
		{
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
			// zunächst alle bereits vorhandenen Datensätze löschen
			$sql = sprintf('
				DELETE FROM
					`monatssalden`
				WHERE
					`acb_nr` = %d AND
					`jahr` = %d AND
					`monat` = %d
			',
				$params['acb_nr'],
				$params['jahr'],
				$params['monat']
			);

			// zuvor definierte SQL-Anweisung ausführen
			mysql_query($sql);
			
			// SQL-Befehl zurechtfuddeln,
			// Befehl zum Speichern der Kontensalden festlegen
			$sql = sprintf('
				INSERT INTO
					`monatssalden` (
						`acb_nr`,
						`zyklus`,
						`jahr`,
						`monat`,
						`saldo_anfang`,
						`saldo_ende`
				) VALUES (
					%d, "%s", %d, %d, %01.2f, %01.2f
				)
			',
				$params['acb_nr'],
				$params['zyklus'],
				$params['jahr'],
				$params['monat'],
				$params['saldo_anfang'],
				$params['saldo_ende']
			);

			// zuvor definierte SQL-Anweisung ausführen
			mysql_query($sql);
		}
	}

?>