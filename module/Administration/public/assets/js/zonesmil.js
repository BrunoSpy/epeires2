var mil = function(url){
	
	
	$(".mod-mil").on('click', function(){
		$("#zone-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#zone-form").load(url+'/mil/form?id='+$(this).data('id'));
	});
	
	$("#zone-container").on('click', 'input[type=submit]', function(event){
		event.preventDefault();
		$.post(url+'/mil/save', $("#MilCategory").serialize(), function(data){
			location.reload();
		}, 'json');
	});

};