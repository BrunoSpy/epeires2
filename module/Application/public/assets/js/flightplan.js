var flightplan = function(url){
    
    var $iDate = $('#i-date');
    //TODO BOF
    var isSar = window.location.href.search('sar');
    if(isSar == -1) isSar = 0; else isSar = 1;

    refreshActionButtons();

    $("#btn-add-fp").click(function() {
        $("#title-edit-fp").html("Suivi du Vol");
        loadFpForm();
    });

    $iDate
        .bootstrapMaterialDatePicker({
            format: "DD/MM/YYYY",
            time: false,
            lang: 'fr',
            cancelText: "Annuler",
            weekStart : 1
        })
        .change(function() {
            var date = moment($(this).val(), "DD/MM/YYYY").format("MM/DD/YYYY");
            $("#list-fp").load(url+'flightplans/list', {date: date, sar: isSar}, function() {
                refreshActionButtons();
            });
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
    
    function loadFpForm(id = null) 
    {
        $("#f-edit-fp").load(url+'flightplans/form', {id: id,}, function() 
        {
            $.material.checkbox();
            $(this).find('input[name=timeofarrival]')
                .timepickerform({
                    'id':'start', 
                    'clearable':true, 
                    'init':true}
                );

            $(this).find('input[name=estimatedtimeofarrival]')
                .timepickerform({
                    'id':'end', 
                    'clearable':true, 
                    'init':true
                });

            $(this).find('input[type=submit]').click(submitBtnHandler);

            $fGrpComment = $(this).find('textarea[name=comment]').parents('.form-group');

            $fGrpChkbox = $(this).find('input[type=checkbox]').parents('.form-group');

            $sTypeAlert = $(this).find('select[name=typealerte]')
                .change(alerteChangeHandler)
                .trigger('change');

            $.each($fGrpChkbox, function(i, fgrp) {
                label = $(fgrp).find('label').html();
                $(fgrp).find('input[type=checkbox]')
                    .data({label: label})
                    .click(chkBoxClickHandler);
            });

            function alerteChangeHandler(e) {
                if ($(this).val() > 0) { 
                    $fGrpComment.show();
                    $fGrpChkbox.show();
                } else {
                    $fGrpComment.hide();
                    $fGrpChkbox.hide();
                }
            }

            function chkBoxClickHandler(e) {
                var $txtAComment = $fGrpComment.find('textarea[name=comment]')
                    .val($(this).data('label'));

                $('input[class=form-control]').not(this).prop('checked', false);

                if ($(this).prop('checked') == false) {
                    $fGrpComment.find('textarea[name=comment]')
                        .val('');
                }
            }

            function submitBtnHandler(e) {
                e.preventDefault();
                $.post(url+'flightplans/save', $("#FlightPlan").serialize(), function(data){
                   location.reload();
                },'json');
            }

        });
    };

    function refreshActionButtons() {
        $(".a-edit-fp").click(function() {
            $("#title-edit-fp").html('Modification du Plan de Vol - <em>'+$(this).data('aircraft-id')+'</em>');
            loadFpForm($(this).data('id'));
        });

        $(".a-del-fp").click(function() {
            var id = $(this).data('id');
            $('#s-del-fp-airid').html($(this).data('aircraft-id'));
            $('#a-del-fp-ok').click(function(){
                $.post(url+'flightplans/delete', {id: id}, function(){
                    location.reload();
                }, 'json');      
            });
        });
    }
};