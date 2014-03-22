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
	
	$("#antenna-container").on('submit', function(event){
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
	
	$("#frequency-container").on('submit', function(event){
		event.preventDefault();
		$.post(url+'/radio/savefrequency', $("#Frequency").serialize(), function(data){
			location.reload();
		}, 'json');
	});
	
	$(".delete-frequency").on('click', function(event){
		$('a#delete-frequency-href').attr('href', $(this).data('href'));
		$('#frequency-name').html($(this).data('name'));
		$("a#delete-frequency-href").data('freqid', $(this).data('id'));
	});
	
	$("#confirm-delete-frequency").on('click', '#delete-frequency-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-frequency").modal('hide');
		$.post(me.attr('href'), function(data){
			if(!data['error']){
				$("#frequency_"+me.data('freqid')).remove();
			}
			displayMessages(data);
		});
	});
	
	$("#frequency-container").on('change', 'select[name=defaultsector]', function(){
		var option = $('select[name=defaultsector] option:selected');
		if(option.val() != ''){
			$("input[name=othername]").val(option.text());
		}
	});
	
	/* **************************** */
	/*       Page Fréquences        */
	/* **************************** */
	
	var updateCarets = function(element){
		var tbody = element.find('tbody');
		tbody.find('a.groupup').removeClass('disabled');
		tbody.find('a.groupdown').removeClass('disabled');
		tbody.find('tr:first a.groupup').addClass('disabled');
		tbody.find('tr:last a.groupdown').addClass('disabled');
		
	};
	
	$(document).on('click', '.groupup', function(event){
		event.preventDefault();
		var me = $(this);
		var href = me.attr('href');
		$.post(href, function(data){
			//si message d'erreur : ne rien faire
			if(!data['error']) {
				var metr = me.closest('tr');
				var prevtr = metr.prev();
				metr.remove();
				metr.insertBefore(prevtr);
				updateCarets(me.closest('table'));
			}
			displayMessages(data);
		}, 'json');
	});
	
	$(document).on('click',".groupdown", function(event){
		event.preventDefault();
		var me = $(this);
		var href = me.attr('href');
		$.post(href, function(data){
			//si message d'erreur : ne rien faire
			if(!data['error']) {
				var metr = me.closest('tr');
				var nexttr = metr.next();
				metr.remove();
				metr.insertAfter(nexttr);
				updateCarets(me.closest('table'));
			}
			displayMessages(data);
		}, 'json');
	});
	
	$(document).on('switch-change', ".group-switch", function(event){
		event.preventDefault();
		var me = $(this);
		$.post(me.data('href'), function(data){
			if(data['error']){
				me.bootstrapSwitch('setState', !me.bootstrapSwitch('status'), true);
			} 
			displayMessages(data);
		}, 'json');
	});
        
        $(".change-model").on('click', function(){
		$("#model-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#model-form").load(url+'/radio/formantennamodel?id='+$(this).data('id'));
	});
        
        $("#model-container").on('submit', function(event){
            event.preventDefault();
            $.post(url+'/radio/saveantennamodel', $("#model").serialize(), function(data){
                location.reload();
            });
        });
};