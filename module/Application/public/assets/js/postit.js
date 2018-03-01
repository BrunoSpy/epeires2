
var background = ["lazur-bg", "yellow-bg", "red-bg", "navy-bg"];
var rotate = ["rotate-1", "rotate-2"];

var lastupdate_postit;
var timerpostit;

var newPostIt = function(id, date, name, content) {
    var bg = background[Math.floor(Math.random()*4)];
    var rotation = rotate[(Math.random() < 0.5 ? 0 : 1)];
    var options = {year: "numeric", month: "numeric", day: "numeric",
        hour: "numeric", minute: "numeric"};
    var dateString = new Intl.DateTimeFormat('fr-FR', options).format(new Date(date));
    var li = $('<li data-id="'+id+'" id="postit-'+id+'"><div class="'+bg+' '+rotation+'">'
                +'<div class="postit-handle"><small>'+dateString+'</small><h5>'+name+'</h5></div>'
                +'<p class="postit-content">'+content+'</p>'
                +'<div class="buttons"><a href="#" class="btn btn-xs btn-outline postit-mod">Modifier</a><a href="#" class="btn btn-xs postit-delete">Supprimer</a></div></div></li>');
    return li;
};

var refreshPostit = function(url){
    $.getJSON(url + 'events/getpostits'+(typeof lastupdate_postit != 'undefined' ? '?lastupdate='+lastupdate_postit.toUTCString() : ''),
        function(data, textStatus, jqHXR){
        if(jqHXR.status != 304) {
            lastupdate_postit = new Date(jqHXR.getResponseHeader("Last-Modified"));
            $.each(data, function (index, value) {
                if ($("#postit-" + value.id).length === 0) {
                    if (value.open == '1') {
                        var newPostItem = newPostIt(value.id, value.datetime, value.name, value.content);
                        var position = Cookies.getJSON('postit-'+value.id);
                        if(typeof(position) !== 'undefined') {
                            newPostItem.css({
                               "position": "relative",
                               "top": position.top+'px',
                               "left": position.left+'px'
                            });
                        } else {
                            var nextTop = 0;
                            $("#notes li").each(function(i,e){
                                var cssTop = $(this).css('top');
                                var currentTop = parseInt(cssTop.substr(0, cssTop.length-2)) + i*230;
                                if(currentTop > nextTop) {
                                    nextTop = currentTop;
                                }
                            });
                            var existingNotes = $("#notes li").length;
                            var top = (existingNotes == 0 ? nextTop : nextTop+230-230*existingNotes);
                            if(top + existingNotes*230 + 230 > window.innerHeight) {
                                //out of view -> random vertical position Math.abs(Math.floor(Math.random()*(window.innerHeight-240)))
                                top = Math.abs(Math.floor(Math.random()*(window.innerHeight-340))) - 230*existingNotes;
                            }
                            newPostItem.css({
                                "position": "relative",
                                "top": top+"px"});
                        }
                        var classes = Cookies.get('postit-css-'+value.id);
                        if(typeof(classes) !== 'undefined') {
                            newPostItem.children().removeClass().addClass(classes);
                        } else {
                            Cookies.set('postit-css-'+value.id, newPostItem.children().attr('class'));
                        }
                        $("#notes").append(newPostItem);
                    }
                } else {
                    if (value.open == '0') {
                        $("#postit-" + value.id).nextAll().each(function(i,e){
                            $(this).css('top', '+=230px');
                            var id = $(this).data('id');
                            var topCss = $(this).css('top');
                            var leftCss = $(this).css('left');
                            Cookies.set('postit-'+id, {top: topCss.substr(0, topCss.length-2), left: leftCss.substr(0, leftCss.length-2)});
                        });

                        $("#postit-" + value.id).remove();
                    } else {
                        var postitItem = $("#postit-" + value.id);
                        postitItem.find('h5').text(value.name);
                        postitItem.find('.postit-content').text(value.content);
                    }
                }
            });
        }
    }).always(function(e){
        $( "ul#notes li" ).draggable({
            handle: ".postit-handle",
            stop: function(event, ui){
                Cookies.set('postit-'+$(event.target).data('id'),ui.position);
            },
            containment:".page-wrap"
        });
        timerpostit = setTimeout(function(){refreshPostit(url)}, 10000);
    });
};

var postit = function(url) {

    refreshPostit(url);

    $(document).on('submit', '#add-postit', function(e) {
        e.preventDefault();
        var me = $(this);
        var id = $("#add-postit").data('id');
        var idurl = '';
        if(typeof(id) !== 'undefined') {
            idurl = '?id='+id;
        }
        $.post(url + 'events/addpostit'+idurl, me.serialize(), function (data) {
            clearTimeout(timerpostit);
            refreshPostit(url);
            displayMessages(data);
            me.parents('.modal').modal('hide');
        });
    });

    $(document).on('click', '.postit-mod', function(e){
        var me = $(this).closest('li');
        $('#add-postit input[name="name"]').val(me.find('h5').text());
        $('#add-postit textarea').val(me.find('.postit-content').text());
        $('#add-postit').data('id', me.data('id'));
        $('#add-postit-modal').modal('show');
    });

    $(document).on('click', '.postit-delete', function(e){
        var id = $(this).closest('li').data('id');
        $.post(url+'events/deletepostit?id='+id, function(data){
            displayMessages(data);
            clearTimeout(timerpostit);
            refreshPostit(url);
        });
    });

};

