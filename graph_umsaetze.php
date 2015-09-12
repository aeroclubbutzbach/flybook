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
	$segelflug   = array();
	$motorsegler = array();
	$ul          = array();
	
	foreach ($flugstunden as $item) {
		// die Flugstunden ermitteln und in das Array schreiben
		$segelflug[]   = $item['segelflug'];
		$motorsegler[] = $item['falke'] + $item['dimona'];
		$ul[]          = $item['ul'];		
	}

	// neue Liniendiagramme mit den oben ermittelten Daten erstellen
	$lplot_segelflug   = new LinePlot($segelflug);
	$lplot_motorsegler = new LinePlot($motorsegler);
	$lplot_ul          = new LinePlot($ul);

	// Liniendiagramme hinzufügen
	$graph->Add($lplot_segelflug);
	$graph->Add($lplot_motorsegler);
	$graph->Add($lplot_ul);
	
	// Überschrift und Achsenbeschriftung definieren
	$graph->title->Set('Flugzeugnutzung / -auslastung der vergangenen Jahre');
	$graph->title->SetFont(FF_VERDANA, FS_BOLD, 11);
	$graph->xaxis->SetTickLabels(array_keys($flugstunden));
	$graph->xaxis->SetFont(FF_VERDANA, FS_NORMAL, 8);
	$graph->xaxis->SetLabelAngle(60);
	$graph->yaxis->title->SetFont(FF_VERDANA, FS_BOLD, 10);
	$graph->yaxis->title->SetMargin(14);
	$graph->yaxis->SetTitleSide(SIDE_LEFT); 
	$graph->yaxis->title->Set('Flugstunde(n)');
	
	// Liniendiagramm definieren für Anzahl der Flugstunden im Segelflug
	$lplot_segelflug->SetWeight(3);
	$lplot_segelflug->SetColor('#ff0000');
	$lplot_segelflug->SetLegend('Segelflug');

	// Liniendiagramm definieren für Anzahl der Flugstunden auf Motorsegler
	$lplot_motorsegler->SetWeight(3);
	$lplot_motorsegler->SetColor('#00c000');
	$lplot_motorsegler->SetLegend('Motorsegler');
		
	// Liniendiagramm definieren für Anzahl der Flugstunden auf UL
	$lplot_ul->SetWeight(3);
	$lplot_ul->SetColor('#0000ff');
	$lplot_ul->SetLegend('Ultraleicht');
	
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