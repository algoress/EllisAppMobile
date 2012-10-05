// JavaScript Document
var l_lang;
if (navigator.userLanguage) // Explorer
	l_lang = navigator.userLanguage;
  else if (navigator.language) // FF
	l_lang = navigator.language;
  else
	l_lang = "en";

$(document).ready(function(e) {
	// Tra 5 minuti lancia una richiesta per aggiornare i dati.
	// Se siamo online i dati saranno ricevuti e processati immediatamente
	// In caso contrario verrà messa in cosa una richiesta appena
	// torneremo online
	setTimeout(function periodicUpdater() {
	// passa la funzione "periodicUpdater" come callback a "updateArticles"
		updateArticles(periodicUpdater);
	}, 300000)
	 
	// Aggiorna i dati prendendoli dalla cache
	//
	// Se siamo online verràà eseguito un processo in background
	// per aggiornare la cache. Sennò si mette in coda per quando
	// saremo online
	updateArticles();
	
	
	  
	
	
});

// FUNZIONE LISTA PRODOTTI
var updateArticles = function(callback) {
		// jQuery.retrieveJSON usa le funzioni Ajax di jQuery
		// quindi possiamo associare funzioni agli eventi "normali" 

		$.retrieveJSON("http://test.vision121.it/appPhoneGap/ext/findprod-"+l_lang+".php", function(json, status) {
			//var content = $("#lista-prod").render( json );
			$('#dett-prod').empty();
			$('#dett-prod').append('<ul id="lista-prod"></ul>');
			$(json).each(function(index, element) {
				var prodlink = "javascript:dettProd('"+this["id"]+"');"
				$('<li><a href="'+prodlink+'"><img src="http://test.vision121.it/appPhoneGap/_files/immagini/'+this["immagine"]+'" alt="'+this["nome"]+'"  /></a><br />'+this["nome"]+'</li>').appendTo($('#lista-prod'));
			});
			// Se siamo online e dunque abbiamo recuperato i dati
			// metti in coda un aggiornamento fra 5 minuti
			if( status == "success" ) { setTimeout( callback, 300000 ); }
	   });
	}

//trovo dettaglio prod
function dettProd(idprod) {

	$.ajax({
		type: "GET",
		data: {id_prod: idprod},
		url: "http://test.vision121.it/appPhoneGap/ext/dett-prod-"+l_lang+".php",
		dataType: "json",
		success: parseDettProd,
		error: function(jqXHR, exception) {
			if (jqXHR.status === 0) {
				alert('Not connect.\n Verify Network.');
			} else if (jqXHR.status == 404) {
				alert('Requested page not found. [404]');
			} else if (jqXHR.status == 500) {
				alert('Internal Server Error [500].');
			} else if (exception === 'parsererror') {
				alert('Requested JSON parse failed.');
			} else if (exception === 'timeout') {
				alert('Time out error.');
			} else if (exception === 'abort') {
				alert('Ajax request aborted.');
			} else {
				alert('Uncaught Error.\n' + jqXHR.responseText);
			}
		}
	});	
}

function parseDettProd(json) {
	$("#dett-prod").empty();
	if ($(json).length != 0) {
		$(json).each(function(index, element) {
			$('<div id="menu-prod"><a href="javascript:updateArticles();"><< Lista Prodotti</div>').appendTo($('#dett-prod'));
			$('<div id="dett-prod-img"><img src="http://test.vision121.it/appPhoneGap/_files/immagini/'+this["immagine"]+'" alt="'+this["nome"]+'"  /></div><div id="dett-prod-txt"><h2>'+this["nome"]+'</h2>'+this["descrizione"]+'</div>').appendTo($('#dett-prod'));
			//$('#testo').append(this["nome_it"]).trigger("create");
		});
	}
}

