var url;

var setURLAlarm = function(urlt){
	url = urlt;
}

//stockage des timers
var alarms = new Array();
var lastupdate;
var timerAlarm;
var updateAlarms = function(){
	$.getJSON(url+'alarm/getalarms?lastupdate='+lastupdate, function(data){
		lastupdate = new Date();
		$.each(data, function(i, item){
			//si l'alarme existe déjà, on l'annule
			if(alarms[item.id]){
				clearTimeout(alarms[item.id]);
			}
			var delta = new Date(item.datetime) - new Date(); //durée avant l'alarme
			var timer = setTimeout(function(){
				var n = noty({
					text:item.text,
					type:'warning',
					layout:'topCenter',
					timeout:false,
					callback: {
						onClose: function(){
							$.post(url+'alarm/confirm?id='+item.id, function(data){displayMessages(data);});
						}
					}
				});
			}, delta);
			alarms[item.id] = timer;
		});
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



