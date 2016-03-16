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
          <dt>ID：</dt>
          <dd>
            <input name="id" type="text" value="<?php echo ($id); ?>">
            <p>多个时使用英文的“,”分割</p>
          </dd>
        </dl>

        <dl class="lineD">
            <dt>分类：</dt>
            <dd>
                <input type="radio" name="type" value="" <?php if($type == "") echo 'checked'; ?>>全部
                <?php if(is_array($appArticleConfig)): ?><?php $i = 0;?><?php $__LIST__ = $appArticleConfig?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><input type="radio" name="type" value="<?php echo ($vo["id"]); ?>" <?php if($type == $vo['id']) echo 'checked'; ?>><?php echo ($vo["name"]); ?><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
                <!-- <select name="type">
                    <option value="" <?php if($type == "") echo 'selected'; ?>>请选择</option>
                    <?php if(is_array($appArticleConfig)): ?><?php $i = 0;?><?php $__LIST__ = $appArticleConfig?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><option value="<?php echo ($vo["id"]); ?>" <?php if($type == $vo['id']) echo 'selected'; ?>><?php echo ($vo["name"]); ?></option><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
                </select> -->
            </dd>
        </dl>
        <dl class="lineD">
            <dt>文章作者：</dt>
            <dd>
                <input name="author" type="text" value="<?php echo ($author); ?>">
            </dd>
        </dl>
        <dl class="lineD">
          <dt>激活状态：</dt>
          <dd>
                <input type="radio" name="is_active" value="" <?php if($is_active === "") echo 'checked'; ?>> 全部
                <input type="radio" name="is_active" value="1" <?php if($is_active ==1) echo 'checked'; ?>> 激活
                <input type="radio" name="is_active" value="0" <?php if($is_active === "0") echo 'checked'; ?>> 未激活
          </dd>
        </dl>
        
        <div class="page_btm">
            <input type="submit" class="btn_b" value="确定" />
        </div>
        </form>
        </div>
    </div>

    <div class="page_tit">行业报告 </div>
    <div class="Toolbar_inbox">
        <div class="page right"><?php echo ($html); ?></div>
         <a href="javascript:void(0);" class="btn_a" onclick="searchDenounce();">
            <span class="search_action">&nbsp;&nbsp;搜索&nbsp;&nbsp;</span>
        </a>
        <a href="<?php echo U('admin/Content/editAppArticle');?>" class="btn_a">
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
        <th style="width:12%;">封面图片</th>
        <th style="width:20%;">标题</th>
        <th style="width:5%;">分类</th>
        <th style="width:5%;">关键字</th>
        <th style="width:20%;">文章内容</th>
        <th style="width:8%;">附件</th>
        <th style="width:3%;">状态</th>
        <th style="width:10%;">发布时间</th>
        <th style="width:10%;">操作</th>
    </tr>
    <?php if(is_array($data)): ?><?php $i = 0;?><?php $__LIST__ = $data?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><tr overstyle='on' id="<?php echo ($vo["id"]); ?>">
            <td><input type="checkbox" name="checkbox" id="checkbox2" onclick="checkon(this)" value="<?php echo ($vo["id"]); ?>"></td>
            <td><?php echo ($vo["id"]); ?></td>
            <td><?php if(($vo["pic_url"])  !=  ""): ?><a href="<?php echo ($vo["pic_url"]); ?>" target="_blank">
                    <img src="<?php echo ($vo["pic_url"]); ?>" width="120px">
                </a><?php endif; ?>
            </td>
            <td><?php echo msubstr($vo['title'],0, 50);?></td>
            <td><?php echo ($appArticleConfig[$vo['type']]['name']); ?></td>
            <td><?php echo msubstr($vo['keyword'],0, 50);?></td>
            <td><?php echo msubstr(strip_tags($vo['content']),0, 50);?></td>
            <td>
                <?php if ($vo['file_url']) { ?>
                <a href="<?php echo ($vo['file_url']); ?>">
                    <img src="__THEME__/images/zip_file.png" width="50px" height="50px" />
                </a><br />
                <?php }else{
                echo '暂无';
                } ?>
            </td>
            <!--
            <td>
                <notempty name="vo.link">
                    <a href="<?php echo ($vo["link"]); ?>" class="btn_a" target="_blank">
                        <?php echo msubstr($vo['link'],0, 50);?>
                    </a>
                </notempty>
            </td>
            -->
            <td>
                <?php if(($vo["is_active"])  ==  "1"): ?>激活<?php endif; ?>
                <?php if(($vo["is_active"])  !=  "1"): ?><span style="color:red">冻结</span><?php endif; ?>
            </td>
            <td><?php echo (date("Y-m-d H:i",$vo["ctime"])); ?></td>
            <td>
                <a href="<?php echo U('admin/Content/editAppArticle', array('id'=>$vo['id']));?>" class="btn_a">修改</a>
                <?php if(($vo["ishot"])  ==  "1"): ?><a href="javascript:void(0);" class="btn_a J_ishot_<?php echo ($vo["id"]); ?>" onclick="setStatus(<?php echo ($vo["id"]); ?>, 'ishot','0');">设为普通</a><?php endif; ?>
                <?php if(($vo["ishot"])  !=  "1"): ?><a href="javascript:void(0);" class="btn_a J_ishot_<?php echo ($vo["id"]); ?>" onclick="setStatus(<?php echo ($vo["id"]); ?>, 'ishot', '1');" title="热门将在测试圈优先显示，必须有对应尺寸的封面">设为热门</a><?php endif; ?>
                <br />
                <?php if(($vo["is_active"])  ==  "1"): ?><a href="javascript:void(0);" class="btn_a J_is_active_<?php echo ($vo["id"]); ?>" onclick="setStatus(<?php echo ($vo["id"]); ?>, 'is_active','0');">设为冻结</a><?php endif; ?>
                <?php if(($vo["is_active"])  !=  "1"): ?><a href="javascript:void(0);" class="btn_a J_is_active_<?php echo ($vo["id"]); ?>" onclick="setStatus(<?php echo ($vo["id"]); ?>, 'is_active', '1');">设为激活</a><?php endif; ?>
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
        <a href="<?php echo U('admin/Content/editAppArticle');?>" class="btn_a">
            <span class="search_action">&nbsp;&nbsp;添加&nbsp;&nbsp;</span>
        </a>
        <a href="javascript:void(0);" class="btn_a" onclick="deleteRecord();">
            <span>&nbsp;&nbsp;删除&nbsp;&nbsp;</span>
        </a>
    </div>
</div>

<script>
    var _setStatusUrl_   = "<?php echo U('admin/Content/setStatus');?>";
    var _setStatusTable_ = 'app_article';
    var _moveUrl_        = "<?php echo U('admin/Content/doChangeOrder');?>";
    var _moveTable_      = 'app_article';

    var _delUrl_    = "<?php echo U('admin/Content/doDelData');?>";
    var _delTable_  = 'app_article';
    var _logTitle_  = "行业报告";
</script>
<script src="../Public/admin_common.js?t=20150701"></script>
</body>
</html>