define(function(require, exports, module){
    var $ = require('jquery');

    var Widget = exports;
    var WidgetMap = {};
    var WidgetCounter = 0;

    function guid () {
        var id = 'widget' + (++WidgetCounter);
        WidgetMap[id] = {
            defer: $.Deferred()
        };
        return id;
    }

    Widget.ready = function (el, cb) {
        var $el = $(el);
        var id = $el.data('widget-id');

        if (!id) {
            id = guid();
            $el.data('widget-id', id);
        }

        WidgetMap[id].defer.done(cb);
    };

    Widget.initWidgets = function () {
        $('[data-widget]').each(function () {
            exports.initWidget($(this));
        });
    };

    Widget.initWidget = function (el) {
        var $el = $(el);
        var config = $el.data();
        var roles = {};
        var widget = config.widget.split('#');
        var id = $el.data('widget-id');

        if (!id) {
            id = guid();
            $el.data('widget-id', id);
        }

        config.$el = $el;
        // 自动收集元素, 例如: config.$btn
        $el.find('[data-role]').each(function () {
            var role = $(this).data('role');

            if (!roles[role]) {
                roles[role] = [];
            }
            roles[role].push(this);
        });

        $.each(roles, function (key, role) {
            config['$'+key] = $(role);
        });

        seajs.use(widget[0], function (Fn) {
            var instance;
            if (widget[1]) {
                Fn = Fn[widget[1]];
            }

            instance = new Fn(config);

            WidgetMap[id].instance = instance;
            WidgetMap[id].defer.resolve();
        });
    };

    Widget.define = function (def) {
        def = def || {};
        def.events = def.events || {};
        def.init = def.init || function (config) {
            this.config = config;
        };

        return function (config) {
            var self = $.extend({}, def);
            var events = $.extend({}, self.events);

            if (config.$el) {
                self.$el = $(config.$el);
                $.each(events, function (key, cb) {
                    var index = key.indexOf(' ');
                    var event = index === -1 ? key : key.substr(0, index);
                    var $el   = index === -1 ? '' : key.substr(index, key.length - 1);

                    if (typeof cb === 'function') {
                        cb = $.proxy(cb, self);
                    } else {
                        cb = $.proxy(self[cb], self);
                    }

                    if ($el) {
                        self.$el.on(event, $el, cb);
                    } else {
                        self.$el.on(event, cb);
                    }
                });
            }

            self.init(config);

            return self;
        };
    };
});