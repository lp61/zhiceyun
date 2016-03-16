<?php
class AppEmailSendModel extends Model {
    protected $tableName = 'app_email_send';
    
    /*
     * $sendto_email 收件人邮箱
     * $subject 主题
     * $body    正文
     * $type    邮件类别 1-激活 2-重置密码 3-好友邀请 4- 5- 
     * $send_status 状态 
     * $send_type   发送方式 1-实时 2-队列
     */
    public function addSendRecord($sendto_email, $subject, $body, $type=0, $send_status=0, $send_type=0) {
        if (empty($sendto_email) || empty($subject))
            return false;

        $saveData['email']       = $sendto_email;
        $saveData['subject']     = $subject;
        $saveData['content']     = $body;
        $saveData['type']        = $type;
        $saveData['ctime']       = $saveData['mtime'] = time();
        $saveData['send_status'] = $send_status;
        $saveData['send_type']   = $send_type;
        return $this->add($saveData);
    }
    
    public function setSendStatus($id, $send_status=1){
        if (empty($id))
            return false;
        $saveData['id']          = $id;
        $saveData['mtime']       = time();
        $saveData['send_status'] = $send_status;
        return $this->save($saveData);
    }

    public function getInfoById($id){
        if (empty($id))
            return false;
        $map['id'] = $id;
        return $this->where($map)->find();
    }
}