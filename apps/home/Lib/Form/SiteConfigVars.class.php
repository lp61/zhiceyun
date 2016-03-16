<?php

/**
 * Description of SiteVars
 *
 * @author vini
 */
class SiteConfigVars {

    //状态分类
    const IS_ACTIVE = 1; // 已激活
    const NO_ACTIVE = 0; // 未激活
    
    //FAQ类型设置
    const FAQ_TYPE1 = 1; //常见问题
    const FAQ_TYPE2 = 2; //功能使用
    const FAQ_TYPE3 = 3; //账户设置

    public static $FAQ_TYPE_CONFIG = array(
        self::FAQ_TYPE1 => array(
            'id' => self::FAQ_TYPE1,
            'title' => '常见问题',
            'is_active' => self::IS_ACTIVE,
        ),
        self::FAQ_TYPE2 => array(
            'id' => self::FAQ_TYPE2,
            'title' => '功能使用',
            'is_active' => self::IS_ACTIVE,
        ),
        self::FAQ_TYPE3 => array(
            'id' => self::FAQ_TYPE3,
            'title' => '账户设置',
            'is_active' => self::IS_ACTIVE,
        ),
    );

    /**
     * [getAppTestConfig 取测试应用列表]
     * @param  integer $is_active [激活状态， 1激活，0未激活， -1取全部]
     * @return [type]             [description]
     */
    public static function getFaqTypeConfig($is_active = 1) {
        $faqTypeConfig = self::$FAQ_TYPE_CONFIG;

        if ($is_active != -1) {
            foreach ($faqTypeConfig as $key => &$_one) {
                if ($_one['is_active'] != $is_active) {
                    unset($faqTypeConfig[$key]);
                }
            }
        }
        return $faqTypeConfig;
    }

}
