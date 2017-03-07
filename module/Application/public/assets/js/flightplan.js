var flightplan = function(url) 
{
    "use strict";
    //TODO voir pour editer en cliquant sur la ligne
    //$('tr').draggable().click(modFpHandler);
    var idEvent = 0;
    var globdate = null;
    var $iDate = $('#i-date');
    var $tableFp = $('.panel-body table');
    //TODO BOF
    var isSar = window.location.href.search('sar');
    if(isSar == -1) isSar = 0; else isSar = 1;

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
        $('.a-trig-alt .a-end-fp .a-end-alt').remove();

        $tableFp.load(url+'flightplans/get', {date: globdate, sar: isSar}, function() 
        {
            var $btnAlts = $(".a-trig-alt");
            // $.each($btnAlts, function() {
            //     if ($(this).hasClass('active-alt')) {
            //         $(this).tooltip();
            //         return false;
            //     }
            // });

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
                $.post(
                    url+'flightplans/endAlert', 
                    {id: $(this).data('id')}, 
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
    

    // function loadFpForm(id = null) 
    // {
    //     $("#f-edit-fp").load(url+'flightplans/form', {id: id,}, function() 
    //     {
    //         $.material.checkbox();
    //         $(this).find('input[name=timeofarrival]')
    //             .timepickerform({
    //                 'id':'start', 
    //                 'clearable':true, 
    //                 'init':true}
    //             );

    //         $(this).find('input[name=estimatedtimeofarrival]')
    //             .timepickerform({
    //                 'id':'end', 
    //                 'clearable':true, 
    //                 'init':true
    //             });

    //         // $(this).find('input[type=submit]')
    //         //     .val('')
    //         //     .append($('<span class="glyphicon glyphicon-plane"></span>'))
    //         //     .click(submitBtnHandler);

    //         var $fGrpComment = $(this).find('textarea[name=comment]').parents('.form-group');

    //         var $fGrpChkbox = $(this).find('input[type=checkbox]').parents('.form-group');

    //         var $sTypeAlert = $(this).find('select[name=typealerte]')
    //             .change(alerteChangeHandler)
    //             .trigger('change');

    //         $.each($fGrpChkbox, function(i, fgrp) {
    //             var label = $(fgrp).find('label').html();
    //             $(fgrp).find('input[type=checkbox]')
    //                 .data({label: label})
    //                 .click(chkBoxClickHandler);
    //         });

    //         var $btnSave = $('<button class="btn btn-primary"><span class="glyphicon glyphicon-floppy-save"></span></button>');
    //         $btnSave.click(submitBtnHandler);

    //         var $mdlFooter = $(this).find('.modal-footer');
    //         $mdlFooter.append($btnSave);

    //         var aircraftid = $('input[name=aircraftid]').val();
            
    //         if(id) {
    //             var $btnDel = $('<button class="btn btn-warning"><span class="glyphicon glyphicon-trash"></span></button>');
    //             $btnDel.click(function(e) {
    //                 e.preventDefault();
    //                 $("#mdl-edit-fp").modal('hide');
    //                 // var id = $(this).data('id');
    //                 $('#s-del-fp-airid').html(aircraftid);
    //                 $('#a-del-fp-ok').click(function(){
    //                     $.post(url+'flightplans/delete', {id: id}, function(){
    //                         location.reload();
    //                     }, 'json');      
    //                 });
    //                 $("#mdl-del-fp").modal('show');
                   
    //             });
    //             $btnDel.appendTo($mdlFooter);
    //         }

    //         function alerteChangeHandler(e) {
    //             if ($(this).val() > 0) { 
    //                 $fGrpComment.show();
    //                 $fGrpChkbox.show();
    //             } else {
    //                 $fGrpComment.hide();
    //                 $fGrpChkbox.hide();
    //             }
    //         }

    //         function chkBoxClickHandler(e) {
    //             var $txtAComment = $fGrpComment.find('textarea[name=comment]')
    //                 .val($(this).data('label'));

    //             $('input[class=form-control]').not(this).prop('checked', false);

    //             if ($(this).prop('checked') == false) {
    //                 $fGrpComment.find('textarea[name=comment]')
    //                     .val('');
    //             }
    //         }

    //         function submitBtnHandler(e) {
    //             e.preventDefault();
    //             $.post(url+'flightplans/save', $("#FlightPlan").serialize(), function(data){
    //                location.reload();
    //             },'json');
    //         }

    //     });
    // };

    // function refreshActionButtons() {
    //     $(".a-edit-fp").click(function() {
    //         $("#title-edit-fp").html('Modification du Plan de Vol - <em>'+$(this).data('aircraft-id')+'</em>');
    //         loadFpForm($(this).data('id'));
    //     });

    //     $(".a-del-fp").click(function() {
    //         var id = $(this).data('id');
    //         $('#s-del-fp-airid').html($(this).data('aircraft-id'));
    //         $('#a-del-fp-ok').click(function(){
    //             $.post(url+'flightplans/delete', {id: id}, function(){
    //                 location.reload();
    //             }, 'json');      
    //         });
    //     });
    // }
};