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
	
	$("#model-container").on('click', '.mod', function(){
		$("#action-title").html("Modifier <em>"+$(this).data('name')+"</em>");
		$("#action-form").load(url+'/models/form?action=true&parentid='+$(this).data('parent')+'&id='+$(this).data('id'));
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
			updateCarets($("#model-container"));
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
	
	var updateCarets = function(element){
		var tbody = element.find('tbody');
		tbody.find('a.up').removeClass('disabled');
		tbody.find('a.down').removeClass('disabled');
		tbody.find('tr:first a.up').addClass('disabled');
		tbody.find('tr:last a.down').addClass('disabled');
	};
	
	$("#model-container").on('click', 'a.up', function(event){
		event.preventDefault();
		var me = $(this);
		$.post(me.attr('href'), function(){
			var metr = me.closest('tr');
			var prevtr = metr.prev();
			metr.remove();
			prevtr.before(metr);
			updateCarets($("#model-container"));
		});
	});
	
	$("#model-container").on('click', 'a.down', function(event){
		event.preventDefault();
		var me = $(this);
		$.post(me.attr('href'), function(){
			var metr = me.closest('tr');
			var nexttr = metr.next();
			metr.remove();
			metr.insertAfter(nexttr);
			updateCarets($("#model-container"));
		});
	});
	
	$("#action-container").on('click', 'input[type=submit]', function(event){
		event.preventDefault();
		var id = $("#action-form").find('input[name=id]').val();
		$.post(url+'/models/save', $(this).closest("#PredefinedEvent").serialize(), function(data){
			if(id > 0){ //modification d'une action existante
				var tr = $("tr#"+id);
				tr.find('td:eq(0)').html(data.name);
				tr.find('td:eq(1)').html('<span class="label label-'+data.impactstyle+'">'+data.impactname+'</span>');
				tr.find('td:eq(3) > a').data('name', data.name);
			} else { //nouvelle action
				var newtr = $('<tr id="'+data.id+'"></tr>');
				newtr.append('<td>'+data.name+'</td>');
				newtr.append('<td><span class="label label-'+data.impactstyle+'">'+data.impactname+'</span></td>');
				newtr.append('<td><a class="down" href="'+url+'/models/down?id='+data.id+'">'+
							'<span class="caret middle"></span></a> '+
							'<a class="up" href="'+url+'/models/up?id='+data.id+'"><span class="up-caret middle"></span></a>');
				newtr.append('<td><i class="icon-pencil"></i> <a title="Supprimer"'+ 
						'href="'+url+'/models/delete?id='+data.id+'&redirect=0 '+ 
						'class="action-delete" '+ 
						'data-id='+data.id +' '+ 
						'data-name="'+data.name+'" '+ 
				'><i class="icon-trash"></i></td>');
				$("#actions-table").append(newtr);
				updateCarets($("#model-container"));
			}
			$("#action-container").modal('hide');
		}, 'json');
	});
};