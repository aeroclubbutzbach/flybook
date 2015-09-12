<?php
/**
 * Prozedurale Steuerdatei zur Ausgabe des HTML Streams in einer PDF Datei
 * @author S.Düster
 * @version beta 0.1.0 am 22.07.2011
 * 
 * 
 */

// setzen der Bedingungen
	define('FPDF_FONTPATH', 'font/');
	require(dirname(__FILE__)."/html_table_parser.php");

// Vorbelegung der Ausgabe Variable
	$strOutput = array();

// setzen ser Variable, wenn Ausgabe über SESSION kommt
	if ( isset($_SESSION['strOutput']) ) $strOutput = $_SESSION['strOutput'];

// Reset wenn Parameter übergeben wird
	if ( isset($_REQUEST['reset']) ) session_destroy();

// Ausgabeweiche try/catch
	if ( count($strOutput) >= 1 )			// Ausgabe vorhanden
	{
		$pdf = new PDF();
		#$pdf->PDF( "L", "mm", "A4" );
		$pdf->PDF( "P", "mm", "A4" );
		$pdf->AddPage();
		$pdf->SetFont('Arial', '', 7);
		#$pdf->iDebug = true;
		
		foreach ( $strOutput as $sI=>$sLine )
		{
			$html = $sLine;
			
			switch ( $sI )
			{
				case 'vs0_ausgabe0':	// Header und Logo
					$pdf->WriteHTMLHeader($html);
					$pdf->SetY(70);
					break;
					
				case 'vs0_ausgabe1':	// Allgemeiner Text
					$pdf->WriteHTMLTable($html);
					break;
					
				case 'vs0_ausgabe2':	// Hard- / Softfacts
					$pdf->firstPic = 1;
					$pdf->aTD = array( 'WIDTH'=>array( 1=>90, 2=>9, 3=>82 ) );
					$pdf->WriteHTMLTable($html);
					break;
					
				case 'vs0_ausgabe3':	// Automatischer Fließtext...
				// Daten parsen
					$pdf->parseTableFirst($html);
					$aTAB = $pdf->aTabflow;
                    
                    
				// Daten Erfassen
					$sHeadLine = $aTAB[1][2][1];
					$sBody     = $aTAB[1][4][1];
				// PDF Ausgabe generieren
					$pdf->SetFont( 'Arial', 'B', 10);					
					$pdf->Cell(190, 6, $sHeadLine, 'B');
					$pdf->SetFont('Arial', '', 7);
					$pdf->Ln(8);
					$pdf->MultiCell(190, 3, $sBody );
					$pdf->Ln(10);
					break;
					
				case 'vs0_ausgabe4':	// Branchenvergleich
					$pdf->AddPage( 'L', 'A4' );
					$pdf->aTD = array( 'WIDTH'=>array( 1=>50, 2=>24, 3=>24, 4=>24, 5=>24, 6=>24, 7=>24, 8=>24, 9=>24 ) );
					$pdf->WriteHTMLTable($html);
					break;
					
				case 'vs0_ausgabe5':	// Vorjahresvergleich
					#if ( $pdf->GetY() >= 100) $pdf->AddPage( 'P', 'A4' );
					$pdf->AddPage( 'L', 'A4' );
					#$pdf->firstPic = 1;
					$pdf->aTD = array( 'WIDTH'=>array( 1=>48, 2=>1, 3=>11, 4=>11, 5=>1, 6=>11, 7=>6, 8=>11, 9=>11, 10=>1, 11=>11, 12=>6, 13=>11, 14=>11, 15=>1, 16=>11, 17=>6, 18=>11, 19=>11, 20=>1, 21=>11, 22=>6, 23=>11, 24=>11, 25=>1, 26=>11 ) );
					$pdf->WriteHTMLTable($html);
					break;
					
				case 'vs0_ausgabe6':	// Entwicklung der Kennzahlen
					$pdf->firstPic = 1;
					$pdf->aTD = array( 'WIDTH'=>array( 1=>70, 2=>10, 3=>35, 4=>35, 5=>35, 6=>1, 7=>1 ) );
					$pdf->WriteHTMLTable($html);
					break;
					
				case 'vs0_ausgabe8':	// Formeln zur Berechnung der Kennzahlen
					$pdf->WriteKennzahlen();
					break;
					
				case 'vs0_ausgabe9':	// Notenerklärung und Gewichtung
					$pdf->Ln(8);
					$pdf->aTD = array( 'WIDTH'=>array( 1=>35, 2=>15, 3=>15, 4=>15, 5=>15, 6=>15, 7=>15, 8=>15, 9=>15 ) );
					$pdf->WriteHTMLTable($html);
					break;
					
				default:
					if ( !strstr($sI, "T") )
					{
						$pdf->SetFont('Verdana', 'B', 5);
						$pdf->WriteHTMLTable($html);
						$pdf->SetFont('Arial', '', 7);
					}
			} // eOf: switch()
			
		} // eOf: foreach()
		
		$pdf->Output();
	}
	else									// Default Ausgabe
	{
		$sMsg = "Kein Ergebnis zu ermitteln!";
		printf ( "<b>%s</b><br/>\n", $sMsg);
	}
	


?>