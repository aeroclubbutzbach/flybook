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
	 * getFlugleiterliste()
	 *
	 * gibt die Flugleiterliste anhand der übergebenen Parameter zurück
	 *
	 * @return array $data
	 */
	if (!function_exists('getFlugleiterliste')) {
		function getFlugleiterliste()
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
					`mitglieder`.*
				FROM
					`mitglieder`
				WHERE
					FIND_IN_SET("U", `mitglieder`.`fachausweise`) AND
					`mitglieder`.`in_abrechn` = "J"
				ORDER BY
					`nachname`, `vorname` ASC
			');
			
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
				$data[$i]['vorname']              = utf8_encode($zeile->vorname);
				$data[$i]['nachname']             = utf8_encode($zeile->nachname);
				$data[$i]['strasse']              = utf8_encode($zeile->strasse);
				$data[$i]['plz']                  = utf8_encode($zeile->plz);
				$data[$i]['ort']                  = utf8_encode($zeile->ort);
				$data[$i]['telefon1']             = utf8_encode($zeile->telefon1);
				$data[$i]['telefon2']             = utf8_encode($zeile->telefon2);
				$data[$i]['mobil1']               = utf8_encode($zeile->mobil1);
				$data[$i]['mobil2']               = utf8_encode($zeile->mobil2);
				$data[$i]['email']                = utf8_encode($zeile->email);
				$data[$i]['fl_dienst_absprache']  = utf8_encode($zeile->fl_dienst_absprache);
				$data[$i]['fl_dienst_wochentags'] = utf8_encode($zeile->fl_dienst_wochentags);
				
				// Zähler erhöhen
				$i++;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Flugleiterliste
			return $data;
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
	$pdf->SetFont('Times', 'BU' , 14);
	$pdf->Cell(160, 7, utf8_decode('Telefonnummern der Flugleiter/-innen, Aero-Club Butzbach e.V.'), 0, 1, 'C');   

	// Schriftart festlegen
	$pdf->SetFont('Times', '' , 11);
	$pdf->Cell(160, 5, sprintf(utf8_decode('Stand: %s %d'), utf8_decode($_monate[date('n')]), date('Y')), 0, 1, 'C');
	
	// Position auf der X- und Y-Achse
	$pdf->SetXY(20, 280);
	// Schriftart und -farbe ändern
	$pdf->SetTextColor(128, 128, 128);
	$pdf->SetFont('Times', '' , 8);
	// Ausgabe des Fusszeilentext
	$pdf->Cell(0, 10, 'Seite ' . $pdf->PageNo() . ' von {nb}', 0, 0, 'C');
	
	// Position auf der X- und Y-Achse
	$x = 20;
	$y = 63;

	// alle Flugleiter des Vereins ermitteln
	$data = getFlugleiterliste();

	// Schriftfarbe setzen
	$pdf->SetTextColor(0, 0, 0);

	foreach ($data as $mitglied) {
		// Schriftart festlegen
		$pdf->SetFont('Times', 'B' , 11);
		// Schriftfarbe setzen
		$pdf->SetTextColor(0, 0, 0);
		
		$pdf->SetXY($x, $y);
		$pdf->Write(5, utf8_decode(mb_strtoupper($mitglied['nachname'], 'UTF-8')));
		
		if ($mitglied['fl_dienst_wochentags'] == 'J') {
			$pdf->SetFont('Times', 'B' , 16);
			$pdf->SetTextColor(255, 0, 0);
			$pdf->Write(5, utf8_decode('*'));
		}
		
		// Schriftfarbe setzen
		$pdf->SetTextColor(0, 0, 0);
		// Schriftart festlegen
		$pdf->SetFont('Times', '' , 11);
		$pdf->Write(5, utf8_decode(sprintf(', %s', $mitglied['vorname'])));

		if ($mitglied['fl_dienst_absprache'] == 'J') {
			// neue Position auf der X-Achse
			$pdf->SetX(80);
			// Schriftart festlegen
			$pdf->SetFont('Times', 'B' , 11);
			$pdf->SetTextColor(255, 0, 0);
			$pdf->Write(5, utf8_decode('(n.n.R.)'));
		}

		// Schriftart festlegen
		$pdf->SetFont('Times', '' , 11);
		$pdf->SetTextColor(0, 0, 0);
		
		$y_alt = $pdf->GetY();
		$offset = 0;
		
		if (!empty($mitglied['telefon1'])) {
			$pdf->SetX(110);
			$pdf->Write(5, utf8_decode(sprintf('%s (privat)', $mitglied['telefon1'])));
			$y += 5;
			$offset += 5;
		}
		
		if (!empty($mitglied['telefon2'])) {
			$pdf->SetXY(110, $y);
			$pdf->Write(5, utf8_decode(sprintf('%s (dienstl.)', $mitglied['telefon2'])));
			$y += 5;
			$offset += 5;
		}
		
		if (!empty($mitglied['mobil1'])) {
			$pdf->SetXY(110, $y);
			$pdf->Write(5, utf8_decode(sprintf('Handy: %s (privat)', $mitglied['mobil1'])));
			$y += 5;
			$offset += 5;
		}
		
		if (!empty($mitglied['mobil2'])) {
			$pdf->SetXY(110, $y);
			$pdf->Write(5, utf8_decode(sprintf('Handy: %s (dienstl.)', $mitglied['mobil2'])));
			$y += 5;
			$offset += 5;
		}
		
		if (!empty($mitglied['email'])) {
			$pdf->SetXY(110, $y);
			$pdf->Write(5, utf8_decode('eMail: '));
			
			// Schriftart und -farbe ändern und Link verdeutlichen
			$pdf->SetTextColor(0, 0, 255);
			$pdf->SetFont('Times', 'U' , 11);
			
			$pdf->Write(5, utf8_decode($mitglied['email']));
			$offset += 5;
		}
		
		// Schriftart und -farbe ändern
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Times', '' , 11);
		
		// der Offset muss mindestens 15 mm betragen
		if ($offset < 15) {
			$offset = 15;
		}
		
		// neue Position auf der Y-Achse
		$y = $y_alt + 5;
		$pdf->SetXY($x, $y);
		$pdf->Write(5, utf8_decode($mitglied['strasse']));
		// neue Position auf der Y-Achse
		$y += 5;
		$pdf->SetXY($x, $y);
		$pdf->Write(5, utf8_decode(sprintf('%s %s', $mitglied['plz'], $mitglied['ort'])));
		
		// neue Position auf der Y-Achse
		$y += ($offset - 5);
		
		if ($y > 260) {
			// eine neue Seite einfügen
			$pdf->AddPage();
			
			// Position auf der X- und Y-Achse
			$pdf->SetXY(20, 280);
			// Schriftart und -farbe ändern und Link verdeutlichen
			$pdf->SetTextColor(128, 128, 128);
			$pdf->SetFont('Arial', '' , 8);
			// Ausgabe des Fusszeilentext
			$pdf->Cell(0, 10, 'Seite ' . $pdf->PageNo() . ' von {nb}', 0, 0, 'C');
		
			// Position auf der X- und Y-Achse
			$x = 20;
			$y = 17;
		}
	}
	
	// Position auf der X- und Y-Achse
	$pdf->SetXY($x, $y + 5);
	// Schriftart festlegen
	$pdf->SetFont('Times', 'B' , 12);
	$pdf->SetTextColor(255, 0, 0);
	$pdf->Write(5, utf8_decode('(n.n.R.): '));
	// Schriftart festlegen
	$pdf->SetFont('Times', '' , 12);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Write(5, utf8_decode('Flugleiter-Dienst nur nach Absprache'));
	// Position auf der X- und Y-Achse
	$pdf->SetXY($x, $y + 15);
	// Schriftart festlegen
	$pdf->SetFont('Times', 'B' , 16);
	$pdf->SetTextColor(255, 0, 0);
	$pdf->Write(5, utf8_decode('* '));
	// Schriftart festlegen
	$pdf->SetFont('Times', '' , 12);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Write(5, utf8_decode('können im Bedarfsfall angesprochen werden um Flüge außerhalb der'));
	// Position auf der X- und Y-Achse
	$pdf->SetXY($x, $y + 20);
	$pdf->Write(5, utf8_decode('offiziellen Betriebszeiten rauszulassen!'));
	// Position auf der X- und Y-Achse
	$pdf->SetXY($x, $y + 35);	
	// Schriftart festlegen
	$pdf->SetFont('Arial', 'B' , 12);
	$pdf->SetTextColor(255, 0, 0);
	$pdf->Cell(160, 5, utf8_decode('Telefonliste ist nur für den internen Gebrauch bestimmt.'), 0, 1, 'C');
	$pdf->Cell(160, 5, utf8_decode('Eine Weitergabe an Dritte ist aus Datenschutzgründen untersagt!'), 0, 1, 'C');
	
	// ******************************************************** SEITE ******************************************************** //

	// PDF-Dokument ausgeben
	$pdf->Output(sprintf('%s.pdf', md5('flugleiter')), 'I');

?>