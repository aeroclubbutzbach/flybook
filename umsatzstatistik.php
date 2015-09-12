<!-- BEGINN: SKRIPT -->
<?php

	// allgemeine Funktionen einbinden
	include_once('./functions.php');
	
	/*
	 * getTabelleUmsatzstatistik()
	 *
	 * gibt die Tabelle der Umsatzstatistik zurück
	 *
	 * @params integer $jahr
	 * @params integer $monat
	 * @return string  $html
	 */
	if (!function_exists('getTabelleUmsatzstatistik')) {
		function getTabelleUmsatzstatistik($jahr, $monat)
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
			// Flüge für den festgelegten Zeitraum ermitteln
			$sql = sprintf('
				SELECT
					`preiskategorien`.`kennzeichen` AS `kennzeichen`,
					`preiskategorien`.`muster` AS `muster`,
					`flugbuch`.`preiskategorie` AS `preiskategorie`,
					`preiskategorien`.`rechnungstext` AS `rechnungstext`,
					`startarten`.`bezeichnung` AS `startart`,
					`preiskategorien`.`flugart` AS `flugart`,
					SUM(`flugbuch`.`anteilsumme_1` + `flugbuch`.`anteilsumme_2`) AS `summe`,
					COUNT(*) AS `fluege`,
					`preiskategorien`.`mwst_satz` AS `mwst_satz`,
					`steuersaetze`.`bemerkungen` AS `bemerkungen`
				FROM
					`flugbuch`
				INNER JOIN
					`preiskategorien`
					ON (`flugbuch`.`luftfahrzeug` = `preiskategorien`.`kennzeichen` AND `flugbuch`.`preiskategorie` = `preiskategorien`.`id`)
				INNER JOIN
					`steuersaetze`
					ON (`preiskategorien`.`mwst_satz` = `steuersaetze`.`steuersatz`)
				INNER JOIN
					`startarten`
					ON (`preiskategorien`.`startart` = `startarten`.`bezeichnung`)
				WHERE
					`flugbuch`.`datum` BETWEEN "%d-%d-01" AND "%d-%d-%d"
				GROUP BY
					`flugbuch`.`luftfahrzeug`,
					`flugbuch`.`preiskategorie`
			',
				$jahr, $monat, $jahr, $monat,
				date('t', strtotime(
					sprintf('%d-%d-01', $jahr, $monat)
				))
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Zählervariable initialisieren
			$i = 0;

			// es sind Datensätze vorhanden
			if (mysql_num_rows($db_erg) > 0) {
				// Array für die Aufsummierung der Umsätze anlegen
				$umsatz = array();
				// Gesamtumsatz aus allen Aufsummierungen
				$umsatz_ges = 0.0;
				
				$html .= '<table width="92%">';
				$html .= '<tr>';

				$html .= sprintf('
					<td width="140">
						<a href="./pdf_umsatzstatistik.php?jahr=%s&monat=%s" class="neuanlageMitglied" style="font-size:10pt !important;" target="_blank">
							<img src="./img/pdf_icon.png" border="0" align="left" width="22" height="22" style="margin-top:-4px; margin-left:15px; margin-right:7px;" />
							Folgende Umsatz-Tabelle als PDF ausgeben
						</a>
					</td>
				',
					$jahr, $monat
				);

				$html .= '</tr>';
				$html .= '</table>';
				$html .= '<br />';
			
				// Tabellenanfang
				$html .= '<table border="0" cellspacing="1" class="tabelle_umsatzstatistik">';
				
				// Tabellenüberschriften
				$html .= '<tr height="45">';
				$html .= sprintf('<th colspan="9" align="left" style="color: #ffffff; padding-left: 10px; letter-spacing: 5px; font-size: 22pt;" bgcolor="#666666">Ums&auml;tze %s / %s</th>', $monat, $jahr);
				$html .= '</tr>';

				// Tabellenüberschriften
				$html .= '<tr height="25">';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="60">Kennz.</th>';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="110">Muster</th>';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="30"></th>';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="210">Preiskategorie / Rechnungstext</th>';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="80">Startart</th>';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="180">Flugart</th>';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="65">Umsatz</th>';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="40">Fl&uuml;ge</th>';
				$html .= '<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="75">MwSt-Satz</th>';
				$html .= '</tr>';

				while ($zeile = mysql_fetch_object($db_erg)) {
					// Hintergrundfarbe für gerade/ungerade Zeilen festlegen
					$bgColor = ($i % 2) ? '#eeeeee' : '#cccccc';
				
					// eine neue Zeile für die Tabelle anlegen inkl. Hintergrundfarbe
					$html .= sprintf('<tr bgcolor="%s" height="22">', $bgColor);
					
					// Tabelleninhalte zuweisen
					$html .= sprintf('<td align="left" style="padding-left: 4px;">%s</td>',          $zeile->kennzeichen);
					$html .= sprintf('<td align="left" style="padding-left: 4px;">%s</td>',          $zeile->muster);
					$html .= sprintf('<td align="left" style="padding-left: 4px;">%s</td>',          $zeile->preiskategorie);
					$html .= sprintf('<td align="left" style="padding-left: 4px;">%s</td>',          $zeile->rechnungstext);
					$html .= sprintf('<td align="left" style="padding-left: 4px;">%s</td>',          $zeile->startart);
					$html .= sprintf('<td align="left" style="padding-left: 4px;">%s</td>',          $zeile->flugart);
					$html .= sprintf('<td align="right" style="padding-right: 4px;">%s &euro;</td>', number_format($zeile->summe, 2, ',', ''));
					$html .= sprintf('<td align="right" style="padding-right: 4px;">%d</td>',        $zeile->fluege);
					$html .= sprintf('<td align="right" style="padding-right: 4px;">%s &#037;</td>', number_format($zeile->mwst_satz, 2, ',', ''));

					// Umsätze aufsummieren
					$umsatz[$zeile->mwst_satz]['mwst']       = $zeile->mwst_satz;
					$umsatz[$zeile->mwst_satz]['bemerkung']  = $zeile->bemerkungen;
					$umsatz[$zeile->mwst_satz]['summe']     += $zeile->summe;
					$umsatz[$zeile->mwst_satz]['fluege']    += $zeile->fluege;
					
					// Gesamtumsätze aufsummieren
					$umsatz_ges += $zeile->summe;
			
					// Ende der Zeile
					$html .=  '</tr>';
	  
					// Zähler erhöhen
					$i++;
				}
				
				// Tabellenende
				$html .= '</table>';
				// Zeilenumbruch
				$html .= '<br /><br />';
				
				// Tabellenanfang
				$html .= '<table border="0" cellspacing="0" class="tabelle_gesamtumsatz">';
				
				// Tabellenüberschriften
				$html .= '<tr height="22">';
				$html .= '<th align="left">MwSt-Satz</th>';
				$html .= '<th align="left">Bemerkungen / Flugarten</th>';
				$html .= '<th align="left">Fl&uuml;ge</th>';
				$html .= '<th align="left">Ges.-Umsatz</th>';
				$html .= '</tr>';
				
				// Array sortieren :-)
				sort($umsatz);
				
				// Umsätze nach MwSt-Prozentsätzen ausgeben
				foreach ($umsatz as $data) {
					// die Bemerkungen/Flugarten formatieren
					$data['bemerkung'] = str_replace(array('[', ']'), array('<b>', '</b>'), $data['bemerkung']);
				
					$html .= '<tr height="26">';
					$html .= sprintf('<td valign="top" align="right" style="width: 90px; padding-right: 40px;">%s &#037;</td>',  number_format($data['mwst'], 2, ',', ''));
					$html .= sprintf('<td valign="top" align="left" style="width: 300px; padding-right: 10px; font-size: 8pt; line-height: 150%%;">%s</td>', $data['bemerkung']);
					$html .= sprintf('<td valign="top" align="right" style="width: 40px; padding-right: 30px;">%d</td>',         $data['fluege']);
					$html .= sprintf('<td valign="top" align="right" style="width: 110px; padding-right: 20px;">%s &euro;</td>', number_format($data['summe'], 2, ',', ''));
					$html .= '</tr>';
				}

				// Gesamtumsatz anzeigen
				$html .= '<tr height="26">';
				$html .= '<td></td>';
				$html .= '<td></td>';
				$html .= '<td></td>';
				$html .= sprintf('<td align="right" style="width: 110px; padding-right: 20px; border-top: 3px double #000000;">%s &euro;</td>', number_format($umsatz_ges, 2, ',', ''));
				$html .= '</tr>';
				
				// Tabellenende
				$html .= '</table>';
				$html .= '<br />';
			}
			
			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Umsatzstatistik
			return $html;
		}
	}
	
	

	/**************************************************************************************************************************/
	/* ------------------------------------------ BEGINN : FILTER NACH POST-BEFEHL ------------------------------------------ */
	/**************************************************************************************************************************/
	// es wird geprüft, ob der POST-Befehl ausgeführte wurde und
	// entsprechende Kriterien zum Filtern ausgewählt wurden
	if (isset($_POST) && !empty($_POST)) {
		// den Zeitraum ermitteln
		$monat = $_POST['monat'];
		$jahr  = $_POST['jahr'];

		// die Tabelle wird ermittelt
		$htmlTabelleUmsatzStatistik = getTabelleUmsatzstatistik($jahr, $monat);
	} else {
		// den heutigen Monat ermitteln
		$monat = date('n');
		$jahr  = date('Y');
	
		// es wird keine Tabelle ausgegeben
		$htmlTabelleUmsatzStatistik = '';
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

        <title>Umsatzstatistik</title>

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
				
					<h2>Ums&auml;tze je Monat auswerten</h2>
					
					<div class="helpline">
						Hier hast Du die M&ouml;glichkeit einen Monat und das zugeh&ouml;rige Jahr
						auszuw&auml;hlen und anschlie&szlig;en die Umsatzstatistik f&uuml;r den
						entsprechenden Zeitraum anzuzeigen.
					</div>
					
					<br />

					<form action="umsatzstatistik.php" method="POST">

						<fieldset style="width: 530px;">
							<legend>Zeitraum / Rechnungszyklus ausw&auml;hlen</legend>
							
							<table border="0" class="monatsabrechnung">
								<tr>
									<th align="left" width="150" style="padding-left:5px;"><label for="monat">Monat</label></th>
									<th align="left" width="110" style="padding-left:5px;"><label for="jahr">Jahr</label></th>
								</tr>
								<tr>
									<td>
										<select id="monat" name="monat" class="zeitraum_monat_jahr" style="width:150px;">
											<?php echo getListeMonate($monat); ?>
										</select>
									</td>
									<td>
										<select id="jahr" name="jahr" class="zeitraum_monat_jahr" style="width:100px;">
											<?php echo getListeJahre($jahr); ?>
										</select>
									</td>
									<td valign="bottom">
										<input type="submit" value="Ums&auml;tze anzeigen" name="button_monatsabrechnung" id="button_monatsabrechnung" />
									</td>
								</tr>
							</table>
						</fieldset>

					</form>
					
					<br />
					
					<?php echo $htmlTabelleUmsatzStatistik; ?>

				</td>
			</tr>
		</table>


	</body>

</html>
<!-- ENDE: AUSGABE -->