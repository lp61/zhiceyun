<?php

/* *
 * 功能：支付宝服务器异步通知页面
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。


 * ************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */

require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");
require_once("../include/dbinc.php");

//积分转换规则 1元=1积分
$rule = 10;

//计算得出通知验证结果
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();

if ($verify_result) {//验证成功
    //请在这里加上商户的业务逻辑程序代
    $trade_status = $_POST['trade_status'];

    $out_trade_no = $_POST['out_trade_no'];
    $trade_no = $_POST['trade_no'];
    $ord_money = $_POST['total_fee'];
    $pay_time = $_POST['gmt_payment'];
    $notify_time = $_POST['notify_time'];
    $ord_status = 1;
    $status = 1;
    $timestamp = time();

    if ($trade_status == 'TRADE_SUCCESS') {
        //STEP-1 更新订单状态
        $row = $db->get_one("SELECT id,ord_status,uid FROM ts_pay WHERE ord_no='$out_trade_no' LIMIT 1");
        $uid = $row['uid'];
        $fkid = $row['id'];
        if ($row['ord_status'] == 0) { //该订单未回调
            $sql = "UPDATE ts_pay SET trade_no='$trade_no',ord_money='$ord_money',"
                . "ord_status='$ord_status',pay_time='$pay_time',notify_time='$notify_time',status='$status' "
                . " WHERE ord_no='$out_trade_no'";
            $db->Query($sql);
            $score = $ord_money * $rule;
            $db->Query("UPDATE ts_user SET pay_amount=pay_amount+'$ord_money' WHERE uid='$uid'");
            //STEP-2 兑换积分
            //ts_credit_user_log 积分日志
            $sql1 = "INSERT INTO ts_credit_user_log SET uid='$uid',fkid='$fkid',score='$score',credit_name='pay_ment',credit_alias='积分充值',ctime='$timestamp'";
            $db->Query($sql1);
            //ts_credit_user 修改用户积分
            $db->Query("UPDATE ts_credit_user SET score=score+'$score' WHERE uid='$uid'");
            //ts_app_message 发送通知消息
            $content = "恭喜您，成功充值" . $ord_money . "元，兑换积分" . $score . "分！您本次的支付宝交易订单号为：" . $trade_no . "。";
            $db->Query("INSERT INTO ts_app_message SET to_uid='$uid',title='充值成功',content='$content',ctime='$timestamp'");
            //STEP-3 发送微信推送通知(队列)
            $sql2 = "SELECT  openID  FROM  ts_wx_user  WHERE  uid='$uid'  AND  status=1 LIMIT 1";
            $wx = $db->get_one($sql2);
            $touser = $wx['openID'];
            if ($touser) {
                $type = 4; //4、积分变动通知
                $list1 = $db->get_one("SELECT score FROM ts_credit_user WHERE uid='$uid' LIMIT 1");
                $list2 = $db->get_one("SELECT email FROM ts_user WHERE uid='$uid' LIMIT 1");
                $first = '您的智测云账号充值成功，充值金额：' . $ord_money . '元！';
                $key1 = $list2['email']; //用户名
                $key2 = date("Y-m-d H:i"); //时间
                $key3 = $score; //积分变动
                $key4 = $list1['score']; //积分余额
                $key5 = '积分充值'; //变动原因
                $sql = "INSERT  INTO  ts_wx_push  SET uid='$uid',openID='$touser',type='$type',first='$first',key1='$key1',key2='$key2',key3='$key3',key4='$key4',key5='$key5',ctime=now()";
                logResult($sql . "\n");
                $db->Query($sql);
            }
        }
    }

    /*
      //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
      //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
      //商户订单号
      $out_trade_no = $_POST['out_trade_no'];
      //支付宝交易号
      $trade_no = $_POST['trade_no'];
      //交易状态
      $trade_status = $_POST['trade_status'];

    if($_POST['trade_status'] == 'TRADE_FINISHED') {
		//判断该笔订单是否在商户网站中已经做过处理
        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
        //如果有做过处理，不执行商户的业务程序
        //注意：
        //该种交易状态只在两种情况下出现
        //1、开通了普通即时到账，买家付款成功后。
        //2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
        //调试用，写文本函数记录程序运行情况是否正常
        logResult("TRADE_FINISHED");
    } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
        //判断该笔订单是否在商户网站中已经做过处理
        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
        //如果有做过处理，不执行商户的业务程序
        //注意：
        //该种交易状态只在一种情况下出现——开通了高级即时到账，买家付款成功后。
        //调试用，写文本函数记录程序运行情况是否正常
        
//     	discount=0.00
//     	payment_type=1
//     	subject=123
//     	trade_no=2015091321001004180080750423  //支付宝交易号
//     	buyer_email=cyh337@126.com  //买家mail
//     	gmt_create=2015-09-13 00:29:53 //订单创建时间
//     	notify_type=trade_status_sync 
//     	quantity=1
//     	out_trade_no=5432112345  //内部订单号
//     	seller_id=2088512165008187  //卖家ID
//     	notify_time=2015-09-13 00:30:00  //支付宝异步通知时间
//     	body=22121 //商品描述
//     	trade_status=TRADE_SUCCESS  //订单状态
//     	is_total_fee_adjust=N
//     	total_fee=0.01 //付款总金额
//     	gmt_payment=2015-09-13 00:30:00 //订单支付时间
//     	seller_email=linruijie@catr.cn //卖家mail
//     	price=0.01  //价格
//     	buyer_id=2088002005027189  //买家ID
//     	notify_id=8443d76a982dcd4beed4fae83f169cdhe0  //异步ID
//     	use_coupon=N
//     	sign_type=MD5
//     	sign=c70db3ccc45f1e375658eb5ebd5f3f0e	
    }
*/	
    	foreach ($_POST as $k=>$v){
    		$tmp_str.=$k."=".$v."\n";
    	}
    	
        logResult($tmp_str);
	//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
        
	echo "success";		//请不要修改或删除
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
else {
    //验证失败
    echo "fail";

    //调试用，写文本函数记录程序运行情况是否正常
    logResult(json_encode($_POST . "\r\n"));
}
?>