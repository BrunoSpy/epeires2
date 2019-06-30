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

var afis_adm = function(url)
{
    var $fEditAf = $('#f-edit-af');

    /* Search AFIS on list */
    $('#search-afis').find('input')
        .keyup(searchKeyUpHandler)
        .click(searchClickHandler);

    /* Show notam list */
    $('.a-show-not, #refresh-not').click(clickBtnNotamHandler);

    /* add AFIS */
    $('#btn-add-af').click(addAfisHandler);

    /* edit AFIS */
    $('.a-edit-af').click(editAfisHandler);

    /* submit AFIS form */
    $fEditAf.on('submit', submitHandler);

    /* delete AFIS */
    $('.a-del-af').click(delAfisHandler);

    $('.af-tooltips').tooltip();

    function searchKeyUpHandler()
    {
        $entree = $(this).val().toLowerCase();
        $tUsrbody.find('tr').each(function() {
            $codeAf = $(this).find('td').first().html().toLowerCase();
            ($codeAf.indexOf($entree)!=-1) ? $(this).show() : $(this).hide();
        });
    }

    function searchClickHandler()
    {
        if($(this).val() == "") $(this).val('LF');
    }


    function addAfisHandler()
    {
        $("#title-edit-af").html("Nouvel AFIS");
        loadAfisForm()
    }

    function editAfisHandler()
    {
        $("#title-edit-af").html("Modifier un AFIS");
        loadAfisForm($(this).data('id'));
    };


    function loadAfisForm(id)
    {
        $fEditAf.load(url + '/afis/form', { id: id }, function() {
            var $code = $fEditAf.find('input[name=code]');
            $code.prop('autocomplete', 'off');
            if(id) $code.prop('disabled', true);

            $code.keyup(keyPressedCodeHandler);
            $.material.checkbox();
        });

        function keyPressedCodeHandler(e)
        {
            function keyIsValid(key) {
                if (key == 8 || (key >= 65 && key <= 90))  return true;
            }

            $(this).val($(this).val().toUpperCase());

            if ($(this).val().length == 4 && keyIsValid(e.which))
            {
                var code = $(this).val();
                noty({
                    text: 'Recherche des informations (horaires et contacts) associées au code donné.',
                    type: 'info',
                    timeout: 3000,
                });

                $.get(url + '/afis/testNotamAccess', accesNotam);

                function accesNotam(data)
                {
                    if(data.accesNotam == 1) {
                        $.get(url + '/afis/getAllNotamFromCode', {code: code}, getFormDataFromNotam);
                    } else {
                        noty({
                            text: 'Impossible d\'accéder aux NOTAM pour extraire les données associées au code '+code,
                            type: 'error',
                            timeout: 4000,
                        });
                     }
                }

                function getFormDataFromNotam(data)
                {
                    var siaNotams = $(data.notams)
                        .find('font.NOTAMBulletin');
                    if (siaNotams.length <= 0)
                    {
                        return false;
                    }

                    $fEditAf.find('input[name=name]').val('');
                    $fEditAf.find('textarea[name=openedhours]').val('');
                    $fEditAf.find('textarea[name=contacts]').val('');

                    // notams.js must be included
                    listNotam = CreateNotamListFromSIA(siaNotams);

                    // extract afis name
                    $fEditAf.find('input[name=name]')
                        .val(listNotam.get(0).getName());

                    // extract afis opened hours
                    $.each(listNotam.findOpenHours(), function()
                    {
                        $fEditAf.find('textarea[name=openedhours]').val(this.getE());
                    });

                    // extract afis contacts
                    $.each(listNotam.getAll(), function(i, notam)
                    {
                        var contacts = notam.getContacts();
                        if(contacts)
                            $fEditAf.find('textarea[name=contacts]').val(contacts);
                    });
                }
            }
        }
    }

    function submitHandler(e)
    {
        e.preventDefault();
        $("#mdl-edit-af").modal('hide');
        $fEditAf.find('input[name=code]').prop('disabled', false);
        $.post(
            url + '/afis/save',
            $('#Afis').serialize(),
            function()
            {
                location.reload();
            },
            'json'
        );
    }


    function delAfisHandler()
    {
        var id = $(this).data('id');
        $('#s-del-af-name').html($(this).data('name'));
        $('#a-del-af-ok').click(function() {
            $("#mdl-del-af").modal('hide');
            $.post(
                url + '/afis/delete',
                { id: id },
                function()
                {
                    location.reload();
                },
                'json'
            );
        });
    };


    function clickBtnNotamHandler(data)
    {
        var code = $(this).data('code');

        $("#title-show-not").html(code + " / NOTAM");
        $('#refresh-not').data('code', code);

        showNotamInElement($('#show-not'), $("#mdl-show-not .loading"),
            code, url + "/afis/testNotamAccess", url + "/afis/getAllNotamFromCode");

    }
};
