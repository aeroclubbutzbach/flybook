<!-- BEGINN: SKRIPT -->
<?php
	
	/*
	 * getMailtext()
	 *
	 * holt den letzten aktuellen Mailtext aus der Datenbank
	 *
	 * @return string $return
	 */
	if (!function_exists('getMailtext')) {
		function getMailtext()
		{
			// Rückgabe-Variable definieren
			$return = '';
		
			// SQL-Befehl zurecht fuddeln,
			// prüfen ob der Datensatz bereits vorhanden ist
			$sql = sprintf('
				SELECT
					`mailtext`
				FROM
					`mailtemplate`
				WHERE
					`opt` = "FB"
				ORDER BY
					`id` DESC
				LIMIT 1
			');
				
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			mysql_set_charset('utf8');

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			while ($zeile = mysql_fetch_object($db_erg)) {
				// Mailtext auslesen
				$return = $zeile->mailtext;
			}
			
			// Verbindung zur Datenbank schließen
			mysql_close($db_erg);
			
			// den gefundenen Mailtext zurückgeben
			return $return;
		}
	}

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
	 * printTabelleFlugstatistik()
	 *
	 * die Flugstunden und die Anzahl der Landungen der einzelnen Mitglieder
	 * werden geladen, und anschließend als HTML-Tabelle zurückgegeben
	 *
	 * @return string $html
	 */
	if (!function_exists('printTabelleFlugstatistik')) {
		function printTabelleFlugstatistik()
		{
			// Rückgabe-Variable definieren
			$html = '';
			// Variable zum Erfassen der Flugstatistik definieren
			$fluege = array();

			// Flüge der letzten 24 Monate je Mitglied ermitteln
			$alle_fluege = getListeFlugstatistik(24);
			
			// die Flüge innerhalb der letzten 24 Monate
			// werden in das Rückgabe-Array geschrieben
			foreach ($alle_fluege as $statistik) {
				$fluege[$statistik['acb_nr']]['nachname']     = $statistik['nachname'];
				$fluege[$statistik['acb_nr']]['vorname']      = $statistik['vorname'];
				$fluege[$statistik['acb_nr']]['flugzeit_24']  = $statistik['flugzeit'];
				$fluege[$statistik['acb_nr']]['landungen_24'] = $statistik['landungen'];
				$fluege[$statistik['acb_nr']]['flugzeit_12']  = '';
				$fluege[$statistik['acb_nr']]['landungen_12'] = '';
				$fluege[$statistik['acb_nr']]['flugzeit_90']  = '';
				$fluege[$statistik['acb_nr']]['landungen_90'] = '';
				$fluege[$statistik['acb_nr']]['ampel_icon']   = 'ampel_rot.gif';
			}
			
			// Flüge der letzten 12 Monate je Mitglied ermitteln
			$alle_fluege = getListeFlugstatistik(12);
			
			// die Flüge innerhalb der letzten 12 Monate
			// werden in das Rückgabe-Array geschrieben
			foreach ($alle_fluege as $statistik) {
				$fluege[$statistik['acb_nr']]['flugzeit_12']  = $statistik['flugzeit'];
				$fluege[$statistik['acb_nr']]['landungen_12'] = $statistik['landungen'];
			}
			
			// Flüge der letzten 90 Tage je Mitglied ermitteln
			$alle_fluege = getListeFlugstatistik(3);

			// die Flüge innerhalb der letzten 90 Tage
			// werden in das Rückgabe-Array geschrieben
			foreach ($alle_fluege as $statistik) {
				$fluege[$statistik['acb_nr']]['flugzeit_90']  = $statistik['flugzeit'];
				$fluege[$statistik['acb_nr']]['landungen_90'] = $statistik['landungen'];
			}
			
			// Flüge innerhalb der letzten 6 Monate ermitteln für den
			// aktuellen Trainingsstand des jeweiligen Mitgliedes
			$alle_fluege = getListeFlugstatistik(6);
			
			// die Flüge innerhalb der letzten 6 Monate
			// werden in das Rückgabe-Array geschrieben
			foreach ($alle_fluege as $statistik) {
				// Ampel-Symbol, anhand der übergebenen Parameter, ermitteln
				$fluege[$statistik['acb_nr']]['ampel_icon'] = getAmpelStatus($statistik['flugzeit'], $statistik['landungen']);
			}
			
			// Zählervariable initialisieren
			$i = 0;
			// Variable zum Zwischenspeichern des Anfangsbuchstaben initialisieren
			$buchstabe = '';
			
			// alle gefundenen Einträge durchforsten
			foreach ($fluege as $key => $value) {
				// Hintergrundfarbe jeder Zeile abwechseln gestalten
				$bgColor = ($i % 2) ? '#eeeeee' : '#cccccc';
				
				// neue Zeile anlegen
				$html .= sprintf('<tr height="24" bgcolor="%s">', $bgColor);
				
				// prüfen ob der Anfangsbuchstabe schonmal vorkam, ansonsten
				// wird dieser Links neben der Zeile zu Beginn angezeigt
				if ($buchstabe == strtoupper(substr($value['nachname'], 0, 1))) {
					// kein neuer Anfangsbuchstabe
					$html .= '<td bgcolor="#f7f7f7"></td>';
				} else {
					// neuen Anfangsbuchstaben setzen
					$html .= sprintf('<td class="buchstabe"><a name="%s">%s</a></td>', strtoupper(substr($value['nachname'], 0, 1)), strtoupper(substr($value['nachname'], 0, 1)));
				}

				// die weiteren Parameter Mitgliedsnummer, Nachname, Vorname und Flugzeiten in die Zeile einfügen
				$html .= sprintf('<td align="left" width="45" style="padding-left: 5px;">%d</td>', $key);
				$html .= sprintf('<td align="left" width="130" style="padding-left: 5px;">%s</td>', $value['nachname']);
				$html .= sprintf('<td align="left" width="130" style="padding-left: 5px;">%s</td>', $value['vorname']);
				$html .= sprintf('<td align="center" width="75">%s</td>', $value['flugzeit_24']);
				$html .= sprintf('<td align="center" width="75">%s</td>', $value['landungen_24']);
				$html .= sprintf('<td align="center" width="75">%s</td>', $value['flugzeit_12']);
				$html .= sprintf('<td align="center" width="75">%s</td>', $value['landungen_12']);
				$html .= sprintf('<td align="center" width="75">%s</td>', $value['flugzeit_90']);
				$html .= sprintf('<td align="center" width="75">%s</td>', $value['landungen_90']);
				
				// am Ende der Zeile wird der aktuelle Trainingsstand
				// des jeweiligen Mitgliedes anhand einer Ampel dargestellt
				$html .= sprintf('<td align="center" width="60"><img src="img/%s" height="20" /></td>', $value['ampel_icon']);
			
				// Ende der Zeile
				$html .= '</tr>';
				
				// Anfangsbuchstabe des aktuellen Datensatzes (Mitgliedsname) speichern
				$buchstabe = substr($value['nachname'], 0, 1);
				
				// Zähler erhöhen
				$i++;
			}
			
			// Tabellenansicht zurückgeben
			return $html;
		}
	}
	
	/*
	 * getAmpelStatus()
	 *
	 * ermittelt den Ampelstatus anhand der übergebenen Parameter für
	 * Flugzeit und Anzahl der Landungen innerhalb der letzten 6 Monate
	 *
	 * @params time    $flugzeit
	 * @params integer $landungen
	 * @return string  $icon
	 */
	if (!function_exists('getAmpelStatus')) {
		function getAmpelStatus($flugzeit, $landungen)
		{
			// Rückgabe-Variable definieren
			$icon = '';

			// Flugzeit aufdröseln und in Fließkommazahl umwandeln
			$zeit = explode(':', $flugzeit);
			// in Stunden und Minuten aufdröseln
			$stunden = $zeit[0];
			$minuten = $zeit[1] / 60;
			// Flugzeit als Kommazahl darstellen
			$flugzeit = $stunden + $minuten;

			// Berechnungen aus Starts und Anzahl der Landungen
			// zum ermitteln des Ampelsymbols durchführen
			$y1 = 715 - (473 * ($flugzeit / 30));
			$y2 = 715 - (473 * ($landungen / 42));

			if ($y1 > $y2) {
				$yy = ($y1 - $y2) / 2 + $y2 - 3;
			} else {
				$yy = ($y2 - $y1) / 2 + $y1 - 3;
			}

			// das entsprechende Ampelsymbol ermitteln
			if ($yy > 555) {
				// rot
				$icon = 'ampel_rot.gif';
			} else if (($yy <= 555) && ($yy > 397)) {
				// gelb
				$icon = 'ampel_gelb.gif';
			} else if ($yy <= 397) {
				// grün
				$icon = 'ampel_gruen.gif';
			};
			
			// Ampelsymbol zurückgeben
			return $icon;
		}
	}
	
	/*
	 * getListeFlugstatistik()
	 *
	 * es werden die Flugstunden und die Anzahl der Landungen der
	 * einzelnen Mitglieder, anhand des übergebenen Zeitraumes in
	 * Monaten, ermittelt und als Array zurückgegeben
	 *
	 * @params integer $zeitraum
	 * @return array   $return
	 */
	if (!function_exists('getListeFlugstatistik')) {
		function getListeFlugstatistik($zeitraum)
		{
			// Rückgabe-Variable definieren
			$return = array();
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');
			
			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			mysql_set_charset('utf8');

			// SQL-Befehl zurecht fuddeln,
			// Liste der Flugstatistik ermitteln
			$sql = sprintf('
				SELECT
					`t`.`acb_nr` AS `acb_nr`,
					`t`.`nachname` AS `nachname`,
					`t`.`vorname` AS `vorname`,
					`t`.`in_abrechn` AS `in_abrechn`,
					TIME_FORMAT(SEC_TO_TIME(SUM(`t`.`flugzeit`) * 60), "%%k:%%i") AS `flugzeit`,
					SUM(`t`.`landungen`) AS `landungen`
				FROM
				(
					SELECT
						`a`.`acb_nr` AS `acb_nr`,
						`a`.`nachname` AS `nachname`,
						`a`.`vorname` AS `vorname`,
						`a`.`in_abrechn` AS `in_abrechn`,
						SUM(`a`.`flugzeit`) AS `flugzeit`,
						SUM(`a`.`landungen`) AS `landungen`
					FROM (
						SELECT
							`mitglieder`.`id` AS `acb_nr`,
							`mitglieder`.`nachname` AS `nachname`,
							`mitglieder`.`vorname` AS `vorname`,
							`mitglieder`.`in_abrechn` AS `in_abrechn`,
							SUM(`flugbuch`.`flugzeit`) AS `flugzeit`,
							COUNT(*) AS `landungen`
						FROM
							`flugbuch`
						INNER JOIN
							`mitglieder` ON `flugbuch`.`besatzung1` LIKE CONCAT("%%", `mitglieder`.`ameavia`, "%%")
						WHERE
							(`flugbuch`.`datum` > DATE_SUB(NOW(), INTERVAL %d MONTH)) AND
							`flugbuch`.`geloescht` = "N"
						GROUP BY
							`mitglieder`.`id`
						UNION ALL (
							SELECT
								`mitglieder`.`id` AS `acb_nr`,
								`mitglieder`.`nachname` AS `nachname`,
								`mitglieder`.`vorname` AS `vorname`,
								`mitglieder`.`in_abrechn` AS `in_abrechn`,
								SUM(`flugbuch`.`flugzeit`) AS `flugzeit`,
								COUNT(*) AS `landungen`
							FROM
								`flugbuch`
							INNER JOIN
								`mitglieder` ON `flugbuch`.`besatzung2` LIKE CONCAT("%%", `mitglieder`.`ameavia`, "%%")
							INNER JOIN
								`flugzeuge` ON `flugbuch`.`luftfahrzeug` = `flugzeuge`.`kennzeichen`
							WHERE
								(`flugbuch`.`preiskategorie` != "FM" AND
								`flugbuch`.`preiskategorie` != "FR" AND
								(`flugbuch`.`datum` > DATE_SUB(NOW(), INTERVAL %d MONTH))) AND
									`flugbuch`.`geloescht` = "N" AND (
									(`flugzeuge`.`typ1` IN ("S1", "S2") AND FIND_IN_SET("C", `mitglieder`.`fachausweise`)) OR
									(`flugzeuge`.`typ1` IN ("MS") AND FIND_IN_SET("B", `mitglieder`.`fachausweise`)) OR
									(`flugzeuge`.`typ1` IN ("M1", "M2", "M3") AND FIND_IN_SET("A", `mitglieder`.`fachausweise`)) OR
									(`flugzeuge`.`typ1` = "UL" AND FIND_IN_SET("H", `mitglieder`.`fachausweise`))
								)
							GROUP BY
								`mitglieder`.`id`
						)
					) AS `a`
					WHERE
						`a`.`flugzeit` > 0
					GROUP BY
						`a`.`acb_nr`
					UNION ALL (
						SELECT
							`b`.`acb_nr` AS `acb_nr`,
							`b`.`nachname` AS `nachname`,
							`b`.`vorname` AS `vorname`,
							`b`.`in_abrechn` AS `in_abrechn`,
							(HOUR(SEC_TO_TIME(SUM(TIME_TO_SEC(`b`.`flugzeit`)))) * 60) +
							MINUTE(SEC_TO_TIME(SUM(TIME_TO_SEC(`b`.`flugzeit`)))) AS `flugzeit`,
							SUM(`b`.`landungen`) AS `landungen`
						FROM (
							SELECT
								`hauptflugbuch`.`pilot` AS `acb_nr`,
								`mitglieder`.`nachname` AS `nachname`,
								`mitglieder`.`vorname` AS `vorname`,
								`mitglieder`.`in_abrechn` AS `in_abrechn`,
								SEC_TO_TIME(SUM(TIME_TO_SEC(`hauptflugbuch`.`flugzeit`))) AS `flugzeit`,
								SUM(`hauptflugbuch`.`landungen`) AS `landungen`
							FROM
								`hauptflugbuch`
							INNER JOIN
								`mitglieder` ON `hauptflugbuch`.`pilot` = `mitglieder`.`id`
							WHERE
								(`hauptflugbuch`.`datum` > DATE_SUB(NOW(), INTERVAL %d MONTH)) AND
								`hauptflugbuch`.`geloescht` = "N"
							GROUP BY
								`hauptflugbuch`.`pilot`
							UNION ALL (
								SELECT
									`hauptflugbuch`.`begleiter` AS `acb_nr`,
									`mitglieder`.`nachname` AS `nachname`,
									`mitglieder`.`vorname` AS `vorname`,
									`mitglieder`.`in_abrechn` AS `in_abrechn`,
									SEC_TO_TIME(SUM(TIME_TO_SEC(`hauptflugbuch`.`flugzeit`))) AS `flugzeit`,
									SUM(`hauptflugbuch`.`landungen`) AS `landungen`
								FROM
									`hauptflugbuch`
								INNER JOIN
									`flugzeuge` ON `hauptflugbuch`.`kennzeichen` = `flugzeuge`.`kennzeichen`
								INNER JOIN
									`mitglieder` ON `hauptflugbuch`.`begleiter` = `mitglieder`.`id`
								WHERE
									(`hauptflugbuch`.`art` != 8 AND
									`hauptflugbuch`.`geloescht` = "N" AND
									(`hauptflugbuch`.`datum` > DATE_SUB(NOW(), INTERVAL %d MONTH))) AND (
										(`flugzeuge`.`typ1` IN ("S1", "S2") AND FIND_IN_SET("C", `mitglieder`.`fachausweise`)) OR
										(`flugzeuge`.`typ1` IN ("MS") AND FIND_IN_SET("B", `mitglieder`.`fachausweise`)) OR
										(`flugzeuge`.`typ1` IN ("M1", "M2", "M3") AND FIND_IN_SET("A", `mitglieder`.`fachausweise`)) OR
										(`flugzeuge`.`typ1` = "UL" AND FIND_IN_SET("H", `mitglieder`.`fachausweise`))
									)
								GROUP BY
									`hauptflugbuch`.`begleiter`
							)
						) AS `b`
						WHERE
							`b`.`flugzeit` > 0
						GROUP BY
							`b`.`acb_nr`
					)
				) AS `t`
				WHERE
					`t`.`acb_nr` < 9996 AND `t`.`in_abrechn` = "J"
				GROUP BY
					`t`.`acb_nr`
				ORDER BY
					`t`.`nachname` ASC,
					`t`.`vorname` ASC
			',
				$zeitraum, $zeitraum,
				$zeitraum, $zeitraum
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
		
			while ($zeile = mysql_fetch_object($db_erg)) {
				// die ermittelten Flugzeiten und die Anzahl der Starts
				// und Landungen je Mitglied in das Rückgabe-Array schreiben
				$return[] = array(
					'acb_nr'    => $zeile->acb_nr,
					'nachname'  => $zeile->nachname,
					'vorname'   => $zeile->vorname,
					'flugzeit'  => $zeile->flugzeit,
					'landungen' => $zeile->landungen
				);
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);

			// Rückgabe der Flugstatistik
			return $return;
		}
	}
	
	
	/**************************************************************************************************************************/
	/* ------------------------------------------ BEGINN : FILTER NACH POST-BEFEHL ------------------------------------------ */
	/**************************************************************************************************************************/
	// es wird geprüft, ob der POST-Befehl ausgeführte wurde und
	// entsprechende Kriterien zum Filtern ausgewählt wurden
	if (isset($_POST) && !empty($_POST)) {
		echo 'HOI';
	}
	/**************************************************************************************************************************/
	/* ------------------------------------------- ENDE : FILTER NACH POST-BEFEHL ------------------------------------------- */
	/**************************************************************************************************************************/

?>
<!-- ENDE: SKRIPT -->
<!-- BEGINN: AUSGABE -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

    <head>

        <title>Trainingsst&auml;nde &amp; Flugstatistik je Mitglied</title>

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="Content-Script-Type" content="text/javascript" />
        <meta http-equiv="Content-Style-Type" content="text/css" />
        <meta http-equiv="content-language" content="de" />
        <meta name="author" content="Benjamin Stopfkuchen" />
		
		<script type="text/javascript" src="./js/jquery-1.10.2.js"></script>
		<script type="text/javascript" src="./js/jquery-1.10.2.min.js"></script>

        <link rel="Stylesheet" type="text/css" href="./css/stylish.css" />
		
		<script type="text/javascript" language="JavaScript">
		<!--

			$(document).ready(function() {
				$(this).ajaxStart(function() {
					$('body').append('<div id="overlay"><img id="ladegrafik" src="./img/img_ajax_ladegrafik.gif" /></div>');
					
					$('#overlay').css('top',    '0px');
					$('#overlay').css('left',   '0px');
					$('#overlay').css('width',  (parseInt($('body').width())));
					$('#overlay').css('height', (parseInt($('body').height())));

					$('#ladegrafik').css('position', 'absolute');
					$('#ladegrafik').css('left', (parseInt($('body').width()) / 2) - 66);
					$('#ladegrafik').css('top',  (parseInt($(window).height()) - 450));
				});

				$(this).ajaxStop(function() {
					$('#overlay').remove();
				});
				
				$('#form_training').submit(function() {
					// AJAX ausführen
					$.get('pdf_flugbuchauszug.php', function(data) {
						$.get('ajax_mailversand.php', function(data) {
							if (data == 1) {
								$('.info_message').css('display', 'block');
							} else {
								$('.error_message').css('display', 'block');
							}
						});
					});
					
					return false;
				});
			});

		</script>
		
    </head>

	<body style="margin-top: 0px; margin-left: 0px;">
	
		<table style="border: 1px solid #000000; background-color: #f7f7f7;" width="100%" height="100%">
			<tr>
				<td valign="top" style="padding:20px;">
				
					<h2>Trainingsst&auml;nde &amp; Flugstatistik(en) <small>je Mitglied</small><strong style="margin-left: 50px; font-size: 10pt;">Stichtag: <?php echo date('d.m.Y') ?></strong></h2>
					
					<div class="helpline">
						Hier findest Du eine &Uuml;bersicht der aktuellen Trainingsst&auml;nde,
						sowie der Flugstatistik(en) der Mitglieder.
						<br />
						Weiter unten kannst Du sogar einen aktuellen Auszug aus dem Flugbuch (des aktuellen Jahres) und
						der Flugstatistik (Gesamtstunden der letzten 12 bzw. 24 Monate) an die einzelnen Mitglieder, als
						Information, per Rundmail versenden.
					</div>
					
					<br />
					
					<div class="anfangsBuchstaben"><?php echo getAnfangsbuchstaben(); ?></div>
					
					<br />
					
					<table width="92%">
						<tr>
							<td width="140"><a href="#unten" class="neuanlageMitglied" style="font-size:10pt !important;"><img src="./img/agt_update_misc.png" border="0" align="left" width="22" height="22" style="margin-top: -2px; margin-left:15px; margin-right:7px;" /> Flugstatistiken per eMail versenden</a></td>
						</tr>
					</table>
					
					<br />
					
					<table border="0" cellspacing="1" cellpadding="1" class="fluggeldkonten">

						<tr height="22">
							<th width="24" rowspan="3"></th>
							<th align="left" valign="top" rowspan="3" width="45" bgcolor="#666666" style="color: #ffffff; padding-left: 5px; padding-top: 4px;">Mitgl-<br />Nr</th>
							<th align="left" valign="top" rowspan="3" width="130" bgcolor="#666666" style="color: #ffffff; padding-left: 5px; padding-top: 4px;">Nachname</th>
							<th align="left" valign="top" rowspan="3" width="130" bgcolor="#666666" style="color: #ffffff; padding-left: 5px; padding-top: 4px;">Vorname</th>
							<th colspan="6" align="center" bgcolor="#666666" style="color: #ffffff;">Flugzeit und Fl&uuml;ge innerhalb der letzten ...</th>
							<th align="left" valign="top" rowspan="3" width="60" bgcolor="#666666" style="color: #ffffff; padding-left: 5px; padding-top: 4px;">&Uuml;bungs-<br />stand</th>
						</tr>		
						<tr height="20">
							<th colspan="2" align="center" bgcolor="#666666" style="color: #ffffff;">24 Monate</th>
							<th colspan="2" align="center" bgcolor="#666666" style="color: #ffffff;">12 Monate</th>
							<th colspan="2" align="center" bgcolor="#666666" style="color: #ffffff;">90 Tage <small>(Gastflug)</small></th>
						</tr>						
						<tr height="20">
							<th align="center" width="75" bgcolor="#666666" style="color: #ffffff;">Zeit</th>
							<th align="center" width="75" bgcolor="#666666" style="color: #ffffff;">Fl&uuml;ge</th>
							<th align="center" width="75" bgcolor="#666666" style="color: #ffffff;">Zeit</th>
							<th align="center" width="75" bgcolor="#666666" style="color: #ffffff;">Fl&uuml;ge</th>
							<th align="center" width="75" bgcolor="#666666" style="color: #ffffff;">Zeit</th>
							<th align="center" width="75" bgcolor="#666666" style="color: #ffffff;">Fl&uuml;ge</th>
						</tr>

						<?php echo printTabelleFlugstatistik(); ?>
					
					</table>
					
					<br />
					<hr />
					<br />
					
					<a name="unten"></a>

					<div class="helpline">
						Hier hast Du die M&ouml;glichkeit jedem Mitglied einen aktuellen Auszug seiner Fl&uuml;ge zuzusenden.
						<br />
						Dies beinhaltet den Flugbuchauszug des laufenden Jahres, sowie der Anzahl der Flugstunden und
						Flugbewegungen der letzten 12 bzw. 24 Monate und den aktuellen &Uuml;bungsstand lt. des
						offiziellen DAeC-Trainingsbarometers.
					</div>
					
					<!-- Infomeldung -->
					<div class="info_message">
						<br />
						<div class="infoline">
							<h3>eMails erfolgreich versendet!</h3>
							Die Ausz&uuml;ge aus dem Flugbuch wurden erfolgreich an alle Mitglieder versendet!
						</div>
						<br />
					</div>
					<div class="error_message">
						<br />
						<div class="errorline">
							<h3>Ein Fehler ist aufgetreten!</h3>
							Der Versand der Ausz&uuml;ge aus dem Flugbuch verlief leider nicht fehlerfrei.<br />
							Probier es doch einfach noch einmal!
						</div>
						<br />
					</div>
					<!-- Infomeldung -->
					
					<br />

					<form action="trainingsstaende.php" method="POST" id="form_training">
						<table class="monatsabrechnung">
							<tr>
								<td>
									<fieldset>
										<legend style="color:#333333;">
											<img src="./img/pencil_16_top.png" align="left" hspace="5" />
											<strong>eMailtext verfassen</strong>
										</legend>
										<textarea id="mailtext" name="mailtext" class="mailversand" style="width:630px;height:200px;font-family:Courier New;padding:5px;"><?php echo getMailtext(); ?></textarea>
									</fieldset>
								</td>
							</tr>
							<tr height="5"></tr>
							<tr>
								<td>
									<fieldset>
										<legend style="color:#333333;">
											<img src="./img/envelope-2-19.png" align="left" hspace="5" />
											<strong>Ausz&uuml;ge aus dem Flugbuch versenden</strong>
										</legend>
										<table class="monatsabrechnung" style="margin-top: -5px;">
											<tr>
												<td align="right">
													<input type="submit" value="Auszug (Flugbuch) per eMail versenden" name="button_mailversand" id="button_mailversand" />
												</td>
											</tr>
										<table>
									</fieldset>
								</td>
							</tr>
						</table>
					</form>
					
				</td>
			</tr>
		</table>

	</body>

</html>
<!-- ENDE: AUSGABE -->