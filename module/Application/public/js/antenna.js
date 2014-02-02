/**
 * @author Bruno Spyckerelle
 */

var antenna = function(url){

	//if true, switch the button to its previous state
	var back = true;

	$(document).on('click', '.switch-antenna', function(){
		var state = $("#switch_"+$(this).data('antenna')).bootstrapSwitch('status');
		$("#switch_"+$(this).data('antenna')).bootstrapSwitch('setState', !state);
	});
	
	$(document).on('click','.switch-coverture', function(){
		var me = $(this);
		$.post(url+'frequencies/switchcoverture?frequencyid='+me.data("freqid")+'&cov='+me.data('cov'), function(data){
			displayMessages(data);
			if(!data['error']){
				var frequency = $('.frequency-'+me.data('freqid'));
				var antennas = frequency.siblings('.antennas');
				if(me.data('cov') == '0'){
					antennas.find('.mainantenna-color').addClass('background-selected');
					antennas.find('.backupantenna-color').removeClass('background-selected');
					frequency.addClass('background-status-ok');
					frequency.removeClass('background-status-fail');
				} else {
					frequency.addClass('background-status-fail');
					frequency.removeClass('background-status-ok');
					antennas.find('.backupantenna-color').addClass('background-selected');
					antennas.find('.mainantenna-color').removeClass('background-selected');
				}
			};
		}, 'json');
	});

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
		$("#confirm-end-event").modal('show');
	});

	$("#confirm-end-event").on('hide', function(){
		if(back){
			var switchAntenna = $('#switch_'+$("#cancel-antenna").data('antenna'));
			var state = switchAntenna.bootstrapSwitch('status');
			switchAntenna.bootstrapSwitch('setState', !state, true);
		}
	});

	$("#end-antenna-href").on('click', function(event){
		event.preventDefault();
		back = false;
		$("#confirm-end-event").modal('hide');
		$.post($("#end-antenna-href").attr('href'), function(data){
			displayMessages(data);
			back = true;
			var switchbtn = $('#switch_'+$("#cancel-antenna").data('antenna'));
			if(data['error']){
				//dans le doute, on remet le bouton à son état antérieur
				var state = switchbtn.bootstrapSwitch('status');
				switchbtn.bootstrapSwitch('setState', !state, true);
			} else {
				//mise à jour des fréquences
				var antenna = $('.antenna-color.antenna-'+$('#cancel-antenna').data('antenna'));
				if(switchbtn.bootstrapSwitch('status')){
					antenna.removeClass('background-status-fail')
					.addClass('background-status-ok');
					antenna.closest('.sector').find('.sector-color').removeClass('background-status-fail')
					.addClass('background-status-ok');
					if(antenna.hasClass('backupantenna-color')){
						//antenne backup de retour : rien à faire
					} else if(antenna.hasClass('mainantenna-color')){
						//antenne main de retour : on reprend la couverture normale
						antenna.siblings('.backupantenna-color').removeClass('background-selected');
						antenna.addClass('background-selected');
					}
				} else {
					antenna.removeClass('background-status-ok')
					.addClass('background-status-fail');
					antenna.closest('.sector').find('.sector-color').removeClass('background-status-ok')
					.addClass('background-status-fail');
					if(antenna.hasClass('mainantenna-color')){
						//antenne main en panne
						antenna.removeClass('background-selected');
						antenna.siblings('.backupantenna-color').addClass('background-selected');
					}
				}
			}
		}, 'json');
	});

	$("#antennas tr").hover(
			//in
			function(){
				$('.antenna-'+$(this).data('id')).closest('.sector').addClass('background-status-test');
			},
			//out
			function(){
				$('.antenna-'+$(this).data('id')).closest('.sector').removeClass('background-status-test'); 
			});

	//refresh page every 30s
	(function doPollAntenna(){
		$.post(url+'frequencies/getantennastate')
		.done(function(data) {
			$.each(data, function(key, value){
				$('#switch_'+key).bootstrapSwitch('setState', value, true);
				if(value){
					$('.antenna-color.antenna-'+key).removeClass('background-status-fail');
					$('.antenna-color.antenna-'+key).addClass('background-status-ok');
				} else {
					$('.antenna-color.antenna-'+key).removeClass('background-status-ok');
					$('.antenna-color.antenna-'+key).addClass('background-status-fail');
				}
			});
		})
		.always(function() { setTimeout(doPollAntenna, 30000);});
	})();

	(function doPollFrequencies(){
		$.post(url+'frequencies/getfrequenciesstate')
		.done(function(data) {
			$.each(data, function(key, value){
				var sector = $('.sector-color.frequency-'+key);
				if(value.status){
					sector.removeClass('background-status-fail');
					sector.addClass('background-status-ok');
				} else {
					sector.removeClass('background-status-ok');
					sector.addClass('background-status-fail');
				}
				if(value.cov){ //principale = 0
					sector.closest('.sector').find('.mainantenna-color').removeClass('background-selected');
					sector.closest('.sector').find('.backupantenna-color').addClass('background-selected');
				} else {
					sector.closest('.sector').find('.backupantenna-color').removeClass('background-selected');
					sector.closest('.sector').find('.mainantenna-color').addClass('background-selected');
				}
			});
		})
		.always(function() { setTimeout(doPollFrequencies, 30000);});
	})();
};
