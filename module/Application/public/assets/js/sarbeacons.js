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
var sarbeacons = function(url) {
    "use strict";
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
    const URL_IMG = url + 'assets/img/orbit/',
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
            iconUrl: URL_IMG + 'btn-ter.png',
            iconSize: [IC_TER_SIZE, IC_TER_SIZE]
        }),
        icBal = L.icon({
            iconUrl: URL_IMG + 'btn-bal.png',
            iconSize: [IC_BAL_SIZE, IC_BAL_SIZE]
        }),
        icPIO = L.icon({
            iconUrl: URL_IMG + 'bal-pio.png',
            iconSize: [IC_PIO_SIZE, IC_PIO_SIZE]
        }),

        icHel = L.icon({
            iconUrl: URL_IMG + 'btn-hel.png',
            iconSize: [IC_HEL_SIZE, IC_HEL_SIZE]
        }),
        icSAR = L.icon({
            iconUrl: URL_IMG + 'marker-sar.png',
            iconSize: [IC_SAR_SIZE, IC_SAR_SIZE],
            iconAnchor: [IC_SAR_ANCH, IC_SAR_ANCH],
        });

    /* init de la map */
    var orbit = L.map('mapid').setView(DFLT_LAT_LNG, DFLT_ZOOM);
    orbit.zoomControl.setPosition('topright');

    L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
        attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
        maxZoom: 18,
        id: 'oziatek.ppg7d633',
        accessToken: 'pk.eyJ1Ijoib3ppYXRlayIsImEiOiJjaW5oZXI1dW8wMDF2dnNrbGNkMmpzZzRwIn0.cD36ZQU6C4tc0uqLzU8MGw'
    }).addTo(orbit);

    /* calques contenant résultat des pi*/
    var pioLay;

    var mapLayers = {
        'ter': L.geoJSON(),
        'bal': L.geoJSON()
    };
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
    var $reqPio = $('#req-pio'),

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
        $bPrintPi = $('#btn-print-pi'),
        $bMailPi = $('#btn-mail-pi'),

        $fEditPi = $('#f-edit-pi'),
        $carInner = $('.carousel-inner'),
        $carIndic = $('.carousel-indicators'),

        $aHist = $('#a-hist'),
        $listIp = $('#list-ip');

    /*  Le carousel reste statique */
    $reqPio.carousel({
        interval: false
    });
    /* raz des inputs */
    $('input').val('');

    /* init des onglets */
    $tabs.tabs()
        .find('.nav-pills>li').each(function() {
            $(this).click(function() {
                $(this)
                    .addClass('active')
                    .siblings('.active')
                    .removeClass('active');
            });
        })
        /** Evenements **/
    $('.raz-cherche').click(resetSearches);

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
    $bEditPi.click(btnEditIpHandler);
    $bSavPi.click(btnSaveIpHandler);
    $bPrintPi.click(printIp);

    $aHist.click(aHistHandler);

    /* declenchement pi sur un bouton droit sur la carte */
    orbit.on('contextmenu', function(e) {
        var coord = [e.latlng.lat, e.latlng.lng];
        centerMap(coord, PIO_ZOOM);
        triggerIp(coord);
    });
    /* Class pour ajouter un bouton sur la carte */
    L.Control.Button = L.Control.extend({

        options: {
            position: 'topright'
        },

        initialize: function(options) {
            this._button = {};
            this.setButton(options);
        },

        onAdd: function(map) {
            this._map = map;
            this._container = L.DomUtil.create(
                'div', 'leaflet-bar leaflet-control leaflet-control-custom custom-button'
            );
            this._container.style.backgroundImage = "url(" + this._button.iconUrl + ")";
            this._container.onclick = this._button.onClick;

            if (this._button.doToggle && this._button.toggleStatus == true)
                L.DomUtil.addClass(this._container, 'custom-button-off');

            L.DomEvent
                .addListener(this._container, 'click', L.DomEvent.stop)
                .addListener(this._container, 'click', this._button.onClick, this)
                .addListener(this._container, 'click', this._clicked, this);
            L.DomEvent.disableClickPropagation(this._container);

            return this._container;
        },

        setButton: function(options) {
            var button = {
                'text': options.text, //string
                'iconUrl': options.iconUrl, //string
                'onClick': options.onClick, //callback function
                'hideText': !!options.hideText, //forced bool
                'maxWidth': options.maxWidth || 70, //number
                'doToggle': options.doToggle, //bool
                'toggleStatus': options.toggleStatus, //bool
                'layer': options.layer,
                'map': options.map
            };

            this._button = button;
        },

        toggle: function(e) {
            if (typeof e === 'boolean') {
                this._button.toggleStatus = e;
            } else {
                this._button.toggleStatus = !this._button.toggleStatus;
            }
            this._update();
        },

        _update: function() {
            if (!this._map) return;
            this._container.innerHTML = '';
            this._makeButton(this._button);
        },

        _makeButton: function(button) {

        },

        _clicked: function() {
            if (this._button.doToggle) {
                if (this._button.toggleStatus) {
                    L.DomUtil.removeClass(this._container, 'custom-button-off');
                    if (this._button.layer) {
                        this._button.map.addLayer(this._button.layer);
                    }
                } else {
                    L.DomUtil.addClass(this._container, 'custom-button-off');
                    if (this._button.layer) {
                        this._button.map.removeLayer(this._button.layer);
                    }
                }
                this.toggle();
            }
            return;
        }

    });

    function refreshBtnClickHandler() {
        centerMap();
    }

    new L.Control.Button({
        'text': '', 
        'iconUrl': icPIO.options.iconUrl, 
        'onClick': refreshBtnClickHandler,
        'hideText': true, 
        'maxWidth': 25, 
        'doToggle': false,
        'toggleStatus': false, 
        'map': orbit
    }).addTo(orbit);

    function showFieldsBtnClickHandler() {

    }

    new L.Control.Button({
        'text': '',
        'iconUrl': icTer.options.iconUrl, 
        'onClick': showFieldsBtnClickHandler, 
        'hideText': true,
        'maxWidth': 25,
        'doToggle': true,
        'toggleStatus': true,
        'layer': mapLayers['ter'],
        'map': orbit
    }).addTo(orbit);

    function showBeaconsBtnClickHandler() {

    }

    new L.Control.Button({
        'text': '',
        'iconUrl': icBal.options.iconUrl, 
        'onClick': showBeaconsBtnClickHandler,
        'hideText': true,
        'maxWidth': 25,
        'doToggle': true,
        'toggleStatus': true,
        'layer': mapLayers['bal'],
        'map': orbit
    }).addTo(orbit);

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
            mapLayers['ter']._layers = lay._layers;
        })
        .fail(function() { console.log("Erreur lors du chargement du fichier GeoJson des terrains") });

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
            mapLayers['bal']._layers = lay._layers;
        })
        .fail(function() { console.log("Erreur lors du chargement du fichier GeoJson des balises") });

    function centerMap(latLon, zoom) {
        orbit.setView(latLon || DFLT_LAT_LNG, zoom || DFLT_ZOOM);
    }

    function aHistHandler(e) {
        if (!$listIp.children('a').length) loadListIp();
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

    function triggerIp(latLon) {
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
            $reqPio.hasClass('cache') ? $reqPio.removeClass('cache') : '';
            $bEditPi.removeClass('btn-success').addClass('btn-info');
            $('#btn-sav-pi, #btn-mail-pi, #btn-print-pi')
                .addClass('btn-warning disabled')
                .removeClass('btn-info');

            $fEditPi.find('input').val('');
            $fEditPi.find('li').remove();
        }

        function infoPI() {
            $fIp.find('h4')
                .html('<span class="glyphicon glyphicon-alert"></span>'+
                        ' PI démarré à ' + moment().format('hh:mm:ss') + 
                        ' le ' + moment().format('DD/MM'));
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
                    $('<a class="list-group-item">' +
                        '<span class="badge">d = ' + Math.trunc(ter.d) + ' km, cap = ' + Math.trunc(ter.cap) + '°</span>' +
                        '<h5><strong>' + (i + 1) + ' - ' + props.code + '</strong> <br /><em>' + props.name + '</em> </h5>' +
                        '</a>')
                    .click({ 'latLon': [coord[1], coord[0]] }, clickFieldHandler);

                var $fOptCom = $('<div class="form-group comment"></div>')
                    .appendTo($ter)
                    .hide();

                $('<textarea rows="1" class="form-control" placeholder="commentaire optionnel"></textarea>')
                    .data({
                        "idt": i,
                        "name": props.name,
                    })
                    .blur(function() {
                        var p = pio[$(this).data().idt];
                        p["comment"] = $(this).val();

                    })
                    .appendTo($fOptCom);

                $('<button class = "btn-xs btn-info"><span class="glyphicon glyphicon-check"></span></button>')
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

                $('<button class = "btn-xs btn-info"><span class="glyphicon ' + img + '"></span></button>')
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

                function clickContactHandler() {
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

                    $bSavPi
                        .removeClass('btn-warning disabled')
                        .addClass('btn-info');
                    // pio = pio.filter(x => x.name != $(this).data().name);

                    if ($(this).hasClass('btn-danger')) {
                        $fOptCom.show();
                        pio[$(this).data().idt] = {
                            name: $(this).data().name,
                            code: $(this).data().code,
                            intTime: moment().format('X'),
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

    function btnEditIpHandler() {
        if ($(this).hasClass('disabled')) return;
        $('#title-edit-pi').html("Editer le Plan d'Interrogation");
        if(idIp == null && !$fEditPi.find('form').length)
        {
            $fEditPi.load(url + 'sarbeacons/form',
                {
                    'id': idIp,
                    'lat': mkSAR._latlng.lat,
                    'lon': mkSAR._latlng.lng
                },
                function() {
                    refreshFieldList();

                    $fEditPi.find('input[type="submit"]')
                        .click(function (e) {
                            e.preventDefault();
                            $("#mdl-edit-pi").modal('hide');
                            $bEditPi
                                .removeClass('btn-info')
                                .addClass('btn-success');
                        });
                }
            );
        }
        else refreshFieldList();

        function refreshFieldList() {
            var $ul = $fEditPi.find("ul");
            $ul.find('li').remove();
            $.each(pio, function(index, field) {
                if (!field) return true;
                var $li = $('<li class="list-group-item"><strong> ' + moment.unix(field.intTime).format('DD/MM/YY hh:mm:ss') + '</strong> ' + field.name + '<button class="btn-xs btn-danger type = "button"><span class="glyphicon glyphicon-remove"></span></button><br />' + field.comment + '</li>');

                $li.find('button')
                    .data({ 'name': field.name, 'idt': index })
                    .click(function() {
                        pio = pio.filter(x => x.name != $(this).data().name);
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

    function printIp() {
        if ($(this).hasClass('disabled')) return;
        if ($(this).data('id')) location.href = url + 'sarbeacons/print/' + $(this).data('id');
    }

    function btnSaveIpHandler() {
        if ($(this).hasClass('disabled')) return;
        $('input[name="latitude"], input[name="longitude"]').prop('disabled', false);

        $.post(url + 'sarbeacons/save', { datas: $("#InterrogationPlan").serialize(), pio: pio }, function(data) {
            idIp = data.id;
            if (idIp > 0) {
                loadListIp();
                $fEditPi.find('input[name=id]').val(idIp);

                $bSavPi
                    .removeClass('btn-info')
                    .addClass('btn-success');

                $bPrintPi
                    .data({ 'id': idIp })
                    .removeClass('btn-warning disabled')
                    .addClass('btn-info');

                $bMailPi
                    .data({ 'id': idIp })
                    .removeClass('btn-warning disabled')
                    .addClass('btn-info');

                data.msg = "Le plan d'interrogation a bien été enregistré.";
            }

            noty({
                text: data.msg,
                type: data.type,
                timeout: 4000,
            });

        })
    }

    function loadListIp() {
        $listIp.load(url + 'sarbeacons/list', function() {
            $.each($listIp.find('a'), function() {
                var id = $(this).data().id;
                $(this).click(function(e) {
                    $(this).find('.list-ip-content').toggleClass('cache');
                });

                $(this).find('.btn-show').click(function(e) {
                    e.stopPropagation();
                    $.post(url + 'sarbeacons/get', { 'id': id }, function(data) {
                        idIp = id;

                        triggerIp([
                            data.latitude,
                            data.longitude
                        ]);

                        $bEditPi
                            .removeClass('btn-info')
                            .addClass('btn-warning disabled');

                        $bPrintPi
                            .data({ 'id': idIp })
                            .removeClass('btn-warning disabled')
                            .addClass('btn-info');

                        $bMailPi
                            .data({ 'id': idIp })
                            .removeClass('btn-warning disabled')
                            .addClass('btn-info');

                        // TODO un peu bourrin
                        $.each(data.fields, function(i, field) {
                            $.each($carInner.find('em'), function(j, val) {
                                if ($(this).html() == field.name) {
                                    pio[j] = {
                                        name: field.name,
                                        code: field.code,
                                        intTime: moment(field).format('X'),
                                        comment: field.comment,
                                        latitude: field.lat,
                                        longitude: field.lon
                                    };
                                    var $a = $(this).parents('a');
                                    $a.addClass('list-group-item-success');
                                    if (field.comment) {
                                        $a.find('.comment').show();
                                        $a.find('textarea')
                                            .prop('readonly', true)
                                            .html(field.comment);
                                    }
                                }
                            });
                        });
                    })
                });
            });
        });
    }
};
