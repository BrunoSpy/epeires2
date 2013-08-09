var categories = function(url){
	var reload = false;
	var closesttr;
	
	var updateCarets = function(element){
		var tbody = element.find('tbody');
		tbody.find('a.up').removeClass('disabled');
		tbody.find('a.down').removeClass('disabled');
		tbody.find('tr:first a.up').addClass('disabled');
		tbody.find('tr:last a.down').addClass('disabled');
	};
	
	/* ************************************ */
	/*  Fenêtre création/mod d'une action   */
	/* ************************************ */
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
	/* ************************************ */
	
	/* ************************************ */
	/*  Fenêtre de création/mod de modèle   */
	/* ************************************ */
	$("#model-container").on('click', 'input[type=submit]', function(event){
		event.preventDefault();
		var catid = $("#PredefinedEvent select[name=category] option:selected").val();
		$.post($("#PredefinedEvent").attr('action')+'?catid='+catid, $("#PredefinedEvent").serialize(), function(data){
			var id = $("#PredefinedEvent input[type=hidden]").val();
			if(id > 0){
				var tr = $("#models-container tr#"+data.id);
				tr.find('td:eq(0)').html(data.name);
				tr.find('td:eq(1) a').data('name', data.name);
			} else {
				var tr = $('<tr id="'+data.id+'"></tr>');
				tr.append('<td>'+data.name+'</td>');
				tr.append('<td><a '+
							'title="Modifier" '+
							'class="mod" '+
							'href="#model-container" '+
							'data-toggle="modal" '+
							'data-id='+data.id+' '+
							'data-name="'+data.name+'" '+
							'><i class="icon-pencil"></i></a>'+ 
							'<a '+
							'href="#confirm-delete-model" '+
							'data-toggle="modal" '+
							'title="Supprimer" '+ 
							'data-href="'+url+'/models/delete?id='+data.id+'&redirect=0" '+ 
							'class="delete" '+
							'data-id='+data.id+' '+
							'data-name="'+data.name+'" >'+ 
							'<i class="icon-trash"></i></a></td>');
				$("#models-container tbody").append(tr);
			}
			$("#model-container").modal('hide');
		}, 'json');
	});
	
	$("#confirm-delete-model").on('click', '#delete-model-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-model").modal('hide');
		$.post($("#delete-model-href").attr('href'), function(){
			me.closest('tr').remove();
		});
	});
	
	$(document).on('change', 'select[name=category]', function(){
		$(this).closest(".modal-body").find(".custom-fields").load(url+'/models/customfields?id='+$(this).val(), function(){
			
		});
	});
	
	$("#model-container").on('click', '#new-action', function(){
		$("#action-title").html("Nouveau modèle");
		$("#action-form").load(url+'/models/form?action=true&parentid='+$(this).data('parent'));
	});
	
	$("#model-container").on('click', '.mod', function(){
		$("#action-title").html("Modifier <em>"+$(this).data('name')+"</em>");
		$("#action-form").load(url+'/models/form?action=true&parentid='+$(this).data('parent')+'&id='+$(this).data('id'));
	});
	
	$("#model-container").on('click', '.action-delete', function(event){
		event.preventDefault();
		var me = $(this);
		$.post(url+'/models/delete?id='+$(this).data('id'), function(){
			me.closest('tr').remove();
			updateCarets($("#model-container"));
		});
	});
	
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
	/* ************************************ */
	
	/* ************************************ */
	/* *** Fenêtre de liste des modèles *** */
	/* ************************************ */
	$("#models-table").on('click', '.new', function(){
		var catid = $(this).data('catid');
		$("#model-title").html('Nouveau modèle');
		$("#model-form").load(url+'/models/form?catid='+catid);
	});
	
	$("#models-table").on('click', '.mod', function(){
		$("#model-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#model-form").load(url+'/models/form?id='+$(this).data('id'));
	});
	
	$("#models-table").on('click', '.delete', function(event){
		$("#model_name").html($(this).data('name'));
		$("#delete-model-href").attr('href', $(this).data('href'));
	});
	
	$(".models-list").on('click', function(){
		$("#models-title").html("Modèles de "+$(this).data('name'));
		$("#models-table").load($(this).data('href'));
	});
	/* ************************************ */
	
	$(".mod").on('click', function(){
		$("#form-title").html("Modification de "+$(this).data('name'));
		$("#form").load(url+'/categories/form'+'?id='+$(this).data('id'),
				function(){
					$("#form").find(".colorpicker").spectrum({
						showInitial: false,
						showInput: true,
						preferredFormat: "hex",
					});
			});	
	});

	$("#add-cat").on('click', function(evt){
		$("#form-title").html("Nouvelle catégorie");
		$("#form").load(url+'/categories/form',
				function(){
			$("#form").find(".colorpicker").spectrum({
				showInitial: false,
				showInput: true,
				preferredFormat: "hex",
			}
			);
		}
		);	
	});

	$(".mod_fields").on('click', function(){
		$("#fields-title").html("Champs de la catégorie <em>"+$(this).data('name')+"</em>");
		$("#fields-table").load(url+'/categories/fields'+'?id='+$(this).data('id'));
	});

	$(".delete").on('click', function(){
		$('a#delete-href').attr('href', $(this).data('href'));
		$('#cat_name').html($(this).data('name'));
	});

	$("#fieldscontainer").on('click', '#new-field', function(){
		$("#add-field").load(url+'/fields/form'+'?categoryid='+$(this).data('id'));
	});

	$("#fieldscontainer").on('click', '#cancel-form-field', function(){
		var id = $(this).closest('tr').find('input[type=hidden]').val();
		if(id>0){
			$(this).closest('tr').html(closesttr);
			closesttr = null;
		} else {
			$("#add-field").html('');
		}

		$('#new-field').removeClass('disabled');
	});


	$("#fieldscontainer").on('click', '.mod-field', function(){
		var me = $(this);	
		closesttr = me.closest('tr').html();
		me.closest('tr').load(url+'/fields/form'+'?id='+$(this).data('id'));
		//don't add a new field during modifying one
		$('#new-field').addClass('disabled');
	});


	$("#fieldscontainer").on('click', '.delete-field', function(){
		var me = $(this);	
		$('#field_name').html(me.data('name'));
		$('#delete-field-href').attr('href', me.data('href'));
		closesttr = me.closest('tr');
	});

