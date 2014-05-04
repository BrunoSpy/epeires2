var url;

var setURLAlarm = function(urlt){
	url = urlt;
}

//stockage des timers
var alarms = new Array();
var alarmsnoty = new Array();
var lastupdate;
var timerAlarm;
var updateAlarms = function(){
	$.getJSON(url+'alarm/getalarms'+(typeof lastupdate != 'undefined' ? '?lastupdate='+lastupdate.toUTCString() : ''), function(data, textStatus, jqHXR){
		lastupdate = new Date();
		if(jqHXR.status != 304){
			$.each(data, function(i, item){
				if(item.status == 3) { //alarme acquittée par ailleurs
					//si l'alarme est ouverte et acquittée, on la ferme
					if(alarmsnoty[item.id]){
						alarmsnoty[item.id].close();
						delete(alarmsnoty[item.id]);
					}
				} else {
					//si l'alarme existe déjà, on l'annule
					if(alarms[item.id]){
						clearTimeout(alarms[item.id]);
						delete(alarms[item.id]);
					}

					var delta = new Date(item.datetime) - new Date(); //durée avant l'alarme
					var timer = setTimeout(function(){
						alarmsnoty[item.id] = noty({
							text:item.text,
							type:'warning',
							layout:'topCenter',
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

			});
		}
	}).always(function(){
		timerAlarm = setTimeout(updateAlarms, 50000);
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



