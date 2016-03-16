<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php if(($ts['site']['page_title'])  !=  ""): ?><?php echo ($ts['site']['page_title']); ?><?php else: ?><?php echo ($ts['site']['site_header_title']); ?><?php endif; ?></title>
    <link rel="shortcut icon" href="__THEME__/favicon.ico" />
    <meta name="keywords" content="<?php echo ($ts['site']['site_header_keywords']); ?>" />
    <meta name="description" content="<?php echo ($ts['site']['site_header_description']); ?>" />
    <meta property="qc:admins" content="2411042346635124521003636" />
    
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!--meta name="viewport" content="width=device-width, target-densityDpi=device-dpi, user-scalable=yes"-->
    <meta id="vp" name="viewport" content="width=device-width, initial-scale=0.4, minimum-scale=0.4, maximum-scale=2.0, user-scalable=yes" />
    
    <link rel="stylesheet" type="text/css" href="__THEME__/css/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="__THEME__/css/bootstrap-theme.min.css"/>
    <link rel="stylesheet" type="text/css" href="__THEME__/css/animate.min.css"/>
    <link rel="stylesheet" type="text/css" href="__THEME__/css/swiper.min.css"/>
    <link rel="stylesheet" type="text/css" href="__THEME__/css/style.css"/>
    <link rel="stylesheet" type="text/css" href="__THEME__/css/page.css"/>
    
    <!--[if lt IE 9]>
      <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="__PUBLIC__/js/zhiceyun/jquery-1.11.3.min.js"></script>
    <script type='text/javascript' src='__PUBLIC__/js/seajs/sea.js?nowrap'></script>
    <script type='text/javascript' src='__PUBLIC__/js/seajs/sea.config.js?'></script>
    <script src="__PUBLIC__/js/zhiceyun/bootstrap.min.js"></script>
<script type="text/javascript">
$(function() {
	//导航链接的样式
	var mainMenuOn=false;
	var subMenuOn=false;
	var ii=0;
	$(".dropdown>a").mouseover(function(){
		mainMenuOn=true;
		//console.log(mainMenuOn+" 1 "+subMenuOn)
		if ($(this).parent().is(".open")) {return;}
		$(this).dropdown("toggle");
		
	}).mouseleave(function(){
		mainMenuOn=false;
		var thisDropDownMenu=$(this);
		//console.log(mainMenuOn+" 2 "+subMenuOn)
		if (!$(this).parent().is(".open")) {return;}
		setTimeout(function(){
			if(mainMenuOn==false && subMenuOn==false){
				if (!thisDropDownMenu.parent().is(".open")) {return;}
				thisDropDownMenu.dropdown("toggle");
			}
		},100)
	}).click(function(){
		location.href=$(this).attr("href");
	})
	$(".dropdown-menu").mouseover(function(){
		subMenuOn=true;
		if ($(this).parent().is(".open")) {return;}
		$(this).prev().dropdown("toggle");
	}).mouseleave(function(){
		subMenuOn=false;
		var thisDropDownMenu=$(this);
		if (!$(this).parent().is(".open")) {return;}
		setTimeout(function(e){
			if(mainMenuOn==false && subMenuOn==false){
				if (!thisDropDownMenu.parent().is(".open")) {return;}
				thisDropDownMenu.parent().removeClass("open");
			}
		},100);
	});
})
</script>
    <script>
        var _UID_   = <?php echo (int) $uid; ?>;
        var _MID_   = <?php echo (int) $mid; ?>;
        var _ROOT_  = '__ROOT__';
        var _THEME_ = '__THEME__';
        var _PUBLIC_ = '__PUBLIC__';
        var _LANG_SET_ = '<?php echo LANG_SET; ?>';
        var $CONFIG = {};
            $CONFIG['uid'] = _UID_;
            $CONFIG['mid'] = _MID_;
            $CONFIG['root_path'] = _ROOT_;
            $CONFIG['theme_path'] = _THEME_;
            $CONFIG['public_path'] = _PUBLIC_;
            $CONFIG['lang'] =  '<?php echo LANG_SET; ?>';
    </script>
    
