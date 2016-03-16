define(function(require, exports, module){
    var Swipe = require('swipe');

    exports.init = function() {
        var slider = $('#scroll_img').Swipe({
            auto: 3000,
            continuous: true,
            pwidth:320, // 420
            pheight:210, //450
            callback: function(pos) {
                $('#scroll_position i').attr('class', ' ');
                $('#scroll_position i').eq(pos).attr('class', 'active');
            }
        });
        
        $.each($('#scroll_img .scroll_wrap li'), function(index, val) {
            var aclass = index == 0 ? 'active':'';
            $('#scroll_position').append('<i class="'+aclass+'"></i>');
        });
    }
});