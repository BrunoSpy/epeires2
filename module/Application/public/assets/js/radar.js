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

var radar = function(url){

    //if true, switch the button to its previous state
    var back = true;

    $('.radar-switch').on('change', function(e){
        var newState = $(this).is(':checked');
        $('button#end-radar-href').attr('href', $(this).data('href')+"&state="+newState);
        $('#radar_name').html($(this).data('radar'));
        $("#cancel-radar").data('radar', $(this).data('radarid')) ;

        if(!newState){
            $("#confirm-end-event .modal-body #message").html("<p>Voulez-vous vraiment créer un nouvel évènement radar ?</p>"+
                "<p>L'heure actuelle sera utilisée comme heure de début.</p>");
            $('.form-group').show();
        } else {
            $("#confirm-end-event .modal-body #message").html( "<p>Voulez-vous vraiment terminer l'évènement radar en cours ?</p>"+
                "<p>L'heure actuelle sera utilisée comme heure de fin.</p>");
            $('.form-group').hide();
        }
        $("#confirm-end-event").modal('show');
    });

    $("#confirm-end-event").on('hide.bs.modal', function(){
        if(back){
            var button = $('#switch_'+$("#cancel-radar").data('radar'));
            button.prop('checked', !button.is(':checked') );
        }
    });

    $("#Event").on('submit', function(event){
        event.preventDefault();
        back = false;
        $("#confirm-end-event").modal('hide');
        $.post($("#end-radar-href").attr('href'), $("#Event").serialize(), function(data){
            displayMessages(data);
            back = true;
            var button = $('#switch_'+$("#cancel-radar").data('radar'));
            if(data['error']){
                //dans le doute, on remet le bouton à son état antérieur
                button.prop('checked', !button.is(':checked') );
            }
            if(!button.is(':checked')) {
                $("#radar-"+$("#cancel-radar").data('radar')+" a.open-fiche").show();
            } else {
                $("#radar-"+$("#cancel-radar").data('radar')+" a.open-fiche").hide();
            }
        }, 'json');
    });

    //refresh page every 30s
    (function doPoll(){
        $.post(url+'radars/getradarstate')
            .done(function(data) {
                $.each(data, function(key, value){
                    $('#switch_'+key).prop('checked', value);
                    if(!value) {
                        $("#radar-"+key+" a.open-fiche").show();
                    } else {
                        $("#radar-"+key+" a.open-fiche").hide();
                    }
                });
            })
            .always(function() { setTimeout(doPoll, 30000);});
    })();
};
