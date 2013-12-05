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
			if(inputSearch.length > 2) {
				$.post(url+"/search", $("#search").serialize(), function(data){
					if(data.length < 1) {
						$("#results").html("Aucun résultat.");
					} else {
						var html = "";
						$.each(data, function(key, value){
							html += '<p>'+value.name+'</p>';
						});
						$("#results").html(html);
					}
				});
			} else {
				$("#results").html("3 caractères minimum");
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