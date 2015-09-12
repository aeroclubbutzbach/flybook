<?php

	header('Content-type: text/html; charset=utf-8');

	// PDF-Bibliothek einbinden
	require_once('./pdf/fpdf.php');
	
	// neues PDF-Dokument erzeugen
	$pdf = new FPDF('L', 'mm', 'A4');
	
	// ... entspricht dem Aufruf von
	$pdf->AliasNbPages('{nb}');
	
	// Automatischen Seitenumbruch deaktivieren
	$pdf->SetAutoPageBreak(false);
	
	// Seitenabstand definieren
	$pdf->SetMargins(25, 15, 15);
	
	// das aktuelle Bordbuch aus dem Hauptflugbuch ermitteln
//	$data = getBordbuchPdf($_GET['datum_id']);

	// ******************************************************** SEITE ******************************************************** //
	// Seite hinzufügen
	$pdf->AddPage();

	// ******************************************************** SEITE ******************************************************** //

	// PDF-Dokument ausgeben
	$pdf->Output(sprintf('bordbuch-vom-%s.pdf', $_GET['datum_id']), 'I');

?>