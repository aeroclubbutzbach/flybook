<!-- BEGINN: SKRIPT -->
<?php

	/*
	 * getFlugzeugliste()
	 *
	 * gibt die Flugzeugliste anhand des übergebenen Parameters zurück
	 *
	 * @params string  $lfz_art
	 * @params boolean $filter
	 * @return string $html
	 */
	if (!function_exists('getFlugzeugliste')) {
		function getFlugzeugliste($lfz_art, $filter = false)
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
			// alle vorhandenen Flugzeuge werden ermittelt
			$sql = sprintf('
				SELECT
					`flugzeuge`.`kennzeichen` AS `kennzeichen`,
					`flugzeuge`.`flugzeugtyp` AS `flugzeugtyp`,
					`flugzeugtyp`.`bezeichnung` AS `art`,
					`flugzeuge`.`halter` AS `halter`,
					`flugplaetze`.`name` AS `standort`,
					`flugzeugstatus`.`bezeichnung` AS `status`
				FROM
					`flugzeuge`
				INNER JOIN
					`flugzeugtyp` ON `flugzeuge`.`typ1` = `flugzeugtyp`.`id`
				INNER JOIN
					`flugplaetze` ON `flugzeuge`.`standort` = `flugplaetze`.`id`
				INNER JOIN
					`flugzeugstatus` ON `flugzeuge`.`status` = `flugzeugstatus`.`id`
				WHERE
					%s
					`flugzeuge`.`typ1` = "%s" AND
					`flugzeuge`.`in_abrechn` = "J"
				ORDER BY
					`flugzeuge`.`kennzeichen`, `flugzeuge`.`sort` ASC
			',
				($filter == false) ? '`flugzeuge`.`status` <> 99 AND' : '',
				$lfz_art
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// Zählervariable initialisieren
			$i = 0;

			// es sind Datensätze vorhanden
			if (mysql_num_rows($db_erg) > 0) {
				// Tabellenüberschriften
				$html .= '<tr><td colspan="7" height="5"></td></tr>';
				$html .= '<tr>';
				$html .= '<th align="left" width="85" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; padding: 3px;">Kennzeichen</th>';
				$html .= '<th align="left" width="110" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; padding: 3px;">Flugzeugtyp</th>';
				$html .= '<th align="left" width="185" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; padding: 3px;">Art</th>';
				$html .= '<th align="left" width="150" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; padding: 3px;">Halter</th>';
				$html .= '<th align="left" width="110" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; padding: 3px;">Standort</th>';
				$html .= '<th align="left" width="100" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff; padding: 3px;">Status</th>';
				$html .= '<th align="left" width="50" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff;">&nbsp;</th>';
				$html .= '</tr>';
			
				while ($zeile = mysql_fetch_object($db_erg)) {
					// Hintergrundfarbe für gerade/ungerade Zeilen festlegen
					$bgColor = ($i % 2) ? '#eeeeee' : '#cccccc';
					// Vordergrundfarbe für nicht mehr vorhandene Flugzeuge festlegen
					$foreColor = (strpos($zeile->status, 'gel') !== false) ? '#909090' : '#333333';
					$cssClass  = (strpos($zeile->status, 'gel') !== false) ? 'flugzeugAuswahl_02' : 'flugzeugAuswahl_01';
					
					// eine neue Zeile für die Tabelle anlegen inkl. Hintergrundfarbe
					$html .= sprintf('<tr bgcolor="%s" id="o%s" style="color: %s;">', $bgColor, str_replace('-', '', $zeile->kennzeichen), $foreColor);

					$zeile->flugzeugtyp = ($cssClass == 'flugzeugAuswahl_02') ? sprintf('%s', $zeile->flugzeugtyp) : $zeile->flugzeugtyp;
					$zeile->art         = ($cssClass == 'flugzeugAuswahl_02') ? sprintf('%s', $zeile->art)         : $zeile->art;
					$zeile->halter      = ($cssClass == 'flugzeugAuswahl_02') ? sprintf('%s', $zeile->halter)      : $zeile->halter;
					$zeile->standort    = ($cssClass == 'flugzeugAuswahl_02') ? sprintf('%s', $zeile->standort)    : $zeile->standort;
					$zeile->status      = ($cssClass == 'flugzeugAuswahl_02') ? sprintf('%s', $zeile->status)      : $zeile->status;
					
					$html .= sprintf('
						<td valign="middle" align="left" style="padding-left: 7px;">
							<a href="flugzeuge_edit.php?kennzeichen=%s" class="%s">%s</a>
						</td>'
					,
						$zeile->kennzeichen, $cssClass,
						(($cssClass == 'flugzeugAuswahl_02') ? sprintf('%s', $zeile->kennzeichen) : $zeile->kennzeichen)
					);
					
					$html .= sprintf('<td valign="middle" align="left">%s</td>', $zeile->flugzeugtyp);
					$html .= sprintf('<td valign="middle" align="left">%s</td>', $zeile->art);
					$html .= sprintf('<td valign="middle" align="left">%s</td>', $zeile->halter);
					$html .= sprintf('<td valign="middle" align="left">%s</td>', $zeile->standort);
					$html .= sprintf('<td valign="middle" align="left">%s</td>', $zeile->status);
					
					// Felder hinzufügen für Bearbeiten und Löschen
					$html .= sprintf('
						<td valign="top" align="center" nowrap>
							<a href="flugzeuge_edit.php?kennzeichen=%s"><img src="./img/edit_icon.gif" border="0" title="bearbeiten" /></a>
							<a onClick="flugzeug_loeschen(\'%s\');" style="cursor: pointer;"><img src="./img/delmsg.png" border="0" title="l&ouml;schen" height="18" /></a>
						</td>'
					,
						$zeile->kennzeichen, $zeile->kennzeichen
					);
					
					// Ende der Zeile
					$html .=  '</tr>';

					// Zähler erhöhen
					$i++;
				}
			}
			
			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Flugzeuge
			return $html;
		}
	}
	
	
	/**************************************************************************************************************************/
	/* ------------------------------------ BEGINN : FLUGZEUGLISTE LADEN NACH GET-BEFEHL ------------------------------------ */
	/**************************************************************************************************************************/
	// Filter für Flugzeugliste setzen
	if (isset($_GET['filter']) && $_GET['filter'] == true) {
		// Filter gesetzt
		$lfz_filter = true;
	} else {
		// Filter nicht gesetzt
		$lfz_filter = false;
	}
	/**************************************************************************************************************************/
	/* ------------------------------------- ENDE : FLUGZEUGLISTE LADEN NACH GET-BEFEHL ------------------------------------- */
	/**************************************************************************************************************************/

