<?php

require_once '../include/sqlite.class.php';
/*
 * 兼容性测试数据结果分析
 * +-------------------------
 * $uuid 任务ID
 * $device_types 此任务下需要测试的所有终端型号【用户自选终端 or 任务下发时返回的需要检测终端】
 * $mtime 任务完成时间
 */

function analysis_one($db, $uuid, $device_types, $mtime) {
    LogWrite('anay_' . $uuid, 'device_types', $device_types, LOG_ANALYSIS_PATH);
    $dir = scandir(SAVE_PATH . $uuid);
    foreach ($dir as $key => $_one) {
        if (!is_dir(SAVE_PATH . $uuid . "/" . $_one) || $_one == "." || $_one == "..") { //判断是不是文件夹
            unset($dir[$key]);
        }
    }
    LogWrite('anay_' . $uuid, 'dir', json_encode($dir), LOG_ANALYSIS_PATH);
    $device_array = explode(',', $device_types);
    if (count($dir) != count($device_array)) {
        //不一致时，以实际测试终端为准进行分析；并更新测试任务表数据
        $actual = implode(',', $dir);
        $device_array = explode(',', $actual); //重置$device_array，包括数组下标
        $device_num = count($dir);
        $db->Query("UPDATE ts_app_test_task SET device_type='$actual',device_num='$device_num' WHERE uuid='$uuid'");
        LogWrite("Analysis_error", $uuid, "device_types unequal actual device! \r\n tb:$device_types \t ac:$actual", LOG_PATH);
    }
    $avg1 = array(); //安装时间
    $avg2 = array(); //启动时间
    $avg3 = array(); //cpu占用率
    $avg4 = array(); //内存占用率
    $avg5 = array(); //流量耗用
    $avg6 = array(); //电池温度
    $sta1 = array(); //安装失败
    $sta2 = array(); //运行失败
    $sta3 = array(); //启动失败
    $sta4 = array(); //功能异常
    $sta5 = array(); //UI异常
    $status = array(); //状态 1-失败 2-待优化 3-通过
    $model = array(); //终端品牌
    foreach ($device_array as $device_type) {
        $sdb_file = SAVE_PATH . $uuid . '/' . $device_type . '/' . $uuid . '.trclog';
        //$log_file = SAVE_PATH . $uuid . '/' . $device_type . '/traverse.log';
        $xml_file = SAVE_PATH . $uuid . '/' . $device_type . '/logcatAnalysis.xml';
        $return = analysis_device($db, $sdb_file, $xml_file, $uuid, $device_type, $mtime);
        @unlink($sdb_file);
        //过滤掉为0的值
        if ($return['install_time'] != '--') {
            $avg1[] = $return['install_time'];
        }
        $avg11[] = $return['install_time'];
        if ($return['boot_time'] != '--') {
            $avg2[] = $return['boot_time'];
        }
        $avg22[] = $return['boot_time'];
        if ($return['cpu_usage'] != '--') {
            $avg3[] = $return['cpu_usage'];
        }
        $avg33[] = $return['cpu_usage'];
        if ($return['memory_usage'] != '--') {
            $avg4[] = $return['memory_usage'];
        }
        $avg44[] = $return['memory_usage'];
        if ($return['flow_use'] != '--') {
            $avg5[] = $return['flow_use'];
        }
        $avg55[] = $return['flow_use'];
        if ($return['battery_temperature'] != '--') {
            $avg6[] = $return['battery_temperature'];
        }
        $avg66[] = $return['battery_temperature'];
        $sta1[] = $return['install_result'];
        $sta2[] = $return['run_result'];
        $sta3[] = $return['boot_result'];
        $sta4[] = $return['func_result'];
        $sta5[] = $return['ui_result'];
        $status[] = $return['status'];
        if ($return['model'] != '') {
            $model[] = $return['model']; //过滤为空的品牌
        }
    }
    LogWrite('anay_' . $uuid, "device_array", json_encode($device_array), LOG_ANALYSIS_PATH);
    LogWrite('anay_' . $uuid, "status", json_encode($status), LOG_ANALYSIS_PATH);
    LogWrite('anay_' . $uuid, "avg", json_encode($avg11), LOG_ANALYSIS_PATH);
    $s = array_count_values($status);
    $data['uuid'] = $uuid;
    $data['models'] = implode(',', array_unique($model)); //任务所含品牌
    $data['optimize_num'] = (int) $s[1]; //待优化终端
    $data['success_num'] = (int) $s[2]; //成功终端
    $data['install_failed_num'] = array_sum($sta1);
    $data['run_failed_num'] = array_sum($sta2);
    $data['boot_failed_num'] = array_sum($sta3);
    $data['func_exception_num'] = array_sum($sta4);
    $data['ui_exception_num'] = array_sum($sta5);
    $install_time = get_extremum('install_time', $avg1, $avg11, $device_array);
    $boot_time = get_extremum('boot_time', $avg2, $avg22, $device_array);
    $cpu_usage = get_extremum('cpu_usage', $avg3, $avg33, $device_array);
    $memory_usage = get_extremum('memory_usage', $avg4, $avg44, $device_array);
    $flow_use = get_extremum('flow_use', $avg5, $avg55, $device_array);
    $battery_temperature = get_extremum('battery_temperature', $avg6, $avg66, $device_array);
    $data['install_distribute'] = json_encode(create_distribute($avg1, $install_time['install_time_min'], $install_time['install_time_max']));
    $data['boot_distribute'] = json_encode(create_distribute($avg2, $boot_time['boot_time_min'], $boot_time['boot_time_max']));
    $data['cpu_distribute'] = json_encode(create_distribute($avg3, $cpu_usage['cpu_usage_min'], $cpu_usage['cpu_usage_max']));
    $data['memory_distribute'] = json_encode(create_distribute($avg4, $memory_usage['memory_usage_min'], $memory_usage['memory_usage_max']));
    $data['flow_distribute'] = json_encode(create_distribute($avg5, $flow_use['flow_use_min'], $flow_use['flow_use_max']));
    $data['battery_distribute'] = json_encode(create_distribute($avg6, $battery_temperature['battery_temperature_min'], $battery_temperature['battery_temperature_max']));

    LogWrite('anay_' . $uuid, 'data', json_encode($data), LOG_ANALYSIS_PATH);
    $saveData = array_merge($data, $install_time, $boot_time, $cpu_usage, $memory_usage, $flow_use, $battery_temperature);

    return add($db, $saveData, 'apptest_result_comp');
}

