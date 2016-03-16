<?php
import('home.Form.AppTestVars');
import('home.Form.AppEmailSendVars');
import('home.Form.SiteConfigVars');

class ContentAction extends AdministratorAction {
    protected $parentTitle = '内容管理';

    private function __isValidRequest($field, $array = 'post') {
        $field = is_array($field) ? $field : explode(',', $field);
        $array = $array == 'post' ? $_POST : $_GET;
        foreach ($field as $v){
            $v = trim($v);
            if ( !isset($array[$v]) || $array[$v] == '' ) return false;
        }
        return true;
    }

    /** 内容 - 附件管理 */

    public function attach($map) {
        $dao = model('Attach');
        $attaches   = $dao->getAttachByMap($map);
        $extensions = $dao->enumerateExtension();
        $this->assign($attaches);
        $this->assign('extensions', $extensions);

        $this->assign($_POST);
        $this->assign('isSearch', empty($map)?'0':'1');
        $this->display('attach');
    }

    public function doSearchAttach() {
        // 安全过滤
        $_POST = array_map('t',$_POST);

        $map = $this->_getSearchMap(array('in' => array('id', 'userId', 'extension')));
        $this->attach($map);
    }

    public function doDeleteAttach() {
        if( empty($_POST['ids']) ) {
            echo 0;
            exit ;
        }

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '2';
        $data[] = '内容 - 附件管理 ';
        $map['id'] = array('in',t($_POST['ids']));
        $data[] = model('Attach')->getAttachByMap($map);
        $data[] = array('isFile'=>intval($_POST['withfile']));
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);
        $map['attach_id'] = $map['id'];
        unset($map['id']);
        $weibo = M('weibo_attach')->where($map)->findAll();
        unset($map['attach_id']);
        foreach ($weibo as $v) {
            $weibo_id[] = $v['weibo_id'];
        }
        $weibo_id = implode(',', $weibo_id);
        $map['weibo_id'] = array('in',$weibo_id);
        M('weibo')->where($map)->delete();
        echo model('Attach')->deleteAttach( t($_POST['ids']), intval($_POST['withfile']) ) ? '1' : '0';
    }

    /**
     * 后台日志管理
     */
    public function adminLog($map){
        $data = M( 'AdminLog' )->where($map)->order('ID DESC')->findpage();
        $this->assign($data);
        $this->assign($_POST);
        $this->assign('isSearch', empty($map)?'0':'1');
        $this->display(adminLog);
    }

    public function showAdminLog(){
        $map['id'] = $_GET['id'];
        $data = M('AdminLog')->where($map)->find();

        $this->assign($data);
        $this->display();
    }

    public function doSearchAdminLog(){
        if(!$_POST['type'])
            unset($_POST['type']);
        // 安全过滤
        $_POST = array_map('t',$_POST);

        $map = $this->_getSearchMap(array('in'=>array('id','uid','type')));
        $this->assign('type',$_POST['type']);
        $this->adminLog($map);
    }

    public function doDeleteAdminLog() {
        if( empty($_POST['ids']) ) {
            echo 0;
            exit ;
        }
        // $where['id'] = array('in',t($_POST['ids']));
        // echo M( 'AdminLog' )->where( $where )->delete() ? '1' : '0';
    }

    public function lookDetail(){
        $data = M( 'AdminLog' )->where( 'id='.$_POST['ids'] )->find();
        $this->assign($data);
        $this->display();
    }

    public function feedback() {
        if(!$_POST['type'])
            unset($_POST['type']);
        // 安全过滤
        $_POST = array_map('t',$_POST);

        $map = $this->_getSearchMap(array('in'=>array('id','uid','type', 'is_reply')));
        $this->assign('type',$_POST['type']);

        $map['isdel'] = 0;
        $listData     = M('user_feedback')->where($map)->order('ctime DESC')->findPage();

        $this->assign('appTestConfig', AppTestVars::getAppTestConfig());
        $this->assign($listData);
        $this->assign($_POST);
        $this->display();
    }
    public function doDeleteFeedback() {
        if( empty($_POST['ids']) ) {
            echo 0;
            exit ;
        }

        $_LOG['uid']   = $this->mid;
        $_LOG['type']  = '2';
        $data[]        = '内容 - 用户反馈 - 进入回收站';
        $map['id']     = array('in',t($_POST['ids']));
        $data[]        = M( 'user_feedback' )->where($map)->findAll();
        $_LOG['data']  = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $where['id'] = array('in',t($_POST['ids']));
        $saveData['isdel'] =1;
        echo M( 'user_feedback' )->where( $where )->save($saveData) ? '1' : '0';
    }

    public function isValidFeedback() {
        if( empty($_POST['ids']) ) {
            echo 0;
            exit ;
        }
        $_LOG['uid']   = $this->mid;
        $_LOG['type']  = '3';
        $data[]        = '内容 - 用户反馈 - 设为有效反馈';
        $map['id']     = array('in',t($_POST['ids']));
        $list = $data[]        = M( 'user_feedback' )->where($map)->findAll();
        $_LOG['data']  = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $where['id']          = array('in',t($_POST['ids']));
        $saveData['is_valid'] = 1;
        $ret                  = M( 'user_feedback' )->where( $where )->save($saveData);
        if ($ret) {
            // 积分处理
            foreach ($list as $key => $info) {
                if ($uid = $info['uid'])
                    X('Credit')->setUserCredit($uid, 'app_feedback');
            }
        }
        echo $ret ? '1' : '0';
    }

    public function replyFeedback() {
        if ($_POST) {
            if (empty($_POST['content'])) {
                $this->error('请填写回复内容');
            }
            $saveData['fid']         = $_POST['fid'];
            $saveData['fuid']        = $_POST['fuid'];
            $saveData['ftitle']      = $_POST['ftitle'];
            $saveData['content']     = $_POST['content'];
            $saveData['notice_type'] = $_POST['notice_type'];
            $saveData['uid']         = $this->mid;
            $saveData['ctime']       = $saveData['mtime'] = time();
            $ret = M( 'user_feedback_reply' )->add($saveData);
            if ($ret) {
                $toTitle = '管理员回复了您的用户反馈';
                if ($saveData['notice_type'] == 1) {
                    $msgData['title']   = $toTitle;
                    $msgData['content'] = $saveData['content'];
                    $msgData['to_link'] = U('home/Account/replyFeedback', array('id'=>$saveData['fid']));
                    model('AppMessage')->postMessage($saveData['fuid'], $msgData);
                }
                if ($saveData['notice_type'] == 2) {
                    $fuserInfo     = M('user')->find($saveData['fuid']);
                    $email_sent    = service('Mail')->send_email($fuserInfo['email'], $toTitle, $saveData['content']);
                    $send_email_id = model('AppEmailSend')->addSendRecord($fuserInfo['email'], $toTitle, $saveData['content'], AppEmailSendVars::REGISTER_ACTIVE);
                    // 渲染输出
                    if ($email_sent) {
                        model('AppEmailSend')->setSendStatus($send_email_id);
                    }else {
                        model('AppEmailSend')->setSendStatus($send_email_id, AppEmailSendVars::FAIL_SENDING);
                    }
                }
            }

            $fsaveData['id']       = $_POST['fid'];
            $fsaveData['is_reply'] = 1;
            M( 'user_feedback' )->save($fsaveData);
        }
        $id            = $_GET['id'];
        $fmap['id']    = $id;
        $fmap['isdel'] = 0;
        $feedback      = M( 'user_feedback' )->where($fmap)->find();

        $map['fid'] = $id;
        $map['isdel'] = 0;
        $listData   = M('user_feedback_reply')->where($map)->order('ctime DESC')->findPage();
        $this->assign($listData);
        $this->assign('appTestConfig', AppTestVars::getAppTestConfig());
        $this->assign('feedback', $feedback);
        $this->assign($_POST);
        $this->display();
    }

    public function doDeleteReplyFeedback () {
        if( empty($_POST['ids']) ) {
            echo 0;
            exit ;
        }

        $_LOG['uid']   = $this->mid;
        $_LOG['type']  = '2';
        $data[]        = '内容 - 用户反馈回复 - 进入回收站';
        $map['id']     = array('in',t($_POST['ids']));
        $data[]        = M( 'user_feedback_reply' )->where($map)->findAll();
        $_LOG['data']  = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);
        
        $where['id']       = array('in',t($_POST['ids']));
        $saveData['isdel'] = 1;
        $saveData['mtime'] = time();
        echo M( 'user_feedback_reply' )->where( $where )->save($saveData) ? '1' : '0';
    }

    public function appSystemConfig() {
        if(!$_POST['type'])
            unset($_POST['type']);
        // 安全过滤
        $_POST = array_map('t',$_POST);

        $map = $this->_getSearchMap(array('in'=>array('id','type')));

        if ($_POST && $_POST['add']) {
            $count                     = M('app_config')->max('display_order');
            $saveData['type']          = $_POST['type'];
            $saveData['content']       = trim($_POST['content']);
            if ($_POST['type'] == AppTestVars::APP_SYSTEM_RESOLUTION) {
                $re = explode(',', $saveData['content']);
                $saveData['width'] = $re[0];
                $saveData['height'] = $re[1];
                $saveData['content'] = $re[0] . "×" . $re[1];
            }
            $saveData['display_order'] = $count+1;
            $saveData['ctime']         = $saveData['mtime'] = time();
            M('app_config')->add($saveData);

            $_LOG['uid']   = $this->mid;
            $_LOG['type']  = '1';
            $data[]        = '内容 - 终端配置 - 增加';
            $data[]        = $saveData;
            $_LOG['data']  = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);
        }

        $listData = M('app_config')->where($map)->order('type asc,content asc')->findPage();

        $this->assign('appSystemConfig', AppTestVars::getAppSystemConfig());
        $this->assign($listData);
        $this->assign($_POST);
        $this->display();
    }

    public function doChangeOrder() {
        $_POST['document_id'] = intval($_POST['document_id']);
        $_POST['baseid']      = intval($_POST['baseid']);
        $tableName            = $_POST['tableName'];
        if ( $_POST['document_id'] <= 0 || $_POST['baseid'] <= 0 ) {
            echo 0;
            exit;
        }

        // 获取详情
        $map['id'] = array('in', array($_POST['document_id'], $_POST['baseid']));
        $res       = M($tableName)->where($map)->field('id,display_order')->findAll();
        if ( count($res) < 2 ) {
            echo 0;
            exit;
        }

        //转为结果集为array('id'=>'order')的格式
        foreach($res as $v) {
            $order[$v['id']] = intval($v['display_order']);
        }
        unset($res);

        //交换order值
        $res =         M($tableName)->where('`id`=' . $_POST['document_id'])->setField(  'display_order', $order[$_POST['baseid']] );
        $res = $res && M($tableName)->where('`id`=' . $_POST['baseid'])->setField( 'display_order', $order[$_POST['document_id']]  );

        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
    }

    private function appPhonePre() {
        $setData         = M('app_config')->order('type asc,content asc')->findAll();
        $appSystemConfig = AppTestVars::getAppSystemConfig(1);
        foreach ($setData as $_one) {
            $appSystemConfig[$_one['type']]['data'][] = $_one;
        }
        
        $a = M('app_config')->where("type=" . AppTestVars::APP_SYSTEM_RESOLUTION)->order('width asc,height asc')->findAll();
        $appSystemConfig[AppTestVars::APP_SYSTEM_RESOLUTION]['data'] = $a;
        
        return $appSystemConfig;
    }

    public function appPhone() {
        if(!$_POST['type'])
            unset($_POST['type']);
        // 安全过滤
        $_POST           = array_map('t',$_POST);
        $appSystemConfig = $this->appPhonePre();
        $appPackage      = M('app_test_package')->field('id,title')->findAll();
        $searchArr[]     = 'id'; //搜索条件-1 ID
        $searchArr[]     = 'package_type'; //搜索条件-2 测试包
        $searchArr[]     = 'devState'; //搜索条件-3 在线状态
        $searchArr[]     = 'isEnabled'; //搜索条件-4 启用状态
        foreach ($appSystemConfig as $key => $_one) {
            $searchArr[] = $_one['adapter_name']; //搜索条件-5 其它配置项
        }

        $map      = $this->_getSearchMap(array('in' => $searchArr));
        if ($_GET['package_type']) {
            $map['package_type'] = $_GET['package_type'];
        }
        $listData = M('app_client')->where($map)->order('devState desc ,isEnabled desc')->findPage();
        
        $this->assign('appSystemConfig', $appSystemConfig);
        $this->assign('appPackage', $appPackage);
        $this->assign($listData);
        $this->assign($_POST);
        $this->display();
    }
    
    public function editAppPhone() {
        $appSystemConfig = $this->appPhonePre();
        $model = M('app_client');
        $count           = $model->max('display_order');
        if ($_POST) {
            include_once( SITE_PATH.'/addons/libs/ImageResize.class.php' );
            if (!empty($_FILES['pic_url']['name'])) {
                $pic_path = $this->savePic('app_client_pic_url', 'pic_url');
                $pic_name = basename($_POST['pic_url']);
                new resizeimage($pic_path . $pic_name, '', 1 / 2, "", "", "1", "6", "0");
            }

            if (!empty($_FILES['photo_url']['name'])) {
                $pic_path = $this->savePic('app_client_pic_url', 'photo_url', false);
                $pic_name = basename($_POST['photo_url']);
                new resizeimage($pic_path . $pic_name, '', 1, "", "", "1", "6", "0");
            }

            foreach ($appSystemConfig as $key => $_one) {
                if (isset($_POST[$_one['adapter_name']]) && !empty($_POST[$_one['adapter_name']])) {
                    $saveData[$_one['adapter_name']] = $_POST[$_one['adapter_name']];
                }
            }
            $add = true;
            $saveData['mtime'] = time();
            if (isset($_POST['id']) && intval($_POST['id']) > 0) {
                $saveData['id']    = $_POST['id'];
                $add               = false;
            }else {
                $saveData['ctime']         = time();
                $saveData['display_order'] = $count+1;
            }
            if (isset($_POST['name'])) {
                $saveData['name']      = $_POST['name'];
            }
            if (isset($_POST['release_time'])) {
                $saveData['release_time'] = $_POST['release_time'];
            }
            if (isset($_POST['pic_url'])) {
                $saveData['pic_url']   = $_POST['pic_url'];
            }
            if (isset($_POST['attach_id'])) {
                $saveData['attach_id'] = $_POST['attach_id'];
            }
            if (isset($_POST['photo_url'])) {
                $saveData['photo_url']   = $_POST['photo_url'];
            }

            $action = $add ? 'add' : 'save';
            if ($action == 'save') {
                if (empty($_POST['divice_type'])) {
                    $map['id'] = $_POST['id'];
                } else {
                    $map['divice_type'] = $_POST['divice_type'];
                }
                $ret = $model->where($map)->$action($saveData);
            } else {
                $ret = $model->$action($saveData);
            }

            if ($ret) {
                $logName = $add ? '增加' : '修改';
                $_LOG['uid']   = $this->mid;
                $_LOG['type']  = $add ? '1':'3';
                $data[]        = '内容 - 手机终端 - '.$logName;
                $data[]        = $saveData;
                $_LOG['data']  = serialize($data);
                $_LOG['ctime'] = time();
                M('AdminLog')->add($_LOG);
                $this->assign('jumpUrl', U('admin/Content/appPhone'));
                $this->success('保存成功');
            }else{
                $this->error('保存失败');
            }
        }
        
        if ($_GET['id']) {
            $map['id'] = $_GET['id'];
            $listData  = $model->where($map)->find();
        }

        $this->assign('appSystemConfig', $appSystemConfig);
        $this->assign($listData);
        $this->display();
    }

