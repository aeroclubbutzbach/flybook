<!-- BEGINN: SKRIPT -->
<?php

	// allgemeine Funktionen einbinden
	include_once('./functions.php');

	/*
	 * getInfoMessage()
	 *
	 * eine Infomeldung wird dem Anwender zurückgegeben,
	 * falls alle Abrechnungen per eMail versendet wurden
	 *
	 * @return string $html
	 */
	if (!function_exists('getInfoMessage')) {
		function getInfoMessage()
		{
			// nachdem die Abrechnungen verschickt wurden, wird
			// dem Anwender eine entsprechende Meldung ausgegeben
			$html  = '<div class="infoline">';
			$html .= '<h3>eMails erfolgreich versendet!</h3>';
			$html .= 'Die Abrechnungen wurden erfolgreich an alle Mitglieder versendet!';
			$html .= '</div><br />';
			
			// Meldung zurückgeben
			return $html;
		}
	}
	
	/*
	 * getErrorMessage()
	 *
	 * eine Fehlermeldung wird dem Anwender zurückgegeben,
	 * falls der eMail-Versand nicht ordnungsgemäß verlief
	 *
	 * @return string $html
	 */
	if (!function_exists('getErrorMessage')) {
		function getErrorMessage()
		{
			// wird ein fehlerhafter Wert oder sogar gar keiner eingegeben,
			// kommt eine entsprechende Fehlermeldung zum Vorschein!
			$html  = '<div class="errorline">';
			$html .= '<h3>Ein Fehler ist aufgetreten!</h3>';
			$html .= 'Der Versand der Abrechnungen verlief leider nicht fehlerfrei.<br />';
			$html .= 'Probier es doch einfach noch einmal!';
			$html .= '</div><br />';
			
			// Meldung zurückgeben
			return $html;
		}
	}
	
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
					`opt` = "ABR"
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
	 * getRechnungszyklen()
	 *
	 * ermittelt alle vorhandenen Rechnungszyklen anhand der Dateistruktur
	 * aus und gibt diese als Listenelemente für die Combobox zurück
	 *
	 * @return string $html
	 */
	if (!function_exists('getRechnungszyklen')) {
		function getRechnungszyklen()
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
							
							// Tabellenzeile in Array hinzufügen
							$row[] = sprintf('%s%s', $dir_jahr, $dir_monat);
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
			
			// alle vorhandenen Zyklen in die Combobox-Elemente übertragen
			foreach ($row as $zyklus) {
				$html .= sprintf('<option value="%s">%s</option>', $zyklus, $zyklus);
			}
			
			// Rückgabe der Rechnungszyklen (ComboBox)
			return $html;
		}
	}

	
	
	/**************************************************************************************************************************/
	/* ------------------------------------------ BEGINN : FILTER NACH POST-BEFEHL ------------------------------------------ */
	/**************************************************************************************************************************/
	// es wird geprüft, ob der POST-Befehl ausgeführte wurde und
	// entsprechende Kriterien zum Filtern ausgewählt wurden
	if (isset($_POST) && !empty($_POST)) {
		// Laufzeit des Skriptes setzen
		set_time_limit(1000);
		
		// den Pfad zu den gespeicherten PDF-Abrechnungen ermitteln
		$dir_jahr  = substr($_POST['rechn_zyklus'], 0, 4);
		$dir_monat = substr($_POST['rechn_zyklus'], 4, 2);
		// den Pfad zurechtfuddeln
		$pfad = sprintf('abrech/%s/%s/', $dir_jahr, $dir_monat);

		// Liste der eMailadressen ermitteln
		$email_adressen = getEmailadressen();

		// den Mailtext ermitteln
		$mailtext = $_POST['mailtext'];

		// eMail-Adressen in einem Array verarbeiten
		foreach ($email_adressen as $email) {
			// anhand der gefundenen Mitgliedsnummer,
			// den Namen der zugehörigen PDF-Datei kreieren
			$pdf = sprintf('%s.pdf', md5($email['acb_nr']));

			// prüfen ob für das aktuelle Mitglied eine Abrechnung existiert
			if (file_exists($pfad . $pdf)) {
				// aktuellen Saldo des Mitglieds ermitteln
				$saldo  = getFluggeldkontoSaldo($email['acb_nr'], $dir_monat, $dir_jahr);
				$saldo  = number_format($saldo, 2, ',', '');
				$umsatz = getFluggeldkontoUmsatz($email['acb_nr'], $dir_monat, $dir_jahr);
				$umsatz = number_format($umsatz, 2, ',', '');
			
				// den Namen im Mailtext, mit dem des aktuellen Mitgliedes, ersetzen
				$alt = array('{name}', '{summe}', '{saldo}');
				$neu = array($email['vorname'], $umsatz, $saldo);
				$nachricht = str_replace($alt, $neu, $mailtext);

				// PHP-Mailer Klasse einbinden
				include_once('./phpmailer/class.phpmailer.php');
				
				// neue Instanz des PHPMailer anlegen
				$mail = new PHPMailer();
				// Absender eintragen
				$mail->From     = 'abrechnung@aero-club-butzbach.de';
				$mail->FromName = 'Aero Club Butzbach e.V.';
				// eMail-Adresse des Empfängers hinzufügen
				$mail->AddAddress($email['email']);
				// Betreffzeile definieren
				$mail->Subject = sprintf('Abrechnung aus ameAVIA %s/%s', $dir_monat, $dir_jahr);
				// eMail-Text
				$mail->Body = $nachricht;

				// Abrechnung als Anhang der eMailadresse hinzufügen
				$mail->AddAttachment($pfad . $pdf);

				if (!$mail->send()) {
					// ein Fehler ist aufgetreten
					$html_msg = getErrorMessage();
				} else {
					// eine Infomeldung für den Anwender einblenden, falls
					// der eMailversand erfolgreich durchgeführt wurde
					$html_msg = getInfoMessage();
				}
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

        <title>eMail Versand</title>

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
				
					<h2>eMail Versand <small>der Monatsabrechnungen</small></h2>
					
					<div class="helpline">
						Schreibe komfortabel einen netten Text zu Monatsabrechnung an alle Mitglieder aus dem Verein,
						welche eine eMail-Adresse hinterlegt haben.
						<br />
						Diese eMail wird dann anschlie&szlig;end mit der entsprechenden Abrechnung im Anhang versehen. 
					</div>
					
					<br />
					
					<!-- Infomeldung -->
					<?php if (isset($html_msg)) { echo $html_msg; } ?>
					<!-- Infomeldung -->

					<form action="mailversand.php" method="POST">
						<table class="monatsabrechnung">
							<tr>
								<td>
									<fieldset>
										<legend style="color:#333333;">
											<img src="./img/pencil_16_top.png" align="left" hspace="5" />
											<strong>eMailtext verfassen</strong>
										</legend>
										<textarea id="mailtext" name="mailtext" class="mailversand" style="width:630px;height:400px;font-family:Courier New;padding:5px;"><?php echo getMailtext(); ?></textarea>
									</fieldset>
								</td>
							</tr>
							<tr height="5"></tr>
							<tr>
								<td>
									<fieldset>
										<legend style="color:#333333;">
											<img src="./img/envelope-2-19.png" align="left" hspace="5" />
											<strong>Rechnung ausw&auml;hlen und versenden</strong>
										</legend>
										<table class="monatsabrechnung" style="margin-top: -5px;">
											<tr>
												<td width="350" style="padding-left:10px;">
													<img src="./img/pdf_icon_16x19.png" align="left" style="margin-top:5px;margin-right:5px;" />
													<label for="rechn_zyklus">Rechnungs-Zyklus <small>ausw&auml;hlen</small>:</label>&nbsp;
													<select name="rechn_zyklus" id="rechn_zyklus" class="mailversand" style="width:110px;">
														<?php echo getRechnungszyklen(); ?>
													</select>
												</td>
												<td align="right">
													<input type="submit" value="Abrechnung per eMail versenden" name="button_mailversand" id="button_mailversand" />
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