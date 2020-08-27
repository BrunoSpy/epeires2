/**
 * JS for user modal windows
 */

var switchobjects = function(url){
	
	$(".add-object").on('click', function(){
		$("#object-title").html("Nouvel objet");
		let type = $(this).data('type');
		$("#object-form").load(url+'/switchobjects/form?type='+type, function(e){
		    $.material.checkbox();
		    $('#SwitchObject input[name="type"]').val(type);
		});
	});
	
	$(".mod-object").on('click', function(){
		$("#object-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#object-form").load(url+'/switchobjects/form?id='+$(this).data('id')+'&type='+$(this).data('type'), function(e){
		    $.material.checkbox();
		});
	});
	
	$("#object-container").on('submit', function(event){
		event.preventDefault();
		$.post(url+'/switchobjects/save', $("#SwitchObject").serialize(), function(data){
			location.reload();
		}, 'json');
	});
	
	$(".delete-object").on('click', function(event){
		$('a#delete-object-href').attr('href', $(this).data('href'));
		$('.object-name').html($(this).data('name'));
		$("#delete-object-href").data('id', $(this).data('id'));
	});

	$(".decommission-object").on('click', function(e){
        $('a#decommission-object-href').attr('href', $(this).data('href'));
        $('.object-name').html($(this).data('name'));
        $("#decommission-object-href").data('id', $(this).data('id'));
	});

    $("#confirm-decommission-object").on('click', '#decommission-object-href', function(event){
        event.preventDefault();
        var me = $(this);
        $("#confirm-decommission-object").modal('hide');
        $.post(me.attr('href'), function(){
            location.reload();
        });
    });

	$("#confirm-delete-object").on('click', '#delete-object-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-object").modal('hide');
		$.post(me.attr('href'), function(){
			location.reload();
		});
	});

	//Modèles associés
    $(".change-model").on('click', function(){
        $("#model-title").html('Modification de <em>'+$(this).data('name')+'</em>');
        $("#model-form").load(url+'/switchobjects/formobjectmodel?id='+$(this).data('id'));
    });

    $("#model-container").on('submit', function(event){
        event.preventDefault();
        $.post(url+'/switchobjects/saveobjectmodel', $("#model").serialize(), function(data){
            location.reload();
        });
    });

    $(".mod-category").on('click', function(e){
    	$("#category-title").html('Modification des objets de <em>'+$(this).data('name')+'</em>');
    	$("#category-form").load(url+'/switchobjects/formcategory?id='+$(this).data('id'), function(e){
    		$("#objects").sortable();
    		$("#objects li").draggable({
				connectToSortable: "#availableobjects, #objects"
			});
    		$("#availableobjects").sortable();
    		$("#availableobjects li").draggable({
				connectToSortable: "#objects, #availableobjects"
			});
		});
	});

    $("#category-container").on('submit', function(e){
    	e.preventDefault();
    	let postData = $("#Category").serialize()+'&'+$("#objects").sortable("serialize");
    	$.post(url+'/switchobjects/savecategory', postData, function(data){
    		location.reload();
		}, 'json');
	});
};