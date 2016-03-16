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

  <div >
    <div class="page_tit">回复用户 [ <a href="<?php echo U('admin/Content/feedback');?>">返回</a> ]</div>
    <div class="form2" id="search_div" >
      <dl class="lineD">
      <dt>反馈用户：</dt>
        <dd>
          <?php echo (getUserName($feedback["uid"])); ?>
        </dd>
      </dl>
      <dl class="lineD">
      <dt>问题类型：</dt>
        <dd>
          <?php echo ($appTestConfig[$feedback['type']]['name']); ?>
        </dd>
      </dl>
      <dl class="lineD">
      <dt>标题：</dt>
        <dd>
          <?php echo ($feedback["title"]); ?>
        </dd>
      </dl>
      <dl class="lineD">
      <dt>描述：</dt>
        <dd>
          <?php echo ($feedback["content"]); ?>
        </dd>
      </dl> 
      <dl class="lineD">
      <dt>时间：</dt>
        <dd>
          <?php echo (date("Y-m-d H:i",$feedback["ctime"])); ?>
        </dd>
      </dl>
      <?php if (!$_GET['no_reply']) { ?>
      <form method="post" action="">
        <input type="hidden" name="fid" value="<?php echo ($feedback["id"]); ?>"/>
        <input type="hidden" name="fuid" value="<?php echo ($feedback["uid"]); ?>"/>
        <input type="hidden" name="ftitle" value="<?php echo ($feedback["title"]); ?>"/>
        
        <dl class="lineD">
          <dt>回复方式：</dt>
          <dd>
              <input type="radio" name="notice_type" value="" <?php if($notice_type == "") echo 'checked'; ?>>默认
              <input type="radio" name="notice_type" value="1" <?php if($notice_type ==1) echo 'checked'; ?>>系统通知
              <input type="radio" name="notice_type" value="2" <?php if($notice_type == 2) echo 'checked'; ?>>
              邮件回复
          </dd>
        </dl>

        <dl class="lineD">
          <dt>回复用户：</dt>
          <dd>
            <textarea name="content" cols="45" rows="5" ></textarea>
          </dd>
        </dl>
        
        <div class="page_btm">
          <input type="submit" class="btn_b" value="确定" />
        </div>
        <?php } ?>
      </form>
  </div>
  </div>
  
  <div class="page_tit">回复列表 </div>
  <div class="Toolbar_inbox">
    <div class="page right"><?php echo ($html); ?></div>
    <a href="<?php echo U('admin/Content/feedback');?>" class="btn_a"><span>返回</span></a>
    <a href="javascript:void(0);" class="btn_a" onclick="deleteRecord();"><span>删除记录</span></a>
  </div>
  <div class="list">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <th style="width:30px;">
        <input type="checkbox" id="checkbox_handle" onclick="checkAll(this)" value="0">
        <label for="checkbox"></label>
    </th>
    <th class="line_l">ID</th>
    <th class="line_l">反馈问题id</th>
    <th class="line_l">反馈人邮箱</th>
    <th class="line_l">反馈人邮箱</th>
    <th class="line_l">回复内容</th>
    <th class="line_l">回复方式</th>
    <th class="line_l">提交时间</th>
    <th class="line_l">操作</th>
  </tr>
  <?php if(is_array($data)): ?><?php $i = 0;?><?php $__LIST__ = $data?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><tr overstyle='on' id="feedback_<?php echo ($vo["id"]); ?>">
        <td><input type="checkbox" name="checkbox" id="checkbox2" onclick="checkon(this)" value="<?php echo ($vo["id"]); ?>"></td>
        <td><?php echo ($vo["id"]); ?></td>
        <td><?php echo ($vo["fid"]); ?></td>
        <td><?php echo (getUserName($vo["fuid"])); ?></td>
        <td><?php echo (getUserName($vo["uid"])); ?></td>
        <td><?php echo ($vo["content"]); ?></td>
        <td>
          <?php if($vo['notice_type']==1){ ?>系统通知<?php } ?>
          <?php if($vo['notice_type']==2){ ?>邮件回复<?php } ?>
          <?php if($vo['notice_type']==0){ ?>默认<?php } ?>
        </td>
        <td><?php echo (date("Y-m-d H:i",$vo["ctime"])); ?></td>
        <td>
            <a href="javascript:void(0);" class="btn_a" onclick="deleteRecord(<?php echo ($vo["id"]); ?>);">删除</a>
        </td>
      </tr><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
  </table>
  </div>
  <div class="Toolbar_inbox">
    <div class="page right"><?php echo ($html); ?></div>
    <a href="<?php echo U('admin/Content/feedback');?>" class="btn_a"><span>返回</span></a>
    <a href="javascript:void(0);" class="btn_a" onclick="deleteRecord();"><span>删除记录</span></a>
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
    		ui.error('请先选择一个记录');
    		return ;
    	}
    	if(confirm('您将删除'+length+'条记录，确定继续？')) {
    		$.post("<?php echo U('admin/Content/doDeleteReplyFeedback');?>",{ids:ids},function(res){
    			if(res=='1') {
    				ui.success('删除成功');
    				if( length == 1){
    					$('#feedback_'+ids).remove();
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
            $('#feedback_'+ids[i]).remove();
        }
    }
    var isSearchHidden = 0;
    function searchDenounce() {
        if(isSearchHidden == 1) {
            $("#search_div").slideDown("fast");
            isSearchHidden = 0;
        }else {
            $("#search_div").slideUp("fast");
            isSearchHidden = 1;
        }
    }
</script>

</body>
</html>