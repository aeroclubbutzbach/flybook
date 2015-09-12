<!-- BEGINN: SKRIPT -->
<?php

	/*
	 * getIpAdresse()
	 *
	 * die aktuelle IP-Adresse des angemeldeten Benutzer wird ermittelt
	 *
	 * @return string $ip
	 */
	if (!function_exists('getIpAdresse')) {
		function getIpAdresse()
		{
			// Rückgabe-Variable definieren
			$ip = '0.0.0.0';
		
			// prüfen, welche Art von IP-Adresse gesetzt ist
			if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				// Umgebungsvariable der Server-IP
				$ip = $_SERVER['REMOTE_ADDR'];
			} else {
				// falls ein Proxy-Server benutzt wird
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			
			// IP-Adresse zurückgeben
			return $ip;
		}
	}
	
	/*
	 * getBetriebssystem()
	 *
	 * das aktuelle Betriebssystem des angemeldeten Benutzer wird ermittelt
	 *
	 * @return string $os
	 */
	if (!function_exists('getBetriebssystem')) {
		function getBetriebssystem()
		{
			// Betriebssystem aus der Umgebungsvariablen ermitteln
			$agent = $_SERVER['HTTP_USER_AGENT'];
			// Rückgabe-Variable definieren
			$os    = '<img src="./img/os/os-icon.png" align="left" height="14" />unbekannt';

			// Betriebssystem ermitteln
			if (stripos($agent, 'win') !== false) {
				// Microsoft Windows
				if (strstr($agent, 'Windows 95')) { $os = 'Windows 95'; }
				else if (strstr($agent, 'Windows 98')) { $os = 'Windows 98'; }
				else if (strstr($agent, 'Windows Me') || strstr($agent, '9x 4.90')) { $os = 'Windows Me'; }
				else if (strstr($agent, 'Windows 2000') || strstr($agent, 'NT 5.0')) { $os = 'Windows 2000'; }
				else if (strstr($agent, 'NT 4.0')) { $os = 'Windows NT'; }
				else if (strstr($agent, 'NT 5.1') || strstr($agent, 'XP')) { $os = 'Windows XP'; }
				else if (strstr($agent, 'NT 5.2')) { $os = 'Windows Server 2003'; }
				else if (strstr($agent, 'NT 6.0')) { $os = 'Windows Vista'; }
				else if (strstr($agent, 'NT 6.1')) { $os = 'Windows 7'; }
				else { $os = 'Windows'; }
				
				$os = sprintf('<img src="./img/os/windows-icon.png" align="left" height="14" />%s', $os);
			} else if (stripos($agent,'linux') !== false) {
				// Linux
				$os = '<img src="./img/os/linux-icon.png" align="left" height="14" />Linux';
			} else if (stripos($agent,'mac') !== false) {
				// Macintosh
				$os = '<img src="./img/os/mac-icon.png" align="left" height="14" />Macintosh';
			}
			
			// Betriebssystem zurückgeben
			return $os;
		}
	}
	
	/*
	 * getWebBrowser()
	 *
	 * den aktuell verwendeten Browser des angemeldeten Benutzer ermitteln
	 *
	 * @return string $browser
	 */
	if (!function_exists('getWebBrowser')) {
		function getWebBrowser()
		{
			// Webbrowser aus der Umgebungsvariablen ermitteln
			$agent = $_SERVER['HTTP_USER_AGENT'];

			// Rückgabe-Variablen definieren
			$browser = '<img src="./img/browser/browser-icon.png" align="left" height="14" />unbekannt';
			$version = '';

			// es wird geprüft um welchen Browser es sich handelt
			if (preg_match('/MSIE/i',$agent) && !preg_match('/Opera/i', $agent)) {
				// Internet Explorer
				$browser = '<img src="./img/browser/ie-icon.png" align="left" height="14" />Internet Explorer';
				$ub = 'MSIE';
			} else if (preg_match('/Firefox/i', $agent)) {
				// Mozilla Firefox
				$browser = '<img src="./img/browser/firefox-icon.png" align="left" height="14" />Mozilla Firefox';
				$ub = 'Firefox';
			} else if (preg_match('/Chrome/i', $agent)) {
				// Google Chrome
				$browser = '<img src="./img/browser/chrome-icon.png" align="left" height="14" />Google Chrome';
				$ub = 'Chrome';
			} else if (preg_match('/Safari/i', $agent)) {
				// Safari
				$browser = '<img src="./img/browser/safari-icon.png" align="left" height="14" />Apple Safari';
				$ub = 'Safari';
			} else if (preg_match('/Opera/i', $agent)) {
				// Opera
				$browser = '<img src="./img/browser/opera-icon.png" align="left" height="14" />Opera';
				$ub = 'Opera';
			} else if (preg_match('/Netscape/i', $agent)) {
				// Netscape
				$browser = '<img src="./img/browser/netscape-icon.png" align="left" height="14" />Netscape';
				$ub = 'Netscape';
			} else if (preg_match('/Konqueror/i', $agent)) {
				// Konqueror
				$browser = '<img src="./img/browser/konqueror-icon.png" align="left" height="14" />Konqueror';
				$ub = 'Konqueror';
			}
		   
			// die richtige Versionsnummer ermitteln
			$known = array('Version', $ub, 'other');
			$pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
			preg_match_all($pattern, $agent, $matches);
		   
			if (count($matches['browser']) != 1) {
				if (strripos($agent, 'Version') < strripos($agent, $ub)){
					$version = $matches['version'][0];
				} else {
					$version = $matches['version'][1];
				}
			} else {
				$version = $matches['version'][0];
			}
		   
			// Versionsnummer an den Browser anhängen
			$browser = sprintf('%s %s', $browser, $version);

			// Webbrowser zurückgeben
			return $browser;
		}
	}
	
	/*
	 * getHerkunftsland()
	 *
	 * das aktuelle Herkunftsland des angemeldeten Benutzer ermitteln
	 *
	 * @return string $browser
	 */
	if (!function_exists('getHerkunftsland')) {
		function getHerkunftsland()
		{
			// als erstens den Host der IP-Adresse der Besuchers ermitteln
			$ip = $_SERVER['REMOTE_ADDR'];
			$host = gethostbyaddr($ip);
			
			// Host-Korrekturen
			$host = strtolower($host);
			
			$hostTrans = array(
				'.arcor-ip.net'  => '.de', '.t-dialin.net' => '.de',
				'.sui-inter.net' => '.ch', '.drei.com'     => '.at',
				'.proxad.net'    => '.fr', '.gaoland.net'  => '.fr',
				'.mchsi.com'     => '.us', '.comcast.net'  => '.us'
			);
			$host = strtr($host, $hostTrans);
			
			// der Host, welchen wir bekommen haben, enthält eine Top-Level-Domain
			// (z.B. de oder ch). Diese extrahieren wir nun ...
			$land = (strpos($host, '.') === false) ? $host : substr(strrchr($host, '.'), 1);
			
			// Fehler bei gethostbyaddr()
			if ($ip === $host) { $land = '?'; }
			if ($land == 'local') { $land = 'de'; }
			
			// Array mit Ländercodes einbinden
			require_once('laendercodes.php');

			if (isset($laendercodes[strtoupper($land)])) {
				$return = sprintf('
					<img src="./img/flags/%s.png" align="left" width="16" vspace="2" />%s',
					$land, $laendercodes[strtoupper($land)]
				);
			} else {
				$return = '<img src="./img/flags/europeanunion.png" align="left" width="16" vspace="2" /> Europa';
			}
			
			// Herkunftsland zurückgeben
			return $return;
		}
	}

