<?php

/**
 * Description of ApplyAction
 *
 * @author vini
 */
class ApplyAction extends Action {

    //put your code here
    public function test() {
        $this->assign('title', '应用测试');
        $this->assign('toUrl', 'javascript:history.go(-1);void(0);');
        $this->display();
    }

    public function fastTest() {
        $this->assign('testTitle', '快速测试');
        $this->display();
    }

    public function compatibleTest() {
        $this->assign('testTitle', '兼容性测试');
        $this->display();
    }

    public function friendTest() {
        $this->assign('testTitle', '网络友好测试');
        $this->display();
    }
    
    public function newPhone() {
        $this->assign('testTitle', '新机兼容测试');
        $this->display();
    }

    public function pressTest() {
        $this->assign('testTitle', '压力测试');
        $this->display();
    }

    public function weakTest() {
        $this->assign('testTitle', '弱网络测试');
        $this->display();
    }

}
