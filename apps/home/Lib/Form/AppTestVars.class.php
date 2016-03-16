<?php
/**
 *  应用测试相关信息配置
 */
class AppTestVars {

    const IS_ACTIVE = 1; // 已激活
    const NO_ACTIVE = 0; // 未激活

    //测试业务类型
    const QUICK_TEST        = 1; // 快速测试
    const COMPATIBLE_TEST   = 2; // 兼容测试
    const NETWORK_TEST      = 3; // 网络友好测试
    const NEW_MAC_COMP_TEST = 4; // 新机兼容测试
    const SPECIAL_MAC_TEST  = 5; // 专机测试
    const WEAK_NETWORK_TEST = 6; // 弱网络测试
    const SAFETY_TEST       = 7; // 安全测试
    const QH_TEST   = 8; // 清华兼容测试
    
    //测试包类型
    const QUICK_PACKAGE         = 1; // 快速测试包
    const COMP_HIGEH_PACKAGE    = 2; // 兼容测试-高覆盖率包
    const COMP_NEW_PACKAGE      = 3; // 兼容测试-最新机型包
    const COMP_OPT_PACKAGE      = 4; // 兼容测试-自选终端包
    const NET_FREE_PACKAGE      = 5; // 网络友好-免费测试包
    const NET_DEEP_PACKAGE      = 6; // 网络友好-深度测试包
    const NEW_PACKAGE           = 7; // 新机兼容测试包
    const SPECIAL_PACKAGE       = 8; // 专机测试包
    const QH_HIGEH_PACKAGE    = 9; // 清华兼容测试-高覆盖率包
    const QH_NEW_PACKAGE      = 10; // 清华兼容测试-最新机型包
    const QH_OPT_PACKAGE      = 11; // 清华兼容测试-自选终端包
    
    //任务源类型
    const APP_TEST_SOURCE1 = 1; //前端网站
    const APP_TEST_SOURCE2 = 2; //应用宝
    
    //APP类型
    const APP_TYPE0 = 19; //未分类
    const APP_TYPE1 = 1; //社交网络
    const APP_TYPE2 = 2; //生活娱乐
    const APP_TYPE3 = 3; //系统工具
    const APP_TYPE4 = 4; //教育学习
    const APP_TYPE5 = 5; //商务办公
    const APP_TYPE6 = 6; //电商导购
    const APP_TYPE7 = 7; //通信聊天
    const APP_TYPE8 = 8; //新闻资讯
    const APP_TYPE9 = 9; //图书阅读
    const APP_TYPE10 = 10; //游戏
    const APP_TYPE11 = 11; //健康医疗
    const APP_TYPE12 = 12; //金融理财
    const APP_TYPE13 = 13; //视频播放
    const APP_TYPE14 = 14; //旅游出行
    const APP_TYPE15 = 15; //音乐音频
    const APP_TYPE16 = 16; //拍摄美化
    const APP_TYPE17 = 17; //主题壁纸
    const APP_TYPE18 = 18; //交通导航

    //手机终端属性
    const APP_SYSTEM_MODEL      = 1;  // 终端机型（品牌）
    const APP_SYSTEM_OS         = 2;  // 系统
    const APP_SYSTEM_SIZE       = 3;  // 屏幕尺寸
    const APP_SYSTEM_RESOLUTION = 4;  // 屏幕分辨率
    const APP_SYSTEM_COVERAGE   = 5;  // 用户覆盖率

    //测试圈分类
    const APP_ARTICLE_NEWS  = 1; // 新闻
    const APP_ARTICLE_INFOR = 2; // 资讯
    const APP_ARTICLE_TECH  = 3; // 技术
    const APP_ARTICLE_TOPIC = 4; // 专题
    const APP_ARTICLE_SALON = 5; // 沙龙

    //终端排行分类
    const APP_RANK_TOTEL   = 1; // 总得分
    const APP_RANK_NETWORK = 2; // 网络友好
    const APP_RANK_POWER   = 3; // 省电应用

