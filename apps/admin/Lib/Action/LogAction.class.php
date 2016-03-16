<?php

import('home.Form.AppEmailSendVars');

class LogAction extends AdministratorAction {

    protected $parentTitle = '日志管理';

    private function __isValidRequest($field, $array = 'post') {
        $field = is_array($field) ? $field : explode(',', $field);
        $array = $array == 'post' ? $_POST : $_GET;
        foreach ($field as $v) {
            $v = trim($v);
            if (!isset($array[$v]) || $array[$v] == '')
                return false;
        }
        return true;
    }

    /** 内容 - 附件管理 */
    public function creditLog() {
        if (!$_POST['type'])
            unset($_POST['type']);
        // 安全过滤
        $_POST = array_map('t', $_POST);

        $map = $this->_getSearchMap(array('in' => array('id', 'uid', 'credit_name')));
        $this->assign('type', $_POST['type']);

        $listData = M('credit_user_log')->where($map)->order('ctime DESC')->findPage();

        $this->assign($listData);
        $this->assign($_POST);
        $this->display();
    }

    public function sendEmail() {
        if (!$_POST['type'])
            unset($_POST['type']);

        // 安全过滤
        $_POST = array_map('t', $_POST);
        $map = $this->_getSearchMap(array('in' => array('id', 'type', 'email', 'send_status')));
        $listData = M('app_email_send')->where($map)->order('ctime DESC')->findPage();

        $appEmailSendConfig = AppEmailSendVars::getAppEmailSendConfig();
        $this->assign('appEmailSendConfig', $appEmailSendConfig);
        $this->assign($listData);
        $this->assign($_POST);
        $this->display();
    }

