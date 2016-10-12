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

/* 
 * Timeline - JQuery UI Widget
 * 
 * Usage :
 * $('element').timeline({
 *      eventUrl: "url to get events",
 *      categoriesUrl: "url to get categories",
 *      controllerUrl: "root url of the controller",
 *      topOffset: vertical offset in pixels,
 *      leftOffset: left offset in pixels,
 *      rightOffset: right offset in pixels,
 *      eventHeight: height of an event in pixels,
 *      showOnlyRootCategories: boolean, if true, do not draw subcategories (default : true)
 * });
 * 
 * @author Jonathan Colson
 * @author Bruno Spyckerelle
 */

(function ($, undefined) {

    $.widget("epeires.timeline", {

        /**
         *
         * @memberOf $
         */
        version: "1.0.2",
        /**
         * List of events
         * Some properties are added during drawing:
         * - event.outside : label inside -> 0; label left -> 1; label right -> 2
         * - event.xleft
         * - event.xright
         * - event.label : label affiché ou non
         */
        events: [],
        /**
         * Table id => event position
         */
        eventsPosition: [],
        /**
         * Evènements affichés
         */
        eventsDisplayed: [],
        eventsDisplayedPosition: [],
        /**
         * Y position of displayed events
         */
        eventsYPosition: [],
        /**
         * List of categories
         */
        categories: [],
        /**
         * Table id => cat position
         */
        catPositions: [],
        /**
         * Current view
         */
        dayview: false,
        /**
         * Day to display if dayview === true
         */
        currentDay: new Date(),
        /**
         * Beginning of the timeline
         */
        timelineBegin: new Date(),
        /**
         * End of the timeline
         */
        timelineEnd: new Date(),
        /**
         * Duration of the timeline in hours
         */
        timelineDuration: 6,
        /**
         * Intervalle entre deux barres "heure"
         */
        intervalle: 0.0,
        /**
         * Largeur disponible pour la timeline, en pixels
         */
        largeurDisponible: 0,
        /**
         * Memorize current scroll position
         */
        prev_scroll: 0,
        /**
         * Last download of events
         */
        lastupdate: 0,
        /**
         *
         */
        lastUpdateTimebar: undefined,
        timerUpdate: 0,
        lastCategoriesComparator: undefined,
        lastEventComparator: undefined,
        lastFilter: undefined,
        /**
         * Is update of the view allowed ?
         */
        update: true,
        /**
         * ongoing drag event
         */
        on_drag: false,
        //paramètres pour les dessins, en pixels
        params: {
            //espace entre les catégories
            catSpace: 7,
            //espace entre les évènements
            eventSpace: 6,
            //espace entre le haut et la barre horizontale
            topSpace: 55,
            //espace entre le haut et les heures
            topHourSpace: 25,
            //espace entre le haut et les demi-heures
            topHalfHourSpace: 35,
            //espace horizontal min entre deux évènements
            eventHorizSpace:55
        },
        //default options
        options: {
            eventUrl: "",
            categoriesUrl: "",
            controllerEvent: "",
            topOffset: 0,
            leftOffset: 95,
            rightOffset: 40,
            eventHeight: 30,
            showCategories: true,
            showOnlyRootCategories: true,
            category: "",
            compact: false
        },
        //Main function
        //Initialize the timeline
        _create: function () {
            var self = this;
            var height = $(window).height() - this.options.topOffset + 'px';
            this.element.css('height', height);
            //this.element.css('top', this.options.topOffset+'px');
            var eventsContainer = $('<div id="events"></div>');
            eventsContainer.css({
                'width': 'calc(100% - '+(this.options.rightOffset + this.options.leftOffset)+'px)',
                'margin-left': this.options.leftOffset+'px'
            });
            this.element.append(eventsContainer);
            this.largeurDisponible = this.element.width() - this.options.leftOffset - this.options.rightOffset;
            //first : draw categories
            $.when(
                $.getJSON(self.options.categoriesUrl, function (data) {
                    var pos = 0;
                    $.each(data, function (key, value) {
                        self.categories.push(value);
                        self.catPositions[value.id] = pos;
                        pos++;
                    });
                })
            ).then(function () {
                //unable user to select objects
                self._setUnselectable();
                //sort categories by place
                self.sortCategories(function (a, b) {
                    if (self.options.showOnlyRootCategories) {
                        return a.place - b.place;
                    } else {
                        if (a.parent_id === -1 && b.parent_id !== -1) {
                            if (b.parent_id === a.id) {
                                return -1;
                            } else {
                                return a.place - b.parent_place;
                            }
                        } else if (a.parent_id !== -1 && b.parent_id === -1) {
                            if (a.parent_id === b.id) {
                                return 1;
                            } else {
                                return a.parent_place - b.place;
                            }
                        } else if (a.parent_id === -1 && b.parent_id === -1) {
                            return a.place - b.place;
                        } else if (a.parent_id !== -1 && b.parent_id !== -1) {
                            if (a.parent_id === b.parent_id) {
                                return a.place - b.place;
                            } else {
                                return a.parent_place - b.parent_place;
                            }
                        }
                        return 0;
                    }
                });
                //get events and display them
                $.when($.getJSON(self.options.eventUrl,
                    function (data, textStatus, jqHXR) {
                        if (jqHXR.status !== 304) {
                            var pos = 0;
                            $.each(data, function (key, value) {
                                //ajout des attributs aux évènements
                                value.display = true;
                                value.shade = false;
                                self.events.push(value);
                                self.eventsPosition[pos] = value.id;
                                pos++;
                            });
                            self.lastupdate = new Date(jqHXR.getResponseHeader("Last-Modified"));
                        }
                    })).then(
                    function () {
                        //sort events by categories which will trigger events' drawing
                        self.sortEvents();
                        //update timebar every minute
                        setInterval(function () {
                            self._updateTimebar();
                        }, 60000);
                        //update events every 10s
                        self.timerUpdate = setTimeout(function () {
                            self._updateEvents();
                        }, 10000);
                        //trigger event when init is finished
                        self._trigger("initComplete");
                    }
                );
            });

            //retracé de la timeline en cas de changement de taille de fenêtre
            $(window).resize(function () {
                var height = $(window).height() - self.options.topOffset + 'px';
                self.element.css('height', height);
                self.largeurDisponible = self.element.width() - self.options.leftOffset - self.options.rightOffset;
            });

            $(window).scroll(function(){
                $('.Base').css('top', $(window).scrollTop());
            });

            //gestion des évènements souris
            this.element.on({
                mouseenter: function () {
                    //affichage du tooltip
                    var id = $(this).data('ident');
                    var event = self.events[self.eventsPosition[id]];
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
                        viewport: '#timeline',
                        template: '<div class="tooltip tooltip-actions" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                    }).tooltip('show');
                    //affichage heure et boutons
                    $(this).find('.disp').show();
                    $(this).find('.lien.disp').hide();
                },
                mouseleave: function () {
                    //suppression tooltip
                    $(this).tooltip('destroy');
                    //suppression heure et boutons
                    $(this).find('.disp').hide();
                    $(this).find('.lien.disp').show();
                }
            }, '.elmt');

            // Déplacement de l'heure de debut
            this.element.on('mousedown', '.move_deb', function (e1) {
                self._pauseUpdate();
                self.on_drag = 1;
                var x_ref = e1.clientX;
                var x_temp = x_ref;

                var elmt = $(this).closest('.elmt');
                elmt.addClass('on_drag');
                elmt.find('.elmt_deb').show();

                var elmt_deb = elmt.find('.elmt_deb span.hour-txt');
                var rect_elmt = elmt.find('.rect_elmt');
                var elmt_compl = elmt.find('.complement');
                var rect_width = rect_elmt.width();
                var elmt_txt = elmt.find('.label_elmt');
                var pix_time = 30 * 60000 / (self.intervalle * self.largeurDisponible / 100);
                var elmt_fin = elmt.find('.elmt_fin');

                var id = elmt.data('ident');
                var event = self.eventsDisplayed[self.eventsDisplayedPosition[id]];
                var start = new Date(event.start_date);
                var temp_deb = new Date();

                self.element.mousemove(function (e2) {
                    e2.preventDefault();
                    //désactivaton de tous les liens des boutons
                    elmt.find('.label_elmt a').addClass('disabled');
                    //les jalons sont à des positions fausses pendant le déplacement :
                    //inutile de les afficher
                    elmt.find('.milestone').hide();
                    var delt = e2.clientX - x_temp;
                    var delt2 = e2.clientX - x_ref;
                    if (delt2 + 40 < rect_width) {
                        temp_deb.setTime(start.getTime() + delt2 * pix_time);
                        var txtHour = self._formatNumberLength(temp_deb.getUTCHours(), 2) + ":" + self._formatNumberLength(temp_deb.getMinutes(), 2);
                        elmt_deb.text(" " + txtHour);
                        elmt.data('start', temp_deb.getTime());
                        x_temp = e2.clientX;
                        elmt.css({'left': '+=' + delt, 'width': '-=' + delt});
                        rect_elmt.css({'width': '-=' + delt});
                        elmt_compl.css({'left': '-=' + delt});
                        if(elmt.find('.lien').hasClass('rightlink')){
                            elmt_txt.css({'left':'-='+delt});
                        }
                    }
                });
            });

            // Déplacement de l'heure de fin
            this.element.on('mousedown', '.move_fin', function (e1) {
                //pas de mise à jour des évènements pendant le mouvement
                self._pauseUpdate();
                //type de mouvement : changement de fin
                self.on_drag = -2;
                var x_ref = e1.clientX;
                var x_temp = x_ref;
                var elmt = $(this).closest('.elmt');
                //les jalons sont à des positions fausses pendant le déplacement :
                //inutile de les afficher
                elmt.find('.milestone').hide();
                elmt.addClass('on_drag');
                elmt.find('.elmt_flecheD').hide();
                elmt.find('.elmt_fin').show();
                var rect_elmt = elmt.find('.rect_elmt');
                elmt.find('.complement').hide();
                var rect_width = rect_elmt.width();
                var elmt_fin = elmt.find('.elmt_fin');
                var elmt_txt = elmt.find('.label_elmt');
                var pix_time = 30 * 60000 / (self.intervalle * self.largeurDisponible / 100);

                //récupération de l'heure de fin
                var id = elmt.data('ident');
                var event = self.eventsDisplayed[self.eventsDisplayedPosition[id]];
                var enddate = new Date(event.end_date);
                var d_fin = new Date();
                elmt.data('end', d_fin.getTime());
                if (event.end_date !== null
                    && self._isValidDate(enddate)
                    && enddate <= self.timelineEnd) {
                    d_fin = enddate;
                } else {
                    d_fin = self.timelineEnd;
                }
                //à chaque mouvement, calcul et mise à jour de l'heure
                self.element.mousemove(function (e2) {
                    e2.preventDefault();
                    //désactivaton de tous les liens des boutons
                    elmt.find('.label_elmt a').addClass('disabled');
                    var delt = e2.clientX - x_temp;
                    var delt2 = e2.clientX - x_ref;
                    if(delt !== 0){
                        //mouvement effectif
                        self.on_drag = 2;
                    }
                    if (rect_width + delt2 > 40) {
                        var temp_fin = new Date();
                        temp_fin.setTime(d_fin.getTime() + delt2 * pix_time);
                        var txtHour = self._formatNumberLength(temp_fin.getUTCHours(), 2) + ":" + self._formatNumberLength(temp_fin.getMinutes(), 2);
                        elmt_fin.find('span.hour-txt').text(txtHour + " ");
                        elmt.data('end', temp_fin.getTime());
                        x_temp = e2.clientX;
                        elmt.css({'width': '+=' + delt});
                        rect_elmt.css({'width': '+=' + delt});
                        if(elmt.find('.lien').hasClass('rightlink')) {
                            elmt_txt.css({'left':'+='+delt});
                        }
                    }
                });
            });

            //on rélache la souris :
            //enregistrement des heure de début ou fin
            this.element.on('mouseup', function () {
                self.element.unbind('mousemove');
                self.pauseUpdateView();
                var elmt = self.element.find('.on_drag');
                if (elmt[0] !== null) {
                    elmt.removeClass('on_drag');
                    var id = elmt.data("ident");
                    if (self.on_drag === 1) {
                        var time = new Date();
                        time.setTime(elmt.data('start'));
                        $.post(self.options.controllerUrl + '/changefield?id=' + id + '&field=startdate&value=' + time.toUTCString(),
                            function (data) {
                                displayMessages(data.messages);
                                if (data['event']) {
                                    self.addEvents(data.event);
                                }
                            }).always(function(){
                            self.forceUpdateView(false);
                        });
                    } else if (self.on_drag === 2) {
                        var time = new Date();
                        time.setTime(elmt.data('end'));
                        $.post(self.options.controllerUrl + '/changefield?id=' + id + '&field=enddate&value=' + time.toUTCString(),
                            function (data) {
                                displayMessages(data.messages);
                                if (data['event']) {
                                    self.addEvents(data.event);
                                }
                            }).always(function(){
                            self.forceUpdateView(false);
                        });
                    } else if (self.on_drag === -2){
                        //clic simple : heure de fin = heure actuelle
                        var time = new Date();
                        $.post(self.options.controllerUrl + '/changefield?id=' + id + '&field=enddate&value=' + time.toUTCString(),
                            function (data) {
                                displayMessages(data.messages);
                                if (data['event']) {
                                    self.addEvents(data.event);
                                }
                            }).always(function(){
                            self.forceUpdateView(false);
                        });
                    }
                }
                self.on_drag = 0;
                self._restoreUpdate();
            });
            //clic sur les heures de début => passage à confirmé
            this.element.on('click', '.elmt_deb', function(e){
                e.preventDefault();
                self.pauseUpdateView();
                var me = $(this);
                var elmt = me.closest('.elmt');
                var id = elmt.data('ident');
                var newstatus = 2;
                $.post(self.options.controllerUrl+'/changefield?id='+id+'&field=status&value='+newstatus,
                    function(data){
                        displayMessages(data.messages);
                        if(data['event']){
                            self.addEvents(data.event);
                        }
                    }
                ).always(function(){
                    self.forceUpdateView(false);
                });
            });

            //clic sur heure de fin => passage à terminé
            this.element.on('click', '.elmt_fin', function(e){
                e.preventDefault();
                self.pauseUpdateView();
                var me = $(this);
                var elmt = me.closest('.elmt');
                var id = elmt.data('ident');
                var newstatus = 3;
                $.post(self.options.controllerUrl+'/changefield?id='+id+'&field=status&value='+newstatus,
                    function(data){
                        displayMessages(data.messages);
                        if(data['event']){
                            self.addEvents(data.event);
                        }
                    }
                ).always(function(){
                    self.forceUpdateView(false);
                });
            });

            //clic sur ouverture de tooltip
            this.element.on('click', '.tooltip-evt', function(e){
                e.preventDefault();
                var me = $(this);
                var id = me.data('id');
                var txt = '<p class="elmt_tooltip actions">'
                    + '<p><a href="#" data-id="'+id+'" class="send-evt"><span class="glyphicon glyphicon-envelope"></span> Envoyer IPO</a></p>';
                var event = self.events[self.eventsPosition[id]];
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
                me.popover({
                    container: '#timeline',
                    content: txt,
                    placement:'auto top',
                    html: 'true',
                    viewport: '#timeline',
                    template: '<div class="popover label_elmt" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
                }).popover('show');
                me.parents('.elmt').tooltip('hide');
            });
            //fermeture des popover sur clic en dehors
            this.element.on('click', function (e) {
                self.element.find('.tooltip-evt').each(function () {
                    // hide any open popovers when the anywhere else in the body is clicked
                    if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                        $(this).popover('destroy');
                    }
                });
            });

            this.element.on('click', '.add-note', function(e){
                e.preventDefault();
                $("#add-note").data('id', $(this).data('id'));
            });

            this.element.on('click', '.evt-important', function(e){
                e.preventDefault();
                var me = $(this);
                var id = me.data('id');
                $.post(self.options.controllerUrl+'/changefield?id='+id+'&field=star&value=1',
                    function(data){
                        displayMessages(data.messages);
                        if(data['event']){
                            self._highlightEvent(id, true);
                        }
                    }
                );
                self.element.find('#event'+id+' .tooltip-evt').popover('destroy');
            });

            this.element.on('click', '.evt-non-important', function(e){
                e.preventDefault();
                var me = $(this);
                var id = me.data('id');
                $.post(self.options.controllerUrl+'/changefield?id='+id+'&field=star&value=0',
                    function(data){
                        displayMessages(data.messages);
                        if(data['event']){
                            self._highlightEvent(id, false);
                        }
                    }
                );
                self.element.find('#event'+id+' .tooltip-evt').popover('destroy');
            });

            this.element.on('click', '.send-evt', function(e){
                e.preventDefault();
                var me = $(this);
                var id = me.data('id');
                $.post(self.options.controllerUrl+'/sendevent?id='+id,
                    function(data){
                        displayMessages(data.messages);
                    }
                );
                self.element.find('#event'+id+' .tooltip-evt').popover('destroy');
            });

            this.element.on('click', '.cancel-evt', function(e){
                e.preventDefault();
                var me = $(this);
                var id = me.data('id');
                self.pauseUpdateView();
                $.post(self.options.controllerUrl+'/changefield?id='+id+'&field=status&value=4',
                    function(data){
                        displayMessages(data.messages);
                        if(data['event']){
                            self.addEvents(data.event);
                        }
                    }
                ).always(function(){
                    self.forceUpdateView(false);
                });
                self.element.find('#event'+id+' .tooltip-evt').popover('destroy');
            });

            this.element.on('click', '.delete-evt', function(e){
                e.preventDefault();
                var me = $(this);
                var id = me.data('id');
                self.pauseUpdateView();
                $.post(self.options.controllerUrl + '/deleteevent?id='+id,
                    function(data){
                        displayMessages(data.messages);
                        if(data['event']) {
                            self.addEvents(data.event);
                        }
                    }).always(function(){
                    self.forceUpdateView(false);
                });
                self.element.find('#event'+id+' .tooltip-evt').popover('destroy');
            });
        },
        _setOption: function (key, value) {
            if (key === "showCategories") {
                if (this.options.showCategories !== value) {
                    this._super(key, value);
                    if (this.update) {
                        this._updateView(false);
                    }
                }
            } else {
                this._super(key, value);
            }
        },
        /* ********************** */
        /* *** Public methods *** */
        /* ********************** */

        /*
         * Add or modify an event
         * @param {type} event Object
         */
        addEvent: function (event, sort) {
            //ne pas ajouter une évènement déjà existant
            if (event.id in this.eventsPosition && this.events[this.eventsPosition[event.id]]) {
                var old = this.events[this.eventsPosition[event.id]];
                event.display = old.display;
                event.shade = old.shade;
                this.events[this.eventsPosition[event.id]] = event;
            } else {
                //ajout de l'évènement en fin de tableau
                var pos = this.events.length;
                event.shade = false;
                this.events.push(event);
                this.eventsPosition[event.id] = pos;
            }
            if (sort === undefined || sort === true) {
                this.sortEvents();
            }
        },
        /**
         * Add or modify multiple events at once
         * @param {type} eventsList
         * @returns {undefined}
         */
        addEvents: function (eventsList) {
            var self = this;
            $.each(eventsList, function (key, value) {
                self.addEvent(value, false);
            });
            //sort events
            this.sortEvents();
        },
        /**
         * Return an event
         * @param id
         */
        getEvent:function (id) {
            if(id in this.eventsPosition) {
                return this.events[this.eventsPosition[id]];
            } else {
                return null;
            }
        },
        removeEvent: function (event) {
            this._hideEvent(event);
            this._trigger("hide", event, {eventId: event.id});
        },
        view: function (viewName, day) {
            if (viewName === "day" && !this.dayview) {
                this.dayview = true;
                if (day === undefined) {
                    this.currentDay = new Date(); //now
                } else {
                    var tempday = new Date(day);
                    if (this._isValidDate(tempday)) {
                        this.currentDay = tempday;
                    } else {
                        this.currentDay = new Date();
                    }
                }
                this.forceUpdateView(true);
            } else if (viewName === "sixhours" && this.dayview) {
                this.dayview = false;
                this.element.removeClass('anotherday');
                this.forceUpdateView(true);
            }
        },
        /**
         * Change the day, only avalaible if dayview == true
         * @param {type} date
         * @returns {undefined}
         */
        day: function (date) {
            var self = this;
            if (this.dayview) {
                var tempday = new Date(date);
                if (this._isValidDate(tempday)) {
                    this.currentDay = tempday;
                } else {
                    var now = new Date();
                    this.currentDay = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0);
                }
                var now = new Date();
                if(Math.floor((now.getTime() - this.currentDay.getTime())/(1000*60*60*24)) !== 0){
                    this.element.addClass('anotherday');
                } else {
                    this.element.removeClass('anotherday');
                }
                //on récupère les évènements
                this._pauseUpdate();
                this.element.find(".loading").show();
                var url = self.options.eventUrl;
                if(url.indexOf('?') > 0){
                    url += '&day=' + self.currentDay.toUTCString();
                } else {
                    url += '?day=' + self.currentDay.toUTCString();
                }
                $.when($.getJSON(url,
                    function (data, textStatus, jqHXR) {
                        if (jqHXR.status !== 304) {
                            self.pauseUpdateView();
                            self.addEvents(data);
                        }
                    }))
                    .then(function () {
                        self.forceUpdateView();
                        self._restoreUpdate();
                        self.element.find('.loading').hide();
                    });
            }
            //else : do nothing
        },
        /*
         * Sort events
         * @param {type} comparator callback 
         * @returns {undefined}
         */
        sortEvents: function (comparator) {
            var self = this;
            //comparateur par défaut 
            if (comparator === "default" || (comparator === undefined && this.lastEventComparator === undefined)) {
                if (this.options.showCategories === true) {
                    //catégorie racine, puis catégorie, puis nom, puis date de début
                    this.events.sort(function (a, b) {
                        var aPosition = self.catPositions[a.category_root_id] === undefined ? -1 : self.catPositions[a.category_root_id];
                        var bPosition = self.catPositions[b.category_root_id] === undefined ? -1 : self.catPositions[b.category_root_id];
                        if (aPosition < bPosition) {
                            return -1;
                        } else if (aPosition > bPosition) {
                            return 1;
                        }
                        if (a.category_place < b.category_place) {
                            return -1;
                        } else if (a.category_place > b.category_place) {
                            return 1;
                        }
                        if (a.name < b.name) {
                            return -1;
                        } else if (a.name > b.name) {
                            return 1;
                        }
                        var aStartdate = new Date(a.start_date);
                        var bStartdate = new Date(b.start_date);
                        if (aStartdate < bStartdate) {
                            return -1;
                        } else if (aStartdate > bStartdate) {
                            return 1;
                        }
                        return 0;
                    });
                } else {
                    this.events.sort(function (a, b) {
                        //tri par date de début uniquement
                        var aStartdate = new Date(a.start_date);
                        var bStartdate = new Date(b.start_date);
                        if (aStartdate < bStartdate) {
                            return -1;
                        } else if (aStartdate > bStartdate) {
                            return 1;
                        }
                        return 0;
                    });
                }
                this.lastEventComparator = undefined;
            } else if (comparator === undefined && this.lastEventComparator !== undefined) {
                this.events.sort(this.lastEventComparator);
            } else {
                this.lastEventComparator = comparator;
                this.events.sort(this.lastEventComparator);
            }
            for (var i = 0; i < this.events.length; i++) {
                this.eventsPosition[this.events[i].id] = i;
            }
            if (this.update === true) {
                this._updateView();
            }
        },
        /**
         * Sort categories according to comparator
         * If comparator is undefined, sort alphabetically
         * @param {type} comparator
         * @returns {undefined}
         */
        sortCategories: function (comparator) {
            if (typeof comparator === "undefined") {
                this.categories.sort(function (a, b) {
                    return (a.name > b.name ? 1 : a.name < b.name ? -1 : 0);
                });
            } else {
                this.categories.sort(comparator);
            }
            //update cat positions
            for (var i = 0; i < this.categories.length; i++) {
                this.catPositions[this.categories[i].id] = i;
            }
        },
        //default callback : display all events except status_id == 5
        filter: function (callback) {
            var cb = callback;
            if (cb === "default" || (cb === undefined && this.lastFilter === undefined)) {
                cb = function (event) {
                    return event.status_id != 5;
                };
                this.lastFilter = cb;
            } else if (cb === undefined && this.lastFilter !== undefined) {
                cb = this.lastFilter;
            } else {
                this.lastFilter = cb;
            }
            this.events.forEach(function (element, index) {
                var elt = element;
                elt.display = cb(elt);
                this[index] = elt;
            }, this.events);
        },
        /**
         * Pause updates of the view
         * Useful when many options are to be changed to avoid flicker
         */
        pauseUpdateView: function () {
            this.update = false;
        },
        /**
         * Force an update of the view or cancel pause
         * @param full
         */
        forceUpdateView: function (full) {
            this.update = true;
            this._updateView(full);
        },
        /* ********************** */
        /* *** Private methods ** */
        /* ********************** */

        /**
         * Switch between dayview and 6-hours view
         * @param full If true, redraw base and timebar
         * @returns {undefined}
         */
        _updateView: function (full) {
            //update local var
            if (this.dayview) {
                this.timelineDuration = 24;
                this.currentDay = new Date(Date.UTC(this.currentDay.getUTCFullYear(), this.currentDay.getUTCMonth(), this.currentDay.getUTCDate()));
                this.timelineBegin = new Date(Date.UTC(this.currentDay.getUTCFullYear(), this.currentDay.getUTCMonth(), this.currentDay.getUTCDate(), 0, 0, 0));
                this.timelineEnd = new Date(Date.UTC(this.timelineBegin.getFullYear(), this.timelineBegin.getMonth(), this.timelineBegin.getDate(),
                    this.timelineDuration, 0, 0));
            } else {
                this.timelineDuration = 6;
                var now = new Date();
                this.timelineBegin = new Date(now.getFullYear(), now.getMonth(), now.getDate(), now.getHours() - 1, 0, 0);
                this.timelineEnd = new Date(now.getFullYear(), now.getMonth(), now.getDate(), now.getHours() - 1 + this.timelineDuration, 0, 0);
            }
            if (!this.update) {
                return;
            }
            if (full !== false) {
                // draw base
                this._drawBase();
                // draw timeBar
                this._drawTimeBar();
            }
            //update events
            //reapply filter and determine wich events are to be displayed
            this.filter();
            for (var i = 0; i < this.events.length; i++) {
                var evt = this.events[i];
                evt.display = evt.display && this._isEventInTimeline(evt);
                //ne pas afficher les évènements dont on n'a pas la catégorie
                evt.display = evt.display && (this.options.showOnlyRootCategories ?
                    evt.category_root_id in this.catPositions :
                    evt.category_id in this.catPositions);
            }
            this.eventsDisplayed = this.events.filter(function (event) {
                return event.display;
            });
            this.eventsDisplayedPosition.length = 0;
            for (var i = 0; i < this.eventsDisplayed.length; i++) {
                var id = this.eventsDisplayed[i].id;
                this.eventsDisplayedPosition[id] = i;
            }
            //for each event, update attributes
            for (var i = 0; i < this.events.length; i++) {
                this._drawEvent(this.events[i]);
            }
            //then update y position
            var maxY = this._updateYPosition(true) + this.options.eventHeight;

            //draw categories
            if (this.options.showCategories) {
                var y = this._drawCategories();
                maxY = (maxY > y ? maxY : y );
            } else {
                this._hideCategories();
            }
            //then update height of timeline
            //var height = $(window).height() - this.options.topOffset;
            //maxY = (maxY > height ? maxY : height);
            //this.element.css('height', maxY + 'px');
        },
        _getCategory: function (id) {
            if (id in this.catPositions) {
                return this.categories[this.catPositions[id]];
            }
            return null;
        },
        _getCategoryPosition: function (id) {
            return this.catPositions[id];
        },
        _getEvent: function (id) {
            for (var i = 0; i < this.events.length; i++) {
                if (this.events[i].id === id) {
                    return this.events[i];
                }
            }
            return null;
        },
        _getEventPosition: function (id) {
            for (var i = 0; i < this.events.length; i++) {
                if (this.events[i].id === id) {
                    return i;
                }
            }
        },
        /**
         * Calcule l'abscisse en % correspondant à une date
         * Retourne -1 si en dehors de la timeline
         * @param {type} date
         * @returns number
         */
        _computeX: function (date) {
            if (date < this.timelineBegin || date > this.timelineEnd) {
                return -1;
            } else {
                return ((date - this.timelineBegin)/(1000*60*60))* this.intervalle * 2;
            }
        },
        /**
         * Calcule l'ordonnée correspondant à un évènement
         * Les abscisses de l'évènement ainsi que des précédents doivent être calculées avant
         * pour décider si il y a de la place pour le compactage
         * @param {type} event Evènement
         * @returns number
         */
        _computeY: function (event) {
            var i = this.eventsDisplayedPosition[event.id];
            if (this.options.showCategories) {
                var catPos = (this.options.showOnlyRootCategories ?
                    this._getCategoryPosition(event.category_root_id) :
                    this._getCategoryPosition(event.category_id));
                var cat = this.categories[catPos];
                if (i === 0) {
                    //premier élément : somme des hauteurs min des cat précédentes (vides par conséquent)
                    //                  + offset entre chaque catégorie (5px à rendre paramétrable)
                    var top = this.params.topSpace + this.params.catSpace;
                    for (var j = 0; j < catPos; j++) {
                        top += this._getCategoryMinHeight(this.categories[j]) + this.params.catSpace;
                    }
                    return top;
                } else {
                    var prevEvent = this.eventsDisplayed[i - 1];
                    var catOffset = 0;
                    //deux cas selon si l'évènement appartient à la même catégorie que le précédent
                    //si c'est le cas, il faut prendre en compte les possibles catégories vides précédentes
                    //le calcul de la catégorie précédente est différent si on affiche les cat non racines ou pas
                    if ((this.options.showOnlyRootCategories && prevEvent.category_root_id !== event.category_root_id) ||
                        (!this.options.showOnlyRootCategories && prevEvent.category_id !== event.category_id)) {
                        //calcul du bas de la catégorie non vide précédente
                        var prevCatPos = (this.options.showOnlyRootCategories ?
                            this._getCategoryPosition(prevEvent.category_root_id) :
                            this._getCategoryPosition(prevEvent.category_id));
                        catOffset += this._getCategoryBottom(prevCatPos);
                        //si la cat précédente est celle juste avant : rien à faire
                        if (prevCatPos < catPos - 1) {
                            for (var j = prevCatPos + 1; j < catPos; j++) {
                                catOffset += this._getCategoryMinHeight(this.categories[j]) + this.params.catSpace;
                            }
                        }
                        return catOffset + this.params.catSpace;
                    } else {
                        //l'évènement n'est pas le premier : soit on compacte soit on crée une nouvelle ligne
                        //règle de compactage : cat.compact == true : compactage sans critère
                        //cat.compact == false : compactage si le nom est identique uniquement
                        //évènements déjà tracés qui respectent les règles de compactage
                        var catEvents = this.eventsDisplayed.slice(0,i);
                        if (cat.compact) {
                            catEvents = (this.options.showOnlyRootCategories ?
                                catEvents.filter(function (val) {
                                    return val.category_root_id === cat.id;
                                }) :
                                catEvents.filter(function (val) {
                                    return val.category_id === cat.id;
                                }));
                        } else {
                            catEvents = (this.options.showOnlyRootCategories ?
                                catEvents.filter(function (val) {
                                    //warning name can be an integer
                                    if(typeof val.name !== 'string' || typeof event.name !== 'string') {
                                        return val.category_root_id === cat.id && val.name === event.name;
                                    } else {
                                        return val.category_root_id === cat.id && val.name.trim() === event.name.trim();
                                    }
                                }) :
                                catEvents.filter(function (val) {
                                    if(typeof val.name !== 'string' || typeof event.name !== 'string') {
                                        return val.category_id === cat.id && val.name === event.name;
                                    } else {
                                        return val.category_id === cat.id && val.name.trim() === event.name.trim();
                                    }
                                }));
                        }
                        //pour toutes les lignes dessinées de la catégorie, on cherche si il y a de la place
                        //liste des lignes dessinées
                        var lines = [];
                        for(var j = 0; j < catEvents.length; j++){
                            var line = this.eventsYPosition[catEvents[j].id];
                            if(!isNaN(line) && lines.indexOf(line) === -1){
                                lines.push(line);
                            }
                        }
                        for(var j = 0; j < lines.length; j++){
                            //liste des ids des evts sur la ligne                               
                            var eventsLine = [];
                            for(var id in this.eventsYPosition){
                                if(this.eventsYPosition[id] === lines[j]){
                                    eventsLine.push(id);
                                }
                            }
                            var place = true;
                            var eventRemoveLabel = [];
                            for(var k = 0; k < eventsLine.length; k++){
                                var evt = this.eventsDisplayed[this.eventsDisplayedPosition[eventsLine[k]]];
                                if(!((evt.xright + this.params.eventHorizSpace < event.xleft) ||
                                    (evt.xleft - this.params.eventHorizSpace > event.xright))){
                                    //pas de place
                                    //si compactage sur nom uniquement, on tente de supprimer le label
                                    if(cat.compact === false){
                                        var sizeWithoutLabel = this._tryRemoveLabel(evt);
                                        //on tente en enlevant un seul label
                                        if(!((sizeWithoutLabel[1] + this.params.eventHorizSpace < event.xleft) ||
                                            (sizeWithoutLabel[0] - this.params.eventHorizSpace > event.xright))){
                                            place = false;
                                            break;
                                            //on ne tente pas d'enlever les deux labels
                                            //de façon à ce qu'il en reste un sur la ligne à la fin
                                        } else {
                                            //on met l'evt de côté pour suppression du label si compactage effectif
                                            eventRemoveLabel.push(evt);
                                        }
                                    } else {
                                        //pas de place
                                        place = false;
                                        break; //inutile de continuer la vérification
                                    }
                                }
                            }
                            if(place === true){
                                for(var l = 0; l < eventRemoveLabel.length; l++){
                                    this._removeLabel(eventRemoveLabel[l]);
                                }
                                return lines[j];
                            }
                        }
                        //on n'a pas trouvé de ligne existante ayant de la place : création d'une nouvelle
                        //ensemble des evts de la catégorie déjà dessinés
                        var allCatEvents = this.eventsDisplayed.slice(0,i);
                        allCatEvents = (this.options.showOnlyRootCategories ?
                            allCatEvents.filter(function (val) {
                                return val.category_root_id === cat.id;
                            }) :
                            allCatEvents.filter(function (val) {
                                return val.category_id === cat.id;
                            }));
                        var max = 0;
                        for(var j = 0; j < allCatEvents.length; j++){
                            var ypos = this.eventsYPosition[allCatEvents[j].id];
                            if(!isNaN(ypos) && ypos > max){
                                max = ypos;
                            }
                        }
                        return max + this.options.eventHeight + this.params.eventSpace;
                    }
                }
            } else {
                if (this.options.compact) {
                    if(i === 0){
                        return this.params.topSpace;
                    } else {
                        //pour toutes les lignes dessinés, on cherche si il y a de la place
                        var lines = this.eventsYPosition.filter(function(value, index, self){
                            return self.indexOf(value) === index;
                        });
                        for(var index in lines){
                            //id des evts sur la ligne
                            var eventsLine = [];
                            for(var id in this.eventsYPosition){
                                if(this.eventsYPosition[id] === lines[index]){
                                    eventsLine.push(id);
                                }
                            }
                            var hasPlace = true;
                            //il y a de la place sur une ligne
                            //si le nouvel élément n'intersecte pas les précédents
                            for(var j = 0; j < eventsLine.length; j++){
                                var evt = this.eventsDisplayed[this.eventsDisplayedPosition[eventsLine[j]]];
                                if(!((evt.xright + this.params.eventHorizSpace < event.xleft) ||
                                    (evt.xleft - this.params.eventHorizSpace > event.xright))){
                                    //pas de place
                                    hasPlace = false;
                                    break; //inutile de continuer la vérification
                                }
                            }
                            if(hasPlace === true){
                                return lines[index];
                            }
                        }
                        //si on arrive ici, c'est qu'aucune place n'a été trouvée
                        //on renvoit donc la valeur max du tableau des positions
                        return this.options.eventHeight + this.params.eventSpace + Math.max.apply(null, this.eventsYPosition.filter(function(val){return !isNaN(val);}));
                    }
                } else {
                    //pas de compactage
                    return this.params.topSpace + i * (this.options.eventHeight + this.params.eventSpace);
                }
            }
        },
        /**
         * Update vertical position of each displayed event
         * @param {boolean} animate Animate modification of position
         * @returns {float} position of the event at the bottom
         */
        _updateYPosition: function (animate) {
            this.eventsYPosition.length = 0;
            var maxY = 0;
            for (var i = 0; i < this.eventsDisplayed.length; i++) {
                var y = this._computeY(this.eventsDisplayed[i]);
                var eventID = this.eventsDisplayed[i].id;
                this.eventsYPosition[eventID] = y;
                var elmt = this.element.find('#event' + eventID);
                if (animate === true) {
                    elmt.animate({top: y + 'px'});
                } else {
                    elmt.css('top', y + 'px');
                }
                if(y > maxY) {
                    maxY = y;
                }
            }
            return maxY;
        },
        /**
         * Calcul le bas d'une catégorie en fonction des évènements réellement affichés
         * @param {type} i Place de la catégorie dans le tableau
         * @returns {undefined}
         */
        _getCategoryBottom: function (i) {
            var cat = this.categories[i];
            var catEvents = (this.options.showOnlyRootCategories ?
                this.eventsDisplayed.filter(function (val) {
                    return val.category_root_id === cat.id;
                }) :
                this.eventsDisplayed.filter(function (val) {
                    return val.category_id === cat.id;
                }));
            var top = 0;
            var bottom = 0;
            for (var j = 0; j < catEvents.length; j++) {
                var topEvent = this.eventsYPosition[catEvents[j].id];
                if (top === 0 || topEvent < top) {
                    top = topEvent;
                }
                if (topEvent > bottom) {
                    bottom = topEvent;
                }
            }
            var height = bottom - top + this.options.eventHeight;
            var minHeight = this._getCategoryMinHeight(cat);
            if (minHeight > height) {
                return top + minHeight;
            } else {
                return top + height;
            }
        },
        /**
         * Calcul la hauteur d'une catégorie en fonction des évènements réellement affichés
         * @param {type} i Place de la catégorie dans le tableau
         * @returns {undefined}
         */
        _getCategoryHeight: function (i) {
            var cat = this.categories[i];
            var catEvents = (this.options.showOnlyRootCategories ?
                this.eventsDisplayed.filter(function (val) {
                    return val.category_root_id === cat.id;
                }) :
                this.eventsDisplayed.filter(function (val) {
                    return val.category_id === cat.id;
                }));
            var top = 0;
            var bottom = 0;
            for (var j = 0; j < catEvents.length; j++) {
                var topEvent = this.eventsYPosition[catEvents[j].id];
                if (top === 0 || topEvent < top) {
                    top = topEvent;
                }
                if (topEvent > bottom) {
                    bottom = topEvent;
                }
            }
            var height = bottom - top + this.options.eventHeight;
            var minHeight = this._getCategoryMinHeight(cat);
            return (minHeight > height ? minHeight : height);
        },
        /**
         * Calcule le nouvel intervalle lorsque timelineDuration a été modifié
         */
        _computeIntervalle: function () {
            var nbIntervalles = this.timelineDuration * 2;
            //if scrollbar visible, width is different
            //TODO : do it better
            //if ($(document).height() > $(window).height()) {
            //    largeurDisponible += this._getScrollbarWidth();
            //}
            this.intervalle = 1 / nbIntervalles * 100;
        },
        /**
         * Dessine les heures et les barres verticales
         */
        _drawBase: function () {
            //erase previous elements
            $("#Time_obj").remove();
            $("#timeline-base").remove();

            this._computeIntervalle();

            var h_temp = this.timelineBegin.getUTCHours();
            var time_obj = $('<div class="Time_obj horiz_bar"></div>');
            var base_elmt = $('<div id="timeline-base" class="Base"></div>');
            base_elmt.css({
                'margin-left': this.options.leftOffset+'px',
                'width': 'calc(100% - '+(this.options.leftOffset + this.options.rightOffset)+'px)',
                'height' : '100%'
            });
            this.element.prepend(base_elmt);
            base_elmt.append(time_obj);
            time_obj.css({
                'top': this.params.topSpace + 'px',
                'width': '100%',
                'height': 1});

            var largeurDisponible = this.element.width() - this.options.leftOffset - this.options.rightOffset;
            //if scrollbar visible, width is different
            //TODO : do it better
            if ($(document).height() > $(window).height()) {
                largeurDisponible += this._getScrollbarWidth();
            }
            //nombre d'éléments "heure" à afficher
            var nbHours = this.timelineDuration + 1;
            var tailleTotaleHours = nbHours * 37; //37px = taille de la boite TODO : à calculer dynamiquement
            var tailleTotaleHoursAndHalves = nbHours * 37 + (nbHours -1)*20; //20px = taille de la boite des demi-heures
            //si taille des heures + demi >= largeur dispo, on ne dessine plus les demi
            var drawHalves = tailleTotaleHoursAndHalves < largeurDisponible;
            //on supprime l'affichage de certaines heures jusqu'à ce que ça tienne
            var modulo = 1;
            while( (tailleTotaleHours / modulo) > largeurDisponible) {
                modulo++;
            }

            for (var i = 0; i < this.timelineDuration * 2 + 1; i++) {
                var vert_bar = $('<div class="Time_obj vert_bar"></div>');
                base_elmt.append(vert_bar);
                vert_bar.css({
                    'top': this.params.topSpace - 5 + 'px',
                    'left': this.intervalle * i + '%',
                    'width': 1,
                    'height': 'calc(100% - '+(this.params.topSpace)+'px)',
                    'background-color': '#C0C0C0'});
                if (i % 2 === 1) {
                    if(drawHalves){
                        var halfHour = $('<div class="Time_obj halfhour">30</div>');
                        base_elmt.append(halfHour);
                        halfHour.css({
                            'top': this.params.topHalfHourSpace + 'px',
                            'left': this.intervalle * i + '%'});
                    }
                } else {
                    if( (i / 2) % modulo === 0) {
                        var roundHour = $('<div class="Time_obj roundhour">' + this._formatNumberLength(h_temp, 2) + ':00</div>');
                        base_elmt.append(roundHour);
                        roundHour.css({
                            'top': this.params.topHourSpace + 'px',
                            'left': this.intervalle * i  + '%'});
                    }
                    if (h_temp === 23) {
                        h_temp = 0;
                    } else {
                        h_temp++;
                    }
                }
            }
        },
        _drawTimeBar: function () {
            if ($('#TimeBar').length > 0) {
                //Timebar exists : update
                this._updateTimebar();
            } else {
                var TimeBar = $('<div id="TimeBar"></div>');
                this.element.prepend(TimeBar);
                var x = this._computeX(new Date());
                TimeBar.css({
                    'top': /*this.options.topOffset + */this.params.topSpace + 'px',
                    'height': 'calc(100% - '+(this.params.topSpace + 5)+'px)'});
                if (x > 0) {
                    TimeBar.css('left', x + '%');
                    TimeBar.show();
                } else {
                    TimeBar.hide();
                }
            }
        },
        _updateTimebar: function () {
            var now = new Date();
            var diff = (now - this.timelineBegin) / (1000 * 60 * 60); //différence en heure
            //si vue six heures et diff > 2 heures : décaler d'une heure
            if (this.dayview === false && diff > 2) {
                //force la récupération de tous les évènements une fois par heure
                this._updateEvents(true);
                //si vue journée et affichage du jour en cours et changement de jour : afficher jour suivant
            } else if (this.dayview === true) {
                var nowUTC = Date.UTC(now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate());
                var currentDayUTC = Date.UTC(this.currentDay.getUTCFullYear(), this.currentDay.getUTCMonth(), this.currentDay.getUTCDate());
                if(this.lastUpdateTimebar !== undefined) {
                    var lastUpUTC = Date.UTC(this.lastUpdateTimebar.getUTCFullYear(), this.lastUpdateTimebar.getUTCMonth(), this.lastUpdateTimebar.getUTCDate());
                }
                if(lastUpUTC !== undefined
                        //changement de jour depuis la dernière mise à jour
                    && Math.ceil((nowUTC - lastUpUTC) / (1000 * 60 * 60 * 24)) !== 0
                        //si le jour affiché est la veille
                    && Math.ceil((nowUTC - currentDayUTC) / (1000 * 60 * 60 * 24)) === 1){
                    this.day(now);
                    //ne pas oublier de mettre à jour le widget date si il existe
                    if($("#date").length > 0){
                        var day = now.getUTCDate();
                        var month = now.getUTCMonth() + 1;
                        var year = now.getUTCFullYear();
                        var daystring = FormatNumberLength(day, 2) + "/" + FormatNumberLength(month, 2) + "/" + FormatNumberLength(year, 4);
                        $("#date").val(daystring);
                    }
                    this.lastUpdateTimebar = now;
                    return;
                }
            } else {
                //dans tous les cas : mise à jour des évènements en fonction du statut
                for (var i = 0; i < this.eventsDisplayed.length; i++) {
                    var event = this.eventsDisplayed[i];
                    var elmt = this.element.find('#event'+event.id);
                    this._updateStatus(event, elmt);
                }
            }
            var x = this._computeX(now);
            var timeBar = $('#TimeBar');
            if (x > 0) {
                /*var left = parseInt($("#timeline").css('left'));
                if (isNaN(left)) {
                    left = 0;
                }*/
                timeBar.css('left', x /*+ left*/ + '%');
                timeBar.show();
            } else {
                timeBar.hide();
            }
            this.lastUpdateTimebar = now;
        },
        /**
         * Draw or update categories
         * Events have to be drawn before in order to compute size of categories
         */
        _drawCategories: function () {
            if ($('#category').length === 0) {
                this.element.append($('<div id="category"></div>'));
            } else {
                $('#category').show();
            }
            var y = this.params.topSpace + this.params.catSpace;
            for (var i = 0; i < this.categories.length; i++) {
                var curCat = this.categories[i];
                var text_cat = '<div class="verticaltxt">'+curCat.short_name+'</div>';
                var cat = $('#category' + curCat.id);
                if ($('#category' + curCat.id).length === 0) {
                    var cat = $('<div class="category" id="category' + curCat.id + '" data-id="' + curCat.id + '" data-parentid="'+curCat.parent_id+'">'
                        + text_cat
                        + '</div>');
                    $('#category').append(cat);
                    cat.css({'background-color': curCat.color, 'height': 'auto',
                        'left': '15px'});
                    if(this._yiq(this._hex2rgb(curCat.color)) < 0.5) {
                        cat.css('color', "#000");
                    } else {
                        cat.css('color', "#fff");
                    }
                }
                var minHeight = this._getCategoryMinHeight(curCat);
                var height = this._getCategoryHeight(i);
                var trueHeight = (minHeight > height ? minHeight : height);
                cat.animate({'top': y + 'px', 'height': trueHeight + 'px'});
                y += trueHeight + this.params.catSpace;
            }
            return y;
        },
        _hideCategories: function () {
            $('#category').hide();
        },
        /**
         * Compute minimum height of a category
         * Usefull to compute size to reserve for an empty category
         * Basically : each letter takes 20px
         * TODO do it better, should depend on font size
         * @param {type} category
         * @returns {undefined}
         */
        _getCategoryMinHeight: function (category) {
            return category.short_name.length * 20;
        },
        /**
         * Draw an event if necessary
         * @param {object} event
         */
        _drawEvent: function (event) {
            var cat = (this.options.showOnlyRootCategories ?
                this._getCategory(event.category_root_id) :
                this._getCategory(event.category_id));
            //si l'evt est affiché et que la catégorie existe
            if (event.display && cat !== null) {
                //si oui, déterminer si il existe déjà ou non
                if (this.element.find('#event' + event.id).length === 0) {
                    //création de l'évènement
                    var elmt = this._getSkeleton(event);
                    //mise à jour des attributs
                    this._doDrawEvent(event, elmt, cat);
                    //ajout à la timeline
                    this.element.find("#events").append(elmt);
                } else {
                    //mise à jour des attributs
                    this._doDrawEvent(event, this.element.find('#event' + event.id), cat);
                }
            } else {
                //suppression de l'évènement
                this._hideEvent(event);
            }
        },
        /**
         * Surligne un évènement pour le mettre en valeur
         * @param {type} eventid
         * @param {boolean} highlight true to highlight, false to get back to normal
         * @returns {undefined}
         */
        _highlightEvent: function (eventid, highlight) {
            var elmt = this.element.find('#event'+eventid);
            if(highlight !== undefined && highlight === true){
                if(eventid in this.eventsPosition){
                    this.events[this.eventsPosition[eventid]].star = true;
                }
            } else {
                if(eventid in this.eventsPosition){
                    this.events[this.eventsPosition[eventid]].star = false;
                }
            }
            this._highlightElmt(elmt, highlight);
        },
        _highlightElmt: function(elmt, highlight){
            if(highlight !== undefined && highlight === true){
                if(!elmt.hasClass('star')){
                    elmt.addClass('star');
                }
            } else {
                elmt.removeClass('star');
                elmt.find('.rect_shadow').remove();
            }
        },
        /**
         *
         * @param {type} event
         * @returns {undefined}
         */
        _shadeEvent: function (event, elmt, rate) {
            var rect = elmt.find('.rect_elmt');
            var compl = elmt.find('.complement');
            var cat = (this.options.showOnlyRootCategories ?
                this._getCategory(event.category_root_id) :
                this._getCategory(event.category_id));
            var color = cat.color;
            var newcolor = this._shadeHexColor(color, rate);
            if(event.punctual){
                rect.css('border-bottom-color', newcolor);
                compl.css('border-top-color', newcolor);
            } else {
                rect.css('background-color', newcolor);
                compl.css('border-left-color', newcolor);
            }
        },
        /**
         * Hide an event if displayed
         * @param {type} event
         * @returns {undefined}
         */
        _hideEvent: function (event) {
            var elmt = this.element.find('#event' + event.id);
            //remove tooltips before
            elmt.find('span.badge.recurrence').tooltip('destroy');
            elmt.tooltip('destroy');
            elmt.fadeOut(function () {

                //remove from DOM
                $(this).remove();
            });
        },
        /**
         * Get new and modified events every 10 seconds
         * @param boolean refetch If true, refetch all events
         * @returns {undefined}
         */
        _updateEvents: function (refetch) {
            clearTimeout(this.timerUpdate);
            var self = this;
            var url = self.options.eventUrl;
            if(typeof(refetch) == "undefined" || refetch == false) {
                if(url.indexOf('?') > 0){
                    url += (self.lastupdate != 0 ? '&lastupdate=' + self.lastupdate.toUTCString() : '');
                } else {
                    url += (self.lastupdate != 0 ? '?lastupdate=' + self.lastupdate.toUTCString() : '')
                }
            }
            clearTimeout(this.timerUpdate);
            return $.getJSON(url,
                function (data, textStatus, jqHXR) {
                    if (jqHXR.status !== 304) {
                        self.addEvents(data);
                        self.forceUpdateView();
                        self.lastupdate = new Date(jqHXR.getResponseHeader("Last-Modified"));
                    }
                }).always(function () {
                self.timerUpdate = setTimeout(function () {
                    self._updateEvents();
                }, 10000);
            });
        },
        _pauseUpdate: function () {
            clearTimeout(this.timerUpdate);
        },
        /**
         * Restore automatic update of events.
         * Wait 10s before next download
         */
        _restoreUpdate: function () {
            clearTimeout(this.timerUpdate);
            var self = this;
            this.timerUpdate = setTimeout(function(){
                self._updateEvents();
            }, 10000);
        },
        /**
         * Updates all attributes of an event, except top
         * @param {type} event
         * @returns {undefined}
         */
        _doDrawEvent: function (event, elmt, categ) {
            var self = this;
            var elmt_rect = elmt.find('.rect_elmt');
            var elmt_compl = elmt.find('.complement');
            var elmt_flecheG = elmt.find('.elmt_flecheG');
            var elmt_flecheD = elmt.find('.elmt_flecheD');
            var elmt_mod = elmt.find('.modify-evt');
            var elmt_check = elmt.find('.checklist-evt');
            var elmt_tooltip = elmt.find('.tooltip-evt');
            var elmt_txt = elmt.find('.label_elmt');
            var elmt_deb = elmt.find('.elmt_deb');
            var elmt_fin = elmt.find('.elmt_fin');
            var move_deb = elmt.find('.move_deb');
            var move_fin = elmt.find('.move_fin');
            var lien = elmt.find('.lien');
            var couleur = categ.color;
            var textColor = (self._yiq(self._hex2rgb(couleur)) >= 0.5 ? "#fff" : "#000");
            var startdate = new Date(event.start_date);
            var enddate;
            if (event.end_date !== null) {
                enddate = new Date(event.end_date);
            } else {
                enddate = -1;
            }

            ////// réini
            event.label = true;
            elmt.find('.disp').removeClass('disp');
            elmt_flecheG.hide();
            elmt_flecheD.hide();
            move_deb.hide().css('border-color', textColor);
            move_fin.hide().css('border-color', textColor);
            lien.removeClass('disp leftlink rightlink').hide();
            elmt_compl.hide();
            elmt_txt.show();
            elmt_txt.css({'background-color': '', 'border-style': ''});
            elmt_txt.find('a').removeClass('disabled');
            elmt_txt.find('span.elmt_name').removeClass('unvisible');
            elmt_rect.find('.milestone').remove();
            //////

            //création de l'évènement en plusieurs étapes :
            // 1* construction des éléments : libellé, heures de début et de fin, boutons
            // 2* dessin
            // 3* ajout du label
            // 4* positionnement horizontal
            // les étapes 2, 3 et 4 dépendent du caractère ponctuel ou non de l'évènement

            /* **************** */
            /* 1: Construction  */
            /* **************** */

            // libellé de l'évènement à mettre à jour
            var name = event.name;
            if(event.recurr == true) {
                name += ' <span data-toggle="tooltip" data-container="body" data-placement="bottom" data-title="'+event.recurr_readable+'" class="badge recurrence">R</span>';
            } else {
                elmt.find('.modify-evt').data('recurr', '');
            }
            if (event.scheduled > 0) {
                name += ' <a href="#"><span class="badge scheduled">P</span></a>';
            }
            elmt_txt.find('span.elmt_name').html(name);
            elmt_txt.find('span.badge.recurrence').tooltip();

            var yDeb, yEnd, hDeb, hEnd;
            // ajout de l'heure de début
            var hDeb = this._formatNumberLength(startdate.getUTCHours(), 2) + ":" + this._formatNumberLength(startdate.getMinutes(), 2);
            var d_actuelle = new Date();
            if (startdate.getDate() !== d_actuelle.getDate()) {
                hDeb = '<span style="display: inline-block; vertical-align: middle;">' + this._formatNumberLength(startdate.getUTCDate(), 2) + "/" +
                    this._formatNumberLength(startdate.getUTCMonth() + 1, 2) + "<br/>" + hDeb + '</span>';
                yDeb = -5;
            } else {
                yDeb = 6;
            }
            elmt_deb.find('span.hour-txt').html(" " + hDeb);
            elmt_deb.css({'top': yDeb + 'px'});

            // ajout de l'heure de fin
            if (enddate !== -1) {
                var hEnd = this._formatNumberLength(enddate.getUTCHours(), 2) + ":" + this._formatNumberLength(enddate.getMinutes(), 2);
                if (enddate.getDate() !== d_actuelle.getDate()) {
                    hEnd = '<span style="display: inline-block; vertical-align: middle;">'
                        + this._formatNumberLength(enddate.getUTCDate(), 2) + "/"
                        + this._formatNumberLength(enddate.getUTCMonth() + 1, 2)
                        + "<br/>" + hEnd + '</span>';
                    yEnd = -5;
                } else {
                    yEnd = 6;
                }
            } else {
                yEnd = 4;
                hEnd = "";
            }
            elmt_fin.find('span.hour-txt').html(hEnd + " ");
            elmt_fin.css({'top': yEnd + 'px'});

            //affichage des boutons en fonction des droits
            if (event.modifiable) {
                elmt_mod.addClass('disp');
                elmt_check.addClass('disp');
            }
            elmt_tooltip.addClass('disp');

            //calcul des tailles des éléments pour le dessin
            // et le positionnement
            var x_deb = this._computeX(startdate);
            if(x_deb == -1) {
                x_deb = 0;
            }
            var x_end;
            //décalage par rapport à l'heure de début = taille de tous les objets à gauche
            var offset = 0;
            //taille finale de la boite
            var totalWidth = 0;

            var txtSize = this._computeTextSize(elmt_txt.text().trim(), "RobotoDraft", "700", "14px");

            event.txtSize = txtSize;
            //taille totale de la boite contenant le texte et les icônes
            var txt_wid = txtSize +
                + 17*3
                + (elmt_txt.find('.badge').length * 13)
                + 4 //padding
                + 2; //border-width*2
            //place à droite du texte
            var txtOffset = 17*3
                + 2 //padding
                + 1;
            var debWidth = this._outerWidth(elmt_deb) + 2; //2 pixels pour décoller du rectangle
            var endWidth = this._outerWidth(elmt_fin) + 2; //idem

            if(event.punctual) {

                /* 2: Dessin        */
                var haut = this.options.eventHeight * 2 / 3;
                var larg = haut * 5 / 8;
                elmt.addClass('punctual');
                elmt.removeClass('notpunctual');
                elmt_rect.css({'left': -larg + 'px',
                    'border-left-width': larg + 'px',
                    'border-right-width': larg + 'px',
                    'border-bottom-width': haut + 'px',
                    'border-bottom-color': couleur});
                elmt_compl.show();
                elmt_compl.css({
                    'border-left-width': larg + 'px',
                    'border-right-width': larg + 'px',
                    'border-top-width': haut + 'px',
                    'border-top-color': couleur,
                    'margin': haut * 3 / 8 + 'px 0 0 -' + larg + 'px',
                    'left' : '0px'});
                totalWidth += larg * 2;
                x_end = x_deb;

                /* 3: Positionnement du label  */
                lien.addClass('disp').show();
                // on place l'heure à droite
                if (startdate.getDate() !== d_actuelle.getDate()) {
                    elmt_deb.css({'top':'-6px'});
                } else {
                    elmt_deb.css({'top':'4px'});
                }
                elmt_deb.css({'left': larg + 1+ 'px'});
                elmt_txt.addClass('outside');
                elmt_txt.css({'top': '0px'});

                //calcul de la place restante à droite
                var place = (100 - x_deb) * this.largeurDisponible / 100;

                if ( larg + debWidth + txt_wid < place) { // s'il reste assez de place à droite du rectangle, on écrit le txt à droite
                    elmt_txt.css({
                        'left': larg + debWidth + 'px'
                    });
                    lien.css({
                        'left': 'auto',
                        'right': '100%',
                        'top' : larg + 'px',
                        'width': debWidth + 'px'});
                    lien.addClass('rightlink');

                    offset = larg; // rien à gauche, la boite finalee est décalée de la moitié de l'étoile
                    totalWidth = larg * 2 + debWidth + txt_wid;
                    event.outside = 2;
                } else { // sinon on le met à gauche
                    offset = larg + txt_wid + 2; //2 = décollage
                    elmt_txt.css({'left': - offset + 'px'});
                    lien.css({'left': txtSize + 4 + 'px', //4 = padding
                        'top' : larg + 'px',
                        'width':  (txt_wid - txtSize) + 'px'});
                    lien.addClass('leftlink');

                    event.outside = 1;
                }
                totalWidth += debWidth + txt_wid;
                elmt_txt.css('color', 'black');
                elmt_txt.find('a > span.glyphicon').css('color', 'black');

                /* 4: positionnement final de la boit englobante */
                elmt.css({'left': 'calc('+x_deb+'% - '+offset+'px)',
                    'width': totalWidth+'px'});
                elmt.children().css({'left':'+='+offset+'px'});

            } else {
                /* 2: Dessin        */
                /* Consiste à ajouter les flèches, la fin du rectangle et les heures */
                /* Positionnés par rapport au dessin du rectangle */
                elmt.removeClass('punctual');
                elmt.addClass('notpunctual');
                //cas 2 : date début antérieure au début de la timeline
                if (startdate < this.timelineBegin) {
                    elmt_flecheG.show();
                    elmt_flecheG.css({'left': - 14 + 'px'});
                    offset += 14 + debWidth;
                    totalWidth += offset;
                    elmt_deb.css({'left': -(debWidth + 14)+'px'});
                } else {
                    if(event.modifiable && event.recurr == false){
                        move_deb.addClass('disp');
                        move_deb.css({'left': 8 + 'px'});
                    }
                    offset += debWidth;
                    totalWidth += offset;
                    elmt_deb.css({'left': - debWidth + 'px'});
                }
                //cas 3 : date fin postérieure à la fin de la timeline
                if (enddate > this.timelineEnd) {
                    x_end = this._computeX(this.timelineEnd);
                    elmt_flecheD.show();
                    elmt_flecheD.css({'right': -14 + 'px', 'left': 'auto'});
                    totalWidth += 14 + endWidth;
                    elmt_fin.css({'left': 'auto',
                        'right': - (14 + endWidth) + 'px'});
                    //cas 4 : date fin dans la timeline
                } else if (enddate > 0) {
                    x_end = this._computeX(enddate);
                    totalWidth += endWidth;
                    elmt_fin.css({'left':'auto', 'right': - endWidth+'px'});
                    //cas 5 : pas de fin
                } else {
                    x_end = this._computeX(this.timelineEnd);
                    var haut = this.options.eventHeight;
                    elmt_compl.css({'left': 'auto',
                        'right' : - haut - 3 +'px',
                        'border-left-width': haut + 'px',
                        'border-left-color' : couleur,
                        'border-top-width': haut / 2 + 1 + 'px',
                        'border-bottom-width': haut / 2 + 1 + 'px'});
                    elmt_compl.show();
                    totalWidth += haut;
                }
                //dans tous les cas
                if(event.modifiable){
                    move_fin.addClass('disp');
                    move_fin.css({'right': 12 + 'px'});
                }
                elmt_rect.css({'left': 0 + 'px',
                    'height': this.options.eventHeight,
                    'background-color': couleur});

                //milestones
                $.each(event.milestones, function(index, item){
                    var xMilestone = self._computeX(new Date(item));
                    if(xMilestone > x_deb && xMilestone < x_end) {
                        var milestone = $('<div class="milestone"></div>');
                        var left = (xMilestone - x_deb)/(x_end - x_deb)*100;
                        milestone.css({'left': left+"%", 'color':textColor});
                        elmt_rect.append(milestone);
                    }
                });


                /* 3: Positionnement du label  */
                //conversion en pixel de la taille du rectangle
                var rectPixels = (x_end - x_deb) * this.largeurDisponible / 100;
                if(rectPixels < 40) {
                    // suppression des barres de modifcations des heures
                    // qui se chevauchent
                    move_deb.removeClass('disp');
                    move_fin.removeClass('disp');
                }
                if(txt_wid + 40 < rectPixels) {
                    //assez de place dans le rectangle
                    //le label est positionnée par rapport à la boite
                    //et non par rapport au rectangle
                    elmt_txt.removeClass('outside');
                    event.outside = 0;
                    elmt_txt.css({'left': 22 + debWidth + 'px',
                        'top': (this.options.eventHeight/2-11)+'px'});
                    elmt_txt.css('color', textColor);
                    elmt_txt.find('a > span.glyphicon').css('color', textColor);
                } else {
                    elmt_txt.css({'top': (this.options.eventHeight/2-13)+'px'});
                    //positionnement à droite ou à gauche
                    var place = (100 - x_end) * this.largeurDisponible / 100;
                    lien.addClass('disp').show();
                    elmt_txt.addClass('outside');
                    elmt_txt.css('color', 'black');
                    elmt_txt.find('a > span.glyphicon').css('color', 'black');
                    if (endWidth + txt_wid < place) { // s'il reste assez de place à droite du rectangle, on écrit le txt à droite
                        elmt_txt.css({'left': 'calc(100% - '+txt_wid+'px)'});
                        totalWidth += txt_wid;
                        lien.css({'left': - endWidth + 'px',
                            'width': endWidth + 'px'});
                        lien.addClass('rightlink');
                        event.outside = 2;
                    } else { // sinon on le met à gauche
                        offset += txt_wid;
                        lien.css({'left': txt_wid - txtOffset + 'px', 'width': debWidth + txtOffset + 'px'});
                        elmt_txt.css({'left': 0 + 'px'});
                        lien.addClass('leftlink');
                        totalWidth += txt_wid;
                        event.outside = 1;
                    }
                }


                /* 4: positionnement final de la boit englobante */
                elmt.css({'left': 'calc('+x_deb+'% - '+offset+'px)',
                    'width': 'calc('+(x_end-x_deb)+'% + '+totalWidth+'px)'});
                elmt_rect.css({'width': 'calc(100% - '+totalWidth+'px)',
                                'left': '+=' + offset + 'px'});
            }

            //highlight
            this._highlightElmt(elmt, event.star);

            //pour les besoins de comparaison des positions des évènements
            //on a besoin de positions absolues
            event.xleft = x_deb * this.largeurDisponible / 100 - offset;
            event.xright = x_end * this.largeurDisponible / 100 + totalWidth - offset;
            //impossible de récupérer la règle calc() par la suite
            //on stocke donc les éléments permettant de la recalculer
            event.xdeb = x_deb;
            event.xend = x_end;
            event.offset = offset;
            event.totalWidth = totalWidth;
            //mise à jour des attributs en fonction du statut
            this._updateStatus(event, elmt);

            //une fois le statut analysé, on sait si l'affichage de l'heure est forcé ou non
            if(event.outside === 0) {//inside
                if(event.hourBeginForced == false) {
                    event.xleft += debWidth;
                }
                if(event.hourEndForced == false) {
                    event.xright -= endWidth;
                }
            } else if (event.outside === 1 && event.hourBeginForced == false) { //left
                event.xright -= endWidth;
            } else if (event.outside === 2 && event.hourEndForced == false) { //right
                event.xleft += debWidth;
            }
        },
        /**
         * Remove label and update size
         * Only if label is outside
         */
        _removeLabel: function(event){
            var elmt = this.element.find('#event'+event.id);
            var elmt_rect = elmt.find('.rect_elmt');
            var elmt_txt = elmt.find('.label_elmt.outside');
            if(elmt_txt.length > 0 && event.label === true){
                event.label = false;
                var lien = elmt.find('.lien');
                //mise à jour des dimensions
                var txt_width = event.txtSize;
                if(lien.hasClass('leftlink')){

                    event.offset -= txt_width;
                    event.totalWidth += txt_width;
                    event.xleft += txt_width;
                    elmt.css({'left': 'calc('+event.xdeb+'% - '+event.offset+'px)',
                        'width': 'calc('+(event.xend-event.xdeb)+'% + '+event.totalWidth+'px)'});
                    elmt_rect.css({'width': 'calc(100% - '+event.totalWidth+'px)',
                        'left': '-=' + txt_width + 'px'});

                } else if(lien.hasClass('rightlink')){
                    event.totalWidth -= txt_width;
                    elmt_txt.css({'left': 'calc(100% - '+txt_width+'px)'});
                    elmt.css({'width': 'calc('+(event.xend-event.xdeb)+'% + '+event.totalWidth+'px)'});
                    elmt_rect.css({'width': 'calc(100% - '+event.totalWidth+'px)'});

                    event.xright -= txt_width;
                }
                //suppression du lien
                elmt.find('.lien').removeClass('disp leftlink rightlink').hide();
                //on cache le txt
                elmt_txt.find('span.elmt_name').addClass('unvisible');
                elmt_txt.addClass('disp').hide();
            }
        },
        /**
         * Compute size of an event if label removed
         * But do not remove the label at this point
         * @param event
         * @return array [x1, x2]
         */
        _tryRemoveLabel: function(event){
            var result = [event.xleft, event.xright];
            if(event.outside === 1){
                result[0] = event.xleft + event.txtSize;
            } else if(event.outside === 2){
                result[1] = event.xright - event.txtSize;
            }
            return result;
        },
        /**
         * Update an event according to its status and dates :
         *  - start date and icon
         *  - end date and icon
         *  - label
         *  - color
         * @param {type} event
         * @param {type} elmt jquery elmt representing an event
         * @returns {undefined}
         */
        _updateStatus: function (event, elmt) {
            var elmt_txt = elmt.find('.label_elmt');
            var elmt_deb = elmt.find('.elmt_deb');
            var elmt_fin = elmt.find('.elmt_fin');
            var elmt_compl = elmt.find('.complement');
            var move_deb = elmt.find('.move_deb');
            var move_fin = elmt.find('.move_fin');
            var rect = elmt.find('.rect_elmt');
            var lien = elmt.find('.lien');
            var now = new Date();
            var start = new Date(event.start_date);
            var end = new Date(event.end_date);
            event.hourBeginForced = false;
            event.hourEndForced = false;
            switch (event.status_id) {
                case 1: //nouveau
                    //label en italique
                    elmt_txt.css({'font-style': 'italic'});
                    elmt_txt.find('span').css({'text-decoration': ''});
                    elmt_txt.find('span.elmt_name').removeClass('dlt dlt-grey');
                    //heure de début cliquable
                    elmt_deb.removeClass('disabled');
                    if (now > start) {
                        //afficher heure de début avec warning + enlever lien
                        elmt_deb.find('span.glyphicon').removeClass().addClass('glyphicon glyphicon-warning-sign');
                        elmt_deb.removeClass('disp').show().tooltip({
                            title: "Cliquer pour confirmer l'heure de début.",
                            container: 'body'
                        });
                        lien.filter('.leftlink').addClass('disp').show();
                        event.hourBeginForced = true;
                    } else {
                        //affichage sur hover avec (?)
                        elmt_deb.find('span.glyphicon').removeClass().addClass('glyphicon glyphicon-question-sign');
                        elmt_deb.addClass('disp').tooltip({
                            title: "Cliquer pour confirmer l'heure de début.",
                            container: 'body'
                        });
                        lien.filter('.leftlink').addClass('disp').show();
                    }
                    //heure de fin cliquable
                    elmt_fin.removeClass('disabled');
                    if(event.punctual || event.end_date === null){
                        elmt_fin.removeClass('disp').hide().tooltip('destroy');
                        elmt_compl.show();
                    } else {
                        if (this._isValidDate(end) && now > end) {
                            //afficher heure de fin avec warning
                            elmt_fin.find('span.glyphicon').removeClass().addClass('glyphicon glyphicon-warning-sign');
                            elmt_fin.removeClass('disp').show().tooltip({
                                title: "Cliquer pour confirmer l'heure de fin.",
                                container: 'body'
                            });
                            lien.filter('.rightlink').removeClass('disp').hide();
                            event.hourEndForced = true;
                        } else {
                            //affichage sur hover avec (?)
                            elmt_fin.find('span.glyphicon').removeClass().addClass('glyphicon glyphicon-question-sign');
                            elmt_fin.addClass('disp').hide().tooltip({
                                title: "Cliquer pour confirmer l'heure de fin.",
                                container: 'body'
                            });
                            lien.filter('.rightlink').addClass('disp').show();
                        }
                    }
                    //couleur estompée
                    this._shadeEvent(event, elmt, 0.4);
                    if(!event.punctual) {
                        rect.addClass('stripes');
                    }
                    break;
                case 2: //confirmé
                    //label normal
                    elmt_txt.css({'font-style': 'normal'});
                    elmt_txt.find('span').css({'text-decoration': ''});
                    elmt_txt.find('span.elmt_name').removeClass('dlt dlt-grey');
                    //heure de début : non cliquable, sur demande avec case cochée
                    elmt_deb.find('span.glyphicon').removeClass().addClass('glyphicon glyphicon-check');
                    elmt_deb.addClass('disp disabled').hide().tooltip('destroy');
                    lien.filter('.leftlink').addClass('disp').show();
                    //heure de fin cliquable
                    elmt_fin.removeClass('disabled');
                    if(event.punctual || event.end_date === null){
                        elmt_fin.removeClass('disp').hide();
                        elmt_compl.show();
                    } else {
                        if (this._isValidDate(end) && now > end) {
                            //afficher heure de fin avec warning
                            elmt_fin.find('span.glyphicon').removeClass().addClass('glyphicon glyphicon-warning-sign');
                            elmt_fin.removeClass('disp').show().tooltip({
                                title: "Cliquer pour confirmer l'heure de fin.",
                                container: 'body'
                            });
                            lien.filter('.rightlink').removeClass('disp').hide();
                            event.hourEndForced = true;
                        } else {
                            //affichage sur hover avec (?)
                            elmt_fin.find('span.glyphicon').removeClass().addClass('glyphicon glyphicon-question-sign');
                            elmt_fin.addClass('disp').hide().tooltip({
                                title: "Cliquer pour confirmer l'heure de fin.",
                                container: 'body'
                            });
                            lien.filter('.rightlink').addClass('disp').show();
                        }
                    }
                    //couleur normale
                    rect.removeClass('stripes');
                    break;
                case 3: //terminé
                    //label normal
                    elmt_txt.css({'font-style': 'normal'});
                    elmt_txt.find('span').css({'text-decoration': ''});
                    elmt_txt.find('span.elmt_name').removeClass('dlt dlt-grey');
                    //heure de début et heure de fin : non cliquable, sur demande avec case cochée
                    elmt_deb.find('span.glyphicon').removeClass().addClass('glyphicon glyphicon-check');
                    elmt_deb.addClass('disp disabled').hide().tooltip('destroy');
                    lien.filter('.leftlink').addClass('disp').show();
                    elmt_fin.addClass('disabled').tooltip('destroy');
                    if (event.punctual || event.end_date === null) {
                        elmt_fin.removeClass('disp').hide();
                        elmt_compl.show();
                    } else {
                        elmt_fin.find('span.glyphicon').removeClass().addClass('glyphicon glyphicon-check');
                        elmt_fin.addClass('disp').hide();
                    }
                    lien.filter('.rightlink').addClass('disp').show();
                    //couleur normale
                    rect.removeClass('stripes');
                    break;
                case 4: //annulé
                    //label barré
                    elmt_txt.css({'font-style': 'normal', 'color': 'grey'});
                    elmt_txt.find('span.elmt_name').removeClass('dlt dlt-grey');
                    elmt_txt.find('span.elmt_name').css({'text-decoration': 'line-through'});
                    //heure de début et heure de fin : non cliquable, sur demande sans icone
                    elmt_deb.find('span.glyphicon').removeClass().addClass('glyphicon');
                    elmt_deb.addClass('disp disabled').hide().tooltip('destroy');
                    elmt_fin.addClass('disabled').tooltip('destroy');
                    lien.filter('.leftlink').addClass('disp').show();
                    if (event.punctual || event.end_date === null){
                        elmt_fin.removeClass('disp').hide();
                        elmt_compl.show();
                    } else {
                        elmt_fin.find('span.glyphicon').removeClass().addClass('glyphicon');
                        elmt_fin.addClass('disp').hide();
                    }
                    lien.filter('.rightlink').addClass('disp').show();
                    //ne pas permettre la modification des heures
                    move_deb.removeClass('disp');
                    move_fin.removeClass('disp');
                    //couleur estompée
                    this._shadeEvent(event, elmt, 0.5);
                    rect.removeClass('stripes');
                    //un évènement annulé ne peut pas être important
                    this._highlightElmt(elmt, false);
                    break;
                case 5:
                    //label barré
                    elmt_txt.css({'font-style': 'normal', 'color': 'grey'});
                    elmt_txt.find('span.elmt_name').addClass('dlt  dlt-grey');
                    //heure de début et heure de fin : non cliquable, sur demande sans icone
                    elmt_deb.find('span.glyphicon').removeClass().addClass('glyphicon');
                    elmt_deb.addClass('disp disabled').hide().tooltip('destroy');
                    elmt_fin.addClass('disabled').tooltip('destroy');
                    lien.filter('.leftlink').addClass('disp').show();
                    if (event.punctual || event.end_date === null){
                        elmt_fin.removeClass('disp').hide();
                        elmt_compl.show();
                    } else {
                        elmt_fin.find('span.glyphicon').removeClass().addClass('glyphicon');
                        elmt_fin.addClass('disp').hide();
                    }
                    lien.filter('.rightlink').addClass('disp').show();
                    //ne pas permettre la modification des heures
                    move_deb.removeClass('disp');
                    move_fin.removeClass('disp');
                    //couleur estompée
                    this._shadeEvent(event, elmt, 0.5);
                    rect.removeClass('stripes');
                    //un évènement supprimé ne peut pas être important
                    this._highlightElmt(elmt, false);
                    break;

            }
        },
        /**
         * Create a skeleton for an event.
         * @param {type} event
         * @returns {$|Window.$|@exp;_$|jQuery}
         */
        _getSkeleton: function (event) {
            // création d'un élément
            var elmt = $('<div class="elmt" id="event' + event.id + '"></div>');
            elmt.data("ident", event.id);
            // ajout d'un rectangle
            var elmt_rect = $('<div class="rect_elmt"></div>');
            elmt.append(elmt_rect);
            var elmt_compl = $('<div class="complement"></div>');
            elmt_rect.append(elmt_compl);
            // si l'événement a commencé avant la timeline, ajout d'une flèche gauche
            var elmt_flecheG = $('<div class="elmt_flecheG"></div>');
            elmt_rect.append(elmt_flecheG);
            elmt_flecheG.append('<span class="glyphicon glyphicon-arrow-left"></span>');
            // si l'événement se poursuit au-delà de la timeline, ajout d'une flèche droite
            var elmt_flecheD = $('<div class="elmt_flecheD"></div>');
            elmt_rect.append(elmt_flecheD);
            elmt_flecheD.append('<span class="glyphicon glyphicon-arrow-right"></span>');
            // ajout du nom de l'événement
            var elmt_txt = $('<p class="label_elmt"><span class="elmt_name">' + event.name + '</span></p>');
            elmt.append(elmt_txt);
            // ajout du bouton "ouverture fiche"
            var elmt_b1 = $('<a href="#" class="modify-evt" data-id="' + event.id + '" data-name="' + event.name + '" data-recurr="' + event.recurr + '"></a>');
            elmt_txt.append(elmt_b1);
            elmt_b1.append(' <span class="glyphicon glyphicon-pencil"></span>');
            // ajout du bouton "ouverture fiche réflexe"
            var elmt_b2 = $('<a href="#" class="checklist-evt" data-id="' + event.id + '" data-name="' + event.name + '"></a>');
            elmt_txt.append(elmt_b2);
            elmt_b2.append(' <span class="glyphicon glyphicon-tasks"></span>');
            //ajout bouton ouverture menu tooltip
            var elmt_b3= $('<a href="#" class="tooltip-evt" data-id="' + event.id + '"></a>');
            elmt_b3.append(' <span class="glyphicon glyphicon-chevron-up"></span>');
            elmt_txt.append(elmt_b3);
            // lien entre le texte et l'événement (si texte écrit en dehors)
            var lien = $('<div class="lien"></div>');
            elmt_txt.append(lien);
            var elmt_deb = $('<a href="#" class="elmt_deb"><span class="glyphicon"></span><span class="hour-txt"></span></a>');
            elmt_rect.append(elmt_deb);
            var elmt_fin = $('<a href="#" class="elmt_fin"><span class="hour-txt"></span><span class="glyphicon"></span></a>');
            elmt_rect.append(elmt_fin);
            var move_deb = $('<p class="move_deb"></p>');
            elmt_rect.append(move_deb);
            var move_fin = $('<p class="move_fin"></p>');
            elmt_rect.append(move_fin);
            var dy = this.options.eventHeight;
            var largeur = this.element.width();
            elmt.css({'position': 'absolute', 'left': '0px', 'width': largeur, 'height': dy, 'top': this.options.topOffset+'px'});
            elmt_flecheG.css({'position': 'absolute', 'top': dy/2 - 10 + 'px', 'left': '0px'});
            elmt_flecheD.css({'position': 'absolute', 'top': dy/2 - 10 + 'px', 'left': '0px'});
            elmt_b1.css({'z-index': 1});
            elmt_b2.css({'z-index': 1});
            elmt_txt.css({'position': 'absolute', 'top': dy / 2 - 11 + 'px', 'left': '0px'});
            lien.css({'position': 'absolute', 'top': dy / 2 + 'px', 'left': '0px', 'width': '10px', 'height': '1px', 'background-color': 'gray', 'z-index': 1});

            move_deb.css({'height': dy - 8});
            move_fin.css({'height': dy - 8});
            return elmt;
        },
        /* *********************** */
        /* ** Utilitary methods ** */
        /* *********************** */

        /**
         * Format a number into a string with a predefined length
         * @param {type} num
         * @param {type} length
         * @returns {String}
         */
        _formatNumberLength: function (num, length) {
            var r = "" + num;
            while (r.length < length) {
                r = "0" + r;
            }
            return r;
        },
        /**
         * Compute width of the scrollbar
         * @returns int Width of the scrollbar in pixels
         */
        _getScrollbarWidth: function () {
            var outer = document.createElement("div");
            outer.style.visibility = "hidden";
            outer.style.width = "100px";
            outer.style.msOverflowStyle = "scrollbar"; // needed for WinJS apps

            document.body.appendChild(outer);

            var widthNoScroll = outer.offsetWidth;
            // force scrollbars
            outer.style.overflow = "scroll";

            // add innerdiv
            var inner = document.createElement("div");
            inner.style.width = "100%";
            outer.appendChild(inner);

            var widthWithScroll = inner.offsetWidth;

            // remove divs
            outer.parentNode.removeChild(outer);

            return widthNoScroll - widthWithScroll;
        },
        /**
         * Do not allow selection of objects on the timeline
         * @returns {undefined}
         */
        _setUnselectable: function () {
            this.element.attr('unselectable', 'on')
                .css({'-moz-user-select': '-moz-none',
                    '-moz-user-select':'none',
                    '-o-user-select': 'none',
                    '-khtml-user-select': 'none',
                    '-webkit-user-select': 'none',
                    '-ms-user-select': 'none',
                    'user-select': 'none'
                }).bind('selectstart', function () {
                return false;
            });
        },
        _isValidDate: function (d) {
            if (Object.prototype.toString.call(d) !== "[object Date]")
                return false;
            return !isNaN(d.getTime());
        },
        /**
         *
         * @param {type} event
         * @returns {boolean}
         */
        _isEventInTimeline: function (event) {
            var startdate = new Date(event.start_date);
            var enddate = null;
            if (event.end_date !== null) {
                enddate = new Date(event.end_date);
            }
            // si l'evt intersecte la timeline
            if ((event.punctual && startdate >= this.timelineBegin && startdate <= this.timelineEnd) ||
                (!event.punctual && startdate < this.timelineEnd && enddate === null) ||
                (!event.punctual && enddate !== null &&
                (enddate > this.timelineBegin && startdate < this.timelineEnd))) {
                return true;
            } else {
                return false;
            }
        },
        _computeTextSize: function (str, font, fontWeight, fontSize) {
            var fakeEl = $('<span>').hide().appendTo(document.body);
            fakeEl.text(str).css({'font' : font,
                                'font-weight' : fontWeight,
                                'font-size' : fontSize});
            var size = fakeEl.width();
            fakeEl.remove();
            return size;
        },
        /**
         * Returns original width and not calculated pixels
         * @param element
         * @returns {*}
         * @private
         */
        _getCSSWidth: function (element) {
            return element.clone().appendTo('body').wrap('<div style="display: none"></div>').css('width');
        },
        _outerWidth: function(object){
            var fakediv = $('<div>').hide().appendTo(document.body);
            var newobj = object.clone();
            fakediv.append(newobj);
            var width = newobj.outerWidth();
            fakediv.remove();
            return width;
        },
        _hexdec: function(hexString){
            hexString = (hexString + '').replace(/[^a-f0-9]/gi, '');
            return parseInt(hexString, 16);
        },
        /**
         *
         * @param color
         */
        _hex2rgb: function(color) {
            var hex = color.replace("#", "");

            if(hex.length == 3) {
                var r = this._hexdec(hex.substr(0,1).substr(0,1));
                var g = this._hexdec(hex.substr(1,1).substr(1,1));
                var b = this._hexdec(hex.substr(2,1).substr(2,1));
            } else {
                var r = this._hexdec(hex.substr(0,2));
                var g = this._hexdec(hex.substr(2,2));
                var b = this._hexdec(hex.substr(4,2));
            }
            return [r, g, b];
        },
        _yiq: function(rgb) {
            return 1 - (rgb[0] * 0.299 + rgb[1] * 0.587 + rgb[2] * 0.114)/255;
        },
        /**
         * @param color Hex color (with #)
         * @param percent Value between -1.0 and 1.0. Negative : darker, positive : lighter
         */
        _shadeHexColor: function(color, percent) {
            var f=parseInt(color.slice(1),16),t=percent<0?0:255,p=percent<0?percent*-1:percent,R=f>>16,G=f>>8&0x00FF,B=f&0x0000FF;
            return "#"+(0x1000000+(Math.round((t-R)*p)+R)*0x10000+(Math.round((t-G)*p)+G)*0x100+(Math.round((t-B)*p)+B)).toString(16).slice(1);
        },
        _shadeRGBColor:function (color, percent) {
            var f=color.split(","),t=percent<0?0:255,p=percent<0?percent*-1:percent,R=parseInt(f[0].slice(4)),G=parseInt(f[1]),B=parseInt(f[2]);
            return "rgb("+(Math.round((t-R)*p)+R)+","+(Math.round((t-G)*p)+G)+","+(Math.round((t-B)*p)+B)+")";
        }
    });
})(jQuery);
