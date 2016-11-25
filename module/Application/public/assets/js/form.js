/*
 *  This file is part of Epeires².
 *  Epeires² is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  Epeires² is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with Epeires².  If not, see <http://www.gnu.org/licenses/>.
 *
 */

var urlt;

var formAddFile = function(fileId, formData, modifiable){
	modifiable = (typeof modifiable === "undefined") ? true : modifiable;
	var tr = $('<tr id="file_'+fileId+'"></tr>');
	tr.append('<td>'+formData.reference+'</td>');
	tr.append('<td><a rel="external" href="'+urlt.slice(0, -1)+formData.path+'">'+formData.name+'</a></td>');
	tr.append('<td><a rel="external" href="'+urlt.slice(0, -1)+formData.path+'"><span class="glyphicon glyphicon-download"></span></a></td>');
	if(modifiable){
		tr.append('<td><a href="#confirm-delete-file" class="delete-file" '+
			'data-href="'+urlt+'events/deletefile?id='+fileId+'" '+
			'data-id="'+fileId+'" '+
			'data-name="'+formData.name+'" '+
			'data-toggle="modal" '+
			'><span class="glyphicon glyphicon-trash"></span></a></td>');
	}
	$("#file-table tbody").append(tr);
	var input = $('<input type="hidden" name="fichiers['+fileId+']" value="'+fileId+'"></input>');
	$("#files-tab").append(input);
}

/**
 *
 * @param {type} alarm
 * @param {boolean} alter if true, then alarm can be altered according to change of start date
 * @returns {undefined}
 */
var formAddAlarm = function(alarm, alter) {
	alter = typeof alter !== 'undefined' ? alter : false;

	var d = computeDateAlarm(alarm);
	var count = Math.floor($("#memos-tab input").length / 5);
	//ajouter ligne dans le tableau
	var tr = $('<tr '+(alter ? 'class="fake-alarm" id="tr-fake-'+count+'"' : '')+' data-id="fake-'+count+'"></tr>');
	tr.data('deltabegin', alarm.deltabegin);
	if(alarm.deltaend) {
		tr.data('deltaend', alarm.deltaend);
	}
	//si date est déjà passée : warning
	var now = new Date();
	if(now - d > 0) {
		tr.append('<td><span class="glyphicon glyphicon-warning-sign"></span></td>');
	} else {
		tr.append('<td><span class="glyphicon glyphicon-bell"></span></td>');
	}
	if(d === -1){ //unable to compute alarm time at this point
		tr.append("<td>TBC</td>");
	} else {
		tr.append("<td>"+FormatNumberLength(d.getUTCHours(), 2)+":"+FormatNumberLength(d.getUTCMinutes(), 2)+"</td>");
	}
	tr.append('<td>'+alarm.name+'</td>');
	tr.append('<td><a class="delete-fake-alarm" href="#"><span class="glyphicon glyphicon-trash"></span></a></td>');
	$('#alarm-table').append(tr);
	//ajouter fieldset caché
	var div = $('<div '+(alter ? 'class="fake-alarm"' : '')+' id="alarm-fake-'+count+'" data-alarm="fake-'+count+'"></div>');
	if(d === -1) {
		//alarm creation needs a date, even if it is inaccurate
		d = new Date();
	}
	var datestring = d.getUTCDate()+"-"+(d.getUTCMonth()+1)+"-"+d.getUTCFullYear();
	var timestring = FormatNumberLength(d.getUTCHours(), 2)+":"+FormatNumberLength(d.getUTCMinutes(), 2);
	div.append('<input type="hidden" name="alarm['+count+'][date]" value="'+datestring+" "+timestring+'"></input>');
	div.append('<input type="hidden" name="alarm['+count+'][name]" value="'+alarm.name+'"></input>');
	div.append('<input type="hidden" name="alarm['+count+'][comment]" value="'+alarm.comment+'"></input>');
	div.append('<input type="hidden" name="alarm['+count+'][deltabegin]" value="'+alarm.deltabegin+'"></input>');
	div.append('<input type="hidden" name="alarm['+count+'][deltaend]" value="'+(alarm.deltaend ? alarm.deltaend : '')+'"></input>');
	$('#memos-tab').append(div);
};

var convertInputIntoDate = function(input){
	var split = input.split(' ');
	var daysplit = split[0].split('-');
	var hoursplit = split[1].split(':');
	return new Date(Date.UTC(daysplit[2], daysplit[1]-1, daysplit[0], hoursplit[0], hoursplit[1]));
};

var computeDateAlarm = function(alarm){
	var end = $('#event input[name=enddate]').val();
	var start = $('#event input[name=startdate]').val();
	var deltaend = parseInt(alarm.deltaend);
	var deltabegin = parseInt(alarm.deltabegin);
	if(!isNaN(deltaend)){
		if(end !== ""){
			var d = convertInputIntoDate(end);
			return new Date(d.getTime()+(deltaend*60*1000));
		} else {
			return -1;
		}
	} else if(!isNaN(deltabegin)) {
		var d = convertInputIntoDate(start);
		return new Date(d.getTime()+(deltabegin*60*1000));
	} else {
		return new Date(alarm.datetime);
	}
};

/**
 * Mettre à jour l'affichage du tableau d'alarme en fonction des
 * heures de début et de fin de l'evt.
 * La mise à jour effective de la date des alarmes est faite à l'enregistrement
 * de l'évènement.
 */