//获取某个参数的平均值和极值，及取到极值的终端类型
function get_extremum($field, $avg, $avgg, $device_array) {
    $data = array(
        $field => '--',
        $field . "_min" => '--',
        $field . "_max" => '--',
        $field . "_min_device" => '--',
        $field . "_max_device" => '--'
    );
    $count = count($avg);
    if ($count > 0) {
        $data[$field] = round(array_sum($avg) / $count, 2);
        $data[$field . "_min"] = $min = min($avg);
        $data[$field . "_max"] = $max = max($avg);
        $data[$field . "_min_device"] = $device_array[array_search($min, $avgg)];
        $data[$field . "_max_device"] = $device_array[array_search($max, $avgg)];
    }

    return $data;
}

//计算某个参数的分布情况
function create_distribute($avg, $min, $max) {
    $count = count($avg);
    if ($count <= 1 || $min == $max) {
        return array();
    }
    $start = floor($min);
    $end = ceil($max);
    $step = ($end - $start) / 5;
    for ($i = 1; $i < 6; $i++) {
        $x_data[] = ($start + $step * ($i - 1)) . "-" . ($start + $step * $i);
    }
    $tmp = array(0, 0, 0, 0, 0);
    foreach ($avg as $value) {
        $key = floor(($value - $start) / $step);
        $k = $key == 5 ? 4 : $key;
        $tmp[$k] = $tmp[$k] + 1;
    }
    for ($i = 1; $i < 6; $i++) {
        $y_data[] = round($tmp[$i - 1] * 100 / $count, 2) . "%";
    }
    return array("x" => $x_data, "y" => $y_data);
}

