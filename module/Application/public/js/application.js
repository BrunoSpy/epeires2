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
 
$(document).ready(function(){

   setInterval('updateClock()', 1000);
   
   //higlight tabs
   var url = window.location;
   $(".nav > li a").filter(function(){
	   //remove #
	   var i = url.toString().lastIndexOf("#");
	   if(i != -1){
		   url = url.toString().substring(0, i);
	   }
	   return this.href == url; 
   }).parent().addClass('active') //on ajoute la classe active
   .siblings().removeClass('active'); //suppression des classes active positionnées dans la page
   
   $("a[data-toggle=tooltip]").tooltip();
   $("th[data-toggle=tooltip]").tooltip();
   
   //toggle panel
   $("#toggle-panel").on('click', function(){
	   var panel = $("#panel");
	   $("#timeline").width('auto');
	   $('#timeline').css({position:'relative'});
	   if(panel.width() > 0 ) {
		   $("#panel").animate({width:'0%'}, function(){
			   $("#panel").hide();
		   });
	   } else {
		   $("#panel").show();
		   $("#panel").animate({width:'20%'});
		   $("#panel").load('http://127.0.0.1/epeires2/events/gethistory?id=172');
	   }
	   
   });
   
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

});


