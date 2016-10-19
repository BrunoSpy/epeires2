/**
 * Licence : AGPL
 * @author Bruno Spyckerelle
 */



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

var updateNavbarTop = function() {
    var windowWidth = $(window).width();
    var totalWidth = 0;

    var brandWidth = $("#navbar-first .navbar-header").show().innerWidth();
    totalWidth += brandWidth;
    totalWidth += $("#navbar-first .navbar-nav").innerWidth();

    $('#navbar-first-collapse .navbar-form').each(function(index, elm){
        totalWidth += $(this).innerWidth();
    });
    $("#ipo").show();
    $('#day').show();
    $('#navbar-clock').show();

    totalWidth += 200; //width of clock depends on time and date...

    totalWidth += 30; //some margin

    if(windowWidth > 768) {
        if(totalWidth > windowWidth) {
            //remove day
            $("#day").hide();
            totalWidth -= 110;
        }
        if(totalWidth > windowWidth) {
            //remove brand
            $("#navbar-first .navbar-header").hide();
            totalWidth -= brandWidth;
        }
        if(totalWidth > windowWidth) {
            //remove hour completely
            $("#navbar-clock").hide();
            totalWidth -= 90;
        }
        if(totalWidth > windowWidth) {
            //remove Ipo
            $("#ipo").hide();
        }
    }
};

var updateNavbar = function() {
    var windowWidth = $(window).width();
    var totalWidth = 0;
    var maxWidth = $("#navbar-collapse").width();
    var padding = maxWidth / 4;
    var centerWidth = $(".navbar-lower .navbar-center").width()
                    + padding;
    var searchWidth = $("#search").show().innerWidth();
    var viewWidth = $("#changeview").innerWidth();

    var totalWidth = centerWidth + searchWidth + viewWidth;

    $(".navbar-lower .navbar-center").css('padding-left', '25%');
    if(windowWidth > 768) {
        if(totalWidth > maxWidth) {
            $(".navbar-lower .navbar-center").css('padding-left', '0%');
            totalWidth -= padding;
        }
        if(totalWidth > maxWidth) {
            $("#search").hide();
        }
    } else {
        $(".navbar-lower .navbar-center").css('padding-left', '0%');
    }
};

var updateView = function(){
    if($(window).width() <= 768) {
        $("#viewmonth").trigger('click');
        $("#changeview").hide();
    } else {
        $("#changeview").show();
    }
};
/* **************** */
/* Gestion panneau  */
/* **************** */
var timerFiche;

var closeFiche = function() {
    $("#fiche").empty();$("#fiche").hide();
    $("#main-nav-check").prop('checked', false);
    clearTimeout(timerFiche);
};

var openFiche = function() {
    $("#main-nav-check").prop('checked', true);
    $("#fiche").show();
};

var loadFiche = function(id, actionUrl, files) {
    if($("#main-nav-check").is(':checked')){
        if($('#fiche').data('id') === id){
            closeFiche();
        } else {
            $('#fiche').load(url+actionUrl+'?id='+id, function(){
                $('tr[data-toggle=tooltip]').tooltip();
                timerFiche = setTimeout(updateFiche, 10000, $("#close-button").data('parentid'));
            });
        }
    } else {
        $("#fiche").empty();
        $('#fiche').load(url+actionUrl+'?id='+id, function(){
            $('tr[data-toggle=tooltip]').tooltip();
            if(files){
                $("#files-panel").trigger('click');
            }
            timerFiche = setTimeout(updateFiche, 10000, $("#close-button").data('parentid'));
        });

        openFiche();
    }
};

$(document).on('click', '.open-fiche, .checklist-evt', function(){
    var id = $(this).data('id');
    var actionUrl = $("#fiche").data('url');
    loadFiche(id, actionUrl, false);
});

