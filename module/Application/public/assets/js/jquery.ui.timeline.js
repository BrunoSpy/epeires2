/* 
 * Timeline - JQuery UI Widget
 * 
 * Usage :
 * $('element').timeline({
 *      eventUrl: "url to get events",
 *      categoriesUrl: "url to get categories"
 * });
 * 
 * @author Jonathan Colson
 */

$.widget("epeires.timeline",{
    
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
     * Current view
     */
    dayview: false,
    
    //default options
    options: {
        eventUrl: "",
        categoriesUrl: "",
        topOffset: 0
    },
    
    //Main function
    //Initialize the timeline
    _create: function() {
        var self = this;
        $.when(
            $.getJSON(this.options.categoriesUrl, function(data){
                $.each(data, function(key, value){
                    self.categories.push(value);
                });
            }),
            $.getJSON(this.options.eventUrl, function(data){
                $.each(data, function(key, value){
                    self.events.push(value);
                });
            })
        ).then(function(){
            //unable user to select objects
            self._setUnselectable();
            //sort categories by name
            self.sortCategories();
            //sort events by categories
            self.sortEvents();
            //create ours line and timebar
            self.creatHours();
            //draw events
            self.applyChanges();
            //trigger event when init is finished
            self._trigger("initComplete");});
    },
    
    /* ********************** */
    /* *** Public methods *** */
    /* ********************** */
    
    /*
     * 
     * @param {type} event Object
     */
    addEvt: function(event){
        
    },
    
    modifyEvent: function(event){
        
    },
    
    /*
     * Sort events
     * @param {type} comparator callback 
     * @returns {undefined}
     */
    sortEvents: function(comparator){
        if(typeof comparator === "undefined"){
            
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
    sortCategories: function(comparator){
        if(typeof comparator === "undefined"){
            this.categories.sort(function(a, b){
                return a.name > b.name;
            });
        } else {
            this.categories.sort(comparator);
        }
    },
    
    filter: function(){
        
    },
    
    view: function(viewName){
        if(viewName === "day" && !this.dayview){
            this.dayview = true;
            this._changeView();
        } else if(viewName === "sixhours" && this.dayview){
            this.dayview = false;
            this._changeView();
        }
        
    },
    
    /* ********************** */
    /* *** Private methods ** */
    /* ********************** */
    
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
    
    _createTimeline: function() {
        this.element.css('height', $(window).height()-this.options.topOffset+'px');
        
    },
    
    /**
     * Draw new events and update positions
     * @returns {undefined}
     */
    _applyChanges: function() {
        
    },
    
    /**
     * Switch between dayview and 6-hours view
     * @returns {undefined}
     */
    _changeView: function() {
        
    }
});