?>
<!-- ENDE: SKRIPT -->
<!-- BEGINN: AUSGABE -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

    <head>

        <title>Flugzeugliste</title>

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
		
			function flugzeug_loeschen(kennzeichen)
			{
				var dlgBreite = 400;
				var dlgHoehe  = 250;
			
				var x = (window.innerWidth  / 2) - dlgBreite;
				var y = (window.innerHeight / 2) - dlgHoehe;

				$('<div id="dialog-confirm" title="Ausgew&auml;hltes Flugzeug wirklich l&ouml;schen?">' +
					'<img src="./img/QuestionIcon.jpg" align="left" height="64" style="margin-right: 10px;" />' +
					'<p style="font-size: 9pt !important; line-height: 150% !important;">' +
					'Bist Du sicher, dass das ausgew&auml;hlte<br />Flugzeug wirklich gel&ouml;scht werden soll?</p></div>'
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
						
							xmlHttp.open('get', 'ajax_flugzeug_loeschen.php?kennzeichen=' + kennzeichen);

							xmlHttp.onreadystatechange = function()
							{
								if (xmlHttp.readyState == 4) {
									var bgColor_alt = $('#o' + kennzeichen.replace(/-/g, '')).css('background-color');
									var next_obj = $('#o' + kennzeichen.replace(/-/g, '')).next();
									
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
									$('#o' + kennzeichen.replace(/-/g, '')).remove();
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
			
			$(document).ready(function() {
				$('#filter').click(function() {
					if ($(this).is(':checked') == true) {
						location.href = 'flugzeuge.php?filter=true';
					} else {
						location.href = 'flugzeuge.php';
					}
				});
			});
		
		//-->
		</script>
	
    </head>

	<body style="margin-top: 0px; margin-left: 0px;">
	
		<a name="oben"></a>
	
		<table style="border: 1px solid #000000; background-color: #f7f7f7;" width="100%" height="100%">
			<tr>
				<td valign="top" style="padding:20px;">
				
					<h2>Flugzeugliste</h2>
					
					<div class="helpline">
						Je Flugzeug k&ouml;nnen Kennzeichen, Bezeichnung, Halter, Eigent&uuml;mer, Flugzeugtyp und Flugzeugpreise
						verwaltet werden. In Verbindung mit einem Reservierungssystem kann definiert werden, ob das Flugzeug am Platz
						stationiert und reservierbar ist.
					</div>
					
					<br />
					
					<div class="anfangsBuchstaben" style="line-height: 150%;">
						<?php if (getFlugzeugliste('S1', $lfz_filter) != '') : ?>
							<a href="#S1" class="flugzeugeLinks">Segelflugzeuge</a>
						<?php else : ?>
							<span class="flugzeugeLinks">Segelflugzeuge</span>
						<?php endif; ?>
					
						<?php if (getFlugzeugliste('S2', $lfz_filter) != '') : ?>
							<a href="#S2" class="flugzeugeLinks">Seg mit Hilfsantrieb</a>
						<?php else : ?>
							<span class="flugzeugeLinks">Seg mit Hilfsantrieb</span>
						<?php endif; ?>
						
						<?php if (getFlugzeugliste('M3', $lfz_filter) != '') : ?>
							<a href="#M3" class="flugzeugeLinks">Motorflugzeuge <small>(bis 600 kg)</small></a>
						<?php else : ?>
							<span class="flugzeugeLinks">Motorflugzeuge <small>(bis 600 kg)</small></span>
						<?php endif; ?>
						
						<?php if (getFlugzeugliste('M2', $lfz_filter) != '') : ?>
							<a href="#M2" class="flugzeugeLinks">Motorflugzeuge <small>(bis 750 kg)</small></a>
						<?php else : ?>
							<span class="flugzeugeLinks">Motorflugzeuge <small>(bis 750 kg)</small></span>
						<?php endif; ?>
						
						<br />
						
						<?php if (getFlugzeugliste('M1', $lfz_filter) != '') : ?>
							<a href="#M1" class="flugzeugeLinks">Motorflugzeuge <small>(bis 2000 kg)</small></a>
						<?php else : ?>
							<span class="flugzeugeLinks">Motorflugzeuge <small>(bis 2000 kg)</small></span>
						<?php endif; ?>
						
						<?php if (getFlugzeugliste('MS', $lfz_filter) != '') : ?>
							<a href="#MS" class="flugzeugeLinks">Motorsegler</a>
						<?php else : ?>
							<span class="flugzeugeLinks">Motorsegler</span>
						<?php endif; ?>
						
						<?php if (getFlugzeugliste('UL', $lfz_filter) != '') : ?>
							<a href="#UL" class="flugzeugeLinks">Ultraleichtflugzeuge</a>
						<?php else : ?>
							<span class="flugzeugeLinks">Ultraleichtflugzeuge</span>
						<?php endif; ?>						
					</div>
					
					<br />
					
					<table cellpadding="1" cellspacing="0" border="0" style="margin-left:5px;" class="flugzeugliste">
						<tr>
							<td><img src="./img/filter.png" border="0" align="left" /></td>
							<td><input type="checkbox" id="filter" name="filter" <?php if (isset($_GET['filter']) && ($_GET['filter'] == true)) { echo 'checked="checked"'; } ?> /></td>
							<td><label for="filter">auch ehemalige Flugzeuge anzeigen</label></td>
							<td width="30"></td>
							<td><img src="./img/mini_plane_add.png" border="0" align="left" height="22" /></td>
							<td><a href="./flugzeuge_edit.php?kennzeichen=neu" class="neuanlageFlugzeug" style="font-size: 10pt !important;">Neues Flugzeug anlegen</a></td>
						</tr>
					</table>
					
					<br />

					<?php if (getFlugzeugliste('S1', $lfz_filter) != '') : ?>
						<table cellpadding="1" cellspacing="0" border="0" class="flugzeugliste">
							<tr>
								<th colspan="7" bgcolor="#ccccff" style="border: 1px solid #8080ff; font-size: 11pt; color: navy; padding: 3px;">
									<a name="S1">Segelflugzeuge</a>
								</th>
							</tr>
							<?php echo getFlugzeugliste('S1', $lfz_filter); ?>
						</table>
						
						<div style="margin-left: 10px; margin-top: 10px;">
							<a href="#oben" class="zur_uebersicht"><img src="./img/top.gif" align="left" /> <span style="position: relative;top: -1px; left: 2px;">zur&uuml;ck nach ganz oben</span></a>
						</div>
						
						<br />
					<?php endif; ?>
					
					<?php if (getFlugzeugliste('S2', $lfz_filter) != '') : ?>
						<table cellpadding="1" cellspacing="0" border="0" class="flugzeugliste">
							<tr>
								<th colspan="7" bgcolor="#ccccff" style="border: 1px solid #8080ff; font-size: 11pt; color: navy; padding: 3px;">
									<a name="S2">Segelflugzeuge mit Hilfsantrieb</a>
								</th>
							</tr>
							<?php echo getFlugzeugliste('S2', $lfz_filter); ?>
						</table>
						
						<div style="margin-left: 10px; margin-top: 10px;">
							<a href="#oben" class="zur_uebersicht"><img src="./img/top.gif" align="left" /> <span style="position: relative;top: -1px; left: 2px;">zur&uuml;ck nach ganz oben</span></a>
						</div>
						
						<br />
					<?php endif; ?>
					
					<?php if (getFlugzeugliste('M3', $lfz_filter) != '') : ?>
						<table cellpadding="1" cellspacing="0" border="0" class="flugzeugliste">
							<tr>
								<th colspan="7" bgcolor="#ccccff" style="border: 1px solid #8080ff; font-size: 11pt; color: navy; padding: 3px;">
									<a name="M3">Motorflugzeuge <small>(bis 600 kg)</small> / LSA</a>
								</th>
							</tr>
							<?php echo getFlugzeugliste('M3', $lfz_filter); ?>
						</table>
						
						<div style="margin-left: 10px; margin-top: 10px;">
							<a href="#oben" class="zur_uebersicht"><img src="./img/top.gif" align="left" /> <span style="position: relative;top: -1px; left: 2px;">zur&uuml;ck nach ganz oben</span></a>
						</div>
						
						<br />
					<?php endif; ?>
					
					<?php if (getFlugzeugliste('M2', $lfz_filter) != '') : ?>
						<table cellpadding="1" cellspacing="0" border="0" class="flugzeugliste">
							<tr>
								<th colspan="7" bgcolor="#ccccff" style="border: 1px solid #8080ff; font-size: 11pt; color: navy; padding: 3px;">
									<a name="M2">Motorflugzeuge <small>(bis 750 kg)</small> / VLA</a>
								</th>
							</tr>
							<?php echo getFlugzeugliste('M2', $lfz_filter); ?>
						</table>
						
						<div style="margin-left: 10px; margin-top: 10px;">
							<a href="#oben" class="zur_uebersicht"><img src="./img/top.gif" align="left" /> <span style="position: relative;top: -1px; left: 2px;">zur&uuml;ck nach ganz oben</span></a>
						</div>
						
						<br />
					<?php endif; ?>
					
					<?php if (getFlugzeugliste('M1', $lfz_filter) != '') : ?>
						<table cellpadding="1" cellspacing="0" border="0" class="flugzeugliste">
							<tr>
								<th colspan="7" bgcolor="#ccccff" style="border: 1px solid #8080ff; font-size: 11pt; color: navy; padding: 3px;">
									<a name="M1">Motorflugzeuge <small>(bis 2000 kg)</small></a>
								</th>
							</tr>
							<?php echo getFlugzeugliste('M1', $lfz_filter); ?>
						</table>
						
						<div style="margin-left: 10px; margin-top: 10px;">
							<a href="#oben" class="zur_uebersicht"><img src="./img/top.gif" align="left" /> <span style="position: relative;top: -1px; left: 2px;">zur&uuml;ck nach ganz oben</span></a>
						</div>
						
						<br />
					<?php endif; ?>
					
					<?php if (getFlugzeugliste('MS', $lfz_filter) != '') : ?>
						<table cellpadding="1" cellspacing="0" border="0" class="flugzeugliste">
							<tr>
								<th colspan="7" bgcolor="#ccccff" style="border: 1px solid #8080ff; font-size: 11pt; color: navy; padding: 3px;">
									<a name="MS">Motorsegler / TMG</a>
								</th>
							</tr>
							<?php echo getFlugzeugliste('MS', $lfz_filter); ?>
						</table>
						
						<div style="margin-left: 10px; margin-top: 10px;">
							<a href="#oben" class="zur_uebersicht"><img src="./img/top.gif" align="left" /> <span style="position: relative;top: -1px; left: 2px;">zur&uuml;ck nach ganz oben</span></a>
						</div>
						
						<br />
					<?php endif; ?>
					
					<?php if (getFlugzeugliste('UL', $lfz_filter) != '') : ?>
						<table cellpadding="1" cellspacing="0" border="0" class="flugzeugliste">
							<tr>
								<th colspan="7" bgcolor="#ccccff" style="border: 1px solid #8080ff; font-size: 11pt; color: navy; padding: 3px;">
									<a name="UL">Ultraleichtflugzeuge</a>
								</th>
							</tr>
							<?php echo getFlugzeugliste('UL', $lfz_filter); ?>
						</table>
						
						<div style="margin-left: 10px; margin-top: 10px;">
							<a href="#oben" class="zur_uebersicht"><img src="./img/top.gif" align="left" /> <span style="position: relative;top: -1px; left: 2px;">zur&uuml;ck nach ganz oben</span></a>
						</div>
						
						<br />
					<?php endif; ?>
				</td>
			</tr>
		</table>

	</body>

</html>
<!-- ENDE: AUSGABE -->