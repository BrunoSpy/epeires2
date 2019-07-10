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

var afis = function(url)
{
    $('#search-afis').find('input')
        .keyup(searchKeyUpHandler)
        .click(searchClickHandler);

    $('.btn-switch-af').change(switchAfisHandler);

    $('.a-show-not, #refresh-not').click(clickBtnNotamHandler);

    function switchAfisHandler(data)
    {
        var boolState = 0;
        if ($(this).is(':checked')) {
            boolState = 1;
        }

        $.post(url + 'afis/switchafis', { id: $(this).data('id'), state: boolState }, switched, 'json');

        function switched(data) {
            noty({
                text: data.msg,
                type: data.type,
                timeout: 4000,
            });
            headerbar(url);
        }

        $('span.glyphicon').tooltip();
        $.material.togglebutton();
    }

    function searchClickHandler() {
        if($(this).val() == "") $(this).val('LF');
    }

    function searchKeyUpHandler() {
        $entree = $(this).val().toLowerCase();
        $tUsrbody.find('tr').each(function() {
            $codeAf = $(this).find('td').first().html().toLowerCase();
            ($codeAf.indexOf($entree)!=-1) ? $(this).show() : $(this).hide();
        });
    }

    function clickBtnNotamHandler()
    {
        var code = $(this).data('code');

        $("#title-show-not").html(code + " / NOTAM");
        $('#refresh-not').data('code', code);

        showNotamInElement($('#show-not'), $("#mdl-show-not .loading"),
            code, url + "afis/testNotamAccess", url + "afis/getAllNotamFromCode");
    }



       function refresh()
    {
        $('.btn-switch-af .a-edit-af .a-del-af').remove();
        if ($tUsrbody.length > 0) {
            $tUsrbody.load(url + 'afis/get', { decomissionned: 0, admin: 0 }, setUsrBtn);
        }

        if ($tAdmbodies.length > 0) {
            $tAdmbodies.eq(0).load(url + 'afis/get', { decomissionned: 0, admin: 1 }, setAdmBtn);
            $tAdmbodies.eq(1).load(url + 'afis/get', { decomissionned: 1, admin: 1 }, setAdmBtn);
        }

        function setUsrBtn()
        {
            $('.btn-switch-af').change(function()
            {
                var boolState = 0;
                if ($(this).is(':checked')) {
                    boolState = 1;
                }

                $.post(url + 'afis/switchafis', { id: $(this).data('id'), state: boolState }, switched, 'json');

                function switched(data) {
                    noty({
                        text: data.msg,
                        type: data.type,
                        timeout: 4000,
                    });
                    headerbar(url);
                }
            });

            $tUsrbody.find('span.glyphicon').tooltip();
            $.material.togglebutton();
            setNotamBtn($(this));
        }

        function setAdmBtn()
        {
            $(this).find('.a-edit-af').unbind('click').click(function() {
                $("#title-edit-af").html("Modifier un AFIS");
                loadAfisForm($(this).data('id'));
            });

            $(this).find('.a-del-af').unbind('click').click(function() {
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
            $tAdmbodies.find('span.glyphicon').tooltip();
            setNotamBtn($(this));
        }

        function setNotamBtn($obj)
        {
            $.get(url + 'afis/testNotam', function(data) {
                if (data.accesNotam == 1) {
                    $obj.find('.btn-notam')
                        .removeClass('disabled btn-warning')
                        .prop('disabled', false)
                        .addClass('btn-primary');
                }
            });
            $obj.find('.a-show-not').click(clickBtnNotamHandler);
        }
    }

    function keyIsValid(key) {
        if (key == 8 || (key >= 65 && key <= 90))  return true;
    }
};