$("#fiche").on('click', "#close-panel", function(e){
    e.preventDefault();
    closeFiche();
});
 
 var updateFiche = function(id){
    //on ne met à jour que le contenu de la fiche reflexe
    //sinon on a un effet de flip-flop sur les panneaux
    var change = false;
    $.getJSON(url + 'events/actionsStatus?id=' + id, function(data){
	$.each(data, function(key, value){
	    var td = $('tr[data-id='+key+'] td:last a');
	    if(value && td.hasClass('btn-success')) {
		change = true;
		td.removeClass('active btn-success').addClass('btn-primary').html('<strong>A faire</strong>');
	    } else if (!value && td.hasClass('btn-primary')) {
		change = true;
		td.addClass('active btn-success').removeClass('btn-primary').html('<strong>Fait</strong>');
	    }
	});
	//il faut aussi mettre l'historique à jour si il y a eu un changement
	if(change == true){
	    //mise à jour histo
	    $("#history").load(url+'events/gethistory?id='+id, function(){
		$("#history").closest('.panel').find("span.badge").html($("#history dd").length);
	    });
	}
	timerFiche = setTimeout(updateFiche, 10000, id);
    });
 };
/* **** End Left Panel  **** */


 var url;
 var setURL = function(urlt){
     url = urlt;
 };

