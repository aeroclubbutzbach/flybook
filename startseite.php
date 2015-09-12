<!-- BEGINN: SKRIPT -->
<?php

	/*
	 * getGeburtstage()
	 *
	 * ermittelt die Geburtstage ausgehend vom heutigen Datum
	 * von Gestern/Heute/Morgen und Übermorgen, berechnet das
	 * Alter und gibt diese als HTML-Tabelle zurück
	 *
	 * @return string $html
	 */
	if (!function_exists('getGeburtstage')) {
		function getGeburtstage()
		{
			// Rückgabe-Variable definieren
			$html = '';
			
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
            // Geburtstage seit gestern und der nächsten 2 Tage auslesen
            $sql = sprintf('
				SELECT
					`mitglieder`.`vorname`,
					`mitglieder`.`nachname`,
					`mitglieder`.`email`,
					DATE_FORMAT(`mitglieder`.`geburtsdatum`, "%%d.%%m.%%Y") AS `geburtsdatum_heute`,
					YEAR(CURRENT_DATE()) - YEAR(`geburtsdatum`) AS `alter`,
					DATEDIFF(`Geburtsdatum` + INTERVAL(
						YEAR(CURRENT_DATE) - YEAR(`geburtsdatum`) + IF(
							DATE_FORMAT(CURRENT_DATE, "%%m%%d") > DATE_FORMAT(`geburtsdatum`, "%%m%%d") , 1, 0)
						) YEAR, CURRENT_DATE
					) AS `days_to_birthday`
				FROM
					`mitglieder`
				WHERE
					`status` != "X"
				HAVING
					`days_to_birthday` <= 2
				OR
					`days_to_birthday` >= 364
				ORDER BY
					DATE_FORMAT(`geburtsdatum`, "%%m%%d")
			',
				date('Y')
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Zählervariable initialisieren
			$i = 0;
			
			while ($zeile = mysql_fetch_object($db_erg)) {
				// Hintergrundfarbe jeder Zeile abwechseln gestalten
				$bgColor = ($i % 2) ? '#eeeeee' : '#cccccc';
				
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
				
				// neue Zeile anlegen
				$html .= sprintf('<tr height="25" bgcolor="%s">', $bgColor);
			
				// neuen Geburtstag hinzufügen
				$html .= sprintf('<td align="left" style="padding-left: 8px;">%s, <small>%s</small></td>', $zeile->nachname, $zeile->vorname);
				$html .= sprintf('<td align="center">%s</td>', $zeile->geburtsdatum_heute);
				$html .= sprintf('<td align="right">%s</td>', $zeile->alter);
				
				// prüfen ob eine eMail-Adresse vorhanden ist
				if (!empty($zeile->email)) {
					// es gibt eine eMail-Adresse
					$html .= sprintf(
						'<td align="left" style="padding-left: 10px;"><a href="mailto:%s" class="anfangsBuchstaben" /><img src="./img/email_icon.png" title="%s" border="0" align="left" style="margin-top: -2px; margin-right: 4px;" /><small>Nachricht schreiben</small></a></td>',
						$zeile->email, $zeile->email
					);
				} else {
					// es gibt keine eMail-Adresse
					$html .= '<td>&nbsp;</td>';
				}
				
				// Ende der Zeile
				$html .= '</tr>';
				
				// Zähler erhöhen
				$i++;
			}
			
			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe des anstehenden Geburtstage
			return $html;
		}
	}
	
	/*
	 * getMitgliederzahlen()
	 *
	 * ermittelt aktuellen Mitgliederzahlen geordnet nach der Art
	 * der Mitgliedschaft und gibt diese als HTML-Tabelle zurück
	 *
	 * @return string $html
	 */
	if (!function_exists('getMitgliederzahlen')) {
		function getMitgliederzahlen()
		{
			// Rückgabe-Variable definieren
			$html = '';
			
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
            // Mitgliederzahlen nach Mitgliedsstatus geordnet ermitteln
            $sql = sprintf('
				SELECT
					`mitgliedschaft`.`bezeichnung`,
					COUNT(`mitglieder`.`status`) AS `mitgliedschaft`
				FROM
					`mitglieder`
				INNER JOIN
					`mitgliedschaft` ON `mitgliedschaft`.`id` = `mitglieder`.`status`
				WHERE
					`mitglieder`.`status` != "X"
				GROUP BY
					`mitgliedschaft`.`status`
				ORDER BY
					`mitglieder`.`status` ASC
			');
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Zählervariable initialisieren
			$i = 0;
			
			while ($zeile = mysql_fetch_object($db_erg)) {
				// Hintergrundfarbe jeder Zeile abwechseln gestalten
				$bgColor = ($i % 2) ? '#eeeeee' : '#cccccc';
		
				// neue Zeile anlegen
				$html .= sprintf('<tr height="16" bgcolor="%s">', $bgColor);

				// neue Mitgliederzahl hinzufügen
				$html .= sprintf('<td align="left" width="150" style="padding-left: 8px;">%s:</td>', $zeile->bezeichnung);
				$html .= sprintf('<td align="right" width="60" style="padding-right: 10px;">%s</td>', $zeile->mitgliedschaft);
				
				// Ende der Zeile
				$html .= '</tr>';
				
				// Zähler erhöhen
				$i++;
			}
			
			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe des aktuellen Mitgliedszahlen
			return $html;
		}
	}
	
	/*
	 * getPositiveSalden()
	 *
	 * ermittelt die Summe aller positiven Salden des Fluggeldkontos
	 *
	 * @return float $return
	 */
	if (!function_exists('getPositiveSalden')) {
		function getPositiveSalden()
		{
			// Rückgabe-Variable definieren
			$return = 0.0;
			
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
            // Summe aller positiven Salden ermitteln
            $sql = sprintf('
				SELECT
					SUM(`fluggeldkonto`.`saldo`) AS `positive_salden`
				FROM
					`fluggeldkonto`
				INNER JOIN
					`mitglieder` ON `mitglieder`.`id` = `fluggeldkonto`.`acb_nr`
				WHERE
					`saldo` > 0 AND
					`in_abrechn` = "J"
			');
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			if ($zeile = mysql_fetch_object($db_erg)) {
				// die Summe der positiven Salden in die Rückgabe-Variable schreiben
				$return = $zeile->positive_salden;
			}
			
			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der positiven Salden
			return $return;
		}
	}
	
	/*
	 * getNegativeSalden()
	 *
	 * ermittelt die Summe aller negativen Salden des Fluggeldkontos
	 *
	 * @return float $return
	 */
	if (!function_exists('getNegativeSalden')) {
		function getNegativeSalden()
		{
			// Rückgabe-Variable definieren
			$return = 0.0;
			
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
            // Summe aller negativen Salden ermitteln
            $sql = sprintf('
				SELECT
					SUM(`fluggeldkonto`.`saldo`) AS `negative_salden`
				FROM
					`fluggeldkonto`
				INNER JOIN
					`mitglieder` ON `mitglieder`.`id` = `fluggeldkonto`.`acb_nr`
				WHERE
					`saldo` < 0 AND
					`in_abrechn` = "J"
			');
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			if ($zeile = mysql_fetch_object($db_erg)) {
				// die Summe der negativen Salden in die Rückgabe-Variable schreiben
				$return = $zeile->negative_salden;
			}
			
			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der negativen Salden
			return $return;
		}
	}
	
	/*
	 * getFlugstunden()
	 *
	 * ermittelt die Flugstunden und die Flugbewegungen für den
	 * per Parameter übergebenen Zeitraum und dem entsprechenden Flugzeug
	 *
	 * @params string  $kennzeichen
	 * @params integer $vonJahr
	 * @params integer $bisJahr
	 *
	 * @return string $html
	 */
	if (!function_exists('getFlugstunden')) {
		function getFlugstunden($kennzeichen, $vonJahr, $bisJahr)
		{
			// Rückgabe-Variable definieren
			$html = '';
			
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
            // Flugstunden für das aktuelle Flugzeug ermitteln
			$sql = sprintf('
				SELECT
					`kennzeichen` AS `Flugzeug`,
					YEAR(`hauptflugbuch`.`datum`) AS `Jahr`,
					SUM(((HOUR(`hauptflugbuch`.`flugzeit`) * 60) + MINUTE(`hauptflugbuch`.`flugzeit`))) AS `Flugstunden`,
					SUM(`hauptflugbuch`.`landungen`) AS `Flugbewegungen`
				FROM
					`hauptflugbuch`
				WHERE
					`hauptflugbuch`.`kennzeichen` = "%s" AND
					`hauptflugbuch`.`datum` BETWEEN "%d-01-01" AND "%d-12-31"
				GROUP BY
					`Jahr`
				UNION (
					SELECT
						`Luftfahrzeug` AS `Flugzeug`,
						YEAR(`flugbuch`.`datum`) AS `Jahr`,
						SUM(`flugbuch`.`flugzeit`) AS `Flugstunden`,
						COUNT(`flugbuch`.`luftfahrzeug`) AS `Flugbewegungen`
					FROM
						`flugbuch`
					WHERE
						`flugbuch`.`luftfahrzeug` = "%s" AND
						`flugbuch`.`datum` BETWEEN "%d-01-01" AND "%d-12-31"
					GROUP BY
						`Jahr`
				)
			',
				$kennzeichen,
				$vonJahr,
				$bisJahr,
				$kennzeichen,
				$vonJahr,
				$bisJahr
			);

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Array anlegen, welches später die Anzahl der
			// Flugstunden und die Anzahl der Landungen enthält
			$daten = array();

			for ($i=$vonJahr; $i<=$bisJahr; $i++) {
				// Array initialisieren
				$daten[$i]['Flugzeug']    = $kennzeichen;
				$daten[$i]['Flugstunden'] = '&nbsp;';
				$daten[$i]['Landungen']   = '&nbsp;';
			}
			
			while ($zeile = mysql_fetch_object($db_erg)) {
				// Array aktualisieren mit den ermittelten Werten
				$daten[$zeile->Jahr]['Flugstunden'] = $zeile->Flugstunden;
				$daten[$zeile->Jahr]['Landungen']   = $zeile->Flugbewegungen;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			foreach ($daten as $datensatz) {
				if ($datensatz['Flugstunden'] != '&nbsp;') {
					// Flugminuten in ganze Stunden umrechnen
					$datensatz['Flugstunden'] = sprintf('%s:%s',
						intval($datensatz['Flugstunden'] / 60),
						str_pad(intval($datensatz['Flugstunden'] % 60), 2, '0', STR_PAD_LEFT)
					);
				}
			
				// für Flugstunden und Flugbewegungen jeweils als eine neue Spalte anlegen
				$html .= sprintf(
					'<td align="right" style="border-left: 1px solid #ffffff; line-height: 150%%;">%s<br />%s</td>',
					$datensatz['Flugstunden'], $datensatz['Landungen']
				);
			}
		
			// Rückgabe der aktuellen Flugstatistiken
			return $html;
		}
	}
	
	/*
	 * getTabelleFlugstatistiken()
	 *
	 * gibt eine HTML-Tabelle der Flugstunden und -bewegungen der letzten zehn Jahre zurück
	 * es werden bei der Auswertung nur die Stunden und Bewegungen auf Vereinsflugzeugen berücksichtigt
	 *
	 * @return string $html
	 */
	if (!function_exists('getTabelleFlugstatistiken')) {
		function getTabelleFlugstatistiken()
		{
			// Rückgabe-Variable definieren
			$html = '';
			
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// Zeitraum der Flugauswertung festlegen
			$von = date('Y') - 10;
			$bis = date('Y');
			
			// SQL-Befehl zurechtfuddeln,
            // alle eingesetzten Flugzeuge anhand ihres Kennzeichen ermitteln
            $sql = sprintf('
				SELECT
					DISTINCT `hauptflugbuch`.`kennzeichen` AS `kennung`
				FROM
					`hauptflugbuch`
				INNER JOIN
					`flugzeuge` ON `hauptflugbuch`.`kennzeichen` = `flugzeuge`.`kennzeichen`
				WHERE
					`hauptflugbuch`.`datum`
				BETWEEN
					"%d-01-01" AND "%d-12-31" AND `flugzeuge`.`vereinsflugzeug` = "J"
				UNION (
					SELECT DISTINCT
						`flugbuch`.`luftfahrzeug` AS `kennung`
					FROM
						`flugbuch`
					INNER JOIN
						`flugzeuge` ON `flugbuch`.`luftfahrzeug` = `flugzeuge`.`kennzeichen`
					WHERE
						`flugbuch`.`datum`
					BETWEEN
						"%d-01-01" AND "%d-12-31" AND `flugzeuge`.`vereinsflugzeug` = "J"
				)
				ORDER BY `kennung` ASC
			',
				$von, $bis, $von, $bis
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Zählervariable initialisieren
			$i = 0;
			
			while ($zeile = mysql_fetch_object($db_erg)) {
				// Hintergrundfarbe jeder Zeile abwechseln gestalten
				$bgColor = ($i % 2) ? '#eeeeee' : '#cccccc';

				// neue Zeile anlegen
				$html .= sprintf('<tr height="10" bgcolor="%s">', $bgColor);
				$html .= sprintf('<td style="font-size: 9.5pt;"><strong>%s</strong><br />&nbsp;</td>', $zeile->kennung);
				$html .= sprintf('<td style="line-height: 150%%;">Flugstunden<br />Landungen</td>');

				// aktuelle Flugstatistiken zum Flugzeug holen
				$html .= getFlugstunden($zeile->kennung, $von, $bis);
				$html .= sprintf('</tr>');
				
				// Zähler erhöhen
				$i++;
			}
			
			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe des aktuellen Flugstatistiken
			return $html;
		}
	}
	
	
	// positive, sowie negative Fluggeldkonto-Salden ermitteln
	$positiv = getPositiveSalden();
	$negativ = getNegativeSalden();
	
	// die Differenzsumme berechnen
	$differenz = $positiv + $negativ;

?>
<!-- ENDE: SKRIPT -->
<!-- BEGINN: AUSGABE -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

    <head>

        <title>Startseite / &Uuml;bersicht</title>

        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
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
				
					<h2>Startseite / &Uuml;bersicht</h2>
					
					<div class="helpline">
						Willkommen in der Vereinsverwaltung.<br />
						Hier kannst Du nun die gew&uuml;nschte Funktion aus dem Men&uuml; an der linken Seite ausw&auml;hlen.
					</div>
					
					<br />
					<br />

					<table cellpadding="3" cellspacing="0" border="0" class="mitgliederliste">
						<tr height="28">
							<td colspan="4" bgcolor="#ccccff" style="border: 1px solid #8080ff; font-size: 10pt; color: navy;">
								<img src="./img/wlEmoticon-birthdaycake.png" style="margin-top: -2px;" align="left" hspace="7" />
								<strong>Geburtstage Gestern/Heute/Morgen/&Uuml;bermorgen</strong>
							</td>
						</tr>
						<tr><td colspan="4" height="2"></td></tr>
						<tr bgcolor="#666666">
							<th width="175" align="left" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff;">Name</th>
							<th width="80" align="center" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff;">Geburtstag</th>
							<th width="40" align="right" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff;">Alter</th>
							<th width="140" align="left" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; padding-left: 10px;">eMail</th>
						</tr>
						<?php echo getGeburtstage(); ?>
					</table>

					<br />

					<table border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td valign="top">
								<table cellpadding="3" cellspacing="0" border="0" class="mitgliederliste">
									<tr height="28">
										<td colspan="2" bgcolor="#ccccff" style="border: 1px solid #8080ff; font-size: 10pt; color: navy;">
											<img src="./img/Agent_group_people_users.png" style="margin-top: -2px;" width="19" align="left" hspace="7" />
											<strong>Aktuelle Mitgliederzahlen</strong>
										</td>
									</tr>
									<tr><td colspan="2" height="2"></td></tr>
									<?php echo getMitgliederzahlen(); ?>
								</table>
							</td>
							<td width="30"></td>
							<td valign="top">
								<table cellpadding="3" cellspacing="0" border="0" class="mitgliederliste">
									<tr height="28">
										<td colspan="3" bgcolor="#ccccff" style="border: 1px solid #8080ff; font-size: 10pt; color: navy;">
											<img src="./img/icon-money.png" style="margin-top: -2px;" width="21" align="left" hspace="5" />
											<strong>Aktuelle Fluggeldkonten&uuml;bersicht</strong>
										</td>
									</tr>
									<tr><td colspan="3" height="2"></td></tr>
									<tr height="20" bgcolor="#cccccc">
										<td width="65" style="padding-left: 8px;">Positiv:</td>
										<td width="120" align="right" style="color: #000000; font-weight: bold;">
										<?php echo number_format($positiv, 2, ',', ''); ?> Euro
										</td>
										<td width="150"><small>(d. h. &uuml;berzahlt)</small></td>
									</tr>
									<tr height="20" bgcolor="#eeeeee">
										<td width="65" style="padding-left: 8px;">Negativ:</td>
										<td width="120" align="right" style="color: #ff0000; font-weight: bold;">
										<?php echo number_format($negativ, 2, ',', ''); ?> Euro
										</td>
										<td width="150"><small>(d. h. noch einzuziehen)</small></td>
									</tr>
									<tr height="20" bgcolor="#cccccc">
										<td width="65" style="padding-left: 8px;">Saldo:</td>
										<td width="120" align="right" style="font-weight: bold;">
										<?php
											// Schriftfarbe bestimmen
											$fontColor = ($differenz < 0) ? '#ff0000' : '#000000';
										
											printf('
												<span style="color:%s;">%s Euro</span>',
												$fontColor, number_format($differenz, 2, ',', '')
											);
										?>
										</td>
										<td width="150">&nbsp;</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					
					<br />

					<table cellpadding="3" cellspacing="0" border="0" class="flugzeugstatistik">
						<tr height="28">
							<td colspan="13" bgcolor="#ccccff" style="border: 1px solid #8080ff; font-size: 10pt; color: navy;">
								<img src="./img/pie-chart-icon.png" style="margin-top: -2px;" align="left" hspace="7" width="21" />
								<strong>Flugbewegungen und -stunden (von <u>Vereinsflugzeugen</u>) im Vergleich zu den Vorjahren</strong>
							</td>
						</tr>
						<tr><td colspan="4" height="2"></td></tr>
						<tr bgcolor="#666666">
							<th width="85" align="left" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; font-size: 9pt;">Flugzeug</th>
							<th width="90" align="left" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; font-size: 9pt;">Jahr(e)</th>
							<th width="50" align="right" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; border-left: 1px solid #ffffff; font-size: 9pt;"><?php echo date('Y') - 10; ?></th>
							<th width="50" align="right" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; border-left: 1px solid #ffffff; font-size: 9pt;"><?php echo date('Y') - 9;  ?></th>
							<th width="50" align="right" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; border-left: 1px solid #ffffff; font-size: 9pt;"><?php echo date('Y') - 8;  ?></th>
							<th width="50" align="right" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; border-left: 1px solid #ffffff; font-size: 9pt;"><?php echo date('Y') - 7;  ?></th>
							<th width="50" align="right" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; border-left: 1px solid #ffffff; font-size: 9pt;"><?php echo date('Y') - 6;  ?></th>
							<th width="50" align="right" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; border-left: 1px solid #ffffff; font-size: 9pt;"><?php echo date('Y') - 5;  ?></th>
							<th width="50" align="right" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; border-left: 1px solid #ffffff; font-size: 9pt;"><?php echo date('Y') - 4;  ?></th>
							<th width="50" align="right" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; border-left: 1px solid #ffffff; font-size: 9pt;"><?php echo date('Y') - 3;  ?></th>
							<th width="50" align="right" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; border-left: 1px solid #ffffff; font-size: 9pt;"><?php echo date('Y') - 2;  ?></th>
							<th width="50" align="right" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; border-left: 1px solid #ffffff; font-size: 9pt;"><?php echo date('Y') - 1;  ?></th>
							<th width="50" align="right" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; border-left: 1px solid #ffffff; font-size: 9pt;"><?php echo date('Y');      ?></th>
						</tr>
						<?php echo getTabelleFlugstatistiken(); ?>
					</table>
					
					<br />
					<br />

					<table cellpadding="0" cellspacing="0" border="0" class="mitgliederliste">
						<tr height="28">
							<td bgcolor="#ccccff" style="border: 1px solid #8080ff; font-size: 10pt; color: navy;">
								<img src="./img/Chart-bar-icon.png" style="margin-bottom: -2px;" width="21" align="left" hspace="7" />
								<strong>Aktuelle Flugstatistik (Motorflug) im Vergleich zum Vorjahr</strong>
							</td>
						</tr>
						<tr><td height="2"></td></tr>
						<tr>
							<td>
								<img src="graph_motorflug.php" style="border: 1px solid #c0c0c0;" />
							</td>
						</tr>
					</table>
					
					<br />

					<table cellpadding="0" cellspacing="0" border="0" class="mitgliederliste">
						<tr height="28">
							<td bgcolor="#ccccff" style="border: 1px solid #8080ff; font-size: 10pt; color: navy;">
								<img src="./img/Chart-bar-icon.png" style="margin-bottom: -2px;" width="21" align="left" hspace="7" />          
								<strong>Aktuelle Flugstatistik (Segelflug) im Vergleich zum Vorjahr</strong>
							</td>
						</tr>
						<tr><td height="2"></td></tr>
						<tr>
							<td>
								<img src="graph_segelflug.php" style="border: 1px solid #c0c0c0;" />
							</td>
						</tr>
					</table>
					
					<br />

					<table cellpadding="0" cellspacing="0" border="0" class="mitgliederliste">
						<tr height="28">
							<td bgcolor="#ccccff" style="border: 1px solid #8080ff; font-size: 10pt; color: navy;">
								<img src="./img/Chart-bar-icon.png" style="margin-bottom: -2px;" width="21" align="left" hspace="7" />          
								<strong>Flugzeugauslastung (Segelflug &amp; Motorflug) der vergangenen Jahre</strong>
							</td>
						</tr>
						<tr><td height="2"></td></tr>
						<tr>
							<td>
								<img src="graph_umsaetze.php" style="border: 1px solid #c0c0c0;" />
							</td>
						</tr>
					</table>
					
					<br />

					<table cellpadding="0" cellspacing="0" border="0" class="mitgliederliste">
						<tr height="28">
							<td bgcolor="#ccccff" style="border: 1px solid #8080ff; font-size: 10pt; color: navy;">
								<img src="./img/Chart-bar-icon.png" style="margin-bottom: -2px;" width="21" align="left" hspace="7" />          
								<strong>Flugzeugauslastung der Motorsegler in den vergangenen Jahre</strong>
							</td>
						</tr>
						<tr><td height="2"></td></tr>
						<tr>
							<td>
								<img src="graph_motorsegler.php" style="border: 1px solid #c0c0c0;" />
							</td>
						</tr>
					</table>
					
				</td>
			</tr>
		</table>

	</body>

</html>
<!-- ENDE: AUSGABE -->