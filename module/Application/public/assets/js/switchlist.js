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
 */

/**
 * @author Bruno Spyckerelle
 */

var switchlist = function(url, tabid){

    //if true, switch the button to its previous state
    var back = true;
    var timeout;

    $('.object-switch').on('change', function(e){
        var newState = $(this).is(':checked');
        $('button#end-object-href').attr('href', $(this).data('href')+"&state="+newState+"&tabid="+tabid);
        $('#object_name').html($(this).data('object'));
        $("#cancel-object").data('object', $(this).data('objectid')) ;

        if(!newState){
            $("#confirm-end-event .modal-body #message").html("<p>Voulez-vous vraiment créer un nouvel évènement ?</p>"+
                "<p>L'heure actuelle sera utilisée comme heure de début.</p>");
            $('.form-group').show();
        } else {
            $("#confirm-end-event .modal-body #message").html( "<p>Voulez-vous vraiment terminer l'évènement en cours ?</p>"+
                "<p>L'heure actuelle sera utilisée comme heure de fin.</p>");
            $('.form-group').hide();
        }
        $("#confirm-end-event").modal('show');
    });

    $("#confirm-end-event").on('hide.bs.modal', function(){
        if(back){
            var button = $('#switch_'+$("#cancel-object").data('object'));
            button.prop('checked', !button.is(':checked') );
        }
    });

    $("#Event").on('submit', function(event){
        event.preventDefault();
        back = false;
        $("#confirm-end-event").modal('hide');
        $.post($("#end-object-href").attr('href'), $("#Event").serialize(), function(data){
            displayMessages(data);
            back = true;
            var button = $('#switch_'+$("#cancel-object").data('object'));
            if(data['error']){
                //dans le doute, on remet le bouton à son état antérieur
                button.prop('checked', !button.is(':checked') );
            }
            clearTimeout(timeout);
            doPoll();
        }, 'json');
    });

    //refresh page every 30s
    var doPoll = function(){
        $.post(url+'/switchlisttab/getobjectstate?tabid='+tabid)
            .done(function(data) {
                $.each(data, function(key, value){
                    $('#switch_'+key).prop('checked', value.status);
                    if(!value.status) {
                        $("#object-"+key+" a.open-fiche").show();
                    } else {
                        $("#object-"+key+" a.open-fiche").hide();
                    }
                    if(value.eventid > 0 ) {
                        $("#object-"+key+" a.modify-evt").data('id', value.eventid).show();
                    } else {
                        $("#object-"+key+" a.modify-evt").hide();
                    }
                });
            })
            .always(function() { timeout = setTimeout(doPoll, 30000);});
    };
    doPoll();
};
