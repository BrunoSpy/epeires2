var flightplan = function(url, current_date) 
{
    "use strict";
    var selectedDate = null;
    var $iDate = $('#i-date');
    var idEvent = 0;
    // pas retrouvé l'utilité
    // headerbar(url);

    // modification/création d'evenement PLN
    $("#create-link, .modify-evt").click(editFpHandler);

    // gestion des boutons d'actions sur PLN
    $('#pan-show-fp')
        .on('click', '.a-trig-alt', trigAlertHandler)
        .on('click', '.a-end-fp', endFpHandler)
        .on('click', '.a-end-alt', endAltHandler)
    
    // gestion des actions de confirmation
    $('#a-end-fp-ok').click(endFpConfirmationHandler);
    $('#a-end-alt-ok').click(endAltConfirmationHandler);
    $('#a-edit-alt-ok').click(editAltConfirmationHandler);
    $('#a-trig-alt-ok').click(trigAltConfirmationHandler);
    
    // gestion de la selection de la date d'affichage des PLN
    // click sur jour précédent
    $("#a-date-back").click({amount: -1}, changeDaysHandler);
    // click sur jour suivant
    $("#a-date-forward").click({amount: +1}, changeDaysHandler);
    // click sur jour courant
    $("#a-date-today").click(function(e) { location.replace(url + 'flightplans') });
    
    
    // gestion des <table>
    $('.active-alt').tooltip();
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
    };
    
    function endFpHandler() 
    {
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
            {id: idEvent, endDate},
            function (data) {
                $iDate.trigger('change');
            }
            )
        }
        
    function trigAlertHandler() 
    {
        // on ne fait rien si le bouton est inactif (alerte close)
        if ($(this).find('button').hasClass('disabled'))
        return null;
        
        $('.s-trig-alt').html($(this).data('type'));
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
            {id: idEvent, type: $('#mdl-trig-fp .s-trig-alt').html(), cause: $('#mdl-trig-fp .t-causealt').val()}, 
            function (data) {
                $iDate.trigger('change');
            }
        );      
    }

    function editAltConfirmationHandler()
    {
        $('#mdl-edit-alt').modal('hide');
        $.post(
            url+'flightplans/triggerAlert', 
            {id: idEvent, type: $('#mdl-edit-alt .s-trig-alt').html(), cause: $('#mdl-edit-alt .t-causealt').val()}, 
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
            {id: idEvent, endAltDate},
            function (data) {
                $iDate.trigger('change');
            }
        );
    }  
};