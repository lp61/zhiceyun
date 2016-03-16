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

  <div id="search_div" <?php if(($isSearch)  !=  "1"): ?>style="display:none;"<?php endif; ?>>
    <div class="page_tit">搜索用户积分 [ <a href="javascript:void(0);" onclick="searchDenounce();">隐藏</a> ]</div>
    <div class="form2">
    <form method="post" action="">
    <input type="hidden" name="isSearch" value="1"/>
    <dl class="lineD">
      <dt>ID：</dt>
      <dd>
        <input name="id" type="text" value="<?php echo ($id); ?>">
        <p>多个时使用英文的“,”分割</p>
      </dd>
    </dl>
    
    <?php if($isSearch != 1) $uid = ''; ?>
    <dl class="lineD">
      <dt>用户ID：</dt>
      <dd>
        <input name="uid" type="text" value="<?php echo ($uid); ?>">
        <p>后台日志操作人ID,多个时使用英文的","分割</p>
      </dd>
    </dl>
    
    <dl class="lineD">
      <dt>积分类型：</dt>
      <dd>
      	<input name="credit_name" type="text" value="<?php echo ($credit_name); ?>">
      </dd>
    </dl>
    <div class="page_btm">
      <input type="submit" class="btn_b" value="确定" />
    </div>
    </form>
  </div>
  </div>
  
  <div class="page_tit">后台用户积分</div>
  <div class="Toolbar_inbox">
    <div class="page right"><?php echo ($html); ?></div>
    <a href="javascript:void(0);" class="btn_a" onclick="searchDenounce();">
        <span class="search_action"><?php if(($isSearch)  !=  "1"): ?>搜索用户积分<?php else: ?>搜索完毕<?php endif; ?></span>
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
    <th class="line_l">用户ID</th>
    <th class="line_l">用户名</th>
    <th class="line_l">积分类型</th>
    <th class="line_l">积分别名</th>
    <th class="line_l">积分</th>
    <th class="line_l">操作时间</th>
  </tr>
  <?php if(is_array($data)): ?><?php $i = 0;?><?php $__LIST__ = $data?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><tr overstyle='on' id="adminLog_<?php echo ($vo["id"]); ?>">
        <td><input type="checkbox" name="checkbox" id="checkbox2" onclick="checkon(this)" value="<?php echo ($vo["id"]); ?>"></td>
        <td><?php echo ($vo["id"]); ?></td>
        <td><?php echo ($vo["uid"]); ?></td>
        <td><?php echo (getUserName($vo["uid"])); ?></td>
        <td><?php echo ($vo["credit_name"]); ?></td>
        <td><?php echo ($vo["credit_alias"]); ?></td>
        <td><?php echo ($vo["score"]); ?></td>
        <td><?php echo (date("Y-m-d H:i",$vo["ctime"])); ?></td>
      </tr><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
  </table>
  </div>
  <div class="Toolbar_inbox">
    <div class="page right"><?php echo ($html); ?></div>
    <a href="javascript:void(0);" class="btn_a" onclick="searchDenounce();">
        <span class="search_action"><?php if(($isSearch)  !=  "1"): ?>搜索用户积分<?php else: ?>搜索完毕<?php endif; ?></span>
    </a>
  </div>
</div>

<script>
    //鼠标移动表格效果
    $(document).ready(function(){
        $("tr[overstyle='on']").hover(
          function () {
            $(this).addClass("bg_hover");
          },
          function () {
            $(this).removeClass("bg_hover");
          }
        );
    });
    
    function checkon(o){
        if( o.checked == true ){
            $(o).parents('tr').addClass('bg_on') ;
        }else{
            $(o).parents('tr').removeClass('bg_on') ;
        }
    }
    
    function checkAll(o){
        if( o.checked == true ){
            $('input[name="checkbox"]').attr('checked','true');
            $('tr[overstyle="on"]').addClass("bg_on");
        }else{
            $('input[name="checkbox"]').removeAttr('checked');
            $('tr[overstyle="on"]').removeClass("bg_on");
        }
    }

    //获取已选择用户的ID数组
    function getChecked() {
        var ids = new Array();
        $.each($('table input:checked'), function(i, n){
            ids.push( $(n).val() );
        });
        return ids;
    }
    
    function deleteRecord(ids) {
        var length = 0;
    	if(ids) {
    		length = 1;    		
    	}else {
    		ids    = getChecked();
    		length = ids.length;
            ids    = ids.toString();
    	}
    	if(ids=='') {
    		ui.error('请先选择一个后台日志');
    		return ;
    	}
    	if(confirm('您将删除'+length+'条记录，确定继续？')) {
    		$.post("<?php echo U('admin/Content/doDeleteAdminLog');?>",{ids:ids},function(res){
    			if(res=='1') {
    				ui.success('删除成功');
    				if( length == 1){
    					$('#adminLog_'+ids).remove();
    				}else{
    					removeItem(ids);
    				}
    			}else {
    				ui.error('删除失败');
    			}
    		});
    	}
    }
    
    function removeItem(ids) {
    	ids = ids.split(',');
        for(i = 0; i < ids.length; i++) {
            $('#adminLog_'+ids[i]).remove();
        }
    }
    
    //搜索用户
    var isSearchHidden = <?php if(($isSearch)  !=  "1"): ?>1<?php else: ?>0<?php endif; ?>;
    function searchDenounce() {
        if(isSearchHidden == 1) {
            $("#search_div").slideDown("fast");
            $(".search_action").html("搜索完毕");
            isSearchHidden = 0;
        }else {
            $("#search_div").slideUp("fast");
            $(".search_action").html("搜索后台日志");
            isSearchHidden = 1;
        }
    }
</script>

</body>
</html>