    public static $APP_TEST_CONFIG = array(
        self::QUICK_TEST => array(
            'id'         => self::QUICK_TEST, //businessID
            'name'       => 'quick_test',
            'title'       => '快速测试',
            'is_active'  => self::IS_ACTIVE,
            'packages' => array(
                self::QUICK_PACKAGE => array(
                    'package_type' => self::QUICK_PACKAGE, //packageType
                    'title' => '快速测试包',
                )
            ),
        ),
        self::COMPATIBLE_TEST => array(
            'id'         => self::COMPATIBLE_TEST,
            'name'       => 'compatible_test',
            'title'      => '兼容性测试',
            'is_active'  => self::IS_ACTIVE,
            'packages' => array(
                self::COMP_HIGEH_PACKAGE => array(
                    'package_type' => self::COMP_HIGEH_PACKAGE,
                    'title' => '覆盖率最高',
                ),
                self::COMP_NEW_PACKAGE => array(
                    'package_type' => self::COMP_NEW_PACKAGE,
                    'title' => '最新上市',
                ),
                self::COMP_OPT_PACKAGE => array(
                    'package_type' => self::COMP_OPT_PACKAGE,
                    'title' => '自由选择',
                ),
            ),
        ),
        self::NETWORK_TEST => array(
            'id'         => self::NETWORK_TEST,
            'name'       => 'network_test',
            'title'      => '网络友好测试',
            'is_active'  => self::IS_ACTIVE,
            'packages' => array(
                self::NET_FREE_PACKAGE => array(
                    'package_type' => self::NET_FREE_PACKAGE,
                    'title' => '免费测试',
                ),
                self::NET_DEEP_PACKAGE => array(
                    'package_type' => self::NET_DEEP_PACKAGE,
                    'title' => '深度测试',
                ),
            ),
        ),
        self::NEW_MAC_COMP_TEST => array(
            'id'         => self::NEW_MAC_COMP_TEST,
            'name'       => 'new_mac_comp_test',
            'title'      => '新机兼容测试',
            'is_active'  => self::IS_ACTIVE,
            'packages' => array(
                self::NEW_PACKAGE => array(
                    'package_type' => self::NEW_PACKAGE,
                    'title' => '新机兼容测试包',
                ),
            ),
        ),
        self::SPECIAL_MAC_TEST => array(
            'id'         => self::SPECIAL_MAC_TEST,
            'name'       => 'special_mac_test',
            'title'      => '专机测试',
            'is_active'  => self::IS_ACTIVE,
            'packages' => array(
                self::SPECIAL_PACKAGE => array(
                    'package_type' => self::SPECIAL_PACKAGE,
                    'title' => '专机测试包',
                ),
            ),
        ),
        self::WEAK_NETWORK_TEST => array(
            'id'         => self::WEAK_NETWORK_TEST,
            'name'       => 'weak_network_test',
            'title'      => '弱网络测试',
            'is_active'  => self::NO_ACTIVE,
            'packages' => array(),
        ),
        self::SAFETY_TEST => array(
            'id'         => self::SAFETY_TEST,
            'name'       => 'safety_test',
            'title'      => '安全测试',
            'is_active'  => self::NO_ACTIVE,
            'packages' => array(),
        ),
    );
    /**
     * [getAppTestConfig 取测试应用列表]
     * @param  integer $is_active [激活状态， 1激活，0未激活， -1取全部]
     * @return [type]             [description]
     */
    public static function getAppTestConfig($is_active=1) {
        $appTestConfig = self::$APP_TEST_CONFIG;

        foreach ($appTestConfig as $key => &$_one) {
            $_one['name'] = L($_one['name']);
            if ($is_active == -1) {

            }elseif($_one['is_active'] != $is_active) {
                unset($appTestConfig[$key]);
            }
        }
        return $appTestConfig;
    }

