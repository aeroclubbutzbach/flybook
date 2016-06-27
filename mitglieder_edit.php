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
	 * getListeMitgliedsstatus()
	 *
	 * gibt eine ComboBox mit den entsprechenden Listeneinträgen der
	 * Mitgliedschaften zurück, per optionalen Parameter kann noch ein
	 * bestimmter Eintrag der ComboBox als Selektiert dargestellt werden
	 *
	 * @params char   $selektor
	 * @return string $html
	 */
	if (!function_exists('getListeMitgliedsstatus')) {
		function getListeMitgliedsstatus($selektor = 'A')
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
			// die Bezeichnungen und die IDs der Mitglied-
			// schaften werden als Liste zurückgegeben
			$sql = sprintf('
				SELECT
					`mitgliedschaft`.`id`,
					`mitgliedschaft`.`bezeichnung`
				FROM
					`mitgliedschaft`
				ORDER BY
					`mitgliedschaft`.`id` ASC
			');
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// es sind Datensätze vorhanden
			while ($zeile = mysql_fetch_object($db_erg)) {
				// prüfen ob ein selektierter Eintrag existiert
				$selected = ($selektor == $zeile->id) ? 'selected="selected"' : '';
			
				// Rückgabe eines entsprechenden Mitgliedsstatus/-gruppe
				$html .= sprintf(
					'<option %s value="%s">%s</option>',
					$selected,
					$zeile->id,
					utf8_encode($zeile->bezeichnung)
				);
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Mitgliedschaften (ComboBox)
			return $html;
		}
	}
	
	/*
	 * getListeLaender()
	 *
	 * gibt eine ComboBox mit den entsprechenden Listeneinträgen der
	 * Länder zurück, per optionalen Parameter kann noch ein
	 * bestimmter Eintrag der ComboBox als Selektiert dargestellt werden
	 *
	 * @params char   $selektor
	 * @return string $html
	 */
	if (!function_exists('getListeLaender')) {
		function getListeLaender($selektor = 'DE')
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
			// die Bezeichnungen und die IDs der Länder-
			// kennungen werden als Liste zurückgegeben
			$sql = sprintf('
				SELECT
					`laender`.`id`,
					`laender`.`bezeichnung`
				FROM
					`laender`
				ORDER BY
					`laender`.`id` ASC
			');
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// es sind Datensätze vorhanden
			while ($zeile = mysql_fetch_object($db_erg)) {
				// prüfen ob ein selektierter Eintrag existiert
				$selected = ($selektor == $zeile->id) ? 'selected="selected"' : '';
			
				// Rückgabe eines entsprechenden Land/-kennung
				$html .= sprintf(
					'<option %s value="%s">%s</option>',
					$selected,
					$zeile->id,
					$zeile->bezeichnung
				);
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Länder (ComboBox)
			return $html;
		}
	}
	
	/*
	 * getTaetigkeiten()
	 *
	 * gibt die gewählten Tätigkeiten zurück
	 *
	 * @params array  $params
	 * @return string $return
	 *
	 */
	if (!function_exists('getTaetigkeiten')) {
		function getTaetigkeiten(array $params)
		{
			// leeres Array anlegen zum speichern der angegebenen Tätigkeiten
			$taetigkeiten = array();

			// Angaben zu den eingetragenen Tätigkeiten
			if (!empty($params['job_motorflug']))     $taetigkeiten[] = 'A'; /* Motorflug            */
			if (!empty($params['job_motorsegler']))   $taetigkeiten[] = 'B'; /* Motorsegler          */
			if (!empty($params['job_segelflug']))     $taetigkeiten[] = 'C'; /* Segelflug            */
			if (!empty($params['job_modellflug']))    $taetigkeiten[] = 'D'; /* Modellflug           */
			if (!empty($params['job_fallschirm']))    $taetigkeiten[] = 'E'; /* Fallschirmspringen   */
			if (!empty($params['job_ballon']))        $taetigkeiten[] = 'F'; /* Ballonfahren         */
			if (!empty($params['job_drachen']))       $taetigkeiten[] = 'G'; /* Drachenfliegen       */
			if (!empty($params['job_ul']))            $taetigkeiten[] = 'H'; /* Ultraleichtflug      */
			if (!empty($params['job_jugendleiter']))  $taetigkeiten[] = 'I'; /* Jugendleiter         */
			if (!empty($params['job_uebungsleiter'])) $taetigkeiten[] = 'J'; /* Übungsleiter         */
			if (!empty($params['job_trainer']))       $taetigkeiten[] = 'K'; /* Trainer              */
			if (!empty($params['job_sonstige']))      $taetigkeiten[] = 'L'; /* Sonstige Tätigkeiten */
			if (!empty($params['job_gleitschirm']))   $taetigkeiten[] = 'M'; /* Gleitschirmfliegen   */
			
			// vorhandene Tätigkeiten aufsplitten und Array in Zeichenkette konvertieren
			$taetigkeiten = implode($taetigkeiten, ',');

			// Angaben zu den Tätigkeiten als SQL-Teilbefehl darstellen
			if (!empty($taetigkeiten)) {
				return sprintf('"%s"', $taetigkeiten);
			} else {
				return 'NULL';
			}
		}
	}
			
	/*
	 * getFachausweise()
	 *
	 * gibt die gewählten Fachausweise zurück
	 *
	 * @params array  $params
	 * @return string $return
	 *
	 */
	if (!function_exists('getFachausweise')) {
		function getFachausweise(array $params)
		{
			// leeres Array anlegen zum speichern der angegebenen Fachausweise
			$fachausweise = array();

			// Angaben zu den eingetragenen Fachausweisen
			if (!empty($params['job_motorfluglehrer']))   $fachausweise[] = 'A'; /* Motorfluglehrer   */
			if (!empty($params['job_moselehrer']))        $fachausweise[] = 'B'; /* Motorseglerlehrer */
			if (!empty($params['job_segelfluglehrer']))   $fachausweise[] = 'C'; /* Segelfluglehrer   */
			if (!empty($params['job_modellfluglehrer']))  $fachausweise[] = 'D'; /* Modellfluglehrer  */
			if (!empty($params['job_sprunglehrer']))      $fachausweise[] = 'E'; /* Sprunglehrer      */
			if (!empty($params['job_ballonausbilder']))   $fachausweise[] = 'F'; /* Ballonausbilder   */
			if (!empty($params['job_drachenfluglehrer'])) $fachausweise[] = 'G'; /* Drachenfluglehrer */
			if (!empty($params['job_ul_fluglehrer']))     $fachausweise[] = 'H'; /* UL-Fluglehrer     */
			if (!empty($params['job_werkstattleiter']))   $fachausweise[] = 'I'; /* Werkstattleiter   */
			if (!empty($params['job_flugzeugwart']))      $fachausweise[] = 'J'; /* Flugzeugwart      */
			if (!empty($params['job_mosewart']))          $fachausweise[] = 'K'; /* Motorseglerwart   */
			if (!empty($params['job_segelflugwart']))     $fachausweise[] = 'L'; /* Segelflugzeugwart */
			if (!empty($params['job_fallschirmwart']))    $fachausweise[] = 'M'; /* Fallschirmpacker  */
			if (!empty($params['job_ballonwart']))        $fachausweise[] = 'N'; /* Ballonwart        */
			if (!empty($params['job_pruefer']))           $fachausweise[] = 'T'; /* Pr�fer            */
			if (!empty($params['job_zeuge_motorflug']))   $fachausweise[] = 'O'; /* Zeuge Motorflug   */
			if (!empty($params['job_zeuge_segelflug']))   $fachausweise[] = 'P'; /* Zeuge Segelflug   */
			if (!empty($params['job_zeuge_modellflug']))  $fachausweise[] = 'R'; /* Zeuge Modellflug  */
			if (!empty($params['job_zeuge_fallschirm']))  $fachausweise[] = 'S'; /* Zeuge Fallschirm  */
			if (!empty($params['job_sonstiges']))         $fachausweise[] = 'X'; /* Sonstiges         */
			if (!empty($params['job_flugleiter']))        $fachausweise[] = 'U'; /* Flugleiter        */
			
			// vorhandene Fachausweise aufsplitten und Array in Zeichenkette konvertieren
			$fachausweise = implode($fachausweise, ',');

			// Angaben zu den Fachausweise als SQL-Teilbefehl darstellen
			if (!empty($fachausweise)) {
				return sprintf('"%s"', $fachausweise);
			} else {
				return 'NULL';
			}
		}
	}
	
	/*
	 * neuFluggeldkonto()
	 *
	 * legt ein neues Fluggeldkonto anhand der übergebenen Mitgliedsnummer an
	 *
	 * @params integer $acb_nr
	 */
	if (!function_exists('neuFluggeldkonto')) {
		function neuFluggeldkonto($acb_nr)
		{
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
			// Befehl zum Speichern einer Neuanlage eines Mitglieds
			$sql = sprintf('
				INSERT INTO `fluggeldkonto` (`acb_nr`, `saldo`) VALUES (%d, "0.00")
			',
				$acb_nr
			);

			// zuvor definierte SQL-Anweisung ausführen
			mysql_query($sql);
		}
	}
	
	/*
	 * neuAnlageMitglied()
	 *
	 * legt ein neues Mitglied anhand der übergebenen Parameter an
	 *
	 * @params array $params
	 */
	if (!function_exists('neuAnlageMitglied')) {
		function neuAnlageMitglied(array $params)
		{
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// SQL-Befehl zurechtfuddeln,
			// Befehl zum Speichern einer Neuanlage eines Mitglieds
			$sql = sprintf('
				INSERT INTO
					`mitglieder` (
						`id`,
						`status`,
						`sort`,
						`anrede`,
						`titel`,
						`ameavia`,
						`vorname`,
						`nachname`,
						`geburtsdatum`,
						`strasse`,
						`land`,
						`plz`,
						`ort`,
						`telefon1`,
						`telefon2`,
						`mobil1`,
						`mobil2`,
						`email`,
						`www`,
						`bank`,
						`blz`,
						`kto`,
						`ktoinhaber`,
						`eintritt`,
						`austritt`,
						`datenschutz`,
						`rundmail`,
						`fl_dienst_absprache`,
						`fl_dienst_wochentags`,
						`funktion`, 
						`ppladat`,
						`pplbdat`,
						`pplcdat`,
						`uldat`,
						`medical`,
						`jar_tmg`,
						`jar_sep`,
						`hlbnr`,
						`taetigkeiten`,
						`fachausweise`
					)
					VALUES (
						%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
						%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
						%s, %s, %s, %s, %s, %s, %s, %s
					)
			',
				getDbValue($params['acb_nr'],               T_NUMERIC),
				getDbValue($params['status'],               T_STR),
				getDbValue($params['sort'],                 T_NUMERIC),
				getDbValue($params['anrede'],               T_STR),
				getDbValue($params['titel'],                T_STR),
				getDbValue($params['ameavia'],              T_STR),
				getDbValue($params['vorname'],              T_STR),
				getDbValue($params['nachname'],             T_STR),
				getDbValue($params['geburtsdatum'],         T_DATE),
				getDbValue($params['strasse'],              T_STR),
				getDbValue($params['land'],                 T_STR),
				getDbValue($params['plz'],                  T_STR),
				getDbValue($params['ort'],                  T_STR),
				getDbValue($params['telefon1'],             T_STR),
				getDbValue($params['telefon2'],             T_STR),
				getDbValue($params['mobil1'],               T_STR),
				getDbValue($params['mobil2'],               T_STR),
				getDbValue($params['email'],                T_STR),
				getDbValue($params['www'],                  T_STR),
				getDbValue($params['bank'],                 T_STR),
				getDbValue($params['blz'],                  T_STR),
				getDbValue($params['kto'],                  T_STR),
				getDbValue($params['ktoinhaber'],           T_STR),
				getDbValue($params['eintritt'],             T_DATE),
				getDbValue($params['austritt'],             T_DATE),
				getDbValue($params['datenschutz'],          T_BOOL),
				getDbValue($params['rundmail'],             T_BOOL),
				getDbValue($params['fl_dienst_absprache'],  T_BOOL),
				getDbValue($params['fl_dienst_wochentags'], T_BOOL),
				getDbValue($params['funktion'],             T_STR),
				getDbValue($params['ppladat'],              T_DATE),
				getDbValue($params['pplbdat'],              T_DATE),
				getDbValue($params['pplcdat'],              T_DATE),
				getDbValue($params['uldat'],                T_DATE),
				getDbValue($params['medical'],              T_DATE),
				getDbValue($params['jar_tmg'],              T_BOOL),
				getDbValue($params['jar_sep'],              T_BOOL),
				getDbValue($params['hlbnr'],                T_STR),
				getTaetigkeiten($params),
				getFachausweise($params)
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			mysql_query($sql);
			
			// Neuanlage Mitglieds-/Fluggeldkonto
			neuFluggeldkonto($params['acb_nr']);
		}
	}

	/*
	 * updateMitglied()
	 *
	 * aktualisiert ein bereits vorhandenes Mitglied anhand der
	 * übergebenen Parameter und der bestehenden Mitgliedsnummer
	 *
	 * @params array $params
	 */
	if (!function_exists('updateMitglied')) {
		function updateMitglied(array $params)
		{
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');
			
			// Austritts-Merkmal prüfen
			if (getDbValue($params['in_abrechn'], T_BOOL) == '"J"') {
				// Austrittsmerkmal ist gesetzt, Mitglied wird einfernt
				$in_abrechn = '"N"';
				$rundmail = '"N"';
				$status = '"X"';
			} else {
				// Austrittsmerkmal nicht gesetzt, Mitglied bleibt vorhanden
				$in_abrechn = '"J"';
				$rundmail = '"J"';
				$status = getDbValue($params['status'], T_STR);
			}

			// SQL-Befehl zurechtfuddeln,
			// Befehl zum Speichern einer Veränderung eines Mitglieds
			$sql = sprintf('
				UPDATE
					`mitglieder`
				SET
					`status` = %s,
					`sort` = %s,
					`anrede` = %s,
					`titel` = %s,
					`ameavia` = %s,
					`vorname` = %s,
					`nachname` = %s,
					`geburtsdatum` = %s,
					`strasse` = %s,
					`land` = %s,
					`plz` = %s,
					`ort` = %s,
					`telefon1` = %s,
					`telefon2` = %s,
					`mobil1` = %s,
					`mobil2` = %s,
					`email` = %s,
					`www` = %s,
					`bank` = %s,
					`blz` = %s,
					`kto` = %s,
					`ktoinhaber` = %s,
					`eintritt` = %s,
					`austritt` = %s,
					`datenschutz` = %s,
					`rundmail` = %s,
					`fl_dienst_absprache` = %s,
					`fl_dienst_wochentags` = %s,
					`funktion` = %s, 
					`ppladat` = %s,
					`pplbdat` = %s,
					`pplcdat` = %s,
					`uldat` = %s,
					`medical` = %s,
					`jar_tmg` = %s,
					`jar_sep` = %s,
					`hlbnr` = %s,
					`taetigkeiten` = %s,
					`fachausweise` = %s,
					`in_abrechn` = %s
				WHERE
					`id` = %d
			',
				$status,
				getDbValue($params['sort'],                 T_NUMERIC),
				getDbValue($params['anrede'],               T_STR),
				getDbValue($params['titel'],                T_STR),
				getDbValue($params['ameavia'],              T_STR),
				getDbValue($params['vorname'],              T_STR),
				getDbValue($params['nachname'],             T_STR),
				getDbValue($params['geburtsdatum'],         T_DATE),
				getDbValue($params['strasse'],              T_STR),
				getDbValue($params['land'],                 T_STR),
				getDbValue($params['plz'],                  T_STR),
				getDbValue($params['ort'],                  T_STR),
				getDbValue($params['telefon1'],             T_STR),
				getDbValue($params['telefon2'],             T_STR),
				getDbValue($params['mobil1'],               T_STR),
				getDbValue($params['mobil2'],               T_STR),
				getDbValue($params['email'],                T_STR),
				getDbValue($params['www'],                  T_STR),
				getDbValue($params['bank'],                 T_STR),
				getDbValue($params['blz'],                  T_STR),
				getDbValue($params['kto'],                  T_STR),
				getDbValue($params['ktoinhaber'],           T_STR),
				getDbValue($params['eintritt'],             T_DATE),
				getDbValue($params['austritt'],             T_DATE),
				getDbValue($params['datenschutz'],          T_BOOL),
				$rundmail,
				getDbValue($params['fl_dienst_absprache'],  T_BOOL),
				getDbValue($params['fl_dienst_wochentags'], T_BOOL),
				getDbValue($params['funktion'],             T_STR),
				getDbValue($params['ppladat'],              T_DATE),
				getDbValue($params['pplbdat'],              T_DATE),
				getDbValue($params['pplcdat'],              T_DATE),
				getDbValue($params['uldat'],                T_DATE),
				getDbValue($params['medical'],              T_DATE),
				getDbValue($params['jar_tmg'],              T_BOOL),
				getDbValue($params['jar_sep'],              T_BOOL),
				getDbValue($params['hlbnr'],                T_STR),
				getTaetigkeiten($params),
				getFachausweise($params),
				$in_abrechn,
				getDbValue($params['acb_nr'], T_NUMERIC)
			);

			// zuvor definierte SQL-Anweisung ausführen
			mysql_query($sql);
		}
	}
	
	/*
	 * getMitglied()
	 *
	 * alle Mitgliedsdaten zum ausgewählten Mitglied werden geladen
	 *
	 * @params string $acb_nr
	 * @return array  $data
	 */
	if (!function_exists('getMitglied')) {
		function getMitglied($acb_nr)
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
			// die Daten für das aktuell ausgewählte Mitglied laden
			$sql = sprintf('
				SELECT
					*
				FROM
					`mitglieder`
				WHERE
					`id` = %d
				LIMIT 1
			',
				$acb_nr
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// es sind Datensätze vorhanden
			while ($zeile = mysql_fetch_object($db_erg)) {
				// Daten übernehmen wie hinterlegt
				$data['acb_nr']               = utf8_encode($zeile->id);
				$data['status']               = utf8_encode($zeile->status);
				$data['sort']                 = utf8_encode($zeile->sort);
				$data['anrede']               = utf8_encode($zeile->anrede);
				$data['titel']                = utf8_encode($zeile->titel);
				$data['ameavia']              = utf8_encode($zeile->ameavia);
				$data['vorname']              = utf8_encode($zeile->vorname);
				$data['nachname']             = utf8_encode($zeile->nachname);
				$data['geburtsdatum']         = utf8_encode(fromSqlDatum($zeile->geburtsdatum));
				$data['strasse']              = utf8_encode($zeile->strasse);
				$data['land']                 = utf8_encode($zeile->land);
				$data['plz']                  = utf8_encode($zeile->plz);
				$data['ort']                  = utf8_encode($zeile->ort);
				$data['telefon1']             = utf8_encode($zeile->telefon1);
				$data['telefon2']             = utf8_encode($zeile->telefon2);
				$data['mobil1']               = utf8_encode($zeile->mobil1);
				$data['mobil2']               = utf8_encode($zeile->mobil2);
				$data['email']                = utf8_encode($zeile->email);
				$data['www']                  = utf8_encode($zeile->www);
				$data['bank']                 = utf8_encode($zeile->bank);
				$data['blz']                  = utf8_encode($zeile->blz);
				$data['kto']                  = utf8_encode($zeile->kto);
				$data['ktoinhaber']           = utf8_encode($zeile->ktoinhaber);
				$data['eintritt']             = utf8_encode(fromSqlDatum($zeile->eintritt));
				$data['austritt']             = utf8_encode(fromSqlDatum($zeile->austritt));
				$data['in_abrechn']           = utf8_encode($zeile->in_abrechn);
				$data['datenschutz']          = utf8_encode($zeile->datenschutz);
				$data['rundmail']             = utf8_encode($zeile->rundmail);
				$data['fl_dienst_absprache']  = utf8_encode($zeile->fl_dienst_absprache);
				$data['fl_dienst_wochentags'] = utf8_encode($zeile->fl_dienst_wochentags);
				$data['funktion']             = utf8_encode($zeile->funktion); 
				$data['ppladat']              = utf8_encode(fromSqlDatum($zeile->ppladat));
				$data['pplbdat']              = utf8_encode(fromSqlDatum($zeile->pplbdat));
				$data['pplcdat']              = utf8_encode(fromSqlDatum($zeile->pplcdat));
				$data['uldat']                = utf8_encode(fromSqlDatum($zeile->uldat));
				$data['medical']              = utf8_encode(fromSqlDatum($zeile->medical));
				$data['jar_tmg']              = utf8_encode($zeile->jar_tmg);
				$data['jar_sep']              = utf8_encode($zeile->jar_sep);
				$data['hlbnr']                = utf8_encode($zeile->hlbnr);
				$data['taetigkeiten']         = utf8_encode($zeile->taetigkeiten);
				$data['fachausweise']         = utf8_encode($zeile->fachausweise);
				
				// Adressdaten holen
				if (($data['strasse'] != '') && ($data['plz'] != '') && ($data['ort'] != '')) {
					// Google Maps-Link hinzufügen
					$data['googlemaps'] = sprintf(
						'http://maps.google.de/maps?q=%s,+%s+%s,&t=h&z=17',
						$data['strasse'], $data['plz'], $data['ort']
					);
				}
				
				// eMail-Adresse holen
				// eMail-Feld darf nicht leer sein
				if ($data['email'] != '') {
					// eMail-Adresse als Link hinterlegen
					$data['goto_email'] = sprintf('mailto:%s', $data['email']);
				}
				
				// Homepage-Adresse holen
				// Homepage-Feld darf nicht leer sein
				if ($data['www'] != '') {
					// prüfen ob vorne ein HTTP dran steht
					if (strpos($data['www'], 'http') === false) {
						// Homepage-Adresse als Link hinterlegen
						$data['goto_www'] = sprintf('http://%s', $data['www']);
					} else {
						// Homepage-Adresse als Link hinterlegen
						$data['goto_www'] = $data['www'];
					}
				}
				
				// Bild (Avatar) holen
				// das Bild (Avatar) muss existent sein
				if (file_exists(sprintf('./userpics/%s.jpg', md5($data['acb_nr'])))) {
					// Bild laden
					$data['avatar_img'] = sprintf('%s.jpg', md5($data['acb_nr']));
				} else {
					// Bild wieder auf das Dummy-Pic (anhand der Anrede) zurücksetzen
					if ($data['anrede'] == 'H') {
						$data['avatar_img'] = '_dummy_pic_male.jpg';
					} else if ($data['anrede'] == 'C') {
						$data['avatar_img'] = '_dummy_pic_company.jpg';
					} else {
						$data['avatar_img'] = '_dummy_pic_female.jpg';
					}
				}
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Mitgliedsdaten
			return $data;
		}
	}


	/**************************************************************************************************************************/
	/* ------------------------------------- BEGINN : MITGLIED SPEICHERN NACH GET-BEFEHL ------------------------------------ */
	/**************************************************************************************************************************/
	// Array anlegen für die Feldinhalte
	$data = array();
	
	if (isset($_GET['action']) && $_GET['action'] == 'speichern') {
		// es wird geprüft, ob es sich um eine Neuanlage eines bestehenden
		// Mitglieds handelt, oder ob ein neuer Datensatz angelegt werden soll
		if ($_GET['acb_nr'] == 'neu') {
			// Neuanlage eines Mitglieds
			neuAnlageMitglied($_POST);
		} else {
			// Bearbeiten eines bestehenden Datensatzes
			updateMitglied($_POST);
		}

		// zurück zur normalen Mitgliederliste
		echo '<script language="javascript" type="text/javascript">';
		echo 'window.location.href = "mitglieder.php"';
		echo '</script>';

		// sicher stellen, dass der nachfolgende Code nicht
		// ausgefuehrt wird, wenn eine Umleitung stattfindet.
		exit();
	} else {
		// Feldinhalte laden, falls ein Mitglied ausgewählt wurde
		if ($_GET['acb_nr'] != 'neu') {
			$data = getMitglied($_GET['acb_nr']);
		}
	}
	/**************************************************************************************************************************/
	/* ------------------------------------- ENDE : MITGLIED SPEICHERN NACH GET-BEFEHL -------------------------------------- */
	/**************************************************************************************************************************/
	
?>
<!-- ENDE: SKRIPT -->
<!-- BEGINN: AUSGABE -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

    <head>

        <title>Mitglied speichern</title>

        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <meta http-equiv="Content-Script-Type" content="text/javascript" />
        <meta http-equiv="Content-Style-Type" content="text/css" />
        <meta http-equiv="content-language" content="de" />
        <meta name="author" content="Benjamin Stopfkuchen" />
		
		<!--<script type="text/javascript" src="./js/jquery-1.10.2.js"></script>-->
		<script type="text/javascript" src="./js/jquery-1.10.2.min.js"></script>
		<!--<script src="http://code.jquery.com/jquery-1.9.1.js"></script>-->
		<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
		<script type="text/javascript" src="./js/functions.js"></script>

        <link rel="Stylesheet" type="text/css" href="./css/stylish.css" />
		<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
		
		<script type="text/javascript" language="JavaScript">
		<!--
		
			function pruefeMitgliedsnummer(acb_nr)
			{
				// Rückgabevariable initialisieren
				var ret = false;

				// Aufruf per AJAX an das PHP-Modul, welches die
				// Funktion zum Prüfen vorhandener Mitgliedsnummer enthält
				$.ajax({
					url: 'ajax_mitgliedsnummer_pruefen.php',
					type: 'POST',
					async: false,  
					data: { acb_nr : acb_nr }
				}).done(function(msg) {
					// es wird geprüft, ob die Mitgliedsnummer bereits vergebenen wurde
					if (msg == 1) {
						// Mitgliedsnummer bereits vorhanden
						ret = true;
					} else {
						// Mitgliedsnummer noch frei
						ret = false;
					}
				});
				
				// Rückgabe ob Mitgliedsnummer vorhanden
				return ret;
			}
			
			function pruefeAmeAviaReferenz(ameavia_ref)
			{
				// Rückgabevariable initialisieren
				var ret = false;

				// Aufruf per AJAX an das PHP-Modul, welches die
				// Funktion zum Prüfen vorhandener AmeAvia-Referenzen enthält
				$.ajax({
					url: 'ajax_ameavia_ref_pruefen.php',
					type: 'POST',
					async: false,  
					data: { ameavia_ref : ameavia_ref }
				}).done(function(msg) {
					// es wird geprüft, ob die AmeAvia-Referenz bereits vergebenen wurde
					if (msg == 1) {
						// AmeAvia-Referenz bereits vorhanden
						ret = true;
					} else {
						// AmeAvia-Referenz noch frei
						ret = false;
					}
				});
				
				// Rückgabe ob AmeAvia-Referenz vorhanden
				return ret;
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
			
				$('#geburtsdatum').datepicker({ dateFormat: 'dd.mm.yy' });
				$('#eintritt').datepicker({ dateFormat: 'dd.mm.yy' });
				$('#austritt').datepicker({ dateFormat: 'dd.mm.yy' });
				$('#ppladat').datepicker({ dateFormat: 'dd.mm.yy' });
				$('#pplbdat').datepicker({ dateFormat: 'dd.mm.yy' });
				$('#pplcdat').datepicker({ dateFormat: 'dd.mm.yy' });
				$('#uldat').datepicker({ dateFormat: 'dd.mm.yy' });
				$('#medical').datepicker({ dateFormat: 'dd.mm.yy' });
				
				$('#email').change(function() {
					// Inhalt übernehmen
					var email = $('#email').val();
				
					// eMail-Feld darf nicht leer sein
					if (email.trim() != '') {
						// eMail-Adresse als Link hinterlegen
						$('#goto_email').attr('href', 'mailto:' + email);
					} else {
						// Link der eMail-Adresse entfernen
						$('#gogo_email').removeAttr('href');
					}
				});
				
				$('#anrede').change(function() {
					// Anrede ermitteln
					var anrede = $('#anrede').val();
					
					// Bild wieder auf das Dummy-Pic (anhand der Anrede) zurücksetzen
					if (anrede == 'H') {
						$('#avatar_img').attr('src', './userpics/_dummy_pic_male.jpg');
					} else if (anrede == 'C') {
						$('#avatar_img').attr('src', './userpics/_dummy_pic_company.jpg');
					} else {
						$('#avatar_img').attr('src', './userpics/_dummy_pic_female.jpg');
					}
				});
				
				$('#www').change(function() {
					// Inhalt übernehmen
					var www = $('#www').val();
				
					// Homepage-Feld darf nicht leer sein
					if (www.trim() != '') {
						// prüfen ob vorne ein HTTP dran steht
						if (www.indexOf('http') == -1) {
							www = 'http://' + www;
						}

						// Homepage-Adresse als Link hinterlegen
						$('#goto_www').attr('href', www);
					} else {
						// Link der Homepage-Adresse entfernen
						$('#goto_www').removeAttr('href');
					}
				});
				
				$('.adresse').change(function() {
					// Adressdaten holen
					var strasse = $('#strasse').val();
					var plz     = $('#plz').val();
					var ort     = $('#ort').val();

					if ((strasse.trim() != '') && (plz.trim() != '') && (ort.trim() != '')) {
						// Google Maps-Link hinzufügen
						var www = 'http://maps.google.de/maps?q=' + strasse + ',+' + plz + '+' + ort + ',&t=h&z=17';
						
						$('#goto_googlemaps').attr('href', www);
					} else {
						// Link zu Google Maps entfernen
						$('#goto_googlemaps').removeAttr('href');
					}
				});
				
				$('#avatar').click(function() {
					// öffnet den Dialog zur Dateiauswahl
					$('input:file').click();
				});
				
				$('input:file').change(function() {
					// lädt den Namen der ausgewählten Bilddatei in
					// das zugehörige Textfeld zur weiteren Verwendung
					$('#avatar').val($('input:file').val());
				});

				$('#avatar_upload').click(function() {
					if ($('#avatar').val().trim() != '') {
						// Variable erzeugen zur Übergabe der Parameter
						var data = new FormData();

						// die hochzuladende Datei per Parameter anhängen
						data.append('upload', $('input:file')[0].files[0]);
						// zusätzlicher Parameter wird angehängt
						// -> Mitgliedsnummer
						data.append('id', $('#acb_nr').val());
						// das Zielverzeichnis festlegen
						data.append('dir', 'userpics');
						
						// Aufruf per AJAX an das PHP-Modul, welches
						// die Funktion zum Hochladen der Datei enthält
						$.ajax({
							url: 'ajax_file_upload.php',
							data: data,
							type: 'POST',
							processData: false,
							contentType: false,
							async: false,
							success: function(data) {
								// Rückgabe-Daten per JSON auslesen
								var json = $.parseJSON(data);
							
								// Ergebnis prüfen, ob das Bild hochgeladen wurde
								if (json.result == true) {
									// alles gut, Bild wurde hochgeladen und kann
									// nun in der Anzeige rechts angezeigt werden
									$('#avatar_img').attr('src', json.image);
									// Feldinhalte entleeren
									$('#avatar').val('');
								}
							}
						});
					}
				});
				
				$('#avatar_delete').click(function() {
					// Pfad und Dateiname zum Bild holen
					var avatar_img = $('#avatar_img').attr('src');
				
					// nur Löschen wenn es sich um KEIN Dummy-Bild handelt
					if (avatar_img.indexOf('dummy') == -1) {
						// das aktuelle Benutzerfoto kann gelöscht werden
						// AJAX ausführen
						if (navigator.appName == "Microsoft Internet Explorer") {
							xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
						} else {
							xmlHttp = new XMLHttpRequest();
						}
					
						xmlHttp.open('get', 'ajax_file_delete.php?img=' + avatar_img);

						xmlHttp.onreadystatechange = function()
						{
							if (xmlHttp.readyState == 4) {
								// Anrede ermitteln
								var anrede = $('#anrede').val();
								
								// Bild wieder auf das Dummy-Pic (anhand der Anrede) zurücksetzen
								if (anrede == 'H') {
									$('#avatar_img').attr('src', './userpics/_dummy_pic_male.jpg');
								} else if (anrede =='C') {
									$('#avatar_img').attr('src', './userpics/_dummy_pic_company.jpg');
								} else {
									$('#avatar_img').attr('src', './userpics/_dummy_pic_female.jpg');
								}
							}
						}

						xmlHttp.send(null);
					}
				});
				
				$('#mitglied_speichern').click(function() {
					// Variable für die Fehlermeldung anlegen
					var error_msg = '';

					// zu allererst prüfen, ob alle Pflichtfelder korrekt ausgefüllt wurden
					// prüfen ob ein Vorname angegeben wurde
					if ($('#vorname').val().trim() == '') {
						// es wurde kein Vorname angegeben
						error_msg  = 'Ein von Dir eingegebenes Feld ist entweder leer oder fehlerhaft.<br />';
						error_msg += 'Bitte noch einmal versuchen, und diesmal einen richtigen Wert oder Text angeben!';
						
						// das entsprechende Feld Vorname als Fehler markieren
						$('#vorname').removeAttr('class').addClass('error_line');
					} else {
						// die normale Klasse des Feld Vorname wiederherstellen
						$('#vorname').removeAttr('class').addClass('mitglied_anlegen');
					}
					
					// prüfen ob ein Nachname angegeben wurde
					if ($('#nachname').val().trim() == '') {
						// es wurde kein Nachname angegeben
						error_msg  = 'Ein von Dir eingegebenes Feld ist entweder leer oder fehlerhaft.<br />';
						error_msg += 'Bitte noch einmal versuchen, und diesmal einen richtigen Wert oder Text angeben!';
						
						// das entsprechende Feld Nachname als Fehler markieren
						$('#nachname').removeAttr('class').addClass('error_line');
					} else {
						// die normale Klasse des Feld Nachname wiederherstellen
						$('#nachname').removeAttr('class').addClass('mitglied_anlegen');
					}
					
					if (getParam('acb_nr') == 'neu') {
						// prüfen ob eine Mitgliedsnummer angegeben wurde und
						// ob diese auch nicht bereits vergeben wurde
						if (($('#acb_nr').val() == '') || ($('#acb_nr').val() == '0')) {
							// es wurde keine Mitgliedsnummer angegeben
							error_msg  = 'Ein von Dir eingegebenes Feld ist entweder leer oder fehlerhaft.<br />';
							error_msg += 'Bitte noch einmal versuchen, und diesmal eine richtige Mitgliedsnummer angeben!';
							
							// das entsprechende Feld Mitgliedsnummer als Fehler markieren
							$('#acb_nr').removeAttr('class').addClass('error_line');
						} else if (pruefeMitgliedsnummer($('#acb_nr').val())) {
							// die eingegebene Mitgliedsnummer ist bereits vergeben
							error_msg  = 'Die von Dir eingegebene Mitgliedsnummer ist bereits einem anderen Mitglied zugeordnet und kann daher nicht verwendet werden.<br />';
							error_msg += 'Bitte noch einmal versuchen, und diesmal eine richtige Mitgliedsnummer angeben!';
							
							// das entsprechende Feld Mitgliedsnummer als Fehler markieren
							$('#acb_nr').removeAttr('class').addClass('error_line');
						} else {
							// die normale Klasse des Feld Mitgliedsnummer wiederherstellen
							$('#acb_nr').removeAttr('class').addClass('mitglied_anlegen');
						}
					
						// prüfen ob eine Referenz zu AmeAvia angegeben wurde
						// und ob diese auch nicht bereits vergeben wurde
						if ($('#ameavia').val() == '') {
							// es wurde keine Referenz zu AmeAvia angegeben
							error_msg  = 'Ein von Dir eingegebenes Feld ist entweder leer oder fehlerhaft.<br />';
							error_msg += 'Bitte noch einmal versuchen, und diesmal eine richtige Referenz zu AmeAvia angeben!';
							
							// das entsprechende Feld Referenz zu AmeAvia als Fehler markieren
							$('#ameavia').removeAttr('class').addClass('error_line');
						} else if (pruefeAmeAviaReferenz($('#ameavia').val())) {
							// die eingegebene Referenz zu AmeAvia ist bereits vergeben
							error_msg  = 'Die von Dir eingegebene Referenz zu AmeAvia ist bereits einem anderen Mitglied zugeordnet und kann daher nicht verwendet werden.<br />';
							error_msg += 'Bitte noch einmal versuchen, und diesmal eine richtige Referenz zu AmeAvia angeben!';
							
							// das entsprechende Feld Referenz zu AmeAvia als Fehler markieren
							$('#ameavia').removeAttr('class').addClass('error_line');
						} else {
							// die normale Klasse des Feld Referenz zu AmeAvia wiederherstellen
							$('#ameavia').removeAttr('class').addClass('mitglied_anlegen');
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
				
				$('#mitglied_cancel').click(function() {
					// die Fehlermeldung wird ausgeblendet, falls diese vorher bereits eingeblendet war
					if ($('#fehlermeldung').css('display') == 'inline') {
						// Fehlermeldung ausblenden, falls bereits da
						$('#fehlermeldung').css('display', 'none');
					}
				
					// die normale Klasse des Feld Vorname wiederherstellen
					$('#vorname').removeAttr('class').addClass('mitglied_anlegen');
					// die normale Klasse des Feld Nachname wiederherstellen
					$('#nachname').removeAttr('class').addClass('mitglied_anlegen');
					// die normale Klasse des Feld Mitgliedsnummer wiederherstellen
					$('#acb_nr').removeAttr('class').addClass('mitglied_anlegen');
					// die normale Klasse des Feld Referenz zu AmeAvia wiederherstellen
					$('#ameavia').removeAttr('class').addClass('mitglied_anlegen');
				
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
				
					<?php if (isset($_GET['acb_nr'])) : ?>
						<?php if ($_GET['acb_nr'] == 'neu') : ?>
							<!-- Mitglied NEUANLAGE -->
							<!-- Seitenüberschrift einfügen -->
							<h2>Mitglied anlegen</h2>
							<!-- Informationstext einfügen -->
							<div class="helpline">
								Hier kannst Du ein neues Mitglied anlegen und dessen pers&ouml;nlichen Daten eingeben.
								Das Hinzuf&uuml;gen eines Profilbildes (Avatar) ist ebenfalls m&ouml;glich.
							</div>
						<?php else : ?>
							<!-- Mitglied BEARBEITEN -->
							<!-- Seitenüberschrift einfügen -->
							<h2>Mitglied bearbeiten</h2>
							<!-- Informationstext einfügen -->
							<div class="helpline">
								Hier kannst Du die pers&ouml;nlichen Daten des ausgew&auml;hlten Mitglieds &auml;ndern und erg&auml;nzen.
								Das Hinzuf&uuml;gen eines Profilbildes (Avatar) ist ebenfalls m&ouml;glich.
							</div>
						<?php endif; ?>
					<?php endif; ?>
				
					<br />
					
					<!-- Fehlermeldung -->
					<div id="fehlermeldung" style="display: none;">
						<div class="errorline"></div><br />
					</div>
					<!-- Fehlermeldung -->

					<form action="mitglieder_edit.php?acb_nr=<?php if (isset($_GET['acb_nr'])) { echo $_GET['acb_nr']; } ?>&action=speichern" method="POST">
					
						<div id="tabs" style="height: 800px;">
							<ul>
								<li style="font-size: 10pt; font-family: Tahoma, Sans-Serif;"><a href="#tabs-1">Pers&ouml;nliche Daten</a></li>
								<li style="font-size: 10pt; font-family: Tahoma, Sans-Serif;"><a href="#tabs-2">Vereinsmitgliedschaft</a></li>
								<li style="font-size: 10pt; font-family: Tahoma, Sans-Serif;"><a href="#tabs-3">T&auml;tigkeiten/Fachausweise</a></li>
							</ul>
							<div id="tabs-1">
								<fieldset style="width: 97%; background-color: #eeeeee;">
									<legend style="font-size: 11pt;"><img src="./img/people_group_users_friends-16.png" align="left" hspace="5" /> Pers&ouml;nliche Daten</legend>
							
									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd">
											<th align="left" width="160" style="padding-left: 8px;"><label for="anrede">Anrede / Titel:</label></th>
											<td width="400">
												<select name="anrede" id="anrede" class="mitglied_anlegen" style="width: 200px;" tabindex="1">
													<?php if (isset($data['anrede'])) : ?>
														<option value="H" <?php if ($data['anrede'] == 'H') { echo 'selected="selected"'; } ?>>Herr</option>
														<option value="F" <?php if ($data['anrede'] == 'F') { echo 'selected="selected"'; } ?>>Frau</option>
														<option value="C" <?php if ($data['anrede'] == 'C') { echo 'selected="selected"'; } ?>>Firma</option>
													<?php else : ?>
														<option value="H">Herr</option>
														<option value="F">Frau</option>
														<option value="C">Firma</option>
													<?php endif; ?>
												</select>
												<input type="text" name="titel" id="titel" maxlength="10" tabindex="2" value="" class="mitglied_anlegen" style="width: 196px;" />
											</td>
											<td></td>
										</tr>
										<tr bgcolor="#eeeeee">
											<th align="left" width="160" style="padding-left: 8px;"><label for="vorname">Vorname: <span class="pflichtfeld">*)</span></label></th>
											<td width="400"><input type="text" name="vorname" id="vorname" maxlength="30" tabindex="3" value="<?php if (isset($data['vorname'])) { echo $data['vorname']; } ?>" class="mitglied_anlegen" style="width: 400px;" /></td>
											<td></td>
										</tr>
										<tr bgcolor="#dddddd">
											<th align="left" width="160" style="padding-left: 8px;"><label for="nachname">Nachname: <span class="pflichtfeld">*)</span></label></th>
											<td width="400"><input type="text" name="nachname" id="nachname" maxlength="30" tabindex="4" value="<?php if (isset($data['nachname'])) { echo $data['nachname']; } ?>" class="mitglied_anlegen" style="width: 400px;" /></td>
											<td></td>
										</tr>
										<tr bgcolor="#eeeeee">
											<th align="left" width="160" style="padding-left: 8px;"><label for="geburtsdatum">Geburtsdatum:</label></th>
											<td width="400"><input type="text" name="geburtsdatum" id="geburtsdatum" maxlength="10" tabindex="5" value="<?php if (isset($data['geburtsdatum'])) { echo $data['geburtsdatum']; } ?>" class="mitglied_anlegen" /></td>
											<td></td>
										</tr>
									</table>
								</fieldset>
								
								<fieldset style="width: 97%; background-color: #eeeeee; margin-top: 10px;">
									<legend style="font-size: 11pt;"><img src="./img/envelope_symbol_simple.png" align="left" hspace="5" /> Kontakt</legend>

									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd">
											<th align="left" width="160" style="padding-left: 8px;"><label for="strasse">Stra&szlig;e:</label></th>
											<td width="400"><input type="text" name="strasse" id="strasse" maxlength="30" tabindex="6" value="<?php if (isset($data['strasse'])) { echo $data['strasse']; } ?>" class="adresse" style="width: 400px;" /></td>
											<td align="left"><a id="goto_googlemaps" name="goto_googlemaps" target="_blank" <?php if (isset($data['googlemaps'])) { printf('href="%s"', $data['googlemaps']); } ?>><img src="./img/13-googlemaps_icon-e1375377036164.png" align="left" border="0" height="22" /></a></td>
										</tr>
										<tr bgcolor="#eeeeee">
											<th align="left" width="160" style="padding-left: 8px;"><label for="plz">PLZ / Wohnort:</label></th>
											<td width="400" nowrap>
												<input type="text" name="plz" id="plz" value="<?php if (isset($data['plz'])) { echo $data['plz']; } ?>" maxlength="10" tabindex="7" class="adresse" style="width: 130px;" />
												<input type="text" name="ort" id="ort" value="<?php if (isset($data['ort'])) { echo $data['ort']; } ?>" maxlength="30" tabindex="8" class="adresse" style="width: 266px;" />
											</td>
											<td></td>
										</tr>
										<tr bgcolor="#dddddd">
											<th align="left" width="160" style="padding-left: 8px;"><label for="land">Land:</label></th>
											<td width="400">
												<select name="land" id="land" tabindex="9" class="mitglied_anlegen" style="width: 400px;">
													<?php if (isset($data['land'])) { echo getListeLaender($data['land']); } else { echo getListeLaender(); } ?>
												</select>
											</td>
											<td></td>
										</tr>
									</table>
									
									<hr />

									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd">
											<th align="left" width="160" style="padding-left: 8px;"><label for="telefon1">Telefon <small>(priv.)</small>:</label></th>
											<td width="400"><input type="text" name="telefon1" id="telefon1" value="<?php if (isset($data['telefon1'])) { echo $data['telefon1']; } ?>" maxlength="20" tabindex="10" class="mitglied_anlegen" style="width: 400px;" /></td>
											<td></td>
										</tr>
										<tr bgcolor="#eeeeee">
											<th align="left" width="160" style="padding-left: 8px;"><label for="telefon2">Telefon <small>(dientl.)</small>:</label></th>
											<td width="400"><input type="text" name="telefon2" id="telefon2" value="<?php if (isset($data['telefon2'])) { echo $data['telefon2']; } ?>" maxlength="20" tabindex="11" class="mitglied_anlegen" style="width: 400px;" /></td>
											<td></td>
										</tr>
										<tr bgcolor="#dddddd">
											<th align="left" width="160" style="padding-left: 8px;"><label for="mobil1">Mobil <small>(priv.)</small>:</label></th>
											<td width="400"><input type="text" name="mobil1" id="mobil1" value="<?php if (isset($data['mobil1'])) { echo $data['mobil1']; } ?>" maxlength="20" tabindex="12" class="mitglied_anlegen" style="width: 400px;" /></td>
											<td></td>
										</tr>
										<tr bgcolor="#eeeeee">
											<th align="left" width="160" style="padding-left: 8px;"><label for="mobil2">Mobil <small>(dienstl.)</small>:</label></th>
											<td width="400"><input type="text" name="mobil2" id="mobil2" value="<?php if (isset($data['mobil2'])) { echo $data['mobil2']; } ?>" maxlength="20" tabindex="13" class="mitglied_anlegen" style="width: 400px;" /></td>
											<td></td>
										</tr>
									</table>
									
									<hr />

									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd">
											<th align="left" width="160" style="padding-left: 8px;"><label for="email">eMail:</label></th>
											<td width="400"><input type="text" name="email" id="email" value="<?php if (isset($data['email'])) { echo $data['email']; } ?>" maxlength="100" tabindex="14" class="mitglied_anlegen" style="width: 400px; color: #0000ff; text-decoration: underline;" /></td>
											<td align="left"><a id="goto_email" name="goto_email" <?php if (isset($data['goto_email'])) { printf('href="%s"', $data['goto_email']); } ?>><img src="./img/Forward.png" align="left" border="0" height="22" /></a></td>
										</tr>
										<tr bgcolor="#eeeeee">
											<th align="left" width="160" style="padding-left: 8px;"><label for="www">Homepage:</label></th>
											<td width="400"><input type="text" name="www" id="www" value="<?php if (isset($data['www'])) { echo $data['www']; } ?>" maxlength="100" tabindex="15" class="mitglied_anlegen" style="width: 400px; color: #0000ff; text-decoration: underline;" /></td>
											<td align="left"><a id="goto_www" name="goto_www" target="_blank" <?php if (isset($data['goto_www'])) { printf('href="%s"', $data['goto_www']); } ?>><img src="./img/Earth-icon.png" align="left" border="0" height="22" /></a></td>
										</tr>
									</table>
								</fieldset>
								
								<fieldset style="width: 97%; background-color: #eeeeee; margin-top: 10px;">
									<legend style="font-size: 11pt;"><img src="./img/bank.png" align="left" hspace="5" /> Bankverbindung</legend>

									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd">
											<th align="left" width="160" style="padding-left: 8px;"><label for="bank">Bank:</label></th>
											<td width="400"><input type="text" name="bank" id="bank" maxlength="100" tabindex="16" value="<?php if (isset($data['bank'])) { echo $data['bank']; } ?>" class="mitglied_anlegen" style="width: 400px;" /></td>
											<td></td>
										</tr>
										<tr bgcolor="#eeeeee">
											<th align="left" width="160" style="padding-left: 8px;"><label for="blz">Bankleitzahl:</label></th>
											<td width="400"><input type="text" name="blz" id="blz" value="<?php if (isset($data['blz'])) { echo $data['blz']; } ?>" maxlength="8" tabindex="17" class="mitglied_anlegen" style="width: 400px;" /></td>
											<td></td>
										</tr>
										<tr bgcolor="#dddddd">
											<th align="left" width="160" style="padding-left: 8px;"><label for="kto">Kontonummer:</label></th>
											<td width="400"><input type="text" name="kto" id="kto" value="<?php if (isset($data['kto'])) { echo $data['kto']; } ?>" maxlength="20" tabindex="18" class="mitglied_anlegen" style="width: 400px;" /></td>
											<td></td>
										</tr>
										<tr bgcolor="#eeeeee">
											<th align="left" width="160" style="padding-left: 8px;"><label for="ktoinhaber">Konto Inhaber:</label></th>
											<td width="400"><input type="text" name="ktoinhaber" id="ktoinhaber" value="<?php if (isset($data['ktoinhaber'])) { echo $data['ktoinhaber']; } ?>" maxlength="100" tabindex="19" class="mitglied_anlegen" style="width: 400px;" /></td>
											<td></td>
										</tr>
									</table>
								</fieldset>
							</div>
							
							<div id="tabs-2">
								<fieldset style="width: 97%; background-color: #eeeeee;">
									<legend style="font-size: 11pt;"><img src="./img/star_1.png" align="left" hspace="5" /> Mitgliedschaft</legend>

									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd">
											<th align="left" width="160" style="padding-left: 8px;"><label for="acb_nr">Mitgliedsnummer: <span class="pflichtfeld">*)</span></label></th>
											<td width="450">
												<?php if (isset($data['acb_nr'])) : ?>
													<input type="text" name="acb_nr" id="acb_nr" value="<?php echo $data['acb_nr']; ?>" maxlength="5" tabindex="20" class="mitglied_anlegen_gesperrt" style="width: 132px;" readonly="readonly" onfocus="this.blur();" />
													<img style="position: relative; left 0px; top: 2px;" title="Prim&auml;rschl&uuml;ssel => Mitgliedsnummer" src="./img/1351092510_key.png">
												<?php else : ?>
													<input type="text" name="acb_nr" id="acb_nr" value="" maxlength="5" tabindex="20" class="mitglied_anlegen" style="width: 132px;" />
													<img style="position: relative; left 0px; top: 2px;" title="Prim&auml;rschl&uuml;ssel => Mitgliedsnummer" src="./img/1351092510_key.png">
												<?php endif; ?>
											</td>
											<td></td>
										</tr>
										<tr bgcolor="#eeeeee">
											<th align="left" width="160" style="padding-left: 8px;"><label for="ameavia">AmeAvia Referenz: <span class="pflichtfeld">*)</span></label></th>
											<td width="450">
												<?php if (isset($data['ameavia'])) : ?>
													<input type="text" name="ameavia" id="ameavia" value="<?php echo $data['ameavia']; ?>" maxlength="60" tabindex="21" class="mitglied_anlegen_gesperrt" style="width: 400px;" readonly="readonly" onfocus="this.blur();" />
												<?php else : ?>
													<input type="text" name="ameavia" id="ameavia" value="" maxlength="60" tabindex="21" class="mitglied_anlegen" style="width: 400px;" />
												<?php endif; ?>
											</td>
											<td align="left"><img style="position: relative; left 0px; top: 2px;" height="22" title="Referenz => AmeAvia" src="./img/database_key.png"></td>
										</tr>
										<tr bgcolor="#dddddd">
											<th align="left" width="160" style="padding-left: 8px;"><label for="status">Mitgliedsstatus:</label></th>
											<td width="450">
												<select name="status" id="status" tabindex="22" class="mitglied_anlegen" style="width: 199px;">
													<?php if (isset($data['status'])) { echo getListeMitgliedsstatus($data['status']); } else { echo getListeMitgliedsstatus(); } ?>
												</select>
												<strong><label for="sort">Sort:</label></strong>
												<input type="text" name="sort" id="sort" value="<?php if (isset($data['sort'])) { echo $data['sort']; } else { echo '0'; } ?>" maxlength="3" tabindex="23" class="mitglied_anlegen" style="width: 40px;" />
											</td>
											<td></td>
										</tr>
										<tr bgcolor="#eeeeee">
											<th align="left" width="160" style="padding-left: 8px;"><label for="eintritt">Beitrittsdatum:</label></th>
											<td width="450"><input type="text" name="eintritt" id="eintritt" value="<?php if (isset($data['eintritt'])) { echo $data['eintritt']; } ?>" maxlength="10" tabindex="24" class="mitglied_anlegen" /></td>
											<td></td>
										</tr>
										<tr bgcolor="#dddddd">
											<th align="left" width="160" style="padding-left: 8px;"><label for="austritt">Austrittsdatum:</label></th>
											<td width="450"><input type="text" name="austritt" id="austritt" value="<?php if (isset($data['austritt'])) { echo $data['austritt']; } ?>" maxlength="10" tabindex="25" class="mitglied_anlegen" /></td>
											<td></td>
										</tr>
										<tr bgcolor="#eeeeee" height="28">
											<th align="left" width="160" style="padding-left: 8px;"><label for="in_abrechn">Austritt Check:</label></th>
											<td width="450">
												<?php if (isset($data['acb_nr']) && ($data['acb_nr'] != 'neu')) : ?>
													<input type="checkbox" name="in_abrechn" id="in_abrechn" tabindex="26" <?php if (isset($data['in_abrechn']) && ($data['in_abrechn'] == 'N')) { echo 'checked="checked"'; } ?>/>
												<?php else : ?>
													<input type="checkbox" name="in_abrechn" id="in_abrechn" tabindex="26" <?php if (isset($data['in_abrechn']) && ($data['in_abrechn'] == 'N')) { echo 'checked="checked"'; } ?> disabled="disabled" onfocus="this.blur();" />
												<?php endif; ?>
											</td>
											<td></td>
										</tr>
										<tr bgcolor="#dddddd" height="28">
											<th align="left" width="160" style="padding-left: 8px;"><label for="datenschutz">Datenschutzerkl&auml;rung:</label></th>
											<td width="450"><input type="checkbox" name="datenschutz" id="datenschutz" tabindex="27" <?php if (isset($data['datenschutz']) && ($data['datenschutz'] == 'J')) { echo 'checked="checked"'; } ?>/></td>
											<td></td>
										</tr>
										<tr bgcolor="#eeeeee" height="28">
											<th align="left" width="160" style="padding-left: 8px;"><label for="rundmail">Rundmailempf&auml;nger:</label></th>
											<td width="450"><input type="checkbox" name="rundmail" id="rundmail" tabindex="28" <?php if (isset($data['rundmail']) && ($data['rundmail'] == 'J')) { echo 'checked="checked"'; } ?>/></td>
											<td></td>
										</tr>
										<tr bgcolor="#dddddd" height="28">
											<th align="left" width="160" style="padding-left: 8px;"><label for="fl_dienst_absprache">Flugleiter-Dienst <small>(1)</small>:</label></th>
											<td width="450">
												<input type="checkbox" name="fl_dienst_absprache" id="fl_dienst_absprache" tabindex="29" <?php if (isset($data['fl_dienst_absprache']) && ($data['fl_dienst_absprache'] == 'J')) { echo 'checked="checked"'; } ?>/>
												<span style="position:relative;left:2px;top:-2px;"><strong>nur nach Absprache</strong></span>
											</td>
											<td></td>
										</tr>
										<tr bgcolor="#eeeeee" height="28">
											<th align="left" width="160" style="padding-left: 8px;"><label for="fl_dienst_wochentags">Flugleiter-Dienst <small>(2)</small>:</label></th>
											<td width="450">
												<input type="checkbox" name="fl_dienst_wochentags" id="fl_dienst_wochentags" tabindex="30" <?php if (isset($data['fl_dienst_wochentags']) && ($data['fl_dienst_wochentags'] == 'J')) { echo 'checked="checked"'; } ?>/>
												<span style="position:relative;left:2px;top:-2px;"><strong>im Bedarfsfall auch au&szlig;erhalb der offiziellen Betriebszeiten</strong></span>
											</td>
											<td></td>
										</tr>
									</table>
									
									<hr />

									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd">
											<th align="left" width="160" valign="top" style="padding-top: 10px; padding-left: 8px;"><label for="funktion">Funktion(en):</label></th>
											<td width="400"><textarea name="funktion" id="funktion" maxlength="255" tabindex="31" class="mitglied_anlegen" style="width: 400px; height: 140px; padding: 5px !important;"><?php if (isset($data['funktion'])) { echo $data['funktion']; } ?></textarea></td>
											<td></td>
										</tr>
									</table>
								</fieldset>
								
								<fieldset style="width: 97%; background-color: #eeeeee; margin-top: 10px;">
									<legend style="font-size: 11pt;"><img src="./img/cam_icon.gif" align="left" hspace="5" /> Profilbild</legend>
									
									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd">
											<td valign="top">
												<table cellspacing="5" cellpadding="0" border="0">
													<tr>
														<th width="160" valign="middle" style="font-size: 9pt;"><label for="avatar">Profilbild ausw&auml;hlen</label></th>
														<td width="310" valign="middle">
															<input type="file" style="display:none;position:absolute;" name="dateiauswahl_upload" id="dateiauswahl_upload" />
															<input type="text" tabindex="32" maxlength="255" style="font-size:9pt;width:300px;" class="mitglied_anlegen" name="avatar" id="avatar" readonly="readonly" />
														</td>
													</tr>
													<tr>
														<th width="160"></th>
														<td width="310" valign="middle">
															<button type="button" tabindex="33" id="avatar_upload" name="avatar_upload" style="width: 147px; height: 23px;">Aktualisieren</button>
															<button type="button" tabindex="34" id="avatar_delete" name="avatar_delete" style="width: 147px; height: 23px;">Bild l&ouml;schen</button>
														</td>
													</tr>
													<tr height="30">
														<th width="160"></th>
														<td width="310" valign="bottom" style="font-size: 7pt; color: #ff0000;">
															Hinweis: maximale Dateigr&ouml;&szlig;e = 64 kb,<br>Dateityp = .jpg oder .png
														</td>
													</tr>
												</table>
											</td>
											<td width="160" valign="middle" align="right">
												<img width="110" height="140" hspace="5" vspace="5" style="border: 1px solid #333333;" id="avatar_img" name="avatar_img" src="./userpics/<?php if (isset($data['avatar_img'])) { echo $data['avatar_img']; } else { echo '_dummy_pic_male.jpg'; } ?>">
												<input type="hidden" value="<?php if (isset($data['avatar_img'])) { echo $data['avatar_img']; } ?>" name="userpic" id="userpic">
											</td>
										</tr>
									</table>
								</fieldset>
							</div>
							<div id="tabs-3">
								<fieldset style="width: 97%; background-color: #eeeeee;">
									<legend style="font-size: 11pt;"><img src="./img/dialog_icon.png" align="left" hspace="5" style="margin-top: 2px;" /> Lizenz<small>(en)</small> / Berechtigung<small>(en)</small></legend>
							
									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd">
											<th align="left" width="150" style="padding-left: 8px;"><label for="ppladat">PPL(A) g&uuml;ltig bis:</label></th>
											<td width="400"><input type="text" name="ppladat" id="ppladat" maxlength="10" tabindex="35" value="<?php if (isset($data['ppladat'])) { echo $data['ppladat']; } ?>" class="mitglied_anlegen" /></td>
											<td></td>
										</tr>
										<tr bgcolor="#eeeeee">
											<th align="left" width="150" style="padding-left: 8px;"><label for="pplbdat">PPL(B) g&uuml;ltig bis:</label></th>
											<td width="400"><input type="text" name="pplbdat" id="pplbdat" maxlength="10" tabindex="36" value="<?php if (isset($data['pplbdat'])) { echo $data['pplbdat']; } ?>" class="mitglied_anlegen" /></td>
											<td></td>
										</tr>
										<tr bgcolor="#dddddd">
											<th align="left" width="150" style="padding-left: 8px;"><label for="pplcdat">PPL(C) g&uuml;ltig bis:</label></th>
											<td width="400"><input type="text" name="pplcdat" id="pplcdat" maxlength="10" tabindex="37" value="<?php if (isset($data['pplcdat'])) { echo $data['pplcdat']; } ?>" class="mitglied_anlegen" /></td>
											<td></td>
										</tr>
										<tr bgcolor="#eeeeee">
											<th align="left" width="150" style="padding-left: 8px;"><label for="uldat">UL-Schein g&uuml;ltig bis:</label></th>
											<td width="400"><input type="text" name="uldat" id="uldat" maxlength="10" tabindex="38" value="<?php if (isset($data['uldat'])) { echo $data['uldat']; } ?>" class="mitglied_anlegen" /></td>
											<td></td>
										</tr>
										<tr bgcolor="#dddddd">
											<th align="left" width="150" style="padding-left: 8px;"><label for="medical">Medical g&uuml;ltig bis:</label></th>
											<td width="400"><input type="text" name="medical" id="medical" maxlength="10" tabindex="39" value="<?php if (isset($data['medical'])) { echo $data['medical']; } ?>" class="mitglied_anlegen" /></td>
											<td></td>
										</tr>
										<tr bgcolor="#eeeeee" height="26">
											<th align="left" width="150" style="padding-left: 8px;"><label for="jar_tmg">JAR-FCL TMG:</label></th>
											<td width="400"><input type="checkbox" name="jar_tmg" id="jar_tmg" tabindex="40" <?php if (isset($data['jar_tmg']) && ($data['jar_tmg'] == 'J')) { echo 'checked="checked"'; } ?>/></td>
											<td></td>
										</tr>
										<tr bgcolor="#dddddd" height="26">
											<th align="left" width="150" style="padding-left: 8px;"><label for="jar_sep">JAR-FCL SEP:</label></th>
											<td width="400"><input type="checkbox" name="jar_sep" id="jar_sep" tabindex="41" <?php if (isset($data['jar_sep']) && ($data['jar_sep'] == 'J')) { echo 'checked="checked"'; } ?>/></td>
											<td></td>
										</tr>
									</table>
								</fieldset>

								<fieldset style="width: 97%; background-color: #eeeeee; margin-top: 10px;">
									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd">
											<th align="left" width="150" style="padding-left: 8px;"><label for="hlbnr">HLB-Nummer:</label></th>
											<td width="400"><input type="text" name="hlbnr" id="hlbnr" value="<?php if (isset($data['hlbnr'])) { echo $data['hlbnr']; } ?>" maxlength="10" tabindex="42" class="mitglied_anlegen" style="width: 132px;" /></td>
											<td></td>
										</tr>
									</table>
								</fieldset>
								
								<fieldset style="width: 97%; background-color: #eeeeee; margin-top: 10px;">
									<legend style="font-size: 11pt;"><img src="./img/icon-settings.png" align="left" hspace="5" /> T&auml;tigkeit<small>(en)</small></legend>

									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd" height="24">
											<th align="left" width="150" style="padding-left: 8px;"><label for="job_motorflug"><u>A</u> Motorflug:</label></th>
											<td width="50"><input type="checkbox" name="job_motorflug" id="job_motorflug" tabindex="43" <?php if ((isset($data['taetigkeiten']) && (strpos($data['taetigkeiten'], 'A') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_motorsegler"><u>B</u> Motorsegler:</label></th>
											<td width="50"><input type="checkbox" name="job_motorsegler" id="job_motorsegler" tabindex="44" <?php if ((isset($data['taetigkeiten']) && (strpos($data['taetigkeiten'], 'B') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_segelflug"><u>C</u> Segelflug:</label></th>
											<td><input type="checkbox" name="job_segelflug" id="job_segelflug" tabindex="45" <?php if ((isset($data['taetigkeiten']) && (strpos($data['taetigkeiten'], 'C') !== false))) { echo 'checked="checked"';} ?>/></td>
										</tr>
										<tr bgcolor="#eeeeee" height="24">
											<th align="left" width="150" style="padding-left: 8px;"><label for="job_modellflug"><u>D</u> Modellflug:</label></th>
											<td width="50"><input type="checkbox" name="job_modellflug" id="job_modellflug" tabindex="46" <?php if ((isset($data['taetigkeiten']) && (strpos($data['taetigkeiten'], 'D') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_fallschirm"><u>E</u> Fallschirmspringen:</label></th>
											<td width="50"><input type="checkbox" name="job_fallschirm" id="job_fallschirm" tabindex="47" <?php if ((isset($data['taetigkeiten']) && (strpos($data['taetigkeiten'], 'R') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_ballon"><u>F</u> Ballonfahren:</label></th>
											<td><input type="checkbox" name="job_ballon" id="job_ballon" tabindex="48" <?php if ((isset($data['taetigkeiten']) && (strpos($data['taetigkeiten'], 'F') !== false))) { echo 'checked="checked"';} ?>/></td>
										</tr>
										<tr bgcolor="#dddddd" height="24">
											<th align="left" width="150" style="padding-left: 8px;"><label for="job_drachen"><u>G</u> Drachenfliegen:</label></th>
											<td width="50"><input type="checkbox" name="job_drachen" id="job_drachen" tabindex="49" <?php if ((isset($data['taetigkeiten']) && (strpos($data['taetigkeiten'], 'G') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_ul"><u>H</u> Ultraleichtflug:</label></th>
											<td width="50"><input type="checkbox" name="job_ul" id="job_ul" tabindex="50" <?php if ((isset($data['taetigkeiten']) && (strpos($data['taetigkeiten'], 'H') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_jugendleiter"><u>I</u> Jugendleiter:</label></th>
											<td><input type="checkbox" name="job_jugendleiter" id="job_jugendleiter" tabindex="51" <?php if ((isset($data['taetigkeiten']) && (strpos($data['taetigkeiten'], 'I') !== false))) { echo 'checked="checked"';} ?>/></td>
										</tr>
										<tr bgcolor="#eeeeee" height="24">
											<th align="left" width="150" style="padding-left: 8px;"><label for="job_uebungsleiter"><u>J</u> &Uuml;bungsleiter:</label></th>
											<td width="50"><input type="checkbox" name="job_uebungsleiter" id="job_uebungsleiter" tabindex="52" <?php if ((isset($data['taetigkeiten']) && (strpos($data['taetigkeiten'], 'J') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_trainer"><u>K</u> Trainer:</label></th>
											<td width="50"><input type="checkbox" name="job_trainer" id="job_trainer" tabindex="53" <?php if ((isset($data['taetigkeiten']) && (strpos($data['taetigkeiten'], 'K') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_sonstige"><u>L</u> Sonstige T&auml;tigkeiten:</label></th>
											<td><input type="checkbox" name="job_sonstige" id="job_sonstige" tabindex="54" <?php if ((isset($data['taetigkeiten']) && (strpos($data['taetigkeiten'], 'L') !== false))) { echo 'checked="checked"';} ?>/></td>
										</tr>
										<tr bgcolor="#dddddd" height="24">
											<th align="left" width="150" style="padding-left: 8px;"><label for="job_gleitschirm"><u>M</u> Gleitschirmfliegen:</label></th>
											<td colspan="5"><input type="checkbox" name="job_gleitschirm" id="job_gleitschirm" tabindex="55" <?php if ((isset($data['taetigkeiten']) && (strpos($data['taetigkeiten'], 'M') !== false))) { echo 'checked="checked"';} ?>/></td>
										</tr>
									</table>
								</fieldset>
								
								<fieldset style="width: 97%; background-color: #eeeeee; margin-top: 10px;">
									<legend style="font-size: 11pt;"><img src="./img/tick_circle_frame.png" align="left" hspace="5" /> Fachausweis<small>(e)</small></legend>

									<table width="100%" cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
										<tr bgcolor="#dddddd" height="24">
											<th align="left" width="150" style="padding-left: 8px;"><label for="job_motorfluglehrer"><u>A</u> Motorfluglehrer:</label></th>
											<td width="50"><input type="checkbox" name="job_motorfluglehrer" id="job_motorfluglehrer" tabindex="56" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'A') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_moselehrer"><u>B</u> Motorseglerlehrer:</label></th>
											<td width="50"><input type="checkbox" name="job_moselehrer" id="job_moselehrer" tabindex="57" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'B') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_segelfluglehrer"><u>C</u> Segelfluglehrer:</label></th>
											<td><input type="checkbox" name="job_segelfluglehrer" id="job_segelfluglehrer" tabindex="58" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'C') !== false))) { echo 'checked="checked"';} ?>/></td>
										</tr>
										<tr bgcolor="#eeeeee" height="24">
											<th align="left" width="150" style="padding-left: 8px;"><label for="job_modellfluglehrer"><u>D</u> Modellfluglehrer:</label></th>
											<td width="50"><input type="checkbox" name="job_modellfluglehrer" id="job_modellfluglehrer" tabindex="59" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'D') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_sprunglehrer"><u>E</u> Sprunglehrer:</label></th>
											<td width="50"><input type="checkbox" name="job_sprunglehrer" id="job_sprunglehrer" tabindex="60" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'E') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_ballonausbilder"><u>F</u> Ballonausbilder:</label></th>
											<td><input type="checkbox" name="job_ballonausbilder" id="job_ballonausbilder" tabindex="61" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'F') !== false))) { echo 'checked="checked"';} ?>/></td>
										</tr>
										<tr bgcolor="#dddddd" height="24">
											<th align="left" width="150" style="padding-left: 8px;"><label for="job_drachenfluglehrer"><u>G</u> Drachenfluglehrer:</label></th>
											<td width="50"><input type="checkbox" name="job_drachenfluglehrer" id="job_drachenfluglehrer" tabindex="62" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'G') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_ul_fluglehrer"><u>H</u> UL-Fluglehrer:</label></th>
											<td width="50"><input type="checkbox" name="job_ul_fluglehrer" id="job_ul_fluglehrer" tabindex="63" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'H') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_werkstattleiter"><u>I</u> Werkstattleiter:</label></th>
											<td><input type="checkbox" name="job_werkstattleiter" id="job_werkstattleiter" tabindex="64" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'I') !== false))) { echo 'checked="checked"';} ?>/></td>
										</tr>
										<tr bgcolor="#eeeeee" height="24">
											<th align="left" width="150" style="padding-left: 8px;"><label for="job_flugzeugwart"><u>J</u> Flugzeugwart:</label></th>
											<td width="50"><input type="checkbox" name="job_flugzeugwart" id="job_flugzeugwart" tabindex="65" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'J') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_mosewart"><u>K</u> Motorseglerwart:</label></th>
											<td width="50"><input type="checkbox" name="job_mosewart" id="job_mosewart" tabindex="66" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'K') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_segelflugwart"><u>L</u> Segelflugzeugwart:</label></th>
											<td><input type="checkbox" name="job_segelflugwart" id="job_segelflugwart" tabindex="67" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'L') !== false))) { echo 'checked="checked"';} ?>/></td>
										</tr>
										<tr bgcolor="#dddddd" height="24">
											<th align="left" width="150" style="padding-left: 8px;"><label for="job_fallschirmwart"><u>M</u> Fallschirmpacker:</label></th>
											<td width="50"><input type="checkbox" name="job_fallschirmwart" id="job_fallschirmwart" tabindex="68" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'M') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_ballonwart"><u>N</u> Ballonwart:</label></th>
											<td width="50"><input type="checkbox" name="job_ballonwart" id="job_ballonwart" tabindex="69" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'N') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_pruefer"><u>T</u> Pr&uuml;fer:</label></th>
											<td><input type="checkbox" name="job_pruefer" id="job_pruefer" tabindex="70" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'T') !== false))) { echo 'checked="checked"';} ?>/></td>
										</tr>
										<tr bgcolor="#eeeeee" height="24">
											<th align="left" width="150" style="padding-left: 8px;"><label for="job_zeuge_motorflug"><u>O</u> Zeuge Motorflug:</label></th>
											<td width="50"><input type="checkbox" name="job_zeuge_motorflug" id="job_zeuge_motorflug" tabindex="71" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'O') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_zeuge_segelflug"><u>P</u> Zeuge Segelflug:</label></th>
											<td width="50"><input type="checkbox" name="job_zeuge_segelflug" id="job_zeuge_segelflug" tabindex="72" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'P') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_zeuge_modellflug"><u>R</u> Zeuge Modellflug:</label></th>
											<td><input type="checkbox" name="job_zeuge_modellflug" id="job_zeuge_modellflug" tabindex="73" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'R') !== false))) { echo 'checked="checked"';} ?>/></td>
										</tr>
										<tr bgcolor="#dddddd" height="24">
											<th align="left" width="150" style="padding-left: 8px;"><label for="job_zeuge_fallschirm"><u>S</u> Zeuge Fallschirm:</label></th>
											<td width="50"><input type="checkbox" name="job_zeuge_fallschirm" id="job_zeuge_fallschirm" tabindex="74" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'S') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_sonstiges"><u>X</u> Sonstiges:</label></th>
											<td width="50"><input type="checkbox" name="job_sonstiges" id="job_sonstiges" tabindex="75" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'X') !== false))) { echo 'checked="checked"';} ?>/></td>
											<th align="left" width="160" style="padding-left: 8px;"><label for="job_flugleiter"><u>U</u> Flugleiter:</label></th>
											<td><input type="checkbox" name="job_flugleiter" id="job_flugleiter" tabindex="76" <?php if ((isset($data['fachausweise']) && (strpos($data['fachausweise'], 'U') !== false))) { echo 'checked="checked"';} ?>/></td>
										</tr>
									</table>
								</fieldset>
							</div>
						</div>

						<div class="mitglied_speichern_buttons">
							<input type="submit" tabindex="77" name="mitglied_speichern" id="mitglied_speichern" value="Daten speichern" style="width: 150px; margin-left: 10px;" />
							<input type="reset" tabindex="78" name="mitglied_cancel" id="mitglied_cancel" value="Abbrechen" style="width: 150px;" />
							
							<span class="pflichtfeld" style="font: 9pt Verdana; margin-left: 20px;">*) Pflichtfelder</span>
						</div>

					</form>

				</td>
			</tr>
		</table>

	</body>

</html>
<!-- ENDE: AUSGABE -->