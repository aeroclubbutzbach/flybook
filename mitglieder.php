<!-- BEGINN: SKRIPT -->
<?php

	/*
	 * getUserAvatar()
	 *
	 * es wird ein zugehöriges Avatar-Bild geladen, sofern es existiert.
	 * Ist dies nicht der Fall, wird ein Standard-Dummy-Avatar zurückgegeben
	 *
	 * @params integer $acb_nr
	 * @params char    $anrede
	 * @return string  $jpeg
	 */
	if (!function_exists('getUserAvatar')) {
		function getUserAvatar($acb_nr, $anrede)
		{
			// MD5-Hash erzeugen, da die Bilder beim Importieren
			// mit dem MD5-Hash der Mitgliedsnummer benannt werden
			$jpeg = sprintf('%s.jpg', md5($acb_nr));
			
			// prüfen ob ein Avatar mit dem aktuellen Namen exisitert
			if (!file_exists('./userpics/' . $jpeg)) {
				// existiert das Bild nicht, dann wird anhand der
				// als Parameter übergebenen Anrede ein Standard-
				// Avatar für männlich oder weiblich geladen
				$jpeg = ($anrede == 'H') ? '_dummy_pic_male.jpg' : '_dummy_pic_female.jpg';
			}
			
			// das Avatar wird zurückgegeben
			return sprintf('./userpics/%s', $jpeg);
		}
	}
	
	/*
	 * getMitgliedsstatus()
	 *
	 * ermittelt die Bezeichnung des Mitgliedsstatus
	 * anhand der übergebenen ID
	 *
	 * @params char   $status_id
	 * @return string $return
	 */
	if (!function_exists('getMitgliedsstatus')) {
		function getMitgliedsstatus($status_id)
		{
			// Rückgabe-Variable definieren
			$return = '';
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
			// die Bezeichnung des Mitgliedsstatus wird anhand
			// der als Parameter übergebenen ID ermittelt
			$sql = sprintf('
				SELECT
					`mitgliedschaft`.`bezeichnung`
				FROM
					`mitgliedschaft`
				WHERE
					`mitgliedschaft`.`id` = "%s"
				LIMIT 1
			',
				$status_id
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// es sind Datensätze vorhanden
			if ($zeile = mysql_fetch_object($db_erg)) {
				// Rückgabe des entsprechenden Mitgliedsstatus/-gruppe
				$return = $zeile->bezeichnung;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe des Mitgliedsstatus
			return $return;
		}
	}
	
	/*
	 * getMitgliederliste()
	 *
	 * gibt die Mitgliederliste anhand der übergebenen Parameter zurück
	 *
	 * @params array  $params
	 * @return string $html
	 */
	if (!function_exists('getMitgliederliste')) {
		function getMitgliederliste(array $params)
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
			// die aktuellen Mitglieder werden ermittelt
			$sql = sprintf('
				SELECT
					`mitglieder`.*
				FROM
					`mitglieder`
				WHERE
					`mitglieder`.`status` = "%s" AND `mitglieder`.`in_abrechn` = "J"
				ORDER BY
					%s ASC
			',
				$params['Mitgliedsstatus'],
				$params['Sortierung']
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// Zählervariable initialisieren
			$i = 0;

			// es sind Datensätze vorhanden
			if (mysql_num_rows($db_erg) > 0) {
				// Überschrift für den Mitgliedsstatus ermitteln
				$tabellenkopf = getMitgliedsstatus($params['Mitgliedsstatus']);
			
				// erste Zeile festlegen für die entsprechende Mitgliedsgruppe
				$html = sprintf('<tr><th colspan="6" bgcolor="#ccccff" style="border: 1px solid #8080ff; font-size: 11pt; color: navy;"><a name="%s">%s</a></th></tr>', $params['Mitgliedsstatus'], $tabellenkopf);
				
				// Tabellenüberschriften
				$html .= '<tr><td colspan="6" height="5"></td></tr>';
				$html .= '<tr>';
				$html .= '<th width="85" bgcolor="#666666" style="border-bottom: 1px solid #ffffff;">&nbsp;</th>';
				$html .= '<th align="left" width="210" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff;">Name</th>';
				$html .= '<th align="left" width="140" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff;">Funktion</th>';
				$html .= '<th align="left" width="120" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff;">Telefon</th>';
				$html .= '<th align="left" width="250" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff;">eMail</th>';
				$html .= '<th align="left" width="65" bgcolor="#666666" style="color: #ffffff; border-bottom: 1px solid #ffffff;">&nbsp;</th>';
				$html .= '</tr>';
			
				while ($zeile = mysql_fetch_object($db_erg)) {
					// Hintergrundfarbe für gerade/ungerade Zeilen festlegen
					$bgColor = ($i % 2) ? '#eeeeee' : '#cccccc';
					
					// Avatar des aktuellen Mitglieds ermitteln
					$avatar = getUserAvatar($zeile->id, $zeile->anrede);
					
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
					
					// eine neue Zeile für die Tabelle anlegen inkl. Hintergrundfarbe
					$html .= sprintf('<tr bgcolor="%s" id="o%s">', $bgColor, $zeile->id);
					$html .= sprintf('<td valign="top" align="center"><img src="%s" height="100" width="75" vspace="3" style="border: 1px solid #999999;" /></td>', $avatar);
					$html .= sprintf('<td valign="top" align="left"><strong>%s %s</strong><br /><br />%s<br />%s %s</td>', nl2br($zeile->vorname), nl2br($zeile->nachname), $zeile->strasse, $zeile->plz, $zeile->ort);
					$html .= sprintf('<td valign="top" align="left">%s</td>', nl2br($zeile->funktion));
					$html .= sprintf('<td valign="top" align="left">%s<br />%s</td>', nl2br($zeile->telefon1), nl2br($zeile->mobil1));
					
					// es wird geprüft, ob eine gültige eMail-Adresse eingetragen ist
					if (!empty($zeile->email)) {
						// eMail-Adresse zurückgeben
						$html .= sprintf('
							<td valign="top" align="left">
								<a href="mailto:%s" class="mitgliederliste"><img src="./img/email_icon.png" border="0" align="left" /> %s</a>
							</td>', $zeile->email, nl2br($zeile->email)
						);
					} else {
						// keine eMail-Adresse hinterlegt
						$html .= '<td></td>';
					}
					
					// Felder hinzufügen für Bearbeiten, Löschen und PDF-Druck
					$html .= sprintf('
						<td valign="top" align="left" nowrap>
							<a href="mitglieder_edit.php?acb_nr=%s"><img src="./img/edit_icon.gif" border="0" vspace="3" title="bearbeiten" /></a>
							<a onClick="mitglied_loeschen(\'%s\');" style="cursor: pointer;"><img src="./img/delmsg.png" border="0" vspace="3" title="l&ouml;schen" height="18" /></a>
							<a href="pdf_mitglied.php?acb_nr=%s" target="_blank"><img src="./img/pdf_icon_16x19.png" border="0" vspace="3" title="PDF drucken" height="18" /></a>
						</td>'
					,
						$zeile->id, $zeile->id, $zeile->id
					);
					
					// Ende der Zeile
					$html .=  '</tr>';

					// Zähler erhöhen
					$i++;
				}
			}
			
			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Vorstandsmitglieder
			return $html;
		}
	}

	/*
	 * getVorstand()
	 *
	 * gibt den aktuellen Vorstand als HTML-Tabelle zurück
	 *
	 * @return string $html
	 */
	if (!function_exists('getVorstand')) {
		function getVorstand()
		{
			// Parameter zum Ermitteln der Vorstandsmitglieder setzen
			$params = array(
				'Mitgliedsstatus' => 'V',
				'Sortierung' => '`sort`'
			);
		
			// Rückgabe der Vorstandsmitglieder
			return getMitgliederliste($params);
		}
	}
	
	/*
	 * getFluglehrer()
	 *
	 * gibt die aktuellen Fluglehrer als HTML-Tabelle zurück
	 *
	 * @return string $html
	 */
	if (!function_exists('getFluglehrer')) {
		function getFluglehrer()
		{
			// Parameter zum Ermitteln der Fluglehrer setzen
			$params = array(
				'Mitgliedsstatus' => 'L',
				'Sortierung' => '`sort`'
			);
		
			// Rückgabe der Fluglehrer
			return getMitgliederliste($params);
		}
	}
	
	/*
	 * getTechnik()
	 *
	 * gibt das aktuelle Technische Personal als HTML-Tabelle zurück
	 *
	 * @return string $html
	 */
	if (!function_exists('getTechnik')) {
		function getTechnik()
		{
			// Parameter zum Ermitteln der Technik setzen
			$params = array(
				'Mitgliedsstatus' => 'T',
				'Sortierung' => '`sort`'
			);
		
			// Rückgabe des Technischen Personals
			return getMitgliederliste($params);
		}
	}
	
	/*
	 * getFlugschueler()
	 *
	 * gibt die aktuell gemeldeten Flugschüler als HTML-Tabelle zurück
	 *
	 * @return string $html
	 */
	if (!function_exists('getFlugschueler')) {
		function getFlugschueler()
		{
			// Parameter zum Ermitteln der Flugschüler setzen
			$params = array(
				'Mitgliedsstatus' => 'S',
				'Sortierung' => '`nachname`, `vorname`'
			);
		
			// Rückgabe der Flugschüler
			return getMitgliederliste($params);
		}
	}
	
	/*
	 * getAktiveMitglieder()
	 *
	 * gibt die aktiven Mitglieder als HTML-Tabelle zurück
	 *
	 * @return string $html
	 */
	if (!function_exists('getAktiveMitglieder')) {
		function getAktiveMitglieder()
		{
			// Parameter zum Ermitteln der aktiven Mitglieder setzen
			$params = array(
				'Mitgliedsstatus' => 'A',
				'Sortierung' => '`nachname`, `vorname`'
			);
		
			// Rückgabe der aktiven Mitglieder
			return getMitgliederliste($params);
		}
	}
	
	/*
	 * getPassiveMitglieder()
	 *
	 * gibt die passiven Mitglieder als HTML-Tabelle zurück
	 *
	 * @return string $html
	 */
	if (!function_exists('getPassiveMitglieder')) {
		function getPassiveMitglieder()
		{
			// Parameter zum Ermitteln der passiven Mitglieder setzen
			$params = array(
				'Mitgliedsstatus' => 'P',
				'Sortierung' => '`nachname`, `vorname`'
			);
		
			// Rückgabe der passiven Mitglieder
			return getMitgliederliste($params);
		}
	}
	
	/*
	 * getEhrenmitglieder()
	 *
	 * gibt die Ehrenmitglieder als HTML-Tabelle zurück
	 *
	 * @return string $html
	 */
	if (!function_exists('getEhrenmitglieder')) {
		function getEhrenmitglieder()
		{
			// Parameter zum Ermitteln der Ehrenmitglieder setzen
			$params = array(
				'Mitgliedsstatus' => 'E',
				'Sortierung' => '`nachname`, `vorname`'
			);
		
			// Rückgabe der Ehrenmitglieder
			return getMitgliederliste($params);
		}
	}

?>
<!-- ENDE: SKRIPT -->
<!-- BEGINN: AUSGABE -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

    <head>

        <title>Mitgliederliste</title>

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
		
			function mitglied_loeschen(acb_nr)
			{
				var dlgBreite = 400;
				var dlgHoehe  = 250;
			
				var x = (window.innerWidth  / 2) - dlgBreite;
				var y = (window.innerHeight / 2) - dlgHoehe;

				$('<div id="dialog-confirm" title="Ausgew&auml;hltes Mitglied wirklich l&ouml;schen?">' +
					'<img src="./img/QuestionIcon.jpg" align="left" height="64" style="margin-right: 10px;" />' +
					'<p style="font-size: 9pt !important; line-height: 150% !important;">' +
					'Bist Du sicher, dass das ausgew&auml;hlte<br />Mitglied wirklich gel&ouml;scht werden soll?</p></div>'
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
						
							xmlHttp.open('get', 'ajax_mitglied_loeschen.php?acb_nr=' + acb_nr);

							xmlHttp.onreadystatechange = function()
							{
								if (xmlHttp.readyState == 4) {
									var bgColor_alt = $('#o' + acb_nr).css('background-color');
									var next_obj = $('#o' + acb_nr).next();
									
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
									$('#o' + acb_nr).remove();
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
	
		<a name="oben"></a>
	
		<table style="border: 1px solid #000000; background-color: #f7f7f7;" width="100%" height="100%">
			<tr>
				<td valign="top" style="padding:20px;">
				
					<h2>Mitgliederliste</h2>
					
					<div class="helpline">
						Hier kannst Du alle mitgliedsrelevanten Informationen, wie zum Beispiel Vorname,
						Nachname, Stra&szlig;e, PLZ, Ort, Geburtstag, Mailadresse und Telefondaten hinterlegen.
						<br />
						Je Mitglied k&ouml;nnen dar&uuml;ber hinaus ein Foto sowie eine Mitgliedsart/-gruppe
						(Vorstand, Fluglehrer, Technik etc.) zugeordnet werden.
					</div>
					
					<br />
					
					<div class="anfangsBuchstaben">
						<a href="#V" class="mitgliederLinks">Vorstand</a>
						<a href="#L" class="mitgliederLinks">Fluglehrer</a>
						<a href="#T" class="mitgliederLinks">Technik</a>
						<a href="#S" class="mitgliederLinks">Flugsch&uuml;ler</a>
						<a href="#A" class="mitgliederLinks">Aktive Mitglieder</a>
						<a href="#E" class="mitgliederLinks">Ehrenmitglieder</a>
						<a href="#P" class="mitgliederLinks">Passive Mitglieder</a>
					</div>
					
					<br />
					
					<table width="100%">
						<tr>
							<td width="250"><a href="./mitglieder_edit.php?acb_nr=neu" class="neuanlageMitglied"><img src="./img/user_add.png" border="0" align="left" style="margin-top:-6px; margin-left:22px;" /> Neues Mitglied anlegen</a></td>
							<td></td>
							<td width="170"><a href="./ebe_export.php" class="neuanlageMitglied" style="font-size:10pt !important;"><img src="./img/excel_icon_32x32.gif" border="0" align="left" width="22" height="22" style="margin-top:-4px; margin-left:5px; margin-right:7px;" /> eBE (LSBH) Export</a></td>
							<td width="230"><a href="./csv_export.php" class="neuanlageMitglied" style="font-size:10pt !important;"><img src="./img/csv_icon.gif" border="0" align="left" width="22" height="22" style="margin-top:-4px; margin-left:5px; margin-right:7px;" /> eMail-Adressen <small>(Alle Mitglieder)</small> exportieren</a></td>
							<td width="230"><a href="./csv_export_schueler.php" class="neuanlageMitglied" style="font-size:10pt !important;"><img src="./img/csv_icon.gif" border="0" align="left" width="22" height="22" style="margin-top:-4px; margin-left:5px; margin-right:7px;" /> eMail-Adressen <small>(Flugsch&uuml;ler)</small> exportieren</a></td>
							<td width="140"><a href="./pdf_mitgliederliste.php" class="neuanlageMitglied" style="font-size:10pt !important;" target="_blank"><img src="./img/pdf_icon.png" border="0" align="left" width="22" height="22" style="margin-top:-4px; margin-left:5px; margin-right:7px;" /> Mitgliederliste</a></td>
							<td width="140"><a href="./pdf_flugleiter.php" class="neuanlageMitglied" style="font-size:10pt !important;" target="_blank"><img src="./img/pdf_icon.png" border="0" align="left" width="22" height="22" style="margin-top:-4px; margin-left:5px; margin-right:7px;" /> Flugleiterliste</a></td>
						</tr>
					</table>
					
					<br />

					<table cellpadding="3" cellspacing="0" border="0" class="mitgliederliste">
						<?php echo getVorstand(); ?>
					</table>
					
					<div style="margin-left: 10px; margin-top: 10px;">
						<a href="#oben" class="zur_uebersicht"><img src="./img/top.gif" align="left" /> <span style="position: relative;top: -1px; left: 2px;">zur&uuml;ck nach ganz oben</span></a>
					</div>
					
					<br />
					
					<table cellpadding="3" cellspacing="0" border="0" class="mitgliederliste">
						<?php echo getFluglehrer(); ?>
					</table>
					
					<div style="margin-left: 10px; margin-top: 10px;">
						<a href="#oben" class="zur_uebersicht"><img src="./img/top.gif" align="left" /> <span style="position: relative;top: -1px; left: 2px;">zur&uuml;ck nach ganz oben</span></a>
					</div>
					
					<br />
					
					<table cellpadding="3" cellspacing="0" border="0" class="mitgliederliste">
						<?php echo getTechnik(); ?>
					</table>
					
					<div style="margin-left: 10px; margin-top: 10px;">
						<a href="#oben" class="zur_uebersicht"><img src="./img/top.gif" align="left" /> <span style="position: relative;top: -1px; left: 2px;">zur&uuml;ck nach ganz oben</span></a>
					</div>

					<br />
					
					<table cellpadding="3" cellspacing="0" border="0" class="mitgliederliste">
						<?php echo getFlugschueler(); ?>
					</table>
					
					<div style="margin-left: 10px; margin-top: 10px;">
						<a href="#oben" class="zur_uebersicht"><img src="./img/top.gif" align="left" /> <span style="position: relative;top: -1px; left: 2px;">zur&uuml;ck nach ganz oben</span></a>
					</div>

					<br />
					
					<table cellpadding="3" cellspacing="0" border="0" class="mitgliederliste">
						<?php echo getAktiveMitglieder(); ?>
					</table>
					
					<div style="margin-left: 10px; margin-top: 10px;">
						<a href="#oben" class="zur_uebersicht"><img src="./img/top.gif" align="left" /> <span style="position: relative;top: -1px; left: 2px;">zur&uuml;ck nach ganz oben</span></a>
					</div>
					
					<br />
					
					<table cellpadding="3" cellspacing="0" border="0" class="mitgliederliste">
						<?php echo getEhrenmitglieder(); ?>
					</table>
					
					<div style="margin-left: 10px; margin-top: 10px;">
						<a href="#oben" class="zur_uebersicht"><img src="./img/top.gif" align="left" /> <span style="position: relative;top: -1px; left: 2px;">zur&uuml;ck nach ganz oben</span></a>
					</div>
					
					<br />
					
					<table cellpadding="3" cellspacing="0" border="0" class="mitgliederliste">
						<?php echo getPassiveMitglieder(); ?>
					</table>
					
					<div style="margin-left: 10px; margin-top: 10px;">
						<a href="#oben" class="zur_uebersicht"><img src="./img/top.gif" align="left" /> <span style="position: relative;top: -1px; left: 2px;">zur&uuml;ck nach ganz oben</span></a>
					</div>
				</td>
			</tr>
		</table>

	</body>

</html>
<!-- ENDE: AUSGABE -->