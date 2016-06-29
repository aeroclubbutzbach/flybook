<!-- BEGINN: SKRIPT -->
<?php

	// allgemeine Funktionen einbinden
	include_once('./functions.php');

	/*
	 * updateZahlungen()
	 *
	 * die per Parameter übergebenen Buchungsangaben werden auf das
	 * Fluggeldkonto vom aktuell ausgewählten Mitglied verbucht
	 *
	 * @params integer $acb_nr
	 * @params array   $buchung
	 */
	if (!function_exists('updateZahlungen')) {
		function updateZahlungen($acb_nr, array $buchung)
		{
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// Datum umbauen, falls nötig
			$datum = toSqlDatum($buchung['buchungsdatum']);
			
			// beim Betrag Komma durch Punkt ersetzen
			$summe = str_replace(',', '.', $buchung['betrag']);
		
			// SQL-Befehl zurecht fuddeln,
			// eine neue Buchung soll hinzugefügt werden
			$sql = sprintf('
				INSERT INTO
					`zahlungen` (
						`datum`,
						`summe`,
						`acb_nr`,
						`bemerkungen`
					)
					VALUES (
						"%s",
						%s,
						%s,
						"%s"
					)
			',
				$datum,
				$summe,
				$acb_nr,
				utf8_decode($buchung['bemerkungen'])
			);
			
			// SQL-Befehl ausführen und Buchungen aktualisieren
			mysql_query($sql);

			// SQL-Befehl zurecht fuddeln,
			// das entsprechende Fluggeldkonto soll aktualisiert werden
			$sql = sprintf('UPDATE `fluggeldkonto` SET `saldo` = `saldo` + (%s) WHERE `acb_nr` = %d', $summe, $acb_nr);

			// SQL-Befehl ausführen und Fluggeldkonto aktualisieren
			mysql_query($sql);
		}
	}
	
	/*
	 * setKopfdaten()
	 *
	 * die Kopfdaten (Name, Mitgliedsnummer), sowie der aktuelle
	 * Kontostand des Fluggeldkontos werden ermittelt
	 *
	 * @params integer $acb_nr
	 * @params array   $params
	 */
	if (!function_exists('setKopfdaten')) {
		function setKopfdaten($acb_nr, array &$params)
		{
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			mysql_set_charset('utf8');
			
			// SQL-Befehl zurecht fuddeln,
			// Kopfdaten und aktuellen Saldo des Fluggeldkontos ermitteln
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
					`fluggeldkonto`.`acb_nr` = %d
				LIMIT 1
			',
				$acb_nr
			);

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			if ($zeile = mysql_fetch_object($db_erg)) {
				// Kopfdaten und Saldo auslesen und in die globalen Parameter-Variablen schreiben
				$params['mitglied_nachname']   = $zeile->nachname;
				$params['nachname']            = $zeile->nachname;
				$params['vorname']             = $zeile->vorname;
				$params['acb_nr']              = $zeile->acb_nr;
				$params['bgColorSaldo']        = ($zeile->saldo < 0) ? '#ff0000' : '#000000';
				$params['fluggeldkonto_saldo'] = $zeile->saldo;
				$params['saldo_aktuell']       = $zeile->saldo;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
		}
	}
	
	/*
	 * getFluggeldkontoTabelle()
	 *
	 * die Tabellenansicht vom Fluggeldkonto des ausgewählten Mitglieds,
	 * welches anhand des übergebenen Parameters ermittelt wird, wird geladen
	 *
	 * @params array $fluggeldkonto
	 * @params array $params
	 * @return array $html
	 */
	if (!function_exists('getFluggeldkontoTabelle')) {
		// Definitionen festlegen
		define('DD',   0); // Tag
		define('MM',   1); // Monat
		define('YYYY', 2); // Jahr

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
		
		function getFluggeldkontoTabelle(array $fluggeldkonto, array $params)
		{
			// Rückgabe-Variable definieren
			$html = '';
			
			// die einzelnen Einträge rückwärts durchlaufen
			for ($i = count($fluggeldkonto); $i >= 0; $i--) {
				// es wird geprüft, ob es kein leerer Eintrag ist
				if (!empty($fluggeldkonto[$i])) {
					// aktuellen Eintrag, separiert per Semikolon,
					// splitten und anschließend als Array wiedergeben
					$item = explode(';', $fluggeldkonto[$i]);
					
					// Datum formatieren von JJJJ-MM-TT nach TT.MM.JJJJ
					$datum_splitten  = explode('-', $item[FLUGDATUM]);
					$item[FLUGDATUM] = sprintf('%s.%s.%s',
						$datum_splitten[YYYY], $datum_splitten[MM], $datum_splitten[DD]
					);
				
					// Hintergrundfarbe für gerade/ungerade Zeilen festlegen
					$bgColor = ($i % 2) ? '#eeeeee' : '#cccccc';
					
					// ist der Saldo negativ wird die Farbe rot
					// für den entsprechenden Betrag verwendet
					$bgColorSaldo_1 = ($item[ZW_SALDO] < 0) ? '#ff0000' : '#000000';
					$betrag_saldo   = $params['fluggeldkonto_saldo'] + ($item[ZW_SALDO] * -1);
					// ist der Gesamtsaldo ebenfalls negativ, dann bitte auch in rot
					$bgColorSaldo_2 = ($betrag_saldo < 0) ? '#ff0000' : '#000000';
					
					// globalen Parameter für den Saldo mit dem von eben aktualisieren
					$params['fluggeldkonto_saldo'] = $betrag_saldo;
					
					// bei Geldbeträgen als Dezimaltrennzeichen immer Kommata
					// verwenden und auf zwei Nachkommastellen aufrunden
					$item[ZW_SALDO] = number_format($item[ZW_SALDO], 2, ',', '');
					$betrag_saldo   = number_format($betrag_saldo,   2, ',', '');

					// eine neue Zeile für die Tabelle anlegen inkl. Hintergrundfarbe
					$html .= sprintf('<tr bgcolor="%s" height="22">', $bgColor);
					
					// Tabelleninhalte zuweisen
					$html .= sprintf('<td>%s</td>', $item[FLUGDATUM]);
					$html .= sprintf('<td>%s</td>', $item[LFZ_TYP]);
					$html .= sprintf('<td>%s</td>', $item[LFZ_KENNZ]);
					$html .= sprintf('<td>%s</td>', $item[PILOT]);
					$html .= sprintf('<td>%s</td>', $item[CO_PILOT]);
					$html .= sprintf('<td>%s</td>', $item[STARTART]);
					
					// sind die nachfolgenden Felder leer, handelt es sich um keinen
					// Flugeintrag, sondern um eine geleistete Zahlung oder Forderung
					if (
						empty($item[LFZ_TYP]) && empty($item[LFZ_KENNZ]) &&
						empty($item[PILOT]) && empty($item[CO_PILOT]) &&
						empty($item[STARTART])
					) {
						// Zahlung einfügen und negative Beträge rot kennzeichen
						$html .= sprintf('<td align="left" colspan="6">%s</td>', $item[ZAHLUNGEN]);
						$html .= sprintf('<td align="right" style="color:%s;">%s</td>', $bgColorSaldo_1, $item[ZW_SALDO]);
						$html .= sprintf('<td align="right" style="color:%s;">%s</td>', $bgColorSaldo_2, $betrag_saldo);
					} else {
						$html .= sprintf('<td align="left">%s</td>',              $item[STARTORT]);  // Startort
						$html .= sprintf('<td align="left">%s</td>',              $item[LANDEORT]);  // Landeort
						$html .= sprintf('<td align="left">%s</td>',              $item[STARTZEIT]); // Startzeit
						$html .= sprintf('<td align="left">%s</td>',              $item[LANDEZEIT]); // Landezeit
						$html .= sprintf('<td align="right">%s&nbsp;&nbsp;</td>', $item[FLUGDAUER]); // Flugdauer
						$html .= sprintf('<td align="left">%s</td>',              $item[PREIS_KAT]); // Preiskategorie
						$html .= sprintf('<td align="right" style="color:%s;">%s</td>', $bgColorSaldo_1, $item[ZW_SALDO]); // Betrag
						$html .= sprintf('<td align="right" style="color:%s;">%s</td>', $bgColorSaldo_2, $betrag_saldo);   // letzter Saldo
					}
					
					// Ende der Zeile
					$html .= '</tr>';
				}
			}

			// Rückgabe der Tabellenansicht
			return $html;
		}
	}
	

	/**************************************************************************************************************************/
	/* ------------------------------------------ BEGINN : IMPORT NACH GET-BEFEHL ------------------------------------------- */
	/**************************************************************************************************************************/
	// Behelfsvariable zum Speichern der notwendigen Parameter anlegen
	$params = array();

	if (isset($_GET['acb_nr'])) {
		// es wird geprüft, ob eine neue Buchung angelegt werden soll
		if (isset($_POST['buchungsdatum'])) {
			// Zahlung auf das Fluggeldkonto des aktuellen Mitglieds verbuchen
			updateZahlungen($_GET['acb_nr'], $_POST);
			
			// POST-Variable zurücksetzen
			unset($_POST);
		}

		// Kopfdaten und Kontostand des Mitglieds laden
		setKopfdaten($_GET['acb_nr'], $params);

		// Fluggeldkonto für das aktuell ausgewählte Mitglied ermitteln
		$fluggeldkonto_array = getFluggeldkonto($_GET['acb_nr']);

		// Tabellenansicht für das aktuelle Fluggeldkonto ermitteln
		if (!empty($fluggeldkonto_array)) {
			$fluggeldkonto_tabelle = getFluggeldkontoTabelle($fluggeldkonto_array, $params);	
		}
	}
	/**************************************************************************************************************************/
	/* ------------------------------------------- ENDE : IMPORT NACH GET-BEFEHL -------------------------------------------- */
	/**************************************************************************************************************************/
	
