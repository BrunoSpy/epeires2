var form = function(url){
	
	/*********************/
	/***** Time Picker ***/
	/*********************/
	
	var hourplusone = function(input, delta){
		if(input.val()){
			var hour = parseInt(input.val())+delta;
			if(hour >0 && hour <= 9)
				hour = "0"+hour;
			if(hour<=0)
				hour = 23;
			if(hour>23)
				hour = "00";
		} else {
			var d = new Date();
			hour = d.getUTCHours();
			if(hour >=0 && hour <= 9){
				hour = "0"+hour;
			}
		}
		return hour;
	};
	
	var minuteplusone = function(input, delta){
		if(input.val()){
			var minutes = parseInt(input.val())+delta;
			if(minutes>0 && minutes <= 9)
				minutes = "0"+minutes;
			if(minutes<=0)
				minutes = 59;
			if(minutes>59)
				minutes = "00";
		} else {
			var d = new Date();
			minutes = d.getUTCMinutes();
			if(minutes >=0 && minutes <= 9){
				minutes = "0"+minutes;
			}
		}
		return minutes;
	};
	
	var fillInputs = function(timepickerform){
		var day = "";
		if(!timepickerform.find('.day input').val()){
			var d = new Date();
			day = d.getUTCDate()+"-"+(d.getUTCMonth()+1)+"-"+d.getUTCFullYear();
			timepickerform.find('.day input').val(day);
		}
		if(!timepickerform.find('.hour input').val()){
			var hour = "00";
			if($(this).attr('end')){
				hour = $("#start .hour input").val();
			} else {
				var d = new Date();
				hour = d.getUTCHours();
				if(hour >=0 && hour <= 9){
					hour = "0"+hour;
				}
			}
			timepickerform.find('.hour input').val(hour);
		}
		if(!timepickerform.find('.minute input').val()){
			var minutes = "00";
			if($(this).attr('end')){
				minutes = $("#start .minute input").val();
			} else {
				var d = new Date();
				minutes = d.getUTCMinutes();
				if(minutes >=0 && minutes <= 9){
					minutes = "0"+minutes;
				}
			}
			timepickerform.find('.minute input').val(minutes);
		}
	};
	
	$('#event').on('change', '.timepicker-form#start input', function(){
		var me = $(this).closest('.timepicker-form');
		fillInputs(me);
		$("#dateDeb").val(me.find('.day input').val()+" "+me.find('.hour input').val()+":"+me.find('.minute input').val());
		//check if start_date > end_date, if end_date is set
		if($("#dateFin").val()){
			var daysplit = me.find('.day input').val().split('-');
			var endsplit = $("#dateFin").val().split(' ');
			var enddaysplit = endsplit[0].split('-');
			var hoursplit = endsplit[1].split(':');
			var deb = new Date(daysplit[0], daysplit[1]-1, daysplit[2], me.find('.hour input').val(),me.find('.minute input').val());
			var end = new Date(enddaysplit[0], enddaysplit[1]-1, enddaysplit[2], hoursplit[0], hoursplit[1]);
			if(deb > end){
				$("#dateFin").val($("#dateDeb").val());
				$("#dateFin").trigger('change');
				updateHours();
			}
		}
		updateHourTitle();
	});
	
	$('#event').on('change', '.timepicker-form#end input', function(){
		var me = $(this).closest('.timepicker-form');
		fillInputs(me);
		$("#dateFin").val(me.find('.day input').val()+" "+me.find('.hour input').val()+":"+me.find('.minute input').val());
		$("#dateFin").trigger('change');
		//check if end_date < start_date	
		var daysplit = me.find('.day input').val().split('-');
		var startsplit = $("#dateDeb").val().split(' ');
		var startdaysplit = startsplit[0].split('-');
		var hoursplit = startsplit[1].split(':');
		var end = new Date(daysplit[0], daysplit[1]-1, daysplit[2], me.find('.hour input').val(),me.find('.minute input').val());
		var deb = new Date(startdaysplit[0], startdaysplit[1]-1, startdaysplit[2], hoursplit[0], hoursplit[1]);
		if(deb > end){
			$("#dateDeb").val($("#dateFin").val());
			updateHours();
		}
		updateHourTitle();
	});
	
	$('#event').on('click', '.timepicker-form .hour .next', function(event){
		event.preventDefault();
		var input = $(this).closest('td').find('input');
		input.val(hourplusone(input, 1));
		input.trigger('change');
	});
	
	$('#event').on('click', '.timepicker-form .minute .next', function(event){
		event.preventDefault();
		var input = $(this).closest('td').find('input');
		input.val(minuteplusone(input, 1));
		input.trigger('change');
	});
	
	$('#event').on('click', '.timepicker-form .hour .previous', function(event){
		event.preventDefault();
		var input = $(this).closest('td').find('input');
		input.val(hourplusone(input, -1));
		input.trigger('change');
	});
	
	$('#event').on('click', '.timepicker-form .minute .previous', function(event){
		event.preventDefault();
		var input = $(this).closest('td').find('input');
		input.val(minuteplusone(input, -1));
		input.trigger('change');
	});
	
	$('#event').on('mousewheel', 'td.hour input', function(event, delta){
		event.preventDefault();
		$(this).val(hourplusone($(this), delta));
		$(this).trigger('change');
	});
	
	$('#event').on('mousewheel', 'td.minute input', function(event, delta){
		event.preventDefault();
		$(this).val(minuteplusone($(this), delta));;
		$(this).trigger('change');
	});
	
	var updateHours = function(){
		//initialize datetime pickers
		//start date
		var start = $("#dateDeb").val();
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
		var end = $("#dateFin").val();
		if(end){
			var daysplit = end.split(' ');
			var hoursplit = daysplit[1].split(':');
			$("#end .day input").val(daysplit[0]);
			$("#end .hour input").val(hoursplit[0]);
			$("#end .minute input").val(hoursplit[1]);
		} 
	};
	
	var updateHourTitle = function(){
		var start = $("#dateDeb").val();
		var split = start.split(' ');
		var daysplit = split[0].split('-');
		var text = "Horaires : "+daysplit[0]+"/"+daysplit[1]+" "+split[1];
		var punctual = $("#punctual").is(':checked');
		if(!punctual){
			text += " > ";
			var end = $("#dateFin").val();
			if(end){
				var split = end.split(' ');
				var daysplit = split[0].split('-');
				text += daysplit[0]+"/"+daysplit[1]+" "+split[1];
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
			url: url+'/save',
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
	});
	
	$("#event").on("focus", 'input[type=text].date', function(){
		$(this).datepicker({
			dateFormat: "dd-mm-yy",
		});
	});

	$("#create-link").on("click", function(){
		if($("#create-evt").is(':visible')){
			$("#create-evt").slideUp('fast');
			$("#create-evt").offset({top:8, left:5});
			$("#create-link").html('<i class="icon-pencil"></i> <i class="icon-chevron-down"></i>');
		} else {
			$("#create-evt").offset({top: $(".navbar").offset().top+$(".navbar").outerHeight(), left:3.5});
			$("#event").html('<div>Chargement...</div>');
			$("#form-title").html("Nouvel évènement");
			$("#event").load(
					url+'/form',
					function(){
						//disable every accordion but the first
						$("a.accordion-toggle:gt(0)").addClass("disabled");
						updateHours();
						updateHourTitle();
					}
			);
			$("#create-evt").slideDown('fast');
			$("#create-link").html('<i class="icon-pencil"></i> <i class="icon-chevron-up"></i>');
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
		$("#event").load(url+'/form?id='+me.data('id')+'&model=1', function(){
			updateHours();
			$("#Horairesid").trigger('click');
		});
		$("#search-results").offset({top:0, left:0});
	});
	
	//clic sur copie d'un évènement
	$("#search-results").on("click", ".copy-event", function(){
		var me = $(this);
		$("#search-results").slideUp('fast');
		$("#event").html('<div>Chargement...</div>');
		$("#form-title").html(me.data('name'));
		$("#create-evt").slideDown('fast');
		$("#create-link").html('<i class="icon-pencil"></i> <i class="icon-chevron-up"></i>');
		$("#event").load(url+'/form?id='+me.data('id')+'&copy=1', function(){
			updateHours();
			$("#Horairesid").trigger('click');
		});
		$("#search-results").offset({top:0, left:0});
	});
	
	//click sur modification d'un évènement
	$("#timeline").on("click", "button.modify-evt", function(){
		var me = $(this);	
		$("#event").html('<div>Chargement...</div>');
		$("#form-title").html(me.data('name'));
		$("#create-evt").slideDown('fast');
		$("#create-link").html('<i class="icon-pencil"></i> <i class="icon-chevron-up"></i>');

		$("#event").load(url+'/form?id='+me.data('id'), function(){
			updateHours();
		});
	});
	
	//click sur une fiche reflexe
	$("#event").on("click", "a.fiche", function(){
		var id = $(this).data('id');
		var me = $(this);
		//tell the server to toggle the status
		$.getJSON(url+'/togglefiche'+'?id='+id,
				function(data){
			if(data.open){
				me.html("A faire");
				me.removeClass("active");
			} else {
				me.html("Fait");
				me.addClass("active");
			}
		}
		);
	});

	//click on a predefined events
	$("#event").on("click", "a.predefined", function(){
		$("#Modèlesid").html('Modèle : '+$(this).parent().prev().html());
		var me = $(this);
		$.getJSON(
				url+'/getpredefinedvalues?id='+me.data('id'),
				function(data){
					$("#punctual").prop('checked', data.defaultvalues.punctual);
					$("#punctual").trigger("change");
					$("select[name=impact] option[value="+data.defaultvalues.impact+"]").prop('selected', true);
					$.each(data.defaultvalues.zonefilters, function(key, value){
						$("select[name='zonefilters[]'] option[value="+value+"]").prop('selected', true);
					});
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
				url+'/getactions?id='+me.data('id'),
				function(data){
					var container = $("#inner-Ficheréflexe");
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
					});						
					content += '</tbody></table>';
					container.html(content);
				}
		);
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
			
			$.post(url+'/subform?part=subcategories&id='+$(this).val(),
				function(data){
					$('#subcategories').prop('disabled',false);
					$("#subcategories").html(data);
					$("#category_title").html('Catégories : '+$("#root_categories option:selected").text());
					$.post(
						url+'/subform?part=custom_fields&id='+root_value,
						function(data){
							$("#custom_fields").html(data);
						}			
					);
					
					$("#Horairesid").removeClass("disabled");
					$("#Descriptionid").removeClass("disabled");
					$("#filesTitle").removeClass("disabled");
					
					$("input[name='submit']").prop('disabled', false);
					
			});
			$("input[name='category']").val(root_value);
		} else {
			$("#category_title").html('Catégories');
			$("#Horairesid").addClass("disabled");
			$("#Descriptionid").addClass("disabled");
			$("#filesTitle").addClass("disabled");
			$("input[name='submit']").prop('disabled', true);
			$("input[name='category']").val('');
		}
	});

	//choosing a subcategory
	$("#event").on("change", "#subcategories", function(){
		var subcat_value = $("#subcategories option:selected").val();
		if(subcat_value > 0) {
			$.post(
				url+'/subform?part=predefined_events&id='+$(this).val(),
				function(data){
					$("#predefined_events").html(data);
					$("#category_title").html('Catégories : '+$("#root_categories option:selected").text()+' > '+$("#subcategories option:selected").text());
					$("#Modèlesid").removeClass("disabled");
					$("#custom_fields").html("");
					$.post(
						url+'/subform?part=custom_fields&id='+subcat_value,
						function(data){
							$("#custom_fields").html(data);
						}			
					);
					$('#Modèlesid').trigger('click');
				}
			);
			$("input[name='category']").val(subcat_value);
		} else {
			//réinit en fonction de la cat racine
			$("#root_categories").trigger('change');
		}
	});

	$("#event").on("change", "#punctual", function(){
		$("#dateFin").prop('disabled',$(this).is(':checked')); 
		$("#end input").prop('disabled', $(this).is(':checked'));
		if($(this).is(':checked')){
			$("#end a").addClass("disabled");
		} else {
			$("#end a").removeClass("disabled");
		}
		updateHourTitle();
	});

	//ajout formulaire fichier
	$("#event").on('click', "#addfile", function(){
		var lastinput = $("#file_list > div:last").attr('id');
		var count = (typeof lastinput == 'undefined' ? 1 : parseInt(lastinput[lastinput.length -1]) +1);
		$("<div>").load(url+'/subform?part=file_field&count='+count, function(){
			$("#file_list").append($(this).html());
		});
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
			displayMessages(data);
		}, 'json');
	});
	
	$("#event").on('click', ".removefile", function(){
		var count = $(this).data('count');
		$("#filefield_"+count).remove();		
	});
	
	//interdiction de sauver un evt si status = terminé et !punctual et pas de date de fin
	$('#event').on('change', 'select[name=status]', function(){
		var select = $(this);
		if(select.val() == '3' && !$("#punctual").is(':checked') && $('#dateFin').val() == ''){
			$("input[type=submit]").tooltip({
				container :'body',
				title: 'Date de fin manquante'
			}).addClass('disabled');
		} else {
			$("input[type=submit]").tooltip('destroy').removeClass('disabled');
		}
	});
	
	$("#event").on('change', '#dateFin', function(){
		$("select[name=status]").trigger('change');
	});
};
