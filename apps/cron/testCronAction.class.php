<?php

class testCronAction {

    /**
     * [run 队列发送邮件]
     * @return [type] [建议1分钟 执行一次]
     * 执行命令：cron_index.php test\run
     */
    public function run() {
        $map['send_status'] = 0;
        $map['send_type'] = 2;
        $list = M('app_email_send')->where($map)->limit(50)->findAll();

        if ($list) {
            foreach ($list as $key => $_one) {
                if (empty($_one['email']) || empty($_one['subject'])) {
                    model('AppEmailSend')->setSendStatus($_one['id'], -1);
                    continue;
                }
                $email_sent = service('Mail')->send_email($_one['email'], $_one['subject'], $_one['content']);

                if ($email_sent) {
                    model('AppEmailSend')->setSendStatus($_one['id']);
                } else {
                    model('AppEmailSend')->setSendStatus($_one['id'], -1);
                }
                sleep(1);
            }
        }
    }

    /*
     * apk图标恢复
     */

    public function resume() {
        $list = M('app_test_task')->where("id>2610 AND id<=14427 AND attach_id>1000")->field('apk_url,attach_id,apkurl')->group('attach_id')->order('attach_id')->select();
        echo count($list) . PHP_EOL;
        foreach ($list as $_one) {
            $info = M('attach')->where("id='" . $_one['attach_id'] . "'")->find();
            //$info = M('attach')->where("id='148'")->find();
            //$filename = basename($_one['apk_url']); //文件名
            $filename = $info['savename'];
            //$n1 = dirname($_one['apk_url']);
            //$n2 = str_replace('http://42.96.171.6', '/data/zhiceyun/webroot', $n1);
            //$filepath = $n2 == $n1 ? str_replace('http://new.smarterapps.cn', '/data/zhiceyun/webroot/zhiceyun', $n2) : $n2;
            $filepath = UPLOAD_PATH . "/" . $info['savepath'];
            $this->CreateAllDir($filepath);
            $apkfile = $filepath . $filename;

            $iconname = basename($info['apk_icon']);
            $apkicon = $filepath . $iconname;
            if (!file_exists($apkfile)) {
                //echo $apkfile . ' not exits!' . PHP_EOL;
            }
            if (!file_exists($apkicon)) {
//                @unlink($apkfile);
//                @unlink($apkicon);
                echo $apkicon . ' not exits!' . PHP_EOL;
                echo $_one['attach_id'] . '   ' . $apkfile . PHP_EOL;
                echo 'file downloading...   ' . PHP_EOL;
                file_put_contents($apkfile, file_get_contents($_one['apkurl']));
                if (filesize($apkfile) == 0) {
                    echo 'downloading error!' . $_one['apkurl'] . PHP_EOL;
                    continue;
                }

                echo file_exists($apkicon) . 'icon creating... \t ' . PHP_EOL . PHP_EOL;
                $this->getApkInfo($apkfile, $apkicon);
//                exit();
            }
        }
    }

    function CreateAllDir($dir) {
        $dir_array = explode("/", $dir);
        $DirName = "";
        for ($i = 0; $i < count($dir_array); $i++) {
            if ($dir_array[$i] != "") {
                $DirName .= "/" . $dir_array[$i];
                if (!is_dir($DirName)) {
                    mkdir($DirName, 0755);
                }
            }
        }
    }

    //读取apk包信息：应用图标、应用名称、应用版本、应用大小
    /*
     * $tmp_array[0] = package: name='com.jiuzhi.yuanpuapp' versionCode='1' versionName='1.0'
     * $tmp_array[1] = application: label='缘谱' icon='res/drawable-hdpi/logo1.png'
     * $tmp_array[2] = launchable activity name='com.jiuzhi.yuanpuapp.SplashAct'label='' icon=''
     * $tmp_array[3] = 11872 /data/zhiceyun/webroot/test/YuanPuApp.apk
     */
    public function getApkInfo($apkfile, $apkicon) {
        //dump($apkfile);
        //读取apk信息到数组中
        $tmp_array = array();
        exec("aapt d badging $apkfile|egrep -i 'versionName|label|icon';du -s $apkfile ", $tmp_array);
        echo $tmp_array[3] . PHP_EOL;

        $start1 = strpos($tmp_array[1], "label='") + strlen("label='");
        $len1 = strpos($tmp_array[1], "' icon=") - $start1;
        $map['apk_name'] = substr($tmp_array[1], $start1, $len1);
        $data['apk_name'] = $map['apk_name'] ? $map['apk_name'] : '';

        $start2 = strpos($tmp_array[1], "icon='") + strlen("icon='");
        $len2 = strrpos($tmp_array[1], "'") - $start2;
        $map['apk_icon'] = substr($tmp_array[1], $start2, $len2);

        $start3 = strpos($tmp_array[0], "versionName='") + strlen("versionName='");
        $len3 = strrpos($tmp_array[0], "'") - $start3;
        $map['apk_version'] = substr($tmp_array[0], $start3, $len3);
        $data['apk_version'] = $map['apk_version'] ? $map['apk_version'] : '';

        $map['apk_size'] = filesize($apkfile); //apk大小（bytes）
        $data['apk_size'] = $map['apk_size'] ? $map['apk_size'] : '0';

        $icon_apk_path = $map['apk_icon']; //apk图片在zip包中的icon路径
        //$map['apk_icon'] = $savepath . uniqid() . "." . substr(strrchr($icon_apk_path, '.'), 1);
        //$map['apk_icon'] = $apkicon;
        //$data['apk_icon'] = $map['apk_icon'] ? $map['apk_icon'] : '';
        //dump($map);
        echo $icon_apk_path . PHP_EOL;
        $icon_path = $apkicon; //apk图片保存绝对路径
        $file = tempnam("/tmp", "zip"); // 生成临时文件
        file_put_contents($file, file_get_contents($apkfile));
        $zip = new ZipArchive;
        $zip->open($file, ZIPARCHIVE::CREATE);
        file_put_contents($icon_path, $zip->getFromName($icon_apk_path));
        $zip->close();
        @unlink($file);

        return $data;
    }
    
    //删除终端配置表中已经失效的终端配置信息
    public function update_appConfig() {
        $model = M();
        $model->query("delete from ts_app_config where `type`=1 and content not in (select `model` from ts_app_client group by `model`)");
        $model->query("delete from ts_app_config where `type`=2 and content not in (select `os` from ts_app_client group by `model`)");
        $model->query("delete from ts_app_config where `type`=3 and content not in (select `size` from ts_app_client group by `model`)");
        $model->query("delete from ts_app_config where `type`=4 and content not in (select `resolution` from ts_app_client group by `model`)");
        echo "seccess";
    }

}