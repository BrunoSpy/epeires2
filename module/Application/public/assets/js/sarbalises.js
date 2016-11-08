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
    const ALERTES = ["INERFA", "ALERFA", "DETRESSFA"];
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

    /* calques contenant résultat des pi, balises et terrains */
    var pioLay,
        balLay,
        terLay;

    var mapLayers = {
        'bal': chargerBalises(),
        'ter': chargerTerrains()
    }

    /* marqueurs SAR et terrain sélectionné */
    var SARMk,
        sltedTerMk;
    /* données :
        terrains : tableau créée à partir des données GeoJson avec la liste des features.
        nomTerrains : tableau ne contenant que le nom des terrains pour l'autocompletion. 
        balises : tableau créée à partir des données GeoJson avec la liste des features.
        nomBalises : tableau ne contenant que le nom des balises pour l'autocompletion.
    */
    var terrains = [],
        nomTerrains = [],
        balises = [],
        nomBalises = [],
        pio = [];
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

        $onglets = $('#tabs'),
        $tab1 = $('#tabs-1'),
        $tab2 = $('#tabs-2'),
        $tab1 = $('#tabs-3'),
        $fPio = $('#f-pio'),
        $bSavPi = $('#btn-sav-pi'),
        $bModPi = $('#btn-mod-pi'),

        $fModPi = $('<div id = "f-mod-pi"></div>'),
        $carousel = $("#req-pio"),
        $carInner = $('.carousel-inner'),
        $carIndic = $('.carousel-indicators');

    var lsPioProp = [{
            label: "Type",
            name: "pio",
            type: "select",
            options: { 'pio': 'PIO', 'pia': 'PIA' },
            required: true
        }, {
            label: "Alerte",
            name: "alerte",
            type: "select",
            options: { '0': 'INERFA', '1': 'ALERTFA', '2': 'DETRESSFA' },
            required: true
        }, {
            label: "FIR Source",
            name: "fir-src",
            type: "text"
        }, {
            label: "FIR interrogée",
            name: "fir-int",
            type: "text"
        }];
    /*  Le carousel reste statique */
    $reqPio.carousel({
        interval: false
    });
    /* raz des inputs */
    $('input').val('');

    /* init des onglets */
    $onglets.tabs();

    /** Evenements **/
    $iLat.keyup(toucheLat);
    $iLon.keyup(toucheLon);
    $bRecC.click(rechercheParCoord);

    $iBal.keyup(toucheBalise);
    $iBal.autocomplete({
        autofocus: true,
        source: function(request, response) {
            source(request, response, nomBalises);
        },
        select: select
    });

    $iTer.keyup(toucheTerrain);
    $iTer.autocomplete({
        autofocus: true,
        source: function(request, response) {
            source(request, response, nomTerrains);
        },
        select: select
    });

    $bRecB.click(rechercheParBalise);
    $bRecT.click(rechercheParTerrain);

    $('.raz-cherche').click(razCherche);

    /* declenchement pi sur un bouton droit sur la carte */
    orbit.on('contextmenu', function(e) {
        var coord = [e.latlng.lat, e.latlng.lng];
        centrerMap(coord, PIO_ZOOM);
        declencherPIO(coord);
    });

    L.easyButton('glyphicon-refresh', function() { 
        centrerMap();     
    }).addTo(orbit);

    creerModals();

    function creerModals() {
        var $f = $('<form method="post" action="#" class="form-horizontal"></form>');

        $f.fhtml(lsPioProp, '');

        $f.append('<h3>Terrains interrogés</h3><ul></ul>');

        $content.append(
            $fModPi.addModal(
                'Editer le PI',
                $f,
                '<button class="btn btn-primary btn-small"><span class="glyphicon glyphicon-edit"></span></button>'
            )
        );

        $fModPi.find('.btn').click(modPiHandler);
    }

    function modPiHandler() {
            $('#btn-mod-pi')
                .removeClass('btn-info')
                .addClass('btn-success');
            $('#btn-sav-pi,#btn-mail-pi,#btn-print-pi')
                .removeClass('btn-warning disabled')
                .addClass('btn-info');

            $fModPi.modal('hide');
        }

    function changeEtatBtn($btn, etat) {
        if (etat)
            $btn
            .removeClass('btn-warning disabled')
            .addClass('btn-success');
        else
            $btn
            .addClass('btn-warning disabled')
            .removeClass('btn-success');
    }

    function source(request, response, data) {
        var results = $.ui.autocomplete.filter(data, request.term);
        response(results.slice(0, NB_RESULT_AUTOCOMP));
    }

    function select(ev, ui) {
        $(this).val(ui.item.value);
        $(this).trigger("keyup");
    }

    function centrerMap(latLon, zoom) {
        orbit.setView(latLon || DFLT_LAT_LNG, zoom || DFLT_ZOOM);
    }

    function basculeRazCherche($raz) {
        ($raz.prev().val() == '') ? $raz.addClass('cache'): $raz.removeClass('cache');
    }

    function razCherche() {
        $(this).addClass('cache')
            .prev().val('')
            .parent().addClass('has-error');

        changeEtatBtn($(this).closest('.row').find('button'), 0);
    }

    /** fn recherche par coordonnées **/

    function toucheLat(key) {
        basculeRazCherche($(this).next());
        toucheLatLon(key);
    }

    function toucheLon(key) {
        basculeRazCherche($(this).next());
        toucheLatLon(key);
    }

    function toucheLatLon(key) {
        var lat = validerLat(),
            lon = validerLon();
        if (lat && lon) {
            changeEtatBtn($bRecC, 1);
            (key.keyCode == '13') ? $bRecC.trigger('click'): '';
        } else
            changeEtatBtn($bRecC, 0);
    }

    function validerLat() {
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

    function validerLon() {
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

    function rechercheParCoord(e) {
        if (!$(this).hasClass('btn-warning')) {
            declencherPIO([$iLat.val(), $iLon.val()]);
        }
    };

    /** fn recherche par balises **/
    function toucheBalise(e) {
        basculeRazCherche($(this).next());
        var nomBal = $(this).val();
        if (!nomBal) {
            changeEtatBtn($bRecB, 0);
            return false;
        }
        var iBal = trouveNomBalise(nomBal);
        if (typeof iBal == 'undefined') {
            $(this).parent()
                .addClass('has-error');
            changeEtatBtn($bRecB, 0);
        } else {
            $(this).parent()
                .removeClass('has-error');
            changeEtatBtn($bRecB, 1);

            var coord = balises[iBal].geometry.coordinates;
            $(this).data('latLon', [coord[1], coord[0]]);

            if (e.keyCode == '13') {
                $(this).autocomplete('close');
                $bRecB.trigger('click');
            }
        }
    };

    function rechercheParBalise() {
        if (!$(this).hasClass('btn-warning')) {
            var latlon = $iBal.data('latLon');
            centrerMap(latlon);
            declencherPIO(latlon);
        }
    }

    function trouveNomBalise(bal) {
        var iBal;
        $.each(nomBalises, function(i, b) {
            if (bal.toUpperCase() === b.toUpperCase()) {
                iBal = i;
                return false;
            }
        });
        return iBal;
    }

    /** fn recherche par terrains **/
    function toucheTerrain(e) {
        basculeRazCherche($(this).next());
        var nomTer = $(this).val();
        if (!nomTer) {
            changeEtatBtn($bRecT, 0);
            return false;
        }
        var iTer = trouveNomTerrain(nomTer);
        if (typeof iTer == 'undefined') {
            $(this).parent()
                .addClass('has-error');
            changeEtatBtn($bRecT, 0);
        } else {
            $(this).parent()
                .removeClass('has-error');
            changeEtatBtn($bRecT, 1);

            var coord = terrains[iTer].geometry.coordinates;
            $(this).data('latLon', [coord[1], coord[0]]);

            if (e.keyCode == '13') {
                $(this).autocomplete('close');
                $bRecT.trigger('click');
            }
        }
    };

    function rechercheParTerrain() {
        if (!$(this).hasClass('btn-warning')) {
            var latlon = $iTer.data('latLon');
            centrerMap(latlon);
            declencherPIO(latlon);
        }
    }

    function trouveNomTerrain(ter) {
        var iTer;
        $.each(nomTerrains, function(i, t) {
            if (ter.toUpperCase() === t.toUpperCase()) {
                iTer = i;
                return false;
            }
        });
        return iTer;
    }

    function nouveauPIO(obj) {

        var $li = $(
            '<li class="list-group-item lspio"></li>'
            );

        var $a = $('<a href = "#"><strong>' + ALERTES[obj.typeAl] + '</strong> le '+ obj.date + '</a>')
            .click(function() {
                
            })
            .prependTo($li);

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

    function declencherPIO(latLon) {
        rafraichir();
        /* PLACER LE MARKER SUR LA POSITION DE L'ALERTE */
        SARMk = majMarker(SARMk, latLon, icSAR);
        /* AFFICHE EN-TETE */
        infoPI();
        /* [] CONTENANT LES MARQUEURS DES TERRAINS LES PLUS PROCHES */
        var markersPIO = [];
        var tabDist = calculTerrains(latLon);
        traiterListeTerrains();

        if (pioLay) orbit.removeLayer(pioLay);
        pioLay = L.layerGroup(markersPIO).addTo(orbit);

        $onglets.tabs("option", "active", 1);

        function rafraichir() {
            pio = [];
            $carIndic.find('li').remove();
            $carInner.find('div.item').remove();
            $tab2.find('h4').eq(0).html('');
            $fPio.hasClass('cache') ? $fPio.removeClass('cache') : '';
            $carousel.hasClass('cache') ? $carousel.removeClass('cache') : '';
            $bModPi.removeClass('btn-success').addClass('btn-info');
            $('#btn-sav-pi, #btn-mail-pi, #btn-print-pi')
                .addClass('btn-warning disabled')
                .removeClass('btn-info');

            $fModPi.find('input').val('');
            $fModPi.find('li').remove();
        }

        function infoPI() {
            var dateDebut = moment().format('DD/MM hh:mm:ss');

            $fPio.find('h4').html('<span class="glyphicon glyphicon-alert"></span> PI démarré à ' + moment().format('hh:mm:ss') + ' le ' + moment().format('DD/MM'));

            $fPio.find('.label').html(Number(latLon[0]).toFixed(4) + ', ' + Number(latLon[1]).toFixed(4));

            $bSavPi.click(savePioHandler);

            function savePioHandler(e){
                nouveauPIO({
                    "typePi": $fModPi.find('select').eq(0).val(),
                    "typeAl": $fModPi.find('select').eq(1).val(),
                    "firInt": $fModPi.find('input').eq(0).val(),
                    "firSrc": $fModPi.find('input').eq(1).val(),
                    "date": moment().format('DD/MM hh:mm:ss'),
                    "pio": pio
                });
            }
        }

        function traiterListeTerrains() {

            for (var j = 0; j < Math.floor(NB_RESULT_PIO / NB_RESULT_PIO_AFF); j++) {

                var $dItem = $('<div class = "item"></div>');
                var $liIndicator = $('<li data-target = "#req-pio" data-slide-to="' + j + '"></li>');

                if (j == 0) {
                    $dItem.addClass('active');
                    $liIndicator.addClass('active');
                }

                for (var i = j * NB_RESULT_PIO_AFF; i <= ((j + 1) * NB_RESULT_PIO_AFF) - 1; i++) {

                    var $ter = traiterTerrain(i, tabDist[i]);;
                    $dItem.append($ter);
                }

                $carIndic.append($liIndicator);
                $carInner.append($dItem);
            }

            function traiterTerrain(i, ter) {

                var coord = ter.geometry.coordinates;
                var props = ter.properties;

                var $ter = 
                    $('<a class="list-group-item">'+
                        '<span class="badge">d = ' + Math.trunc(ter.d) + ' km, cap = ' + Math.trunc(ter.cap) + '°</span>'+
                        '<h5><strong>' + (i + 1) + ' - ' + props.code + '</strong> <br /><em>' + props.name + '</em> </h5>' +
                        '</a>')
                    .click({ 'latLon': [coord[1], coord[0]] }, cliqueTerrainHandler);

                var $btnContact = $('<button class = "btn-xs btn-info"><span class="glyphicon glyphicon-check"></span></button>')
                    .data({
                        "name": props.name,
                    })
                    .click(clickContactHandler)
                    .prependTo($ter);

                var img = (props.type == 'AD') ? 'glyphicon-plane' : 'glyphicon-header';

                var $btnCentrer = $('<button class = "btn-xs btn-info"><span class="glyphicon ' + img + '"></span></button>')
                    .data({ 'latLon': [coord[1], coord[0]] })
                    .click(function(e) {
                        centrerMap($(this).data().latLon, orbit.getZoom());
                    })
                    .prependTo($ter);

                if (i == 0) {
                    $ter.addClass('active');
                    sltedTerMk = majMarker(sltedTerMk, [coord[1], coord[0]], null);
                }
                markersPIO.push(creerMarkerPio(i, [coord[1], coord[0]]));

                return $ter;

                function cliqueTerrainHandler(e) {
                    detaillerBalise(e);
                    $('.carousel-inner a.active').removeClass('active');
                    $(this).addClass('active');
                }

                function clickContactHandler(e) {

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

                    pio = pio.filter(x => x.nom !=  $(this).data().name);

                    if($(this).hasClass('btn-danger')){
                        pio.push({ nom: $(this).data().name, t: moment().format('hh:mm:ss')});
                    }

                    $fModPi.find("li").remove();

                    var $ul = $fModPi.find('ul');
                    $.each(pio, function(index, val) {
                        var $li = $('<li class="list-group-item"><button class="btn-xs btn-danger type = "button"><span class="glyphicon glyphicon-remove"></span></button><strong> ' + val.t + '</strong> ' + val.nom + '</li>');
                        $li.find('button')
                            .data({'nom': val.nom})
                            .click(function(){
                                pio = pio.filter(x => x.nom !=  $(this).data().nom);
                                $(this).parent().remove();
                            });
                        $ul.append($li);
                    });
                }
            }
        }

        function creerMarkerPio(i, latlon) {
            var icon = L.icon({
                iconUrl: IMG_PIO,
                iconSize: [2 * NB_RESULT_PIO - 2 * i, 2 * NB_RESULT_PIO - 2 * i]
            });
            return L.marker(latlon, {
                icon: icon,
                opacity: (NB_RESULT_PIO - i) / NB_RESULT_PIO
            }).bindPopup(tabDist[i].properties.name);
        }

        function majMarker(marker, latLon, icon) {
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

        function calculTerrains(latLon) {
            var tabDist = [];
            $.each(terrains, function(i, val) {
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

        function detaillerBalise(e) {
            e.preventDefault();
            sltedTerMk = majMarker(sltedTerMk, e.data.latLon);
        }
    }
    /* chargement ajax des données de la map */
    function chargerTerrains() {
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

                        terrains.push(feature);
                        nomTerrains.push(prop.code);

                        return marker;
                    }
                });
                lay.addTo(orbit);
                ajouterBoutonMap('ter', lay);
            })
            .fail(function() { console.log("Erreur lors du chargement du fichier GeoJson des terrains") });
    }

    function chargerBalises() {
        $.getJSON("data/bal.GeoJson")
            .done(function(data) {
                var lay = L.geoJson(data, {
                    pointToLayer: function(feature, latlng) {
                        balises.push(feature);
                        nomBalises.push(feature.properties.code);
                        var marker = L.marker(latlng, { icon: icBal });
                        marker.bindPopup(feature.properties.code);
                        return marker;
                    }
                });
                lay.addTo(orbit);
                ajouterBoutonMap('bal', lay);

                return lay;

            })
            .fail(function() { console.log("Erreur lors du chargement du fichier GeoJson des balises.") });
    }

    function ajouterBoutonMap(nom, layer) {
        L.easyButton({
            states: [{
                stateName: nom + "-on",
                icon: '<img src="' + URL_IMG + 'btn-' + nom + '-on.png" class="btn-icon">',
                title: "Désactiver marqueurs terrains.",
                onClick: function(btn, map) {
                    map.removeLayer(layer);
                    btn.state(nom + "-off");
                }
            }, {
                stateName: nom + "-off",
                icon: '<img src="' + URL_IMG + 'btn-' + nom + '-off.png" class="btn-icon">',
                title: "Activer marqueurs terrains.",
                onClick: function(btn, map) {
                    map.addLayer(layer);
                    btn.state(nom + "-on");
                }
            }]
        }).addTo(orbit);
    }

});
