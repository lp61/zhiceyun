$(function(){
//应用测试点击开始测试的弹窗
	$("#TESTbg").hide();
	$(".testButton").click(function(){
		$("#TESTbg").show();
	});
	$(".begin .close").click(function(){
		$("#TESTbg").hide();
	});
	$(".begin button").click(function(){
		$("#TESTbg").hide();
	});
//后退按钮
	$(".IOT .header .history").click(function(){
		history.go(-1);
	})	
//菜单按钮首页
	var isshow=false;	
	$(".index .header .ui-block-b").click(function(){
		if(isshow){
			$(this).children(".arrow").hide();
			$(this).children(".check").hide();
		}else{
			$(this).children(".arrow").show();
			$(this).children(".check").show();
		}
		isshow=!isshow;
	});
	$(".applyTest_0,.applyTest_1,.applyTest_2,.applyTest_3,.applyTest_4,.applyTest_5,.aboutUs,.aboutUs_banner,.applyTest,.source,.experience,.record,.stork,.testCircle,#iot").click(function(){
		if(isshow){
			isshow=false;
			$(".header .arrow").hide();
			$(".header .check").hide();
		}
	})
//菜单按钮
	$(".menubtn .arrow,.menubtn .check").hide();
		var isshow=false;
		$(".menubtn").click(function(){
			if(isshow){
				$(this).children(".arrow").hide();
				$(this).children(".check").hide();
			}else{
				$(this).children(".arrow").show();
				$(this).children(".check").show();
			}
				isshow=!isshow;
		});
//关于我们
	//$(".third .ui-grid-c div").height($(".third .ui-grid-c div").width())
	$(".third .erwei img").hide();
	var isQrShow=false;
	
	$(".qrbtn a").click(function(){
		var m=$(this);
		
		$(".qrbtn a").each(function(){
			$(this).children("img").attr("src",$(this).children("img").attr("src").replace("2.png",".png"));
			$(this).css("background","#fff");
		})
			
		if(m.children("img").attr("src").indexOf("2.png")==-1){
			m.children("img").attr("src",m.children("img").attr("src").replace(".png","2.png"));
			m.css("background","#25a3cb");
		}
		if(m.hasClass("qrWeixin")){
			if(isQrShow){
				$(".erwei").hide();
				isQrShow=false;
			}else{
				$(".erwei").show();
				isQrShow=true;
			}	
		}else{
		$(".erwei").hide();
			isQrShow=false;	
		}
	})
	
	
})