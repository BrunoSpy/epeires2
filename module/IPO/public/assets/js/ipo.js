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
        e.preventDefault();
        var temp = $('#day').val().split('/');
    	var date = new Date(temp[2],temp[1]-1,temp[0],12);
        var prevday = new Date();
        prevday.setDate(date.getDate()-1);
        var prevString = FormatNumberLength(prevday.getUTCDate(), 2) + "/"
                + FormatNumberLength(prevday.getUTCMonth()+1, 2) + "/"
                + FormatNumberLength(prevday.getUTCFullYear(), 4);
        $("#day").val(prevString);
        $("#day").trigger('change');
    });
    
    $("#next-day").on('click', function(e){
        e.preventDefault();
        console.log($("#day").val());
        var temp = $('#day').val().split('/');
    	var date = new Date(temp[2],temp[1]-1,temp[0],12);
        var nextday = new Date();
        nextday.setDate(date.getDate()+1);
        var nextString = FormatNumberLength(nextday.getUTCDate(), 2) + "/"
                + FormatNumberLength(nextday.getUTCMonth()+1, 2) + "/"
                + FormatNumberLength(nextday.getUTCFullYear(), 4);
        $("#day").val(nextString);
        $("#day").trigger('change');
    });
    
    $("#day").datepicker({
            dateFormat: "dd/mm/yy",
            showButtonPanel: true
    });
    
    
    $("#day").on('change', function(e){
        e.preventDefault();
        var temp = $('#day').val().split('/');
    	var new_date = new Date(temp[2],temp[1]-1,temp[0],12);
        $.getJSON('ipo/index/getevents?day='+new_date.toUTCString(), function(data){
            $('#table-events tbody').empty();
            fillTable(data);
        });
    });
});