//    public function appPhoneClassify() {        
//        if(!$_POST['type'])
//            unset($_POST['type']);
//
//        // 安全过滤
//        $_POST    = array_map('t',$_POST);
//        $map      = $this->_getSearchMap(array('in' => array('id', 'name', 'is_active', 'type','alias')));
//        $listData = M('app_client_classify')->where($map)->order('id DESC')->findPage();
//        $appClientTmp = M('app_client')->order('id DESC')->findAll();
//        $appClient    = array();
//        foreach ($appClientTmp as $key => $_one) {
//            $appClient[$_one['id']] = $_one;
//        }
//
//        $this->assign('appClient', $appClient);
//
//        $appTestConfig = AppTestVars::getAppTestConfig();
//        $this->assign('appTestConfig', $appTestConfig);
//        $this->assign($listData);
//        $this->assign($_POST);
//        $this->display();
//    }
//    public function editAppPhoneClassify() {
//        $appTestConfig = AppTestVars::getAppTestConfig();
//
//        if ($_POST) {
//            $this->assign('jumpUrl', U('admin/Content/editAppPhoneClassify'));
//            $add = true;
//            $saveData['mtime'] = time();
//            if (isset($_POST['id']) && intval($_POST['id']) > 0) {
//                $saveData['id']        = $_POST['id'];
//                $add                   = false;
//            }else {
//                $saveData['ctime'] = time();
//                $saveData['is_active'] = 1;
//            }
//            if (!empty($_POST['name'])) {
//                $saveData['name']  = $_POST['name'];
//            }else{
//                $this->error('请填写分类名称!');
//            }
//            if (!empty($_POST['alias'])) {
//                $saveData['alias'] = $_POST['alias'];
//            }else{
//                $this->error('请填写分类中文别名！');
//            }
//            if (isset($_POST['type']) && !empty($_POST['type'])) {
//                $saveData['type'] = $_POST['type'];
//            }else{
//                $this->error('请应用测试类型!');
//            }
//
//            if (isset($_POST['child_type'])) {
//                $saveData['child_type'] = $_POST['child_type'];
//            }else{
//                $saveData['child_type'] = 0;
//            }
//
//            if (isset($_POST['client_ids']) && !empty($_POST['client_ids'])) {
//                $saveData['client_ids'] = implode(',', $_POST['client_ids']);
//            }else{
//                $this->error('请选择终端!');
//            }
//
//            $action = $add ? 'add' : 'save';
//            $ret = M('app_client_classify')->$action($saveData);
//
//            if ($ret) {
//                $logName = $add ? '增加' : '修改';
//                $_LOG['uid']   = $this->mid;
//                $_LOG['type']  = $add ? '1':'3';
//                $data[]        = '内容 - 手机终端分配 - '.$logName;
//                $data[]        = $saveData;
//                $_LOG['data']  = serialize($data);
//                $_LOG['ctime'] = time();
//                M('AdminLog')->add($_LOG);
//                $this->assign('jumpUrl', U('admin/Content/appPhoneClassify'));
//                $this->success('保存成功');
//            }else{
//                $this->error('保存失败');
//            }
//        }
//        
//        if ($_GET['id']) {
//            $map['id'] = $_GET['id'];
//            $listData  = M('app_client_classify')->where($map)->find();
//            $listData['client_ids_arr'] = !empty($listData['client_ids']) ? explode(',', $listData['client_ids']):array();
//        }
//
//        $appClient = M('app_client')->order('id DESC')->findAll();
//
//        $this->assign('appClient', $appClient);
//        $this->assign('appTestConfig', $appTestConfig);
//        $this->assign($listData);
//        $this->display();
//    }

    public function appArticle() {
        if(!$_POST['type'])
            unset($_POST['type']);

        // 安全过滤
        $_POST    = array_map('t',$_POST);
        $map      = $this->_getSearchMap(array('in' => array('id', 'type', 'ishot','is_active', 'author')));
        $listData = M('app_article')->where($map)->order('ctime DESC')->findPage();

        $appArticleConfig = AppTestVars::getAppArticleConfig();
        $this->assign('appArticleConfig', $appArticleConfig);
        $this->assign($listData);
        $this->assign($_POST);
        $this->display();
    }

    public function editAppArticle() {
        if ($_POST) {
            $this->savePic('app_article_pic_url');
            $this->saveFile('app_article_file_url');
            $add               = true;
            $saveData['mtime'] = time();
            if (isset($_POST['id']) && intval($_POST['id']) > 0) {
                $saveData['id']    = $_POST['id'];
                $add               = false;
            }else {
                $saveData['ctime']   = time();
            }
            if (isset($_POST['title'])) {
                $saveData['title']   = $_POST['title'];
            }
            if (isset($_POST['keyword'])) {
                $saveData['keyword'] = $_POST['keyword'];
            }
            if (isset($_POST['content'])) {
                $saveData['content'] = $_POST['content'];
            }
            if (isset($_POST['author'])) {
                $saveData['author']  = $_POST['author'];
            }
            if (isset($_POST['link'])) {
                $saveData['link']    = $_POST['link'];
            }
            if (isset($_POST['pic_url'])) {
                $saveData['pic_url'] = $_POST['pic_url'];
            }
            if (isset($_POST['attach_id'])) {
                $saveData['attach_id'] = $_POST['attach_id'];
            }
            if (isset($_POST['file_url'])) {
                $saveData['file_url'] = $_POST['file_url'];
            }
            if (isset($_POST['attach_file_id'])) {
                $saveData['attach_file_id'] = $_POST['attach_file_id'];
            }
            if (isset($_POST['type'])) {
                $saveData['type'] = $_POST['type'];
            }

            $action = $add ? 'add' : 'save';
            $ret = M('app_article')->$action($saveData);

            if ($ret) {
                $logName = $add ? '增加' : '修改';
                $_LOG['uid']   = $this->mid;
                $_LOG['type']  = $add ? '1' :'3';
                $data[]        = '内容 - 行业报告 - '.$logName;
                $data[]        = $saveData;
                $_LOG['data']  = serialize($data);
                $_LOG['ctime'] = time();
                M('AdminLog')->add($_LOG);
                $this->assign('jumpUrl', U('admin/Content/appArticle'));
                $this->success('保存成功');
            }else{
                $this->error('保存失败');
            }
        }
        
        if ($_GET['id']) {
            $map['id'] = $_GET['id'];
            $listData  = M('app_article')->where($map)->find();
        }

        $appArticleConfig = AppTestVars::getAppArticleConfig();
        $this->assign('appArticleConfig', $appArticleConfig);
        $this->assign($listData);
        $this->display();
    }

    public function savePic($attach_type='attach', $post_name='pic_url', $save_to_db = true) {
        //保存LOGO
        if (!empty($_FILES[$post_name]['name'])) {
            $logo_options['save_to_db'] = $save_to_db;
            $logo = X('Xattach')->upload($attach_type, $logo_options);
            if ($logo['status']) {
                $logofile = UPLOAD_URL . '/' . $logo['info'][0]['savepath'] . $logo['info'][0]['savename'];
            }
            $_POST[$post_name] = $logofile;
            if ($save_to_db == true) {
                $_POST['attach_id'] = $logo['info'][0]['id'];
            }
        }

        return SITE_PATH . UPLOAD_URL_EXT .'/'. $logo['info'][0]['savepath'];
    }
    
    public function saveFile($attach_type='attach') {
        //保存附件
        if(!empty($_FILES['file_url']['name'])){
            $logo_options['save_to_db'] = true;
            $logo                       = X('Xattach')->upload($attach_type, $logo_options);
            if($logo['status']){
                $logofile = UPLOAD_URL .'/'.$logo['info'][0]['savepath'].$logo['info'][0]['savename'];
            }
            $_POST['file_url']       = $logofile;
            $_POST['attach_file_id'] = $logo['info'][0]['id'];
        }
    }

