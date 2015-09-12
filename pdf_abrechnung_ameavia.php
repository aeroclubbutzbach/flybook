<?php

	// allgemeine Funktionen einbinden
	include_once('./functions.php');

	/*
	 * Array anlegen, welches die Monatsnamen im Klartext enthält
	 */
	$_monate = array(
		 1 => 'Januar',   2 => 'Februar',   3 => 'März',
		 4 => 'April',    5 => 'Mai',       6 => 'Juni',
		 7 => 'Juli',     8 => 'August',    9 => 'September',
		10 => 'Oktober', 11 => 'November', 12 => 'Dezember'
	);

	// Definitionen festlegen
	define('DD',   2); // Tag
	define('MM',   1); // Monat
	define('YYYY', 0); // Jahr

	// Definitionen für die Tabellenspalten
	define('FLUGDATUM',  1); // Flugdatum
	define('LFZ_TYP',    2); // Flugzeugtyp
	define('LFZ_KENNZ',  3); // Flugzeug-Kennzeichen
	define('PILOT',      4); // Pilot
	define('CO_PILOT',   5); // Co-Pilot
	define('STARTART',   6); // Startart
	define('ZAHLUNGEN',  7); // Zahlungen
	define('STARTORT',   7); // Startort
	define('LANDEORT',   8); // Landeort
	define('STARTZEIT',  9); // Startzeit
	define('LANDEZEIT', 10); // Landezeit
	define('FLUGDAUER', 11); // Flugdauer
	define('PREIS_KAT', 12); // Preiskategorie
	define('ZW_SALDO',  13); // Zwischensaldo

	/*
	 * getMitgliedsnummern()
	 *
	 * gibt die Liste aller Mitglieder zurück,
	 * welche in der Abrechnung berücksichtig werden
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
			// die aktuellen Mitglieder werden ermittelt
			$sql = sprintf('
				SELECT
					`mitglieder`.`id`,
					`mitglieder`.`nachname`,
					`mitglieder`.`vorname`
				FROM
					`mitglieder`
				WHERE
					`mitglieder`.`in_abrechn` = "J"
				ORDER BY
					`id` ASC
			');
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Zählervariable initialisieren
			$i = 0;
			
			while ($zeile = mysql_fetch_object($db_erg)) {	
				// Daten übernehmen wie hinterlegt
				// die weiteren Parameter Mitgliedsnummer, Nachname, Vorname und Saldo in die Zeile einfügen
				$return[$i]['acb_nr']   = utf8_encode($zeile->id);
				$return[$i]['nachname'] = utf8_encode($zeile->nachname);
				$return[$i]['vorname']  = utf8_encode($zeile->vorname);
				
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
	 * getFluggeldkontoPdf()
	 *
	 * die PDF-Ansicht vom Fluggeldkonto des ausgewählten Mitglieds,
	 * welches anhand des übergebenen Parameters ermittelt wird, wird geladen
	 *
	 * @params array $fluggeldkonto
	 * @return array $html
	 */
	if (!function_exists('getFluggeldkontoPdf')) {
		function getFluggeldkontoPdf(array $fluggeldkonto)
		{
			// Rückgabe-Variable definieren
			$return = array();
			
			$umsatz_monat = 0.0;
			
			// die einzelnen Einträge rückwärts durchlaufen
			for ($i=0; $i<count($fluggeldkonto); $i++) {
				// es wird geprüft, ob es kein leerer Eintrag ist
				if (!empty($fluggeldkonto[$i])) {
					// aktuellen Eintrag, separiert per Semikolon,
					// splitten und anschließend als Array wiedergeben
					$item = explode(';', $fluggeldkonto[$i]);
					
					// Zwischenvariable einfügen zum Speichern
					$text = ';';
					
					// Datum formatieren von JJJJ-MM-TT nach TT.MM.
					$datum_splitten  = explode('-', $item[FLUGDATUM]);
					$item[FLUGDATUM] = sprintf('%s.%s.', $datum_splitten[DD], $datum_splitten[MM]);
					$umsatz_monat += $item[ZW_SALDO];
					
					// Tabelleninhalte zuweisen
					$text .= sprintf('%s;', $item[FLUGDATUM]);
					$text .= sprintf('%s;', $item[LFZ_TYP]);
					$text .= sprintf('%s;', $item[LFZ_KENNZ]);
					$text .= sprintf('%s;', $item[PILOT]);
					$text .= sprintf('%s;', $item[CO_PILOT]);
					$text .= sprintf('%s;', $item[STARTART]);
					
					// sind die nachfolgenden Felder leer, handelt es sich um keinen
					// Flugeintrag, sondern um eine geleistete Zahlung oder Forderung
					if (
						empty($item[LFZ_TYP]) && empty($item[LFZ_KENNZ]) &&
						empty($item[PILOT]) && empty($item[CO_PILOT]) &&
						empty($item[STARTART])
					) {
						// Zahlung einfügen und negative Beträge rot kennzeichen
						$text .= sprintf('%s;;;;;;', $item[ZAHLUNGEN]);
						$text .= sprintf('%s', number_format($item[ZW_SALDO], 2, ',', '')); // Betrag
						
						// Zeile wieder hinzufügen
						$return['flugbuch'][] = $text;
					} else {
						$text .= sprintf('%s;', $item[STARTORT]);  // Startort
						$text .= sprintf('%s;', $item[LANDEORT]);  // Landeort
						$text .= sprintf('%s;', $item[STARTZEIT]); // Startzeit
						$text .= sprintf('%s;', $item[LANDEZEIT]); // Landezeit
						$text .= sprintf('%s;', $item[FLUGDAUER]); // Flugdauer
						$text .= sprintf('%s;', $item[PREIS_KAT]); // Preiskategorie
						$text .= sprintf('%s',  number_format($item[ZW_SALDO], 2, ',', '')); // Betrag
						
						// Zeile wieder hinzufügen
						$return['flugbuch'][] = $text;
					}
				}
			}
			
			// den aktuellen Umsatz für diesen Monat hinzufügen
			$return['umsatz'] = $umsatz_monat;
			
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
	 * @params array  $mitglied
	 */
	if (!function_exists('pdf_header')) {
		function pdf_header(& $ref_pdf, array $mitglied)
		{
			// Array, welches die Monatsnamen enthält, global erreichbar machen
			global $_monate;
			
			// Schriftfarbe setzen
			$ref_pdf->SetTextColor(0, 0, 0);
		
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Arial', 'B' , 10);
			$ref_pdf->Text(20, 15, utf8_decode('Aero-Club Butzbach e.V.'));
			$ref_pdf->Text(20, 19, utf8_decode('Butzbach'));
			$ref_pdf->Text(75, 16, sprintf('Abrechnungsmonat %s %d', $_POST['zeitraum_monat'], $_POST['zeitraum_jahr']));
			
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Arial', 'B' , 14);
			$ref_pdf->Text(140, 16, $mitglied['acb_nr']);
			$ref_pdf->SetFont('Arial', 'BI' , 10);
			$ref_pdf->Text(154, 15, utf8_decode($mitglied['vorname']));
			$ref_pdf->Text(154, 19, utf8_decode($mitglied['nachname']));
			
			// Linienbreite einstellen, 0.2 mm
			$ref_pdf->SetLineWidth(0.2);
			// Linie(n) zeichnen
			$ref_pdf->Line(72, 17.5, 125, 17.5);

			// Schriftart hinzufügen
			// Schriftgrad einstellen
			$ref_pdf->SetFont('Arial Narrow', '', 8);
			$ref_pdf->Text(21,    26, utf8_decode('Tag'));
			$ref_pdf->Text(21,    29, utf8_decode('Mon'));
			$ref_pdf->Text(29,    26, utf8_decode('Flugzeug'));
			$ref_pdf->Text(29,    29, utf8_decode('Typ'));
			$ref_pdf->Text(45,    26, utf8_decode('Kenn-'));
			$ref_pdf->Text(45,    29, utf8_decode('zeich'));
			$ref_pdf->Text(45,  32.5, utf8_decode('D-'));
			$ref_pdf->Text(57,    26, utf8_decode('Pilot'));
			$ref_pdf->Text(77,    26, utf8_decode('Copilot'));
			$ref_pdf->Text(97,    26, utf8_decode('St'));
			$ref_pdf->Text(97,    29, utf8_decode('art'));
			$ref_pdf->Text(102,   26, utf8_decode('Startort'));
			$ref_pdf->Text(102,   29, utf8_decode('Zahlungen'));
			$ref_pdf->Text(127,   26, utf8_decode('Landeort'));
			$ref_pdf->Text(152,   26, utf8_decode('St-'));
			$ref_pdf->Text(152,   29, utf8_decode('zeit'));
			$ref_pdf->Text(160,   26, utf8_decode('Ld-'));
			$ref_pdf->Text(160,   29, utf8_decode('zeit'));
			$ref_pdf->Text(168,   26, utf8_decode('Flug-'));
			$ref_pdf->Text(168,   29, utf8_decode('zeit'));
			$ref_pdf->Text(168, 32.5, utf8_decode('min'));
			$ref_pdf->Text(175,   26, utf8_decode('Ab-'));
			$ref_pdf->Text(175,   29, utf8_decode('rech'));
			$ref_pdf->Text(186,   26, utf8_decode('Saldo'));
			$ref_pdf->Text(187, 32.5, utf8_decode('Euro'));

			// Linienbreite einstellen, 0.1 mm
			$ref_pdf->SetLineWidth(0.1);
			// Linie(n) zeichnen
			$ref_pdf->Line(19,   30, 195,   30);
			$ref_pdf->Line(19, 33.3, 195, 33.3);
			$ref_pdf->Line(19,   34, 195,   34);
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
			$ref_pdf->Text(90,  284, utf8_decode('Konto: Volksbank Butzbach, BLZ 51861403, Kto.-Nr: 37052'));
			$ref_pdf->Text(187, 284, sprintf('%d / {nb}', $ref_pdf->PageNo()));
			
			// Schriftfarbe setzen
			$ref_pdf->SetTextColor(0, 0, 0);
		}
	}
	
	// Laufzeit des Skriptes setzen
	set_time_limit(1000);
	
	// PDF-Bibliothek einbinden
	require_once('./pdf/fpdf.php');
	
	// alle Mitgliedernummern für die spätere Auslese ermitteln
	$mitgl_nr = getMitgliedsnummern();
	
	$pdf_all = new FPDF('P', 'mm', 'A4');
	// ... entspricht dem Aufruf von
	$pdf_all->AliasNbPages('{nb}');
	// Automatischen Seitenumbruch deaktivieren
	$pdf_all->SetAutoPageBreak(false);
	// Seitenabstand definieren
	$pdf_all->SetMargins(25, 15, 15);
	
	foreach ($mitgl_nr as $mitglied) {
		// die Fluggeldkonto des Mitglieds ermitteln
		$data = getFluggeldkonto($mitglied['acb_nr'], $_POST['zeitraum_monat'], $_POST['zeitraum_jahr']);
		
		// es wird geprüft, ob überhaupt Daten zur Mitgliedsnummer
		// und dem aktuell ausgewählten Zeitraum vorliegen
		if (!empty($data)) {
			// neues PDF-Dokument erzeugen
			$pdf = new FPDF('P', 'mm', 'A4');
			
			// ... entspricht dem Aufruf von
			$pdf->AliasNbPages('{nb}');
			
			// Automatischen Seitenumbruch deaktivieren
			$pdf->SetAutoPageBreak(false);
			
			// Seitenabstand definieren
			$pdf->SetMargins(25, 15, 15);

			$fgk  = getFluggeldkontoPdf($data);
			$data = $fgk['flugbuch'];
			$saldo_ende   = getFluggeldkontoSaldo($mitglied['acb_nr'], $_POST['zeitraum_monat'], $_POST['zeitraum_jahr']);
			$saldo_anfang = $saldo_ende - $fgk['umsatz'];

			// die notwendigen Information zum Speichern der Konten-
			// salden ermitteln und in das Array zur Übergabe speichern
			$params = array(
				'acb_nr'       => $mitglied['acb_nr'],
				'zyklus'       => sprintf('%s%s', $_POST['zeitraum_jahr'], $_POST['zeitraum_monat']),
				'jahr'         => $_POST['zeitraum_jahr'],
				'monat'        => $_POST['zeitraum_monat'],
				'saldo_anfang' => $saldo_anfang,
				'saldo_ende'   => $saldo_ende
			);

			// die aktuellen Kontensalden für den Mitglied speichern
			setMonatssalden($params);

			// ******************************************************** SEITE ******************************************************** //
			// Seite hinzufügen
			$pdf->AddPage();
			$pdf_all->AddPage();
			
			// Schriftart hinzufügen
			$pdf->AddFont('Arial Narrow', '', 'c0bd260bcc2709f99785311b28a9541f_arialn.php');
			$pdf_all->AddFont('Arial Narrow', '', 'c0bd260bcc2709f99785311b28a9541f_arialn.php');
			
			// Kopfzeile auf der aktuellen Seite hinzufügen
			pdf_header($pdf, $mitglied);
			pdf_header($pdf_all, $mitglied);
			
			$y = 35;
			$flugzeit_gesamt = 0.0;
			$flugzeit_pilot  = 0.0;
			$flugzeit_starts = 0;
			$first = 0;
			
			foreach ($data as $zeile) {
				// aktuellen Eintrag, separiert per Semikolon,
				// splitten und anschließend als Array wiedergeben
				$item = explode(';', $zeile);
			
				$pdf->SetXY(20, $y);
				$pdf_all->SetXY(20, $y);
				
				// Schriftgrad einstellen
				$pdf->SetFont('Arial Narrow', '', 8);
				$pdf_all->SetFont('Arial Narrow', '', 8);
				
				// Füllfarbe auf weiß setzen
				$pdf->SetFillColor(255, 255, 255);
				$pdf_all->SetFillColor(255, 255, 255);
				
				if ($first == 0) {
					$vormonat = date('Ym', mktime(0, 0, 0, ($_POST['zeitraum_monat'] - 1), 1, $_POST['zeitraum_jahr']));
				
					$pdf->Cell(81, 4, sprintf('01.%s.', $_POST['zeitraum_monat']), 0, 0, 'L', 1);
					$pdf_all->Cell(81, 4, sprintf('01.%s.', $_POST['zeitraum_monat']), 0, 0, 'L', 1);
					$pdf->Cell(73, 4, sprintf('Saldo %s', $vormonat), 0, 0, 'L', 1);
					$pdf_all->Cell(73, 4, sprintf('Saldo %s', $vormonat), 0, 0, 'L', 1);
					$pdf->Cell(19, 4, number_format($saldo_anfang, 2, ',', ''), 0, 0, 'R', 1);
					$pdf_all->Cell(19, 4, number_format($saldo_anfang, 2, ',', ''), 0, 0, 'R', 1);

					// Y-Position erhöhen
					$y += 4;
					$pdf->SetXY(20, $y);
					$pdf_all->SetXY(20, $y);
				}
			
				$pdf->Cell(8,  4, $item[FLUGDATUM], 0, 0, 'L', 1);
				$pdf->Cell(16, 4, $item[LFZ_TYP],   0, 0, 'L', 1);
				$pdf->Cell(12, 4, $item[LFZ_KENNZ], 0, 0, 'L', 1);
				$pdf->Cell(20, 4, $item[PILOT],     0, 0, 'L', 1);
				$pdf->Cell(20, 4, $item[CO_PILOT],  0, 0, 'L', 1);
				$pdf->Cell(5,  4, $item[STARTART],  0, 0, 'L', 1);
				$pdf->Cell(25, 4, $item[STARTORT],  0, 0, 'L', 1);
				$pdf->Cell(25, 4, $item[LANDEORT],  0, 0, 'L', 0);
				$pdf->Cell(8,  4, $item[STARTZEIT], 0, 0, 'L', 1);
				$pdf->Cell(8,  4, $item[LANDEZEIT], 0, 0, 'L', 1);
				$pdf->Cell(6,  4, $item[FLUGDAUER], 0, 0, 'R', 1);
				$pdf->Cell(6,  4, ' ' . $item[PREIS_KAT], 0, 0, 'L', 1);
				$pdf->Cell(14, 4, $item[ZW_SALDO],  0, 0, 'R', 1);
				
				$pdf_all->Cell(8,  4, $item[FLUGDATUM], 0, 0, 'L', 1);
				$pdf_all->Cell(16, 4, $item[LFZ_TYP],   0, 0, 'L', 1);
				$pdf_all->Cell(12, 4, $item[LFZ_KENNZ], 0, 0, 'L', 1);
				$pdf_all->Cell(20, 4, $item[PILOT],     0, 0, 'L', 1);
				$pdf_all->Cell(20, 4, $item[CO_PILOT],  0, 0, 'L', 1);
				$pdf_all->Cell(5,  4, $item[STARTART],  0, 0, 'L', 1);
				$pdf_all->Cell(25, 4, $item[STARTORT],  0, 0, 'L', 1);
				$pdf_all->Cell(25, 4, $item[LANDEORT],  0, 0, 'L', 0);
				$pdf_all->Cell(8,  4, $item[STARTZEIT], 0, 0, 'L', 1);
				$pdf_all->Cell(8,  4, $item[LANDEZEIT], 0, 0, 'L', 1);
				$pdf_all->Cell(6,  4, $item[FLUGDAUER], 0, 0, 'R', 1);
				$pdf_all->Cell(6,  4, ' ' . $item[PREIS_KAT], 0, 0, 'L', 1);
				$pdf_all->Cell(14, 4, $item[ZW_SALDO],  0, 0, 'R', 1);
				
				$flugzeit_gesamt += $item[FLUGDAUER];
				$first++;
				
				if ($item[PILOT] == utf8_decode($mitglied['nachname'])) {
					$flugzeit_pilot += $item[FLUGDAUER];
					$flugzeit_starts++;
				}
			
				// Y-Position erhöhen
				$y += 4;
				
				if ($y > 275) {
					// Fusszeile auf der aktuellen Seite hinzufügen
					pdf_footer($pdf);
					pdf_footer($pdf_all);

					// Seite hinzufügen
					$pdf->AddPage();
					$pdf_all->AddPage();
					
					// Kopfzeile auf der aktuellen Seite hinzufügen
					pdf_header($pdf, $mitglied);
					pdf_header($pdf_all, $mitglied);
					
					// Y-Wert zurücksetzen
					$y = 35;
				}
			}
			
			// Linienbreite einstellen, 0.1 mm
			$pdf->SetLineWidth(0.1);
			$pdf_all->SetLineWidth(0.1);
			// Linie(n) zeichnen
			$pdf->Line(19, $y, 195, $y);
			$pdf_all->Line(19, $y, 195, $y);
			$pdf->Line(19, $y + 0.7, 195, $y + 0.7);
			$pdf_all->Line(19, $y, 195, $y);
			
			$pdf->SetFont('Arial', 'BI', 9);
			$pdf_all->SetFont('Arial', 'BI', 9);
			$pdf->SetXY(101, $y + 1);
			$pdf_all->SetXY(101, $y + 1);
			
			// Füllfarbe auf weiß setzen
			$pdf->SetFillColor(255, 255, 255);
			$pdf_all->SetFillColor(255, 255, 255);
			
			$pdf->Cell(66, 5, utf8_decode('Flugzeit gesamt in Stunden'), 0, 0, 'L');
			$pdf_all->Cell(66, 5, utf8_decode('Flugzeit gesamt in Stunden'), 0, 0, 'L');
			$pdf->Cell(6,  5, number_format(($flugzeit_gesamt / 60), 2, ',', ''), 0, 0, 'R');
			$pdf_all->Cell(6,  5, number_format(($flugzeit_gesamt / 60), 2, ',', ''), 0, 0, 'R');

			$pdf->SetXY(101, $y + 7);
			$pdf_all->SetXY(101, $y + 7);
			// Linie(n) zeichnen
			$pdf->Line(100, $y + 6.5, 195, $y + 6.5);
			$pdf_all->Line(100, $y + 6.5, 195, $y + 6.5);
			
			$pdf->SetFont('Arial', 'BI', 8);
			$pdf_all->SetFont('Arial', 'BI', 8);
			$pdf->Cell(30, 5, utf8_decode('Pilot: Anzahl Starts :'), 0, 0, 'L');
			$pdf_all->Cell(30, 5, utf8_decode('Pilot: Anzahl Starts :'), 0, 0, 'L');
			$pdf->Cell(6,  5, $flugzeit_starts, 0, 0, 'R');
			$pdf_all->Cell(6,  5, $flugzeit_starts, 0, 0, 'R');
			$pdf->Cell(30, 5, utf8_decode('   Flugzeit in Std. :'), 0, 0, 'L');
			$pdf_all->Cell(30, 5, utf8_decode('   Flugzeit in Std. :'), 0, 0, 'L');
			$pdf->Cell(6,  5, number_format(($flugzeit_pilot / 60), 2, ',', ''), 0, 0, 'R');
			$pdf_all->Cell(6,  5, number_format(($flugzeit_pilot / 60), 2, ',', ''), 0, 0, 'R');
			$pdf->SetXY(101, $y + 12);
			$pdf_all->SetXY(101, $y + 12);
			// Linie(n) zeichnen
			$pdf->Line(100, $y + 12, 195, $y + 12);
			$pdf_all->Line(100, $y + 12, 195, $y + 12);
			$pdf->Cell(77, 5, utf8_decode('Neuer Saldo in EURO'), 0, 0, 'L');
			$pdf_all->Cell(77, 5, utf8_decode('Neuer Saldo in EURO'), 0, 0, 'L');
			$pdf->Cell(17, 5, number_format($saldo_ende, 2, ',', '') . ' Euro', 0, 0, 'R');
			$pdf_all->Cell(17, 5, number_format($saldo_ende, 2, ',', '') . ' Euro', 0, 0, 'R');
			// Linie(n) zeichnen
			$pdf->Line(100, $y + 17, 195, $y + 17);
			$pdf_all->Line(100, $y + 17, 195, $y + 17);
			
			if ($y > 35) {
				// letzte Fusszeile auf der aktuellen Seite hinzufügen
				pdf_footer($pdf);
				pdf_footer($pdf_all);
			}
			
			// ******************************************************** SEITE ******************************************************** //

			// PDF-Dokument ausgeben
			$pdf->Output(sprintf(
				'abrech/%s/%s/%s.pdf',
				$_POST['zeitraum_jahr'], $_POST['zeitraum_monat'], md5($mitglied['acb_nr'])
			), 'F');
		}
	}
	
	// PDF-Dokument (gesamt) ausgeben
	$pdf_all->Output(sprintf(
		'abrech/%s/%s/Abrechn_%s%s_Rev_01.pdf',
		$_POST['zeitraum_jahr'], $_POST['zeitraum_monat'],
		$_POST['zeitraum_jahr'], $_POST['zeitraum_monat']
	), 'F');

?>