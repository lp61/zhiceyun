<?php

/**
 * Description of WeixinService
 *
 * @author vini
 */
class WeixinService extends Service {

    //put your code here
    protected $AppID;
    protected $AppSecret;

    public function __construct() {
        $this->init();
    }

    /**
     * 加载WeixinService, 初始化默认参数
     */
    public function init() {
        $this->AppID = 'wx853e30fa671758e0';
        $this->AppSecret = 'fd0ce8cc6de36ef771e2fc5f9b260876';
    }

    /**
     * 发送微信通知
     *
     * @param string $touser 接收人微信标志
     * @param string $type   消息类型
     * @param array  $sendInfo   模板信息内容 array('first'=>'', 'key1'=>'', 'key2'=>'', 'key3'=>'', ..., 'remark'=>'')
     * @return boolean
     */
    public function send_notice($touser, $type, $sendInfo) {
        include_once(SITE_PATH . '/addons/libs/WxPush.class.php');
        $template = $this->get_template($type, $sendInfo);
        $url = "";
        $template_id = $template['id'];
        $data = $template['content'];
        $wx = new WxPush($this->AppID, $this->AppSecret);
        return $wx->doSend($touser, $template_id, $url, $data, $topcolor = '#000000');
    }

    /*
     * **************************参数说明****************************
     * @param $type 
     * 1-成为会员通知 2-会员级别变更提醒 3-会员卡充值成功提醒
     * 4-积分变动通知 5-发票提醒 6-任务处理结果提醒
     * +--------------------------------------------------
     * 1、成为会员通知：$key1-$key5
      欢迎加入智测云会员中心
      昵称：亲爱的开发者
      手机：13012345678     空
      成为会员赠送积分：10
      会员级别：普通会员
      会员卡号：0425    空
      感谢您使用智测云！
     * +--------------------------------------------------
     * 2、会员级别变更提醒：$first, $key1-$key3
      亲爱的开发者，恭喜你成功升级为XXX！(企业用户)
      原先等级：普通用户
      当前等级：企业用户
      变更时间：2015年9月30日 17:58
      感谢您使用智测云！
     * +--------------------------------------------------
     * 3、会员卡充值成功提醒：$first, $key1-$key4
      您的智测云账号充值成功，当前可用积分XXX！
      充值金额：500元
      余额：800元   空
      享受折扣：无
      时间：2015-10-01 10:10
      感谢您使用智测云！
     * +--------------------------------------------------
     * 4、积分变动(积分消耗)通知：$first, $key1-$key5
      您好，您刚刚申请进行了兼容性测试，消费200积分，尚余510积分。
      用户名：XX
      时间：2015年7月21日 18:36
      积分变动：-200
      积分余额：510
      变动原因：兼容性测试
      感谢您使用智测云！
     * +---------------------------------------------------
     * 5、发票提醒：$key1-$key5
      您好，您的发票已开具成功。
      发票号码：144031539110
      开票日期：2015年7月31日
      开票企业：工业和信息化部电信研究院
      发票抬头：XX
      票面金额：30.47
      感谢您使用智测云！
     * +---------------------------------------------------
     * 6、任务处理结果提醒：$first, $key1-$key3
      您好，亲爱的开发者，您提交的测试任务已XX！ (任务 开始/完成 通知)
      任务ID：8128
      任务名：QQ音乐
      状态：完成
      您可通过点击下方“我的服务”随时查看测试状态！
     */

