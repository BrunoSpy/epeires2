/*
 *  This file is part of Epeires².
 *  Epeires² is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  Epeires² is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with Epeires².  If not, see <http://www.gnu.org/licenses/>.
 */
$(function() {
    "use strict";
    /** constantes **/

    /* nombre de résultats à afficher pour un PI */
    const NB_RESULT_PIO_AFF = 5;
    const NB_RESULT_PIO = 30;
    /* nombre de proposition pour l'autocompletion (balise & terrain) */
    const NB_RESULT_AUTOCOMP = 20;
    /* paramètres des coordonnées et pour le calcul des distances & cap */
    const R_TERRE_KM = 6371,
        MIN_LON = -180,
        MAX_LON = 180,
        MIN_LAT = -90,
        MAX_LAT = 90;
    /* centrage par defaut */
    const DFLT_LAT_LNG = [48.8534100, 2.3488000],
        DFLT_ZOOM = 7;
    /* niveau de zoom lors d'un PI */
    const PIO_ZOOM = 8;
    /* image du marqueur représentant le lieu de l'alerte */
    const URL_IMG = 'assets/img/orbit/',
        IMG_PIO = URL_IMG + '/marker-pio.png';
    /* icones */
    const IC_TER_SIZE = 20,
        IC_BAL_SIZE = 10,
        IC_PIO_SIZE = 20,
        IC_HEL_SIZE = 20,
        IC_SAR_SIZE = 20,
        IC_SAR_ANCH = IC_SAR_SIZE / 2;

    const
        icTer = L.icon({
            iconUrl: URL_IMG + 'terrain.png',
            iconSize: [IC_TER_SIZE, IC_TER_SIZE]
        }),
        icBal = L.icon({
            iconUrl: URL_IMG + 'bal.png',
            iconSize: [IC_BAL_SIZE, IC_BAL_SIZE]
        }),
        icPIO = L.icon({
            iconUrl: URL_IMG + 'bal-pio.png',
            iconSize: [IC_PIO_SIZE, IC_PIO_SIZE]
        }),

        icHel = L.icon({
            iconUrl: URL_IMG + 'hel.png',
            iconSize: [IC_HEL_SIZE, IC_HEL_SIZE]
        }),
        icSAR = L.icon({
            iconUrl: URL_IMG + 'marker-sar.png',
            //shadowUrl: 'leaf-shadow.png',
            iconSize: [IC_SAR_SIZE, IC_SAR_SIZE], // size of the icon
            //shadowSize:   [50, 64], // size of the shadow
            iconAnchor: [IC_SAR_ANCH, IC_SAR_ANCH], // point of the icon which will correspond to marker's location
            //shadowAnchor: [4, 62],  // the same for the shadow
            //popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
        });

    /** Variables globales **/
    /* init de la map */
    var orbit = L.map('mapid').setView(DFLT_LAT_LNG, DFLT_ZOOM);
    L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
        attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
        maxZoom: 18,
        id: 'oziatek.ppg7d633',
        accessToken: 'pk.eyJ1Ijoib3ppYXRlayIsImEiOiJjaW5oZXI1dW8wMDF2dnNrbGNkMmpzZzRwIn0.cD36ZQU6C4tc0uqLzU8MGw'
    }).addTo(orbit);

    /* calques contenant résultat des pi, beacons et fields */
    var pioLay,
        balLay,
        terLay;

    var mapLayers = {
        'bal': loadBeacons(),
        'ter': loadFields()
    }

    /* marqueurs SAR et terrain sélectionné */
    var mkSAR,
        mkSelected;
    /* données :
        fields : tableau créée à partir des données GeoJson avec la liste des features.
        fieldNames : tableau ne contenant que le nom des fields pour l'autocompletion. 
        beacons : tableau créée à partir des données GeoJson avec la liste des features.
        beaconNames : tableau ne contenant que le nom des beacons pour l'autocompletion.
        pio : tableau contenant les terrains interrogés.
        idIp : contient l'id de la BDD du plan d'interrogation s'il a été créée.
    */
    var fields = [],
        fieldNames = [],
        beacons = [],
        beaconNames = [],
        pio = [],
        idIp = null;
    /* DOM */
    var $content = $('.content'),

        $reqPio = $('#req-pio'),

        $iLat = $('#inp-lat'),
        $iLon = $('#inp-lon'),
        $iBal = $('#inp-bal'),
        $iTer = $('#inp-ter'),

        $bRecC = $('#btn-rech-coo'),
        $bRecB = $('#btn-rech-bal'),
        $bRecT = $('#btn-rech-ter'),

        $tabs = $('#tabs'),
        $tab1 = $('#tabs-1'),
        $tab2 = $('#tabs-2'),
        $tab3 = $('#tabs-3'),

        $fIp = $('#f-ip'),
        $bSavPi = $('#btn-sav-pi'),
        $bEditPi = $('#btn-edit-pi'),

        $fEditPi = $('#f-edit-pi'),
        $carousel = $("#req-pio"),
        $carInner = $('.carousel-inner'),
        $carIndic = $('.carousel-indicators'),

        $aHist = $('#a-hist'),
        $listIp = $('#list-ip'),
        $tplList = $listIp.find('.tpl');
    /*  Le carousel reste statique */
    $reqPio.carousel({
        interval: false
    });
    /* raz des inputs */
    $('input').val('');
    // $('.tpl').hide();

    /* init des onglets */
    $tabs.tabs();
    $tabs.find('.nav-pills>li').each(function(){
        $(this).click(function(){
            $(this).addClass('active')
                .siblings('.active').removeClass('active');
        })
    })
    /** Evenements **/
    $iLat.keyup(keyPressedLat);
    $iLon.keyup(keyPressedLon);
    $bRecC.click(findByCoord);

    $iBal.keyup(keyPressedBeacon);
    $iBal.autocomplete({
        autofocus: true,
        source: function(request, response) {
            source(request, response, beaconNames);
        },
        select: select
    });

    $iTer.keyup(keyPressedFields);
    $iTer.autocomplete({
        autofocus: true,
        source: function(request, response) {
            source(request, response, fieldNames);
        },
        select: select
    });

    $bRecB.click(findByBeacon);
    $bRecT.click(findByField);
    $bEditPi.click(btnEditPiHandler);
    $bSavPi.click(saveIp);

    $('.raz-cherche').click(resetSearches);

    $aHist.click(aHistHandler);

    /* declenchement pi sur un bouton droit sur la carte */
    orbit.on('contextmenu', function(e) {
        var coord = [e.latlng.lat, e.latlng.lng];
        centerMap(coord, PIO_ZOOM);
        triggerIp(coord);
    });

    L.easyButton('glyphicon-refresh', function() { 
        centerMap();     
    }).addTo(orbit);

    function centerMap(latLon, zoom) {
        orbit.setView(latLon || DFLT_LAT_LNG, zoom || DFLT_ZOOM);
    }
    // function modPiHandler() {
    //         $bEditPi
    //             .removeClass('btn-info')
    //             .addClass('btn-success');
    //         $('#btn-sav-pi,#btn-mail-pi,#btn-print-pi')
    //             .removeClass('btn-warning disabled')
    //             .addClass('btn-info');

    //         $fEditPi.modal('hide');
    //     }

    function aHistHandler(e) {
        if(!$listIp.children('a').length) loadListIp();
    }

    function editBtnState($btn, etat) {
        if (etat)
            $btn
            .removeClass('btn-warning disabled')
            .addClass('btn-success');
        else
            $btn
            .addClass('btn-warning disabled')
            .removeClass('btn-success');
    }

    function activateResetSearches($raz) {
        ($raz.prev().val() == '') ? $raz.addClass('cache'): $raz.removeClass('cache');
    }

    function resetSearches() {
        $(this).addClass('cache')
            .prev().val('')
            .parent().addClass('has-error');

        editBtnState($(this).closest('.row').find('button'), 0);
    }

    /** fn recherche par coordonnées **/

    function keyPressedLat(key) {
        activateResetSearches($(this).next());
        keyPressedLatLon(key);
    }

    function keyPressedLon(key) {
        activateResetSearches($(this).next());
        keyPressedLatLon(key);
    }

    function keyPressedLatLon(key) {
        var lat = validateLat(),
            lon = validateLon();
        if (lat && lon) {
            editBtnState($bRecC, 1);
            (key.keyCode == '13') ? $bRecC.trigger('click'): '';
        } else
            editBtnState($bRecC, 0);
    }

    function validateLat() {
        var lat = $iLat.val();
        if (lat !== '' && lat >= MIN_LAT && lat <= MAX_LAT) {
            $iLat.parent()
                .removeClass('has-error');
            return lat;
        } else {
            $iLat.parent()
                .addClass('has-error');
            return false;
        }
    }

    function validateLon() {
        var lon = $iLon.val();
        if (lon !== '' && lon >= MIN_LON && lon <= MAX_LON) {
            $iLon.parent()
                .removeClass('has-error');
            return lon;
        } else {
            $iLon.parent()
                .addClass('has-error');
            return false;
        }
    }

    function findByCoord(e) {
        if (!$(this).hasClass('btn-warning')) {
            triggerIp([$iLat.val(), $iLon.val()]);
        }
    };

    function source(request, response, data) {
        var results = $.ui.autocomplete.filter(data, request.term);
        response(results.slice(0, NB_RESULT_AUTOCOMP));
    }

    function select(ev, ui) {
        $(this).val(ui.item.value);
        $(this).trigger("keyup");
    }
    /** fn recherche par beacons **/
    function keyPressedBeacon(e) {
        activateResetSearches($(this).next());
        var nomBal = $(this).val();
        if (!nomBal) {
            editBtnState($bRecB, 0);
            return false;
        }
        var iBal = findByBeaconsName(nomBal);
        if (typeof iBal == 'undefined') {
            $(this).parent().addClass('has-error');
            editBtnState($bRecB, 0);
        } else {
            $(this).parent().removeClass('has-error');
            editBtnState($bRecB, 1);

            var coord = beacons[iBal].geometry.coordinates;
            $(this).data('latLon', [coord[1], coord[0]]);

            if (e.keyCode == '13') {
                $(this).autocomplete('close');
                $bRecB.trigger('click');
            }
        }
    };

    function findByBeacon() {
        if (!$(this).hasClass('btn-warning')) {
            var latlon = $iBal.data('latLon');
            centerMap(latlon);
            triggerIp(latlon);
        }
    }

    function findByBeaconsName(bal) {
        var iBal;
        $.each(beaconNames, function(i, b) {
            if (bal.toUpperCase() === b.toUpperCase()) {
                iBal = i;
                return false;
            }
        });
        return iBal;
    }

    /** fn recherche par fields **/
    function keyPressedFields(e) {
        activateResetSearches($(this).next());
        var nomTer = $(this).val();
        if (!nomTer) {
            editBtnState($bRecT, 0);
            return false;
        }
        var iTer = findByFieldsName(nomTer);
        if (typeof iTer == 'undefined') {
            $(this).parent().addClass('has-error');
            editBtnState($bRecT, 0);
        } else {
            $(this).parent().removeClass('has-error');
            editBtnState($bRecT, 1);

            var coord = fields[iTer].geometry.coordinates;
            $(this).data('latLon', [coord[1], coord[0]]);

            if (e.keyCode == '13') {
                $(this).autocomplete('close');
                $bRecT.trigger('click');
            }
        }
    };

    function findByField() {
        if (!$(this).hasClass('btn-warning')) {
            var latlon = $iTer.data('latLon');
            centerMap(latlon);
            triggerIp(latlon);
        }
    }

    function findByFieldsName(ter) {
        var iTer;
        $.each(fieldNames, function(i, t) {
            if (ter.toUpperCase() === t.toUpperCase()) {
                iTer = i;
                return false;
            }
        });
        return iTer;
    }

    function newIp(obj) {

        var $li = $(
            '<li class="list-group-item lspio"></li>'
            );

        // var $a = $('<a href = "#"><strong>' + ALERTES[obj.typeAl] + '</strong> le '+ obj.date + '</a>')
        //     .click(function() {
                
        //     })
        //     .prependTo($li);

        // var $ol = $('<ol class="cache"></ol>').appendTo($li);
        // $(obj.pio).each(function(index, val) {
        //     $('<li> ' + val.nom + '</li>').appendTo($ol);
        // });  

        // var $bPrint = $('<button type="button" class="btn btn-xs btn-info"><span class="glyphicon glyphicon-print"></span></button>');
        // $bPrint.click(function() {

        // }).prependTo($li);

        // var $bMail = $('<button type="button" class="btn btn-xs btn-info"><span class="glyphicon glyphicon-envelope"></span></button>');
        // $bMail.click(function() {

        // }).prependTo($li);

        $('#pio-hist h4').after($li);

        $('#pio li').removeClass('list-group-item-warning').addClass('list-group-item-success');
    }

    function triggerIp(latLon) 
    {
        refreshIp();
        /* PLACER LE MARKER SUR LA POSITION DE L'ALERTE */
        mkSAR = updateMarker(mkSAR, latLon, icSAR);
        /* AFFICHE EN-TETE */
        infoPI();
        /* [] CONTENANT LES MARQUEURS DES fields LES PLUS PROCHES */
        var markersPIO = [];
        var tabDist = calculEachFieldsDistance(latLon);
        processFieldsList();

        if (pioLay) orbit.removeLayer(pioLay);
        pioLay = L.layerGroup(markersPIO).addTo(orbit);

        $tabs.tabs("option", "active", 1);
        $tabs.find('.nav-pills>li').eq(1).trigger('click');

        function refreshIp() {
            pio = [];
            $carIndic.find('li').remove();
            $carInner.find('div.item').remove();
            $tab2.find('h4').eq(0).html('');
            $fIp.hasClass('cache') ? $fIp.removeClass('cache') : '';
            $carousel.hasClass('cache') ? $carousel.removeClass('cache') : '';
            $bEditPi.removeClass('btn-success').addClass('btn-info');
            $('#btn-sav-pi, #btn-mail-pi, #btn-print-pi')
                .addClass('btn-warning disabled')
                .removeClass('btn-info');

            $fEditPi.find('input').val('');
            $fEditPi.find('li').remove();
        }

        function infoPI() {
            var dateDebut = moment().format('DD/MM hh:mm:ss');

            $fIp.find('h4').html('<span class="glyphicon glyphicon-alert"></span> PI démarré à ' + moment().format('hh:mm:ss') + ' le ' + moment().format('DD/MM'));

            $fIp.find('.label').html(Number(latLon[0]).toFixed(4) + ', ' + Number(latLon[1]).toFixed(4));

            $bSavPi.click(saveIpHandler);

            function saveIpHandler(e){
                newIp({
                    "typePi": $fEditPi.find('select').eq(0).val(),
                    "typeAl": $fEditPi.find('select').eq(1).val(),
                    "firInt": $fEditPi.find('input').eq(0).val(),
                    "firSrc": $fEditPi.find('input').eq(1).val(),
                    "date": moment().format('DD/MM hh:mm:ss'),
                    "pio": pio
                });
            }
        }

        function processFieldsList() {

            for (var j = 0; j < Math.floor(NB_RESULT_PIO / NB_RESULT_PIO_AFF); j++) {

                var $dItem = $('<div class = "item"></div>');
                var $liIndicator = $('<li data-target = "#req-pio" data-slide-to="' + j + '"></li>');

                if (j == 0) {
                    $dItem.addClass('active');
                    $liIndicator.addClass('active');
                }

                for (var i = j * NB_RESULT_PIO_AFF; i <= ((j + 1) * NB_RESULT_PIO_AFF) - 1; i++) {

                    var $ter = processField(i, tabDist[i]);;
                    $dItem.append($ter);
                }

                $carIndic.append($liIndicator);
                $carInner.append($dItem);
            }

            function processField(i, ter) {

                var coord = ter.geometry.coordinates;
                var props = ter.properties;

                var $ter = 
                    $('<a class="list-group-item">'+
                        '<span class="badge">d = ' + Math.trunc(ter.d) + ' km, cap = ' + Math.trunc(ter.cap) + '°</span>'+
                        '<h5><strong>' + (i + 1) + ' - ' + props.code + '</strong> <br /><em>' + props.name + '</em> </h5>' +
                      '</a>')
                    .click({ 'latLon': [coord[1], coord[0]] }, clickFieldHandler);

                var $fOptCom = $('<div class="form-group comment"></div>')
                    .appendTo($ter)
                    .hide();

                var $optCom =
                    $('<textarea rows="1" class="form-control" placeholder="commentaire optionnel"></textarea>')
                    .data({
                        "idt": i,
                        "name": props.name,
                    })
                    .blur(function() { 
                        // var p = pio.filter(x => x.nom == props.name);
                        var p =  pio[$(this).data().idt];

                        p["comment"] = $(this).val();

                     })
                    .appendTo($fOptCom);

                var $btnContact = $('<button class = "btn-xs btn-info"><span class="glyphicon glyphicon-check"></span></button>')
                    .data({
                        "idt": i,
                        "code": props.code,
                        "name": props.name,
                        "lat": coord[1],
                        "lon": coord[0]
                    })
                    .click(clickContactHandler)
                    .prependTo($ter);

                var img = (props.type == 'AD') ? 'glyphicon-plane' : 'glyphicon-header';

                var $btnCentrer = $('<button class = "btn-xs btn-info"><span class="glyphicon ' + img + '"></span></button>')
                    .data({ 'latLon': [coord[1], coord[0]] })
                    .click(function(e) {
                        centerMap($(this).data().latLon, orbit.getZoom());
                    })
                    .prependTo($ter);

                if (i == 0) {
                    $ter.addClass('active');
                    mkSelected = updateMarker(mkSelected, [coord[1], coord[0]], null);
                }
                markersPIO.push(createIpMarker(i, [coord[1], coord[0]]));

                return $ter;

                function clickFieldHandler(e) {
                    detailBeacon(e);
                    $('.carousel-inner a.active').removeClass('active');
                    $(this).addClass('active');
                }

                function clickContactHandler(e) {
                    var $fOptCom = $(this).parent().find('.form-group');

                    $carInner.find('a.active')
                        .removeClass('active');

                    $(this)
                        .toggleClass('btn-info')
                        .toggleClass('btn-danger');
                    $(this).find('span')
                        .toggleClass('glyphicon-check')
                        .toggleClass('glyphicon-remove');

                    $(this).parent('.list-group-item')
                        .toggleClass('list-group-item-success')
                        .addClass('active');

                    // pio = pio.filter(x => x.name != $(this).data().name);

                    if($(this).hasClass('btn-danger')){
                        $fOptCom.show();
                        pio[$(this).data().idt] = { 
                            name: $(this).data().name,
                            code: $(this).data().code, 
                            intTime: moment().format('hh:mm:ss'), 
                            comment: $fOptCom.find('textarea').val(),
                            latitude: $(this).data().lat,
                            longitude: $(this).data().lon
                        };
                    } else {
                        $fOptCom.hide();
                        pio.splice($(this).data().idt, 1);
                    }

                }
            }
        }

        function createIpMarker(i, latlon) {
            var icon = L.icon({
                iconUrl: IMG_PIO,
                iconSize: [2 * NB_RESULT_PIO - 2 * i, 2 * NB_RESULT_PIO - 2 * i]
            });
            return L.marker(latlon, {
                icon: icon,
                opacity: (NB_RESULT_PIO - i) / NB_RESULT_PIO
            }).bindPopup(tabDist[i].properties.name);
        }

        function updateMarker(marker, latLon, icon) {
            if (!(marker === undefined)) orbit.removeLayer(marker);
            marker = L.marker(latLon);
            if (icon) marker.setIcon(icon);
            orbit.addLayer(marker);
            return marker;
        }

        function distRad(lat1, lon1, lat2, lon2) {
            // var dLat = (lat2 - lat1) * Math.PI / 180;  // deg2rad below
            // var dLon = (lon2 - lon1) * Math.PI / 180;
            // var a = 
            //  0.5 - Math.cos(dLat)/2 + 
            //  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
            //  (1 - Math.cos(dLon))/2;

            lat1 = (Math.PI / 180) * lat1;
            lat2 = (Math.PI / 180) * lat2;
            lon1 = (Math.PI / 180) * lon1;
            lon2 = (Math.PI / 180) * lon2;
            return Math.acos(Math.sin(lat1) * Math.sin(lat2) + Math.cos(lat1) * Math.cos(lat2) * Math.cos(lon1 - lon2));
        }

        function radTokm(rad) {
            return R_TERRE_KM * rad;
        }

        function cap(drad, lat1, lon1, lat2, lon2) {
            lat1 = (Math.PI / 180) * lat1;
            lat2 = (Math.PI / 180) * lat2;
            lon1 = (Math.PI / 180) * lon1;
            lon2 = (Math.PI / 180) * lon2;

            if (Math.sin(lon2 - lon1) < 0)
                return Math.acos((Math.sin(lat2) - Math.sin(lat1) * Math.cos(drad)) / (Math.sin(drad) * Math.cos(lat1)));
            else
                return 2 * Math.PI - Math.acos((Math.sin(lat2) - Math.sin(lat1) * Math.cos(drad)) / (Math.sin(drad) * Math.cos(lat1)));

        }

        function calculEachFieldsDistance(latLon) {
            var tabDist = [];
            $.each(fields, function(i, val) {
                var tmp = val;
                var coord = val.geometry.coordinates;
                var dRad = distRad(latLon[0], latLon[1], coord[1], coord[0]);
                tmp.d = radTokm(dRad);
                tmp.cap = cap(dRad, latLon[0], latLon[1], coord[1], coord[0]) * 180 / Math.PI;
                if (tmp.d && tmp.cap) tabDist.push(tmp);
            });

            tabDist.sort(function(a, b) {
                return a.d - b.d;
            });

            return tabDist;
        }

        function detailBeacon(e) {
            e.preventDefault();
            mkSelected = updateMarker(mkSelected, e.data.latLon);
        }
    }

    function btnEditPiHandler(e) {
        $bEditPi
            .removeClass('btn-info')
            .addClass('btn-success');
        $('#btn-sav-pi,#btn-mail-pi,#btn-print-pi')
            .removeClass('btn-warning disabled')
            .addClass('btn-info');

        $('#title-mod-pi').html("Editer le Plan d'Interrogation");

        // if(idIp == null && !$fEditPi.find('form').length) {
            $fEditPi.load('/sarbeacons/form', {'id' : idIp, 'lat' : mkSAR._latlng.lat, 'lon' : mkSAR._latlng.lng}, function() {

                refreshFieldList();

                $fEditPi.find('input[type="submit"]')
                .click(function(e){
                    e.preventDefault();
                    $("#mdl-edit-pi").modal('hide');
                })
            });
        // } else refreshFieldList();

        function refreshFieldList() {
            var $ul = $fEditPi.find("ul");
            $ul.find('li').remove();
            $.each(pio, function(index, val) {
                if(!val) return true;
                var $li = $('<li class="list-group-item"><strong> ' + val.intTime + '</strong> ' + val.name + '<button class="btn-xs btn-danger type = "button"><span class="glyphicon glyphicon-remove"></span></button><br />' + val.comment + '</li>');

                $li.find('button')
                    .data({'name': val.name, 'idt': index})
                    .click(function(){
                        pio = pio.filter(x => x.name !=  $(this).data().name);
                        var $a = $carInner.find('a').eq($(this).data().idt);
                            $a.find('.form-group').hide();
                            $a.toggleClass('list-group-item-success')
                                .find('button').eq(1)
                                .toggleClass('btn-info')
                                .toggleClass('btn-danger')
                                .find('span')
                                .toggleClass('glyphicon-check')
                                .toggleClass('glyphicon-remove');
                        $(this).parent().remove();
                    });
                $ul.append($li);
            });
        }
    }

    function saveIp(e) {
        e.preventDefault();
        $('input[name="latitude"], input[name="longitude"]').prop('disabled', false);
        // pio = JSON.stringify({ 'pio': pio });

        // $.ajax({
        //     contentType: 'application/json; charset=utf-8',
        //     dataType: 'json',
        //     type: 'POST',
        //     url: '/sarbeacons/sauver',
        //     data: $('#InterrogationPlan').serialize()
        // }); 
        $.post("/sarbeacons/save", {datas:$("#InterrogationPlan").serialize(),pio: pio}, function(data) 
        {
            loadListIp();
            idIp = data.id;
            noty({
                text: data.message,
                type: data.type,
                timeout: 4000,
            });

        })
    }

    function loadListIp(e) {
        $listIp.load('sarbeacons/list', function(data) {
            $.each($listIp.find('a'), function() {
                var id = $(this).data().id;
                $(this).click(function(e) {
                    $(this).find('.list-ip-content').toggleClass('cache');
                });

                $(this).find('.btn-show').click(function(e){
                    e.stopPropagation();
                    $.post('sarbeacons/get', {'id' : id}, function(data) {
                        triggerIp([
                            data.latitude,
                            data.longitude
                        ]);
                        $.each(data.fields, function(i, field) {
                            $.each($carInner.find('em'), function() {
                                if($(this).html() == field.name) {
                                    var $a = $(this).parents('a')
                                    $a.addClass('list-group-item-success')
                                    if(field.comment) {    
                                        $a.find('.comment').show();
                                        $a.find('textarea').html(field.comment);
                                    }    
                                }
                            });
                        });
                    })
                });
            });
        });
    }

    // function printListIp(listIp) {
    //     $.each(listIp, function(i, ip){
    //         $listIp.append(printIp(ip));
    //     });
    // }

    // function printIp(ip) {
    //     var $item = new IpList(ip);
    //     return $item.getHtml();
    // }


    /* chargement ajax des données de la map */
    function loadFields() {
        $.getJSON("data/testter.geojson")
            .done(function(data) {
                var lay = L.geoJson(data, {
                    pointToLayer: function(feature, latlng) {
                        var prop = feature.properties;
                        var icon;
                        var marker;

                        switch (prop.type) {
                            case "AD":
                                icon = icTer;
                                break;
                            case "HP":
                                icon = icHel;
                                break;
                        }

                        marker = L.marker(latlng, { icon: icon })
                            .bindPopup(prop.name);

                        fields.push(feature);
                        fieldNames.push(prop.code);

                        return marker;
                    }
                });
                lay.addTo(orbit);
                addBtnToMap('ter', lay);
            })
            .fail(function() { console.log("Erreur lors du chargement du fichier GeoJson des fields") });
    }

    function loadBeacons() {
        $.getJSON("data/bal.GeoJson")
            .done(function(data) {
                var lay = L.geoJson(data, {
                    pointToLayer: function(feature, latlng) {
                        beacons.push(feature);
                        beaconNames.push(feature.properties.code);
                        var marker = L.marker(latlng, { icon: icBal });
                        marker.bindPopup(feature.properties.code);
                        return marker;
                    }
                });
                lay.addTo(orbit);
                addBtnToMap('bal', lay);

                return lay;

            })
            .fail(function() { console.log("Erreur lors du chargement du fichier GeoJson des beacons.") });
    }

    function addBtnToMap(nom, layer) {
        L.easyButton({
            states: [{
                stateName: nom + "-on",
                icon: '<img src="' + URL_IMG + 'btn-' + nom + '-on.png" class="btn-icon">',
                title: "Désactiver marqueurs fields.",
                onClick: function(btn, map) {
                    map.removeLayer(layer);
                    btn.state(nom + "-off");
                }
            }, {
                stateName: nom + "-off",
                icon: '<img src="' + URL_IMG + 'btn-' + nom + '-off.png" class="btn-icon">',
                title: "Activer marqueurs fields.",
                onClick: function(btn, map) {
                    map.addLayer(layer);
                    btn.state(nom + "-on");
                }
            }]
        }).addTo(orbit);
    }

});
