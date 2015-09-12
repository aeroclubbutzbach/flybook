<?php
	
	/**************************************************************************************************************************/
	/* ------------------------------------------ BEGINN : ZEICHNEN DES DIAGRAMMES ------------------------------------------ */
	/**************************************************************************************************************************/

	// content="text/plain; charset=utf-8"
	include_once('./jpgraph/jpgraph.php');
	include_once('./jpgraph/jpgraph_bar.php');
	include_once('./jpgraph/jpgraph_line.php');
	
	// allgemeine Funktionen einbinden
	include_once('./functions.php');
	
	// aktuelle Flugstundenzahlen über die letzten Jahre
	// holen und in ein schönes Array reinpacken
	$flugstunden = getFlugstundenUmsatz();

	// Größe festlegen
	$graph = new Graph(650, 400, 'auto');
	// Maßstäbe setzen
	$graph->SetScale('textlin');
	
	// Abstände festlegen
	$graph->img->SetMargin(60, 30, 20, 20);
	// sicher stellen das Anti-Aliasing deaktiviert ist
	// falls nicht, kann die Methode SetWeight() nicht verwendet werden
	$graph->img->SetAntiAliasing(false);
	
	// Array anlegen, welches später die Stunden der
	// einzelnen Flugzeugklassifizierungen enthalten soll
	$falke     = array();
	$dimona    = array();
	
	foreach ($flugstunden as $item) {
		// die Flugstunden ermitteln und in das Array schreiben
		$falke[]  = $item['falke'];
		$dimona[] = $item['dimona'];
	}

	// neue Liniendiagramme mit den oben ermittelten Daten erstellen
	$lplot_falke  = new LinePlot($falke);
	$lplot_dimona = new LinePlot($dimona);

	// Liniendiagramme hinzufügen
	$graph->Add($lplot_falke);
	$graph->Add($lplot_dimona);
	
	// Überschrift und Achsenbeschriftung definieren
	$graph->title->Set('Flugzeugnutzung - Gegenüberstellung innerhalb der Sparte Motorsegler');
	$graph->title->SetFont(FF_VERDANA, FS_BOLD, 11);
	$graph->subtitle->Set('(Falke vs. Dimona)');
	$graph->subtitle->SetFont(FF_VERDANA, FS_BOLD, 9);
	$graph->xaxis->SetTickLabels(array_keys($flugstunden));
	$graph->xaxis->SetFont(FF_VERDANA, FS_NORMAL, 8);
	$graph->xaxis->SetLabelAngle(60);
	$graph->yaxis->title->SetFont(FF_VERDANA, FS_BOLD, 10);
	$graph->yaxis->title->SetMargin(14);
	$graph->yaxis->SetTitleSide(SIDE_LEFT); 
	$graph->yaxis->title->Set('Flugstunde(n)');

	// Liniendiagramm definieren für Anzahl der Flugstunden auf Falke
	$lplot_falke->SetWeight(3);
	$lplot_falke->SetColor('#00c000');
	$lplot_falke->SetLegend('Falke');
	$lplot_falke->SetFillColor("#00c000@0.8");
		
	// Liniendiagramm definieren für Anzahl der Flugstunden auf Dimona
	$lplot_dimona->SetWeight(3);
	$lplot_dimona->SetColor('#0000ff');
	$lplot_dimona->SetLegend('Dimona');
	$lplot_dimona->SetFillColor("#0000ff@0.8");
	
	// Legende generieren
	$graph->legend->Pos(0.5, 0.95, 'center', 'bottom');
	$graph->legend->SetLayout(LEGEND_HOR);
	$graph->legend->SetFont(FF_VERDANA ,FS_NORMAL, 9);
	$graph->legend->SetFillColor('#ebebeb'); 
	$graph->legend->SetFrameWeight(1);
	$graph->legend->SetColumns(4);

	// Diagramm erzeugen
	$graph->Stroke();
	
	/**************************************************************************************************************************/
	/* ------------------------------------------- ENDE : ZEICHNEN DES DIAGRAMMES ------------------------------------------- */
	/**************************************************************************************************************************/
	
?>