<!-- BEGINN: SKRIPT -->
<?php
	
	// allgemeine Funktionen einbinden
	include_once('./functions.php');
	
	/*
	 * getTabelleRechnungen()
	 *
	 * gibt die Tabelle aller erstellten Rechnungen zurück
	 *
	 * @return string $html
	 */
	if (!function_exists('getTabelleRechnungen')) {
		function getTabelleRechnungen()
		{
			// Rückgabe-Variable definieren
			$html = '';
			
			// Array zum Speichern der Zeile erstellen
			$row = array();
			
			// alle Dateien in einem Verzeichnis auslesen
			$hd_jahr = opendir('abrech');
			
			while ($dir_jahr = readdir($hd_jahr)) {
				// oft werden auch die Standardordner "." und ".."
				// ausgelesen, diese sollen ignoriert werden
				if (($dir_jahr != '.') && ($dir_jahr != '..')) {
					// nächstes Verzeichnis auslesen
					$hd_monat = opendir(sprintf('abrech/%s', $dir_jahr));
				
					while ($dir_monat = readdir($hd_monat)) {
						// oft werden auch die Standardordner "." und ".."
						// ausgelesen, diese sollen ignoriert werden
						if (($dir_monat != '.') && ($dir_monat != '..')) {
							// eine neue Zeile für die Tabelle anlegen inkl. Hintergrundfarbe
							$html = sprintf('<tr bgcolor="#farbe" height="22">');
						
							// Erstellungsdatum der Datei ermitteln
							$filetime = sprintf(
								'./abrech/%s/%s/Abrechn_%s%s_Rev_01.pdf',
								$dir_jahr, $dir_monat, $dir_jahr, $dir_monat
							);
							
							// prüfen, ob die Datei vorhanden ist
							if (file_exists($filetime)) {
								$filetime = sprintf(
									'erstellt am %s <small>um %s Uhr</small>',
									date('d.m.Y', filemtime($filetime)),
									date('H:i:s', filemtime($filetime))
								);
							} else {
								// Erstellungsdatum der zweiten Datei ermitteln
								$filetime = sprintf(
									'./abrech/%s/%s/Abrechn_%s%s_Rev_02.pdf',
									$dir_jahr, $dir_monat, $dir_jahr, $dir_monat
								);
								
								// prüfen, ob die zweite Datei vorhanden ist
								if (file_exists($filetime)) {
									$filetime = sprintf(
										'erstellt am %s <small>um %s Uhr</small>',
										date('d.m.Y', filemtime($filetime)),
										date('H:i:s', filemtime($filetime))
									);
								} else {
									// Datei ist nicht vorhanden
									$filetime = '';
								}
							}

							// den Zeitraum des ausgewählten Monats ermitteln
							$zeitraum = sprintf(
								'01.%s.%s - %s.%s.%s',
								$dir_monat, $dir_jahr,
								date('t', strtotime(
									sprintf('%s-%s-01', $dir_jahr, $dir_monat)
								)), $dir_monat, $dir_jahr
							);
							
							$erste_datei  = sprintf('./abrech/%s/%s/Abrechn_%s%s_Rev_01.pdf', $dir_jahr, $dir_monat, $dir_jahr, $dir_monat);
							$zweite_datei = sprintf('./abrech/%s/%s/Abrechn_%s%s_Rev_02.pdf', $dir_jahr, $dir_monat, $dir_jahr, $dir_monat);
						
							$html .= sprintf('<td align="left">');
							
							if (file_exists($erste_datei)) {
								$html .= sprintf('<a href="%s" target="_blank">', $erste_datei);
								$html .= sprintf('<img src="./img/pdf_icon.png" height="22" width="22" title="Abrechnung nach AmeAvia" hspace="3" />');
								$html .= sprintf('</a>');
							}
							
							if (file_exists($zweite_datei)) {
								$html .= sprintf('<a href="%s" target="_blank">', $zweite_datei);
								$html .= sprintf('<img src="./img/pdf_icon_blue.png" height="22" width="22" title="Abrechnung nach altem Schema" hspace="3" />');
								$html .= sprintf('</a>');
							}

							$html .= sprintf('</td>');
							$html .= sprintf('<td style="padding-left:5px;">%s / %s</td>', $dir_jahr, $dir_monat);
							$html .= sprintf('<td style="padding-left:5px;">%s</td>', $zeitraum);
							$html .= sprintf('<td style="padding-left:5px;">%s</td>', $filetime);

							// Ende der Zeile
							$html .=  '</tr>';
							
							// Tabellenzeile in Array hinzufügen
							$row[] = $html;
						}
					}
					
					// MONATE = Verzeichnis schließen
					closedir($hd_monat);
				}
			}

			// JAHRE = Verzeichnis schließen
			closedir($hd_jahr);

			// Tabellenzeilen absteigend sortieren
			rsort($row);
			// Rückgabevariable zurücksetzen
			$html = '';
			
			// Zählervariable initialisieren
			$i = 0;

			// Rückgabevariable mit den Tabellenzeilen füllen
			foreach ($row as $zeile) {
				// Hintergrundfarbe für gerade/ungerade Zeilen festlegen
				$bgColor = ($i % 2) ? '#eeeeee' : '#cccccc';

				// Hintergrundfarbe ersetzen
				$zeile = str_replace('#farbe', $bgColor, $zeile);
				
				// Zeile in Rückgabevariable anhängen
				$html .= $zeile;
				
				// Zähler erhöhen
				$i++;
			}
			
			// Rückgabe der bereits erstellten Rechnungen
			return $html;
		}
	}
	
	
	
	/**************************************************************************************************************************/
	/* ------------------------------------------ BEGINN : FILTER NACH POST-BEFEHL ------------------------------------------ */
	/**************************************************************************************************************************/
	// es wird geprüft, ob der POST-Befehl ausgeführte wurde und
	// entsprechende Kriterien zum Filtern ausgewählt wurden
	if (isset($_POST) && !empty($_POST)) {
		// anzulegende Verzeichnisse
		$dir_jahr  = sprintf('abrech/%s', $_POST['zeitraum_jahr']);
		$dir_monat = sprintf('abrech/%s/%s', $_POST['zeitraum_jahr'], $_POST['zeitraum_monat']);
	
		// JAHR => prüfen, ob das Verzeichnis bereits existiert
		if (!is_dir($dir_jahr)) {
			// Verzeichnis für das Jahr erstellen
			mkdir($dir_jahr, 0777);
		}
		// MONAT => prüfen, ob das Verzeichnis bereits existiert
		if (!is_dir($dir_monat)) {
			// Verzeichnis für das Jahr erstellen
			mkdir($dir_monat, 0777);
		} else {
			// falls es existiert, bitte entleeren
			// Ordner öffnen zur weiteren Bearbeitung
			if ($dh = opendir($dir_monat)) {
				// Schleife, bis alle Dateien im Verzeichnis ausgelesen wurden
				while (($file = readdir($dh)) !== false) {
					// oft werden auch die Standardordner "." und ".."
					// ausgelesen, diese sollen ignoriert werden
					if (($file != '.') && ($file != '..')) {
						// Datei vom Server entfernen
						unlink(sprintf('%s/%s', $dir_monat, $file));
					}
				}
				
				// geöffnetes Verzeichnis wieder schließen
				closedir($dh);
			}
		}

		// Abrechnung erstellen nach AmeAvia
		if (isset($_POST['abrechnung_aus_ameavia'])) { require_once('./pdf_abrechnung_ameavia.php'); }
		// Abrechnung erstellen nach der klassischen Methode
//		if (isset($_POST['abrechnung_aus_standard'])) { require_once('./pdf_abrechnung_gesamt.php'); }

		// den Zeitraum ermitteln
		$monat = $_POST['zeitraum_monat'];
		$jahr  = $_POST['zeitraum_jahr'];
	
		// POST-Variable nach dem PDF-Erstellen wieder zurücksetzen
		unset($_POST);
	} else {
		// den heutigen Monat ermitteln
		$monat = date('n');
		$jahr  = date('Y');
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

        <title>Monatsrechnung</title>

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
				
					<h2>Abrechnung je Monat erstellen</h2>
					
					<div class="helpline">
						Hier hast Du die M&ouml;glichkeit einen Monat und das zugeh&ouml;rige Jahr
						auszuw&auml;hlen und anschlie&szlig;en die Monatsabrechnung zu erstellen.
						<br />
						Sollte bereits eine Abrechnung zu einem gewissen Zeitraum existieren,
						wird diese neu erstellt und die die alte &uuml;berschrieben.
					</div>
					
					<br />

					<form action="monatsrechnung.php" method="POST">

						<fieldset style="width: 530px;">
							<legend>Zeitraum / Rechnungszyklus ausw&auml;hlen</legend>
							
							<table border="0" class="monatsabrechnung">
								<tr>
									<th align="left" width="150" style="padding-left:5px;"><label for="zeitraum_monat">Monat</label></th>
									<th align="left" width="300" style="padding-left:5px;"><label for="zeitraum_jahr">Jahr</label></th>
								</tr>
								<tr>
									<td>
										<select id="zeitraum_monat" name="zeitraum_monat" class="zeitraum_monat_jahr" style="width:150px;">
											<?php echo getListeMonate($monat); ?>
										</select>
									</td>
									<td>
										<select id="zeitraum_jahr" name="zeitraum_jahr" class="zeitraum_monat_jahr" style="width:100px;">
											<?php echo getListeJahre($jahr); ?>
										</select>
									</td>
								</tr>
								<tr height="5"></tr>
								<tr>
									<td colspan="2" valign="top">
										<table class="monatsabrechnung">
											<tr>
												<td><img src="./img/pdf_icon_16x19.png" align="right" /></td>
												<td>
													<input type="checkbox" name="abrechnung_aus_ameavia" value="ameavia" checked="checked" /> Abrechnung aus AmeAvia-Daten <small>(importierte Daten)</small>
												</td>
											</tr>
											<tr>
												<td><img src="./img/file_icon_pdf_blue.gif" align="right" /></td>
												<td>
													<input type="checkbox" name="abrechnung_aus_standard" value="standard" /> Abrechnung aus Standard-Daten <small>(manuell erfasste Daten)</small>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td colspan="2" height="35" valign="bottom">
										<input type="submit" value="Abrechnung erstellen" name="button_monatsabrechnung" id="button_monatsabrechnung" />
									</td>
								</tr>
							</table>
						</fieldset>

					</form>
					
					<br />
					
					<table border="0" cellspacing="1" class="tabelle_fluege_zeitfenster">
						<tr height="22">
							<th align="left" bgcolor="#666666" width="60">&nbsp;</th>
							<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="110">Rechn.-Zyklus</th>
							<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="170">Zeitraum von / bis</th>
							<th align="left" style="color: #ffffff; padding-left: 5px;" bgcolor="#666666" width="275">Bemerkungen</th>
						</tr>
						<?php echo getTabelleRechnungen(); ?>
					</table>

				</td>
			</tr>
		</table>

	</body>

</html>
<!-- ENDE: AUSGABE -->