?>
<!-- ENDE: SKRIPT -->
<!-- BEGINN: AUSGABE -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

    <head>

        <title>Fluggeldkonto bearbeiten</title>

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="Content-Script-Type" content="text/javascript" />
        <meta http-equiv="Content-Style-Type" content="text/css" />
        <meta http-equiv="content-language" content="de" />
        <meta name="author" content="Benjamin Stopfkuchen" />
		
		<script type="text/javascript" src="./js/jquery-1.10.2.js"></script>
		<script type="text/javascript" src="./js/jquery-1.10.2.min.js"></script>
		<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
		<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

        <link rel="Stylesheet" type="text/css" href="./css/stylish.css" />
		<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
		
		<script type="text/javascript" language="JavaScript">
		<!--
		
			function validateNumber(event)
			{
				var key = window.event ? event.keyCode : event.which;

				if (event.keyCode == 8 || event.keyCode == 46
				 || event.keyCode == 37 || event.keyCode == 39
				 || (key >= 43 && key <= 46)) {
					return true;
				}
				else if (key < 48 || key > 57) {
					return false;
				}
				else return true;
			};
		
			jQuery(function($) {
				// Initialisierung der Datumsauswahl
				// -> jQuery-UI-Komponente DatePicker
				$.datepicker.regional['de'] = {
					clearText: 'l&ouml;schen',
					clearStatus: 'aktuelles Datum l&ouml;schen',
					closeText: 'schlie&szlig;en',
					closeStatus: 'ohne &Auml;nderungen schlie&szlig;en',
					prevText: '< zur&uuml;ck',
					prevStatus: 'letzten Monat zeigen',
					nextText: 'vor >',
					nextStatus: 'n&auml;chsten Monat zeigen',
					currentText: 'heute',
					currentStatus: '',
					monthNames: ['Januar','Februar','M&auml;rz','April','Mai','Juni', 'Juli','August','September','Oktober','November','Dezember'],
					monthNamesShort: ['Jan','Feb','M&auml;r','Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Dez'],
					monthStatus: 'anderen Monat anzeigen',
					yearStatus: 'anderes Jahr anzeigen',
					weekHeader: 'Wo',
					weekStatus: 'Woche des Monats',
					dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
					dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
					dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
					dayStatus: 'Setze DD als ersten Wochentag',
					dateStatus: 'W&auml;hle D, M d',
					dateFormat: 'dd.mm.yy',
					firstDay: 1,
					initStatus: 'W&auml;hle ein Datum',
					isRTL: false
				};
				// Ländereinstellung der DatePicker-Komponente auf Deutsch setzen
				$.datepicker.setDefaults($.datepicker.regional['de']);
			});
		
			$(document).ready(function() {
				$('#buchungsdatum').datepicker({ dateFormat: 'dd.mm.yy' });
				$('#betrag').keypress(validateNumber);
			});
		
		//-->
		</script>
		
    </head>

	<body style="margin-top: 0px; margin-left: 0px;">
	
		<table style="border: 1px solid #000000; background-color: #f7f7f7;" width="100%" height="100%">
			<tr>
				<td valign="top" style="padding:20px;">
				
					<h2>Fluggeldkonto bearbeiten</h2>
					
					<div class="helpline">
						Hier hast Du die M&ouml;glichkeit, weitere Buchungen und Zahlungen
						f&uuml;r das nachfolgend ausgew&auml;hlte Mitglied zu erfassen.
					</div>
					
					<br />
					
					<table>
						<tr>
							<td width="170"><h3 class="fluggeldkonto"><i style="font-size: 11pt;">Fluggeldkonto f&uuml;r : </i></h3></td>
							<td width="70"><h3 class="fluggeldkonto"><?php if (isset($params['acb_nr'])) { echo $params['acb_nr']; } ?></h3></td>
							<td><h2 style="font-size: 16pt;"><?php
								if (isset($params['nachname']) && isset($params['vorname'])) {
									echo  sprintf('%s, <small style="font-size: 13pt;">%s</small>', $params['nachname'], $params['vorname']);
								} ?></h2>
							</td>
						</tr>
					</table>

					<hr />
					
					<span style="font: 11pt Verdana, Sans-Serif;">
						<b>Aktueller Saldo:&nbsp;&nbsp;&nbsp;
							<i style="color: <?php if (isset($params['bgColorSaldo'])) { echo $params['bgColorSaldo']; } ?>;">
							<?php if (isset($params['saldo_aktuell'])) { echo number_format($params['saldo_aktuell'], 2, ',', ''); } ?> &euro;</i>
						</b>
					</span>

					<br />
					<br />
					<br />

					<form action="fluggeldkonto_edit.php?acb_nr=<?php if (isset($params['acb_nr'])) { echo $_GET['acb_nr']; } ?>" method="POST">
						<fieldset>
							<legend>Buchung hinzuf&uuml;gen</legend>
							<table class="fluggeldkonto_edit">
								<tr>
									<th align="left">Datum</th>
									<th align="left">Bemerkungen / Buchungstext</th>
									<th align="left">Betrag</th>
								</tr>
								<tr>
									<td><input type="text" id="buchungsdatum" name="buchungsdatum" value="<?php echo date('d.m.Y'); ?>" maxlength="10" /></td>
									<td><input type="text" id="bemerkungen" name="bemerkungen" value="" maxlength="255" /></td>
									<td><input type="text" id="betrag" name="betrag" value="" maxlength="10" /></td>
									<td><input type="submit" id="neue_buchung" name="neue_buchung" value="Buchung hinzuf&uuml;gen" /></td>
									<td><input type="reset" id="cancel_buchung" name="cancel_buchung" value="abbrechen" /></td>
								</tr>
							</table>
						</fieldset>
					</form>
					
					<br />
					
					<table cellspacing="0" class="tabelle_kontoauszug" border="0">
						<tr height="45">
							<th align="left" valign="top" width="90" style="line-height: 150%; color: #ffffff; padding-left: 4px;" bgcolor="#666666">Datum</th>
							<th align="left" valign="top" width="80" style="line-height: 150%; color: #ffffff; padding-left: 4px;" bgcolor="#666666">Flugzeug<br />Typ</th>
							<th align="left" valign="top" width="80" style="line-height: 150%; color: #ffffff; padding-left: 4px;" bgcolor="#666666"><br />Kennz.</th>
							<th align="left" valign="top" width="100" style="line-height: 150%; color: #ffffff; padding-left: 4px;" bgcolor="#666666">Pilot</th>
							<th align="left" valign="top" width="100" style="line-height: 150%; color: #ffffff; padding-left: 4px;" bgcolor="#666666">Co-Pilot</th>
							<th align="left" valign="top" width="50" style="line-height: 150%; color: #ffffff; padding-left: 4px;" bgcolor="#666666">St-<br />Art</th>
							<th align="left" valign="top" width="100" style="line-height: 150%; color: #ffffff; padding-left: 4px;" bgcolor="#666666">Startort<br />Zahlungen</th>
							<th align="left" valign="top" width="100" style="line-height: 150%; color: #ffffff; padding-left: 4px;" bgcolor="#666666">Landeort</th>
							<th align="left" valign="top" width="55" style="line-height: 150%; color: #ffffff; padding-left: 4px;" bgcolor="#666666">St-Zeit</th>
							<th align="left" valign="top" width="55" style="line-height: 150%; color: #ffffff; padding-left: 4px;" bgcolor="#666666">Ld-Zeit</th>
							<th align="left" valign="top" width="45" style="line-height: 150%; color: #ffffff; padding-left: 4px;" bgcolor="#666666">Dauer</th>
							<th align="left" valign="top" width="15" style="line-height: 150%; color: #ffffff; padding-left: 4px;" bgcolor="#666666">Abr-<br />Art</th>
							<th align="right" valign="top" width="100" style="line-height: 150%; color: #ffffff; padding-right: 4px;" bgcolor="#666666">Betrag</th>
							<th align="right" valign="top" width="100" style="line-height: 150%; color: #ffffff; padding-right: 4px;" bgcolor="#666666">letzt. Saldo</th>
						</tr>
						<tr bgcolor="#ffffc0" height="22">
							<td align="left" colspan="6"><i><?php echo date('d.m.Y'); ?></i></td>
							<td align="left" colspan="7"><i>&rarr; Saldo aktuell zum <?php echo date('d.m.Y'); ?></i></td>
							<td align="right" style="color:<?php if (isset($params['bgColorSaldo'])) { echo $params['bgColorSaldo']; } ?>;">
								<i><?php if (isset($params['saldo_aktuell'])) { echo number_format($params['saldo_aktuell'], 2, ',', ''); } ?></i>
							</td>
						</tr>
						<?php if (isset($fluggeldkonto_tabelle)) { echo $fluggeldkonto_tabelle; } ?>
					</table>
					
					<br />
					<br />

					<div style="margin-left: 10px;">
						<a href="fluggeldkonten.php" class="zur_uebersicht"><img src="./img/top.gif" align="left" /> <span style="position: relative;top: -1px; left: 2px;">zur&uuml;ck zur &Uuml;bersicht</span></a>
					</div>

				</td>
			</tr>
		</table>

	</body>

</html>
<!-- ENDE: AUSGABE -->