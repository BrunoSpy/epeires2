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
        $.post(url + '/savezone', $("#zoneform").serialize(), function () {
            //refresh timeline instead of entire window
            location.reload();
        });
    });

    $("select[name=nameopsup]").on("change", function (event) {
        event.preventDefault();
        var form = $(this).closest('form');
        $.post(url + '/saveopsup', form.serialize(), function (data) {
            displayMessages(data);
        }, 'json');
    });

    $("select[name=nameipo]").on("change", function (event) {
        event.preventDefault();
        $.post(url + '/saveipo', $("#ipo").serialize(), function (data) {
            displayMessages(data);
        }, 'json');
    });

    //update IPO every minute
    setInterval(function () {
        $.getJSON(url + '/getIPO', function (data) {
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
    	$.getJSON(url + '/getNumberEventsTab', function(data){
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
    
};