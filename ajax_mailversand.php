<?php

	// allgemeine Funktionen einbinden
	include_once('./functions.php');

	/*
	 * getMailtext()
	 *
	 * holt den letzten aktuellen Mailtext aus der Datenbank
	 *
	 * @return string $return
	 */
	if (!function_exists('getMailtext')) {
		function getMailtext()
		{
			// Rückgabe-Variable definieren
			$return = '';
		
			// SQL-Befehl zurecht fuddeln,
			// prüfen ob der Datensatz bereits vorhanden ist
			$sql = sprintf('
				SELECT
					`mailtext`
				FROM
					`mailtemplate`
				WHERE
					`opt` = "FB"
				ORDER BY
					`id` DESC
				LIMIT 1
			');
				
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
				// Mailtext auslesen
				$return = $zeile->mailtext;
			}
			
			// Verbindung zur Datenbank schließen
			mysql_close($db_erg);
			
			// den gefundenen Mailtext zurückgeben
			return $return;
		}
	}
	
	/*
	 * getListeFlugstatistik()
	 *
	 * es werden die Flugstunden und die Anzahl der Landungen der
	 * aktuellen Mitgliedes, anhand des übergebenen Zeitraumes in
	 * Monaten, ermittelt und als Array zurückgegeben
	 *
	 * @params integer $zeitraum
	 * @params integer $acb_nr
	 * @return array   $return
	 */
	if (!function_exists('getListeFlugstatistik')) {
		function getListeFlugstatistik($zeitraum, $acb_nr)
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
			// Liste der Flugstatistik ermitteln
			$sql = sprintf('
				SELECT
					`t`.`acb_nr` AS `acb_nr`,
					`t`.`nachname` AS `nachname`,
					`t`.`vorname` AS `vorname`,
					TIME_FORMAT(SEC_TO_TIME(SUM(`t`.`flugzeit`) * 60), "%%k:%%i") AS `flugzeit`,
					SUM(`t`.`landungen`) AS `landungen`
				FROM
				(
					SELECT
						`a`.`acb_nr` AS `acb_nr`,
						`a`.`nachname` AS `nachname`,
						`a`.`vorname` AS `vorname`,
						SUM(`a`.`flugzeit`) AS `flugzeit`,
						SUM(`a`.`landungen`) AS `landungen`
					FROM (
						SELECT
							`mitglieder`.`id` AS `acb_nr`,
							`mitglieder`.`nachname` AS `nachname`,
							`mitglieder`.`vorname` AS `vorname`,
							SUM(`flugbuch`.`flugzeit`) AS `flugzeit`,
							COUNT(*) AS `landungen`
						FROM
							`flugbuch`
						INNER JOIN
							`mitglieder` ON `flugbuch`.`besatzung1` LIKE CONCAT("%%", `mitglieder`.`ameavia`, "%%")
						WHERE
							(`flugbuch`.`datum` > DATE_SUB(NOW(), INTERVAL %d MONTH))
						GROUP BY
							`mitglieder`.`id`
						UNION ALL (
							SELECT
								`mitglieder`.`id` AS `acb_nr`,
								`mitglieder`.`nachname` AS `nachname`,
								`mitglieder`.`vorname` AS `vorname`,
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
								(`flugbuch`.`datum` > DATE_SUB(NOW(), INTERVAL %d MONTH))) AND (
									(`flugzeuge`.`typ1` IN ("S1", "S2") AND FIND_IN_SET("C", `mitglieder`.`fachausweise`)) OR
									(`flugzeuge`.`typ1` IN ("MS") AND FIND_IN_SET("B", `mitglieder`.`fachausweise`)) OR
									(`flugzeuge`.`typ1` IN ("M1", "M2", "M3") AND FIND_IN_SET("A", `mitglieder`.`fachausweise`)) OR
									(`flugzeuge`.`typ1` = "UL" AND FIND_IN_SET("H", `mitglieder`.`fachausweise`))
								)
							GROUP BY
								`mitglieder`.`id`
						)
					) AS `a`
					GROUP BY
						`a`.`acb_nr`
					UNION ALL (
						SELECT
							`b`.`acb_nr` AS `acb_nr`,
							`b`.`nachname` AS `nachname`,
							`b`.`vorname` AS `vorname`,
							(HOUR(SEC_TO_TIME(SUM(TIME_TO_SEC(`b`.`flugzeit`)))) * 60) +
							MINUTE(SEC_TO_TIME(SUM(TIME_TO_SEC(`b`.`flugzeit`)))) AS `flugzeit`,
							SUM(`b`.`landungen`) AS `landungen`
						FROM (
							SELECT
								`hauptflugbuch`.`pilot` AS `acb_nr`,
								`mitglieder`.`nachname` AS `nachname`,
								`mitglieder`.`vorname` AS `vorname`,
								SEC_TO_TIME(SUM(TIME_TO_SEC(`hauptflugbuch`.`flugzeit`))) AS `flugzeit`,
								SUM(`hauptflugbuch`.`landungen`) AS `landungen`
							FROM
								`hauptflugbuch`
							INNER JOIN
								`mitglieder` ON `hauptflugbuch`.`pilot` = `mitglieder`.`id`
							WHERE
								(`hauptflugbuch`.`datum` > DATE_SUB(NOW(), INTERVAL %d MONTH))
							GROUP BY
								`hauptflugbuch`.`pilot`
							UNION ALL (
								SELECT
									`hauptflugbuch`.`begleiter` AS `acb_nr`,
									`mitglieder`.`nachname` AS `nachname`,
									`mitglieder`.`vorname` AS `vorname`,
									SEC_TO_TIME(SUM(TIME_TO_SEC(`hauptflugbuch`.`flugzeit`))) AS `flugzeit`,
									SUM(`hauptflugbuch`.`landungen`) AS `landungen`
								FROM
									`hauptflugbuch`
								INNER JOIN
									`flugzeuge` ON `hauptflugbuch`.`kennzeichen` = `flugzeuge`.`kennzeichen`
								INNER JOIN
									`mitglieder` ON `hauptflugbuch`.`begleiter` = `mitglieder`.`id`
								WHERE
									(`hauptflugbuch`.`art` != 8 AND
									(`hauptflugbuch`.`datum` > DATE_SUB(NOW(), INTERVAL %d MONTH))) AND (
										(`flugzeuge`.`typ1` IN ("S1", "S2") AND FIND_IN_SET("C", `mitglieder`.`fachausweise`)) OR
										(`flugzeuge`.`typ1` IN ("MS") AND FIND_IN_SET("B", `mitglieder`.`fachausweise`)) OR
										(`flugzeuge`.`typ1` IN ("M1", "M2", "M3") AND FIND_IN_SET("A", `mitglieder`.`fachausweise`)) OR
										(`flugzeuge`.`typ1` = "UL" AND FIND_IN_SET("H", `mitglieder`.`fachausweise`))
									)
								GROUP BY
									`hauptflugbuch`.`begleiter`
							)
						) AS `b`
						GROUP BY
							`b`.`acb_nr`
					)
				) AS `t`
				WHERE
					`t`.`acb_nr` = %d
			',
				$zeitraum, $zeitraum,
				$zeitraum, $zeitraum,
				$acb_nr
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
		
			while ($zeile = mysql_fetch_object($db_erg)) {
				// die ermittelten Flugzeiten und die Anzahl der Starts
				// und Landungen des Mitglied in das Rückgabe-Array schreiben
				$return[] = array(
					'flugzeit'  => !empty($zeile->flugzeit)  ? $zeile->flugzeit  : '0:00',
					'landungen' => !empty($zeile->landungen) ? $zeile->landungen : '0'
				);
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);

			// Rückgabe der Flugstatistik
			return $return;
		}
	}
	
	
	
	// Laufzeit des Skriptes setzen
	set_time_limit(1000);
	
	// den Pfad zu den gespeicherten PDF-Abrechnungen ermitteln
	$dir_jahr  = date('Y');
	$dir_monat = date('m');
	// den Pfad zurechtfuddeln
	$pfad = sprintf('flugbuch/%s/%s/', $dir_jahr, $dir_monat);

	// Liste der eMailadressen ermitteln
	$email_adressen = getEmailadressen();

	// den Mailtext ermitteln
	$mailtext = getMailtext();

	// eMail-Adressen in einem Array verarbeiten
	foreach ($email_adressen as $email) {
		// anhand der gefundenen Mitgliedsnummer,
		// den Namen der zugehörigen PDF-Datei kreieren
		$pdf = sprintf('%s.pdf', md5($email['acb_nr']));

		// prüfen ob für das aktuelle Mitglied ein Flugbuch existiert
		if (file_exists($pfad . $pdf)) {
			// Variable zum Erfassen der Flugstatistik definieren
			$fluege = array();

			// Flüge der letzten 24 Monate je Mitglied ermitteln
			$alle_fluege = getListeFlugstatistik(24, $email['acb_nr']);
			
			// die Flüge innerhalb der letzten 24 Monate
			// werden in das Rückgabe-Array geschrieben
			foreach ($alle_fluege as $statistik) {
				$fluege['flugzeit_24']  = $statistik['flugzeit'];
				$fluege['landungen_24'] = $statistik['landungen'];
				$fluege['flugzeit_12']  = '0:00';
				$fluege['landungen_12'] = '0';
				$fluege['flugzeit_90']  = '0:00';
				$fluege['landungen_90'] = '0';
			}
			
			// Flüge der letzten 12 Monate je Mitglied ermitteln
			$alle_fluege = getListeFlugstatistik(12, $email['acb_nr']);
			
			// die Flüge innerhalb der letzten 12 Monate
			// werden in das Rückgabe-Array geschrieben
			foreach ($alle_fluege as $statistik) {
				$fluege['flugzeit_12']  = $statistik['flugzeit'];
				$fluege['landungen_12'] = $statistik['landungen'];
			}
			
			// Flüge der letzten 90 Tage je Mitglied ermitteln
			$alle_fluege = getListeFlugstatistik(3, $email['acb_nr']);

			// die Flüge innerhalb der letzten 90 Tage
			// werden in das Rückgabe-Array geschrieben
			foreach ($alle_fluege as $statistik) {
				$fluege['flugzeit_90']  = $statistik['flugzeit'];
				$fluege['landungen_90'] = $statistik['landungen'];
			}
		
			// Flugzeiten zurecht basteln und in einer HTML-Tabelle schreiben
			$html_tabelle  = '<table cellpadding="0" cellspacing="0" border>';
			$html_tabelle .= '<tr bgcolor="#e0e0e0">';
			$html_tabelle .= '<th colspan="6">Flugzeit und Fl&uuml;ge innerhalb der letzten ...</th>';
			$html_tabelle .= '</tr>';
			$html_tabelle .= '<tr bgcolor="#e0e0e0">';
			$html_tabelle .= '<th colspan="2">24 Monate</th>';
			$html_tabelle .= '<th colspan="2">12 Monate</th>';
			$html_tabelle .= '<th colspan="2">90 Tage</th>';
			$html_tabelle .= '</tr>';
			$html_tabelle .= '<tr bgcolor="#e0e0e0">';
			$html_tabelle .= '<th>Zeit</th>';
			$html_tabelle .= '<th>Fl&uuml;ge</th>';
			$html_tabelle .= '<th>Zeit</th>';
			$html_tabelle .= '<th>Fl&uuml;ge</th>';
			$html_tabelle .= '<th>Zeit</th>';
			$html_tabelle .= '<th>Fl&uuml;ge</th>';
			$html_tabelle .= '</tr>';
			$html_tabelle .= '<tr>';
			$html_tabelle .= '<td align="center" width="75">' . $fluege['flugzeit_24']  . '</td>';
			$html_tabelle .= '<td align="center" width="75">' . $fluege['landungen_24'] . '</td>';
			$html_tabelle .= '<td align="center" width="75">' . $fluege['flugzeit_12']  . '</td>';
			$html_tabelle .= '<td align="center" width="75">' . $fluege['landungen_12'] . '</td>';
			$html_tabelle .= '<td align="center" width="75">' . $fluege['flugzeit_90']  . '</td>';
			$html_tabelle .= '<td align="center" width="75">' . $fluege['landungen_90'] . '</td>';
			$html_tabelle .= '</tr>';
			$html_tabelle .= '</table>';

			// den Namen im Mailtext, mit dem des aktuellen Mitgliedes, ersetzen
			$alt = array('{name}', '{flugzeiten}');
			$neu = array($email['vorname'], $html_tabelle);
			$nachricht = str_replace($alt, $neu, $mailtext);

			// PHP-Mailer Klasse einbinden
			include_once('./phpmailer/class.phpmailer.php');
			
			// neue Instanz des PHPMailer anlegen
			$mail = new PHPMailer();
			// Absender eintragen
			$mail->From     = 'abrechnung@aero-club-butzbach.de';
			$mail->FromName = 'Aero Club Butzbach e.V.';
			// eMail-Adresse des Empfängers hinzufügen
			$mail->AddAddress($email['email']);
			// Betreffzeile definieren
			$mail->Subject = sprintf('Flugbuchauszug aus ameAVIA %s/%s', $dir_monat, $dir_jahr);
			// eMail-Text
            $mail->IsHTML(true);
			$mail->Body = nl2br($nachricht);

			// Abrechnung als Anhang der eMailadresse hinzufügen
			$mail->AddAttachment($pfad . $pdf);
			
			// eMail versenden
			if (!$mail->send()) {
				// ein Fehler ist aufgetreten
				echo 0;
			} else {
				// eine Infomeldung für den Anwender einblenden, falls
				// der eMailversand erfolgreich durchgeführt wurde
				echo 1;
			}
		}
	}

	// Skript beenden
	die();
	
?>