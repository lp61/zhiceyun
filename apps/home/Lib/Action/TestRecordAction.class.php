<?php

import('@.Form.AppTestVars');

class TestRecordAction extends Action {

    //测试记录
    public function index() {
        $model = M('app_test_task');
//        //删除记录
//        if ($_GET) {
//            $where['id'] = $_GET['id'];
//            if ($_GET['type'] == 1) { //type=1 删除
//                $saveData['user_id'] = $this->mid;
//                $saveData['status'] = -1;
//                $model->where($where)->save($saveData);
//            }
//        }

        $map['user_id'] = $this->mid;
        $map['status'] = 1;
        $listData = $model->where($map)->order('ctime DESC')->findPage();
        $this->assign($listData);
        $this->display();
    }

    function report() {
        $package_type = $_REQUEST['package_type'];
        if (in_array($package_type, array(2, 3, 4, 6, 7, 8))) {
            //兼容性测试报告
            $this->compatible();
        } elseif ($package_type == 1) {
            //快速测试报告
            $this->quick();
        } elseif ($package_type == 5) {
            //网络友好测试报告
            $this->network();
        }
    }

    //网络友好测试报告
    public function network() {
//        if (empty($_REQUEST['uuid'])) {
//            $_REQUEST['uuid'] = '78a21fd1-044e-5a29-0663-b8282e3b2e6c';
//        }
        $where['uuid'] = $uuid = $_REQUEST['uuid'];
        $model = M('app_test_task'); //查询基本信息
        $task = $model->where($where)->field("apk_name,apk_icon,package_type,ctime,mtime,package_type,device_type")->find();
        $task['package_type'] = "网络友好测试"; //测试业务名称
        $task['ctime'] = date("Y-m-d H:i:s", $task['ctime']); //任务提交时间
        $task['mtime'] = date("Y-m-d H:i:s", $task['mtime']); //任务结束时间
        $report_url = $task['report_url'];

        $this->assign('report_url', $report_url);
        $this->assign('task', $task);
        $this->assign('uuid', $uuid);
        $this->display('network');
    }

    //网络友好报告-综合测试
    public function network_complex() {
//        if (empty($_REQUEST['uuid'])) {
//            $_REQUEST['uuid'] = '78a21fd1-044e-5a29-0663-b8282e3b2e6c';
//        }
        $where['uuid'] = $uuid = $_REQUEST['uuid'];
        $model = M('apptest_result_network');
        $deviceList = $model->where($where)->field('device_type')->group('device_type')->order('id asc')->select();
        $this->assign('deviceList', $deviceList);
        //查询报告下载地址
        $report_url = M('app_test_task')->where($where)->getField('report_url');
        $this->assign('report_url', $report_url);

        $where['device_type'] = $device_type = empty($_REQUEST['device_type']) ? $deviceList[0]['device_type'] : $_REQUEST['device_type'];
        //根据id号和任务id查询测试信息
        $vo = $model->where($where)->field('casenum,pass,warning,fail,star,pass_details,warning_details,fail_details')->find();
        $pass_details_name = $this->qdl($vo['pass_details']);
        $warning_details_name = $this->qdl($vo['warning_details']);
        $fail_details_name = $this->qdl($vo['fail_details']);
        $this->assign('pass_details_name', $pass_details_name);
        $this->assign('warning_details_name', $warning_details_name);
        $this->assign('fail_details_name', $fail_details_name);

        $this->assign('vo', $vo);
        $this->assign('uuid', $uuid);
        $this->assign('device_type', $device_type);
        $this->display();
    }

