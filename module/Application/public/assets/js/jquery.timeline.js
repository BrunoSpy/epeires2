/**
 * @author Jonathan Colson
 */
(function($, window, document, undefined) {

    /**
     * The plugin constructor
     * @param {DOM Element} element The DOM element where plugin is applied
     * @param {Object} options Options passed to the constructor
     */
    function Plugin(element, options) {

        // Store a reference to the source element
        this.el = element;

        // Store a jQuery reference to the source element
        this.$el = $(element);

        // Set the instance options extending the plugin defaults and
        // the options passed by the user
        this.options = $.extend({}, $.fn.timeline.defaults, options);

        // Initialize the plugin instance
        this.init();
    }

    /**
     * Set up your Plugin protptype with desired methods.
     * It is a good practice to implement 'init' and 'destroy' methods.
     */
    Plugin.prototype = {
        /**
         * Initialize the plugin instance.
         * Set any other attribtes, store any other element reference, register
         * listeners, etc
         *
         * When bind listerners remember to name tag it with your plugin's name.
         * Elements can have more than one listener attached to the same event
         * so you need to tag it to unbind the appropriate listener on destroy:
         *
         * @example
         * this.$someSubElement.on('click.' + pluginName, function() {
         * // Do something
         * });
         *
         */
        init: function() {
            this.impt_name = new Array();
            this.impt_style = new Array();
            this.impt_value = new Array();
            setImpacts.call(this);

        },
        /**
         * The 'destroy' method is were you free the resources used by your plugin:
         * references, unregister listeners, etc.
         *
         * Remember to unbind for your event:
         *
         * @example
         * this.$someSubElement.off('.' + pluginName);
         *
         * Above example will remove any listener from your plugin for on the given
         * element.
         */
        destroy: function() {

            // Remove any attached data from your plugin
            this.$el.removeData();
        },
        /**
         * Change filter
         *
         * @example
         * $('#element').timeline('changeFilter','filter');
         *
         * @param {string} 'showAll', 'hideArchived'
         * @return {[type]}
         */
        changeFilter: function(filter) {

            // This is a call to a pseudo private method
            this._pseudoPrivateMethod();

            // This is a call to a real private method. You need to use 'call' or 'apply'
            privateMethod.call(this);
        },
        
        /**
         * Another public method which acts as a getter method. You can call as any usual
         * public method:
         *
         * @example
         * $('#element').jqueryPlugin('someGetterMethod');
         *
         * to get some interesting info from your plugin.
         *
         * @return {[type]} Return something
         */
        someGetterMethod: function() {

        },
        /**
         * You can use the name convention functions started with underscore are
         * private. Really calls to functions starting with underscore are
         * filtered, for example:
         *
         * @example
         * $('#element').jqueryPlugin('_pseudoPrivateMethod'); // Will not work
         */
        _pseudoPrivateMethod: function() {

        }
    };

    /* *********************** */
    /* *** Private methods *** */
    /* *********************** */
    var setImpacts = function() {
        console.log('getImpacts');
        var i = 0;
        return $.getJSON(this.options.url + "/getimpacts", function(data) {
            $.each(data, function(key, value) {
                this.impt_name[i] = value.name;
                this.impt_style[i] = value.short_name;
                this.impt_value[i] = value.color;
                i++;
            });
        });
    };


    /**
     * Usage :
     * $('#element').timeline({
     * defaultOption: 'this options overrides a default plugin option',
     * additionalOption: 'this is a new option'
     * });
     */
    $.fn.timeline = function(options) {
        var args = arguments;

        if (options === undefined || typeof options === 'object') {
            // Creates a new plugin instance, for each selected element, and
            // stores a reference withint the element's data
            return this.each(function() {
                if (!$.data(this, 'plugin_' + timeline)) {
                    $.data(this, 'plugin_' + timeline, new Plugin(this, options));
                }
            });
        } else if (typeof options === 'string' && options[0] !== '_' && options !== 'init') {
            // Call a public pluguin method (not starting with an underscore) for each
            // selected element.
            if (Array.prototype.slice.call(args, 1).length == 0 && $.inArray(options, $.fn.timeline.getters) != -1) {
                // If the user does not pass any arguments and the method allows to
                // work as a getter then break the chainability so we can return a value
                // instead the element reference.
                var instance = $.data(this[0], 'plugin_' + timeline);
                return instance[options].apply(instance, Array.prototype.slice.call(args, 1));
            } else {
                // Invoke the speficied method on each selected element
                return this.each(function() {
                    var instance = $.data(this, 'plugin_' + timeline);
                    if (instance instanceof Plugin && typeof instance[options] === 'function') {
                        instance[options].apply(instance, Array.prototype.slice.call(args, 1));
                    }
                });
            }
        }
    };

    /**
     * Names of the plugin methods that can act as a getter method.
     * @type {Array}
     */
    $.fn.timeline.getters = ['someGetterMethod'];

    /**
     * Default options
     */
    $.fn.timeline.defaults = {
        url: ''
    };

})(jQuery, window, document);