/**
 * JS for IPO modal windows
 */

var ipos = function(url){
	
	$("#add-ipo").on('click', function(){
		$("#ipo-title").html("Nouvel IPO");
		$("#ipo-form").load(url+'/ipos/form');
	});
	
	$(".mod-ipo").on('click', function(){
		$("#ipo-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#ipo-form").load(url+'/ipos/form?ipoid='+$(this).data('id'));
	});
	
	
	$("#ipo-container").on('click', '#IPO input[type=submit]', function(event){
		event.preventDefault();
		$.post(url+'/ipos/saveipo', $("#IPO").serialize(), function(data){
			location.reload();
		}, 'json');
	});
	
	$(".delete-ipo").on('click', function(event){
		$('a#delete-ipo-href').attr('href', $(this).data('href'));
		$('#ipo-name').html($(this).data('name'));
		$("#delete-ipo-href").data('id', $(this).data('id'));
	});
	
	$("#confirm-delete-ipo").on('click', '#delete-ipo-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#confirm-delete-ipo").modal('hide');
		$.post(me.attr('href'), function(){
			location.reload();
		});
	});
	
};