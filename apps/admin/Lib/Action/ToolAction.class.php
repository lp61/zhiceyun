<?php

class ToolAction extends AdministratorAction {
    /*
     * 邀请统计
     */

    public function inviteRecord() {
        $records = model('InviteRecord')->getStatistics($_POST['uid']);
        $this->assign($records);
        $this->display();
    }

    public function invitedUser() {
        $users = model('InviteRecord')->getInvitedUser($_GET['uid']);
        $uids = array_merge(array($_GET['uid']), getSubByKey($users['data'], 'fid'));
        D('User', 'home')->setUserObjectCache($uids);
        $this->assign($users);
        $this->assign('uid', $_GET['uid']);
        $this->display();
    }

}
