/**
 * @author Bruno Spyckerelle
 */

var antenna = function(url, frequencyTestMenu){

    //if true, switch the button to its previous state
    var back = true;

    var currentantennas;
    var currentfrequencies;

    var timer;

    $(document).on('click','.switch-coverture', function(){
        var me = $(this);
        $('a.actions-freq, a#changefreq').popover('hide');
        $.post(url+'frequencies/switchcoverture'
            +'?frequencyid='+me.data("freqid")
            +'&cov='+me.data('cov')
            + (me.data('cause') ? '&cause='+me.data('cause') : ''),
            function(data){
                displayMessages(data);
                if(!data['error']){
                    var frequency = $('.frequency-'+me.data('freqid'));
                    var antennas = frequency.siblings('.antennas');
                    if(me.data('cov') == '0'){
                        antennas.find('.mainantenna-color').addClass('background-selected');
                        antennas.find('.backupantenna-color').removeClass('background-selected');
                    } else {
                        antennas.find('.backupantenna-color').addClass('background-selected');
                        antennas.find('.mainantenna-color').removeClass('background-selected');
                    }
                    clearTimeout(timer);
                    doFullUpdate();
                };
            }, 'json');
    });

    $(document).on('click', '.action-changefreq', function(event){
        var me = $(this);
        //close all popover
        $('a.actions-freq, a#changefreq').popover('hide');
        $.post(url+'frequencies/switchfrequency?fromid='+me.data('fromfreq')+'&toid='+me.data('tofreq'), function(data){
            displayMessages(data.messages);
            //force page refresh
            clearTimeout(timer);
            doFullUpdate();
        }, 'json');
    });

    $(document).on('click', '.switch-freq-state', function(e){
        var me = $(this);
        $('a.actions-freq, a#changefreq').popover('hide');
        $.post(url+'frequencies/switchFrequencyState?freqid='+me.data('freqid')+'&state='+me.data('state'), function(data){
            displayMessages(data);
            //force page refresh
            clearTimeout(timer);
            doFullUpdate();
        }, 'json');
    });


    $('.antenna-switch').on('change', function(e){
        var newState = $(this).is(':checked');
        $('button#end-antenna-href').attr('href', $(this).data('href')+"&state="+newState);
        $('#antenna_name').html($(this).data('antenna'));
        $("#cancel-antenna").data('antenna', $(this).data('antennaid')) ;

        if(!newState){
            var select = $('<select class="form-control" id="freqsImpacted" name="frequencies[]" multiple></select>');
            $.getJSON(url+'frequencies/getFrequenciesOnAntenna?antennaid='+$(this).data('antennaid'), function(data){
                $.each(data, function(key, value){
                    select.append($('<option>', {
                        value: key,
                        text: value
                    }))
                });
                var form = $('<form class="form-horizontal"><div class="form-group">' +
                    '<label class="control-label col-sm-3" for="frequencies">Fréquences impactées : </label>' +
                    '<div class="col-sm-9">'+select[0].outerHTML+'</div>'+
                    '</div></form>')
                $("#confirm-end-event .modal-body").html("<p>Voulez-vous vraiment créer un nouvel évènement antenne ?</p>"+
                    "<p>L'heure actuelle sera utilisée comme heure de début.</p>" +
                    "<p><strong>Astuce</strong> : laissez vide pour sélectionner toutes les fréquences ou utilisez CTRL pour sélectionner plusieurs fréquences.</p>").append(form);
            });

        } else {
            $("#confirm-end-event .modal-body").html( "<p>Voulez-vous vraiment terminer l'évènement antenne en cours ?</p>"+
                "<p>L'heure actuelle sera utilisée comme heure de fin.</p>");
        }
        clearTimeout(timer);
        $("#confirm-end-event").modal('show');
    });

    $("#confirm-end-event").on('hide.bs.modal', function(){
        if(back){
            var button = $('#switch_'+$("#cancel-antenna").data('antenna'));
            button.prop('checked', !button.is(':checked') );
        }
        clearTimeout(timer);
        timer = setTimeout(doFullUpdate, 30000);
    });

    $("#end-antenna-href").on('click', function(event){
        event.preventDefault();
        back=false;
        $("#confirm-end-event").modal('hide');
        $.post($("#end-antenna-href").attr('href')+'&'+$('#freqsImpacted').serialize(), function(data){
            back = true;
            displayMessages(data.messages);
            var switchbtn = $('#switch_'+$("#cancel-antenna").data('antenna'));
            if(data.messages['error']){
                //dans le doute, on remet le bouton à son état antérieur
                var state = switchbtn.is(':checked');
                switchbtn.prop('checked', !state);
            } else {
                currentantennas = data.antennas;
                currentfrequencies = data.frequencies;
                updateantennas();
                updatefrequencies();
                updateantennas();
                updateActions();
            }
        }, 'json');
    });

    $(document).on('click', '.switch-antenna', function(event){
        var me = $(this);
        $('a.actions-antenna').popover('hide');
        $.post(url+'frequencies/switchantenna?antennaid='+me.data('antennaid')+'&state='+me.data('state')+'&freq='+me.data('freqid'), function(data){
            displayMessages(data.messages);
            currentantennas = data.antennas;
            currentfrequencies = data.frequencies;
            updateantennas();
            updatefrequencies();
            updateantennas();
            updateActions();
        }, 'json');
    });

    $("#antennas tr").hover(
        //in
        function(){
            $('.antenna-'+$(this).data('id')).closest('.sector').addClass('background-status-test');
        },
        //out
        function(){
            $('.antenna-'+$(this).data('id')).closest('.sector').removeClass('background-status-test');
        });

    $(".antenna-color").hover(
        function(){
            $("#antenna-"+$(this).data('antennaid')+" td").css({'background-color': 'yellow'});
        },
        function(){
            $("#antenna-"+$(this).data('antennaid')+" td").css({'background-color': ''});
        });

    $(document).on('click', '#changefreq', function(event){
        event.preventDefault();
        var me = $(this);
        $.post(url+'frequencies/getfrequencies?id='+me.data('freqid')+'&groupid='+me.data('groupid'), function(data){
            var list = $("<ul id=\"list-change-freq-"+me.data('freqid')+"\"></ul>");
            if(data['backup']) {
                var dataArray = [];
                $.each(data.backup, function(key, value){
                    dataArray.push([key, data.backup[key]]);
                });
                dataArray.sort(function(a, b){
                    return a[1]['place'] > b[1]['place'] ? 1 : a[1]['place'] < b[1]['place'] ? -1 : 0;
                });
                if(dataArray.length > 0) {
                    list.append('<li class="title">Fréquences préconisées</li>');
                    for(var i = 0; i < dataArray.length; i++){
                        list.append("<li><a href=\"#\" class=\"action-changefreq\" data-fromfreq=\""+me.data('freqid')+"\" data-tofreq=\""+dataArray[i][0]+"\">"+dataArray[i][1]['data']+"</a></li>");
                    }
                }
            }
            if(data['preferred']) {
                var dataArray = [];
                $.each(data.preferred, function(key, value){
                    dataArray.push([key, data.preferred[key]]);
                });
                dataArray.sort(function(a, b){
                    return a[1]['place'] > b[1]['place'] ? 1 : a[1]['place'] < b[1]['place'] ? -1 : 0;
                });
                if(dataArray.length > 0) {
                    list.append('<li class="title">Fréquences du groupe</li>');
                    for(var i = 0; i < dataArray.length; i++){
                        list.append("<li><a href=\"#\" class=\"action-changefreq\" data-fromfreq=\""+me.data('freqid')+"\" data-tofreq=\""+dataArray[i][0]+"\">"+dataArray[i][1]['data']+"</a></li>");
                    }
                }
            }


            if((data['backup'] && Object.keys(data.backup).length > 0 ) || (data['preferred'] && Object.keys(data.preferred).length > 0)) {
                list.append('<li class="title">Autres fréquences</li>');
            }
            //convert json into array to sort it
            var dataArray = [];
            $.each(data.others, function(key, value){
                dataArray.push([key, data.others[key]]);
            });
            dataArray.sort(function(a, b){
                return a[1]['place'] > b[1]['place'] ? 1 : a[1]['place'] < b[1]['place'] ? -1 : 0;
            });

            for(var i = 0; i < dataArray.length; i++){
                list.append("<li><a href=\"#\" class=\"action-changefreq\" data-fromfreq=\""+me.data('freqid')+"\" data-tofreq=\""+dataArray[i][0]+"\">"+dataArray[i][1]['data']+"</a></li>");
            }
            if(list.find('li').length > 0 ){
                var div = $('<div class="vertical-scroll"></div>');
                div.append(list);
                me.popover({
                    content: div,
                    html: true,
                    container: '#popover-frequencies',
                    trigger: 'manual'
                });
                me.popover('show');
                div.closest('.popover-content').css('padding', '0px');
            };
        }, 'json');
    });

    $(document).on('click', '.brouillage', function(e){
        $("#frequency_name").html($(this).data('freqname'));
        $('#form-brouillage').load(url+'frequencies/formbrouillage?id='+$(this).data('freqid'), function(){
            $("input[name=startdate]").timepickerform({'id':'start', 'required':true});
        });
    });

    $('.sector-color').on('click', function(e){
        e.preventDefault();
        $(this).find('a.actions-freq').trigger('click');
    });

    $('.antenna-color').on('click', function(e){
        e.preventDefault();
        $(this).find('a.actions-antenna').trigger('click');
    });

    $('#form-brouillage').on('submit', function(e){
        e.preventDefault();
        $.post($("#form-brouillage form").attr('action'), $("#form-brouillage form").serialize(), function(data){
            if(!data.messages['error']){
                $("#fne-brouillage").modal('hide');
            }
            displayMessages(data);
        });
    });

    //actions
    var updateActions = function(){
        $("a.actions-freq").each(function(index, element){
            var sector = $(this).closest('.sector');
            var list = $("<ul></ul>");
            var freqid = $(this).data('freq');
            var groupid = $(this).data('groupid');
            if(currentfrequencies[freqid].status){
                list.append("<li><a href=\"#\" class=\"switch-freq-state\" data-groupid=\""+groupid+"\" data-freqid=\""+freqid+"\" data-state=\"false\">Fréquence indisponible</a></li>");
            } else {
                list.append("<li><a href=\"#\" class=\"switch-freq-state\" data-groupid=\""+groupid+"\" data-freqid=\""+freqid+"\" data-state=\"true\">Fréquence disponible</a></li>");
            }

            var mainantennacolor = sector.find('.antennas .mainantenna-color');
            var backupantennacolor = sector.find('.antennas .backupantenna-color');
            var bicouv = (sector.find(".antennas li").length > 1);
            if(bicouv) {
                //menus inutiles si une seule couv
                if (mainantennacolor.filter('.background-selected').find('li').length === mainantennacolor.find('li').length && backupantennacolor.filter('.background-status-ok').find('li').length === backupantennacolor.find('li').length) {
                    list.append("<li><a href=\"#\" class=\"switch-coverture\" data-cov=\"1\" data-freqid=\"" + $(this).data('freq') + "\">" + i18n.t('frequencies.change_couv_secours') + "</a></li>");
                }
                if (backupantennacolor.filter('.background-selected').find('li').length === backupantennacolor.find('li').length && mainantennacolor.filter('.background-status-ok').find('li').length === mainantennacolor.find('li').length) {
                    list.append("<li><a href=\"#\" class=\"switch-coverture\" data-cov=\"0\" data-freqid=\"" + $(this).data('freq') + "\">" + i18n.t('frequencies.change_couv_normale') + "</a></li>");
                }
                if (frequencyTestMenu
                    && mainantennacolor.filter('.background-selected').find('li').length === mainantennacolor.find('li').length
                    && backupantennacolor.filter('.background-status-ok').find('li').length === backupantennacolor.find('li').length) {
                    list.append("<li><a href=\"#\" class=\"switch-coverture\" data-cause=\"Test couverture secours\" data-cov=\"1\" data-freqid=\"" + $(this).data('freq') + "\">" + "Test couverture secours" + "</a></li>");
                }
                //retour à la fréquence nominale
                if (sector.find(".sector-name span").length > 0) {
                    list.append("<li><a href=\"#\" class=\"action-changefreq\" data-fromfreq=\"" + sector.data('freq') +
                        "\" data-tofreq=\"" + sector.data('freq') + "\">Retour à la fréquence nominale</a></li>");
                }
            }
            var submenu = $("<li class=\"submenu\"></li>");
            submenu.append("<a id=\"changefreq\" data-groupid=\""+groupid+"\" data-freqid=\""+sector.data('freq')+"\" href=\#\>Changer de fréquence &nbsp;</a>");
            list.append(submenu);
            list.append("<li><a class=\"brouillage\" data-toggle=\"modal\" data-freqname=\""+$(this).html()+"\" data-freqid=\""+sector.data('freq')+"\" href=\"#fne-brouillage\">Brouillage</a></li>");
            list.append("<li class=\"divider\"></li>");
            //antennes
            var mainantenna = sector.find('.antennas .mainantenna-color.antenna-color:not(.antenna-climax-color)');
            if(mainantenna.hasClass('background-status-ok')){
                list.append("<li><a href=\"#\" class=\"switch-antenna\" data-freqid=\""+freqid+"\" data-state=\"false\" data-antennaid=\""+mainantenna.data('antennaid')+"\">Antenne principale HS (uniquement cette fréq.)</a></li>");
            } else {
                list.append("<li><a href=\"#\" class=\"switch-antenna\" data-freqid=\""+freqid+"\" data-state=\"true\" data-antennaid=\""+mainantenna.data('antennaid')+"\">Antenne principale OPE (uniquement cette fréq.)</a></li>");
            }
            if(bicouv) {
                var backupantenna = sector.find('.antennas .backupantenna-color.antenna-color:not(.antenna-climax-color)');
                if (backupantenna.hasClass('background-status-ok')) {
                    list.append("<li><a href=\"#\" class=\"switch-antenna\" data-freqid=\"" + freqid + "\" data-state=\"false\" data-antennaid=\"" + backupantenna.data('antennaid') + "\">Antenne secours HS (uniquement cette fréq.)</a></li>");
                } else {
                    list.append("<li><a href=\"#\" class=\"switch-antenna\" data-freqid=\"" + freqid + "\" data-state=\"true\" data-antennaid=\"" + backupantenna.data('antennaid') + "\">Antenne secours OPE (uniquement cette fréq.)</a></li>");
                }
            }
            //si climax
            var mainantennaclimax = sector.find('.antennas .mainantenna-color.antenna-climax-color');
            if(mainantennaclimax.length > 0){
                if(mainantennaclimax.hasClass('background-status-ok')){
                    list.append("<li><a href=\"#\" class=\"switch-antenna\" data-freqid=\""+freqid+"\" data-state=\"false\" data-antennaid=\""+mainantennaclimax.data('antennaid')+"\">Antenne principale climaxée HS (uniquement cette fréq.)</a></li>");
                } else {
                    list.append("<li><a href=\"#\" class=\"switch-antenna\" data-freqid=\""+freqid+"\" data-state=\"true\" data-antennaid=\""+mainantennaclimax.data('antennaid')+"\">Antenne principale climaxée OPE (uniquement cette fréq.)</a></li>");
                }
            }
            if(bicouv) {
                var backupantennaclimax = sector.find('.antennas .backupantenna-color.antenna-climax-color');
                if (backupantennaclimax.length > 0) {
                    if (backupantennaclimax.hasClass('background-status-ok')) {
                        list.append("<li><a href=\"#\" class=\"switch-antenna\" data-freqid=\"" + freqid + "\" data-state=\"false\" data-antennaid=\"" + backupantennaclimax.data('antennaid') + "\">Antenne secours climaxée HS (uniquement cette fréq.)</a></li>");
                    } else {
                        list.append("<li><a href=\"#\" class=\"switch-antenna\" data-freqid=\"" + freqid + "\" data-state=\"true\" data-antennaid=\"" + backupantennaclimax.data('antennaid') + "\">Antenne secours climaxée OPE (uniquement cette fréq.)</a></li>");
                    }
                }
            }
            if(list.find('li').length > 0 ){
                if($(this).data('bs.popover')) {
                    $(this).data('bs.popover').options.content = list;
                } else {
                    $(this).popover({
                        title: "",
                        content: list,
                        html: true,
                        container: '#popover-frequencies',
                        trigger: 'manual'
                    });
                }
            };
        });
        $("a.actions-antenna").each(function(){
            var antenna = $(this).closest('.antenna-color');
            var freqid = $(this).closest('.sector').find('.actions-freq').data('freq');
            var list = $('<ul></ul>');
            if(antenna.hasClass('background-status-ok')){
                list.append("<li><a href=\"#\" class=\"switch-antenna\" data-freqid=\""+freqid+"\" data-state=\"false\" data-antennaid=\""+antenna.data('antennaid')+"\">Antenne HS (uniquement cette fréq.)</a></li>");
            } else {
                list.append("<li><a href=\"#\" class=\"switch-antenna\" data-freqid=\""+freqid+"\" data-state=\"true\" data-antennaid=\""+antenna.data('antennaid')+"\">Antenne OPE (uniquement cette fréq.)</a></li>");
            }
            if(list.find('li').length > 0){
                if($(this).data('bs.popover')) {
                    $(this).data('bs.popover').options.content = list;
                } else {
                    $(this).popover({
                        title: "",
                        content: list,
                        html: true,
                        container: '#popover-frequencies',
                        trigger: 'manual'
                    });
                }
            }
        });
    };

    $('a.actions-antenna, a.actions-freq').on('click', function(event){
        event.preventDefault();
        event.stopPropagation();
        $(this).popover('toggle');
    });

//	//hide popover if click outside
    $(document).mousedown(function(e){
        var container = $(".popover");
        if(!container.is(e.target) && container.has(e.target).length === 0){
            $("a.actions-freq, a.actions-antenna, a.actions-changefreq").not($(e.target)).popover('hide');
            $("a#changefreq").not($(e.target)).popover('hide');
        };
    });

    var changeantenna = function(freqid, antennaSelector, newid){
        var sector = $('.sector-color.frequency-'+freqid);
        var antenna = sector.closest('.sector').find(antennaSelector);
        var currentid = antenna.data('antennaid');
        antenna.data('antennaid', newid);
        antenna.removeClass('antenna-'+currentid);
        antenna.addClass('antenna-'+newid);
        antenna.find('.actions-antenna').html($("table#antennas tr#antenna-"+newid).data('shortname'));
    };

    var createantenna = function(freqid, selector, newid){
        var sector = $('.sector-color.frequency-'+freqid);
        var div = $('<div></div>');
        div.addClass("background-status-ok "+selector+" antenna-color antenna-climax-color antenna-"+newid);
        div.data('antennaid', newid);
        var li = $('<li></li>');
        var a = $('<a></a>');
        a.addClass('actions-antenna');
        a.data('id', newid);
        a.html($("table#antennas tr#antenna-"+newid).data('shortname'));
        li.append(a);
        div.append(li);
        if(sector.closest('.sector').find('ul.antennas').length == 1) {
            var ul = $('<ul></ul>');
            if(selector == 'backupantenna-color') {
                var newdiv = $("<div></div>");
                newdiv.addClass('mainantenna-color antenna-color antenna-climax-color');
                ul.append(newdiv);
            }
            ul.addClass('antennas');
            ul.append(div);
            sector.closest('.sector').append(ul);
        } else {
            sector.closest('.sector').find('ul.antennas:last-child').append(div);
        }
    };

    var updateantennas = function(){
        $.each(currentantennas, function(key, value){
            $('#switch_'+key).prop('checked', value.status);
            var antennabutton = $("#antenna-"+key+" .togglebutton");
            var antennatd = $("#antenna-"+key+" td:first");
            var antenna = $('.antenna-color.antenna-'+key);
            if(!value.status) {
                //affichage du panneau contextuel
                antennatd.find('a').show();
                if(value.full_fault) {
                    //bouton rouge
                    antennabutton.removeClass('togglebutton-orange').addClass('togglebutton-red');
                    //antennes des fréquences en rouge
                    antenna.removeClass('background-status-ok')
                        .removeClass('background-status-planned')
                        .addClass('background-status-fail');
                } else {
                    //bouton jaune
                    antennabutton.addClass('togglebutton-orange').removeClass('togglebutton-red');
                    //antennes des fréquences impactées en rouge
                    antenna.removeClass('background-status-fail')
                        .removeClass('background-status-planned')
                        .addClass('background-status-ok');
                    $('.antenna-color.antenna-'+key).each(function(i){
                        var freqid = $(this).closest('.sector').find('.actions-freq').data('freq')+"";
                        if($.inArray(freqid,value.frequencies) !== -1 ){
                            $(this).removeClass('background-status-ok');
                            $(this).addClass('background-status-fail');
                        }
                    });
                }
            } else {
                antenna.removeClass('background-status-fail')
                        .removeClass('background-status-planned')
                        .addClass('background-status-ok');
                antennatd.find('a').hide();
                if (value.planned) {
                    antennatd.find('a').show();
                    antennabutton.addClass('togglebutton-blue');
                    if(value.full_fault) {
                        antenna.removeClass('background-status-ok')
                            .addClass('background-status-planned');
                    } else {
                        antenna.each(function(i){
                            var freqid = $(this).closest('.sector').find('.actions-freq').data('freq')+"";
                            if($.inArray(freqid,value.frequencies) !== -1 ){
                                $(this).removeClass('background-status-ok');
                                $(this).addClass('background-status-planned');
                            }
                        });
                    }
                } else {
                    antennabutton.removeClass('togglebutton-blue');
                }
            }
        });
    };

    var updatefrequencies = function() {
        $.each(currentfrequencies, function(key, value){
            var sector = $('.sector-color.frequency-'+key);
            var $failAntennas = sector.closest('.sector').find('.antenna-color.background-status-fail').length;
            if(value.status != 0 && value.otherfreq == 0 && value.cov == 0 && $failAntennas == 0){
                sector.removeClass('background-status-fail');
                sector.removeClass('background-status-warning');
                sector.addClass('background-status-ok');
            } else {
                if(value.status == 0){
                    sector.removeClass('background-status-ok');
                    sector.removeClass('background-status-warning');
                    sector.addClass('background-status-fail');
                } else {
                    //une au moins des antennes est HS -> warning
                    //changement de fréquence -> warning
                    if($failAntennas >= 1 || value.otherfreq != 0 || value.cov != 0){
                        sector.removeClass('background-status-ok');
                        sector.addClass('background-status-warning');
                        sector.removeClass('background-status-fail');
                    }
                }
            }

            //changement de couverture
            if(value.cov == 1){ //principale = 0
                sector.closest('.sector').find('.mainantenna-color').removeClass('background-selected');
                sector.closest('.sector').find('.backupantenna-color').addClass('background-selected');
            } else {
                sector.closest('.sector').find('.backupantenna-color').removeClass('background-selected');
                sector.closest('.sector').find('.mainantenna-color').addClass('background-selected');
            }
            //changement de fréquence
            if(value.otherfreq != 0 && value.otherfreqid != sector.closest('.sector').data('freq')){
                sector.find('.actions-freq').html(value.otherfreq).addClass('em').data('freq',value.otherfreqid);
                sector.find('.sector-name span').remove();
                var name = sector.find('.sector-name').html();
                sector.find('.sector-name').html(name+'<span> <span class="glyphicon glyphicon-forward"></span> '+value.otherfreqname);
            } else {
                sector.find('.actions-freq').html(value.name).removeClass('em').data('freq', key);
                sector.find('.sector-name span').remove();
            }
            //mise à jour des antennes (uniquement si passage en autre freq ou retour en freq normale
            //mais on le fait à tous les coups pour faire simple)
            //les couleurs sont mises à jour à l'appel de doPollAntenna juste ensuite
            changeantenna(key, '.mainantenna-color.antenna-color:not(.antenna-climax-color)', value.main);
            changeantenna(key, '.backupantenna-color.antenna-color:not(.antenna-climax-color)', value.backup);
            if (value['mainclimax']) {
                if (sector.closest('.sector').find('.mainantenna-color.antenna-climax-color').length > 0) {
                    changeantenna(key, '.mainantenna-color.antenna-climax-color', value.mainclimax);
                } else {
                    createantenna(key, 'mainantenna-color', value.mainclimax);
                }
            } else {
                sector.closest('.sector').find('.mainantenna-color.antenna-climax-color').empty().data('antennaid','');
            }
            if (value['backupclimax']) {
                if (sector.closest('.sector').find('.backupantenna-color.antenna-climax-color').length > 0) {
                    changeantenna(key, '.backupantenna-color.antenna-climax-color', value.backupclimax);
                } else {
                    createantenna(key, 'backupantenna-color', value.backupclimax);
                }
            } else {
                sector.closest('.sector').find('.backupantenna-color.antenna-climax-color').empty().data('antennaid','');
            }
            //if no backup -> add wide class
            if(!value['backup'] && !value['backupclimax']) {
                sector.closest('.sector').find('.mainantenna-color').addClass('mainantenna-wide');
            }
            sector.each(function(i, item){
                if(!value['mainclimax'] && !value['backupclimax'] && $(this).closest('.sector').find('.antennas').length > 1){
                    $(this).closest('.sector').find('.antennas:last-child').remove();
                }
            });


            if(value.status != 0 && value.otherfreq == 0 && value.cov == 0 && $failAntennas == 0){
                //prise en compte des évts planifiés uniquement si pas d'evt en cours => statut ok
                if(value.planned){
                    sector.addClass('background-status-planned');
                    sector.removeClass('background-status-ok');
                } else {
                    sector.removeClass('background-status-planned');
                    sector.addClass('background-status-ok');
                }
            }
        });
    }
    //refresh page every 30s
    function doFullUpdate(){
        $.post(url+'frequencies/getantennastate')
            .done(function(data) {
                currentantennas = data;
            })
            .always(function(){
                doPollFrequencies();
                timer = setTimeout(doFullUpdate, 30000);
            });
    };

    function doPollFrequencies(){
        $.post(url+'frequencies/getfrequenciesstate')
            .done(function(data) {
                currentfrequencies = data;
                updateantennas();
                updatefrequencies();
                //double passe sur les couleurs antennes en cas de changement de fréquence
                updateantennas();
                //delay update if a popover is opened
                if($("body").find('.popover').length == 0) {
                    updateActions();
                }
            });
    };

    //refresh page every 30s
    doFullUpdate();


};