    public function get_template($type, $sendInfo) {
        $data_arrary = array(
            1 => array(
                'id' => 'CAoP6pde2WFYQ5Bu5UMqszqhk2MqkBekv_Y1bNuDG00',
                'content' => array(
                    'first' => array('value' => urlencode("欢迎加入智测云会员中心。"), 'color' => "#743A3A"),
                    'keyword1' => array('value' => urlencode($sendInfo['key1']), 'color' => '#000000'),
                    'keyword2' => array('value' => urlencode($sendInfo['key2']), 'color' => '#000000'),
                    'keyword3' => array('value' => urlencode($sendInfo['key3']), 'color' => '#000000'),
                    'keyword4' => array('value' => urlencode($sendInfo['key4']), 'color' => '#000000'),
                    'keyword5' => array('value' => urlencode($sendInfo['key5']), 'color' => '#000000'),
                    'remark' => array('value' => urlencode("感谢您使用智测云！"), 'color' => '#000000'),
                )
            ),
            2 => array(
                'id' => 'VOg2on8pRDtq3tCZvUbFB327K_YE5d3XTOYKBkL_GpQ',
                'content' => array(
                    'first' => array('value' => urlencode($sendInfo['first']), 'color' => "#743A3A"),
                    'grade1' => array('value' => urlencode($sendInfo['key1']), 'color' => '#000000'),
                    'grade2' => array('value' => urlencode($sendInfo['key2']), 'color' => '#000000'),
                    'time' => array('value' => urlencode($sendInfo['key3']), 'color' => '#000000'),
                    'remark' => array('value' => urlencode("感谢您使用智测云！"), 'color' => '#000000'),
                )
            ),
            3 => array(
                'id' => 'myKYnEOrcwwN9FaE3x52vp4hOGjz0a8MJCRnKdoOL5I',
                'content' => array(
                    'first' => array('value' => urlencode($sendInfo['first']), 'color' => "#743A3A"),
                    'keyword1' => array('value' => urlencode($sendInfo['key1']), 'color' => '#000000'),
                    'keyword2' => array('value' => urlencode($sendInfo['key2']), 'color' => '#000000'),
                    'keyword3' => array('value' => urlencode($sendInfo['key3']), 'color' => '#000000'),
                    'keyword4' => array('value' => urlencode($sendInfo['key4']), 'color' => '#000000'),
                    'remark' => array('value' => urlencode("感谢您使用智测云！"), 'color' => '#000000'),
                )
            ),
            4 => array(
                'id' => 'TYiKpoLgXV09BIvbsrkbxGdjZK0h65NbpjWWYiHG9KQ',
                'content' => array(
                    'first' => array('value' => urlencode($sendInfo['first']), 'color' => "#743A3A"),
                    'keyword1' => array('value' => urlencode($sendInfo['key1']), 'color' => '#000000'),
                    'keyword2' => array('value' => urlencode($sendInfo['key2']), 'color' => '#000000'),
                    'keyword3' => array('value' => urlencode($sendInfo['key3']), 'color' => '#000000'),
                    'keyword4' => array('value' => urlencode($sendInfo['key4']), 'color' => '#000000'),
                    'keyword5' => array('value' => urlencode($sendInfo['key5']), 'color' => '#000000'),
                    'remark' => array('value' => urlencode("感谢您使用智测云！"), 'color' => '#000000'),
                )
            ),
            5 => array(
                'id' => 'xKH8PqqUuIN-1x8vu9pq_EtBCmx_jz1H_acyZHvBRYM',
                'content' => array(
                    'first' => array('value' => urlencode("您好，您的发票已开具成功。"), 'color' => "#743A3A"),
                    'keyword1' => array('value' => urlencode($sendInfo['key1']), 'color' => '#000000'),
                    'keyword2' => array('value' => urlencode($sendInfo['key2']), 'color' => '#000000'),
                    'keyword3' => array('value' => urlencode($sendInfo['key3']), 'color' => '#000000'),
                    'keyword4' => array('value' => urlencode($sendInfo['key4']), 'color' => '#000000'),
                    'keyword5' => array('value' => urlencode($sendInfo['key5']), 'color' => '#000000'),
                    'remark' => array('value' => urlencode("感谢您使用智测云！"), 'color' => '#000000'),
                )
            ),
            6 => array(
                'id' => '49MofIp8Yxv1kSWeYnHirHFAbpzu6_LiwgmQDdkvmIg',
                'content' => array(
                    'first' => array('value' => urlencode($sendInfo['first']), 'color' => "#743A3A"),
                    'keyword1' => array('value' => urlencode($sendInfo['key1']), 'color' => '#000000'),
                    'keyword2' => array('value' => urlencode($sendInfo['key2']), 'color' => '#000000'),
                    'keyword3' => array('value' => urlencode($sendInfo['key3']), 'color' => '#000000'),
                    'remark' => array('value' => urlencode("您可通过点击下方“我的服务”随时查看测试状态！"), 'color' => '#000000'),
                )
            )
        );

        return $data_arrary[$type];
    }

    public function run() {
        
    }

    public function _start() {
        
    }

    public function _stop() {
        
    }

    public function _install() {
        
    }

    public function _uninstall() {
        
    }

}