    public static $APP_SYSTEM_CONFIG = array(
        self::APP_SYSTEM_MODEL => array(
            'id'           => self::APP_SYSTEM_MODEL,
            'name'         => '终端品牌',
            'adapter_name' => 'model', // 数据库映射字段
            'is_show'      => true,
        ),
        self::APP_SYSTEM_OS => array(
            'id'           => self::APP_SYSTEM_OS,
            'name'         => '系统版本',
            'adapter_name' => 'os',
            'is_show'      => true,
        ),
        self::APP_SYSTEM_SIZE => array(
            'id'           => self::APP_SYSTEM_SIZE,
            'name'         => '屏幕尺寸',
            'adapter_name' => 'size',
            'is_show'      => true,
        ),
        self::APP_SYSTEM_RESOLUTION => array(
            'id'           => self::APP_SYSTEM_RESOLUTION,
            'name'         => '屏幕分辨率',
            'adapter_name' => 'resolution',
            'is_show'      => true,
        ),
        self::APP_SYSTEM_COVERAGE => array(
            'id'           => self::APP_SYSTEM_COVERAGE,
            'name'         => '用户覆盖率',
            'adapter_name' => 'coverage',
            'is_show'      => false,
        ),
    );

    /**
     * [getAppSystemConfig 取测试系统配置]
     * @return [type]    [description]
     */
    public static function getAppSystemConfig($is_show = -1) {
        $appSystemConfig = self::$APP_SYSTEM_CONFIG;
        foreach ($appSystemConfig as $key => &$_one) {
            $_one['name'] = L($_one['name']);
            if ($is_show == -1) {

            }elseif($_one['is_show'] != $is_show) {
                unset($appSystemConfig[$key]);
            }
        }
        return $appSystemConfig;
    }

    public static $APP_ARTICLE_CONFIG = array(
        // self::APP_ARTICLE_NEWS => array(
        //     'id'           => self::APP_ARTICLE_NEWS,
        //     'name'         => '新闻',
        // ),
        self::APP_ARTICLE_INFOR => array(
            'id'           => self::APP_ARTICLE_INFOR,
            'name'         => '资讯',
            'limit'        => 8,
            'logo'         => '__THEME__/images/testCircle2.jpg',
            'class'        => 'infom',
        ),
        self::APP_ARTICLE_TECH => array(
            'id'           => self::APP_ARTICLE_TECH,
            'name'         => '技术',
            'limit'        => 8,
            'logo'         => '__THEME__/images/testCircle2.jpg',
            'class'        => 'skill',
        ),
        self::APP_ARTICLE_TOPIC => array(
            'id'           => self::APP_ARTICLE_TOPIC,
            'name'         => '专题',
            'limit'        => 8,
            'logo'         => '__THEME__/images/testCircle2.jpg',
            'class'        => 'special',
        ),
        self::APP_ARTICLE_SALON => array(
            'id'           => self::APP_ARTICLE_SALON,
            'name'         => '沙龙',
            'limit'        => 8,
            'logo'         => '__THEME__/images/testCircle2.jpg',
            'class'        => 'sal',
        ),
    );

    /**
     * [getAppArticleConfig 取文章配置]
     * @return [type]    [description]
     */
    public static function getAppArticleConfig() {
        $appArticleConfig = self::$APP_ARTICLE_CONFIG;
        return $appArticleConfig;
    }
    
