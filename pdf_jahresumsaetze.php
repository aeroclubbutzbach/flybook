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
	 * getJahresumsaetzePdf()
	 *
	 * die Jahresumsätze der einzelnen Flugzeuge wird anhand
	 * der übergebenen Parameter ermittelt und zurückgegeben
	 *
	 * @params date  $von
	 * @params date  $bis
	 * @return array $data
	 */
	if (!function_exists('getJahresumsaetzePdf')) {
		function getJahresumsaetzePdf($von, $bis)
		{
			// Rückgabe-Array definieren
			$data = array();
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');

			// SQL-Befehl zurechtfuddeln,
			// Umsätze für den festgelegten Zeitraum ermitteln
			$sql = sprintf('
				SELECT
					`preiskategorien`.`kennzeichen` AS `kennzeichen`,
					`flugzeuge`.`flugzeugtyp` AS `typ`,
					SUM(`flugbuch`.`anteilsumme_1` + `flugbuch`.`anteilsumme_2`) AS `summe`,
					COUNT(*) AS `fluege`,
					SUM(`flugbuch`.`flugzeit`) AS `flugzeit`,
					`preiskategorien`.`mwst_satz` AS `mwst_satz`
				FROM
					`flugbuch`
				INNER JOIN
					`preiskategorien`
					ON (`flugbuch`.`luftfahrzeug` = `preiskategorien`.`kennzeichen` AND `flugbuch`.`preiskategorie` = `preiskategorien`.`id`)
				INNER JOIN
					`flugzeuge` ON `flugbuch`.`luftfahrzeug` = `flugzeuge`.`kennzeichen`
				WHERE
					`flugbuch`.`datum` BETWEEN "%s" AND "%s"
				GROUP BY
					`flugbuch`.`luftfahrzeug`,
					`preiskategorien`.`mwst_satz`
			',
				$von, $bis
			);

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Zählervariable initialisieren
			$i = 0;

			while ($zeile = mysql_fetch_object($db_erg)) {	
				// Nettobetrag berechnen
				$netto = $zeile->summe * 100 / ($zeile->mwst_satz + 100);
			
				// Daten übernehmen wie hinterlegt
				$data[$i]['kennzeichen'] = utf8_encode($zeile->kennzeichen);
				$data[$i]['typ']         = utf8_encode($zeile->typ);
				$data[$i]['fluege']      = utf8_encode($zeile->fluege);
				$data[$i]['mwst_satz']   = sprintf('%01.2f', $zeile->mwst_satz);
				$data[$i]['flugzeit']    = utf8_encode($zeile->flugzeit);
				$data[$i]['netto']       = sprintf('%01.2f', $netto);
				$data[$i]['mwst']        = sprintf('%01.2f', $zeile->summe - $netto);
				$data[$i]['brutto']      = sprintf('%01.2f', $zeile->summe);
				
				// Zähler erhöhen
				$i++;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Umsatzliste
			return $data;
		}
	}
	
	/*
	 * pdf_header()
	 *
	 * erstellt die Kopfzeile des übergebenen PDF-Dokumentes
	 *
	 * @params object $ref_pdf
	 * @params date   $von
	 * @params date   $bis
	 */
	if (!function_exists('pdf_header')) {
		function pdf_header(& $ref_pdf, $von, $bis)
		{
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Times', '' , 24);
			
			$ref_pdf->Text(15, 14, 'Bewertung der Flugzeuge');
			
			// neue X- und Y-Koordinaten setzen
			$ref_pdf->SetXY(15, 21.5);
			
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Times', 'B' , 10);
			
			$ref_pdf->Cell(32, 5, 'LFZ-Typ',         0, 0, 'L');
			$ref_pdf->Cell(28, 5, 'LFZ-Kennung',     0, 0, 'L');
			$ref_pdf->Cell(25, 5, 'MwSt-Satz %',     0, 0, 'R');
			$ref_pdf->Cell(17, 5, 'Minuten',         0, 0, 'R');
			$ref_pdf->Cell(29, 5, 'Betrag (Netto)',  0, 0, 'R');
			$ref_pdf->Cell(23, 5, sprintf('MwSt in %s', iconv('UTF-8', 'CP1252', '€')), 0, 0, 'R');
			$ref_pdf->Cell(29, 5, 'Betrag (Brutto)', 0, 0, 'R');

			// Linienbreite einstellen, 0.5 mm
			$ref_pdf->SetLineWidth(0.5);
			// Linie(n) zeichnen
			$ref_pdf->Line(13, 20.5, 200, 20.5);
			$ref_pdf->Line(13, 27.5, 200, 27.5);
			
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Times', 'B', 12);
			
			$ref_pdf->Text(116, 14, 'Zeitraum von :');
			$ref_pdf->Text(168, 14, 'bis :');
			
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Times', '' , 12);
			
			// Von- und Bis-Datum setzen
			$ref_pdf->Text(145, 14, fromSqlDatum($von));
			$ref_pdf->Text(178, 14, fromSqlDatum($bis));
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
		
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Times', '', 8);

			// Datum des Ausdruckes
			$ref_pdf->Text(25, 288, utf8_decode(
				sprintf('%s, %d. %s %d', $_wochentage[date('N')], date('j'), $_monate[date('n')], date('Y'))
			));
			
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Times', '', 9);
			
			// Seitenzahlen hinzufügen
			$ref_pdf->Text(170, 289, sprintf('SEITE %d VON {nb}', $ref_pdf->PageNo()));
		}
	}

	// PDF-Bibliothek einbinden
	require_once('./pdf/fpdf.php');
	
	// neues PDF-Dokument erzeugen
	$pdf = new FPDF('P', 'mm', 'A4');
	
	// ... entspricht dem Aufruf von
	$pdf->AliasNbPages('{nb}');
	
	// Automatischen Seitenumbruch deaktivieren
	$pdf->SetAutoPageBreak(false);
	
	// Seitenabstand definieren
	$pdf->SetMargins(15, 15, 15);
	
	// die Daten der Jahresumsätze ermitteln
	$data = getJahresumsaetzePdf($_GET['von'], $_GET['bis']);

	// ******************************************************** SEITE ******************************************************** //
	// Seite hinzufügen
	$pdf->AddPage();
	
	// Kopfzeile auf der aktuellen Seite hinzufügen
	pdf_header($pdf, $_GET['von'], $_GET['bis']);
	
	$y = 28;
	
	// Schwarz gefülltes Rechteck zeichnen 
	$pdf->SetFillColor(255, 255, 255);
	
	// Zählervariable initialisieren
	$i = 1;

	// Variable zum Zwischenspeichern des Kennzeichens anlegen
	$kennzeichen = '';
	
	// Summe aller Flüge zum zwischenspeichern
	$summe = 0;
	
	$minuten = 0;
	$betrag  = 0.0;
	$betrag_netto = 0.0;
	$last_type = '';
	
	$y_pos = 0.0;
	
	$gesamt = array(
		'minuten' => 0,
		'netto'   => 0.0,
		'mwst'    => 0.0,
		'brutto'  => 0.0
	);
	
	foreach ($data as $record) {
		if ($kennzeichen != $record['kennzeichen']) {
			if ($kennzeichen != '') {
				// Schriftgrad einstellen
				$pdf->SetFont('Times', 'I' , 8);
				$pdf->Text(22, $y_pos, sprintf(utf8_decode('Zusammenfassung für "Typ" = %s (%d Detaildatensätze)'), $last_type, $summe));
				// temporäre Y-Position wieder zurückgesetzen
				$y_pos = 0.0;
			
				// neue X- und Y-Koordinaten setzen
				$pdf->SetXY(100, $y - 2);
				
				$pdf->Cell(17, 5, $minuten, 0, 0, 'R');
				$pdf->Cell(29, 5, sprintf('%s %s', number_format($betrag_netto, 2, ',', '.'), iconv('UTF-8', 'CP1252', '€')), 0, 0, 'R');
				$pdf->Cell(23, 5, sprintf('%s %s', number_format($betrag - $betrag_netto, 2, ',', '.'), iconv('UTF-8', 'CP1252', '€')), 0, 0, 'R');
				$pdf->Cell(29, 5, sprintf('%s %s', number_format($betrag, 2, ',', '.'), iconv('UTF-8', 'CP1252', '€')), 0, 0, 'R');

				// Gesamtsummen bilden
				$gesamt['minuten'] += $minuten;
				$gesamt['netto']   += sprintf('%01.2f', $betrag_netto);
				$gesamt['mwst']    += sprintf('%01.2f', $betrag - $betrag_netto);
				$gesamt['brutto']  += sprintf('%01.2f', $betrag);
				
				// Linienbreite einstellen, 0.1 mm
				$pdf->SetLineWidth(0.1);
				// Linie(n) zeichnen
				$pdf->Line(40, $y - 3, 200, $y - 3);
				
				// Schriftgrad einstellen
				$pdf->SetFont('Times', 'B' , 8);
				$pdf->Text(44, $y + 1, 'Zwischensumme(n) :');
			}
			
			if (($y + 30) > 260) {
				// Fusszeile auf der aktuellen Seite hinzufügen
				pdf_footer($pdf);

				// Seite hinzufügen
				$pdf->AddPage();
				
				// Kopfzeile auf der aktuellen Seite hinzufügen
				pdf_header($pdf, $_GET['von'], $_GET['bis']);
				
				// Y-Wert zurücksetzen
				$y = 28;
			}
		
			// Schriftgrad einstellen
			$pdf->SetFont('Times', 'B' , 12);
			$pdf->Text(16, $y + 10, $record['typ']);
			$pdf->Text(48, $y + 10, $record['kennzeichen']);
			
			if ($y_pos == 0.0) {
				$y_pos = $y + 14.5;
			}
		
			$y += 20;
		
			$i = 1;
			$kennzeichen = $record['kennzeichen'];
			$summe = 0;
			$minuten = 0;
			$betrag  = 0.0;
			$betrag_netto = 0.0;
		} else {
			$kennzeichen = $record['kennzeichen'];
		}

		$last_type = $record['typ'];		
		$netto = $record['brutto'];
		$netto *= 100.0 / ($record['mwst_satz'] + 100);
		$netto = floatval(sprintf('%01.2f', $netto));
		
		// Schriftgrad einstellen
		$pdf->SetFont('Times', 'B', 8);
		
		$pdf->Text(55, $y, 'Summe(n) :');
		
		// neue X- und Y-Koordinaten setzen
		$pdf->SetXY(75, $y - 3);
		
		// Schriftgrad einstellen
		$pdf->SetFont('Times', '' , 8);

		$pdf->Cell(25, 5, sprintf('%s %%', number_format($record['mwst_satz'], 2, ',', '.')), 0, 0, 'R');
		$pdf->Cell(17, 5, $record['flugzeit'], 0, 0, 'R');
		$pdf->Cell(29, 5, sprintf('%s %s', number_format($netto, 2, ',', '.'), iconv('UTF-8', 'CP1252', '€')), 0, 0, 'R');
		$pdf->Cell(23, 5, sprintf('%s %s', number_format($record['brutto'] - $netto, 2, ',', '.'), iconv('UTF-8', 'CP1252', '€')), 0, 0, 'R');
		$pdf->Cell(29, 5, sprintf('%s %s', number_format($record['brutto'], 2, ',', '.'), iconv('UTF-8', 'CP1252', '€')), 0, 0, 'R');
		
		// Flüge aufsummieren
		$summe   += $record['fluege'];
		$minuten += $record['flugzeit'];
		$betrag  += $record['brutto'];
		$betrag_netto += $netto;
		
		$y += 5.1;
		$pdf->SetXY(23, $y);
	}
	
	// Schriftgrad einstellen
	$pdf->SetFont('Times', 'I' , 8);
	$pdf->Text(22, $y_pos, sprintf(utf8_decode('Zusammenfassung für "Typ" = %s (%d Detaildatensätze)'), $last_type, $summe));
	
	if ($y > 28) {
		// letzte Fusszeile auf der aktuellen Seite hinzufügen
		pdf_footer($pdf);
	}
	
	if (($y + 20) > 260) {
		// Seite hinzufügen
		$pdf->AddPage();
		
		// Kopfzeile auf der aktuellen Seite hinzufügen
		pdf_header($pdf, $_GET['von'], $_GET['bis']);
		
		// Fusszeile auf der aktuellen Seite hinzufügen
		pdf_footer($pdf);
		
		$y = 38;
	}
	
	// Schriftgrad einstellen
	$pdf->SetFont('Times', '' , 8);
	// neue X- und Y-Koordinaten setzen
	$pdf->SetXY(100, $y - 2);
	
	$pdf->Cell(17, 5, $minuten, 0, 0, 'R');
	$pdf->Cell(29, 5, sprintf('%s %s', number_format($betrag_netto, 2, ',', '.'), iconv('UTF-8', 'CP1252', '€')), 0, 0, 'R');
	$pdf->Cell(23, 5, sprintf('%s %s', number_format($betrag - $betrag_netto, 2, ',', '.'), iconv('UTF-8', 'CP1252', '€')), 0, 0, 'R');
	$pdf->Cell(29, 5, sprintf('%s %s', number_format($betrag, 2, ',', '.'), iconv('UTF-8', 'CP1252', '€')), 0, 0, 'R');
	
	// Linienbreite einstellen, 0.1 mm
	$pdf->SetLineWidth(0.1);
	// Linie(n) zeichnen
	$pdf->Line(40, $y - 3, 200, $y - 3);
	
	// Schriftgrad einstellen
	$pdf->SetFont('Times', 'B' , 8);
	$pdf->Text(44, $y + 1, 'Zwischensumme(n) :');

	// Y-Position erhöhen
	$y += 10;
	
	// neue X- und Y-Koordinaten setzen
	$pdf->SetXY(100, $y);
	
	// Schriftgrad einstellen
	$pdf->SetFont('Times', 'B' , 8);
	$pdf->Text(46.1, $y + 3.5, 'Gesamtsumme(n) :');
	
	// Linienbreite einstellen, 0.5 mm
	$pdf->SetLineWidth(0.5);
	// Linie(n) zeichnen
	$pdf->Line(40, $y - 1, 200, $y - 1);
	
	// Gesamtsummen bilden
	$gesamt['minuten'] += $minuten;
	$gesamt['netto']   += sprintf('%01.2f', $betrag_netto);
	$gesamt['mwst']    += sprintf('%01.2f', $betrag - $betrag_netto);
	$gesamt['brutto']  += sprintf('%01.2f', $betrag);

	$pdf->Cell(17, 5, $gesamt['minuten'], 0, 0, 'R');
	$pdf->Cell(29, 5, sprintf('%s %s', number_format($gesamt['netto'],  2, ',', '.'), iconv('UTF-8', 'CP1252', '€')), 0, 0, 'R');
	$pdf->Cell(23, 5, sprintf('%s %s', number_format($gesamt['mwst'],   2, ',', '.'), iconv('UTF-8', 'CP1252', '€')), 0, 0, 'R');
	$pdf->Cell(29, 5, sprintf('%s %s', number_format($gesamt['brutto'], 2, ',', '.'), iconv('UTF-8', 'CP1252', '€')), 0, 0, 'R');
	
	// ******************************************************** SEITE ******************************************************** //

	// PDF-Dokument ausgeben
	$pdf->Output(sprintf('jahresumsaetze_vom_%s_bis_%s.pdf', $_GET['von'], $_GET['bis']), 'I');

?>