    //网络友好报告-缓存统计
    public function network_cache() {
//        if (empty($_REQUEST['uuid'])) {
//            $_REQUEST['uuid'] = '78a21fd1-044e-5a29-0663-b8282e3b2e6c';
//        }
        $where['uuid'] = $uuid = $_REQUEST['uuid'];
        $model = M('apptest_result_network');
        $deviceList = $model->where($where)->field('device_type')->group('device_type')->order('id asc')->select();
        $this->assign('deviceList', $deviceList);
        //查询报告下载地址
        $report_url = M('app_test_task')->where($where)->getField('report_url');
        $this->assign('report_url', $report_url);

        $where['device_type'] = $device_type = empty($_REQUEST['device_type']) ? $deviceList[0]['device_type'] : $_REQUEST['device_type'];
        //根据id号和任务id查询测试信息
        $vo = $model->where($where)->find();

        $this->assign('vo', $vo);
        $this->assign('uuid', $uuid);
        $this->assign('device_type', $device_type);
        $this->display();
    }

    //网络友好报告-传输统计
    public function network_transfer() {
//        if (empty($_REQUEST['uuid'])) {
//            $_REQUEST['uuid'] = '78a21fd1-044e-5a29-0663-b8282e3b2e6c';
//        }
        $where['uuid'] = $uuid = $_REQUEST['uuid'];
        $model = M('apptest_result_network');
        $deviceList = $model->where($where)->field('device_type')->group('device_type')->order('id asc')->select();
        $this->assign('deviceList', $deviceList);
        //查询报告下载地址
        $report_url = M('app_test_task')->where($where)->getField('report_url');
        $this->assign('report_url', $report_url);

        $where['device_type'] = $device_type = empty($_REQUEST['device_type']) ? $deviceList[0]['device_type'] : $_REQUEST['device_type'];
        //根据id号和任务id查询测试信息
        $vo = $model->where($where)->find();

        $this->assign('vo', $vo);
        $this->assign('uuid', $uuid);
        $this->assign('device_type', $device_type);
        $this->display();
    }

    //网络友好-屏幕截图
    public function network_image() {
        $where['uuid'] = $uuid = $_REQUEST['uuid'];
        $model = M('apptest_result_network');
        $deviceList = $model->where($where)->field('device_type')->group('device_type')->order('id asc')->select();
        $this->assign('deviceList', $deviceList);
        //查询报告下载地址
        $report_url = M('app_test_task')->where($where)->getField('report_url');
        $this->assign('report_url', $report_url);
        $where['device_type'] = $device_type = empty($_REQUEST['device_type']) ? $deviceList[0]['device_type'] : $_REQUEST['device_type'];
        //根据uuid号和终端类型查询测试信息
        $map['fkid'] = $model->where($where)->getField('id');
        $listData = M('apptest_result_network_image')->where($map)->findPage(8);
        $this->assign($listData);
        $this->assign('uuid', $uuid);
        $this->assign('device_type', $device_type);
        $this->display();
    }

    //兼容性报告-基本信息
    public function compatible() {
//        if (empty($_REQUEST['uuid'])) {
//            $_REQUEST['uuid'] = '5c8a91fe-8485-34c9-e827-b2540d24c2ab';
//        }
        $where['uuid'] = $uuid = $_REQUEST['uuid'];
        $model = M('app_test_task');
        $vo = $model->where($where)->find();
        $vo['apk_size'] = number_format($vo['apk_size'] / 1024, 2, '.', '');
        $vo['status'] = "已完成";
        $vo['ctime'] = date("Y-m-d H:i:s", $vo['ctime']);
        $this->assign('business_title', $vo['businessID']); //测试包类型名
        $this->assign('vo', $vo);
        $this->assign('report_url', $vo['report_url']);
        $this->assign('uuid', $uuid);
        $this->display('compatible');
    }

    //兼容性报告-兼容概况
    public function compatible_comp() {
//        if (empty($_REQUEST['uuid'])) {
//            $_REQUEST['uuid'] = '5c8a91fe-8485-34c9-e827-b2540d24c2ab';
//        }
        $where['uuid'] = $uuid = $_REQUEST['uuid'];
        $model = M('apptest_result_comp');
        $vo = $model->where($where)->find();
        $mo = M('app_test_task')->where($where)->field('businessID,report_url')->find(); //测试包类型名       

        $this->assign('report_url', $mo['report_url']);
        $this->assign('business_title', $mo['businessID']);
        $this->assign('vo', $vo);
        $this->assign('uuid', $uuid);
        $this->display();
    }

