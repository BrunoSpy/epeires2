var urlt;

var createEventSuggest = function(id, event){
	var div = $('<div class="result"></div>');
	var html = "";
	var start = new Date(event.start_date);
	var end = new Date(event.end_date);
	html += "<dt>"+event.name+((event.status_id <= 2) ? ' <em>(en cours)</em>' : '')+"</dt>";
	html += '<dd>';
	html += '<small>Catégorie : '+event.category+'</small>';
	if(event.status_id <= 2){
		//evt en cours : modifier l'evt
		html += '<a data-name="'+event.name+'" data-id="'+id+'" class="btn btn-mini pull-right modify-evt">Modifier</a></dd>';
	} else {
		//evt terminé : copier
		html += '<a data-id='+id+' class="btn btn-mini pull-right copy-event">Copier</a></dd>';
	}
	div.append(html);
	var titlehtml = '<b>Date de début :</b> '+FormatNumberLength(start.getUTCDate(), 2)+'/'+FormatNumberLength(start.getUTCMonth(),2);
	if(event.end_date != null){
		titlehtml += '<br /><b>Date de fin :</b> '+FormatNumberLength(end.getUTCDate(), 2)+'/'+FormatNumberLength(end.getUTCMonth(),2);
	}
	$.each(event.fields, function(key, value){
		titlehtml += '<br/><b>'+key+' :</b> '+value; 
	});
	return div;
};

var formAddFile = function(fileId, formData, modifiable){
    modifiable = (typeof modifiable === "undefined") ? true : modifiable;
    var tr = $('<tr id="file_'+fileId+'"></tr>');
    tr.append('<td>'+formData.reference+'</td>');
    tr.append('<td><a rel="external" href="'+urlt+formData.path+'">'+formData.name+'</a></td>');
    tr.append('<td><a rel="external" href="'+urlt+formData.path+'"><i class="icon-download"></i></a></td>');
    if(modifiable){
        tr.append('<td><a href="#confirm-delete-file" class="delete-file" '+
            'data-href="'+urlt+'events/deletefile?id='+fileId+'" '+
            'data-id="'+fileId+'" '+
            'data-name="'+formData.name+'" '+
            'data-toggle="modal" '+
            '><i class="icon-trash"></i></a></td>');
    }
    $("#file-table").append(tr);
    var input = $('<input type="hidden" name="fichiers['+fileId+']" value="'+fileId+'"></input>');
    $("#inner-filesTitle").append(input);
    $("#filesTitle span").html(parseInt($("#filesTitle span").html())+1);
}

/**
 * 
 * @param {type} alarm
 * @param {boolean} alter if true, then alarm can be altered according to change of start date
 * @returns {undefined}
 */
var formAddAlarm = function(alarm, alter) {
        alter = typeof alter !== 'undefined' ? alter : false;
    
	var d = new Date(alarm.datetime);
	var count = Math.floor($("#inner-alarmTitle input").length / 3);
        //ajouter ligne dans le tableau
        var tr = $('<tr '+(alter ? 'class="fake-alarm"' : '')+' data-id="fake-'+count+'"></tr>');
	//si date est déjà passée : warning
	var now = new Date();
	if(now - d > 0) {
		tr.append('<td><i class="icon-warning-sign"></i></td>');
	} else {
		tr.append('<td><i class="icon-bell"></i></td>');
	}
        tr.append("<td>"+FormatNumberLength(d.getUTCHours(), 2)+":"+FormatNumberLength(d.getUTCMinutes(), 2)+"</td>");
        tr.append('<td>'+alarm.name+'</td>');
	tr.append('<td><a class="delete-fake-alarm" href="#"><i class="icon-trash"></i></a></td>');
        $('#alarm-table').append(tr);
        //ajouter fieldset caché
        var datestring = d.getUTCDate()+"-"+(d.getUTCMonth()+1)+"-"+d.getUTCFullYear();
        var timestring = FormatNumberLength(d.getUTCHours(), 2)+":"+FormatNumberLength(d.getUTCMinutes(), 2);
	var div = $('<div '+(alter ? 'class="fake-alarm"' : '')+' data-delta="'+alarm.delta+'" id="alarm-fake-'+count+'"></div>');
        div.append('<input type="hidden" name="alarm['+count+'][date]" value="'+datestring+" "+timestring+'"></input>');
        div.append('<input type="hidden" name="alarm['+count+'][name]" value="'+alarm.name+'"></input>');
        div.append('<input type="hidden" name="alarm['+count+'][comment]" value="'+alarm.comment+'"></input>');
	$('#inner-alarmTitle').append(div);
        $('#alarmTitle span').html(parseInt($('#alarmTitle span').html())+1);
};

