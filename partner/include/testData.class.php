<?php

/**
 * Description of testData
 *
 * @author vini
 */
class testData {

    public $db; //数据库连接
    public $uid; //用户ID
    public $uuid; //任务ID
    
    //错误处理
    public $errCode;
    public $errMsg;

    public function __construct($db, $uid, $uuid) {
        $this->db = $db;
        $this->uid = $uid;
        $this->uuid = $uuid;
    }

    public function getData() {
        $sql = "SELECT  `id`,`package_type`  FROM  ts_app_test_task  WHERE  user_id='" . $this->uid . "' AND uuid='" . $this->uuid . "'";
        $row = $this->db->get_one($sql);
        if (!$row) {
            $this->errCode = '1';
            $this->errMsg = '用户ID或任务ID有误！';
            return false;
        }
        if (in_array($row['package_type'], array(2, 3, 4, 7, 8))) {
            //兼容性测试结果
            $data = $this->getCompData();
        } elseif ($row['package_type'] == 5) {
            //网络友好测试结果
            $data = $this->getNetworkData();
        } elseif ($row['package_type'] == 1) {
            //快速测试结果
            $data = $this->getQuickData();
        } else {
            $this->errCode = '2';
            $this->errMsg = '测试包类型错误！';
            return false;
        }

        return $data;
    }

    protected function getCompData($uuid = '') {
        if ($uuid == '') {
            $uuid = $this->uuid;
        }

        //所有测试终端的结果详情
        $sql1 = "SELECT  *  FROM  `ts_apptest_result_comp_device`  WHERE  uuid='" . $uuid . "'";
        $data = $this->db->get_array($sql1);

        return $data;
    }

    protected function getNetworkData($uuid = '') {
        if ($uuid == '') {
            $uuid = $this->uuid;
        }

        //所有测试终端的结果详情
        $sql1 = "SELECT  *  FROM  `ts_apptest_result_network`  WHERE  uuid='" . $uuid . "'";
        $data = $this->db->get_array($sql1);
        if ($data) {
            foreach ($data as $key => $_one) {
                $data[$key]['pass_details'] = $this->qdl($_one['pass_details']);
                $data[$key]['warning_details'] = $this->qdl($_one['warning_details']);
                $data[$key]['fail_details'] = $this->qdl($_one['fail_details']);
            }
        }

        return $data;
    }

    protected function getQuickData($uuid = '') {
        if ($uuid == '') {
            $uuid = $this->uuid;
        }

        return array();
    }

    //$source 数据源数组
    protected function qdl($source) {
        if (empty($source)) {
            return array();
        }
        $rule = array(
            'bp1_detail' => array(
                'title' => '文件压缩',
                'content' => '检测到%s个超过850字节的未压缩文本文件'
            ),
            'bp2_detail' => array(
                'title' => '重复内容',
                'content' => '检测到TCP内容重复率为%s%%'
            ),
            'bp3_detail' => array(
                'title' => '缓存控制',
                'content' => '%s%%的文件没有缓存头部信息'
            ),
            'bp4_detail' => array(
                'title' => '过期缓存',
                'content' => '重复下载的未过期缓存文件数为%s'
            ),
            'bp5_detail' => array(
                'title' => '预下载机制',
                'content' => 'N/A'                   //没任何值
            ),
            'bp6_detail' => array(
                'title' => '合并JS\CSS请求',
                'content' => '无效CSS和JS请求数为%s'
            ),
            'bp7_detail' => array(
                'title' => '图片调校',
                'content' => '图片尺寸大于指定区域的个数为%s'
            ),
            'bp8_detail' => array(
                'title' => '信息精简',
                'content' => '检测到%s个需要精简的文件'
            ),
            'bp9_detail' => array(
                'title' => '图片组合',
                'content' => '未用Sprite的图片组为%s'
            ),
            'bp10_detail' => array(
                'title' => '建立连接次数',
                'content' => 'N/A'                 //没任何值
            ),
            'bp11_detail' => array(
                'title' => '并发TCP连接',
                'content' => '检测到%s个同时段并发连接'
            ),
            'bp12_detail' => array(
                'title' => '并发周期性连接',
                'content' => '检测到%s个周期性连接'
            ),
            'bp13_detail' => array(
                'title' => '屏幕旋转',
                'content' => '检测到%s个屏幕旋转'
            ),
            'bp14_detail' => array(
                'title' => '关闭连接',
                'content' => '传输结束仍有%s焦耳的电量用于控制这些连接' //有小数
            ),
            'bp15_detail' => array(
                'title' => 'WiFi网络使用',
                'content' => '%s个非wifi条件下突发下载数据'
            ),
            'bp16_detail' => array(
                'title' => '400, 500 HTTP响应状态码',
                'content' => '发生%s个HTTP 404错误'
            ),
            'bp17_detail' => array(
                'title' => '301, 302 HTTP响应状态码',
                'content' => '发生%s个HTTP 301错误'
            ),
            'bp18_detail' => array(
                'title' => '第三方脚本使用',
                'content' => '使用了%s个第三方脚本'
            ),
            'bp19_detail' => array(
                'title' => 'JavaScript异步加载',
                'content' => 'HEAD部分的JS采用同步加载的HTML文件数为%s'
            ),
            'bp20_detail' => array(
                'title' => 'HTTP1.0使用',
                'content' => '头部使用HTTP1.0的文件数为%s',
            ),
            'bp21_detail' => array(
                'title' => '合脚本加载顺序',
                'content' => '在头部先加载JS脚本再加载CSS脚本的文件数为%s'
            ),
            'bp22_detail' => array(
                'title' => '空属性',
                'content' => '有%s个空的HTML标签文件'
            ),
            'bp23_detail' => array(
                'title' => 'Flash文件使用',
                'content' => '%s个使用Flash文件'
            ),
            'bp24_detail' => array(
                'title' => 'DisplayNone使用',
                'content' => '有%s个文件在CSS脚本中使用“display: none”'
            ),
            'bp25_detail' => array(
                'title' => '外设使用',
                'content' => '检测到%s%%GPS使用、%s%%蓝牙使用、%s%%照相机使用'
            )
        );
        $arrayname = json_decode($source, TRUE);
        foreach ($arrayname as $_one) {
            if ($_one['name'] == 'bp25_detail') {
                $a['title'] = $rule['bp25_detail']['title'];
                $format = $rule['bp25_detail']['content'];
                $a['content'] = sprintf($format, $_one['value1'], $_one['value2'], $_one['value3']);
            } else {
                $a['title'] = $rule[$_one['name']]['title'];
                $format = $rule[$_one['name']]['content'];
                $a['content'] = sprintf($format, $_one['value']);
            }
            $result[] = $a;
        }
        return $result;
    }

}
