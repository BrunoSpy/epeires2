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
		$("#opsup-form").load(url+'/op-sups/form?opsupid='+$(this).data('id'));
	});
	
	
	$("#opsup-container").on('click', '#OperationalSupervisor input[type=submit]', function(event){
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
};