    //兼容性报告-性能概况
    public function compatible_prop() {
//        if (empty($_REQUEST['uuid'])) {
//            $_REQUEST['uuid'] = '5c8a91fe-8485-34c9-e827-b2540d24c2ab';
//        }
        $where['uuid'] = $uuid = $_REQUEST['uuid'];
        $model = M('apptest_result_comp');
        $vo = $model->where($where)->find();
        $mo = M('app_test_task')->where($where)->field('businessID,report_url')->find(); //测试包类型名

        $this->assign('business_title', $mo['businessID']);
        $this->assign('report_url', $mo['report_url']);
        $this->assign('vo', $vo);
        $this->assign('uuid', $uuid);

        /*
          $mlist = explode(',', $vo['models']);
          if ($_REQUEST['model']) {
          $where['model'] = $_REQUEST['model']; //手机型号
          $listData = M('apptest_result_comp_device')->where($where)->order('mtime DESC')->findPage(8);
          } else {
          $listData = M('apptest_result_comp_device')->where($where)->order('mtime DESC')->findPage(8);
          }
          $this->assign($listData);
          $this->assign('mlist', $mlist);
         * 
         */
        $this->display();
    }

    //兼容性报告-终端列表
    public function compatible_list() {
//        if (empty($_REQUEST['uuid'])) {
//            $_REQUEST['uuid'] = '5c8a91fe-8485-34c9-e827-b2540d24c2ab';
//        }
        $where['uuid'] = $uuid = $_REQUEST['uuid'];
        $model = M('apptest_result_comp_device');
        $mlist = $model->where($where)->group('model')->select();
        if ($_REQUEST['model']) {
            $where['model'] = $_REQUEST['model']; //手机型号
        }
        $listData = M('apptest_result_comp_device')->where($where)->order('mtime DESC')->findPage(8);
        $mo = M('app_test_task')->where($where)->field('businessID,report_url')->find(); //测试包类型名
        $this->assign('business_title', $mo['businessID']);
        $this->assign('report_url', $mo['report_url']);
        $this->assign('uuid', $uuid);
        $this->assign('model', $_REQUEST['model']);
        $this->assign('mlist', $mlist);
        $this->assign($listData);
        $this->display();
    }

    //兼容性报告-终端详情
    public function compatible_detail() {
        $type = empty($_REQUEST['type']) ? 1 : $_REQUEST['type'];
        if (isset($_REQUEST['id'])) {
            $where['id'] = $_REQUEST['id'];
        } else {
            $where['uuid'] = $_REQUEST['uuid'];
            $where['device_type'] = $_REQUEST['device_type'];
        }

        $model = M('apptest_result_comp_device');
        $vo = $model->where($where)->find();
        $uuid = $vo['uuid'];
        $mo = M('app_test_task')->where("uuid='$uuid'")->field('businessID,report_url')->find(); //测试包类型名
        $deviceList = $model->where("uuid='$uuid'")->field('device_type')->group('device_type')->select();
        $this->assign('vo', $vo);
        $this->assign('deviceList', $deviceList);
        $this->assign('business_title', $mo['businessID']);
        $this->assign('report_url', $mo['report_url']);
        //type=3 LOG日志分析
        if ($type == 3) {
            $map['fkid'] = $vo['id'];
            if (!empty($_REQUEST['level'])) {
                $map['LogLevel'] = $_REQUEST['level'];
            }
            $model = M('apptest_xml_cache');
            $flag = $model->where($map)->find();
            //dump($model->getLastSql());
            if (empty($flag) && !empty($vo['log_url']) && !empty($map['LogLevel'])) {
                $xml_file = dirname(urldecode($vo['log_url'])) . "/logcatAnalysis.xml";
                $tmp_file = UPLOAD_PATH . "/" . $vo['uuid'] . "_" . $vo['device_type'] . ".xml";
                file_put_contents($tmp_file, file_get_contents($xml_file));
                $AnalyzeStatistics = simplexml_load_file($tmp_file);
                if (!empty($AnalyzeStatistics)) {
                    foreach ($AnalyzeStatistics->Logcats->Logcat as $value) {
                        $a = (array) $value;
                        $a['fkid'] = $vo['id'];
                        $model->add($a);
                    }
                    @unlink($tmp_file); //读出成功删除
                }
            }
            $listData = $model->where($map)->findPage(8);
            $arrs = $model->query("SELECT count(*) as count,ErrorType FROM `ts_apptest_xml_cache` WHERE fkid='" . $vo['id'] . "' GROUP BY ErrorType ORDER BY ErrorType ASC");
            if (!empty($arrs)) {
                $list = array(0, 0, 0, 0, 0, 0, 0);
                foreach ($arrs as $_one) {
                    $list[$_one['ErrorType']] = $_one['count'];
                }
                $this->assign('list', $list);
            }
            $this->assign($listData);
        }
        //type=4 屏幕截图
        if ($type == 4) {
            $map['fkid'] = $vo['id'];
            $model = M('apptest_result_image');
            $listData = $model->where($map)->findPage(8);
            $this->assign($listData);
        }

        $this->assign('uuid', $uuid);
        $this->assign('device_type', $vo['device_type']);
        $this->display('compatible_detail_' . $type);
    }

