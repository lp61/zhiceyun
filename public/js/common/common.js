define(function(require, exports, module){
    var $        = require('jquery');

    exports.init = function(config){
        $("#message_show, #account_show").mouseenter(function(event) {
            $(this).addClass('hover');
        }).mouseleave(function(event) {
            $(this).removeClass('hover');
        });

        $(function(){
            $("input[placeholder][type!='password']").focus(function(){
                if($(this).val()==$(this).attr("placeholder")){
                    $(this).val("");
                }
            }).blur(function(){
                if($(this).val()===""){
                    $(this).val($(this).attr("placeholder"));
                }
            }).each(function(){
                $(this).val($(this).attr("placeholder"));
            });

			//页面底部的二维码图
            $(".footIco>a>img").mouseover(function(){
                $(this).attr("src",$(this).attr("src").replace("2.png",".png").replace(".png","2.png"));
                $(this).next().show();
            }).mouseout(function(){
                $(this).attr("src",$(this).attr("src").replace("2.png",".png"));
                $(this).next().hide();
            });

            //图片上浮动的图层
            setTimeout(function(){
                $(".imgTitle").each(function(){
                    if(navigator.appName == "Microsoft Internet Explorer" && navigator.appVersion .split(";")[1].replace(/[ ]/g,"")=="MSIE7.0") { 
                        $(this).css("margin-top",($(this).prev().children(0).height()-$(this).height())+"px");
                        $(this).css("width",($(this).prev().children(0).width()-10)+"px");
                    }
                });
            },2000);
            
            //右下角弹出窗口
            $(".corner ul a").click(function(){
                $(".corner ul a").removeClass("active");
                $(this).addClass("active");
            });
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
            });
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
            });

            //ie兼容
            if(navigator.appName == "Microsoft Internet Explorer" && navigator.appVersion .split(";")[1].replace(/[ ]/g,"")=="MSIE8.0") { 
                $(".APPbag .buton").css("padding-top","5px");
				$(".selectStartIE8").css("margin-left","60px");
            }else if(navigator.appName == "Microsoft Internet Explorer" && navigator.appVersion .split(";")[1].replace(/[ ]/g,"")=="MSIE7.0") { 
                $(".test .btn").css("margin-top","-13px").css("margin-left","-26px");
				$(".right2 .inentify").css("height","50px").css("padding-top","10px");
				$(".right2 .fieldset").css("width","122px")
				$("select").css("border","none");
				$(".verifyStartIE7").css("margin-left","10px");
            }
	
			//复选框
			if(location.search.indexOf("register")==-1 && location.search.indexOf("login")==-1){
				$(".checkbox").each(function(){
					if($(this).children(0).attr("checked")){
						$(this).css("background-position","0");
						$(this).children(0).attr("checked","true").attr("value",1);
					}else{
						$(this).children(0).removeAttr("checked").attr("value",0);
						$(this).css("background-position","18px");
					}
				});
				$(".checkbox").click(function(){
					if($(this).children(0).attr("checked")){
						$(this).children(0).removeAttr("checked").attr("value",0);
						$(this).css("background-position","18px");
					}else{
						$(this).css("background-position","0");
						$(this).children(0).attr("checked","true").attr("value",1);
					}
				});
				$(".checkboxlabel").click(function(){
					if($(this).prev().children(0).attr("checked")){
						$(this).prev().children(0).removeAttr("checked").attr("value",0);
						$(this).prev().css("background-position","18px");
					}else{
						$(this).prev().css("background-position","0");
						$(this).prev().children(0).attr("checked","true").attr("value",1);
					}
				});	
			}
			//单选框
			$(".radio").click(function(){
				$(".radio").css("background-position","18px");
				$(this).css("background-position","0");
				$(".radio").children(0).removeAttr("checked").attr("value",0);
				$(this).children(0).attr("checked","true").attr("value",1);
			});
			$(".radiolabel").click(function(){
				$(".radio").css("background-position","18px");
				$(this).prev().css("background-position","0");
				$(".radio").children(0).removeAttr("checked").attr("value",0);
				$(this).prev().children(0).attr("checked","true").attr("value",1);

			});
        });
	
        $('[data-ahref]').click(function(){
            var id = $(this).data('ahref');
            $('#'+id)[0].click();
            return false;
        });

    };
});