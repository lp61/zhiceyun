<?php
class AdministratorAction extends Action {
    private $parentTitle = '后台';

    public function _initialize()
    {
        // $this->success(); 和 $this->error();通过isAdmin变量决定是否加载头部
        $this->assign('isAdmin', 1);
        
        // 检查用户是否登录管理后台, 有效期为$_SESSION的有效期
        if (!service('Passport')->isLoggedAdmin())
            redirect( U('home/Public/adminlogin') );
        
        // 如果是应用的后台，检查用户是否具有节点权限
        if (APP_NAME != 'admin' && ! service('SystemPopedom')->hasPopedom($this->mid, 'admin/Index/index', false)) {
            $this->assign('noJump', 1);
            $this->error('您无权限查看');
        }
    }
    
    protected function _getSearchMap($fields)
    {
        // 为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['admin_search_attach'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_search_attach']);
        } else {
            unset($_SESSION['admin_search_attach']);
        }
        
        // 组装查询条件
        $map = array();
        foreach ($fields as $k => $v) {
            foreach ($v as $field) {
                if (isset($_POST[$field]) && $_POST[$field] != '') {
                    if($k == 'in')
                        $map[$field] = array($k, explode(',', $_POST[$field]));
                    else
                        $map[$field] = array($k, $_POST[$field]);                   
                }
            }
        }
        
        return $map;
    }

    public function doDelData () {
        if( empty($_POST['ids']) ) {
            echo 0;
            exit ;
        }
        $tableName = $_POST['tableName'];
        $title     = $_POST['title'] ? $_POST['title']:$tableName;

        $_LOG['uid']   = $this->mid;
        $_LOG['type']  = '2';
        $data[]        = $this->parentTitle .' - '.$title.' - 进入回收站';
        $map['id']     = array('in',t($_POST['ids']));
        $data[]        = M( $tableName )->where($map)->findAll();
        $_LOG['data']  = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);
        
        $where['id']       = array('in',t($_POST['ids']));
        echo M( $tableName )->where( $where )->delete() ? '1' : '0';
    }

    public function setStatus() {
        $_POST['id'] = intval($_POST['id']);
        $status      = intval($_POST['status']);
        $tableName   = $_POST['tableName'];
        $field       = $_POST['field'];
        $title       = $_POST['title'] ? $_POST['title']:$tableName;
        $setTitle    = $_POST['setTitle'] ? $_POST['setTitle']:$field;

        if ( $_POST['id'] <= 0) {
            echo 0;
            exit;
        }
        $data[]        = $this->parentTitle .' - '.$title.' - '.$setTitle;
        $map['id']     = $_POST['id'];
        $data[]        = M( $tableName )->where($map)->find();

        $res = M($tableName)->where('`id`=' . $_POST['id'])->setField( $field, $status );
        if ($res) {
            $_LOG['uid']   = $this->mid;
            $_LOG['type']  = '3';
            $_LOG['data']  = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);
        }
        echo $res ? 1:0;
    }
}