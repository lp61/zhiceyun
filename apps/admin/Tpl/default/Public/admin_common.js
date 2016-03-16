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
        var val = $(n).val();
        if(val > 0) {
            ids.push( val );
        }
    });
    return ids;
}

function removeItem(ids) {
    ids = ids.split(',');
    for (i = 0; i < ids.length; i++) {
        $('#' + ids[i]).remove();
    }
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
        $.post(_delUrl_, {ids:ids, tableName:_delTable_, title:_logTitle_},function(res){
            if(res=='1') {
                ui.success('删除成功');
                if( length == 1){
                    $('#'+ids).remove();
                }else{
                    removeItem(ids);
                }
            }else {
                ui.error('删除失败');
            }
        });
    }
}
//搜索用户
function searchDenounce(add) {
    var add = add || '';
    if($("#search_div"+add).css("display")=="none") {
        $('.search_div').hide();
        $("#search_div"+add).slideDown("fast");
    }else {
        $("#search_div"+add).slideUp("fast");
    }
}

function move(document_id, direction) {
    var baseid  = direction == 'up' ? $('#'+document_id).prev().attr('id') : $('#'+document_id).next().attr('id');
    if(!baseid) {
        direction == 'up' ? ui.error('已经是最前面了') : ui.error('已经是最后面了');
    }else {
        $.post(_moveUrl_, {document_id:document_id, baseid:baseid, tableName:_moveTable_}, function(res){
            if(res == '1') {
                //交换位置
                direction == 'up' ? $('#'+document_id).insertBefore('#'+baseid) : $("#"+document_id).insertAfter('#'+baseid);
                ui.success('保存成功');
            }else {
                ui.error('保存失败');
            }
        });
    }
}
function setStatus(id, type, status) {
    if(!id) {
         ui.error('参数不合法');
    }else {
        $.post(_setStatusUrl_, {id:id, tableName:_setStatusTable_, 'field':type, status:status,title:_logTitle_,setTitle:$(".J_"+type+'_'+id).html()}, function(res){
            if(res == '1') {
                $(".J_"+type+'_'+id).hide();
                ui.success('保存成功');
            }else {
                ui.error('保存失败');
            }
        });
    }
}