var updateAlarmForms = function(){
	var end = $("#event input[name=enddate]").val();
	var start = $("#event input[name=startdate]").val();
	$('#alarm-table tr').each(function(){
		var deltaend = parseInt($(this).data('deltaend'));
		var deltabegin = parseInt($(this).data('deltabegin'));
		var newdate;
		if(!isNaN(deltaend)){
			if(end !== ""){
				var d = convertInputIntoDate(end);
				newdate = new Date(d.getTime() + (deltaend*60*1000));
			}
		} else if(!isNaN(deltabegin)){
			var d = convertInputIntoDate(start);
			newdate = new Date(d.getTime()+(deltabegin*60*1000));
		}
		if(typeof(newdate) !== 'undefined'){
			$(this).find('td:nth-child(2)').text(FormatNumberLength(newdate.getUTCHours(),2)+':'+FormatNumberLength(newdate.getUTCMinutes(),2));
		}
	});

}

var formModifyAlarm = function(alarm) {
	var d = computeDateAlarm(alarm);
	//ajouter ligne dans le tableau
	var tr = $('<tr id="tr-'+alarm.id+'" data-id="'+alarm.id+'"></tr>');
	tr.data('deltabegin', alarm.deltabegin);
	tr.data('deltaend', alarm.deltaend);
	//si date est déjà passée : warning
	var now = new Date();
	if(now - d > 0) {
		tr.append('<td><span class="glyphicon glyphicon-warning-sign"></span></td>');
	} else {
		tr.append('<td><span class="glyphicon glyphicon-bell"></span></td>');
	}
	if(d == -1){
		tr.append("<td>TBC</td>");
	} else {
		tr.append("<td>"+FormatNumberLength(d.getUTCHours(), 2)+":"+FormatNumberLength(d.getUTCMinutes(), 2)+"</td>");
	}
	tr.append('<td>'+alarm.name+'</td>');
	tr.append('<td><a href="#add-alarm" data-toggle="modal" class="modify-alarm"><span class="glyphicon glyphicon-pencil"></span></a> <a class="delete-alarm" href="#"><span class="glyphicon glyphicon-trash"></span></a></td>');
	$('#alarm-table tr#tr-'+alarm.id).remove();
	$('#alarm-table').append(tr);
};

