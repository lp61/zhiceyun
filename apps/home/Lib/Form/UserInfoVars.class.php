<?php

/**
 *  用户相关信息配置
 */
class UserInfoVars {

    //审核状态
    const NO_AUDIT = 2; //审核不通过
    const YES_AUDIT = 1; //审核通过
    const IS_AUDITING = 0; //待审核
    
    //状态分类
    const IS_ACTIVE = 1; //已经激活
    const NO_ACTIVE = 0; //未激活
    
    //用户类型
    const USER_NORMAL = 0; //普通用户
    const USER_VIP = 1; //vip
    const USER_COM = 2; //企业用户

    /**
     * [$USER_TYPE_CONFIG 用户配置 name:名称，validate:验证标识]
     * @var array
     */

    public static $USER_TYPE_CONFIG = array(
        self::USER_NORMAL => array(
            'name' => '普通开发者',
            'validate' => 'normal'
        ),
        self::USER_VIP => array(
            'name' => 'VIP开发者',
            'validate' => 'vip'
        ),
        self::USER_COM => array(
            'name' => '企业开发者',
            'validate' => 'company'
        ),
    );

    /**
     * [$WORK_CLASSIFY 工作行业]
     * @var array
     */
    public static $WORK_CLASSIFY = array(
        1 => '游戏',
        2 => '时尚与购物',
        3 => '新闻',
        4 => '影音视听',
        5 => '聊天与社交',
        6 => '实用工具',
        7 => '学习与教育',
        8 => '办公效率',
        9 => '理财',
        10 => '摄影摄像',
        11 => '医疗与健康',
        12 => '其他',
    );

    /**
     * [$WORK_STATION 岗位]
     * @var array
     */
    public static $WORK_STATION = array(
        1 => '测试工程师',
        2 => '研发工程师',
        3 => '运维工程师',
        4 => '市场运营人员',
        5 => '售前/售后技术支持',
        6 => '架构师',
        7 => '产品经理/项目经理',
        8 => 'IT咨询/顾问',
        9 => '企业高管',
        10 => '其他',
    );

    /**
     * [$COMPANY_SIZE 公司规模]
     * @var array
     */
    public static $COMPANY_SIZE = array(
        1 => '1-10人',
        2 => '11-50人',
        3 => '51-100人',
        4 => '100人以上',
    );
    public static $MESSAGE_CONFIG = array(
        'register' => array(
            'title' => '恭喜您，注册成功！',
            'content' => '您已经注册成功【智测云】账号，享受更多优质测试服务！！',
        ),
    );

    /**
     * [getWorkClassify 工作行业信息]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public static function getWorkClassify($id = null) {
        $resArr = self::$WORK_CLASSIFY;
        return !empty($id) ? $resArr[$id] : $resArr;
    }

    /**
     * [getWorkStation 岗位信息]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public static function getWorkStation($id = null) {
        $resArr = self::$WORK_STATION;
        return !empty($id) ? $resArr[$id] : $resArr;
    }

    /**
     * [getCompanySize 公司规模信息]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public static function getCompanySize($id = null) {
        $resArr = self::$COMPANY_SIZE;
        return !empty($id) ? $resArr[$id] : $resArr;
    }

}
