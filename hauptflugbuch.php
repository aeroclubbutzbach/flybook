<!-- BEGINN: SKRIPT -->
<?php

	/*
	 * printMonatskalender()
	 *
	 * gibt einen Monatskalender anhand der übergebenen
	 * Parameter für Jahres- und Monatszahl zurück
	 *
	 * @params integer $jahr
	 * @params integer $monat
	 * @return string  $html
	 */
	if (!function_exists('printMonatskalender')) {
		function printMonatskalender($jahr, $monat)
		{
			// Rückgabe-Variable definieren
			$html = '';
			
			// Array mit Monatsnamen anlegen
			$monate = array(
				 1 => 'Januar',   2 => 'Februar',   3 => 'M&auml;rz',
				 4 => 'April',    5 => 'Mai',       6 => 'Juni',
				 7 => 'Juli',     8 => 'August',    9 => 'September',
				10 => 'Oktober', 11 => 'November', 12 => 'Dezember'
			);
			
			// Kalender als HTML-Tabelle darstellen
			// die Überschriften
			$html = sprintf('
				<table cellpadding="0" cellspacing="1" border="0" class="monats_kalender">
					<tr class="monat_name" height="27" bgcolor="#ccccff">
						<th colspan="7" style="border: 1px solid #8080ff; font-size: 12pt; color: navy;">%s</th>
					</tr>
					<tr height="2"></tr>
					<tr class="wochentage" height="20" bgcolor="#666666">
						<th width="30">Mo</th>
						<th width="30">Di</th>
						<th width="30">Mi</th>
						<th width="30">Do</th>
						<th width="30">Fr</th>
						<th width="30">Sa</th>
						<th width="30">So</th>
					</tr>
			',
				$monate[$monat]
			);
			
			// die maximale Anzahl der Tage des übergebenen Monats ermitteln
			$max_tage = date('t', mktime(0, 0, 0, $monat, 1, $jahr));
			// den ersten und den letzten Wochentag des übergebenen Monats ermitteln
			$erster_wochentag  = date('N', mktime(0, 0, 0, $monat, 1, $jahr));
			$letzter_wochentag = date('N', mktime(0, 0, 0, $monat, $max_tage, $jahr));
			// heutiges Datum ermitteln
			$heute = array(
				'tag' => date('j'), // Tag
				'mon' => date('n'), // Monat
				'jhr' => date('Y')  // Jahr
			);

			// Anfang und Ende der Tabellenzelle bestimmen
			$start = 1 - $erster_wochentag + 1;
			$ende  = 7 - $letzter_wochentag + $max_tage;
			
			// Zähler für die Wochentage initialisieren
			$j = 1;
			// Variable für Zeilenhintergrund initialisieren
			$bgColor = '';

			for ($i=$start; $i<=$ende; $i++) {
				// handelt es sich um einen Montag,
				// dann eine neue Zeile/Woche im Kalender anfangen
				if ($j == 1) {
					// Hintergrundfarbe für gerade/ungerade Zeilen festlegen
					$bgColor = ($bgColor == '#eeeeee') ? '#cccccc' : '#eeeeee';

					// neue Zeile
					$html .= sprintf('<tr class="wochenkalender" height="22" bgcolor="%s">', $bgColor);
				}
			
				// es wird geprüft ob sich die Zählervariable im
				// aktuell gültigen Bereich der Kalendertage befindet
				if (($i >= 1) && ($i <= $max_tage)) {
					// prüfen, ob es sich um den heutigen Tag handelt
					if (
						($jahr == $heute['jhr']) &&
						($monat == $heute['mon']) &&
						($i == $heute['tag'])
					) {
						// den heutigen Tag einfügen und besonders markieren
						$html .= sprintf('<td align="center" style="background: #ffe7e7; border: 2px solid #ff0000;">%d</td>', $i);
					} else {
						// prüfen ob eine Startliste existiert
						if (getStartliste($i, $monat, $jahr) > 0) {
							// den aktuellen Tag einfügen, inklusive Verlinkung zum Bearbeiten
							$html .= sprintf('
								<td align="center">
									<a href="startliste.php?datum_id=%s-%s-%s">%s</a>
								</td>
							',
								$jahr,
								str_pad($monat, 2, '0', STR_PAD_LEFT),
								str_pad($i, 2, '0', STR_PAD_LEFT),
								$i
							);
						} else {
							// den aktuellen Tag einfügen
							$html .= sprintf('<td align="center">%d</td>', $i);
						}
					}
				} else {
					// eine leere Zeile einfügen
					$html .= '<td>&nbsp;</td>';
				}
				
				// handelt es sich um einen Sonntag,
				// dann die aktuelle Zeile/Woche im Kalender anfangen				
				if ($j == 7) {
					// Zeile beenden
					$html .= '</tr>';
					// Wochenzähler zurücksetzen
					$j = 1;
				} else {
					// Wochenzähler erhöhen
					$j++;
				}
			}
			
			$html .= sprintf('
				<tr height="30" bgcolor="#ffffe1">
					<td colspan="7" align="right" valign="middle" style="padding-right: 7px; border: 1px solid #e5e5e5;">
						<a href="pdf_bordbuch.php?von=%s-%s-01&bis=%s-%s-%s" target="_blank"><img src="img/file_icon_pdf_blue.gif" align="right" width="18" height="18" hspace="2" /></a>
						<a href="pdf_startliste.php?von=%s-%s-01&bis=%s-%s-%s" target="_blank"><img src="img/pdf-icon.gif" align="right" width="17" height="17" hspace="3" style="margin-top: 1px;" /></a>
					</td>
				</tr>
			',
				$jahr, str_pad($monat, 2, '0', STR_PAD_LEFT), $jahr, str_pad($monat, 2, '0', STR_PAD_LEFT), $max_tage,
				$jahr, str_pad($monat, 2, '0', STR_PAD_LEFT), $jahr, str_pad($monat, 2, '0', STR_PAD_LEFT), $max_tage
			);
			
			// Tabellenende
			$html .= '</table>';
			
			// Rückgabe des Monatskalender
			return $html;
		}
	}
	
	/*
	 * getStartliste()
	 *
	 * es wird geprüft, ob für das übergebene Datum Flüge vorhanden sind oder nicht,
	 * entsprechend wird die Summe der gefundenen Flüge zurückgegeben oder eben 0
	 *
	 * @params integer $tag
	 * @params integer $monat
	 * @params integer $jahr
	 * @return integer $return
	 */
	if (!function_exists('getStartliste')) {
		function getStartliste($tag, $monat, $jahr)
		{
			// Rückgabe-Variable definieren
			$return = 0;
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
			// Summe der Flüge für den übergebenen Tag ermitteln
			$sql = sprintf('
				SELECT
					`datum` AS `summe`
				FROM
					`logbuch`
				WHERE
					`datum` = "%d-%d-%d"
				LIMIT 1
			',
				$jahr, $monat, $tag
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// es sind Datensätze vorhanden
			if (mysql_num_rows($db_erg) > 0) {
				while ($zeile = mysql_fetch_object($db_erg)) {
					// falls Datensätze gefunden wurden,
					// wird die Anzahl jener zurückgegeben
					$return = $zeile->summe;
				}
			}
			
			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe ob Datensätze vorhanden oder nicht
			return $return;
		}
	}



	/**************************************************************************************************************************/
	/* --------------------------------------- BEGINN : KALENDER LADEN NACH GET-BEFEHL -------------------------------------- */
	/**************************************************************************************************************************/
	// aktuelle Jahreszahl ermitteln
	$jahreszahl = date('Y');
	
	if (isset($_GET['goto'])) {
		// prüfen ob eine alternative Jahreszahl gesetzt wurde
		$jahreszahl = $_GET['goto'];
	}
	/**************************************************************************************************************************/
	/* --------------------------------------- ENDE : KALENDER LADEN NACH GET-BEFEHL ---------------------------------------- */
	/**************************************************************************************************************************/

?>
<!-- ENDE: SKRIPT -->
<!-- BEGINN: AUSGABE -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

    <head>

        <title>Hauptflugbuch</title>

        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
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
				$('table.tabelle_kalender .monats_kalender tr.wochenkalender td > a').mouseover(function() {
					$(this).parent().css('background', '#ccccff');
					$(this).parent().css('border', '1px solid navy');
				});
				
				$('table.tabelle_kalender .monats_kalender tr.wochenkalender td > a').mouseout(function() {
					$(this).parent().css('background', 'none');
					$(this).parent().css('border', 'none');
				});
			});
		
		//-->
		</script>
		
    </head>

	<body style="margin-top: 0px; margin-left: 0px;">
	
		<table style="border: 1px solid #000000; background-color: #f7f7f7;" width="100%" height="100%">
			<tr>
				<td valign="top" style="padding:20px;">
				
					<h2>Hauptflugbuch</h2>
					
					<div class="helpline">
						Das Hauptflugbuch (Startkladde) enth&auml;lt alle Angaben zu den Flugbewegungen am Platz.
						<br />
						<br />
						So k&ouml;nnen, bereits aus AmeAvia importierte Fl&uuml;ge (Datum, Kennzeichen, Startarten, Pilot, Begleiter,
						Startzeit und Landezeit, Startort und Landeort, Anzahl Landungen, Strecke, Motorz&auml;hler, Gastfluggeb&uuml;hren,
						und Bemerkungen, nachtr&auml;glich editiert oder erg&auml;nzt werden.
					</div>
					
					<br />
					<br />

					<table border="0" cellspacing="1" class="tabelle_kalender">
						<tr>
							<td></td>
							<td align="center" valign="middle">
								<a href="hauptflugbuch.php?goto=<?php echo $jahreszahl - 1; ?>"><img src="img/arrow_left_green.png" border="0" align="left" /></a>
								<span id="jahr"><?php echo $jahreszahl; ?></span>
								<a href="hauptflugbuch.php?goto=<?php echo $jahreszahl + 1; ?>"><img src="img/arrow_right_green.png" border="0" align="right" /></a>
							</td>
							<td></td>
						</tr>
						<tr height="20"></tr>
						<tr>
							<td width="250" align="center" valign="top"><?php echo printMonatskalender($jahreszahl, 1); ?></td>
							<td width="250" align="center" valign="top"><?php echo printMonatskalender($jahreszahl, 2); ?></td>
							<td width="250" align="center" valign="top"><?php echo printMonatskalender($jahreszahl, 3); ?></td>
						</tr>
						<tr>
							<td width="250" align="center" valign="top"><?php echo printMonatskalender($jahreszahl, 4); ?></td>
							<td width="250" align="center" valign="top"><?php echo printMonatskalender($jahreszahl, 5); ?></td>
							<td width="250" align="center" valign="top"><?php echo printMonatskalender($jahreszahl, 6); ?></td>
						</tr>
						<tr>
							<td width="250" align="center" valign="top"><?php echo printMonatskalender($jahreszahl, 7); ?></td>
							<td width="250" align="center" valign="top"><?php echo printMonatskalender($jahreszahl, 8); ?></td>
							<td width="250" align="center" valign="top"><?php echo printMonatskalender($jahreszahl, 9); ?></td>
						</tr>
						<tr>
							<td width="250" align="center" valign="top"><?php echo printMonatskalender($jahreszahl, 10); ?></td>
							<td width="250" align="center" valign="top"><?php echo printMonatskalender($jahreszahl, 11); ?></td>
							<td width="250" align="center" valign="top"><?php echo printMonatskalender($jahreszahl, 12); ?></td>
						</tr>
					</table>
					
				</td>
			</tr>
		</table>

	</body>

</html>
<!-- ENDE: AUSGABE -->