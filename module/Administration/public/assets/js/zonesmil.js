var mil = function(url){
	
	
	$(".mod-mil").on('click', function(){
		$("#zone-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#zone-form").load(url+'/mil/form?id='+$(this).data('id'), function(){
                    $(this).find(".pick-a-color").pickAColor();
                    $.material.checkbox();
                });
	});
	
	$("#zone-container").on('submit', function(event){
		event.preventDefault();
		$.post(url+'/mil/save', $("#MilCategory").serialize(), function(data){
			location.reload();
		}, 'json');
	});

};