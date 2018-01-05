/**
 * JS for Op Sup modal windows
 */

var opsups = function(url){
	
	$("#add-opsup").on('click', function(){
		$("#opsup-title").html("Nouveau Op Sup");
		$("#opsup-form").load(url+'/op-sups/form');
	});
	
	$(".mod-opsup").on('click', function(){
		$("#opsup-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#opsup-form").load(url+'/op-sups/form?opsupid='+$(this).data('id'), function(e){
            $.material.checkbox();
		});
	});
	
	$("#opsup-container").on('submit', function(event){
		event.preventDefault();
		$.post(url+'/op-sups/saveopsup', $("#OperationalSupervisor").serialize(), function(data){
			location.reload();
		}, 'json');
	});
	
	$(".delete-opsup").on('click', function(event){
		$('a#delete-opsup-href').attr('href', $(this).data('href'));
		$('#opsup-name').html($(this).data('name'));
		$("#delete-opsup-href").data('id', $(this).data('id'));
	});
	
	$("#confirm-delete-opsup").on('click', '#delete-opsup-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-opsup").modal('hide');
		$.post(me.attr('href'), function(){
			location.reload();
		});
	});
	
	$("#opsup-container").on('change', 'select[name=organisation]', function(){
		$.getJSON(url+'/op-sups/getqualifzone?id='+$(this).val(), function(data){
			var select = $("#opsup-container select[name=zone]");
			var options = select.prop('options');
			$('option', select).remove();
			$.each(data, function(key, value){
				options[options.length] = new Option(value, key);
			});
		});
	});

    $(".archive-opsup").on('click', function(event){
        $('a#archive-opsup-href').attr('href', $(this).data('href'));
        $('#opsup-name').html($(this).data('name'));
        $("#archive-opsup-href").data('id', $(this).data('id'));
    });

    $("#confirm-archive-opsup").on('click', '#archive-opsup-href', function(event){
        event.preventDefault();
        var me = $(this);
        $("#confirm-archive-opsup").modal('hide');
        $.post(me.attr('href'), function(){
            location.reload();
        });
    });

	$("#add-opsuptype").on('click', function(){
		$("#opsuptype-title").html("Nouveau type Op Sup");
		$("#opsuptype-form").load(url+'/op-sups/formtype');
	});

	$(".mod-opsuptype").on('click', function(){
		$("#opsuptype-title").html('Modification du type <em>'+$(this).data('name')+'</em>');
		$("#opsuptype-form").load(url+'/op-sups/formtype?opsuptypeid='+$(this).data('id'));
	});

	$("#opsuptype-container").on('submit', function(event){
		event.preventDefault();
		$.post(url+'/op-sups/saveopsuptype', $("#OpSupType").serialize(), function(data){
			location.reload();
		}, 'json');
	});

	$(".delete-opsuptype").on('click', function(event){
		$('a#delete-opsuptype-href').attr('href', $(this).data('href'));
		$('#opsuptype-name').html($(this).data('name'));
		$("#delete-opsuptype-href").data('id', $(this).data('id'));
	});

	$("#confirm-delete-opsuptype").on('click', '#delete-opsuptype-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-opsuptype").modal('hide');
		$.post(me.attr('href'), function(){
			location.reload();
		});
	});



	/**** Shift Hour ****/
	$("#add-shifthour").on('click', function(){
		$("#shifthour-title").html("Nouvelle heure de relève");
		$("#shifthour-form").load(url+'/op-sups/formshifthour', function(){
			$("#shifthour-form input[name=hour]").bootstrapMaterialDatePicker({
                format: 'HH:mm',
                date: false,
                hour: true,
                switchOnClick: true,
                lang: 'fr',
                cancelText: "Annuler"
            });
		});
	});

	$(".mod-shifthour").on('click', function(){
		$("#shifthour-title").html('Modification de l\'heure de relève');
		$("#shifthour-form").load(url+'/op-sups/formshifthour?shifthourid='+$(this).data('id'), function() {
            $("#shifthour-form input[name=hour]").bootstrapMaterialDatePicker({
                format: 'HH:mm',
                date: false,
                hour: true,
                switchOnClick: true,
                lang: 'fr',
                cancelText: "Annuler"
            });
        });
	});

	$("#shifthour-container").on('submit', function(event){
		event.preventDefault();
		$.post(url+'/op-sups/saveshifthour', $("#ShiftHour").serialize(), function(data){
			location.reload();
		}, 'json');
	});

	$(".delete-shifthour").on('click', function(event){
		$('a#delete-shifthour-href').attr('href', $(this).data('href'));
		$("#delete-shifthour-href").data('id', $(this).data('id'));
	});

	$("#confirm-delete-shifthour").on('click', '#delete-shifthour-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-shifthour").modal('hide');
		$.post(me.attr('href'), function(){
			location.reload();
		});
	});

};