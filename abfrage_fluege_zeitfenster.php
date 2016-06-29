<!-- BEGINN: SKRIPT -->
<?php

	/*
	 * getErrorMessage()
	 *
	 * eine Fehlermeldung wird dem Anwender zurückgegeben,
	 * falls der Anwender kein gültiges Datum eingegeben hat
	 *
	 * @return string $html
	 */
	if (!function_exists('getErrorMessage')) {
		function getErrorMessage()
		{
			// wird ein ungültiges Datum oder sogar gar keines eingegeben,
			// kommt eine entsprechende Fehlermeldung zum Vorschein!
			$html  = '<div class="errorline">';
			$html .= '<h3>Ein Fehler ist aufgetreten!</h3>';
			$html .= 'Ein von Dir eingegebenes Datum ist entweder leer oder fehlerhaft.<br />';
			$html .= 'Bitte noch einmal versuchen, und diesmal ein richtiges Datum angeben!';
			$html .= '</div><br />';
			
			// Meldung zurückgeben
			return $html;
		}
	}

	/*
	 * getFluegeZeitfenster()
	 *
	 * es werden die Flüge aufgelistet, für welche die
	 * Filtereinstellungen per Parameter übergeben wurden
	 *
	 * @params string $filter_sql
	 * @return string $html
	 */
	if (!function_exists('getFluegeZeitfenster')) {
		function getFluegeZeitfenster($filter_sql)
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
			
			// SQL-Befehl zurechtfuddeln,
			// Flüge für den festgelegten Zeitraum ermitteln
			$sql = sprintf('
				SELECT
					`flugbuch`.`datum`,
					`flugbuch`.`luftfahrzeug`,
					`mitglieder_1`.`nachname` AS `pilot`,
					`mitglieder_2`.`nachname` AS `begleiter`,
					`flugbuch`.`startzeit`,
					`flugbuch`.`landezeit`,
					`flugbuch`.`flugart`,
					`flugbuch`.`fluggebuehren`,
					`flugbuch`.`anteil_1`,
					`flugbuch`.`anteil_2`
				FROM
					`flugbuch`
				LEFT JOIN
					`mitglieder` AS `mitglieder_2` ON `flugbuch`.`besatzung2` LIKE CONCAT("%%", `mitglieder_2`.`ameavia`, "%%")
				LEFT JOIN
					`mitglieder` AS `mitglieder_1` ON `flugbuch`.`besatzung1` LIKE CONCAT("%%", `mitglieder_1`.`ameavia`, "%%")
				WHERE
					%s AND
					`flugbuch`.`geloescht` = "N"
				ORDER BY
					`flugbuch`.`datum` ASC
				',
				$filter_sql
			);

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Zählervariable initialisieren
			$i = 0;
			
			// Flugarten prüfen und entsprechend zuweisen
			$flugart = array(
				 1 => '1',
				 2 => 'Checkflug',      // C : Checkflug (intern)
				 3 => 'F-Schleppflug',  // F : Flugzeug-Schleppflug
				 4 => 'Passagierflug',  // P : Passagierflug
				 5 => '5',
				 6 => 'Werkverkehr',    // V : Werkverkehr
				 7 => '7',
				 8 => 'Schulflug',      // S : Schulflug
				 9 => 'Gewerbeflug',    // G : Gewerblicher Flug
				10 => 'Privatflug',     // N : Privatflug (nichtgewerblich)
				11 => '11',
				12 => '&Uuml;bungsflug' // Ü : Übungsflug (2 jährig)
			);
			
			// es sind Datensätze vorhanden
			if (mysql_num_rows($db_erg) > 0) {
				// Tabellenanfang
				$html .= '<table border="0" cellspacing="1" class="tabelle_fluege_zeitfenster">';
				
				// Tabellenüberschriften
				$html .= '<tr height="22">';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="80">Datum</th>';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="60">Kennz.</th>';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="110">Pilot</th>';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="110">Begleiter</th>';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="50">S-Zeit</th>';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="50">L-Zeit</th>';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="100">Flugart</th>';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="110">Fluggeb&uuml;hren</th>';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="65">Anteil_1</th>';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="65">Anteil_2</th>';
				$html .= '</tr>';

				while ($zeile = mysql_fetch_object($db_erg)) {
					// Hintergrundfarbe für gerade/ungerade Zeilen festlegen
					$bgColor = ($i % 2) ? '#eeeeee' : '#cccccc';
				
					// eine neue Zeile für die Tabelle anlegen inkl. Hintergrundfarbe
					$html .= sprintf('<tr bgcolor="%s" height="22">', $bgColor);
					
					// Datum formatieren von JJJJ-MM-TT nach TT.MM.JJJJ
					$datum = explode('-', $zeile->Datum);
					$zeile->Datum = sprintf('%s.%s.%s', $datum[2], $datum[1], $datum[0]);
					
					// Tabelleninhalte zuweisen
					$html .= sprintf('<td align="left" style="padding-left: 4px;">%s</td>', $zeile->datum);
					$html .= sprintf('<td align="left" style="padding-left: 4px;">%s</td>', $zeile->luftfahrzeug);
					$html .= sprintf('<td align="left" style="padding-left: 4px;">%s</td>', $zeile->pilot);
					$html .= sprintf('<td align="left" style="padding-left: 4px;">%s</td>', $zeile->begleiter);
					$html .= sprintf('<td align="left" style="padding-left: 4px;">%s</td>', substr($zeile->startzeit, 0, 5));
					$html .= sprintf('<td align="left" style="padding-left: 4px;">%s</td>', substr($zeile->landezeit, 0, 5));
					$html .= sprintf('<td align="left" style="padding-left: 4px;">%s</td>', $flugart[$zeile->flugart]);
					$html .= sprintf('<td align="right" style="padding-right: 5px;">%s &euro;</td>', number_format($zeile->fluggebuehren, 2, ',', ''));
					$html .= sprintf('<td align="right" style="padding-right: 5px;">%s &#037;</td>', $zeile->anteil_1);
					$html .= sprintf('<td align="right" style="padding-right: 5px;">%s &#037;</td>', $zeile->anteil_2);

					// Ende der Zeile
					$html .=  '</tr>';
	  
					// Zähler erhöhen
					$i++;
				}
				
				// Tabellenende
				$html .= '</table>';
			}
			
			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe des Fluggeldkontos
			return $html;
		}
	}
	

	
	/**************************************************************************************************************************/
	/* ------------------------------------------ BEGINN : FILTER NACH POST-BEFEHL ------------------------------------------ */
	/**************************************************************************************************************************/
	// es wird geprüft, ob der POST-Befehl ausgeführte wurde und
	// entsprechende Kriterien zum Filtern ausgewählt wurden
	if (isset($_POST)) {
		// Definitionen festlegen
		define ('ANDERES_DATUM', 1);
		define ('ZEITRAUM',      2);
		
		// Style für fehlerhaftes Feld festlegen
		$error_style = 'border: 2px solid #ff0000; background-color: #ffe7e7; color: #ff0000;';
	
		// Variable für die Filter-Parameter anlegen
		$filter_sql = '';
		// Variable für eventuell auftretende Fehler
		$error_msg  = 0;
	
		// prüfen ob die POST-Variable für den Datumsfilter gesetzt ist
		if (isset($_POST['datumsfilter'])) {
			// eingestellten Filter ermitteln
			// Filtern auf heutiges Datum
			if ($_POST['datumsfilter'] == 'Heute') {
				// es wird nach dem heutigen Datum gefiltert
				$filter_sql = sprintf('`datum` = "%s"', date('Y-m-d'));
			} else if ($_POST['datumsfilter'] == 'Gestern') {
				// es wird nach dem gestrigen Datum gefiltert
				$gestern = date('U') - 86400;
				$filter_sql = sprintf('`datum` = "%s"', date('Y-m-d', $gestern));
			} else if ($_POST['datumsfilter'] == 'Anderes_Datum') {
				// nach einem bestimmt Datum filtern, falls dieses gesetzt wurde
				if (isset($_POST['datum_filter']) && !empty($_POST['datum_filter'])) {
					// gültiges Datum ist vorhanden
					$datum_array = explode('.', $_POST['datum_filter']);
					
					// das Datum wird auf die korrekte Länge geprüft
					// d.h. es müssen 3 Werte vorhanden sein, Tag - Monat - Jahr
					if (count($datum_array) == 3) {
						// Datum nun in das Format JJJJ-MM-TT bringen
						$datum = sprintf('%s-%s-%s', $datum_array[2], $datum_array[1], $datum_array[0]);
					}

					// bestimmtes Datum zurückgeben als SQL-Teilstring
					$filter_sql = sprintf('`datum` = "%s"', $datum);
				} else {
					// es wurde kein Datum angegeben
					$error_msg = ANDERES_DATUM;
				}
			} else if ($_POST['datumsfilter'] == 'Zeitraum') {
				// nach von- und bis-Datum filtern, falls diese gesetzt wurden
				if (
					(isset($_POST['datum_von']) && !empty($_POST['datum_von'])) &&
					(isset($_POST['datum_bis']) && !empty($_POST['datum_bis']))
				) {
					// Datumsangaben umbauen, falls nötig
					// Datumsangaben formatieren von TT.MM.JJJJ mach JJJJ-MM-TT
					$von_datum_array = explode('.', $_POST['datum_von']);
					$bis_datum_array = explode('.', $_POST['datum_bis']);
					
					// das Datum wird auf die korrekte Länge geprüft
					// d.h. es müssen 3 Werte vorhanden sein, Tag - Monat - Jahr
					if (count($von_datum_array) == 3) {
						// Datum nun in das Format JJJJ-MM-TT bringen
						$von_datum = sprintf('%s-%s-%s', $von_datum_array[2], $von_datum_array[1], $von_datum_array[0]);
					}

					// das Datum wird auf die korrekte Länge geprüft
					// d.h. es müssen 3 Werte vorhanden sein, Tag - Monat - Jahr
					if (count($bis_datum_array) == 3) {
						// Datum nun in das Format JJJJ-MM-TT bringen
						$bis_datum = sprintf('%s-%s-%s', $bis_datum_array[2], $bis_datum_array[1], $bis_datum_array[0]);
					}
					
					if (isset($von_datum) && isset($bis_datum)) {
						// den Zeitraum zurückgeben als SQL-Teilstring
						$filter_sql = sprintf('`datum` BETWEEN "%s" AND "%s"', $von_datum, $bis_datum);
					} else {
						// es wurde kein oder ein inkorrektes Datum angegeben
						$error_msg = ZEITRAUM;
					}
				} else {
					// es wurde kein Datum angegeben
					$error_msg = ZEITRAUM;
				}
			}
		}
		
		// es wird geprüft, ob bereits im Vorlauf ein Fehler aufgetreten ist		
		if ($error_msg != 0) {
			// Verarbeitung des Fehlers
			// Fehlermeldung falls Anwender ein falsches Datum eingegeben hat
			$html_err = getErrorMessage();
		} else {
			// mal schauen ob nur nach Segelflug gefiltert werden soll
			if (isset($_POST['nur_segelflug'])) {
				// nur nach Segelflug filtern, anhand der Startarten
				$filter_sql .= ' AND (`startart` = 1 OR `startart` = 2)';
			}
		
			// alles gut, dann Rückgabe der Datensätze
			$html = getFluegeZeitfenster($filter_sql);
		}
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

        <title>Abfrage Fl&uuml;ge je Zeitfenster</title>

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
				$('#datum_filter').datepicker({ dateFormat: 'dd.mm.yy' });
				$('#datum_von').datepicker({ dateFormat: 'dd.mm.yy' });
				$('#datum_bis').datepicker({ dateFormat: 'dd.mm.yy' });
				
				$('input:radio').change(function() {
					if ($(this).val() == 'Anderes_Datum') {
						$('#datum_von').val('');
						$('#datum_bis').val('');
					} else if ($(this).val() == 'Zeitraum') {
						$('#datum_filter').val('');
					} else {
						$('#datum_filter').val('');
						$('#datum_von').val('');
						$('#datum_bis').val('');
					}
				});
			});
		
		//-->
		</script>
		
    </head>

	<body style="margin-top: 0px; margin-left: 0px;">
	
		<table style="border: 1px solid #000000; background-color: #f7f7f7;" width="100%" height="100%">
			<tr>
				<td valign="top" style="padding:20px;">
				
					<h2>&Uuml;bersicht Fl&uuml;ge <small>nach Zeitfenster</small></h2>
					
					<div class="helpline">
						Hier hast Du die M&ouml;glichkeit eine &Uuml;bersicht der Fl&uuml;ge nach vorgegebenen
						Filterkriterien anzeigen zu lassen.
						<br />
						Die Ansicht enth&auml;lt die Verteilung der Fluggeb&uuml;hren auf Pilot und 
						Begleiter.
					</div>
					
					<br />

					<form action="abfrage_fluege_zeitfenster.php" method="POST" enctype="multipart/form-data" style="width:500px;">

						<fieldset style="width: 530px;">
							<legend>Fl&uuml;ge nach Zeitfenster filtern</legend>
							
							<table class="tabelle_fluege_zeitfenster" cellspacing="0">
								<tr height="22">
									<td valign="top" width="110"><strong>Flugdatum :</strong></td>
									<td valign="top" width="150">
										<input type="radio" id="datumsfilter" name="datumsfilter" value="Heute" <?php if (isset($_POST['datumsfilter']) && $_POST['datumsfilter'] == 'Heute') { echo 'checked'; }; ?> /> Heute
									</td>
									</td></td>
								</tr>
								<tr height="22">
									<td></td>
									<td valign="top" width="150">
										<input type="radio" id="datumsfilter" name="datumsfilter" value="Gestern" <?php if (isset($_POST['datumsfilter']) && $_POST['datumsfilter'] == 'Gestern') { echo 'checked'; }; ?> /> Gestern
									</td>
									<td></td>
								</tr>
								<tr height="22">
									<td></td>
									<td valign="top" width="150">
										<input type="radio" id="datumsfilter" name="datumsfilter" value="Anderes_Datum" <?php if (isset($_POST['datumsfilter']) && $_POST['datumsfilter'] == 'Anderes_Datum') { echo 'checked'; }; ?> /> Anderes Datum :
									</td>
									<td>
										<input type="text" id="datum_filter" name="datum_filter" style="height: 21px !important; <?php if ($error_msg == ANDERES_DATUM) { echo $error_style; } ?>" value="<?php if (isset($_POST['datum_filter'])) { echo $_POST['datum_filter']; } ?>" maxlength="10" />
									</td>
								</tr>
								<tr height="22">
									<td></td>
									<td>
										<input type="radio" id="datumsfilter" name="datumsfilter" value="Zeitraum" <?php if (isset($_POST['datumsfilter']) && $_POST['datumsfilter'] == 'Zeitraum') { echo 'checked'; }; ?> /> Zeitraum von :
									</td>
									<td>
										<input type="text" id="datum_von" name="datum_von" style="height: 21px !important; <?php if ($error_msg == ZEITRAUM) { echo $error_style; } ?>" value="<?php if (isset($_POST['datum_von'])) { echo $_POST['datum_von']; } ?>" maxlength="10" />
									</td>
									<td style="padding-left: 5px; padding-right: 5px;">bis</td>
									<td>
										<input type="text" id="datum_bis" name="datum_bis" style="height: 21px !important; <?php if ($error_msg == ZEITRAUM) { echo $error_style; } ?>" value="<?php if (isset($_POST['datum_bis'])) { echo $_POST['datum_bis']; } ?>" maxlength="10" />
									</td>
								</tr>
								<tr height="30">
									<td valign="bottom" width="110"><strong>Einstellungen :</strong></td>
									<td valign="bottom" width="150">
										<input type="checkbox" name="nur_segelflug" value="Segelflug" <?php if (isset($_POST['nur_segelflug'])) { echo 'checked'; }; ?> /> nur Segelflug
									</td>
									</td></td>
								</tr>
							</table>
							
							<br />

							<input type="submit" value="Fl&uuml;ge filtern" name="zeitfenster_filter" id="zeitfenster_filter" />

							<br />
						</fieldset>

					</form>
					
					<br />
					
					<?php if (isset($html_err)) { echo $html_err; } ?>

					<?php if (isset($html)) { echo $html; } ?>
					
				</td>
			</tr>
		</table>

	</body>

</html>
<!-- ENDE: AUSGABE -->