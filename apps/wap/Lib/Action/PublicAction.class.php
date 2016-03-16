<?php

/**
 * Description of PublicAction
 *
 * @author vini
 */
class PublicAction extends Action {

    //put your code here

    public function aboutUs() {
        $this->assign('title', '关于我们');
        $this->assign('toUrl', 'javascript:history.go(-1);void(0);');
        $this->display();
    }

    public function aboutUs_banner() {
        $this->assign('title', '关于我们');
        $this->assign('toUrl', 'javascript:history.go(-1);void(0);');
        $this->display();
    }

    public function experience() {
        $this->assign('title', '终端用户体验');
        $this->assign('toUrl', U('wap/Index/index', array('source' => 'source')));
        $this->display();
    }

    public function iot() {
        $this->assign('title', 'IOT');
        $this->assign('toUrl', U('wap/Index/index', array('source' => 'source')));
        $this->display();
    }

    public function source() {
        $this->assign('title', '模拟网络资源');
        $this->assign('toUrl', U('wap/Index/index', array('source' => 'source')));
        $this->display();
    }

    public function pressYun() {
        $this->assign('title', '压力云');
        $this->assign('toUrl', U('wap/Index/index', array('source' => 'source')));
        $this->display();
    }

    public function stork() {
        $mo2 = M('app_client')->select();
        $map = array('isnew' => 1);
        $mo1 = M('app_client')->where($map)->select();
        $this->assign('mo1', $mo1);
        $this->assign('mo2', $mo2);
        $this->assign('title', '终端库');
        $this->assign('toUrl', U('wap/Index/index', array('source' => 'source')));
        $this->display();
    }

    public function testCircle() {
        $map1['type'] = 2;
        $article1 = M('app_article')->where($map1)->order('ctime desc')->limit('0,4')->select();
        $map2['type'] = 3;
        $article2 = M('app_article')->where($map2)->order('ctime desc')->limit('0,4')->select();
        $map3['type'] = 4;
        $article3 = M('app_article')->where($map3)->order('ctime desc')->limit('0,4')->select();
        $map4['type'] = 5;
        $article4 = M('app_article')->where($map4)->order('ctime desc')->limit('0,4')->select();
        $this->assign('article1', $article1);
        $this->assign('article2', $article2);
        $this->assign('article3', $article3);
        $this->assign('article4', $article4);

        $this->assign('title', '测试圈');
        $this->assign('toUrl', U('wap/Index/index', array('testCircle' => 'info')));
        $this->display();
    }

}
