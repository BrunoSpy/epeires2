var atfcm = function(url){
	
	
	$(".mod-atfcm").on('click', function(){
		$("#atfcm-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#atfcm-form").load(url+'/atfcm/form?id='+$(this).data('id'), function(){
                    $(this).find(".pick-a-color").pickAColor();
                    $.material.checkbox();
                });
	});
	
	$("#atfcm-container").on('submit', function(event){
		event.preventDefault();
		$.post(url+'/atfcm/save', $("#ATFCMCategory").serialize(), function(data){
			location.reload();
		}, 'json');
	});

};