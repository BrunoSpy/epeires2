var url;

var setURLAlarm = function(urlt){
	url = urlt;
}

//stockage des timers
var alarms = new Array();
var alarmsnoty = new Array();
var lastupdate_alarm;
var timerAlarm;
var updateAlarms = function(){
        $.getJSON(url+'alarm/getalarms'+(typeof lastupdate_alarm != 'undefined' ? '?lastupdate='+lastupdate_alarm.toUTCString() : ''),
            function(data, textStatus, jqHXR){
		if(jqHXR.status != 304){
                        lastupdate_alarm = new Date(jqHXR.getResponseHeader("Last-Modified"));
			$.each(data, function(i, item){
				if(item.status == 3) { //alarme acquittée par ailleurs
					//si l'alarme est ouverte et acquittée, on la ferme
					//if(alarmsnoty[item.id]){
					//	alarmsnoty[item.id].close();
					//	delete(alarmsnoty[item.id]);
					//}
                                        //NON : ne pas fermer automatiquement les alarmes acquittées sur d'autres postes
				} else {
					//si l'alarme existe déjà, on l'annule
					if(alarms[item.id]){
						clearTimeout(alarms[item.id]);
						delete(alarms[item.id]);
					}
                                        //si l'alarme est affichée, on la ferme : 
                                        // * pour éviter les doublons 
                                        // * pour enlever les alarmes annulées
                                        if(alarmsnoty[item.id]){
                                            //on ne peut pas utiliser .close à cause du callback
                                            //par conséquent, on supprimer à la main l'élément
                                            $('div#alarmnoty-'+item.id).closest('li').remove();
                                            delete(alarmsnoty[item.id]);
                                        }
                                        //on ajoute l'alarme si statut nouveau ou en cours
                                        if(item.status == 1 || item.status == 2) {
                                            var delta = new Date(item.datetime) - new Date(); //durée avant l'alarme
                                            var timer = setTimeout(function(){
						alarmsnoty[item.id] = noty({
							text:item.text,
							type:'error',
							layout:'topMiddleCenter',
							timeout:false,
							callback: {
								onClose: function(){
									$.post(url+'alarm/confirm?id='+item.id, function(data){displayMessages(data);});
									delete(alarmsnoty[item.id]);
								}
							}
						});
                                            }, delta);
                                            alarms[item.id] = timer;
                                        }
				}

			});
		}
	}).always(function(){
		timerAlarm = setTimeout(updateAlarms, 10000);
	});
};

var pauseUpdateAlarms = function(){
	clearTimeout(timerAlarm);
}

var restoreUpdateAlarms = function(){
	clearTimeout(timerAlarm);
	updateAlarms();
}

var deleteAlarm = function(id){
	clearTimeout(alarms[id]);
};