//	confirm delete field
	$('#delete-field-href').on('click', function(event){
		event.preventDefault();
		$.post($(this).attr('href'), function(){
			closesttr.remove();
			closesttr = null;
			reload = true;
			updateCarets($("#fieldscontainer"));
		});
		$("#confirm-delete-field").modal('hide');
	});

	$('#fieldscontainer').on('hide', function(){
		//on hide, refresh category page
		if(reload){//reload only if changes
			reload = false;
			location.reload();
		}
	});

	$('#fieldscontainer').on('click', '.up', function(event){
		var me = $(this);
		event.preventDefault();
		var href = me.attr('href');
		$.post(href, function(){
			var metr = me.closest('tr');
			var prevtr = metr.prev();
			metr.remove();
			metr.insertBefore(prevtr);
			updateCarets($('#fieldscontainer'));
		});
	});

	$('#fieldscontainer').on('click', '.down', function(event){
		var me = $(this);
		event.preventDefault();
		var href = me.attr('href');
		$.post(href, function(){
			var metr = me.closest('tr');
			var nexttr = metr.next();
			metr.remove();
			metr.insertAfter(nexttr);
			updateCarets($('#fieldscontainer'));
		});
	});
	
//	ajaxify form field submit
	$('#fieldscontainer').on('click', 'input[type=submit]', function(event){
		event.preventDefault();
		var me = $(this);
		href = $(this).closest('form').attr('action');
		$.post(href, $("#CustomField").serialize(), function(data){
			var id = me.closest('tr').find('input[type=hidden]').val();
			var tr = me.closest('tr');
			if(id>0){
				var i = tr.parent().children().index(tr);
				var size = tr.parent().children().size();
				//modify
				tr.find('td:eq(0)').html(data.id);
				tr.find('td:eq(1)').html(data.name);
				tr.find('td:eq(2)').html(data.type);
				//name can change
				tr.find('td:eq(4)').html('<a href="#" class="mod-field" data-id="'+data.id+'" data-name="'+data.name+'"><i class="icon-pencil"></i></a> '+
						'<a href="#confirm-delete-field" '+
						'data-href="'+url+'/fields/delete?id='+data.id+ 
							' class="delete-field" '+ 
							'data-id="'+data.id+'" '+ 
							'data-name="'+data.name+'" '+ 
							'data-toggle="modal"><i class="icon-trash"></i> </a>');					
				updateCarets($("#fieldscontainer"));
			} else {
				var tr = me.closest('tr');
				var newhtml = $("<tr></tr>");
				newhtml.append('<td>'+data.id+'</td>');
				newhtml.append('<td>'+data.name+'</td>');
				newhtml.append('<td>'+data.type+'</td>');
				newhtml.append('<td>'+'<a href="'+url+'/fields/fieldup?id='+data.id+'" class="up"><span class="up-caret middle"></span></a> '+
						'<a href="'+url+'/fields/fielddown?id='+data.id+'" class="down disabled"><span class="caret middle"></span></a>');
				newhtml.append('<td>'+'<a href="#" class="mod-field" data-id="'+data.id+'" data-name="'+data.name+'"><i class="icon-pencil"></i></a> '+
						'<a href="#confirm-delete-field" '+
						'data-href="'+url+'/fields/delete?id='+data.id+ 
							' class="delete-field" '+ 
							'data-id="'+data.id+'" '+ 
							'data-name="'+data.name+'" '+ 
							'data-toggle="modal"><i class="icon-trash"></i> </a></td>');

				newhtml.insertBefore(tr);
				tr.remove();
				updateCarets($("#fieldscontainer"));
			}
			reload = true;
		}, 'json');
		$('#new-field').removeClass('disabled');
	});
}