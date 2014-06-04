/**
 * Licence : AGPL
 * @author Bruno Spyckerelle
 */

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

function FormatNumberLength(num, length) {
    var r = "" + num;
    while (r.length < length) {
        r = "0" + r;
    }
    return r;
}
 
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
   
   $("a[data-toggle=tooltip], th[data-toggle=tooltip], td[data-toggle=tooltip]").tooltip();
   
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
	
    $('#timeline').on({
        mouseenter: function() {
            var id = $(this).find('.modify-evt').data('id');
            $(this).tooltip({
                title: 'test' + id,
                container: 'body'
            }).tooltip('show');
        },
        mouseleave: function() {
            $(this).tooltip('hide');
        }
    }, '.elmt'); 
       
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
                //mise à jour notes
                $("#updates").load(url+'events/updates?id='+me.data('id'), function(){
                    $("#updates").parent().find("span.badge").html($("#updates dt").size());
                });
                $("#updates").show();
                //mise à jour histo
                $("#history").load(url+'events/gethistory?id='+me.data('id'), function(){
                    $("#history").parent().find("span.badge").html($("#history dd").size());
                });
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
				me.removeClass("active btn-success");
			} else {
				me.html("Fait");
				me.addClass("active btn-success");
			}
                        $("#history").load(url+'events/gethistory?id='+me.data('eventid'), function(){
                            $("#history").parent().find("span.badge").html($("#history dd").size());
                        });
                        
                    }
		);
	});
    
    $(document).on('click', '#fiche .block-header', function(){
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

    $(document).on('click', '.note', function(){
        var me = $(this).html();
        var dd = $(this).closest('dd');
        dd.empty();
        var form = $('<form data-cancel="'+me+'" data-id="'+$(this).data('id')+'" class="form-inline modify-note" action="'+url+'events/savenote?id='+$(this).data('id')+'"></form>');
        form.append('<textarea name="note">'+me+'</textarea>');
        form.append('<button class="btn btn-mini btn-primary" type="submit"><i class="icon-ok"></i></button>');
        form.append('<a href="#" class="cancel-note btn btn-mini"><i class="icon-remove"></i></a>');
        dd.append(form);
    });
    
    $(document).on('submit', 'form.modify-note', function(e){
        e.preventDefault();
        var me = $(this);
        $.post($(this).attr('action'), $(this).serialize(), function(data){
            if(!data['error']){
                var dd = me.closest('dd');
                var span = $('<span class="note" data-id="'+me.data('id')+'">'+me.find('textarea').val()+'</span>');
                dd.empty();
                dd.append(span);
            }
            displayMessages(data);
        });
    });

    $(document).on('click', '.cancel-note', function(e){
        e.preventDefault();
        var form = $(this).closest('form');
        var dd = $(this).closest('dd');
        var span = $('<span class="note" data-id="'+form.data('id')+'">'+form.data('cancel')+'</span>');
        dd.empty();
        dd.append(span);
    });
    
    //à mettre dans timeline.js ?
    $('#zoom').on('switch-change', function(e, data){
       if(data.value){
           $("#calendar").show();
       }  else {
           $("#calendar").hide();
       }
    });
    
    var now = new Date();
    $("#calendar input[type=text].date").val(now.toLocaleDateString());
    $("#calendar input[type=text].date").on('change', function(){
        
    });
    $("#date").datepicker({
            dateFormat: "dd/mm/yy",
            showButtonPanel: true
    });
    
});