/*
 * 单个终端结果分析函数
 * +------------------------------------------------
 */

function analysis_device($db, $sdb_file, $xml_file, $uuid, $device_type, $mtime) {
    if (!file_exists($sdb_file)) {
        LogWrite("Analysis_error", $uuid, $sdb_file . " not exist!", LOG_PATH);
        return FALSE;
    }
    $sdb = new sqlite($sdb_file);
    if (!$sdb) {
        LogWrite("Analysis_error", $uuid, $sdb->lastErrorMsg(), LOG_PATH);
        return FALSE;
    }
    $return_array = array(
        'install_time' => '--',
        'boot_time' => '--',
        'cpu_usage' => '--',
        'memory_usage' => '--',
        'flow_use' => '--',
        'battery_temperature' => '--',
        'model' => '--',
        'install_result' => '0', //安装结果--失败时+1
        'boot_result' => '0', //启动结果--失败时+1
        'run_result' => '0', //运行结果--失败时+1
        'func_result' => '0', //功能异常结果--存在时+1
        'ui_result' => '0', //UI异常结果--存在时+1
        'status' => '0' //0-失败 1-待优化 2-成功
    );
    $data['uuid'] = $uuid; //任务ID
    $data['device_type'] = $device_type; //终端型号
    $data['mtime'] = $mtime; //任务完成时间
    if (file_exists(SAVE_PATH . $uuid . "/" . $device_type . "/traverse.log")) {
        $log_path = urlencode(str_replace(' ', '', "streamFile/apktest/" . $uuid . "/" . $device_type . "/traverse.log"));
        $data['log_url'] = "http://42.96.171.6:8008/zhiceyun/file/download.php?file=" . $log_path; //log_url-完成日志下载路径
    }
    $step = 3; //测量固定时间间隔(s)（参数随时间变化的测量）
    $flag = FALSE; //是否可以进行详细数据分析标志 默认FALSE-否 [安装成功&启动成功]后变更为TRUE
//STEP-1 查询终端基本信息
    $sql1 = "SELECT `model`,`size`,`imei`,`cpu`,`cpu_kernel`,`cpu_hz`,`resolution`,`os` FROM ts_app_client WHERE `device_type`='$device_type'  LIMIT 1";
    $info = $db->get_one($sql1);
    $return_array['model'] = $info['model']; //终端品牌
    LogWrite('anay_' . $uuid, "STEP-1", $sql1, LOG_ANALYSIS_PATH);
//STEP-2 查询终端检测时的一些参数信息
    $sql2 = "SELECT FREE_RAM,FREE_ROM,NETWORK FROM DEVICE_INFO LIMIT 1";
    $dev = $sdb->get_one($sql2); //NETWORK 0：无网络 1：wifi 2：其他网络
    $data['ram'] = round((float) $dev['FREE_RAM'] / (1024 * 1024), 2); //1-内存 单位MB
    $data['rom'] = round((float) $dev['FREE_ROM'] / (1024 * 1024), 2); //2-存储 单位MB
    $data['net'] = $dev['NETWORK']; //3-网络状况
//STEP-3 查询安装、启动、运行结，检查顺序：安装->启动->运行；前一个失败后一个不再检查
    $sql3 = "select INSTALL_STATUS,LUNCHER_TIME,ANR_TIME,CRASH_TIME,INSTALL_TIME from APP_INFO";
    $app = $sdb->get_one($sql3);
    LogWrite('anay_' . $uuid, "STEP-2", json_encode($app), LOG_ANALYSIS_PATH);
    //安装测试 INSTALL_STATUS=1 => 安装成功
    $data['install_result'] = $app['INSTALL_STATUS'];
    $return_array['install_result'] = !$data['install_result'];
    if ($data['install_result'] == 1) { //安装成功，继续向下分析
        $return_array['install_time'] = $data['install_time'] = round((float) $app['INSTALL_TIME'] / 1000, 2); //安装耗时(s) -求均值所需
        //启动测试 LUNCHER_TIME>0 => 启动成功
        $data['boot_result'] = $app['LUNCHER_TIME'] > 0 ? 1 : 0;
        $return_array['boot_result'] = !$data['boot_result'];
        if ($data['boot_result'] == 1) { //启动成功，继续向下分析
            $return_array['boot_time'] = $data['boot_time'] = round((float) $app['LUNCHER_TIME'] / 1000, 2); //启动耗时(s) -求均值所需
            //运行测试 ANR_TIME/CRASH_TIME都为空 => 运行成功
            $data['run_result'] = ($app['ANR_TIME'] == '' && $app['CRASH_TIME'] == '') ? 1 : 0;
            $return_array['run_result'] = !$data['run_result'];
            //功能检测 logcatAnalysis.xml中ErrorCount=0
            $xml = simplexml_load_string(file_get_contents($xml_file)); //读取XML
            $data['error_num'] = empty($xml->ErrorCount) ? 0 : $xml->ErrorCount; //日志-错误
            $data['warning_num'] = empty($xml->WarningCount) ? 0 : $xml->WarningCount; //日志-警告
            $data['func_result'] = $data['error_num'] == 0 ? 1 : 0;
            $return_array['func_result'] = !$data['func_result'];
            //UI检测 TRACE_LOG中不存在UI_ERROR='true'的记录 => UI无异常
            $sql6 = "select 1 from TRACE_LOG WHERE UI_ERROR='true' limit 1";
            $ui_error = $sdb->get_one($sql6);
            $data['ui_result'] = empty($ui_error) ? 1 : 0;
            $return_array['ui_result'] = !$data['ui_result'];
            //测试结果 前五项都没有异常，即通过
            $data['test_result'] = ($data['install_result'] + $data['boot_result'] + $data['run_result'] + $data['func_result'] + $data['ui_result']) < 5 ? 0 : 1;
            //0-失败 1-待优化 2-成功 (测试未通过=>失败 测试通过&有警告=>待优化 测试通过&无警告=>成功）
            if ($data['test_result'] == 0) {
                $return_array['status'] = 0;
            } else {
                $return_array['status'] = $data['warning_num'] > 0 ? 1 : 2;
            }
            $flag = TRUE;
        }
    }
    if ($flag) {
//STEP-4 查询终端运行参数数据（1-均值、最值） 说明：求均值剔除了值为0的点
        $sql4_1 = "select AVG(APP_CPU_PERCENT) as cpu from PERF_REPORT where APP_CPU_PERCENT>0";
        $row1 = $sdb->get_one($sql4_1);
        $sql4_2 = "select AVG(APP_MEMORY) as memory from PERF_REPORT where APP_MEMORY>0";
        $row2 = $sdb->get_one($sql4_2);
        $sql4_3 = "select MAX(APP_TRAFFIC) as flow from PERF_REPORT";
        $row3 = $sdb->get_one($sql4_3);
        $sql4_4 = "select replace(AVG(DEV_TEMPERATURE),'℃','') as battery from PERF_REPORT where DEV_TEMPERATURE>0";
        $row4 = $sdb->get_one($sql4_4);
//STEP-5 查询终端运行参数数据（2-随时间变化的量）
        $sql5 = "select APP_CPU_PERCENT as cpu,APP_MEMORY as memory,APP_TRAFFIC as flow,replace(DEV_TEMPERATURE,'℃','') as battery from PERF_REPORT";
        $arrs = $sdb->get_array($sql5);
        //PART-1
        $return_array['cpu_usage'] = $data['cpu_usage'] = round($row1['cpu'], 2); //CPU占用率 -均值
        $return_array['memory_usage'] = $data['memory_usage'] = round((float) $row2['memory'] / (1024 * 1024), 2); //内存消耗(MB) -均值
        if ($row3['flow'] != null) { //流量不为空时
            $return_array['flow_use'] = $data['flow_use'] = round((float) $row3['flow'] / 1024, 2); //流量消耗(KB) -合计
        }
        $return_array['battery_temperature'] = $data['battery_temperature'] = round((float) $row4['battery'], 2); //电池温度(℃) -均值
        //PART-2
        $a = $b = $c = $d = array();
        if (!empty($arrs)) {
            foreach ($arrs as $key => $_one) {
                $x[] = $key * $step;
                $a[] = round($_one['cpu'], 2); //小数点后保留两位
                $b[] = round((float) $_one['memory'] / (1024 * 1024), 2);
                $c[] = round((float) $_one['flow'] / 1024, 2);
                $d[] = round((float) $_one['battery'], 2);
            }
            $data['x_data'] = json_encode($x);
            $data['cpu_diagram'] = json_encode($a); //cpu占用随时间变化数据
            $data['memory_diagram'] = json_encode($b); //内存占用随时间变化数据
            $data['flow_diagram'] = json_encode($c); //流量随时间变化数据
            $data['battery_diagram'] = json_encode($d); //电池温度随时间变化数据
            unset($x);
            unset($a);
            unset($b);
            unset($c);
            unset($d);
        }
    }
    $saveData = array_merge($data, $info);
    $res = add($db, $saveData, 'apptest_result_comp_device');
    LogWrite('anay_' . $uuid, $res > 0 ? 'comp_device insert success!' : 'comp_device insert failed!', json_encode($saveData), LOG_ANALYSIS_PATH);
    //PART-3 保存截图
    $sql6 = "select ID,SNAPSHOT from trace_log WHERE SNAPSHOT != 'null'";
    $arr = $sdb->get_array($sql6);
    if (!empty($arr)) {
        try {
            $obj = new phdfs("10.1.10.101", "9000");
            $hdfs_link = $obj->connect();
            LogWrite('anay_' . $uuid, 'SNAPSHOT', $hdfs_link ? "HDFS连接成功" : "HDFS连接失败", LOG_ANALYSIS_PATH);

            $insert_sql = "INSERT  INTO  ts_apptest_result_image(`fkid`,`url`)  VALUES  ";
            foreach ($arr as $key => $_one) {
                //转存至HDFS HDFS文件上传路径(注：文件路径中不允许有空格)
                $file_dir = str_replace(' ', '', '/apktest/' . $uuid . "/" . $device_type . "/");
                $obj->create_directory($file_dir);
                $file_path = $file_dir . $_one['ID'] . '.png';
                $contents = base64_encode($_one['SNAPSHOT']);
                if (!$obj->write($file_path, $contents, O_WRONLY | O_CREAT)) {
                    LogWrite("uploadHDFS", $uuid . "-" . $device_type, 'snapshot upload failed!', LOG_PATH);
                }
                unset($contents);
                $url = "http://42.96.171.6:8008/zhiceyun/file/download.php?file=" . urlencode("streamFile" . $file_path); //截图下载路径
                $insert_sql .= "('$res','$url'),";
            }
            LogWrite('anay_' . $uuid, 'SQL', $insert_sql, LOG_ANALYSIS_PATH);
            $db->Query(substr($insert_sql, 0, -1));
        } catch (Exception $ex) {
            LogWrite('uploadHDFS', $uuid . "-" . $device_type, $ex->getMessage(), LOG_PATH);
        }
    }
    $sdb->close();

    return $return_array;
}
