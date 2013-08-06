var models = function(url){
	
	$("#add-model").on('click', function(evt){
		$("#model-title").html("Nouveau modèle");
		$("#form").load(url+'/models/form');	
	});
	
	$(".mod").on('click', function(){
		$("#model-title").html("Modification de <em>"+$(this).data('name')+"</em>");
		$("#form").load(url+'/models/form'+'?id='+$(this).data('id'));	
	});
	
	$("#model-container").on('click', '#new-action', function(){
		$("#action-title").html("Nouveau modèle");
		$("#action-form").load(url+'/models/form?action=true');
	});
	
	$(document).on('change', 'select[name=category]', function(){
		$(this).closest(".modal-body").find(".custom-fields").load(url+'/models/customfields?id='+$(this).val(), function(){
			
		});
	});
	
	
};