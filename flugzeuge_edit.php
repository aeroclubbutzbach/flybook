<!-- BEGINN: SKRIPT -->
<?php

	// allgemeine Funktionen einbinden
	include_once('./functions.php');

	/*
	 * getErrorMessage()
	 *
	 * eine Fehlermeldung wird dem Anwender zurückgegeben,
	 * falls der Anwender fehlerhafte Werte eingegeben hat
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
			$html .= 'Ein von Dir eingegebenes Feld ist entweder leer oder fehlerhaft.<br />';
			$html .= 'Bitte noch einmal versuchen, und diesmal einen richtigen Wert oder Text angeben!';
			$html .= '</div><br />';
			
			// Meldung zurückgeben
			return $html;
		}
	}
	
	/*
	 * getListeFlugzeugklasse()
	 *
	 * gibt eine ComboBox mit den entsprechenden Listeneinträgen der
	 * Flugzeugklassen zurück, per optionalen Parameter kann noch ein
	 * bestimmter Eintrag der ComboBox als Selektiert dargestellt werden
	 *
	 * @params char   $selektor
	 * @return string $html
	 */
	if (!function_exists('getListeFlugzeugklasse')) {
		function getListeFlugzeugklasse($selektor = 'M1')
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
			// die Bezeichnungen und die IDs der Flugzeug-
			// klassen werden als Liste zurückgegeben
			$sql = sprintf('
				SELECT
					`flugzeugtyp`.`id`,
					`flugzeugtyp`.`bezeichnung`
				FROM
					`flugzeugtyp`
				ORDER BY
					`flugzeugtyp`.`id` ASC
			');

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// es sind Datensätze vorhanden
			while ($zeile = mysql_fetch_object($db_erg)) {
				// prüfen ob ein selektierter Eintrag existiert
				$selected = ($selektor == $zeile->id) ? 'selected="selected"' : '';
			
				// Rückgabe einer entsprechenden Flugzeugklasse
				$html .= sprintf(
					'<option %s value="%s">%s</option>',
					$selected,
					$zeile->id,
					$zeile->bezeichnung
				);
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Flugzeugklassen (ComboBox)
			return $html;
		}
	}
	
	/*
	 * getListeFlugplaetze()
	 *
	 * gibt eine ComboBox mit den entsprechenden Listeneinträgen der
	 * Flugplätze/Standorte zurück, per optionalen Parameter kann noch ein
	 * bestimmter Eintrag der ComboBox als Selektiert dargestellt werden
	 *
	 * @params char   $selektor
	 * @return string $html
	 */
	if (!function_exists('getListeFlugplaetze')) {
		function getListeFlugplaetze($selektor = 'BUTZB1')
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
			// die Bezeichnungen und die IDs der Flugplätze/
			// Standorte werden als Liste zurückgegeben
			$sql = sprintf('
				SELECT
					`flugplaetze`.`id`,
					`flugplaetze`.`name`
				FROM
					`flugplaetze`
				WHERE
					`flugplaetze`.`heim` = "J"
				ORDER BY
					`flugplaetze`.`name` ASC
			');

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// es sind Datensätze vorhanden
			while ($zeile = mysql_fetch_object($db_erg)) {
				// prüfen ob ein selektierter Eintrag existiert
				$selected = ($selektor == $zeile->id) ? 'selected="selected"' : '';
			
				// Rückgabe eines entsprechenden Flugplätzes/Standortes
				$html .= sprintf(
					'<option %s value="%s">%s</option>',
					$selected,
					$zeile->id,
					$zeile->name
				);
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Flugplätze/Standorte (ComboBox)
			return $html;
		}
	}
	
	/*
	 * getListeFlugzeugstatus()
	 *
	 * gibt eine ComboBox mit den entsprechenden Listeneinträgen der
	 * Flugzeugstati zurück, per optionalen Parameter kann noch ein
	 * bestimmter Eintrag der ComboBox als Selektiert dargestellt werden
	 *
	 * @params char   $selektor
	 * @return string $html
	 */
	if (!function_exists('getListeFlugzeugstatus')) {
		function getListeFlugzeugstatus($selektor = '1')
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
			// die Bezeichnungen und die IDs der Flugzeugstati
			// werden als Liste zurückgegeben
			$sql = sprintf('
				SELECT
					`flugzeugstatus`.`id`,
					`flugzeugstatus`.`bezeichnung`
				FROM
					`flugzeugstatus`
				ORDER BY
					`flugzeugstatus`.`id` ASC
			');

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// es sind Datensätze vorhanden
			while ($zeile = mysql_fetch_object($db_erg)) {
				// prüfen ob ein selektierter Eintrag existiert
				$selected = ($selektor == $zeile->id) ? 'selected="selected"' : '';
			
				// Rückgabe eines entsprechenden Flugzeugstatus
				$html .= sprintf(
					'<option %s value="%s">%s</option>',
					$selected,
					$zeile->id,
					$zeile->bezeichnung
				);
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Flugzeugstati (ComboBox)
			return $html;
		}
	}
	
	/*
	 * getListeWartungen()
	 *
	 * gibt eine ComboBox mit den entsprechenden Listeneinträgen der
	 * möglichen Wartungen zurück, per optionalen Parameter kann noch ein
	 * bestimmter Eintrag der ComboBox als Selektiert dargestellt werden
	 *
	 * @params char   $selektor
	 * @return string $html
	 */
	if (!function_exists('getListeWartungen')) {
		function getListeWartungen($selektor = '1')
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
			// die Bezeichnungen und die IDs der Wartungen
			// werden als Liste zurückgegeben
			$sql = sprintf('
				SELECT
					`wartungen`.`id`,
					`wartungen`.`bezeichnung`
				FROM
					`wartungen`
				ORDER BY
					`wartungen`.`id` ASC
			');

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// es sind Datensätze vorhanden
			while ($zeile = mysql_fetch_object($db_erg)) {
				// prüfen ob ein selektierter Eintrag existiert
				$selected = ($selektor == $zeile->id) ? 'selected="selected"' : '';
			
				// Rückgabe einer entsprechenden Wartungen
				$html .= sprintf(
					'<option %s value="%s">%s</option>',
					$selected,
					$zeile->id,
					$zeile->bezeichnung
				);
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Wartungen (ComboBox)
			return $html;
		}
	}
	
	/*
	 * getListePersonenTechnik()
	 *
	 * gibt eine ComboBox mit den entsprechenden Listeneinträgen der Technik zurück
	 *
	 * @return string $html
	 */
	if (!function_exists('getListePersonenTechnik')) {
		function getListePersonenTechnik()
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
			// die Bezeichnungen und die IDs des technischen
			// Personals werden als Liste zurückgegeben
			$sql = sprintf('
				SELECT
					`mitglieder`.`id`,
					`mitglieder`.`nachname`
				FROM
					`mitglieder`
				WHERE
					`mitglieder`.`status` = "T" OR
					FIND_IN_SET("I", `mitglieder`.`fachausweise`) OR
					FIND_IN_SET("J", `mitglieder`.`fachausweise`) OR
					FIND_IN_SET("K", `mitglieder`.`fachausweise`) OR
					FIND_IN_SET("L", `mitglieder`.`fachausweise`) OR
					FIND_IN_SET("M", `mitglieder`.`fachausweise`) OR
					FIND_IN_SET("N", `mitglieder`.`fachausweise`) OR
					FIND_IN_SET("T", `mitglieder`.`fachausweise`)
				ORDER BY
					`mitglieder`.`nachname` ASC
			');

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// es sind Datensätze vorhanden
			while ($zeile = mysql_fetch_object($db_erg)) {
				// Rückgabe eines entsprechenden technischen Mitglieds
				$html .= sprintf(
					'<option value="%s">%s</option>',
					$zeile->id,
					$zeile->nachname
				);
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Technik (ComboBox)
			return $html;
		}
	}
	
	/*
	 * getLetzteJNP()
	 *
	 * die Daten zur letzten Jahresnachprüfung, des per Parameter über-
	 * gebenen Luftfahrzeuges, werden ermittelt und als Array zurückgegeben
	 *
	 * @params string $kennzeichen
	 * @return string $html
	 */
	if (!function_exists('getLetzteJNP')) {
		function getLetzteJNP($kennzeichen)
		{
			// Rückgabe-Variable definieren
			$return = array();
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			mysql_set_charset('utf8');
			
			// SQL-Befehl zurechtfuddeln,
			// die Bezeichnungen und die Daten der
			// letzten Jahresnachprüfung ermitteln
			$sql = sprintf('
				SELECT
					`wartungsplan`.`datum`,
					`wartungsplan`.`flugstunden`,
					`wartungsplan`.`landungen`
				FROM
					`wartungsplan`
				WHERE
					`wartungsplan`.`wartung` = 6 AND
					`wartungsplan`.`kennzeichen` = "%s"
				ORDER BY
					`wartungsplan`.`datum` DESC
				LIMIT 1
			',
				$kennzeichen
			);

			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// es ist ein Datensatz vorhanden
			if ($zeile = mysql_fetch_object($db_erg)) {
				// Rückgabe eines entsprechenden Daten zur Jahresnachprüfung
				$return['datum']     = fromSqlDatum($zeile->datum);
				$return['stunden']   = $zeile->flugstunden;
				$return['landungen'] = $zeile->landungen;
			} else {
				// Nullwerte zurückgeben, falls Suche keine Treffer
				$return['datum']     = '';
				$return['stunden']   = '';
				$return['landungen'] = '';
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Daten über die Jahresnachprüfung
			return $return;
		}
	}
	
	/*
	 * getTabelleWartungen()
	 *
	 * gibt eine HTML-Tabelle der letzten fünf durchgeführten
	 * Wartungen, des als Parameter übergebene Flugzeuges, zurück
	 *
	 * @params string $kennzeichen
	 * @return string $html
	 */
	if (!function_exists('getTabelleWartungen')) {
		function getTabelleWartungen($kennzeichen)
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
            // die letzten fünf Wartungen für das aktuelle Flugzeug ermitteln
            $sql = sprintf('
				SELECT
					`wartungsplan`.`id`,
					`wartungsplan`.`wartung`,
					`wartungsplan`.`datum`,
					`wartungsplan`.`flugstunden`,
					`wartungsplan`.`landungen`,
					`wartungen`.`bezeichnung`,
					`wartungsplan`.`technik`,
					`mitglieder`.`nachname`,
					`wartungsplan`.`bemerkungen`
				FROM
					`wartungsplan`
				INNER JOIN
					`wartungen` ON `wartungsplan`.`wartung` = `wartungen`.`id`
				INNER JOIN
					`mitglieder` ON `wartungsplan`.`technik` = `mitglieder`.`id`
				WHERE
					`wartungsplan`.`kennzeichen` = "%s" AND
					`wartungsplan`.`in_abrechn` = "J"
				ORDER BY
					`wartungsplan`.`datum` DESC
			',
				$kennzeichen
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);
			
			// Zählervariable initialisieren
			$i = 0;
			
			while ($zeile = mysql_fetch_object($db_erg)) {
				// Hintergrundfarbe jeder Zeile abwechseln gestalten
				$bgColor = ($i % 2) ? '#eeeeee' : '#cccccc';
				
				// neue Zeile anlegen
				$html .= sprintf('<tr bgcolor="%s" id="wartung_%d">', $bgColor, $zeile->id);
				$html .= sprintf('<td style="display:none;">%s</td>', $zeile->wartung);
				$html .= sprintf('<td align="left" valign="top" width="100" style="padding:3px 3px 3px 5px;">%s</td>', $zeile->bezeichnung);
				$html .= sprintf('<td align="left" valign="top" width="65" style="padding:3px 3px 3px 5px;">%s</td>',  fromSqlDatum($zeile->datum));
				$html .= sprintf('<td style="display:none;">%s</td>', $zeile->technik);
				$html .= sprintf('<td align="left" valign="top" width="105" style="padding:3px 3px 3px 5px;">%s</td>', $zeile->nachname);
				$html .= sprintf('<td align="left" valign="top" width="75" style="padding:3px 3px 3px 5px;">%s</td>',  $zeile->flugstunden);
				$html .= sprintf('<td align="left" valign="top" width="68" style="padding:3px 3px 3px 5px;">%s</td>',  $zeile->landungen);
				$html .= sprintf('<td align="left" valign="top" width="180" style="padding:3px 3px 3px 5px;">%s</td>', $zeile->bemerkungen);
				$html .= sprintf('<td valign="top" align="center" nowrap>');
				$html .= sprintf('<a style="cursor:pointer;" onclick="wartung_bearbeiten(%d);"><img src="./img/edit_icon.gif" border="0" title="bearbeiten" height="16" width="16" hspace="2" /></a>', $zeile->id);
				$html .= sprintf('<a style="cursor:pointer;" onclick="wartung_loeschen(%d);"><img src="./img/delmsg.png" border="0" title="l&ouml;schen" height="16" width="16" hspace="2" /></a>', $zeile->id);
				$html .= sprintf('</td>');
				$html .= sprintf('</tr>');
				
				// Zähler erhöhen
				$i++;
			}
			
			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe des aktuellen Wartungen
			return $html;
		}
	}
	
	/*
	 * neuAnlageFlugzeug()
	 *
	 * legt ein neues Flugzeug anhand der übergebenen Parameter an
	 *
	 * @params array $params
	 */
	if (!function_exists('neuAnlageFlugzeug')) {
		function neuAnlageFlugzeug(array $params)
		{
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
			// Befehl zum Speichern einer Neuanlage eines Flugzeugs
			$sql = sprintf('
				INSERT INTO
					`flugzeuge` (
						`kennzeichen`,
						`flugzeugtyp`,
						`halter`,
						`standort`,
						`fluggebuehr`,
						`motorgebuehr`,
						`startgebuehr`,
						`landegebuehr`,
						`nvfr`,
						`cvfr`,
						`vereinsflugzeug`,
						`w_kennz`,
						`typ`,
						`gastgebuehr`,
						`typ1`,
						`startart`,
						`status`,
						`bemerkungen`,
						`zelle_stunden`,
						`restzeit_motor`,
						`restzeit_prop`,
						`stand_wartung`,
						`naechste_wartung`,
						`naechste_wartung_stunden`,
						`naechste_wartung_datum`,
						`sort`
					)
					VALUES (
						%s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
						%s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
						%s, %s, %s, %s, %s, %s
					)
			',
				getDbValue(strtoupper($params['kennzeichen']),  T_STR),
				getDbValue($params['flugzeugtyp'],              T_STR),
				getDbValue($params['halter'],                   T_STR),
				getDbValue($params['standort'],                 T_STR),
				getDbValue($params['fluggebuehr'],              T_FLOAT),
				getDbValue($params['motorgebuehr'],             T_FLOAT),
				getDbValue($params['startgebuehr'],             T_FLOAT),
				getDbValue($params['landegebuehr'],             T_FLOAT),
				getDbValue($params['nvfr'],                     T_BOOL),
				getDbValue($params['cvfr'],                     T_BOOL),
				getDbValue($params['vereinsflugzeug'],          T_BOOL),
				getDbValue($params['w_kennz'],                  T_STR),
				getDbValue($params['typ'],                      T_STR),
				getDbValue($params['gastgebuehr'],              T_FLOAT),
				getDbValue($params['typ1'],                     T_STR),
				getStartarten($params),
				getDbValue($params['status'],                   T_NUMERIC),
				getDbValue($params['bemerkungen'],              T_STR),
				getDbValue($params['zelle_stunden'],            T_NUMERIC),
				getDbValue($params['restzeit_motor'],           T_NUMERIC),
				getDbValue($params['restzeit_prop'],            T_NUMERIC),
				getDbValue($params['stand_wartung'],            T_DATE),
				getDbValue($params['naechste_wartung'],         T_NUMERIC),
				getDbValue($params['naechste_wartung_stunden'], T_NUMERIC),
				getDbValue($params['naechste_wartung_datum'],   T_DATE),
				getDbValue($params['sort'],                     T_STR)
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			mysql_query($sql);
		}
	}
	
	/*
	 * updateFlugzeug()
	 *
	 * aktualisiert ein bereits vorhandenes Flugzeug anhand der
	 * übergebenen Parameter und des bestehenden Kennzeichens
	 *
	 * @params array $params
	 */
	if (!function_exists('updateFlugzeug')) {
		function updateFlugzeug(array $params)
		{
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// Ausscheidungs-Merkmal prüfen
			if (getDbValue($params['in_abrechn'], T_BOOL) == '"J"') {
				// Ausscheidungsmerkmal ist gesetzt, Flugzeug wird einfernt
				$in_abrechn = '"N"';
			} else {
				// Ausscheidungsmerkmal nicht gesetzt, Flugzeug bleibt vorhanden
				$in_abrechn = '"J"';
			}

			// SQL-Befehl zurechtfuddeln,
			// Befehl zum Speichern einer Veränderung eines Flugzeuges
			$sql = sprintf('
				UPDATE
					`flugzeuge`
				SET
					`flugzeugtyp` = %s,
					`halter` = %s,
					`standort` = %s,
					`fluggebuehr` = %s,
					`motorgebuehr` = %s,
					`startgebuehr` = %s,
					`landegebuehr` = %s,
					`nvfr` = %s,
					`cvfr` = %s,
					`vereinsflugzeug` = %s,
					`w_kennz` = %s,
					`typ` = %s,
					`gastgebuehr` = %s,
					`typ1` = %s,
					`startart` = %s,
					`status` = %s,
					`bemerkungen` = %s,
					`zelle_stunden` = %s,
					`restzeit_motor` = %s,
					`restzeit_prop` = %s,
					`stand_wartung` = %s,
					`naechste_wartung` = %s,
					`naechste_wartung_stunden` = %s,
					`naechste_wartung_datum` = %s,
					`in_abrechn` = %s,
					`sort` = %s
				WHERE
					`kennzeichen` = %s
			',
				getDbValue($params['flugzeugtyp'],              T_STR),
				getDbValue($params['halter'],                   T_STR),
				getDbValue($params['standort'],                 T_STR),
				getDbValue($params['fluggebuehr'],              T_FLOAT),
				getDbValue($params['motorgebuehr'],             T_FLOAT),
				getDbValue($params['startgebuehr'],             T_FLOAT),
				getDbValue($params['landegebuehr'],             T_FLOAT),
				getDbValue($params['nvfr'],                     T_BOOL),
				getDbValue($params['cvfr'],                     T_BOOL),
				getDbValue($params['vereinsflugzeug'],          T_BOOL),
				getDbValue($params['w_kennz'],                  T_STR),
				getDbValue($params['typ'],                      T_STR),
				getDbValue($params['gastgebuehr'],              T_FLOAT),
				getDbValue($params['typ1'],                     T_STR),
				getStartarten($params),
				getDbValue($params['status'],                   T_NUMERIC),
				getDbValue($params['bemerkungen'],              T_STR),
				getDbValue($params['zelle_stunden'],            T_NUMERIC),
				getDbValue($params['restzeit_motor'],           T_NUMERIC),
				getDbValue($params['restzeit_prop'],            T_NUMERIC),
				getDbValue($params['stand_wartung'],            T_DATE),
				getDbValue($params['naechste_wartung'],         T_NUMERIC),
				getDbValue($params['naechste_wartung_stunden'], T_NUMERIC),
				getDbValue($params['naechste_wartung_datum'],   T_DATE),
				$in_abrechn,
				getDbValue($params['sort'],                     T_STR),
				getDbValue($params['kennzeichen'],              T_STR)
			);

			// zuvor definierte SQL-Anweisung ausführen
			mysql_query($sql);
		}
	}
	
	/*
	 * getStartarten()
	 *
	 * gibt die gewählten Startarten zurück
	 *
	 * @params array  $params
	 * @return string $return
	 *
	 */
	if (!function_exists('getStartarten')) {
		function getStartarten(array $params)
		{
			// leeres Array anlegen zum speichern der angegebenen Startarten
			$startarten = array();

			// Angaben zu den eingetragenen Startarten
			if (!empty($params['starttype_w'])) $startarten[] = 'W'; /* Windenstart */
			if (!empty($params['starttype_f'])) $startarten[] = 'F'; /* F-Schlepp   */
			if (!empty($params['starttype_e'])) $startarten[] = 'E'; /* Eigenstart  */
			
			// vorhandene Startarten aufsplitten und Array in Zeichenkette konvertieren
			$startarten = implode($startarten, ',');

			// Angaben zu den Startarten als SQL-Teilbefehl darstellen
			if (!empty($startarten)) {
				return sprintf('"%s"', $startarten);
			} else {
				return 'NULL';
			}
		}
	}

	/*
	 * getFlugzeug()
	 *
	 * alle Flugzeugdaten zum ausgewählten Flugzeug werden geladen
	 *
	 * @params string $kennzeichen
	 * @return array  $data
	 */
	if (!function_exists('getFlugzeug')) {
		function getFlugzeug($kennzeichen)
		{
			// Rückgabe-Array definieren
			$data = array();
		
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
			// die Daten für das aktuell ausgewählte Flugzeug laden
			$sql = sprintf('
				SELECT
					*
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
			while ($zeile = mysql_fetch_object($db_erg)) {
				// Daten übernehmen wie hinterlegt
				$data['kennzeichen']              = utf8_encode($zeile->kennzeichen);
				$data['flugzeugtyp']              = utf8_encode($zeile->flugzeugtyp);
				$data['w_kennz']                  = utf8_encode($zeile->w_kennz);
				$data['sort']                     = utf8_encode($zeile->sort);
				$data['typ1']                     = utf8_encode($zeile->typ1);
				$data['standort']                 = utf8_encode($zeile->standort);
				$data['halter']                   = utf8_encode($zeile->halter);
				$data['status']                   = utf8_encode($zeile->status);
				$data['nvfr']                     = utf8_encode($zeile->nvfr);
				$data['cvfr']                     = utf8_encode($zeile->cvfr);
				$data['vereinsflugzeug']          = utf8_encode($zeile->vereinsflugzeug);
				$data['bemerkungen']              = utf8_encode($zeile->bemerkungen);
				$data['fluggebuehr']              = utf8_encode(number_format($zeile->fluggebuehr,  2, ',', ''));
				$data['motorgebuehr']             = utf8_encode(number_format($zeile->motorgebuehr, 2, ',', ''));
				$data['gastgebuehr']              = utf8_encode(number_format($zeile->gastgebuehr,  2, ',', ''));
				$data['startgebuehr']             = utf8_encode(number_format($zeile->startgebuehr, 2, ',', ''));
				$data['landegebuehr']             = utf8_encode(number_format($zeile->landegebuehr, 2, ',', ''));
				$data['startart']                 = utf8_encode($zeile->startart);
				$data['typ']                      = utf8_encode($zeile->typ);
				$data['zelle_stunden']            = utf8_encode($zeile->zelle_stunden);
				$data['restzeit_motor']           = utf8_encode($zeile->restzeit_motor);
				$data['restzeit_prop']            = utf8_encode($zeile->restzeit_prop);
				$data['stand_wartung']            = utf8_encode(fromSqlDatum($zeile->stand_wartung));
				$data['naechste_wartung']         = utf8_encode($zeile->naechste_wartung);
				$data['naechste_wartung_stunden'] = utf8_encode($zeile->naechste_wartung_stunden);
				$data['naechste_wartung_datum']   = utf8_encode(fromSqlDatum($zeile->naechste_wartung_datum));
				
				// Daten der letzten Jahresnachprüfung ermitteln
				$letzte_jnp = getLetzteJNP($zeile->kennzeichen);
				// Daten der letzten Jahresnachprüfung in die Rückgabevariablen schreiben
				$data['letzte_jnp']    = $letzte_jnp['datum'];
				$data['jnp_stunden']   = $letzte_jnp['stunden'];
				$data['jnp_landungen'] = $letzte_jnp['landungen'];
				
				// Bild holen
				// das Bild muss existent sein
				if (file_exists(sprintf('./planepics/%s.jpg', md5($data['kennzeichen'])))) {
					// Bild laden
					$data['planepic_img'] = sprintf('%s.jpg', md5($data['kennzeichen']));
				} else {
					// Bild wieder auf das Dummy-Pic zurücksetzen
					$data['planepic_img'] = '_dummy_plane.jpg';
				}
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Flugzeugdaten
			return $data;
		}
	}
	

	/**************************************************************************************************************************/
	/* ------------------------------------- BEGINN : FLUGZEUG SPEICHERN NACH GET-BEFEHL ------------------------------------ */
	/**************************************************************************************************************************/
	// Array anlegen für die Feldinhalte
	$data = array();
	
	if (isset($_GET['action']) && $_GET['action'] == 'speichern') {
		// es wird geprüft, ob es sich um eine Neuanlage eines bestehenden
		// Flugzeuges handelt, oder ob ein neuer Datensatz angelegt werden soll
		if ($_GET['kennzeichen'] == 'neu') {
			// Neuanlage eines Flugzeuges
			neuAnlageFlugzeug($_POST);
		} else {
			// Bearbeiten eines bestehenden Datensatzes
			updateFlugzeug($_POST);
		}
		
		// zurück zur normalen Flugzeugliste
		echo '<script language="javascript" type="text/javascript">';
		echo 'window.location.href = "flugzeuge.php"';
		echo '</script>';

		// sicher stellen, dass der nachfolgende Code nicht
		// ausgefuehrt wird, wenn eine Umleitung stattfindet.
		exit();
	} else {
		// Feldinhalte laden, falls ein Flugzeug ausgewählt wurde
		if ($_GET['kennzeichen'] != 'neu') {
			$data = getFlugzeug($_GET['kennzeichen']);
		}
	}
	/**************************************************************************************************************************/
	/* ------------------------------------- ENDE : FLUGZEUG SPEICHERN NACH GET-BEFEHL -------------------------------------- */
	/**************************************************************************************************************************/
	
?>
<!-- ENDE: SKRIPT -->
<!-- BEGINN: AUSGABE -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

    <head>

        <title>Flugzeug speichern</title>

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="Content-Script-Type" content="text/javascript" />
        <meta http-equiv="Content-Style-Type" content="text/css" />
        <meta http-equiv="content-language" content="de" />
        <meta name="author" content="Benjamin Stopfkuchen" />
		
		<script type="text/javascript" src="./js/jquery-1.10.2.js"></script>
		<script type="text/javascript" src="./js/jquery-1.10.2.min.js"></script>
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.js"></script>
		<script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
		<script type="text/javascript" src="./js/functions.js"></script>

		<script type="text/javascript" src="./js/fancybox-1.3.4/jquery.fancybox-1.3.4.js"></script>
		<script type="text/javascript" src="./js/fancybox-1.3.4/jquery.mousewheel-3.0.4.pack.js"></script>
		<script type="text/javascript" src="./js/fancybox-1.3.4/jquery.fancybox-1.3.4.pack.js"></script>
		
		<link rel="Stylesheet" type="text/css" href="./js/fancybox-1.3.4/jquery.fancybox-1.3.4.css" media="screen" />
		<link rel="Stylesheet" type="text/css" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
		<link rel="Stylesheet" type="text/css" href="./css/stylish.css" media="screen" />
	
		<script type="text/javascript" language="JavaScript">
		<!--
		
			function pruefeFlugzeugKennzeichen(kennzeichen)
			{
				// Rückgabevariable initialisieren
				var ret = false;

				// Aufruf per AJAX an das PHP-Modul, welches die
				// Funktion zum Prüfen vorhandener Flugzeugkennzeichen enthält
				$.ajax({
					url: 'ajax_lfz_kennzeichen_pruefen.php',
					type: 'POST',
					async: false,  
					data: { kennzeichen : kennzeichen }
				}).done(function(msg) {
					// es wird geprüft, ob das Flugzeugkennzeichen bereits vergebenen wurde
					if (msg == 1) {
						// Flugzeugkennzeichen bereits vorhanden
						ret = true;
					} else {
						// Flugzeugkennzeichen noch frei
						ret = false;
					}
				});
				
				// Rückgabe ob Flugzeugkennzeichen vorhanden
				return ret;
			}
			
			function wartung_loeschen(id)
			{
				var dlgBreite = 375;
				var dlgHoehe  = 210;
			
				var x = (window.innerWidth  / 2) - dlgBreite;
				var y = (window.innerHeight / 2) - dlgHoehe;

				$('<div id="dialog-confirm" title="Ausgew&auml;hltes Wartung wirklich l&ouml;schen?">' +
					'<img src="./img/QuestionIcon.jpg" align="left" height="64" style="margin-right: 10px;" />' +
					'<p style="font-size: 9pt !important; line-height: 150% !important;">' +
					'Bist Du sicher, dass die ausgew&auml;hlte Wartung wirklich gel&ouml;scht werden soll?</p></div>'
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

							// Variable erzeugen zur Übergabe der Parameter
							var data = new FormData();

							// zusätzlicher Parameter wird angehängt
							// -> ID der Wartung
							data.append('id', id);
							
							// Aufruf per AJAX an das PHP-Modul, welches
							// die Funktion zum Hochladen der Datei enthält
							$.ajax({
								url: 'ajax_wartung_loeschen.php',
								data: data,
								type: 'POST',
								processData: false,
								contentType: false,
								success: function(data) {
									var bgColor_alt = $('#wartung_' + id).css('background-color');
									var next_obj = $('#wartung_' + id).next();
									
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
									$('#wartung_' + id).remove();
								}
							})
						},
						'Nein' : function() {
							// Meldungsfenster wieder schließen
							$(this).dialog('close');
						}
					}
				});
			}
			
			function wartung_bearbeiten(id)
			{
				// die Daten werden aus der aktuell gewählten Tabellenzeile geladen
				var wartung   = $('#wartung_' + id).find('td').first().text();
				var datum     = $('#wartung_' + id).find('td').first().next().next().text();
				var technik   = $('#wartung_' + id).find('td').first().next().next().next().text();
				var stunden   = $('#wartung_' + id).find('td').first().next().next().next().next().next().text();
				var landungen = $('#wartung_' + id).find('td').first().next().next().next().next().next().next().text();
				var bemerkung = $('#wartung_' + id).find('td').first().next().next().next().next().next().next().next().text();

				// Eingabe einblenden
				$('a[href="#inline1"]').click();
			
				// Beschriftungen entsprechend der Eingabe anpassen
				$('#inline_wartung_h3').html('Wartung bearbeiten');
				$('#wartung_neu_add').css('display', 'none');
				$('#wartung_neu_edit').css('display', 'inline');
				
				// Art der Wartung
				$('#wartung_neu option:selected').prop('selected', false);
				$('#wartung_neu option[value="' + wartung + '"]').prop('selected', 'selected');

				// durchgeführt von
				$('#wartung_neu_technik option:selected').prop('selected', false);
				$('#wartung_neu_technik option[value="' + technik + '"]').prop('selected', 'selected');

				// alle weiteren Angaben übernehmen
				$('#wartung_neu_datum').val(datum);         // durchgeführt am
				$('#wartung_neu_stunden').val(stunden);     // Flugstunden
				$('#wartung_neu_landungen').val(landungen); // Landungen
				$('#wartung_neu_bemerkung').val(bemerkung); // Bemerkungen
				$('#wartung_neu_id').val(id);               // ID
			}

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
				$(this).ajaxStart(function() {
					$('body').append('<div id="overlay"><img id="ladegrafik" src="./img/img_ajax_ladegrafik.gif" /></div>');
					
					$('#overlay').css('top',    '0px');
					$('#overlay').css('left',   '0px');
					$('#overlay').css('width',  (parseInt($('body').width())));
					$('#overlay').css('height', (parseInt($('body').height())));

					$('#ladegrafik').css('position', 'absolute');
					$('#ladegrafik').css('left', (parseInt($('body').width()) / 2)  - 66);
					$('#ladegrafik').css('top',  (parseInt($(window).height()) / 2) + $('#fehlermeldung').height());
				});

				$(this).ajaxStop(function() {
					$('#overlay').remove();
				});
			
				// Initialisierung der Karteireiter
				// -> jQuery-UI-Komponente Tabs
				$('#tabs').tabs();
				// -> jQuery-UI-Komponente Tooltip
				$('#typ-zuweisung').tooltip({
					track: true,
					content: function() {
						var tooltip  = '<div id="info_box">';
							tooltip += '<table style="font-size:9pt;">';
							tooltip += '<tr><th width="35" align="left">1sp</th><td>Segelflugzeug <small>(1-sitzig, privat)</small></td></tr>';
							tooltip += '<tr><th width="35" align="left">1sv</th><td>Segelflugzeug <small>(1-sitzig, Verein)</small></td></tr>';
							tooltip += '<tr><th width="35" align="left">2sp</th><td>Segelflugzeug <small>(2-sitzig, privat)</small></td></tr>';
							tooltip += '<tr><th width="35" align="left">2sv</th><td>Segelflugzeug <small>(2-sitzig, Verein)</small></td></tr>';
							tooltip += '<tr><th width="35" align="left">Dip</th><td>Dimona <small>(privat)</small></td></tr>';
							tooltip += '<tr><th width="35" align="left">Div</th><td>Dimona <small>(Verein)</small></td></tr>';
							tooltip += '<tr><th width="35" align="left">Fap</th><td>Falke <small>(privat)</small></td></tr>';
							tooltip += '<tr><th width="35" align="left">Fav</th><td>Falke <small>(Verein)</small></td></tr>';
							tooltip += '<tr><th width="35" align="left">Ulp</th><td>Ultraleichtflugzeug <small>(privat)</small></td></tr>';
							tooltip += '<tr><th width="35" align="left">Ulv</th><td>Ultraleichtflugzeug <small>(Verein)</small></td></tr>';
							tooltip += '</table>';
							tooltip += '</div>';
					
						return tooltip;
					}
				});
				// -> jQuery-UI-Komponente Fancybox
				$('#inline_wartung').fancybox({
					'titlePosition'	: 'inside'
				});
				
				$('#inline_wartung').click(function() {
					$('#inline_wartung_h3').html('Neue Wartung anlegen');
					$('#wartung_neu_edit').css('display', 'none');
					$('#wartung_neu_add').css('display', 'inline');
				});
				
				$('#stand_wartung').datepicker({ dateFormat: 'dd.mm.yy' });
				$('#naechste_wartung_datum').datepicker({ dateFormat: 'dd.mm.yy' });
				$('#wartung_neu_datum').datepicker({ dateFormat: 'dd.mm.yy' });
				
				$('#bilddatei').click(function() {
					// es muss ein Kennzeichen ausgewählt sein
					if ($('#kennzeichen').val().trim() != '') {
						// öffnet den Dialog zur Dateiauswahl
						$('input:file').click();
					}
				});
				
				$('input:file').change(function() {
					// lädt den Namen der ausgewählten Bilddatei in
					// das zugehörige Textfeld zur weiteren Verwendung
					$('#bilddatei').val($('input:file').val());
				});

				$('#bilddatei_upload').click(function() {
					if ($('#bilddatei').val().trim() != '') {
						// Variable erzeugen zur Übergabe der Parameter
						var data = new FormData();

						// die hochzuladende Datei per Parameter anhängen
						data.append('upload', $('input:file')[0].files[0]);
						// zusätzlicher Parameter wird angehängt
						// -> Flugzeug-Kennzeichen
						data.append('id', $('#kennzeichen').val());
						// das Zielverzeichnis festlegen
						data.append('dir', 'planepics');
						
						// Aufruf per AJAX an das PHP-Modul, welches
						// die Funktion zum Hochladen der Datei enthält
						$.ajax({
							url: 'ajax_file_upload.php',
							data: data,
							type: 'POST',
							processData: false,
							contentType: false,
							success: function(data) {
								// Rückgabe-Daten per JSON auslesen
								var json = $.parseJSON(data);
							
								// Ergebnis prüfen, ob das Bild hochgeladen wurde
								if (json.result == true) {
									// alles gut, Bild wurde hochgeladen und kann
									// nun in der Anzeige rechts angezeigt werden
									$('#planepic_img').attr('src', json.image);
									// Feldinhalte entleeren
									$('#bilddatei').val('');
								}
							}
						});
					}
				});
				
				$('#bilddatei_delete').click(function() {
					// Pfad und Dateiname zum Bild holen
					var planepic_img = $('#planepic_img').attr('src');
				
					// nur Löschen wenn es sich um KEIN Dummy-Bild handelt
					if (planepic_img.indexOf('dummy') == -1) {
						// das aktuelle Benutzerfoto kann gelöscht werden
						// AJAX ausführen
						if (navigator.appName == "Microsoft Internet Explorer") {
							xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
						} else {
							xmlHttp = new XMLHttpRequest();
						}
					
						xmlHttp.open('get', 'ajax_file_delete.php?img=' + planepic_img);

						xmlHttp.onreadystatechange = function()
						{
							if (xmlHttp.readyState == 4) {
								// Bild wieder auf das Dummy-Piczurücksetzen
								$('#planepic_img').attr('src', './planepics/_dummy_plane.jpg');
							}
						}

						xmlHttp.send(null);
					}
				});
				
				$('#wartung_neu_add').click(function() {
					// Variable erzeugen zur Übergabe der Parameter
					var data = new FormData();

					// zusätzliche Parameter werden angehängt
					data.append('kennzeichen', $('#kennzeichen').val());           // Flugzeug
					data.append('wartung',     $('#wartung_neu').val());           // Art der Wartung
					data.append('datum',       $('#wartung_neu_datum').val());     // durchgeführt am
					data.append('flugstunden', $('#wartung_neu_stunden').val());   // Flugstunden
					data.append('landungen',   $('#wartung_neu_landungen').val()); // Landungen
					data.append('technik',     $('#wartung_neu_technik').val());   // durchgeführt von
					data.append('bemerkungen', $('#wartung_neu_bemerkung').val()); // Bemerkungen
					
					// Aufruf per AJAX an das PHP-Modul, welches
					// die Funktion zum Hochladen der Datei enthält
					$.ajax({
						url: 'ajax_wartung_neuanlage.php',
						data: data,
						type: 'POST',
						processData: false,
						contentType: false,
						success: function(data) {
							// Rückgabe-Daten per JSON auslesen
							var json = $.parseJSON(data);
							// Prüfvariable festlegen, ob bereits ein Datensatz vorhanden ist
							var i = 0;

							// Ergebnis prüfen, ob der Datensatz angelegt wurde
							if (json.id != 0) {
								$('#wartungstabelle').find('tr').each(function(index) {
									// Prüfvariable setzen, da Datensatz vorhanden
									i++;
								
									var datum_alt = $(this).find('td').first().next().next().text();
									var datum_neu = $('#wartung_neu_datum').val();

									// Datum umbauen ins Format JJJJMMTT
									datum_alt = datum_alt.split('.');
									datum_neu = datum_neu.split('.');
									datum_alt = datum_alt[2] + datum_alt[1] + datum_alt[0];
									datum_neu = datum_neu[2] + datum_neu[1] + datum_neu[0];
									
									// anhand des Datums prüfen, an welcher Stelle die neue Zeile eingefügt werden soll
									if (datum_alt <= datum_neu) {
										// neue Zeile definieren
										var html  = '<td style="display:none;">' + $('#wartung_neu option:selected').val() + '</td>';
											html += '<td align="left" valign="top" width="100" style="padding:3px 3px 3px 5px;">' + $('#wartung_neu option:selected').text()         + '</td>';
											html += '<td align="left" valign="top" width="65" style="padding:3px 3px 3px 5px;">'  + $('#wartung_neu_datum').val()                    + '</td>';
											html += '<td style="display:none;">' + $('#wartung_neu_technik option:selected').val() + '</td>';
											html += '<td align="left" valign="top" width="105" style="padding:3px 3px 3px 5px;">' + $('#wartung_neu_technik option:selected').text() + '</td>';
											html += '<td align="left" valign="top" width="75" style="padding:3px 3px 3px 5px;">'  + $('#wartung_neu_stunden').val()                  + '</td>';
											html += '<td align="left" valign="top" width="68" style="padding:3px 3px 3px 5px;">'  + $('#wartung_neu_landungen').val()                + '</td>';
											html += '<td align="left" valign="top" width="180" style="padding:3px 3px 3px 5px;">' + $('#wartung_neu_bemerkung').val()                + '</td>';
											html += '<td valign="top" align="center" nowrap>';
											html += '<a style="cursor:pointer;" onclick="wartung_bearbeiten(' + json.id + ');"><img src="./img/edit_icon.gif" border="0" title="bearbeiten" height="16" width="16" hspace="2" /></a>';
											html += '<a style="cursor:pointer;" onclick="wartung_loeschen('   + json.id + ');"><img src="./img/delmsg.png" border="0" title="l&ouml;schen" height="16" width="16" hspace="2" /></a>';
											html += '</td>';
									
										// neue Zeile hinzufügen
										$(this).before('<tr bgcolor="#eeeeee" id="wartung_' + json.id + '">' + html + '</tr>');

										// Schleife abbrechen, wenn neue Zeile hinzugefügt
										return false;
									}
								});
								
								if (i == 0) {
									// neue Zeile definieren, der erste Eintrag in der Tabelle
									var html  = '<td style="display:none;">' + $('#wartung_neu option:selected').val() + '</td>';
										html += '<td align="left" valign="top" width="100" style="padding:3px 3px 3px 5px;">' + $('#wartung_neu option:selected').text()         + '</td>';
										html += '<td align="left" valign="top" width="65" style="padding:3px 3px 3px 5px;">'  + $('#wartung_neu_datum').val()                    + '</td>';
										html += '<td style="display:none;">' + $('#wartung_neu_technik option:selected').val() + '</td>';
										html += '<td align="left" valign="top" width="105" style="padding:3px 3px 3px 5px;">' + $('#wartung_neu_technik option:selected').text() + '</td>';
										html += '<td align="left" valign="top" width="75" style="padding:3px 3px 3px 5px;">'  + $('#wartung_neu_stunden').val()                  + '</td>';
										html += '<td align="left" valign="top" width="68" style="padding:3px 3px 3px 5px;">'  + $('#wartung_neu_landungen').val()                + '</td>';
										html += '<td align="left" valign="top" width="180" style="padding:3px 3px 3px 5px;">' + $('#wartung_neu_bemerkung').val()                + '</td>';
										html += '<td valign="top" align="center" nowrap>';
										html += '<a style="cursor:pointer;" onclick="wartung_bearbeiten(' + json.id + ');"><img src="./img/edit_icon.gif" border="0" title="bearbeiten" height="16" width="16" hspace="2" /></a>';
										html += '<a style="cursor:pointer;" onclick="wartung_loeschen('   + json.id + ');"><img src="./img/delmsg.png" border="0" title="l&ouml;schen" height="16" width="16" hspace="2" /></a>';
										html += '</td>';
										
									// neue Zeile hinzufügen
									$('#wartungstabelle').append('<tr bgcolor="#eeeeee" id="wartung_' + json.id + '">' + html + '</tr>');
								}
								
								// Hintergrundfarbe für erste Zeile neu festlegen
								var bgColor = '#cccccc';

								// Hintergrundfarben der Zeilen neu setzen
								$('#wartungstabelle').find('tr').each(function(index) {
									// Hintergrundfarbe setzen
									$(this).css('background-color', bgColor);
									
									// nächste Hintergrundfarbe für nächste Zeile neu festlegen
									if (bgColor == '#cccccc') {
										// dunkleres Grau als Hintergrundfarbe
										bgColor = '#eeeeee';
									} else {
										// helleres Grau als Hintergrundfarbe
										bgColor = '#cccccc';
									}
								});
							}
						}
					});
					
					// Eingabefenster schließen
					$.fancybox.close();
				});
				
				$('#wartung_neu_edit').click(function() {
					// Variable erzeugen zur Übergabe der Parameter
					var data = new FormData();

					// zusätzliche Parameter werden angehängt
					data.append('id',          $('#wartung_neu_id').val());        // Wartung ID
					data.append('wartung',     $('#wartung_neu').val());           // Art der Wartung
					data.append('datum',       $('#wartung_neu_datum').val());     // durchgeführt am
					data.append('flugstunden', $('#wartung_neu_stunden').val());   // Flugstunden
					data.append('landungen',   $('#wartung_neu_landungen').val()); // Landungen
					data.append('technik',     $('#wartung_neu_technik').val());   // durchgeführt von
					data.append('bemerkungen', $('#wartung_neu_bemerkung').val()); // Bemerkungen
					
					var id = $('#wartung_neu_id').val();
					
					// Aufruf per AJAX an das PHP-Modul, welches
					// die Funktion zum Hochladen der Datei enthält
					$.ajax({
						url: 'ajax_wartung_bearbeiten.php',
						data: data,
						type: 'POST',
						processData: false,
						contentType: false,
						success: function(data) {
							// Daten in die ausgewählte Zeile schreiben
							$('#wartung_' + id).find('td').first().text($('#wartung_neu option:selected').val());
							$('#wartung_' + id).find('td').first().next().text($('#wartung_neu option:selected').text());
							$('#wartung_' + id).find('td').first().next().next().text($('#wartung_neu_datum').val());
							$('#wartung_' + id).find('td').first().next().next().next().text($('#wartung_neu_technik option:selected').val());
							$('#wartung_' + id).find('td').first().next().next().next().next().text($('#wartung_neu_technik option:selected').text());
							$('#wartung_' + id).find('td').first().next().next().next().next().next().text($('#wartung_neu_stunden').val());
							$('#wartung_' + id).find('td').first().next().next().next().next().next().next().text($('#wartung_neu_landungen').val());
							$('#wartung_' + id).find('td').first().next().next().next().next().next().next().next().text($('#wartung_neu_bemerkung').val());
						
							// Eingabefenster schließen
							$.fancybox.close();
							
							// Zeitverzögerung einbauen
							if (sleep(500)) {
								$('#inline_wartung_h3').html('Neue Wartung anlegen');
								$('#wartung_neu_edit').css('display', 'none');
								$('#wartung_neu_add').css('display', 'display');
							}
							
							// Alle Feldinhalte zurücksetzen
							// Art der Wartung
							$('#wartung_neu option:selected').prop('selected', false);
							$('#wartung_neu option:first').prop('selected', 'selected');
							// durchgeführt von
							$('#wartung_neu_technik option:selected').prop('selected', false);
							$('#wartung_neu_technik option:first').prop('selected', 'selected');
							$('#wartung_neu_datum').val(getLocaleDate()); // durchgeführt am
							$('#wartung_neu_stunden').val('0');           // Flugstunden
							$('#wartung_neu_landungen').val('0');         // Landungen
							$('#wartung_neu_bemerkung').val('');          // Bemerkungen
						}
					});
				});
				
				$('#wartung_neu_cancel').click(function() {
					// Eingabefenster schließen
					$.fancybox.close();
					
					// Zeitverzögerung einbauen
					if (sleep(500)) {
						$('#inline_wartung_h3').html('Neue Wartung anlegen');
						$('#wartung_neu_edit').css('display', 'none');
						$('#wartung_neu_add').css('display', 'display');
					}
					
					// Alle Feldinhalte zurücksetzen
					// Art der Wartung
					$('#wartung_neu option:selected').prop('selected', false);
					$('#wartung_neu option:first').prop('selected', 'selected');
					// durchgeführt von
					$('#wartung_neu_technik option:selected').prop('selected', false);
					$('#wartung_neu_technik option:first').prop('selected', 'selected');
					$('#wartung_neu_datum').val(getLocaleDate()); // durchgeführt am
					$('#wartung_neu_stunden').val('0');           // Flugstunden
					$('#wartung_neu_landungen').val('0');         // Landungen
					$('#wartung_neu_bemerkung').val('');          // Bemerkungen
				});
				
				$('#flugzeug_speichern').click(function() {
					// Variable für die Fehlermeldung anlegen
					var error_msg = '';

					if (getParam('kennzeichen') == 'neu') {
						// zu allererst prüfen, ob alle Pflichtfelder korrekt ausgefüllt wurden
						// prüfen ob ein Flugzeugkennzeichen angegeben wurde
						if ($('#kennzeichen').val().trim() == '') {
							// es wurde kein Kennzeichen angegeben
							error_msg  = 'Ein von Dir eingegebenes Feld ist entweder leer oder fehlerhaft.<br />';
							error_msg += 'Bitte noch einmal versuchen, und diesmal ein richtiges Flugzeug-Kennzeichen angeben!';
							
							// das entsprechende Feld Kennzeichen als Fehler markieren
							$('#kennzeichen').removeAttr('class').addClass('error_line');
						} else if (pruefeFlugzeugKennzeichen($('#kennzeichen').val())) {
							// das eingegebene Kennzeichen ist bereits vergeben
							error_msg  = 'Das von Dir eingegebene Kennzeichen ist bereits einem anderen Flugzeug zugeordnet und kann daher nicht verwendet werden.<br />';
							error_msg += 'Bitte noch einmal versuchen, und diesmal ein richtiges Flugzeug-Kennzeichen angeben!';
							
							// das entsprechende Feld Kennzeichen als Fehler markieren
							$('#kennzeichen').removeAttr('class').addClass('error_line');
						} else {
							// die normale Klasse des Feld Kennzeichen wiederherstellen
							$('#kennzeichen').removeAttr('class').addClass('flugzeug_anlegen');
						}
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
				
				$('#flugzeug_cancel').click(function() {
					// die Fehlermeldung wird ausgeblendet, falls diese vorher bereits eingeblendet war
					if ($('#fehlermeldung').css('display') == 'inline') {
						// Fehlermeldung ausblenden, falls bereits da
						$('#fehlermeldung').css('display', 'none');
					}

					// alles Bestens, keine Fehler
					// alle bisherigen Formatierungen der Felder wurden zurückgesetzt
					return true;
				});
			});
		
		//-->
		</script>
	
    </head>

	<body style="margin-top: 0px; margin-left: 0px;">
	
		<table style="border: 1px solid #000000; background-color: #f7f7f7;" width="100%" height="100%">
			<tr>
				<td valign="top" style="padding:20px;">
				
					<?php if (isset($_GET['kennzeichen'])) : ?>
						<?php if ($_GET['kennzeichen'] == 'neu') : ?>
							<!-- Flugzeug NEUANLAGE -->
							<!-- Seitenüberschrift einfügen -->
							<h2>Flugzeug anlegen</h2>
							<!-- Informationstext einfügen -->
							<div class="helpline">
								Hier kannst Du ein neues Flugzeug anlegen und die zugeh&ouml;rigen Daten eingeben.
								Je Flugzeug k&ouml;nnen Kennzeichen, Bezeichnung, Halter, Eigent&uuml;mer,
								Flugzeugtyp und Flugzeugpreise verwaltet werden.
							</div>
						<?php else : ?>
							<!-- Flugzeug BEARBEITEN -->
							<!-- Seitenüberschrift einfügen -->
							<h2>Flugzeug bearbeiten</h2>
							<!-- Informationstext einfügen -->
							<div class="helpline">
								Hier kannst Du die zugeh&ouml;rigen Daten des ausgew&auml;hlten Flugzeug &auml;ndern und erg&auml;nzen.
								Je Flugzeug k&ouml;nnen Kennzeichen, Bezeichnung, Halter, Eigent&uuml;mer,
								Flugzeugtyp und Flugzeugpreise verwaltet werden.
							</div>
						<?php endif; ?>
					<?php endif; ?>
				
					<br />
					
					<!-- Fehlermeldung -->
					<div id="fehlermeldung" style="display: none;">
						<div class="errorline"></div><br />
					</div>
					<!-- Fehlermeldung -->

					<form action="flugzeuge_edit.php?kennzeichen=<?php if (isset($_GET['kennzeichen'])) { echo $_GET['kennzeichen']; } ?>&action=speichern" method="POST">
					
						<div id="tabs" style="height: 845px;">
							<ul>
								<li style="font-size: 10pt; font-family: Tahoma, Sans-Serif;"><a href="#tabs-1">Allgemeine Daten</a></li>
								<li style="font-size: 10pt; font-family: Tahoma, Sans-Serif;"><a href="#tabs-2">Flugbetrieb / Wartung</a></li>
							</ul>
							<div id="tabs-1">
								<fieldset style="width: 97%; background-color: #eeeeee; margin-top: 10px;">
									<legend style="font-size: 11pt;"><img src="./img/cam_icon.gif" align="left" hspace="5" /> Bild</legend>
									
									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd">
											<td valign="middle" align="left">
												<img width="600" height="240" hspace="5" vspace="5" style="border: 1px solid #333333;" id="planepic_img" name="planepic_img" src="./planepics/<?php if (isset($data['planepic_img'])) { echo $data['planepic_img']; } else { echo '_dummy_plane.jpg'; } ?>">
												<input type="hidden" value="<?php if (isset($data['planepic_img'])) { echo $data['planepic_img']; } ?>" name="planepic" id="planepic">
											</td>
										</tr>
										<tr bgcolor="#eeeeee">
											<td valign="top">
												<table cellspacing="5" cellpadding="0" border="0">
													<tr>
														<th width="145" valign="middle" style="font-size: 9pt;"><label for="bilddatei">Bild ausw&auml;hlen</label></th>
														<td width="310" valign="middle">
															<input type="file" style="display:none;position:absolute;" name="dateiauswahl_upload" id="dateiauswahl_upload" />
															<input type="text" tabindex="1" maxlength="255" style="font-size:9pt;width:450px;" class="flugzeug_anlegen" name="bilddatei" id="bilddatei" readonly="readonly" />
														</td>
													</tr>
													<tr>
														<th width="145"></th>
														<td width="310" valign="middle">
															<button type="button" tabindex="2" id="bilddatei_upload" name="bilddatei_upload" style="width: 147px; height: 23px;">Aktualisieren</button>
															<button type="button" tabindex="3" id="bilddatei_delete" name="bilddatei_delete" style="width: 147px; height: 23px;">Bild l&ouml;schen</button>
														</td>
													</tr>
													<tr height="30">
														<th width="145"></th>
														<td width="310" valign="bottom" style="font-size: 7pt; color: #ff0000;">
															Hinweis: maximale Dateigr&ouml;&szlig;e = 128 kb,<br>Dateityp = .jpg oder .png
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</fieldset>
								
								<fieldset style="width: 97%; background-color: #eeeeee; margin-top: 10px;">
									<legend style="font-size: 11pt;"><img src="./img/plane-16.png" align="left" hspace="5" /> Flugzeugdaten</legend>
									
									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd">
											<th align="left" width="145" style="padding-left: 8px;"><label for="kennzeichen">Kennzeichen: <span class="pflichtfeld">*)</span></label></th>
											<td width="200">
												<?php if (isset($data['kennzeichen'])) : ?>
													<input type="text" name="kennzeichen" id="kennzeichen" maxlength="10" tabindex="4" value="<?php if (isset($data['kennzeichen'])) { echo $data['kennzeichen']; } ?>" class="flugzeug_anlegen_gesperrt" style="width: 150px; text-transform: uppercase" readonly="readonly" onfocus="this.blur();" />
													<img style="position: relative; left: 0px; top: 2px;" title="Prim&auml;rschl&uuml;ssel => Kennzeichen" src="./img/1351092510_key.png">
												<?php else : ?>
													<input type="text" name="kennzeichen" id="kennzeichen" maxlength="10" tabindex="4" value="<?php if (isset($data['kennzeichen'])) { echo $data['kennzeichen']; } ?>" class="flugzeug_anlegen" style="width: 150px; text-transform: uppercase" />
													<img style="position: relative; left: 0px; top: 2px;" title="Prim&auml;rschl&uuml;ssel => Kennzeichen" src="./img/1351092510_key.png">
												<?php endif; ?>
											</td>
											<th align="left" width="90" style="padding-left: 8px;"><label for="typ1">Kategorie:</label></th>
											<td>
												<select name="typ1" id="typ1" tabindex="8" class="flugzeug_anlegen" style="width: 240px;">
													<?php if (isset($data['typ1'])) { echo getListeFlugzeugklasse($data['typ1']); } else { echo getListeFlugzeugklasse(); } ?>
												</select>
											</td>
										</tr>
										<tr bgcolor="#eeeeee">
											<th align="left" width="145" style="padding-left: 8px;"><label for="flugzeugtyp">Flugzeugtyp:</label></th>
											<td width="200"><input type="text" name="flugzeugtyp" id="flugzeugtyp" value="<?php if (isset($data['flugzeugtyp'])) { echo $data['flugzeugtyp']; } ?>" maxlength="20" tabindex="5" class="flugzeug_anlegen" style="width: 150px;" /></td>
											<th align="left" width="90" style="padding-left: 8px;"><label for="standort">Standort:</label></th>
											<td>
												<select name="standort" id="standort" tabindex="9" class="flugzeug_anlegen" style="width: 240px;">
													<?php if (isset($data['standort'])) { echo getListeFlugplaetze($data['standort']); } else { echo getListeFlugplaetze(); } ?>
												</select>
											</td>
										</tr>
										<tr bgcolor="#dddddd">
											<th align="left" width="145" style="padding-left: 8px;"><label for="w_kennz">WB-Kennzeichen:</label></th>
											<td width="200"><input type="text" name="w_kennz" id="w_kennz" value="<?php if (isset($data['w_kennz'])) { echo $data['w_kennz']; } ?>" maxlength="3" tabindex="6" class="flugzeug_anlegen" style="width: 150px;" /></td>
											<th align="left" width="90" style="padding-left: 8px;"><label for="halter">Halter:</label></th>
											<td><input type="text" name="halter" id="halter" value="<?php if (isset($data['halter'])) { echo $data['halter']; } ?>" maxlength="50" tabindex="10" class="flugzeug_anlegen" style="width: 240px;" /></td>
										</tr>
										<tr bgcolor="#eeeeee">
											<th align="left" width="145" style="padding-left: 8px;"><label for="sort">Sort:</label></th>
											<td width="200"><input type="text" name="sort" id="sort" value="<?php if (isset($data['sort'])) { echo $data['sort']; } ?>" maxlength="1" tabindex="7" class="flugzeug_anlegen" style="width: 150px;" /></td>
											<th align="left" width="90" style="padding-left: 8px;"><label for="status">Status:</label></th>
											<td>
												<select name="status" id="status" tabindex="11" class="flugzeug_anlegen" style="width: 240px;">
													<?php if (isset($data['status'])) { echo getListeFlugzeugstatus($data['status']); } else { echo getListeFlugzeugstatus(); } ?>
												</select>
											</td>
										</tr>
									</table>
									
									<hr />
									
									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd" height="28">
											<th align="left" width="180" style="padding-left: 8px;"><label for="nvfr">F&uuml;r Nachtflug ausger&uuml;stet:</label></th>
											<td width="50"><input type="checkbox" name="nvfr" id="nvfr" tabindex="12" <?php if (isset($data['nvfr']) && ($data['nvfr'] == 'J')) { echo 'checked="checked"'; } ?>/></td>
											<th align="right" width="180" style="padding-left: 8px;"><label for="cvfr">F&uuml;r CVFR ausger&uuml;stet:</label></th>
											<td width="50"><input type="checkbox" name="cvfr" id="cvfr" tabindex="13" <?php if (isset($data['cvfr']) && ($data['cvfr'] == 'J')) { echo 'checked="checked"'; } ?>/></td>
											<th align="right" width="180" style="padding-left: 8px;"><label for="vereinsflugzeug">Vereinsflugzeug:</label></th>
											<td><input type="checkbox" name="vereinsflugzeug" id="vereinsflugzeug" tabindex="14" <?php if (isset($data['vereinsflugzeug']) && ($data['vereinsflugzeug'] == 'J')) { echo 'checked="checked"'; } ?>/></td>
										</tr>
									</table>
									
									<hr />
									
									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#eeeeee">
											<th align="left" width="145" valign="top" style="padding-top: 10px; padding-left: 8px;"><label for="bemerkungen">Bemerkung(en):</label></th>
											<td width="600"><textarea name="bemerkungen" id="bemerkungen" maxlength="255" tabindex="15" class="flugzeug_anlegen" style="width: 544px; height: 100px; padding: 5px !important;"><?php if (isset($data['bemerkungen'])) { echo $data['bemerkungen']; } ?></textarea></td>
											<td></td>
										</tr>
									</table>
								</fieldset>
							</div>
							<div id="tabs-2">
							
								<fieldset style="width: 97%; background-color: #eeeeee; margin-top: 10px;">
									<legend style="font-size: 11pt;"><img src="./img/money_cash.png" align="left" hspace="5" /> Flugbetriebskosten</legend>
									
									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd">
											<th align="left" width="130" style="padding-left: 8px;"><label for="fluggebuehr">Fluggeb&uuml;hr/h:</label></th>
											<td width="200"><input type="text" name="fluggebuehr" id="fluggebuehr" value="<?php if (isset($data['fluggebuehr'])) { echo $data['fluggebuehr']; } else { echo '0,00'; } ?>" maxlength="10" tabindex="16" class="flugzeug_anlegen" style="width:107px;text-align:right;padding-right:5px;" /><strong style="padding-left:5px;"><small>EUR</small></strong></td>
											<th align="left" width="115" style="padding-left: 8px;"><label for="startgebuehr">Startgeb&uuml;hr:</label></th>
											<td><input type="text" name="startgebuehr" id="startgebuehr" value="<?php if (isset($data['startgebuehr'])) { echo $data['startgebuehr']; } else { echo '0,00'; } ?>" maxlength="10" tabindex="19" class="flugzeug_anlegen" style="width:107px;text-align:right;padding-right:5px;" /><strong style="padding-left:5px;"><small>EUR</small></strong></td>
										</tr>
										<tr bgcolor="#eeeeee">
											<th align="left" width="130" style="padding-left: 8px;"><label for="motorgebuehr">Motorgeb&uuml;hr/h:</label></th>
											<td width="200"><input type="text" name="motorgebuehr" id="motorgebuehr" value="<?php if (isset($data['motorgebuehr'])) { echo $data['motorgebuehr']; } else { echo '0,00'; } ?>" maxlength="10" tabindex="17" class="flugzeug_anlegen" style="width:107px;text-align:right;padding-right:5px;" /><strong style="padding-left:5px;"><small>EUR</small></strong></td>
											<th align="left" width="115" style="padding-left: 8px;"><label for="landegebuehr">Landegeb&uuml;hr:</label></th>
											<td><input type="text" name="landegebuehr" id="landegebuehr" value="<?php if (isset($data['landegebuehr'])) { echo $data['landegebuehr']; } else { echo '0,00'; } ?>" maxlength="10" tabindex="20" class="flugzeug_anlegen" style="width:107px;text-align:right;padding-right:5px;" /><strong style="padding-left:5px;"><small>EUR</small></strong></td>
										</tr>
										<tr bgcolor="#dddddd">
											<th align="left" width="130" style="padding-left: 8px;"><label for="gastgebuehr">Gastgeb&uuml;hr/min:</label></th>
											<td width="200"><input type="text" name="gastgebuehr" id="gastgebuehr" value="<?php if (isset($data['gastgebuehr'])) { echo $data['gastgebuehr']; } else { echo '0,00'; } ?>" maxlength="10" tabindex="18" class="flugzeug_anlegen" style="width:107px;text-align:right;padding-right:5px;" /><strong style="padding-left:5px;"><small>EUR</small></strong></td>
											<th></th>
											<td></td>
										</tr>
									</table>
									
									<hr />
									
									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd" height="28">
											<th align="left" width="130" style="padding-left: 8px;">Startarten:</th>
											<td width="90"><label for="starttype_w"><strong>Windenstart</strong></label></td>
											<td width="76"><input type="checkbox" name="starttype_w" id="starttype_w" tabindex="21" <?php if ((isset($data['startart']) && (strpos($data['startart'], 'W') !== false))) { echo 'checked="checked"';} ?>/></td>
											<td width="75"><label for="starttype_f"><strong>F-Schlepp</strong></label></td>
											<td width="76"><input type="checkbox" name="starttype_f" id="starttype_f" tabindex="22" <?php if ((isset($data['startart']) && (strpos($data['startart'], 'F') !== false))) { echo 'checked="checked"';} ?>/></td>
											<td width="80"><label for="starttype_e"><strong>Eigenstart</strong></label></td>
											<td><input type="checkbox" name="starttype_e" id="starttype_e" tabindex="23" <?php if ((isset($data['startart']) && (strpos($data['startart'], 'E') !== false))) { echo 'checked="checked"';} ?>/></td>
										</tr>
									</table>

									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#eeeeee" height="30">
											<th align="left" width="130" style="padding-left:8px;padding-top:5px;">
												Typ-<small>Zuweisung</small>: <a href="#" title="" id="typ-zuweisung" style="cursor:help;"><img src="./img/info_button_16.png" align="top" border="0" style="margin-top:1px;" /></a>
											</th>
											<td width="20"><label for="1sp"><strong>1sp</strong></label></td>
											<td width="20"><input type="radio" name="typ" id="1sp" value="1sp" tabindex="24" style="margin-bottom:3px;margin-left:0px;" <?php if ((isset($data['typ']) && ($data['typ'] == '1sp')) || ($_GET['kennzeichen'] == 'neu')) { echo 'checked="checked"';} ?>/></td>
											<td width="20"><label for="1sv"><strong>1sv</strong></label></td>
											<td width="20"><input type="radio" name="typ" id="1sv" value="1sv" tabindex="25" style="margin-bottom:3px;margin-left:0px;" <?php if ((isset($data['typ']) && ($data['typ'] == '1sv'))) { echo 'checked="checked"';} ?>/></td>
											<td width="20"><label for="2sp"><strong>2sp</strong></label></td>
											<td width="20"><input type="radio" name="typ" id="2sp" value="2sp" tabindex="26" style="margin-bottom:3px;margin-left:0px;" <?php if ((isset($data['typ']) && ($data['typ'] == '2sp'))) { echo 'checked="checked"';} ?>/></td>
											<td width="20"><label for="2sv"><strong>2sv</strong></label></td>
											<td width="20"><input type="radio" name="typ" id="2sv" value="2sv" tabindex="27" style="margin-bottom:3px;margin-left:0px;" <?php if ((isset($data['typ']) && ($data['typ'] == '2sv'))) { echo 'checked="checked"';} ?>/></td>
											<td width="20"><label for="dip"><strong>Dip</strong></label></td>
											<td width="20"><input type="radio" name="typ" id="dip" value="Dip" tabindex="28" style="margin-bottom:3px;margin-left:0px;" <?php if ((isset($data['typ']) && ($data['typ'] == 'Dip'))) { echo 'checked="checked"';} ?>/></td>
											<td width="20"><label for="div"><strong>Div</strong></label></td>
											<td width="20"><input type="radio" name="typ" id="div" value="Div" tabindex="29" style="margin-bottom:3px;margin-left:0px;" <?php if ((isset($data['typ']) && ($data['typ'] == 'Div'))) { echo 'checked="checked"';} ?>/></td>
											<td width="20"><label for="fap"><strong>Fap</strong></label></td>
											<td width="20"><input type="radio" name="typ" id="fap" value="Fap" tabindex="30" style="margin-bottom:3px;margin-left:0px;" <?php if ((isset($data['typ']) && ($data['typ'] == 'Fap'))) { echo 'checked="checked"';} ?>/></td>
											<td width="20"><label for="fav"><strong>Fav</strong></label></td>
											<td width="20"><input type="radio" name="typ" id="fav" value="Fav" tabindex="31" style="margin-bottom:3px;margin-left:0px;" <?php if ((isset($data['typ']) && ($data['typ'] == 'Fav'))) { echo 'checked="checked"';} ?>/></td>
											<td width="20"><label for="ulp"><strong>Ulp</strong></label></td>
											<td width="20"><input type="radio" name="typ" id="ulp" value="Ulp" tabindex="32" style="margin-bottom:3px;margin-left:0px;" <?php if ((isset($data['typ']) && ($data['typ'] == 'Ulp'))) { echo 'checked="checked"';} ?>/></td>
											<td width="20"><label for="ulv"><strong>Ulv</strong></label></td>
											<td><input type="radio" name="typ" id="ulv" value="Ulv" tabindex="33" style="margin-bottom:3px;margin-left:0px;" <?php if ((isset($data['typ']) && ($data['typ'] == 'Ulv'))) { echo 'checked="checked"';} ?>/></td>
										</tr>
									</table>
								</fieldset>
								
								<fieldset style="width: 97%; background-color: #eeeeee; margin-top: 10px;">
									<legend style="font-size: 11pt;"><img src="./img/toolbox_icon_16.png" align="left" hspace="5" /> Wartungsdaten</legend>
									
									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd">
											<th align="left" width="130" style="padding-left: 8px;"><label for="letzte_jnp">Letzte JNP:</label></th>
											<td width="200"><input type="text" name="letzte_jnp" id="letzte_jnp" value="<?php if (isset($data['letzte_jnp'])) { echo $data['letzte_jnp']; } ?>" maxlength="10" class="flugzeug_anlegen_gesperrt" style="width:107px;background:url('./img/datum.png') no-repeat 85px;" readonly="readonly" onfocus="this.blur();" /></td>
											<th align="left" width="115" style="padding-left: 8px;"><label for="zelle_stunden">Zelle <small>(Stunden)</small>:</label></th>
											<td><input type="text" name="zelle_stunden" id="zelle_stunden" value="<?php if (isset($data['zelle_stunden'])) { echo $data['zelle_stunden']; } else { echo '0'; } ?>" maxlength="10" tabindex="34" class="flugzeug_anlegen" style="width:107px;text-align:right;padding-right:5px;" /></td>
										</tr>
										<tr bgcolor="#eeeeee">
											<th align="left" width="130" style="padding-left: 8px;"><label for="jnp_stunden">JNP <small>(Stunden)</small>:</label></th>
											<td width="200"><input type="text" name="jnp_stunden" id="jnp_stunden" value="<?php if (isset($data['jnp_stunden'])) { echo $data['jnp_stunden']; } ?>" maxlength="10" class="flugzeug_anlegen_gesperrt" style="width:107px;text-align:right;padding-right:5px;" readonly="readonly" onfocus="this.blur();" /></td>
											<th align="left" width="115" style="padding-left: 8px;"><label for="restzeit_motor">Restzeit Motor:</label></th>
											<td><input type="text" name="restzeit_motor" id="restzeit_motor" value="<?php if (isset($data['restzeit_motor'])) { echo $data['restzeit_motor']; } else { echo '0'; } ?>" maxlength="10" tabindex="35" class="flugzeug_anlegen" style="width:107px;text-align:right;padding-right:5px;" /></td>
										</tr>
										<tr bgcolor="#dddddd">
											<th align="left" width="130" style="padding-left: 8px;"><label for="jnp_landungen">JNP <small>(Landungen)</small>:</label></th>
											<td width="200"><input type="text" name="jnp_landungen" id="jnp_landungen" value="<?php if (isset($data['jnp_landungen'])) { echo $data['jnp_landungen']; } ?>" maxlength="10" class="flugzeug_anlegen_gesperrt" style="width:107px;text-align:right;padding-right:5px;" readonly="readonly" onfocus="this.blur();" /></td>
											<th align="left" width="115" style="padding-left: 8px;"><label for="restzeit_prop">Restzeit Prop:</label></th>
											<td><input type="text" name="restzeit_prop" id="restzeit_prop" value="<?php if (isset($data['restzeit_prop'])) { echo $data['restzeit_prop']; } else { echo '0'; } ?>" maxlength="10" tabindex="36" class="flugzeug_anlegen" style="width:107px;text-align:right;padding-right:5px;" /></td>
										</tr>
										<tr>
											<th></th>
											<td></td>
											<th align="left" width="115" style="padding-left: 8px;"><label for="stand_wartung">Stand:</label></th>
											<td><input type="text" name="stand_wartung" id="stand_wartung" value="<?php if (isset($data['stand_wartung'])) { echo $data['stand_wartung']; } ?>" maxlength="10" tabindex="37" class="flugzeug_anlegen" style="width:107px;" /></td>
										</tr>
									</table>
									
									<hr />
									
									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd">
											<th align="left" width="130" style="padding-left: 8px;"><label for="naechste_wartung">N&auml;chste Wartung:</label></th>
											<td colspan="3">
												<select name="naechste_wartung" id="naechste_wartung" tabindex="38" class="flugzeug_anlegen" style="width: 437px;">
													<option value="0"></option>
													<?php if (isset($data['naechste_wartung'])) { echo getListeWartungen($data['naechste_wartung']); } else { echo getListeWartungen(); } ?>
												</select>
											</td>
										</tr>
										<tr bgcolor="#eeeeee">
											<th align="left" width="130" style="padding-left: 8px;"><label for="naechste_wartung_stunden">bei Stunden:</label></th>
											<td width="200"><input type="text" name="naechste_wartung_stunden" id="naechste_wartung_stunden" value="<?php if (isset($data['naechste_wartung_stunden'])) { echo $data['naechste_wartung_stunden']; } else { echo '0'; } ?>" maxlength="10" tabindex="39" class="flugzeug_anlegen" style="width:107px;text-align:right;padding-right:5px;" /></td>
											<th align="left" width="115" style="padding-left: 8px;"><label for="naechste_wartung_datum">geplant f&uuml;r den:</label></th>
											<td><input type="text" name="naechste_wartung_datum" id="naechste_wartung_datum" value="<?php if (isset($data['naechste_wartung_datum'])) { echo $data['naechste_wartung_datum']; } ?>" maxlength="10" tabindex="40" class="flugzeug_anlegen" style="width:107px;" /></td>
										</tr>
									</table>
								</fieldset>
							
								<fieldset style="width: 97%; background-color: #eeeeee; margin-top: 10px; height: 250px;">
									<legend style="font-size: 11pt;"><img src="./img/korganizer_todo.png" align="left" hspace="5" /> Wartungs&uuml;bersicht / durchgef&uuml;hrte Wartungen</legend>
									
									<?php if (isset($_GET['kennzeichen']) && ($_GET['kennzeichen'] == 'neu')) : ?>
										<div style="height:27px; margin-top:5px;font:10pt Verdana;color:#999999;">
											<img src="./img/icon-plus-16x16.gif" border="0" alt="neue Wartung anlegen" align="left" hspace="2" style="margin-top:1px; margin-left:5px; margin-right:5px;" />
											neue Wartung anlegen
										</div>
									<?php else : ?>
										<div style="height:27px; margin-top:5px;">
											<a id="inline_wartung" href="#inline1" class="neuanlageWartung" style="font:10pt Verdana;color:#0000ff;">
												<img src="./img/icon-plus-16x16.gif" border="0" alt="neue Wartung anlegen" align="left" hspace="2" style="margin-top:1px; margin-left:5px; margin-right:5px;" />
												neue Wartung anlegen
											</a>
										</div>
									<?php endif; ?>

									<div style="display:none;">
										<div id="inline1" style="width:440px;height:280px;overflow:hidden;">
											<img src="./img/yarin_kaul_icon_tools48.png" align="left" border="0" width="32" height="32" hspace="6" />

											<h3 class="inline_wartung" id="inline_wartung_h3" style="margin-top:4px;">Neue Wartung anlegen</h3>
										
											<table cellpadding="1" cellspacing="0" border="0" width="99%" class="flugzeugliste">
												<tr bgcolor="#dddddd">
													<th align="left" width="130"><label for="wartung_neu" style="padding:3px 3px 3px 5px;">Art der Wartung</label></th>
													<td align="left" width="230">
														<?php
															if (isset($_GET['kennzeichen']) && ($_GET['kennzeichen'] == 'neu')) {
																$cssClass = 'class="wartung_anlegen_gesperrt" readonly="readonly" onfocus="this.blur();"';
																$buttonDisabled = 'disabled="disabled"';
															} else {
																$cssClass = 'class="wartung_anlegen"';
																$buttonDisabled = '';
															}
														?>
														<select name="wartung_neu" id="wartung_neu" tabindex="41" <?php echo $cssClass; ?> style="width:230px;">
															<?php echo getListeWartungen(); ?>
														</select>
													</td>
												</tr>
												<tr bgcolor="#eeeeee">
													<th align="left" width="130"><label for="wartung_neu_datum" style="padding:3px 3px 3px 5px;">durchgef&uuml;hrt am</label></th>
													<td align="left" width="230">
														<input type="text" name="wartung_neu_datum" id="wartung_neu_datum" value="<?php echo date('d.m.Y'); ?>" maxlength="10" tabindex="42" <?php echo $cssClass; ?> style="width:107px;" />
													</td>
												</tr>
												<tr bgcolor="#dddddd">
													<th align="left" width="130"><label for="wartung_neu_stunden" style="padding:3px 3px 3px 5px;">Flugstunden</label></th>
													<td align="left" width="230">
														<input type="text" name="wartung_neu_stunden" id="wartung_neu_stunden" value="0" maxlength="10" tabindex="43" <?php echo $cssClass; ?> style="width:107px;text-align:right;padding-right:3px;" />
													</td>
												</tr>
												<tr bgcolor="#eeeeee">
													<th align="left" width="130"><label for="wartung_neu_landungen" style="padding:3px 3px 3px 5px;">Landungen</label></th>
													<td align="left" width="230">
														<input type="text" name="wartung_neu_landungen" id="wartung_neu_landungen" value="0" maxlength="10" tabindex="44" <?php echo $cssClass; ?> style="width:107px;text-align:right;padding-right:3px;" />
													</td>
												</tr>
												<tr bgcolor="#dddddd">
													<th align="left" width="130"><label for="wartung_neu_technik" style="padding:3px 3px 3px 5px;">durchgef&uuml;hrt von</label></th>
													<td align="left" width="230">
														<select name="wartung_neu_technik" id="wartung_neu_technik" tabindex="45" <?php echo $cssClass; ?> style="width:230px;">
															<?php echo getListePersonenTechnik(); ?>
														</select>
													</td>
												</tr>
												<tr bgcolor="#eeeeee">
													<th align="left" width="130" valign="top" style="padding:7px 3px 3px 5px;"><label for="wartung_neu_bemerkung">Bemerkung(en)</label></th>
													<td align="left" width="230">
														<textarea name="wartung_neu_bemerkung" id="wartung_neu_bemerkung" maxlength="255" tabindex="46" <?php echo $cssClass; ?> style="width:230px; height:60px; padding:5px !important;"></textarea>
													</td>
												</tr>
												<tr height="40">
													<td colspan="2" align="right">
														<input type="button" id="wartung_neu_add" name="wartung_neu_add" value="Wartung hinzuf&uuml;gen" tabindex="47" style="height:21px;" <?php echo $buttonDisabled; ?> />
														<input type="button" id="wartung_neu_edit" name="wartung_neu_edit" value="&Auml;nderungen speichern" tabindex="47" style="height:21px; display:none;" <?php echo $buttonDisabled; ?> />
														<input type="button" id="wartung_neu_cancel" name="wartung_neu_cancel" value="Abbrechen" tabindex="48" style="height:21px;" <?php echo $buttonDisabled; ?> />
														<input type="hidden" id="wartung_neu_id" name="wartung_neu_id" value="0" />
													</td>
												</tr>
											</table>
										</div>
									</div>
									
									
									<table cellpadding="1" cellspacing="0" border="0" class="wartungsliste">
										<tr bgcolor="#666666">
											<th align="left" width="100" style="color:#ffffff;border-bottom:1px solid #ffffff;padding:3px 3px 3px 5px;" bgcolor="#666666">Wartung</th>
											<th align="left" width="65" style="color:#ffffff;border-bottom:1px solid #ffffff;padding:3px 3px 3px 5px;" bgcolor="#666666">Datum</th>
											<th align="left" width="105" style="color:#ffffff;border-bottom:1px solid #ffffff;padding:3px 3px 3px 5px;" bgcolor="#666666">Durchgef&uuml;hrt von</th>
											<th align="left" width="75" style="color:#ffffff;border-bottom:1px solid #ffffff;padding:3px 3px 3px 5px;" bgcolor="#666666">Flugstunden</th>
											<th align="left" width="68" style="color:#ffffff;border-bottom:1px solid #ffffff;padding:3px 3px 3px 5px;" bgcolor="#666666">Landungen</th>
											<th align="left" width="180" style="color:#ffffff;border-bottom:1px solid #ffffff;padding:3px 3px 3px 5px;" bgcolor="#666666">Bemerkung(en)</th>
											<th style="color:#ffffff;border-bottom:1px solid #ffffff;" bgcolor="#666666">&nbsp;</th>
										</tr>
										<tr>
											<td colspan="7">
												<div class="tabelle_wartungen" style="overflow-y:scroll;">
													<table cellpadding="1" cellspacing="0" border="0" class="wartungsliste" id="wartungstabelle" name="wartungstabelle">
														<?php if (isset($data['kennzeichen'])) { echo getTabelleWartungen($data['kennzeichen']); } ?>
													</table>
												</div>
											</td>
										</tr>

									</table>
									
								</fieldset>
							</div>
						</div>
						
						<div class="flugzeug_speichern_buttons">
							<input type="submit" tabindex="75" name="flugzeug_speichern" id="flugzeug_speichern" value="Daten speichern" style="width: 150px; margin-left: 10px;" />
							<input type="reset" tabindex="76" name="flugzeug_cancel" id="flugzeug_cancel" value="Abbrechen" style="width: 150px;" />
							
							<span class="pflichtfeld" style="font: 9pt Verdana; margin-left: 20px;">*) Pflichtfelder</span>
						</div>

					</form>

				</td>
			</tr>
		</table>

	</body>

</html>
<!-- ENDE: AUSGABE -->