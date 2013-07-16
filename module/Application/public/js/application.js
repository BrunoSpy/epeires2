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
	   $("#create-evt").toggle();
   });

   //higlight tabs
   var url = window.location;
   if(url == ""){
	   
   } else {
	   $(".nav > li a").filter(function(){
		   return this.href == url; 
	   }).parent().addClass('active') //on ajoute la classe active
	   .siblings().removeClass('active'); //suppression des classes active positionnées dans la page
   }
   
   
   //datetimepicker
   $(".datetime").datetimepicker();
   
});
