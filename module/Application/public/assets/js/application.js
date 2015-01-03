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
 
 var displayPanel = function(id, files){
        var timeline = $('#timeline');
        if(timeline.css('left') === '330px' && $("#fiche").data('id') === id) {
            //panneau ouvert avec la fiche actuelle : fermeture du panneau
            hidePanel();
        } else {
            if(timeline.css('left') !== '330px'){
                //panneau fermé : on l'ouvre
                $('.Time_obj, .TimeBar').animate({left: '+=330px'}, 300);
                timeline.animate({
                    left: '330px'
                }, 300);
            }
            $('#fiche').load(url+'events/getfiche?id='+id, function(){
                $('tr[data-toggle=tooltip]').tooltip();
                if(files){
                    $("#files-panel").trigger('click');
                }
            }).data('id', id);
        }
 };
 
 var hidePanel = function(){
        var timeline = $('#timeline');
        $('#fiche').empty();
        if(timeline.css('left') === '330px') {
            $('.Time_obj, .TimeBar').animate({left: '-=330px'}, 300);
        }
        timeline.animate({
            left: '0px'
        }, 300);
        
 };
 
 var togglePanel = function(id){
        var panel = $('#timeline');
        //on détermine si on affiche ou si on cache
        var val = panel.css('left') === '330px' ? '0px' : '330px';
        if(panel.css('left') === '330px') {
            $('#fiche').empty();
            $('.Time_obj, .TimeBar').animate({left: '-=330px'}, 300);
        } else {
            $('.Time_obj, .TimeBar').animate({left: '+=330px'}, 300);
            $('#fiche').load(url+'events/getfiche?id='+id, function(){
                $('tr[data-toggle=tooltip]').tooltip();
            });
        }
        panel.animate({
            left: val
        }, 300);
 };
 
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
	
    var showSpinner = 0;
        
    $(document)
    .hide()  // hide it initially
    .ajaxSend(function(event, jqxhr, settings) {
        if(settings.url.indexOf("events/suggestEvents") > -1 ||
        settings.url.indexOf("events/form") > -1 ||
        settings.url.indexOf("events/subform") > -1 ||
        settings.url.indexOf("events/getpredefinedvalues") > -1 ||
        settings.url.indexOf("events/getactions") > -1 ||
        settings.url.indexOf("events/getalarms") > -1 ||
        settings.url.indexOf("events/getfiles") > -1) {
            $("#create-evt .loading").show();
            showSpinner++;
        }
        
        if(settings.url.indexOf("events/search") > -1 ) {
            $("#search-results .loading").show();
        }
    })
    .ajaxComplete(function(event, jqxhr, settings) {
        if(settings.url.indexOf("events/suggestEvents") > -1 ||
            settings.url.indexOf("events/form") > -1 ||
            settings.url.indexOf("events/subform") > -1 ||
            settings.url.indexOf("events/getpredefinedvalues") > -1 ||
            settings.url.indexOf("events/getactions") > -1 ||
            settings.url.indexOf("events/getalarms") > -1 ||
            settings.url.indexOf("events/getfiles") > -1) {
            showSpinner--;
            if(showSpinner === 0){
                $("#create-evt .loading").hide();
            }
        }
        
        if(settings.url.indexOf("events/search") > -1 ) {
            $("#search-results .loading").hide();
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
    
    
    //slidepanel
    $(document).on('click', "#close-panel", function(e){
        e.preventDefault();
        hidePanel();
    });
    
    $(document).on('submit', '#add-note', function(e){
        e.preventDefault();
        var me = $(this);
        $.post(url+'events/addnote?id='+me.data('id'), me.serialize(), function(data){
            if(!data['error']){
                me.find('textarea').val('');
                //mise à jour notes
                $("#updates").load(url+'events/updates?id='+me.data('id'), function(){
                    $("#updates").parent().find("span.badge").html($("#updates blockquote").size());
                });
                $("#updates").show();
                //mise à jour histo
                $("#history").load(url+'events/gethistory?id='+me.data('id'), function(){
                    $("#history").parent().find("span.badge").html($("#history dd").size());
                });
                //mise à jour timeline
                timeline.modify(data.events, 0);
            }
            displayMessages(data);
            me.parent('.modal').modal('hide');
        });
    });
    
    //click sur une fiche reflexe
    $(document).on("click", "#fiche a.fiche", function(){
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

    $(document).on('click', '#updates .note', function(){
        var me = $(this).html();
        var p = $(this).closest('p');
        p.empty();
        var form = $('<form data-cancel="'+me+'" data-id="'+$(this).data('id')+'" class="form-inline modify-note" action="'+url+'events/savenote?id='+$(this).data('id')+'"></form>');
        form.append('<textarea name="note">'+me+'</textarea>');
        form.append('<button class="btn btn-mini btn-primary" type="submit"><i class="icon-ok"></i></button>');
        form.append('<a href="#" class="cancel-note btn btn-mini"><i class="icon-remove"></i></a>');
        p.append(form);
    });
    
    $(document).on('submit', 'form.modify-note', function(e){
        e.preventDefault();
        var me = $(this);
        $.post($(this).attr('action'), $(this).serialize(), function(data){
            if(!data['error']){
                var p = me.closest('p');
                var span = $('<span class="note" data-id="'+me.data('id')+'">'+me.find('textarea').val()+'</span>');
                p.empty();
                p.append(span);
                timeline.modify(data.events, 0);
            }
            displayMessages(data);
        });
    });

    $(document).on('click', '.cancel-note', function(e){
        e.preventDefault();
        var form = $(this).closest('form');
        var p = $(this).closest('p');
        var span = $('<span class="note" data-id="'+form.data('id')+'">'+form.data('cancel')+'</span>');
        p.empty();
        p.append(span);
    });
    

    /* ******************************* */
    /* *** Contrôle de la timeline *** */
    /* ******************************* */

    $('#tri_deb').on('click', function(e){
        e.preventDefault();
        $(this).parent().addClass('active');
	$('#tri_cat').parent().removeClass('active');
        $('#timeline').timeline('pauseUpdateView');
        $('#timeline').timeline('option', 'showCategories', false);
        $('#timeline').timeline('sortEvents', function(a, b){
            var aStartdate = new Date(a.start_date);
            var bStartdate = new Date(b.start_date);
            if(aStartdate < bStartdate){
                return -1;
            } else if (aStartdate > bStartdate){
                return 1;
            }
            return 0;
        });
        $('#timeline').timeline('forceUpdateView');
    });
    
    $('#tri_cat').on('click', function(e){
        e.preventDefault();
        $(this).parent().addClass('active');
	$('#tri_deb').parent().removeClass('active');
        $('#timeline').timeline('pauseUpdateView');
        $('#timeline').timeline('option', 'showCategories', true);
        $('#timeline').timeline('sortEvents', "default");
        $('#timeline').timeline('forceUpdateView');
    });

    $('#zoom').on('switch-change', function(e, data) {
        if (data.value) {
            $("#calendar").show();
            $("#export").show();
            var now = new Date();
            var day = now.getUTCDate();
            var month = now.getUTCMonth() + 1;
            var year = now.getUTCFullYear();
            var nowString = FormatNumberLength(day, 2) + "/" + FormatNumberLength(month, 2) + "/" + FormatNumberLength(year, 4);
            $("#calendar input[type=text].date").val(nowString);
            $('#timeline').timeline("view", "day");
        } else {
            $("#calendar").hide();
            $("#export").hide();
            $("#timeline").timeline('view', 'sixhours');
        }
    });

    $("#date").datepicker({
            dateFormat: "dd/mm/yy",
            showButtonPanel: true
    });
    
    $("#date").on('change', function(){
        var temp = $('#calendar input[type=text].date').val().split('/');
        var date = new Date(temp[2], temp[1] - 1, temp[0], "5");
        $("#timeline").timeline("day", date.toString());
    });
    
    $("#day-backward").on('click', function(e) {
        e.preventDefault();
        var temp = $('#calendar input[type=text].date').val().split('/');
        var date = new Date(temp[2], temp[1] - 1, temp[0], "5");
        var back = new Date(date.getTime() - (24 * 60 * 60 * 1000));
        var day = back.getUTCDate();
        var month = back.getUTCMonth() + 1;
        var year = back.getUTCFullYear();
        var backString = FormatNumberLength(day, 2) + "/" + FormatNumberLength(month, 2) + "/" + FormatNumberLength(year, 4);
        $("#calendar input[type=text].date").val(backString);
        $("#calendar input[type=text].date").trigger('change');
    });

    $("#day-forward").on('click', function(e) {
        e.preventDefault();
        var temp = $('#calendar input[type=text].date').val().split('/');
        var date = new Date(temp[2], temp[1] - 1, temp[0], "5");
        var forward = new Date(date.getTime() + (24 * 60 * 60 * 1000));
        var day = forward.getUTCDate();
        var month = forward.getUTCMonth() + 1;
        var year = forward.getUTCFullYear();
        var forwardString = FormatNumberLength(day, 2) + "/" + FormatNumberLength(month, 2) + "/" + FormatNumberLength(year, 4);
        $("#calendar input[type=text].date").val(forwardString);
        $("#calendar input[type=text].date").trigger('change');
    });  
    
    $("#export").on('click', function(e){
        e.preventDefault();
        var temp = $('#calendar input[type=text].date').val().split('/');
    	var date = new Date(temp[2],temp[1]-1,temp[0],"5");
        window.open(url+'report/daily?day='+date.toUTCString());
    });
    
    $("#timeline").on('click', ".checklist-evt", function(e){
        e.preventDefault();
        displayPanel($(this).data('id'));
    });
    
    /**
     * Ouverture des protections via le label
     */
    $("#timeline").on('click', ".label_elmt span.badge", function(e){
        e.preventDefault();
        var me = $(this);
        var id = parseInt(me.closest(".elmt").data('ident'));
        if(!isNaN(id)){
            displayPanel(id, true);
        }
    })
    
    /* ******************************* */

});


