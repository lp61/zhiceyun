<?php
/*
 * 总体配置文件，包括API Key, Secret Key，以及所有允许调用的API列表
 * This file for configure all necessary things for invoke, including API Key, Secret Key, and all APIs list
 *
 * @Modified by mike on 17:54 2011/12/21.
 * @Modified by Edison tsai on 16:34 2011/01/13 for remove call_id & session_key in all parameters.
 * @Created: 17:21:04 2010/11/23
 * @Author:	Edison tsai<dnsing@gmail.com>
 * @Blog:	http://www.timescode.com
 * @Link:	http://www.dianboom.com
 */
//获取人人站配置信息
$renren_config	= new stdClass;
$renren_config->APIURL		= 'http://api.renren.com/restserver.do'; //RenRen网的API调用地址，不需要修改
// $renren_config->APPID		= '214892';	//你的API Key，请自行申请
$renren_config->APPID		= '215988';	//你的API Key，请自行申请
// $renren_config->APIKey		= '0a5b5599d90f45f19c165fe4eaa9c616';	//你的API Key，请自行申请
$renren_config->APIKey		= '0cf9cb2d0fae4f51938b10f54d8df676';	//你的API Key，请自行申请
// $renren_config->SecretKey	= 'f4b2b211bc0b4a9cb3158352ef96411b';	//你的API 密钥
$renren_config->SecretKey	= 'a7be3f7369d3416ca812fc86f851db0a';	//你的API 密钥
$renren_config->APIVersion	= '1.0';	//当前API的版本号，不需要修改
$renren_config->decodeFormat	= 'json';	//默认的返回格式，根据实际情况修改，支持：json,xml
$renren_config->scope='publish_feed,photo_upload';
?>