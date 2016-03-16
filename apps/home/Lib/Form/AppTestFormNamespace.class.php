<?php
/**
 * @brief 用户验证
 */
import('FormNamespace', VENDOR_PATH . '/libs/form');
import('@.Form.AppTestVars');

class AppTestFormNamespace extends FormNamespace {
    private $_form_type;
    private $_isInitUserInfo=false;

    public function __construct($init_userInfo = false) {
        $this->_isInitUserInfo = $init_userInfo;

        $this->fileJsonField   = array(
            'name'            => 'fileJson',
            'tipSpanId'       => 'tip_fileJson',
            'requiredMessage' => '请添加APP',
        );

        $this->ApkTypeSelectId = array(
            'name'            => 'ApkTypeSelectId',
            'tipSpanId'       => 'tip_ApkTypeSelectId',
            'requiredMessage' => '请选择APK类型',
        );
    }

    /**
     * [setCommonValidateField 验证表单公共部分]
     */
    public function setCommonValidateField() {
        $this->addField($this->fileJsonField);

        $this->addField($this->ApkTypeSelectId);
    }

    public function setQuickTestValidateField() {
        $this->setCommonValidateField();
    }
    /**
     * saveData 保存表单数据 
     * @param mixed $extraData 
     * @access public
     * @return void
     */
    public function saveData($extraData = array()) {
        $data = self::getPostData();
        $saveFunName = 'save'.ucfirst($this->_form_type).'Data';
        if (method_exists($this, $saveFunName)) {
            return $this->$saveFunName($data, $extraData); 
        }
        return false; 
    }
}