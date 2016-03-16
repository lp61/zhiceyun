$(function(){
	//placeholder兼容ie78
	$("input").add("textarea").focus(function(){
		if($(this).val()==$(this).attr("placeholder")){
			$(this).val("");
		}
	}).blur(function(){
		if($(this).val()==""){
			$(this).val($(this).attr("placeholder"));
		}
	}).each(function(){
		$(this).val($(this).attr("placeholder"));
	});
	
	
	//复选框
	$(".checkbox").click(function(){
		if($(this).children(0).attr("checked")){
			$(this).children(0).removeAttr("checked");
			$(this).css("background-position","18px");
		}else{
			$(this).css("background-position","0");
			$(this).children(0).attr("checked","true");
		}
	})
	$(".checkboxlabel").click(function(){
		if($(this).prev().children(0).attr("checked")){
			$(this).prev().children(0).removeAttr("checked");
			$(this).prev().css("background-position","18px");
		}else{
			$(this).prev().css("background-position","0");
			$(this).prev().children(0).attr("checked","true");
		}
	})
	//单选框
	$(".radio").click(function(){
		$(".radio").css("background-position","18px");
		$(this).css("background-position","0");
		$(".radio").children(0).removeAttr("checked");
		$(this).children(0).attr("checked","true");
	})
	$(".radiolabel").click(function(){
		$(".radio").css("background-position","18px");
		$(this).prev().css("background-position","0");
		$(".radio").children(0).removeAttr("checked");
		$(this).prev().children(0).attr("checked","true");
	})
	
	//页面底部的二维码图标
	$(".footIco>a>img").mouseover(function(){
		$(this).attr("src",$(this).attr("src").replace("2","").replace(".","2."));
		$(this).next().show();
	}).mouseout(function(){
		$(this).attr("src",$(this).attr("src").replace("2",""));
		$(this).next().hide();
	})
	
	//图片上浮动的图层
	setTimeout(function(){
		$(".imgTitle").each(function(){
			if(navigator.appName == "Microsoft Internet Explorer" && navigator.appVersion .split(";")[1].replace(/[ ]/g,"")=="MSIE7.0") { 
				$(this).css("margin-top",($(this).prev().children(0).height()-$(this).height())+"px");
				$(this).css("width",($(this).prev().children(0).width()-10)+"px");
			}
		});
	},2000)
	
	//右下角弹出窗口
	$(".corner ul a").click(function(){
		$(".corner ul a").removeClass("active");
		$(this).addClass("active");
	})
	var shareStatus=0;
	$("a.shareico").click(function(){
		if(commentStatus==1){
		    commentStatus=0;
			$(".comment").animate({left: "10px"}, 1000 ).hide(10);
	    }
		if(shareStatus==0){
			shareStatus=1;
			$(".share").show().animate({left: "-320px"}, 1000 );
		}else{
			shareStatus=0;
			$(".share").animate({left: "10px"}, 1000 ).hide(10);
			$("a.shareico").removeClass("active");
		}
		
		return;
	})
	var commentStatus=0;
	$("a.commentico").click(function(){
		if(shareStatus==1){
		    shareStatus=0;
			$(".share").animate({left: "10px"}, 1000 ).hide(10);
	    }
		if(commentStatus==0){
			commentStatus=1;
			$(".comment").show().animate({left: "-320px"}, 1000 );
		}else{
			commentStatus=0;
			$(".comment").animate({left: "10px"}, 1000 ).hide(10);
			$("a.commentico").removeClass("active");
		}
		return;
	})
	
	//ie兼容
	if(navigator.appName == "Microsoft Internet Explorer" && navigator.appVersion .split(";")[1].replace(/[ ]/g,"")=="MSIE8.0") { 
		$(".APPbag .buton").css("padding-top","5px");
	}else if(navigator.appName == "Microsoft Internet Explorer" && navigator.appVersion .split(";")[1].replace(/[ ]/g,"")=="MSIE7.0") { 
		
	}
})