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

};