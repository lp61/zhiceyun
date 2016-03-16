<?php
/**
 *  使用 方法 E:\wamp\bin\php\php5.5.12\php cron_index.php test\run
 *
 * 	cron_index.php：cron入口文件 处理业务分发
 * 	test\run ：执行脚本文件\方法 testCronAction run()
 */


error_reporting(E_ERROR | E_PARSE | E_STRICT);
ini_set('display_errors', 0);

$actionString = $_SERVER['argv'][1];
if (empty($actionString)) {
	echo 'no action!!';
	return false;
}

$string = explode('/', str_replace('\\', '/', $actionString));
$module = $string[0];
$action = $string[1];
$module .= 'CronAction';


//网站根路径设置
define('SITE_PATH', getcwd());
//载入核心文件
//require(SITE_PATH.'/../core/core.php');
$GLOBALS['_beginTime'] = microtime(TRUE);

//核心路径定义
if(!defined('SITE_PATH'))		define('SITE_PATH'	, dirname(getcwd()));
if(!defined('CORE_PATH'))		define('CORE_PATH'	, SITE_PATH.'/core');
if(!defined('APPS_PATH'))		define('APPS_PATH'	, SITE_PATH.'/apps');
if(!defined('ADDON_PATH'))		define('ADDON_PATH'	, SITE_PATH.'/addons');
if(!defined('SITE_DATA_PATH'))	define('SITE_DATA_PATH'	, SITE_PATH.'/data');
if(!defined('UPLOAD_PATH'))		define('UPLOAD_PATH'	, SITE_DATA_PATH.'/uploads');

if(!defined('SITE_URL'))        define('SITE_URL', 'http://new.smarterapps.cn');
//应用路径解析
$app_name = 'cron';

if(!defined('APP_NAME'))			define('APP_NAME' , $app_name);
if(!defined('APP_PATH'))			define('APP_PATH' , APPS_PATH.'/'.APP_NAME);

//重新设定编译路径
if(!defined('THINK_PATH'))			define('THINK_PATH' , CORE_PATH.'/ThinkPHP');
if(!defined('RUNTIME_PATH'))		define('RUNTIME_PATH' , SITE_PATH.'/_runtime/~'.APP_NAME);
if(!defined('RUNTIME_ALLINONE'))	define('RUNTIME_ALLINONE', true);


if (!file_exists(APP_PATH .'/'.$module.'.class.php')) {
	echo 'file '.APP_PATH.'/'.$module.'.class.php is not exists!!';
	return false;
}
require APP_PATH .'/'.$module.'.class.php';


// 加载编译函数文件
require CORE_PATH."/sociax/runtime.php";
// 生成核心编译~runtime缓存
build_runtime();

if(!is_dir(APP_PATH)) mkdir(APP_PATH,0777,true);

if(is_writeable(APP_PATH)) {
    $dirs  = array(
        RUNTIME_PATH,
    );
    mkdirs($dirs);
}



// 预编译项目
App::cronBuild();

// 初始化运行时缓存
object_cache_init();

if (C('APP_AUTOLOAD_REG') && function_exists('spl_autoload_register'))
    spl_autoload_register(array('Think', 'autoload'));

//执行当前操作
if (empty($action)) {
	$action = 'run';
}

header("Content-type:text/html;charset=utf-8");
$obj = new $module;
$obj->$action();
// call_user_func(array($module,$action));