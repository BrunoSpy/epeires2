var flightplan = function(url, current_date) 
{
    "use strict";

    // pas retrouvé l'utilité
    //headerbar(url);

    /*** 
     * Gestion de la selection de la date d'affichage des PLN
     */
    var selectedDate = null;
    var $iDate = $('#i-date');
    $iDate
        // affichage calendrier bootstrap
        .bootstrapMaterialDatePicker({
            format: "DD/MM/YYYY",
            time: false,
            lang: 'fr',
            cancelText: "Annuler",
            weekStart : 1
        })
        // action quand la date est modifiée
        .change(function() {
            selectedDate = moment($(this).val(), "L").format("MM/DD/YYYY");
            location.replace(getCurrentDateUrl());
        })
        // par défaut valeur du jour courant
        .val(moment(current_date).format('L'));

    // click sur jour précédent
    $("#a-date-back").click(function(e) 
    {
        e.preventDefault();
        changeDays(-1)
    });
    // click sur jour suivant
    $("#a-date-forward").click(function(e) 
    {
        e.preventDefault();
        changeDays(1);
    });
    // click sur jour courant
    $("#a-date-today").click(function(e) 
    {
        e.preventDefault();
        location.reload();
    });
    function changeDays(amount)
    {
        $iDate.val(
            moment($iDate.val(), "L")
                .add(amount, 'days')
                .format("L")
            )
            .trigger('change');
    };

    function getCurrentDateUrl() 
    {
        return url + "flightplans/index?d=" + selectedDate;
    }

    /***
     * Gestion des actions utilisateur
     */

    // click sur ajouter un PLN ou modifier un PLN
    $("#create-link, .modify-evt").click(function() 
    {
        if($(this).hasClass('modify-evt')) 
            $('#form-title').html('Modifier le vol');

        // enlever le champ contenant l'id de l'alerte
        removeAlertField();
        // Pas retrouvé l'utilité ...
        //setClickSubmit();
    });

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

    function setClickSubmit() {
        var $sub = $('input[name="submit"]');
        if($sub.length > 0) {
            $sub.click(function(){
                setTimeout(refresh, 1000);
            });
        }   
        else {
            setTimeout(setClickSubmit, 200);
        }
    }

    //  
    var idEvent = 0;
    $('#p-show-fp')
        .on('click', '.a-trig-alt', trigAlertHandler)
        .on('click', '.a-end-fp', endFpHandler)
        .on('click', '.a-end-alt', endAltHandler)

    function endFpHandler() {
        idEvent = $(this).data('id');
        $('#s-end-airid').html($(this).data('air-id'));
        $('input[name=end-date]')
            .timepickerform({
                'id':'start',
                'clearable':true,
                'init':true
            });
    }

    function trigAlertHandler() {
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

    function endAltHandler() {
        idEvent = $(this).data('id');
        $('input[name=end-alt-date]')
            .timepickerform({
                'id':'start',
                'clearable':true,
                'init':true
            })
        ;
    }

    $('#a-end-fp-ok').click(function()
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
    });

    $('#a-end-alt-ok').click(function()
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
    });   

    $('#a-edit-alt-ok').click(function() {
        $('#mdl-edit-alt').modal('hide');
        $.post(
            url+'flightplans/triggerAlert', 
            {id: idEvent, type: $('#mdl-edit-alt .s-trig-alt').html(), cause: $('#mdl-edit-alt .t-causealt').val()}, 
            function (data) {
                $iDate.trigger('change');
            }
        );      
    });

    $('#a-trig-alt-ok').click(function() {
        $('#mdl-trig-fp').modal('hide');
        $.post(
            url+'flightplans/triggerAlert', 
            {id: idEvent, type: $('#mdl-trig-fp .s-trig-alt').html(), cause: $('#mdl-trig-fp .t-causealt').val()}, 
            function (data) {
                $iDate.trigger('change');
            }
        );      
    }); 

    //refresh(); 
    
    function refresh() {
        // maj du nombre d'événements actif visible sur l'onglet
        // Inutile ?
        // $('.a-trig-alt .a-end-fp .a-end-alt').remove();
        // Conteneur des pln
        
        
        
        
        
        ;
        // s'applique apres le chargement en ajax du conteneur
        function loadFpHandler () {
            $('.active-alt').tooltip();
            $('.sortable').stupidtable();
            $('.sortable').bind('aftertablesort', function(event, data){
                var th = $(this).find("th");
                th.find(".arrow").remove();
                var arrow = data.direction === "asc" ? "<span class=\"glyphicon glyphicon-arrow-down\"></span>" : "<span class=\"glyphicon glyphicon-arrow-up\"></span>";
                th.eq(data.column).append('<span class="arrow"> ' + arrow +'</span>');
            });

        }
        // clique sur un des 3 boutons de déclenchement d'alerte

    }

};