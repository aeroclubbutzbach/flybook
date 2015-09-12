<!-- BEGINN: SKRIPT -->
<?php

	// allgemeine Funktionen einbinden
	include_once('./functions.php');
	
	/*
	 * getListeFlugzeuge()
	 *
	 * gibt eine ComboBox mit allen enthaltenen Flugzeugen zurück
	 * es wird der Eintrag selektiert, auf den das per Parameter
	 * übergebene Kennzeichen passt
	 *
	 * @params string $kennzeichen
	 * @return string $html
	 */
	if (!function_exists('getListeFlugzeuge')) {
		function getListeFlugzeuge($kennzeichen)
		{
			// Rückgabe-Variable definieren
			$html = '<option value=""></option>';

			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
			// alle vorhandenen Flugzeuge laden
			$sql = sprintf('
				SELECT
					`flugzeugtyp`,
					`kennzeichen`
				FROM
					`flugzeuge`
				ORDER BY
					`kennzeichen` ASC
			');
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// es sind Datensätze vorhanden
			while ($zeile = mysql_fetch_object($db_erg)) {
				// prüfen ob es sich bei dem aktuellen Flugzeug um das ausgewählte handelt
				$selected = ($zeile->kennzeichen == $kennzeichen) ? ' selected="selected"' : '';

				// Daten übernehmen wie hinterlegt
				// und Flugzeugliste zusammenstellen
				$html .= sprintf(
					'<option value="%s"%s>%s, %s</option>',
					$zeile->kennzeichen, $selected, $zeile->kennzeichen, $zeile->flugzeugtyp
				);
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Flugzeugliste
			return $html;
		}
	}
	
	/*
	 * getListeMitglieder()
	 *
	 * gibt eine ComboBox mit allen enthaltenen Mitgliedern zurück
	 * es wird der Eintrag selektiert, auf den die per Parameter
	 * übergebene Mitgliedsnummer passt
	 *
	 * @params integer $acb_nr
	 * @return string  $html
	 */
	if (!function_exists('getListeMitglieder')) {
		function getListeMitglieder($acb_nr)
		{
			// Rückgabe-Variable definieren
			$html = '<option value=""></option>';

			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
			// alle vorhandenen Mitglieder laden
			$sql = sprintf('
				SELECT
					`id`,
					`nachname`,
					`ameavia`
				FROM
					`mitglieder`
				ORDER BY
					`ameavia` ASC
			');
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// es sind Datensätze vorhanden
			while ($zeile = mysql_fetch_object($db_erg)) {
				// prüfen ob es sich bei dem aktuellen Mitglied um das ausgewählte handelt
				$selected = ($zeile->id == $acb_nr) ? ' selected="selected"' : '';

				// Daten übernehmen wie hinterlegt
				// und Mitgliederliste zusammenstellen
				$html .= sprintf(
					'<option value="%s|%s"%s>%s</option>',
					$zeile->id, $zeile->nachname, $selected, $zeile->ameavia
				);
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Mitgliederliste
			return $html;
		}
	}
	
	/*
	 * getListeFlugplaetze()
	 *
	 * gibt eine ComboBox mit allen enthaltenen Flugplätze zurück
	 * es wird der Eintrag selektiert, auf den die per Parameter
	 * übergebene Identifikation passt
	 *
	 * @params string $flugplatz_id
	 * @return string $html
	 */
	if (!function_exists('getListeFlugplaetze')) {
		function getListeFlugplaetze($flugplatz_id)
		{
			// Rückgabe-Variable definieren
			$html = '<option value=""></option>';

			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
			// alle vorhandenen Mitglieder laden
			$sql = sprintf('
				SELECT
					`ameavia`,
					`name`
				FROM
					`flugplaetze`
				ORDER BY
					`name` ASC
			');
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// es sind Datensätze vorhanden
			while ($zeile = mysql_fetch_object($db_erg)) {
				// prüfen ob es sich bei dem aktuellen Flugplatz um den ausgewählten handelt
				$selected = ($zeile->ameavia == $flugplatz_id) ? ' selected="selected"' : '';

				// Daten übernehmen wie hinterlegt
				// und Flugplatzliste zusammenstellen
				$html .= sprintf(
					'<option value="%s"%s>%s</option>',
					$zeile->ameavia, $selected, $zeile->name
				);
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Flugplatzliste
			return $html;
		}
	}
	
	/*
	 * getListeStartarten()
	 *
	 * gibt eine ComboBox mit allen enthaltenen Startarten zurück
	 * es wird der Eintrag selektiert, auf den die per Parameter
	 * übergebene Identifikation passt
	 *
	 * @params integer $startart_id
	 * @params string  $kennzeichen
	 * @params integer $jahr
	 * @return string  $html
	 */
	if (!function_exists('getListeStartarten')) {
		function getListeStartarten($startart_id, $kennzeichen, $jahr)
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
			// hinterlegte Startarten für das ausgewählte Flugzeug ermitteln
			$sql = sprintf('
				SELECT
					`startart`
				FROM
					`flugzeuge`
				WHERE
					`kennzeichen` = "%s"
				LIMIT 1
			',
				$kennzeichen
			);

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// es sind Datensätze vorhanden
			if ($zeile = mysql_fetch_object($db_erg)) {
				// die Startarten werden ermittelt
				$startarten = str_replace(',', '","', $zeile->startart);
				$startarten = sprintf('"%s"', $startarten);
			
				// prüfen um welches Jahr es sich handelt um entsprechend den SQL-Befehl anzupassen
				if ($jahr < 2014) {
					// SQL-Befehl zurechtfuddeln,
					// alle vorhandenen Startarten laden
					$sql = sprintf('
						SELECT
							`kbez` AS `id`,
							`bezeichnung` AS `bezeichnung`
						FROM
							`startarten`
						WHERE
							`startart` IN (%s) AND
							`projekt` = "flybook"
						ORDER BY
							`kbez` ASC
					',
						$startarten
					);
				} else {
					// SQL-Befehl zurechtfuddeln,
					// alle vorhandenen Startarten laden
					$sql = sprintf('
						SELECT
							`id` AS `id`,
							`bezeichnung` AS `bezeichnung`
						FROM
							`startarten`
						WHERE
							`startart` IN (%s) AND
							`projekt` = "ameavia"
						ORDER BY
							`id` ASC
					',
						$startarten
					);
				}
			}
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// es sind Datensätze vorhanden
			while ($zeile = mysql_fetch_object($db_erg)) {
				// prüfen ob es sich bei der aktuellen Startart um die ausgewählte handelt
				$selected = ($zeile->id == $startart_id) ? ' selected="selected"' : '';

				// Daten übernehmen wie hinterlegt
				// und Startartenliste zusammenstellen
				$html .= sprintf(
					'<option value="%s"%s style="width: 300px;">%s</option>\n',
					$zeile->id, $selected, $zeile->bezeichnung
				);
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Startartenliste
			return $html;
		}
	}
	
	/*
	 * getFlugdaten()
	 *
	 * die Angaben zum ausgewählten Flug werden geladen
	 *
	 * @params integer $flug_id
	 * @params date    $datum
	 * @return array   $data
	 */
	if (!function_exists('getFlugdaten')) {
		function getFlugdaten($flug_id, $datum)
		{
			// Rückgabe-Array definieren
			$data = array();
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// aktuelles Jahr aus übergebenen Parameter ermitteln
			$jahr = intval(substr($datum, 0, 4));

			// prüfen um welches Jahr es sich handelt um entsprechend den SQL-Befehl anzupassen
			if ($jahr < 2014) {
				// SQL-Befehl zurechtfuddeln,
				// die Daten für die aktuell ausgewählte Startliste laden
				$sql = sprintf('
					SELECT
						`hauptflugbuch`.`id` AS `id`,
						`hauptflugbuch`.`datum` AS `datum`,
						`hauptflugbuch`.`kennzeichen` AS `kennzeichen`,
						`hauptflugbuch`.`typ` AS `flugzeugtyp`,
						`hauptflugbuch`.`pilot` AS `pilot`,
						`hauptflugbuch`.`pilotname` AS `pilotname`,
						`mitglieder_1`.`ameavia` AS `besatzung1`,
						`hauptflugbuch`.`begleiter` AS `begleiter`,
						`hauptflugbuch`.`begleitername` AS `begleitername`,
						`mitglieder_2`.`ameavia` AS `besatzung2`,
						`flugplaetze_1`.`ameavia` AS `startort`,
						`hauptflugbuch`.`startort` AS `startflugplatz`,
						`flugplaetze_2`.`ameavia` AS `landeort`,
						`hauptflugbuch`.`landeort` AS `landeflugplatz`,
						TIME_FORMAT(`hauptflugbuch`.`startzeit`, "%%H:%%i") AS `startzeit`,
						TIME_FORMAT(`hauptflugbuch`.`landezeit`, "%%H:%%i") AS `landezeit`,
						(MINUTE(`hauptflugbuch`.`flugzeit`) + (HOUR(`hauptflugbuch`.`flugzeit`) * 60)) AS `flugzeit`,
						`hauptflugbuch`.`motorstart` AS `motorstart`,
						`hauptflugbuch`.`motorende` AS `motorende`,
						`hauptflugbuch`.`einheiten` AS `einheiten`,
						`hauptflugbuch`.`startart` AS `startart`,
						`hauptflugbuch`.`landungen` AS `landungen`,
						`hauptflugbuch`.`bemerkungen` AS `bemerkungen`,
						`hauptflugbuch`.`kostengast` AS `fluggebuehren`,
						`hauptflugbuch`.`strecke` AS `strecke`
					FROM
						`hauptflugbuch`
					LEFT JOIN
						`flugplaetze` AS `flugplaetze_1` ON `hauptflugbuch`.`startort` = `flugplaetze_1`.`name`
					LEFT JOIN
						`flugplaetze` AS `flugplaetze_2` ON `hauptflugbuch`.`landeort` = `flugplaetze_2`.`name`
					LEFT JOIN
						`mitglieder` AS `mitglieder_1` ON `hauptflugbuch`.`pilot` = `mitglieder_1`.`id`
					LEFT JOIN
						`mitglieder` AS `mitglieder_2` ON `hauptflugbuch`.`pilot` = `mitglieder_2`.`id`
					WHERE
						`hauptflugbuch`.`id` = %d
					LIMIT 1
				',
					$flug_id
				);
			} else {
				// SQL-Befehl zurechtfuddeln,
				// die Daten für die aktuell ausgewählte Startliste laden
				$sql = sprintf('
					SELECT
						`flugbuch`.`id` AS `id`,
						`flugbuch`.`datum` AS `datum`,
						`flugbuch`.`luftfahrzeug` AS `kennzeichen`,
						`flugzeuge`.`flugzeugtyp` AS `flugzeugtyp`,
						`mitglieder_1`.`id` AS `pilot`,
						CASE
							WHEN (`mitglieder_1`.`nachname` IS NULL) THEN
								`flugbuch`.`besatzung1`
							ELSE
								`mitglieder_1`.`nachname`
						END AS `pilotname`,
						`flugbuch`.`besatzung1` AS `besatzung1`,
						`mitglieder_2`.`id` AS `begleiter`,
						CASE
							WHEN (`mitglieder_2`.`nachname` IS NULL) THEN
								`flugbuch`.`besatzung2`
							ELSE
								`mitglieder_2`.`nachname`
						END AS `begleitername`,
						`flugbuch`.`besatzung2` AS `besatzung2`,
						CASE
							WHEN (`flugplaetze_1`.`name` IS NULL) THEN (
								SELECT
									`ameavia`
								FROM
									`flugplaetze`
								WHERE
									`name` = `flugbuch`.`startort`
								LIMIT 1
							)
							ELSE
								`flugplaetze_1`.`ameavia`
						END AS `startort`,
						`flugplaetze_1`.`name` AS `startflugplatz`,
						CASE
							WHEN (`flugplaetze_2`.`name` IS NULL) THEN (
								SELECT
									`ameavia`
								FROM
									`flugplaetze`
								WHERE
									`name` = `flugbuch`.`landeort`
								LIMIT 1
							)
							ELSE
								`flugplaetze_2`.`ameavia`
						END AS `landeort`,
						`flugplaetze_2`.`name` AS `landeflugplatz`,
						TIME_FORMAT(`flugbuch`.`startzeit`, "%%H:%%i") AS `startzeit`,
						TIME_FORMAT(`flugbuch`.`landezeit`, "%%H:%%i") AS `landezeit`,
						`flugbuch`.`flugzeit` AS `flugzeit`,
						`flugbuch`.`motorstart` AS `motorstart`,
						`flugbuch`.`motorende` AS `motorende`,
						`flugbuch`.`einheiten` AS `einheiten`,
						`flugbuch`.`startart` AS `startart`,
						`flugbuch`.`landungen` AS `landungen`,
						`flugbuch`.`bemerkungen` AS `bemerkungen`,
						`flugbuch`.`fluggebuehren` AS `fluggebuehren`,
						`flugbuch`.`gebuehrenleistung1` AS `gebuehrenleistung`,
						`flugbuch`.`strecke` AS `strecke`
					FROM
						`flugbuch`
					LEFT JOIN
						`flugzeuge` ON `flugzeuge`.`kennzeichen` = `flugbuch`.`luftfahrzeug`
					LEFT JOIN
						`mitglieder` AS `mitglieder_1` ON `flugbuch`.`besatzung1` LIKE CONCAT("%%", `mitglieder_1`.`ameavia`, "%%")
					LEFT JOIN
						`mitglieder` AS `mitglieder_2` ON `flugbuch`.`besatzung2` LIKE CONCAT("%%", `mitglieder_2`.`ameavia`, "%%")
					LEFT JOIN
						`flugplaetze` AS `flugplaetze_1` ON `flugbuch`.`startort` = `flugplaetze_1`.`ameavia`
					LEFT JOIN
						`flugplaetze` AS `flugplaetze_2` ON `flugbuch`.`landeort` = `flugplaetze_2`.`ameavia`
					WHERE
						`flugbuch`.`id` = %d
					LIMIT 1
				',
					$flug_id
				);
			}

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// es sind Datensätze vorhanden
			while ($zeile = mysql_fetch_object($db_erg)) {
				// Daten übernehmen wie hinterlegt
				$data['id']             = $zeile->id;
				$data['datum']          = $zeile->datum;
				$data['kennzeichen']    = $zeile->kennzeichen;
				$data['flugzeugtyp']    = $zeile->flugzeugtyp;
				$data['pilot']          = $zeile->pilot;
				$data['pilotname']      = $zeile->pilotname;
				$data['besatzung1']     = $zeile->besatzung1;
				$data['begleiter']      = $zeile->begleiter;
				$data['begleitername']  = $zeile->begleitername;
				$data['besatzung2']     = $zeile->besatzung2;
				$data['startort']       = $zeile->startort;
				$data['startflugplatz'] = $zeile->startflugplatz;
				$data['landeort']       = $zeile->landeort;
				$data['landeflugplatz'] = $zeile->landeflugplatz;
				$data['startzeit']      = $zeile->startzeit;
				$data['landezeit']      = $zeile->landezeit;
				$data['flugzeit']       = $zeile->flugzeit;
				$data['motorstart']     = number_format($zeile->motorstart, 2, ',', '');
				$data['motorende']      = number_format($zeile->motorende, 2, ',', '');
				$data['einheiten']      = number_format($zeile->einheiten, 2, ',', '');
				$data['startart']       = $zeile->startart;
				$data['landungen']      = $zeile->landungen;
				$data['strecke']        = $zeile->strecke;
				$data['bemerkungen']    = $zeile->bemerkungen;
				$data['fluggebuehren']  = number_format($zeile->fluggebuehren + $zeile->gebuehrenleistung, 2, ',', '');
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Flugdaten
			return $data;
		}
	}
	
	/*
	 * updateFlugbuch()
	 *
	 * aktualisiert einen bereits vorhandenen Flug anhand der
	 * übergebenen Parameter und des übergebenen Datums
	 *
	 * @params array $params
	 * @params date  $datum
	 */
	if (!function_exists('updateFlugbuch')) {
		function updateFlugbuch(array $params, $datum)
		{
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// prüfen um welches Jahr es sich handelt um entsprechend den SQL-Befehl anzupassen
			if ($params['jahr'] < 2014) {
				// die Flugzeit vom Format MM in das Format HH:MM bringen
				$params['flugzeit'] = minutesToTime($params['flugzeit']);
			
				// SQL-Befehl zurechtfuddeln,
				// Befehl zum Speichern einer Veränderung eines Fluges
				$sql = sprintf('
					UPDATE
						`hauptflugbuch`
					SET
						`typ` = %s,
						`kennzeichen` = %s,
						`pilot` = %s,
						`pilotname` = %s,
						`begleiter` = %s,
						`begleitername` = %s,
						`startart` = %s,
						`loc_startort` = (SELECT `icao_id` FROM `flugplaetze` WHERE `ameavia` = %s),
						`startort` = %s,
						`loc_landeort` = (SELECT `icao_id` FROM `flugplaetze` WHERE `ameavia` = %s),
						`landeort` = %s,
						`startzeit` = %s,
						`landezeit` = %s,
						`flugzeit` = %s,
						`motorstart` = %s,
						`motorende` = %s,
						`einheiten` = %s,
						`landungen` = %s,
						`strecke` = %s,
						`bemerkungen` = %s
					WHERE
						`id` = %s
				',
					getDbValue($params['typ'],            T_STR),
					getDbValue($params['kennzeichen'],    T_STR),
					getDbValue($params['pilot'],          T_NUMERIC),
					getDbValue($params['pilotname'],      T_STR),
					getDbValue($params['begleiter'],      T_NUMERIC),
					getDbValue($params['begleitername'],  T_STR),
					getDbValue($params['startart'],       T_STR),
					getDbValue($params['startort'],       T_STR),
					getDbValue($params['startflugplatz'], T_STR),
					getDbValue($params['landeort'],       T_STR),
					getDbValue($params['landeflugplatz'], T_STR),
					getDbValue($params['startzeit'],      T_TIME),
					getDbValue($params['landezeit'],      T_TIME),
					getDbValue($params['flugzeit'],       T_TIME),
					getDbValue($params['motorstart'],     T_FLOAT),
					getDbValue($params['motorende'],      T_FLOAT),
					getDbValue($params['einheiten'],      T_FLOAT),
					getDbValue($params['landungen'],      T_NUMERIC),
					getDbValue($params['strecke'],        T_NUMERIC),
					getDbValue($params['bemerkungen'],    T_STR),
					getDbValue($params['lfd_nr'],         T_NUMERIC)
				);
			} else {
				// SQL-Befehl zurechtfuddeln,
				// Befehl zum Speichern einer Veränderung eines Fluges
				$sql = sprintf('
					UPDATE
						`flugbuch`
					SET
						`startzeit` = %s,
						`landezeit` = %s,
						`flugzeit` = %s,
						`motorstart` = %s,
						`motorende` = %s,
						`einheiten` = %s,
						`luftfahrzeug` = %s,
						`besatzung1` = %s,
						`besatzung2` = %s,
						`startort` = %s,
						`landeort` = %s,
						`startart` = %s,
						`landungen` = %s,
						`strecke` = %s,
						`bemerkungen` = %s
					WHERE
						`id` = %s
				',
					getDbValue($params['startzeit'],   T_TIME),
					getDbValue($params['landezeit'],   T_TIME),
					getDbValue($params['flugzeit'],    T_NUMERIC),
					getDbValue($params['motorstart'],  T_FLOAT),
					getDbValue($params['motorstart'],  T_FLOAT),
					getDbValue($params['einheiten'],   T_FLOAT),
					getDbValue($params['kennzeichen'], T_STR),
					getDbValue($params['besatzung1'],  T_STR),
					getDbValue($params['besatzung2'],  T_STR),
					getDbValue($params['startort'],    T_STR),
					getDbValue($params['landeort'],    T_STR),
					getDbValue($params['startart'],    T_NUMERIC),
					getDbValue($params['landungen'],   T_NUMERIC),
					getDbValue($params['strecke'],     T_NUMERIC),
					getDbValue($params['bemerkungen'], T_STR),
					getDbValue($params['lfd_nr'],      T_NUMERIC)
				);
			}

			// zuvor definierte SQL-Anweisung ausführen
			mysql_query($sql);
		}
	}
	


	/**************************************************************************************************************************/
	/* --------------------------------------- BEGINN : FLUG SPEICHERN NACH GET-BEFEHL -------------------------------------- */
	/**************************************************************************************************************************/
	
	// Array anlegen für die Feldinhalte
	$data = array();
	
	if (isset($_GET['action']) && $_GET['action'] == 'speichern') {
		// Bearbeiten eines bestehenden Datensatzes
		updateFlugbuch($_POST, $_GET['datum_id']);

		// zurück zur normalen Startliste
		echo '<script language="javascript" type="text/javascript">';
		echo sprintf('window.location.href = "startliste.php?datum_id=%s"', $_GET['datum_id']);
		echo '</script>';

		// sicher stellen, dass der nachfolgende Code nicht
		// ausgefuehrt wird, wenn eine Umleitung stattfindet.
		exit();
	} else {
		if (isset($_GET['datum_id']) && isset($_GET['id'])) {
			// prüfen ob ein Datum gesetzt wurde
			$datum_id = $_GET['datum_id'];
			$flug_id  = $_GET['id'];
			
			// Flugdaten ermitteln
			$data = getFlugdaten($flug_id, $datum_id);
		} else {
			// ein leeres Datum
			$datum_id = '';
			$flug_id  = '';
		}
	}

	/**************************************************************************************************************************/
	/* ---------------------------------------- ENDE : FLUG SPEICHERN NACH GET-BEFEHL --------------------------------------- */
	/**************************************************************************************************************************/

?>
<!-- ENDE: SKRIPT -->
<!-- BEGINN: AUSGABE -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

    <head>

        <title>Flug &auml;ndern</title>

        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <meta http-equiv="Content-Script-Type" content="text/javascript" />
        <meta http-equiv="Content-Style-Type" content="text/css" />
        <meta http-equiv="content-language" content="de" />
        <meta name="author" content="Benjamin Stopfkuchen" />
		
		<script type="text/javascript" src="./js/functions.js"></script>
		<script type="text/javascript" src="./js/jquery-1.10.2.js"></script>
		<script type="text/javascript" src="./js/jquery-1.10.2.min.js"></script>
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.js"></script>
		<script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
		
		<script type="text/javascript" src="./js/jquery.timeentry.package-2.0.1/jquery.plugin.js"></script>
		<script type="text/javascript" src="./js/jquery.timeentry.package-2.0.1/jquery.timeentry.js"></script>

        <link rel="Stylesheet" type="text/css" href="./css/stylish.css" />
		<link rel="stylesheet" type="text/css" href="./js/jquery.timeentry.package-2.0.1/jquery.timeentry.css" />
		<link rel="Stylesheet" type="text/css" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
		
		<script type="text/javascript" language="JavaScript">
		<!--
		
			function setStartarten(kennzeichen, jahr)
			{
				// Variable erzeugen zur Übergabe der Parameter
				var data = new FormData();

				// zusätzlicher Parameter wird angehängt
				// -> Flugzeug-Kennzeichen
				data.append('kennzeichen', kennzeichen);
				// das Zielverzeichnis festlegen
				data.append('jahr', jahr);
				
				// Aufruf per AJAX an das PHP-Modul, welches
				// die Funktion zum Ermitteln der Startarten enthält
				$.ajax({
					url: 'ajax_getdata_startarten.php',
					data: data,
					type: 'POST',
					processData: false,
					contentType: false,
					success: function(data) {
						// Rückgabe-Daten per JSON auslesen
						var json = $.parseJSON(data);
						
						// alles gut, die Liste der Startarten wird zurückgegeben
						$('#startart').html(json.html_options);
					}
				});
			}
			
			function pilotChange()
			{
				if ($('#pilot option:selected').val() == '') {
					$('#pilotname').val('');
					$('#pilotname').removeAttr('readonly');
					$('#pilotname').attr('onfocus', '');
					$('#pilotname').removeClass('flug_anlegen_gesperrt');
					$('#pilotname').addClass('flug_anlegen');
				} else {
					var pilot = $('#pilot option:selected').val();
					pilot = pilot.split('|', 2);
				
					$('#pilotname').val(pilot[1]);
					$('#pilotname').attr('readonly', 'readonly');
					$('#pilotname').attr('onfocus', 'this.blur()');
					$('#pilotname').removeClass('flug_anlegen');
					$('#pilotname').addClass('flug_anlegen_gesperrt');
				}
			}
			
			function getCurrentTime()
			{
				var jetzt = new Date();

				var std = jetzt.getHours();
				var min = jetzt.getMinutes();

				std = ((std < 10) ? '0' + std : std);
				min = ((min < 10) ? '0' + min : min);

				return std + ':' + min;
			}
			
			function getTimeDiff(von, bis)
			{
				if ((von == '00:00' || bis == '00:00') || (von == '' || bis == '')) {
					return 0;
				} else {
					var startzeit = von.split(':');
					var landezeit = bis.split(':');
					
					var vonDatum = new Date(2014, 1, 1, parseInt(startzeit[0]), parseInt(startzeit[1]), 0);
					var bisDatum = new Date(2014, 1, 1, parseInt(landezeit[0]), parseInt(landezeit[1]), 0);
					
					var timeDiff = (bisDatum - vonDatum) / 1000 / 60 / 60;
					
					var std = parseInt(timeDiff);
					var min = parseInt((timeDiff - std) * 60);
					
					return (std * 60 + min);
				}
			}
			
			function getEngineDiff(von, bis)
			{
				var motorstart = von.replace(',', '.');
				var motorende  = bis.replace(',', '.');
				
				motorstart = parseFloat(motorstart).toFixed(2);
				motorende  = parseFloat(motorende).toFixed(2);
				
				var einheiten = motorende - motorstart;
				
				einheiten = einheiten.toFixed(2).toString();
				einheiten = einheiten.replace('.', ',');

				return einheiten;
			}
			
			jQuery(function($) {
				// Initialisierung der Zeitauswahl
				// -> jQuery-UI-Komponente Timepicker
				$.timeEntry.setDefaults({ show24Hours: true, spinnerImage: '' });
			});
			
			$(document).ready(function() {
				// Initialisierung der Karteireiter
				// -> jQuery-UI-Komponente Timepicker
				$('#startzeit').timeEntry();
				$('#landezeit').timeEntry();
			
				$('#flugzeug').change(function() {
					if ($('#flugzeug option:selected').val() == '') {
						$('#kennzeichen').val('');
						$('#kennzeichen').removeAttr('readonly');
						$('#kennzeichen').attr('onfocus', '');
						$('#kennzeichen').removeClass('flug_anlegen_gesperrt');
						$('#kennzeichen').addClass('flug_anlegen');
						$('#typ').val('');
						$('#typ').removeAttr('readonly');
						$('#typ').attr('onfocus', '');
						$('#typ').removeClass('flug_anlegen_gesperrt');
						$('#typ').addClass('flug_anlegen');
					} else {
						var lfz = $('#flugzeug option:selected').text();
						lfz = lfz.split(', ', 2);
					
						$('#kennzeichen').val(lfz[0]);
						$('#kennzeichen').attr('readonly', 'readonly');
						$('#kennzeichen').attr('onfocus', 'this.blur()');
						$('#kennzeichen').removeClass('flug_anlegen');
						$('#kennzeichen').addClass('flug_anlegen_gesperrt');
						$('#typ').val(lfz[1]);
						$('#typ').attr('readonly', 'readonly');
						$('#typ').attr('onfocus', 'this.blur()');
						$('#typ').removeClass('flug_anlegen');
						$('#typ').addClass('flug_anlegen_gesperrt');
					}
					
					// Startarten laden
					setStartarten($('#kennzeichen').val(), $('#jahr').val());
				});
				
				$('#pilot').change(function() {
					if ($('#pilot option:selected').val() == '') {
						$('#besatzung1').val('');
						$('#pilotname').val('');
						$('#pilotname').removeAttr('readonly');
						$('#pilotname').attr('onfocus', '');
						$('#pilotname').removeClass('flug_anlegen_gesperrt');
						$('#pilotname').addClass('flug_anlegen');
					} else {
						var pilot = $('#pilot option:selected').val();
						pilot = pilot.split('|', 2);
					
						$('#besatzung1').val($('#pilot option:selected').text());
						$('#pilotname').val(pilot[1]);
						$('#pilotname').attr('readonly', 'readonly');
						$('#pilotname').attr('onfocus', 'this.blur()');
						$('#pilotname').removeClass('flug_anlegen');
						$('#pilotname').addClass('flug_anlegen_gesperrt');
					}
				});
				
				$('#pilotname').change(function() {
					if ($('#pilot option:selected').val() == '') {
						$('#besatzung1').val($('#pilotname').val());
					}
				});
				
				$('#begleiter').change(function() {
					if ($('#begleiter option:selected').val() == '') {
						$('#besatzung2').val('');
						$('#begleitername').val('');
						$('#begleitername').removeAttr('readonly');
						$('#begleitername').attr('onfocus', '');
						$('#begleitername').removeClass('flug_anlegen_gesperrt');
						$('#begleitername').addClass('flug_anlegen');
					} else {
						var begleiter = $('#begleiter option:selected').val();
						begleiter = begleiter.split('|', 2);
					
						$('#besatzung2').val($('#begleiter option:selected').text());
						$('#begleitername').val(begleiter[1]);
						$('#begleitername').attr('readonly', 'readonly');
						$('#begleitername').attr('onfocus', 'this.blur()');
						$('#begleitername').removeClass('flug_anlegen');
						$('#begleitername').addClass('flug_anlegen_gesperrt');
					}
				});
				
				$('#begleitername').change(function() {
					if ($('#begleiter option:selected').val() == '') {
						$('#besatzung2').val($('#begleitername').val());
					}
				});
				
				$('#startort').change(function() {
					$('#startflugplatz').val($('#startort option:selected').text());
				});
				
				$('#landeort').change(function() {
					$('#landeflugplatz').val($('#landeort option:selected').text());
				});
				
				$('#neue_startzeit').click(function() {
					$('#startzeit').val(getCurrentTime());
					$('.flug_anlegen_zeit').trigger('change');
				});

				$('#neue_landezeit').click(function() {
					$('#landezeit').val(getCurrentTime());
					$('.flug_anlegen_zeit').trigger('change');
				});
				
				$('.flug_anlegen_zeit').change(function() {
					var startzeit = $('#startzeit').val();
					var landezeit = $('#landezeit').val();
					
					$('#flugzeit').val(getTimeDiff(startzeit, landezeit));
				}).blur(function() {
					if ($(this).val() == '') {
						$(this).val('00:00');
					}
				});
				
				$('#landungen').keydown(function(e) {
					return !(e.altKey || e.ctrlKey || e.shiftKey) && (
						e.keyCode >= 48 && e.keyCode <= 57 // 0 - 9
						|| e.keyCode >= 96 && e.keyCode <= 105 // 0 - 9 NumPad
						|| e.keyCode == 8 // <- Back
						|| e.keyCode == 9 // Tab
						|| e.keyCode == 16 // Shift
						|| e.keyCode == 37 // <- Left
						|| e.keyCode == 39 // -> Right
						|| e.keyCode == 46 // Delete
					);
				}).blur(function() {
					if (($(this).val() == '') || ($(this).val() == '0')) {
						$(this).val('1');
					}
				});
				
				$('#strecke').keydown(function(e) {
					return !(e.altKey || e.ctrlKey || e.shiftKey) && (
						e.keyCode >= 48 && e.keyCode <= 57 // 0 - 9
						|| e.keyCode >= 96 && e.keyCode <= 105 // 0 - 9 NumPad
						|| e.keyCode == 8 // <- Back
						|| e.keyCode == 9 // Tab
						|| e.keyCode == 16 // Shift
						|| e.keyCode == 37 // <- Left
						|| e.keyCode == 39 // -> Right
						|| e.keyCode == 46 // Delete
					);
				}).blur(function() {
					if ($(this).val() == '') {
						$(this).val('0');
					}
				});
				
				$('#motorstart, #motorende').keydown(function(e) {
					if (e.keyCode == 188 || e.keyCode == 110) {
						var txt = $(this).val();
						
						if (txt.indexOf(',') != -1) {
							return false;
						}
					} else {
						return !(e.altKey || e.ctrlKey || e.shiftKey) && (
							e.keyCode >= 48 && e.keyCode <= 57 // 0 - 9
							|| e.keyCode >= 96 && e.keyCode <= 105 // 0 - 9 NumPad
							|| e.keyCode == 188 // Komma ,
							|| e.keyCode == 110 // NumPad Komma ,
							|| e.keyCode == 8 // <- Back
							|| e.keyCode == 9 // Tab
							|| e.keyCode == 16 // Shift
							|| e.keyCode == 37 // <- Left
							|| e.keyCode == 39 // -> Right
							|| e.keyCode == 46 // Delete
						);
					}
				}).blur(function() {
					if ($(this).val() == '') {
						$(this).val('0,00');
					} else {
						var value = $(this).val();

						value = value.replace(',', '.');
						value = parseFloat(value).toFixed(2);
						value = value.toString();
						value = value.replace('.', ',');

						$(this).val(value);
					}
				}).change(function() {
					var motorstart = $('#motorstart').val();
					var motorende  = $('#motorende').val();

					$('#einheiten').val(getEngineDiff(motorstart, motorende));
				});
				
				$('#logbuch_speichern').click(function() {
					// Variable für die Fehlermeldung anlegen
					var error_msg = '';

					// zu allererst prüfen, ob alle Pflichtfelder korrekt ausgefüllt wurden
					// prüfen ob ein Flugzeugkennzeichen angegeben wurde
					if ($('#kennzeichen').val().trim() == '') {
						// es wurde kein Kennzeichen angegeben
						error_msg  = 'Ein von Dir eingegebenes Feld ist entweder leer oder fehlerhaft.<br />';
						error_msg += 'Bitte noch einmal versuchen, und diesmal ein richtiges Flugzeug angeben!';
						
						// das entsprechende Feld Kennzeichen als Fehler markieren
						$('#flugzeug').removeAttr('class').addClass('error_line');
						$('#kennzeichen').removeAttr('class').addClass('error_line');
					} else {
						// die normale Klasse des Feld Kennzeichen wiederherstellen
						$('#flugzeug').removeAttr('class').addClass('flug_anlegen');
						$('#kennzeichen').removeAttr('class').addClass('flug_anlegen');
					}
					
					// prüfen ob ein Pilot angegeben wurde
					if ($('#pilotname').val().trim() == '') {
						// es wurde kein Pilotname angegeben
						error_msg  = 'Ein von Dir eingegebenes Feld ist entweder leer oder fehlerhaft.<br />';
						error_msg += 'Bitte noch einmal versuchen, und diesmal einen richtigen Piloten angeben!';
						
						// die entsprechenden Felder für den Piloten als Fehler markieren
						$('#pilot').removeAttr('class').addClass('error_line');
						$('#pilotname').removeAttr('class').addClass('error_line');
					} else {
						// die normale Klasse der Felder für den Piloten wiederherstellen
						$('#pilot').removeAttr('class').addClass('flug_anlegen');
						$('#pilotname').removeAttr('class').addClass('flug_anlegen');
					}

					// prüfen ob die Landezeit nach der Startzeit liegt
					if ($('#flugzeit').val() < 0) {
						// die Landezeit passt nicht zur Startzeit, daher ist die
						// Gesamtflugzeit negativ, das ist natürlich nicht so schön
						error_msg  = 'Die Landezeit liegt vor der eingegebenen Startzeit! Das ist nicht so sch&ouml;n!<br />';
						error_msg += 'Bitte noch einmal versuchen, und diesmal richtige Zeiten angeben!';
						
						// die entsprechenden Felder für die Zeiten als Fehler markieren
						$('#startzeit').removeAttr('class').addClass('error_line');
						$('#landezeit').removeAttr('class').addClass('error_line');
					} else {
						// die normale Klasse der Felder für die Zeiten wiederherstellen
						$('#startzeit').removeAttr('class').addClass('flug_anlegen');
						$('#landezeit').removeAttr('class').addClass('flug_anlegen');
					}
					
					// prüfen ob die Motorzählerstände korrekt sind
					if (parseFloat($('#einheiten').val().replace(',', '.')) < 0) {
						// die Motorzählerstände passen nicht zueinanden, daher sind die
						// Gesamteinheiten negativ, das ist natürlich nicht so schön
						error_msg  = 'Der Endstand des Motorz&auml;hlers liegt vor dem eingegebenen Anfangsz&auml;hlerstand! Das ist nicht so sch&ouml;n!<br />';
						error_msg += 'Bitte noch einmal versuchen, und diesmal richtige Zeiten angeben!';
						
						// die entsprechenden Felder für die Zählerstände als Fehler markieren
						$('#motorstart').removeAttr('class').addClass('error_line');
						$('#motorende').removeAttr('class').addClass('error_line');
					} else {
						// die normale Klasse der Felder für die Zeiten wiederherstellen
						$('#motorstart').removeAttr('class').addClass('flug_anlegen');
						$('#motorende').removeAttr('class').addClass('flug_anlegen');
					}
					
					// prüfen, ob die Fehlervariable gesetzt ist
					if (error_msg != '') {
						// Fehlermeldung ausgeben, wenn Fehlervariable gesetzt
						$('.errorline').html('<h3>Ein Fehler ist aufgetreten!</h3>' + error_msg);
						$('#fehlermeldung').css('display', 'inline');

						// als Ergebnis wird FALSCH zurückgegeben,
						// es findet also keine Speicherung der Daten statt
						return false;
					} else {
						// alles Bestens, keine Fehler
						// also kann nun ohne Bedenken gespeichert werden
						return true;
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
				
					<h2>Flug bearbeiten</h2>
					
					<div class="helpline">
						 Wichtige Informationen wie zum Beispiel Datum, Kennzeichen, Startart, Pilot,
						 Begleiter, Start-/Landezeit, Start-/Landeort, Anzahl Landungen, Stecke,
						 Motorlaufzeit k&ouml;nnen hier nachtr&auml;glich bearbeitet werden.
						 <br />
						 <br />
						 Das &Auml;ndern der Parameter hat keinen Einfluss auf die Abrechnung,
						 d.h. die bereits berechneten Fluggeb&uuml;hren bleiben nach wie vor bestehen.
					</div>

					<br />
					
					<!-- Fehlermeldung -->
					<div id="fehlermeldung" style="display: none;">
						<div class="errorline"></div><br />
					</div>
					<!-- Fehlermeldung -->
					
					<form action="stl_aendern.php?id=<?php echo $flug_id; ?>&datum_id=<?php echo $datum_id; ?>&action=speichern" method="POST">

						<fieldset style="width: auto; background-color: #eeeeee;">
							<legend style="font-size: 11pt;"><img src="./img/mini_plane.png" align="left" height="20" width="20" hspace="5" /> Angaben zum aktuellen Flug</legend>

							<table cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
								<tr bgcolor="#dddddd">
									<th align="left" width="140" style="padding-left: 8px;"><label for="lfd_nr">Flug-Nummer:</label></th>
									<td colspan="3">
										<input style="width: 100px;" type="text" value="<?php echo $data['id']; ?>" maxlength="4" size="10" name="lfd_nr" id="lfd_nr" class="flug_anlegen_gesperrt" onFocus="this.blur();" readonly="readonly" />
										<img style="position: relative; left 0px; top: 2px;" title="Prim&auml;rschl&uuml;ssel => Lanfende Nummer" src="./img/1351092510_key.png">
									</td>
								</tr>
								<tr bgcolor="#eeeeee">
									<th width="140" align="left" valign="top" style="padding-top: 8px; padding-left: 8px;"><label for="startort">Flugzeug:</label></th>
									<td width="300">
										<select size="1" name="flugzeug" id="flugzeug" style="width: 269px;" class="flug_anlegen" tabindex="1" />
											<?php echo getListeFlugzeuge($data['kennzeichen']); ?>
										</select>
										<br />
										<input style="width: 100px; text-align: left; padding-left: 7px; text-transform: uppercase;" type="text" value="<?php echo $data['kennzeichen']; ?>" maxlength="10" size="10" name="kennzeichen" id="kennzeichen" class="flug_anlegen_gesperrt" onFocus="this.blur();" readonly="readonly" tabindex="2" />
										<input style="width: 165px; text-align: left; padding-left: 7px;" type="text" value="<?php echo $data['flugzeugtyp']; ?>" maxlength="20" size="20" name="typ" id="typ" class="flug_anlegen_gesperrt" onFocus="this.blur();" readonly="readonly" tabindex="3" />
									</td>
									<th width="140" align="left" valign="top" style="padding-top: 8px; padding-left: 8px;"><label for="startart">Startart:</label></th>
									<td width="300" valign="top">
										<select name="startart" id="startart" style="width: 269px; max-width: 269px;" class="flug_anlegen" tabindex="4">
											<?php echo getListeStartarten($data['startart'], $data['kennzeichen'], substr($datum_id, 0, 4)); ?>
										</select>
									</td>
								</tr>
								<tr bgcolor="#dddddd">
									<th width="140" align="left" valign="top" style="padding-top: 8px; padding-left: 8px;"><label for="pilot">Pilot:</label></th>
									<td width="300">
										<select size="1" name="pilot" id="pilot" style="width: 269px;" class="flug_anlegen" tabindex="5" />
											<?php echo getListeMitglieder($data['pilot']); ?>
										</select>
										<input type="hidden" name="besatzung1" id="besatzung1" value="<?php echo $data['besatzung1']; ?>" />
										<br />
										<?php if (!empty($data['pilot']) || ($data['pilot'] != '')) { ?>
											<input style="width: 269px; text-align: left; padding-left: 7px;" type="text" value="<?php echo $data['pilotname']; ?>" maxlength="30" size="30" name="pilotname" id="pilotname" class="flug_anlegen_gesperrt" onFocus="this.blur();" readonly="readonly" tabindex="6" />
										<?php } else { ?>
											<input style="width: 269px; text-align: left; padding-left: 7px;" type="text" value="<?php echo $data['pilotname']; ?>" maxlength="30" size="30" name="pilotname" id="pilotname" class="flug_anlegen" tabindex="6" />
										<?php } ?>
									</td>
									<th width="140" align="left" valign="top" style="padding-top: 8px; padding-left: 8px;"><label for="begleiter">Begleiter/<br />F-Schleppzahler:</label></th>
									<td width="300" valign="top">
										<select size="1" name="begleiter" id="begleiter" style="width: 269px;" class="flug_anlegen" tabindex="7" />
											<?php echo getListeMitglieder($data['begleiter']); ?>
										</select>
										<input type="hidden" name="besatzung2" id="besatzung2" value="<?php echo $data['besatzung2']; ?>" />
										<br />
										<?php if (!empty($data['begleiter']) || ($data['begleiter'] != '')) { ?>
											<input style="width: 269px; text-align: left; padding-left: 7px;" type="text" value="<?php echo $data['begleitername']; ?>" maxlength="30" size="30" name="begleitername" id="begleitername" class="flug_anlegen_gesperrt" onFocus="this.blur();" readonly="readonly" tabindex="8" />
										<?php } else { ?>
											<input style="width: 269px; text-align: left; padding-left: 7px;" type="text" value="<?php echo $data['begleitername']; ?>" maxlength="30" size="30" name="begleitername" id="begleitername" class="flug_anlegen" tabindex="8" />
										<?php } ?>
									</td>
								</tr>
								<tr>
									<td colspan="4"><hr /></td>
								</tr>
								<tr bgcolor="#eeeeee">
									<th width="140" align="left" style="padding-left: 8px;"><label for="startzeit">Startzeit:</label></th>
									<td width="300">
										<input style="width: 100px;" type="text" value="<?php echo fromSqlDatum($data['datum']); ?>" name="startdatum" id="startdatum" class="flug_anlegen_gesperrt" onFocus="this.blur();" readonly="readonly" />
										<img style="position: relative; left 0px; top: 2px;" title="Prim&auml;rschl&uuml;ssel => Flugdatum" src="./img/1351092510_key.png">
										<input style="width: 60px; text-align: center; margin-left: 10px;" type="text" value="<?php echo $data['startzeit']; ?>" maxlength="5" name="startzeit" id="startzeit" class="flug_anlegen_zeit" tabindex="9" />
										<input id="neue_startzeit" name="neue_startzeit" type="button" value="Start" tabindex="10" />
									</td>
									<th width="140" align="left" style="padding-left: 8px;"><label for="landezeit">Landezeit:</label></th>
									<td width="300">
										<input style="width: 100px;" type="text" value="<?php echo fromSqlDatum($data['datum']); ?>" name="landedatum" id="landedatum" class="flug_anlegen_gesperrt" onFocus="this.blur();" readonly="readonly" />
										<img style="position: relative; left 0px; top: 2px;" title="Prim&auml;rschl&uuml;ssel => Flugdatum" src="./img/1351092510_key.png">
										<input style="width: 60px; text-align: center; margin-left: 10px;" type="text" value="<?php echo $data['landezeit']; ?>" maxlength="5" name="landezeit" id="landezeit" class="flug_anlegen_zeit" tabindex="11" />
										<input id="neue_landezeit" name="neue_landezeit" type="button" value="Landung" tabindex="12" />
									</td>
								</tr>
								<tr bgcolor="#dddddd">
									<th width="140" align="left" style="padding-left: 8px;"><label for="startort">Startort:</label></th>
									<td width="300">
										<select name="startort" id="startort" style="width: 269px;" class="flug_anlegen" tabindex="13">
											<?php echo getListeFlugplaetze($data['startort']); ?>
										</select>
										<input type="hidden" name="startflugplatz" id="startflugplatz" value="<?php echo $data['startflugplatz']; ?>" />
									</td>
									<th width="140" align="left" style="padding-left: 8px;"><label for="landeort">Landeort:</label></th>
									<td width="300">
										<select name="landeort" id="landeort" style="width: 269px;" class="flug_anlegen" tabindex="14">
											<?php echo getListeFlugplaetze($data['landeort']); ?>
										</select>
										<input type="hidden" name="landeflugplatz" id="landeflugplatz" value="<?php echo $data['landeflugplatz']; ?>" />
									</td>
								</tr>
								<tr bgcolor="#eeeeee">
									<th width="140" align="left" style="padding-left: 8px;"><label for="landungen"><small>Anzahl d.</small> Landungen:</label></th>
									<td width="300">
										<input style="width: 100px; text-align: right; padding-right: 5px;" type="text" value="<?php echo $data['landungen']; ?>" maxlength="2" name="landungen" id="landungen" class="flug_anlegen" tabindex="15" />
									</td>
									<th width="140" align="left" style="padding-left: 8px;"><label for="flugzeit">Flugzeit:</label></th>
									<td width="300">
										<input style="width: 100px; text-align: right; padding-right: 5px;" type="text" value="<?php echo $data['flugzeit']; ?>" maxlength="5" name="flugzeit" id="flugzeit" class="flug_anlegen_gesperrt" onFocus="this.blur();" readonly="readonly" />
										<strong style="margin-left: 5px;">Min.</strong>
									</td>
								</tr>
								<tr>
									<td colspan="4"><hr /></td>
								</tr>
								<tr bgcolor="#dddddd">
									<th width="140" align="left" style="padding-left: 8px;"><label for="motorstart">Motorstart:</label></th>
									<td width="300">
										<input style="width: 100px; text-align: right; padding-right: 5px;" type="text" value="<?php echo $data['motorstart']; ?>" maxlength="10" name="motorstart" id="motorstart" class="flug_anlegen" tabindex="16" />
									</td>
									<th width="140" align="left" style="padding-left: 8px;"><label for="strecke">Strecke:</label></th>
									<td width="300">
										<input style="width: 100px; text-align: right; padding-right: 5px;" type="text" value="<?php echo $data['strecke']; ?>" maxlength="5" name="strecke" id="strecke" class="flug_anlegen" tabindex="18" />
										<strong style="margin-left: 5px;">km</strong>
									</td>
								</tr>
								<tr bgcolor="#eeeeee">
									<th width="140" align="left" style="padding-left: 8px;"><label for="motorende">Motorende:</label></th>
									<td width="300">
										<input style="width: 100px; text-align: right; padding-right: 5px;" type="text" value="<?php echo $data['motorende']; ?>" maxlength="10" name="motorende" id="motorende" class="flug_anlegen" tabindex="17" />
									</td>
									<th width="140" align="left" style="padding-left: 8px;"><label for="fluggebuehren">Fluggeb&uuml;hren:</label></th>
									<td width="300">
										<input style="width: 100px; text-align: right; padding-right: 5px;" type="text" value="<?php echo $data['fluggebuehren']; ?>" maxlength="10" name="fluggebuehren" id="fluggebuehren" class="flug_anlegen_gesperrt" onFocus="this.blur();" readonly="readonly" />
										<strong style="margin-left: 5px;">Euro</strong>
									</td>
								</tr>
								<tr bgcolor="#dddddd">
									<th width="140" align="left" style="padding-left: 8px;"><label for="einheiten">Einheiten:</label></th>
									<td width="300" colspan="3">
										<input style="width: 100px; text-align: right; padding-right: 5px;" type="text" value="<?php echo $data['einheiten']; ?>" maxlength="10" name="einheiten" id="einheiten" class="flug_anlegen_gesperrt" onFocus="this.blur();" readonly="readonly" />
									</td>
								</tr>
								<tr>
									<td colspan="4"><hr /></td>
								</tr>
								<tr bgcolor="#eeeeee">
									<th width="140" align="left" style="padding-left: 8px;"><label for="bemerkungen">Bemerkungen:</label></th>
									<td width="300" colspan="3">
										<input style="width: 723px; text-align: left; padding-left: 5px;" type="text" value="<?php echo $data['bemerkungen']; ?>" maxlength="255" name="bemerkungen" id="bemerkungen" class="flug_anlegen" tabindex="19" />
									</td>
								</tr>
							</table>
						</fieldset>
						
						<div class="logbuch_speichern_buttons" style="width: auto;">
							<input type="submit" name="logbuch_speichern" id="logbuch_speichern" value="Daten speichern" style="width: 150px; margin-left: 10px;" tabindex="20" />
							<input type="button" name="logbuch_cancel" id="logbuch_cancel" value="Abbrechen" style="width: 150px;" onClick="window.location.href='startliste.php?datum_id=<?php echo $datum_id; ?>';" tabindex="21" />
						</div>
						
						<input type="hidden" id="jahr" name="jahr" value="<?php echo substr($datum_id, 0, 4); ?>" />
						
					</form>
					
				</td>
			</tr>
		</table>

	</body>

</html>
<!-- ENDE: AUSGABE -->