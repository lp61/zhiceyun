<?php if (!defined('THINK_PATH')) exit();?>﻿<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta id="vp" name="viewport" content="width=device-width, initial-scale=0.4, minimum-scale=0.4, maximum-scale=2.0, user-scalable=yes">
<meta property="qc:admins" content="2411042346635124521003636" />

<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
<link rel="stylesheet" type="text/css" href="__THEME__/css/bootstrap.min.css"/>
<link rel="stylesheet" type="text/css" href="__THEME__/css/bootstrap-theme.min.css"/>
<link rel="stylesheet" type="text/css" href="__THEME__/css/animate.min.css"/>
<link rel="stylesheet" type="text/css" href="__THEME__/css/swiper.min.css"/>
<link rel="stylesheet" type="text/css" href="__THEME__/css/index_style.css"/>

<style type="text/css">
	*{font-family:微软雅黑;}
	.swiper-container{min-height:900px;}
	.swiper-slide{height:900px;}
	.swiper-pagination-bullet{background:#fff;opacity:1.0;}
	.swiper-container-horizontal>.swiper-pagination .swiper-pagination-bullet{margin:1px 20px;}
	.swiper-pagination-bullet-active{background:#24a9e4;width:30px;border-radius:5px;}
	.swiper-container-horizontal>.swiper-pagination{background-image: url(__THEME__/images/whiteLine.png); background-repeat: repeat-x; background-position: center;bottom:100px;}
	.animate1,.animate2,.animate3,.animate4,.animate5{display:none;}
	.active{display:inline-block;}
	.swiper-button-prev{background-image: url(__THEME__/images/swipeLeftArrow.png);}
	.swiper-button-next{background-image: url(__THEME__/images/swipeRightArrow.png);}
	#newinfo{color:#666;border:1px solid #25a3cb;padding:0 10px; border-radius:3px;margin-left:590px;margin-bottom:-30px;margin-top:8px;width:120px;display:none;}
</style>
<title><?php if(($ts['site']['page_title'])  !=  ""): ?><?php echo ($ts['site']['page_title']); ?>
    <?php else: ?>
    <?php echo ($ts['site']['site_header_title']); ?><?php endif; ?></title> 
<link rel="shortcut icon" href="__THEME__/favicon.ico" />
<meta name="keywords" content="<?php echo ($ts['site']['site_header_keywords']); ?>" />
<meta name="description" content="<?php echo ($ts['site']['site_header_description']); ?>" />
</head>
<body>
<div class="navbar">
	<div class="head center">
    <div id="newinfo"><a href="<?php echo U('home/Message/index');?>">您有<span class="blue">1</span>条新消息</a></div>
		<div class="login_after">
        <?php if(isset($_SESSION["userInfo"])): ?><a href="<?php echo U('home/Message/index');?>"><img src="__THEME__/images/loginAfter.png"/></a>
            <img class="newNote" style="display:none;" src="__THEME__/images/newNote.png"/>
			<div class="dropdown">
		        <a href="<?php echo U('home/Account/index');?>" data-role="button" data-toggle="dropdown" class="gray"><?php echo ($_SESSION['userInfo']['email']); ?></a>
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
        
		<div class="logo"><a href="index.php"><img src="__THEME__/images/logo.png"></a></div>
		<div class="nav">
			<div class="dropdown">
		        <a href="<?php echo U('home/Apptest/index');?>" class="navbarWhite" data-role="button" data-toggle="dropdown">应用测试 <i class="glyphicon glyphicon-menu-down small"></i></a>
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
			<a href="<?php echo U('home/Appinfo/experience');?>" class="navbarWhite">终端用户体验</a>
            <a href="<?php echo U('home/Appinfo/iot');?>" class="navbarWhite">IOT</a>
            <a href="<?php echo U('home/Appinfo/article');?>" class="navbarWhite">测试圈</a>
			<div class="dropdown">
		        <a href="#" class="navbarWhite" data-role="button" data-toggle="dropdown">资源池 <i class="glyphicon glyphicon-menu-down small"></i></a>
		        <ul class="dropdown-menu">
		        	<div class="topArrow"><img src="__THEME__/images/dropdpwnTop.png"/></div>
		            <li><a href="<?php echo U('home/Appinfo/mobile');?>">终端库</a></li>
		            <li><a href="<?php echo U('home/Appinfo/internet_source');?>">模拟网资源</a></li>
		            <li><a href="<?php echo U('home/Appinfo/source_press');?>">压力测试资源</a></li>
		        </ul>
		    </div>
			<a href="<?php echo U('home/Public/about');?>" class="navbarWhite">关于我们</a>
            <a href="<?php echo U('home/Public/faq');?>" class="navbarBlue">FAQ</a>
            <?php if( !isset($_SESSION["userInfo"])): ?><a href="<?php echo U('home/Public/login');?>">登录</a><?php endif; ?>
        </div>
    </div>
</div>
<div class="swiper-container">
    <div class="swiper-wrapper">
		<a href="<?php echo U('home/Public/activity_detail');?>">
        <div class="swiper-slide index_first">
		    <div class="center index">
		    	<h1><img src="__THEME__/images/h1.png" class="animate2 mouse1"></h1>
		        <h2><img src="__THEME__/images/h2.png" class="animate1 mouse1"></h2>
		        <div class="code"><img style="width:150px; height:150px;" src="__THEME__/images/aboutUsEr.png" class="animate3 mouse1"></div>
		        <p class="white animate4" style="display:block;"><span>注册用户</span> / <span>邀请好友</span> / <a href="<?php echo U('home/Public/activity_detail');?>">关注微信</a></p>
		    </div>
		</div>
		</a>
        <!--新增banner-->
        <div class="swiper-slide" style="text-align:center;background:#f8a877;">
		    <a href="http://www.wenjuan.com/s/FZ3ERr/">
            	<div style="margin-top:140px"><img class="animate3" src="__THEME__/images/banner1.png"></div>
                <p class="white animate4" style="font-size:60px;font-weight:bold;">泰尔智测云</p><br>
                <div style="margin:10px auto"><img class="animate2" src="__THEME__/images/banner2.png"></div>
                <p class="white animate1" style="font-size:36px;font-weight:bold;">《移动智能终端应用软件（APP）预置和分发管理暂行规定》<br>征求开发者意见调查问卷</p>
            </a>
		</div>
         <!--<div class="swiper-slide" style="text-align:center;background:url(__THEME__/images/banner3.png) no-repeat center;">
		    <a href="http://new.smarterapps.cn/index.php?app=home&mod=Appinfo&act=articleDetail&id=56">
            	<div style="margin:165px auto 70px"><img class="animate3" src="__THEME__/images/banner4.png"></div>
                <h1 class="white animate4" style="font-size:50px;font-weight:bold;">检测应用<img src="__THEME__/images/banner5.png"/></h1><br>
                <h2 class="white animate1" style="font-size:45px;font-weight:bold;margin-top:60px">注册开发者突破<img src="__THEME__/images/banner6.png"/></h2>
            </a>
		</div>-->
        <div class="swiper-slide index_first2">
			<div class="center index">
				<h1 class="gray animate2">更 <span class="red">新 </span> 机 型 、更<span class="red"> 全</span> 服 务 、超<span class="red"> 值</span> 优 惠</h1>
				<div style="display:block"><h2 class="red animate4">新机兼容性测试/专机测试/网络友好测试</h2></div>
				<div class="index_first2_left animate1"><img class="mouse1" src="__THEME__/images/index_first2_left.png"></div>
				<div class="index_first2_right">
					<div class="animate3 index_first2_top"><img class="mouse1" src="__THEME__/images/index_first2_right1.png"></div>
					<div class="index_first2_bottom">
						<div class="index_first2_bottom1 animate1"><img class="mouse1" src="__THEME__/images/index_first2_right2.png"></div>
						<div class="index_first2_bottom2 animate2"><a href="<?php echo U('home/Apptest/index');?>" class="mouse_1"><img src="__THEME__/images/index_first2_testnow.png"></a><br><img class="shadow" src="__THEME__/images/index_first2_testnowbg.png"></div>
					</div>
				</div>
			</div>
		</div>
        <div class="swiper-slide index_first3">
			<div class="center index">
				<h2 class="blue animate2">终端用户体验评测&nbsp;将数据转化为行为</h2>
				<h1 class="red animate1">客户与优质终端互惠互利</h1>
				<div class="index_first3_left animate1">
                <a href="<?php echo U('home/Appinfo/experience');?>" class="mouse_1">
                	<img src="__THEME__/images/index_first2_testnow.png"></a><br>
                    <img class="shadow" src="__THEME__/images/index_first2_testnowbg.png"></div>
				<div class="index_first3_right animate2"><img src="__THEME__/images/index_first3.png"></div>
			</div>
		</div>
        <div class="swiper-slide index_first4">
        	<div class="center index">
				<div  style="display:block"><h1 class="darkgray animate3">智能硬件产品要的都是<span class="red">新</span>，<span class="red">奇</span>，<span class="red">快</span>！</h1></div>
				<div style="display:block;float:left;"><div class="index_first4_left animate1">
					<p class="darkgray mouse1">我们的测试</p>
					<p class="red">协助您快速开发和迭代，减少错误降低风险！</p>
					<p class="darkgray mouse1">我们的测评</p>
					<p class="red">让您的产品与众不同！</p>
					<p class="darkgray mouse1">我们的服务</p>
					<p class="red">让您以最小的花费获得最大受益！</p>
                    
					<div class="animate5"><a href="<?php echo U('home/Appinfo/iot');?>" class="mouse_1"><img src="__THEME__/images/index_first2_testnow.png"></a><br><img class="shadow" src="__THEME__/images/index_first2_testnowbg.png"></div>
                    </div>
				</div>
				<div class="index_first4_right animate4" style="float:left;"><img class="mouse1" src="__THEME__/images/index_first4_2.png"/></div>
			</div>
		</div>
		<div class="swiper-slide index_first5">
			<div class="center index">
				<h1 class="white animate2 mouse1">恭喜智测云（泰尔终端实验室）</h1>
				<h2 class="white animate1 mouse1">于2014年9月成为AQUA的认证实验室</h2>
				<div class="logo animate3"><img class="mouse1" src="__THEME__/images/index_first5logo.png"><img src="__THEME__/images/index_AQUA.png"></div>
				<div style="display:block"><div class="more white animate3 mouse1"><a href="<?php echo U('home/Public/about');?>">了解更多</a></div></div>
			</div>
		</div>
    </div>
    <!-- Add Pagination -->
    <div class="swiper-pagination"></div>
    <!-- Add Arrows -->
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
</div>
<div class="index_second">
    <div class="center">
    	<div class="sp ef3" style="display:block">
                    <h1 class="blue"><a href="<?php echo U('home/Apptest/index');?>">应用测试</a></h1>
                    <h2 class="white"><a href="<?php echo U('home/Apptest/index');?>">APP TEST</a></h2>
        <p class="darkgray">已检测应用<span id="num1" class="blue"><?php echo ($appTestNum); ?></span>款   注册开发者<span id="num2" class="blue"><?php echo ($registerNum); ?></span>人</p>
        </div>
                <div class="index_test sp ef4"><span class="index_test_img mouse2"><a href="<?php echo U('home/Apptest/index');?>"><img src="__THEME__/images/indexNew.png"></a></span><br><span class="blue">最新</span><br><span class="darkgray">最新上市热门高端旗舰机型每周及时更新，紧跟市场潮流</span></a></div>
                <div class="index_test sp ef1"><span class="index_test_img mouse2"><a href="<?php echo U('home/Apptest/index');?>"><img src="__THEME__/images/indexFast.png"></a></span><br><span class="blue">最快</span><br><span class="darkgray">快速提交apk，全程自动化测试24小时反馈测试报告</span></a></div>
                <div class="index_test sp ef1"><span class="index_test_img mouse2"><a href="<?php echo U('home/Apptest/index');?>"><img src="__THEME__/images/indexHight.png"></a></span><br><span class="blue">最深入</span><br><span class="darkgray">行业内最专业的测试系统，庞大的通信模拟网资源，测试更深入</span></a></div>
                <div class="index_test sp ef2"><span class="index_test_img mouse2"><a href="<?php echo U('home/Apptest/index');?>"><img src="__THEME__/images/indexRight.png"></a></span><br><span class="blue">最准确</span><br><span class="darkgray">测试结果精准直接，丰富的截图和详细的日志，准确快速定位</span></a></div>
        <div style="clear:both"></div>
        <div class="blue index_button"><a href="<?php echo U('home/Public/register');?>">立即注册，免费体验</a></div>
    </div>
</div>
<div class="center index_third">
	<div class="index3 sp ef5"><img src="__THEME__/images/index3.png"></div>
	<h1 class="blue"><a href="<?php echo U('home/Appinfo/article');?>">测试圈</a></h1>
    <h2 class="lightgray"><a href="<?php echo U('home/Appinfo/article');?>">TEST BBS</a></h2>
    <?php $titleLen =12;$contentLen=100;$url1=U('home/Appinfo/articleDetail', array('id'=>$article[0]['id']));$url2=U('home/Appinfo/articleDetail', array('id'=>$article[1]['id']));$url3=U('home/Appinfo/articleDetail', array('id'=>$article[2]['id']));$url4=U('home/Appinfo/articleDetail', array('id'=>$article[3]['id'])); ?>
    
    <div class="third_border third_border_1"></div>
    <div class="third_content third_content_1 sp ef3">
    	<a href="<?php echo ($url1); ?>">
            <h4 class="darkgray" title="<?php echo ($article[0]['title']); ?>"><?php echo msubstr(strip_tags($article[0]['title']),0, $titleLen);?></h4>
            <p class="gray">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo msubstr(strip_tags($article[0]['content']),0, $contentLen);?></p>
        </a>
        <a href="<?php echo U('home/Appinfo/articleList',array('type'=>2));?>">
        	<h3 class="lightgray">资讯</h3>
        </a>
    </div>
    <div class="third_content third_content_2 sp ef2">
    	<a href="<?php echo ($url2); ?>">
            <h4 class="darkgray" title="<?php echo ($article[1]['title']); ?>"><?php echo msubstr(strip_tags($article[1]['title']),0, $titleLen);?></h4>
            <p class="gray">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo msubstr(strip_tags($article[1]['content']),0, $contentLen);?></p>
        </a>
        <a href="<?php echo U('home/Appinfo/articleList',array('type'=>3));?>">
        	<h3 class="lightgray">技术</h3>
        </a>
    </div>
    <div class="third_border third_border_2"></div>
    <div class="third_border third_border_3"></div>
    <div class="third_content third_content_3 sp ef4">
        <a href="<?php echo U('home/Appinfo/articleList',array('type'=>4));?>">
        	<h3 class="lightgray">专题</h3>
        </a>
        <a href="<?php echo ($url3); ?>">
            <h4 class="darkgray" title="<?php echo ($article[2]['title']); ?>"><?php echo msubstr(strip_tags($article[2]['title']),0, $titleLen);?></h4>
            <p class="gray">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo msubstr(strip_tags($article[2]['content']),0, $contentLen);?></p>
        </a>
    </div>
    <div class="third_content third_content_4 sp ef1" onclick="location.href='<?php echo ($url4); ?>'">
    	<a href="<?php echo U('home/Appinfo/articleList',array('type'=>5));?>">
        	<h3 class="lightgray">沙龙</h3>
        </a>
        <a href="<?php echo ($url4); ?>">
            <h4 class="darkgray" title="<?php echo ($article[3]['title']); ?>"><?php echo msubstr(strip_tags($article[3]['title']),0, $titleLen);?></h4>
            <p class="gray">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo msubstr(strip_tags($article[3]['content']),0, $contentLen);?></p>
        </a>
    </div>
    <div class="third_border third_border_4"></div>
    <div class="more"><a href="<?php echo U('home/Appinfo/article');?>"><img src="__THEME__/images/index3_arrowdown.png"><p class="gray">点击查看更多新闻</p></a></div>
</div>
<div class="index_fourth">
	<div class="center">
      <h1 class="white sp ef3" style="display:block"><a href="<?php echo U('home/Apptest/specialTest');?>">专机测试</a></h1>
      <h2 class="white sp ef1" style="display:block"><a href="<?php echo U('home/Apptest/specialTest');?>">HOT DEVICE TEST</a></h2>
      <div class="device sp ef2" onclick="location.href='<?php echo U('home/Appinfo/mobileDetail',array('id'=>$appNewArray[0]['id']));?>'">
          <div class="mbtn"><span>测试</span></div><div class="img"><img src="<?php echo ($appNewArray[0]['photo_url']); ?>"></div>
          <h3 class="white"><?php echo ($appNewArray[0]['name']); ?></h3>
          <P class="white">操作系统：<?php echo ($appNewArray[0]['os']); ?><br>分辨率：<?php echo ($appNewArray[0]['resolution']); ?><br>屏幕尺寸：<?php echo ($appNewArray[0]['size']); ?><br>上市时间：<?php echo ($appNewArray[0]['release_time']); ?></P>
      </div>
      <div class="device middle sp ef1" onclick="location.href='<?php echo U('home/Appinfo/mobileDetail',array('id'=>$appNewArray[1]['id']));?>'">
          <div class="mbtn"><span>测试</span></div><div class="img"><img src="<?php echo ($appNewArray[1]['photo_url']); ?>"></div>
          <h3 class="white"><?php echo ($appNewArray[1]['name']); ?></h3>
          <P class="white">操作系统：<?php echo ($appNewArray[1]['os']); ?><br>分辨率：<?php echo ($appNewArray[1]['resolution']); ?><br>屏幕尺寸：<?php echo ($appNewArray[1]['size']); ?><br>上市时间：<?php echo ($appNewArray[1]['release_time']); ?></P></div>
      <div class="device sp ef4" onclick="location.href='<?php echo U('home/Appinfo/mobileDetail',array('id'=>$appNewArray[2]['id']));?>'">
          <div class="mbtn"><span>测试</span></div><div class="img"><img src="<?php echo ($appNewArray[2]['photo_url']); ?>"></div>
          <h3 class="white"><?php echo ($appNewArray[2]['name']); ?></h3>
          <P class="white">操作系统：<?php echo ($appNewArray[2]['os']); ?><br>分辨率：<?php echo ($appNewArray[2]['resolution']); ?><br>屏幕尺寸：<?php echo ($appNewArray[2]['size']); ?><br>上市时间：<?php echo ($appNewArray[2]['release_time']); ?></P>
      </div>
	  <div style="clear:both"></div>
   	  <div class="white more index_button2 sp ef1" style="display:block"><a href="<?php echo U('home/Apptest/specialTest');?>">MORE...</a></div>
    </div>
</div>
<div class="index_fifth center scrollpoint sp ef5" id="bbb" style="display:block;">
            <h1 class="blue">合作伙伴</a></h1>
            <h2 class="lightgray">PARTINERS</a></h2>
  <div class="index5_content">
    <div data="#"><a href="http://android.myapp.com/" target="_blank"><img src="__THEME__/images/PARNTER/yyb.png" class="mouse1"></a></div>
    <div data="#" class="lightgrayBg"><a href="http://www.360.cn/" target="_blank"><img src="__THEME__/images/PARNTER/360.png" class="mouse1"></a></div>
    <div data="#"><a href="https://www.apkudo.com" target="_blank"><img src="__THEME__/images/PARNTER/apk.png" class="mouse1"></a></div>
    <div data="#" class="lightgrayBg"><a href="http://www.appqualityalliance.org/" target="_blank"><img src="__THEME__/images/PARNTER/aqua.png" class="mouse1"></a></div>
    <div data="#"><a href="http://www.att.com" target="_blank"><img src="__THEME__/images/PARNTER/at.png" class="mouse1"></a></div>
    <div data="#" class="lightgrayBg"><a href="http://www.gsma.com" target="_blank"><img src="__THEME__/images/PARNTER/gsma.png" class="mouse1"></a></div>
    <div data="#"><a href="http://www.huawei.com/cn/" target="_blank"><img src="__THEME__/images/PARNTER/hw.png" class="mouse1"></a></div>
    <div data="#" class="lightgrayBg"><a href="http://www.10010.com/" target="_blank"><img src="__THEME__/images/PARNTER/lt.png" class="mouse1"></a></div>
    <div data="#"><a href="http://www.lenovo.com.cn/" target="_blank"><img src="__THEME__/images/PARNTER/lx.png" class="mouse1"></a></div>
    <div data="#" class="lightgrayBg"><a href="http://www.zte.com.cn/cn/" target="_blank"><img src="__THEME__/images/PARNTER/zx.png" class="mouse1"></a></div>
    <div data="#"><a href="http://www.sony.com.cn/" target="_blank"><img src="__THEME__/images/PARNTER/sony.png" class="mouse1"></a></div>
    <div data="#" class="lightgrayBg"><a href="http://www.samsung.com/cn/" target="_blank"><img src="__THEME__/images/PARNTER/sx.png" class="mouse1"></a></div>
    <div data="#"><a href="http://www.thundersoft.com/cn/" target="_blank"><img src="__THEME__/images/PARNTER/thumdersoft.png" class="mouse1"></a></div>
    <div data="#" class="lightgrayBg"><a href="http://www.hicdma.com/" target="_blank"><img src="__THEME__/images/PARNTER/ty.png" class="mouse1"></a></div>
    <div data="#"><a href="http://zs.91.com/" target="_blank"><img src="__THEME__/images/PARNTER/91.png" class="mouse1"></a></div>
  </div>
</div>
<div class="index_sixth lightgrayBg">
	<div class="center">
    	<h1 class="green sp ef1" style="display:block"><b>关注Smarterapps微信公众号，实时查询测试状态，获得App测试最新信息</b></h1>
        <h2 class="green sp ef3" style="display:block"><b>您可以通过微信，QQ与我们互动哦！</b></h2>
        <div class="index_sixth_content">
        	<div class="theThird sp ef2"><a href="###"><img src="__THEME__/images/index_footer1.png"></a><br><span style="display:block">官方微信</span></div>
            <div class="theThird middle sp ef3"><a href="http://jq.qq.com/?_wv=1027&k=SvQsir" target="_blank"><img src="__THEME__/images/index_footer2.png"></a><br></div>
        	<div class="theThird sp ef4"><a href="http://weibo.com/5704661965" target="_blank"><img src="__THEME__/images/index_footer3.png"></a><br></div>
        </div>
        <div style="clear:both;"></div>
        <p class="gray sp ef1" style="display: block;">电话：010-62304633（2923）  / QQ：2059235439 / 邮箱：service@smarterapps.cn / 合作：market@smarterapps.cn<br>Copyright by China Academy of Information & Communication Technology：京ICP备09013372号-10<p>
    </div>
</div>
<script src="__THEME__/js/jquery-1.11.3.min.js" type="text/javascript" charset="utf-8"></script>
<script src="__THEME__/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="__THEME__/js/jquery.waypoints.min.js" type="text/javascript" charset="utf-8"></script>
<script src="__THEME__/js/sticky.min.js" type="text/javascript" charset="utf-8"></script>
<script src="__THEME__/js/swiper.jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script src="__THEME__/js/swiper.animate1.0.2.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
$(function() {
	$(".login_after").css("margin-left",720);
	var swiper = new Swiper('.swiper-container', {
        pagination: '.swiper-pagination',
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
        slidesPerView: 1,
        paginationClickable: true,
        centeredSlides: true,
        autoplay: 5000,//自动播放延时
        autoplayDisableOnInteraction: true,
        loop: true,
		onTransitionStart:function(swiper){
        	$(".animate1").removeClass('active').removeClass('animated fadeInUp');
        	$(".animate2").removeClass('active').removeClass('animated fadeInRight');
        	$(".animate3").removeClass('active').removeClass('animated fadeInDown');
        	$(".animate4").removeClass('active').removeClass('animated fadeInLeft');
        	$(".animate5").removeClass('active').removeClass('animated fadeIn');
        },
        onSlideChangeEnd: function(swiper){
	      //alert('事件触发了;');
	      $(".animate1").addClass('active').addClass('animated fadeInUp');
	      $(".animate2").addClass('active').addClass('animated fadeInRight');
	      $(".animate3").addClass('active').addClass('animated fadeInDown');
	      $(".animate4").addClass('active').addClass('animated fadeInLeft');
	      $(".animate5").addClass('active').addClass('animated fadeIn');
	    },
	    onInit:function(swiper){
	    	var m=$(".swiper-pagination").children().length*48+22;
	    	var w=$("body").width();
	    	w=(w-m)/2;
	    	$(".swiper-pagination").width(m).css("margin-left",w+"px");
	    }
    });
   /*鼠标放上停止循环播放 
   $('.swiper-slide').mouseover(function(){
		swiper.stopAutoplay();
	}).mouseout(function(){
		swiper.startAutoplay();
	});*/
	var waypoint = new Waypoint({
	  element:$('.navbar'),
	  handler: function(direction) {
	  	if(direction=="down"){
		    $('.navbar').css({"position":"fixed","top":"0","margin-top":"0"}).css("background","url(__THEME__/images/navBg75.png)").hide().fadeIn(1000);
		    $('.navbar .nav a').removeClass("navbarWhite").addClass("navbarBlue");
	  	}else if(direction=="up"){
		  	$('.navbar').hide().fadeIn(1000).css({"position":"absolute","background":"none"});
		    //$('.navbar .nav a').removeClass("navbarBlue").addClass("navbarWhite");
	  	}
	  }
	})
$('.navbar').hide().fadeIn(1000).css({"position":"absolute","background":"none"});
		    $('.navbar .nav a').removeClass("navbarBlue").addClass("navbarWhite");

	$('.sp.ef1').waypoint(function(){var m=this.adapter.$element;m.toggleClass('active');m.toggleClass('animated fadeInUp');},{offset:'100%'});
    $('.sp.ef2').waypoint(function(){var m=this.adapter.$element;m.toggleClass('active');m.toggleClass('animated fadeInRight');},{offset:'100%'});
    $('.sp.ef3').waypoint(function(){var m=this.adapter.$element;m.toggleClass('active');m.toggleClass('animated fadeInDown');},{offset:'100%'});
    $('.sp.ef4').waypoint(function(){var m=this.adapter.$element;m.toggleClass('active');m.toggleClass('animated fadeInLeft');},{offset:'100%'});
    $('.sp.ef5').waypoint(function(){var m=this.adapter.$element;m.toggleClass('active');m.toggleClass('animated fadeIn');},{offset:'100%'});
	
	//鼠标效果一
	/*$(".mouse1").mouseover(function(){
		var w=$(this).width();
		$(this)
			.animate({"width":(w*0.8)+"px","opacity":"0.3"},100)
			.animate({"width":(w*1.1)+"px","opacity":1},100)
			.animate({"width":(w*0.9)+"px","opacity":0.5},100)
			.animate({"width":(w*1.05)+"px","opacity":1},100)
			.animate({"width":(w*0.95)+"px","opacity":0.7},100)
			.animate({"width":(w*1.02)+"px","opacity":0.9},100)
			.animate({"width":w+"px","opacity":1},100)
			
	})*/

	$(".mouse2").mouseover(function(){
		$(this).css({"border-width":10})			
	}).mouseout(function(){
		$(this).removeAttr("style");
	})
	/*第五个模块鼠标滑过的效果*/
	$(".index5_content div").mouseover(function(){
		$($(this).find("img").attr("src",$(this).find("img").attr("src").replace(".png","active.png")));
	}).mouseout(function(){
		$($(this).find("img").attr("src",$(this).find("img").attr("src").replace("active.png",".png")));
	}).click(function(){
		location.href=$(this).attr("data");
	})
	/*第六个模块鼠标滑过的效果*/
	$(".theThird span").hide();
	$(".theThird").mouseover(function(){
		$($(this).find("img").attr("src",$(this).find("img").attr("src").replace(".png","active.png")));
		$(this).find("span").show();
	}).mouseout(function(){
		$($(this).find("img").attr("src",$(this).find("img").attr("src").replace("active.png",".png")));
		$(this).find("span").hide();
	})
	
	$(".index_button2").mouseover(function(){
		$(this).css("background","#fff").children("a").css("color","#666");
	}).mouseout(function(){
		$(this).css("background","none").children("a").css("color","#fff");
	})
	
	
	//登录后账号上的下拉菜单补丁
	//$(".dropdown7>a").attr("data-role","button").attr("data-toggle","dropdown");
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
	var mm=($("body").width()-1080)/2+10;
	$(".swiper-button-prev").css("left",mm+"px");
	$(".swiper-button-next").css("right",mm+"px");
	/*
	$("#num1,#num2").waypoint(function(){
	var num01=<?php echo ($appTestStart); ?>;
	var interval1=setInterval(function(){
		num01++;
		$("#num1").html(num01);
		if(num01>=<?php echo ($appTestNum); ?>){
			clearInterval(interval1);
		}
	},200);
	var num02=<?php echo ($registerStart); ?>;
	var interval2=setInterval(function(){
		num02++;
		$("#num2").html(num02);
		if(num02>=<?php echo ($registerNum); ?>){
			clearInterval(interval2);
		}
	},200)
	},{offset:'100%'})
	*/
	
	$(window).resize(function() {
		//location.reload();
	});
	
	<?php if ($mid){ ?>
    function getNoreadMsg() {
        var url = "<?php echo U('home/Message/getNoreadMsg');?>";
        var obj = $('.newNote');
        $.post(url, {}, function(data){
            console.log(data);
                if (data.count > 0) {
                    obj.show();
                    if (data.number > 0){
                        //判断有新消息后，执行此段代码
                        $('#newinfo').show().delay(3000).hide(0);
                        $('#newinfo .blue').html(data.number);}
                }else{
                    obj.hide();
                }
        },'json');
    }
    getNoreadMsg();
    setInterval(getNoreadMsg, 15000);
    <?php } ?>
})

$(function(){
	function setViewport(){
            var sca=360/1080;
            vp.content='width=device-width,initial-scale = '+sca+',minimum-scale='+sca+',user-scalable=yes';
    }
 
	setTimeout(function(){
		setViewport();
	},1000);
})
</script>
</body>
</html>