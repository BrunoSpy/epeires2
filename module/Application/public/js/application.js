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
   $(".nav > li a").filter(function(){
	   return this.href == url; 
   }).parent().addClass('active') //on ajoute la classe active
   .siblings().removeClass('active'); //suppression des classes active positionnées dans la page



   //datetimepicker
   $(".datetime").datetimepicker();



/*		"use strict";

		$(".gantt").gantt({
			source: [{
				name: "Sprint 0",
				desc: "Analysis",
				values: [{
					from: "/Date(1320192000000)/",
					to: "/Date(1322401600000)/",
					label: "Requirement Gathering", 
					customClass: "ganttRed"
				}]
			},{
				name: " ",
				desc: "Scoping",
				values: [{
					from: "/Date(1322611200000)/",
					to: "/Date(1323302400000)/",
					label: "Scoping", 
					customClass: "ganttRed"
				}]
			},{
				name: "Sprint 1",
				desc: "Development",
				values: [{
					from: "/Date(1323802400000)/",
					to: "/Date(1325685200000)/",
					label: "Development", 
					customClass: "ganttGreen"
				}]
			},{
				name: " ",
				desc: "Showcasing",
				values: [{
					from: "/Date(1325685200000)/",
					to: "/Date(1325695200000)/",
					label: "Showcasing", 
					customClass: "ganttBlue"
				}]
			},{
				name: "Sprint 2",
				desc: "Development",
				values: [{
					from: "/Date(1326785200000)/",
					to: "/Date(1325785200000)/",
					label: "Development", 
					customClass: "ganttGreen"
				}]
			},{
				name: " ",
				desc: "Showcasing",
				values: [{
					from: "/Date(1328785200000)/",
					to: "/Date(1328905200000)/",
					label: "Showcasing", 
					customClass: "ganttBlue"
				}]
			},{
				name: "Release Stage",
				desc: "Training",
				values: [{
					from: "/Date(1330011200000)/",
					to: "/Date(1336611200000)/",
					label: "Training", 
					customClass: "ganttOrange"
				}]
			},{
				name: " ",
				desc: "Deployment",
				values: [{
					from: "/Date(1336611200000)/",
					to: "/Date(1338711200000)/",
					label: "Deployment", 
					customClass: "ganttOrange"
				}]
			},{
				name: " ",
				desc: "Warranty Period",
				values: [{
					from: "/Date(1336611200000)/",
					to: "/Date(1349711200000)/",
					label: "Warranty Period", 
					customClass: "ganttOrange"
				}]
			}],
			navigate: "scroll",
			itemsPerPage: 10,
			onItemClick: function(data) {
				alert("Item clicked - show some details");
			},
			onAddClick: function(dt, rowId) {
				alert("Empty space clicked - add an item!");
			},
			onRender: function() {
				if (window.console && typeof console.log === "function") {
					console.log("chart rendered");
				}
			}
		});

		$(".gantt").popover({
			selector: ".bar",
			title: "I'm a popover",
			content: "And I'm the content of said popover.",
			trigger: "hover"
		});
   */
});
