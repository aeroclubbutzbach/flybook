<?php

require(dirname(__FILE__).'/fpdf.php');

/**
 * HILFSFUNKTIONEN
 * zum Aufbau der HTML-Table -> PDF Klasse
 * Abgeleitet aus dem Script HTML2PDF von Clement Lavoillotte
 * Anpassung Steffen Düster
 *
 * @version 1.01 vom 22.07.2011
 */

/**
 * Hex-Farbwert in RGB umrechnen
 * @param string $couleur
 */
function hex2dec( $color = "#000000" )
{
	$R = substr($color, 1, 2);
	$rouge = hexdec($R);
	$V = substr($color, 3, 2);
	$vert = hexdec($V);
	$B = substr($color, 5, 2);
	$bleu = hexdec($B);
	$tbl_color = array();
	$tbl_color['R']=$rouge;
	$tbl_color['G']=$vert;
	$tbl_color['B']=$bleu;
	return $tbl_color;
}

/**
 * Konvertierung Pixel in mm bei 72 DPI
 * @param int $px
 */
function px2mm( $px = 1 )
{
	return $px*25.4/72;
}

/**
 * HTML Entities konvertieren
 * @param string $html
 */
function txtentities( $html ){
	$trans = get_html_translation_table(HTML_ENTITIES);
	$trans = array_flip($trans);
	return strtr($html, $trans);
}


/**
 * Klassenerweiterung
 * basiert auf FPDF
 * @author Klar IT webconsulting
 */
class PDF extends FPDF
{
	//variables of html parser
	var $B;
	var $I;
	var $U;
	var $HREF;
	var $fontList;
	var $issetfont;
	var $issetcolor;
	
	// Modifikation PDF Parser
	var $iTAB;			// interner Zähler, Anzahl Tabellen
	var $iTR;			// interner Zähler, Anzahl Zeilen
	var $iTD;			// interner Zähler, Anzahl Zellen in einer Zeile
	var $aTD;			// Wert kann von außen übergeben werden, width der Zellen
	var $firstPic;		// Wert kann von außen übergeben werden, 1. Pic width
	var $aTabflow;		// internes Array zur Tabellengestalt
	
	var $iDebug;		// Schalter für Debug Ausgaben
	
	function PDF( $orientation='P', $unit='mm', $format='A4' )
	{
		//Call parent constructor
		$this->FPDF($orientation,$unit,$format);
		//Initialization
		$this->B=0;
		$this->I=0;
		$this->U=0;
		$this->HREF='';
	
		$this->tableborder=0;
		$this->tdbegin=false;
		$this->tdwidth=0;
		$this->tdheight=0;
		$this->tdalign="L";
		$this->tdbgcolor=false;
	
		$this->oldx=0;
		$this->oldy=0;
	
		$this->fontlist=array("arial","times","courier","helvetica","symbol");
		$this->issetfont=false;
		$this->issetcolor=false;
		
		$this->iTAB = 0;
		$this->iTR  = 0;
		$this->iTD  = 0;
		
		$this->aTD      = array();
		$this->aTabflow = array();
		
		$this->firstPic = 0;
		
		$this->iDebug = false;
	}
	
	/**
	 * Html Parser
	 * modifiziert v Steffen Düster
	 */
	
