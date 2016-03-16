<?php

/*
 * ARP测试数据结果分析
 * +-------------------------
 * $db 数据库连接
 * $p_uuid 任务UUID
 * $type 测试业务类型  1-快速测试 3-网络友好测试
 */

function analysis_net_one($db, $p_uuid, $type) {
    $uuid = substr($p_uuid, 0, 36);
    //$list = $db->Query("DELETE  FROM  `ts_apptest_result_network`  WHERE  uuid='$uuid'");
    $table = $type == 1 ? 'arotest5' : 'arotest4';
    require_once '../include/dbinc.net.php'; //$sdb-结果数据库连接
    $sql = "SELECT * FROM " . $table . " WHERE uuid='$p_uuid'";
    $row = $sdb->get_array($sql);
    LogWrite('anay_net_' . $uuid, $sql, json_encode($row), LOG_ANALYSIS_PATH);
    foreach ($row as $v) {
        $data['uuid'] = $uuid;
        $data['device_type'] = $device_type = $v['mobile'];

        //查询同一任务下，如果已有该终端型号的结果，则跳过此结果记录（避免同一任务的结果中有重复终端）
        $sql = "SELECT  `id`  FROM  `ts_apptest_result_network`  WHERE  uuid='$uuid' AND `device_type`='$device_type' LIMIT 1";
        $list = $db->get_one($sql);
        if (!empty($list)) {
            LogWrite('anay_net_' . $uuid, $v['mobile'], json_encode($v), LOG_ANALYSIS_PATH);
            continue;
        }

        $data['pass'] = $v['pass'];
        $data['warning'] = $v['warning'];
        $data['casenum'] = "25";
        $data['fail'] = $v['fail'];
        $data['star'] = $v['star'] . "颗星";
        $pass_details = $warning_details = $fail_details = array();
        for ($i = 1; $i <= 25; $i++) {
            $re = "bp" . $i . "_result";
            $str = "bp" . $i . "_detail";
            $arr = explode(" ", $v[$str]);
            $a['name'] = $str;
            if ($i != 25) {
                $a['value'] = $arr[0];
            } else {
                $a['value1'] = $arr[0];
                $a['value2'] = $arr[3];
                $a['value3'] = $arr[6];
            }
            if ($v[$re] == 'PASS') {
                $pass_details[] = $a;
            } elseif ($v[$re] == 'Warning') {
                $warning_details[] = $a;
            } elseif ($v[$re] == 'FAIL') {
                $fail_details[] = $a;
            }
        }
        $data['pass_details'] = json_encode($pass_details);
        $data['warning_details'] = json_encode($warning_details);
        $data['fail_details'] = json_encode($fail_details);
        if ($type == 3) { //网络友好测试记过独有字段
            $data['TcpControl_Bytes'] = $v['TcpControl_Bytes'];
            $data['TcpControl_BytesPercent'] = $v['TcpControl_BytesPercent'];
            $data['TcpControl_Energy'] = $v['TcpControl_Energy'];
            $data['TcpControl_EnergyPercent'] = $v['TcpControl_Bytes_EnergyPercent'];
            $data['TcpLossRecoverOrDup_Bytes'] = $v['TcpLossRecoverOrDup_Bytes'];
            $data['TcpLossRecoverOrDup_BytesPercent'] = $v['TcpLossRecoverOrDup_BytesPercent'];
            $data['TcpLossRecoverOrDup_Energy'] = $v['TcpLossRecoverOrDup_Energy'];
            $data['TcpLossRecoverOrDup_EnergyPercent'] = $v['TcpLossRecoverOrDup_EnergyPercent'];
            $data['UserInput_Bytes'] = $v['UserInput_Bytes'];
            $data['UserInput_BytesPercent'] = $v['UserInput_BytesPercent'];
            $data['UserInput_Energy'] = $v['UserInput_Energy'];
            $data['UserInput_EnergyPercent'] = $v['UserInput_EnergyPercent'];
            $data['ScreenRotation_Bytes'] = $v['ScreenRotation_Bytes'];
            $data['ScreenRotation_BytesPercent'] = $v['ScreenRotation_BytesPercent'];
            $data['ScreenRotation_Energy'] = $v['ScreenRotation_Energy'];
            $data['ScreenRotation_EnergyPercent'] = $v['ScreenRotation_EnergyPercent'];
            $data['App_Bytes'] = $v['App_Bytes'];
            $data['App_BytesPercent'] = $v['App_BytesPercent'];
            $data['App_Energy'] = $v['App_Energy'];
            $data['App_EnergyPercent'] = $v['App_EnergyPercent'];
            $data['ServerNetDelay_Bytes'] = $v['ServerNetDelay_Bytes'];
            $data['ServerNetDelay_BytesPercent'] = $v['ServerNetDelay_BytesPercent'];
            $data['ServerNetDelay_Energy'] = $v['ServerNetDelay_Energy'];
            $data['ServerNetDelay_EnergyPercent'] = $v['ServerNetDelay_EnergyPercent'];
            $data['LargeBurst_Bytes'] = $v['LargeBurst_Bytes'];
            $data['LargeBurst_BytesPercent'] = $v['LargeBurst_BytesPercent'];
            $data['LargeBurst_Energy'] = $v['LargeBurst_Energy'];
            $data['LargeBurst_EnergyPercent'] = $v['LargeBurst_EnergyPercent'];
            $data['Periodical_Bytes'] = $v['Periodical_Bytes'];
            $data['Periodical_BytesPercent'] = $v['Periodical_BytesPercent'];
            $data['Periodical_Energy'] = $v['Periodical_Energy'];
            $data['Periodical_EnergyPercent'] = $v['Periodical_EnergyPercent'];
            $data['Cacheable_FilePercent'] = $v['Cacheable_FilePercent'];
            $data['Cacheable_BytesPercent'] = $v['Cacheable_BytesPercent'];
            $data['NoStore_FilePercent_2'] = $v['NoStore_FilePercent_2'];
            $data['NoStore_BytesPercent_2'] = $v['NoStore_BytesPercent_2'];
            $data['Once_FilePercent'] = $v['Once_FilePercent'];
            $data['Once_BytesPercent'] = $v['Once_BytesPercent'];
            $data['NoStore_FilePercent_1'] = $v['NoStore_FilePercent_1'];
            $data['NoStore_BytesPercent_1'] = $v['NoStore_BytesPercent_1'];
            $data['Expired304_FilePercent'] = $v['Expired304_FilePercent'];
            $data['Expired304_BytesPercent'] = $v['Expired304_BytesPercent'];
            $data['ExpiredChanged_FilePercent'] = $v['ExpiredChanged_FilePercent'];
            $data['ExpiredChanged_BytesPercent'] = $v['ExpiredChanged_BytesPercent'];
            $data['Dup_FilePercent'] = $v['Dup_FilePercent'];
            $data['Dup_BytesPercent'] = $v['Dup_BytesPercent'];
            $data['NoHeader_FilePercent'] = $v['NoHeader_FilePercent'];
            $data['NoHeader_BytesPercent'] = $v['NoHeader_BytesPercent'];
            $data['HeaderIgnored_FilePercent'] = $v['HeaderIgnored_FilePercent'];
            $data['HeaderIgnored_BytesPercent'] = $v['HeaderIgnored_BytesPercent'];
            $data['PDup_FilePercent'] = $v['PDup_FilePercent'];
            $data['PDup_BytesPercent'] = $v['PDup_BytesPercent'];
            $data['PNoHeader_FilePercent'] = $v['PNoHeader_FilePercent'];
            $data['PNoHeader_BytesPercent'] = $v['PNoHeader_BytesPercent'];
            $data['PHeaderIgnored_FilePercent'] = $v['PHeaderIgnored_FilePercent'];
            $data['PHeaderIgnored_BytesPercent'] = $v['PHeaderIgnored_BytesPercent'];
            $data['DownloadNoExp_FilePercent'] = $v['DownloadNoExp_FilePercent'];
            $data['DownloadNoExp_BytesPercent'] = $v['DownloadNoExp_BytesPercent'];
            $data['Download24hNoExp_FilePercent'] = $v['Download24hNoExp_FilePercent'];
            $data['Download24hNoExp_BytesPercent'] = $v['Download24hNoExp_BytesPercent'];
            $data['DownloadExp_FilePercent'] = $v['DownloadExp_FilePercent'];
            $data['DownloadExp_BytesPercent'] = $v['DownloadExp_BytesPercent'];
            $data['Download24hExp_FilePercent'] = $v['Download24hExp_FilePercent'];
            $data['Download24hExp_BytesPercent'] = $v['Download24hExp_BytesPercent'];
        }

        //插入结果记录表
        $res = add($db, $data, 'apptest_result_network');
        //保存截图
        save_picture($db, $p_uuid, $device_type, $res);
    }
}

