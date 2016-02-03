var categories = function(url){
	var reload = false;
	var closesttr;

    var updateCarets = function(element, last) {
        var tbody = element.find('tbody');
        tbody.find('a.up').removeClass('disabled');
        tbody.find('a.down').removeClass('disabled');
        tbody.find('tr:first a.up').addClass('disabled');
        if ((typeof last !== 'undefined') && !last) {
            tbody.find('tr:last').prev().find('a.down').addClass('disabled');
        } else {
            tbody.find('tr:last a.down').addClass('disabled');
        }

    };
	
        $(".up-category").on('click', function(event){
            event.preventDefault();
            $.post(url+'/categories/upcategory?id='+$(this).data('id'), function(){
                location.reload();
            });
        });
        
        $(".down-category").on('click', function(event){
            event.preventDefault();
            $.post(url+'/categories/downcategory?id='+$(this).data('id'), function(){
                location.reload();
            });
        });
        
	/* ************************************ */
	/* *** Fenêtre de liste des modèles *** */
	/* ************************************ */
	

	$(".models-list").on('click', function(){
		$("#models-title").html("Modèles de "+$(this).data('name'));
		$("#models-table").load($(this).data('href'));
	});
	/* ************************************ */
	
	$(".mod").on('click', function(){
		$("#form-title").html("Modification de "+$(this).data('name'));
		$("#form").load(url+'/categories/form'+'?id='+$(this).data('id'),
                        function(){
                                $("#form").find(".pick-a-color").pickAColor();
                                $.material.checkbox();
                                $('#tcdiv').tooltip();
                                $('.form-control[name="timeline"]').change(function () {
                                        if (this.checked) {
                                                $('.form-control[name="timelineconfirmed"]').prop('disabled', false);
                                                $('#tcdiv').css({ opacity: 1 });
                                        } else {
                                                $('.form-control[name="timelineconfirmed"]').prop('disabled', true).attr('checked', false);
                                                $('#tcdiv').css({ opacity: 0.5 });
                                        }
                                });
                                if ($('.form-control[name="timeline"]').is(':checked')) {
                                        $('.form-control[name="timelineconfirmed"]').prop('disabled', false);
                                        $('#tcdiv').css({ opacity: 1 });
                                } else {
                                        $('.form-control[name="timelineconfirmed"]').prop('disabled', true).attr('checked', false);
                                        $('#tcdiv').css({ opacity: 0.5 });
                                }
                });
        });

	$("#add-cat").on('click', function(evt){
		$("#form-title").html("Nouvelle catégorie");
		$("#form").load(url+'/categories/form',
			function(){
				$("#form").find(".pick-a-color").pickAColor();
				$.material.checkbox();
			});	
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
	    	var me = $(this);
		$("#add-field").load(url+'/fields/form'+'?categoryid='+$(this).data('id'), function(){
                    me.closest('.modal-dialog').css('width', '1100px');
                });
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
		var dialog = me.closest('.modal-dialog');
		closesttr = me.closest('tr').html();
		me.closest('tr').load(url+'/fields/form'+'?id='+$(this).data('id'), function(){
                    dialog.css('width', '1100px');
                });
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
		$.post($(this).attr('href'), function(data){
			if(data['success']){
				closesttr.remove();
				closesttr = null;
				reload = true;
				updateCarets($("#fieldscontainer"));
			} 
			displayMessages(data);
		}, "json").fail(function(){
			var messages = '({error: ["Impossible de supprimer le champ."]})';
			displayMessages(eval(messages));
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
	$('#fieldscontainer').on('submit', function(event){
		event.preventDefault();
		href = $("#CustomField").attr('action');
		var me = $("#CustomField").find("input[type=submit]");
		$.post(href, $("#CustomField").serialize(), function(data){
			var id = me.closest('tr').find('input[type=hidden]').val();
			var tr = me.closest('tr');
			if(id>0){
				//modify
				tr.find('td:eq(0)').html(data.id);
				tr.find('td:eq(1)').html(data.name);
				tr.find('td:eq(2)').html(data.type);
                tr.find('td:eq(3)').html(data.multiple ? '<span class="glyphicon glyphicon-ok"></span>' : '<span class="glyphicon glyphicon-remove"></span>');
				tr.find('td:eq(4)').html(data.defaut);
                tr.find('td:eq(5)').html(data.help);
				tr.find('td:eq(6)').html(data.trace ? '<span class="glyphicon glyphicon-ok"></span>' : '<span class="glyphicon glyphicon-remove"></span>');
				tr.find('td:eq(7)').html('<a href="'+url+'/fields/fieldup?id='+data.id+'" class="up"><span class="up-caret middle"></span></a> '+
						'<a href="'+url+'/fields/fielddown?id='+data.id+'" class="down disabled"><span class="caret middle"></span></a>');
				tr.find('td:eq(8)').html('<a href="#" class="mod-field" data-id="'+data.id+'" data-name="'+data.name+'"><span class="glyphicon glyphicon-pencil"></span></a> '+
						'<a href="#confirm-delete-field" '+
						'data-href="'+url+'/fields/delete?id='+data.id+ 
							' class="delete-field" '+ 
							'data-id="'+data.id+'" '+ 
							'data-name="'+data.name+'" '+ 
							'data-toggle="modal"><span class="glyphicon glyphicon-trash"></span> </a>');					
				updateCarets($("#fieldscontainer"), false);
			} else {
				var tr = me.closest('tr');
				var newhtml = $("<tr></tr>");
				newhtml.append('<td>'+data.id+'</td>');
				newhtml.append('<td>'+data.name+'</td>');
				newhtml.append('<td>'+data.type+'</td>');
                newhtml.append('<td>'+(data.multiple ? '<span class="glyphicon glyphicon-ok"></span>' : '<span class="glyphicon glyphicon-remove"></span>')+'</td>');
				newhtml.append('<td>'+data.defaut+'</td>');
                newhtml.append('<td>'+data.help+'</td>');
				newhtml.append('<td>'+(data.trace ? '<span class="glyphicon glyphicon-ok"></span>' : '<span class="glyphicon glyphicon-remove"></span>')+'</td>');
				newhtml.append('<td>'+'<a href="'+url+'/fields/fieldup?id='+data.id+'" class="up"><span class="up-caret middle"></span></a> '+
						'<a href="'+url+'/fields/fielddown?id='+data.id+'" class="down disabled"><span class="caret middle"></span></a></td>');
				newhtml.append('<td>'+'<a href="#" class="mod-field" data-id="'+data.id+'" data-name="'+data.name+'"><span class="glyphicon glyphicon-pencil"></span></a> '+
						'<a href="#confirm-delete-field" '+
						'data-href="'+url+'/fields/delete?id='+data.id+ 
							' class="delete-field" '+ 
							'data-id="'+data.id+'" '+ 
							'data-name="'+data.name+'" '+ 
							'data-toggle="modal"><span class="glyphicon glyphicon-trash"></span> </a></td>');

				newhtml.insertBefore(tr);
				tr.html('');
				updateCarets($("#fieldscontainer"), false);
			}
			reload = true;
		}, 'json');
                $("#fieldscontainer").css('left','');
		$('#new-field').removeClass('disabled');
	});
        
        $("#freq-select").on('change', function(){
            $.post(url+'/categories/changedefaultfrequency?id='+$(this).val(), function(data){
                    displayMessages(data);
                });
        });
        
        $("#radar-select").on('change', function(){
            $.post(url+'/categories/changedefaultradar?id='+$(this).val(), function(data){
                    displayMessages(data);
                });
        });
        
        $("#antenna-select").on('change', function(){
            $.post(url+'/categories/changedefaultantenna?id='+$(this).val(), function(data){
                    displayMessages(data);
                });
        });  
	
        $("#brouillage-select").on('change', function(){
            $.post(url+'/categories/changedefaultbrouillage?id='+$(this).val(), function(data){
                    displayMessages(data);
                });
        });
}