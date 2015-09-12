function getParam(get_var)
{
	// aktuelle URL der Seite ermitteln
	var query = window.location.search.substring(1);
	var vars  = query.split('&');

	// alle vorhandenen Parameter durchlaufen
	for (var i=0; i<vars.length; i++) {
		// Parameter zerlegen in
		// Parameter-Name : Parameter-Wert
		var pair = vars[i].split('=');

		// es wird geprüft, ob der gesuchte GET-Parameter existiert
		if (pair[0] == get_var) {
			// diese Funktion liefert nun wie man es aus PHP kennt,
			// den Wert des entsprechenden GET–Parameters zurück
			return pair[1];
		}
	}
	
	// alternativ wird false zurückgeliefert,
	// falls dieser Parameter (Wert) nicht vorhanden ist
	return false;
}

function sleep(delay)
{ 
	var start = new Date().getTime(); 
	
	while (new Date().getTime() < start + delay);
	
	return true;
}

function getLocaleDate()
{
	var datum = new Date();
	
	var tag = (datum.getDate()  < 10) ? '0' + datum.getDate()        : datum.getDate();
	var mon = (datum.getMonth() < 10) ? '0' + (datum.getMonth() + 1) : (datum.getMonth() + 1);
	var jhr = datum.getFullYear();
	
	return tag + '.' + mon + '.' + jhr;
}

function Runden2Dezimal(x)
{
	var erg = Math.round(x * 100) / 100;
	
/*	erg = erg.toString();
	
	var komma  = erg.indexOf('.');
	var laenge = erg.length - 1;
	
	if ((laenge - komma) != 2) {
		
	}*/

	return erg;
}