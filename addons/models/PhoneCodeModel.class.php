<?php
class PhoneCodeModel extends Model
{
    protected $tableName = 'phone_code';
    
    public function addRecord($phone, $code) {
        $saveData['phone'] = $phone;
        $saveData['code']  = $code;
        $saveData['valid'] = 0;
        $saveData['ctime'] = $saveData['mtime'] = time();

        return $this->add($saveData);
    }
    
    public function setRecordValid($phone){
        if (empty($phone))
            return false;
        $where['phone'] = $phone;
        $saveData['mtime'] = time();
        $saveData['valid'] = 1;
        return $this->where($where)->order('ctime desc')->limit(1)->save($saveData);
    }

    public function getStatusByPhone($phone, $type=1){
        if (empty($phone))
            return false;

        $extraTime = null;
        switch ($type) {
            case 1:
                $extraTime = 60; // 60秒后才能再次发送
                break;
            case 2:
                $extraTime = 300; // 300秒内有效
                break;
            case 3:
                break;
            
            default:
                $extraTime = 300; // 300秒内有效
                break;
        }
        if ($extraTime) {
            $nowTime      = time() - $extraTime;
            $map['ctime'] = array('gt', $nowTime);
        }
       
        $map['phone'] = $phone;

        return $this->where($map)->order('ctime desc')->find();
    }
}