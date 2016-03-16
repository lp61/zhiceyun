<?php

class MessageAction extends Action {
    /* 系统消息 */

    function _initialize() {
        if (!$this->mid) {
            redirect(U('home/Public/login'));
        }
    }

    /*
     * 消息列表
     * +----------------------
     * type 1-标记已读 2-删除 3-清空
     */

    public function index() {
        if ($_POST) {
            $where['to_uid'] = $this->mid;
            if ($_POST['type'] == 1) {
                $where['id'] = array('in', $_POST['msg_id']);
                $save['is_read'] = 1;
                M('app_message')->where($where)->save($save);
            }
            if ($_POST['type'] == 2) {
                $where['id'] = array('in', $_POST['msg_id']);
                $save['is_del'] = 1;
                $save['is_read'] = 1;
                M('app_message')->where($where)->save($save);
            }
            if ($_POST['type'] == 3) {
                $where['is_del'] = 0;
                $save['is_del'] = 1;
                $save['is_read'] = 1;
                M('app_message')->where($where)->save($save);
            }
        }

        $map['to_uid'] = $this->mid;
        $map['is_del'] = 0;
        $listData = M('app_message')->where($map)->order('ctime DESC')->findPage();

        $map['is_read'] = 0;
        $unReadCount = M('app_message')->where($map)->count();

        $this->assign($listData);
        $this->assign('unReadCount', (int) $unReadCount);
        $this->setTitle('消息列表');
        $this->display();
    }

    // 消息详情
    public function detail() {
        $map['id'] = $_GET['id'];
        $listData = M('app_message')->where($map)->order('ctime DESC')->find();
        if (!$listData['is_read']) {
            M('app_message')->where($map)->setField('is_read', 1);
        }

        $this->assign('vo', $listData);
        $this->display();
    }

    //ajax获取未读消息
    public function getNoreadMsg() {
        $map['to_uid'] = $this->mid;
        $map['is_del'] = 0;
        $map['is_read'] = 0;
        //$unReadList = M('app_message')->where($map)->order('ctime DESC')->findPage(5);
        $unReadList['count'] = M('app_message')->where($map)->count();

        $where['to_uid'] = $this->mid;
        $where['ctime'] = array('egt', time() - 15);
        $unReadList['number'] = M('app_message')->where($where)->count();
        echo json_encode($unReadList);
    }

}
