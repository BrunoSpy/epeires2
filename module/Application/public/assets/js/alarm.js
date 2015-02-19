var url;

/*
 *  This file is part of Epeires².
 *  Epeires² is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  Epeires² is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with Epeires².  If not, see <http://www.gnu.org/licenses/>.
 *
 */

var setURLAlarm = function(urlt){
	url = urlt;
};

var flash = function(){
	$("ul#noty_topCenter_layout_container").addClass('animated infinite flash');
	setTimeout(function(){$("ul#noty_topCenter_layout_container").removeClass('animated infinite flash')}, 5000);
};
//stockage des timers
var alarms = new Array();
var alarmsnoty = new Array();
var lastupdate_alarm;
var timerAlarm;
//animation : toutes les 30s, clignoter pendant 5s
var timerAnimation = setInterval(flash, 30000);

var updateAlarms = function(){
	$.getJSON(url+'alarm/getalarms'+(typeof lastupdate_alarm != 'undefined' ? '?lastupdate='+lastupdate_alarm.toUTCString() : ''),
			function(data, textStatus, jqHXR){
		if(jqHXR.status != 304){
			lastupdate_alarm = new Date(jqHXR.getResponseHeader("Last-Modified"));
			$.each(data, function(i, item){
				if(item.status == 3) { //alarme acquittée par ailleurs
					//ne rien faire : il faut la laisser allumée
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
						//par conséquent, on supprime à la main l'élément
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
							//à chaque ajout, réinitialiser le timer des animations
							flash();
							clearInterval(timerAnimation);
							timerAnimation = setInterval(flash, 30000);
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
};

var restoreUpdateAlarms = function(){
	clearTimeout(timerAlarm);
	updateAlarms();
};

var deleteAlarm = function(id){
	clearTimeout(alarms[id]);
};