</head>
<style type="text/css">
.J_hide{display: none;}
#newinfo{color:#666;border:1px solid #25a3cb;padding:0 10px;border-radius:3px;margin-left:590px;margin-bottom:-30px;margin-top:8px;width:120px;display:none;}
</style>
<body>
<a href="<?php echo U('home/Index/index');?>" ></a>
<div class="navbar navbarBlue">
    <div class="head center">
        <div id="newinfo">您有<span class="blue">1</span>条新消息</div>
        <div class="login_after" style="clear:both">
            <?php if(isset($_SESSION["userInfo"])): ?><a  href="<?php echo U('home/Message/index');?>"><img src="__THEME__/images/loginAfter.png"/></a>
                <img class="newNote" style="display:none;" src="__THEME__/images/newNote.png"/>
                <div class="dropdown">
                    <a href="<?php echo U('home/Account/index');?>" data-role="button" data-toggle="dropdown" class="gray navbarBlue"><?php echo ($_SESSION['userInfo']['email']); ?></a>
                    <ul class="dropdown-menu">
                        <div class="topArrow" style="margin-top:-21px"><img src="__THEME__/images/dropdpwnTop.png"/></div>
                        <li><a href="<?php echo U('home/Account/feedback');?>">用户反馈</a></li>
                        <li><a href="<?php echo U('home/TestRecord/index');?>">测试记录</a></li>
                        <li><a href="<?php echo U('home/Public/logout');?>">退出</a></li>
                    </ul>
                </div>
                <img class="arrow" src="__THEME__/images/indexarrowdown.png"/>
                <span class="blue"><?php echo (getUserType($_SESSION['userInfo']['user_type'])); ?></span><?php endif; ?>
        </div>

        <div class="logo" style="clear:both"><a href="<?php echo U('home/Index/index');?>"><img src="__THEME__/images/logo.png"></a></div>
        <div class="nav">
            <div class="dropdown">
                <a href="<?php echo U('home/Apptest/index');?>" class="navbarBlue" data-role="button" data-toggle="dropdown">应用测试 <i class="glyphicon glyphicon-menu-down small"></i></a>
                <ul class="dropdown-menu">
                    <div class="topArrow"><img src="__THEME__/images/dropdpwnTop.png"/></div>
                    <li><a href="<?php echo U('home/Apptest/quickTest');?>">快速测试</a></li>
                    <li><a href="<?php echo U('home/Apptest/compatibleTest');?>">兼容性测试</a></li>
                    <li><a href="<?php echo U('home/Apptest/networkTest');?>">网络友好测试</a></li>
                    <li><a href="<?php echo U('home/Apptest/specialTest');?>">专机测试</a></li>
                    <li><a href="<?php echo U('home/Apptest/weakTest');?>">弱网络测试</a></li>
                    <li><a href="<?php echo U('home/Apptest/pressTest');?>">压力测试</a></li>
                    <li><a href="<?php echo U('home/Apptest/qhTest');?>">清华兼容性测试</a></li>
                </ul>
            </div>
            <a href="<?php echo U('home/Appinfo/experience');?>" class="navbarBlue">终端用户体验</a>
            <a href="<?php echo U('home/Appinfo/iot');?>" class="navbarBlue">IOT</a>
            <a href="<?php echo U('home/Appinfo/article');?>" class="navbarBlue">测试圈</a>
            <div class="dropdown">
                <a href="#" class="navbarBlue" data-role="button" data-toggle="dropdown">资源池 <i class="glyphicon glyphicon-menu-down small"></i></a>
                <ul class="dropdown-menu">
                    <div class="topArrow"><img src="__THEME__/images/dropdpwnTop.png"/></div>
                    <li><a href="<?php echo U('home/Appinfo/mobile');?>">终端库</a></li>
                    <li><a href="<?php echo U('home/Appinfo/internet_source');?>">模拟网资源</a></li>
                    <li><a href="<?php echo U('home/Appinfo/source_press');?>">压力测试资源</a></li>
                </ul>
            </div>
            <a href="<?php echo U('home/Public/about');?>" class="navbarBlue">关于我们</a>
            <a href="<?php echo U('home/Public/faq');?>" class="navbarBlue">FAQ</a>
            <?php if( !isset($_SESSION["userInfo"])): ?><a class="navbarBlue" href="<?php echo U('home/Public/login');?>">登录</a><?php endif; ?>
        </div>
    </div>
</div>

<div style="clear:both;height:90px;"></div>
<script>
        $(function(){
                //登录后账号上的下拉菜单补丁
                ///$(".dropdown7>a").attr("data-role","button").attr("data-toggle","dropdown");
                //导航链接的样式
                /*$(".nav>a").mouseover(function(){
                        $(this).css("border-bottom","1px solid #007eca");
                        $(this).css("background","none");
                }).mouseout(function(){
                        $(this).css("border-bottom","none");
                })
                $(".dropdown>a").mouseover(function(){
                        $(this).css("background","none");
                        if ($(this).parent().is(".open")) {return;}
                        $(this).dropdown("toggle");
                }).click(function(){
                        location.href=$(this).attr("href");
                })
                $(".dropdown-menu a").mouseover(function(){
                        $(this).css("background","none");
                        $(this).parent().css("background","#0f9bd8");
                }).mouseout(function(){
                        $(this).parent().css("background","none");
                })*/
                //按钮效果
                //var bgcolor="";
                //var textcolor="";
                $("button").mouseover(function(){
                        //bgcolor=$(this).css("background");
                        //textcolor=$(this).css("color");
                        //$(this).css("background","#79a31e").css("color","#fff");
                        $(this).css("opacity",0.6);
                }).mouseout(function(){
                        //$(this).css("background",bgcolor);
                        //$(this).css("color",textcolor);
                        $(this).css("opacity",1.0);
                });
        });
    $(function(){
        function setViewport(){
            var sca=360/1080;
            vp.content='width=device-width,initial-scale = '+sca+',minimum-scale='+sca+',user-scalable=yes';
        }
        setTimeout(function(){
                setViewport();
        },1000);
        //densityDpi= densityDpi>1?300*640*densityDpi/640:densityDpi;
        //<meta name="viewport" content="width=device-width, initial-scale=0.4, minimum-scale=0.4, maximum-scale=2.0, user-scalable=yes">
        //viewport.setAttribute('content', 'width=device-width, initial-scale=0.83, minimum-scale=0.83, maximum-scale=2.0, user-scalable=yes');
    });
