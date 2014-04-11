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

var displayMessages = function(messages){
	if(messages['success']){
		$.each(messages.success, function(key, value){
			var n = noty({text:value, 
				type:'success',
				layout: 'bottomRight',});
		});
	}
	if(messages['error']){
		$.each(messages.error, function(key, value){
			var n = noty({text:value, 
				type:'error',
				layout: 'bottomRight',});
		});
	}
};
 
 var displayPanel = function(id){
        var timeline = $('#timeline');
        timeline.animate({
            left: '330px'
        }, 300);
        $('#panel').load(url+'events/getfiche?id='+id);
 };
 
 var hidePanel = function(){
        var panel = $('#timeline');
        panel.animate({
            left: '0px'
        }, 300, function(){
            $('#panel').empty();
        });
        
 }
 
 var togglePanel = function(id){
        var panel = $('#timeline');
        //on détermine si on affiche ou si on cache
        var val = panel.css('left') == '330px' ? '0px' : '330px';
        panel.animate({
            left: val
        }, 300);
        if(panel.css('left') == '330px') {
            $('#panel').empty();
        } else {
            $('#panel').load(url+'events/getfiche?id='+id);
        }
 }
 
 var url;
 
 var setURL = function(urlt){
     url = urlt;
 };
 
$(document).ready(function(){
	
   setInterval('updateClock()', 1000);
   
   $.datepicker.regional[ "fr" ];
   
   //higlight tabs
   var urlt = window.location;
   $(".nav > li a").filter(function(){
	   //remove #
	   var i = urlt.toString().lastIndexOf("#");
	   if(i != -1){
		   urlt = urlt.toString().substring(0, i);
	   }
	   return this.href == urlt; 
   }).parent().addClass('active') //on ajoute la classe active
   .siblings().removeClass('active'); //suppression des classes active positionnées dans la page
   
   $("a[data-toggle=tooltip]").tooltip();
   $("th[data-toggle=tooltip]").tooltip();
   
   $("a[data-toggle=popover]").popover();
   
   //open links in new window
   $(document).on('click', 'a[rel="external"]',function(){
	   window.open($(this).attr('href'));
	   return false;
   });
   
	$(document).ajaxStart(function(){
		$(".loading").show();
		})
	.ajaxStop(function(){
		$(".loading").hide();
		}
	);
	
   $.noty.defaults = {
		    layout: 'bottomRight',
		    theme: 'defaultTheme',
		    type: 'alert',
		    text: '',
		    dismissQueue: true, // If you want to use queue feature set this true
		    template: '<div class="noty_message"><span class="noty_text"></span><div class="noty_close"></div></div>',
		    animation: {
		        open: {height: 'toggle'},
		        close: {height: 'toggle'},
		        easing: 'swing',
		        speed: 500 // opening & closing animation speed
		    },
		    timeout: 5000, // delay for closing event. Set false for sticky notifications
		    force: false, // adds notification to the beginning of queue when set to true
		    modal: false,
		    maxVisible: 5, // you can set max visible notification for dismissQueue true option
		    closeWith: ['click'], // ['click', 'button', 'hover']
		    callback: {
		        onShow: function() {},
		        afterShow: function() {},
		        onClose: function() {},
		        afterClose: function() {}
		    },
		    buttons: false // an array of buttons
		};
    
    
    //slidepanel
    $(document).on('click', "#close-panel", function(e){
        e.preventDefault();
        hidePanel();
    });
    
    $(document).on('submit', '#add-note', function(e){
        e.preventDefault();
        var me = $(this);
        $.post(url+'events/addnote?id='+$(this).data('id'), $(this).serialize(), function(data){
            if(!data['error']){
                me.find('textarea').val('');
                $("#updates").load(url+'events/updates?id='+me.data('id'));
                $("#updates").show();
            }
            displayMessages(data);
        });
    });
    
    //click sur une fiche reflexe
    $(document).on("click", "a.fiche", function(){
		var id = $(this).data('id');
		var me = $(this);
		//tell the server to toggle the status
		$.getJSON(url+'events/togglefiche'+'?id='+id,
                    function(data){
			if(data.open){
				me.html("A faire");
				me.removeClass("active");
			} else {
				me.html("Fait");
				me.addClass("active");
			}
                        $("#history").load(url+'events/gethistory?id='+me.data('eventid'));
                    }
		);
	});
    
    $(document).on('click', '.block-header', function(){
        var me = $(this);
        var content = me.siblings(".block-content");
        //change icon
        if(content.is(':visible')){
            me.find('i').removeClass('icon-chevron-up');
            me.find('i').addClass('icon-chevron-down');
            content.slideUp('fast(');
        } else {
            me.find('i').addClass('icon-chevron-up');
            me.find('i').removeClass('icon-chevron-down');
            content.slideDown('fast');
        }        
    });

});


