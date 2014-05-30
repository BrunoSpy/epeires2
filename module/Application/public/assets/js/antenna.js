/**
 * @author Bruno Spyckerelle
 */

var togglefiche = function(){
      
        if($('#fiche').is(':visible')){
            $("#fiche").animate({'margin-left':'-23%'}, '1000', function(){
                $(this).hide();
            });
            $("#frequencies").animate({
                'margin-left': '0px',
                'width': '73%'
            }, '1000');
        } else {
            $("#fiche").show(),
            $("#fiche").animate({'margin-left':'0px'}, '1000');
            $("#frequencies").animate({
                'margin-left': '2.56%',
                'width': '48.7%'
            }, '1000');
        }
    
    //without animation
//        if($('#fiche').is(':visible')){
//            $('#fiche').hide();
//           $('#frequencies').css('margin-left', '0px');
//            $('#frequencies').removeClass('span6').addClass('span9')
//        } else {
//            $('#fiche').show();
//            $('#frequencies').css('margin-left', '');
//            $('#frequencies').removeClass('span9').addClass('span6');
//        }

};

var closeFiche = function() {
    $("#fiche").animate({'margin-left': '-23%'}, '1000', function() {
        $(this).hide();
    });
    $("#frequencies").animate({
        'margin-left': '0px',
        'width': '73%'
    }, '1000');
};

var openFiche = function() {
    if(!$('#fiche').is(':visible')){
        $("#fiche").show(),
        $("#fiche").animate({'margin-left': '0px'}, '1000');
        $("#frequencies").animate({
            'margin-left': '2.56%',
            'width': '48.7%'
        }, '1000');
    }
};

