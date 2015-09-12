<?php

	/*
	 * getMitglied()
	 *
	 * alle Mitgliedsdaten zum ausgewählten Mitglied werden geladen
	 *
	 * @params string $acb_nr
	 * @return array  $data
	 */
	if (!function_exists('getMitglied')) {
		function getMitglied($acb_nr)
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
			// die Daten für das aktuell ausgewählte Mitglied laden
			$sql = sprintf('
				SELECT
					*
				FROM
					`mitglieder`
				WHERE
					`id` = %d
				LIMIT 1
			',
				$acb_nr
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// es sind Datensätze vorhanden
			while ($zeile = mysql_fetch_object($db_erg)) {
				// Mitgliedsstatus ermitteln
				if ($zeile->status != 'P' && $zeile->status != 'E') {
					if (!empty($zeile->geburtsdatum)) {
						$alter = floor((time() - strtotime($zeile->geburtsdatum)) / (60 * 60 * 24 * 365));
					} else {
						$alter = 0;
					}
					
					// Geburtstag prüfen, ob Jugendmitglied
					$zeile->status = (($alter <= 25) && ($alter != 0)) ? 'J' : 'N';
				} else if ($zeile->status == 'E') {
					// Ehrenmitglieder
					$zeile->status = 'R';
				}
				
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
				$data['status']       = utf8_encode($zeile->status);
				$data['anrede']       = utf8_encode($zeile->anrede);
				$data['vorname']      = utf8_encode($zeile->vorname);
				$data['nachname']     = utf8_encode($zeile->nachname);
				$data['geburtsdatum'] = fromSqlDatum($zeile->geburtsdatum);
				$data['strasse']      = utf8_encode($zeile->strasse);
				$data['land']         = utf8_encode($zeile->land);
				$data['plz']          = utf8_encode($zeile->plz);
				$data['ort']          = utf8_encode($zeile->ort);
				$data['telefon1']     = utf8_encode($zeile->telefon1);
				$data['telefon2']     = utf8_encode($zeile->telefon2);
				$data['mobil1']       = utf8_encode($zeile->mobil1);
				$data['mobil2']       = utf8_encode($zeile->mobil2);
				$data['email']        = utf8_encode($zeile->email);
				$data['www']          = utf8_encode($zeile->www);
				$data['bank']         = utf8_encode($zeile->bank);
				$data['blz']          = utf8_encode($zeile->blz);
				$data['kto']          = utf8_encode($zeile->kto);
				$data['ktoinhaber']   = utf8_encode($zeile->ktoinhaber);
				$data['eintritt']     = fromSqlDatum($zeile->eintritt);
				$data['austritt']     = fromSqlDatum($zeile->austritt);
				$data['datenschutz']  = utf8_encode($zeile->datenschutz);
				$data['rundmail']     = utf8_encode($zeile->rundmail);
				$data['funktion']     = utf8_encode($zeile->funktion); 
				$data['ppladat']      = fromSqlDatum($zeile->ppladat);
				$data['pplbdat']      = fromSqlDatum($zeile->pplbdat);
				$data['pplcdat']      = fromSqlDatum($zeile->pplcdat);
				$data['uldat']        = fromSqlDatum($zeile->uldat);
				$data['medical']      = fromSqlDatum($zeile->medical);
				$data['jar_tmg']      = utf8_encode($zeile->jar_tmg);
				$data['jar_sep']      = utf8_encode($zeile->jar_sep);
				$data['hlbnr']        = utf8_encode($zeile->hlbnr);
				$data['taetigkeiten'] = utf8_encode($zeile->taetigkeiten);
				$data['fachausweise'] = utf8_encode($zeile->fachausweise);
				
				// Gültigkeiten der Lizenzen prüfen
				$data['ppladat'] = ($data['ppladat'] == '31.12.9999') ? 'unbefristed' : $data['ppladat'];
				$data['pplbdat'] = ($data['pplbdat'] == '31.12.9999') ? 'unbefristed' : $data['pplbdat'];
				$data['pplcdat'] = ($data['pplcdat'] == '31.12.9999') ? 'unbefristed' : $data['pplcdat'];

				// Bild (Avatar) holen
				// das Bild (Avatar) muss existent sein
				if (file_exists(sprintf('./userpics/%s.jpg', md5($data['acb_nr'])))) {
					// Bild laden
					$data['avatar_img'] = sprintf('%s.jpg', md5($data['acb_nr']));
				} else {
					// Bild wieder auf das Dummy-Pic (anhand der Anrede) zurücksetzen
					if ($data['anrede'] == 'H') {
						$data['avatar_img'] = '_dummy_pic_male.jpg';
					} else {
						$data['avatar_img'] = '_dummy_pic_female.jpg';
					}
				}
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Mitgliedsdaten
			return $data;
		}
	}
	
	/*
	 * setTaetigkeitFachausweis()
	 *
	 * setzt die Tätigkeiten/Fachausweise entsprechend der übergebenen Parameter und
	 * schreibt diese anschließend in das als Referenz übergebene PDF-Dokument
	 *
	 * @params class  $ref_pdf
	 * @params string $taetigkeiten
	 * @params string $id
	 * @params string $value
	 */
	if (!function_exists('setTaetigkeitFachausweis')) {
		function setTaetigkeitFachausweis(& $ref_pdf, $taetigkeiten, $id, $value)
		{
			// neue Tätigkeit festlegen
			// Schriftart festlegen
			$ref_pdf->SetFont('Courier', '' , 14);
			if (strpos($taetigkeiten, $id) !== false) {
				$ref_pdf->Cell(5,  5, 'X', 1, 0, 'C');
			} else {
				$ref_pdf->Cell(5,  5, '', 1, 0, 'C');
			}

			// Schriftart festlegen
			$ref_pdf->SetFont('Arial', '' , 10);
			$ref_pdf->Cell(5,  5, utf8_decode($id), 1, 0, 'C');
			$ref_pdf->Cell(45, 5, utf8_decode($value), 0, 0, 'L');
		}
	}
	
	/*
	 * setMitgliedschaft()
	 *
	 * setzt die Mitgliedschaften entsprechend der übergebenen Parameter und
	 * schreibt diese anschließend in das als Referenz übergebene PDF-Dokument
	 *
	 * @params class  $ref_pdf
	 * @params string $taetigkeiten
	 * @params string $id
	 * @params string $value
	 */
	if (!function_exists('setMitgliedschaft')) {
		function setMitgliedschaft(& $ref_pdf, $status, $id, $value)
		{
			// Schriftart festlegen
			$ref_pdf->SetFont('Courier', '' , 14);
			if ($status == $id) {
				$ref_pdf->Cell(5,  5, 'X', 1, 0, 'C');
			} else {
				$ref_pdf->Cell(5,  5, '', 1, 0, 'C');
			}

			// Schriftart festlegen
			$ref_pdf->SetFont('Arial', '' , 10);
			$ref_pdf->Cell(5,  5, utf8_decode($id), 1, 0, 'C');
			$ref_pdf->Cell(45, 5, utf8_decode($value), 0, 0, 'L');
		}
	}
	
	/*
	 * setLizenz()
	 *
	 * setzt die Lizenzen entsprechend der übergebenen Parameter und
	 * schreibt diese anschließend in das als Referenz übergebene PDF-Dokument
	 *
	 * @params class  $ref_pdf
	 * @params string $status
	 * @params string $id
	 * @params string $value
	 */
	if (!function_exists('setLizenz')) {
		function setLizenz(& $ref_pdf, $status, $id, $value)
		{
			// Schriftart festlegen
			$ref_pdf->SetFont('Courier', '' , 14);
			if ($status == $id) {
				$ref_pdf->Cell(5,  5, 'X', 1, 0, 'C');
			} else {
				$ref_pdf->Cell(5,  5, '', 1, 0, 'C');
			}

			// Schriftart festlegen
			$ref_pdf->SetFont('Arial', '' , 10);
			$ref_pdf->Cell(5,  5, utf8_decode($id), 1, 0, 'C');
			$ref_pdf->Cell(45, 5, utf8_decode($value), 0, 0, 'L');
		}
	}
		
		

	// es wird zunächst geprüft, ob ein Mitglied ausgewählt wurde
	if (isset($_GET['acb_nr'])) {
		// Mitgliedsdaten laden
		$data = getMitglied($_GET['acb_nr']);
	
		// PDF-Bibliothek einbinden
		require_once('./pdf/fpdf.php');

		// neues PDF-Dokument erzeugen
		$pdf = new FPDF('P', 'mm', 'A4');
		
		// Seitenabstand definieren
		$pdf->SetMargins(25, 15, 15);
		
		// Automatischen Seitenumbruch deaktivieren
		$pdf->SetAutoPageBreak(false);

		// ******************************************************* SEITE 1 ******************************************************* //
		// erste Seite hinzufügen
		$pdf->AddPage();
		
		// Logo einfügen
		$pdf->Image('./img/acb_logo_neublau_klein.jpg', 165, 10, 30);

		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 16);
		// Schriftfarbe festlegen
		$pdf->SetTextColor(5, 3, 252);
		$pdf->Text(30, 15, utf8_decode('A E R O - C L U B   B U T Z B A C H   E. V.'));
		
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 9);
		$pdf->Text(40, 20, utf8_decode('Ringstraße 4  -  35647 Waldsolms  -  Telefon 06085-987330'));
		$pdf->Text(55, 24, utf8_decode('eMail: vorstand@aero-club-butzbach.de'));
		
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'BU' , 14);
		// Schriftfarbe festlegen
		$pdf->SetTextColor(0, 0, 0);
		$pdf->Text(20, 38, utf8_decode('Personen-Datenblatt'));

		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(20, 46.5, '01');
		
		// neue XY-Position bestimmen
		$pdf->SetXY(30, 43);
		// neuer Zugang
		$pdf->Cell(5, 5, '', 1, 0, 'C');
		$pdf->Cell(5, 5, utf8_decode('Z'), 1, 0, 'C');
		$pdf->Cell(20, 5, utf8_decode('ugang'), 0, 0, 'L');
		$pdf->Cell(80, 5, utf8_decode('(Alles ausfüllen, soweit zutreffend)'), 0, 0, 'L');
		
		// Zeilenumbruch und neue X-Position bestimmen
		$pdf->Ln();
		$pdf->SetX(30);
		// Änderung
		$pdf->Cell(5, 5, '', 1, 0, 'C');
		$pdf->Cell(5, 5, utf8_decode('Ä'), 1, 0, 'C');
		$pdf->Cell(20, 5, utf8_decode('nderung'), 0, 0, 'L');
		$pdf->Cell(80, 5, utf8_decode('(Ziff. 1 - 5a und alle Änderungen ausfüllen)'), 0, 0, 'L');
		
		// Zeilenumbruch und neue X-Position bestimmen
		$pdf->Ln();
		$pdf->SetX(30);
		// Löschung
		$pdf->Cell(5, 5, '', 1, 0, 'C');
		$pdf->Cell(5, 5, utf8_decode('L'), 1, 0, 'C');
		$pdf->Cell(20, 5, utf8_decode('öschung'), 0, 0, 'L');
		$pdf->Cell(80, 5, utf8_decode('(Ziff. 1 - 5a ausfüllen)'), 0, 0, 'L');
		
		// Linienbreite einstellen, 0.1 mm
		$pdf->SetLineWidth(0.1);
		
		// ---------- MITGLIEDSCHAFT --------- //
		$pdf->Text(20, 67, '03');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 67, utf8_decode('Art der Mitgliedschaft'));

		// neue XY-Position bestimmen
		$pdf->SetXY(30, 70);
		
		setMitgliedschaft($pdf, $data['status'], 'N', 'Aktives Mitglied');      // Aktives Mitglied
		setMitgliedschaft($pdf, $data['status'], 'J', 'Jugendliches Mitglied'); // Jugendliches Mitglied
		setMitgliedschaft($pdf, $data['status'], 'P', 'Förderndes Mitglied');   // Förderndes Mitglied
		
		// Zeilenumbruch und neue X-Position bestimmen
		$pdf->Ln();
		$pdf->SetX(30);
		
		setMitgliedschaft($pdf, $data['status'], 'R', 'Ehrenmitglied');   // Ehrenmitglied
		setMitgliedschaft($pdf, $data['status'], 'S', 'Verbandmitglied'); // Verbandmitglied
		// ---------- MITGLIEDSCHAFT --------- //
		
		// --------- MITGLIEDSNUMMER --------- //
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(20, 89, '04');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 89, utf8_decode('Mitgliedsnummer'));
		// Linie(n) zeichnen
		$pdf->Line(70, 90, 90, 90);
		$pdf->Line(70, 89, 70, 90);
		$pdf->Line(90, 89, 90, 90);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 89, utf8_decode($_GET['acb_nr']));

		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(109, 89, utf8_decode('HLB-Nummer'));
		// Linie(n) zeichnen
		$pdf->Line(140, 90, 185, 90);
		$pdf->Line(140, 89, 140, 90);
		$pdf->Line(185, 89, 185, 90);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(143, 89, utf8_decode($data['hlbnr']));
		
		// --------- MITGLIEDSNUMMER --------- //
		
		// ---------- NAME, VORNAME ---------- //
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(20, 99, '05');
		$pdf->Text(26, 99, 'a');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 99, utf8_decode('Name, Vorname'));
		// Linie(n) zeichnen
		$pdf->Line(70,  100, 185, 100);
		$pdf->Line(70,  99,  70, 100);
		$pdf->Line(185, 99, 185, 100);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 99, sprintf('%s, %s', utf8_decode($data['nachname']), utf8_decode($data['vorname'])));
		// ---------- NAME, VORNAME ---------- //
		
		// ----------- STRASSE, NR ----------- //
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(26, 106, 'b');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 106, utf8_decode('Straße, Nr.'));
		// Linie(n) zeichnen
		$pdf->Line(70,  107, 185, 107);
		$pdf->Line(70,  106,  70, 107);
		$pdf->Line(185, 106, 185, 107);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 106, utf8_decode($data['strasse']));
		// ----------- STRASSE, NR ----------- //
		
		// ----------- PLZ, WOHNORT ---------- //
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(26, 113, 'c');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 113, utf8_decode('PLZ, Wohnort'));
		// Linie(n) zeichnen
		$pdf->Line(70,  114, 185, 114);
		$pdf->Line(70,  113,  70, 114);
		$pdf->Line(185, 113, 185, 114);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 113, sprintf('%s -  %s %s', utf8_decode($data['land']), utf8_decode($data['plz']), utf8_decode($data['ort'])));
		// ----------- PLZ, WOHNORT ---------- //
		
		// ------------- TELEFON 1 ----------- //
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(26, 120, 'd');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 120, utf8_decode('Telefon (priv.)'));
		// Linie(n) zeichnen
		$pdf->Line(70,  121, 185, 121);
		$pdf->Line(70,  120,  70, 121);
		$pdf->Line(185, 120, 185, 121);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 120, utf8_decode($data['telefon1']));
		// ------------- TELEFON 1 ----------- //

		// ------------- TELEFON 2 ----------- //
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(26, 127, 'e');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 127, utf8_decode('Telefon (dienstl.)'));
		// Linie(n) zeichnen
		$pdf->Line(70,  128, 185, 128);
		$pdf->Line(70,  127,  70, 128);
		$pdf->Line(185, 127, 185, 128);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 127, utf8_decode($data['telefon2']));
		// ------------- TELEFON 2 ----------- //
		
		// ------------- MOBIL 1 ------------- //
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(26, 134, 'f');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 134, utf8_decode('Mobil (priv.)'));
		// Linie(n) zeichnen
		$pdf->Line(70,  135, 185, 135);
		$pdf->Line(70,  134,  70, 135);
		$pdf->Line(185, 134, 185, 135);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 134, utf8_decode($data['mobil1']));
		// ------------- MOBIL 1 ------------- //
		
		// ------------- MOBIL 2 ------------- //
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(26, 141, 'g');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 141, utf8_decode('Mobil (dienstl.)'));
		// Linie(n) zeichnen
		$pdf->Line(70,  142, 185, 142);
		$pdf->Line(70,  141,  70, 142);
		$pdf->Line(185, 141, 185, 142);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 141, utf8_decode($data['mobil2']));
		// ------------- MOBIL 2 ------------- //
		
		// ---------- GEBURTSDATUM ----------- //
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(20, 151, '06');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 151, utf8_decode('Geburtsdatum'));
		// Linie(n) zeichnen
		$pdf->Line(70,  152, 110, 152);
		$pdf->Line(70,  151,  70, 152);
		$pdf->Line(110, 151, 110, 152);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 151, utf8_decode($data['geburtsdatum']));
		// ---------- GEBURTSDATUM ----------- //
		
		// --------- EINTRITTSDATUM ---------- //
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(20, 160, '07');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 160, utf8_decode('Eintritt in Verein'));
		// Linie(n) zeichnen
		$pdf->Line(70,  161, 110, 161);
		$pdf->Line(70,  160,  70, 161);
		$pdf->Line(110, 160, 110, 161);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 160, utf8_decode($data['eintritt']));
		// --------- EINTRITTSDATUM ---------- //
		
		// --------- EMAIL/HOMEPAGE ---------- //
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(20, 170, '08');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 170, utf8_decode('eMail'));
		// Linie(n) zeichnen
		$pdf->Line(70,  171, 185, 171);
		$pdf->Line(70,  170,  70, 171);
		$pdf->Line(185, 170, 185, 171);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 170, utf8_decode($data['email']));
		
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 177, utf8_decode('Homepage'));
		// Linie(n) zeichnen
		$pdf->Line(70,  178, 185, 178);
		$pdf->Line(70,  177,  70, 178);
		$pdf->Line(185, 177, 185, 178);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 177, utf8_decode($data['www']));
		// --------- EMAIL/HOMEPAGE ---------- //
		
		// ----- BETÄTIGUNGEN IM VEREIN ------ //
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(20, 186, '09');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 186, utf8_decode('Betätigung(en) im Verein'));

		// neue XY-Position bestimmen
		$pdf->SetXY(30, 189);
		
		setTaetigkeitFachausweis($pdf, $data['taetigkeiten'], 'A', 'Motorflug');   // Motorflug
		setTaetigkeitFachausweis($pdf, $data['taetigkeiten'], 'B', 'Motorsegler'); // Motorsegler
		setTaetigkeitFachausweis($pdf, $data['taetigkeiten'], 'C', 'Segelflug');   // Segelflug
		
		// Zeilenumbruch und neue X-Position bestimmen
		$pdf->Ln();
		$pdf->SetX(30);
		
		setTaetigkeitFachausweis($pdf, $data['taetigkeiten'], 'D', 'Modellflug');         // Modellflug
		setTaetigkeitFachausweis($pdf, $data['taetigkeiten'], 'E', 'Fallschirmspringen'); // Fallschirmspringen
		setTaetigkeitFachausweis($pdf, $data['taetigkeiten'], 'F', 'Ballonfahren');       // Ballonfahren		
		
		// Zeilenumbruch und neue X-Position bestimmen
		$pdf->Ln();
		$pdf->SetX(30);
		
		setTaetigkeitFachausweis($pdf, $data['taetigkeiten'], 'G', 'Drachenfliegen');     // Drachenfliegen
		setTaetigkeitFachausweis($pdf, $data['taetigkeiten'], 'H', 'Ultraleichtfliegen'); // Ultraleichtfliegen
		setTaetigkeitFachausweis($pdf, $data['taetigkeiten'], 'I', 'Jugendleiter');       // Jugendleiter
		
		// Zeilenumbruch und neue X-Position bestimmen
		$pdf->Ln();
		$pdf->SetX(30);
		
		setTaetigkeitFachausweis($pdf, $data['taetigkeiten'], 'J', 'Übungsleiter');         // Übungsleiter
		setTaetigkeitFachausweis($pdf, $data['taetigkeiten'], 'K', 'Trainer');              // Trainer
		setTaetigkeitFachausweis($pdf, $data['taetigkeiten'], 'L', 'Sonstige Tätigkeiten'); // Sonstige Tätigkeiten
		
		// Zeilenumbruch und neue X-Position bestimmen
		$pdf->Ln();
		$pdf->SetX(30);
		
		setTaetigkeitFachausweis($pdf, $data['taetigkeiten'], 'M', 'Gleitschirmfliegen'); // Gleitschirmfliegen
		// ----- BETÄTIGUNGEN IM VEREIN ------ //
		
		// ---------- FACHAUSWEISE ----------- //
		$pdf->Text(20, 220, '10');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 220, utf8_decode('Fachausweis(e)'));

		// neue XY-Position bestimmen
		$pdf->SetXY(30, 222);
		
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'A', 'Motorfluglehrer');   // Motorfluglehrer
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'B', 'Motorseglerlehrer'); // Motorseglerlehrer
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'C', 'Segelfluglehrer');   // Segelfluglehrer
		
		// Zeilenumbruch und neue X-Position bestimmen
		$pdf->Ln();
		$pdf->SetX(30);
		
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'D', 'Modellfluglehrer'); // Modellfluglehrer
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'E', 'Sprunglehrer');     // Sprunglehrer
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'F', 'Ballonausbilder');  // Ballonausbilder
		
		// Zeilenumbruch und neue X-Position bestimmen
		$pdf->Ln();
		$pdf->SetX(30);
		
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'G', 'Drachenfluglehrer'); // Drachenfluglehrer
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'H', 'UL-Fluglehrer');     // UL-Fluglehrer
		
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 242, utf8_decode('Technisches Personal'));

		// neue XY-Position bestimmen
		$pdf->SetXY(30, 244);
		
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'I', 'Werkstattleiter');       // Werkstattleiter
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'J', 'Flugzeugwart');          // Flugzeugwart
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'K', 'Motorsegler- /UL-Wart'); // Motorsegler- /UL-Wart
		
		// Zeilenumbruch und neue X-Position bestimmen
		$pdf->Ln();
		$pdf->SetX(30);
		
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'L', 'Segelflugzeugwart'); // Segelflugzeugwart
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'M', 'Fallschirmpacker');  // Fallschirmpacker
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'N', 'Ballonwart');        // Ballonwart

		// Zeilenumbruch und neue X-Position bestimmen
		$pdf->Ln();
		$pdf->SetX(30);
		
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'T', 'Prüfer'); // Prüfer
		
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 264, utf8_decode('Sportzeuge'));
		
		// neue XY-Position bestimmen
		$pdf->SetXY(30, 266);
		
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'O', 'Zeuge Motorflug');  // Zeuge Motorflug
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'P', 'Zeuge Segelflug');  // Zeuge Segelflug
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'R', 'Zeuge Modellflug'); // Zeuge Modellflug
		
		// Zeilenumbruch und neue X-Position bestimmen
		$pdf->Ln();
		$pdf->SetX(30);
		
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'S', 'Zeuge Fallschirm'); // Zeuge Fallschirm
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'X', 'Sonstiges');        // Sonstiges
		setTaetigkeitFachausweis($pdf, $data['fachausweise'], 'U', 'Flugleiter');       // Flugleiter
		// ---------- FACHAUSWEISE ----------- //
		
		// über dem unteren Seitenrand positionieren 
		$pdf->SetY(-10); 
		// Schriftart festlegen
		$pdf->SetFont('Arial', '', 9); 
		// Zentrierte Ausgabe der Seitenzahl
		$pdf->Cell(0, 3, sprintf('- %s -', $pdf->PageNo()) , 0, 0, 'C');
		// ******************************************************* SEITE 1 ******************************************************* //
		
		// ******************************************************* SEITE 2 ******************************************************* //
		// Zweite Seite hinzufügen
		$pdf->AddPage();
		
		
		// Linienbreite einstellen, 0.1 mm
		$pdf->SetLineWidth(0.1);
		
		// ---------- BANKVERBINDUNG --------- //
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(20, 22, '11');
		$pdf->Text(30, 22, utf8_decode('Der Mitgliedsbeitrag ist vierteljahresweise abzubuchen von'));
		
		// --------------- BANK -------------- //
		$pdf->Text(26, 29, 'a');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 29, utf8_decode('Kreditinstitut'));
		// Linie(n) zeichnen
		$pdf->Line(70,  30, 185, 30);
		$pdf->Line(70,  29,  70, 30);
		$pdf->Line(185, 29, 185, 30);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 29, utf8_decode($data['bank']));
		// --------------- BANK -------------- //
		
		// ----------- KONTO-NUMMER ---------- //
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(26, 36, 'b');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 36, utf8_decode('Konto-Nummer'));
		// Linie(n) zeichnen
		$pdf->Line(70,  37, 120, 37);
		$pdf->Line(70,  36,  70, 37);
		$pdf->Line(120, 36, 120, 37);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 36, utf8_decode($data['kto']));
		// ----------- KONTO-NUMMER ---------- //
		
		// ----------- BANKLEITZAHL ---------- //
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(26, 43, 'c');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 43, utf8_decode('Bankleitzahl'));
		// Linie(n) zeichnen
		$pdf->Line(70,  44, 120, 44);
		$pdf->Line(70,  43,  70, 44);
		$pdf->Line(120, 43, 120, 44);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 43, utf8_decode($data['blz']));
		// ----------- BANKLEITZAHL ---------- //
		
		// ---------- KONTO-INHABER ---------- //
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(26, 50, 'd');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 50, utf8_decode('Kontoinhaber'));
		// Linie(n) zeichnen
		$pdf->Line(70,  51, 185, 51);
		$pdf->Line(70,  50,  70, 51);
		$pdf->Line(185, 50, 185, 51);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 50, utf8_decode($data['ktoinhaber']));
		// ---------- KONTO-INHABER ---------- //
		// ---------- BANKVERBINDUNG --------- //
		
		// ------- FLIEGERISCHER STAND ------- //
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(20, 59, '12');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 59, utf8_decode('Stand der fliegerischen Ausbildung und Fähigkeiten'));

		// neue XY-Position bestimmen
		$pdf->SetXY(30, 62);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->MultiCell(155, 5, utf8_decode($data['funktion']), 0, 'L');

		// Linie(n) zeichnen
		$pdf->Line(30,  61, 185, 61);
		$pdf->Line(30,  61,  30, 63);
		$pdf->Line(185, 61, 185, 63);
		$pdf->Line(30,  86, 185, 86);
		$pdf->Line(30,  84,  30, 86);
		$pdf->Line(185, 84, 185, 86);
		// ------- FLIEGERISCHER STAND ------- //
		
		// ----- LIZENZEN/BERECHTIGUNGEN ----- //
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(20, 94, '13');
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(30, 94, utf8_decode('Gültigkeit von Lizenz(en) und Berchtigung(en)'));

		// -------- PPL(A) GÜLTIG BIS -------- //
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(26, 101, 'a');
		$pdf->Text(30, 101, utf8_decode('PPL(A) gültig bis'));
		// Linie(n) zeichnen
		$pdf->Line(70,  102, 110, 102);
		$pdf->Line(70,  101,  70, 102);
		$pdf->Line(110, 101, 110, 102);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 101, utf8_decode($data['ppladat']));
		// -------- PPL(A) GÜLTIG BIS -------- //
		
		// -------- PPL(B) GÜLTIG BIS -------- //
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(26, 108, 'b');
		$pdf->Text(30, 108, utf8_decode('PPL(B) gültig bis'));
		// Linie(n) zeichnen
		$pdf->Line(70,  109, 110, 109);
		$pdf->Line(70,  108,  70, 109);
		$pdf->Line(110, 108, 110, 109);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 108, utf8_decode($data['pplbdat']));
		// -------- PPL(B) GÜLTIG BIS -------- //
		
		// -------- PPL(C) GÜLTIG BIS -------- //
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(26, 115, 'c');
		$pdf->Text(30, 115, utf8_decode('PPL(C) gültig bis'));
		// Linie(n) zeichnen
		$pdf->Line(70,  116, 110, 116);
		$pdf->Line(70,  115,  70, 116);
		$pdf->Line(110, 115, 110, 116);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 115, utf8_decode($data['pplcdat']));
		// -------- PPL(C) GÜLTIG BIS -------- //
		
		// ------- UL-SCHEIN GÜLTIG BIS ------ //
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(26, 122, 'd');
		$pdf->Text(30, 122, utf8_decode('UL-Schein gültig bis'));
		// Linie(n) zeichnen
		$pdf->Line(70,  123, 110, 123);
		$pdf->Line(70,  122,  70, 123);
		$pdf->Line(110, 122, 110, 123);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 122, utf8_decode($data['uldat']));
		// ------- UL-SCHEIN GÜLTIG BIS ------ //
		
		// -------- MEDICAL GÜLTIG BIS ------- //
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(26, 129, 'e');
		$pdf->Text(30, 129, utf8_decode('Medical gültig bis'));
		// Linie(n) zeichnen
		$pdf->Line(70,  130, 110, 130);
		$pdf->Line(70,  129,  70, 130);
		$pdf->Line(110, 129, 110, 130);
		
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 13);
		$pdf->Text(73, 129, utf8_decode($data['medical']));
		// -------- MEDICAL GÜLTIG BIS ------- //
		
		// neue XY-Position bestimmen
		$pdf->SetXY(120, 97);

		// Lizenz JAR-FCL SEP und/oder JAR-FCL TMG
		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 14);
		if ($data['jar_tmg'] == 'J') {
			$pdf->Cell(5,  5, 'X', 1, 0, 'C');
		} else {
			$pdf->Cell(5,  5, '', 1, 0, 'C');
		}

		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Cell(30, 5, utf8_decode('JAR-FCL TMG'), 0, 0, 'L');

		// Schriftart festlegen
		$pdf->SetFont('Courier', '' , 14);
		if ($data['jar_sep'] == 'J') {
			$pdf->Cell(5,  5, 'X', 1, 0, 'C');
		} else {
			$pdf->Cell(5,  5, '', 1, 0, 'C');
		}

		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Cell(30, 5, utf8_decode('JAR-FCL SEP'), 0, 0, 'L');
		// Lizenz JAR-FCL SEP und/oder JAR-FCL TMG
		// ----- LIZENZEN/BERECHTIGUNGEN ----- //
		
		// ------------ SONSTIGES ------------ //
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(20, 144, '14');
		
		// neue XY-Position bestimmen
		$pdf->SetXY(29, 140.5);
		
		// Weitergabe der Daten an den HLB
		$pdf-> MultiCell(98, 5, utf8_decode(
			'Ich bin damit einverstanden, dass die Daten zu Ziff. 1 - 10 an den Hessischen Luftsportbund im DAeC weitergegeben werden.'
		), 0, 'J');
		
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(20, 162, '15');
		
		// neue XY-Position bestimmen
		$pdf->SetXY(29, 158.5);
		
		// Datenschutz im Aero-Club Butzbach
		$pdf-> MultiCell(98, 5, utf8_decode(
			'Ich bin mit der Veröffentlichung meiner personenbezogenen Daten im Rahmen meiner Mitgliedschaft im Aero-Club Butzbach e.V. einverstanden.'
		), 0, 'J');
		
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(20, 183, '16');
		
		// neue XY-Position bestimmen
		$pdf->SetXY(29, 179.5);
		
		// Datum und Unterschrift
		$pdf-> MultiCell(98, 5, utf8_decode('Datum und Unterschrift'), 0, 'L');
		// Linie(n) zeichnen
		$pdf->Line(30,  194, 126, 194);
		$pdf->Line(30,  193,  30, 194);
		$pdf->Line(126, 193, 126, 194);
		
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(20, 200, '17');
		
		// neue XY-Position bestimmen
		$pdf->SetXY(29, 196.5);
		
		// Datum und Unterschrift
		$pdf-> MultiCell(98, 5, utf8_decode('Unterschrift des Vertretungsberechtigten (bei Minderjährigen)'), 0, 'L');
		// Linie(n) zeichnen
		$pdf->Line(30,  211, 126, 211);
		$pdf->Line(30,  210,  30, 211);
		$pdf->Line(126, 210, 126, 211);
		
		// Linie(n) zeichnen
		$pdf->Line(20,   216, 195,   216);
		$pdf->Line(20, 216.5, 195, 216.5);
		
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(21, 222, utf8_decode('Beschluss des Vorstands'));
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(66, 222, utf8_decode('vom'));
		// Linie(n) zeichnen
		$pdf->Line(76,  223, 105, 223);
		$pdf->Line(76,  222,  76, 223);
		$pdf->Line(105, 222, 105, 223);
		$pdf->Text(108, 222, utf8_decode('über den vorläufigen Aufnahmeantrag'));

		$pdf->Text(21,  230, utf8_decode('Aufnahme in Verein zum (Datum)'));
		// Linie(n) zeichnen
		$pdf->Line(76,  231, 105, 231);
		$pdf->Line(76,  230,  76, 231);
		$pdf->Line(105, 230, 105, 231);
		
		$pdf->Text(21,  238, utf8_decode('Der Beschluss wurde dem Antragsteller mitgeteilt am'));
		// Linie(n) zeichnen
		$pdf->Line(106, 239, 134, 239);
		$pdf->Line(106, 238, 106, 239);
		$pdf->Line(134, 238, 134, 239);
		$pdf->Text(137, 238, utf8_decode('von'));
		// Linie(n) zeichnen
		$pdf->Line(146, 239, 185, 239);
		$pdf->Line(146, 238, 146, 239);
		$pdf->Line(185, 238, 185, 239);
		
		// Linienbreite einstellen, 0.4 mm
		$pdf->SetLineWidth(0.4);
		
		// Linie(n) zeichnen
		$pdf->Line(20, 243, 195, 243);
		
		// Linienbreite einstellen, 0.1 mm
		$pdf->SetLineWidth(0.1);
		
		// Schriftart festlegen
		$pdf->SetFont('Arial', 'B' , 10);
		$pdf->Text(21, 248, utf8_decode('Daten übernommen in'));
		
		// Schriftart festlegen
		$pdf->SetFont('Arial', '' , 10);
		$pdf->Text(21, 255, utf8_decode('Stammdatei am'));
		// Linie(n) zeichnen
		$pdf->Line(50, 256, 79, 256);
		$pdf->Line(50, 255, 50, 256);
		$pdf->Line(79, 255, 79, 256);
		
		$pdf->Text(83, 255, utf8_decode('VERA am'));
		// Linie(n) zeichnen
		$pdf->Line(103, 256, 134, 256);
		$pdf->Line(103, 255, 103, 256);
		$pdf->Line(134, 255, 134, 256);
		
		$pdf->Text(137, 255, utf8_decode('HLB am'));
		// Linie(n) zeichnen
		$pdf->Line(154, 256, 185, 256);
		$pdf->Line(154, 255, 154, 256);
		$pdf->Line(185, 255, 185, 256);
		
		// Linienbreite einstellen, 0.4 mm
		$pdf->SetLineWidth(0.4);
		
		// Linie(n) zeichnen
		$pdf->Line(20, 260, 195, 260);
		
		// Linienbreite einstellen, 0.1 mm
		$pdf->SetLineWidth(0.1);
		
		// Schriftart festlegen
		$pdf->Text(21,  267, utf8_decode('Aufnahmegebühr I'));
		$pdf->Text(90,  267, utf8_decode('Euro'));
		$pdf->Text(120, 267, utf8_decode('eingezahlt am'));

		// Linie(n) zeichnen
		$pdf->Line(55, 268, 87, 268);
		$pdf->Line(55, 268, 55, 267);
		$pdf->Line(87, 268, 87, 267);
		
		// Linie(n) zeichnen
		$pdf->Line(145, 268, 185, 268);
		$pdf->Line(145, 268, 145, 267);
		$pdf->Line(185, 268, 185, 267);

		// Schriftart festlegen
		$pdf->Text(21,  274, utf8_decode('Aufnahmegebühr II'));
		$pdf->Text(90,  274, utf8_decode('Euro'));
		$pdf->Text(120, 274, utf8_decode('eingezahlt am'));
		
		// Linie(n) zeichnen
		$pdf->Line(55, 276, 87, 276);
		$pdf->Line(55, 276, 55, 275);
		$pdf->Line(87, 276, 87, 275);
		
		// Linie(n) zeichnen
		$pdf->Line(145, 276, 185, 276);
		$pdf->Line(145, 276, 145, 275);
		$pdf->Line(185, 276, 185, 275);
		
		// Linienbreite einstellen, 0.4 mm
		$pdf->SetLineWidth(0.4);
		
		// Linie(n) zeichnen
		$pdf->Line(20, 280, 195, 280);
		// ------------ SONSTIGES ------------ //
		
		// ------------ PROFILBILD ----------- //
		// Linienbreite einstellen, 0.1 mm
		$pdf->SetLineWidth(0.1);
		
		// Linie(n) zeichnen
		$pdf->Line(133, 211, 140, 211);
		$pdf->Line(133, 211, 133, 204);
		$pdf->Line(185, 211, 178, 211);
		$pdf->Line(185, 211, 185, 204);
		$pdf->Line(133, 141, 140, 141);
		$pdf->Line(133, 141, 133, 148);
		$pdf->Line(178, 141, 185, 141);
		$pdf->Line(185, 141, 185, 148);
		
		// Bilddatei wählen
		$avatar = sprintf('./userpics/%s.jpg', md5($_GET['acb_nr']));
		
		// prüfen ob die Bilddatei existiert
		if (!file_exists($avatar)) {
			// Anrede prüfen und entsprechend dessen Dummybild laden
			if ($data['anrede'] == 'H') {
				$avatar = './userpics/_dummy_pic_male.jpg';
			} else {
				$avatar = './userpics/_dummy_pic_female.jpg';
			}
		}
		
		// Bild einfügen (Position x = 135 / y = 143)
		$pdf->Image($avatar, 135, 143, 48, 66);		
		// ------------ PROFILBILD ----------- //
		
		// über dem unteren Seitenrand positionieren 
		$pdf->SetY(-10); 
		// Schriftart festlegen
		$pdf->SetFont('Arial', '', 9); 
		// Zentrierte Ausgabe der Seitenzahl
		$pdf->Cell(0, 3, sprintf('- %s -', $pdf->PageNo()) , 0, 0, 'C');
		// ******************************************************* SEITE 2 ******************************************************* //

		// PDF-Dokument ausgeben
		$pdf->Output(sprintf('%s.pdf', md5($_GET['acb_nr'])), 'I');
	}

?>