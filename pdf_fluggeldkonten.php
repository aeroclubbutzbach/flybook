<?php

	/*
	 * Array anlegen, welches die Monatsnamen im Klartext enthält
	 */
	$_monate = array(
		 1 => 'Januar',   2 => 'Februar',   3 => 'März',
		 4 => 'April',    5 => 'Mai',       6 => 'Juni',
		 7 => 'Juli',     8 => 'August',    9 => 'September',
		10 => 'Oktober', 11 => 'November', 12 => 'Dezember'
	);
	
	/*
	 * getTabelleFluggeldkonten()
	 *
	 * die Fluggeldkonten (Saldenliste) der einzelnen Mitglieder wird geladen,
	 * und anschließend als HTML-Tabelle zum Bearbeiten zurückgegeben
	 *
	 * @return string $html
	 */
	if (!function_exists('getTabelleFluggeldkonten')) {
		function getTabelleFluggeldkonten()
		{
			// Rückgabe-Array definieren
			$data = array();
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');

			// SQL-Befehl zurecht fuddeln,
			// Liste der Fluggeldkonten ermitteln
			$sql = sprintf('
				SELECT
					`fluggeldkonto`.`acb_nr`,
					`mitglieder`.`nachname`,
					`mitglieder`.`vorname`,
					`fluggeldkonto`.`saldo`
				FROM
					`fluggeldkonto`
				INNER JOIN
					`mitglieder` ON `fluggeldkonto`.`acb_nr` = `mitglieder`.`id`
				WHERE
					`mitglieder`.`in_abrechn` = "J"
				ORDER BY
					`mitglieder`.`nachname`,
					`mitglieder`.`vorname`
				ASC
			');

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Zählervariable initialisieren
			$i = 0;

			while ($zeile = mysql_fetch_object($db_erg)) {	
				// Daten übernehmen wie hinterlegt
				// die weiteren Parameter Mitgliedsnummer, Nachname, Vorname und Saldo in die Zeile einfügen
				$data[$i]['acb_nr']   = utf8_encode($zeile->acb_nr);
				$data[$i]['nachname'] = utf8_encode($zeile->nachname);
				$data[$i]['vorname']  = utf8_encode($zeile->vorname);
				$data[$i]['saldo']    = number_format($zeile->saldo, 2, ',', '');
				
				// Zähler erhöhen
				$i++;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Saldenliste
			return $data;
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
			// Array, welches die Monatsnamen enthält, global erreichbar machen
			global $_monate;
			
			// Schriftfarbe setzen
			$ref_pdf->SetTextColor(0, 0, 0);
		
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Arial', 'B' , 11);
			$ref_pdf->Text(20, 15, utf8_decode('Aero-Club Butzbach e.V.'));
			$ref_pdf->Text(20, 20, utf8_decode('Butzbach'));
			$ref_pdf->SetFont('Arial', 'B', 13);
			$ref_pdf->Text(85, 16, utf8_decode('Kontostand : '));
			$ref_pdf->Text(120, 16, sprintf(utf8_decode('%d. %s %d'), date('d'), utf8_decode($_monate[date('n')]), date('Y')));
			
			// Linienbreite einstellen, 0.2 mm
			$ref_pdf->SetLineWidth(0.2);
			// Linie(n) zeichnen
			$ref_pdf->Line(83, 17.5, 195, 17.5);

			$ref_pdf->SetFont('Arial', 'B', 10);
			$ref_pdf->Text(20,  30, utf8_decode('Nr.'));
			$ref_pdf->Text(40,  30, utf8_decode('Name'));
			$ref_pdf->Text(120, 30, utf8_decode('Kontostand'));
			$ref_pdf->Text(150, 30, utf8_decode('weitere Zahlungen'));

			// Linienbreite einstellen, 0.1 mm
			$ref_pdf->SetLineWidth(0.1);
			// Linie(n) zeichnen
			$ref_pdf->Line(19, 31.3, 195, 31.3);
			$ref_pdf->Line(19,   32, 195,   32);
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
			// Linienbreite einstellen, 0.1 mm
			$ref_pdf->SetLineWidth(0.1);
			// Linie(n) zeichnen
			$ref_pdf->Line(19, 280, 195, 280);
			
			// Schriftfarbe setzen
			$ref_pdf->SetTextColor(128, 128, 128);
		
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Arial', '' , 8);
		
			$ref_pdf->Text(20,  284, utf8_decode('Druckdatum:'));
			$ref_pdf->Text(40,  284, date('d.m.Y H:i:s'));
			$ref_pdf->Text(75,  284, utf8_decode('Volksbank Butzbach - IBAN: DE29 5186 1403 0000 0370 52 - BIC: GENODE51BUT'));
			$ref_pdf->Text(187, 284, sprintf('%d / {nb}', $ref_pdf->PageNo()));
			
			// Schriftfarbe setzen
			$ref_pdf->SetTextColor(0, 0, 0);
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
	$pdf->SetMargins(25, 15, 15);
	
	// die Fluggeldkonten aller Mitglieder ermitteln
	$data = getTabelleFluggeldkonten();

	// ******************************************************** SEITE ******************************************************** //
	// Seite hinzufügen
	$pdf->AddPage();
	
	// Kopfzeile auf der aktuellen Seite hinzufügen
	pdf_header($pdf);
	
	$y = 35;
	
	foreach ($data as $saldo) {
		$pdf->SetXY(20, $y);
		
		// Schriftgrad einstellen
		$pdf->SetFont('Arial', '' , 11);
	
		$pdf->Cell(20, 5.5, utf8_decode($saldo['acb_nr']),   0, 0, 'L');
		$pdf->Cell(40, 5.5, utf8_decode($saldo['nachname']), 0, 0, 'L');
		$pdf->Cell(40, 5.5, utf8_decode($saldo['vorname']),  0, 0, 'L');
		$pdf->Cell(20, 5.5, sprintf('%s %s', $saldo['saldo'], iconv('UTF-8', 'CP1252', '€')), 0, 0, 'R');
	
		// Y-Position erhöhen
		$y += 5.6;
		
		if ($y > 275) {
			// Fusszeile auf der aktuellen Seite hinzufügen
			pdf_footer($pdf);

			// Seite hinzufügen
			$pdf->AddPage();
			
			// Kopfzeile auf der aktuellen Seite hinzufügen
			pdf_header($pdf);
			
			// Y-Wert zurücksetzen
			$y = 35;
		}
	}
	
	if ($y > 35) {
		// letzte Fusszeile auf der aktuellen Seite hinzufügen
		pdf_footer($pdf);
	}
	
	// ******************************************************** SEITE ******************************************************** //

	// PDF-Dokument ausgeben
	$pdf->Output(sprintf('fluggeldkonten-%s%s.pdf', date('Y'), date('n')), 'I');

?>