    /**
     * [$WORK_CLASSIFY 工作行业]
     * @var array
     */
    public static $APP_TYPE_CONFIG = array(
        self::APP_TYPE0 => array(
            'id'           => self::APP_TYPE0,
            'name'         => '未分类',
        ),
        self::APP_TYPE1 => array(
            'id'           => self::APP_TYPE1,
            'name'         => '社交网络',
        ),
        self::APP_TYPE2 => array(
            'id'           => self::APP_TYPE2,
            'name'         => '生活娱乐',
        ),
        self::APP_TYPE3 => array(
            'id'           => self::APP_TYPE3,
            'name'         => '系统工具',
        ),
        self::APP_TYPE4 => array(
            'id'           => self::APP_TYPE4,
            'name'         => '教育学习',
        ),
        self::APP_TYPE5 => array(
            'id'           => self::APP_TYPE5,
            'name'         => '商务办公',
        ),
        self::APP_TYPE6 => array(
            'id'           => self::APP_TYPE6,
            'name'         => '电商导购',
        ),
        self::APP_TYPE7 => array(
            'id'           => self::APP_TYPE7,
            'name'         => '通信聊天',
        ),
        self::APP_TYPE8 => array(
            'id'           => self::APP_TYPE8,
            'name'         => '新闻资讯',
        ),
        self::APP_TYPE9 => array(
            'id'           => self::APP_TYPE9,
            'name'         => '图书阅读',
        ),
        self::APP_TYPE10 => array(
            'id'           => self::APP_TYPE10,
            'name'         => '游戏',
        ),
        self::APP_TYPE11 => array(
            'id'           => self::APP_TYPE11,
            'name'         => '健康医疗',
        ),
        self::APP_TYPE12 => array(
            'id'           => self::APP_TYPE12,
            'name'         => '金融理财',
        ),
        self::APP_TYPE13 => array(
            'id'           => self::APP_TYPE13,
            'name'         => '视频播放',
        ),
        self::APP_TYPE14 => array(
            'id'           => self::APP_TYPE14,
            'name'         => '旅游出行',
        ),
        self::APP_TYPE15 => array(
            'id'           => self::APP_TYPE15,
            'name'         => '音乐音频',
        ),
        self::APP_TYPE16 => array(
            'id'           => self::APP_TYPE16,
            'name'         => '拍摄美化',
        ),
        self::APP_TYPE17 => array(
            'id'           => self::APP_TYPE17,
            'name'         => '主题壁纸',
        ),
        self::APP_TYPE18 => array(
            'id'           => self::APP_TYPE18,
            'name'         => '交通导航',
        ),
    );
    
    /**
     * [getWorkClassify APK类型信息]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public static function getAppTypeConfig($id=null) {
        $resArr = self::$APP_TYPE_CONFIG;
        return !empty($id) ? $resArr[$id] : $resArr;        
    }

    public static $APP_RANK_CONFIG = array(
        self::APP_RANK_TOTEL => array(
            'id'           => self::APP_RANK_TOTEL,
            'name'         => '总得分',
        ),
        self::APP_RANK_NETWORK => array(
            'id'           => self::APP_RANK_NETWORK,
            'name'         => '网络友好',
        ),
        self::APP_RANK_POWER => array(
            'id'           => self::APP_RANK_POWER,
            'name'         => '省电应用',
        ),
    );
    /**
     * [getAppRankConfig 取测试排行配置]
     * @return [type]    [description]
     */
    public static function getAppRankConfig() {
        $appRankConfig = self::$APP_RANK_CONFIG;
        return $appRankConfig;
    }

    /**
     * [getAppRankConfig 取测试来源配置]
     * @return [type]    [description]
     */
    public static function getAppSourceConfig() {
        $appRankConfig = self::$APP_SOURCE_CONFIG;
        return $appRankConfig;
    }

    public static $APP_SOURCE_CONFIG = array(
        self::APP_TEST_SOURCE1 => array(
            'id'           => self::APP_TEST_SOURCE1,
            'name'         => '网站',
        ),
        self::APP_TEST_SOURCE2 => array(
            'id'           => self::APP_TEST_SOURCE2,
            'name'         => '应用宝',
        ),
    );
    
    public static $UPGRADE_USER_CONFIG =  array(
        1 => array(
            'fields'    => array('phone'),
            'validFunc' => 'upgradePhone',
            'userType'  => 0,
        ),
        2 => array(
            'fields'    => array('company_name', 'work_station', 'work_classify'),
            'validFunc' => 'upgradeVip',
            'userType'  => 1,
        ),
        3 => array(
            'fields'    => array('company_size', 
                // 'licence_img'
            ),
            'validFunc' => 'upgradeCompany',
            'userType'  => 2,
        ),
    );
    
    public static $QUICK_TEST_VERIFY =  array(
        1 => array(
            'fields'    => array('phone'),
            'validFunc' => 'upgradePhone',
            'userType'  => 0,
        ),
        2 => array(
            'fields'    => array('company_name', 'work_station', 'work_classify'),
            'validFunc' => 'upgradeVip',
            'userType'  => 1,
        ),
        3 => array(
            'fields'    => array('company_size', 
                // 'licence_img'
            ),
            'validFunc' => 'upgradeCompany',
            'userType'  => 2,
        ),
    );

}
