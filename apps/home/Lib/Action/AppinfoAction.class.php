<?php

import('@.Form.AppTestVars');

class AppinfoAction extends Action {

    //终端库
    public function mobile() {
        if (!$_POST['type'])
            unset($_POST['type']);
        // 安全过滤
        $_POST = array_map('t', $_POST);
        $appSystemConfig = $this->appPhonePre();
        $searchArr[] = 'id';
        foreach ($appSystemConfig as $key => $_one) {
            $searchArr[] = $_one['adapter_name'];
            if ($_POST[$_one['adapter_name']]) {
                $_GET[$_one['adapter_name']] = $_POST[$_one['adapter_name']];
            }
        }
        $model = M('app_client');
        //查询终端品牌（去重）
        $array_model = $model->where("isEnabled=1")->field('model as content')->group('model')->select();
        $tmp = array();
        if (!empty($array_model)) {
            foreach ($array_model as $value) {
                if ($value['content'] != '') {
                    $tmp[] = $value;
                }
            }
        }
        $appSystemConfig[AppTestVars::APP_SYSTEM_MODEL]['data'] = $tmp;
        $map = $this->_getSearchMap(array('in' => $searchArr));
        if (!$_REQUEST['isSearch']) {
            $map = array('isnew' => 1);
        }
        $map['isEnabled'] = 1;
        $ids = $model->where($map)->field("SUBSTRING_INDEX(group_concat(id order by `attach_id` desc),',',1) as id")->group('device_type')->select();
        $count = 0;
        if (!empty($ids)) {
            foreach ($ids as $key => $_one) {
                $ids_array[] = $_one['id'];
            }
            $count = $key + 1;
            $map['id'] = array('in', $ids_array);
        }
        $listData = $model->where($map)->group('device_type')->order('display_order asc')->findPage(12, $count);
        
        $this->assign('appSystemConfig', $appSystemConfig);
        $this->assign($listData);
        $this->assign($_REQUEST);
        $this->display();
    }

    private function appPhonePre() {
        $model = M('app_config');
        $setData = $model->order('type asc,content asc')->findAll();
        $appSystemConfig = AppTestVars::getAppSystemConfig();
        foreach ($setData as $_one) {
            $appSystemConfig[$_one['type']]['data'][] = $_one;
        }

        $a = $model->where("type=" . AppTestVars::APP_SYSTEM_RESOLUTION)->order('width asc,height asc')->findAll();
        $appSystemConfig[AppTestVars::APP_SYSTEM_RESOLUTION]['data'] = $a;

        return $appSystemConfig;
    }

    //测试圈：首页
    public function article() {
        $where['is_active'] = 1;
        $appArticleConfig = AppTestVars::getAppArticleConfig();
        foreach ($appArticleConfig as $_one) {
            $where['type'] = $_one['id'];
            //$limit = $_one['limit'] ? $_one['limit'] : 8;
            $limit=8;
            $listData[$_one['id']] = M('app_article')->where($where)->order('ishot DESC, ctime DESC')->limit($limit)->findAll();
        }

        $this->assign('appArticleConfig', $appArticleConfig);
        $this->assign('listData', $listData);
        $this->display();
    }

    //测试圈：文章列表
    public function articleList() {
        if (!$_GET['type'])
            unset($_GET['type']);
        // 安全过滤
        $_GET = array_map('t', $_GET);
        $where = $this->_getSearchMap(array('in' => array('type', 'ishot')));
        if (isset($_REQUEST['keyword']) && !empty($_REQUEST['keyword'])) {
            $keyword = $_REQUEST['keyword'];
            $map = array();
            $map['title'] = array('like', '%' . $keyword . '%');
            $map['content'] = array('like', '%' . $keyword . '%');
            $map['_logic'] = 'OR';
            $where['_complex'] = $map;
        }

        $where['is_active'] = 1;
        $listData = M('app_article')->where($where)->order('ishot DESC,ctime DESC')->findPage(6);

        $appArticleConfig = AppTestVars::getAppArticleConfig();
        $this->assign('appArticleConfig', $appArticleConfig);
        $this->assign($listData);
        $this->assign($_REQUEST);
        $this->display();
    }

