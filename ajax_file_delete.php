<?php

	// es wird zunächst noch einmal überprüft, ob die Datei exisitiert
	// und falls dies der Fall ist wird diese im nächsten Schritt gelöscht
	if (file_exists($_GET['img'])) {
		// Datei löschen und das war's auch schon
		unlink($_GET['img']);
	}

	// Skript beenden
	die();

?>