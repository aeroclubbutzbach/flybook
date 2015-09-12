<?php

	// allgemeine Funktionen einbinden
	include_once('./functions.php');
	
	header('Content-type: text/html; charset=utf-8');
	
	/*
	 * Array anlegen, welches die Monate im Klartext enthält
	 */
	$_monate = array(
		 1 => 'Januar',   2 => 'Februar',   3 => 'März',
		 4 => 'April',    5 => 'Mai',       6 => 'Juni',
		 7 => 'Juli',     8 => 'August',    9 => 'September',
		10 => 'Oktober', 11 => 'November', 12 => 'Dezember'
	);
	
	/*
	 * Array anlegen, welches die Wochentage im Klartext enthält
	 */
	$_wochentage = array(
		 1 => 'Montag',     2 => 'Dienstag',  3 => 'Mittwoch',
		 4 => 'Donnerstag', 5 => 'Freitag',   6 => 'Samstag',
		 7 => 'Sonntag'
	);

	/*
	 * getBordbuchPdf()
	 *
	 * die PDF-Ansicht aus dem Hauptflugbuch der ausgewählten Startliste,
	 * welche anhand des übergebenen Parameters ermittelt wird, wird geladen
	 * diesmal aber sortiert und gruppiert nach den einzelnen Flugzeugen
	 *
	 * @params date  $von
	 * @params date  $bis
	 * @return array $return
	 */
	if (!function_exists('getBordbuchPdf')) {
		function getBordbuchPdf($von, $bis)
		{
			// Rückgabe-Array definieren
			$return = array();
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.');
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// aktuelles Jahr aus übergebenen Parameter ermitteln
			$jahr = intval(substr($von, 0, 4));
			
			// prüfen um welches Jahr es sich handelt um entsprechend den SQL-Befehl anzupassen
			if ($jahr < 2014) {
				// SQL-Befehl zurechtfuddeln,
				// die Flugeinträge aus dem Hauptflugbuch, der
				// aktuell ausgewählten Startliste, werden ermittelt
				$sql = sprintf('
					SELECT
						`hauptflugbuch`.`typ` AS `typ`,
						`hauptflugbuch`.`kennzeichen` AS `kennzeichen`,
						DATE_FORMAT(`hauptflugbuch`.`datum`, "%%d.%%m.%%Y") AS `startdatum`,
						`hauptflugbuch`.`startzeit` AS `startzeit`,
						`hauptflugbuch`.`startort` AS `startort`,
						DATE_FORMAT(`hauptflugbuch`.`datum`, "%%d.%%m.%%Y") AS `landedatum`,
						`hauptflugbuch`.`landezeit` AS `landezeit`,
						`hauptflugbuch`.`landeort` AS `landeort`,
						(MINUTE(`hauptflugbuch`.`flugzeit`) + (HOUR(`hauptflugbuch`.`flugzeit`) * 60)) AS `flugzeit`,
						`hauptflugbuch`.`pilotname` AS `pilot`,
						`hauptflugbuch`.`begleitername` AS `begleiter`,
						SUBSTRING(`hauptflugbuch`.`startart`, 1, 1) AS `startart`,
						`hauptflugbuch`.`art` AS `flugart`,
						CASE WHEN
							`hauptflugbuch`.`startzeit` = "00:00:00"
						THEN
							`hauptflugbuch`.`landezeit`
						ELSE
							`hauptflugbuch`.`startzeit`
						END AS `sort`
					FROM
						`hauptflugbuch`
					WHERE
						`hauptflugbuch`.`datum` >= "%s" AND `hauptflugbuch`.`datum` <= "%s" AND
						`hauptflugbuch`.`geloescht` = "N"
					ORDER BY
						`kennzeichen` ASC,
						`startdatum` ASC,
						`sort` ASC
				',
					$von, $bis
				);
			} else {
				// SQL-Befehl zurechtfuddeln,
				// die Flugeinträge aus dem Hauptflugbuch, der
				// aktuell ausgewählten Startliste, werden ermittelt
				$sql = sprintf('
					SELECT
						`flugzeuge`.`flugzeugtyp` AS `typ`,
						`flugzeuge`.`kennzeichen` AS `kennzeichen`,
						DATE_FORMAT(`flugbuch`.`datum`, "%%d.%%m.%%Y") AS `startdatum`,
						`flugbuch`.`startzeit` AS `startzeit`,
						CASE
							WHEN (`flugplaetze_1`.`name` IS NULL) THEN
								`flugbuch`.`startort`
							ELSE
								`flugplaetze_1`.`name`
						END AS `startort`,
						DATE_FORMAT(`flugbuch`.`datum`, "%%d.%%m.%%Y") AS `landedatum`,
						`flugbuch`.`landezeit` AS `landezeit`,
						CASE
							WHEN (`flugplaetze_2`.`name` IS NULL) THEN
								`flugbuch`.`landeort`
							ELSE
								`flugplaetze_2`.`name`
						END AS `landeort`,
						`flugbuch`.`flugzeit` AS `flugzeit`,
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
						`flugbuch`.`preiskategorie` AS `flugart`,
						CASE WHEN
							`flugbuch`.`startzeit` = "00:00:00"
						THEN
							`flugbuch`.`landezeit`
						ELSE
							`flugbuch`.`startzeit`
						END AS `sort`
					FROM
						`flugbuch`
					LEFT JOIN
						`flugzeuge` ON `flugbuch`.`luftfahrzeug` = `flugzeuge`.`kennzeichen`
					LEFT JOIN
						`mitglieder` AS `mitglieder_1` ON `flugbuch`.`besatzung1` LIKE CONCAT("%%", `mitglieder_1`.`ameavia`, "%%")
					LEFT JOIN
						`mitglieder` AS `mitglieder_2` ON `flugbuch`.`besatzung2` LIKE CONCAT("%%", `mitglieder_2`.`ameavia`, "%%")
					INNER JOIN
						`startarten` ON `startarten`.`id` = `flugbuch`.`startart`
					LEFT JOIN
						`flugplaetze` AS `flugplaetze_1` ON `flugbuch`.`startort` = `flugplaetze_1`.`ameavia`
					LEFT JOIN
						`flugplaetze` AS `flugplaetze_2` ON `flugbuch`.`landeort` = `flugplaetze_2`.`ameavia`
					WHERE
						`flugbuch`.`datum` >= "%s" AND `flugbuch`.`datum` <= "%s" AND
						`flugbuch`.`geloescht` = "N"
					ORDER BY
						`kennzeichen` ASC,
						`startdatum` ASC,
						`sort` ASC
				',
					$von, $bis
				);
			}

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
				// Bordbuchdaten in das Rückgabe-Array einfügen
				$return[$i]['typ']         = $zeile->typ;
				$return[$i]['kennzeichen'] = $zeile->kennzeichen;
				$return[$i]['startdatum']  = $zeile->startdatum;
				$return[$i]['startzeit']   = $zeile->startzeit;
				$return[$i]['startort']    = $zeile->startort;
				$return[$i]['landedatum']  = $zeile->landedatum;
				$return[$i]['landezeit']   = $zeile->landezeit;
				$return[$i]['landeort']    = $zeile->landeort;
				$return[$i]['flugzeit']    = $zeile->flugzeit;
				$return[$i]['startart']    = $zeile->startart;
				$return[$i]['flugart']     = $zeile->flugart;
				
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
	 * pdf_header()
	 *
	 * erstellt die Kopfzeile des übergebenen PDF-Dokumentes
	 *
	 * @params object $ref_pdf
	 */
	if (!function_exists('pdf_header')) {
		function pdf_header(& $ref_pdf, $von, $bis)
		{
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Times', '' , 26);
			
			$ref_pdf->Text(40, 20, 'Hauptflugbuch');

			// Linienbreite einstellen, 0.5 mm
			$ref_pdf->SetLineWidth(0.5);
			// Linie(n) zeichnen
			$ref_pdf->Line(23, 23.5, 270, 23.5);
			
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Times', 'B' , 12);
			
			$ref_pdf->Text(115, 20, 'von :');
			$ref_pdf->Text(155, 20, 'bis :');
			
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Times', '' , 12);
			
			// Von- und Bis-Datum setzen
			$ref_pdf->Text(126, 20, fromSqlDatum($von));
			$ref_pdf->Text(165, 20, fromSqlDatum($bis));
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
			// Array, welches die Monats- und Wochentagsnamen enthält, global erreichbar machen
			global $_monate;
			global $_wochentage;
		
			// Linienbreite einstellen, 0.1 mm
			$ref_pdf->SetLineWidth(0.1);
			// Linie(n) zeichnen
			$ref_pdf->Line(23, 195.4, 270, 195.4);
		
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Times', '', 8);

			// Datum des Ausdruckes
			$ref_pdf->Text(30, 200, utf8_decode(
				sprintf('%s, %d. %s %d', $_wochentage[date('N')], date('j'), $_monate[date('n')], date('Y'))
			));
			
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Times', '', 9);
			
			// Seitenzahlen hinzufügen
			$ref_pdf->Text(240, 201, sprintf('SEITE %d VON {nb}', $ref_pdf->PageNo()));
		}
	}
	
	// PDF-Bibliothek einbinden
	require_once('./pdf/fpdf.php');
	
	// neues PDF-Dokument erzeugen
	$pdf = new FPDF('L', 'mm', 'A4');
	
	// ... entspricht dem Aufruf von
	$pdf->AliasNbPages('{nb}');
	
	// Automatischen Seitenumbruch deaktivieren
	$pdf->SetAutoPageBreak(false);
	
	// Seitenabstand definieren
	$pdf->SetMargins(25, 15, 15);
	
	// das aktuelle Bordbuch aus dem Hauptflugbuch ermitteln
	$data = getBordbuchPdf($_GET['von'], $_GET['bis']);
	
	// ******************************************************** SEITE ******************************************************** //
	// Seite hinzufügen
	$pdf->AddPage();
	
	// Kopfzeile auf der aktuellen Seite hinzufügen
	pdf_header($pdf, $_GET['von'], $_GET['bis']);
	
	$y = 24.5;
	
	// Schwarz gefülltes Rechteck zeichnen 
	$pdf->SetFillColor(255, 255, 255);
	
	// Zählervariable initialisieren
	$i = 1;

	// Variable zum Zwischenspeichern des Kennzeichens anlegen
	$kennzeichen = '';
	
	foreach ($data as $record) {
		$pdf->SetXY(23, $y);
		
		if ($kennzeichen != $record['kennzeichen']) {
			if (($y + 23) > 192) {
				// Fusszeile auf der aktuellen Seite hinzufügen
				pdf_footer($pdf);

				// Seite hinzufügen
				$pdf->AddPage();
				
				// Kopfzeile auf der aktuellen Seite hinzufügen
				pdf_header($pdf, $_GET['von'], $_GET['bis']);
				
				// Y-Wert zurücksetzen
				$y = 24.5;
			}
			
			// Linienbreite einstellen, 0.1 mm
			$pdf->SetLineWidth(0.1);
		
			// Rechteck zeichnen, nur Rahmen, ohne Füllung
			$pdf->Rect(23, $y, 247, 9); 
			
			// Schriftgrad einstellen
			$pdf->SetFont('Times', '' , 16);
			$pdf->Text(25,  $y + 6.5, 'Flugzeug-Typ :');
			$pdf->Text(65,  $y + 6.5, $record['typ']);
			$pdf->Text(115, $y + 6.5, 'Flugzeug-Kennung :');
			$pdf->Text(167, $y + 6.5, $record['kennzeichen']);
		
			$kennzeichen = $record['kennzeichen'];
			$y += 9.1;
			$pdf->SetXY(23, $y);
			
			// Schriftgrad einstellen
			$pdf->SetFont('Times', 'B' , 10);
			
			$pdf->Cell(16, 10, 'Flug-Nr',    0, 0, 'L', 1);
			$pdf->Cell(20, 10, 'St-Datum',   0, 0, 'C', 1);
			$pdf->Cell(20, 10, 'St-Zeit',    0, 0, 'C', 1);
			$pdf->Cell(25, 10, 'Abflug-Ort', 0, 0, 'L', 1);
			$pdf->Cell(20, 10, 'Ld-Datum',   0, 0, 'C', 1);
			$pdf->Cell(20, 10, 'Ld-Zeit',    0, 0, 'C', 1);
			$pdf->Cell(25, 10, 'Ziel-Ort',   0, 0, 'L', 1);
			$pdf->Cell(15, 10, 'Flug-Min',   0, 0, 'R', 1);
			$pdf->Cell(28, 10, 'Pilot',      0, 0, 'L', 1);
			$pdf->Cell(28, 10, 'Begleiter',  0, 0, 'L', 1);
			$pdf->Cell(15, 10, 'Startart',   0, 0, 'C', 1);
			$pdf->Cell(15, 10, 'Flugart',    0, 0, 'L', 1);
			
			$y += 10.1;
			$pdf->SetXY(23, $y);
			
			$i = 1;
		} else {
			$kennzeichen = $record['kennzeichen'];
		}
		
		// Schriftgrad einstellen
		$pdf->SetFont('Times', '' , 8);
	
		$pdf->Cell(13, 5, $i,                    'T', 0, 'R', 1);
		$pdf->Cell(3,  5, '',                    'T', 0, 'L', 1);
		$pdf->Cell(20, 5, $record['startdatum'], 'T', 0, 'C', 1);
		$pdf->Cell(20, 5, $record['startzeit'],  'T', 0, 'C', 1);
		$pdf->Cell(25, 5, $record['startort'],   'T', 0, 'L', 1);
		$pdf->Cell(20, 5, $record['landedatum'], 'T', 0, 'C', 1);
		$pdf->Cell(20, 5, $record['landezeit'],  'T', 0, 'C', 1);
		$pdf->Cell(25, 5, $record['landeort'],   'T', 0, 'L', 1);
		$pdf->Cell(10, 5, $record['flugzeit'],   'T', 0, 'R', 1);
		$pdf->Cell(5,  5, '',                    'T', 0, 'L', 1);
		$pdf->Cell(28, 5, $record['pilot'],      'T', 0, 'L', 1);
		$pdf->Cell(28, 5, $record['begleiter'],  'T', 0, 'L', 1);
		$pdf->Cell(15, 5, $record['startart'],   'T', 0, 'C', 1);
		
		if (is_numeric($record['flugart'])) {
			$pdf->Cell(10, 5, $record['flugart'], 'T', 0, 'R', 1);
			$pdf->Cell(5,  5, '',                 'T', 0, 'L', 1);
		} else {
			$pdf->Cell(5,  5, '',                 'T', 0, 'L', 1);
			$pdf->Cell(10, 5, $record['flugart'], 'T', 0, 'L', 1);
		}
	
		// Y-Position erhöhen
		$y += 7;

		if ($y > 190) {
			// Fusszeile auf der aktuellen Seite hinzufügen
			pdf_footer($pdf);

			// Seite hinzufügen
			$pdf->AddPage();
			
			// Kopfzeile auf der aktuellen Seite hinzufügen
			pdf_header($pdf, $_GET['von'], $_GET['bis']);
			
			// Y-Wert zurücksetzen
			$y = 24.5;
		}
		
		// Linienbreite einstellen, 0.1 mm
		$pdf->SetLineWidth(0.1);
		
		// Zähler erhöhen
		$i++;
	}
	
	if ($y > 24.5) {
		// letzte Fusszeile auf der aktuellen Seite hinzufügen
		pdf_footer($pdf);
	}
	
	// ******************************************************** SEITE ******************************************************** //

	// PDF-Dokument ausgeben
	$pdf->Output(sprintf('bordbuch_vom_%s_bis_%s.pdf', $_GET['von'], $_GET['bis']), 'I');

?>