/**
 * JS for interactions of the roles page
 */

var roles = function(url){
	$(".permission").on('change', function(event){
		event.preventDefault();
		var me = $(this);
		var state = me.is(':checked');
		if(!state){
			$.post(url + '/roles/removepermission?permission='+$(this).data('permission')+'&roleid='+$(this).data('roleid'), function(){
				me.attr('checked', true);
			});
		} else {
			$.post(url + '/roles/addpermission?permission='+$(this).data('permission')+'&roleid='+$(this).data('roleid'), function(){
				me.attr('checked', true);
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
	
	$("#role-container").on('click', 'input[type=submit]', function(event){
		event.preventDefault();
		$.post(url+'/roles/saverole', $("#Role").serialize(), function(data){
			location.reload();
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