	/**
	 * splitTag()
	 * Parser uns Splitter für die Tags - übergibt ein Array
	 * @param string $html
	 * @return array $a
	 */
	function splitTag( $html )
	{
		$html = strip_tags( html_entity_decode($html),"<img><br><tr><td><th><tr><table>" ); //remove all unsupported tags
		$html = str_replace( "\n",'',$html ); //Zeilenumbruch mit Leerzeichen ersetzen
		$html = str_replace( "\t",'',$html ); //Zeilenumbruch mit Leerzeichen ersetzen
		$html = str_replace( "&euro;", 'Euro', $html );
		
		$a = preg_split( '/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE ); //String explodieren lassen ;-)
	    //Es werden auch die eingeklammerten Ausdrücke des Trennsymbol-Suchmusters erfasst und zurückgegeben. 
		return $a;
	}
	
	/**
	 * extractTag()
	 * Extrahiert Main Tag und dessen Attribute
	 * @param string $e
	 * @return array
	 */
	function extractTag( $e )
	{
		//Attribute extrahieren
		$a2=explode( ' ',$e );
		$tag=strtoupper( array_shift($a2) );
		$attr=array();
		foreach($a2 as $v)
		{
			if(preg_match( '/([^=]*)=["\']?([^"\']*)/',$v,$a3) ) //RegExe um nur den Text und die Vorzeichen zu bekommen
			{
				//Anpassung an Ausgabe String
				if ( $a3[1] == "width" AND $a3[2] == "920" )
				{
					$a3[2] = "540";
				}
				$attr[strtoupper($a3[1])]=$a3[2];
				
			}
			#printf("[%s]%s<br/>\n", $tag, print_r($attr,1) );
		}

		return array( "tag"=>$tag, "attr"=>$attr );
	}
	
	function parseTableFirst( $html )
	{
		$this->resetTagCounter('I');
		$this->aTabflow = array();
		
		$a = $this->splitTag($html);
		
		$iz = 0;
		$iW = 0;
		foreach( $a as $i=>$e )
		{
			$iz++;
			
			/**
			 * Jede zweite Zeile ist Text oder leer
			 */
			if( $i%2 == 0 )
			{
				if ( $iW == 1 )
				{
					$this->aTabflow[$this->iTAB][$this->iTR][$this->iTD] = trim($e);
                    //ITAB=Tabellenindex,ITR=RowIndex, iTD=TDIndex,$e=inhalt
				}
				$iW = 0;
			}
			else
			{
				if( $e[0] != '/' )
				{
					#print $e."<br/>\n";
					$aTag = $this->extractTag($e); // Extrahiert Main Tag und dessen Attribute
					$tag  = $aTag['tag'];
					$attr = $aTag['attr'];
					switch( $tag )
					{
						case 'TABLE':
							$this->iTD = 0;
							$this->iTR = 0;
							$this->iTAB++;
							$this->aTabflow[$this->iTAB] = array();
							break;
							
						case 'TR':
							$this->iTD = 0;
							$this->iTR++;
							$this->aTabflow[$this->iTAB]['TR'] = $this->iTR;
							break;

						case 'TD':
						case 'TH':
							$this->iTD++;
							if ( @$this->aTabflow[$this->iTAB]['TD'] < $this->iTD ) $this->aTabflow[$this->iTAB]['TD'] = $this->iTD;
							$this->aTabflow[$this->iTAB][$this->iTR][$this->iTD] = ',';
							$iW = 1;
							break;
					}
				}
			}
		}
		$this->resetTagCounter('I');
		
		if ( $this->iDebug !== false ) 
		{
			$this->WriteDebugLine($html);
			$this->Write(5, sprintf( '.%s', print_r($this->aTabflow,1)));
		}
	}
	
	function resetTagCounter( $t='A' )
	{
		switch ($t) {
			case 'I':
				$this->iTAB = 0;
				$this->iTR  = 0;
				$this->iTD  = 0;
				break;
				
			default:
				$this->aTD  = array();
				break;
		}
	}
	
	function WriteHTMLText( $html, $aTag )
	{
		$a = $this->splitTag($html);
	}
	
	/**
	 * WriteHTML
	 * HTML Parser zum parsen des HTML Strings und Vorbereitung zur Parametriesierung
	 * Ein String wird in seine Einzelteile / Tags zerlegt und vorbereitet
	 * @param string $html
	 */
	function WriteHTMLTable( $html )
	{
		#$this->parseTableFirst($html);
		
		$a = $this->splitTag($html);
		 # printf("[%s]%s<br/>\n", $tag, print_r($a,1) );
		$iz = 0;
		foreach( $a as $i=>$e )
		{
			$iz++;
			
			/**
			 * Jede zweite Zeile ist Text oder leer
			 */
			if( $i%2 == 0 )
			{
				//Text String
				if( $this->HREF )
				{
					$this->PutLink( $this->HREF, $e );
				}
				elseif( $this->tdbegin ) 
				{
					if( trim($e)!='' && $e!="&nbsp;" ) 
					{
						$this->Cell( $this->tdwidth,$this->tdheight,$e,$this->tableborder,'',$this->tdalign,$this->tdbgcolor );


                        //Cell mit attributen füllen
					}
					elseif( $e=="&nbsp;" ) 
					{
						$this->Cell( $this->tdwidth,$this->tdheight,'',$this->tableborder,'',$this->tdalign,$this->tdbgcolor );
					}
				}
				else
				{
					$this->Write( 5,stripslashes(txtentities($e)) );
                    
                    /*//Ausgabe eines Textes mit Höhe 5*/
				}
			}
			else
			{
				//Tag String
				if($e[0]=='/')
				{
					$this->CloseTag( strtoupper(substr($e,1)) );
				}
				else
				{
					$aTag = $this->extractTag($e);
					$tag  = $aTag['tag'];
					$attr = $aTag['attr'];
					$this->OpenTag( $tag,$attr );
					#printf("[%s] %s<br/>\n", $tag, print_r($attr,1) );
				}
			}
		}
		$this->resetTagCounter();
	}
	
	/**
	 * WriteHeader()
	 * Gibt den formatieten Header fest aus.
	 * @param string $html
	 */
	function WriteHTMLHeader( $html )
	{
		$a = $this->splitTag($html);
		
		$iz = 0;		// Standard Zähler
		$txtZ = 0;		// Textzähler
		$vtxZ = 0;		// Textzähler nur wenn Text vorhanden
		$tagZ = 0;		// Tagzähler (wird hier nur gemehrt, wenn ein IMG Tag vorhanden ist)
		$width = 197;	// PAgeWidth in mm
		
		foreach( $a as $i=>$e )
		{
		  
         
			$iz++;
			
			/**
			 * Jede zweite Zeile ist Text oder leer
			 */
			// TEXT
			if( $i%2 == 0 )//WENN TEXTZEILE
			{
				$txtZ++;
				if ( trim($e)!='' && $e!="&nbsp;" )
				{    
					$vtxZ++;
					switch( $vtxZ )
					{
						case 1:
							$this->SetY(10);
							$this->SetFontSize(15);
							break;
							
						case 2:
							$this->ln(4);
							$this->SetFontSize(9);
							break;
							
						case 3:
							$this->ln(7);
							$this->SetFontSize(8);
							break;
							
						case 4:
							$this->ln(7);
							$this->SetFontSize(10);
							break;
							
						case 5:
							$this->ln(3);
							$this->SetFontSize(8);
							break;
							
						case 6:
							$this->ln(3);
							$this->SetFontSize(8);
							break;
					}
					$this->SetX( $width-$this->GetStringWidth($e) );
					$this->Write( 5,stripslashes(txtentities($e)) );
					$this->SetFontSize(7);

				}
			}
			// TAG / IMG
			else
			{
				$aTag = $this->extractTag($e);
				$tag  = $aTag['tag'];
				$attr = $aTag['attr'];
				
				if ( $tag == "IMG" )
				{
					$tagZ++;
					
					switch ($tagZ) {
						
						case 1:
							if ( !strstr( $attr['SRC'], "blank.gif") )
							{
								$this->Image($attr['SRC'], 10, 25, px2mm(300), px2mm(65));
							}
							break;
							
						case 2:
							$this->Image($attr['SRC'], $width-px2mm(70), $vtxZ*7, px2mm(70), px2mm(70));
							break;
					}
				}
			}
		}
	}
	
	/**
	 * OpenTag
	 * öffnet einen HTML Tag in der PDF Klasse
	 * @param string $tag
	 * @param array $attr
	 */
	function OpenTag( $tag, $attr )
	{
		//Start Tag
		//diverse Anpassungen an Kunden Bedürfnisse
		switch( $tag ){
	
			case 'SUP':
				if( !empty($attr['SUP']) ) 
				{	
					//Set current font to 6pt 	
					$this->SetFont( '','',6 );
					//Start 125cm plus width of cell to the right of left margin 		
					//Superscript "1" 
					$this->Cell( 2,2,$attr['SUP'],0,0,'L' );
				}
				break;
	
			case 'TABLE': // TABLE-BEGIN
				$this->iTAB++;
				$this->iTR = 0;
				if( !empty($attr['BORDER']) ) $this->tableborder=$attr['BORDER'];
				#else $this->tableborder=0;
				else $this->tableborder='B';
				break;
			case 'TR': //TR-BEGIN
				$this->iTR++;
				$this->iTD = 0;
				break;
			case 'TH':
			case 'TD': // TD-BEGIN
				$this->iTD++;
				/*if( !empty($attr['WIDTH']) ) $this->tdwidth=($attr['WIDTH']/4);
				else $this->setTDdim( 'WIDTH' );
				if( !empty($attr['HEIGHT']) ) $this->tdheight=($attr['HEIGHT']/6);
				else $this->setTDdim( 'HEIGHT' );*/
				$this->setTDdim( 'WIDTH' );
				$this->setTDdim( 'HEIGHT' );
				
				if( !empty($attr['ALIGN']) ) {
					$align=$attr['ALIGN'];		
					if($align=='LEFT') $this->tdalign='L';
					if($align=='CENTER') $this->tdalign='C';
					if($align=='RIGHT') $this->tdalign='R';
				}
				else $this->tdalign='L'; // Set to your own
				if( !empty($attr['BGCOLOR']) ) {
					$coul=hex2dec($attr['BGCOLOR']);
						$this->SetFillColor($coul['R'],$coul['G'],$coul['B']);
						$this->tdbgcolor=true;
					}
				$this->tdbegin=true;
				break;
	
			case 'HR':
				if( !empty($attr['WIDTH']) )
					//$axt=1;
					$Width = $attr['WIDTH'];
				else
					$Width = $this->w - $this->lMargin-$this->rMargin;
				$x = $this->GetX();
				$y = $this->GetY();
				$this->SetLineWidth(0.2);
				$this->Line($x,$y,$x+$Width,$y);
				$this->SetLineWidth(0.2);
				$this->Ln(1);
				break;
			case 'STRONG':
				$this->SetStyle('B',true);
				break;
			case 'EM':
				$this->SetStyle('I',true);
				break;
			case 'B':
			case 'I':
			case 'U':
				$this->SetStyle($tag,true);
				break;
			case 'A':
				$this->HREF=$attr['HREF'];
				break;
			case 'IMG':
				if( isset($attr['SRC']) ) {
					if(!isset($attr['WIDTH']))
					{
						if ( $this->firstPic == 1 )
						{
							$attr['WIDTH'] = 540;
							$this->firstPic = 0;
						}
						else
						{
							$attr['WIDTH'] = 0;
						}
					}
					if(!isset($attr['HEIGHT']))
					{
						$attr['HEIGHT'] = 0;
					}
					$this->Image($attr['SRC'], $this->GetX(), $this->GetY(), px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']));
				}
				break;
			case 'BLOCKQUOTE':
			case 'BR':
				$this->Ln(5);
				break;
			case 'P':
				$this->Ln(10);
				break;
			case 'FONT':
				if (isset($attr['COLOR']) && $attr['COLOR']!='') {
					$coul=hex2dec($attr['COLOR']);
					$this->SetTextColor($coul['R'],$coul['G'],$coul['B']);
					$this->issetcolor=true;
				}
				if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
					$this->SetFont(strtolower($attr['FACE']));
					$this->issetfont=true;
				}
				if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist) && isset($attr['SIZE']) && $attr['SIZE']!='') {
					$this->SetFont(strtolower($attr['FACE']),'',$attr['SIZE']);
					$this->issetfont=true;
				}
				break;
		}
	}
	
	/**
	 * CloseTag
	 * schliesst einen HTML Tag in der PDF Klasse
	 * @param string $tag
	 */
	function CloseTag( $tag )
	{
		//schliesst Tag
		if($tag=='SUP') {
		}
	
		if($tag=='TD') { // TD-END
			$this->tdbegin=false;
			$this->tdwidth=0;
			$this->tdheight=0;
			$this->tdalign="L";
			$this->tdbgcolor=false;
		}
		if($tag=='TR') { // TR-END
			$this->Ln();
		}
		if($tag=='TABLE') { // TABLE-END
			//$this->Ln();
			$this->tableborder=0;
		}
	
		if($tag=='STRONG')
			$tag='B';
		if($tag=='EM')
			$tag='I';
		if($tag=='B' || $tag=='I' || $tag=='U')
			$this->SetStyle($tag,false);
		if($tag=='A')
			$this->HREF='';
		if($tag=='FONT'){
			if ($this->issetcolor==true) {
				$this->SetTextColor(0);
			}
			if ($this->issetfont) {
				$this->SetFont('arial');
				$this->issetfont=false;
			}
		}
	}
	
	function setTDdim( $switch="WIDTH" )
	{
		$aTD = $this->aTD;
		$iTD = $this->iTD;
		
		if ( count($aTD ) >= 1)
		{
		
			switch ($switch) {
				
				case 'WIDTH':
					if ( count($aTD[$switch]) >= 1)
					{
						$this->tdwidth = isset($aTD[$switch][$iTD])?$aTD[$switch][$iTD]:15;
					}
					else 
					{
						if ( $iTD == 1 ) 
						{ 
							$this->tdwidth = 50; 
						} 
						else 
						{ 
							$this->tdwidth = 25; 
						}
					}
					break;
					
				case 'HEIGHT':
					$this->tdheight=5;
					break;
			}
		}
		else
		{
			$this->tdwidth  = 25;
			$this->tdheight = 5;
		}
	}
	
	/**
	 * Style setzen
	 * @param string $tag
	 * @param int $enable
	 */
	function SetStyle( $tag, $enable )
	{
		//Modify style and select corresponding font
		$this->$tag+=($enable ? 1 : -1);
		$style='';
		foreach(array('B','I','U') as $s) {
			if($this->$s>0)
				$style.=$s;
		}
		$this->SetFont('',$style);
	}
	
	/**
	 * Linke setzen
	 * @param string $URL
	 * @param string $txt
	 */
	function PutLink($URL, $txt)
	{
		# Link setzen
		$this->SetTextColor(0,0,255);
		$this->SetStyle('U',true);
		$this->Write(5,$txt,$URL);
		$this->SetStyle('U',false);
		$this->SetTextColor(0);
	}
	
	function WriteDebugLine( $html )
	{
		$fs = $this->FontSize;
		#$this->SetFontSize(7);
		$this->Write( 2, sprintf("%s", print_r($this->splitTag($html),1)) );
		#$this->SetFontSize($fs);
	}
	
	function WriteKennzahlen()
	{
		if ( $this->GetY() >= 80 ) $this->AddPage();
		$this->Ln(10);
		$this->SetFont('Arial', 'B', 10 );
		$this->Cell(190, 4, "Formeln zur Berechnung der Kennzahlen", 'B', 1, 'L');
		$this->SetFont('Arial', '', 7);
		$this->Ln(5);
		$this->Cell(50, 8, "Konsolidierte EK Quote =");
		$this->Cell(100, 4, "Konsolidiertes Eigenkapital", 'B', 1, 'C');
		$this->Ln(1);
		$this->Cell(50, 1, "");
		$this->Cell(100, 4, "Konsolidiertes Eigenkapital", 0, 1, 'C');
		$this->Ln(5);
		#$this->SetLineWidth(1);
		$this->SetDrawColor(20, 20, 20);
		$this->Line($this->GetX(), $this->GetY(), $this->GetX()+190, $this->GetY());
		$this->Ln(5);
		$this->Cell(50, 8, "Cash flow (operativ) =");
		$this->Cell(100, 8, "Gesamtergebnis nach Steuern + Abschreibungen", 0, 1, 'C');
		$this->Ln(5);
		#$this->SetLineWidth(1);
		$this->SetDrawColor(20, 20, 20);
		$this->Line($this->GetX(), $this->GetY(), $this->GetX()+190, $this->GetY());
		$this->Ln(5);
		$this->Cell(50, 8, "Cash flow Quote =");
		$this->Cell(100, 4, "Cash flow (operativ) x 100", 'B', 1, 'C');
		$this->Ln(1);
		$this->Cell(50, 1, "");
		$this->Cell(100, 4, "Umsatzerlöse", 0, 1, 'C');
		$this->Ln(5);
		#$this->SetLineWidth(1);
		$this->SetDrawColor(20, 20, 20);
		$this->Line($this->GetX(), $this->GetY(), $this->GetX()+190, $this->GetY());
		$this->Ln(5);
		$this->Cell(50, 8, "Rendite Betriebsergebnis =");
		$this->Cell(100, 4, "Betriebsergebnis (EBIT) x 100", 'B', 1, 'C');
		$this->Ln(1);
		$this->Cell(50, 1, "");
		$this->Cell(100, 4, "Umsatzerlöse", 0, 1, 'C');
		$this->Ln(5);
		#$this->SetLineWidth(1);
		$this->SetDrawColor(20, 20, 20);
		$this->Line($this->GetX(), $this->GetY(), $this->GetX()+190, $this->GetY());
		$this->Ln(5);
		$this->Cell(50, 8, "Rendite Ergebnis =");
		$this->Cell(100, 4, "Gesamtergebnis nach Steuern x 100", 'B', 1, 'C');
		$this->Ln(1);
		$this->Cell(50, 1, "");
		$this->Cell(100, 4, "Umsatzerlöse", 0, 1, 'C');
		$this->Ln(5);
		#$this->SetLineWidth(1);
		$this->SetDrawColor(20, 20, 20);
		$this->Line($this->GetX(), $this->GetY(), $this->GetX()+190, $this->GetY());
		$this->Ln(5);
		$this->Cell(50, 8, "Anlagedeckungsgrad II =");
		$this->Cell(100, 4, "Eigenkapital + langfristiges Fremdkapital x 100", 'B', 1, 'C');
		$this->Ln(1);
		$this->Cell(50, 1, "");
		$this->Cell(100, 4, "Anlagevermögen", 0, 1, 'C');
		$this->Ln(10);
	}

}//eOf class PDF

?>
