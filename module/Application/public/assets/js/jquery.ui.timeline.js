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
 *      topOffset: vertical offset in pixels,
 *      leftOffset: left offset in pixels,
 *      rightOffset: right offset in pixels,
 *      eventHeight: height of an event in pixels,
 *      showOnlyRootCategories: boolean, if true, do not draw subcategories (default : true)
 * });
 * 
 * @author Jonathan Colson
 */

(function( $, undefined ) {
$.widget("epeires.timeline", {
    version: "0.0.1",
    /**
     * List of events
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
    eventsDiplayedPosition: [],
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
    intervalle: 0,
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
    timerUpdate:0,
    
    lastCategoriesComparator: undefined,
    lastEventComparator: undefined,
    lastFilter: undefined,
    /**
     * Is update of the view allowed ?
     */
    update: true,
    //paramètres pour les dessins
    params: {
        //espace entre les catégories
        catSpace: 5,
        //espace entre les évènements
        eventSpace: 5,
        //espace entre le haut et la barre horizontale
        topSpace: 50,
        //espace entre le haut et les heures
        topHourSpace: 20,
        //espace entre le haut et les demi-heures
        topHalfHourSpace: 30
    },
    //default options
    options: {
        eventUrl: "",
        categoriesUrl: "",
        topOffset: 0,
        leftOffset: 95,
        rightOffset: 40,
        eventHeight: 30,
        showCategories: true,
        showOnlyRootCategories: true,
        category: ""
    },
    //Main function
    //Initialize the timeline
    _create: function () {
        var self = this;
        var height = $(window).height() - this.options.topOffset+'px';
        this.element.css('height', height);
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
                if (self.options.showOnlyRootCategories){
                    return a.place - b.place;
                } else {
                    if(a.parent_id === -1 && b.parent_id !== -1){
                        if(b.parent_id === a.id){
                            return -1;
                        } else {
                            return a.place - b.parent_place;
                        }
                    } else if(a.parent_id !== -1 && b.parent_id === -1) {
                        if(a.parent_id === b.id){
                            return 1;
                        } else {
                            return a.parent_place - b.place;
                        }
                    } else if(a.parent_id === -1 && b.parent_id === -1){
                        return a.place - b.place;
                    } else if(a.parent_id !== -1 && b.parent_id !== -1){
                        if(a.parent_id === b.parent_id) {
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
                    function() {
                        //sort events by categories which will trigger events' drawing
                        self.sortEvents();
                        //update timebar every minute
                        setInterval(function(){self._updateTimebar();}, 60000);
                        //update events every 10s
                        setTimeout(function(){self._updateEvents();}, 10000);
                        //trigger event when init is finished
                        self._trigger("initComplete");
                    }
                );
        });
        
        //manage scrolling
        $(window).on('scroll', function (event) {
            var scrolltop = $(window).scrollTop();
            var offset = self.options.topOffset + 12;
            var diff = 0;
            if(scrolltop === 0) {
                //reinit positions and height
                $('.Time_obj.horiz_bar, #TimeBar').css('top', self.options.topOffset + self.params.topSpace + 'px');
                $("#TimeBar").css('height', self.element.height() - self.params.topSpace);
                $('.Time_obj.vert_bar').css({'height': self.element.height() - self.params.topSpace,
                        'top': self.options.topOffset + self.params.topSpace - 5 + 'px'});
                $('.Time_obj.halfhour').css('top', self.options.topOffset + self.params.topHalfHourSpace + 'px');
                $('.Time_obj.roundhour').css('top', self.options.topOffset + self.params.topHourSpace + 'px');
                self.prev_scroll = 0;
            } else { 
                if (scrolltop <= offset) {
                    diff = scrolltop - self.prev_scroll;
                    self.prev_scroll = scrolltop;
                } else {
                    diff = offset - self.prev_scroll;
                    self.prev_scroll = offset;
                }
                $('.Time_obj, #TimeBar').css('top', '-=' + diff + 'px');
                $('.Time_obj.vert_bar, #TimeBar').css('height', '+=' + diff + 'px');
            }
        });
        
        //retracé de la timeline en cas de changement de taille de fenêtre
	$(window).resize(function () {
            var height = $(window).height() - self.options.topOffset+'px';
            self.element.css('height', height);
            self._updateView();
	});
        
        //gestion des évènements souris
        this.element.on({
            mouseenter: function(){
                //affichage du tooltip
                var id = $(this).data('ident');
                var event = self.events[self.eventsPosition[id]];
                var text = "";
                $.each(event.fields, function(nom, contenu){
                    text += nom + " : " + contenu + "<br />";
                });
                $(this).tooltip({
                    title: '<span class="elmt_tooltip">'+text+'</span',
                    container: 'body',
                    html: 'true'
                }).tooltip('show');
                //affichage heure et boutons
                $(this).find('.disp').show();
                $(this).find('.lien.disp').hide();
            },
            mouseleave: function(){
                //suppression tooltip
                $(this).tooltip('destroy');
                //suppression heure et boutons
                $(this).find('.disp').hide();
                $(this).find('.lien.disp').show();
            }
        }, '.elmt');
    },
    _setOption: function (key, value){
        if(key === "showCategories"){
            if(this.options.showCategories !== value){
                this._super(key, value);
                if(this.update){
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
    addEvent: function (event) {
        //ne pas ajouter une évènement déjà existant
        if(event.id in this.eventsPosition && this.events[this.eventsPosition[event.id]]) {
            var old = this.events[this.eventsPosition[event.id]];
            event.display = old.display;
            event.shade = old.shade;
            this.events[this.eventsPosition[event.id]] = event;
            this.sortEvents();
        } else {
            //ajout de l'évènement en fin de tableau
            //la relance du tri forcera le dessin et la mise à jour des évènements
            var pos = this.events.length;
            event.display = true;
            event.shade = false;
            this.events.push(event);
            this.eventsPosition[pos] = event.id;
            this.sortEvents();
        }
    },
    /**
     * Add or modify multiple events at once
     * @param {type} eventsList
     * @returns {undefined}
     */
    addEvents: function(eventsList){
        var self = this;
        this.update = false;
        $.each(eventsList, function(key,value){
            self.addEvent(value);
        });
        this.update = true;
        this._updateView(false);
    },
    removeEvent: function (event) {
        this._hideEvent(event);
        this._trigger("hide", event, {eventId: event.id});
    },
    view: function (viewName, day) {
        if (viewName === "day" && !this.dayview) {
            this.dayview = true;
            this._updateView();
        } else if (viewName === "sixhours" && this.dayview) {
            this.dayview = false;
            if(day === undefined) {
                this.currentDay = new Date(); //now
            } else {
                var tempday = new Date(day);
                if(this._isValidDate(tempday)){
                    this.currentDay = tempday;
                } else {
                    this.currentDay = new Date();
                }
            }
            this._updateView(true);
        }
    },
    /**
     * Change the day, only avalaible if dayview == true
     * @param {type} date
     * @returns {undefined}
     */
    day: function (date) {
        var self = this;
        if(this.dayview){
            var tempday = new Date(date);
            if(this._isValidDate(tempday)){
                this.currentDay = tempday;
            } else {
                this.currentDay = new Date();
            }
            //on récupère les évènements
            this._pauseUpdate();
            this.element.find(".loading").show();
            $.when($.getJSON(self.options.eventUrl+'?day='+self.currentDay.toUTCString(), function(data){
                self.addEvents(data);
            })).then(function(){
                self._updateView();    
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
        //comparateur par défaut : catégorie racine, puis place dans la catégorie, puis date de début
        if (comparator === "default" || (comparator === undefined && this.lastEventComparator === undefined)) {
            this.events.sort(function (a, b) {
                if(self.catPositions[a.category_root_id] < self.catPositions[b.category_root_id]) {
                    return -1;
                } else if (self.catPositions[a.category_root_id] > self.catPositions[b.category_root_id]){
                    return 1;
                }
                if(a.category_place < b.category_place){
                    return -1;
                } else if(a.category_place > b.category_place) {
                    return 1;
                }
                var aStartdate = new Date(a.start_date);
                var bStartdate = new Date(b.start_date);
                if(aStartdate < bStartdate){
                    return -1;
                } else if (aStartdate > bStartdate){
                    return 1;
                }
                return 0;
            });
        } else if(comparator === undefined && this.lastEventComparator !== undefined){
            this.events.sort(this.lastEventComparator);
        } else {
            this.lastEventComparator = comparator;
            this.events.sort(this.lastEventComparator);
        }
        for(var i = 0; i < this.events.length; i++){
            this.eventsPosition[this.events[i].id] = i;
        }
        if(this.update){
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
        for(var i = 0; i < this.categories.length; i++){
            this.catPositions[this.categories[i].id] = i;
        }
    },
    filter: function (callback) {
        var cb = callback;
        if(cb === undefined && this.lastFilter === undefined){
            cb = function(event){return true;};
        } else if(cb === undefined && this.lastFilter !== undefined){
            cb = this.lastFilter;
        } else {
            this.lastFilter = cb;
        }
        this.events.forEach(function(element, index){
            var elt = element;
            elt.display = cb(elt);
            this[index] = elt;
        }, this.events);
    },
    /**
     * Pause updates of the view
     * Useful when many options are to be changed to avoid flicker
     */
    pauseUpdateView: function(){
        this.update = false;
    },
    /**
     * Force an update of the view or cancel pause
     */
    forceUpdateView: function(){
        this.update = true;
        this._updateView(true);
    },
    /* ********************** */
    /* *** Private methods ** */
    /* ********************** */

    /**
     * Switch between dayview and 6-hours view
     * @param full If true, redrawe base and timebar
     * @returns {undefined}
     */
    _updateView: function (full) {
        //update local var
        if (this.dayview) {
            this.timelineDuration = 24;
            var diff = this.currentDay.getTimezoneOffset()/(-60);
            this.timelineBegin = new Date(this.currentDay.getFullYear(), this.currentDay.getMonth(), this.currentDay.getDate(), diff, 0, 0);
            this.timelineEnd = new Date(this.timelineBegin.getFullYear(), this.timelineBegin.getMonth(), this.timelineBegin.getDate(),
            this.timelineBegin.getHours() + this.timelineDuration, 0, 0);
        } else {
            this.timelineDuration = 6;
            var now = new Date();
            this.timelineBegin = new Date(now.getFullYear(), now.getMonth(), now.getDate(), now.getHours() - 1, 0, 0);
            this.timelineEnd = new Date(now.getFullYear(), now.getMonth(), now.getDate(), now.getHours() - 1 + this.timelineDuration, 0, 0);
        }
        if(!this.update){
            return;
        }
        if(full !== false){
            // draw base
            this._drawBase();
            // draw timeBar
            this._drawTimeBar();
        }
        //update events
        //reapply filter and determine wich events are to be displayed
        this.filter();
        for(var i = 0; i < this.events.length ; i++){
            var evt = this.events[i];
            evt.display = evt.display && this._isEventInTimeline(evt);
            //ne pas afficher les évènements dont on n'a pas la catégorie
            evt.display = evt.display && (this.options.showOnlyRootCategories ? 
                                            evt.category_root_id in this.catPositions :
                                            evt.category_id in this.catPositions);
        }
        this.eventsDisplayed = this.events.filter(function(event){return event.display;});
        for(var i = 0; i < this.eventsDisplayed.length; i++){
            var id = this.eventsDisplayed[i].id;
            this.eventsDiplayedPosition[id] = i;
        }
        //for each event, update attributes
        for(var i = 0; i < this.events.length; i++){
            this._drawEvent(this.events[i]);
        }
        //then update y position
        this._updateYPosition(true);
        //draw categories
        if(this.options.showCategories){
            this._drawCategories();
        } else {
            this._hideCategories();
        }
    },
    _getCategory: function (id) {
        if(id in this.catPositions){
            return this.categories[this.catPositions[id]];
        }
        return null;
    },
    _getCategoryPosition: function(id){
        return this.catPositions[id];
    },
    _getEvent: function (id) {
        for(var i = 0; i < this.events.length; i++) {
            if(this.events[i].id === id) {
                return this.events[i];
            }
        }
        return null;
    },
    _getEventPosition: function(id){
        for(var i = 0; i < this.events.length; i++){
            if(this.events[i].id === id){
                return i;
            }
        }
    },
    /**
     * Calcule l'abscisse correspondant à une date
     * Retourne -1 si en dehors de la timeline
     * @param {type} date
     * @returns {undefined}
     */
    _computeX: function (date) {
        if (date < this.timelineBegin || date > this.timelineEnd) {
            return -1;
        } else {
            var delta;
            if (date.getUTCHours() >= this.timelineBegin.getUTCHours()) {
                delta = date.getUTCHours() - this.timelineBegin.getUTCHours();
            } else {
                delta = 24 + date.getUTCHours() - this.timelineBegin.getUTCHours();
            }
            return this.options.leftOffset + delta * this.intervalle * 2 + date.getMinutes() * this.intervalle * 2 / 60;
        }
    },
    /**
     * Calcule l'ordonnée correspondant à un évènement
     * @param {type} event Evènement
     * @param {array} eventsYPosition 
     * @returns {undefined}
     */
    _computeY: function (event, eventsYPosition) {
        var i = this.eventsDiplayedPosition[event.id];
        if(this.options.showCategories){
            var catPos = (this.options.showOnlyRootCategories ?
                                this._getCategoryPosition(event.category_root_id) :
                                this._getCategoryPosition(event.category_id) );
            var cat = this.categories[catPos];
            if(i === 0){
                //premier élément : somme des hauteurs min des cat précédentes (vides par conséquent)
                //                  + offset entre chaque catégorie (5px à rendre paramétrable)
                var top = this.params.topSpace + this.params.catSpace;
                for(var j = 0; j < catPos; j++){
                    top += this._getCategoryMinHeight(this.categories[j]) + this.params.catSpace;
                }
                return top;
            } else {
                var prevEvent = this.eventsDisplayed[i-1];
                var catOffset = 0;
                //deux cas selon si l'évènement appartient à la même catégorie que le précédent
                //si c'est le cas, il faut prendre en compte les possibles catégories vides précédentes
                //le calcul de la catégorie précédente est différent si on affiche les cat non racines ou pas
                if((this.options.showOnlyRootCategories && prevEvent.category_root_id !== event.category_root_id) ||
                    (!this.options.showOnlyRootCategories && prevEvent.category_id !== event.category_id)){
                    //calcul du bas de la catégorie non vide précédente
                    var prevCatPos = (this.options.showOnlyRootCategories ?
                                        this._getCategoryPosition(prevEvent.category_root_id) :
                                        this._getCategoryPosition(prevEvent.category_id) );
                    catOffset += this._getCategoryBottom(prevCatPos);
                    //si la cat précédente est celle juste avant : rien à faire
                    if(prevCatPos < catPos -1){
                        for(var j = prevCatPos + 1 ; j < catPos; j++){
                            catOffset += this._getCategoryMinHeight(this.categories[j]) + this.params.catSpace ;
                        }
                    }
                    return catOffset + this.params.catSpace;
                } else {
                    //l'évènement n'est pas le premier : soit on compacte soit on crée une nouvelle ligne
                    if(cat.compact){
                        
                    }
                    var topPrevEvent = this.eventsYPosition[prevEvent.id];
                    return topPrevEvent + this.options.eventHeight + this.params.eventSpace;
                }
            }
        } else {
            if(this.options.compact) {
                
            } else {
                //pas de compactage
                return this.params.topSpace + i*(this.options.eventHeight + this.params.eventSpace);
            }
        }
    },
    /**
     * Update vertical position of each displayed event
     * @param {boolean} Animate modification of position
     * @returns {undefined}
     */
    _updateYPosition: function(animate) {
        this.eventsYPosition = [];
        for(var i = 0; i < this.eventsDisplayed.length; i++){
            var y = this._computeY(this.eventsDisplayed[i]);
            this.eventsYPosition[this.eventsDisplayed[i].id] = y;
            var eventID = this.eventsDisplayed[i].id;
            var elmt = this.element.find('#event'+eventID);
            if(animate === true) {
                elmt.animate({top: y+'px'});
            } else {
                elmt.css('top', y+'px');
            }
        }
    },
    /**
     * Calcul le bas d'une catégorie en fonction des évènements réellement affichés
     * @param {type} i Place de la catégorie dans le tableau
     * @returns {undefined}
     */
    _getCategoryBottom: function(i){
        var cat = this.categories[i];
        var catEvents = (this.options.showOnlyRootCategories ? 
                            this.eventsDisplayed.filter(function(val){return val.category_root_id === cat.id;}) :
                            this.eventsDisplayed.filter(function(val){return val.category_id === cat.id;}));
        var top = 0;
        var bottom = 0;
        for(var j = 0; j < catEvents.length; j++){
            var topEvent = this.eventsYPosition[catEvents[j].id];
            if(top === 0 || topEvent < top) {
                top = topEvent;
            }
            if(topEvent > bottom){
                bottom = topEvent;
            }
        }
        var height = bottom - top + this.options.eventHeight;
        var minHeight = this._getCategoryMinHeight(cat);
        if(minHeight > height) {
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
    _getCategoryHeight: function(i){
        var cat = this.categories[i];
        var catEvents = (this.options.showOnlyRootCategories ? 
                            this.eventsDisplayed.filter(function(val){return val.category_root_id === cat.id;}) :
                            this.eventsDisplayed.filter(function(val){return val.category_id === cat.id;}));
        var top = 0;
        var bottom = 0;
        for(var j = 0; j < catEvents.length; j++){
            var topEvent = this.eventsYPosition[catEvents[j].id];
            if(top === 0 || topEvent < top) {
                top = topEvent;
            }
            if(topEvent > bottom){
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
        var largeurDisponible = this.element.width() - this.options.leftOffset - this.options.rightOffset;
        //if scrollbar visible, width is different
        //TODO : do it better
        if ($(document).height() > $(window).height()) {
            largeurDisponible += this._getScrollbarWidth();
        }
        this.intervalle = largeurDisponible / nbIntervalles;
    },
    /**
     * Dessine les heures et les barres verticales
     */
    _drawBase: function () {
        //erase previous elements
        $("#Time_obj").remove();
        $("#timeline-base").remove();
        
        this._computeIntervalle();
        //si la timeline est décalée, ajouter le décalage
        var left = parseInt(this.element.css('left'));
        if (isNaN(left)) {
            left = 0;
        }
        var h_temp = this.timelineBegin.getUTCHours();
        var time_obj = $('<div class="Time_obj horiz_bar"></div>');
        var base_elmt = $('<div id="timeline-base" class="Base"></div>');
        this.element.append(base_elmt);
        base_elmt.append(time_obj);
        time_obj.css({
            'top': this.options.topOffset + this.params.topSpace + 'px',
            'left': this.options.leftOffset + left + 'px',
            'width': this.intervalle * this.timelineDuration * 2 + 'px',
            'height': 1});
        for (var i = 0; i < this.timelineDuration * 2 + 1; i++) {
            var vert_bar = $('<div class="Time_obj vert_bar"></div>');
            base_elmt.append(vert_bar);
            vert_bar.css({
                'top': this.options.topOffset + this.params.topSpace - 5 + 'px',
                'left': this.intervalle * i + this.options.leftOffset + left + 'px',
                'width': 1,
                'height': this.element.height() - this.params.topSpace,
                'background-color': '#C0C0C0'});
            if (i % 2 == 1) {
                var halfHour = $('<div class="Time_obj halfhour">30</div>');
                base_elmt.append(halfHour);
                halfHour.css({
                    'top': this.options.topOffset + this.params.topHalfHourSpace + 'px',
                    'left': this.intervalle * i + this.options.leftOffset - 10 + left + 'px'});
            } else {
                var roundHour = $('<div class="Time_obj roundhour">' + this._formatNumberLength(h_temp, 2) + ':00</div>');
                base_elmt.append(roundHour);
                roundHour.css({
                    'top': this.options.topOffset + this.params.topHourSpace + 'px',
                    'left': this.intervalle * i + this.options.leftOffset - 20 + left + 'px'});
                if (h_temp === 23) {
                    h_temp = 0;
                } else {
                    h_temp++;
                }
            }
        }
    },
    _drawTimeBar: function () {
        if($('#TimeBar').length > 0 ){
            //Timebar exists : update
            this._updateTimebar();
        } else {
            var TimeBar = $('<div id="TimeBar"></div>');
            this.element.append(TimeBar);
            var x = this._computeX(new Date());
            TimeBar.css({
                'top': this.options.topOffset + this.params.topSpace + 'px',
                'height': this.element.height() - this.params.topSpace});
            if(x > 0){
                TimeBar.css('left', x+'px');
                TimeBar.show();
            } else {
                TimeBar.hide();
            }
        }
    },
    _updateTimebar: function(){
        var now = new Date();
        var diff = (now - this.timelineBegin)/(1000*60*60); //différence en heure
        //si vue six heures et diff > 2 heures : décaler d'une heure
        if(this.dayview === false && diff > 2){
            this._updateView();
        //si vue journée et affichage du jour en cours et changement de jour : afficher jour suivant
        } else if(this.dayview === true) {
            
        }
        var x = this._computeX(new Date());
        var timeBar = $('#TimeBar');
        if(x > 0){
            timeBar.css('left', x+'px');
            timeBar.show();
        } else {
            timeBar.hide();
        }
    },
    /**
     * Draw or update categories
     * Events have to be drawn before in order to compute size of categories
     */
    _drawCategories: function(){
        if($('#category').length === 0){
            this.element.append($('<div id="category"></div>'));
        } else {
            $('#category').show();
        }
        var y = this.params.topSpace + this.params.catSpace;
        for(var i = 0;i < this.categories.length; i++){
            var curCat = this.categories[i];
            var text_cat = "";
            for (var k = 0; k < curCat.short_name.length; k++) {
                text_cat += curCat.short_name[k]+'<br>';
            }
            var cat = $('#category'+curCat.id);
            if($('#category'+curCat.id).length === 0){
                var cat = $('<div class="category" id="category'+curCat.id+'" data-id="'+curCat.id+'">'+text_cat+'</div>');
                $('#category').append(cat);
                cat.css({'background-color':curCat.color,'height':'auto',
                        'left':'15px'}); 
            }
            var minHeight = this._getCategoryMinHeight(curCat);
            var height = this._getCategoryHeight(i);
            var trueHeight = (minHeight > height ? minHeight : height);
            cat.animate({'top': y+'px','height': trueHeight+'px'});
            y += trueHeight  + this.params.catSpace;
        }
    },
    _hideCategories: function(){
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
    _getCategoryMinHeight: function(category){
        return category.short_name.length * 20;
    },
    /**
     * Draw an event if necessary
     */
    _drawEvent: function(event){
        var cat = (this.options.showOnlyRootCategories ? 
                        this._getCategory(event.category_root_id) : 
                        this._getCategory(event.category_id));
        //si l'evt est affiché et que la catégorie existe
        if(event.display && cat !== null){
            //si oui, déterminer si il existe déjà ou non
            if(this.element.find('#event'+event.id).length === 0){
                //création de l'évènement
                var elmt = this._getSkeleton(event);
                //mise à jour des attributs
                this._doDrawEvent(event, elmt, cat);
                //ajout à la timeline
                this.element.append(elmt);
            } else {
                //mise à jour des attributs
                this._doDrawEvent(event, this.element.find('#event'+event.id), cat);
            }
        } else {
            //suppression de l'évènement
            this._hideEvent(event);
        }
    },
    /**
     * Surligne un évènement pour le mettre en valeur
     * @param {type} event
     * @param {boolean} highlight true to highlight, false to get back to normal
     * @returns {undefined}
     */
    _highlightEvent: function(event, highlight){
        
    },
    /**
     * 
     * @param {type} event
     * @param {boolean} shade true to shade an event, false to get back to normal
     * @returns {undefined}
     */
    _shadeEvent: function(event, shade){
        
    },
    /**
     * Hide an event if displayed
     * @param {type} event
     * @returns {undefined}
     */
    _hideEvent: function(event){
        var elmt = this.element.find('#event'+event.id);
        //destroy popups
        elmt.tooltip('destroy');
        //TODO : animate ?
        elmt.remove();
    },
    /**
     * Get new and modified events every 10 seconds
     * @returns {undefined}
     */
    _updateEvents: function () {
        var self = this;
        return $.getJSON(self.options.eventUrl + (self.lastupdate != 0 ? '?lastupdate=' + self.lastupdate.toUTCString() : ''),
                function (data, textStatus, jqHXR) {
                    if (jqHXR.status !== 304) {
                        self.addEvents(data);
                        self.lastupdate = new Date(jqHXR.getResponseHeader("Last-Modified"));
                    }
                }).always(function () {
                    self.timerUpdate = setTimeout(function(){self._updateEvents();}, 10000);
                });
    },
    _pauseUpdate: function() {
        clearTimeout(this.timerUpdate);
    },
    _restoreUpdate: function() {
        clearTimeout(this.timerUpdate);
        this._updateEvents();
    },
    /**
     * Updates all attributes of an event, except top
     * @param {type} event
     * @returns {undefined}
     */
    _doDrawEvent: function (event, elmt, categ) {
            var elmt_rect = elmt.find('.rect_elmt');
            var elmt_compl = elmt.find('.complement');
            var elmt_flecheG = elmt.find('.elmt_flecheG');
            var elmt_flecheD = elmt.find('.elmt_flecheD');
            var elmt_mod = elmt.find('.modify-evt');
            var elmt_check = elmt.find('.checklist-evt');
            var elmt_txt = elmt.find('.label_elmt');
            var elmt_deb = elmt.find('.elmt_deb');
            var elmt_fin = elmt.find('.elmt_fin');
            var move_deb = elmt.find('.move_deb');
            var move_fin = elmt.find('.move_fin');
            var lien = elmt.find('.lien');
            var couleur = categ.color;
            var startdate = new Date(event.start_date);
            var enddate;
            if (event.end_date != null) {
            	enddate = new Date(event.end_date);
            } else {
            	enddate = -1;
            }        
            
            ////// réini
            elmt.children().removeClass('disp');
            elmt_flecheG.hide();
            elmt_flecheD.hide();
            move_deb.hide();
            move_fin.hide();
            elmt_deb.hide();
            elmt_fin.hide();
            lien.hide();
            elmt_txt.css({'background-color':'','border-style':''});
            //////   
      
            // libellé de l'évènement à mettre à jour
			if (event.files > 0) {
				elmt_txt.find('span').html(event.name+' <a href="#"><span class="badge">P</span></a>');
			} else {
				elmt_txt.find('span').text(event.name);
			}
			var yDeb, yEnd, hDeb, hEnd;
			// ajout de l'heure de début
			var hDeb = this._formatNumberLength(startdate.getUTCHours(),2)+":"+this._formatNumberLength(startdate.getMinutes(),2);						
			var d_actuelle = new Date();
			if (startdate.getDate() != d_actuelle.getDate()){ 
				hDeb = '<span style="display: inline-block; vertical-align: middle;">'+this._formatNumberLength(startdate.getUTCDate(),2)+"/"+
						this._formatNumberLength(startdate.getUTCMonth()+1,2)+"<br/>"+hDeb+'</span>'; 
				yDeb = 0;
			} else {
				yDeb = 6;
			}
			elmt_deb.html(" "+hDeb);
			var data_deb = elmt_deb[0];
			jQuery.data(data_deb,"d_deb",startdate);
			elmt_deb.css({'top':yDeb+'px'});
			
			// ajout de l'heure de fin
			if (this._isValidDate(enddate)) {
				var hEnd = this._formatNumberLength(enddate.getUTCHours(),2)+":"+this._formatNumberLength(enddate.getMinutes(),2);
				if (enddate.getDate() != d_actuelle.getDate()){
					hEnd = '<span style="display: inline-block; vertical-align: middle;">'+this._formatNumberLength(enddate.getUTCDate(),2)+"/"+
					this._formatNumberLength(enddate.getUTCMonth()+1,2)+"<br/>"+hEnd+'</span>';
					yEnd = 0;
				} else {
					yEnd = 6;
				}
			} else { 
				yEnd = 4;
				hEnd = ""; 
			}
			elmt_fin.html(hEnd+" ");
			var data_fin = elmt_fin[0];
			jQuery.data(data_fin,"d_fin",enddate);
			elmt_fin.css({'top':yEnd+'px'});
			
			if (event.modifiable) {
				elmt_mod.addClass('disp');
	            elmt_check.addClass('disp');
			}
			
            var x = this._computeX(startdate);

            var x_end = -1;     
            //cas 1 : évènement ponctuel
            if (event.punctual) {
            	var haut = this.options.eventHeight * 2 / 3;
            	var larg = haut * 5 / 8;
            	elmt_rect.css({'position': 'absolute', 
            		'left': -larg + 'px', 
            		'width': 0, 
            		'height': 0, 
            		'border-left': larg + 'px solid transparent',
            		'border-right': larg + 'px solid transparent', 
            		'border-bottom': haut + 'px solid ' + couleur, 
            		'z-index': 1});
            	elmt_compl.css({'position': 'absolute', 'left': '0px', 'width': 0, 'height': 0, 'border-left': larg + 'px solid transparent',
            		'border-right': larg + 'px solid transparent', 'border-top': haut + 'px solid ' + couleur, 'margin': haut * 3 / 8 + 'px 0 0 -' + larg + 'px', 'z-index': 2});
            	elmt_rect.css({'left': '+=' + x});
            	elmt_compl.css({'left': x + 'px'});
            	elmt_deb.addClass('disp');
            } else {
            	elmt_deb.addClass('disp');
            	//cas 2 : date début antérieure au début de la timeline
            	if (startdate < this.timelineBegin) { 
            		x = this.options.leftOffset;
            		elmt_flecheG.show();
            		elmt_flecheG.css({'left': x-12+'px'});
            		elmt_fin.addClass('disp');
            	} else {
            		x = this._computeX(startdate);
            		move_deb.addClass('disp');
            		move_deb.css({'left': 8+'px', 'width':'2px'});
            		elmt_fin.addClass('disp');
            	}
            	//cas 3 : date fin postérieure à la fin de la timeline
            	if (enddate > this.timelineEnd) {
            		x_end = this._computeX(this.timelineEnd);
            		elmt_flecheD.show();
            		elmt_flecheD.css({'left': x_end+'px'});
            		elmt_fin.addClass('disp');
            	//cas 4 : date fin dans la timeline
            	} else if (enddate > 0) {
            		x_end = this._computeX(enddate);
            		move_fin.addClass('disp');
            		move_fin.css({'left': x_end-x-10+'px', 'width':'2px'});
            		elmt_fin.addClass('disp');
            	//cas 5 : pas de fin
            	} else {
            		x_end = this._computeX(this.timelineEnd);
            		move_fin.addClass('disp');
            		move_fin.css({'left': x_end-x-10+'px', 'width':'2px'});
            		var haut = this.options.eventHeight;
            		elmt_compl.css({'position':'absolute','left': x_end+4+'px' , 'width':0, 'height':0, 'border-left':haut+'px solid '+couleur, 
    					'border-top':haut/2+1+'px solid transparent', 'border-bottom': haut/2+1+'px solid transparent' });
            	}
            	elmt_rect.css({'position':'absolute', 'top':'0px', 'left': x+'px', 'width': (x_end-x)+'px', 'height':this.options.eventHeight ,'z-index' : 2, 
            		'background-color':couleur,'border-color':'transparent',  'border-width': '1px', 'border-radius': '5px'});
            }
            
            /// positionnement des heures de début, heure de fin, texte et trait éventuel associé
            var x1 = x;
            var x0 = x;
            var x2 = x_end;
			var txt_wid = elmt_txt.outerWidth()+60;

			var largeur = this._computeX(this.timelineEnd);
			if (event.punctual) {
				lien.addClass('disp');
				lien.show();
				x2 = x1+elmt_rect.outerWidth();
				x1 -= 30;
				// on place l'heure à droite
				elmt_deb.css({'left': x2+'px'});
				elmt_txt.css({'background-color':'white','border-style':'solid', 'border-color':'gray','border-width': '1px','border-radius': '0px', 'padding':'2px'});
				x2 += elmt_deb.outerWidth()+10;
				if (x2+txt_wid < largeur) { // s'il reste assez de place à droite du rectangle, on écrit le txt à droite
					elmt_txt.css({'left': x2+'px'});
					lien.css({'left': x2-(elmt_deb.outerWidth()+10)+'px','width':elmt_deb.outerWidth()+10+'px'});	
					x2 += txt_wid;
					// A transférer dans update status
					/*					if (elmt_fin.find('i').hasClass("icon-warning-sign")) { 
						lien.hide(); 
						lien.removeClass('disp');	
					}*/
				} else { // sinon on le met à gauche
					x1 -= txt_wid+2;
					elmt_txt.css({'left': x1+'px'});
					lien.css({'left': x1+'px','width':x0-x1+'px'});
				}
			} else {
				var lar_nec = txt_wid + 50;
				var x_left = x1+18;
				if (x_end-x > lar_nec) {
					elmt_txt.css({'left': x_left+2+'px'});
					// on place l'heure de début à gauche
					x1 -= elmt_deb.outerWidth()+20;
					elmt_deb.css({'left': x1+'px'});
					// on place l'heure de fin à droite
					elmt_fin.css({'left': x2+5+'px'});
					x2 += elmt_fin.outerWidth()+20;
				} else {
					lien.addClass('disp');
					lien.show();
					elmt_txt.css({'background-color':'white','border-style':'solid', 'border-color':'gray','border-width': '1px',
						'border-radius': '5px', 'padding':'2px'});
					// on place l'heure de début à gauche
					x1 -= elmt_deb.outerWidth();
					elmt_deb.css({'left': x1+'px'});
				//	if (bord_gauche) {
				//		elmt_deb.css({'background-color':'white','border-style':'solid', 'border-color':'gray','border-width': '1px',
				//			'border-radius': '5px', 'padding':'2px'});
				//	}
					// on place l'heure de fin à droite
					elmt_fin.css({'left': x2+5+'px'});
					x2 += elmt_fin.outerWidth()+20;
					if (x2+txt_wid < largeur) { // s'il reste assez de place à droite du rectangle, on écrit le txt à droite
					//	dr = 1;
						elmt_txt.css({'left': x2+'px'});
						lien.css({'left': x2-(elmt_fin.outerWidth()+20)+'px','width':elmt_fin.outerWidth()+20+'px'});
						// idem
/*						if (elmt_fin.find('i').hasClass("icon-warning-sign")) { 
							lien.hide(); 
							lien.removeClass('lien');	
						}*/
					} else { // sinon on le met à gauche
					//	dr = 0;
						lien.css({'left': x1-60+'px','width':x0-x1+60+'px'});
						x1 -= txt_wid+2;
						elmt_txt.css({'left': x1+'px'});
/*						if (elmt_deb.find('i').hasClass("icon-warning-sign")) { 
							lien.hide(); 
							lien.removeClass('lien');	
						}*/
					}
				} 
			}        
    },
    /**
     * Update an event according to its status and dates
     * @param {type} event
     * @returns {undefined}
     */
    _updateStatus: function(event){
            switch (event.status_id) {
                case 1: //nouveau
                    break;
                case 2: //confirmé
                    break;
                case 3: //terminé
                    break;
                case 4: //annulé
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
        elmt_rect.after(elmt_compl);
        // si l'événement a commencé avant la timeline, ajout d'une flèche gauche
        var elmt_flecheG = $('<div class="elmt_flecheG"></div>');
        elmt.append(elmt_flecheG);
        elmt_flecheG.append('<i class="icon-arrow-left"></i>');
        // si l'événement se poursuit au-delà de la timeline, ajout d'une flèche droite
        var elmt_flecheD = $('<div class="elmt_flecheD"></div>');
        elmt.append(elmt_flecheD);
        elmt_flecheD.append('<i class="icon-arrow-right"></i>');
        // ajout du nom de l'événement
        var elmt_txt = $('<p class="label_elmt"><span>' + event.name + '</span></p>');
        elmt.append(elmt_txt);
        // ajout du bouton "ouverture fiche"
        var elmt_b1 = $('<a href="#" class="modify-evt" data-id="' + event.id + '" data-name="' + event.name + '"></a>');
        elmt_txt.append(elmt_b1);
        elmt_b1.append('    <i class="icon-pencil"></i>');
        // ajout du bouton "ouverture fiche réflexe"
        var elmt_b2 = $('<a href="#" class="checklist-evt" data-id="' + event.id + '" data-name="' + event.name + '"></a>');
        elmt_txt.append(elmt_b2);
        elmt_b2.append('    <i class="icon-tasks"></i>');
        // lien entre le texte et l'événement (si texte écrit en dehors)
        var lien = $('<div class="lien"></div>');
        elmt.append(lien);
        var elmt_deb = $('<a href="#" class="elmt_deb"></a>');
        elmt.append(elmt_deb);
        var elmt_fin = $('<a href="#" class="elmt_fin"></a>');
        elmt.append(elmt_fin);
        var move_deb = $('<p class="move_deb"></p>');
        elmt_rect.append(move_deb);
        var move_fin = $('<p class="move_fin"></p>');
        elmt_rect.append(move_fin);
        var dy = this.options.eventHeight;
        var largeur = this.element.width();
        elmt.css({'position': 'absolute', 'left': '0px', 'width': largeur, 'height': dy});
        elmt_flecheG.css({'position': 'absolute', 'top': dy - 22 + 'px', 'left': '0px'});
        elmt_flecheD.css({'position': 'absolute', 'top': dy - 22 + 'px', 'left': '0px'});
        elmt_b1.css({'z-index': 1});
        elmt_b2.css({'z-index': 1});
        elmt_txt.css({'position': 'absolute', 'top': dy / 2 - 11 + 'px', 'left': '0px', 'z-index': 2, 'color':'black', 'white-space': 'nowrap', 'font-weight':'bold', 'width':'auto'});
        lien.css({'position': 'absolute', 'top': dy / 2 + 'px', 'left': '0px', 'width': '10px', 'height': '1px', 'background-color': 'gray', 'z-index': 1});
        
        move_deb.css({'height': dy - 8});
        move_fin.css({'height': dy - 8});
        move_deb.hover(function () {
            $(this).css({'cursor': 'e-resize'});
        });
        move_fin.hover(function () {
            $(this).css({'cursor': 'e-resize'});
        });
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
        if ( Object.prototype.toString.call(d) !== "[object Date]" )
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
        if(event.end_date !== null){
            enddate = new Date(event.end_date);
        }
        // si l'evt intersecte la timeline
        if ((event.punctual && startdate >= this.timelineBegin && startdate < this.timelineEnd) ||
            (!event.punctual && startdate <= this.timelineEnd && enddate === null) ||
            (!event.punctual && enddate !== null &&
                ((startdate >= this.timelineBegin && startdate <= this.timelineEnd) ||
                (enddate >= this.timelineBegin && enddate <= this.timelineEnd)))) {
            return true;
        } else {
            return false;
        }
    },
    
    _computeTextSize: function (){}
    
  });
})( jQuery );
