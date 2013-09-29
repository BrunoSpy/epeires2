/**
 * JS for interactions of the radio page
 */

var radio = function(url){
	/* **************************** */
	/*         Antennes        */
	/* **************************** */
	$("#add-antenna").on('click', function(){
		$("#antenna-title").html("Nouvelle antenne");
		$("#antenna-form").load(url+'/radio/formantenna');
	});
	
	$(".mod-antenna").on('click', function(){
		$("#antenna-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#antenna-form").load(url+'/radio/formantenna?id='+$(this).data('id'));
	});
	
	$("#antenna-container").on('click', 'input[type=submit]', function(event){
		event.preventDefault();
		$.post(url+'/radio/saveantenna', $("#Antenna").serialize(), function(data){
			location.reload();
		}, 'json');
	});
	
	$(".delete-antenna").on('click', function(event){
		$('a#delete-antenna-href').attr('href', $(this).data('href'));
		$('#antenna-name').html($(this).data('name'));
		$("#delete-antenna-href").data('id', $(this).data('id'));
	});
	
	$("#confirm-delete-antenna").on('click', '#delete-antenna-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-antenna").modal('hide');
		$.post(me.attr('href'), function(){
			location.reload();
		});
	});
	
	/* **************************** */
	/*         Fréquences        */
	/* **************************** */
	$("#add-frequency").on('click', function(){
		$("#frequency-title").html("Nouvelle fréquence");
		$("#frequency-form").load(url+'/radio/formfrequency');
	});
	
	$(".mod-frequency").on('click', function(){
		$("#frequency-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#frequency-form").load(url+'/radio/formfrequency?id='+$(this).data('id'));
	});
	
	$("#frequency-container").on('click', 'input[type=submit]', function(event){
		event.preventDefault();
		$.post(url+'/radio/savefrequency', $("#Frequency").serialize(), function(data){
			location.reload();
		}, 'json');
	});
	
	$(".delete-frequency").on('click', function(event){
		$('a#delete-antenna-href').attr('href', $(this).data('href'));
		$('#antenna-name').html($(this).data('name'));
		$("#delete-antenna-href").data('id', $(this).data('id'));
	});
	
	$("#confirm-delete-frequency").on('click', '#delete-frequency-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-frequency").modal('hide');
		$.post(me.attr('href'), function(){
			location.reload();
		});
	});
};