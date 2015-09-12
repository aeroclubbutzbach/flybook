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
	
	// die aktuellen Motorflugzeuge holen und in ein Array packen
	$flugzeuge = getFlugzeuge(FILTER_MOTORFLUG);

	// Größe festlegen
	$graph = new Graph(650, 400, 'auto');
	// Maßstäbe setzen
	$graph->SetScale('textlin');
	
	// Abstände festlegen
	$graph->img->SetMargin(50, 30, 20, 20);
	// sicher stellen das Anti-Aliasing deaktiviert ist
	// falls nicht, kann die Methode SetWeight() nicht verwendet werden
	$graph->img->SetAntiAliasing(false);
	
	// aktuelle Flugstatistik und die aus dem Vorjahr ermitteln
	$flugstatistik_neu = getFlugstatistik(date('Y'), FILTER_MOTORFLUG);
	$flugstatistik_alt = getFlugstatistik(date('Y') - 1, FILTER_MOTORFLUG);

	// Flugstunden aus dem aktuellen Jahr und dem Vorjahr ermitteln
	$dataY_Flugzeit_neu = getFlugstunden($flugstatistik_neu);
	$dataY_Flugzeit_alt = getFlugstunden($flugstatistik_alt);
	// Flugbewegungen aus dem aktuellen Jahr und dem Vorjahr ermitteln
	$dataY_Starts_neu = getFlugbewegungen($flugstatistik_neu);
	$dataY_Starts_alt = getFlugbewegungen($flugstatistik_alt);

	// neue Balkendiagramme mit den oben ermittelten Daten erstellen
	$bplot_neu = new BarPlot($dataY_Flugzeit_neu);
	$bplot_alt = new BarPlot($dataY_Flugzeit_alt);
	// neue Liniendiagramme mit den oben ermittelten Daten erstellen
	$lplot_neu = new LinePlot($dataY_Starts_neu);
	$lplot_alt = new LinePlot($dataY_Starts_alt);
	
	// Balkendiagramme gruppieren
	$gbplot = new GroupBarPlot(array($bplot_neu, $bplot_alt));

	// Balken- und Liniendiagramme hinzufügen
	$graph->Add($gbplot);
	$graph->Add($lplot_neu);
	$graph->Add($lplot_alt);
	
	// Überschrift und Achsenbeschriftung definieren
	$graph->title->Set('Flugstunden und -bewegung(en) im Motorflug');
	$graph->title->SetFont(FF_VERDANA, FS_BOLD, 11);
	$graph->xaxis->SetTickLabels($flugzeuge);
	$graph->xaxis->SetFont(FF_VERDANA, FS_NORMAL, 10);
	
	$bplot_neu->SetWeight(0);
	$bplot_neu->SetFillColor('#61a9f3');
	$bplot_neu->SetFillGradient('#61a9f3', '#c0c0ff', GRAD_HOR);
	$bplot_neu->SetLegend(sprintf('Flugstunden %d', date('Y')));
	
	$bplot_alt->SetWeight(0);
	$bplot_alt->SetFillColor('orange');
	$bplot_alt->SetFillGradient('orange', '#ffff00', GRAD_HOR);
	$bplot_alt->SetLegend(sprintf('Flugstunden %d', date('Y') - 1));
	
	// Liniendiagramm definieren für Anzahl der Landungen im aktuellen Jahr
	$lplot_neu->SetBarCenter();
	$lplot_neu->SetWeight(2);
	$lplot_neu->SetColor('#0000ff');
	$lplot_neu->SetLegend(sprintf('Flugbewegung(en) %d', date('Y')));
	$lplot_neu->mark->SetType(MARK_UTRIANGLE, '', 1.0);
	$lplot_neu->mark->SetWeight(2);
	$lplot_neu->mark->SetWidth(8);
	$lplot_neu->mark->setColor('#0000ff');
	$lplot_neu->mark->setFillColor('#0000ff');

	// Liniendiagramm definieren für Anzahl der Landungen im Vorjahr
	$lplot_alt->SetBarCenter();
	$lplot_alt->SetWeight(2);
	$lplot_alt->SetColor('#ff0000');
	$lplot_alt->SetLegend(sprintf('Flugbewegung(en) %d', date('Y') - 1));
	$lplot_alt->mark->SetType(MARK_UTRIANGLE, '', 1.0);
	$lplot_alt->mark->SetWeight(2);
	$lplot_alt->mark->SetWidth(8);
	$lplot_alt->mark->setColor('#ff0000');
	$lplot_alt->mark->setFillColor('#ff0000');
	
	// Legende generieren
	$graph->legend->Pos(0.5, 0.95, 'center', 'bottom');
	$graph->legend->SetLayout(LEGEND_HOR);
	$graph->legend->SetFont(FF_VERDANA ,FS_NORMAL, 9);
	$graph->legend->SetFillColor('#ebebeb'); 
	$graph->legend->SetFrameWeight(1);
	$graph->legend->SetColumns(2);

	// Diagramm erzeugen
	$graph->Stroke();
	
	/**************************************************************************************************************************/
	/* ------------------------------------------- ENDE : ZEICHNEN DES DIAGRAMMES ------------------------------------------- */
	/**************************************************************************************************************************/
	
?>