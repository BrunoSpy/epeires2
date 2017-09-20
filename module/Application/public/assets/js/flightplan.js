var flightplan = function(url) 
{
    "use strict";
    $("#create-link").click(function() {
        removeAlertField();
        setClickSubmit();
    });

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
    //TODO voir pour editer en cliquant sur la ligne
    //$('tr').draggable().click(modFpHandler);
    var idEvent = 0;
    var globdate = null;
    var $iDate = $('#i-date');
    var $tableFp = $('.panel-body table');

    $('#a-end-fp-ok').click(function()
    {
        $('#mdl-end-fp').modal('hide');
        $.post(
            url+'flightplans/end', 
            {id: idEvent, end_date: $('input[name=end-date]').val()}, 
            function (data) {
                refresh();
                noty({
                    text: data.msg,
                    type: data.type,
                    timeout: 4000,
                });
            }
        )
    });

    $('#a-end-alt-ok').click(function()
    {
        $('#mdl-end-alt').modal('hide');
        $.post(
            url+'flightplans/endAlert', 
            {id: idEvent, end_date: $('input[name=end-date]').val()}, 
            function (data) {
                refresh();
                noty({
                    text: data.msg,
                    type: data.type,
                    timeout: 4000,
                });
            }
        );
    });   


    $('#a-trig-alt-ok').click(function() {
        $('#mdl-trig-fp').modal('hide');
        $.post(
            url+'flightplans/triggerAlert', 
            {id: idEvent, type: $('#s-trig-alt').html(), cause: $('#t-causealt').val()}, 
            function (data) {
                refresh();
                noty({
                    text: data.msg,
                    type: data.type,
                    timeout: 4000,
                });
            }
        );      
    }); 

    refresh(); 
    
    function refresh() 
    {
        headerbar(url);
        $('.a-trig-alt .a-end-fp .a-end-alt').remove();

        $('.sar').load(url+'flightplans/get', {date: globdate, sar:1}, function() {
            $('.nosar').load(url+'flightplans/get', {date: globdate, sar:0}, function() {
                var $btnAlts = $(".a-trig-alt");

                $btnAlts.click(function() {
                    $('#s-trig-alt').html($(this).data('type'));
                    $('#s-trig-airid').html($(this).data('air-id'));
                    $('#t-causealt').val($(this).data('original-title'));
                    idEvent = $(this).data('id');
                }).tooltip();

                $('.a-end-fp').click(function() 
                {
                    idEvent = $(this).data('id');
                    $('#s-end-airid').html($(this).data('air-id'));
                    $('input[name=end-date]')
                        .timepickerform({
                            'id':'start', 
                            'clearable':true, 
                            'init':true
                        });    
                });

                $('.a-end-alt').click(function() {
                    idEvent = $(this).data('id');
                    $('input[name=end-date]')
                        .timepickerform({
                            'id':'start', 
                            'clearable':true, 
                            'init':true
                        });    
                });
            });
        });    
    }

    $iDate
        .bootstrapMaterialDatePicker({
            format: "DD/MM/YYYY",
            time: false,
            lang: 'fr',
            cancelText: "Annuler",
            weekStart : 1
        })
        .change(function() {
            globdate = moment($(this).val(), "DD/MM/YYYY").format("MM/DD/YYYY");
            refresh();
        })
        .val(moment().format('DD/MM/YYYY'));

    $("#a-date-back").click(function(e) {
        e.preventDefault();
        $iDate
            .val(
                moment($iDate.val(), "DD/MM/YYYY")
                .subtract(1, 'days')
                .format("DD/MM/YYYY")
            )
            .trigger('change');
    });

    $("#a-date-forward").click(function(e) {
        e.preventDefault();
        $iDate
            .val(
                moment($iDate.val(), "DD/MM/YYYY")
                .add(1, 'days')
                .format("DD/MM/YYYY")
            )
            .trigger('change');
    });

    $("#a-date-today").click(function(e) {
        e.preventDefault();
        location.reload();
    });
    
};