var antenna = function(url){

	//if true, switch the button to its previous state
	var back = true;
	
	var currentantennas;
	var currentfrequencies;
	
        var timer;
        
        $(document).on('click', '.open-fiche', function(){
            openFiche();
            if($("#fiche").is(':visible')){
                $('#fiche').load(url+'frequencies/getfiche?id='+$(this).data('id'));
            } else {
                $("#fiche").empty();
            }
        });
        
        $("#fiche").on('click', "#close-panel", function(e){
            e.preventDefault();
            closeFiche();
        });
        
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
				updateActions();
			};
		}, 'json');
	});

        $(document).on('click', '.action-changefreq', function(event){
            var me = $(this);
            //close all popover
            $('a.actions-freq, a#changefreq').popover('hide');
            $.post(url+'frequencies/switchfrequency?fromid='+me.data('fromfreq')+'&toid='+me.data('tofreq'), function(data){
                displayMessages(data.messages);
                //force page refresh
                clearTimeout(timer);
                doPollFrequencies();
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
			displayMessages(data.messages);
			back = true;
			var switchbtn = $('#switch_'+$("#cancel-antenna").data('antenna'));
			if(data.messages['error']){
				//dans le doute, on remet le bouton à son état antérieur
				var state = switchbtn.bootstrapSwitch('status');
				switchbtn.bootstrapSwitch('setState', !state, true);
			} else {
				//mise à jour des fréquences
				var antenna = $('.antenna-color.antenna-'+$('#cancel-antenna').data('antenna'));
				if(switchbtn.bootstrapSwitch('status')){
					antenna.removeClass('background-status-fail')
					.addClass('background-status-ok');
					//changement de couv : antenne main opérationnelle
					antenna.filter('.mainantenna-color').addClass('background-selected')
						.siblings('.backupantenna-color').removeClass('background-selected');
                                        //suppression de la fiche reflexe si besoin
                                        $("#antennas #antenna-"+$('#cancel-antenna').data('antenna')+" td a").remove();
				} else {
					antenna.removeClass('background-status-ok')
					.addClass('background-status-fail');
					//changement de couv : antenne main en panne
					antenna.filter('.mainantenna-color').removeClass('background-selected')
					.siblings('.backupantenna-color').addClass('background-selected');
                                        $("#antennas #antenna-"+$('#cancel-antenna').data('antenna')+" td:first-child").append('<a href="#" class="open-fiche" data-id="'+$('#cancel-antenna').data('antenna')+'"> <i class="icon-tasks"></i></a>');
				}
				currentfrequencies = data.frequencies;
				updatefrequencies();
				updateActions();
			}
		}, 'json');
	});

	$(document).on('click', '.switch-antenna', function(){
		$("#switch_"+$(this).data('antennaid')).bootstrapSwitch('setState', $(this).data('state'));
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

	$(document).on('click', '#changefreq', function(event){
		event.preventDefault();
		var me = $(this);
		$.post(url+'frequencies/getfrequencies?id='+me.data('freqid'), function(data){
			var list = $("<ul id=\"list-change-freq-"+me.data('freqid')+"\"></ul>");
			$.each(data, function(key, value){
				list.append("<li><a href=\"#\" class=\"action-changefreq\" data-fromfreq=\""+me.data('freqid')+"\" data-tofreq=\""+key+"\">"+value+"</a></li>");
			});
			if(list.find('li').length > 0 ){
				var div = $('<div class="vertical-scroll"></div>');
				div.append(list);
				me.popover({
					content: div,
					html: true,
					container: '#popover-frequencies',
					trigger: 'manual'
				});
				me.popover('show');
				div.closest('.popover-content').css('padding', '0px');
			};
		}, 'json');
	});
	
	$(document).on('click', '.brouillage', function(e){
		$("#frequency_name").html($(this).data('freqname'));
		$('#form-brouillage').load(url+'frequencies/formbrouillage?id='+$(this).data('freqid'), function(){
                    $("input[name=startdate]").timepickerform({'id':'start', 'required':true});
                });
	});
	
	$('#form-brouillage').on('submit', function(e){
		e.preventDefault();
		$.post($("#form-brouillage form").attr('action'), $("#form-brouillage form").serialize(), function(data){
			if(!data.messages['error']){
				$("#fne-brouillage").modal('hide');
				window.open(url+'report/fnebrouillage?view=pdf&id='+data.eventid);
			}
			displayMessages(data);
		});
	});
	
	//actions
	var updateActions = function(){
		$("a.actions-freq").each(function(index, element){
			var sector = $(this).closest('.sector');
			var list = $("<ul></ul>");
			var mainantennacolor = sector.find('.antennas .mainantenna-color');
			var backupantennacolor = sector.find('.antennas .backupantenna-color');
			if(mainantennacolor.filter('.background-selected').length == mainantennacolor.length && backupantennacolor.filter('.background-status-ok').length == backupantennacolor.length){
				list.append("<li><a href=\"#\" class=\"switch-coverture\" data-cov=\"1\" data-freqid=\""+$(this).data('freq')+"\">Passer en couverture secours</a></li>");
			}
			if (backupantennacolor.filter('.background-selected').length == backupantennacolor.length && mainantennacolor.filter('.background-status-ok').length == mainantennacolor.length) {
				list.append("<li><a href=\"#\" class=\"switch-coverture\" data-cov=\"0\" data-freqid=\""+$(this).data('freq')+"\">Passer en couverture normale</a></li>");
			}
			var submenu = $("<li class=\"submenu\"></li>");
			submenu.append("<a id=\"changefreq\" data-freqid=\""+sector.data('freq')+"\" href=\#\>Changer de fréquence &nbsp;</a>");
			list.append(submenu);
			list.append("<li><a class=\"brouillage\" data-toggle=\"modal\" data-freqname=\""+$(this).html()+"\" data-freqid=\""+sector.data('freq')+"\" href=\"#fne-brouillage\">Brouillage</a></li>");
			list.append("<li class=\"divider\"></li>");
			//antennes
			var mainantenna = sector.find('.antennas .mainantenna-color.antenna-color:not(.antenna-climax-color)');
			if(mainantenna.hasClass('background-status-ok')){
				list.append("<li><a href=\"#\" class=\"switch-antenna\" data-state=\"false\" data-antennaid=\""+mainantenna.data('antennaid')+"\">Antenne principale HS</a></li>");
			} else {
				list.append("<li><a href=\"#\" class=\"switch-antenna\" data-state=\"true\" data-antennaid=\""+mainantenna.data('antennaid')+"\">Antenne principale OPE</a></li>");
			}
			var backupantenna = sector.find('.antennas .backupantenna-color.antenna-color:not(.antenna-climax-color)');
			if(backupantenna.hasClass('background-status-ok')){
				list.append("<li><a href=\"#\" class=\"switch-antenna\" data-state=\"false\" data-antennaid=\""+backupantenna.data('antennaid')+"\">Antenne secours HS</a></li>");
			} else {
				list.append("<li><a href=\"#\" class=\"switch-antenna\" data-state=\"true\" data-antennaid=\""+backupantenna.data('antennaid')+"\">Antenne secours OPE</a></li>");
			}
			//si climax
			var mainantennaclimax = sector.find('.antennas .mainantenna-color.antenna-climax-color');
			if(mainantennaclimax.length > 0){
				if(mainantennaclimax.hasClass('background-status-ok')){
					list.append("<li><a href=\"#\" class=\"switch-antenna\" data-state=\"false\" data-antennaid=\""+mainantennaclimax.data('antennaid')+"\">Antenne principale climaxée HS</a></li>");
				} else {
					list.append("<li><a href=\"#\" class=\"switch-antenna\" data-state=\"true\" data-antennaid=\""+mainantennaclimax.data('antennaid')+"\">Antenne principale climaxée OPE</a></li>");
				}
			}
			var backupantennaclimax = sector.find('.antennas .backupantenna-color.antenna-climax-color');
			if(backupantennaclimax.length > 0){
				if(backupantennaclimax.hasClass('background-status-ok')){
					list.append("<li><a href=\"#\" class=\"switch-antenna\" data-state=\"false\" data-antennaid=\""+backupantennaclimax.data('antennaid')+"\">Antenne secours climaxée HS</a></li>");
				} else {
					list.append("<li><a href=\"#\" class=\"switch-antenna\" data-state=\"true\" data-antennaid=\""+backupantennaclimax.data('antennaid')+"\">Antenne secours climaxée OPE</a></li>");
				}
			}
			if(list.find('li').length > 0 ){
				$(this).popover('destroy');
				$(this).popover({
					content: list,
					html: true,
					container: '#popover-frequencies',
					trigger: 'manual'
					});
				};
		});
		$("a.actions-antenna").each(function(){
			var antenna = $(this).closest('.antenna-color');
			var list = $('<ul></ul>');
			if(antenna.hasClass('background-status-ok')){
				list.append("<li><a href=\"#\" class=\"switch-antenna\" data-state=\"false\" data-antennaid=\""+antenna.data('antennaid')+"\">Antenne HS</a></li>");
			} else {
				list.append("<li><a href=\"#\" class=\"switch-antenna\" data-state=\"true\" data-antennaid=\""+antenna.data('antennaid')+"\">Antenne OPE</a></li>");
			}
			if(list.find('li').length > 0){
				$(this).popover('destroy');
				$(this).popover({
					content: list,
					html: true,
					container: '#popover-frequencies',
					trigger: 'manual'
				});
			}
		});
	};
	
	$('a.actions-antenna, a.actions-freq').on('click', function(event){
		event.preventDefault();
		$(this).popover('toggle');
	});
	
//	//hide popover if click outside
	$(document).mousedown(function(e){
		var container = $(".popover");
		if(!container.is(e.target) && container.has(e.target).length === 0){
			$("a.actions-freq, a.actions-antenna, a.actions-changefreq").not($(e.target)).popover('hide');
			$("a#changefreq").not($(e.target)).popover('hide');
		};		
	});
		
	var changeantenna = function(freqid, antennaSelector, newid){
		var sector = $('.sector-color.frequency-'+freqid);
		var antenna = sector.closest('.sector').find(antennaSelector);
		var currentid = antenna.data('antennaid');
		antenna.data('antennaid', newid);
		antenna.removeClass('antenna-'+currentid);
		antenna.addClass('antenna-'+newid);
		antenna.find('.actions-antenna').html($("table#antennas tr#antenna-"+newid).data('shortname'));
	};
	
	var createantenna = function(freqid, selector, newid){
		var sector = $('.sector-color.frequency-'+freqid);
		var div = $('<div></div>');
		div.addClass("background-status-ok "+selector+" antenna-color antenna-climax-color antenna-"+newid);
		div.data('antennaid', newid);
		var li = $('<li></li>');
		var a = $('<a></a>');
		a.addClass('actions-antenna');
		a.data('id', newid);
		a.html($("table#antennas tr#antenna-"+newid).data('shortname'));
		li.append(a);
		div.append(li);
		if(sector.closest('.sector').find('ul.antennas').length == 1) {
			var ul = $('<ul></ul>');
			if(selector == 'backupantenna-color') {
				var newdiv = $("<div></div>");
				newdiv.addClass('mainantenna-color antenna-color antenna-climax-color');
				ul.append(newdiv);
			}
			ul.addClass('antennas');
			ul.append(div);
			sector.closest('.sector').append(ul);
		} else {
			sector.closest('.sector').find('ul.antennas:last-child').append(div);
		}
	};
	
	var updateantennas = function(){
		$.each(currentantennas, function(key, value){
			$('#switch_'+key).bootstrapSwitch('setState', value.status, true);
                        var antennatd = $("#antenna-"+key+" td:first");
			var antenna = $('.antenna-color.antenna-'+key);
                        antennatd.find('a').remove();
			if(value.status){
                                
				antenna.removeClass('background-status-fail');
				antenna.addClass('background-status-ok');
				//evts planifiés ?
				if(value.planned){
                                        antennatd.append('<a href="#" class="open-fiche" data-id="'+key+'"> <i class="icon-tasks"></i></a>');
					antenna.removeClass('background-status-ok');
					antenna.addClass('background-status-planned');
				} else {
					antenna.removeClass('background-status-planned');
					antenna.addClass('background-status-ok');
				}
			} else {
                                antennatd.append('<a href="#" class="open-fiche" data-id="'+key+'"> <i class="icon-tasks"></i></a>');
				antenna.removeClass('background-status-ok');
				antenna.addClass('background-status-fail');
			}
		});
                //delay update if a popover is opened
                if($("body").find('.popover').length == 0) {
                    updateActions();
                } 
	}
	
	var updatefrequencies = function() {
		$.each(currentfrequencies, function(key, value){
			var sector = $('.sector-color.frequency-'+key);
			if(value.status == 1 && value.otherfreq == 0){
				sector.removeClass('background-status-fail');
				sector.addClass('background-status-ok');
			} else {
				sector.removeClass('background-status-ok');
				sector.addClass('background-status-fail');
			}
			if(value.cov == 1){ //principale = 0
				sector.closest('.sector').find('.mainantenna-color').removeClass('background-selected');
				sector.closest('.sector').find('.backupantenna-color').addClass('background-selected');
			} else {
				sector.closest('.sector').find('.backupantenna-color').removeClass('background-selected');
				sector.closest('.sector').find('.mainantenna-color').addClass('background-selected');
			}
			if(value.otherfreq != 0 && value.otherfreqid != sector.closest('.sector').data('freq')){
				//mise à jour des antennes si changement de fréquence
				//les couleurs sont mises à jour à l'apper de doPollAntenna juste ensuite
				sector.find('.actions-freq').html(value.otherfreq).addClass('em').data('freq',value.otherfreqid);
                                sector.find('.sector-name span').remove();
                                var name = sector.find('.sector-name').html();
                                sector.find('.sector-name').html(name+'<span> <i class="icon-forward"></i> '+value.otherfreqname);
				changeantenna(key, '.mainantenna-color.antenna-color:not(.antenna-climax-color)', value.main);
				changeantenna(key, '.backupantenna-color.antenna-color:not(.antenna-climax-color)', value.backup);
				if(value['mainclimax']){
					if(sector.closest('.sector').find('.mainantenna-color.antenna-climax-color').length > 0) {
						changeantenna(key, '.mainantenna-color.antenna-climax-color', value.mainclimax);
					} else {
						createantenna(key, 'mainantenna-color', value.mainclimax);
					}
				} else {
					sector.closest('.sector').find('.mainantenna-color.antenna-climax-color').remove();
				}
				if(value['backupclimax']) {
					if(sector.closest('.sector').find('.backupantenna-color.antenna-climax-color').length > 0) {
						changeantenna(key, '.backupantenna-climax.antenna-climax-color', value.backupclimax);
					} else {
						createantenna(key, 'backupantenna-color', value.backupclimax);
					}
				} else {
					sector.closest('.sector').find('.backupantenna-climax.antenna-climax-color').remove();
				}
			} else {
				sector.find('.actions-freq').html(value.name).removeClass('em');
                                sector.find('.sector-name span').remove();
			}
			if(value.status == 1){
				//prise en compte des évts planifiés uniquement si pas d'evt en cours => statut ok
				if(value.planned){
					sector.addClass('background-status-planned');
					sector.removeClass('background-status-ok');
				} else {
					sector.removeClass('background-status-planned');
					sector.addClass('background-status-ok');
				}
			}
		});
	}
	//refresh page every 30s
	function doPollAntenna(){
		$.post(url+'frequencies/getantennastate')
		.done(function(data) {
			currentantennas = data;
			updateantennas();
		});
	};

	function doPollFrequencies(){
		$.post(url+'frequencies/getfrequenciesstate')
		.done(function(data) {
			currentfrequencies = data;
			updatefrequencies();
		})
		.always(function() {
			doPollAntenna();
			timer = setTimeout(doPollFrequencies, 30000);
		});
	};
        
        //refresh page every 30s
        doPollFrequencies();
        

};
