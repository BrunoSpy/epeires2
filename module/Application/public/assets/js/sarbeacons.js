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

    var Field = function(index, field, d, cap) {
        this.index = index;
        this.field = field;
        this.coord = this.field.geometry.coordinates;
        this.props = this.field.properties;
        this.d = d;
        this.cap = cap;
        this.comment = '';
        this.intTime = null;

        this.toArray = function() {
            var array = {};
            array.name = this.getName();
            array.code = this.getCode();
            array.latitude = this.coord[0];
            array.longitude = this.coord[1];
            array.comment = this.comment;
            array.intTime = this.intTime;
            return array;
        },

        this.getCoord = function() {
            if(this.coord) return this.coord;
        },

        this.getCode = function() {
            return this.props.code;
        },

        this.getName = function() {
            return this.props.name;
        },

        this.setIntTime = function(time) {
            this.intTime = time;
        },

        this.getIntTime = function() {
            return this.intTime;
        },

        this.getPopup = function() {
            return '<h3>'+this.getCode()+'</h3>'+
                '<h4>'+this.getName()+'</h4>'+
                '<h5>distance : '+this.d+' km</h5>'+
                '<h5>cap : '+this.cap+'°</h5>';
        },

        this.getResume = function() {
            var $li = $('<li class="list-group-item">' +
                '<strong>' + moment.unix(this.getIntTime()).format('HH:mm:ss') + '</strong> ' +
                '[<em>' + this.getCode() + '</em>] ' + 
                this.getName() + 
                '<span class="btn-del-field glyphicon glyphicon-remove-circle"></span><br />' + 
                '<div class = "comment">' + this.comment + '</div>' + 
            '</li>');
            return $li;
        }

        this.getHtml = function() {
            var img = (this.props.type == 'AD') ? 'glyphicon-plane' : 'glyphicon-header';
            var $field =
                $('<a class="list-group-item">' +
                    '<button class = "btn-xs btn-info"><span class="glyphicon glyphicon-check"></span></button>' +
                    '<button class = "btn-xs btn-info"><span class="glyphicon ' + img + '"></span></button>' +
                    '<span class="badge">d = ' + Math.trunc(this.d) + ' km, cap = ' + Math.trunc(this.cap) + '°</span>' +
                    '<h5><strong>' + this.props.code + '</strong> <br /><em>' + this.props.name + '</em> </h5>' +
                    '<div class="form-group comment cache"><textarea rows="1" placeholder="commentaire optionnel"></textarea></div>' +
                '</a>');

            return $field;
        },

        this.setComment = function(str) {
            console.log(str);
            this.comment = str;
        }
    }

    var IntPlan = function(latLon) {
        var _this = this;
        this.latLon = latLon;
        this.listFeature = [];
        this.ip = [];

        this.addIp = function(index) {
            var feature = this.get(index);
            feature.setIntTime(moment().format('X'));
            if (this.ip.indexOf(feature) == -1) this.ip.push(feature);
        }

        this.delIp = function(index) {
            // var feature = this.get(index);
            console.log(this.ip);
            this.ip = this.ip.filter(function(field) {
                return (field.index !== index);
            });
            // if (this.ip.indexOf(feature) != -1) this.ip.slice(index, )
            console.log(this.ip);
        }

        this.getIp = function() {
            return this.ip;
        }

        this.getList = function() {
            return this.listFeature;
        }

        this.get = function(index) {
            return this.listFeature[index];
        }
        // features = tableau de features geojson (leaflet)
        this.setList = function(features) {
            var dist = [];
            $.each(features, function(i, feature) {
                var coord = this.geometry.coordinates;
                var dRad = _this.distRad(_this.latLon[0], _this.latLon[1], coord[1], coord[0]);
                dist.push([dRad, i]);
            });
            dist.sort(function(a, b) {
                return a[0] - b[0];
            });
            dist = dist.slice(0, NB_RESULT_PIO);

            $.each(dist, function(i, dist) {
                var feature = features[dist[1]];
                var coord = feature.geometry.coordinates;
                var d = _this.radTokm(dist[0]);
                var cap = _this.cap(dist[0], _this.latLon[0], _this.latLon[1], coord[1], coord[0]) * 180 / Math.PI;
                _this.listFeature.push(new Field(i, feature, d, cap));
            });
        }

        this.distRad = function(lat1, lon1, lat2, lon2) {
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

        this.radTokm = function(rad) {
            return R_TERRE_KM * rad;
        }

        this.cap = function(drad, lat1, lon1, lat2, lon2) {
            lat1 = (Math.PI / 180) * lat1;
            lat2 = (Math.PI / 180) * lat2;
            lon1 = (Math.PI / 180) * lon1;
            lon2 = (Math.PI / 180) * lon2;

            if (Math.sin(lon2 - lon1) < 0)
                return Math.acos((Math.sin(lat2) - Math.sin(lat1) * Math.cos(drad)) / (Math.sin(drad) * Math.cos(lat1)));
            else
                return 2 * Math.PI - Math.acos((Math.sin(lat2) - Math.sin(lat1) * Math.cos(drad)) / (Math.sin(drad) * Math.cos(lat1)));
        }
    }

    var ActionBtn = function($btn = $('<button></button>'), state = 1, action = '') {
        this.$btn = $btn;
        this.state = $btn.data('state') || state;
        this.action = $btn.data('action') || action;

        this.setState = function(state) {
            this.state = state;
            this.update();
        }

        this.getState = function() {
            return this.state;
        }

        this.getAction = function() {
            return this.action;
        }

        this.update = function() {
            var _this = this;
            this.$btn.removeClass(function() {
                return _this.$btn.attr('class');
            });
            this.$btn.addClass('btn btn-action-ip');
            switch (this.state) {
                case 0:
                    this.$btn
                        .addClass('btn-danger');
                    break;
                case 1:
                    this.$btn
                        .addClass('disabled')
                        .addClass('btn-warning');
                    break;
                case 2:
                    this.$btn
                        .addClass('btn-info');
                    break;
                case 3:
                    this.$btn
                        .addClass('btn-success');
                    break;
            }
        }
    }

    var ListBtn = function() {
        var _this = this;
        this.liste = [];
        this.btnStates = [];
        this.addBtn = function(aBtn = []) {
            if (aBtn instanceof Array) {
                // On conserve l'objet invoquant la méthode
                var initStates = [];
                $.each(aBtn, function() {
                    // ici "this" vaut le bouton
                    var $btn = $(this);
                    var xbtn = new ActionBtn($btn);
                    xbtn.update();
                    initStates.push(xbtn.getState());
                    _this.liste.push(xbtn);
                });
                _this.addStates(initStates);
            }
        };

        this.addStates = function(aState = []) {
            this.btnStates.push(aState);
        };

        this.setStates = function(index = 0) {
            $.each(this.liste, function(i, xbtn) {
                xbtn.setState(_this.btnStates[index][i]);
            });
        }

        this.setBtn = function(action, state) {
            var xbtn = this.getBtn(action);
            xbtn.setState(state);
        };

        this.getBtn = function(action) {
            var xbtn;
            $.each(this.liste, function() {
                if (this.getAction() === action) {
                    xbtn = this;
                }
            });
            return xbtn;
        };
    }
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
        IMG_PIO = URL_IMG + 'marker-pio.png';
    /* icones */
    const IC_TER_SIZE = 20,
        IC_BAL_SIZE = 10,
        IC_PIO_SIZE = 20,
        IC_HEL_SIZE = 20,
        IC_SAR_SIZE = 40,
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
        // pio = [],
        intPlan = null,
        idIp = null,
        btnCenterSar;
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
        $bSavIp = $('#btn-sav-ip'),
        $bEditIp = $('#btn-edit-ip'),
        $bPrintIp = $('#btn-print-ip'),
        $bMailIp = $('#btn-mail-ip'),

        $fEditIp = $('#f-edit-ip'),
        $carInner = $('.carousel-inner'),
        $carIndic = $('.carousel-indicators'),

        $aHist = $('#a-hist'),
        $listIp = $('#list-ip');
    /* Flags */
    var editHasBeenClicked = false;

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
    /* Handlers */
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
    $bRecC.click(findByCoord);
    $bRecB.click(findByBeacon);
    $bRecT.click(findByField);
    $bEditIp.click(btnEditIpHandler);
    $bSavIp.click(btnSaveIpHandler);
    $bPrintIp.click(printIp);
    $bMailIp.click(mailIp);
    $aHist.click(aHistHandler);
    /* Boutons d'action */
    var listBtn = new ListBtn();
    listBtn.addBtn($fIp.find('.btn-action-ip').toArray());
    // On enregistre des tableaux d'etat pour les boutons, l'etat d'index 0 étant l'état initiale
    listBtn.addStates([3, 2, 1, 1]); // index 1 : EDIT OK
    listBtn.addStates([3, 3, 2, 2]); // index 2 : SAV OK
    listBtn.addStates([1, 1, 2, 2]); // index 3 : REJEU
    /* Pour gérer le bouton de centrage sur la FIR */
    // var mapControl = new MapControl();
    /* Pour gérer le bouton de centrage sur le point d'alerte Sar */
    // var mapSarControl = new MapControl();
    // mapControl.center();
    /* declenchement pi sur un bouton droit sur la carte */
    orbit.on('contextmenu', function(e) {
        var coord = [e.latlng.lat, e.latlng.lng];
        triggerIp(coord);
    });

    setMapButtons();

    function centerMap(latLon=DFLT_LAT_LNG, zoom=DFLT_ZOOM) {
        if (zoom == true) zoom = orbit.getZoom();
        orbit.setView(latLon, zoom);
    }

    function setMapButtons() {

        function refreshBtnClickHandler() {
            centerMap();
        }

        function showFieldsBtnClickHandler() {

        }

        function showBeaconsBtnClickHandler() {

        }

        function centerBtnSarHandler() {
            if(this instanceof L.Control.Button) {
                centerMap(this._button.latLon, true);
            }
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

        btnCenterSar = new L.Control.Button({
            'text': '',
            'iconUrl': icSAR.options.iconUrl,
            'onClick': centerBtnSarHandler,
            'hideText': true,
            'maxWidth': 25,
            'doToggle': false,
            'toggleStatus': true,
            'map': orbit
        });

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
        centerMap(latLon, true);
        refreshIp();
        infoPI();

        intPlan = new IntPlan(latLon);
        intPlan.setList(fields);

        mkSAR = updateMarker(mkSAR, latLon, icSAR);
        mkSAR.bindPopup('<h4>Latitude : '+latLon[0]+'</h5><h4>Longitude : '+latLon[1]+'</h5>');
        btnCenterSar._button.latLon = latLon;
        btnCenterSar.addTo(orbit);
        
        var markersPIO = [];
        for (var j = 0; j < Math.floor(NB_RESULT_PIO / NB_RESULT_PIO_AFF); j++) {
            var $dItem = $('<div class = "item"></div>');
            var $liIndicator = $('<li data-target = "#req-pio" data-slide-to="' + j + '"></li>');
            if (j == 0) {
                $dItem.addClass('active');
                $liIndicator.addClass('active');
            }
            for (var i = j * NB_RESULT_PIO_AFF; i <= ((j + 1) * NB_RESULT_PIO_AFF) - 1; i++) {
                var ter = intPlan.get(i);
                markersPIO.push(createIpMarker(i, ter.getCoord(), ter.getPopup()));
                var $ter = ter.getHtml();
                $ter.data({"index": i})
                    .click(clickFieldHandler);
                $ter.find('textarea')
                    .data({"index": i})
                    .blur(blurCommentHandler);
                $ter.find('button').eq(0)
                    .data({"index": i})
                    .click(clickContactHandler);
                $ter.find('button').eq(1)
                    .data({"index": i})
                    .click(clickIconFieldHandler);

                if (i == 0) {
                    var initCoord = intPlan.get(0).getCoord();
                    $ter.addClass('active');
                }
                $dItem.append($ter);
            }
            $carIndic.append($liIndicator);
            $carInner.append($dItem);
        }
        mkSelected = updateMarker(mkSelected, [initCoord[1], initCoord[0]], null);

        if (pioLay) orbit.removeLayer(pioLay);
        pioLay = L.layerGroup(markersPIO).addTo(orbit);

        $tabs.tabs("option", "active", 1);
        $tabs.find('.nav-pills>li').eq(1).trigger('click');

        function refreshIp() {
            idIp = null;
            $carIndic.find('li').remove();
            $carInner.find('div.item').remove();
            $tab2.find('h4').eq(0).html('');
            $fIp.hasClass('cache') ? $fIp.removeClass('cache') : '';
            $reqPio.hasClass('cache') ? $reqPio.removeClass('cache') : '';

            listBtn.setStates();

            $fEditIp.find('input').val('');
            $fEditIp.find('li').remove();
        }

        function infoPI() {
            $fIp.find('h4')
                .html('<span class="glyphicon glyphicon-alert"></span>' +
                    ' PI démarré à ' + moment().format('hh:mm:ss') +
                    ' le ' + moment().format('DD/MM'));
        }

        function clickIconFieldHandler() {
            var coord = intPlan.get($(this).data().index).getCoord();
            centerMap([coord[1], coord[0]], true);
        }

        function clickFieldHandler(e) {
            var coord = intPlan.get($(this).data().index).getCoord();
            mkSelected = updateMarker(mkSelected, [coord[1], coord[0]]);
            $('.carousel-inner a.active').removeClass('active');
            $(this).addClass('active');
        }

        function blurCommentHandler() {
            console.log($(this).html());
            intPlan.get($(this).data().index).setComment($(this).val());
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

            if ($(this).hasClass('btn-danger')) {
                $fOptCom.show();
                intPlan.addIp($(this).data().index);
            } else {
                $fOptCom.hide();
            }
            activateSavBtn();
        }

        function createIpMarker(i, latlon, popuphtml) {
            var icon = L.icon({
                iconUrl: IMG_PIO,
                iconSize: [2 * NB_RESULT_PIO - 2 * i, 2 * NB_RESULT_PIO - 2 * i]
            });
            return L.marker([latlon[1], latlon[0]], {
                icon: icon,
                opacity: (NB_RESULT_PIO - i) / NB_RESULT_PIO
            })
            .bindPopup(popuphtml);
        }

        function updateMarker(marker, latLon, icon) {
            if (!(marker === undefined)) orbit.removeLayer(marker);
            marker = L.marker(latLon);
            if (icon) marker.setIcon(icon);
            orbit.addLayer(marker);
            return marker;
        }
    }

    function activateSavBtn() {
        if (editHasBeenClicked && intPlan.getIp().length > 0) {
            listBtn.setBtn('edit', 3);
            listBtn.setBtn('sav', 2);
        } else {
            listBtn.setBtn('edit', 2);
            listBtn.setBtn('sav', 1);
        }
    }

    function btnEditIpHandler() {
        if ($(this).hasClass('disabled')) return;
        $('#title-edit-ip').html("Editer le Plan d'Interrogation");
        if (idIp == null && !$fEditIp.find('form').length) {
            $fEditIp.load(url + 'sarbeacons/form', {
                    'id': idIp,
                    'lat': mkSAR._latlng.lat,
                    'lon': mkSAR._latlng.lng
                }, loadedFormHandler);
        } else refreshFieldList();

        function loadedFormHandler() {
            refreshFieldList();
            $fEditIp.find('input[type="submit"]')
                .click(function(e) {
                    e.preventDefault();
                    $("#mdl-edit-ip").modal('hide');
                    editHasBeenClicked = true;
                    activateSavBtn();
                });
        }

        function refreshFieldList() {
            var $ul = $fEditIp.find("ul");
            $ul.find('li').remove();
            $.each(intPlan.getIp(), function(index, field) {
                var $li = field.getResume();
                $li.appendTo($ul)
                    .find('.btn-del-field')
                    .data({'index': field.index })
                    .click(removeFieldHandler);

                function removeFieldHandler () {
                    intPlan.delIp($(this).data('index'));
                    var $a = $carInner.find('a').eq($(this).data('index'));
                    $a.find('.form-group').hide();
                    $a.toggleClass('list-group-item-success')
                        .find('button').eq(0)
                        .toggleClass('btn-info')
                        .toggleClass('btn-danger')
                            .find('span')
                            .toggleClass('glyphicon-check')
                            .toggleClass('glyphicon-remove');
                    $(this).parent().remove();
                    activateSavBtn();
                }

            });
        }
    }

    function printIp() {
        if ($(this).hasClass('disabled')) return;
        if (idIp) location.href = url + 'sarbeacons/print/' + idIp;
    }

    function mailIp() {
        if ($(this).hasClass('disabled')) return;
        if (idIp) {
            $.post(url + 'sarbeacons/mail', { id: idIp }, function() {
                noty({
                    text: 'Courriels envoyés avec succès.',
                    type: 'success',
                    timeout: 4000,
                });
            });
        }
    }

    function btnSaveIpHandler() {
        if ($(this).hasClass('disabled')) return;
        $('input[name="latitude"], input[name="longitude"]').prop('disabled', false);
        var iP = [];
        $.each(intPlan.getIp(), function() {
            iP.push(this.toArray())
        });
        $.post(url + 'sarbeacons/save', { datas: $("#InterrogationPlan").serialize(), iP: iP }, function(data) {
            idIp = data.id;
            if (idIp > 0) {
                loadListIp();
                $fEditIp.find('input[name=id]').val(idIp);
                listBtn.setStates(2);
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
                    $(this).find('.list-ip-content')
                        .toggleClass('cache');
                });

                $(this).find('.btn-show').click(function(e) {
                    e.stopPropagation();
                    $.post(url + 'sarbeacons/get', { 'id': id }, function(data) {
                        triggerIp([
                            data.latitude,
                            data.longitude
                        ]);
                        idIp = id;
                        listBtn.setStates(3);
                        // TODO un peu bourrin
                        $.each(data.fields, function(i, field) {
                            $.each($carInner.find('em'), function(j, val) {
                                $(this).parent().siblings('button').eq(0).hide();
                                // console.log($(this).html());
                                // console.log(field);
                                if ($(this).html() == field.name) {
                                    intPlan.addIp(j);
                                    var $btnContact = $(this).parents('button');
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
