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

var iposhow = function (url){
	$('select[name=cat]').change(function(e){
		var me = $(this);
		var eventid = me.data('eventid');
		var newcatid = me.find('option:selected').val();
		var reportid = me.data('reportid');
		$.getJSON(url+'/report/affectcategory?id='+eventid+'&catid='+newcatid+'&reportid='+reportid, function(data){
			if(data['messages']){
				if(!data.messages['error']) {
					//move line to the corresponding table
					var id = data.id;
					var catid = data.catid;
					var tr = $("#event_"+id);
					tr.find('td:first').remove();
					if(catid != -1){
					    tr.find('td').eq(2).after('<td><a href="#add-note-modal" title="Ajouter une note" data-toggle="modal" class="add-note" data-eventid="'+id+'"><span class="glyphicon glyphicon-comment"></span></a></td>')
					}
					tr.detach();
					if(catid == -1) catid = "null";
					$("#category_"+catid+" tbody").append(tr);
					
				}
				displayMessages(data.messages);
			} 
		});
	});
	
	$('select[name=bulkcat]').change(function(e){
	    var me = $(this);
	    var newcatid = me.find('option:selected').val();
	    var reportid = me.data('reportid');
	    $('#toclassify input:checked').each(function(index, item){
		var eventid = $(item).data('id');
		$.getJSON(url+'/report/affectcategory?id='+eventid+'&catid='+newcatid+'&reportid='+reportid, function(data){
		    if(data['messages']){
			if(!data.messages['error']) {
				//move line to the corresponding table
				var id = data.id;
				var catid = data.catid;
				var tr = $("#event_"+id);
				tr.find('td:first').remove();
				if(catid != -1){
				    tr.find('td').eq(2).after('<td><a href="#add-note-modal" title="Ajouter une note" data-toggle="modal" class="add-note" data-eventid="'+id+'"><span class="glyphicon glyphicon-comment"></span></a></td>')
				}
				tr.detach();
				if(catid == -1) catid = "null";
				$("#category_"+catid+" tbody").append(tr);
				
			}
			displayMessages(data.messages);
		    } 
		});
	    });
	    me.find('option[value=""]').prop('selected', true);
	});
};