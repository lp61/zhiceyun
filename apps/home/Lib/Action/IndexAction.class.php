<?php

class IndexAction extends Action {

    public function index() {
        //已注册人数、已测试任务数
        $appTestNum = S('home_appTestNum');
        $registerNum = S('home_registerNum');
        if ($appTestNum == NULL || $registerNum == NULL) {
            $data = $this->get_oldData();

            $registerNum = M('user')->count();
            $registerNum += $data['reg']; // 临时值
            S('home_registerNum', $registerNum, 60); //缓存数据更新周期
            $appTestNum = M('app_test_task')->where('task_status=2')->count();
            $appTestNum += $data['app']; // 临时值
            S('home_appTestNum', $appTestNum, 60); //缓存数据更新周期
        }
        $this->assign('appTestStart', $appTestNum - 100 > 0 ? $appTestNum - 100 : 0);
        $this->assign('appTestNum', $appTestNum);
        $this->assign('registerStart', $registerNum - 100 > 0 ? $registerNum - 100 : 0);
        $this->assign('registerNum', $registerNum);

        $model = M('app_client');
        $where['ishot'] = 1;
        $where['package_type'] = 8;
        $list = $model->where($where)->order('display_order asc')->limit('0,3')->select();

        $typeArr = array(2, 3, 4, 5);
        foreach ($typeArr as $type) {
            $map['type'] = $type;
            $article[] = M('app_article')->where($map)->order('ctime desc')->find();
        }

        $this->assign('appNewArray', $list);
        $this->assign('article', $article);
        $this->display();
    }

    public function get_oldData() {
        $mysql_server_name = "smarterapps.mysql.rds.aliyuncs.com"; //数据库服务器名称
        $mysql_username = "smarterapps"; // 连接数据库用户名
        $mysql_password = "qwerasdf"; // 连接数据库密码
        $mysql_database = "smarterapps"; // 数据库的名字
        // 连接到数据库
        $conn = mysql_connect($mysql_server_name, $mysql_username, $mysql_password);
        //选择一个需要操作的数据库
        mysql_select_db($mysql_database, $conn);
        // 执行sql查询&获取查询结果
        $sql1 = 'SELECT COUNT(1) as num FROM t_users';
        $result1 = mysql_query($sql1, $conn);
        $row1 = mysql_fetch_array($result1);

        $sql2 = 'SELECT COUNT(1) as num FROM t_apply';
        $result2 = mysql_query($sql2, $conn);
        $row2 = mysql_fetch_array($result2);

        $res = M('user')->where("status>=0")->count();
        return array('reg' => $row1['num'] - $res, 'app' => $row2['num']);
    }

    /**
     * [isUrlCode 发送手机验证码]
     * @return [type] [description]
     */
    public function isUrlCode() {
        $data = Service('PhoneCode')->sendCode($_POST['phone']);
        echo json_encode($data);
        exit;
    }

}