?>
<!-- ENDE: SKRIPT -->
<!-- BEGINN: AUSGABE -->
	<table cellpadding="0" cellspacing="0" width="100%">
		<tr height="28">
			<td width="45" align="center" class="runde_ecken_links">
				<img src="./img/home_24.png" width="22" height="22" />
			</td>
			<td style="color: #ffffff;" class="runde_ecken_rechts">
				<a href="startseite.php" class="nav" target="Hauptfenster">Startseite</a>
			</td>
		</tr>
		<tr height="28">
			<td width="45" align="center" class="runde_ecken_links">
				<img src="./img/Users_group_people_friends.png" width="22" height="22" />
			</td>
			<td style="color: #ffffff;" class="runde_ecken_rechts">
				<a href="mitglieder.php" class="nav" target="Hauptfenster">Mitgliederliste</a>
			</td>
		</tr>
		<tr height="28">
			<td width="45" align="center" class="runde_ecken_links">
				<img src="./img/mini_plane.png" width="22" height="24" />
			</td>
			<td style="color: #ffffff;" class="runde_ecken_rechts">
				<a href="flugzeuge.php" class="nav" target="Hauptfenster">Flugzeugliste</a>
			</td>
		</tr>
		<tr height="28">
			<td width="45" align="center" class="runde_ecken_links">
				<img src="./img/x-office-address-book.png" width="22" height="24" />
			</td>
			<td style="color: #ffffff;" class="runde_ecken_rechts">
				<a href="hauptflugbuch.php" class="nav" target="Hauptfenster">Hauptflugbuch</a>
			</td>
		</tr>
		<tr height="28">
			<td width="45" align="center" class="runde_ecken_links">
				<img src="./img/000550-folder-document-import.png" width="22" height="22" />
			</td>
			<td style="color: #ffffff;" class="runde_ecken_rechts">
				<a href="csv_import.php" class="nav" target="Hauptfenster">Fl&uuml;ge importieren</a>
			</td>
		</tr>
		<tr height="28">
			<td width="45" align="center" class="runde_ecken_links">
				<img src="./img/star-24.png" width="22" height="22" />
			</td>
			<td style="color: #ffffff;" class="runde_ecken_rechts">
				<a href="abfrage_fluege_zeitfenster.php" class="nav" target="Hauptfenster">Fl&uuml;ge Zeitfenster</a>
			</td>
		</tr>
		<tr height="28">
			<td width="45" align="center" class="runde_ecken_links">
				<img src="./img/icon-money-24x24.png" width="22" height="22" />
			</td>
			<td style="color: #ffffff;" class="runde_ecken_rechts">
				<a href="fluggeldkonten.php" class="nav" target="Hauptfenster">Fluggeldkonten</a>
			</td>
		</tr>
		<tr height="28">
			<td width="45" align="center" class="runde_ecken_links">
				<img src="./img/football_soccer_sport_ball-24.png" width="20" height="20" />
			</td>
			<td style="color: #ffffff;" class="runde_ecken_rechts">
				<a href="trainingsstaende.php" class="nav" target="Hauptfenster">Trainingsst&auml;nde</a>
			</td>
		</tr>
		<tr height="28">
			<td width="45" align="center" class="runde_ecken_links">
				<img src="./img/printer_24.png" width="22" height="22" />
			</td>
			<td style="color: #ffffff;" class="runde_ecken_rechts">
				<a href="monatsrechnung.php" class="nav" target="Hauptfenster">Rechnung / Monat</a>
			</td>
		</tr>
		<tr height="28">
			<td width="45" align="center" class="runde_ecken_links">
				<img src="./img/money-turnover.png" width="21" height="21" />
			</td>
			<td style="color: #ffffff;" class="runde_ecken_rechts">
				<a href="umsatzstatistik.php" class="nav" target="Hauptfenster">Ums&auml;tze / Statistik</a>
			</td>
		</tr>
		<tr height="28">
			<td width="45" align="center" class="runde_ecken_links">
				<img src="./img/money.png" width="22" height="22" />
			</td>
			<td style="color: #ffffff;" class="runde_ecken_rechts">
				<a href="jahresumsatz.php" class="nav" target="Hauptfenster">Jahresums&auml;tze</a>
			</td>
		</tr>
		<tr height="28">
			<td width="45" align="center" class="runde_ecken_links">
				<img src="./img/E-mail-icon.png" width="22" height="22" />
			</td>
			<td style="color: #ffffff;" class="runde_ecken_rechts">
				<a href="mailversand.php" class="nav" target="Hauptfenster">eMail Versand</a>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<img src="./img/acb_logo.gif" border="0" vspace="25" />
			</td>
		</tr>
		<tr>
			<td colspan="2" align="left" class="login_daten" style="padding-left: 5px;">
				<strong>Deine IP-Adresse:</strong><br />
				<img src="./img/icon-ip.png" align="left" height="14" /><?php echo getIpAdresse(); ?><br />
				<strong>Betriebssystem:</strong><br /><?php echo getBetriebssystem(); ?><br />
				<strong>Herkunft:</strong><br /><?php echo getHerkunftsland(); ?><br />
				<strong>Browser:</strong><br /><?php echo getWebBrowser(); ?><br />
			</td>
		</tr>
	</table>
<!-- ENDE: AUSGABE -->