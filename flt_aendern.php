<!-- BEGINN: SKRIPT -->
<?php

	// allgemeine Funktionen einbinden
	include_once('./functions.php');
	
	/*
	 * getLogbuch()
	 *
	 * alle Kopfdaten zur ausgewählten Startliste werden geladen
	 *
	 * @params date  $datum
	 * @return array $data
	 */
	if (!function_exists('getLogbuch')) {
		function getLogbuch($datum)
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
			// die Daten für die aktuell ausgewählte Startliste laden
			$sql = sprintf('
				SELECT
					*
				FROM
					`logbuch`
				WHERE
					`datum` = "%s"
				LIMIT 1
			',
				$datum
			);
			
			// zuvor definierte SQL-Anweisung ausführen
			// Anzahl der Datensätze sollte größer als 0 sein um TRUE zurückzugeben
			$db_erg = mysql_query($sql);

			// es sind Datensätze vorhanden
			while ($zeile = mysql_fetch_object($db_erg)) {
				// Daten übernehmen wie hinterlegt
				$data['datum']         = $zeile->datum;
				$data['startrichtung'] = $zeile->startrichtung;
				$data['windrichtung']  = $zeile->windrichtung;
				$data['windstaerke']   = $zeile->windstaerke;
				$data['bewoelkung']    = $zeile->bewoelkung;
				$data['temperatur']    = $zeile->temperatur;
				$data['luftdruck']     = $zeile->luftdruck;
				$data['bemerkungen']   = $zeile->bemerkungen;
				$data['wetter']        = $zeile->wetter;
			}

			// Verbindung zur Datenbank schließen
			mysql_free_result($db_erg);
			
			// Rückgabe der Mitgliedsdaten
			return $data;
		}
	}
	
	/*
	 * updateLogbuch()
	 *
	 * aktualisiert ein bereits vorhandenes Logbuch anhand der
	 * übergebenen Parameter und des übergebenen Datums
	 *
	 * @params array $params
	 * @params date  $datum
	 */
	if (!function_exists('updateLogbuch')) {
		function updateLogbuch(array $params, $datum)
		{
			// Modul für DB-Zugriff einbinden
			require_once('konfiguration.php');

			// Verbindung zur Datenbank herstellen
			// am System mit Host, Benutzernamen und Password anmelden
			@mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT) or die('Could not connect to mysql server.' );
			@mysql_select_db(MYSQL_DATENBANK) or die('Could not select database.');

			// SQL-Befehl zurechtfuddeln,
			// Befehl zum Speichern einer Veränderung eines Logbuches
			$sql = sprintf('
				UPDATE
					`logbuch`
				SET
					`startrichtung` = %s,
					`windrichtung` = %s,
					`windstaerke` = %s,
					`bewoelkung` = %s,
					`temperatur` = %s,
					`luftdruck` = %s,
					`bemerkungen` = %s,
					`wetter` = %s
				WHERE
					`datum` = %s
			',
				getDbValue($params['startrichtung'], T_STR),
				getDbValue($params['windrichtung'],  T_NUMERIC),
				getDbValue($params['windstaerke'],   T_NUMERIC),
				getDbValue($params['bewoelkung'],    T_STR),
				getDbValue($params['temperatur'],    T_NUMERIC),
				getDbValue($params['luftdruck'],     T_NUMERIC),
				getDbValue($params['bemerkungen'],   T_STR),
				getDbValue($params['wetter'],        T_STR),
				getDbValue($params['datum'],         T_DATE)
			);

			// zuvor definierte SQL-Anweisung ausführen
			mysql_query($sql);
		}
	}
	


	/**************************************************************************************************************************/
	/* ------------------------------------- BEGINN : LOGBUCH SPEICHERN NACH GET-BEFEHL ------------------------------------- */
	/**************************************************************************************************************************/
	
	// Array anlegen für die Feldinhalte
	$data = array();
	
	if (isset($_GET['action']) && $_GET['action'] == 'speichern') {
		// Bearbeiten eines bestehenden Datensatzes
		updateLogbuch($_POST, $_GET['datum_id']);

		// zurück zur normalen Startliste
		echo '<script language="javascript" type="text/javascript">';
		echo sprintf('window.location.href = "startliste.php?datum_id=%s"', $_GET['datum_id']);
		echo '</script>';

		// sicher stellen, dass der nachfolgende Code nicht
		// ausgefuehrt wird, wenn eine Umleitung stattfindet.
		exit();
	} else {
		if (isset($_GET['datum_id'])) {
			// prüfen ob ein Datum gesetzt wurde
			$datum_id = $_GET['datum_id'];
			
			// Kopfdaten ermitteln
			$data = getLogbuch($datum_id);
		} else {
			// ein leeres Datum
			$datum_id = '';
		}
	}

	/**************************************************************************************************************************/
	/* -------------------------------------- ENDE : LOGBUCH SPEICHERN NACH GET-BEFEHL -------------------------------------- */
	/**************************************************************************************************************************/

