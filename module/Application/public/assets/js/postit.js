
var background = ["lazur-bg", "yellow-bg", "red-bg", "navy-bg"];
var rotate = ["rotate-1", "rotate-2"];

var lastupdate_postit;
var timerpostit;

var newPostIt = function(id, date, name, content) {
    var bg = background[Math.round(Math.random()*3)];
    var rotation = rotate[Math.round(Math.random())];
    var options = {year: "numeric", month: "numeric", day: "numeric",
        hour: "numeric", minute: "numeric"};
    var dateString = new Intl.DateTimeFormat('fr-FR', options).format(date);
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
                        var newPostItem = newPostIt(value.id, value.date, value.name, value.content);
                        var position = Cookies.getJSON('postit-'+value.id);
                        if(typeof(position) !== 'undefined') {
                            newPostItem.css({
                               "position": "relative",
                               "top": position.top,
                               "left": position.left
                            });
                        }
                        $("#notes").append(newPostItem);
                    }
                } else {
                    if (value.open == '0') {
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
            }
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

