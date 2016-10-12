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
var url;

var setUrl = function(urlt){
  url = urlt;
};

$(document).ready(function(){

    
	$("#add-report").on('click', function(){
		$("#report-title").html("Nouveau rapport");
		$("#report-form").load(url+'/report/newreport');
	});
	
	$(".mod-report").on('click', function(){
		var me = $(this);
		$("#report-title").html('Modifier le rapport <em>'+me.data('name')+'</em>');
		$("#report-form").load(url+'/report/newreport?id='+me.data('id'));
	});
    
	$("#report-container").on('submit', function(event){
		event.preventDefault();
		$.post(url+'/report/savereport', $("#Report").serialize(), function(data){
			if(data['messages']){
				displayMessages(data.messages);
			}
			location.reload();
		}, 'json').fail(function(){
			var messages = '({error: ["Impossible d\'enregistrer le rapport."]})';
			displayMessages(eval(messages));
		});
	});
	
	$(".remove-report").on('click', function(event){
		$('#report-name').html($(this).data('name'));
		$("#remove-report-href").data('id', $(this).data('id'));
	});
	
	$("#remove-report-container").on('click', '#remove-report-href', function(event){
		event.preventDefault();
		var me = $(this);
		var id = me.data('id');
		$("#remove-report-container").modal('hide');
		$.post(url+'/report/delete?id='+id, function(data){
			if(data['messages']) {
				displayMessages(data.messages);
			}
			if(data.messages['success']) {
				$('#reports-table tr#report-'+id).remove();
			}
		});
	});
	
	$("#report-container").arrive("#Report", function() {
	    $('#Report input').on('keypress', function(){
		$("#Report").validate({
		    highlight: function(element) {
			$(element).closest('.form-group').addClass('has-error');
		    },
		    unhighlight: function(element) {
		        $(element).closest('.form-group').removeClass('has-error');
		    }
		});
	    });
	});
	
});