</script>

<div class="login">
    <div class="center">   
        <form action="<?php echo U('home/Public/login');?>" method="post" id="submitForm">
            <div class="login_left"><img src="__THEME__/images/login_main_img.png"></div>
            <div class="login_right">
                <div>
                    <input class="white" type="text" placeholder="请输入邮箱账户" name="email" value="<?php echo ($cookie_email); ?>"/>
                    <div class="note" id="tip_email"><img src="__THEME__/images/reeor.png"/><p class="error">请输入邮箱账户</p></div>
                </div>
                <div>
                    <input class="white" type="password" placeholder="请输入密码" name="password" value=""/>
                    <div class="note" id="tip_password"><img src="__THEME__/images/reeor.png"/><p class="error">请输入密码</p></div>
                </div>
                <div class="nextTime">
                    <div class="white" style="height:15px;"><input style="margin-top:3px;" type="checkbox" name="remember" value="1"  checked="checked" ></div><span class="white">下次自动登录</span>
                    <div class="blue"><a href="<?php echo U('home/Public/sendPassword');?>">忘记密码？</a></div>
                </div>
                
                <div><button class="button blueBg regBtn" type="submit">登录</button></div>
                <div><button class="button greenBg" onclick="window.location.href='<?php echo U('home/Public/register');?>';return false;">快速注册</button></div>
                
                <p class="white">使用第三方账号登录</p>
                <div class="login_third">
                    <a href="<?php echo U('home/Public/tryOtherLogin', array('type'=>'qqnumber'));?>"><img src="__THEME__/images/login_QQ.png"/></a>
                    <a href="<?php echo U('home/Public/tryOtherLogin', array('type'=>'sina'));?>"><img src="__THEME__/images/login_xinlang.png"/></a>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
// 公共js
seajs.use(['widget', 'common'], function(Widget, Common){
    Widget.initWidgets();
    Common.init();
});

//<?php if ($mid){ ?>
//    function getNoreadMsg() {
//        var url = "<?php echo U('home/Message/getNoreadMsg');?>";
//        var obj = $('.newNote');
//        $.post(url, {}, function(data){
//            console.log(data);
//            if (data.count > 0) {
//                obj.show();
//                if (data.number > 0){
//                    //判断有新消息后，执行此段代码
//                    $('#newinfo').show().delay(3000).hide(0);
//                    $('#newinfo .blue').html(data.number);
//}
//            }else{
//                obj.hide();
//            }
//        },'json');
//    }
//    getNoreadMsg();
//    setInterval(getNoreadMsg, 15000);
//<?php } ?>
</script>
<a class="QQGroupBtn" target="ifm" href="http://jq.qq.com/?_wv=1027&k=SvQsir"><img src="__THEME__/images/QQGroup.png" /><br/>在<br/>线<br/>客<br/>服</a>
<iframe style="display:none" name="ifm" id="ifm"></iframe>
<div class="footer">
    <p class="center" data-position="fixed" data-role="footer"> 电话：<a href="#">010-62304633（2923）</a>/QQ：<a href="#">2059235439</a>/邮箱：<a href="#">service@smarterapps.cn</a>/合作：<a href="#">market@smarterapps.cn</a><br>Copyright by China Academy of Information & Communication Technology：京ICP备09013372号-10</p>
</div>
<script>
$(function(){
    setTimeout(function(){
        if (window.innerHeight) {
            winHeight = window.innerHeight;
        } else if ((document.body) && (document.body.clientHeight)) {
            winHeight = document.body.clientHeight;
        }
        var footFixHeight=winHeight-$(".footer").position().top-$(".footer").outerHeight();
        if(footFixHeight>0) {
            $(".footer").css("margin-top",footFixHeight+"px");
        }
    },10);
});
</script>
</body>
</html>
<script>
    window.DO_LOGIN_URL = "<?php echo U('home/Public/dologin');?>";
    seajs.use(['js/user/register.js'], function(register){
        register.formValid();
    });
    $(function () {
        //错误提示
//        $(".login_right").find("input[name]").parent().css("height", 73);
//        $(".login_right").find("input").on("click", function () {
//            $(this).next(".note").show();
//        }).mouseout(function () {
//            $(this).next(".note").hide();
//        });
        //第三方鼠标滑过效果	
        $(".login_third a").mouseover(function () {
            $($(this).find("img").attr("src", $(this).find("img").attr("src").replace(".png", "active.png")));
        }).mouseout(function () {
            $($(this).find("img").attr("src", $(this).find("img").attr("src").replace("active.png", ".png")));
        });
    });
</script>