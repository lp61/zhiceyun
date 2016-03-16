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
                    <dt>分类：</dt>
                    <dd>
                    <input type="radio" name="type" value="" <?php if($type == "") echo 'checked'; ?>>全部
                    <?php if(is_array($faqTypeConfig)): ?><?php $i = 0;?><?php $__LIST__ = $faqTypeConfig?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><input type="radio" name="type" value="<?php echo ($vo["id"]); ?>" <?php if($type == $vo['id']) echo 'checked'; ?>><?php echo ($vo["title"]); ?><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
                    </dd>
                </dl>
                <div class="page_btm">
                    <input type="submit" class="btn_b" value="确定" />
                </div>
            </form>
        </div>
    </div>

    <div class="page_tit">FAQ </div>
    <div class="Toolbar_inbox">
        <div class="page right"><?php echo ($html); ?></div>
        <a href="javascript:void(0);" class="btn_a" onclick="searchDenounce();">
            <span class="search_action">&nbsp;&nbsp;搜索&nbsp;&nbsp;</span>
        </a>
        <a href="<?php echo U('admin/Content/editFaq');?>" class="btn_a">
            <span class="search_action">&nbsp;&nbsp;添加&nbsp;&nbsp;</span>
        </a>
        <a href="javascript:void(0);" class="btn_a" onclick="deleteRecord();">
            <span>&nbsp;&nbsp;删除&nbsp;&nbsp;</span>
        </a>
    </div>
    <div class="list">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <th style="width:3%;">
                    <input type="checkbox" id="checkbox_handle" onclick="checkAll(this)" value="0">
                    <label for="checkbox"></label>
                </th>
                <th style="width:4%;">ID</th>
                <th style="width:10%;">分类</th>
                <th style="width:20%;">标题</th>
                <th style="width:45%;">内容</th>
                <th style="width:10%;">发布时间</th>
				<th style="width:5%;">排序</th>
                <th style="width:6%;">操作</th>
            </tr>
            <?php if(is_array($data)): ?><?php $i = 0;?><?php $__LIST__ = $data?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><tr overstyle='on' id="<?php echo ($vo["id"]); ?>">
                    <td><input type="checkbox" name="checkbox" id="checkbox2" onclick="checkon(this)" value="<?php echo ($vo["id"]); ?>"></td>
                    <td><?php echo ($vo["id"]); ?></td>
                    <td><?php echo ($faqTypeConfig[$vo['type']]['title']); ?></td>
                    <td><?php echo msubstr($vo['title'],0, 50);?></td>
                    <td><?php echo msubstr(strip_tags($vo['content']),0, 80);?></td>
                    <td><?php echo (date("Y-m-d H:i",$vo["ctime"])); ?></td>
                    <td>
                        <a href="javascript:void(0)" class="ico_top" onclick="move('<?php echo ($vo['id']); ?>','up');">
                            <img src="__PUBLIC__/admin/images/zw_img.gif">
                        </a>&nbsp;&nbsp;
                        <a href="javascript:void(0)" class="ico_btm" onclick="move('<?php echo ($vo['id']); ?>','down');">
                            <img src="__PUBLIC__/admin/images/zw_img.gif">
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo U('admin/Content/editFaq', array('id'=>$vo['id']));?>" class="btn_a">修改</a>
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
        <a href="<?php echo U('admin/Content/editFaq');?>" class="btn_a">
            <span class="search_action">&nbsp;&nbsp;添加&nbsp;&nbsp;</span>
        </a>
        <a href="javascript:void(0);" class="btn_a" onclick="deleteRecord();">
            <span>&nbsp;&nbsp;删除&nbsp;&nbsp;</span>
        </a>
    </div>
</div>

<script>
    var _delUrl_ = "<?php echo U('admin/Content/doDelData');?>";
    var _delTable_ = 'faq';

    var _moveUrl_ = "<?php echo U('admin/Content/doChangeOrder');?>";
    var _moveTable_ = 'faq';
    var _logTitle_ = "FAQ";
</script>
<script src="../Public/admin_common.js?t=20150701"></script>
</body>
</html>