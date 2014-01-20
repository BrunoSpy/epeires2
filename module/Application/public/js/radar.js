/**
 * @author Bruno Spyckerelle
 */

var radar = function(url){
	   
		//if true, switch the button to its previous state
		var back = true;
	
		//if true, show modal on switch change
		var modal = true;
		
	   $('.radar-switch').on('switch-change', function(e, data){
		   $('a#end-radar-href').attr('href', $(this).data('href')+"&state="+data.value);
		   $('#radar_name').html($(this).data('radar'));
		   $("#cancel-radar").data('radar', $(this).data('radarid')) ;
		   if(!data.value){
			   $("#confirm-end-event .modal-body").html("<p>Voulez-vous vraiment créer un nouvel évènement radar ?</p>"+
						"<p>L'heure actuelle sera utilisée comme heure de début.</p>");
		   } else {
			   $("#confirm-end-event .modal-body").html( "<p>Voulez-vous vraiment terminer l'évènement radar en cours ?</p>"+
				"<p>L'heure actuelle sera utilisée comme heure de fin.</p>");
		   }
		   if(modal){
			   $("#confirm-end-event").modal('show');
		   }
	   });
	
	   $("#confirm-end-event").on('hide', function(){
		 if(back){
			 modal = false;
			 $('#switch_'+$("#cancel-radar").data('radar')).bootstrapSwitch('toggleState');
			 modal = true;
		 }
	   });
	   
	   $("#end-radar-href").on('click', function(event){
		   event.preventDefault();
		   back = false;
		   $("#confirm-end-event").modal('hide');
		   $.post($("#end-radar-href").attr('href'), function(data){
			   displayMessages(data);
			   back = true;
			   if(data['error']){
				   //dans le doute, on remet le bouton à son état antérieur
				   modal = false;
				   $('#switch_'+$("#cancel-radar").data('radar')).bootstrapSwitch('toggleState');
				   modal = true;
			   }
		   }, 'json');
	   });
	   
	   //refresh page every 30s
	   (function doPoll(){
		   $.post(url+'radars/getradarstate')
	   			.done(function(data) {
	   				$.each(data, function(key, value){
	   					$('#switch_'+key).bootstrapSwitch('setState', value, true);
	   				});
	   			})
	   			.always(function() { setTimeout(doPoll, 30000);});
	   })();
};
