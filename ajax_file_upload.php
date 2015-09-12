<?php

	// Array für die Rückgabe erzeugen
	$return = array();
	
	// Dateiendung ermitteln
	$path_parts = pathinfo($_FILES['upload']['name']);
	$extension  = $path_parts['extension'];

	// der Pfad in welche die Uploads eingelegt werden sollen
	$target_path = sprintf(
		'%s/%s/%s.%s', dirname(__FILE__), $_POST['dir'], md5(basename($_POST['id'])), $extension
	);
	
	// die hochzuladende Datei wird in den per Parameter angegebenen Verzeichnispfad
	// geschoben und gibt bei Erfolg dem Anwender eine entsprechenden Meldung aus
	if (move_uploaded_file($_FILES['upload']['tmp_name'], $target_path)) {
		// Datei-Upload war erfolgreich
		// Meldung wird an den Anwender ausgegeben
		$return['result']  = true;
		$return['image']   = sprintf('./%s/%s.%s', $_POST['dir'], md5(basename($_POST['id'])), $extension);
	} else {
		// ein Fehler ist beim Datei-Upload passiert
		// Meldung wird an den Anwender ausgegeben
		$return['result']  = false;
	}

	// die Daten werden im JSON-Format zurückgegeben
	echo json_encode($return);
	// Skript beenden
	die();

?>