var flightplan = function(url, current_date)
{
    "use strict";
    var selectedDate = null;
    var $iDate = $('#i-date');
    var $popTasks = $('.display-fp-tasks');
    var idEvent = 0;
    // pas retrouvé l'utilité
    // headerbar(url);

    // création d'evenement PLN
    $("#create-link").click(editFpHandler);

    // gestion des actions de confirmation
    $('#a-end-fp-ok').click(endFpConfirmationHandler);
    $('#a-reopen-fp-ok').click(reopenFpConfirmationHandler);
    $('#a-end-alt-ok').click(endAltConfirmationHandler);
    $('#a-edit-alt-ok').click(editAltConfirmationHandler);
    $('#a-trig-alt-ok').click(trigAltConfirmationHandler);
    $('#a-reopen-alt-ok').click(reopenAltConfirmationHandler);
    // TODO
    $('.display-fp-tasks').click(function() {
        // hidePopovers();
    })

    // gestion de la selection de la date d'affichage des PLN
    // click sur jour précédent
    $("#a-date-back").click({amount: -1}, changeDaysHandler);
    // click sur jour suivant
    $("#a-date-forward").click({amount: +1}, changeDaysHandler);
    // click sur jour courant
    $("#a-date-today").click(function(e) { location.replace(url + 'flightplans') });
    // affichage des notam
    $('.a-show-not, #refresh-not').click(clickBtnNotamHandler)

    if($('input[name=hide-ended-fp]').prop('checked') == true)
    {
        $('.fp-closed').hide();
    }

    if($('input[name=hide-ended-alt]').prop('checked') == true)
    {
        $('.alt-closed').css('opacity', 0);
    }

    $('input[name=hide-ended-fp]').change(function() {
        if($(this).prop("checked"))
            $('.fp-closed').hide();
        else
            $('.fp-closed').show();
        $.post(url + 'flightplans/toggleFilter', {filter: 'fp', value: $(this).prop("checked")});
    })

    $('input[name=hide-ended-alt]').click(function() {
        if($(this).prop("checked"))
            $('.alt-closed').css('opacity', 0);
        else
            $('.alt-closed').css('opacity', 1);

        $.post(url + 'flightplans/toggleFilter', {filter: 'alt', value: $(this).prop("checked")});
    })

    $.material.checkbox();
    // gestion des tooltips de notes
    $.each($('.show-evt-notes'), function () {
        var data = $(this).data();
        // text += ("cause" in $(this).data) ? $(this).data('cause') : '';
        if (!data.tooltip) return false;

        var text = '';
        var title = (data.title) ? data.title : null;
        var cause = (data.cause) ? data.cause : null;
        var start = (data.start) ? data.start : null;
        var end = (data.end) ? data.end : null;
        text += (title) ? '<h4>' + title + '</h4>' : '';
        text += '<table>'
        text += (start) ? '<tr><td>Début</td><td> :&nbsp;</td><td><strong>' + start + '</strong></td></tr>' : '';
        text += (end) ? '<tr><td>Fin</td><td> :&nbsp;</td><td><strong>' + end + '</strong></td></tr>' : '';
        text += (cause) ? '<tr><td>Cause</td><td> :&nbsp;</td><td><strong>' + cause + '</strong></td></tr>' : '';
        text += '</table><hr /><h4>Notes</h4>';

        text += '<table class="notes-display"><tbody>';
        $.each($(this).data('tooltip').split('$'), function(i, note) {
            if (!note) return false;
            text += "<tr>";
            text += "<td>" + note.split('|')[0] + "</td><td> :&nbsp;</td><td>" + note.split('|')[1] + "</td>";
            text += "</tr>";
        });
        text += "</tbody></table>";

        $(this).tooltip({
            title: '<span class="elmt_tooltip">'+text+'</span>',
            html: 'true',
            placement:'auto',
            template: '<div class="tooltip tooltip-fp" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
        });
    });


    $popTasks
    .popover({
        placement: "left",
        html: true,
        template: '<div class="popover label_elmt" role="tooltip"><div class="arrow"></div><h3 class="popover-title popover-fp-title"></h3><div class="popover-content"></div></div>'
    })
    .on('shown.bs.popover', function() {
        $('.modify-evt').click(editFpHandler);
        $('.a-end-fp').click(endFpHandler);
        $('.a-reopen-fp').click(reopenFpHandler);
        $('.a-hist-fp').click(histFpHandler);
        $('.a-trig-alt').click(trigAlertHandler);
        $('.a-edit-alt').click(editAlertHandler);
        $('.a-end-alt').click(endAltHandler);
        $('.a-reopen-alt').click(reopenAltHandler);
    })

    $('.sortable')
    .sortable()
    .stupidtable()
    .bind('aftertablesort', function(event, data) {
        var th = $(this).find("th");
        th.find(".arrow").remove();
        var arrow = data.direction === "asc" ? "<span class=\"glyphicon glyphicon-arrow-down\"></span>" : "<span class=\"glyphicon glyphicon-arrow-up\"></span>";
        th.eq(data.column).append('<span class="arrow"> ' + arrow +'</span>');
        })
        // par defaut on trie par la première colonne
        .find('th:first').trigger("click");

    $iDate
        // affichage calendrier bootstrap
    .bootstrapMaterialDatePicker({
        format: "DD/MM/YYYY",
        time: false,
        lang: 'fr',
        cancelText: "Annuler",
        weekStart : 1
    })
    .change(function() {
        selectedDate = moment($(this).val(), "L").format("MM/DD/YYYY");
        location.replace(getCurrentDateUrl());
    })
    // par défaut valeur du jour courant
    .val(moment(current_date).format('L'));


    function changeDaysHandler(event)
    {
        event.preventDefault();
        $iDate.val(
            moment($iDate.val(), "L")
                .add(event.data.amount, 'days')
                .format("L")
            )
            .trigger('change');
    }

    function getCurrentDateUrl()
    {
        return url + "flightplans/index?d=" + selectedDate;
    }

    function editFpHandler()
    {
        hidePopovers();
        if($(this).hasClass('modify-evt'))
            $('#form-title').html('Modifier le vol');

        // enlever le champ contenant l'id de l'alerte
        removeAlertField();
        // reload après modification ajax
        setClickSubmit();

        // boucle tant que le champ d'alerte n'existe pas
        function removeAlertField()
        {
            var $alertfield = $("#custom_fields>div.form-group>label")
            .filter(function(){
                    return ($(this).html() == "Alerte :");
                });
            if($alertfield.length > 0) {
                $alertfield.parent().remove();
            }
            else {
                setTimeout(removeAlertField, 200);
            }
        }
        // boucle tant que le bouton de submit n'existe pas
        function setClickSubmit()
        {
            var $sub = $('input[name="submit"]');
            if($sub.length > 0) {
                $sub.click(function(){
                    setTimeout(function() { location.reload() }, 500);
                });
            }
            else {
                setTimeout(setClickSubmit, 200);
            }
        }
    }

    function endFpHandler()
    {
        hidePopovers();
        idEvent = $(this).data('id');
        $('#s-end-airid').html($(this).data('air-id'));
        $('input[name=end-date]')
        .timepickerform({
            'id':'start',
            'clearable':true,
            'init':true
        });
    }

    function endFpConfirmationHandler()
    {
        // date en UTC et contenu dans un string au format YYYY-MM-DDTHH:MM:SS+00:00
        var endDate = moment.utc($('input[name=end-date]').val(), "DD-MM-YYYY HH:mm").format();
        $('#mdl-end-fp').modal('hide');
        $.post(
            url+'flightplans/end',
            {id: idEvent, endDate, fpNote: $('textarea[name=note-end-fp]').val()},
            function (data) {
                $iDate.trigger('change');
            }
            )
    }

    function reopenFpHandler()
    {
        hidePopovers();
        idEvent = $(this).data('id');
    }

    function reopenFpConfirmationHandler()
    {
        $('#mdl-reopen-fp').modal('hide');
        $.post(
            url+'flightplans/reopen',
            {id: idEvent, fpNote: $('textarea[name=note-reopen-fp]').val()},
            function () {
                $iDate.trigger('change');
            }
        );
    }

    function reopenAltHandler()
    {
        hidePopovers();
        idEvent = $(this).data('id');
    }

    function reopenAltConfirmationHandler()
    {
        $('#mdl-reopen-alt').modal('hide');
        $.post(
            url+'flightplans/reopenalt',
            {id: idEvent, altNote: $('textarea[name=note-reopen-alt]').val()},
            function () {
                $iDate.trigger('change');
            }
        );
    }

    function histFpHandler() {
        hidePopovers();
        idEvent = $(this).data('id');
        $('#p-hist-fp').load(url + 'flightplans/hist', { id: idEvent }, function() {});
    }

    function trigAlertHandler()
    {
        hidePopovers();
        idEvent = $(this).data('id');
        $('#f-trig-alt').load(url + 'flightplans/formAlt', { id: idEvent }, function() {});

        return;
        // on ne fait rien si le bouton est inactif (alerte close)
        if ($(this).find('button').hasClass('disabled'))
        return null;

        $('select[name="type-alerte"]').html($(this).data('type'));
        $('.s-trig-airid').html($(this).data('air-id'));
        $('.t-causealt').val($(this).data('cause'));
        idEvent = $(this).data('id');

        if ($(this).hasClass('active-alt'))
            $('#mdl-edit-alt').find('p').hide();
        else
            $('#mdl-edit-alt').find('p').show();
    }

    function trigAltConfirmationHandler()
    {
        $('#mdl-trig-fp').modal('hide');
        $.post(
            url+'flightplans/triggerAlert',
            {
                id: idEvent,
                altType: $('#mdl-trig-fp select[name="alt-type"]').val(),
                altCause: $('#mdl-trig-fp textarea[name="alt-cause"]').val(),
                altNote: $('#mdl-trig-fp textarea[name="alt-note"]').val(),
            },
            function (data) {
                $iDate.trigger('change');
            }
        );
    }

    function editAlertHandler()
    {
        hidePopovers();
        idEvent = $(this).data('id');
        $('#f-edit-alt').load(url + 'flightplans/formAlt', { id: idEvent }, function() {});
    }

    function editAltConfirmationHandler()
    {
        $('#mdl-edit-alt').modal('hide');
        $.post(
            url+'flightplans/triggerAlert', {
            id: idEvent,
                altType: $('#mdl-edit-alt select[name="alt-type"]').val(),
                altCause: $('#mdl-edit-alt textarea[name="alt-cause"]').val(),
                altNote: $('#mdl-edit-alt textarea[name="alt-note"]').val(),
            },
            function (data) {
                $iDate.trigger('change');
            }
        );
    }

    function endAltHandler()
    {
        idEvent = $(this).data('id');
        $('input[name=end-alt-date]')
            .timepickerform({
                'id':'start',
                'clearable':true,
                'init':true
            })
    }

    function endAltConfirmationHandler()
    {
        // date en UTC et contenu dans un string au format YYYY-MM-DDTHH:MM:SS+00:00
        var endAltDate = moment.utc($('input[name=end-alt-date]').val(), "DD-MM-YYYY HH:mm").format();
        $('#mdl-end-alt').modal('hide');
        $.post(
            url+'flightplans/endAlert',
            {
                id: idEvent,
                altNote: $('#mdl-end-alt textarea[name="note-end-alt"]').val(),
                endAltDate
            },
            function (data) {
                $iDate.trigger('change');
            }
        );
    }

    function clickBtnNotamHandler()
    {
        var code = $(this).data('code');

        $("#title-show-not").html(code + " / NOTAM");
        $('#refresh-not').data('code', code);

        showNotamInElement($('#show-not'), $("#mdl-show-not .loading"),
            code, url + "afis/testNotamAccess", url + "afis/getAllNotamFromCode");
    }

    function hidePopovers() {
        $popTasks.popover('hide');
    }
};
