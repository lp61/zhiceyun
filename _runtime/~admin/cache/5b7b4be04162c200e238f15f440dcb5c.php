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
  <div class="page_tit">消息群发</div>
  
  <form method="post" action="<?php echo U('admin/User/doSendMessage');?>">
  <div class="form2">
    <dl class="lineD">
      <dt>收件人：</dt>
      <dd>
        <select name="user_group_id">
            <option value="0">全部用户</option>
            <?php if(is_array($user_group_list)): ?><?php $i = 0;?><?php $__LIST__ = $user_group_list?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><option value="<?php echo ($vo["user_group_id"]); ?>"><?php echo ($vo["title"]); ?></option><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
        </select>
      </dd>
    </dl>
    <dl class="lineD">
      <dt>发送方式：</dt>
      <dd>
        <label><input name="type" type="radio" value="0" checked>系统通知</label><br/>
        <label><input name="type" type="radio" value="1">Email</label>
      </dd>
    </dl>
    <dl class="lineD">
      <dt>标题：</dt>
      <dd>
        <input name="title"type="text" value="" id='title'>
      </dd>
    </dl>
    <dl class="">
      <dt>正文：</dt>
      <dd>
      <textarea name="content" id="content" cols="50" rows="10"></textarea>
      </dd>
    </dl>
    <div class="page_btm">
      <input type="submit" class="btn_b" value="确定" />
    </div>
  </div>
  </form>
</div>

<script>
  function checkForm() {
    alert(123);
    var valueTitle = $('#title').val();
    var title = valueTitle.replace(/(^\s*)|(\s*$)/g, "");
    var valueContent = $('#content').val();
    var content = valueContent.replace(/(^\s*)|(\s*$)/g, "");
    if(title=='') {
          ui.error('请输入标题');
          return false;
      }
    if(content==''){
          ui.error('请输入正文');
          return false;
    }

</script>
</body>
</html>