?>
<!-- ENDE: SKRIPT -->
<!-- BEGINN: AUSGABE -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

    <head>

        <title>Startliste &auml;ndern</title>

        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <meta http-equiv="Content-Script-Type" content="text/javascript" />
        <meta http-equiv="Content-Style-Type" content="text/css" />
        <meta http-equiv="content-language" content="de" />
        <meta name="author" content="Benjamin Stopfkuchen" />
		
		<script type="text/javascript" src="./js/jquery-1.10.2.js"></script>
		<script type="text/javascript" src="./js/jquery-1.10.2.min.js"></script>

        <link rel="Stylesheet" type="text/css" href="./css/stylish.css" />
		
		<script type="text/javascript" language="JavaScript">
		<!--
		
			$(document).ready(function() {
				$('.logbuch_anlegen_integer').keydown(function(e) {
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
				
				$('.logbuch_anlegen_temperatur').keydown(function(e) {
					return !(e.altKey || e.ctrlKey || e.shiftKey) && (
						e.keyCode >= 48 && e.keyCode <= 57 // 0 - 9
						|| e.keyCode >= 96 && e.keyCode <= 105 // 0 - 9 NumPad
						|| e.keyCode == 8 // <- Back
						|| e.keyCode == 9 // Tab
						|| e.keyCode == 16 // Shift
						|| e.keyCode == 37 // <- Left
						|| e.keyCode == 39 // -> Right
						|| e.keyCode == 46 // Delete
						|| e.keyCode == 173 // - Minus
						|| e.keyCode == 109 // - Minus Numpad
					);
				}).blur(function() {
					if ($(this).val() == '') {
						$(this).val('0');
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
				
					<h2>Startliste bearbeiten <small>vom <?php echo fromSqlDatum($data['datum']); ?></small></h2>
					
					<div class="helpline">
						 Hier hast du die M&ouml;glichkeit die Daten was Wetter, Wind, Bew&ouml;lkung und
						 die Startrichtung betrifft, entsprechend anzupassen.
					</div>
					
					<br />
					
					<form action="flt_aendern.php?datum_id=<?php echo $datum_id; ?>&action=speichern" method="POST">

						<fieldset style="width: 58%; background-color: #eeeeee;">
							<legend style="font-size: 11pt;"><img src="./img/weather_16.png" align="left" height="18" width="18" hspace="5" /> Angaben zu &quot;Wind und Wetter&quot;</legend>

							<table cellpadding="2" cellspacing="0" border="0" class="fluggeldkonten">
								<tr bgcolor="#dddddd">
									<th align="left" width="160" style="padding-left: 8px;"><label for="datum">Datum:</label></th>
									<td width="400">
										<input style="width: 100px;" type="text" value="<?php echo fromSqlDatum($data['datum']); ?>" maxlength="10" size="10" name="datum" id="datum" class="flugzeug_anlegen_gesperrt" onFocus="this.blur();" readonly="readonly" />
										<img style="position: relative; left 0px; top: 2px;" title="Prim&auml;rschl&uuml;ssel => Flugdatum" src="./img/1351092510_key.png" />
									</td>
								</tr>
								<tr bgcolor="#eeeeee">
									<th align="left" width="160" style="padding-left: 8px;"><label for="startrichtung">Startrichtung:</label></th>
									<td width="400">
										<select size="1" name="startrichtung" id="startrichtung" style="width: 100px;" class="flugzeug_anlegen" />
											<?php if ($data['startrichtung'] == '10') { ?>
												<option value="10" selected="selected">10</option>
												<option value="28">28</option>
											<?php } else { ?>
												<option value="10">10</option>
												<option value="28" selected="selected">28</option>
											<?php } ?>
										</select>
									</td>
								</tr>
								<tr bgcolor="#dddddd">
									<th align="left" width="160" style="padding-left: 8px;"><label for="windrichtung">Windrichtung:</label></th>
									<td width="400">
										<input style="width: 100px;" type="text" value="<?php echo $data['windrichtung']; ?>" maxlength="3" size="10" name="windrichtung" id="windrichtung" class="logbuch_anlegen_integer" />
										<strong style="margin-left: 5px;">Grad (&deg;)</strong>
									</td>
								</tr>
								<tr bgcolor="#eeeeee">
									<th align="left" width="160" style="padding-left: 8px;"><label for="windstaerke">Windst&auml;rke:</label></th>
									<td width="400">
										<input style="width: 100px;" type="text" value="<?php echo $data['windstaerke']; ?>" maxlength="3" size="3" name="windstaerke" id="windstaerke" class="logbuch_anlegen_integer" />
										<strong style="margin-left: 5px;">Knoten</strong>
									</td>
								</tr>
								<tr bgcolor="#dddddd">
									<th align="left" width="160" style="padding-left: 8px;"><label for="bewoelkung">Bew&ouml;lkung:</label></th>
									<td width="400">
										<input style="width: 100px;" type="text" value="<?php echo $data['bewoelkung']; ?>" maxlength="10" size="10" name="bewoelkung" id="bewoelkung" class="flugzeug_anlegen" />
									</td>
								</tr>
								<tr bgcolor="#eeeeee">
									<th align="left" width="160" style="padding-left: 8px;"><label for="temperatur">Temperatur:</label></th>
									<td width="400">
										<input style="width: 100px;" type="text" value="<?php echo $data['temperatur']; ?>" maxlength="3" size="10" name="temperatur" id="temperatur" class="logbuch_anlegen_temperatur" />
										<strong style="margin-left: 5px;">Grad (&deg;C)</strong>
									</td>
								</tr>
								<tr bgcolor="#dddddd">
									<th align="left" width="160" style="padding-left: 8px;"><label for="luftdruck">Luftdruck:</label></th>
									<td width="400">
										<input style="width: 100px;" type="text" value="<?php echo $data['luftdruck']; ?>" maxlength="4" size="10" name="luftdruck" id="luftdruck" class="logbuch_anlegen_integer" />
										<strong style="margin-left: 5px;">hPa</strong>
									</td>
								</tr>
								<tr bgcolor="#eeeeee">
									<th align="left" width="160" valign="top" style="padding-top: 10px; padding-left: 8px;"><label for="bemerkungen">Bemerkung(en):</label></th>
									<td width="400">
										<textarea name="bemerkungen" id="bemerkungen" maxlength="255" class="logbuch_anlegen" style="width: 400px; height: 100px; padding: 5px !important;"><?php if (isset($data['bemerkungen'])) { echo $data['bemerkungen']; } ?></textarea>
									</td>
								</tr>
								<tr bgcolor="#dddddd">
									<th align="left" width="160" valign="top" style="padding-top: 10px; padding-left: 8px;"><label for="wetter">Wetter:</label></th>
									<td width="400">
										<table>
											<tr>
												<td>
													<input type="radio" name="wetter" value="01" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '01') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/01.png" width="48" height="48" align="right" />
												</td>
												<td>
													<input type="radio" name="wetter" value="02" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '02') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/02.png" width="48" height="48" align="right" />
												</td>
												<td>
													<input type="radio" name="wetter" value="03" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '03') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/03.png" width="48" height="48" align="right" />
												</td>
												<td>
													<input type="radio" name="wetter" value="04" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '04') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/04.png" width="48" height="48" align="right" />
												</td>
												<td>
													<input type="radio" name="wetter" value="05" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '05') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/05.png" width="48" height="48" align="right" />
												</td>
											</tr>
											<tr>
												<td>
													<input type="radio" name="wetter" value="06" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '06') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/06.png" width="48" height="48" align="right" />
												</td>
												<td>
													<input type="radio" name="wetter" value="07" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '07') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/07.png" width="48" height="48" align="right" />
												</td>
												<td>
													<input type="radio" name="wetter" value="08" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '08') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/08.png" width="48" height="48" align="right" />
												</td>
												<td>
													<input type="radio" name="wetter" value="09" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '09') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/09.png" width="48" height="48" align="right" />
												</td>
												<td>
													<input type="radio" name="wetter" value="10" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '10') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/10.png" width="48" height="48" align="right" />
												</td>
											</tr>
											<tr>
												<td>
													<input type="radio" name="wetter" value="11" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '11') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/11.png" width="48" height="48" align="right" />
												</td>
												<td>
													<input type="radio" name="wetter" value="12" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '12') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/12.png" width="48" height="48" align="right" />
												</td>
												<td>
													<input type="radio" name="wetter" value="13" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '13') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/13.png" width="48" height="48" align="right" />
												</td>
												<td>
													<input type="radio" name="wetter" value="14" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '14') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/14.png" width="48" height="48" align="right" />
												</td>
												<td>
													<input type="radio" name="wetter" value="15" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '15') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/15.png" width="48" height="48" align="right" />
												</td>
											</tr>
											<tr>
												<td>
													<input type="radio" name="wetter" value="16" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '16') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/16.png" width="48" height="48" align="right" />
												</td>
												<td>
													<input type="radio" name="wetter" value="17" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '17') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/17.png" width="48" height="48" align="right" />
												</td>
												<td>
													<input type="radio" name="wetter" value="18" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '18') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/18.png" width="48" height="48" align="right" />
												</td>
												<td>
													<input type="radio" name="wetter" value="19" style="position: relative; top: 12px; left: 5px;" <?php echo ($data['wetter'] == '19') ? 'checked="checked" ' : ''; ?>/>
													<img src="./img/weather/19.png" width="48" height="48" align="right" />
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</fieldset>
						
						<div class="logbuch_speichern_buttons">
							<input type="submit" name="logbuch_speichern" id="logbuch_speichern" value="Daten speichern" style="width: 150px; margin-left: 10px;" />
							<input type="button" name="logbuch_cancel" id="logbuch_cancel" value="Abbrechen" style="width: 150px;" onClick="window.location.href='startliste.php?datum_id=<?php echo $datum_id; ?>';" />
						</div>
						
					</form>
					
				</td>
			</tr>
		</table>

	</body>

</html>
<!-- ENDE: AUSGABE -->