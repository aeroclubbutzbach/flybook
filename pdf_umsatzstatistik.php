<?php

	/*
	 * getTabelleUmsatzstatistik()
	 *
	 * gibt die Tabelle der Umsatzstatistik zurück
	 *
	 * @params integer $jahr
	 * @params integer $monat
	 * @return string  $html
	 */
	if (!function_exists('getTabelleUmsatzstatistik')) {
		function getTabelleUmsatzstatistik($jahr, $monat)
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
			// Flüge für den festgelegten Zeitraum ermitteln
			$sql = sprintf('
				SELECT
					`preiskategorien`.`kennzeichen`,
					`preiskategorien`.`muster`,
					`flugbuch`.`preiskategorie`,
					`preiskategorien`.`rechnungstext`,
					`preiskategorien`.`startart`,
					`preiskategorien`.`flugart`,
					SUM(`flugbuch`.`anteilsumme_1` + `flugbuch`.`anteilsumme_2`) AS `summe`,
					COUNT(*) AS `fluege`,
					`preiskategorien`.`mwst_satz`
				FROM
					`flugbuch`
				INNER JOIN
					`preiskategorien`
					ON (`flugbuch`.`luftfahrzeug` = `preiskategorien`.`kennzeichen` AND `flugbuch`.`preiskategorie` = `preiskategorien`.`id`)
				WHERE
					`flugbuch`.`datum` BETWEEN "%d-%d-01" AND "%d-%d-%d"
				GROUP BY
					`flugbuch`.`luftfahrzeug`,
					`flugbuch`.`preiskategorie`
			',
				$jahr, $monat, $jahr, $monat,
				date('t', strtotime(
					sprintf('%d-%d-01', $jahr, $monat)
				))
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Zählervariable initialisieren
			$i = 0;

			// es sind Datensätze vorhanden
			if (mysql_num_rows($db_erg) > 0) {
				// Array für die Aufsummierung der Umsätze anlegen
				$umsatz = array();
				// Gesamtumsatz aus allen Aufsummierungen
				$umsatz_ges = 0.0;

				while ($zeile = mysql_fetch_object($db_erg)) {
					// Tabelleninhalte zuweisen
					$return['umsaetze'][$i]['kennzeichen']    = utf8_encode($zeile->kennzeichen);
					$return['umsaetze'][$i]['muster']         = utf8_encode($zeile->muster);
					$return['umsaetze'][$i]['preiskategorie'] = utf8_encode($zeile->preiskategorie);
					$return['umsaetze'][$i]['rechnungstext']  = utf8_encode($zeile->rechnungstext);
					$return['umsaetze'][$i]['startart']       = utf8_encode($zeile->startart);
					$return['umsaetze'][$i]['flugart']        = utf8_encode($zeile->flugart);
					$return['umsaetze'][$i]['summe']          = utf8_encode($zeile->summe);
					$return['umsaetze'][$i]['fluege']         = utf8_encode($zeile->fluege);
					$return['umsaetze'][$i]['mwst_satz']      = utf8_encode($zeile->mwst_satz);

					// Umsätze aufsummieren
					$umsatz[$zeile->mwst_satz]['mwst']    = $zeile->mwst_satz;
					$umsatz[$zeile->mwst_satz]['summe']  += $zeile->summe;
					$umsatz[$zeile->mwst_satz]['fluege'] += $zeile->fluege;

					// Zähler erhöhen
					$i++;
				}
				
				// Gesamtumsätze zurückgeben
				$return['gesamtumsatz'] = $umsatz;
			}
			
			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Umsatzstatistik
			return $return;
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

	// ******************************************************** SEITE ******************************************************** //
	// Seite hinzufügen
	$pdf->AddPage();
	
	// Schriftart hinzufügen
	$pdf->AddFont('Arial Narrow', '',  'c0bd260bcc2709f99785311b28a9541f_arialn.php');
	$pdf->AddFont('Arial Narrow', 'B', 'b297a3df7cb283d4eed768a69409e8e1_arialnb.php');

	// Logo auf erster Seite einfügen
	$pdf->Image('./img/acb_logo_gross.jpg', 95, 10, 25);

	// Position auf der X- und Y-Achse
	$pdf->SetY(40);
	// Schriftart festlegen
	$pdf->SetFont('Times', 'BU' , 14);
	$pdf->Cell(160, 7, utf8_decode('Monatsumsätze / -statistik, Aero-Club Butzbach e.V.'), 0, 1, 'C');
	
	// Position auf der X- und Y-Achse
	$pdf->SetY(55);
	// Schriftgrad einstellen
	$pdf->SetFont('Arial', '' , 18);
	$pdf->Cell(160, 10, utf8_decode('Umsätze ') . sprintf('%s / %s', $_GET['monat'], $_GET['jahr']), 1, 1, 'L');

	// Schriftgrad einstellen
	$pdf->SetFont('Arial Narrow', 'B' , 9);
	// Überschriften der Tabelle festlegen
	$pdf->Cell(12, 5, utf8_decode('Kennz.'),                    1, 0, 'L');
	$pdf->Cell(21, 5, utf8_decode('Flugzeug'),                  1, 0, 'L');
	$pdf->Cell(6,  5, utf8_decode(''),                          1, 0, 'L');
	$pdf->Cell(35, 5, utf8_decode('Preiskat. / Rechnungstext'), 1, 0, 'L');
	$pdf->Cell(15, 5, utf8_decode('Startart'),                  1, 0, 'L');
	$pdf->Cell(34, 5, utf8_decode('Flugart'),                   1, 0, 'L');
	$pdf->Cell(17, 5, utf8_decode('Umsatz'),                    1, 0, 'L');
	$pdf->Cell(9,  5, utf8_decode('Flüge'),                     1, 0, 'L');
	$pdf->Cell(11, 5, utf8_decode('MwSt'),                      1, 0, 'L');
	
	// Zeilenumbruch einfügen
	$pdf->Ln();
	
	// Schriftgrad einstellen
	$pdf->SetFont('Arial Narrow', '' , 8);
	
	// Umsätze ermitteln
	$umsaetze = getTabelleUmsatzstatistik($_GET['jahr'], $_GET['monat']);
	
	foreach ($umsaetze['umsaetze'] as $umsatz) {
		// Datensatz eintragen
		$pdf->Cell(12, 5, utf8_decode($umsatz['kennzeichen']),    1, 0, 'L');
		$pdf->Cell(21, 5, utf8_decode($umsatz['muster']),         1, 0, 'L');
		$pdf->Cell(6,  5, utf8_decode($umsatz['preiskategorie']), 1, 0, 'L');
		$pdf->Cell(35, 5, utf8_decode($umsatz['rechnungstext']),  1, 0, 'L');
		$pdf->Cell(15, 5, utf8_decode($umsatz['startart']),       1, 0, 'L');
		$pdf->Cell(34, 5, utf8_decode($umsatz['flugart']),        1, 0, 'L');
		$pdf->Cell(17, 5, utf8_decode(
			sprintf('%s EUR', number_format($umsatz['summe'], 2, ',', ''))
		), 1, 0, 'R');
		$pdf->Cell(9,  5, utf8_decode($umsatz['fluege']), 1, 0, 'R');
		$pdf->Cell(11, 5, utf8_decode(
			sprintf('%s %%', number_format($umsatz['mwst_satz'], 2, ',', ''))
		), 1, 0, 'R');

		// Zeilenumbruch einfügen
		$pdf->Ln();
	}
	
	// Zeilenumbrüche einfügen
	$pdf->Ln();
	$pdf->Ln();
	
	// Schriftgrad einstellen
	$pdf->SetFont('Arial', 'B' , 10);
	$pdf->Cell(20, 6, utf8_decode('MwSt-Satz'),   0, 0, 'R');
	$pdf->Cell(15, 6, utf8_decode('Flüge'),       0, 0, 'C');
	$pdf->Cell(27, 6, utf8_decode('Ges.-Umsatz'), 0, 0, 'R');
	
	// Zeilenumbruch einfügen
	$pdf->Ln();
	
	// vertikale Y-Position ermitteln
	$y = $pdf->getY();
	
	// Linienbreite einstellen, 0.2 mm
	$pdf->SetLineWidth(0.2);
	// Linie(n) zeichnen
	$pdf->Line(25, $y, 90, $y);

	// Schriftgrad einstellen
	$pdf->SetFont('Arial', '' , 10);
	
	// Variable zur Aufsumierung der Umsätze anlegen
	$umsatz_ges = 0.0;
	
	foreach ($umsaetze['gesamtumsatz'] as $key => $val) {
		$pdf->Cell(20, 6, sprintf('%s %%', number_format($key, 2, ',', '')), 0, 0, 'R');
		$pdf->Cell(15, 6, $val['fluege'], 0, 0, 'C');
		$pdf->Cell(27, 6, sprintf('%s EUR', number_format($val['summe'], 2, ',', '')), 0, 0, 'R');
		
		// Umsatz aufsumieren
		$umsatz_ges += $val['summe'];
		
		// Zeilenumbruch einfügen
		$pdf->Ln();
	}
	
	// vertikale Y-Position ermitteln
	$y = $pdf->getY();
	
	// Linie(n) zeichnen
	$pdf->Line(60, $y, 90, $y);
	$pdf->Line(60, $y + 0.6, 90, $y + 0.6);
	
	// neues X- und Y-Position festlegen
	$pdf->setY($y + 1);
	$pdf->setX(60);
	
	// Aufsumierten Gesamtumsatz anzeigen
	$pdf->Cell(27, 6, sprintf('%s EUR', number_format($umsatz_ges, 2, ',', '')), 0, 0, 'R');
	
	// Position auf der X- und Y-Achse
	$pdf->SetXY(20, 280);
	// Schriftart und -farbe ändern
	$pdf->SetTextColor(128, 128, 128);
	$pdf->SetFont('Times', '' , 8);
	// Ausgabe des Fusszeilentext
	$pdf->Cell(0, 10, 'Seite ' . $pdf->PageNo() . ' von {nb}', 0, 0, 'C');
	
	// ******************************************************** SEITE ******************************************************** //

	// PDF-Dokument ausgeben
	$pdf->Output(
		sprintf('monatsumsaetze-%s-%s.pdf',
		$_GET['jahr'], $_GET['monat']),
	'I');

?>