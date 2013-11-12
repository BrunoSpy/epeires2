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
};