/**
 * Licence : AGPL
 * @author Bruno Spyckerelle
 * @require application.js
 */


function updateClock ( )
    {
    moment.locale('fr_FR');
    var currentDay = moment.utc().format('D MMMM');
    var currentTime = moment.utc().format('HH:mm:ss');
    
    $("#day").html (currentDay);
    $("#clock").html(currentTime);
        
 };

var headerbar = function (url) {

    setInterval('updateClock()', 1000);

    $("select[name=nameopsup]").on("change", function (event) {
        event.preventDefault();
        var form = $(this).closest('form');
        $.post(url + 'opsups/saveopsup', form.serialize(), function (data) {
            displayMessages(data);
        }, 'json');
    });

    $("select[name=nameipo]").on("change", function (event) {
        event.preventDefault();
        $.post(url + 'events/saveipo', $("#ipo").serialize(), function (data) {
            displayMessages(data);
        }, 'json');
    });

    //update IPO every minute
    setInterval(function () {
        $.getJSON(url + 'events/getIPO', function (data) {
            $.each(data, function (key, value) {
                if ($('#navbar-first-collapse #iponame').length > 0) { //ipo = span
                    $('#navbar-first-collapse #iponame').text(value);
                } else {
                    //ipo = select
                    $('#navbar-first-collapse select[name=nameipo] option[value=' + key + ']').prop('selected', true);
                }
            });
        });
    }, 10000);

    setInterval(function(){
        $('#navbar-first .opsup-form, #navbar-first .opsup-name').each(function(index, element){
            var me = $(this);
            var typeid = me.data('typeid');
            var zoneid = me.data('zoneid');
            $.getJSON(url + 'opsups/getopsups?typeid='+typeid+'&zoneid='+zoneid, function(data){
               $.each(data, function(key,value){
                   if($('#navbar-first .opsup-name.type-'+typeid+'.zone-'+zoneid).length > 0) {
                       $('#navbar-first .opsup-name.type-'+typeid+'.zone-'+zoneid+' span.opsupname').text(value);
                   } else {
                       $('#navbar-first .navbar-form.type-'+typeid+'.zone-'+zoneid+' select[name=nameopsup] option[value='+key+']').prop('selected', true);
                   }
               });
            });
        });
    }, 10000);


    var updateTabs = function() {
    	$.getJSON(url + 'events/getNumberEventsTab', function(data){
    		$.each(data, function(key, value){
    			var count = parseInt(value);
    			var span = $('<span class="exp badge badge-important">'+count+'</span>');
    			if(key === 'radar'){
    				if(isNaN(count) || count <= 0) {
    					$('#radartab').find('span.exp').remove();
    				} else {
    					if($('#radartab span.exp').length > 0){
    						$('#radartab span.exp').text(count);
    					} else {
    						$('#radartab').append(span);
    					}
    				}
    			} else if(key === 'radio'){
    				if(isNaN(count) || count <= 0) {
    					$('#frequency').find('span.exp').remove();
    				} else {
    					if($('#frequency span.exp').length > 0){
    						$('#frequency span.exp').text(count);
    					} else {
    						$('#frequency').append(span);
    					}
    				}
    			} else {
    				if(isNaN(count) || count <= 0) {
    					$('#tab-'+key).find('span.exp').remove();
    				} else {
    					if($('#tab-'+key+' span.exp').length > 0){
    						$('#tab-'+key+' span.exp').text(count);
    					} else {
    						$('#tab-'+key).append(span);
    					}
    				}
    			}
    		updateNavbar();
    		});

    	});
    };

    updateTabs();

    //update events of tabs every minute
    setInterval(function(){
    	updateTabs();
    }, 60000);


    //noty for shift hours
    var shiftHoursNoty = new Array();
    var updateShiftHours = function () {
        $.getJSON(url + 'events/getshifthours', function(data) {
            var shifthours = [];
            $.each(data, function(key, value){
                shifthours.push(value);
            });
            var names = shifthours
                .map(function(obj){return obj.name})
                .filter(function(value, index, self){return self.indexOf(value) === index; });
            names.forEach(function(element, index, array){
                var now = new Date();
                var nowString = (now.getUTCHours() < 10 ? "0" : "" )
                    + now.getUTCHours()
                    +":"+now.getUTCMinutes();
                var nextHour = "";
                var nextDay = false;
                var hours = shifthours
                    .filter(function(value, index, self){
                        return value.name === element;
                    });
                    //.map(function(obj){return obj.hour;});
                var nextHours = hours.filter(function(value, index, self){return value.hour > nowString;});
                if(nextHours.length === 0) {
                    //next hour : first next day
                    nextHours = hours;
                    nextDay = true;
                }
                nextHour = nextHours.reduce(function(a, b, i, arr){return (a.hour <= b.hour ? a : b)});
                var nextDate = new Date(Date.UTC(now.getUTCFullYear(),
                    now.getUTCMonth(),
                    now.getUTCDate(),
                    nextHour.hour.split(":")[0],
                    nextHour.hour.split(":")[1]));
                if(nextDay === true) {
                    nextDate.setDate(nextDate.getDate() + 1);
                }
                var delta = new Date(nextDate) - new Date();
                var timer = setTimeout(function(){
                    var n = noty({
                        text: "Rappel :<br> Relève de "+nextHour.hour+ " " + nextHour.name + (nextHour.zone.length > 0 ? " (zone "+nextHour.zone+")." : ".")
                                + "<br>Penser à mettre à jour le nom du chef de salle en fonction.",
                        type: "warning",
                        timeout: false,
                        layout: "topRight",
                        callback: {
                            onClose: function(){
                                updateShiftHours();
                            },
                            onShow: function() {
                                $('ul#noty_topRight_layout_container').draggable({
                                    stop: function (event, ui) {
                                        $(event.originalEvent.target).one('click', function (e) {
                                            e.stopImmediatePropagation();
                                        })
                                    }
                                });
                            }
                        }
                    });
                }, delta);
            });
        });
    };
    updateShiftHours();

    /***** Op Sups *****/
    $('#navbar-first').on(
        {
            'mouseenter': function () {
                $(this).css('cursor', 'pointer');
                var width = $(this).outerWidth();
                $(this).find('.caret').css({'left' : (width / 2) + "px", "display" : "block"});
            },
            mouseleave: function () {
                $(this).css('cursor', 'auto');
                $(this).find('.caret').hide();
            }
        }, '.opsup-form label, .opsup-name'
    );

    $('#navbar-first .opsup-form label, #navbar-first .opsup-name').on('click', function(e){
        var day = '';
        if($("#calendar:visible").length > 0) {
            day = $('#date').val();
            day = day.replace(/\//g,"-");
        }
        $("#opsupwindow #opsup-content").load(url + 'opsups/opsups'+ (day.length > 0 ? '?day='+day : ''), function(){
            $("#opsupwindow").modal('show');
        });
    });

    $('.filterable .btn-calendar').on('click', function() {
        $('.filterable input[name=opsup-date]').trigger('focus');
    });

    $('.filterable input[name=opsup-date]').bootstrapMaterialDatePicker({
                    format: "DD-MM-YYYY",
                    time: false,
                    lang: 'fr',
                    cancelText: "Annuler",
                    weekStart : 1,
                    switchOnClick: true
    });

    $('.filterable input[name=opsup-date]').on('change', function(e){
        var date = $(this).val();
        $("#opsupwindow #opsup-content").load(url + 'opsups/opsups?day='+date);
    });

    $('.filterable .btn-filter').click(function(){
        var $panel = $(this).parents('.filterable'),
            $filters = $panel.find('.filters input'),
            $tbody = $panel.find('.table tbody');
        if ($filters.prop('disabled') == true) {
            $filters.prop('disabled', false);
            $filters.first().focus();
        } else {
            $filters.val('').prop('disabled', true);
            $tbody.find('.no-result').remove();
            $tbody.find('tr').show();
        }
    });

    $('.filterable .filters input').keyup(function(e){
        /* Ignore tab key */
        var code = e.keyCode || e.which;
        if (code == '9') return;
        /* Useful DOM data and selectors */
        var $input = $(this),
            inputContent = $input.val().toLowerCase(),
            $panel = $input.parents('.filterable'),
            column = $panel.find('.filters th').index($input.parents('th')),
            $table = $panel.find('.table'),
            $rows = $table.find('tbody tr');
        /* Dirtiest filter function ever ;) */
        var $filteredRows = $rows.filter(function(){
            var value = $(this).find('td').eq(column).text().toLowerCase();
            return value.indexOf(inputContent) === -1;
        });
        /* Clean previous no-result if exist */
        $table.find('tbody .no-result').remove();
        /* Show all rows, hide filtered ones (never do that outside of a demo ! xD) */
        $rows.show();
        $filteredRows.hide();
        /* Prepend no-result row if all rows are filtered */
        if ($filteredRows.length === $rows.length) {
            $table.find('tbody').prepend($('<tr class="no-result text-center"><td colspan="'+ $table.find('.filters th').length +'">No result found</td></tr>'));
        }
    });

};