    public function editSendEmail() {
        $appEmailSendConfig = AppEmailSendVars::getAppEmailSendConfig();
        if ($_POST) {
            $add = true;
            $saveData['mtime'] = time();
            if (isset($_POST['id']) && intval($_POST['id']) > 0) {
                $saveData['id'] = $_POST['id'];
                $add = false;
            } else {
                $saveData['ctime'] = time();
            }
            if (isset($_POST['email'])) {
                $saveData['email'] = $_POST['email'];
            }
            if (isset($_POST['subject'])) {
                $saveData['subject'] = $_POST['subject'];
            }
            if (isset($_POST['content'])) {
                $saveData['content'] = $_POST['content'];
            }
            if ($add) {
                $saveData['type'] = AppEmailSendVars::TIPS_EMAIL;
                $saveData['send_type'] = $appEmailSendConfig[$saveData['type']]['send_type'];
            }

            $action = $add ? 'add' : 'save';
            $ret = M('app_email_send')->$action($saveData);

            if ($ret) {
                $logName = $add ? '增加' : '修改';
                $_LOG['uid'] = $this->mid;
                $_LOG['type'] = $add ? '1' : '3';
                $data[] = $this->parentTitle . ' - 邮件发送记录 - ' . $logName;
                $data[] = $saveData;
                $_LOG['data'] = serialize($data);
                $_LOG['ctime'] = time();
                M('AdminLog')->add($_LOG);
                $this->assign('jumpUrl', U('admin/Log/sendEmail'));
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        }

        if ($_GET['id']) {
            $map['id'] = $_GET['id'];
            $listData = M('app_email_send')->where($map)->find();
        }

        $this->assign('appEmailSendConfig', $appEmailSendConfig);
        $this->assign($listData);
        $this->display();
    }

    public function doResetSendEmail() {
        if (empty($_POST['ids'])) {
            echo 0;
            exit;
        }
        $tableName = 'app_email_send';
        $title = '邮件发送';

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '2';
        $data[] = $this->parentTitle . ' - ' . $title . ' - 邮件重发';
        $map['id'] = array('in', t($_POST['ids']));
        $data[] = M($tableName)->where($map)->findAll();
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $where['id'] = array('in', t($_POST['ids']));
        $saveData['send_status'] = 0;
        echo M($tableName)->where($where)->save($saveData) ? '1' : '0';
    }

    public function addSendEmail() {
        if ($_GET['uids']) {
            $sWhere['uid'] = array('in', $_GET['uids']);
            $userList = M('user')->where($sWhere)->findAll();
        }

        $appEmailSendConfig = AppEmailSendVars::getAppEmailSendConfig();
        if ($_POST) {
            $_POST['user_group_id'] = intval($_POST['user_group_id']);
            $_logpost = $_POST ? $_POST : '0';

            if ($_POST['user_group_id'] == -1) {
                $this->error('请选择群组');
            }

            if (empty($_POST['subject'])) {
                $this->error('请填写邮件标题');
            }
            if (empty($_POST['content'])) {
                $this->error('请填写邮件内容');
            }

            if ($_POST['uids']) {
                $map['uid'] = array('in', $_POST['uids']);
                $_POST['to'] = M('user')->where($map)->field('email')->findAll();
            }
            // 收件人
            elseif ($_POST['user_group_id'] == 0) {
                // 全部用户
                $_POST['to'] = M('user')->where('`is_active`=1')->field('`uid`,`email`')->findAll();
            } else {
                // 指定用户组
                $_POST['to'] = model('UserGroup')->getUidByUserGroup($_POST['user_group_id']);

                $map['uid'] = array('in', $_POST['to']);
                $_POST['to'] = M('user')->where($map)->field('email')->findAll();
            }

            $saveData['type'] = AppEmailSendVars::TIPS_EMAIL;
            $saveData['send_type'] = $appEmailSendConfig[$saveData['type']]['send_type'];
            $saveData['mtime'] = $saveData['ctime'] = time();
            $saveData['subject'] = $_POST['subject'];
            $saveData['content'] = $_POST['content'];

            $cc = array();
            foreach ($_POST['to'] as $key => $to) {
                if (!empty($to['email'])) {
                    $saveData['email'] = $to['email'];
                    $ret = M('app_email_send')->add($saveData);
                    // var_dump($saveData);exit;
                    if ($ret) {
                        $logName = '群发增加';
                        $_LOG['uid'] = $this->mid;
                        $_LOG['type'] = '1';
                        $data[] = $this->parentTitle . ' - 邮件发送记录 - ' . $logName;
                        $data[] = $saveData;
                        $_LOG['data'] = serialize($data);
                        $_LOG['ctime'] = time();
                        M('AdminLog')->add($_LOG);
                    } else {
                        $cc[] = $to['email'];
                        // $this->error('保存失败');
                    }
                }
            }
            if (!empty($cc)) {
                $emails = explode(',', $cc);
                $this->error($emails . '进入发送邮件队列失败');
            }
            $this->assign('jumpUrl', U('admin/Log/sendEmail'));
            $this->success('保存成功');
        }

        $user_group_list = model('UserGroup')->field('`user_group_id`,`title`')->findAll();
        $this->assign('user_group_list', $user_group_list);
        $this->assign('userList', $userList);
        $this->assign('uids', $_GET['uids']);
        $this->display();
    }

    public function doSendEmail() {
        $_POST['id'] = intval($_POST['id']);

        if ($_POST['id'] <= 0) {
            echo 0;
            exit;
        }
        $data[] = $this->parentTitle . ' - 邮件发送记录 - 立即发送';
        $map['id'] = $_POST['id'];
        $data[] = $info = M('app_email_send')->where($map)->find();
        $res = service('Mail')->send_email($info['email'], $info['subject'], $info['content']);
        if ($res) {
            model('AppEmailSend')->setSendStatus($info['id']);
            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '3';
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);
        } else {
            model('AppEmailSend')->setSendStatus($info['id'], AppEmailSendVars::FAIL_SENDING);
        }

        echo $res ? 1 : 0;
    }

}
