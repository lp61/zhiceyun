define(function(require, exports, module){
    var $        = require('jquery');
    var Widget   = require('widget');
    
    exports.formValid = function() {
        $(function(){
            exports.checkboxCon();
            function showError(id,tip) {
                $('#'+id+' p').text(tip).parent().show();
            }
            function hideError(id){
                $('#'+id+' p').text('').parent().hide();
            }

            $("#submitForm .regBtn").click(function(){
                var submitForm = $('#submitForm'),
                    email    = submitForm.find('input[name=email]'),
                    password = submitForm.find('input[name=password]'),
                    remember = submitForm.find('input[name=remember]'),
                    emailStr = $.trim(email.val()),
                    passwordStr = $.trim(password.val()),
                    rememberStr = remember.val();
                if (email.val() == email.attr("placeholder") || !emailStr) {
                    showError('tip_email','请输入邮箱账户！');
                    return false;
                }
                if (password.val() == password.attr("placeholder") || !passwordStr) {
                    showError('tip_password','请输入密码！');
                    return false;
                }
                console.log(emailStr, passwordStr, rememberStr);
                $.post(DO_LOGIN_URL, {email:emailStr, password:passwordStr, remember:rememberStr}, function(data) {
                    if (data.status === 0) {
                        showError('tip_password',data.info);
                    }else{
                        $('#submitForm').submit();
                    }
                },'json');
                return false;
            });
        });
    };
	
    exports.checkboxCon = function(){
        $(function(){
            $(".checkbox, .checkboxlabel").unbind().click(function(){
                var checkboxCon    = $('.checkbox');
                var checkboxConBtn = checkboxCon.find('input[type="checkbox"]');
                if (checkboxConBtn.val() == 1) {
                    checkboxConBtn.val(0);
                    checkboxCon.css("background-position","18px");
                }else{
                    checkboxCon.css("background-position","0");
                    checkboxConBtn.val(1);
                }
            });
        });
    };
    // 普通注册刷新登录验证码图片 wight的两种用法
    // exports.refleshRegImg = function(config) {
    //     var $el      = config.$el;
    //     var $reflesh = config.$reflesh;

    //     $reflesh.on('click',function(event) {
    //         var img = $('#img_checkcode').size() > 0 ? $('#img_checkcode') : null;
    //         if (img != null) {
    //             img.attr('src', window.CK_CODE_URL+'&nocache=' + (new Date() * 1));
    //             $('#checkcode_input').val('');
    //         }
    //         return false;
    //     });
    //     $reflesh.trigger('click');
    // }
    exports.refleshRegImg = Widget.define({
        events : {
            "click [data-role=reflesh]" : "replace"
        },
        init : function(config) {
            this.replace();
        },
        replace : function() {
            var img = $('#verifyimg').size() > 0 ? $('#verifyimg') : null;
            if (img !== null) {
                img.attr('src', _PUBLIC_ +'/checkcode.php?nocache=' + (new Date() * 1));
                $('#verify').val('');
            }
            return false;
        }
    });
});