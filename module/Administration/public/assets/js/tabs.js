var tab = function(url){
	
	$("#add-tab").on('click', function(){
		$("#tab-title").html("Nouvel onglet");
		$("#tab-form").load(url+'/tabs/form');
	});
	
	$(".mod-tab").on('click', function(){
		$("#tab-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#tab-form").load(url+'/tabs/form?id='+$(this).data('id'));
	});
	
	$("#tab-container").on('click', 'input[type=submit]', function(event){
		event.preventDefault();
		$.post(url+'/tabs/save', $("#Tab").serialize(), function(data){
			location.reload();
		}, 'json');
	});

};