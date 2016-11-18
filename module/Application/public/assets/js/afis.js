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
    $("#btn-af-add").click(function() {
        $("#title-edit-af").html("Nouvel AFIS");
        loadAfisForm();

    });

    $('.a-edit-af').click(function() {
        $("#title-edit-af").html("Modifier un AFIS");
        loadAfisForm($(this).data('id'));
    });

    function loadAfisForm(id=null) {
        $("#f-edit-af").load(url+'afis/form', {id: id}, function(e){
            $.material.checkbox();
            $(this).find('input[type="submit"]')
                .click(function(e){
                    e.preventDefault();
                    $("#mdl-edit-af").modal('hide');
                    $.post(url+'afis/save', $('#Afis').serialize(), function() {
                        location.reload();
                    }, 'json');
                })
        });
    };

    $('.a-del-af').click(function() {
        var id = $(this).data('id');
        $('#s-del-af-name').html($(this).data('name'));
        $('#a-del-af-ok').click(function(){
            $.post(url+'afis/delete', {id: id}, function(){
                location.reload();
            }, 'json');      
        });
    });

    $('.btn-switch-af').on('change', function(e){
        var boolState = 0;
        if ($(this).is(':checked')) {
            boolState = 1;
        } 
        
        $.post(url+'afis/switchafis',{id: $(this).data('id'),state: boolState},function(){
            location.reload();
        },'json');
    });
};
