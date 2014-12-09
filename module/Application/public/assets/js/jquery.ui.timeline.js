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

$.widget("epeires.timeline", {
    version: "0.0.1",
    /**
     * List of events
     */
    events: [],
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
    dayview: true,
    /**
     * Display or not root categories
     */
    showCategories: true,
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
                            $.each(data, function (key, value) {
                                self.events.push(value);
                            });
                        }
                })).then(
                    function() {
                        //sort events by categories
                        self.sortEvents();
                        // ini params
                        self.view("sixhours");
                        //update timebar every minute
                        setInterval(function(){self._updateTimebar();}, 60000);
                        //trigger event when init is finished
                        self._trigger("initComplete")
                    ;}
            );
        });
        
        //manage scrolling
        $(window).on('scroll', function (event) {
            var scrolltop = $(window).scrollTop();
            var offset = self.options.topOffset + 12;
            if(scrolltop === 0) {
                //reinit positions and height
                $('.Time_obj.horiz_bar, #TimeBar').css('top', self.options.topOffset + self.params.topSpace + 'px');
                $("#TimeBar").css('height', self.element.height() - self.params.topSpace);
                $('.Time_obj.vert_bar').css({'height': self.element.height() - self.params.topSpace,
                        'top': self.options.topOffset + self.params.topSpace - 5 + 'px'});
                $('.Time_obj.halfhour').css('top', self.options.topOffset + self.params.topHalfHourSpace + 'px');
                $('.Time_obj.roundhour').css('top', self.options.topOffset + self.params.topHourSpace + 'px');
            } else if (scrolltop <= offset) {
                var diff = scrolltop - self.prev_scroll;
                $('.Time_obj, #TimeBar').css('top', '-=' + diff + 'px');
                $('.Time_obj.vert_bar, #TimeBar').css('height', '+=' + diff + 'px');
            } else {
                var diff = offset - self.prev_scroll;
                $('.Time_obj, #TimeBar').css('top', '-=' + diff + 'px');
            }
            self.prev_scroll = scrolltop;
        });
        
        //retracé de la timeline en cas de changement de taille de fenêtre
	$(window).resize(function () {
            var height = $(window).height() - self.options.topOffset+'px';
            self.element.css('height', height);
            self._changeView();
	});
    },
    /* ********************** */
    /* *** Public methods *** */
    /* ********************** */

    /*
     * 
     * @param {type} event Object
     */
    addEvent: function (event) {

    },
    modifyEvent: function (event) {

    },
    removeEvent: function (event) {
        this._eraseEvent(event);
        this._trigger("erase", event, {eventId: event.id});
    },
    view: function (viewName) {
        if (viewName == "day" && !this.dayview) {
            this.dayview = true;
            this._changeView();
        } else if (viewName == "sixhours" && this.dayview) {
            this.dayview = false;
            this._changeView();
        }
    },
    /**
     * Change the day, only avalaible if dayview == true
     * @param {type} date
     * @returns {undefined}
     */
    day: function (date) {
        if(this.dayview){
            
        }
    },
    /*
     * Sort events
     * @param {type} comparator callback 
     * @returns {undefined}
     */
    sortEvents: function (comparator) {
        var self = this;
        //comparateur par défaut : catégorie racine, puis place dans la catégorie, puis date de début
        if (typeof comparator === "undefined") {
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
        } else {
            this.events.sort(comparator);
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
    filter: function () {

    },
    /* ********************** */
    /* *** Private methods ** */
    /* ********************** */

    /**
     * Switch between dayview and 6-hours view
     * @returns {undefined}
     */
    _changeView: function () {
        //update local var
        if (this.dayview) {
            this.timelineDuration = 24;
            var now = new Date();
            //diff between utc and local time
            //TODO
            var diff = 2;
            this.timelineBegin = new Date(now.getFullYear(), now.getMonth(), now.getDate(), diff, 0, 0);
            this.timelineEnd = new Date(this.timelineBegin.getFullYear(), this.timelineBegin.getMonth(), this.timelineBegin.getDate(),
            this.timelineBegin.getHours() + this.timelineDuration, 0, 0);
        } else {
            this.timelineDuration = 6;
            var now = new Date();
            this.timelineBegin = new Date(now.getFullYear(), now.getMonth(), now.getDate(), now.getHours() - 1, 0, 0);
            this.timelineEnd = new Date(now.getFullYear(), now.getMonth(), now.getDate(), now.getHours() - 1 + this.timelineDuration, 0, 0);
        }
        // draw base
        this._drawBase();
        // draw timeBar
        this._drawTimeBar();
        //update events
        for(var i = 0; i < this.events.length; i++){
            this._drawEvent(this.events[i]);
        }
        //draw categories
        if(this.showCategories){
            this._drawCategories();
        }
    },
    _getCategory: function (id) {
        for (var i = 0; i < this.categories.length; i++) {
            if (this.categories[i].id === id) {
                return this.categories[i];
            }
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
     * Calcule l'ordonnée correspondant à un évènement à la place i dans le tableau
     * @param {type} i Position de l'évènement dans le tableau
     * @returns {undefined}
     */
    _computeY: function (i) {
        var event = this.events[i];
        if(this.showCategories){
            var catPos = this._getCategoryPosition(event.category_root_id);
            var cat = this.categories[catPos];
            if(i === 0){
                //premier élément : somme des hauteurs min des cat précédentes (vides par conséquent)
                //                  + offset entre chaque catégorie (5px à rendre paramétrable)
                var top = this.params.topSpace + this.params.catSpace;
                for(var j = 0; j < catPos; j++){
                    top += this._getCategoryMinHeight(cat) + this.params.catSpace;
                }
                return top;
            } else {
                var prevEvent = this.events[i-1];
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
                    var topPrevEvent = parseInt($('#event'+prevEvent.id).css('top'), 10);
                    return topPrevEvent + this.options.eventHeight + this.params.eventSpace;
                }
            }
        } else {
            //les catégories ne sont pas affichées
            return 150;
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
                            this.events.filter(function(val){return val.category_root_id === cat.id;}) :
                            this.events.filter(function(val){return val.category_id === cat.id;}));
        var top = this.element.height();
        var bottom = 0;
        for(var j = 0; j < catEvents.length; j++){
            var topEvent = parseInt($('#event'+catEvents[j].id).css('top'),10);
            if(topEvent < top) {
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
                            this.events.filter(function(val){return val.category_root_id === cat.id;}) :
                            this.events.filter(function(val){return val.category_id === cat.id;}));
        var top = this.element.height();
        var bottom = 0;
        for(var j = 0; j < catEvents.length; j++){
            var topEvent = parseInt($('#event'+catEvents[j].id).css('top'),10);
            if(topEvent < top) {
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
            TimeBar.css({
                'top': this.options.topOffset + this.params.topSpace + 'px',
                'left': this._computeX(new Date()) + 'px',
                'height': this.element.height() - this.params.topSpace});
        }
    },
    _updateTimebar: function(){
        var now = new Date();
        var diff = now - this.timelineBegin;
        //si vue six heures et diff > 2 heures : décaler d'une heure
        //calcul faux, tenir compte du changement de jour
        if(this.dayview === false && (now.getUTCHours() - this.timelineBegin.getUTCHours() > 1)){
            this._changeView();
        //TODO si vue journée et affichage du jour en cours et changement de jour : afficher jour suivant
        } else if(true) {
            
        }
        var x = this._computeX(new Date());
        $('#TimeBar').css('left', x+'px');
    },
    /**
     * Draw or update categories
     * Events have to be drawn before in order to compute size of categories
     */
    _drawCategories: function(){
        if($('#category').length === 0){
            this.element.append($('<div id="category"></div>'));
        }
        var y = this.params.topSpace + this.params.catSpace;
        for(var i = 0;i < this.categories.length; i++){
            var curCat = this.categories[i];
            var text_cat = "";
            for (var k = 0; k < curCat.short_name.length; k++) {
                text_cat += curCat.short_name[k]+'<br>';
            }
            if($('#category'+curCat.id).length === 0){
                var cat = $('<div class="category" id="cat'+curCat.id+'" data-id="'+curCat.id+'">'+text_cat+'</div>');
                $('#category').append(cat);
                cat.css({'background-color':curCat.color,'height':'auto',
                        'left':'15px',
                        'top': y+'px'}); 
                var minHeight = this._getCategoryMinHeight(curCat);
                var height = this._getCategoryHeight(i);
                var trueHeight = (minHeight > height ? minHeight : height);
                y += trueHeight  + this.params.catSpace;
                cat.css('height', trueHeight+'px');
            }
        }
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
        var startdate = new Date(event.start_date);
        var enddate = null;
        if(event.end_date !== null){
            enddate = new Date(event.end_date);
        }
        //détermine si l'evt est dans la période affichée
        if((event.punctual && startdate >= this.timelineBegin && startdate < this.timelineEnd) ||
            (!event.punctual && startdate <= this.timelineBegin && enddate === null) ||
            (!event.punctual && enddate !== null && 
                ((startdate >= this.timelineBegin && startdate <= this.timelineEnd) ||
                 (startdate <= this.timelineBegin && enddate >= this.timelineBegin)))){
            //si oui, déterminer si il existe déjà ou non
            if(this.element.find('#event'+event.id).length > 0){
                this._modifyEvent(event);
            } else {
                this._doDrawEvent(event);
            }
        } else {
            //suppression de l'évènement
            this._eraseEvent(event);
        }
    },
    
    /**
     * Erase an event if displayed
     * @param {type} event
     * @returns {undefined}
     */
    _eraseEvent: function(event){
        //TODO : destroy popups
        //TODO : animate ?
        this.element.find('#event'+event.id).remove();
    },
    /**
     * Modify an event already displayed
     * @param {type} event
     * @returns {undefined}
     */
    _modifyEvent: function(event){
        
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
                        $.each(data, function(key, value){
                            var tempEvents = self.events.filter(function(val){
                                return val.id == key;
                            });
                            if(tempEvents.length === 0){
                                self.events.push(value);
                            }
                            self._drawEvent(value);
                        });
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
     * Draw an event for the first time
     * @param {type} event
     * @returns {undefined}
     */
    _doDrawEvent: function (event) {
        var categ = (this.options.showOnlyRootCategories ? 
                        this._getCategory(event.category_root_id) : 
                        this._getCategory(event.category_id));
        if(categ !== null) { //if no category : do not draw this event
            var elmt = this._getSkeleton(event);
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
            var lien = elmt.find('.no_lien');
            var couleur = categ.color;
            var startdate = new Date(event.start_date);
            var x = this._computeX(startdate);
            var y = this._computeY(this._getEventPosition(event.id));
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
            //cas 2 : date antèrieure au début de la timeline
            } else {
                if (startdate < this.timelineBegin) {

                }
            }
            elmt.css('top', y+'px');
            //ajout à la timeline
            this.element.append(elmt);
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
        var elmt_txt = $('<p class="label_elmt">' + event.name + '</p>');
        elmt.append(elmt_txt);
        // ajout du bouton "ouverture fiche"
        var elmt_b1 = $('<a href="#" class="modify-evt data-id="' + event.id + '"data-name="' + event.name + '"></a>');
        elmt_txt.append(elmt_b1);
        elmt_b1.append('    <i class="icon-pencil"></i>');
        // ajout du bouton "ouverture fiche réflexe"
        var elmt_b2 = $('<a href="#" class="checklist-evt" data-id="' + event.id + '"data-name="' + event.name + '"></a>');
        elmt_txt.append(elmt_b2);
        elmt_b2.append('    <i class="icon-tasks"></i>');
        // lien entre le texte et l'événement (si texte écrit en dehors)
        var lien = $('<div class="no_lien"></div>');
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
        var largeur = 300;
        elmt.css({'position': 'absolute', 'left': '0px', 'width': largeur, 'height': dy});
        elmt_flecheG.css({'position': 'absolute', 'top': dy - 22 + 'px', 'left': '0px'});
        elmt_flecheD.css({'position': 'absolute', 'top': dy - 22 + 'px', 'left': '0px'});
        elmt_b1.css({'z-index': 1});
        elmt_b2.css({'z-index': 1});
        elmt_txt.css({'position': 'absolute', 'top': dy / 2 - 11 + 'px', 'left': '0px', 'font-weight': 'normal', 'z-index': 2});
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
    }
});
