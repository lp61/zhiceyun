<?php

/*
 * 微信接口
 */

include_once 'common.php';
define("VERIFY_MODEL", 1); //验证模式 1-开 0-关   验证URL有效性，只有更换公众号Url或Token时，需进行一次验证

$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
$msg = "Index   GET：" . json_encode($_GET) . "\r\n" . "POST：" . $postStr;
LogWrite($msg);

//define your token
define("TOKEN", "catr_654321");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->valid();

class wechatCallbackapiTest {

    public function valid() {
        //valid signature , option
        if ($this->checkSignature()) {
            if (VERIFY_MODEL == 1) {
                $echoStr = $_GET["echostr"];
                echo $echoStr;
            } else {
                $this->responseMsg();   //正常返回消息
            }
            exit;
        }
    }

    public function responseMsg() {
        //get post data
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        if (!empty($postStr)) {
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            if ($postObj->MsgType == "event") {//判断是事件还是文本，其实都一样，只不过是获取keyword的方式不同
                $keyword = trim($postObj->Event);
            } else {
                $keyword = trim($postObj->Content);
            }

            LogWrite("keyword=" . $keyword . "\r\n");
            if (!empty($keyword)) {
                //------------------------此处开始添加微信接口
                //关键词分类处理
                //关注自动回复
                if ($keyword == 'subscribe') {
                    //$content = "欢迎关注收发室~~";
                    //$resultStr = $this->callbackText($fromUsername, $toUsername, $content);
                    //$data[0]['title'] = '快递“蛋痛”的问题是不是依然存在？';
                    //$data[0]['description'] = '快递“蛋痛”的问题是不是依然存在？';
                    //$data[0]['picurl'] = 'http://42.96.201.183/shoufashi/wap/005.jpg';
                    //$data[0]['url'] = 'http://42.96.201.183/shoufashi/wap/index.html';
                    $data[0]['title'] = '既然关注，怎能不绑';
                    $data[0]['description'] = '';
                    $data[0]['picurl'] = 'http://42.96.201.183/shoufashi/msg/msg3/000.jpg';
                    $data[0]['url'] = 'http://42.96.201.183/shoufashi/msg/msg3/index.html';
                    $resultStr = $this->callbackNews($fromUsername, $toUsername, $data);
                    echo $resultStr;
                    LogWrite($resultStr);
                    return;
                }
                //菜单事件，点击菜单拉取消息时的事件推送
                if ($keyword == 'CLICK') {//$title,$description,$picUrl,$url 图文回复
                    $eventKey = $postObj->EventKey;
                    if ($eventKey == "MENU_101") {
                        $data[0]['title'] = '代发快递状态';
                        $data[0]['description'] = '请点击此链接查看代发快递状态！';
                        $data[0]['picurl'] = '';
                        $data[0]['url'] = 'http://42.96.201.183/GuoAo/wxwap/send_1.php?wpk=' . $fromUsername;
                        $resultStr = $this->callbackNews($fromUsername, $toUsername, $data);
                        echo $resultStr;
                        return;
                    }
                    if ($eventKey == "MENU_102") {
                        $data[0]['title'] = '代收快递状态';
                        $data[0]['description'] = '请点击此链接查看代收快递状态！';
                        $data[0]['picurl'] = '';
                        $data[0]['url'] = 'http://42.96.201.183/GuoAo/wxwap/collect_1.php?wpk=' . $fromUsername;
                        $resultStr = $this->callbackNews($fromUsername, $toUsername, $data);
                        echo $resultStr;
                        return;
                    }
                    if ($eventKey == "MENU_201") {
                        $data[0]['title'] = '快递订单查询';
                        $data[0]['description'] = '请点击此链接进行快递订单查询！';
                        $data[0]['picurl'] = '';
                        $data[0]['url'] = 'http://42.96.201.183/GuoAo/wxwap/index_3.php?wpk=' . $fromUsername;
                        $resultStr = $this->callbackNews($fromUsername, $toUsername, $data);
                        echo $resultStr;
                        return;
                    }
                    if ($eventKey == "MENU_301") {
                        $data[0]['title'] = '手机号绑定';
                        $data[0]['description'] = '请点击此链接进行手机号绑定！';
                        $data[0]['picurl'] = '';
                        $data[0]['url'] = 'http://42.96.201.183/GuoAo/wxwap/index_2.php?wpk=' . $fromUsername;
                        $resultStr = $this->callbackNews($fromUsername, $toUsername, $data);
                        echo $resultStr;
                        return;
                    }
                }
                if ($keyword != "") {
                    $data = '您好，欢迎您使用智测云。';
                    $resultStr = $this->callbackText($fromUsername, $toUsername, $data);
                    echo $resultStr;
                    return;
                } else {
                    //关键词空，不操作。
                }
            } else {
                echo "";
                exit;
            }
        }
    }

