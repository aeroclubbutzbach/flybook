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
	 * getMitgliederliste()
	 *
	 * gibt die Mitgliederliste anhand der übergebenen Parameter zurück
	 *
	 * @params array $params
	 * @return array $data
	 */
	if (!function_exists('getMitgliederliste')) {
		function getMitgliederliste(array $params)
		{
			// Rückgabe-Array definieren
			$data = array();
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');
			require_once('functions.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
			// die aktuellen Mitglieder werden ermittelt
			$sql = sprintf('
				SELECT
					`mitglieder`.*,
					`mitgliedschaft`.`bezeichnung`
				FROM
					`mitglieder`
				INNER JOIN
					`mitgliedschaft` ON `mitglieder`.`status` = `mitgliedschaft`.`id`
				WHERE
					`mitglieder`.`status` = "%s" AND `mitglieder`.`in_abrechn` = "J"
				ORDER BY
					%s ASC
			',
				$params['Mitgliedsstatus'],
				$params['Sortierung']
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Zählervariable initialisieren
			$i = 0;

			// es sind Datensätze vorhanden
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
			
				// Daten übernehmen wie hinterlegt
				$data[$i]['mitgliedschaft'] = utf8_encode($zeile->bezeichnung);
				$data[$i]['vorname']        = utf8_encode($zeile->vorname);
				$data[$i]['nachname']       = utf8_encode($zeile->nachname);
				$data[$i]['strasse']        = utf8_encode($zeile->strasse);
				$data[$i]['plz']            = utf8_encode($zeile->plz);
				$data[$i]['ort']            = utf8_encode($zeile->ort);
				$data[$i]['telefon1']       = utf8_encode($zeile->telefon1);
				$data[$i]['telefon2']       = utf8_encode($zeile->telefon2);
				$data[$i]['mobil1']         = utf8_encode($zeile->mobil1);
				$data[$i]['mobil2']         = utf8_encode($zeile->mobil2);
				$data[$i]['email']          = utf8_encode($zeile->email);
				
				// Zähler erhöhen
				$i++;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Mitgliederliste
			return $data;
		}
	}
	
	/*
	 * printMitgliederliste()
	 *
	 * gibt die Mitgliederliste im PDF-Format anhand der übergebenen Parameter zurück
	 *
	 * @params object $ref_pdf
	 * @params float  $x
	 * @params float  $y
	 * @params char   $status
	 * @params string $sort
	 */
	if (!function_exists('printMitgliederliste')) {
		function printMitgliederliste(& $ref_pdf, & $x, & $y, $status, $sort)
		{
			// Mitgliedsdaten laden
			$data = getMitgliederliste(array('Mitgliedsstatus' => $status, 'Sortierung' => $sort));
			
			// Schriftfarbe setzen
			$ref_pdf->SetTextColor(0, 0, 0);
			// Füllung auf GRAU einstellen
			$ref_pdf->SetFillColor(192, 192, 192);
			
			if (isset($data[0]['mitgliedschaft'])) {
				// Schriftart festlegen
				$ref_pdf->SetFont('Arial', 'B' , 14);
				
				// X-Koordinate festlegen
				$ref_pdf->SetXY($x, $y);
				
				$ref_pdf->Cell(80, 7, utf8_decode(mb_strtoupper($data[0]['mitgliedschaft'], 'UTF-8')), 1, 1, 'C', 1);
				$ref_pdf->Ln();
				// neue Position auf der Y-Achse
				$y += 13;
			}

			foreach ($data as $mitglied) {
				// Schriftart festlegen
				$ref_pdf->SetFont('Arial', 'B' , 10);
				// Schriftfarbe setzen
				$ref_pdf->SetTextColor(0, 0, 0);
				
				// Rechteck zeichnen, mit Füllung
				$ref_pdf->Rect($x, $y - 2.3, 2, 2, 'F');
				
				$ref_pdf->Text($x + 3.5, $y, utf8_decode(
					mb_strtoupper(sprintf('%s, %s', $mitglied['nachname'], $mitglied['vorname']), 'UTF-8')
				));

				// Schriftart festlegen
				$ref_pdf->SetFont('Arial', '' , 9);
				
				// neue Position auf der Y-Achse
				$y += 4;
			
				$ref_pdf->Text($x, $y, utf8_decode(
					sprintf('%s,  %s %s', $mitglied['strasse'], $mitglied['plz'], $mitglied['ort'])
				));
				
				// neue Position auf der Y-Achse
				$y += 4;
				
				if (
					!empty($mitglied['telefon1']) || !empty($mitglied['telefon2']) ||
					!empty($mitglied['mobil1']) || !empty($mitglied['mobil2'])
				) {
					$ref_pdf->Text($x, $y, utf8_decode('Tel.:'));
				}
				
				if (!empty($mitglied['telefon1'])) {
					// Telefon (privat) einfügen
					$ref_pdf->Text($x + 12, $y, utf8_decode('privat'));
					$ref_pdf->Text($x + 23, $y, utf8_decode(
						sprintf('%s', $mitglied['telefon1'])
					));
					// neue Position auf der Y-Achse
					$y += 4;
				}
				
				if (!empty($mitglied['telefon2'])) {
					// Telefon (dienstlich) einfügen
					$ref_pdf->Text($x + 12, $y, utf8_decode('dienstl.'));
					$ref_pdf->Text($x + 23, $y, utf8_decode(
						sprintf('%s', $mitglied['telefon2'])
					));
					// neue Position auf der Y-Achse
					$y += 4;
				}
				
				if (!empty($mitglied['mobil1'])) {
					// Handy-Nummer 1 einfügen
					$ref_pdf->Text($x + 12, $y, utf8_decode('mobil'));
					$ref_pdf->Text($x + 23, $y, utf8_decode(
						sprintf('%s', $mitglied['mobil1'])
					));
					$y += 4;
					// neue Position auf der Y-Achse
				}
				
				if (!empty($mitglied['mobil2'])) {
					// Handy-Nummer 2 einfügen
					$ref_pdf->Text($x + 12, $y, utf8_decode('mobil'));
					$ref_pdf->Text($x + 23, $y, utf8_decode(
						sprintf('%s', $mitglied['mobil2'])
					));
					// neue Position auf der Y-Achse
					$y += 4;
				}
				
				if (!empty($mitglied['email'])) {
					// eMail-Adresse einfügen
					$ref_pdf->Text($x, $y, utf8_decode('E-Mail:'));
					
					// Schriftart und -farbe ändern und Link verdeutlichen
					$ref_pdf->SetTextColor(0, 0, 255);
					$ref_pdf->SetFont('Arial', 'U' , 9);
					
					$ref_pdf->Text($x + 23, $y, utf8_decode(
						sprintf('%s', $mitglied['email'])
					));
					// neue Position auf der Y-Achse
					$y += 4;
				}

				// neue Position auf der Y-Achse
				$y += 5;
				
				if (($y > 260) && ($x < 115)) {
					// Position auf der X- und Y-Achse
					$y = 17;
					$x = 115;
					
					if ($ref_pdf->PageNo() == 1) {
						$y = 65;
					}
				} else if (($y > 260) && ($x == 115)) {
					// eine neue Seite einfügen
					$ref_pdf->AddPage();
					
					// Position auf der X- und Y-Achse
					$ref_pdf->SetXY(20, 280);
					// Schriftart und -farbe ändern
					$ref_pdf->SetTextColor(128, 128, 128);
					$ref_pdf->SetFont('Arial', '' , 8);
					// Ausgabe des Fusszeilentext
					$ref_pdf->Cell(0, 10, 'Seite ' . $ref_pdf->PageNo() . ' von {nb}', 0, 0, 'C');
				
					// Position auf der X- und Y-Achse
					$x = 20;
					$y = 17;
				}
			}
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

	// Logo auf erster Seite einfügen
	$pdf->Image('./img/acb_logo_gross.jpg', 95, 10, 25);

	// Position auf der X- und Y-Achse
	$pdf->SetY(40);
	// Schriftart festlegen
	$pdf->SetFont('Arial', 'BU' , 14);
	$pdf->Cell(160, 7, utf8_decode('Anschriften der Mitglieder, Aero-Club Butzbach e.V.'), 0, 1, 'C');

	// Schriftart festlegen
	$pdf->SetFont('Arial', '' , 11);
	$pdf->Cell(160, 5, sprintf(utf8_decode('Stand: %s %d'), utf8_decode($_monate[date('n')]), date('Y')), 0, 1, 'C');
	
	// Position auf der X- und Y-Achse
	$pdf->SetXY(20, 280);
	// Schriftart und -farbe ändern
	$pdf->SetTextColor(128, 128, 128);
	$pdf->SetFont('Arial', '' , 8);
	// Ausgabe des Fusszeilentext
	$pdf->Cell(0, 10, 'Seite ' . $pdf->PageNo() . ' von {nb}', 0, 0, 'C');
	
	// Position auf der X- und Y-Achse
	$x = 20;
	$y = 63;

	// Mitgliederlisten ausgeben
	// Vorstand
	printMitgliederliste($pdf, $x, $y, 'V', '`sort`');
	// Fluglehrer
	printMitgliederliste($pdf, $x, $y, 'L', '`sort`');
	// Technisches Personal
	printMitgliederliste($pdf, $x, $y, 'T', '`sort`');
	// Flugschüler
	printMitgliederliste($pdf, $x, $y, 'S', '`nachname`, `vorname`');
	// Aktive Mitglieder
	printMitgliederliste($pdf, $x, $y, 'A', '`nachname`, `vorname`');
	// Ehrenmitglieder
	printMitgliederliste($pdf, $x, $y, 'E', '`nachname`, `vorname`');
	// Passive Mitglieder
	printMitgliederliste($pdf, $x, $y, 'P', '`nachname`, `vorname`');
	// ******************************************************** SEITE ******************************************************** //

	// PDF-Dokument ausgeben
	$pdf->Output(sprintf('%s.pdf', md5('mitgliederliste')), 'I');

?>