//    public function appRank(){
//        if(!$_POST['type'])
//            unset($_POST['type']);
//        // 安全过滤
//        $_POST    = array_map('t',$_POST);
//        $map      = $this->_getSearchMap(array('in' => array('type')));
//
//        if ($_POST['type']) {
//            $listData = M('app_rank_score')->where($map)->order('score DESC, mtime DESC')->findPage();
//            foreach ($listData['data'] as $key => $_one) {
//                $map1['id']                           = $_one['rank_id'];
//                $score_data                           = $_one;
//                $listData['data'][$key]               = M('app_rank')->where($map1)->find();
//                $listData['data'][$key]['score_data'] = array($score_data);
//            }
//        }else {
//            $listData = M('app_rank')->where($map)->order('ctime DESC')->findPage();
//            foreach ($listData['data'] as $key => $_one) {
//                $map1['rank_id']        = $_one['id'];
//                $listData['data'][$key]['score_data'] = M('app_rank_score')->where($map1)->findAll();
//            }
//        }
//        
//
//        $appRankConfig = AppTestVars::getAppRankConfig();
//        $this->assign('appRankConfig', $appRankConfig);
//        $this->assign($listData);
//        $this->assign($_POST);
//        $this->display();
//    }
//
//    public function editAppRank() {
//        $appRankConfig = AppTestVars::getAppRankConfig();
//
//        if ($_POST) {
//            $this->savePic('app_rank_pic_url');
//            $add               = true;
//            $saveData['mtime'] = time();
//            if (isset($_POST['id']) && intval($_POST['id']) > 0) {
//                $saveData['id']    = $_POST['id'];
//                $add               = false;
//            }else {
//                $saveData['ctime']   = time();
//            }
//            if (isset($_POST['name'])) {
//                $saveData['name']   = $_POST['name'];
//            }
//            if (isset($_POST['link'])) {
//                $saveData['link']    = $_POST['link'];
//            }
//            if (isset($_POST['pic_url'])) {
//                $saveData['pic_url'] = $_POST['pic_url'];
//            }
//            if (isset($_POST['attach_id'])) {
//                $saveData['attach_id'] = $_POST['attach_id'];
//            }
//       
//
//            $action = $add ? 'add' : 'save';
//            $ret = M('app_rank')->$action($saveData);
//
//            if ($ret) {
//                $saveData1['rank_id'] = $add ? $ret : $saveData['id'];
//                foreach ($appRankConfig as $key => $_one) {
//                    $cc    = 'type_'.$_one['id'];
//                    $cc_id = 'type_id_'.$_one['id'];                    
//                    if (isset($_POST[$cc])) {
//                        $saveData1['type']  = $_one['id'];
//                        $saveData1['score'] = $_POST[$cc];
//                        $saveData1['mtime'] = time();
//                        if (!empty($_POST[$cc_id])) {
//                            $saveData1['id']  = $_POST[$cc_id];
//                            M('app_rank_score')->save($saveData1);
//                        }else {
//                            M('app_rank_score')->add($saveData1);
//                        }
//                    }
//                }
//
//                $logName = $add ? '增加' : '修改';
//                $_LOG['uid']   = $this->mid;
//                $_LOG['type']  = $add ? '1' :'3';
//                $data[]        = '内容 - 测试排行 - '.$logName;
//                $data[]        = $saveData;
//                $_LOG['data']  = serialize($data);
//                $_LOG['ctime'] = time();
//                M('AdminLog')->add($_LOG);
//                $this->assign('jumpUrl', U('admin/Content/appRank'));
//                $this->success('保存成功');
//            }else{
//                $this->error('保存失败');
//            }
//        }
//        
//        if ($_GET['id']) {
//            $map['id'] = $_GET['id'];
//            $listData  = M('app_rank')->where($map)->find();
//
//            $map1['rank_id'] = $_GET['id'];
//            $listData1  = M('app_rank_score')->where($map1)->findAll();
//            $ll = array();
//            foreach ($listData1 as $key => $_one) {
//                $ll['type_'.$_one['type']]    = $_one['score'];
//                $ll['type_id_'.$_one['type']] = $_one['id'];
//            }
//        }
//
//        
//        $this->assign('appRankConfig', $appRankConfig);
//        $this->assign($listData);
//        $this->assign($ll);
//        $this->display();
//    }
    
    public function appPackage() {
        if(!$_POST['businessID'])
            unset($_POST['businessID']);

        // 安全过滤
        $_POST    = array_map('t',$_POST);
        $map      = $this->_getSearchMap(array('in' => array('businessID')));
        $listData = M('app_test_package')->where($map)->order('id ASC')->findPage();

        $this->assign('appTestConfig', AppTestVars::getAppTestConfig());
        $this->assign($listData);
        $this->assign($_POST);
        $this->display();
    }
    
    public function editAppPackage() {
        if ($_POST) {
            if (isset($_POST['id']) && intval($_POST['id']) > 0) {
                $saveData['id'] = $_POST['id'];
            }
            if (isset($_POST['title'])) {
                $saveData['title'] = $_POST['title'];
            }
            if (isset($_POST['business_title'])) {
                $saveData['business_title'] = $_POST['business_title'];
            }
            if (isset($_POST['mininum'])) {
                $saveData['mininum'] = $_POST['mininum'];
            }
            if (isset($_POST['free0'])) {
                $saveData['free0'] = $_POST['free0'];
            }
            if (isset($_POST['free1'])) {
                $saveData['free1'] = $_POST['free1'];
            }
            if (isset($_POST['free2'])) {
                $saveData['free2'] = $_POST['free2'];
            }
            if (isset($_POST['score0'])) {
                $saveData['score0'] = $_POST['score0'];
            }
            if (isset($_POST['score1'])) {
                $saveData['score1'] = $_POST['score1'];
            }
            if (isset($_POST['score2'])) {
                $saveData['score2'] = $_POST['score2'];
            }

            $ret = M('app_test_package')->save($saveData);

            if ($ret) {
                $_LOG['uid'] = $this->mid;
                $_LOG['type'] = '3'; //修改
                $data[] = '内容 - 测试包类型管理 - 修改';
                $data[] = $saveData;
                $_LOG['data'] = serialize($data);
                $_LOG['ctime'] = time();
                M('AdminLog')->add($_LOG);
                $this->assign('jumpUrl', U('admin/Content/appPackage'));
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        }

        if ($_GET['cid']) {
            $map['id'] = $_GET['cid'];
            $model = M('app_test_package');
            $listData = $model->where($map)->find();
            $this->assign('vo', $listData);
        }

        $this->display();
    }
    
    //测试任务——列表
    public function appTask() {
        $_POST    = array_map('t',$_POST);
        $map      = $this->_getSearchMap(array('in' => array('uuid','user_id','apk_type','send_status')));
        if(!empty($_POST['apk_name'])){
            $map['apk_name']=array('like','%'.$_POST['apk_name'].'%');
        }
        if(isset($_REQUEST['package_type'])){
            $map['package_type'] = $_REQUEST['package_type']; //测试包类型
            $pacakge_status = M('app_test_package')->where($map)->getField('status');
            $this->assign('pakcage_status', $pacakge_status);
        }
        $map['status'] = 1; //只取提交成功的任务
        $model = M('app_test_task');
        $listData = $model->where($map)->order('task_status ASC,send_order DESC,send_num ASC,ctime DESC')->findPage(40);
        //dump($model->getLastSql());
        
        $this->assign('appTypeConfig', AppTestVars::getAppTypeConfig());
        $this->assign('appTestConfig', AppTestVars::getAppTestConfig());
        $this->assign($listData);
        $this->assign('send_status',$map['send_status']);
        $this->assign($_POST);
        $this->display();
    }
    
    //测试任务——置顶
    public function appTaskTop(){
        if( empty($_POST['id']) || !isset($_POST['package_type']) ) {
            echo 0;
            exit ;
        }
        
        $tableName = $_POST['tableName'];
        $model = M($tableName);
        $where['package_type'] = $_POST['package_type'];
        $max_order = $model->where($where)->max('send_order');
        $data['send_order'] = ++$max_order;
        $map['id'] = $_POST['id'];
        $res = $model->where( $map )->save($data);
        echo $res ? '1' : '0';
    }
    
    //测试任务——重发
    public function appTaskResend() {
        if( empty($_POST['id'])) {
            echo 0;
            exit ;
        }
        
        $tableName = $_POST['tableName'];
        $model = M($tableName);
        $map['id'] = $_POST['id'];
        $data['send_status'] = 1; //重置下发状态：1-排队中
        $data['send_num'] = 0; //重置下发失败次数：0
        $data['task_status'] = 1; //重置任务状态：0-排队中
        $data['retry'] = 1; //重置重发次数：1
        $res = $model->where( $map )->save($data);
        echo $res ? '1' : '0';
    }
    
    //测试配置
    public function appConfig() {
        $result = M('app_test_config')->select();
        foreach ($result as $va) {
            $config[$va['key']] = $va['value'];
        }
        $this->assign('config', $config);
        $this->display();
    }

    public function doAppConfig() {
        if (is_numeric($_POST['num'])) {
            $num = $_POST['num'] ? (int) $_POST['num'] : 3; //默认次数3
        } else {
            $this->error('重发次数必须为数字！');
        }
        $data['value'] = $num;
        $data['ctime'] = date("Y-m-d H:i:s");
        $model = M('app_test_config');
        $model->where("`key`='retry_num'")->save($data);

        if (!empty($_POST['mobile'])) {
            if (preg_match("/1[34578]{1}\d{9}$/", $_POST['mobile'])) {
                $mobile = $_POST['mobile'];
            } else {
                $this->error('手机号格式错误！');
            }
            $data['value'] = $mobile;
            $model->where("`key`='notice_mobiles'")->save($data);
        }
        
        if (!empty($_POST['email'])) {
            if (preg_match('/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i', $_POST['email'])) {
                $email = $_POST['email']; //通知邮箱
            } else {
                $this->error('邮箱格式错误！');
            }
            $data['value'] = $email;
            $model->where("`key`='notice_emails'")->save($data);
        }

        $this->success("配置修改成功！");
    }

    //支付订单列表：时间倒序
    public function order() {
        // 安全过滤
        $_POST    = array_map('t',$_POST);
        $map      = $this->_getSearchMap(array('in' => array('trade_no','ord_no','uid','ord_status')));
        
        $listData = M('pay')->where($map)->order('ctime DESC')->findPage(20);
        $this->assign($listData);
        $this->assign($_POST);
        $this->display();
    }
    
    //发票申请列表：时间倒序
    public function bill() {
        // 安全过滤
        $_POST    = array_map('t',$_POST);
        $map      = $this->_getSearchMap(array('in' => array('uid','status')));
        
        $listData = M('bill')->where($map)->order('status ASC,ctime DESC')->findPage(20);
        $this->assign($listData);
        $this->assign($_POST);
        $this->display();
    }
    
    //确认开票
    public function doBill() {
        $saveData['mtime'] = date("Y-m-d H:i:s");
        if (isset($_POST['id']) && intval($_POST['id']) > 0) {
            $saveData['id'] = $_POST['id'];
        }
        if (isset($_POST['bill_no'])) {
            $saveData['bill_no'] = $_POST['bill_no'];
        }
        if (isset($_POST['status'])) {
            $saveData['status'] = $_POST['status'];
        }

        $model = M('bill');
        $data[] = '内容 - 发票管理 - 开具发票';
        $data[] = $row = $model->where("id=" . $_POST['id'])->find();
        $ret = $model->save($saveData);

        if ($ret) {
            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '3';
            $data[] = $row = $model->where("id=" . $_POST['id'])->find();
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);
            
            //发送给前台消息通知
            $toTitle = '发票申请回执';
            if ($saveData['status'] == 1) {
                $msgData['title'] = $toTitle;
                $msgData['content'] ='管理员同意里您的发票申请！发票编号：'.$saveData['bill_no'];
                model('AppMessage')->postMessage($this->mid, $msgData);
            }
            if ($saveData['status'] == 2) {
                $msgData['title'] = $toTitle;
                $msgData['content'] ='抱歉！管理员拒绝了里您的发票申请。';
                model('AppMessage')->postMessage($this->mid, $msgData);
            }
            //发送微信通知 确认开票&绑定微信
            $uid = $row['uid'];
            $touser = M('wx_user')->where("uid=" . $uid)->getField('openID');
            if ($_POST['status'] == 1 && $touser) {
                $data['type'] = 5; //5、发票提醒
                $data['first'] = '您好，您的发票已开具成功。';
                $data['key1'] = $row['bill_no']; //发票号码：144031539110
                $data['key2'] = date("Y-m-d"); //开票日期：2015年7月31日
                $data['key3'] = '工业和信息化部电信研究院'; //开票企业：工业和信息化部电信研究院
                $data['key4'] = $row['title']; //发票抬头：XX
                $data['key5'] = $row['amount']; //票面金额：30.47
                $data['ctime'] = date("Y-m-d H:i:s");
                M('wx_push')->add($data);
            }
            
            $this->assign('jumpUrl', U('admin/Content/bill'));
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /* 终端用户体验 & IOT信息收集 */

    public function ask_experince() {
        // 安全过滤
        $_POST = array_map('t', $_POST);
        $map = $this->_getSearchMap(array('in' => array('name', 'phone', 'email', 'company', 'uid', 'status')));
        $this->assign('type', $_POST['type']);
        $listData = M('ask_experience')->where($map)->order('id DESC')->findPage();
        $this->assign($listData);
        $this->assign($_POST);
        $this->display();
    }

    //体验终端查询
    public function doSearchexp() {
        if (!empty($_POST)) {
            $_SESSION['admin_searchExp'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_searchExp']);
        } else {
            unset($_SESSION['admin_searchExp']);
        }
        $fields = array('name', 'phone', 'email', 'company', 'uid', 'status');
        $map = array();
        foreach ($fields as $v)
            if (isset($_POST[$v]) && $_POST[$v] != '')
                $map[$v] = array('in', explode(',', $_POST[$v]));

        if (isset($_POST['company']) && $_POST['company'] != '') {
            $map['company'] = array('exp', 'LIKE "%' . $_POST['company'] . '%"');
        }
        $listData = D('ask_experience')->where($map)->order('id DESC')->findPage();
        $this->assign($listData);
        $queryCode = urlencode(base64_encode(serialize($map)));
        $this->assign('type', 'searchexp');
        $this->assign('queryCode', $queryCode);
        $this->display('ask_experince');
    }

    public function ask_iot() {
        // 安全过滤
        $_POST = array_map('t', $_POST);
        $map = $this->_getSearchMap(array('in' => array('company', 'phone','contact','uid', 'status')));
        $listData = M('ask_iot')->where($map)->order('id DESC')->findPage();
        $this->assign($listData);
        $this->assign($_POST);
        $this->display();
    }

    //iot查询
    public function doSearchiot() {
        if (!empty($_POST)) {
            $_SESSION['admin_searchIot'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_searchIot']);
        } else {
            unset($_SESSION['admin_searchIot']);
        }
        $fields = array('company', 'phone', 'contact', 'uid', 'status');
        $map = array();
        foreach ($fields as $v)
            if (isset($_POST[$v]) && $_POST[$v] != '')
                $map[$v] = array('in', explode(',', $_POST[$v]));

        //时，模糊查询
        if (isset($_POST['company']) && $_POST['company'] != '') {
            $map['company'] = array('exp', 'LIKE "%' . $_POST['company'] . '%"');
        }
        $listData = D('ask_iot')->where($map)->order('id DESC')->findPage();
        $this->assign($listData);
        $queryCode = urlencode(base64_encode(serialize($map)));
        $this->assign('type', 'searchIot');
        $this->assign('queryCode', $queryCode);
        // $_POST = array_map('t', $_POST);
        // $map = $this->_getSearchMap(array('in' => array('company', 'phone','contact','uid', 'status')));
        $this->display('ask_iot');
    }

    public function appTask_net() {
        // 安全过滤
        $_POST = array_map('t', $_POST);
        $map = $this->_getSearchMap(array('in' => array('uuid', 'user_id', 'apk_type', 'task_status')));
        if (isset($_GET['package_type'])) {
            $map['package_type'] = $_GET['package_type']; //测试包类型
        }
        $map['status'] = 1; //只取提交成功的任务
        $map['package_type'] = 6;
        $listData = M('app_test_task')->where($map)->order('ctime DESC')->findPage(40);
        $this->assign('appTypeConfig', AppTestVars::getAppTypeConfig());
        $this->assign('appTestConfig', AppTestVars::getAppTestConfig());
        $this->assign($listData);
        $this->assign($_POST);
        $this->display();
    }

    public function appTask_look() {
        $map['id'] = $_REQUEST['id'];
        $task_look = M('app_test_task')->where($map)->find();
        $account=json_decode($task_look['account'], TRUE);
        $memo = json_decode($task_look['memo'], TRUE);
        $this->assign('task_look', $task_look);
        $this->assign('account', $account);
        $this->assign('memo', $memo);
        $this->display();
    }

    //完成测试
    public function doTask() {
        if ($_POST) {
            $this->saveFile('app_test_task_file_url');
            if($_POST['status']==2){
            $saveData['mtime'] = time();
            }
            if (isset($_POST['id']) && intval($_POST['id']) > 0) {
                $saveData['id'] = $_POST['id'];
            }
              if (isset($_POST['status'])) {
                $saveData['task_status'] = $_POST['status'];
            }
            if (isset($_POST['file_url'])) {
                $saveData['file_url'] = $_POST['file_url'];
            }
            if (isset($_POST['attach_file_id'])) {
                $saveData['attach_file_id'] = $_POST['attach_file_id'];
            }
        }
        //保存格式问题
        $model = M('app_test_task');
        $data[] = '内容 - 应用测试 - 网络友好提交测试报告';
        $data[] = $row = $model->where("id=" . $_POST['id'])->find();
        $ret = $model->save($saveData);

        if ($ret) {
            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '3';
            $data[] = $row = $model->where("id=" . $_POST['id'])->find();
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);

            $this->assign('jumpUrl', U('admin/Content/appTask_net'));
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    //导出iot信息
    public function IotExcel() {
        include_once( SITE_PATH . '/addons/libs/PHPExcel.php' );
        $map = unserialize(base64_decode($_GET['queryCode']));
        $iotList = D('ask_iot')->where($map)->select();
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'ID')
                ->setCellValue('B1', '用户名')
                ->setCellValue('C1', '单位名称')
                ->setCellValue('D1', '地址')
                ->setCellValue('E1', '联系人')
                ->setCellValue('F1', '邮箱')
                ->setCellValue('G1', '电话')
                ->setCellValue('H1', '传真')
                ->setCellValue('I1', 'QQ/微信')
                ->setCellValue('J1', '状态');
        $n = 2;
        foreach ((array) $iotList as $key => $val) {
            $username= getUserName($val['uid']);
            switch ($val['status']) {
                case 1:
                    $status = '已处理';
                    break;
                case 0:
                    $status = '未处理';
                    break;
            }
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $n, ' ' . $val['id'])
                    ->setCellValue('B' . $n, ' ' . $username)
                    ->setCellValue('C' . $n, ' ' . $val['company'])
                    ->setCellValue('D' . $n, ' ' . $val['address'])
                    ->setCellValue('E' . $n, ' ' . $val['contact'])
                    ->setCellValue('F' . $n, ' ' . $val['email'])
                    ->setCellValue('G' . $n, ' ' . $val['phone'])
                    ->setCellValue('H' . $n, ' ' . $val['fax'])
                    ->setCellValue('I' . $n, ' ' . $val['other'])
                    ->setCellValue('J' . $n, ' ' . $status);
            $n ++;
        }
        $objPHPExcel->setActiveSheetIndex(0);
        $filename = 'iot信息.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    //导出终端体验信息
    public function experinceExcel() {
        include_once( SITE_PATH . '/addons/libs/PHPExcel.php' );
        $map = unserialize(base64_decode($_GET['queryCode']));
        $expList = D('ask_experience')->where($map)->select();
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'ID')
                ->setCellValue('B1', '用户名')
                ->setCellValue('C1', '申请人姓名')
                ->setCellValue('D1', '申请人邮箱')
                ->setCellValue('E1', '申请人手机号')
                ->setCellValue('F1', '申请人公司')
                ->setCellValue('G1', '申请标题')
                ->setCellValue('H1', '申请内容')
                ->setCellValue('I1', '状态');
        $n = 2;
        foreach ((array) $expList as $key => $val) {
            $username = getUserName($val['uid']);
            switch ($val['status']) {
                case 1:
                    $status = '已处理';
                    break;
                case 0:
                    $status = '未处理';
                    break;
            }
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $n, ' ' . $val['id'])
                    ->setCellValue('B' . $n, ' ' . $username)
                    ->setCellValue('C' . $n, ' ' . $val['name'])
                    ->setCellValue('D' . $n, ' ' . $val['email'])
                    ->setCellValue('E' . $n, ' ' . $val['phone'])
                    ->setCellValue('F' . $n, ' ' . $val['company'])
                    ->setCellValue('G' . $n, ' ' . $val['title'])
                    ->setCellValue('H' . $n, ' ' . $val['content'])
                    ->setCellValue('I' . $n, ' ' . $status);
            $n ++;
        }
        $objPHPExcel->setActiveSheetIndex(0);
        $filename = '终端体验信息.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    //FAQ信息管理
    public function faq() {
        if(!$_POST['type'])
            unset($_POST['type']);

        // 安全过滤
        $_POST    = array_map('t',$_POST);
        $map      = $this->_getSearchMap(array('in' => array('id', 'type', 'title')));
        $listData = M('faq')->where($map)->order('display_order asc')->findPage();
        $faqTypeConfig = SiteConfigVars::getFaqTypeConfig();
        $this->assign('faqTypeConfig', $faqTypeConfig);
        $this->assign($listData);
        $this->assign($_POST);
        $this->display();
    }

    public function editFaq() {
        $model=M('faq');
        if ($_POST) {
            $count = $model->max('display_order');
            $add = true;
            $saveData['mtime'] = time();
            if (isset($_POST['id']) && intval($_POST['id']) > 0) {
                $saveData['id']    = $_POST['id'];
                $add               = false;
            }else {
                $saveData['ctime']   = time();
                $saveData['display_order'] = $count + 1;
            }
            if (isset($_POST['title'])) {
                $saveData['title']   = $_POST['title'];
            }
            if (isset($_POST['content'])) {
                $saveData['content'] = $_POST['content'];
            }
            if (isset($_POST['type'])) {
                $saveData['type'] = $_POST['type'];
            }
            $action = $add ? 'add' : 'save';
            $ret = $model->$action($saveData);

            if ($ret) {
                $logName = $add ? '增加' : '修改';
                $_LOG['uid']   = $this->mid;
                $_LOG['type']  = $add ? '1' :'3';
                $data[]        = '内容 - FAQ - '.$logName;
                $data[]        = $saveData;
                $_LOG['data']  = serialize($data);
                $_LOG['ctime'] = time();
                M('AdminLog')->add($_LOG);
                $this->assign('jumpUrl', U('admin/Content/faq'));
                $this->success('保存成功');
            }else{
                $this->error('保存失败');
            }
        }
        
        if ($_GET['id']) {
            $map['id'] = $_GET['id'];
            $listData  = $model->where($map)->find();
        }

        $faqTypeConfig = SiteConfigVars::getFaqTypeConfig();
        $this->assign('faqTypeConfig', $faqTypeConfig);
        $this->assign($listData);
        $this->display();
    }

}