<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo ($ts['site']['site_name']); ?>管理后台</title>
<link href="__PUBLIC__/admin/style.css" rel="stylesheet" type="text/css">
<link href="__PUBLIC__/js/tbox/box.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
	var _UID_ = "<?php echo ($uid); ?>";
	var _PUBLIC_ = "__PUBLIC__";	
</script>
<script type="text/javascript" src="__PUBLIC__/js/jquery.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/common.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/tbox/box.js"></script>
</head>
<body>
<div class="so_main">

    <div id="search_div" class="search_div" <?php if(($isSearch)  !=  "1"): ?>style="display:none;"<?php endif; ?>>
        <div class="page_tit">搜索 [ <a href="javascript:void(0);" onclick="searchDenounce();">隐藏</a> ]</div>
        <div class="form2">
        <form method="post" action="">
        <input type="hidden" name="isSearch" value="1"/>
<!--        <dl class="lineD">
          <dt>ID：</dt>
          <dd>
            <input name="id" type="text" value="<?php echo ($id); ?>">
            <p>多个时使用英文的“,”分割</p>
          </dd>
        </dl>-->

        <?php if(is_array($appSystemConfig)): ?><?php $i = 0;?><?php $__LIST__ = $appSystemConfig?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo1): ?><?php ++$i;?><?php $mod = ($i % 2 )?><dl class="lineD">
            <dt><?php echo ($vo1["name"]); ?>：</dt>
            <dd>
                <select name="<?php echo ($vo1["adapter_name"]); ?>">
                    <option value="" <?php if($$vo1['adapter_name'] == "") echo 'selected'; ?>>请选择</option>
                    <?php if(is_array($vo1["data"])): ?><?php $i = 0;?><?php $__LIST__ = $vo1["data"]?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><option value="<?php echo ($vo["content"]); ?>" <?php if($$vo1['adapter_name'] == $vo['content']) echo 'selected'; ?>><?php echo ($vo["content"]); ?></option><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
                </select>
            </dd>
        </dl><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
        
        <dl class="lineD">
            <dt>测试包类型：</dt>
            <dd>
                <select name="package_type">
                    <option value="" <?php if($package_type == "") echo 'selected'; ?>>请选择</option>
                    <?php if(is_array($appPackage)): ?><?php $i = 0;?><?php $__LIST__ = $appPackage?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><option value="<?php echo ($vo["id"]); ?>" <?php if($package_type == $vo['id']) echo 'selected'; ?>><?php echo ($vo["title"]); ?></option><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
                </select>
            </dd>
        </dl>

        <dl class="lineD">
            <dt>在线状态：</dt>
            <dd>
                <input type="radio" name="devState" value="" <?php if ("" === $devState) echo 'checked'; ?>>全部
                <input type="radio" name="devState" value="1" <?php if (1 == $devState) echo 'checked'; ?>>在线
                <input type="radio" name="devState" value="0" <?php if ("0" === $devState) echo 'checked'; ?>>离线
            </dd>
        </dl>

        <dl class="lineD">
            <dt>启用状态：</dt>
            <dd>
                <input type="radio" name="isEnabled" value="" <?php if ("" === $isEnabled) echo 'checked'; ?>>全部
                <input type="radio" name="isEnabled" value="1" <?php if (1 == $isEnabled) echo 'checked'; ?>>启用
                <input type="radio" name="isEnabled" value="0" <?php if ("0" === $isEnabled) echo 'checked'; ?>>禁用
            </dd>
        </dl>
        
        <div class="page_btm">
            <input type="submit" class="btn_b" value="确定" />
        </div>
        </form>
        </div>
    </div>
    
    <div class="page_tit">手机终端 </div>
    <div class="Toolbar_inbox">
        <div class="page right"><?php echo ($html); ?></div>
         <a href="javascript:void(0);" class="btn_a" onclick="searchDenounce();">
            <span class="search_action">&nbsp;&nbsp;搜索&nbsp;&nbsp;</span>
        </a>
        <a href="<?php echo U('admin/Content/editAppPhone');?>" class="btn_a">
            <span class="search_action">&nbsp;&nbsp;添加&nbsp;&nbsp;</span>
        </a>
        <a href="javascript:void(0);" class="btn_a" onclick="deleteRecord();"><span>&nbsp;&nbsp;删除&nbsp;&nbsp;</span></a>
    </div>
    <div class="list">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <th style="width:30px;">
            <input type="checkbox" id="checkbox_handle" onclick="checkAll(this)" value="0">
            <label for="checkbox"></label>
        </th>
        <th style="width:70px;">ID</th>
        <th class="line_l">手机名称</th>
        <?php if(is_array($appSystemConfig)): ?><?php $i = 0;?><?php $__LIST__ = $appSystemConfig?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo1): ?><?php ++$i;?><?php $mod = ($i % 2 )?><th class="line_l"><?php echo ($vo1["name"]); ?></th><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
        <th class="line_l">图片</th>
        <th class="line_l">上市时间</th>
        <th class="line_l">状态</th>
        <th class="line_l">排序</th>
        <th class="line_l">操作</th>
    </tr>
    <?php if(is_array($data)): ?><?php $i = 0;?><?php $__LIST__ = $data?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><tr overstyle='on' id="<?php echo ($vo["id"]); ?>">
            <td><input type="checkbox" name="checkbox" id="checkbox2" onclick="checkon(this)" value="<?php echo ($vo["id"]); ?>"></td>
            <td><?php echo ($vo["id"]); ?></td>
            <td><?php echo ($vo["name"]); ?></td>
            <?php if(is_array($appSystemConfig)): ?><?php $i = 0;?><?php $__LIST__ = $appSystemConfig?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo1): ?><?php ++$i;?><?php $mod = ($i % 2 )?><td class="line_l"><?php echo ($vo[$vo1['adapter_name']]); ?></td><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
            <td>
                <a href="<?php echo ($vo["pic_url"]); ?>" target="_blank">
                    <img src="<?php echo ($vo["pic_url"]); ?>" width="50px">
                </a>
            </td>
            <td><?php echo ($vo["release_time"]); ?></td>
            <td><?php echo (getPhoneState($vo["devState"])); ?> - <?php echo (getPhoneEnable($vo["isEnabled"])); ?></td>
            <td>
                <a href="javascript:void(0)" class="ico_top" onclick="move('<?php echo ($vo['id']); ?>','up');">
                    <img src="__PUBLIC__/admin/images/zw_img.gif">
                </a>&nbsp;&nbsp;
                <a href="javascript:void(0)" class="ico_btm" onclick="move('<?php echo ($vo['id']); ?>','down');">
                    <img src="__PUBLIC__/admin/images/zw_img.gif">
                </a>
            </td>
            <td>
                <a href="<?php echo U('admin/Content/editAppPhone', array('id'=>$vo['id']));?>" class="btn_a">
                    修改
                </a>
                <?php if(($vo["package_type"])  ==  "8"): ?><?php if(($vo["ishot"])  ==  "1"): ?><a href="javascript:void(0);" class="btn_a J_ishot_<?php echo ($vo["id"]); ?>" onclick="setStatus(<?php echo ($vo["id"]); ?>, 'ishot','0');">移除首页</a><?php endif; ?>
                <?php if(($vo["ishot"])  !=  "1"): ?><a href="javascript:void(0);" class="btn_a J_ishot_<?php echo ($vo["id"]); ?>" onclick="setStatus(<?php echo ($vo["id"]); ?>, 'ishot', '1');">添至首页</a><?php endif; ?><?php endif; ?>
                <!--
                <?php if(($vo["isnew"])  ==  "1"): ?><a href="javascript:void(0);" class="btn_a J_isnew_<?php echo ($vo["id"]); ?>" onclick="setStatus(<?php echo ($vo["id"]); ?>, 'isnew', '0');">移除首页</a><?php endif; ?>
                <?php if(($vo["isnew"])  !=  "1"): ?><a href="javascript:void(0);" class="btn_a J_isnew_<?php echo ($vo["id"]); ?>" onclick="setStatus(<?php echo ($vo["id"]); ?>, 'isnew', '1');">添至首页</a><?php endif; ?>
                -->

                <a href="javascript:void(0);" class="btn_a" onclick="deleteRecord(<?php echo ($vo["id"]); ?>);">删除</a>
            </td>
        </tr><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
    </table>
    </div>
    <div class="Toolbar_inbox">
        <div class="page right"><?php echo ($html); ?></div>
        <a href="javascript:void(0);" class="btn_a" onclick="searchDenounce();">
            <span class="search_action">&nbsp;&nbsp;搜索&nbsp;&nbsp;</span>
        </a>
        <a href="<?php echo U('admin/Content/editAppPhone');?>" class="btn_a">
            <span class="search_action">&nbsp;&nbsp;添加&nbsp;&nbsp;</span>
        </a>
        <a href="javascript:void(0);" class="btn_a" onclick="deleteRecord();"><span>&nbsp;&nbsp;删除&nbsp;&nbsp;</span></a>
    </div>
</div>

<script>
    var _setStatusUrl_   = "<?php echo U('admin/Content/setStatus');?>";
    var _setStatusTable_ = 'app_client';
    var _moveUrl_        = "<?php echo U('admin/Content/doChangeOrder');?>";
    var _moveTable_      = 'app_client';

    var _delUrl_    = "<?php echo U('admin/Content/doDelData');?>";
    var _delTable_  = 'app_client';
    var _logTitle_  = "手机终端";
</script>
<script src="../Public/admin_common.js?t=20150701"></script>
</body>
</html>