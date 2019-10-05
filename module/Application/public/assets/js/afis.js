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

    $.each($('.af-tooltip'), function() {
        $(this).tooltip({
            title: '<span class="elmt_tooltip">'+ $(this).data('tooltip') +'</span>',
            html: 'true',
            placement:'auto',
            template: '<div class="tooltip tooltip-af" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
        });
    });

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
        // $('span.glyphicon').tooltip();
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
};
