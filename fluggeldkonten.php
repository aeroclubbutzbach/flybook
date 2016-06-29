<!-- BEGINN: SKRIPT -->
<?php

	/*
	 * getAnfangsbuchstaben()
	 *
	 * es wird eine Liste mit den Anfangsbuchstaben der Nachname der
	 * Mitglieder zurückgegeben und auf diese wird ein entsprechender
	 * Verweis gesetzt, damit der Anwender schnell zu den einzelnen
	 * Mitgliedern "springen" kann ohne sich vorher durchscrollen zu müssen
	 *
	 * @return string $html
	 */
	if (!function_exists('getAnfangsbuchstaben')) {
		function getAnfangsbuchstaben()
		{
			// Rückgabe-Variable definieren
			$html = '';
			
			// Array mit dem Alphabet anlegen
			$alphabet = array(
				'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
				'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
			);
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurecht fuddeln,
			// Liste der Anfangsbuchstaben der Mitglieder ermitteln
			$sql = sprintf('
				SELECT DISTINCT
					UPPER(
						SUBSTRING(
							`mitglieder`.`nachname`, 1, 1
						)
					) AS `buchstabe`
				FROM
					`mitglieder`
				INNER JOIN
					`fluggeldkonto` ON `mitglieder`.`id` = `fluggeldkonto`.`acb_nr`
				ORDER BY
					`buchstabe` ASC 
			');
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// ein Array für die vorhandenen Buchstaben anlegen
			$vorhanden = array();
			
			while ($zeile = mysql_fetch_object($db_erg)) {
				foreach ($alphabet as $buchstabe) {
					// prüfen ob der Buchstabe vorhanden ist
					if ($buchstabe == $zeile->buchstabe) {
						// Buchstabe vorhanden
						$vorhanden[] = $zeile->buchstabe;
						// Schleife hier abbrechen
						break;
					}
				}
			}
			
			// folgende Buchstaben sind nicht vorhanden
			$nichtVorhanden = array_diff($alphabet, $vorhanden);
			
			// Array initialisieren, welches später die Verweise sortiert enthält
			$t = array();
			
			// die Links/Verweise für die vorhandenen Buchstaben werden gesetzt
			foreach ($vorhanden as $buchstabe) {
				$t[] = sprintf('%s<a href="#%s" class="anfangsBuchstaben">%s</a> - ', $buchstabe, $buchstabe, $buchstabe);
			}
			
			// für die nicht vorhandenen Buchstaben werden keine Verweise/
			// Links gesetzt, sie werden aber dennoch angezeigt
			foreach ($nichtVorhanden as $buchstabe) {
				$t[] = sprintf('%s%s - ', $buchstabe, $buchstabe);
			}
			
			// Array entsprechend sortieren damit dieganze
			// Sache wieder alphabetisch angeordnet aussieht
			sort($t);
			
			foreach ($t as $link) {
				// Link/Verweis zum entsprechenden Buchstaben anlegen
				$html .= substr($link, 1);
			}
			
			// die letzten drei Stellen wieder abschneiden
			$html = substr($html, 0, strlen($html) - 3);
			
			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Links zurückgeben
			return $html;
		}
	}

	/*
	 * printTabelleFluggeldkonten()
	 *
	 * die Fluggeldkonten (Saldenliste) der einzelnen Mitglieder wird geladen,
	 * und anschließend als HTML-Tabelle zum Bearbeiten zurückgegeben
	 *
	 * @return string $html
	 */
	if (!function_exists('printTabelleFluggeldkonten')) {
		function printTabelleFluggeldkonten()
		{
			// Rückgabe-Variable definieren
			$html = '';
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			mysql_set_charset('utf8');

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
			// Variable zum Zwischenspeichern des Anfangsbuchstaben initialisieren
			$buchstabe = '';

			while ($zeile = mysql_fetch_object($db_erg)) {
				// Hintergrundfarbe jeder Zeile abwechseln gestalten
				$bgColor = ($i % 2) ? '#eeeeee' : '#cccccc';
				// Farbe bei negativen Salden rot, sonst schwarz
				$bgColorSaldo = ($zeile->saldo < 0) ? '#ff0000' : '#000000';
			
				// neue Zeile anlegen
				$html .= sprintf('<tr height="25" bgcolor="%s">', $bgColor);

				// prüfen ob der Anfangsbuchstabe schonmal vorkam, ansonsten
				// wird dieser Links neben der Zeile zu Beginn angezeigt
				if ($buchstabe == strtoupper(substr($zeile->nachname, 0, 1))) {
					// kein neuer Anfangsbuchstabe
					$html .= '<td bgcolor="#f7f7f7"></td>';
				} else {
					// neuen Anfangsbuchstaben setzen
					$html .= sprintf('<td class="buchstabe"><a name="%s">%s</a></td>', strtoupper(substr($zeile->nachname, 0, 1)), strtoupper(substr($zeile->nachname, 0, 1)));
				}
				
				// die weiteren Parameter Mitgliedsnummer, Nachname, Vorname und Saldo in die Zeile einfügen
				$html .= sprintf('<td align="left" style="padding-left: 5px;">%s</td>', $zeile->acb_nr);
				$html .= sprintf('<td align="left" style="padding-left: 5px;">%s</td>', $zeile->nachname);
				$html .= sprintf('<td align="left" style="padding-left: 5px;">%s</td>', $zeile->vorname);
				$html .= sprintf('<td align="right" style="color: %s; padding-right: 5px;">%s</td>', $bgColorSaldo, number_format($zeile->saldo, 2, ',', ''));
				
				// Link zum Bearbeiten des Fluggeldkontos ans Zeilenende hinzufügen
				$html .= sprintf('<td align="left" style="padding-left: 10px;"><a href="fluggeldkonto_edit.php?acb_nr=%s" class="fluggeldkonten"><img src="./img/edit_icon.gif" align="left" border="0" /> <span style="position: relative;top: 3px; left: 2px;">bearbeiten</span></td>', $zeile->acb_nr);
				$html .= '<tr />';
				
				// Anfangsbuchstabe des aktuellen Datensatzes (Mitgliedsname) speichern
				$buchstabe = substr($zeile->nachname, 0, 1);
				
				// Zähler erhöhen
				$i++;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Tabellenansicht zurückgeben
			return $html;
		}
	}

?>
<!-- ENDE: SKRIPT -->
<!-- BEGINN: AUSGABE -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

    <head>

        <title>Fluggeldkonten</title>

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="Content-Script-Type" content="text/javascript" />
        <meta http-equiv="Content-Style-Type" content="text/css" />
        <meta http-equiv="content-language" content="de" />
        <meta name="author" content="Benjamin Stopfkuchen" />
		
		<script type="text/javascript" src="./js/jquery-1.10.2.js"></script>
		<script type="text/javascript" src="./js/jquery-1.10.2.min.js"></script>

        <link rel="Stylesheet" type="text/css" href="./css/stylish.css" />
		
    </head>

	<body style="margin-top: 0px; margin-left: 0px;">
	
		<table style="border: 1px solid #000000; background-color: #f7f7f7;" width="100%" height="100%">
			<tr>
				<td valign="top" style="padding:20px;">
				
					<h2>Fluggeldkonten - <small>Aero Club Butzbach e.V. </small><strong style="margin-left: 50px; font-size: 10pt;">Stand: <?php echo date('d.m.Y') ?></strong></h2>
					
					<div class="helpline">
						Hier findest Du eine &Uuml;bersicht der Fluggeldkonten der Mitglieder.
						<br />
						Klicke auf &quot;bearbeiten&quot; des entsprechenden Datensatzes und f&uuml;ge dem Mitglied
						weitere Buchungen hinzu. Die Buchung der einzelnen Fl&uuml;ge erfolgt &uuml;ber den CSV-Import.
					</div>
					
					<br />
					
					<div class="anfangsBuchstaben"><?php echo getAnfangsbuchstaben(); ?></div>
					
					<br />
					
					<table width="92%">
						<tr>
							<td width="140"><a href="./pdf_fluggeldkonten.php" class="neuanlageMitglied" style="font-size:10pt !important;" target="_blank"><img src="./img/pdf_icon.png" border="0" align="left" width="22" height="22" style="margin-top:-4px; margin-left:15px; margin-right:7px;" /> Fluggeldkonten als PDF ausgeben</a></td>
						</tr>
					</table>
					
					<br />
					
					<table border="0" cellspacing="1" class="fluggeldkonten">
						
						<tr height="25">
							<th width="25"></th>
							<th align="left" width="100" bgcolor="#666666" style="color: #ffffff; padding-left: 5px;">Mitgl-Nr</th>
							<th align="left" width="150" bgcolor="#666666" style="color: #ffffff; padding-left: 5px;">Nachname</th>
							<th align="left" width="150" bgcolor="#666666" style="color: #ffffff; padding-left: 5px;">Vorname</th>
							<th align="left" width="70" bgcolor="#666666" style="color: #ffffff; padding-left: 5px;">Saldo</th>
							<th width="100"></th>
						</tr>
						
						<?php echo printTabelleFluggeldkonten(); ?>
					
					</table>
				</td>
			</tr>
		</table>

	</body>

</html>
<!-- ENDE: AUSGABE -->