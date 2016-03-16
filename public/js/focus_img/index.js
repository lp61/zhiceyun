define(function(require, exports, module) {
    var $ = require('jquery');
    var focusImg = require('js/focus_img/focusImg.js');
    exports.init = function() {
        var domEle = {
            navhover: $('.nav-main a'),
            detBtn: $('.details'),
            maxImg: $('.news-img'),
            fnLi: $('.ft-list li'),
            animat: 'animation',
            watchLb: $('#watch-lb'),
            code: $('.watch-code'),
            downBtn: $('.beta-info a'),
            downlaodMain: $('.downlaod-main'),
            windowMain: $(window),
            bodyEle: $('body'),
            stopAnimte: $('.slide,.prev,.next,.item'),
            prev: $('.prev'),
            next: $('.next'),
            slide: $('.slide'),
            slideCur: $('.item a'),
            phoneImg: $('.phone-img'),
            codeImg: $('.code-img')
        };
        domEle.downlaodMain.show();
        focusImg.init(domEle);
    };
});
