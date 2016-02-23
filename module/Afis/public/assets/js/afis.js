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
 * @author Loïc Perrin
 */

var afis = function(url){
    
    var back = true;
    /*
     * Changer l'état
     */
    $('.afis-switch').on('change', function(e){
        var boolState = 0;
        if ($(this).is(':checked')) {
            boolState = 1;
        } 
        
        $.post(url+'/switchafis',{afisid:$(this).data('afis-id'),state:boolState},function(data){

        },'json');
    });
    /*
     * Ajouter/Modifier
     */
    $("#afis-cont-form").on('click', 'input[type=submit]', function(event){
        event.preventDefault();
        $.post(url+'/save', $("#Afis").serialize(), function(data){
            location.reload();
        }, 'json');
    });
    
    $(".afis-a-edit").on('click', function()
    {
        $("#afis-title").html("Modification AFIS");
        $("#afis-form").load(url+'/form', {afisid:$(this).data('afis-id')}, function(){
            $.material.checkbox();
        });
    });
    $("#afis-add").on('click', function()
    {
        $("#afis-title").html("Nouvel AFIS");
        $("#afis-form").load(url+'/form', function(e){
            $.material.checkbox();
        });
    });
    /*
     * Supprimer
     */
    $(".afis-a-del").on('click', function (e){
        $("#afis-cont-del-name").html($(this).data('afis-name'));
        $("#afis-cont-del-ok").data('afis-id', $(this).data('afis-id'));
    });
    
    $("#afis-cont-del-ok").on('click', function (e){
        $.post(url+'/delete', {afisid:$(this).data('afis-id')}, function(data){
           location.reload();
        }, 'json');
    });
};
