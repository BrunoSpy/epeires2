/**
 * @author Bruno Spyckerelle
 */

var antenna = function(url){
	   
		//if true, switch the button to its previous state
		var back = true;
	
		//if true, show modal on switch change
		var modal = true;
		
	   $('.antenna-switch').on('switch-change', function(e, data){
		   $('a#end-antenna-href').attr('href', $(this).data('href')+"&state="+data.value);
		   $('#antenna_name').html($(this).data('antenna'));
		   $("#cancel-antenna").data('antenna', $(this).data('antennaid')) ;
		   if(!data.value){
			   $("#confirm-end-event .modal-body").html("<p>Voulez-vous vraiment créer un nouvel évènement antenne ?</p>"+
						"<p>L'heure actuelle sera utilisée comme heure de début.</p>");
		   } else {
			   $("#confirm-end-event .modal-body").html( "<p>Voulez-vous vraiment terminer l'évènement antenne en cours ?</p>"+
				"<p>L'heure actuelle sera utilisée comme heure de fin.</p>");
		   }
		   if(modal){
			   $("#confirm-end-event").modal('show');
		   }
	   });
	
	   $("#confirm-end-event").on('hide', function(){
		 if(back){
			 modal = false;
			 $('#switch_'+$("#cancel-antenna").data('antenna')).bootstrapSwitch('toggleState');
			 modal = true;
		 }
	   });
	   
	   $("#end-antenna-href").on('click', function(event){
		   event.preventDefault();
		   back = false;
		   $("#confirm-end-event").modal('hide');
		   $.post($("#end-antenna-href").attr('href'), function(data){
			   displayMessages(data);
			   back = true;
			   if(data['error']){
				   //dans le doute, on remet le bouton à son état antérieur
				   modal = false;
				   $('#switch_'+$("#cancel-antenna").data('antenna')).bootstrapSwitch('toggleState');
				   modal = true;
			   }
		   }, 'json');
	   });
	   
	   //refresh page every 30s
	   (function doPoll(){
		   $.post(url+'frequencies/getantennastate')
	   			.done(function(data) {
	   				$.each(data, function(key, value){
	   					$('#switch_'+key).bootstrapSwitch('setState', value, true);
	   				});
	   			})
	   			.always(function() { setTimeout(doPoll, 30000);});
	   })();
};
