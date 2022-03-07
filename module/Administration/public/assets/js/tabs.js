var tab = function(url){
	
	$("#add-tab").on('click', function(){
		$("#tab-title").html("Nouvel onglet");
		$("#tab-form").load(url+'/tabs/form', function(){
		    $.material.checkbox();
		});
	});
	
	$(".mod-tab").on('click', function(){
		$("#tab-title").html('Modification de <em>'+$(this).data('name')+'</em>');
		$("#tab-form").load(url+'/tabs/form?id='+$(this).data('id'), function(){
		    /*if($('input[name="isDefault"]').is(":checked")){
                $('input[name="isDefault"]').prop('disabled', true)
                    .parent().tooltip({
                        title: "Supprimer l'onglet principal est interdit."
                    });
			}*/
			$.material.checkbox();
		});
	});
	
	$("#tab-container").on('submit', function(event){
		event.preventDefault();
		$.post(url+'/tabs/save', $("#Tab").serialize(), function(data){
			if(data.messages['error']) {
				displayMessages(data.messages);
			} else {
				location.reload();
			}
		}, 'json');
	});

	$(".rm-tab").on('click', function(event){
		$('#tab-name').html($(this).data('name'));
		$("#delete-tab-href").data('id', $(this).data('id'));
	});
	
	$("#tab-rm-container").on('click', '#delete-tab-href', function(event){
		event.preventDefault();
		var me = $(this);
		$("#tab-rm-container").modal('hide');
		var id = me.data('id');
		$.post(url+'/tabs/remove?id='+id, function(){
			location.reload();
		}).fail(function(){
			var messages = '({error: ["Impossible de supprimer l\'onglet."]})';
			displayMessages(eval(messages));
		});
	});

	$('#tab-container').on('change', 'input[name="isDefault"]', function(e){
		var id = $('#Tab input[name="id"]').val();
		if($(this).is(":checked")) {
			$.post(url+'/tabs/setdefault?id='+id, function(data){
				displayMessages(data.messages);
			});
        } else {
            $.post(url+'/tabs/unsetdefault?id='+id, function(data){
                displayMessages(data.messages);
            });
		}
	});

	let previousType;
	$("#tab-container").on('focus', 'select[name="type"]', function(){
		previousType = $(this).find("option:selected").text();
	}).change(function(e){
		let type = $(this).find('select[name="type"] option:selected').text();
		switch (type) {
			case 'timeline':
			case 'splittimeline':
				$('#tab-container input[name="onlyroot"]').closest('.form-group').show();
				$('#tab-container input[name="horizontal"]').closest('.form-group').hide();
				$('#tab-container input[name="colorsInversed"]').closest('.form-group').hide();
				//do not reset categoris if previous type is compatible
				if(previousType == 'switchlist') {
					$.getJSON(url + '/tabs/getcategories?type=timeline', function (data) {
						let cats = [];
						$.each(data, function (index, val) {
							cats.push(val);
						});
						cats.sort(function (a, b) {
							return a.place > b.place;
						});
						let elt = $('#tab-container select[name="categories[]"]').empty();
						cats.forEach(function (element) {
							elt.append($('<option>', {
								value: element.id,
								text: element.name
							}));
						});
						elt.prop("multiple", "multiple");
					});
				}
				break;
			case 'switchlist':
				$('#tab-container input[name="onlyroot"]').closest('.form-group').hide();
				$('#tab-container input[name="horizontal"]').closest('.form-group').show();
				$('#tab-container input[name="colorsInversed"]').closest('.form-group').show();
				$.getJSON(url+'/tabs/getcategories?type=switchlist', function(data){
					let cats = [];
					$.each(data, function(index, val){
						cats.push(val);
					});
					cats.sort(function(a,b){
						return a.place > b.place;
					});
					let elt = $('#tab-container select[name="categories[]"]').empty();
					cats.forEach(function(element){
						elt.append($('<option>',{
							value: element.id,
							text: element.name
						}));
					});
					elt.prop("multiple", "");
				});
				break;
		}
		previousType = type;
	});
};