var formModifyAlarm = function(alarm) {
	var d = new Date(alarm.datetime);
        //ajouter ligne dans le tableau
        var tr = $('<tr id="tr-'+alarm.id+'" data-id="'+alarm.id+'"></tr>');
	//si date est déjà passée : warning
	var now = new Date();
	if(now - d > 0) {
		tr.append('<td><i class="icon-warning-sign"></i></td>');
	} else {
		tr.append('<td><i class="icon-bell"></i></td>');
	}
        tr.append("<td>"+FormatNumberLength(d.getUTCHours(), 2)+":"+FormatNumberLength(d.getUTCMinutes(), 2)+"</td>");
        tr.append('<td>'+alarm.name+'</td>');
	tr.append('<td><a href="#add-alarm" data-toggle="modal" class="modify-alarm"><i class="icon-pencil"></i></a> <a class="delete-alarm" href="#"><i class="icon-trash"></i></a></td>');
        $('#alarm-table tr#tr-'+alarm.id).remove();
        $('#alarm-table').append(tr);
};

var form = function(url){
	
        urlt = url;
	
	//specific functions to maintain coherence between end and start inputs
        
	$('#event').on('change', 'input[name=startdate]', function(){
		var datefin = $("#inner-Horairesid #end").siblings('input[type=hidden]');
		var dateDeb = $("#inner-Horairesid #start").siblings('input[type=hidden]');
                var startsplit = dateDeb.val().split(' ');
		var daysplit = startsplit[0].split('-');
		var hourstartsplit = startsplit[1].split(':');
                var deb = new Date(daysplit[2], daysplit[1]-1, daysplit[0], hourstartsplit[0], hourstartsplit[1]);
		//check if start_date > end_date, if end_date is set
		if(datefin.val()){
			var endsplit = datefin.val().split(' ');
			var enddaysplit = endsplit[0].split('-');
			var hoursplit = endsplit[1].split(':');
			var end = new Date(enddaysplit[2], enddaysplit[1]-1, enddaysplit[0], hoursplit[0], hoursplit[1]);
                        if(deb > end){
				datefin.val(dateDeb.val());
				datefin.trigger('change');
				updateHours();
			}
		}
                var now = new Date();
                var nowUTC = new Date(now.getTime() + now.getTimezoneOffset()*60000);
                var diff = (deb - nowUTC)/60000; //différence en minutes
                //change status if needed, authorized and beginning near actual time
                if($('#event form').data('modstatus') 
                   && $("#event select[name=status] option:selected").val() == '1'
                   && (diff < 10)){
                    $("#event select[name=status] option[value=2]").prop('selected', true);
                }
		updateHourTitle();
                
                //mise à jour des alarmes en fonction de la date de début pour les modèles et les copies
                var delta = $('div.fake-alarm').data('delta');
                if(delta && delta != ''){
                    delta = parseInt(delta);
                    var alarmTime = new Date(deb.getTime() + delta*60000  - now.getTimezoneOffset()*60000);
                    var daystring = alarmTime.getUTCDate()+'-'+(alarmTime.getUTCMonth()+1)+'-'+alarmTime.getUTCFullYear();
                    var hourstring = alarmTime.getUTCHours()+':'+alarmTime.getUTCMinutes();
                    $('div.fake-alarm input:first-child').val(daystring+' '+hourstring);
                    $('tr.fake-alarm td:nth-child(2)').text(hourstring);
                }
	});
	
        $('#event').on('change', 'input[name=enddate]', function() {
            var dateDeb = $("#inner-Horairesid #start").siblings("input[type=hidden]");
            var dateFin = $("#inner-Horairesid #end").siblings("input[type=hidden]");
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
                if (deb > end) {
                    //add a day to enddate
                    var newend = new Date();
                    newend.setDate(end.getDate() +1);
                    $('#end td.day input').val(newend.getUTCDate()+'-'+(newend.getUTCMonth()+1)+'-'+newend.getUTCFullYear());
                    //.trigger('change');
                    //dateDeb.val(dateFin.val());
                    //updateHours();
                }
                //changement du statut à terminé si :
                //   * droits ok
                //et * modif d'un evt
                //et * (heure de début passée ou statut confirmé) et heure de fin < now+15
                var now = new Date();
                var nowUTC = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
                var nowUTCplus = new Date(nowUTC.getTime() + 15 * 60000);
                if ($('#event form').data('modstatus')
                        && $('#event input[name=id]').val() > 0 //id != 0 => modif
                        && (deb < nowUTC || $('#event select[name=status] option:selected').val() == '2')
                        && end < nowUTCplus) {
                    $('#event select[name=status] option[value=3]').prop('selected', true);
                } else if($('#event form').data('modstatus')
                        && $('#event input[name=id]').val() == 0){
                    //en cas de création on se permet le changement de statut
                    if(deb < nowUTC && end < nowUTCplus){
                        $('#event select[name=status] option[value=3]').prop('selected', true);
                    } else {
                        $('#event select[name=status] option[value=2]').prop('selected', true);
                    }
                }
            }
            updateHourTitle();

        });
	
	var updateHours = function(){
		//initialize datetime pickers
		//start date
		var start = $("#inner-Horairesid #start").siblings("input[type=hidden]").val();
		if(start){
			var daysplit = start.split(' ');
			var hoursplit = daysplit[1].split(':');
			$("#start .day input").val(daysplit[0]);
			$("#start .hour input").val(hoursplit[0]);
			$("#start .minute input").val(hoursplit[1]);
		} else {
			var d = new Date();
			$("#start .day input").val(d.getUTCDate()+"-"+(d.getUTCMonth()+1)+"-"+d.getUTCFullYear());
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
		var end = $("#inner-Horairesid #end").siblings("input[type=hidden]").val();
		if(end){
			var daysplit = end.split(' ');
			var hoursplit = daysplit[1].split(':');
			$("#end .day input").val(daysplit[0]);
			$("#end .hour input").val(hoursplit[0]);
			$("#end .minute input").val(hoursplit[1]);
		} 
	};
	
	var updateHourTitle = function(){
		var start = $("#inner-Horairesid #start").siblings("input[type=hidden]").val();
		var split = start.split(' ');
		var daysplit = split[0].split('-');
		var text = "Horaires : "+FormatNumberLength(daysplit[0],2)+"/"+FormatNumberLength(daysplit[1],2)+" "+split[1];
		var punctual = $("#punctual").is(':checked');
		if(!punctual){
			text += " \u2192 ";
			var end = $("#inner-Horairesid #end").siblings("input[type=hidden]").val();
			if(end){
				var split = end.split(' ');
				var daysplit = split[0].split('-');
				text += FormatNumberLength(daysplit[0],2)+"/"+FormatNumberLength(daysplit[1],2)+" "+split[1];
			} else {
				text += "?";
			}
		}
		$("#Horairesid").html(text);
	};
	
	/************************/
	        
	//submit form
	$("#event").on('submit', function(event){
		event.preventDefault();
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
		    	var id = $("#Event").find('input[name="id"]').val();
				if(id>0){
					//modification
					if(data['events']){
						timeline.modify(data.events, 0);
					}
					displayMessages(data.messages);
				} else {
					//new event
					if(data['events']){
						timeline.add(data.events);
					}
					displayMessages(data.messages);
				}
			},
			dataType: "json"
		});
	});
	
	$("#event").on("click", "#cancel-form", function(){
		$("#create-evt").slideUp('fast');
		$("#create-evt").offset({top:8, left:5});
		$("#create-link").html('<i class="icon-pencil"></i> <i class="icon-chevron-down"></i>');
		restoreUpdateAlarms();
	});
	
	$("#create-link").on("click", function(){
		if($("#create-evt").is(':visible')){
			$("#create-evt").slideUp('fast');
			$("#create-evt").offset({top:8, left:5});
			$("#create-link").html('<i class="icon-pencil"></i> <i class="icon-chevron-down"></i>');
			restoreUpdateAlarms();
		} else {
			$("#create-evt").offset({top: $(".navbar").offset().top+$(".navbar").outerHeight(), left:3.5});
			$("#event").html('<div>Chargement...</div>');
			$("#form-title").html("Nouvel évènement");
			$("#event").load(
					url+'events/form',
					function(){
						//disable every accordion but the first
						$("a.accordion-toggle:gt(0)").addClass("disabled");
                                                $("#event input[name=startdate]").timepickerform({'id':'start'});
                                                $("#event input[name=enddate]").timepickerform({'id':'end', 'clearable':true});
						updateHours();
						updateHourTitle();
					}
			);
			$("#create-evt").slideDown('fast');
			$("#create-link").html('<i class="icon-pencil"></i> <i class="icon-chevron-up"></i>');
			pauseUpdateAlarms();
		}
	});
	
	//clic sur utiliser un modèle
	$("#search-results").on("click", ".use-model", function(){
		var me = $(this);
		$("#search-results").slideUp('fast');
		$("#event").html('<div>Chargement...</div>');
		$("#form-title").html(me.data('name'));
		$("#create-evt").slideDown('fast');
		$("#create-link").html('<i class="icon-pencil"></i> <i class="icon-chevron-up"></i>');
		$("#event").load(url+'events/form?id='+me.data('id')+'&model=1', function(){
                        $("#event input[name=startdate]").timepickerform({'id':'start'});
                        $("#event input[name=enddate]").timepickerform({'id':'end', 'clearable':true});
			updateHours();
			$("#Horairesid").trigger('click');
		});
		$("#search-results").offset({top:0, left:0});
		pauseUpdateAlarms();
	});
	
	//clic sur copie d'un évènement
	$("#search-results, #suggestions-container").on("click", ".copy-event", function(e){
		var me = $(this);
		$("#search-results").slideUp('fast');
		$("#form-title").html(me.data('name'));
                if($("#suggestions-container").has(e.target).length === 0){
                    $("#event").html('<div>Chargement...</div>');
                    $("#create-evt").slideDown('fast');
                } else {
                    $("#root_categories").popover('hide');
                }
                
		$("#create-link").html('<i class="icon-pencil"></i> <i class="icon-chevron-up"></i>');
		$("#event").load(url+'events/form?id='+me.data('id')+'&copy=1', function(){
                        $("#event input[name=startdate]").timepickerform({'id':'start'});
                        $("#event input[name=enddate]").timepickerform({'id':'end', 'clearable':true});
			updateHours();
			$("#Horairesid").trigger('click');
		});
		$("#search-results").offset({top:0, left:0});
		pauseUpdateAlarms();
	});
	
	//click sur modification d'un évènement
	$(document).on("click", "#timeline a.modify-evt, #search-results a.modify-evt, #suggestions-container a.modify-evt", function(e){
		var me = $(this);
		$("#form-title").html(me.data('name'));
                //formulaire déjà ouvert si clic via suggestion
		if($("#suggestions-container").has(e.target).length === 0){	
                    $("#event").html('<div>Chargement...</div>');
                    $("#create-evt").slideDown('fast');
                } else {
                    $("#root_categories").popover('hide');
                }
                
		$("#create-link").html('<i class="icon-pencil"></i> <i class="icon-chevron-up"></i>');

		$("#event").load(url+'events/form?id='+me.data('id'), function(){
                        $("#event input[name=startdate]").timepickerform({'id':'start'});
                        $("#event input[name=enddate]").timepickerform({'id':'end', 'clearable':true});
                        //mise à jour en fonction du statut ponctuel
                        $('#event #punctual').trigger('change');
			updateHours();
                        updateHourTitle();
			pauseUpdateAlarms();
                        $('tr[data-toggle=tooltip]').tooltip();
		});
	});
	
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
	$("#event").on("click", "a.predefined", function(){
		$("#Modèlesid").html('Modèle : '+$(this).parent().prev().html());
		var me = $(this);
		$.getJSON(
				url+'events/getpredefinedvalues?id='+me.data('id'),
				function(data){
					$("#punctual").prop('checked', data.defaultvalues.punctual);
					$("#punctual").trigger("change");
					$("select[name=impact] option[value="+data.defaultvalues.impact+"]").prop('selected', true);
                                        if(data.defaultvalues['zonefilters']){
                                            $.each(data.defaultvalues.zonefilters, function(key, value){
						$("select[name='zonefilters[]'] option[value="+value+"]").prop('selected', true);
                                            });
                                        }
					$.each(data.customvalues, function(key, value){
						var elt = $("#custom_fields [name='custom_fields["+key+"]']");
						if(elt.is("select")){
							$("#custom_fields [name='custom_fields["+key+"]'] option[value="+value+"]").prop('selected', true);
						} else if(elt.is('textarea')){
							elt.html(value);
						} else if(elt.is(':hidden')){
							//do nothing
						} else if (elt.is(':checkbox')){
							elt.attr('checked', (value == 1));
						} else if(elt.is('input')){
							elt.prop('value', value);
						} 
						//TODO les autres types de champs : 
					});
					//open hour accordion
					$("#Horairesid").trigger('click');
					//prepare actions
					$("#actionsTitle").removeClass("disabled");
				});
		//get actions
		$.getJSON(
				url+'events/getactions?id='+me.data('id'),
				function(data){
					var container = $("#inner-actionsTitle");
					container.html("");
					//save id of model
					var content = "<input name=\"modelid\" type=\"hidden\" value=\""+me.data('id')+"\" >";
					//then the table of actions
					content += '<table class="table table-hover"><tbody>';
					$.each(data, function(key, value){
						content += "<tr data-id=\""+key+"\">";
						content += "<td><span class=\"label label-"+value.impactstyle+"\">"+value.impactname+"</span></td>";
						content += "<td>"+value.name+"</td>";
						content += '</tr>';
                                                $("#actionsTitle span").html(parseInt($("#actionsTitle span").html())+1);
					});						
					content += '</tbody></table>';
					container.html(content);
				}
		);
                //getfiles
                $.getJSON(url+'events/getfiles?id='+me.data('id'),
                        function(data){
                            $.each(data, function(i, item){
                                formAddFile(item.id, item.datas, false);
                            });
                        });
		//getalerts
		$.getJSON(url+'events/getalarms?id='+me.data('id'), 
			function(data){
				$.each(data, function(i, item){
					formAddAlarm(item, true);
				});  
			});
	});

	//choosing a category
	$("#event").on("change", "#root_categories", function(){
		//disable subcategories select form before getting datas
		$('#subcategories option[value=-1]').prop('selected', true);
		$('#subcategories').prop('disabled',true);
		//suppression des champs liés à une sous-catégorie
		$("#Modèlesid").html('Modèles').addClass("disabled");
		$("#actionsTitle").addClass("disabled");
		$("#inner-Ficheréflexe").html("");
		$("#custom_fields").html("");
		
		var root_value = $("#root_categories option:selected").val();
		
		if(root_value > 0) {
			
			$.post(url+'events/subform?part=subcategories&id='+$(this).val(),
				function(data){
					$('#subcategories').prop('disabled',false);
					$("#subcategories").html(data);
					$("#category_title").html('Catégories : '+$("#root_categories option:selected").text());
					$.post(
						url+'events/subform?part=custom_fields&id='+root_value,
						function(data){
							$("#custom_fields").html(data);
                                                        $("#event input").on("invalid", function(event){
                                                            $("#accordion-Descriptionid").collapse('show');
                                                            console.log('done');
                                                        });
						}			
					);
					
					$("#Horairesid").removeClass("disabled");
					$("#Descriptionid").removeClass("disabled");
					$("#filesTitle").removeClass("disabled");
					$("#alarmTitle").removeClass("disabled");
					$("input[name='submit']").prop('disabled', false);
					
			});
			$("input[name='category']").val(root_value);
                        //affichage des évts suggérés
                        $.getJSON(url+'events/suggestEvents?id='+root_value, function(data){
                            var dl = $("<dl></dl>");
                            $.each(data, function(key, value){
                                dl.append(createEventSuggest(key, value));
                            });
                            //add suggestions
                            $('#root_categories').popover('destroy');
                            if(dl.find('div.result').length > 0){
                                $('#root_categories').popover({
                                    html: true,
                                    trigger: "manual",
                                    title: "Suggestions : ",
                                    content: dl.html(),
                                    container: "#suggestions-container"
                                });
                                $("#root_categories").popover('show');
                            }
                        });
                        //récupération des modèles
                        $.post(
				url+'events/subform?part=predefined_events&id='+$(this).val(),
				function(data){
					$("#predefined_events").html(data);
					$("#Modèlesid").removeClass("disabled");
				}
			);
		} else {
			$("#category_title").html('Catégories');
			$("#Horairesid").addClass("disabled");
			$("#Descriptionid").addClass("disabled");
			$("#alarmTitle").addClass("disabled");
			$("#filesTitle").addClass("disabled");
			$("input[name='submit']").prop('disabled', true);
			$("input[name='category']").val('');
		}
	});

	//choosing a subcategory
	$("#event").on("change", "#subcategories", function(){
		var subcat_value = $("#subcategories option:selected").val();
                if (subcat_value > 0) {
                    $("#category_title").html('Catégories : ' + $("#root_categories option:selected").text() + ' > ' + $("#subcategories option:selected").text());
                    $("#custom_fields").html("");
                    $("input[name='category']").val(subcat_value);
                    $.post(
                            url + 'events/subform?part=custom_fields&id=' + subcat_value,
                            function(data) {
                                $("#custom_fields").html(data);
                                $("#event input").on("invalid", function(event){
                                    $("#Descriptionid-accordion").collapse('show');
                                });
                            }
                    );
                    $.post(
                            url + 'events/subform?part=predefined_events&id=' + $(this).val(),
                                        function(data){
					$("#predefined_events").html(data);
					$("#Modèlesid").removeClass("disabled");
                                        //don't open model panel if there is no model
                                        if($('#predefined_events table').length > 0) {
                                            $('#Modèlesid').trigger('click');
                                        } else {
                                            $("#Horairesid").trigger('click');
                                        }
				}
			);
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
		updateHourTitle();
	});
        
	$("#event").on('click', '.delete-file', function(){
		$("a#delete-file-href").attr('href', $(this).data("href"));
		$("#file_name").html($(this).data('name'));
		$("#delete-file-href").data('id', $(this).data('id'));
	});
	
	$(".content").on('click', "#delete-file-href", function(event){
		event.preventDefault();
		var me = $(this);
		$('#confirm-delete-file').modal('hide');
		$.post($("#delete-file-href").attr('href'), function(data){
			$("#file-table").find('tr#file_'+me.data('id')).remove();
                        $('#inner-filesTitle input[name=fichiers\\['+me.data('id')+'\\]]').remove();
                        $('#filesTitle span').html(parseInt($('#filesTitle span').html())-1);
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
                $('#alarm-title').html("Ajout d'une alarme");
		$('#alarm-form').load(url+'alarm/form', function(){
                    $("#alarm-form input[name=startdate]").timepickerform({"required":true, "id":"alarmstart", 'init':true});
                });
	});
	
        $("#event").on('click', '.modify-alarm', function(e){
            e.preventDefault();
            $('#alarm-title').html("Modification d'une alarme");
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
		$('#alarmTitle span').html(parseInt($('#alarmTitle span').html())-1);
	});
	
	$("#event").on('click', '.delete-alarm', function(e){
		e.preventDefault();
		var me = $(this);
		var id = me.closest('tr').data('id');
		$.post(url+'alarm/delete?id='+id, function(data){
			if(!data['error']){
				me.closest('tr').remove();
				$('#alarmTitle span').html(parseInt($('#alarmTitle span').html())-1);
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
        
        //hide popover if click outside
	$(document).mousedown(function(e){
		var container = $(".popover, #root_categories").not("#create-evt");
		if(!container.is(e.target) && container.has(e.target).length === 0){
                    $("#root_categories").popover('hide');
		};		
	});
        
        //gestion des notes
        $("#event").on('click', '#addnote', function(e){
            e.preventDefault();
            
        });
};
