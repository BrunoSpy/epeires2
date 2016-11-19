var flightplan = function(url){
/*
 *  Clique sur le bouton Ajouter.
 */       
    $("#btn-add-fp").click(function() {
        $("#title-edit-fp").html("Suivi du Vol");
        loadFpForm();
    });
/*
 *  Clique sur modifier d'un flight plan
 */  
    $(".a-edit-fp").click(function() {
        $("#title-edit-fp").html('Modification du Plan de Vol - <em>'+$(this).data('aircraft-id')+'</em>');
        loadFpForm($(this).data('id'));
    });

    function loadFpForm(id = null) {
        console.log(id);
        $("#f-edit-fp").load(url+'flightplans/form', {id: id}, function(e) {
            $(this).find('input[name=timeofarrival]').timepickerform({'id':'start', 'clearable':true});
            $(this).find('input[name=estimatedtimeofarrival]').timepickerform({'id':'end', 'clearable':true});
             /*
              * Clique sur le bouton Enregistrer.
              * Envoie du formulaire serializé au contrôleur save puis rechargement.
              */      
            $(this).find('input[type=submit]').click(function(e) {
                e.preventDefault();
                $.post(url+'flightplans/save',$("#FlightPlan").serialize(),function(data){
                   location.reload();
                },'json');
            });
        });
    };
    
    /*
     * Supprimer
     */
    $(".a-del-fp").click(function() {
        var id = $(this).data('id');
        $('#s-del-fp-airid').html($(this).data('aircraft-id'));
        $('#a-del-fp-ok').click(function(){
            $.post(url+'flightplans/delete', {id: id}, function(){
                location.reload();
            }, 'json');      
        });
    });
    
    $("#fp-cont-del-ok").on('click', function (){
        $.post(url+'/delete', {fpid:$(this).data('fp-id')}, function(data){
           location.reload();
        }, 'json');
    });

    /*
     * Alertes
     */
    $(".fp-a-alt").on('click', function(){
        $(".fp-cont-alt-type").html($(this).data('fp-alt-type'));
        $("#fp-cont-alt-airid").html($(this).data('fp-aircraft-id'));
        $("#fp-cont-alt-ok").data($(this).data());
    });

    $("#fp-cont-alt-ok").on('click', function (){
        $.post(url+'/trigger', {fpid:$(this).data('fp-id'),type:$(this).data('fp-alt-type')}, function(data){
            location.href = '/events';
        }, 'json');
    });
    /*
    TODO
    A factoriser / revoir completement
     */
    $("#fp-day").bootstrapMaterialDatePicker({
        format: "DD/MM/YYYY",
        time: false,
        lang: 'fr',
        cancelText: "Annuler",
        weekStart : 1
    });

    $("#fp-day").on('change', function(){
        var temp = $('#chx-fp-day input[type=text].date').val().split('/');
        //var date = new Date(temp[2], temp[1] - 1, temp[0], "5");
        location.href = '/flightplans?date=' + temp;
    });

    function FormatNumberLength(num, length) {
        var r = "" + num;
        while (r.length < length) {
            r = "0" + r;
        }
        return r;
    }

    function dateToStr(date) {
        return FormatNumberLength(date.getDate(), 2) + "/" + FormatNumberLength(date.getMonth(), 2) + "/" + FormatNumberLength(date.getFullYear(), 4);
    }

    var getUrlParameter = function getUrlParameter(sParam) {
        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    };

    var strDate = getUrlParameter('date');
    if(null == strDate) var date=new Date();
    else {
        var arrayDate = strDate.split(",");
        var date = new Date(arrayDate[2], arrayDate[1], arrayDate[0]);
    }

    $("#chx-fp-day input[type=text].date").val(dateToStr(date));
    $("#fp-day-back").on('click', function(e) {
        e.preventDefault();
        var temp = $('#chx-fp-day input[type=text].date').val().split('/');
        var date = new Date(temp[2], temp[1], temp[0], "5");
        var back = new Date(date.getTime() - (24 * 60 * 60 * 1000));
        $("#chx-fp-day input[type=text].date").val(dateToStr(back));
        $("#chx-fp-day input[type=text].date").trigger('change');
    });

    $("#fp-day-forward").on('click', function(e) {
        e.preventDefault();
        var temp = $('#chx-fp-day input[type=text].date').val().split('/');
        var date = new Date(temp[2], temp[1], temp[0], "5");
        var forward = new Date(date.getTime() + (24 * 60 * 60 * 1000));
        $("#chx-fp-day input[type=text].date").val(dateToStr(forward));
        $("#chx-fp-day input[type=text].date").trigger('change');
    });

    $("#fp-day-today").on('click', function(e) {
        e.preventDefault();
        location.href = 'flightplans';
    });
};