<?php

	// PDF-Bibliothek einbinden
	require_once('./pdf/fpdf.php');

	// neues PDF-Dokument erzeugen
	$pdf = new FPDF('P', 'mm', 'A4');
	
	// ... entspricht dem Aufruf von
	$pdf->AliasNbPages('{nb}');
	
	// Automatischen Seitenumbruch deaktivieren
	$pdf->SetAutoPageBreak(false);
	
	// Seitenabstand definieren
	$pdf->SetMargins(25, 15, 15);

	// ******************************************************** SEITE ******************************************************** //
	// Seite hinzufügen
	$pdf->AddPage();

	// Logo auf erster Seite einfügen
	$pdf->Image('./img/acb_logo_gross.jpg', 95, 10, 25);

	// Position auf der X- und Y-Achse
	$pdf->SetY(40);
	// Schriftart festlegen
	$pdf->SetFont('Times', 'BU' , 14);
	$pdf->Cell(160, 7, utf8_decode('Umsatzstatistiken, Aero-Club Butzbach e.V.'), 0, 1, 'C');   
	
	// Position auf der X- und Y-Achse
	$pdf->SetXY(20, 280);
	// Schriftart und -farbe ändern
	$pdf->SetTextColor(128, 128, 128);
	$pdf->SetFont('Times', '' , 8);
	// Ausgabe des Fusszeilentext
	$pdf->Cell(0, 10, 'Seite ' . $pdf->PageNo() . ' von {nb}', 0, 0, 'C');
	

	
	// ******************************************************** SEITE ******************************************************** //

	// PDF-Dokument ausgeben
	$pdf->Output(sprintf(
		'abrech/%s/%s/Abrechn_%s%s_Rev_02.pdf',
		$_POST['zeitraum_jahr'], $_POST['zeitraum_monat'],
		$_POST['zeitraum_jahr'], $_POST['zeitraum_monat']
	), 'F');

?>