/*
 * 存储截图
 * $p_uuid    任务ID
 * $device_type     终端类型
 * $res     fkid
 */

function save_picture($db, $p_uuid, $device_type, $res) {
    $uuid = substr($p_uuid, 0, 36);
    require_once '../include/ftp.class.php';
    //连接FTP
    $ftp_config = array(
        'hostname' => '10.1.10.118',
        'username' => 'taikai',
        'password' => 'TK@tech1717#',
        'port' => 21
    );
    $ftp = new Ftp();
    $ftp_link = $ftp->connect($ftp_config);
    LogWrite('anay_net_' . $uuid, 'snapshot', $ftp_link ? "FTP连接成功" : "FTP连接失败", LOG_ANALYSIS_PATH);
    //远程FTP文件下载路径
    $remotepath = '/nglogs/' . $p_uuid . "/" . $device_type . "/";
    //本地文件临时存储路径
    $localpath = SAVE_PATH . $uuid . "/" . $device_type . "/";
    CreateAllDir($localpath);
    if ($ftp->download($remotepath . $p_uuid . ".trclog", $localpath . $uuid . ".trclog")) {
        require_once '../include/sqlite.class.php';
        $sdb_file = $localpath . $uuid . ".trclog";
        $sdb = new sqlite($sdb_file);
        if (!$sdb) {
            LogWrite("Analysis_net_error", $uuid, $sdb->lastErrorMsg(), LOG_PATH);
            return FALSE;
        }
        //保存截图
        $sql6 = "select ID,SNAPSHOT from trace_log WHERE SNAPSHOT != 'null'";
        $arr = $sdb->get_array($sql6);
        if (!empty($arr)) {
            try {
                $obj = new phdfs("10.1.10.101", "9000");
                $hdfs_link = $obj->connect();
                LogWrite('anay_net_' . $uuid, 'snapshot', $hdfs_link ? "HDFS连接成功" : "HDFS连接失败", LOG_ANALYSIS_PATH);

                $insert_sql = "INSERT  INTO  ts_apptest_result_network_image(`fkid`,`url`)  VALUES  ";
                foreach ($arr as $_one) {
                    //转存至HDFS HDFS文件上传路径(注：文件路径中不允许有空格)
                    $file_dir = str_replace(' ', '', '/apktest/' . $uuid . "/" . $device_type . "/");
                    $obj->create_directory($file_dir);
                    $file_path = $file_dir . $_one['ID'] . '.png';
                    $contents = base64_encode($_one['SNAPSHOT']);
                    if (!$obj->write($file_path, $contents, O_WRONLY | O_CREAT)) {
                        LogWrite("uploadHDFS", $uuid . "-" . $device_type, 'net snapshot upload failed!', LOG_PATH);
                    }
                    unset($contents);
                    $url = "http://42.96.171.6:8008/zhiceyun/file/download.php?file=" . urlencode("streamFile" . $file_path); //截图下载路径
                    $insert_sql .= "('$res','$url'),";
                }
                LogWrite('anay_net_' . $uuid, 'SQL', $insert_sql, LOG_ANALYSIS_PATH);
                $db->Query(substr($insert_sql, 0, -1));
            } catch (Exception $ex) {
                LogWrite('uploadHDFS', $uuid . "-" . $device_type, $ex->getMessage(), LOG_PATH);
            }
        }
        $sdb->close();
    }
}