    //测试圈：文章详情
    public function articleDetail() {
        $map['id'] = $_GET['id'];
        $map['is_active'] = 1;
        $articleInfo = M('app_article')->where($map)->find();
        if (empty($articleInfo)) {
            $this->assign('jumpUrl', U('home/Appinfo/article'));
            $this->error('页面不存在！！');
        }
        if (!empty($articleInfo['attach_file_id'])) {
            $attach = M('attach')->where("id=" . $articleInfo['attach_file_id'])->field('name,size')->find();
            $articleInfo['file_size'] = ceil($attach['size'] / 1024) . "KB"; //单位KB
            $articleInfo['file_name'] = $attach['name'];
        }
        M('app_article')->where($map)->setInc('read_count');
        $appArticleConfig = AppTestVars::getAppArticleConfig();

        $uid = $this->mid ? $this->mid : 0;
        $key = 'article_id_' . $_GET['id'] . '_uid_' . $uid;


        //获取评论列表
        unset($map);
        //$map['uid'] = $uid;
        $map['article_id'] = $_GET['id'];
        $commentList = M('app_comment')->where($map)->field('uid,content,ctime')->order('ctime DESC')->select();
        //赞的状态
        $has_praise = 0;
        if (cookie($key)) {
            $has_praise = 1;
        } else if ($uid) {
            $ret = M('app_praise')->where($map)->count();
            if ($ret) {
                $has_praise = 1;
            }
        }

        $this->assign('appArticleConfig', $appArticleConfig);
        $this->assign('articleInfo', $articleInfo);
        $this->assign('commentList', $commentList);
        $this->assign('has_praise', $has_praise);
        $this->display();
    }

    //测试圈：点赞
    public function praise() {
        $model = M('app_article');
        $map['id'] = $_POST['aid'];
        $prise_count = $model->where($map)->getField('praise_count');
        $data = array(
            'code' => 0,
            'message' => 'ok',
            'num' => $prise_count + 1
        );

        $uid = $this->mid ? $this->mid : 0;
        $key = 'article_id_' . $_POST['aid'] . '_uid_' . $uid;
        if (cookie($key)) {
            $data = array(
                'code' => 1,
                'message' => '已赞',
                'num' => $prise_count
            );
        }
        $save['uid'] = $uid;
        $save['article_id'] = $_POST['aid'];
        $ret = M('app_praise')->add($save);
        if ($ret) {
            cookie($key, 1, 7 * 24 * 3600); //点赞cookie设置
            $model->where($map)->setInc('praise_count');
        }

        echo json_encode($data);
    }

//    //终端排名
//    public function rank() {
//        $appRankConfig = AppTestVars::getAppRankConfig();
//        $listData = array();
//        foreach ($appRankConfig as $key => $_one) {
//            $map['type'] = $_one['id'];
//            $tmpList = M('app_rank_score')->where($map)->limit(10)->order('score DESC, mtime DESC')->findAll();
//
//            foreach ($tmpList as $key => $_one1) {
//                $map1['id'] = $_one1['rank_id'];
//                $listData[$_one['id']][$key] = M('app_rank')->where($map1)->find();
//                $listData[$_one['id']][$key]['score'] = $_one1['score'];
//                $listData[$_one['id']][$key]['type'] = $_one1['type'];
//            }
//        }
//
//        $this->assign('listData', $listData);
//        $this->assign('appRankConfig', $appRankConfig);
//        $this->display();
//    }

    protected function _getSearchMap($fields) {
        // 为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_GET)) {
            $_SESSION['appinfo_search_attach'] = serialize($_GET);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_GET = unserialize($_SESSION['appinfo_search_attach']);
        } else {
            unset($_SESSION['appinfo_search_attach']);
        }

