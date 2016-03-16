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
        <dl class="lineD">
          <dt>测试业务分类：</dt>
          <dd>
          <input type="radio" name="businessID" value="" <?php if($businessID == "") echo 'checked'; ?>>全部
          <?php if(is_array($appTestConfig)): ?><?php $i = 0;?><?php $__LIST__ = $appTestConfig?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><input type="radio" name="businessID" value="<?php echo ($vo["businessID"]); ?>" <?php if($businessID == $vo['businessID']) echo 'checked'; ?>><?php echo ($vo["title"]); ?>&nbsp;<?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
          <!-- 
          <select name="businessID">
              <option value="" <?php if($businessID == "") echo 'selected'; ?>>请选择</option>
              <?php if(is_array($appTestConfig)): ?><?php $i = 0;?><?php $__LIST__ = $appTestConfig?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><option value="<?php echo ($vo["id"]); ?>" <?php if($type == $vo['id']) echo 'selected'; ?>><?php echo ($vo["title"]); ?></option><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
          </select>
          -->
          </dd>
        </dl>
        <div class="page_btm">
          <input type="submit" class="btn_b" value="确定" />
        </div>
      </form>
    </div>
  </div>

  <div class="page_tit">测试业务</div>
  <div class="Toolbar_inbox">
    <div class="page right"><?php echo ($html); ?></div>
    <a href="javascript:void(0);" class="btn_a" onclick="searchDenounce();">
      <span class="search_action">&nbsp;&nbsp;搜索&nbsp;&nbsp;</span>
    </a>
  </div>

  <div class="list">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <th style="width:30px;">
          <input type="checkbox" id="checkbox_handle" onclick="checkAll(this)" value="0">
          <label for="checkbox"></label>
        </th>
        <th class="line_l">ID</th>
        <th class="line_l">名称</th>
        <th class="line_l">测试业务</th>
        <th class="line_l">操作</th>
      </tr>
      <?php if(is_array($data)): ?><?php $i = 0;?><?php $__LIST__ = $data?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><tr overstyle='on' id="package_<?php echo ($vo['id']); ?>">
          <td><input type="checkbox" name="checkbox" id="checkbox2" onclick="checkon(this)" value="<?php echo ($vo["id"]); ?>"></td>
          <td><?php echo ($vo["id"]); ?></td>
          <td><?php echo ($vo["title"]); ?></td>
          <td><?php echo ($vo["business_title"]); ?></td>
        <td>
          <a href="<?php echo U('admin/Content/editAppPackage', array('cid'=>$vo['id']));?>">编辑</a>&nbsp;|&nbsp;
         <?php if(($vo['package_type'])  ==  "6"): ?><a href="<?php echo U('admin/Content/appTask_net');?>">
         <?php else: ?>
         <a href="<?php echo U('admin/Content/appTask', array('package_type'=>$vo['package_type']));?>"><?php endif; ?>
              查看队列</a>
        </td>
        </tr><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
    </table>

  </div>
  <div class="Toolbar_inbox">
    <div class="page right"><?php echo ($html); ?></div>
    <a href="javascript:void(0);" class="btn_a" onclick="searchDenounce();">
      <span class="search_action">&nbsp;&nbsp;搜索&nbsp;&nbsp;</span>
    </a>
  </div>
</div>

<script src="../Public/admin_common.js?t=20150701"></script>
</body>
</html>