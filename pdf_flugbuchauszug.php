<?php

	// allgemeine Funktionen einbinden
	include_once('./functions.php');
	
	// Definitionen festlegen
	define('SEGELFLUG',   'S');
	define('MOTORSEGLER', 'MS');
	define('MOTORFLUG',   'M');
	define('ULTRALEICHT', 'UL');
	
	header('Content-type: text/html; charset=utf-8');

	/*
	 * getMitgliedsnummern()
	 *
	 * gibt die Liste aller Mitglieder (Nummern) zurück,
	 * welche in den letzten 12 Monaten geflogen haben
	 *
	 * @return array $return
	 */
	if (!function_exists('getMitgliedsnummern')) {
		function getMitgliedsnummern()
		{
			// Rückgabe-Array definieren
			$return = array();
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
			// die aktuellen Mitglieder, welche in den vergangenen
			// 12 Monaten geflogen haben, werden ermittelt
			$sql = sprintf('
				SELECT
					*
				FROM (
					SELECT
						`mitglieder`.`id` AS `acb_nr`,
						`mitglieder`.`nachname` AS `nachname`,
						`mitglieder`.`vorname` AS `vorname`,
						`mitglieder`.`email` AS `email`
					FROM
						`mitglieder`
					LEFT JOIN
						`flugbuch` ON `flugbuch`.`besatzung1` LIKE CONCAT("%%", `mitglieder`.`ameavia`, "%%")
					WHERE
						`flugbuch`.`datum` > DATE_SUB(NOW(), INTERVAL 12 MONTH) AND
						`mitglieder`.`email` IS NOT NULL AND
						`mitglieder`.`rundmail` = "J"
					UNION (
						SELECT
							`mitglieder`.`id` AS `acb_nr`,
							`mitglieder`.`nachname` AS `nachname`,
							`mitglieder`.`vorname` AS `vorname`,
							`mitglieder`.`email` AS `email`
						FROM
							`mitglieder`
						LEFT JOIN
							`flugbuch` ON `flugbuch`.`besatzung2` LIKE CONCAT("%%", `mitglieder`.`ameavia`, "%%")
						INNER JOIN
							`flugzeuge` ON `flugbuch`.`luftfahrzeug` = `flugzeuge`.`kennzeichen`
						INNER JOIN
							`flugzeugtyp` ON `flugzeugtyp`.`id` = `flugzeuge`.`typ1`
						WHERE
							(`flugbuch`.`preiskategorie` != "FM" AND
							`flugbuch`.`preiskategorie` != "FR" AND
							(`flugbuch`.`datum` > DATE_SUB(NOW(), INTERVAL 12 MONTH))) AND (
								(`flugzeuge`.`typ1` IN ("S1", "S2") AND FIND_IN_SET("C", `mitglieder`.`fachausweise`)) OR
								(`flugzeuge`.`typ1` IN ("MS") AND FIND_IN_SET("B", `mitglieder`.`fachausweise`)) OR
								(`flugzeuge`.`typ1` IN ("M1", "M2", "M3") AND FIND_IN_SET("A", `mitglieder`.`fachausweise`)) OR
								(`flugzeuge`.`typ1` = "UL" AND FIND_IN_SET("H", `mitglieder`.`fachausweise`))
							) AND
						`mitglieder`.`email` IS NOT NULL AND
						`mitglieder`.`rundmail` = "J"
					)
				) AS `t`
				GROUP BY
					`t`.`acb_nr`
				ORDER BY
					`t`.`acb_nr` ASC
			');
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Zählervariable initialisieren
			$i = 0;
			
			while ($zeile = mysql_fetch_object($db_erg)) {	
				// Daten übernehmen wie hinterlegt
				// Parameter Mitgliedsnummer in das Rückgabe-Array einfügen
				$return[$i]['acb_nr']   = $zeile->acb_nr;
				$return[$i]['nachname'] = $zeile->nachname;
				$return[$i]['vorname']  = $zeile->vorname;
				$return[$i]['email']    = $zeile->email;
				
				// Zähler erhöhen
				$i++;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Mitglieder
			return $return;
		}
	}
	
	/*
	 * getFluegeTrainingsbarometer()
	 *
	 * gibt die Summe der Flugstunden und die Anzahl der
	 * Landungen, des übergebenen Mitgliedes, innerhalb der letzten
	 * 6 Monate zurück, zur Ermittlung des Trainingsstandes
	 *
	 * @params integer $acb_nr
	 *
	 * @return array $return
	 */
	if (!function_exists('getFluegeTrainingsbarometer')) {
		function getFluegeTrainingsbarometer($acb_nr)
		{
			// Rückgabe-Array definieren
			$return = array();
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
			// die Flüge innerhalb der letzten 6 Monate ermitteln
			$sql = sprintf('
				SELECT
					SUM(`t`.`flugzeit`) / 60 AS `flugzeit`,
					SUM(`t`.`landungen`) AS `landungen`
				FROM
				(
					SELECT
						`a`.`acb_nr` AS `acb_nr`,
						SUM(`a`.`flugzeit`) AS `flugzeit`,
						SUM(`a`.`landungen`) AS `landungen`
					FROM (
						SELECT
							`mitglieder`.`id` AS `acb_nr`,
							SUM(`flugbuch`.`flugzeit`) AS `flugzeit`,
							COUNT(*) AS `landungen`
						FROM
							`flugbuch`
						INNER JOIN
							`mitglieder` ON `flugbuch`.`besatzung1` LIKE CONCAT("%%", `mitglieder`.`ameavia`, "%%")
						WHERE
							(`flugbuch`.`datum` > DATE_SUB(NOW(), INTERVAL 6 MONTH)) AND
							`flugbuch`.`geloescht` = "N"
						GROUP BY
							`mitglieder`.`id`
						UNION ALL (
							SELECT
								`mitglieder`.`id` AS `acb_nr`,
								SUM(`flugbuch`.`flugzeit`) AS `flugzeit`,
								COUNT(*) AS `landungen`
							FROM
								`flugbuch`
							INNER JOIN
								`mitglieder` ON `flugbuch`.`besatzung2` LIKE CONCAT("%%", `mitglieder`.`ameavia`, "%%")
							INNER JOIN
								`flugzeuge` ON `flugbuch`.`luftfahrzeug` = `flugzeuge`.`kennzeichen`
							WHERE
								(`flugbuch`.`preiskategorie` != "FM" AND
								`flugbuch`.`preiskategorie` != "FR" AND
								(`flugbuch`.`datum` > DATE_SUB(NOW(), INTERVAL 6 MONTH))) AND (
									(`flugzeuge`.`typ1` IN ("S1", "S2") AND FIND_IN_SET("C", `mitglieder`.`fachausweise`)) OR
									(`flugzeuge`.`typ1` IN ("MS") AND FIND_IN_SET("B", `mitglieder`.`fachausweise`)) OR
									(`flugzeuge`.`typ1` IN ("M1", "M2", "M3") AND FIND_IN_SET("A", `mitglieder`.`fachausweise`)) OR
									(`flugzeuge`.`typ1` = "UL" AND FIND_IN_SET("H", `mitglieder`.`fachausweise`))
								) AND
								`flugbuch`.`geloescht` = "N"
							GROUP BY
								`mitglieder`.`id`
						)
					) AS `a`
					WHERE
						`a`.`flugzeit` > 0
					GROUP BY
						`a`.`acb_nr`
				) AS `t`
				WHERE
					`t`.`acb_nr` = %d
				LIMIT 1
			',
				$acb_nr
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			while ($zeile = mysql_fetch_object($db_erg)) {	
				// Daten übernehmen wie hinterlegt
				// Flugzeiten in das Rückgabe-Array einfügen
				$return['flugzeit']  = $zeile->flugzeit;
				$return['landungen'] = $zeile->landungen;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Mitglieder
			return $return;
		}
	}
	
	/*
	 * getFlugbuchPdf()
	 *
	 * die PDF-Ansicht vom Flugbuch des ausgewählten Mitglieds, welches
	 * anhand des übergebenen Parameters ermittelt wird, wird geladen
	 *
	 * @params integer $acb_nr
	 * @return array   $return
	 */
	if (!function_exists('getFlugbuchPdf')) {
		function getFlugbuchPdf($acb_nr)
		{
			// Rückgabe-Array definieren
			$return = array();
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.');
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');

			// SQL-Befehl zurechtfuddeln,
			// die Flugeinträge der letzten 12 Monate, des
			// aktuell ausgewählten Mitglieds, werden ermittelt
			$sql = sprintf('
				SELECT
					`hauptflugbuch`.*
				FROM (
					SELECT
						`flugbuch`.`datum` AS `datum`,
						`flugzeugtyp`.`gruppe` AS `gruppe`,
						`flugzeuge`.`flugzeugtyp` AS `typ`,
						`flugzeuge`.`kennzeichen` AS `kennzeichen`,
						`mitglieder_1`.`nachname` AS `pilot`,
						CASE
							WHEN ((`flugbuch`.`preiskategorie` = "FR" OR `flugbuch`.`preiskategorie` = "FM") AND (`flugbuch`.`besatzung2` IS NOT NULL)) THEN (
								SELECT
									CONCAT("F-Schl.", REPLACE(`t`.`luftfahrzeug`, "-", ""))
								FROM
									`flugbuch` AS `t`
								WHERE
									`t`.`datum` = `flugbuch`.`datum` AND
									(
										`t`.`startzeit` = `flugbuch`.`startzeit` OR (
											`t`.`startzeit` >= CONCAT(
												HOUR(`flugbuch`.`startzeit`), ":",
												MINUTE(`flugbuch`.`startzeit`) - 2, ":",
												SECOND(`flugbuch`.`startzeit`)
											)
											AND
											`t`.`startzeit` <= CONCAT(
												HOUR(`flugbuch`.`startzeit`), ":",
												MINUTE(`flugbuch`.`startzeit`) + 2, ":",
												SECOND(`flugbuch`.`startzeit`)
											)
										)
									) AND `t`.`startart` = 2
								LIMIT 1
							)
							WHEN ((`flugbuch`.`preiskategorie` = "FR") AND (`mitglieder_2`.`nachname` IS NULL)) THEN
								"F-Schlepp"
							WHEN ((`mitglieder_2`.`nachname` IS NULL) AND (`flugbuch`.`besatzung2` IS NOT NULL)) THEN
								CONCAT("9999", UPPER(`flugbuch`.`besatzung2`))
							ELSE
								`mitglieder_2`.`nachname`
						END AS `begleiter`,
						`startarten`.`kbez` AS `startart`,
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
						`flugbuch`.`startzeit` AS `startzeit`,
						`flugbuch`.`landezeit` AS `landezeit`,
						`flugbuch`.`flugzeit` AS `flugzeit`,
						`flugbuch`.`landungen` AS `landungen`,
						`flugbuch`.`bemerkungen` AS `bemerkungen`
					FROM
						`flugbuch`
					INNER JOIN
						`flugzeuge` ON `flugbuch`.`luftfahrzeug` = `flugzeuge`.`kennzeichen`
					LEFT JOIN
						`mitglieder` AS `mitglieder_1` ON `flugbuch`.`besatzung1` LIKE CONCAT("%%", `mitglieder_1`.`ameavia`, "%%")
					LEFT JOIN
						`mitglieder` AS `mitglieder_2` ON `flugbuch`.`besatzung2` LIKE CONCAT("%%", `mitglieder_2`.`ameavia`, "%%")
					INNER JOIN
						`startarten` ON `startarten`.`id` = `flugbuch`.`startart`
					INNER JOIN
						`flugzeugtyp` ON `flugzeugtyp`.`id` = `flugzeuge`.`typ1`
					LEFT JOIN
						`flugplaetze` AS `flugplaetze_1` ON `flugbuch`.`startort` = `flugplaetze_1`.`ameavia`
					LEFT JOIN
						`flugplaetze` AS `flugplaetze_2` ON `flugbuch`.`landeort` = `flugplaetze_2`.`ameavia`
					WHERE
						`mitglieder_1`.`id` = %d AND
						`flugbuch`.`datum` > DATE_SUB(NOW(), INTERVAL 12 MONTH) AND
						`flugbuch`.`geloescht` = "N"
					UNION (
						SELECT
							`flugbuch`.`datum` AS `datum`,
							`flugzeugtyp`.`gruppe` AS `gruppe`,
							`flugzeuge`.`flugzeugtyp` AS `typ`,
							`flugzeuge`.`kennzeichen` AS `kennzeichen`,
							CASE
								WHEN ((`mitglieder_1`.`nachname` IS NULL) AND (`flugbuch`.`besatzung1` IS NOT NULL)) THEN
									CONCAT("9999", UPPER(`flugbuch`.`besatzung1`))
								ELSE
									`mitglieder_1`.`nachname`
							END AS `pilot`,
							`mitglieder_2`.`nachname` AS `begleiter`,
							`startarten`.`kbez` AS `startart`,
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
							`flugbuch`.`startzeit` AS `startzeit`,
							`flugbuch`.`landezeit` AS `landezeit`,
							`flugbuch`.`flugzeit` AS `flugzeit`,
							`flugbuch`.`landungen` AS `landungen`,
							`flugbuch`.`bemerkungen` AS `bemerkungen`
						FROM
							`flugbuch`
						INNER JOIN
							`flugzeuge` ON `flugbuch`.`luftfahrzeug` = `flugzeuge`.`kennzeichen`
						LEFT JOIN
							`mitglieder` AS `mitglieder_1` ON `flugbuch`.`besatzung1` LIKE CONCAT("%%", `mitglieder_1`.`ameavia`, "%%")
						LEFT JOIN
							`mitglieder` AS `mitglieder_2` ON `flugbuch`.`besatzung2` LIKE CONCAT("%%", `mitglieder_2`.`ameavia`, "%%")
						INNER JOIN
							`startarten` ON `startarten`.`id` = `flugbuch`.`startart`
						INNER JOIN
							`flugzeugtyp` ON `flugzeugtyp`.`id` = `flugzeuge`.`typ1`
						LEFT JOIN
							`flugplaetze` AS `flugplaetze_1` ON `flugbuch`.`startort` = `flugplaetze_1`.`ameavia`
						LEFT JOIN
							`flugplaetze` AS `flugplaetze_2` ON `flugbuch`.`landeort` = `flugplaetze_2`.`ameavia`
						WHERE
							`mitglieder_2`.`id` = %d AND
							(`flugbuch`.`preiskategorie` != "FM" AND
							`flugbuch`.`preiskategorie` != "FR" AND
							(`flugbuch`.`datum` > DATE_SUB(NOW(), INTERVAL 12 MONTH))) AND (
								(`flugzeuge`.`typ1` IN ("S1", "S2") AND FIND_IN_SET("C", `mitglieder_2`.`fachausweise`)) OR
								(`flugzeuge`.`typ1` IN ("MS") AND FIND_IN_SET("B", `mitglieder_2`.`fachausweise`)) OR
								(`flugzeuge`.`typ1` IN ("M1", "M2", "M3") AND FIND_IN_SET("A", `mitglieder_2`.`fachausweise`)) OR
								(`flugzeuge`.`typ1` = "UL" AND FIND_IN_SET("H", `mitglieder_2`.`fachausweise`))
							) AND
							`flugbuch`.`geloescht` = "N"
					)
				) AS `hauptflugbuch`
				ORDER BY
					`hauptflugbuch`.`datum` ASC,
					`hauptflugbuch`.`startzeit` ASC
			',
				$acb_nr, $acb_nr
			);

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Zählervariable initialisieren
			$i = 0;
			
			while ($zeile = mysql_fetch_object($db_erg)) {
				// Vor- und Nachname für den Pilot formatieren
				if (substr($zeile->pilot, 0, 4) == "9999") {
					$return[$i]['pilot'] = ucwords(strtolower(substr($zeile->pilot, 4, strlen($zeile->pilot) - 4)));
				} else {
					$return[$i]['pilot'] = $zeile->pilot;
				}
				// Vor- und Nachname für den Begleiter formatieren
				if (substr($zeile->begleiter, 0, 4) == "9999") {
					$return[$i]['begleiter'] = ucwords(strtolower(substr($zeile->begleiter, 4, strlen($zeile->begleiter) - 4)));
				} else {
					$return[$i]['begleiter'] = $zeile->begleiter;
				}
			
				// Daten übernehmen wie hinterlegt
				// Flugbuchdaten in das Rückgabe-Array einfügen
				$return[$i]['id']          = $i + 1;
				$return[$i]['datum']       = fromSqlDatum($zeile->datum);
				$return[$i]['gruppe']      = $zeile->gruppe;
				$return[$i]['typ']         = $zeile->typ;
				$return[$i]['kennzeichen'] = $zeile->kennzeichen;
				$return[$i]['startart']    = $zeile->startart;
				$return[$i]['startort']    = $zeile->startort;
				$return[$i]['landeort']    = $zeile->landeort;
				$return[$i]['startzeit']   = substr($zeile->startzeit, 0, 5);
				$return[$i]['landezeit']   = substr($zeile->landezeit, 0, 5);
				$return[$i]['flugzeit']    = $zeile->flugzeit;
				$return[$i]['landungen']   = $zeile->landungen;
				$return[$i]['bemerkungen'] = $zeile->bemerkungen;
				
				// Zähler erhöhen
				$i++;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Tabellenansicht
			return $return;
		}
	}
	
	/*
	 * drawLeftInformation()
	 *
	 * zeichnet die linke Spalte des Trainingsbarometers
	 * inklusive deren als Text dargestellt Informationen
	 *
	 * @params object $ref_pdf
	 */
	if (!function_exists('drawLeftInformation')) {
		function drawLeftInformation(& $ref_pdf)
		{
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Arial', 'B', 10);
			// Überschrift für den ersten linken Block festlegen
			$ref_pdf->Text(15, 63, utf8_decode('Wie finde ich meinen Trainingsstand?'));
			
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Arial', '', 8);
			// der linke erste Block wird definiert
			$ref_pdf->Text(15,  69, utf8_decode('Der Trainingsstand hängt ab von der Anzahl der'));
			$ref_pdf->Text(15,  73, utf8_decode('Starts und Flugstunden in den letzten 6 Monaten.'));
			$ref_pdf->Text(15,  80, utf8_decode('Verbinde die Anzahl der Starts mit der Anzahl der'));
			$ref_pdf->Text(15,  84, utf8_decode('Flugstunden in diesem Zeitraum. Die Mitte der Ver-'));
			$ref_pdf->Text(15,  89, utf8_decode('bildungslinie kennzeichnet den Farbbereich des'));
			$ref_pdf->Text(15,  93, utf8_decode('aktuellen Trainingsstandes.'));
			$ref_pdf->Text(15,  99, utf8_decode('Beispiel:'));
			$ref_pdf->Text(30,  99, utf8_decode('(gestrichelte Linie)'));
			$ref_pdf->Text(30, 103, utf8_decode('25 Flugstunden und 10 Starts'));
			$ref_pdf->Text(15, 107, utf8_decode('Ergebnis:'));
			$ref_pdf->Text(30, 107, utf8_decode('Trotz der Flugzeit liegt der Trainings-'));
			$ref_pdf->Text(30, 111, utf8_decode('stand im gelben Bereich!'));
			
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Arial', 'B', 10);
			// Überschrift für den ersten linken Block festlegen
			$ref_pdf->Text(15, 131, utf8_decode('Was ist mit der Flugerfahrung?'));

			// Schriftgrad einstellen
			$ref_pdf->SetFont('Arial', '', 8);
			
			// der linke zweite Block wird definiert
			$ref_pdf->Text(15, 137, utf8_decode('Die Gesamtzahl aller Starts und Flugstunden be-'));
			$ref_pdf->Text(15, 141, utf8_decode('schreibt die'));
			$ref_pdf->Text(30, 146, utf8_decode('FLUGERFAHRUNG.'));
			$ref_pdf->Text(15, 151, utf8_decode('Erfahrungen sind die Grundlage für schnell und'));
			$ref_pdf->Text(15, 155, utf8_decode('richtige Entscheidungen.'));

			// Schriftgrad einstellen
			$ref_pdf->SetFont('Arial', 'B', 10);
			// Überschrift für den ersten linken Block festlegen
			$ref_pdf->Text(15, 175, utf8_decode('Was ist mit meiner Übung?'));

			// Schriftgrad einstellen
			$ref_pdf->SetFont('Arial', '', 8);
			
			// der linke dritte Block wird definiert
			$ref_pdf->Text(15, 181, utf8_decode('Um sicher zu fliegen braucht man aber Übung. Der'));
			$ref_pdf->Text(25, 186, utf8_decode('AKTUELLE TRAININGSSTAND'));
			$ref_pdf->Text(15, 191, utf8_decode('hängt von der Anzahl der Starts und den Flug-'));
			$ref_pdf->Text(15, 195, utf8_decode('stunden in der letzten Zeit ab.'));
			$ref_pdf->Text(15, 202, utf8_decode('Die Fliegerei ist bei uns eine saisonabhängige'));
			$ref_pdf->Text(15, 206, utf8_decode('Sportart, deshalb bezieht sich das Trainingsbaro-'));
			$ref_pdf->Text(15, 210, utf8_decode('meter auf einen Zeitraum von 6 Monaten.'));
			$ref_pdf->Text(15, 217, utf8_decode('Erinnert sei an die 90-Tage-Regelung der'));
			$ref_pdf->Text(15, 221, utf8_decode('LuftPersV, § 122, wenn man Gäste fliegen will!'));
		}
	}
	
	/*
	 * drawTrainingsbarometer()
	 *
	 * zeichnet das Trainingsbarometer ansich
	 *
	 * @params object  $ref_pdf
	 */
	if (!function_exists('drawTrainingsbarometer')) {
		function drawTrainingsbarometer(& $ref_pdf)
		{
			// Das Trainingsbarometer zeichnen
			// grüner Bereich
			$ref_pdf->SetFillColor(0, 165, 79);
			$ref_pdf->Rect(95, 60, 27, 60, 'FD');
			// gelber Bereich
			$ref_pdf->SetFillColor(255, 186, 0);
			$ref_pdf->Rect(95, 120, 27, 60, 'FD');
			// roter Bereich
			$ref_pdf->SetFillColor(238, 28, 35);
			$ref_pdf->Rect(95, 180, 27, 60, 'FD');
			
			// horizontale Linie(n) zeichnen
			// auf der linken Seite (Flugstunden)
			$ref_pdf->Line(93,  60, 124,  60); // 30 h
			$ref_pdf->Line(94,  90,  96,  90); // 25 h
			$ref_pdf->Line(94, 120,  95, 120); // 20 h
			$ref_pdf->Line(94, 150,  96, 150); // 15 h
			$ref_pdf->Line(94, 180,  95, 180); // 10 h
			$ref_pdf->Line(94, 210,  96, 210); //  5 h
			$ref_pdf->Line(93, 240, 124, 240); //  0 h
			
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Arial', 'B', 9);
			// Text festlegen (Flugstunden)
			$ref_pdf->Text(88.5,  61, '30');
			$ref_pdf->Text(89.5,  91, '25');
			$ref_pdf->Text(89.5, 121, '20');
			$ref_pdf->Text(89.5, 151, '15');
			$ref_pdf->Text(89.5, 181, '10');
			$ref_pdf->Text(91.3, 211, '5');

			// horizontale Linie(n) zeichnen
			// auf der rechten Seite (Starts)
			$ref_pdf->Line(121,  68, 123,  68); // 40 Starts
			$ref_pdf->Line(121,  90, 123,  90); // 35 Starts
			$ref_pdf->Line(121, 111, 123, 111); // 30 Starts
			$ref_pdf->Line(121, 133, 123, 133); // 25 Starts
			$ref_pdf->Line(121, 154, 123, 154); // 20 Starts
			$ref_pdf->Line(121, 175, 123, 175); // 15 Starts
			$ref_pdf->Line(121, 196, 123, 196); // 10 Starts
			$ref_pdf->Line(121, 218, 123, 218); //  5 Starts
			
			// Text festlegen (Starts)
			$ref_pdf->Text(124,  69, '40');
			$ref_pdf->Text(124,  91, '35');
			$ref_pdf->Text(124, 112, '30');
			$ref_pdf->Text(124, 134, '25');
			$ref_pdf->Text(124, 155, '20');
			$ref_pdf->Text(124, 176, '15');
			$ref_pdf->Text(124, 197, '10');
			$ref_pdf->Text(124, 219, '5');
			
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Arial', 'B', 11);
			$ref_pdf->Text(78, 255, utf8_decode('Bin ich fit für den nächsten Start?'));
		}
	}
	
	/*
	 * drawRightInformation()
	 *
	 * zeichnet die rechte Spalte des Trainingsbarometers
	 * inklusive deren als Text dargestellt Informationen
	 *
	 * @params object $ref_pdf
	 */
	if (!function_exists('drawRightInformation')) {
		function drawRightInformation(& $ref_pdf)
		{
			// Linienbreite einstellen, 0.2 mm
			$ref_pdf->SetLineWidth(0.2);
		
			// grüner Bereich
			$ref_pdf->SetFillColor(0, 165, 79);
			$ref_pdf->Rect(135, 60, 64, 5, 'FD');

			// Schriftgrad und -farbe einstellen
			$ref_pdf->SetTextColor(0, 0, 0);
			$ref_pdf->SetFont('Arial', 'B', 8);
			$ref_pdf->Text(153, 63.6, utf8_decode('GRÜNER BEREICH'));
			$ref_pdf->SetFont('Arial', 'B', 10);
			$ref_pdf->Text(136, 70, utf8_decode('Der Übungsstand ist gut'));
			$ref_pdf->SetFont('Arial', '', 9);
			$ref_pdf->Text(136, 75, utf8_decode('TROTZDEM VORSICHT!'));
			// Schriftgrad und -farbe einstellen
			$ref_pdf->SetTextColor(0, 165, 79);
			$ref_pdf->SetFont('Arial', '', 8);
			$ref_pdf->Text(136,    82, utf8_decode('Geübte Piloten machen folgende Fehler:'));
			$ref_pdf->Text(136,    87, utf8_decode('- Flugzeug fehlerhaft ausgerüstet!'));
			$ref_pdf->Text(136,    91, utf8_decode('- Mangelhafter Cockpitcheck!'));
			$ref_pdf->Text(136,    95, utf8_decode('- Fehlverhalten bei Startunterbrechungen!'));
			$ref_pdf->Text(136,    99, utf8_decode('- Fehler bei der Landeeinteilung!'));
			$ref_pdf->Text(137.5, 103, utf8_decode('(vorallem bei Außenlandungen)'));
			
			// gelber Bereich
			$ref_pdf->SetFillColor(255, 186, 0);
			$ref_pdf->Rect(135, 120, 64, 5, 'FD');

			// Schriftgrad und -farbe einstellen
			$ref_pdf->SetTextColor(0, 0, 0);
			$ref_pdf->SetFont('Arial', 'B', 8);
			$ref_pdf->Text(153, 123.6, utf8_decode('GELBER BEREICH'));
			$ref_pdf->SetFont('Arial', 'B', 10);
			$ref_pdf->Text(136, 130, utf8_decode('Mehr Übung könnte nicht schaden'));
			$ref_pdf->SetFont('Arial', '', 9);
			$ref_pdf->Text(136, 135, utf8_decode('UNERWARTETE EREIGNISSE DECKT'));
			$ref_pdf->Text(136, 139, utf8_decode('DER ÜBUNGSSTAND NICHT MEHR AB!'));
			// Schriftgrad und -farbe einstellen
			$ref_pdf->SetTextColor(255, 186, 0);
			$ref_pdf->SetFont('Arial', '', 8);
			$ref_pdf->Text(136,   146, utf8_decode('Vorsicht ist geboten beim Start:'));
			$ref_pdf->Text(136,   151, utf8_decode('- In unbekannten Landschaftsregionen!'));
			$ref_pdf->Text(137.5, 155, utf8_decode('(z.B. in den Alpen)'));
			$ref_pdf->Text(136,   159, utf8_decode('- Auf unbekannten Fluggeländen!'));
			$ref_pdf->Text(136,   163, utf8_decode('- Auf selten geflogenen Flugzeugmustern!'));
			$ref_pdf->Text(136,   167, utf8_decode('- In einer selten durchgeführten Startart!'));
			
			// roter Bereich
			$ref_pdf->SetFillColor(238, 28, 35);
			$ref_pdf->Rect(135, 180, 64, 5, 'FD');

			// Schriftgrad und -farbe einstellen
			$ref_pdf->SetTextColor(0, 0, 0);
			$ref_pdf->SetFont('Arial', 'B', 8);
			$ref_pdf->Text(155, 183.6, utf8_decode('ROTER BEREICH'));
			$ref_pdf->SetFont('Arial', 'B', 10);
			$ref_pdf->Text(136, 190, utf8_decode('Übung tut Not'));
			$ref_pdf->SetFont('Arial', '', 9);
			$ref_pdf->Text(136, 195, utf8_decode('FLIEGEN KANN ZUM RISIKO WERDEN!'));
			// Schriftgrad und -farbe einstellen
			$ref_pdf->SetTextColor(238, 28, 35);
			$ref_pdf->SetFont('Arial', '', 8);
			$ref_pdf->Text(136,   202, utf8_decode('Für ungeübte Piloten gilt:'));
			$ref_pdf->Text(136,   207, utf8_decode('- Die ersten Starts nach einer längeren Pause nur'));
			$ref_pdf->Text(137.5, 211, utf8_decode('mit vertrauten Mustern und bei unkritischen'));
			$ref_pdf->Text(137.5, 215, utf8_decode('Wetterlagen durchführen!'));
			$ref_pdf->Text(136,   219, utf8_decode('- Fall der letzte Start mehr als 90 Tage zurück-'));
			$ref_pdf->Text(137.5, 223, utf8_decode('liegt, ist Training mit einem Fluglehrer der ein-'));
			$ref_pdf->Text(137.5, 227, utf8_decode('fachste Weg zu einem guten Übungsstand!'));
			// Schriftfarbe einstellen
			$ref_pdf->SetTextColor(0, 0, 0);
			$ref_pdf->Text(136, 233, utf8_decode('Gute Flugleher bieten gern ihre Hilfestellung an!'));
		}
	}
	
	/*
	 * drawTrainingsstand()
	 *
	 * zeichnet die Linie des aktuellen Trainings-
	 * standes, je Mitglied, in das Trainingsbarometer
	 *
	 * @params object $ref_pdf
	 * @params integer $acb_nr
	 */
	if (!function_exists('drawTrainingsstand')) {
		function drawTrainingsstand(& $ref_pdf, $acb_nr)
		{
			// maximale und minimale X-/Y-Werte
			// für die Anzeige der Starts und Stunden festlegen
			$max_y =  60;
			$min_y = 240;

			// Flugstunden und Starts ermitteln
			$training = getFluegeTrainingsbarometer($acb_nr);
			$stunden = $training['flugzeit'];
			$starts  = $training['landungen'];
			
			// Stunden auf max. Summe prüfen
			//$stunden = ($stunden > 30) ? 30 : $stunden;
			// Starts auf max. Summe prüfen
			//$starts = ($starts > 42) ? 42 : $starts;

			// linken Y-Wert für die Stunden ermitteln
			$y1 = $min_y - ($stunden * 180 / 30);
			// rechten Y-Wert für die Starts ermitteln
			$y2 = $min_y - ($starts * 180 / 42);
			
			// Linienbreite einstellen, 1 mm
			$ref_pdf->SetDash(3, 3);
			$ref_pdf->SetLineWidth(1);
			// Linie für Trainingsstand zeichnen
			$ref_pdf->Line(95, $y1, 122, $y2);
			$ref_pdf->SetFillColor(255, 255, 255);
			$ref_pdf->Rect(93, 0, 31, 59.5, 'F');
			
			// Mitte der Linie ermitteln
			$mitte = ($y1 + $y2) / 2;
			// Mitte darf nicht aus der Grafik verschwinden
			$mitte = ($mitte < 60) ? 60 : $mitte;
			
			// Linienbreite und Hintergrundfarbe einstellen
			$ref_pdf->SetDash(0, 0);
			$ref_pdf->Circle(108.5, $mitte, 2, 'FD');
		}
	}

	/*
	 * pdf_header()
	 *
	 * erstellt die Kopfzeile des übergebenen PDF-Dokumentes
	 *
	 * @params object $ref_pdf
	 */
	if (!function_exists('pdf_header')) {
		function pdf_header(& $ref_pdf)
		{
			// Linienbreite einstellen, 0.1 mm
			$ref_pdf->SetLineWidth(0.1);

			// horizontale Linie(n) zeichnen
			$ref_pdf->Line(14,    18, 289,   18);
			$ref_pdf->Line(41,  23.5, 131, 23.5);
			$ref_pdf->Line(136, 23.5, 216, 23.5);
			$ref_pdf->Line(14,    32, 289,   32);
			
			// vertikale Linie(n) zeichnen
			$ref_pdf->Line(24,    18,  24,   32);
			$ref_pdf->Line(41,    18,  41,   32);
			$ref_pdf->Line(62,  23.5,  62,   32);
			$ref_pdf->Line(77,    18,  77,   32);
			$ref_pdf->Line(104, 23.5, 104,   32);
			$ref_pdf->Line(131,   18, 131,   32);
			$ref_pdf->Line(136,   18, 136,   32);
			$ref_pdf->Line(166, 23.5, 166,   32);
			$ref_pdf->Line(196,   18, 196,   32);
			$ref_pdf->Line(206, 23.5, 206,   32);
			$ref_pdf->Line(216,   18, 216,   32);
			$ref_pdf->Line(227,   18, 227,   32);
			$ref_pdf->Line(234,   18, 234,   32);
			
			// Schriftart hinzufügen
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Arial Narrow', '', 8);
			// Überschriften hinzufügen
			$ref_pdf->Text(17,    24, utf8_decode('Lfd.'));
			$ref_pdf->Text(17.5,  28, utf8_decode('Nr.'));
			$ref_pdf->Text(30,    24, utf8_decode('Flug-'));
			$ref_pdf->Text(29,    28, utf8_decode('Datum'));
			$ref_pdf->Text(47.5,  29, utf8_decode('Muster'));
			$ref_pdf->Text(66,    29, utf8_decode('Kennz.'));
			$ref_pdf->Text(88,    29, utf8_decode('Pilot'));
			$ref_pdf->Text(113,   29, utf8_decode('Begleiter'));
			$ref_pdf->Text(148,   29, utf8_decode('Start'));
			$ref_pdf->Text(176,   29, utf8_decode('Landung'));
			$ref_pdf->Text(198.5, 29, utf8_decode('Start'));
			$ref_pdf->Text(209,   29, utf8_decode('Ldg.'));
			$ref_pdf->Text(219,   24, utf8_decode('Flug-'));
			$ref_pdf->Text(218.5, 28, utf8_decode('dauer'));
			$ref_pdf->Text(228.3, 24, utf8_decode('Anz.'));
			$ref_pdf->Text(228.5, 28, utf8_decode('Ldg.'));
			$ref_pdf->Text(235.5, 28, utf8_decode('(Bestätigungen, Prüfungen, bes. Vorkomnisse)'));
			$ref_pdf->TextWithDirection(134.5, 29.5, utf8_decode('Startart'), 'U');
			
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Arial Narrow', '', 11);
			// weitere Überschriften hinzufügen
			$ref_pdf->Text(52,    22, utf8_decode('Flugzeug'));
			$ref_pdf->Text(97,    22, utf8_decode('Besatzung'));
			$ref_pdf->Text(163,   22, utf8_decode('Ort'));
			$ref_pdf->Text(201,   22, utf8_decode('Uhrzeit'));
			$ref_pdf->Text(235.5, 24, utf8_decode('Bemerkungen'));
		}
	}
	
	/*
	 * pdf_footer()
	 *
	 * erstellt die Fusszeile des übergebenen PDF-Dokumentes
	 *
	 * @params object $ref_pdf
	 */
	if (!function_exists('pdf_footer')) {
		function pdf_footer(& $ref_pdf)
		{
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Arial Narrow', '', 9);
			// Seitenzahlen hinzufügen
			$ref_pdf->Text(140, 205, sprintf('Seite: %d von {nb}', $ref_pdf->PageNo()));
		}
	}
	
	
	// Laufzeit des Skriptes setzen
	set_time_limit(1000);
	
	// PDF-Bibliothek einbinden
	require_once('./pdf/fpdf.php');
	
	// alle Mitgliedernummern für die spätere Auslese ermitteln
	$mitgl_nr = getMitgliedsnummern();
//	$mitgl_nr[]['acb_nr'] = 5315;

	foreach ($mitgl_nr as $mitglied) {
		// das Flugbuch des Mitglieds ermitteln
		$data = getFlugbuchPdf($mitglied['acb_nr']);
		
		// es wird geprüft, ob überhaupt Daten zur Mitgliedsnummer
		// und dem aktuell ausgewählten Zeitraum vorliegen
		if (!empty($data)) {
			// neues PDF-Dokument erzeugen
			$pdf = new FPDF('L', 'mm', 'A4');
			
			// ... entspricht dem Aufruf von
			$pdf->AliasNbPages('{nb}');
			
			// Automatischen Seitenumbruch deaktivieren
			$pdf->SetAutoPageBreak(false);
			
			// Seitenabstand definieren
			$pdf->SetMargins(14, 18, 10);

			// ******************************************************* SEITE 1 ******************************************************* //
			// Seite hinzufügen
			$pdf->AddPage();

			// Schriftart hinzufügen
			$pdf->AddFont('Arial Narrow', '', 'c0bd260bcc2709f99785311b28a9541f_arialn.php');
			$pdf->AddFont('Arial Narrow', 'B', 'b297a3df7cb283d4eed768a69409e8e1_arialnb.php');
			
			// Kopfzeile auf der aktuellen Seite hinzufügen
			pdf_header($pdf);
			
			// Hilfsvariablen initialisieren
			$segelflug   = array('flugzeit' => 0, 'landungen' => 0);
			$motorsegler = array('flugzeit' => 0, 'landungen' => 0);
			$motorflug   = array('flugzeit' => 0, 'landungen' => 0);
			$ultraleicht = array('flugzeit' => 0, 'landungen' => 0);
			$y = 32;
						
			foreach ($data as $zeile) {
				// Koordinaten setzen
				$pdf->SetXY(14, $y);
				
				// Schriftgrad einstellen
				$pdf->SetFont('Arial Narrow', '', 10);
				// Linienbreite einstellen, 0.1 mm
				$pdf->SetLineWidth(0.1);
				
				// Füllfarbe auf weiß setzen
				$pdf->SetFillColor(255, 255, 255);
				
				$pdf->Cell(10, 5, $zeile['id'],                  'TRB', 0, 'R', 1);
				$pdf->Cell(17, 5, $zeile['datum'],                   1, 0, 'C', 1);
				$pdf->Cell(21, 5, $zeile['typ'],                     1, 0, 'L', 1);
				$pdf->Cell(15, 5, $zeile['kennzeichen'],             1, 0, 'L', 1);
				$pdf->Cell(27, 5, $zeile['pilot'],                   1, 0, 'L', 1);
				$pdf->Cell(27, 5, $zeile['begleiter'],               1, 0, 'L', 1);
				$pdf->Cell(5,  5, $zeile['startart'],                1, 0, 'C', 1);
				$pdf->Cell(30, 5, $zeile['startort'],                1, 0, 'L', 1);
				$pdf->Cell(30, 5, $zeile['landeort'],                1, 0, 'L', 1);
				$pdf->Cell(10, 5, $zeile['startzeit'],               1, 0, 'C', 1);
				$pdf->Cell(10, 5, $zeile['landezeit'],               1, 0, 'C', 1);
				$pdf->Cell(11, 5, minutesToTime($zeile['flugzeit']), 1, 0, 'C', 1);
				$pdf->Cell(7,  5, $zeile['landungen'],               1, 0, 'C', 1);
				$pdf->Cell(55, 5, $zeile['bemerkungen'],         'LTB', 0, 'L', 1);
				
				// Flüge aufsummieren
				switch ($zeile['gruppe']) {
					case SEGELFLUG :
						// Segelflug
						$segelflug['flugzeit']  += $zeile['flugzeit'];
						$segelflug['landungen'] += $zeile['landungen'];
						break;
					case MOTORSEGLER :
						// Motorsegler
						$motorsegler['flugzeit']  += $zeile['flugzeit'];
						$motorsegler['landungen'] += $zeile['landungen'];
						break;
					case MOTORFLUG :
						// Motorflug
						$motorflug['flugzeit']  += $zeile['flugzeit'];
						$motorflug['landungen'] += $zeile['landungen'];
						break;
					case ULTRALEICHT :
						// Ultraleicht
						$ultraleicht['flugzeit']  += $zeile['flugzeit'];
						$ultraleicht['landungen'] += $zeile['landungen'];
						break;
				}
		
				// Y-Position erhöhen
				$y += 5;
				
				if ($y >= 182) {
					// Fusszeile auf der aktuellen Seite hinzufügen
					pdf_footer($pdf);
					// Seite hinzufügen
					$pdf->AddPage();
					
					// Kopfzeile auf der aktuellen Seite hinzufügen
					pdf_header($pdf);
					
					// Y-Wert zurücksetzen
					$y = 32;
				}
			}

			// Schriftgrad einstellen
			$pdf->SetFont('Arial Narrow', '', 10);
			
			// Summenbildung ans Ende des Flugbuches setzen
			// Rechtechte der Summen, der einzelnen Sparten, zeichnen
			$pdf->Rect(14,  $y + 3, 49, 13, 'D');
			$pdf->Rect(63,  $y + 3, 52, 13, 'D');
			$pdf->Rect(115, $y + 3, 49, 13, 'D');
			$pdf->Rect(164, $y + 3, 49, 13, 'D');
			
			// SEGELFLUG
			// Texte eintragen
			$pdf->Text(17, $y +    9, utf8_decode('Segelflugzeit:'));
			$pdf->Text(19, $y + 13.5, utf8_decode('Segelstarts:'));
			// Werte eintragen
			$pdf->Text(36, $y +    9, minutesToTime($segelflug['flugzeit']));
			$pdf->Text(36, $y + 13.5, $segelflug['landungen']);
			
			// MOTORSEGLER
			// Texte eintragen
			$pdf->Text(68.6, $y +    9, utf8_decode('Motorsegelzeit:'));
			$pdf->Text(66,   $y + 13.5, utf8_decode('Motorsegelstarts:'));
			// Werte eintragen
			$pdf->Text(90, $y +    9, minutesToTime($motorsegler['flugzeit']));
			$pdf->Text(90, $y + 13.5, $motorsegler['landungen']);
			
			// MOTORFLUG
			// Texte eintragen
			$pdf->Text(118,   $y +    9, utf8_decode('Motorflugzeit:'));
			$pdf->Text(120.1, $y + 13.5, utf8_decode('Motorstarts:'));
			// Werte eintragen
			$pdf->Text(137.5, $y +    9, minutesToTime($motorflug['flugzeit']));
			$pdf->Text(137.5, $y + 13.5, $motorflug['landungen']);
			
			// ULTRALEICHT
			// Texte eintragen
			$pdf->Text(169.8, $y +    9, utf8_decode('UL-Zeit:'));
			$pdf->Text(167,   $y + 13.5, utf8_decode('UL-Starts:'));
			// Werte eintragen
			$pdf->Text(182.5, $y +    9, minutesToTime($ultraleicht['flugzeit']));
			$pdf->Text(182.5, $y + 13.5, $ultraleicht['landungen']);
			
			// Linienbreite einstellen, 0.4 mm
			$pdf->SetLineWidth(0.4);
			// letztes Rechtecht mit den Gesamtsummen zeichnen
			$pdf->Rect(216, $y + 3, 50, 13, 'D');
			
			// Schriftgrad einstellen
			$pdf->SetFont('Arial Narrow', 'B', 10);
			
			// Summe aller Landungen
			$gesamt_starts = $segelflug['landungen']   +
							 $motorsegler['landungen'] +
							 $motorflug['landungen']   +
							 $ultraleicht['landungen'];
			// Summe aller Flugzeiten
			$gesamt_zeit = $segelflug['flugzeit']   +
						   $motorsegler['flugzeit'] +
						   $motorflug['flugzeit']   +
						   $ultraleicht['flugzeit'];
			
			// GESAMTFLUGZEIT -UND STARTS
			// Texte eintragen
			$pdf->Text(222, $y +    9, utf8_decode('Gesamtzeit:'));
			$pdf->Text(219, $y + 13.5, utf8_decode('Gesamtstarts:'));
			// Werte eintragen
			$pdf->Text(240, $y +    9, minutesToTime($gesamt_zeit));
			$pdf->Text(240, $y + 13.5, $gesamt_starts);
			
			if ($y > 35) {
				// letzte Fusszeile auf der aktuellen Seite hinzufügen
				pdf_footer($pdf);
			}
			
			// ******************************************************* SEITE 1 ******************************************************* //


			// ******************************************************* SEITE 2 ******************************************************* //

			// eine neue Seite wird angelegt, welches das Trainingsbarometer enthält
			// eine neue Seite anlegen im Hochformat
			$pdf->AddPage('P');
			
			// Seitenabstand definieren
			$pdf->SetMargins(15, 20, 15);
			
			// linke Spalte des Trainingsbarometers zeichnen
			drawLeftInformation($pdf);
			// Trainingsbarometer ansich zeichnen
			drawTrainingsbarometer($pdf);
			// rechte Spalte des Trainingsbarometers zeichnen
			drawRightInformation($pdf);
			
			// Linie ins Trainingsbarometer zeichnen
			drawTrainingsstand($pdf, $mitglied['acb_nr']);
			
			// Bild einfügen
			$pdf->Image('img/trainingsbarometer.png', 25, 10);
			// Schriftgrad einstellen
			$pdf->SetFont('Arial', 'B', 9);
			$pdf->Text(90, 48, utf8_decode('In den letzten 6 Monaten'));
			$pdf->SetFont('Arial', 'B', 8);
			$pdf->Text(85,  53, utf8_decode('Flugstunden'));
			$pdf->Text(123, 53, utf8_decode('Starts'));
			
			// Bild einfügen
			$pdf->Image('img/acb_logo_gross.jpg', 20, 263, 25, 25);
			// Schriftgrad einstellen
			$pdf->SetFont('Arial', 'B', 12);
			$pdf->Text(53, 274, utf8_decode('DER AERO-CLUB BUTZBACH E.V. WÜNSCHT EINEN GUTEN'));
			$pdf->Text(53, 280, utf8_decode('START IN DIE NEUE FLUGSAISON!'));
		
			// ******************************************************* SEITE 2 ******************************************************* //
			
			// anzulegende Verzeichnisse
			$dir_jahr  = sprintf('flugbuch/%s', date('Y'));
			$dir_monat = sprintf('flugbuch/%s/%s', date('Y'), date('m'));

			// JAHR => prüfen, ob das Verzeichnis bereits existiert
			if (!is_dir($dir_jahr)) {
				umask(0000);
				// Verzeichnis für das Jahr erstellen
				mkdir($dir_jahr, 0777);
			}

			// MONAT => prüfen, ob das Verzeichnis bereits existiert
			if (!is_dir($dir_monat)) {
				umask(0000);
				// Verzeichnis für das Jahr erstellen
				mkdir($dir_monat, 0777);
			}

			// PDF-Dokument ausgeben
			$pdf->Output(sprintf('flugbuch/%s/%s/%s.pdf', date('Y'), date('m'), md5($mitglied['acb_nr'])), 'F');
		}
	}

?>