var form = function(url){
	/***** Suggestions *****/
	$("#event").on('change', '#root_categories', function(event){
		var offset = $("#create-evt").offset();
		$("#suggest-evt").offset({top: $("#create-evt").offset().top, left: (offset.left + $("#create-evt").width())}).show();
	});
	
	
	$('html').click(function() {
		if($("#suggest-evt").is(':visible')){
			$("#suggest-evt").offset({top:0, left:0});
			$("#suggest-evt").hide();
		}
	});

	$('#suggest-evt').click(function(event){
		event.stopPropagation();
	});
	
	$('#event').on('click', '#root_categories', function(event){
		event.stopPropagation();
	});
	
	/**********************/
	
	//submit form
	$("#event").on('click', 'input[type=submit]', function(event){
		event.preventDefault();
		$.post(url+'/save', $("#eventform").serialize(), function(data){
			//close form
			$("#create-link").trigger("click");
			var id = $("#eventform").find('input#id').val();
			if(id>0){
				//modification
				timeline.modify(data);
			} else {
				//new event
				timeline.add(data);
			}
		}, "json");
		
	});
	
	$("#event").on("click", "#cancel-form", function(){
		$("#create-link").trigger("click");
	});

	$("#event").on("focus", 'input[type=datetime]', function(){
		$(this).datetimepicker({
			dateFormat: "dd-mm-yy",
		});
	});

	$("#loading").ajaxStart(function(){
		$(this).show();})
		.ajaxStop(function(){
			$(this).hide();});


	$("#create-link").on("click", function(){
		if($("#create-evt").is(':visible')){
			$("#create-evt").slideUp('fast');
			$("#create-link").html('<i class="icon-pencil"></i> <i class="icon-chevron-down"></i>');
		} else {
			$("#create-evt").offset({top :$(".navbar").offset().top+$(".navbar").outerHeight(), left:3.5});
			$("#event").html('<div id="loading">Chargement...</div>');
			$("#form-title").html("Nouvel évènement");
			$("#event").load(
					url+'/form',
					function(){
						//disable every accordion but the first
						$("a.accordion-toggle:gt(0)").addClass("disabled");
					}
			);
			$("#create-evt").slideDown('fast');
			$("#create-link").html('<i class="icon-pencil"></i> <i class="icon-chevron-up"></i>');
		}
	});

	//click sur modification d'un évènement
	$(".timeline").on("click", "button.modify-evt", function(){
		var me = $(this);	
		$("#event").html('<div id="loading">Chargement...</div>');
		$("#form-title").html(me.data('name'));
		$("#create-evt").slideDown('fast');
		$("#create-link").html('<i class="icon-pencil"></i> <i class="icon-chevron-up"></i>');

		$("#event").load(url+'/form?id='+me.data('id'));
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
					$("#name").val(data.defaultvalues.name);
					$("#punctual").prop('checked', data.defaultvalues.punctual);
					$("#punctual").trigger("change");
					$.each(data.customvalues, function(key, value){
						var elt = $("#"+key);
						if(elt.is("select")){
							$("#"+key+" option[value="+value+"]").prop('selected', true);
						} else if(elt.is('textarea')){
							elt.html(value);
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
		$.post(url+'/subform?part=subcategories&id='+$(this).val(),
				function(data){
			$("#subcategories").html(data);
			if($("#root_categories option:selected").val() > 0) {
				$("#category_title").html('Catégories : '+$("#root_categories option:selected").text());
				$("#Horairesid").removeClass("disabled");
				$("#Descriptionid").removeClass("disabled");	
				$('#subcategories').trigger("change");
			} else {
				$("#category_title").html('Catégories');
				$("#Horairesid").addClass("disabled");
				$("#Descriptionid").addClass("disabled");
				$('#subcategories').trigger("change");
			}
		});
	});

	//choosing a subcategory
	$("#event").on("change", "#subcategories", function(){
		$.post(
				url+'/subform?part=predefined_events&id='+$(this).val(),
				function(data){
					$('#subcategories').prop('disabled',false);
					$("#predefined_events").html(data);
					if($("#subcategories option:selected").val() > 0) {
						$("#category_title").html('Catégories : '+$("#root_categories option:selected").text()+' > '+$("#subcategories option:selected").text());
						$("#Modèlesid").removeClass("disabled");
						$.post(
								url+'/subform?part=custom_fields&id='+$("#subcategories option:selected").val(),
								function(data){
									$("#custom_fields").html(data);
								}			
						);
						$('#Modèlesid').trigger('click');

					} else {
						//pas de sous-catégorie choisie, on supprime les champs spécifiques
						if($("#root_categories").val() > 0) {
							$("#category_title").html('Catégories : '+$("#root_categories option:selected").text());
						}

						$("#Modèlesid").html('Modèles').addClass("disabled");
						$("#actionsTitle").addClass("disabled");
						$("#custom_fields").html("");
						$("#inner-Ficheréflexe").html("");
					}
				}
		);

	});

	$("#event").on("change", "#punctual", function(){
		$("#dateFin").prop('disabled',$(this).is(':checked')); 
	});

};
