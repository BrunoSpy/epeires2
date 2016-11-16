var flightplan=function(url){
/*
 *  Clique sur le bouton Ajouter.
 *  Chargement du formulaire. Puis transformation des saisies de temps avec timepicker.
 */       
    $("#fp-add").on('click',function(){
        $("#fp-title").html("Suivi du Vol");
        $("#fp-form").load(url+'/form',function(e){
            $("#fp-cont-form input[name=timeofarrival]").timepickerform({'id':'start', 'clearable':true});
            $("#fp-cont-form input[name=estimatedtimeofarrival]").timepickerform({'id':'end', 'clearable':true});
        });
    });
/*
 *  Clique sur modifier d'un flight plan
 *  Chargement du formulaire avec les données du flight plan. Puis transformation des saisies de temps avec timepicker.
 */  
    $(".fp-a-edit").on('click',function(){
        $("#fp-title").html('Modification du Plan de Vol - <em>'+$(this).data('fp-aircraft-id')+'</em>');
        $("#fp-form").load(url+'/form', {fpid:$(this).data('fp-id')},function(){
            $("#fp-cont-form input[name=timeofarrival]").timepickerform({'id':'start', 'clearable':true});
            $("#fp-cont-form input[name=estimatedtimeofarrival]").timepickerform({'id':'end', 'clearable':true});
        });
    });
 /*
  * Clique sur le bouton Enregistrer.
  * Envoie du formulaire serializé au contrôleur save puis rechargement.
  */      
    $("#fp-cont-form").on('click','input[type=submit]',function(event){
        event.preventDefault();
        $.post(url+'/save',$("#FlightPlan").serialize(),function(data){
            location.reload();
        },'json');
    });
    
    /*
     * Supprimer
     */
    $(".fp-a-del").on('click', function (){
        $("#fp-cont-del-airid").html($(this).data('fp-aircraft-id'));
        $("#fp-cont-del-ok").data('fp-id', $(this).data('fp-id'));
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