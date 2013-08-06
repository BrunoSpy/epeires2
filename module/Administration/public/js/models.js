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
		$("#action-form").load(url+'/models/form?action=true&parentid='+$(this).data('parent'));
	});
	
	$(".delete").on('click', function(){
		$('a#delete-href').attr('href', $(this).data('href'));
		$('#model_name').html($(this).data('name'));
	});
	
	$("#model-container").on('click', '.action-delete', function(event){
		event.preventDefault();
		var me = $(this);
		$.post(url+'/models/delete?id='+$(this).data('id'), function(){
			me.closest('tr').remove();
		});
	});
	
	$(document).on('change', 'select[name=category]', function(){
		$(this).closest(".modal-body").find(".custom-fields").load(url+'/models/customfields?id='+$(this).val(), function(){
			
		});
	});
	
	$("#model-container").on('click', 'input[type=submit]', function(event){
		event.preventDefault();
		$.post(url+'/models/save', $(this).closest("#PredefinedEvent").serialize(), function(){
			location.reload();
		});
	});
	
	$("#action-container").on('click', 'input[type=submit]', function(event){
		event.preventDefault();
		$.post(url+'/models/save', $(this).closest("#PredefinedEvent").serialize(), function(data){
			var newtr = $('<tr id="'+data.id+'"></tr>');
			newtr.append('<td>'+data.name+'</td>');
			newtr.append('<td><span class="label label-'+data.impactstyle+'">'+data.impactname+'</span></td>');
			newtr.append('<td><span class="caret middle"></span> <span class="up-caret middle"></span>');
			newtr.append('</td><td><i class="icon-pencil"></i> <a title="Supprimer"'+ 
						'href="'+url+'/models/delete?id='+data.id+'&redirect=0 '+ 
						'class="action-delete" '+ 
						'data-id='+data.id +' '+ 
						'data-name='+data.name+' '+ 
						'><i class="icon-trash"></i></td>');
			$("#actions-table").append(newtr);
			$("#action-container").modal('hide');
		}, 'json');
	});
};