    //下面是返回数据的结构，如果全部都丢在逻辑代码里面，会显得很臃肿，所以我写到下方（我永远都不需要再看的地方），而且我建议你写到另一个文件里面，要用的时候引入一下就好了。
    //上面有使用的例子，参照一下就会了，当然了，如果你还不会可以给我留言
    //下面的图文回复，是重点，但不是难点，图文可以回复若干个，这个根据你自己的情况写，同样，调用例子在上面。
    //text format
    public function callbackText($fromUsername, $toUsername, $content) {
        $time = time();
        $tmpStr = "<xml>
				<ToUserName><![CDATA[%s]]></ToUserName>
				<FromUserName><![CDATA[%s]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[text]]></MsgType>
				<Content><![CDATA[%s]]></Content>
				</xml>";
        $tmpStr = sprintf($tmpStr, $fromUsername, $toUsername, $time, $content);
        return $tmpStr;
    }

    //music format
    public function callbackMusic($fromUsername, $toUsername, $title, $description, $musicUrl, $HQMusicUrl, $ThumbMediaId) {
        $time = time();
        $tmpStr = "<xml>
				<ToUserName><![CDATA[%s]]></ToUserName>
				<FromUserName><![CDATA[%s]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[music]]></MsgType>
				<Music>
				<Title><![CDATA[%s]]></Title>
				<Description><![CDATA[%s]]></Description>
				<MusicUrl><![CDATA[%s]]></MusicUrl>
				<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
				<ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
				</Music>
				</xml>";
        $tmpStr = sprintf($tmpStr, $fromUsername, $toUsername, $time, $title, $description, $musicUrl, $HQMusicUrl, $ThumbMediaId);
        return $tmpStr;
    }

    //news format  这里是图文回复了。
    public function callbackNews($fromUsername, $toUsername, $data) {//$title,$description,$picUrl,$url
        $time = time();
        $count = count($data);
        $header = "
                <xml>
                <ToUserName><![CDATA[" . $fromUsername . "]]></ToUserName>
                <FromUserName><![CDATA[" . $toUsername . "]]></FromUserName>
                <CreateTime>" . $time . "</CreateTime>
                <MsgType><![CDATA[news]]></MsgType>
                <ArticleCount>" . $count . "</ArticleCount>
                <Articles>";
        foreach ($data as $value) {
            $tmp = "
                <item>
                <Title><![CDATA[" . $value['title'] . "]]></Title> 
                <Description><![CDATA[" . $value['description'] . "]]></Description>
                <PicUrl><![CDATA[" . $value['picurl'] . "]]></PicUrl>
                <Url><![CDATA[" . $value['url'] . "]]></Url>
                </item>";
            $content = $content . $tmp;
        }
        $footer = "
                </Articles>
                </xml>";
        return $header . $content . $footer;
    }

    private function checkSignature() {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            LogWrite("签名验证成功！");
            return true;
        } else {
            LogWrite("签名验证失败！");
            return false;
        }
    }

}

?>