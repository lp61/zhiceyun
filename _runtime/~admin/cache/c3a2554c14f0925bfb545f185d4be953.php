<?php if (!defined('THINK_PATH')) exit();?><div class="selection">

<div class="selection_left">
<ul id="selected_user_groups">
</ul>
</div>

<div class="selection_right">
<input type="hidden" name="user_group_id" id="user_group_id" value="" />

<ul class="sort">
<?php if(is_array($user_groups)): ?><?php $i = 0;?><?php $__LIST__ = $user_groups?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li id="user_group_<?php echo ($vo['user_group_id']); ?>">
		<div class="c1">
			<div>
				<label><input type="checkbox" id="selectUserGroup_<?php echo ($vo['user_group_id']); ?>" onclick="selectUserGroup(<?php echo ($vo['user_group_id']); ?>);">
				<span class="user_group_name" id="user_group_name_<?php echo ($vo['user_group_id']); ?>"><?php echo ($vo['title']); ?></span></label>
			</div>
		</div>
	</li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
</ul>

<div style="clear:both"></div>
</div>
<div style="clear:both"></div>
</div>

<script type="text/javascript">
/*
 *  方法:Array.remove(dx) 通过遍历,重构数组
 *  功能:删除数组元素.
 *  参数:dx删除元素的下标.
 */
Array.prototype.remove=function(dx)
{
    if(isNaN(dx)||dx>this.length){return false;}
    for(var i=0,n=0;i<this.length;i++)
    {
        if(this[i]!=this[dx])
        {
            this[n++]=this[i]
        }
    }
    this.length-=1
}

//已选择的部门
var w_selected_user_group 	  = new Array();

//将已选择的用户组选中
<?php if(!empty($selected)) { ?>
	$(document).ready(function(){
		
		<?php if(is_array($selected)): ?><?php $i = 0;?><?php $__LIST__ = $selected?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?>//用户组不存在时不操作
			if( $('#selectUserGroup_<?php echo ($vo); ?>').attr('id') ) {
				selectUserGroup( parseInt("<?php echo ($vo); ?>") );
				$('#selectUserGroup_<?php echo ($vo); ?>').attr('checked', true);
			}<?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
	});
<?php } ?>

//选择部门
function selectUserGroup(w_user_group_id) {
	w_user_group_index   	  = $.inArray(w_user_group_id, w_selected_user_group);
	w_user_group_html	 	  = '';

	if(w_user_group_index == '-1') {
		//选中一个用户组
		w_selected_user_group.push(w_user_group_id);
		
		//用户组名称
		w_user_group_name     = $('#user_group_name_'+w_user_group_id).html();
		
		//填充
		w_user_group_html    += '<li id="selected_user_group_'+w_user_group_id+'">' + w_user_group_name + '&nbsp;&nbsp;&nbsp;';
		w_user_group_html    += '<span><a href="javascript:void(0);" onclick="deleteUserGroup('+w_user_group_id+');">x</a></span>';
		w_user_group_html    += '</li>';
		$('#selected_user_groups').append(w_user_group_html);
	}else {
		//删除一个用户组
		w_selected_user_group.remove(w_user_group_index);
		$('#selected_user_group_'+w_user_group_id).remove();
	}
	$('#user_group_id').val(w_selected_user_group.toString());
}

//删除已选择的部门
function deleteUserGroup(w_user_group_id) {
	w_user_group_index = $.inArray(w_user_group_id, w_selected_user_group);
	if(w_user_group_index == '-1') return false;
	
	w_selected_user_group.remove(w_user_group_index);
	$('#selected_user_group_'+w_user_group_id).remove();
	$('#user_group_id').val(w_selected_user_group.toString());
	$('#selectUserGroup_'+w_user_group_id).removeAttr('checked');
}
</script>