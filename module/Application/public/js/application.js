function updateClock ( )
    {
    var currentTime = new Date ( );
    var currentHours = currentTime.getHours ( );
    var currentMinutes = currentTime.getMinutes ( );
    var currentSeconds = currentTime.getSeconds ( );

    // Pad the minutes and seconds with leading zeros, if required
    currentMinutes = ( currentMinutes < 10 ? "0" : "" ) + currentMinutes;
    currentSeconds = ( currentSeconds < 10 ? "0" : "" ) + currentSeconds;

    // Compose the string for display
    var currentTimeString = currentHours + ":" + currentMinutes + ":" + currentSeconds;
    
    
    $("#clock").html(currentTimeString);
        
 };
 
$(document).ready(function(){
   setInterval('updateClock()', 1000);
   
   $("#create-link").on("click", function(){
	   if($("#create-evt").is(':visible')){
		   $("#create-evt").slideUp('fast');
		   $("#create-link").html('<i class="icon-pencil"></i> <i class="icon-chevron-down"></i>');
	   } else {
		   $("#create-evt").slideDown('fast');
		   $("#create-link").html('<i class="icon-pencil"></i> <i class="icon-chevron-up"></i>');
	   }
   });

   $("#event").on("click", "#cancel-form", function(){
	   $("#create-link").trigger("click");
   });
   
   $("#event").on("focus", 'input[type=datetime]', function(){
	  $(this).datetimepicker({
		  dateFormat: "dd-mm-yy",
	  });
   });
   
   //higlight tabs
   var url = window.location;
   $(".nav > li a").filter(function(){
	   return this.href == url; 
   }).parent().addClass('active') //on ajoute la classe active
   .siblings().removeClass('active'); //suppression des classes active positionnées dans la page
});


