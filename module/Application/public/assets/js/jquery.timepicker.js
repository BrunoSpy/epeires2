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
 */

/**
 * JQuery Plugin to transform a simple datetime input into a functional datetime picker
 * Requires jquery-ui-datepicker
 * Licence : AGPL
 * @author Bruno Spyckerelle
 * @version 2.0
 */

(function($) {

    $.fn.timepickerform = function(options) {

        var dayplusone = function(input, delta){
            if(input.val()){
                var daystring = input.val().split('-');
                var day = new Date(daystring[2], daystring[1]-1, daystring[0], 12);
                var newday = new Date();
                newday.setDate(day.getDate() + delta);
                var newdaystring = newday.getUTCDate()+'-'+(newday.getUTCMonth()+1)+'-'+newday.getUTCFullYear();
            } else {
                var d = new Date();
                newdaystring = d.getUTCDate()+'-'+(d.getUTCMonth()+1)+'-'+d.getUTCFullYear();
            }
            return newdaystring;
        };

        var hourplusone = function(input, delta) {
            if (input.val()) {
                var hour = parseInt(input.val()) + delta;
                if (hour >= 0 && hour <= 9)
                    hour = "0" + hour;
                if (hour < 0)
                    hour = 23;
                if (hour > 23)
                    hour = "00";
            } else {
                var d = new Date();
                hour = d.getUTCHours();
                if (hour >= 0 && hour <= 9) {
                    hour = "0" + hour;
                }
            }
            return hour;
        };

        var minuteplusone = function(input, delta) {
            if (input.val()) {
                var minutes = parseInt(input.val()) + delta;
                if (minutes >= 0 && minutes <= 9)
                    minutes = "0" + minutes;
                if (minutes < 0)
                    minutes = 59;
                if (minutes > 59)
                    minutes = "00";
            } else {
                var d = new Date();
                minutes = d.getUTCMinutes();
                if (minutes >= 0 && minutes <= 9) {
                    minutes = "0" + minutes;
                }
            }
            return minutes;
        };

        var updateFakeForm = function(fakediv) {
            var day = "";
            if (!fakediv.find('.day input').val()) {
                var d = new Date();
                day = d.getUTCDate() + "-" + (d.getUTCMonth() + 1) + "-" + d.getUTCFullYear();
                fakediv.find('.day input').val(day);
            }
            if (!fakediv.find('.hour input').val()) {
                var hour = "00";
                if ($(this).attr('end')) {
                    hour = $("#start .hour input").val();
                } else {
                    var d = new Date();
                    hour = d.getUTCHours();
                    if (hour >= 0 && hour <= 9) {
                        hour = "0" + hour;
                    }
                }
                fakediv.find('.hour input').val(hour);
            }
            if (!fakediv.find('.minute input').val()) {
                var minutes = "00";
                if ($(this).attr('end')) {
                    minutes = $("#start .minute input").val();
                } else {
                    var d = new Date();
                    minutes = d.getUTCMinutes();
                    if (minutes >= 0 && minutes <= 9) {
                        minutes = "0" + minutes;
                    }
                }
                fakediv.find('.minute input').val(minutes);
            }
        };

        var createFakeForm = function() {
            var div = $("<div class=\"timepicker-form\" id=" + parameters.id + "></div>");
            var table = $("<table></table>");
            table.append('<tbody>' +
                '<tr>' +
                '<td class="day">' +
                "<div class=\"input-group\">" +
                "<span class=\"input-group-addon\"><span class=\"glyphicon glyphicon-calendar\"></span></span>" +
                '<input type="text" class="date form-control" ' + (parameters.required ? 'required="required"' : '') + '></input>' +
                "</div>" +
                '</td>' +
                '<td class="hour">' +
                '<a class="next" href="#"><span class="glyphicon glyphicon-chevron-up"></span></a>' +
                '<input maxlength="2" type="text" class="form-control input-mini">' +
                '<a class="previous" href="#"><span class="glyphicon glyphicon-chevron-down"></span></a>' +
                '</td>' +
                '<td class="separator">:</td>' +
                '<td class="minute">' +
                '<a class="next" href="#"><span class="glyphicon glyphicon-chevron-up"></span></a>' +
                '<input maxlength="2" type="text" class="form-control input-mini">' +
                '<a class="previous" href="#"><span class="glyphicon glyphicon-chevron-down"></span></a>' +
                '</td>' +
                '<td>'+
                (parameters.sunrise ? '<a href="#" class="sunrise" title="Heure du lever du soleil"><span class="glyphicon glyphicon-eye-open"></span></a>' : '')+
                (parameters.sunset ? '<a href="#" class="sunset" title="Heure du coucher du soleil"><span class="glyphicon glyphicon-eye-close"></span></a>' : '')+
                '</td>'+
                '<td>'+
                (parameters.clearable ? '<a href="#" class="clear-time"><span class="glyphicon glyphicon-remove"></span></a>' : '') +
                '</td>' +
                '</tr>' +
                '</tbody>');
            div.append(table);
            return div;
        };

        /*
        Return the day with YYYY-MM-DD format
         */
        var getDay = function(element) {
            var day = element.find('.day input').val().split('-');
            return day[2]+'-'+day[1]+'-'+day[0];
        };

        var defaults = {
            'id': '',
            'required': false,
            'clearable': false,
            'init': false,
            'sunrise': false,
            'sunset': false,
            'api_sunrise_url': ''
        };

        var parameters = $.extend(defaults, options);

        return this.each(function() {
            var element = $(this);
            if (element.is('input[type=datetime]')) {
                //change the type of the field to hidden
                element.prop('type', 'hidden');
                //add the div containing timepicker fake form
                var div = createFakeForm();
                element.parent().append(div);

                //add datepicker
                $('input[type=text].date').bootstrapMaterialDatePicker({
                    format: "DD-MM-YYYY",
                    time: false,
                    lang: 'fr',
                    cancelText: "Annuler",
                    weekStart : 1,
                    switchOnClick: true,
                    nowButton: true,
                    nowText: "Ce jour"
                });

                div.on('click', 'span.glyphicon-calendar', function(e){
                    div.find('input[type=text].date').trigger('focus');
                });

                //init fields if original field contains a date
                // else init with current date
                var value = element.val();
                if(value){
                    var daysplit = value.split(' ');
                    var hoursplit = daysplit[1].split(':');
                    div.find('.day input').val(daysplit[0]);
                    div.find('.hour input').val(hoursplit[0]);
                    div.find('.minute input').val(hoursplit[1]);
                } else if(parameters.init){
                    var d = new Date();
                    div.find(".day input").val(d.getUTCDate()+"-"+(d.getUTCMonth()+1)+"-"+d.getUTCFullYear());
                    var hour = ""+d.getUTCHours();
                    if(d.getUTCHours() >= 0 && d.getUTCHours() <= 9){
                        hour = "0"+d.getUTCHours();
                    }
                    div.find(".hour input").val(hour);
                    var minute = ""+d.getUTCMinutes();
                    if(d.getUTCMinutes()>=0 && d.getUTCMinutes()<=9){
                        minute = "0"+d.getUTCMinutes();
                    }
                    div.find(".minute input").val(minute);
                    element.val(div.find('.day input').val() + " " + div.find('.hour input').val() + ":" + div.find('.minute input').val());
                }

                //subscribe to events

                //Change on a fake input -> update other inputs then update hidden input
                div.on('change', 'input', function() {
                    //mise à jour du champ caché
                    // 1 : remplissage des autres champs si besoin
                    updateFakeForm(div);
                    //2: mise à jour du champ caché en fonction
                    element.val(div.find('.day input').val() + " " + div.find('.hour input').val() + ":" + div.find('.minute input').val());
                    //3: on prévient les autres qu'il y a eu un changement
                    element.trigger('change');
                });

                div.on('click', '.hour .next', function(event) {
                    event.preventDefault();
                    var input = $(this).closest('td').find('input');
                    input.val(hourplusone(input, 1));
                    input.trigger('change');
                });

                div.on('click', '.minute .next', function(event) {
                    event.preventDefault();
                    var input = $(this).closest('td').find('input');
                    input.val(minuteplusone(input, 5));
                    input.trigger('change');
                });

                div.on('click', '.hour .previous', function(event) {
                    event.preventDefault();
                    var input = $(this).closest('td').find('input');
                    input.val(hourplusone(input, -1));
                    input.trigger('change');
                });

                div.on('click', '.minute .previous', function(event) {
                    event.preventDefault();
                    var input = $(this).closest('td').find('input');
                    input.val(minuteplusone(input, -5));
                    input.trigger('change');
                });

                div.on('mousewheel', 'td.hour input', function(event, delta) {
                    event.preventDefault();
                    $(this).val(hourplusone($(this), delta));
                    $(this).trigger('change');
                });

                div.on('mousewheel', 'td.minute input', function(event, delta) {
                    event.preventDefault();
                    $(this).val(minuteplusone($(this), delta));
                    $(this).trigger('change');
                });

                div.on('mousewheel', 'td.day input', function(event, delta){
                    event.preventDefault();
                    $(this).val(dayplusone($(this), delta));
                    $(this).trigger('change');
                });

                div.on('click', 'a.clear-time', function(e){
                    e.preventDefault();
                    element.val('');
                    var form = $(this).closest('.timepicker-form');
                    form.find('td input').val('');
                    element.trigger('change');
                });

                div.on('click', 'a.sunrise', function(event){
                    event.preventDefault();
                    var hourinput = div.find('td.hour input');
                    var mininput = div.find('td.minute input');
                    var date = getDay($(this).closest('tr'));
                    $.getJSON(parameters.api_sunrise_url + '/getsunrise?date='+date, function(data){
                        var d = new Date(data.sunrise);
                        var hour = ""+d.getUTCHours();
                        if(d.getUTCHours() >= 0 && d.getUTCHours() <= 9){
                            hour = "0"+d.getUTCHours();
                        }
                        hourinput.val(hour);
                        var minute = ""+d.getUTCMinutes();
                        if(d.getUTCMinutes()>=0 && d.getUTCMinutes()<=9){
                            minute = "0"+d.getUTCMinutes();
                        }
                        mininput.val(minute);
                        element.trigger('change');
                    });
                });

                div.on('click', 'a.sunset', function(event){
                    event.preventDefault();
                    var hourinput = div.find('td.hour input');
                    var mininput = div.find('td.minute input');
                    var date = getDay($(this).closest('tr'));
                    $.getJSON(parameters.api_sunrise_url + '/getsunset?date='+date, function(data){
                        var d = new Date(data.sunset);
                        var hour = ""+d.getUTCHours();
                        if(d.getUTCHours() >= 0 && d.getUTCHours() <= 9){
                            hour = "0"+d.getUTCHours();
                        }
                        hourinput.val(hour);
                        var minute = ""+d.getUTCMinutes();
                        if(d.getUTCMinutes()>=0 && d.getUTCMinutes()<=9){
                            minute = "0"+d.getUTCMinutes();
                        }
                        mininput.val(minute);
                        element.trigger('change');
                    });
                });

                //change input if more than 2 digits are entered in the hour input
                var charCount = 0;

                div.on('focus', 'td.hour input', function(event){
                    event.preventDefault();
                    this.charCount = $(this).val().length;
                });

                div.on('keyup', 'td.hour input', function(event){
                    var currentCharCount = $(this).val().length;
                    if(currentCharCount === 2 && this.charCount === 1){
                        $(this).closest('.timepicker-form').find('td.minute input').focus().select();
                    }
                    this.charCount = currentCharCount;
                });
            }
        });
    };
})(jQuery);




