<?php
class UserAction extends Action {

    const INDEX_TYPE_WEIBO = 0;
    const INDEX_TYPE_GROUP = 1;
    const INDEX_TYPE_ALL   = 2;

    function _initialize() {
        if (!$this->mid) {
            redirect(U('home/Index/index'));
        }
    }

    //个人首页
    function index() {
        redirect(U('home/Account/index'));
    }
}
?>