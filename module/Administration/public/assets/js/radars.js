/**
 * JS for user modal windows
 */

var radars = function(url){
	
	$("#add-radar").on('click', function(){
		$("#radar-title").html("Nouveau radar");
		$("#radar-form").load(url+'/radars/form', function(e){
		    $.material.checkbox();
		});
	});
	
	$(".mod-radar").on('click', function(){
		$("#radar-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#radar-form").load(url+'/radars/form?id='+$(this).data('id'), function(e){
		    $.material.checkbox();
		});
	});
	
	$("#radar-container").on('submit', function(event){
		event.preventDefault();
		$.post(url+'/radars/save', $("#Radar").serialize(), function(data){
			location.reload();
		}, 'json');
	});
	
	$(".delete-radar").on('click', function(event){
		$('a#delete-radar-href').attr('href', $(this).data('href'));
		$('#radar-name').html($(this).data('name'));
		$("#delete-radar-href").data('id', $(this).data('id'));
	});
	
	$("#confirm-delete-radar").on('click', '#delete-radar-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-radar").modal('hide');
		$.post(me.attr('href'), function(){
			location.reload();
		});
	});

	//Modèles associés
    $(".change-model").on('click', function(){
        $("#model-title").html('Modification de <em>'+$(this).data('name')+'</em>');
        $("#model-form").load(url+'/radars/formradarmodel?id='+$(this).data('id'));
    });

    $("#model-container").on('submit', function(event){
        event.preventDefault();
        $.post(url+'/radars/saveradarmodel', $("#model").serialize(), function(data){
            location.reload();
        });
    });
};