/**
 * JS for centre modal windows
 */

var centre = function(url){
	
	/* **************************** */
	/*         Organisations        */
	/* **************************** */
	$("#add-organisation").on('click', function(){
		$("#organisation-title").html("Nouvelle organisation");
		$("#organisation-form").load(url+'/centre/formorganisation');
	});
	
	$(".mod-organisation").on('click', function(){
		$("#organisation-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#organisation-form").load(url+'/centre/formorganisation?id='+$(this).data('id'));
	});
	
	$("#organisation-container").on('submit', function(event){
		event.preventDefault();
		$.post(url+'/centre/saveorganisation', $("#Organisation").serialize(), function(data){
			if(data['messages']){
				displayMessages(data.messages);
			}
			if(data['success']){
				location.reload();
			}
		}, 'json').fail(function(){
			var messages = '({error: ["Impossible d\'enregistrer l\'organisation."]})';
			displayMessages(eval(messages));
		});
	});
	
	$(".delete-organisation").on('click', function(event){
		$('a#delete-organisation-href').attr('href', $(this).data('href'));
		$('#organisation-name').html($(this).data('name'));
		$("#delete-organisation-href").data('id', $(this).data('id'));
	});
	
	$("#confirm-delete-organisation").on('click', '#delete-organisation-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-organisation").modal('hide');
		$.post(me.attr('href'), function(){
			location.reload();
		}).fail(function(){
			var messages = '({error: ["Impossible de supprimer l\'organisation."]})';
			displayMessages(eval(messages));
		});
	});
	
	/* **************************** */
	/*        Zone de qualif        */
	/* **************************** */
	$("#add-qualif").on('click', function(){
		$("#qualif-title").html("Nouvelle zone de qualification");
		$("#qualif-form").load(url+'/centre/formqualif');
	});
	
	$(".mod-qualif").on('click', function(){
		$("#qualif-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#qualif-form").load(url+'/centre/formqualif?id='+$(this).data('id'));
	});
	
	$("#qualif-container").on('submit', function(event){
		event.preventDefault();
		$.post(url+'/centre/savequalif', $("#QualificationZone").serialize(), function(data){
			location.reload();
		}, 'json').fail(function(){
			var messages = '({error: ["Impossible d\'enregistrer la zone de qualification."]})';
			displayMessages(eval(messages));
		});
	});
	
	$(".delete-qualif").on('click', function(event){
		$('a#delete-qualif-href').attr('href', $(this).data('href'));
		$('#qualif-name').html($(this).data('name'));
		$("#delete-qualif-href").data('id', $(this).data('id'));
	});
	
	$("#confirm-delete-qualif").on('click', '#delete-qualif-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-qualif").modal('hide');
		$.post(me.attr('href'), function(){
			location.reload();
		}).fail(function(){
			var messages = '({error: ["Impossible de supprimer la zone de qualification."]})';
			displayMessages(eval(messages));
		});
	});
	
	/* **************************** */
	/*      Groupe de secteurs      */
	/* **************************** */
	$("#add-group").on('click', function(){
		$("#group-title").html("Nouveau groupe de secteurs");
		$("#group-form").load(url+'/centre/formgroup', function(e){
            $("#sectors").sortable();
            $("#sectors li").draggable({
                connectToSortable: "#avalaiblesectors, #sectors"
            });
            $("#avalaiblesectors").sortable();
            $("#avalaiblesectors li").draggable({
                connectToSortable: "#sectors, #avalaiblesectors"
            });
        });
	});
	
	$(".mod-group").on('click', function(){
		$("#group-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#group-form").load(url+'/centre/formgroup?id='+$(this).data('id'), function(e){
            $("#sectors").sortable();
            $("#sectors li").draggable({
                connectToSortable: "#avalaiblesectors, #sectors"
            });
            $("#avalaiblesectors").sortable();
            $("#avalaiblesectors li").draggable({
                connectToSortable: "#sectors, #avalaiblesectors"
            });
        });
	});
	
	$("#group-container").on('change', 'select[name=zone]', function(){
		$.getJSON(url+'/centre/getsectors?zone='+$(this).val(), function(data){
			$.each(data, function(key, value){
				$("#avalaiblesectors").append('<li class="list-group-item shadow-z-1" id="sectors_'+key+'">'+value+'</li>');
			});
            $("#sectors").sortable();
            $("#sectors li").draggable({
                connectToSortable: "#avalaiblesectors, #sectors"
            });
            $("#avalaiblesectors").sortable();
            $("#avalaiblesectors li").draggable({
                connectToSortable: "#sectors, #avalaiblesectors"
            });
		});
	});
	
	$("#group-container").on('submit', function(event){
		event.preventDefault();
		var postData = $("#SectorGroup").serialize()+'&' + $("#sectors").sortable("serialize");
		$.post(url+'/centre/savegroup', postData, function(data){
			location.reload();
		}, 'json');
	});
	
	$(".delete-group").on('click', function(event){
		$('a#delete-group-href').attr('href', $(this).data('href'));
		$('#group-name').html($(this).data('name'));
		$("#delete-group-href").data('id', $(this).data('id'));
	});
	
	$("#confirm-delete-group").on('click', '#delete-group-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-group").modal('hide');
		$.post(me.attr('href'), function(){
			location.reload();
		});
	});
	
	/* **************************** */
	/*            Secteurs          */
	/* **************************** */
	$("#add-sector").on('click', function(){
		$("#sector-title").html("Nouveau  secteur");
		$("#sector-form").load(url+'/centre/formsector');
	});
	
	$(".mod-sector").on('click', function(){
		$("#sector-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#sector-form").load(url+'/centre/formsector?id='+$(this).data('id'), function(e){
            $.material.checkbox();
        });
	});

	$("#sector-container").on('change', 'select[name=zone]', function(){
		$.getJSON(url+'/centre/getgroups?zone='+$(this).val(), function(data){
			var select = $("#sector-container select[name=sectorsgroups\\[\\]]");
			var options = select.prop('options');
			$('option', select).remove();
			$.each(data, function(key, value){
				options[options.length] = new Option(value, key);
			});
			if(data['messages']){
				displayMessages(data['messages']);
			}
		});
	});
	
	$("#sector-container").on('submit', function(event){
		event.preventDefault();
		$.post(url+'/centre/savesector', $("#Sector").serialize(), function(data){
			location.reload();
		}, 'json');
	});
	
	$(".delete-sector").on('click', function(event){
		$('a#delete-sector-href').attr('href', $(this).data('href'));
		$('#sector-name').html($(this).data('name'));
		$("#delete-sector-href").data('id', $(this).data('id'));
	});
	
	$("#confirm-delete-sector").on('click', '#delete-sector-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-sector").modal('hide');
		$.post(me.attr('href'), function(){
			location.reload();
		});
	});
	
	/* **************************** */
	/*            Attentes          */
	/* **************************** */
	$("#add-stack").on('click', function(){
		$("#stack-title").html("Nouvelle attente");
		$("#stack-form").load(url+'/centre/formstack');
	});
	
	$(".mod-stack").on('click', function(){
		$("#stack-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#stack-form").load(url+'/centre/formstack?id='+$(this).data('id'), function(e){
            $.material.checkbox();
		});
	});
	
	$("#stack-container").on('submit', function(event){
		event.preventDefault();
		$.post(url+'/centre/savestack', $("#Stack").serialize(), function(data){
			location.reload();
		}, 'json');
	});
	
	$(".delete-stack").on('click', function(event){
		$('a#delete-stack-href').attr('href', $(this).data('href'));
		$('#stack-name').html($(this).data('name'));
		$("#delete-stack-href").data('id', $(this).data('id'));
	});
	
	$("#confirm-delete-stack").on('click', '#delete-stack-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-stack").modal('hide');
		$.post(me.attr('href'), function(){
			location.reload();
		});
	});
};