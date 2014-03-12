/**
 * JS for user modal windows
 */

var radars = function(url){
	
	$("#add-radar").on('click', function(){
		$("#radar-title").html("Nouveau radar");
		$("#radar-form").load(url+'/radars/form');
	});
	
	$(".mod-radar").on('click', function(){
		$("#radar-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#radar-form").load(url+'/radars/form?id='+$(this).data('id'));
	});
	
	$("#radar-container").on('click', 'input[type=submit]', function(event){
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
	
};