<!-- BEGINN: SKRIPT -->
<?php

	// allgemeine Funktionen einbinden
	include_once('./functions.php');
	
	/*
	 * getInfoMessage()
	 *
	 * eine Infomeldung wird dem Anwender zurückgegeben,
	 * falls keine Datensätze gefunden worden sind
	 *
	 * @return string $html
	 */
	if (!function_exists('getInfoMessage')) {
		function getInfoMessage()
		{
			// wird ein ungültiges Datum oder sogar gar keines eingegeben,
			// kommt eine entsprechende Fehlermeldung zum Vorschein!
			$html  = '<div class="infoline">';
			$html .= '<h3>Keine Datens&auml;tze vorhanden!</h3>';
			$html .= 'F&uuml;r den angegebenen Zeitraum wurden leider keine Datens&auml;tze gefunden!';
			$html .= '</div><br />';
			
			// Meldung zurückgeben
			return $html;
		}
	}

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
	 * getJahresumsaetze()
	 *
	 * ermittelt die Umsätze des ausgewählten Zeitraumes
	 *
	 * @params date   $datum_von
	 * @params date   $datum_bis
	 * @return string $html
	 */
	if (!function_exists('getJahresumsaetze')) {
		function getJahresumsaetze($datum_von, $datum_bis)
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
			// Umsätze für den festgelegten Zeitraum ermitteln
			$sql = sprintf('
				SELECT
					`preiskategorien`.`kennzeichen` AS `kennzeichen`,
					`flugzeuge`.`flugzeugtyp` AS `typ`,
					SUM(`flugbuch`.`anteilsumme_1` + `flugbuch`.`anteilsumme_2`) AS `summe`,
					COUNT(*) AS `fluege`,
					SUM(`flugbuch`.`flugzeit`) AS `flugzeit`,
					`preiskategorien`.`mwst_satz` AS `mwst_satz`
				FROM
					`flugbuch`
				INNER JOIN
					`preiskategorien`
					ON (`flugbuch`.`luftfahrzeug` = `preiskategorien`.`kennzeichen` AND `flugbuch`.`preiskategorie` = `preiskategorien`.`id`)
				INNER JOIN
					`flugzeuge` ON `flugbuch`.`luftfahrzeug` = `flugzeuge`.`kennzeichen`
				WHERE
					`flugbuch`.`datum` BETWEEN "%s" AND "%s"
				GROUP BY
					`flugbuch`.`luftfahrzeug`,
					`preiskategorien`.`mwst_satz`
			',
				toSqlDatum($datum_von),
				toSqlDatum($datum_bis)
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Zählervariable initialisieren
			$i = 0;
			
			// Kennzeichen zwischenspeichern
			$kennzeichen = '';
			
			// Summe aller Flüge zum zwischenspeichern
			$summe = 0;
			
			$minuten = 0;
			$betrag  = 0.0;
			$betrag_netto = 0.0;
			
			// es sind Datensätze vorhanden
			if (mysql_num_rows($db_erg) > 0) {
				$html .= '<table width="92%">';
				$html .= '<tr>';

				$html .= sprintf('
					<td width="140">
						<a href="./pdf_jahresumsaetze.php?von=%s&bis=%s" class="neuanlageMitglied" style="font-size:10pt !important;" target="_blank">
							<img src="./img/pdf_icon.png" border="0" align="left" width="22" height="22" style="margin-top:-4px; margin-left:15px; margin-right:7px;" />
							Die folgenden Jahresums&auml;tze als PDF ausgeben
						</a>
					</td>
				',
					toSqlDatum($datum_von),
					toSqlDatum($datum_bis)
				);

				$html .= '</tr>';
				$html .= '</table>';
				$html .= '<br />';
			
				while ($zeile = mysql_fetch_object($db_erg)) {
					// Hintergrundfarbe für gerade/ungerade Zeilen festlegen
					$bgColor = ($i % 2) ? '#eeeeee' : '#cccccc';
					
					if ($kennzeichen != $zeile->kennzeichen) {
						if ($kennzeichen != '') {
							$html .= '<tr>';
							$html .= '<th colspan="2" style="border-top: 3px solid #000000; padding-right: 6px;" align="right">Gesamtsumme(n) :</th>';
							$html .= '<th style="border-top: 3px solid #000000;">&nbsp;</th>';
							$html .= sprintf('<th align="right" style="border-top: 3px solid #000000; padding-right: 4px;" width="65">%s</th>', $minuten);
							$html .= sprintf('<th align="right" style="border-top: 3px solid #000000; padding-right: 4px;" width="110">%s &euro;</th>', number_format($betrag_netto, 2, ',', '.'));
							$html .= sprintf('<th align="right" style="border-top: 3px solid #000000; padding-right: 4px;" width="110">%s &euro;</th>', number_format($betrag - $betrag_netto, 2, ',', '.'));
							$html .= sprintf('<th align="right" style="border-top: 3px solid #000000; padding-right: 4px;" width="110">%s &euro;</th>', number_format($betrag, 2, ',', '.'));
							$html .= '</tr>';
						
							// Tabellenende der vorherigen Tabelle
							$html .= '</table>';
							$html .= '</br>';
							
							// Summe aller Flüge einblenden
							$html = str_replace('###', $summe, $html);
							$i = 0;
							
							// Hintergrundfarbe für gerade/ungerade Zeilen festlegen
							$bgColor = ($i % 2) ? '#eeeeee' : '#cccccc';
						}
					
						// Tabellenanfang
						$html .= '<table border="0" cellspacing="0" cellpadding="2" class="tabelle_jahresumsaetze">';
						$html .= '<tr>';
						$html .= sprintf('<th bgcolor="#666666" style="color: #ffffff; font-size: 14pt; padding-left: 4px;" width="150" align="left">%s</th>', $zeile->typ);
						$html .= sprintf('<th bgcolor="#666666" style="color: #ffffff; font-size: 14pt; padding-left: 4px;" width="120" align="left">%s</th>', $zeile->kennzeichen);
						$html .= '<th colspan="5" bgcolor="#666666" style="color: #ffffff;">&nbsp;</th>';
						$html .= '</tr>';
						$html .= '<tr height="26">';
						$html .= sprintf('<td colspan="7" bgcolor="#ffffe1" style="padding-left: 8px;"><i>Zusammenfassung f&uuml;r "Typ" = %s (### Detaildatens&auml;tze)</i></td>', $zeile->typ);
						$html .= '</tr>';
						$html .= '<tr height="24">';
						$html .= '<th colspan="2" bgcolor="#666666">&nbsp;</th>';
						$html .= '<th align="right" bgcolor="#666666" style="color: #ffffff; padding-right: 4px;" width="100">MwSt-Satz %</th>';
						$html .= '<th align="right" bgcolor="#666666" style="color: #ffffff; padding-right: 4px;" width="65">Minuten</th>';
						$html .= '<th align="right" bgcolor="#666666" style="color: #ffffff; padding-right: 4px;" width="110">Betrag <small>(netto)</small></th>';
						$html .= '<th align="right" bgcolor="#666666" style="color: #ffffff; padding-right: 4px;" width="110">Betrag <small>(MwSt)</small></th>';
						$html .= '<th align="right" bgcolor="#666666" style="color: #ffffff; padding-right: 4px;" width="110">Betrag <small>(brutto)</small></th>';
						$html .= '</tr>';
						
						$kennzeichen = $zeile->kennzeichen;
						$summe = 0;
						$minuten = 0;
						$betrag  = 0.0;
						$betrag_netto = 0.0;
					}
					
					$netto = sprintf('%01.2f', $zeile->summe * 100 / ($zeile->mwst_satz + 100));
					
					// eine neue Zeile für die Tabelle anlegen inkl. Hintergrundfarbe
					$html .= sprintf('<tr bgcolor="%s" height="22">', $bgColor);
					$html .= '<td>&nbsp;</td>';
					$html .= '<td width="75" align="right" style="padding-right: 6px;"><strong>Summe(n) :</strong></td>';
					$html .= sprintf('<td align="right" style="padding-right: 4px;">%s %%</td>', number_format($zeile->mwst_satz, 2, ',', '.'));
					$html .= sprintf('<td align="right" style="padding-right: 4px;">%s</td>', $zeile->flugzeit);
					$html .= sprintf('<td align="right" style="padding-right: 4px;">%s &euro;</td>', number_format($netto, 2, ',', '.'));
					$html .= sprintf('<td align="right" style="padding-right: 4px;">%s &euro;</td>', number_format($zeile->summe - $netto, 2, ',', '.'));
					$html .= sprintf('<td align="right" style="padding-right: 4px;">%s &euro;</td>', number_format($zeile->summe, 2, ',', '.'));
					$html .= '</tr>';
					
					// Flüge aufsumieren
					$summe   += $zeile->fluege;
					$minuten += $zeile->flugzeit;
					$betrag  += $zeile->summe;
					$betrag_netto += $netto;
					
					// Zähler erhöhen
					$i++;
				}
				
				$html .= '<tr>';
				$html .= '<th colspan="2" style="border-top: 3px solid #000000; padding-right: 6px;" align="right">Gesamtsumme(n) :</th>';
				$html .= '<th style="border-top: 3px solid #000000;">&nbsp;</th>';
				$html .= sprintf('<th align="right" style="border-top: 3px solid #000000; padding-right: 4px;" width="65">%s</th>', $minuten);
				$html .= sprintf('<th align="right" style="border-top: 3px solid #000000; padding-right: 4px;" width="110">%s &euro;</th>', number_format($betrag_netto, 2, ',', '.'));
				$html .= sprintf('<th align="right" style="border-top: 3px solid #000000; padding-right: 4px;" width="110">%s &euro;</th>', number_format($betrag - $betrag_netto, 2, ',', '.'));
				$html .= sprintf('<th align="right" style="border-top: 3px solid #000000; padding-right: 4px;" width="110">%s &euro;</th>', number_format($betrag, 2, ',', '.'));
				$html .= '</tr>';
			
				// Tabellenende der vorherigen Tabelle
				$html .= '</table>';
				$html .= '</br>';
				
				// Summe aller Flüge einblenden
				$html = str_replace('###', $summe, $html);
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Umsätze
			return $html;
		}
	}

	

	/**************************************************************************************************************************/
	/* ------------------------------------------ BEGINN : FILTER NACH POST-BEFEHL ------------------------------------------ */
	/**************************************************************************************************************************/
	// es wird geprüft, ob der POST-Befehl ausgeführte wurde und
	// entsprechende Kriterien zum Filtern ausgewählt wurden
	if (isset($_POST) && !empty($_POST)) {
		// Style für fehlerhaftes Feld festlegen
		$error_style = 'border: 2px solid #ff0000; background-color: #ffe7e7; color: #ff0000;';
	
		// Variable für eventuell auftretende Fehler
		$error_msg = 0;
		
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
			
			if (!isset($von_datum) || !isset($bis_datum)) {
				// es wurde kein oder ein inkorrektes Datum angegeben
				$error_msg = 1;
			} else {
				// Datum zum Prüfen aufdröseln
				$monat = intval($von_datum_array[1]);
				$tag   = intval($von_datum_array[0]);
				$jahr  = intval($von_datum_array[2]);
			
				if (!checkdate($monat, $tag, $jahr)) {
					// Datum ungültig
					$error_msg = 1;
				}
				
				// Datum zum Prüfen aufdröseln
				$monat = intval($bis_datum_array[1]);
				$tag   = intval($bis_datum_array[0]);
				$jahr  = intval($bis_datum_array[2]);
			
				if (!checkdate($monat, $tag, $jahr)) {
					// Datum ungültig
					$error_msg = 1;
				}
			}
		} else {
			// es wurde kein Datum angegeben
			$error_msg = 1;
		}
		
		// es wird geprüft, ob bereits im Vorlauf ein Fehler aufgetreten ist		
		if ($error_msg != 0) {
			// Verarbeitung des Fehlers
			// Fehlermeldung falls Anwender ein falsches Datum eingegeben hat
			$html_err = getErrorMessage();
		} else {
			// alles gut, dann Rückgabe der Datensätze
			$html = getJahresumsaetze($_POST['datum_von'], $_POST['datum_bis']);
			
			// wurden keine Datensätze gefunden,
			// wird eine entsprechende Meldung ausgegeben
			if ($html == '') {
				$html_err = getInfoMessage();
			}
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

        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
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
				$('#datum_von').datepicker({ dateFormat: 'dd.mm.yy' });
				$('#datum_bis').datepicker({ dateFormat: 'dd.mm.yy' });
			});
		
		//-->
		</script>
		
    </head>

	<body style="margin-top: 0px; margin-left: 0px;">
	
		<a name="oben"></a>
	
		<table style="border: 1px solid #000000; background-color: #f7f7f7;" width="100%" height="100%">
			<tr>
				<td valign="top" style="padding:20px;">
				
					<h2>Jahresums&auml;tze</h2>

					<div class="helpline">
						Hier hast Du die M&ouml;glichkeit eine &Uuml;bersicht der Jahresums&auml;tze nach Flugzeugen
						gruppiert anzeigen zu lassen.
					</div>
					
					<br />

					<form action="jahresumsatz.php" method="POST" enctype="multipart/form-data" style="width:500px;">

						<fieldset style="width: 530px;">
							<legend>Ums&auml;tze nach Zeitraum auswerten</legend>
							
							<table class="tabelle_fluege_zeitfenster" cellspacing="0">
								<tr height="22">
									<td>Zeitraum von :</td>
									<td>
										<input type="text" id="datum_von" name="datum_von" style="height: 21px !important; <?php if (isset($error_msg) && ($error_msg != 0)) { echo $error_style; } ?>" value="<?php if (isset($_POST['datum_von'])) { echo $_POST['datum_von']; } else { printf('01.01.%d', date('Y') - 1); } ?>" maxlength="10" />
									</td>
									<td style="padding-left: 5px; padding-right: 5px;">bis</td>
									<td>
										<input type="text" id="datum_bis" name="datum_bis" style="height: 21px !important; <?php if (isset($error_msg) && ($error_msg != 0)) { echo $error_style; } ?>" value="<?php if (isset($_POST['datum_bis'])) { echo $_POST['datum_bis']; } else { printf('31.12.%d', date('Y') - 1); } ?>" maxlength="10" />
									</td>
								</tr>
							</table>
							
							<br />

							<input type="submit" value="Ums&auml;tze anzeigen" name="jahresumsatz" id="jahresumsatz" />

							<br />
						</fieldset>

					</form>
					
					<br />
					
					<?php if (isset($html_err)) { echo $html_err; } ?>

					<?php if (isset($html)) { echo $html; } ?>
					
					<div style="margin-left: 10px; margin-top: 10px;">
						<a href="#oben" class="zur_uebersicht"><img src="./img/top.gif" align="left" /> <span style="position: relative;top: -1px; left: 2px;">zur&uuml;ck nach ganz oben</span></a>
					</div>
					
				</td>
			</tr>
		</table>

	</body>

</html>
<!-- ENDE: AUSGABE -->