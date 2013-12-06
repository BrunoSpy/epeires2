/**
 * @author Bruno Spyckerelle
 */

var search = function(url){
	   //search events
	   $("#search").submit(function(e){
		   e.preventDefault();
		   $("#search-results").offset({top: $(this).offset().top+$(this).outerHeight(), left:$(this).offset().left});
			$("#results").html('<div>Chargement...</div>');
			var inputSearch = $("#search input").val();
			if(inputSearch.length >= 2) {
				$.post(url+"/search", $("#search").serialize(), function(data){
					if(data.events.length < 1 && data.models.length < 1) {
						$("#results").html("Aucun résultat.");
					} else {
						var html = "<dl>";
						$.each(data.models, function(key, value){
							html += createModelEntry(key, value);
						});
						$.each(data.events, function(key, value){
							html += createEventEntry(key, value);
						});
						html += "</dl>";
						$("#results").html(html);
					}
				});
			} else {
				$("#results").html("2 caractères minimum");
			}
			
			$("#search-results").show();
			$("#search-results").slideDown('fast');
	   });
	   
	   //hide search results if click outside
	   $(document).mouseup(function(e){
		   var container = $("#search-results");
		   if(!container.is(e.target) && container.has(e.target).length === 0){
			   container.slideUp('fast');
			   $("#search-results").offset({top:0, left:0});
		   }
	   });
	   
};

var createEventEntry = function(id, event){
	var html = "";
	html += "<dt>"+event.name+"</dt>";
	html += '<dd>';
	html += '<small>Catégorie : '+event.category+'</small>';
	//TODO si evt en cours -> modifier
	html += '<a data-id='+id+' class="btn btn-mini pull-right copy-event">Copier</a></dd>';
	return html;
};

var createModelEntry = function(id, model){
	var html = "";
	html += "<dt>Modèle : "+model.name+"</dt>";
	html += '<dd>';
	html += '<small>Catégorie : '+model.category+'</small>';
	html += '<a data-id='+id+' class="btn btn-mini pull-right">Utiliser</a></dd>';
	return html;
};