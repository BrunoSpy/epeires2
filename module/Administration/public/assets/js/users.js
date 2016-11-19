/**
 * JS for user modal windows
 */

var users = function(url){
	
	$("#add-user").on('click', function(){
		$("#user-title").html("Nouvel utilisateur");
		$("#user-form").load(url+'/users/form');
	});
	
	$(".mod-user").on('click', function(){
		$("#user-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#user-form").load(url+'/users/form?userid='+$(this).data('id'));
	});
	
	$(".change-password").on('click', function(){
		$("#user-title").html('Modification mot de passe de <em>'+$(this).data('name')+'</em>');
		$("#user-form").load(url+'/users/changepasswordform?id='+$(this).data('id'));
	});
	
	$("#user-container").on('submit', '#changepassword', function(event){
		event.preventDefault();
		$.post(url+'/users/changepassword', $("#changepassword").serialize(), function(data){
			location.reload();
		}, 'json');
	});
	
	$("#user-container").on('submit', '#User', function(event){
		event.preventDefault();
		$.post(url+'/users/saveuser', $("#User").serialize(), function(data){
			location.reload();
		}, 'json');
	});
	
	$(".delete-user").on('click', function(event){
		$('a#delete-user-href').attr('href', $(this).data('href'));
		$('#user-name').html($(this).data('name'));
		$("#delete-user-href").data('id', $(this).data('id'));
	});
	
	$("#confirm-delete-user").on('click', '#delete-user-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-user").modal('hide');
		$.post(me.attr('href'), function(){
			location.reload();
		});
	});
	
	$("#user-container").on('change', 'select[name=organisation]', function(){
		$.getJSON(url+'/users/getqualifzone?id='+$(this).val(), function(data){
			var select = $("#user-container select[name=zone]");
			var options = select.prop('options');
			$('option', select).remove();
			options[options.length] = new Option("Facultatif", "");
			$.each(data, function(key, value){
				options[options.length] = new Option(value, key);
			});
		});
	});
	$("#user-container").arrive("#User", function() {
		$('#User input').on('keypress', function(){
			$("#User").validate({
				highlight: function(element) {
					$(element).closest('.form-group').addClass('has-error');
				},
				unhighlight: function(element) {
					$(element).closest('.form-group').removeClass('has-error');
				}
			});
		});
	});
};