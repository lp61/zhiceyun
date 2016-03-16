<?php
/**
 * 消息
 */
class AppMessageModel extends Model
{
    protected $tableName = 'app_message';

    /**
     * 消息详细信息
     *
     * @param int     $uid          用户ID
     * @param int     $id           消息ID
     * @return array
     */
    public function getDetailById($id, $uid=0) {
        $uid = intval($uid);
        $id  = intval($id);

        if (!$id) {
            return false;
        }

        if ($uid) {
            $map['to_uid'] = $uid;
        }
        $map['id']     = $id;

        $res = $this->where($map)->order('ctime DESC')->find();
        return $res;
    }

    /**
     * 消息内容列表
     *
     * @param int     $list_id      消息列表ID
     * @param int     $uid          用户ID
     * @param int     $count        加载条数
     * @return array
     */
    public function getMessageByUid($uid, $count = 20) {
        $uid      = intval($uid);
        $count    = intval($count);

        if (!$uid) {
            return false;
        }

        $map['to_uid'] = $uid;

        $res = $this->where($map)->order('ctime DESC')->findPage();

        return $res;
    }

    /**
     * 用户未读私信数
     *
     * @param int $uid 用户ID
     * @return array
     */
    public function getUnreadMessageCount($uid) {
        $map['to_uid']  = intval($uid);
        $map['is_read'] = 0;

        return intval($this->where($map)->count());
    }

    /**
     * 发送消息
     *
     * @param int   $to_uid   发送私信的用户ID
     * @param array $data     私信信息,包括title私信标题、content私信正文
     * @return array          返回新添加的私信的ID
     */
    public function postMessage($to_uid, $data) {
        $to_uid = intval($to_uid);
        if (!$to_uid || empty($data)) {
            return false;
        }

        $data['to_uid'] = $to_uid;
        // 发起时间
        $data['ctime']  = $data['mtime'] = time();
        return $this->add($data);
    }

    /**
     * 设置私信为已读
     *
     * @param array|string $message_ids 多个私信ID可以组成数组，也可以用“,”分隔
     * @param int          $uid 成员的用户ID
     * @return boolean
     */
    public function setMessageIsRead($list_ids = null, $member_uid) {
        if (!$member_uid) {
            return false;
        }
        $list_ids && $member_map['list_id']    = array('IN', $list_ids);
        $member_map['member_uid'] = intval($member_uid);
        return false !== M('message_member')->where($member_map)->setField('new', 0);
    }
}
