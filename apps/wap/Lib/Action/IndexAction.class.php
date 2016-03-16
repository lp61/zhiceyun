<?php

class IndexAction extends Action {

    //首页
    public function index() {
        $typeArr = array(2, 3, 4, 5);
        foreach ($typeArr as $type) {
            $map['type'] = $type;
            $article[] = M('app_article')->where($map)->order('ctime desc')->find();
        }
        $this->assign('article', $article);
        $this->display();
    }

}
