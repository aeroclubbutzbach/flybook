<?php

	// Fehlerberichterstattung ein
	error_reporting(E_ERROR);

	// Parameter zum Aufbau der Verbindung zur Datenbank
	define('MYSQL_HOST',      'localhost');
	define('MYSQL_BENUTZER',  'web97');
	define('MYSQL_KENNWORT',  'ACE0fM0v');
	define('MYSQL_DATENBANK', 'usr_web97_4');

	// connect to db
	mysql_connect(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT);
	mysql_select_db(MYSQL_DATENBANK) or die(mysql_error());
	mysql_query("SET NAMES 'utf8'");
	
	// call the passed in function
	if (isset($_GET['method']) && !empty($_GET['method'])) {
		if (function_exists($_GET['method'])) {
			$_GET['method']();
		}
	}

	// methods
	function getMitglieder()
	{
		$sql = sprintf('
			SELECT
				*
			FROM
				`mitglieder`
		');
	
		$data = mysql_query($sql);
		$members = array();
		
		while ($member = mysql_fetch_array($data)) {
			$members[] = $member;
		}

		$members = json_encode($members);
		
		echo $_GET['jsoncallback'] . '(' . $members . ')';
	}

?>