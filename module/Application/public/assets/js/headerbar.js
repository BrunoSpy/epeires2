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

    $("select[name=zone]").on("change", function (event) {
        event.preventDefault();
        $.post(url + 'events/savezone', $("#zoneform").serialize(), function () {
            //refresh timeline instead of entire window
            location.reload();
        });
    });

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
    }, 60000);

    var updateTabs = function() {
    	$.getJSON(url + 'events/getNumberEventsTab', function(data){
    		$.each(data, function(key, value){
    			var count = parseInt(value);
    			var span = $('<span class="exp badge badge-important">'+count+'</span>');
    			if(key === 'radar'){
    				if(isNaN(count) || count <= 0) {
    					$('#radartab').find('span').remove();
    				} else {
    					if($('#radartab span').length > 0){
    						$('#radartab span').text(count);
    					} else {
    						$('#radartab').append(span);
    					}
    				}
    			} else if(key === 'radio'){
    				if(isNaN(count) || count <= 0) {
    					$('#frequency').find('span').remove();
    				} else {
    					if($('#frequency span').length > 0){
    						$('#frequency span').text(count);
    					} else {
    						$('#frequency').append(span);
    					}
    				}
    			} else {
    				if(isNaN(count) || count <= 0) {
    					$('#tab-'+key).find('span').remove();
    				} else {
    					if($('#tab-'+key+' span').length > 0){
    						$('#tab-'+key+' span').text(count);
    					} else {
    						$('#tab-'+key).append(span);
    					}
    				}
    			}
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
                nextHour = hours.reduce(function(a, b, i, arr){return (a.hour <= b.hour ? a : b)});
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
                        text: "Rappel :<br> Relève " + nextHour.name + (nextHour.zone.length > 0 ? "(zone "+nextHour.zone+")." : ".")
                                + "<br>Penser à mettre à jour le nom du chef de salle en fonction.",
                        type: "information",
                        timeout: false,
                        layout: "topRight",
                        callback: {
                            onClose: function(){
                                updateShiftHours();
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
        }, '.opsup-form label'
    );

    $('#navbar-first .opsup-form label').on('click', function(e){
        $("#opsupwindow #opsup-content").load(url + 'opsups/opsups', function(){
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