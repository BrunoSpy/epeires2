/**
 * JS to be used with modal windows for editing models
 * Id to trigger "new model window" : #add-model with data-catid for prepositioning category
 * Id to modify a model : #mod-model with data-name and data-id
 * Id to delete a model : #delete-model
 */

var urlt;
var urla;

/* Ajout de fichiers */
var formAddFile = function(fileId, formData) {
    var tr = $('<tr id="file_' + fileId + '"></tr>');
    tr.append('<td>' + formData.reference + '</td>');
    tr.append('<td><a rel="external" href="' + urla + formData.path + '">' + formData.name + '</a></td>');
    tr.append('<td><a rel="external" href="' + urla + formData.path + '"><i class="icon-download"></i></a></td>')
    tr.append('<td><a href="#confirm-delete-file" class="delete-file" ' +
            'data-href="' + urlt + 'models/deletefile?id=' + fileId + '" ' +
            'data-id="' + fileId + '" ' +
            'data-name="' + formData.name + '" ' +
            'data-toggle="modal" ' +
            '><i class="icon-trash"></i></a></td>');
    $("#file-table").append(tr);
    var input = $('<input type="hidden" name="fichiers[' + fileId + ']" value="' + fileId + '"></input>');
    $("#model-files").append(input);
};

var models = function(url, urlapp){
	
        urlt = url;
        urla = urlapp;
        
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
	
        $(".model-listable").on('click', function(e){
            e.preventDefault();
            var me = $(this);
            $.post(url+'/models/listable?id='+$(this).data('modelid')+'&listable='+$(this).is(':checked'), function(data){
                me.prop('checked', data.listable);
                displayMessages(data.messages);
            });
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
				$.material.checkbox();
				$("#model-form").find(".pick-a-color").pickAColor();
			});	
		} else {
			$("#model-form").load(url+'/models/form?catid='+catid, function(){
				fillZoneFilters($("#model-container select[name=organisation]").val());
				$.material.checkbox();
				$("#model-form").find(".pick-a-color").pickAColor();
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
		$.post($("#delete-model-href").attr('href'), function(data){
                    if(!data['error']){
                        $('#models-table tr#model-'+me.data('id')).remove();
                    }
                    displayMessages(data);
                    
		});
	});
	
	/* **************************** */
	/*        Mod of a model        */
	/* **************************** */
	
	$(document).on('change', 'select[name=category]', function(){
		$(this).closest(".modal-body").find(".custom-fields").load(url+'/models/customfields?id='+$(this).val(), function(){
			$.material.checkbox();
			$("#model-form .custom-fields").find(".pick-a-color").pickAColor();
		});
	});
	$(document).on('click',".mod-model", function(){
		$("#model-title").html("Modification de <em>"+$(this).data('name')+"</em>");
		$("#model-form").load(url+'/models/form'+'?id='+$(this).data('id'), function(){
			//fillZoneFilters($("#model-container select[name=organisation]").val());
			$.material.checkbox();
			$("#model-form").find(".pick-a-color").pickAColor();
		});	
	});
		
	$("#model-container").on('submit', function(event){
		event.preventDefault();
		var catid = $("#PredefinedEvent select[name=category] option:selected").val();
		$.post($("#model-form #PredefinedEvent").attr('action')+'?catid='+catid, $("#model-form form#PredefinedEvent").serialize(), function(data){
			var id = $("#PredefinedEvent input[type=hidden]").val();
			var messages = data.messages;
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
							'> <span class="glyphicon glyphicon-pencil"></span></a>'+ 
							'<a '+
							'href="#confirm-delete-model" '+
							'data-toggle="modal" '+
							'title="Supprimer" '+ 
							'data-href="'+url+'/models/delete?id='+data.id+'&redirect=0" '+ 
							'class="delete-model" '+
							'data-id='+data.id+' '+
							'data-name="'+data.name+'" >'+ 
							' <span class="glyphicon glyphicon-trash"></span></a></td>');
				$("#models-container tbody").append(tr);
			}
			$("#model-container").modal('hide');
			//reload only if no other modal
			if(!$("#models-container").is(':visible') && (!messages.error || messages.error.length == 0)){
				location.reload();
			} else {
				displayMessages(messages);
			}
		}, 'json');
	});
	
	$("#model-container, #models-container").on('click', 'a.up', function(event){
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
	
	$("#model-container, #models-container").on('click', 'a.down', function(event){
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
		$("#action-form").load(url+'/models/form?action=true&parentid='+$(this).data('parent'), function(){
			$("#action-form").find(".pick-a-color").pickAColor({allowBlank: true});
		});
	});
	
	$("#model-container").on('click', '.mod-action', function(){
		$("#action-title").html("Modifier <em>"+$(this).data('name')+"</em>");
		$("#action-form").load(url+'/models/form?action=true&parentid='+$(this).data('parent')+'&id='+$(this).data('id'), function(){
			$("#action-form").find(".pick-a-color").pickAColor({allowBlank: true});
		});
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
	$("#action-container").on('submit', function(event){
		event.preventDefault();
		var id = $("#action-form").find('input[name=id]').val();
		$.post(url+'/models/save', $("#action-form form#PredefinedEvent").serialize(), function(data){
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
					newtr.append('<td>'+
						'<a '+
						'title="Modifier"'+ 
						'class="mod-action"'+
						'href="#action-container"'+
						'data-toggle="modal"'+
						'data-id='+data.id+' '+
						'data-name="'+data.name+'" '+
						'data-parent="'+data.parentid+'"> '+
						'<i class="icon-pencil"></i></a> <a title="Supprimer"'+ 
						'href="'+url+'/models/delete?id='+data.id+'&redirect=0" '+ 
						'class="action-delete" '+ 
						'data-id='+data.id +' '+ 
						'data-name="'+data.name+'" '+ 
						'><i class="icon-trash"></i></a></td>');
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
	
        $("#model-form").on('click', '.delete-file', function() {
            $("a#delete-file-href").attr('href', $(this).data("href"));
            $("#file_name").html($(this).data('name'));
            $("#delete-file-href").data('id', $(this).data('id'));
        });

        $("#confirm-delete-file").on('click', "#delete-file-href", function(event) {
            event.preventDefault();
            var me = $(this);
            $('#confirm-delete-file').modal('hide');
            $.post($("#delete-file-href").attr('href'), function(data) {
                $("#file-table").find('tr#file_' + me.data('id')).remove();
                $('#model-files input[name=fichiers\\[' + me.data('id') + '\\]]').remove();
                displayMessages(data);
            }, 'json');
        });
	
	/* ************************************ */
	/*    Fenêtre création d'une alerte     */
	/* ************************************ */
	
	$('#model-container').on('click', '#addalert', function(){
		$("#alert-form").load(url+'/models/formalarm');
        $("#add-alert h4").text("Ajout d'un mémo");
	});

	$("#model-container").on('click', '.mod-alert', function(e){
        $("#alert-form").load(url+'/models/formalarm?id='+$(this).data('id'));
        $("#add-alert h4").text("Modification d'un mémo");
    });

	$('#add-alert').on('submit', function(e){
		e.preventDefault();
		var me = $('#add-alert form');
        var id = $('#add-alert form input[name=id]').val();
		$.post(me.attr('action'), me.serialize(), function(data){
			if(!data.messages['error']){
				$('#add-alert').modal('hide');
                if(id > 0) {
                    $('#alerts #alarm-'+id).find('td:eq(0)').html(data.alarm.deltabegin+' min');
                    $('#alerts #alarm-'+id).find('td:eq(1)').html(data.alarm.deltaend+' min');
                    $('#alerts #alarm-'+id).find('td:eq(2)').html(data.alarm.name);
                } else {
                    var count = Math.floor($("#alerts input").length / 3);
                    var tr = $('<tr id=fake-"' + count + '"></tr>');
                    tr.append('<td>' + data.alarm.deltabegin + ' min</td>');
                    tr.append('<td>' + data.alarm.deltaend + ' min</td>');
                    tr.append('<td>' + data.alarm.name + '</td>');
                    tr.append('<td><a href="#" class="delete-fake-alarm"><i class="icon-trash"></i></a></td>');
                    var div = $('<div id="alarm-fake"' + count + '></div>');
                    div.append('<input type="hidden" name="alarm[' + count + '][delta]" value="' + data.alarm.delta + '"></input>');
                    div.append('<input type="hidden" name="alarm[' + count + '][name]" value="' + data.alarm.name + '"></input>');
                    div.append('<input type="hidden" name="alarm[' + count + '][comment]" value="' + data.alarm.comment + '"></input>');
                    div.append('<input type="hidden" name="alarm[' + count + '][deltabegin]" value="' + data.alarm.deltabegin + '"></input>');
                    div.append('<input type="hidden" name="alarm[' + count + '][deltaend]" value="' + data.alarm.deltaend + '"></input>');
                    $("#alerts").append(div);
                    $('#alerts tbody').append(tr);
                }
			}
			displayMessages(data.messages);
		});
	});
	
	$('#model-container').on('click', '.delete-fake-alarm', function(e){
		e.preventDefault();
		var me = $(this);
		var id = me.closest('tr').data('id');
		me.closest('tr').remove();
		$('#model-container #alarm-'+id).remove();
	});
	
	$('#model-container').on('click', '.delete-alarm', function(e){
		e.preventDefault();
		var me = $(this);
		$.post(url+'/models/deletealarm?id='+$(this).data('id'), function(data){
			if(!data['error']){
				me.closest('tr').remove();
			}
			displayMessages(data);
		});
	});

	$('#add-alert, #add-file, #action-container').on('hidden.bs.modal', function(e){
		$('body').addClass('modal-open');
	});

	$('#json-form').on('submit', function(e){
		e.preventDefault();
		var action = $(this).attr('action');
		$(this).ajaxSubmit({
			url: action,
			type: 'POST',
			success: function(data, textStatus, jqXGR) {
				$("#import-container").modal('hide');
				if (!data.messages['error']) {
					if(data.count == 0) {
						$("#import-result #count-import").html("Aucune fiche n'a été importée.");
					} else if(data.count == 1) {
						$("#import-result #count-import").html(data.count + " fiche a été correctement importée.");
					} else {
						$("#import-result #count-import").html(data.count + " fiches ont été correctement importées.");
					}

					if(data['missing'] && Object.keys(data.missing).length > 0) {
						let ul = $('<ul></ul>');
						data.missing.forEach(function(item, index){
							ul.append('<li>'+item+'</li>');
						});
						let missingcount = Object.keys(data.missing).length;
						$("#missing-import").empty();
						if(missingcount == 1) {
							$("#missing-import")
								.append(missingcount + " modèle non importé :")
								.append(ul);
						} else {
							$("#missing-import").append(missingcount + " modèles non importés :")
								.append(ul);
						}
					}
					$("#import-result").modal('show');
				}
				displayMessages(data.messages);

			}
		});
	});
};