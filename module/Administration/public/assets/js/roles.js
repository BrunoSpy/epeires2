/**
 * JS for interactions of the roles page
 */

var roles = function(url){
	$(".permission").on('change', function(event){
		event.preventDefault();
		var me = $(this);
		var state = me.is(':checked');
		if(!state){
			$.post(url + '/roles/removepermission?permission='+$(this).data('permission')+'&roleid='+$(this).data('roleid'), function(data){
				if(data['error']){
                                    me.prop('checked', true);
                                    displayMessages(data);
                                } else {
                                    location.reload();
                                }
                                
			});
		} else {
			$.post(url + '/roles/addpermission?permission='+$(this).data('permission')+'&roleid='+$(this).data('roleid'), function(data){
				if(data['error']){
                                    me.prop('checked', false);
                                    displayMessages(data);
                                } else {
                                    location.reload();
                                }
			});
		}
	});
	
	
	$("#add-role").on('click', function(){
		$("#role-title").html("Nouveau rôle");
		$("#role-form").load(url+'/roles/form');
	});
	
	$(".mod-role").on('click', function(){
		$("#role-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#role-form").load(url+'/roles/form?id='+$(this).data('id'));
	});
	
	$("#role-container").on('submit', function(event){
		event.preventDefault();
		$.post(url+'/roles/saverole', $("#Role").serialize(), function(data){
                    if(data['error']){
                        displayMessages(data);
                    } else {
                        location.reload();
                    }
		}, 'json');
	});
	
	$(".delete-role").on('click', function(event){
		$('a#delete-role-href').attr('href', $(this).data('href'));
		$('#role-name').html($(this).data('name'));
		$("#delete-role-href").data('id', $(this).data('id'));
	});
	
	$("#confirm-delete-role").on('click', '#delete-role-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-role").modal('hide');
		$.post(me.attr('href'), function(){
			location.reload();
		});
	});
	
};