    //$source 数据源数组
    function qdl($source) {
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

    //导出pdf-测试报告概况
    public function report_summary() {
        if (empty($_REQUEST['uuid'])) {
            $_REQUEST['uuid'] = '5da2b2d1-8505-5211-630f-1c1a6b4737e7';
        }
        $where['uuid'] = $uuid = $_REQUEST['uuid'];
        $model = M('app_test_task');
        $task = $model->where($where)->find();
        $task['apk_size'] = number_format($task['apk_size'] / 1024, 2, '.', '');
        $task['status'] = "已完成";
        $task['ctime'] = date("Y-m-d H:i:s", $task['ctime']);
        $vo = M('apptest_result_comp')->where($where)->find();
        $mlist = M('apptest_result_comp_device')->where($where)->group('model')->select();
        $mobile = M('apptest_result_comp_device')->where($where)->order('mtime DESC')->select();
        $this->assign('model', '');
        $this->assign('task', $task);
        $this->assign('mlist', $mlist);
        $this->assign('mobile', $mobile);
        $this->assign('vo', $vo);
        $this->assign('uuid', $uuid);
        $this->display('report_summary');
    }

    public function report_detail() {
        if (isset($_REQUEST['id'])) {
            $where['id'] = $_REQUEST['id'];
        } else {
            $where['uuid'] = $_REQUEST['uuid'];
            $where['device_type'] = $_REQUEST['device_type'];
        }
        //1-基本信息
        $model = M('apptest_result_comp_device');
        $vo = $model->where($where)->find();
        $vo['mtime'] = date("Y-m-d H:i:s", $vo['mtime']);
        $this->assign('vo', $vo);

        //2-LOG日志
        $map3['fkid'] = $vo['id'];
        $xml_model = M('apptest_xml_cache');
        $flag = $xml_model->where($map3)->find();
        if (empty($flag) && !empty($vo['log_url'])) {
            $xml_file = dirname(urldecode($vo['log_url'])) . "/logcatAnalysis.xml";
            $tmp_file = UPLOAD_PATH . "/" . $vo['uuid'] . "_" . $vo['device_type'] . "_logcatAnalysis.xml";
            file_put_contents($tmp_file, file_get_contents($xml_file));
            $AnalyzeStatistics = simplexml_load_file($tmp_file);
            if (!empty($AnalyzeStatistics)) {
                foreach ($AnalyzeStatistics->Logcats->Logcat as $value) {
                    $a = (array) $value;
                    $a['fkid'] = $vo['id'];
                    $xml_model->add($a);
                }
                @unlink($tmp_file); //读出成功删除
            }
        }
        $xmllist = $xml_model->where($map3)->select();
        $arrs = $model->query("SELECT count(*) as count,ErrorType FROM `ts_apptest_xml_cache` WHERE fkid='" . $vo['id'] . "' GROUP BY ErrorType ORDER BY ErrorType ASC");
        if (!empty($arrs)) {
            $list = array(0, 0, 0, 0, 0, 0, 0);
            foreach ($arrs as $_one) {
                $list[$_one['ErrorType']] = $_one['count'];
            }
            $this->assign('list', $list);
        }
        $this->assign('xmllist', $xmllist);
        //4-屏幕截图
        $map1['fkid'] = $vo['id'];
        $images = M('apptest_result_image')->where($map1)->select();
        $this->assign('images', $images);
        $this->assign('uuid', $vo['uuid']);
        $this->assign('device_type', $vo['device_type']);
        $this->display('report_detail');
    }

    //兼容性测试报告下载
    public function download() {
        $uuid = $_GET['uuid'];
        if (empty($uuid)) {
            exit;
        }
        $file_pdf[] = $tmpPdf = UPLOAD_PATH . "/" . $uuid . "_basic.pdf";
        $url = SITE_URL . "/home/TestRecord/report_summary/" . $uuid;
        system("/usr/local/bin/wkhtmltopdf " . $url . " " . $tmpPdf);

        $tmp = M('apptest_result_comp_device')->where("uuid='$uuid'")->field('id,device_type')->group('device_type')->select();
        if ($tmp) {
            foreach ($tmp as $_one) {
                $_one['device_type'] = str_replace(' ', '-', $_one['device_type']);
                $file_pdf[] = $tmpPdf = UPLOAD_PATH . "/" . $uuid . "_" . $_one['device_type'] . "_detail.pdf";
                $url = SITE_URL . "/home/TestRecord/report_detail/" . $_one['id'];
                system("/usr/local/bin/wkhtmltopdf " . $url . " " . $tmpPdf);
            }
        }
        $zip = new ZipArchive();
        $file_zip = UPLOAD_PATH . "/" . $uuid . ".zip";
        if ($zip->open($file_zip, ZipArchive::OVERWRITE) === TRUE) {
            foreach ($file_pdf as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close(); //关闭处理的zip文件
        }
        foreach ($file_pdf as $file) {
            //@unlink($file); //删除缓存PDF文件
        }
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename=' . basename($file_zip)); //文件名   
        header("Content-Type: application/zip"); //zip格式的   
        header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件    
        header('Content-Length: ' . filesize($file_zip)); //告诉浏览器，文件大小   
        readfile($file_zip);
        @unlink($file_zip); //删除缓存zip文件
//        header('Content-type: application/pdf');
//        header("Content-Disposition: attachment; filename='jianli.pdf'");
    }

    //导出PDF-网络友好报告
    public function report_network() {
        if (isset($_REQUEST['id'])) {
            $where['id'] = $_REQUEST['id'];
        } else {
            $where['uuid'] = $_REQUEST['uuid'];
            $where['device_type'] = $_REQUEST['device_type'];
        }
        $model = M('apptest_result_network');
        $map = $model->where($where)->field('uuid')->find();
        $model1 = M('app_test_task'); //查询基本信息
        $task = $model1->where($map)->field("apk_name,apk_icon,package_type,ctime,mtime,package_type,device_type")->find();
        $task['package_type'] = "网络友好测试"; //测试业务名称
        $task['ctime'] = date("Y-m-d H:i:s", $task['ctime']); //任务提交时间
        $task['mtime'] = date("Y-m-d H:i:s", $task['mtime']); //任务结束时间 
        $this->assign('task', $task);
        $vo = M('apptest_result_network')->where($where)->find();
        $pass_details_name = $this->qdl($vo['pass_details']);
        $warning_details_name = $this->qdl($vo['warning_details']);
        $fail_details_name = $this->qdl($vo['fail_details']);
        $this->assign('pass_details_name', $pass_details_name);
        $this->assign('warning_details_name', $warning_details_name);
        $this->assign('fail_details_name', $fail_details_name);
        $this->assign('vo', $vo);
        $this->display();
    }

    //网络友好测试报告下载
    public function networkdownload() {
        $uuid = $_GET['uuid'];
        if (empty($uuid)) {
            exit;
        }
        $tmp = M('apptest_result_network')->where("uuid='$uuid'")->field('id,device_type')->group('device_type')->select();
        if ($tmp) {
            foreach ($tmp as $_one) {
                $_one['device_type'] = str_replace(' ', '-', $_one['device_type']);
                $file_pdf[] = $tmpPdf = UPLOAD_PATH . "/" . $uuid . "_" . $_one['device_type'] . "_network.pdf";
                $url = SITE_URL . "/home/TestRecord/report_network/" . $_one['id'];
                system("/usr/local/bin/wkhtmltopdf " . $url . " " . $tmpPdf);
            }
        }
        $zip = new ZipArchive();
        $file_zip = UPLOAD_PATH . "/" . $uuid . ".zip";
        if ($zip->open($file_zip, ZipArchive::OVERWRITE) === TRUE) {
            foreach ($file_pdf as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close(); //关闭处理的zip文件
        }
        foreach ($file_pdf as $file) {
            @unlink($file); //删除缓存PDF文件
        }
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename=' . basename($file_zip)); //文件名   
        header("Content-Type: application/zip"); //zip格式的   
        header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件    
        header('Content-Length: ' . filesize($file_zip)); //告诉浏览器，文件大小   
        readfile($file_zip);
        @unlink($file_zip); //删除缓存zip文件
//        header('Content-type: application/pdf');
//        header("Content-Disposition: attachment; filename='jianli.pdf'");
    }

    //快速测试报告
    public function quick() {
//        if (empty($_REQUEST['uuid'])) {
//            $_REQUEST['uuid'] = '5c8a91fe-8485-34c9-e827-b2540d24c2ab';
//        }
        $where['uuid'] = $uuid = $_REQUEST['uuid'];
        $model = M('app_test_task');
        $task = $model->where($where)->find();
        $task['apk_size'] = number_format($task['apk_size'] / 1024, 2, '.', '');
        $task['status'] = "已完成";
        $task['ctime'] = date("Y-m-d H:i:s", $task['ctime']);
        $this->assign('report_url', $task['report_url']);
        $this->assign('task', $task);
        $this->assign('uuid', $uuid);
        $this->display('quick');
    }

    public function quick_compatible() {
//        if (empty($_REQUEST['uuid'])) {
//            $_REQUEST['uuid'] = '5c8a91fe-8485-34c9-e827-b2540d24c2ab';
//        }
        $where['uuid'] = $uuid = $_REQUEST['uuid'];
        $model = M('apptest_result_comp_device');
        $task = $model->where($where)->field('device_type,install_result,run_result,boot_time')->order('device_type asc')->select();
        //查询报告下载地址
        $report_url = M('app_test_task')->where($where)->getField('report_url');
        $this->assign('report_url', $report_url);
        $this->assign('task', $task);
        $this->assign('uuid', $uuid);
        $this->display();
    }

    public function quick_prop() {
//        if (empty($_REQUEST['uuid'])) {
//            $_REQUEST['uuid'] = '5c8a91fe-8485-34c9-e827-b2540d24c2ab';
//        }
        $where['uuid'] = $uuid = $_REQUEST['uuid'];
        $model = M('apptest_result_comp_device');
        $devices = $model->where($where)->group('device_type')->order('device_type asc')->select();
        $this->assign('devices', $devices);
        //查询报告下载地址
        $report_url = M('app_test_task')->where($where)->getField('report_url');
        $this->assign('report_url', $report_url);
        $device_type = empty($_REQUEST['device_type']) ? $devices[0]['device_type'] : $_REQUEST['device_type']; //手机型号
        $where['device_type'] = $device_type;
        $task = $model->where($where)->find();
        //最大内存 最大CPU
        $max_memory = $task['memory_usage'] == '--' ? '--' : number_format(max(json_decode($task['memory_diagram'])), 2);
        $max_cpu = $task['cpu_usage'] == '--' ? '--' : number_format(max(json_decode($task['cpu_diagram'])), 2);
        $this->assign('max_memory', $max_memory);
        $this->assign('max_cpu', $max_cpu);
        $this->assign('task', $task);
        $this->assign('uuid', $uuid);
        $this->assign('device_type', $device_type);
        $this->display();
    }

    public function quick_image() {
//        if (empty($_REQUEST['uuid'])) {
//            $_REQUEST['uuid'] = '01339563-628c-e99d-4f3e-8d0e3cf2dc4e';
//        }
        $where['uuid'] = $uuid = $_REQUEST['uuid'];
        $model = M('apptest_result_comp_device');
        $devices = $model->where($where)->group('device_type')->order('device_type asc')->select();
        $this->assign('devices', $devices);
        //查询报告下载地址
        $report_url = M('app_test_task')->where($where)->getField('report_url');
        $this->assign('report_url', $report_url);
        $device_type = empty($_REQUEST['device_type']) ? $devices[0]['device_type'] : $_REQUEST['device_type'];
        $where['device_type'] = $device_type;
        $map['fkid'] = M('apptest_result_comp_device')->where($where)->getField('id');
        $listData = M('apptest_result_image')->where($map)->findPage(8);
        $this->assign($listData);
        $this->assign('uuid', $uuid);
        $this->assign('device_type', $device_type);
        $this->display();
    }

    public function quick_network() {
//        if (empty($_REQUEST['uuid'])) {
//            $_REQUEST['uuid'] = '78a21fd1-044e-5a29-0663-b8282e3b2e6c';
//        }
        $where['uuid'] = $uuid = $_REQUEST['uuid'];
        $model = M('apptest_result_network');
        $devices = $model->query("SELECT  `device_type` FROM `ts_apptest_result_network` where `uuid`='" . $uuid . "'GROUP BY device_type ORDER BY device_type ASC");
        $this->assign('devices', $devices);
        if (empty($_REQUEST['device_type'])) {
            $device_type = $devices[0]['device_type'];
        } else {
            $device_type = $_REQUEST['device_type'];
        }
        //获取应用名
        $apk = M('app_test_task')->where($where)->field('apk_name,mtime,stime,report_url')->find();

        $this->assign('report_url', $apk['report_url']);
        $time = getCostTime($apk['stime'], $apk['mtime']);
        $where['device_type'] = $device_type;
        //根据id号和任务id查询测试信息
        $vo = $model->where($where)->field('device_type,casenum,pass,warning,fail,star,pass_details,warning_details,fail_details')->find();
        $pass_details_name = $this->qdl($vo['pass_details']);
        $warning_details_name = $this->qdl($vo['warning_details']);
        $fail_details_name = $this->qdl($vo['fail_details']);
        //获取网络环境
        $net = M('apptest_result_comp_device')->where($where)->getField('net');
        $this->assign('time', $time);
        $this->assign('apkname', $apk['apk_name']);
        $this->assign('net', $net);
        $this->assign('device_type', $device_type);
        $this->assign('pass_details_name', $pass_details_name);
        $this->assign('warning_details_name', $warning_details_name);
        $this->assign('fail_details_name', $fail_details_name);
        $this->assign('uuid', $uuid);
        $this->assign('vo', $vo);
        $this->display();
    }

    //快速测试-报告评分
    public function quick_score() {
        $this->display();
    }

    //快速测试报告-基本信息
    public function report_quick() {
        $where['uuid'] = $uuid = $_REQUEST['uuid'];
        $model = M('app_test_task');
        $task = $model->where($where)->find();
        $task['apk_size'] = number_format($task['apk_size'] / 1024, 2, '.', '');
        $task['status'] = "已完成";
        $task['ctime'] = date("Y-m-d H:i:s", $task['ctime']);
        $tasks = M('apptest_result_comp_device')->where($where)->field('device_type,install_result,run_result,boot_time')->select();
        $this->assign('task', $task);
        $this->assign('tasks', $tasks);
        $this->assign('uuid', $uuid);
        $this->display();
    }

    //快速测试报告-详细信息    
    public function report_quick_detail() {
        if (isset($_REQUEST['id'])) {
            $maps['id'] = $_REQUEST['id'];
        } else {
            $maps['uuid'] = $_REQUEST['uuid'];
            $maps['device_type'] = $_REQUEST['device_type'];
        }
        $model = M('apptest_result_comp_device');
        $mo = $model->where($maps)->find();
        $device_type = $mo['device_type'];
        $where['uuid'] = $uuid = $mo['uuid'];
        //网络友好-获取apk名
        $apk = M('app_test_task')->where($where)->field('apk_name,mtime,stime')->find();
        $time = getCostTime($apk['stime'], $apk['mtime']);
        $map_net['device_type'] = $where['device_type'] = $device_type;
        $task = $model->where($where)->find();
        //根据id号和任务id查询测试信息
        $vo = M('apptest_result_network')->where($map_net)->field('device_type,casenum,pass,warning,fail,star,pass_details,warning_details,fail_details')->find();
        $pass_details_name = $this->qdl($vo['pass_details']);
        $warning_details_name = $this->qdl($vo['warning_details']);
        $fail_details_name = $this->qdl($vo['fail_details']);
        //最大内存 最大CPU
        $max_memory = number_format(max(json_decode($task['memory_diagram'])), 2);
        $max_cpu = number_format(max(json_decode($task['cpu_diagram'])), 2);
        $map['fkid'] = M('apptest_result_comp_device')->where($where)->getField('id');
        $listData = M('apptest_result_image')->where($map)->findPage(8);
        $net = M('apptest_result_comp_device')->where($map_net)->getField('net');
        $this->assign('device_type', $device_type);
        $this->assign('uuid', $uuid);
        $this->assign('time', $time);
        $this->assign('apkname', $apk['apk_name']);
        $this->assign('net', $net);
        $this->assign($listData);
        $this->assign('max_memory', $max_memory);
        $this->assign('max_cpu', $max_cpu);
        $this->assign('task', $task);
        $this->assign('pass_details_name', $pass_details_name);
        $this->assign('warning_details_name', $warning_details_name);
        $this->assign('fail_details_name', $fail_details_name);
        $this->assign('vo', $vo);
        $this->display();
    }

    //快速测试报告下载
    public function quick_download() {
        $uuid = $_GET['uuid'];
        if (empty($uuid)) {
            exit;
        }
        $file_pdf[] = $tmpPdf = UPLOAD_PATH . "/" . $uuid . "_basic.pdf";
        $url = SITE_URL . "/home/TestRecord/report_quick/" . $uuid;
        system("/usr/local/bin/wkhtmltopdf " . $url . " " . $tmpPdf);
        $tmp = M('apptest_result_comp_device')->where("uuid='$uuid'")->field('id,device_type')->group('device_type')->select();
        if ($tmp) {
            foreach ($tmp as $_one) {
                $_one['device_type'] = str_replace(' ', '-', $_one['device_type']);
                $file_pdf[] = $tmpPdf = UPLOAD_PATH . "/" . $uuid . "_" . $_one['device_type'] . "_detail.pdf";
                $url = SITE_URL . "/home/TestRecord/report_quick_detail/" . $_one['id'];
                system("/usr/local/bin/wkhtmltopdf " . $url . " " . $tmpPdf);
            }
        }
        $zip = new ZipArchive();
        $file_zip = UPLOAD_PATH . "/" . $uuid . ".zip";
        if ($zip->open($file_zip, ZipArchive::OVERWRITE) === TRUE) {
            foreach ($file_pdf as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close(); //关闭处理的zip文件
        }
        foreach ($file_pdf as $file) {
            //@unlink($file); //删除缓存PDF文件
        }
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename=' . basename($file_zip)); //文件名   
        header("Content-Type: application/zip"); //zip格式的   
        header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件    
        header('Content-Length: ' . filesize($file_zip)); //告诉浏览器，文件大小   
        readfile($file_zip);
        @unlink($file_zip); //删除缓存zip文件
    }

}
