var getLine = function(event){
    var tr = $('<tr class="'+(event.isroot ? 'event' : 'child')+'"></tr>');
    tr.append('<td><i class="'+(event.isroot ? (event.children ? 'icon-chevron-right' : '') : 'icon-arrow-right')+'"></i>'+event.id+'</td>');
    tr.append('<td>'+event.name+'</td>');
    tr.append('<td>'+event.category+'</td>');
    tr.append('<td>'+event.status+'</td>');
    tr.append('<td>'+event.start_date+'</td>');
    tr.append('<td>'+event.end_date+'</td>');
    tr.append('<td>'+event.duration+'</td>');
    return tr;
};

var fillTable = function(events){
    $.each(events, function(i, item){
        $("#table-events tbody").append(getLine(item));
        $.each(item.children, function(c, child){
            $("#table-events tbody").append(getLine(child));
        });
    });
};

var url;

var setUrl = function(urlt){
  url = urlt;  
};

$(document).ready(function(){
    //init view with current day
    $.getJSON('ipo/index/getevents', function(data){
        fillTable(data);
    });
    
    $("#table-events").on('click', 'tr.event', function(){
        $(this).nextUntil('tr.event').toggle();
        $(this).find('i').toggleClass('icon-chevron-right icon-chevron-down');
    });
    
    $("#prev-day").on('click', function(e){
        var date = new Date($('#day').data('day'));
        var prevday = new Date($('#day').data('day'));
        prevday.setDate(date.getDate()-1);
        $('#day').data('day', prevday.toUTCString());
        $.getJSON('ipo/index/getevents?day='+prevday.toUTCString(), function(data){
            $('#day').html(prevday.toLocaleDateString());
            $('#table-events tbody').empty();
            fillTable(data);
        });
    });
    
    $("#next-day").on('click', function(e){
        var date = new Date($('#day').data('day'));
        var nextday = new Date($('#day').data('day'));
        nextday.setDate(date.getDate()+1);
        $('#day').data('day', nextday.toUTCString());
        $.getJSON('ipo/index/getevents?day='+nextday.toUTCString(), function(data){
            $('#day').html(nextday.toLocaleDateString());
            $('#table-events tbody').empty();
            fillTable(data);
        });
    });
});


