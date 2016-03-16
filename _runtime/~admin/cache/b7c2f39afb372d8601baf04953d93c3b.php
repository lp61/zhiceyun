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
  <div class="page_tit">站点配置</div>
  
  <div class="form2">
  	<form method="post" action="<?php echo U('admin/Global/doSetSiteOpt');?>" enctype="multipart/form-data">
    <dl class="lineD">
      <dt>站点名称：</dt>
      <dd>
        <input name="site_name" type="text" value="<?php echo ($site_name); ?>" size="60">
        <span>整个网站的名称</span>
      </dd>  
    </dl>
    <dl class="lineD">
      <dt>默认标题：</dt>
      <dd>
        <input name="site_header_title" type="text" value="<?php echo ($site_header_title); ?>" size="60">
        <span>整个站点默认的标题(title)，搜索引擎优化使用</span>
      </dd>
    </dl>
    <dl class="lineD">
      <dt>默认关键字：</dt>
      <dd>
        <textarea cols="50" rows="3" name="site_header_keywords"><?php echo ($site_header_keywords); ?></textarea><br />
        <span>页面meta标签里的关键字信息(keywords)，搜索引擎优化使用</span>
      </dd>
    </dl>
    <dl class="lineD">
      <dt>默认描述信息：</dt>
      <dd>
        <textarea cols="50" rows="3" name="site_header_description"><?php echo ($site_header_description); ?></textarea><br />
        <span>页面meta标签对关键字内容的描述(description)，搜索引擎优化使用</span>
      </dd>
    </dl>
<!--
    <dl class="lineD">
      <dt>公司名/ICP/IP/域名备案：</dt>
      <dd>
        <input name="site_icp" type="text" value="<?php echo ($site_icp); ?>" size="60">
        <p>例如：xxx有限公司 京ICP备066000001号</p>
      </dd>
    </dl>
    
    <dl class="lineD">
      <dt>站点状态：</dt>
      <dd>
        <label class="check-line">
        	<input class="s-ck" name="site_closed" type="radio" value="1" <?php if(($site_closed)  ==  "1"): ?>checked<?php endif; ?>>关闭
        </label>
        <br>
        <label>
        	<input class="s-ck" name="site_closed" type="radio" value="0" <?php if(($site_closed)  ==  "0"): ?>checked<?php endif; ?>>开启
        </label>
      </dd>
    </dl>
    <dl class="lineD">
      <dt>站点关闭理由：</dt>
      <dd><span>
        <textarea cols="50" rows="3" name="site_closed_reason"><?php echo ($site_closed_reason); ?></textarea><br />网站关闭时，页面显示的内容</span>
      </dd>
    </dl>
    <dl style="*zoom:1">
      <dt>logo：</dt>
      <dd>
          <img src="<?php echo ($site_logo); ?>" /><br />
		  <input type="file" name="site_logo" />
          <p>重要: 在线上传LOGO，最好是8位透明PNG图标!</p>
      </dd>
    </dl>
-->
    <div class="page_btm">
      <input type="submit" class="btn_b" value="确定" />
    </div>
    </form>
  </div>
</div>
</body>
</html>