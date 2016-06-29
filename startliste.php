<!-- BEGINN: SKRIPT -->
<?php

	// allgemeine Funktionen einbinden
	include_once('./functions.php');
	
	/*
	 * printTabelleKopfdaten()
	 *
	 * es werden die Kopfdaten (wie Wetterangabe etc.) der aktuellen,
	 * per Parameter übergebenen, Tagstartliste ermittelt und dargestellt
	 *
	 * @params date   $datum
	 * @return string $html
	 */
	if (!function_exists('printTabelleKopfdaten')) {
		function printTabelleKopfdaten($datum)
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
			// Kopfdaten der Startliste ermitteln
			$sql = sprintf('
				SELECT
					*
				FROM
					`logbuch`
				WHERE
					`datum` = "%s"
			',
				$datum
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			while ($zeile = mysql_fetch_object($db_erg)) {
				// die Kopfdaten der Startliste eintragen
				$html .= sprintf('<td align="center" style="padding: 0 10px 0 10px;">%s</td>',      fromSqlDatum($zeile->datum));
				$html .= sprintf('<td align="center" style="padding: 0 5px 0 5px;">%s</td>',        $zeile->startrichtung);
				$html .= sprintf('<td align="center" style="padding: 0 5px 0 5px;">%s &deg;</td>',  str_pad($zeile->windrichtung, 3, '0', STR_PAD_LEFT));
				$html .= sprintf('<td align="center" style="padding: 0 5px 0 5px;">%d kt</td>',     $zeile->windstaerke);
				$html .= sprintf('<td align="center" style="padding: 0 5px 0 5px;">%s</td>',        $zeile->bewoelkung);
				$html .= sprintf('<td align="center" style="padding: 0 5px 0 5px;">%d &deg;C</td>', $zeile->temperatur);
				$html .= sprintf('<td align="center" style="padding: 0 5px 0 5px;">%d hPa</td>',    $zeile->luftdruck);
				$html .= sprintf('<td align="center" style="padding: 0 7px 0 7px;">');
				$html .= sprintf('<a href="flt_aendern.php?datum_id=%s" title="Kopfdaten bearbeiten">', $zeile->datum);
				$html .= '<img src="img/document_pencil.png" border="0" height="23" width="22" align="left" /></a>';
				$html .= sprintf('<a href="pdf_startliste.php?von=%s&bis=%s" title="Startliste drucken" target="_blank">', $zeile->datum, $zeile->datum);
				$html .= '<img src="img/pdf_icon.png" border="0" height="22" width="22" align="left" /></a>';
				$html .= sprintf('<a href="pdf_bordbuch.php?von=%s&bis=%s" title="Bordbuch drucken" target="_blank">', $zeile->datum, $zeile->datum);
				$html .= '<img src="img/pdf_icon.png" border="0" height="22" width="22" align="left" /></a>';
				$html .= '</td>';
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Tabellenansicht zurückgeben
			return $html;
		}
	}
	
	/*
	 * printStartliste()
	 *
	 * die ausgewählte Tagstartliste ermittelt und dargestellt
	 *
	 * @params date   $datum
	 * @return string $html
	 */
	if (!function_exists('printStartliste')) {
		function printStartliste($datum)
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
			
			// aktuelles Jahr aus übergebenen Parameter ermitteln
			$jahr = intval(substr($datum, 0, 4));

			// prüfen um welches Jahr es sich handelt um entsprechend den SQL-Befehl anzupassen
			if ($jahr < 2014) {
				// SQL-Befehl zurechtfuddeln,
				// Startliste für den festgelegten Zeitraum ermitteln
				$sql = sprintf('
					SELECT
						`hauptflugbuch`.`id` AS `id`,
						`hauptflugbuch`.`typ` AS `typ`,
						`hauptflugbuch`.`kennzeichen` AS `kennzeichen`,
						`hauptflugbuch`.`pilotname` AS `pilot`,
						`hauptflugbuch`.`begleitername` AS `begleiter`,
						`hauptflugbuch`.`startort` AS `startort`,
						`hauptflugbuch`.`landeort` AS `landeort`,
						TIME_FORMAT(`hauptflugbuch`.`startzeit`, "%%H:%%i") AS `startzeit`,
						TIME_FORMAT(`hauptflugbuch`.`landezeit`, "%%H:%%i") AS `landezeit`,
						TIME_FORMAT(`hauptflugbuch`.`flugzeit`, "%%k:%%i") AS `flugzeit`,
						`hauptflugbuch`.`motorstart` AS `zaehler1`,
						`hauptflugbuch`.`motorende` AS `zaehler2`,
						`hauptflugbuch`.`einheiten` AS `einheiten`,
						SUBSTRING(`hauptflugbuch`.`startart`, 1, 1) AS `startart`,
						`hauptflugbuch`.`bemerkungen` AS `bemerkungen`, (
							CASE WHEN
								`hauptflugbuch`.`startzeit` = "00:00:00"
							THEN
								`hauptflugbuch`.`landezeit`
							ELSE
								`hauptflugbuch`.`startzeit`
							END
						) AS `sort`
					FROM
						`hauptflugbuch`
					WHERE
						`hauptflugbuch`.`datum` = "%s" AND
						`hauptflugbuch`.`geloescht` = "N"
					ORDER BY
						`sort` ASC,
						`startart` ASC
				',
					$datum
				);
			} else {
				// SQL-Befehl zurechtfuddeln,
				// Startliste für den festgelegten Zeitraum ermitteln
				$sql = sprintf('
					SELECT
						`flugbuch`.`id` AS `id`,
						`flugzeuge`.`flugzeugtyp` AS `typ`,
						`flugbuch`.`luftfahrzeug` AS `kennzeichen`,
						CASE
							WHEN (`mitglieder_1`.`nachname` IS NOT NULL) THEN
								`mitglieder_1`.`nachname`
							WHEN ((`flugbuch`.`besatzung1` IS NULL) OR (`flugbuch`.`besatzung1` = "")) THEN
								"Fremdpilot"
							ELSE
								`flugbuch`.`besatzung1`
						END AS `pilot`,
						CASE
							WHEN ((`flugbuch`.`preiskategorie` = "FR" OR `flugbuch`.`preiskategorie` = "FM") AND (`flugbuch`.`besatzung2` IS NOT NULL)) THEN (
								SELECT
									CONCAT("F-Schl.", REPLACE(`t`.`luftfahrzeug`, "-", ""))
								FROM
									`flugbuch` AS `t`
								WHERE
									`t`.`datum` = `flugbuch`.`datum` AND
									(
										`t`.`startzeit` = `flugbuch`.`startzeit` OR (
											`t`.`startzeit` >= CONCAT(
												HOUR(`flugbuch`.`startzeit`), ":",
												MINUTE(`flugbuch`.`startzeit`) - 2, ":",
												SECOND(`flugbuch`.`startzeit`)
											)
											AND
											`t`.`startzeit` <= CONCAT(
												HOUR(`flugbuch`.`startzeit`), ":",
												MINUTE(`flugbuch`.`startzeit`) + 2, ":",
												SECOND(`flugbuch`.`startzeit`)
											)
										)
									) AND `t`.`startart` = 2
								LIMIT 1
							)
							WHEN ((`flugbuch`.`preiskategorie` = "FR") AND (`mitglieder_2`.`nachname` IS NULL)) THEN
								"F-Schlepp"
							WHEN ((`mitglieder_2`.`nachname` IS NULL) AND (`flugbuch`.`besatzung2` IS NOT NULL)) THEN
								`flugbuch`.`besatzung2`
							ELSE
								`mitglieder_2`.`nachname`
						END AS `begleiter`,
						CASE
							WHEN (`flugplaetze_1`.`name` IS NULL) THEN
								`flugbuch`.`startort`
							ELSE
								`flugplaetze_1`.`name`
						END AS `startort`,
						CASE
							WHEN (`flugplaetze_2`.`name` IS NULL) THEN
								`flugbuch`.`landeort`
							ELSE
								`flugplaetze_2`.`name`
						END AS `landeort`,
						TIME_FORMAT(`flugbuch`.`startzeit`, "%%H:%%i") AS `startzeit`,
						TIME_FORMAT(`flugbuch`.`landezeit`, "%%H:%%i") AS `landezeit`,
						TIME_FORMAT(SEC_TO_TIME(`flugbuch`.`flugzeit` * 60), "%%k:%%i") AS `flugzeit`,
						`flugbuch`.`motorstart` AS `zaehler1`,
						`flugbuch`.`motorende` AS `zaehler2`,
						`flugbuch`.`einheiten` AS `einheiten`,
						`startarten`.`kbez` AS `startart`,
						`flugbuch`.`bemerkungen` AS `bemerkungen`, (
							CASE WHEN
								`flugbuch`.`startzeit` = "00:00:00"
							THEN
								`flugbuch`.`landezeit`
							ELSE
								`flugbuch`.`startzeit`
							END
						) AS `sort`
					FROM
						`flugbuch`
					INNER JOIN
						`flugzeuge` ON `flugbuch`.`luftfahrzeug` = `flugzeuge`.`kennzeichen`
					LEFT JOIN
						`mitglieder` AS `mitglieder_1` ON `flugbuch`.`besatzung1` LIKE CONCAT("%%", `mitglieder_1`.`ameavia`, "%%")
					LEFT JOIN
						`mitglieder` AS `mitglieder_2` ON `flugbuch`.`besatzung2` LIKE CONCAT("%%", `mitglieder_2`.`ameavia`, "%%")
					LEFT JOIN
						`flugplaetze` AS `flugplaetze_1` ON `flugbuch`.`startort` = `flugplaetze_1`.`ameavia`
					LEFT JOIN
						`flugplaetze` AS `flugplaetze_2` ON `flugbuch`.`landeort` = `flugplaetze_2`.`ameavia`
					INNER JOIN
						`startarten` ON `flugbuch`.`startart` = `startarten`.`id`
					WHERE
						`flugbuch`.`datum` = "%s" AND
						`flugbuch`.`geloescht` = "N"
					ORDER BY
						`sort` ASC,
						`startart` ASC
				',
					$datum
				);
			}
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// Zählervariable initialisieren
			$i = 0;
			
			while ($zeile = mysql_fetch_object($db_erg)) {
				// Hintergrundfarbe jeder Zeile abwechseln gestalten
				$bgColor = ($i % 2) ? '#eeeeee' : '#cccccc';
				
				// neue Zeile anlegen
				$html .= sprintf('<tr height="20" bgcolor="%s" id="ID_%d" style="background-color: %s">', $bgColor, $i + 1, $bgColor);

				// die einzelnen Spalten der Startliste schreiben
				$html .= sprintf('<td align="right" style="padding-right: 5px;">%d</td>',        $i + 1);
				$html .= sprintf('<td align="left" style="padding: 2px 5px 2px 5px;">%s</td>',   $zeile->typ);
				$html .= sprintf('<td align="left" style="padding: 2px 5px 2px 5px;">%s</td>',   $zeile->kennzeichen);
				$html .= sprintf('<td align="left" style="padding: 2px 5px 2px 5px;">%s</td>',   $zeile->pilot);
				$html .= sprintf('<td align="left" style="padding: 2px 5px 2px 5px;">%s</td>',   $zeile->begleiter);
				$html .= sprintf('<td align="left" style="padding: 2px 5px 2px 5px;">%s</td>',   $zeile->startort);
				$html .= sprintf('<td align="left" style="padding: 2px 5px 2px 5px;">%s</td>',   $zeile->landeort);
				$html .= sprintf('<td align="center" style="padding: 2px 5px 2px 5px;">%s</td>', $zeile->startzeit);
				$html .= sprintf('<td align="center" style="padding: 2px 5px 2px 5px;">%s</td>', $zeile->landezeit);
				$html .= sprintf('<td align="center" style="padding: 2px 5px 2px 5px;">%s</td>', $zeile->flugzeit);
				$html .= sprintf('<td align="center" style="padding: 2px 5px 2px 5px;">%s</td>', $zeile->zaehler1);
				$html .= sprintf('<td align="center" style="padding: 2px 5px 2px 5px;">%s</td>', $zeile->zaehler2);
				$html .= sprintf('<td align="center" style="padding: 2px 5px 2px 5px;">%s</td>', $zeile->einheiten);
				$html .= sprintf('<td align="center" style="padding: 2px 5px 2px 5px;">%s</td>', $zeile->startart);
				$html .= sprintf('<td align="left" style="padding: 2px 5px 2px 5px;">%s</td>',   $zeile->bemerkungen);
				$html .= sprintf('<td align="center" style="padding: 2px 0 2px 5px; white-space: nowrap; min-width: 45px;" nowrap>');
				$html .= sprintf('<a href="stl_aendern.php?id=%d&datum_id=%s" title="Datensatz bearbeiten">', $zeile->id, $datum);
				$html .= '<img src="img/edit_icon.gif" border="0" height="20" width="17" align="left" /></a>';
				$html .= sprintf('<a onClick="flug_loeschen(\'%d\', \'%d\', \'%d\');" style="cursor: pointer;" title="Datensatz l&ouml;schen">', $i + 1, $zeile->id, substr($datum, 0, 4));
				$html .= '<img src="img/delmsg.png" border="0" height="18" width="18" align="left" /></a>';
				$html .= '</td>';
				
				// Zeilenende
				$html .= '</tr>';
				
				// Zähler erhöhen
				$i++;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Tabellenansicht zurückgeben
			return $html;
		}
	}



	/**************************************************************************************************************************/
	/* --------------------------------------- BEGINN : KALENDER LADEN NACH GET-BEFEHL -------------------------------------- */
	/**************************************************************************************************************************/
	
	if (isset($_GET['datum_id'])) {
		// prüfen ob ein Datum gesetzt wurde
		$datum_id = $_GET['datum_id'];
	} else {
		// ein leeres Datum
		$datum_id = '';
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

        <title>Startliste</title>

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
		
			function flug_loeschen(row_id, flug_id, jahr)
			{
				var dlgBreite = 400;
				var dlgHoehe  = 250;
			
				var x = (window.innerWidth  / 2) - dlgBreite;
				var y = (window.innerHeight / 2) - dlgHoehe;

				$('<div id="dialog-confirm" title="Ausgew&auml;hlten Datensatz wirklich l&ouml;schen?">' +
					'<img src="./img/QuestionIcon.jpg" align="left" height="64" style="margin-right: 10px;" />' +
					'<p style="font-size: 9pt !important; line-height: 150% !important;">' +
					'Bist Du sicher, dass der ausgew&auml;hlte<br />Flug wirklich gel&ouml;scht werden soll?</p></div>'
				).dialog( {
					modal: true,
					resizable: false,
					width: dlgBreite,
					height: dlgHoehe,
					position: [x, y],
					buttons: {
						'Ja' : function() {
							// Meldungsfenster wieder schließen
							$(this).dialog('close');
							
							// AJAX ausführen
							if (navigator.appName == "Microsoft Internet Explorer") {
								xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
							} else {
								xmlHttp = new XMLHttpRequest();
							}
						
							xmlHttp.open('get', 'ajax_flug_loeschen.php?id=' + flug_id + '&jahr=' + jahr);

							xmlHttp.onreadystatechange = function()
							{
								if (xmlHttp.readyState == 4) {
									var bgColor_alt = $('#ID_' + row_id).css('background-color');
									var next_obj = $('#ID_' + row_id).next();
									
									while (next_obj.css('background-color') != undefined) {
										// Hintergrundfarben der Zeilen neu festlegen,
										// wenn eine Zeile zwischendrin gelöscht wurde
										var bgColor_neu = next_obj.css('background-color');
										
										// Hintergrundfarbe der Zeilen tauschen
										next_obj.css('background-color', bgColor_alt);
										bgColor_alt = bgColor_neu;
										
										// nächste Zeile der Tabelle holen
										next_obj = next_obj.next();
									}
									
									// Zeile aus der Tabelle entfernen
									$('#ID_' + row_id).remove();
								}
							}

							xmlHttp.send(null);
						},
						'Nein' : function() {
							// Meldungsfenster wieder schließen
							$(this).dialog('close');
						}
					}
				});
			}
		
		//-->
		</script>
		
    </head>

	<body style="margin-top: 0px; margin-left: 0px;">
	
		<table style="border: 1px solid #000000; background-color: #f7f7f7;" width="100%" height="100%">
			<tr>
				<td valign="top" style="padding:20px;">
				
					<h2>Startliste <small>vom <?php echo fromSqlDatum($datum_id); ?></small></h2>
					
					<div class="helpline">
						 In der Startkladde sind alle Angaben zu den Fl&uuml;gen enthalten.
						 <br />
						 <br />
						 Die einzelnen Fl&uuml;ge k&ouml;nnen um Angaben wie Kennzeichen, Startart,
						 Pilot, Begleiter, Start-/Landezeit, Start-/Landeort, Anzahl Landungen, Stecke
						 und Motorlaufzeit erg&auml;nzt werden.
					</div>
					
					<br />

					<hr width="85%" align="left" />

					<table border="0" cellspacing="1" cellpadding="0" class="tabelle_logbuch">
						<tr bgcolor="#666666" style="color: #ffffff;" height="26">
							<th align="center">Datum</th>
							<th align="center">Startrichtung</th>
							<th align="center">Windrichtung</th>
							<th align="center">Windst&auml;rke</th>
							<th align="center">Bew&ouml;lkung</th>
							<th align="center">Temperatur</th>
							<th align="center">Luftdruck</th>
							<th align="center">Aktion</th>
						</tr>
						<tr bgcolor="#dddddd" height="26">
							<?php echo printTabelleKopfdaten($datum_id); ?>
						</tr>
					</table>
					
					<hr width="85%" align="left" />
					<br />
					
					<table border="0" cellspacing="1" cellpadding="0" class="tabelle_startliste" width="85%">
						<tr bgcolor="#666666" style="color: #ffffff;" height="22">
							<th align="center">Nr</th>
							<th align="center">Muster</th>
							<th align="center">Kennung</th>
							<th align="center">Pilot</th>
							<th align="center">Begleiter</th>
							<th align="center">Von</th>
							<th align="center">Nach</th>
							<th align="center">Start</th>
							<th align="center">Landung</th>
							<th align="center">Dauer</th>
							<th align="center">Z&auml;hler1</th>
							<th align="center">Z&auml;hler2</th>
							<th align="center">Einh.</th>
							<th align="center">Art</th>
							<th align="center">Bemerkung</th>
							<th align="center">Aktion</th>
						</tr>
						<?php echo printStartliste($datum_id); ?>
					</table>
					
					<br />
					<br />

					<div style="margin-left: 10px;">
						<a href="hauptflugbuch.php?goto=<?php echo substr($datum_id, 0, 4); ?>" class="zur_uebersicht"><img src="./img/top.gif" align="left" /> <span style="position: relative;top: -1px; left: 2px;">zur&uuml;ck zur &Uuml;bersicht</span></a>
					</div>
					
				</td>
			</tr>
		</table>

	</body>

</html>
<!-- ENDE: AUSGABE -->