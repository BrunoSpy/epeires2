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

var afis = function(url) {


    $tAdmbodies = $(".t-adm tbody");
    $tUsrbody = $(".t-usr tbody");

    refresh();

    function refresh() {
        $('.btn-switch-af .a-edit-af .a-del-af').remove();
        if ($tUsrbody.length > 0)
            $tUsrbody
                .load(url + 'afis/get', { decomissionned: 0, admin: 0 }, setUsrBtn);

        if ($tAdmbodies.length > 0) {
            $tAdmbodies.first()
                .load(url + 'afis/get', { decomissionned: 0, admin: 1 }, setAdmBtn);

            $tAdmbodies.eq(1)
                .load(url + 'afis/get', { decomissionned: 1, admin: 1 }, setAdmBtn);
        }

        function setUsrBtn() {
            $('.btn-switch-af').change(function() {
                var boolState = 0;
                if ($(this).is(':checked')) {
                    boolState = 1;
                }

                $.post(
                    url + 'afis/switchafis', { id: $(this).data('id'), state: boolState },
                    function(data) {
                        noty({
                            text: data.msg,
                            type: data.type,
                            timeout: 4000,
                        });
                    },
                    'json'
                );
            });


            $.material.togglebutton();
            
            // console.log($('.togglebutton label input[type="checkbox"]:checked').css('background-color', '#000'));
            // console.log($('.toggle'));
        }

        function setAdmBtn() {
            $('.a-edit-af').unbind('click').click(function() {
                $("#title-edit-af").html("Modifier un AFIS");
                loadAfisForm($(this).data('id'));
            });

            $('.a-del-af').unbind('click').click(function() {
                var id = $(this).data('id');
                $('#s-del-af-name').html($(this).data('name'));
                $('#a-del-af-ok').unbind('click').click(function() {
                    $("#mdl-del-af").modal('hide');
                    $.post(
                        url + 'afis/delete', 
                        { id: id }, 
                        function(data) {
                            refresh();
                            noty({
                                text: data.msg,
                                type: data.type,
                                timeout: 4000,
                            });
                        }, 
                        'json'
                    );
                });
            });
        }

    }

    $("#btn-add-af").click(function() {
        $("#title-edit-af").html("Nouvel AFIS");
        loadAfisForm();
    });


    function loadAfisForm(id = null) {
        $("#f-edit-af").load(url + 'afis/form', { id: id }, function() {
            $.material.checkbox();
            $(this).find('input[type="submit"]')
                .click(submitHandler)
        });
    };

    function submitHandler(e) {
        e.preventDefault();
        $("#mdl-edit-af").modal('hide');
        $.post(
            url + 'afis/save',
            $('#Afis').serialize(),
            function(data) {
                refresh();
                noty({
                    text: data.msg,
                    type: data.type,
                    timeout: 4000,
                });
            },
            'json'
        );
    }
};