        // 组装查询条件
        $map = array();
        foreach ($fields as $k => $v) {
            foreach ($v as $field) {
                if (isset($_GET[$field]) && $_GET[$field] != '') {
                    if ($k == 'in')
                        $map[$field] = array($k, explode(',', $_GET[$field]));
                    else
                        $map[$field] = array($k, $_GET[$field]);
                }
            }
        }

        return $map;
    }

    //专机详情
    public function mobileDetail() {
        //专机信息
        $map['id'] = $_GET['id'];
        $list = M('app_client')->where($map)->find();
        $this->assign('vo', $list);
        $device_type = $list['device_type'];
        //统计测试APP数
        $model = M();
        $sql_all = "SELECT id "
        . " FROM ts_app_test_task a,(SELECT uuid FROM ts_apptest_result_comp_device WHERE device_type='$device_type') b"
        . " WHERE a.uuid=b.uuid"
        . " GROUP BY apk_name";
         $num_device['all'] = count($model->query($sql_all));
        //测试成功的APP信息
        $sql = "SELECT a.apk_icon,a.apk_name,a.apkurl "
            . " FROM ts_app_test_task a,(SELECT uuid FROM ts_apptest_result_comp_device WHERE device_type='$device_type' AND test_result=1) b"
            . " WHERE a.uuid=b.uuid"
            . " GROUP BY apk_name";
        $appList = $model->query($sql);
        $num_device['success'] = count($appList);
        $this->assign('appList', $appList);
        $this->assign('num_device', $num_device);
        $this->display();
    }

    //测试圈-评论
    public function comment() {
//        if (!$this->mid) {   //验证是否登录无效
//            $this->assign('jumpUrl', U('home/Public/login'));
//            $this->error("请先登录");
//        }

        $data['article_id'] = $_POST['id'];
        $data['uid'] = $this->mid;
        $data['content'] = $_POST['content'];
        $data['ctime'] = time();
        $model = M('app_comment');
        $model->add($data);

        $return['email'] = empty($this->user['email']) ? "游客" : $this->user['email'];
        $return['content'] = $_POST['content'];
        $return['ctime'] = date("Y年m月d日 H:i");
        echo json_encode($return);
    }

    //IOT页面
    public function iot() {
        $this->display();
    }

    //IOT-填写反馈
    public function iot_ask() {
        if (!$this->mid) {
            redirect(U('home/Public/login'));
        }
        if ($_POST) {
            $data['uid'] = $this->mid;
            $data['company'] = $_POST['company'];
            $data['address'] = $_POST['address'];
            $data['contact'] = $_POST['contact'];
            $data['email'] = $_POST['email'];
            $data['phone'] = $_POST['phone'];
            $data['fax'] = $_POST['fax'];
            $data['other'] = $_POST['other'];
            $data['status'] = 0;
            $model = M('ask_iot');
            $res = $model->add($data);
            if ($res) {
                $this->assign('jumpUrl', U('home/Appinfo/iot_ask'));
                $this->success('提交成功！！');
            }
        }
        $this->display();
    }

    //用户终端体验页面
    public function experience() {
        $this->display();
    }

    //用户终端体验-填写反馈
    public function experience_ask() {
        if (!$this->mid) {
            redirect(U('home/Public/login'));
        }
        if ($_POST) {
            $data['uid'] = $this->mid;
            $data['name'] = $_POST['name'];
            $data['email'] = $_POST['email'];
            $data['phone'] = $_POST['phone'];
            $data['company'] = $_POST['company'];
            $data['title'] = $_POST['title'];
            $data['content'] = $_POST['content'];
            $data['status'] = 0;
            $model = M('ask_experience');
            $res = $model->add($data);
            if ($res) {
                $this->assign('jumpUrl', U('home/Appinfo/experience_ask'));
                $this->success('申请成功！！');
            }
//            if (!empty($model->add($data))) {
//                $this->assign('jumpUrl', U('home/Appinfo/experience_ask'));
//                $this->success('申请成功！！');
//            }
        }
        $this->display();
    }

    //模拟资源
    public function internet_source() {
        $this->display();
    }

    //压力测试
    public function source_press() {
        $this->display();
    }

}
