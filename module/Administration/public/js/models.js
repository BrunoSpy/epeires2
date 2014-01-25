/**
 * JS to be used with modal windows for editing models
 * Id to trigger "new model window" : #add-model with data-catid for prepositioning category
 * Id to modify a model : #mod-model with data-name and data-id
 * Id to delete a model : #delete-model
 */

var models = function(url){
	
	//by default last = true
	var updateCarets = function(element, last){
		var tbody = element.find('tbody');
		tbody.find('a.up').removeClass('disabled');
		tbody.find('a.down').removeClass('disabled');
		tbody.find('tr:first a.up').addClass('disabled');
		if((typeof last !== 'undefined') && !last){
			tbody.find('tr:last').prev().find('a.down').addClass('disabled');
		} else {
			tbody.find('tr:last a.down').addClass('disabled');
		}
		
	};
	
	
	/* **************************** */
	/*         List of models       */
	/* **************************** */
	$("#models-container").on('hidden', function(){
		//TODO do it only when usefull
		location.reload();
	});
	
	
	/* **************************** */
	/*          New model           */
	/* **************************** */
	$(document).on('click', "#add-model", function(evt){
		$("#model-title").html("Nouveau modèle");
		var catid = $(this).data('catid');
		if(typeof catid === 'undefined'){
			$("#model-form").load(url+'/models/form', function(){
				fillZoneFilters($("#model-container select[name=organisation]").val());
			});	
		} else {
			$("#model-form").load(url+'/models/form?catid='+catid, function(){
				fillZoneFilters($("#model-container select[name=organisation]").val());
			});
		}
	});
	
	/* **************************** */
	/*        Delete a model        */
	/* **************************** */	
	$(document).on('click', ".delete-model", function(){
		$('a#delete-model-href').attr('href', $(this).data('href'));
		$('#model_name').html($(this).data('name'));
		$("#delete-model-href").data('id', $(this).data('id'));
		
	});
	
	$("#confirm-delete-model").on('click', '#delete-model-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-model").modal('hide');
		$.post($("#delete-model-href").attr('href'), function(){
			$('#models-table').find('tr#'+me.data('id')).remove();
		});
	});
	
	/* **************************** */
	/*        Mod of a model        */
	/* **************************** */
	
	$(document).on('change', 'select[name=category]', function(){
		$(this).closest(".modal-body").find(".custom-fields").load(url+'/models/customfields?id='+$(this).val(), function(){
			
		});
	});
	$(document).on('click',".mod-model", function(){
		$("#model-title").html("Modification de <em>"+$(this).data('name')+"</em>");
		$("#model-form").load(url+'/models/form'+'?id='+$(this).data('id'), function(){
			fillZoneFilters($("#model-container select[name=organisation]").val());
		});	
	});
		
	$("#model-container").on('click', 'input[type=submit]', function(event){
		event.preventDefault();
		var catid = $("#PredefinedEvent select[name=category] option:selected").val();
		$.post($("#model-form #PredefinedEvent").attr('action')+'?catid='+catid, $(this).closest("#PredefinedEvent").serialize(), function(data){
			var id = $("#PredefinedEvent input[type=hidden]").val();
			if(id > 0){
				var tr = $("#models-container tr#"+data.id);
				tr.find('td:eq(0)').html(data.name);
				tr.find('td:eq(1) a').data('name', data.name);
			} else {
				var tr = $('<tr id="'+data.id+'"></tr>');
				tr.append('<td>'+data.name+'</td>');
				tr.append('<td>'+
							'<a	href="'+url+'/models/down?id='+data.id+'"'+
								'class="down"><span class="caret middle"></span></a>'+
							'<a	href="'+url+'/models/up?id='+data.id+'"'+
								'class="up"> <span class="up-caret middle"></span></a>'+
							'<a '+
							'title="Modifier" '+
							'class="mod-model" '+
							'href="#model-container" '+
							'data-toggle="modal" '+
							'data-id='+data.id+' '+
							'data-name="'+data.name+'" '+
							'> <i class="icon-pencil"></i></a>'+ 
							'<a '+
							'href="#confirm-delete-model" '+
							'data-toggle="modal" '+
							'title="Supprimer" '+ 
							'data-href="'+url+'/models/delete?id='+data.id+'&redirect=0" '+ 
							'class="delete-model" '+
							'data-id='+data.id+' '+
							'data-name="'+data.name+'" >'+ 
							' <i class="icon-trash"></i></a></td>');
				$("#models-container tbody").append(tr);
			}
			$("#model-container").modal('hide');
			//reload only if no other modal
			if(!$("#models-container").is(':visible')){
				location.reload();
			}
		}, 'json');
	});
	
	$(".modal").on('click', 'a.up', function(event){
		event.preventDefault();
		var me = $(this);
		var href = me.attr('href');
		$.post(href, function(data){
			if(!data['error']){
				var metr = me.closest('tr');
				var prevtr = metr.prev();
				metr.remove();
				metr.insertBefore(prevtr);
				updateCarets(me.closest('table'));
			}
		}, 'json');
	});
	
	$(".modal").on('click', 'a.down', function(event){
		event.preventDefault();
		var me = $(this);
		var href = me.attr('href');
		$.post(href, function(data){
			if(!data['error']){
				var metr = me.closest('tr');
				var nexttr = metr.next();
				metr.remove();
				metr.insertAfter(nexttr);
				updateCarets(me.closest('table'));
			}
		}, 'json');
	});
	
	/* **************************** */
	/*   Mod of a model : actions   */
	/* **************************** */
	
	$("#model-container").on('click', '#new-action', function(){
		$("#action-title").html("Nouvelle action");
		$("#action-form").load(url+'/models/form?action=true&parentid='+$(this).data('parent'));
	});
	
	$("#model-container").on('click', '.mod-action', function(){
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
	
	/* ************************************ */
	/*  Fenêtre création/mod d'une action   */
	/* ************************************ */
	$("#action-container").on('click', 'input[type=submit]', function(event){
		event.preventDefault();
		var id = $("#action-form").find('input[name=id]').val();
		$.post(url+'/models/save', $(this).closest("#PredefinedEvent").serialize(), function(data){
			if(data.hasOwnProperty('name')){
				if(id > 0){ //modification d'une action existante
					var tr = $("tr#"+id);
					tr.find('td:eq(0)').html(data.name);
					tr.find('td:eq(1)').html('<span class="label label-'+data.impactstyle+'">'+data.impactname+'</span>');
					tr.find('td:eq(3) > a').data('name', data.name);
				} else { //nouvelle action
					var newtr = $('<tr id="'+data.id+'"></tr>');
					newtr.append('<td>'+data.name+'</td>');
					newtr.append('<td><span class="label label-'+data.impactstyle+'">'+data.impactname+'</span></td>');
					newtr.append('<td>'+
							'<a class="down" href="'+url+'/models/down?id='+data.id+'">'+
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
			}
			displayMessages(data.messages);
			$("#action-container").modal('hide');
		}, 'json');
	});
	

	/* Remplissage du champ visi en fonction de l'organisation */
	
	$("#model-container").on('change', 'select[name=organisation]', function(){
		fillZoneFilters($(this).val());
	});
	
	var fillZoneFilters = function(orgid){
		var select = $("#model-container select[name=zonefilters\\[\\]]");
		var options = select.prop('options');
		$('option', select).remove();
		$.post(url+'/models/getzonefilters?id='+orgid, function(data){
			$.each(data, function(key, value){
				options[options.length] = new Option(value, key);
			});
		}, 'json');
	};
	
	
};