/**
 * @author Bruno Spyckerelle
 */

var search = function(url){
	   //search events
	   $("#search").submit(function(e){
		   e.preventDefault();
		   $("#search-results").offset({top: $(this).offset().top+$(this).outerHeight()});
			$("#results").html('<div>Chargement...</div>');
			var inputSearch = $("#search input").val();
			if(inputSearch.length >= 2) {
				$.post(url+"/search", $("#search").serialize(), function(data){
					if(data.events.length < 1 && data.models.length < 1) {
						$("#results").html("Aucun résultat.");
					} else {
						$('#results').empty();
						var dl = $("<dl></dl>");
						$.each(data.models, function(key, value){
							dl.append(createModelEntry(key, value));
						});
						//TODO convert json into array as json has no order...
						$.each(data.events, function(key, value){
							dl.append(createEventEntry(key, value));
						});
						$("#results").append(dl);
					}
				});
			} else {
				$("#results").html("2 caractères minimum");
			}
			
			$("#search-results").show();
			$("#search-results").slideDown('fast');
	   });
	   
	   $("#search").on({
		mouseenter:function(){
			$(this).tooltip('show');
		},
		mouseleave:function(){
			$(this).tooltip('hide');
		}
	   }, '.result');
	   
	   //hide search results if click outside
	   $(document).mouseup(function(e){
		   var container = $("#search-results");
		   if(!container.is(e.target) && container.has(e.target).length === 0){
			   if($('#search-results').is(':visible')){
				   container.slideUp('fast', function(){
					   $("#search-results").css({'top':'0px'});
				   });
			   }
		   }
	   });
	   
};

var createEventEntry = function(id, event){
	var div = $('<div class="result"></div>');
	var html = "";
	var start = new Date(event.start_date);
	var end = new Date(event.end_date);
	html += "<dt>"+event.name+((event.status_id <= 2) ? ' <em>(en cours)</em>' : '');
	if(event.status_id <= 2){
		//evt en cours : modifier l'evt
		html += '<a data-name="'+event.name+'" data-id="'+id+'" class="btn btn-sm btn-primary pull-right modify-evt">Modifier</a>';
	} else {
		//evt terminé : copier
		html += '<a data-id='+id+' class="btn btn-sm btn-primary pull-right copy-event">Copier</a>';
	}
	html += "</dt>";
	html += '<dd>';
	html += '<small>Catégorie : '+event.category+'</small>';
	html += '</dd>';
	div.append(html);
	var titlehtml = '<b>Date de début :</b> '+FormatNumberLength(start.getUTCDate(), 2)+'/'+FormatNumberLength(start.getUTCMonth()+1,2);
	if(event.end_date != null){
		titlehtml += '<br /><b>Date de fin :</b> '+FormatNumberLength(end.getUTCDate(), 2)+'/'+FormatNumberLength(end.getUTCMonth()+1,2);
	}
	$.each(event.fields, function(key, value){
		titlehtml += '<br/><b>'+key+' :</b> '+value; 
	});
	div.tooltip({
		title: titlehtml,
		container:'body',
		placement:'left',
		html:true
	});
	return div;
};

var createModelEntry = function(id, model){
	var html = '<div class="result">';
	html += "<dt>Modèle : "+model.name;
	html += '<a data-id='+id+' class="btn btn-sm btn-primary pull-right use-model">Utiliser</a>';
	html += "</dt>";
	html += '<dd>';
	html += '<small>Catégorie : '+model.category+'</small>';
	html += '</dd></div>';
	return html;
};