$(document).ready(function(){
    
    $.material.init();
   
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
   
   //sortable tables
   $("table.sortable").stupidtable();
   //add arrow
   $("table.sortable").bind('aftertablesort', function(event, data){
   	var th = $(this).find("th");
       th.find(".arrow").remove();
       var arrow = data.direction === "asc" ? "<span class=\"glyphicon glyphicon-arrow-down\"></span>" : "<span class=\"glyphicon glyphicon-arrow-up\"></span>";
       th.eq(data.column).append('<span class="arrow"> ' + arrow +'</span>');
   });
   //autosort
   $("table.sortable th[data-autosort=true]").each(function(item){
       $(this).stupidsort();
   });
   
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

	/* **************************************************************************** */
	/* Gestion des éléments dans les navbars en fonction de la taille de la fenêtre */
    /* **************************************************************************** */

    updateNavbarTop();
    updateNavbar();
    updateView();
    $(window).resize(function(){
        updateNavbarTop();
        updateNavbar();
        updateView();
    });


    /* *************************** Fin gestion des navbars ************************ */

    //slidepanel
    $(document).on('click', "#close-panel", function(e){
        e.preventDefault();
        closeFiche();
    });
    
    $(document).on('submit', '#add-note, #add-note-fiche', function(e){
        e.preventDefault();
        var me = $(this);
        $.post(url+'events/addnote?id='+me.data('id'), me.serialize(), function(data){
            if(!data['error']){
                me.find('textarea').val('');
                var idFiche = $('#close-button').data('id');
                if(typeof idFiche != 'undefined') {
                    //fiche ouverte
                    //mise à jour notes
                    $("#updates").load(url+'events/updates?id='+idFiche, function(){
                        $("#updates").closest('.panel').find(".panel-heading span.badge").html($("#updates blockquote").length);
                    });
                    $("#updates").show();
                    //mise à jour histo
                    $("#history").load(url+'events/gethistory?id='+idFiche, function(){
                        $("#history").closest('.panel').find("span.badge").html($("#history dd").length);
                    });
                }
                //mise à jour timeline si besoin
                if($('#timeline').length > 0){
                    $('#timeline').timeline('addEvents',data.events);
                }
                if($('#calendarview').length > 0 && $("#calendarview").is(':visible')) {
                    $('#calendarview').fullCalendar('refetchEvents');
                }
            }
            displayMessages(data);
            me.parents('.modal').modal('hide');
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
				me.html("<strong>A faire</strong>");
				me.removeClass("active btn-success").addClass('btn-primary');
			} else {
				me.html("<strong>Fait</strong>");
				me.addClass("active btn-success");
			}
                        $("#history").load(url+'events/gethistory?id='+me.data('eventid'), function(){
                            $("#history").closest('.panel').find("span.badge").html($("#history dd").length);
                        });
                        
                    }
		);
	});
    
    $(document).on('click', '#fiche .panel-heading', function(){
        var me = $(this);
        var content = me.siblings(".panel-collapse");
        //change icon
        if(content.is(':visible')){
            me.find('span.glyphicon').removeClass('glyphicon-chevron-up');
            me.find('span.glyphicon').addClass('glyphicon-chevron-down');
            content.slideUp('fast(');
        } else {
            me.find('span.glyphicon').addClass('glyphicon-chevron-up');
            me.find('span.glyphicon').removeClass('glyphicon-chevron-down');
            content.slideDown('fast');
        }        
    });

    $(document).on('click', '#updates .note', function(){
        var me = $(this).html();
        var p = $(this).closest('p');
        p.empty();
        var form = $('<form data-cancel="'+me+'" data-id="'+$(this).data('id')+'" class="form-inline modify-note" action="'+url+'events/savenote?id='+$(this).data('id')+'"></form>');
        form.append('<textarea name="note">'+me+'</textarea>');
        form.append('<button class="btn btn-xs btn-primary" type="submit"><span class="glyphicon glyphicon-ok"></span></button>');
        form.append('<button href="#" class="cancel-note btn btn-xs"><span class="glyphicon glyphicon-remove"></span></button>');
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
                $('#timeline').timeline('addEvents',data.events);
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

    $("#filter_none").on('click', function(e){
        e.preventDefault();
        $(this).parent().addClass('active');
        $("#filter_deleted").parent().removeClass('active');
        $("#timeline").timeline('pauseUpdateView');
        $("#timeline").timeline('filter', function(evt) {return true;});
        $('#timeline').timeline('forceUpdateView', false);
        if($("#calendarview").is(':visible')) {
            $("#calendarview").fullCalendar('refetchEvents');
        }
    });

    $("#filter_deleted").on('click', function(e){
        e.preventDefault();
        $(this).parent().addClass('active');
        $("#filter_none").parent().removeClass('active');
        $("#timeline").timeline('pauseUpdateView');
        $("#timeline").timeline('filter', "default");
        $('#timeline').timeline('forceUpdateView', false);
        if($("#calendarview").is(':visible')) {
            $("#calendarview").fullCalendar('refetchEvents');
        }
    });

    $("input[name=viewOptions]").on('change', function(e){
        closeFiche();
        var view = $("input[name=viewOptions]:checked").val();
        if(view.localeCompare("six") == 0){
            $("#calendar").hide();
            $("#export").hide();
            $("#calendarview").hide();
            $("#timeline").show();
            $("#timeline").timeline('view', 'sixhours');
            $.post(url + 'events/saveview?view=6');
            var now = new Date();
            var date = FormatNumberLength(now.getUTCDate(), 2) + '/'
                    + FormatNumberLength(now.getUTCMonth()+1, 2)+ '/'
                    + FormatNumberLength(now.getUTCFullYear(), 4);
            $.post(url + 'events/saveday?day="' + date+'"');
        } else if(view.localeCompare("day") == 0) {
            $("#calendarview").hide();
            $("#timeline").show();
            $("#calendar").show();
            $("#export").show();
            var now = new Date();
            var day = now.getUTCDate();
            var month = now.getUTCMonth() + 1;
            var year = now.getUTCFullYear();
            var nowString = FormatNumberLength(day, 2) + "/" + FormatNumberLength(month, 2) + "/" + FormatNumberLength(year, 4);
            $("#calendar input[type=text].date").val(nowString);
            $('#timeline').timeline("view", "day");
            $.post(url + 'events/saveview?view=24');
        } else if(view.localeCompare("month") == 0) {
            $("#calendarview").show();
            $("#calendarview").fullCalendar('changeView', 'basicWeek');
            $("#timeline").hide();
            $.post(url + 'events/saveview?view=30');
        }
    });

    $("#calendarview").fullCalendar({
        events: url+'events/geteventsFC'
            +(typeof(cats) == "undefined" ? '' : '?'+cats)
            +(typeof(cats) == "undefined" ? '?' : '&') + 'rootcolor='+(typeof(onlyroot) == "undefined" ? '1' : onlyroot),
        timezone: "UTC",
        timeFormat: 'HH:mm',
        forceEventDuration: true,
        header: {
            left: '',
            center: 'title',
            right: 'today basicDay,basicWeek,month prevYear,prev,next,nextYear'
        },
        locale: "fr",
        defaultView: "basicWeek",
        height: $(window).height() - 110,
        navLinks: true,
        navLinkDayClick: function(date, jsEvent) {
            $("#viewday").prop("checked", true);
            $("#calendarview").hide();
            $("#timeline").show();
            $("#calendar").show();
            $("#export").show();
            var dayString = date.format('DD/MM/YYYY');
            $("#calendar input[type=text].date").val(dayString);
            $('#timeline').timeline("view", "day");
            $.post(url + 'events/saveview?view=24');
            $("#calendar input[type=text].date").trigger('change');
        },
        eventAfterAllRender: function(view) {
            if(lastupdateFC == 0) {
                lastupdateFC = new Date();
            }
            timerFC = setTimeout(updateFC, 10000);
        },
        loading: function(isLoading, view){
            if(isLoading) {
                var loading = $('<div class="loading" style="top:0px"></div>');
                $("#calendarview .fc-view").append(loading);
            } else {
                $("#calendarview .loading").remove();
            }
        },
        eventMouseover: function(event, jsEvent, view){
            var text = '<table class="table"><tbody>';
            $.each(event.fields, function (nom, contenu) {
                text += "<tr>";
                text += "<td>" + nom + "</td><td> :&nbsp;</td><td>" + contenu + "</td>";
                text += "</tr>";
            });
            text += "</tbody></table>";
            $(this).tooltip({
                title: '<span class="elmt_tooltip">' + text + '</span>',
                container: 'body',
                html: 'true',
                placement:'auto top',
                viewport: '#calendarview',
                template: '<div class="tooltip tooltip-actions" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
            }).tooltip('show');
            $(this).find('.actions').show();
        },
        eventMouseout: function(event, jsEvent, view){
            $(this).find('.actions').hide();
            $(this).tooltip('destroy');
        },
        eventRender: function(event, element){
            //add id
            element.addClass('cal-event-'+event.id);
            //change rendering accordind to status
            switch (event.status_id) {
                case 4:
                    element.find('.fc-title').css('text-decoration', 'line-through');
                    break;
                case 5:
                    if($('#filter_none').closest('.filter').hasClass('active')) {
                        element.find('.fc-title').addClass('dlt');
                        if(event.textColor.localeCompare('white') == 0){
                            element.find('.fc-title').addClass('dlt-white');
                        } else {
                            element.find('.fc-title').addClass('dlt-black');
                        }
                    } else if($('#filter_deleted').closest('.filter').hasClass('active')) {
                        element.hide();
                    }
                    break;
            }
            //protection and recurrence
            if(event.recurr == true) {
                element.find('.fc-title').append(' <span data-toggle="tooltip" data-container="body" data-placement="bottom" data-title="'+event.recurr_readable+'" class="badge recurrence">R</span>');
                element.find('span.badge.recurrence').tooltip();
            } else {
                element.find('.modify-evt').data('recurr', '');
            }
            if(event.scheduled) {
                element.find('.fc-title').append(' <a href="#"><span class="badge scheduled" data-url="'+event.url_file1+'" data-id="'+event.id+'" data-files="'+event.files+'">P</span></a>');
            }
            //actions
            var actions = $('<span class="actions"></span>');
            actions.append($('<a href="#" class="modify-evt" data-id="' + event.id + '" data-name="' + event.name + '" data-recurr="' + event.recurr + '">' +
                ' <span class="glyphicon glyphicon-pencil" style="color:'+event.textColor+'"></span>' +
                '</a>'));
            actions.append($('<a href="#" class="checklist-evt" data-id="' + event.id + '" data-name="' + event.name + '">'+
                ' <span class="glyphicon glyphicon-tasks" style="color:'+event.textColor+'"></span>'+
                '</a>'));
            var tooltip = $('<a href="#" class="tooltip-evt" data-id="' + event.id + '">'+
                ' <span class="glyphicon glyphicon-chevron-up" style="color:'+event.textColor+'"></span>'+
                '</a>');
            actions.append(tooltip)
            actions.css('display', "none");
            element.find('.fc-content').append(actions);
            var id = event.id;
            var txt = '<p class="elmt_tooltip actions">'
                + '<p><a href="#" data-id="'+event.id+'" class="send-evt"><span class="glyphicon glyphicon-envelope"></span> Envoyer IPO</a></p>';
            if(event.status_id < 4 && event.modifiable){ //modifiable, non annulé et non supprimé
                if(event.punctual === false){
                    if(event.star === true){
                        txt += '<p><a href="#" data-id="'+id+'" class="evt-non-important"><span class="glyphicon glyphicon-leaf"></span> Non important</a></p>';
                    } else {
                        txt += '<p><a href="#" data-id="'+id+'" class="evt-important"><span class="glyphicon glyphicon-fire"></span> Important</a></p>';
                    }
                }
                txt += '<p><a href="#add-note-modal" class="add-note" data-toggle="modal" data-id="'+id+'"><span class="glyphicon glyphicon-comment"></span> Ajouter une note</a></p>';
                txt += '<p><a href="#" data-id="'+id+'" class="cancel-evt"><span class="glyphicon glyphicon-remove"></span> Annuler</a></p>';
            }
            if(event.status_id < 5 && event.deleteable) {
                txt += '<p><a href="#" data-id="'+id+'" class="delete-evt"><span class="glyphicon glyphicon-trash"></span> Supprimer</a></p>';
            }
            txt += '</p>';
            tooltip.popover({
                container: '#calendarview',
                content: txt,
                placement:'auto top',
                html: 'true',
                viewport: '#calendarview',
                template: '<div class="popover label_elmt" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
            });
        }
    });

    /* update calendar view every 10s */
    var lastupdateFC = 0;
    var timerFC;
    var updateFC = function() {
        clearTimeout(timerFC);
        var urlFC = url + 'events/geteventsFC';
        if (typeof(cats) == "undefined") {
            urlFC += (lastupdateFC != 0 ? '?lastupdate=' + lastupdateFC.toUTCString() : '') ;
        } else {
            urlFC += '?'+cats + (lastupdateFC != 0 ? '&lastupdate=' + lastupdateFC.toUTCString() : '');
        }

        var view = $("#calendarview").fullCalendar('getView');
        var start = view.start.format("YYYY-MM-DD");
        if(urlFC.indexOf('?') > 0){
            urlFC += '&start='+start;
        } else {
            urlFC += '?start='+start;
        }
        urlFC += '&rootcolor='+onlyroot;
        var end = view.end.format("YYYY-MM-DD");
        urlFC += '&end='+end;
        return $.getJSON(urlFC,
            function (data, textStatus, jqHXR) {
                if (jqHXR.status !== 304) {
                    lastupdateFC = new Date(jqHXR.getResponseHeader("Last-Modified"));
                    $("#calendarview").fullCalendar('refetchEvents');
                }
            }).always(function () {
            timerFC = setTimeout(function () {
                updateFC();
            }, 10000);
        });
    };

    $('#calendarview').on('click', '.tooltip-evt', function(){
        var me = $(this);
        me.popover('toggle');
    });

    $('#calendarview').on('click', '.evt-important', function(e){
        e.preventDefault();
        var me = $(this);
        var id = me.data('id');
        $.post(url+'events/changefield?id='+id+'&field=star&value=1',
            function(data){
                displayMessages(data.messages);
                $('#calendarview').fullCalendar('refetchEvents');
            }
        );
        $(".cal-event-"+id).find('.tooltip-evt').popover('hide');
    });

    $('#calendarview').on('click', '.evt-non-important', function(e){
        e.preventDefault();
        var me = $(this);
        var id = me.data('id');
        $.post(url+'events/changefield?id='+id+'&field=star&value=0',
            function(data){
                displayMessages(data.messages);

                $('#calendarview').fullCalendar('refetchEvents');
            }
        );
        $(".cal-event-"+id).find('.tooltip-evt').popover('hide');
    });

    $('#calendarview').on('click', '.send-evt', function(e){
        e.preventDefault();
        var me = $(this);
        var id = me.data('id');
        $.post(url+'events/sendevent?id='+id,
            function(data){
                displayMessages(data.messages);
            }
        );
        $(".cal-event-"+id).find('.tooltip-evt').popover('hide');
    });

    $('#calendarview').on('click', '.cancel-evt', function(e){
        e.preventDefault();
        var me = $(this);
        var id = me.data('id');
        $.post(url+'events/changefield?id='+id+'&field=status&value=4',
            function(data){
                displayMessages(data.messages);

                $('#calendarview').fullCalendar('refetchEvents');
            }
        );
        $(".cal-event-"+id).find('.tooltip-evt').popover('destroy');
    });

    $('#calendarview').on('click', '.delete-evt', function(e){
        e.preventDefault();
        var me = $(this);
        var id = me.data('id');
        $.post(url+'events/deleteevent?id='+id,
            function(data){
                displayMessages(data.messages);
                $('#calendarview').fullCalendar('refetchEvents');
            }
        );
        $(".cal-event-"+id).find('.tooltip-evt').popover('hide');
    });

    $("#calendarview").on('click', "span.badge.scheduled", function(e){
        e.preventDefault();
        var me = $(this);
        var id = me.data('id');
        var files = me.data('files');
        var urlF = me.data('url');
        if(!isNaN(id)){
            if(files === 1 && typeof urlF != 'undefined'){
                window.open(window.location.origin+url+urlF);
            } else {
                loadFiche(id, "events/getfiche", true);
            }
        }
    });

    $(document).on('click', function (e) {
        $('#calendarview').find('.tooltip-evt').each(function () {
            // hide any open popovers when the anywhere else in the body is clicked
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                $(this).popover('hide');
            }
        });
    });

    if(typeof(forceMonth) != "undefined" && forceMonth == true) {
        $("#viewmonth").trigger('click');
    }

    $("#date").bootstrapMaterialDatePicker({
            format: "DD/MM/YYYY",
            time: false,
            lang: 'fr',
            cancelText: "Annuler",
            weekStart : 1,
            nowButton: true,
            nowText: "Jour",
            switchOnClick: true
    });
    
    $("#date").on('change', function(){
        var temp = $('#calendar input[type=text].date').val().split('/');
        var date = new Date(temp[2], temp[1] - 1, temp[0], "5");
        $("#timeline").timeline("day", date.toString());
        $.post(url + 'events/saveday?day="' + $('#calendar input[type=text].date').val()+'"');
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

    /**
     * Ouverture des protections via le label
     */
    $("#timeline").on('click', ".label_elmt span.badge.scheduled", function(e){
        e.preventDefault();
        var me = $(this);
        var id = parseInt(me.closest(".elmt").data('ident'));
        if(!isNaN(id)){
        	var event = $("#timeline").timeline('getEvent', id);
        	if(event !== null){
        		if(event.files === 1){
        			window.open(window.location.origin+url+event.url_file1);
        		} else {
        			loadFiche(id, "events/getfiche", true);
        		}
        	} else {
        		loadFiche(id, "events/getfiche", true);
        	}
        }
    });
    
    /* ******************************* */

    //hack très moche pour corriger la couleur des dropdown dans la navbar sous chrome
    //à supprimer une fois corrigé upstream
    var color = $('#navbar-first').css('background-color');
    $("#navbar-first option").css('background-color', color);

    //vérification du maintien de l'authentification
    //si on reçoit un 403 alors que précédemment on a reçu un 200
    //cela veut dire que l'authentification a été réinitialisé par le serveur
    //dans ce cas on force le rechargement de la page
    var lastCode = 0;
    var testAuthentication = function() {
        $.getJSON(url + 'events/testAuthentication',
            function (data, textStatus, jqHXR) {
                lastCode = 200;
            }).fail(function(jqHXR){
            if (jqHXR.status === 401 && lastCode === 200) {
                location.reload();
            }
        });
    };
    testAuthentication();
    setInterval(testAuthentication, 10000);

});


