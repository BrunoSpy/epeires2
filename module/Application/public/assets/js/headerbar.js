function updateClock ( )
    {
    var currentTime = new Date ( );
    var currentHours = currentTime.getUTCHours ( );
    var currentMinutes = currentTime.getUTCMinutes ( );
    var currentSeconds = currentTime.getUTCSeconds ( );

    // Pad the minutes and seconds with leading zeros, if required
    currentMinutes = ( currentMinutes < 10 ? "0" : "" ) + currentMinutes;
    currentSeconds = ( currentSeconds < 10 ? "0" : "" ) + currentSeconds;

    // Compose the string for display
    var currentTimeString = currentHours + ":" + currentMinutes + ":" + currentSeconds;
    
    
    $("#clock").html(currentTimeString);
        
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
        $.post(url + '/saveopsup', $("#opsup").serialize(), function (data) {
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
                if ($('.header #iponame').length > 0) { //ipo = span
                    $('.header #iponame').text(value);
                } else {
                    //ipo = select
                    $('.header select[name=nameipo] option[value=' + key + ']').prop('selected', true);
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