var form = function(url, tabid){

	urlt = url;

	/**
	 *
	 * @param newevt 0 : nouvel évènement, 1 : modification d'un evt, 2 : copie d'un evt ou intanciation modèle via recherche
	 */
	var initTabs = function(newevt){
		switch (newevt) {
			case 0:
				//création d'un nouvel évènement vide ou à partir d'un modèle
				$('#notes-title').hide();
				$('#Event .nav-tabs > li').css('width', (100/6)+'%');
				$("#event input[name='submit']").prop('disabled', true).addClass('disabled');
				break;
			case 1:
				//modification d'un evt
				$('#notes-title').show();
				$('#Event .nav-tabs > li').css('width', (100/7)+'%');
				$('#Event .nav-tabs > li > a').removeClass('disabled');
				$('#description-title > a').trigger('click');
				break;
			case 2:
				//copie d'un evt ou utilisatio modèle via recherche
				$('#notes-title').hide();
				$('#Event .nav-tabs > li').css('width', (100/6)+'%');
				$('#Event .nav-tabs > li > a').removeClass('disabled');
				$('#hours-title > a').trigger('click');
				break;
		}
		//dans tous les cas, réactiver les js material
		$.material.checkbox();
	};

	var updateIconTabs = function() {

		$('#Event .nav-tabs > li').each(function(index){
			$(this).find('div.round')
				.removeClass('blue').addClass('grey')
			var icon = $(this).find('div.round > span.glyphicon');
			icon.removeClass();
			icon.addClass('glyphicon glyphicon-' + icon.data('class'));
		});

		if($('#categories-tab .form-group.has-error').length > 0){
			$('#cat-title').addClass('invalid').removeClass('valid');
		} else {
			$('#cat-title').addClass('valid').removeClass('invalid');
		}

		if($('#hours-tab .form-group.has-error').length > 0){
			$('#hours-title').addClass('invalid').removeClass('valid');
		} else {
			$('#hours-title').addClass('valid').removeClass('invalid');
		}

		if($('#description-tab .form-group.has-error').length > 0){
			$('#description-title').addClass('invalid').removeClass('valid');
		} else {
			$('#description-title').addClass('valid').removeClass('invalid');
		}

		$('#Event .nav-tabs > li.valid > a:not(.disabled) > div.round')
			.removeClass('grey blue orange').addClass('green');

		$('#Event .nav-tabs > li.active > a:not(.disabled) > div.round')
			.removeClass('grey green orange')
			.addClass('blue');

		$('#Event .nav-tabs > li.invalid > a:not(.disabled) > div.round')
			.removeClass('grey blue green').addClass('orange')
			.find('span.glyphicon')
			.removeClass()
			.addClass('glyphicon glyphicon-warning-sign');
	};

	$('#event').arrive('#memos-tab tr', function(){
		var count = $('#memos-tab tr').length;
		$('#memos-title span.badge').html(count);
	});

	$('#event').arrive('#files-tab tr', function(){
		var count = $('#files-tab tr[id]').length;
		$("#files-title span.badge").html(count);
	});

	$('#event').arrive('#actions-tab tr', function(){
		var count = $('#actions-tab tr').length;
		$("#actions-title span.badge").html(count);
	});

	$('#event').arrive('#notes-tab blockquote', function(){
		var count =  $('#notes-tab blockquote').length;
		$('#notes-title span.badge').html(count);
	});

	//gestion des tabs
	$('#event').on('shown.bs.tab', 'a[data-toggle="tab"]', function (event) {
		updateIconTabs();
	});

    var updateFormError = function() {
        var fail = false;
        $("#event input, #event select, #event textarea").each(function(index){
            if($(this).prop('required') && !$(this).val()) {
                fail = true;
                $(this).parents('.form-group').addClass('has-error');
            } else {
                $(this).parents('.form-group').removeClass('has-error');
            }
        });
        if(fail) {
            $("#event input[name='submit']").prop('disabled', true).addClass('disabled');
        } else {
            $("#event input[name='submit']").prop('disabled', false).removeClass('disabled');
        }
        updateIconTabs();
    };

	//enable/disable submit button according to required fields
	$("#event").on('change keyup', function(e){
        updateFormError();
	});

	//specific functions to maintain coherence between end and start inputs
	$('#event').on('change', 'input[name=startdate]', function(){
		var datefin = $("#hours-tab #end").siblings('input[type=hidden]');
		var dateDeb = $("#hours-tab #start").siblings('input[type=hidden]');
		var startsplit = dateDeb.val().split(' ');
		var daysplit = startsplit[0].split('-');
		var hourstartsplit = startsplit[1].split(':');
		var deb = new Date(daysplit[2], daysplit[1]-1, daysplit[0], hourstartsplit[0], hourstartsplit[1]);
        //if duration is set, set end date according to it
        if($("input[name=enddate]").data('duration') > 0){
            var duration = $("input[name=enddate]").data('duration');
            var d = new Date(deb.getTime() + 60*60000);
            var datestring = d.getDate()+"-"+(d.getMonth()+1)+"-"+d.getFullYear();
            var timestring = FormatNumberLength(d.getHours(), 2)+":"+FormatNumberLength(d.getMinutes(), 2);
            datefin.val(datestring + " " + timestring);
            datefin.trigger('change');
            updateHours();
        } else {
            //check if start_date > end_date, if end_date is set
            if(datefin.val()){
                var endsplit = datefin.val().split(' ');
                var enddaysplit = endsplit[0].split('-');
                var hoursplit = endsplit[1].split(':');
                var end = new Date(enddaysplit[2], enddaysplit[1]-1, enddaysplit[0], hoursplit[0], hoursplit[1]);
                //if deb > end, block startdate
                if(deb > end){
                    dateDeb.val(datefin.val());
                    datefin.trigger('change');
                    updateHours();
                }
            }
        }
		var now = new Date();
		var nowUTC = new Date(now.getTime() + now.getTimezoneOffset()*60000);
		var diff = (deb - nowUTC)/60000; //différence en minutes
		//change status if needed, authorized and beginning near actual time
		if($('#event form').data('confirmauto')
			&& $("#event select[name=status] option:selected").val() == '1'
			&& (diff < 10)){
			$("#event select[name=status] option[value=2]").prop('selected', true);
		}
		//mise à jour des alarmes
		updateAlarmForms();
	});

	$('#event').on('change', 'input[name=enddate]', function() {
		var dateDeb = $("#hours-tab #start").siblings("input[type=hidden]");
		var dateFin = $("#hours-tab #end").siblings("input[type=hidden]");
		if (dateFin.val()) {
			//check if end_date < start_date
			var endsplit = dateFin.val().split(' ');
			var daysplit = endsplit[0].split('-');
			var hourendsplit = endsplit[1].split(':');
			var startsplit = dateDeb.val().split(' ');
			var startdaysplit = startsplit[0].split('-');
			var hoursplit = startsplit[1].split(':');
			var end = new Date(daysplit[2], daysplit[1] - 1, daysplit[0], hourendsplit[0], hourendsplit[1]);
			var deb = new Date(startdaysplit[2], startdaysplit[1] - 1, startdaysplit[0], hoursplit[0], hoursplit[1]);
			//if deb > end
			//first : try to just change the date
			//if not enough, take date and time
			if (deb > end) {
				var newEnd = new Date(startdaysplit[2], startdaysplit[1] - 1, startdaysplit[0], hourendsplit[0], hourendsplit[1]);
				if(deb > newEnd){
					dateFin.val(dateDeb.val());
				} else {
					dateFin.val(startsplit[0]+" "+endsplit[1]);
				}
				updateHours();
			}
			//changement du statut à terminé si :
			//   * droits ok
			//et * modif d'un evt
			//et * (heure de début passée ou statut confirmé) et heure de fin < now+15
			var now = new Date();
			var nowUTC = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
			var nowUTCplus = new Date(nowUTC.getTime() + 15 * 60000);
			if ($('#event form').data('confirmauto')
				&& $('#event input[name=id]').val() > 0 //id != 0 => modif
				&& (deb < nowUTC || $('#event select[name=status] option:selected').val() == '2')
				&& end < nowUTCplus) {
				$('#event select[name=status] option[value=3]').prop('selected', true);
			} else if($('#event form').data('confirmauto')
				&& $('#event input[name=id]').val() == 0){
				//en cas de création on se permet le changement de statut
				if(deb < nowUTC && end < nowUTCplus){
					$('#event select[name=status] option[value=3]').prop('selected', true);
				} else {
					$('#event select[name=status] option[value=2]').prop('selected', true);
				}
			}
			//mise à jour des alarmes
			updateAlarmForms();
		}
		//updateHourTitle();

	});

	var updateHours = function(){
		//initialize datetime pickers
		//start date
		var start = $("#hours-tab #start").siblings("input[type=hidden]").val();
		if(start){
			var daysplit = start.split(' ');
			var hoursplit = daysplit[1].split(':');
			$("#start .day input").val(daysplit[0]);
			$("#start .hour input").val(hoursplit[0]);
			$("#start .minute input").val(hoursplit[1]);
		} else {
			var d = new Date();
			if($('#calendar').is(':visible')){
				//init start date with date from calendar
				var calday = $('#calendar input').val().split('/');
				$("#start .day input").val(calday[0]+"-"+calday[1]+"-"+calday[2]);
			} else {
				$("#start .day input").val(d.getUTCDate()+"-"+(d.getUTCMonth()+1)+"-"+d.getUTCFullYear());
			}
			var hour = ""+d.getUTCHours();
			if(d.getUTCHours() >= 0 && d.getUTCHours() <= 9){
				hour = "0"+d.getUTCHours();
			}
			$("#start .hour input").val(hour);
			var minute = ""+d.getUTCMinutes();
			if(d.getUTCMinutes()>=0 && d.getUTCMinutes()<=9){
				minute = "0"+d.getUTCMinutes();
			}
			$("#start .minute input").val(minute);
			$("#start .minute input").trigger('change');
		}
        var end = $("#hours-tab #end").siblings("input[type=hidden]").val();
        if($("input[name=enddate]").data('duration') > 0) {
            var deb = convertInputIntoDate($("#hours-tab #start").siblings("input[type=hidden]").val());
            var duration = $("input[name=enddate]").data('duration');
            var d = new Date(deb.getTime() + duration*60000);
            var datestring = d.getUTCDate()+"-"+(d.getUTCMonth()+1)+"-"+d.getUTCFullYear();
            var timestring = FormatNumberLength(d.getUTCHours(), 2)+":"+FormatNumberLength(d.getUTCMinutes(), 2);
            end = datestring + " " + timestring;
			$("input[name=enddate]").val(end);
			$("input[name=enddate]").data('duration', 0);
        }
		if(end){
			var daysplit = end.split(' ');
			var hoursplit = daysplit[1].split(':');
			$("#end .day input").val(daysplit[0]);
			$("#end .hour input").val(hoursplit[0]);
			$("#end .minute input").val(hoursplit[1]);
		}
	};

	//change end input constraint when input date change
    $("#event").on('change', "#start input", function(e){
        var startdate = $("#start input.date").val()
        $('#end input.date')
            .bootstrapMaterialDatePicker('setMinDate', startdate);

    });


	/************************/

	//submit form
	$("#event").on('submit', function(event){
		event.preventDefault();
		$("#event input[type=submit]").tooltip('destroy');
		//disable submit button to prevent double submission
		$("#event input[name='submit']").prop('disabled', true).addClass('disabled');
		//fill missing minute inputs
		if($('#start .hour input').val().length > 0 && $('#start .minute input').val().length === 0){
			$('#start .minute input').val('00').trigger('change');
		}
		if($('#end .hour input').val().length > 0 && $('#end .minute input').val().length === 0){
			$('#end .minute input').val('00').trigger('change');
		}
		var formData = new FormData($("#Event")[0]);
		$.ajax({
			type: "POST",
			url: url+'events/save',
			xhr: function() {  // Custom XMLHttpRequest
				var myXhr = $.ajaxSettings.xhr();
				if(myXhr.upload){ // Check if upload property exists
					//    myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
				}
				return myXhr;
			},
			// Form data
			data: formData,
			//Options to tell jQuery not to process data or worry about content-type.
			cache: false,
			contentType: false,
			processData: false,
			success: function(data){
				//close form
				$("#create-link").trigger("click");
				//update timeline
				if(data['events']){
					$('#timeline').timeline('addEvents', data.events);
					$('#timeline').timeline('forceUpdateView');
					$('#calendarview').fullCalendar('refetchEvents');
				}
				displayMessages(data.messages);
			},
			dataType: "json"
		});
	});

	$("#event").on("click", "#cancel-form", function(event){
		event.preventDefault();
		$("#create-evt").modal('hide');
		restoreUpdateAlarms();
	});

	var cat_id = -1;
	var cat_parent_id = -1;
	$("#create-link").on("click", function(){
		if($("#create-evt").is(':visible')){
			$("#create-evt").modal('hide')
			restoreUpdateAlarms();
		} else {
			$("#event").html('');
			$("#form-title").html("Nouvel évènement");
			$("#event").load(
				url+'events/form'+'?tabid='+tabid,
				function(){
					initTabs(0);
					$("#event input[name=startdate]").timepickerform({'id':'start'});
					$("#event input[name=enddate]").timepickerform({'id':'end', 'clearable':true});
					updateHours();
					//updateHourTitle();
					if(cat_parent_id >= 0){
						$("#root_categories").val(cat_parent_id);
						$('#root_categories').trigger('change');
					} else {
						//pas de parent : cat_parent_id === -1
						if(cat_id >= 0) {
							$("#root_categories").val(cat_id);
							$('#root_categories').trigger('change');
						}
					}
				}
			);
			$("#create-evt").modal('show');
			pauseUpdateAlarms();
		}
	});

	//clic sur utiliser un modèle
	$("#search-results").on("click", ".use-model", function(){
		var me = $(this);
		$("#search-results").slideUp('fast');
		$("#event").html('');
		$("#form-title").html(me.data('name'));
		$("#create-evt").modal('show');
		$("#event").load(url+'events/form?id='+me.data('id')+'&model=1&tabid='+tabid, function(){
			initTabs(2);
			$("#event input[name=startdate]").timepickerform({'id':'start'});
			$("#event input[name=enddate]").timepickerform({'id':'end', 'clearable':true});
			updateHours();
		});
		$("#search-results").offset({top:0});
		pauseUpdateAlarms();
	});

	//clic sur copie d'un évènement
	$("#search-results").on("click", ".copy-event", function(e){
		var me = $(this);
		$("#search-results").slideUp('fast');
		$("#form-title").html(me.data('name'));
		$("#create-evt").modal('show');

		$("#event").load(url+'events/form?id='+me.data('id')+'&copy=1&tabid='+tabid, function(){
			initTabs(2);
			$("#event input[name=startdate]").timepickerform({'id':'start'});
			$("#event input[name=enddate]").timepickerform({'id':'end', 'clearable':true});
			updateHours();
		});
		$("#search-results").offset({top:0});
		pauseUpdateAlarms();
	});

	//click sur modification d'un évènement
	$(document).on("click", "#timeline a.modify-evt, #search-results a.modify-evt, #calendarview a.modify-evt", function(e){
		e.preventDefault();
		var me = $(this);
        if(me.data('recurr') == true) {
            $("#confirm-recurr").modal('show');
            $("#confirm-recurr").data('id', me.data('id'));
            $("#confirm-recurr").data('name', me.data('name'));
        } else {
            loadForm(me.data('id'), me.data('name'), false);
        }
	});

    $("#confirm-recurr #confirm-modify-series").on('click', function(){
        $("#confirm-recurr").modal('hide');
        var id = $("#confirm-recurr").data('id');
        var name = $("#confirm-recurr").data('name');
        loadForm(id, name, false);
    });
    
    $("#confirm-recurr #confirm-modify-one").on('click', function(){
        $("#confirm-recurr").modal('hide');
        var id = $("#confirm-recurr").data('id');
        var name = $("#confirm-recurr").data('name');
        loadForm(id, name, true);
    });
    
    var loadForm = function(id, name, exclude) {
        $("#form-title").html(name);

        $("#create-evt").modal('show');

        $("#event").load(url+'events/form?id='+id+'&tabid='+tabid, function(){
            initTabs(1);
            $("#event input[name=exclude]").val(exclude);
            $("#event input[name=startdate]").timepickerform({'id':'start'});
            $("#event input[name=enddate]").timepickerform({'id':'end', 'clearable':true});
            //mise à jour en fonction du statut ponctuel
            $('#event #punctual').trigger('change');
            updateHours();
            //updateHourTitle();
            pauseUpdateAlarms();
            $('tr[data-toggle=tooltip]').tooltip();

        });
    };
    
	//click sur une fiche reflexe
	$("#event").on("click", "a.fiche", function(){
		var id = $(this).data('id');
		var me = $(this);
		//tell the server to toggle the status
		$.getJSON(url+'events/togglefiche'+'?id='+id,
			function(data){
				if(data.open){
					me.html("A faire");
					me.removeClass("active btn-success");
				} else {
					me.html("Fait");
					me.addClass("active btn-success");
				}
			}
		);
	});

	//click on a predefined events
	$("#event").on("click", "#predefined_events button", function(e){
		e.preventDefault();
		var me = $(this);
		$.getJSON(
			url+'events/getpredefinedvalues?id='+me.data('id'),
			function(data){
				$("#punctual").prop('checked', data.defaultvalues.punctual);
				$("#punctual").trigger("change");
				$("#scheduled").prop('checked', data.defaultvalues.programmed);
                $("input[name=enddate]").data('duration', data.defaultvalues.duration);
				$("select[name=impact] option[value="+data.defaultvalues.impact+"]").prop('selected', true);
				if(data.defaultvalues['zonefilters']){
					$.each(data.defaultvalues.zonefilters, function(key, value){
						$("select[name='zonefilters[]'] option[value="+value+"]").prop('selected', true);
					});
				}
				$.each(data.customvalues, function(key, value){
					var elt = $("#custom_fields [name='custom_fields["+key+"]']");
					if(elt.length == 0) {
						//select à choix multiples ?
						elt = $("#custom_fields [name='custom_fields["+key+"][]']");
					}
					if(elt.is("select")){
						if(Array.isArray(value)) {
							for(var i = 0; i < value.length; i++) {
								$("#custom_fields [name='custom_fields[" + key + "][]'] option[value=" + value[i] + "]").prop('selected', true);
							}
						} else {
							if (value.length > 0) {
								$("#custom_fields [name='custom_fields[" + key + "]'] option[value=" + value + "]").prop('selected', true);
							}
						}
					} else if(elt.is('textarea')){
						elt.html(value);
					} else if (elt.is(':checkbox')){
						elt.attr('checked', (value == 1));
					} else if(elt.is('input')){
						elt.prop('value', value);
					}
				});
				//open description accordion
				$("#description-title a").trigger('click');
				//prepare actions
				$("#actions-title a").removeClass("disabled");
				//recalculate submit button state
				$("#event").trigger('change');
                //update hours in case of duration
                updateHours();
			});
		//get actions
		$("#actions-tab").load(url+'events/actions?id='+me.data('id'), function(e){
			$('#actions-tab [data-toggle="tooltip"]').tooltip();
		});

		//getfiles
		$.getJSON(url+'events/getfiles?id='+me.data('id'),
			function(data){
				$('#files-tab #file-table tbody').empty();
				$("#files-tab input").remove();
				$("#files-title span.badge").html('0');
				$.each(data, function(i, item){
					formAddFile(item.id, item.datas, false);
				});
			});
		//getalerts
		$.getJSON(url+'events/getalarms?id='+me.data('id'),
			function(data){
				$('#memos-tab #alarm-table').empty();
				$("#memos-title span.badge").html('0');
				$.each(data, function(i, item){
					formAddAlarm(item, true);
				});
			});
		//save the fact that we used a model in order to copy actions
		$('#description-tab').append("<input name=\"modelid\" type=\"hidden\" value=\""+me.data('id')+"\" >");
	});

	var rebootTabs = function () {
		//suppression des champs liés à une sous-catégorie
		$("#custom_fields").empty();
		//suppression des mémos
		$("#memos-tab #alarm-table").empty();
		$("#memos-title span.badge").html('0');
		//suppression des modèles
		$("#predefined_events").empty();
		//suppression des fichiers
		$("#file-table tbody").empty();
		$("#files-tab input").remove();
		$('#files-title span.badge').html('0');
		//suppression des actions
		$("#list-actions").remove();
		$('#actions-title span.badge').html('0');
		//suppression des notes
		$('#form-notes').empty();
		$('#notes-title span.badge').html('0');
        //durée par défaut
        $("input[name=enddate]").data('duration', -1);
	};

	//choosing a category
	$("#event").on("change", "#root_categories", function(){
		//disable subcategories select form before getting datas
		$('#subcategories option[value=-1]').prop('selected', true);
		$('#subcategories').prop('disabled',true);
		rebootTabs();

		var root_value = $("#root_categories option:selected").val();

		if(root_value > 0) {
			$.when(
				$.post(url+'events/subform?part=subcategories&id='+root_value + (tabid === 'timeline' ? '&onlytimeline=true' : '&tabid='+tabid),
					function(data){
						$('#subcategories').prop('disabled',false);
						$("#subcategories").html(data);
						$("#cat-title").addClass('valid');
						$("#hours-title a").removeClass("disabled");
						$("#description-title a").removeClass("disabled");
						$("#files-title a").removeClass("disabled");
						$("#memos-title a").removeClass("disabled");
						$('#actions-title a').removeClass("disabled");
					})
			).then(function(){
                if(cat_parent_id >= 0){
                    $("#subcategories").val(cat_id);
                    $("#subcategories").trigger('change');
                } else {
                    $.when(//récupération des modèles
                        $.post(
                            url+'events/subform?part=predefined_events&id='+root_value,
                            function(data){
                                $("#predefined_events").html(data);
                                $.material.checkbox();
                            }
                        ),
                        $.post(
                            url+'events/subform?part=custom_fields&id='+root_value,
                            function(data){
                                $("#custom_fields").html(data);
                                $("#custom_fields input, #custom_fields select").on("invalid", function(event){
                                    $("#description-title a").trigger('click');
                                    $(this).parents('.form-group').addClass('has-error');
                                });
                                $('#event').trigger('change');
                            }
                        )
                    ).then(function(){
                        if($("#subcategories option").length <= 1 && $("#predefined_events table").length === 0){
                            $("#description-title a").trigger('click');
                        }
                    });
                }
				cat_parent_id = -1;
				cat_id = -1;
			});
			$("input[name='category']").val(root_value);

		} else {
			//pas de catégories :
			$("#cat-title").removeClass('valid');
			$("input[name='submit']").prop('disabled', true).addClass('disabled');
			$("input[name='category']").val('');
			$("#hours-title a").addClass("disabled");
			$("#description-title a").addClass("disabled");
			$("#files-title a").addClass("disabled");
			$("#memos-title a").addClass("disabled");
			$('#actions-title a').addClass("disabled");
		}
	});

	//choosing a subcategory
	$("#event").on("change", "#subcategories", function(){
		var subcat_value = $("#subcategories option:selected").val();
		if (subcat_value > 0) {
			rebootTabs();
			$("input[name='category']").val(subcat_value);
            $.when(
                $.post(url + 'events/subform?part=custom_fields&id=' + subcat_value,
                    function(data) {
                        $("#custom_fields").html(data);
                        $("#event input, #event select").on("invalid", function(event){
                            $("#description-title a").trigger('click');
                        });
                        //force recalcule des conditions en fonction des champs chargés
                        $("#event").trigger('change');
                        $.material.checkbox();
                    }
                ),
                $.post(
                    url + 'events/subform?part=predefined_events&id=' + $(this).val(),
                    function(data){
                        $("#predefined_events").html(data);
                        //don't open model panel if there is no model
                        if($('#predefined_events div').length == 0) {
                            $("#description-title a").trigger('click');
                        }
                    }
                )
            ).then(function(){
                if($("#predefined_events table").length === 0){
                    $("#description-title a").trigger('click');
                }
            });
		} else {
			//réinit en fonction de la cat racine
			$("#root_categories").trigger('change');
		}
	});

	$("#event").on("change", "#punctual", function(){
		$("#end").siblings('input').prop('disabled',$(this).is(':checked'));
		$("#end input").prop('disabled', $(this).is(':checked'));
		if($(this).is(':checked')){
			$("#end a").addClass("disabled");
			$("#end input").val('');
			$("#end").siblings('input').val('');
		} else {
			$("#end a").removeClass("disabled");
		}
        updateFormError();
		var pattern = $("input[name=recurrencepattern]").val();
		if(pattern.length > 0 && pattern.localeCompare("FREQ=DAILY;INTERVAL=1;COUNT=1") != 0) {
            if($(this).is(':checked')){
                $("input[name=enddate]").prop('required', false);
                $("input[name=enddate]").siblings('.timepicker-form').find('input').prop('required', false);
            } else {
                $("input[name=enddate]").prop('required', true);
                $("input[name=enddate]").siblings('.timepicker-form').find('input').prop('required', true);
            }
        } else {
            $("input[name=enddate]").prop('required', false);
            $("input[name=enddate]").siblings('.timepicker-form').find('input').prop('required', false);
        }
        updateFormError();
		//updateHourTitle();
	});

	$("#event").on('click', '.delete-file', function(){
		$("button#delete-file-href").attr('href', $(this).data("href"));
		$("#file_name").html($(this).data('name'));
		$("#delete-file-href").data('id', $(this).data('id'));
	});

	$(".content").on('click', "#delete-file-href", function(event){
		event.preventDefault();
		var me = $(this);
		$('#confirm-delete-file').modal('hide');
		$.post($("#delete-file-href").attr('href'), function(data){
			$("#file-table").find('tr#file_'+me.data('id')).remove();
			$('#files-tab input[name=fichiers\\['+me.data('id')+'\\]]').remove();
			$('#files-title span.badge').html(parseInt($('#files-title span.badge').html())-1);
			displayMessages(data);
		}, 'json');
	});

	//tooltip pour prévenir que l'heure de fin va être remplie automatiquement
	$('#event').on('change', 'select[name=status]', function(){
		var select = $(this);
		if(select.val() == '3' && !$("#punctual").is(':checked') && $('input[name=enddate]').val() == ''){
			$("#event input[type=submit]").tooltip({
				container :'body',
				html:true,
				title: 'Heure de fin non renseignée.<br />L\'heure actuelle sera utilisée pour l\'heure de fin.'
			});
		} else {
			$("#event input[type=submit]").tooltip('destroy');
		}
	});

	$("#event").on('change', 'input[name=enddate]', function(){
		$("select[name=status]").trigger('change');
	});

	//fenêtre de création d'alarme
	$('#event').on('click', '#addalarm', function(e){
		e.preventDefault();
		$('#alarm-title').html("Ajout d'un mémo");
		$('#alarm-form').load(url+'alarm/form', function(){
			$("#alarm-form input[name=startdate]").timepickerform({"required":true, "id":"alarmstart", 'init':true});
		});
	});

	$("#event").on('click', '.modify-alarm', function(e){
		e.preventDefault();
		$('#alarm-title').html("Modification d'un mémo");
		var me = $(this);
		var id = me.closest('tr').data('id');
		$('#alarm-form').load(url+'alarm/form?id='+id, function(){
			$("#alarm-form input[name=startdate]").timepickerform({"required":true, 'id':'alarmstart', 'init':true});
		});
	});



	$('#alarm-form').on('submit', function(e){
		e.preventDefault();
		//deux cas : nouvelle alarme ou modif
		var me = $(this);
		var form = $("#alarm-form form");
		if(me.find('input[name=id]').val()){
			$.post(form.attr('action'), form.serialize(), function(data) {
				if (!data.messages['error']) {
					$("#add-alarm").modal('hide');
					var alarm = data.alarm;
					formModifyAlarm(alarm);
				}
				displayMessages(data.messages);
			});
		} else {
			$.post(form.attr('action'), form.serialize(), function(data) {
				if (!data.messages['error']) {
					$("#add-alarm").modal('hide');
					var alarm = data.alarm;
					formAddAlarm(alarm, false);
				}
				displayMessages(data.messages);
			});
		}
	});

	$("#event").on('click', '.delete-fake-alarm', function(e){
		e.preventDefault();
		var me = $(this);
		var id = me.closest('tr').data('id');
		me.closest('tr').remove();
		$('div#alarm-'+id).remove();
		$('#memos-tile span.badge').html(parseInt($('#memos-title span.badge').html())-1);
	});

	$("#event").on('click', '.delete-alarm', function(e){
		e.preventDefault();
		var me = $(this);
		var id = me.closest('tr').data('id');
		$.post(url+'alarm/delete?id='+id, function(data){
			if(!data['error']){
				me.closest('tr').remove();
				$('#memos-tile span.badge').html(parseInt($('#memos-tile span.badge').html())-1);
				deleteAlarm(id);
			}
			displayMessages(data);
		});
	});

	/**
	 * Si le champ heure de fin est effacé, il faut veiller à supprimer le statut Terminé
	 */
	$('#event').on('click', '.clear-time', function(e){
		var status = $('#event select[name=status]');
		if(status.val() == '3'){
			status.val('2');
			status.trigger('change');
		}
	});

	//ouverture du formulaire lors d'un clic sur catégorie
	$(document).on('click', '.category', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		var parentId = $(this).data('parentid');
		$("#create-link").trigger('click');
		cat_id = id;
		cat_parent_id = parentId;
	});

	//gestion des notes
	$("#event").on('click', '#addnote', function(e){
		e.preventDefault();
		$("#add-note").data('id', $(this).data('id'));
	});

	$("#add-note-modal").on('hide.bs.modal', function(){
		//update notes
		$("#form-notes").load(url+'events/updates?id='+$("#add-note").data('id'), function(){

		});
	});

	$('#recurr').on('show.bs.modal', function() {
        var startdate = $('input[name=startdate]').val();
        var start = convertInputIntoDate(startdate);
        var startsplit = startdate.split(' ');
        var pattern = $("input[name=recurrencepattern]").val();
        var forceEndDate = $('#myEndDate').val() == '';
        $("#recurr-scheduler").scheduler('value', {
            startDateTime: moment(start).utc().format('YYYY-MM-DDTHH:mm:ss.sssZ'),
            timeZone: {
                offset: '+00:00'
            },
            recurrencePattern: pattern
        });
        $("#myStartDate").val(startsplit[0].replace(/-/g, '/'));
        $("#MyStartTime").val(startsplit[1]);
        if(forceEndDate) {
            $("#myEndDate").val(startsplit[0].replace(/-/g, '/'));
        }
		$(".scheduler .end-on-date input").bootstrapMaterialDatePicker({
            format: "DD/MM/YYYY",
            time: false,
            lang: 'fr',
            cancelText: "Annuler",
            weekStart : 1
        });
        $(".scheduler .end-on-date input").bootstrapMaterialDatePicker('setMinDate', start);
        $(".scheduler .end-on-date input").bootstrapMaterialDatePicker('setDate', start);
	});

	$("#recurr .btn-success").on('click', function(){
        var recurrence = $("#recurr-scheduler").scheduler('value');
        var pattern = recurrence.recurrencePattern;
        if(pattern.localeCompare("FREQ=DAILY;INTERVAL=1;COUNT=1") == 0){
            $("input[name=recurrencepattern]").val('');
            $("#recurr-button").text('Configurer...');
            $('#recurr-humanreadable em').text('(Aucune récurrence.)');
			if(!$("#punctual").is(':checked')) {
				$("input[name=enddate]").prop('required', false);
                $("input[name=enddate]").siblings('.timepicker-form').find('input').prop('required', false);
				$("input[name=enddate]").trigger('change');
			}
		} else {
            $("input[name=recurrencepattern]").val(pattern);
            $("#recurr-button").text('Modifier...');
            var startD = moment($("#recurr-scheduler").scheduler('value')['startDateTime']);
            var start = startD.utc().format('YYYYMMDD[T]HHmm');
            $.getJSON(url+'events/getRecurrHumanReadable?pattern='+pattern+'&start='+start, function(data){
                var text = data.text;
                if(text !== '') {
                    $('#recurr-humanreadable em').text('('+text+')');
					if(!$("#punctual").is(':checked')) {
						$("input[name=enddate]").prop('required', true);
                        $("input[name=enddate]").siblings('.timepicker-form').find('input').prop('required', true);
					}
                } else {
                    $('#recurr-humanreadable em').text('(Aucune récurrence.)');
                    $("input[name=enddate]").prop('required', false);
                    $("input[name=enddate]").siblings('.timepicker-form').find('input').prop('required', false);
                }
				$("input[name=enddate]").trigger('change');
			});
        }
    });

    $("#event").on("change", "input[name=startdate]", function(e){
        var pattern = $("input[name=recurrencepattern]").val();
        if(pattern.length > 0 && pattern.localeCompare("FREQ=DAILY;INTERVAL=1;COUNT=1") != 0) {
            var start = moment($('input[name=startdate]').val(), "DD-MM-YYYY HH:mm").format('YYYYMMDD[T]HHmm');
            $.getJSON(url+'events/getRecurrHumanReadable?pattern='+pattern+'&start='+start, function(data){
                var text = data.text;
                if(text !== '') {
                    $('#recurr-humanreadable em').text('('+text+')');
                } else {
                    $('#recurr-humanreadable em').text('(Aucune récurrence.)');
                }
            });
        }
    });

    $('#endOptionsSelectList').on('changed.fu.selectlist', function () {
        var item = $(this).selectlist('selectedItem');
        if(item['value'] && (item.value == "after" || item.value == "date")) {
            $("#validateRecurrence").removeAttr('disabled');
        } else {
            $("#validateRecurrence").attr('disabled', 'disabled');
        }
    });

    $('#recurr').on('hidden.bs.modal', function(e){
        $('body').addClass('modal-open');
    });
};
