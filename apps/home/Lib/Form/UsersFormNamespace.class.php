<?php

/**
 * @brief 用户验证
 */
import('FormNamespace', VENDOR_PATH . '/libs/form');
import('@.Form.UserInfoVars');

class UsersFormNamespace extends FormNamespace {

    private $_form_type;
    private $_isInitUserInfo = false;

    public function __construct($init_userInfo = false) {
        $this->_isInitUserInfo = $init_userInfo;
        $this->emailValidField = array(
            'name' => 'email',
            'tipSpanId' => 'tip_span_email',
            'emptyValue' => '请输入有效的邮箱账号',
            'focusMessage' => '请输入有效的邮箱账号',
            'requiredMessage' => '请输入邮箱账号',
            'rules' => array(
                array(
                    'mode' => ValidatorConfig::MODE_LENGTH,
                    'minLength' => 4,
                    'maxLength' => 100,
                    'errorMessage' => '邮箱长度为4~100',
                ),
                array(
                    'mode' => ValidatorConfig::MODE_AJAX,
                    'ajaxUrl' => U('home/Public/isEmailAvailable'),
                    'errorMessage' => '账号已经存在',
                ),
                array(
                    'mode' => ValidatorConfig::MODE_REGEXP,
                    'regexp' => RegexpConfig::EMAIL,
                    'errorMessage' => '邮箱格式不正确',
                ),
            ),
        );
        $this->verifyValidField = array(
            'name' => 'verify',
            'tipSpanId' => 'tip_verify',
            'defaultMessage' => '',
            'focusMessage' => '',
            'requiredMessage' => '请填写验证码',
            'rules' => array(
                array(
                    'mode' => ValidatorConfig::MODE_CUSTOM,
                    'jsCallback' => 'function(str){ return str.length==4; }',
                    'errorMessage' => '验证码不正确',
                ),
                array(
                    'mode' => ValidatorConfig::MODE_AJAX,
                    // 'ajaxUrl'         => U('home/Public/isVerifyAvailable'),
                    'ajaxUrl' => __PUBLIC__ . '/checkcode.php',
                    'errorMessage' => '验证码不正确',
                ),
            ),
        );

        $this->phoneValidField = array(
            'name' => 'phone',
            'tipSpanId' => 'tip_span_order_phone',
            'focusMessage' => '请输入有效手机号',
            'requiredMessage' => '请输入手机号',
            'rules' => array(
                array(
                    'mode' => ValidatorConfig::MODE_LENGTH,
                    'minLength' => 11,
                    'maxLength' => 11,
                    'errorMessage' => '请输入正确手机号',
                ),
                array(
                    'mode' => ValidatorConfig::MODE_REGEXP,
                    'regexp' => '/1\d{10}/',
                    'errorMessage' => '请输入正确手机号',
                ),
            ),
        );
        $this->phoneCodeValidField = array(
            'name' => 'phone_code',
            'tipSpanId' => 'tip_span_phone_code',
            'focusMessage' => '请输入确认码',
            'requiredMessage' => '确认码不能为空',
            'rules' => array(
                array(
                    'mode' => ValidatorConfig::MODE_REGEXP,
                    'regexp' => '/^\d+$/',
                    'errorMessage' => '请输入纯数字确认码',
                ),
            // array(
            //     'mode'            => ValidatorConfig::MODE_AJAX,
            //     'ajaxUrl'         => U('home/Public/isPhoneCodeAvailable'),
            //     // 'mode'            => ValidatorConfig::MODE_CUSTOM,
            //     // 'jsCallback'      => 'isPhoneCodeAvailable',
            //     'errorMessage'    => '手机确认码错误',
            // ),
            ),
        );
        $this->requiredRule = array(
            'mode' => ValidatorConfig::MODE_CUSTOM,
            'jsCallback' => 'function is_required(str) { if ( str <= 0 ) { return false; } return true}',
            'errorMessage' => '必选',
        );

        $this->companyNameValidField = array(
            'name' => 'company_name',
            'tipSpanId' => 'tip_span_company_name',
            'emptyValue' => '如：xx公司',
            'focusMessage' => '公司名称',
            'requiredMessage' => '不能为空',
            'rules' => array(
                array(
                    'mode' => ValidatorConfig::MODE_LENGTH,
                    'serverCharset' => 'UTF-8',
                    'minLength' => 2,
                    'maxLength' => 50,
                    'errorMessage' => '2-50字',
                ),
                array(
                    'mode' => ValidatorConfig::MODE_REGEXP,
                    'regexp' => array('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u', '/^[\u4E00-\u9FA5\uF900-\uFA2Da-zA-Z0-9]+$/'),
                    'errorMessage' => '要填写汉字、字母、数字哦',
                )
            ),
        );

        $this->passwordValidateField = array(
            'name' => 'password',
            'tipSpanId' => 'tip_span_password',
            'focusMessage' => '6-20位字母、数字组合',
            'rules' => array(
                array(
                    'mode' => ValidatorConfig::MODE_REGEXP,
                    'regexp' => '/^[a-zA-Z0-9]{6,20}$/',
                    'errorMessage' => '6-20位字母、数字组合',
                ),
                array(
                    'mode' => ValidatorConfig::MODE_CUSTOM,
                    'jsCallback' => 'function is_empty_password(str) { var n = str.length;if ( n === 0 ) { return false; } return true}',
                    'errorMessage' => '不能为空',
                ),
                array(
                    'mode' => ValidatorConfig::MODE_CUSTOM,
                    'jsCallback' => 'function is_complex_password(str) { var n = str.length; if ( n < 6 ) { return false; } var cc = 0, c_step = 0; for (var i=0; i<n; ++i) { if ( str.charCodeAt(i) == str.charCodeAt(0) ) { ++ cc; } if ( i > 0 && str.charCodeAt(i) == str.charCodeAt(i-1)+1) { ++ c_step; } } if ( cc == n || c_step == n-1) { return false; } return true; }',
                    'errorMessage' => '请勿使用连续重复字符',
                ),
                array(
                    'mode' => ValidatorConfig::MODE_CUSTOM,
                    'jsCallback' => 'function is_num_str(str) { var reg=/^[\d]{6,20}$/;var reg1=/^[\a-zA-Z]{6,20}$/; return !reg.test(str) && !reg1.test(str);}',
                    'errorMessage' => '必须是字母、数字组合',
                )
            )
        );
        $this->repasswordValidateField = array(
            'name' => 'repassword',
            'tipSpanId' => 'tip_span_repassword',
            'focusMessage' => '重复输一次密码',
            'rules' => array(
                array(
                    'mode' => ValidatorConfig::MODE_COMPARE_FIELD,
                    'fieldName' => 'repassword',
                    'operator' => '==',
                    'toFieldName' => 'password',
                    'errorMessage' => L("password_same_rule"),
                ),
            )
        );
    }

    /**
     * [setRegUserComValidateField 验证表单公共部分]
     */
    public function setRegUserComValidateField() {
        if ($this->_isInitUserInfo === true) {
            return;
        }
        $this->addField($this->emailValidField);
        $this->addField($this->verifyValidField);
        $this->addField($this->passwordValidateField);
        $this->addField($this->repasswordValidateField);

        //验证是否同意条款
        $this->addField(array(
            'name' => 'affirm',
            'tipSpanId' => 'tip_affirm',
            'defaultMessage' => '',
            'focusMessage' => '',
            'requiredMessage' => '必须先接受用户服务协议',
            'rules' => array(
                array(
                    'mode' => ValidatorConfig::MODE_CUSTOM,
                    'jsCallback' => 'function is_checked_affirm(str) { if ( str != 1 ) { return false; } return true}',
                    'errorMessage' => '必须先接受用户服务协议',
                ),
            )
        ));
    }

    /**
     * [setUserValidateField 普通用户验证表单]
     * @return [type] [description]
     */
    public function setNormalUserValidateField() {
        $this->_form_type = 'user';

        $this->setRegUserComValidateField();

        if ($this->_isInitUserInfo === true) {
            return;
        }
        $this->addField($this->verifyValidField);
    }

    /**
     * [setVipUserValidateField VIP用户验证表单]
     * @return [type] [description]
     */
    public function setVipUserValidateField() {
        $this->_form_type = 'user';
        $this->setRegUserComValidateField();
        $this->addField($this->companyNameValidField);
//        $this->addField(array(
//            'name'            => 'work_classify',
//            'tipSpanId'       => 'tip_span_work_classify',
//            'requiredMessage' => '必选',
//            'rules'           => array($this->requiredRule),
//        ));
        $this->addField(array(
            'name' => 'work_station',
            'tipSpanId' => 'tip_span_work_station',
            'requiredMessage' => '必选',
            'rules' => array($this->requiredRule),
        ));

        $this->addField($this->phoneValidField);
        if ($this->_isInitUserInfo === true) {
            return;
        }
        $this->addField($this->phoneCodeValidField);
    }

    /**
     * [setCompanyUserValidateField 企业用户验证表单]
     * @return [type] [description]
     */
    public function setCompanyUserValidateField() {
        $this->_form_type = 'user';

        $this->setRegUserComValidateField();
        $this->addField($this->companyNameValidField);
//        $this->addField(array(
//            'name'            => 'work_classify',
//            'tipSpanId'       => 'tip_span_work_classify',
//            'requiredMessage' => '必选',
//            'rules'           => array($this->requiredRule),
//        ));
        $this->addField(array(
            'name' => 'company_size',
            'tipSpanId' => 'tip_span_company_size',
            'requiredMessage' => '必选',
            'rules' => array($this->requiredRule),
        ));

        $this->addField($this->phoneValidField);

        if ($this->_isInitUserInfo != true) {
            $this->addField($this->phoneCodeValidField);
        }

        //验证是否营业执照
        $this->addField(array(
            'name' => 'pic_url',
            'tipSpanId' => 'tip_pic_url',
            'rules' => array(
                array(
                    'mode' => ValidatorConfig::MODE_CUSTOM,
                    'jsCallback' => 'function is_pic_url(str) {if ($("#J_img").length) {return true;}var len=str.length; if ( len <= 4 ) { return false; } return true}',
                    'errorMessage' => '请上传营业执照副本',
                ),
            )
        ));
    }

    /**
     * [setLoginValidateField 登陆验证表单]
     * @return [type] [description]
     */
    public function setLoginValidateField() {
        $this->_form_type = 'email';

        $this->addField(array(
            'name' => 'email',
            'tipSpanId' => 'tip_span_email',
            'emptyValue' => '请输入邮箱账号',
            'requiredMessage' => '请输入邮箱账号',
            'rules' => array(
                array(
                    'mode' => ValidatorConfig::MODE_REGEXP,
                    'regexp' => RegexpConfig::EMAIL,
                    'errorMessage' => '邮箱格式不正确',
                ),
            ),
        ));
        $this->addField(array(
            'name' => 'password',
            'dbFieldName' => 'password',
            'tipSpanId' => 'tip_span_password',
            'emptyValue' => '请输入密码',
            'defaultMessage' => '',
            'focusMessage' => '',
            'requiredMessage' => '密码不能为空',
        ));
        // $this->addField($this->verifyValidField);
    }

    /**
     * [setSendPasswordValidateField 忘记密码找回]
     * @return [type] [description]
     */
    public function setSendPasswordValidateField($pwdType) {
        $this->addField(array(
            'name' => 'email',
            'tipSpanId' => 'tip_span_email',
            // 'emptyValue'      => '请输入有效的邮箱账号',
            // 'focusMessage'    => '请输入有效的邮箱账号',
            'requiredMessage' => '请输入邮箱账号',
            'rules' => array(
                array(
                    'mode' => ValidatorConfig::MODE_AJAX,
                    'ajaxUrl' => U('home/Public/isEmailAvailable', array('p' => 1)),
                    'errorMessage' => '账号不存在',
                ),
                array(
                    'mode' => ValidatorConfig::MODE_REGEXP,
                    'regexp' => RegexpConfig::EMAIL,
                    'errorMessage' => '邮箱格式不正确',
                ),
            ),
        ));
        $this->addField($this->verifyValidField);
    }

    /**
     * [setSendPhonwPwdValidateField 忘记密码手机找回]
     * @return [type] [description]
     */
    public function setSendPhonwPwdValidateField($pwdType) {
        $this->addField(array(
            'name' => 'phone',
            'tipSpanId' => 'tip_span_phone',
            'requiredMessage' => '请输入手机号码',
            'rules' => array(
                array(
                    'mode' => ValidatorConfig::MODE_AJAX,
                    'ajaxUrl' => U('home/Public/isPhoneAvailable', array('p' => 1)),
                    'errorMessage' => '手机号码不存在',
                ),
                array(
                    'mode' => ValidatorConfig::MODE_REGEXP,
                    'regexp' => '/1\d{10}/',
                    'errorMessage' => '手机号码格式不正确',
                ),
            ),
        ));
        $this->addField($this->verifyValidField);
    }

    /**
     * [setUserEditPwdValidateField 修改密码]
     */
    public function setUserEditPwdValidateField() {
        $oldPwdRule = array(
            'name' => 'oldpassword',
            'tipSpanId' => 'tip_span_oldpassword',
            'emptyValue' => '原始密码',
            'focusMessage' => '原始密码',
            'rules' => array(
                array(
                    'mode' => ValidatorConfig::MODE_CUSTOM,
                    'jsCallback' => 'function is_empty_password(str) { var n = str.length;if ( n === 0 ) { return false; } return true}',
                    'errorMessage' => '不能为空',
                ),
                array(
                    'mode' => ValidatorConfig::MODE_AJAX,
                    'ajaxUrl' => U('home/Account/checkOldPwd'),
                    'errorMessage' => '原始密码不正确',
                )
            )
        );
        $this->addField($oldPwdRule);

        $passwordValidateField = $this->passwordValidateField;
        $passwordValidateField['rules'][] = array(
            'mode' => ValidatorConfig::MODE_COMPARE_FIELD,
            'fieldName' => 'password',
            'operator' => '!=',
            'toFieldName' => 'oldpassword',
            'errorMessage' => '不能和原始密码一致',
        );
        $this->addField($passwordValidateField);
        $this->addField($this->repasswordValidateField);
    }

    /**
     * [resetPasswordValidateField 忘记密码-重置密码]
     * @return [type] [description]
     */
    public function resetPasswordValidateField() {
        $this->addField($this->passwordValidateField);
        $this->addField($this->repasswordValidateField);
    }

    /**
     * [setUserFeedbackValidateField 用户反馈]
     */
    public function setUserFeedbackValidateField() {
        $this->addField(array(
            'name' => 'type',
            'tipSpanId' => 'tip_span_type',
            'requiredMessage' => '必选',
            'rules' => array($this->requiredRule),
        ));
        $this->addField(array(
            'name' => 'content',
            'tipSpanId' => 'tip_span_content',
            'emptyValue' => '描述问题',
            'focusMessage' => '描述问题',
            'requiredMessage' => '描述问题',
            'rules' => array(
                array(
                    'mode' => ValidatorConfig::MODE_LENGTH,
                    'serverCharset' => 'UTF-8',
                    'minLength' => 18,
                    'maxLength' => 5000,
                    'errorMessage' => '18-5000字',
                )
            ),
        ));
        $this->addField(array(
            'name' => 'title',
            'tipSpanId' => 'tip_span_title',
            'emptyValue' => '标题',
            'focusMessage' => '标题',
            'requiredMessage' => '不能为空',
            'rules' => array(
                array(
                    'mode' => ValidatorConfig::MODE_LENGTH,
                    'serverCharset' => 'UTF-8',
                    'minLength' => 2,
                    'maxLength' => 50,
                    'errorMessage' => '2-50字',
                ),
                array(
                    'mode' => ValidatorConfig::MODE_REGEXP,
                    'regexp' => array('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u', '/^[\u4E00-\u9FA5\uF900-\uFA2Da-zA-Z0-9]+$/'),
                    'errorMessage' => '要填写汉字、字母、数字哦',
                )
            ),
        ));
        $this->addField($this->verifyValidField);
    }

    /**
     * [setUpgradePhoneValidateField 完善手机信息]
     * @return [type] [description]
     */
    public function setUpgradePhoneValidateField() {
        $this->addField($this->phoneValidField);
        $this->addField($this->phoneCodeValidField);
    }

    /**
     * [setUpgradeVipValidateField vip信息更新验证]
     * @return [type] [description]
     */
    public function setUpgradeVipValidateField() {
        $this->addField($this->companyNameValidField);
        $this->addField(array(
            'name' => 'work_station',
            'tipSpanId' => 'tip_span_work_station',
            'requiredMessage' => '必选',
            'rules' => array($this->requiredRule),
        ));
//        $this->addField(array(
//            'name'            => 'work_classify',
//            'tipSpanId'       => 'tip_span_work_classify',
//            'requiredMessage' => '必选',
//            'rules'           => array($this->requiredRule),
//        ));
    }

    /**
     * [setUpgradeCompanyValidateField 企业用户信息更新验证]
     * @return [type] [description]
     */
    public function setUpgradeCompanyValidateField() {
        $this->addField(array(
            'name' => 'company_size',
            'tipSpanId' => 'tip_span_company_size',
            'requiredMessage' => '必选',
            'rules' => array($this->requiredRule),
        ));
        // $this->addField(array(
        //     'name'            => 'pic_url',
        //     'tipSpanId'       => 'tip_pic_url',
        //     'rules'        => array(
        //         array(
        //             'mode'         => ValidatorConfig::MODE_CUSTOM,
        //             'jsCallback'   => 'function is_pic_url(str) {if ($("#J_img").length) {return true;}var len=str.length; if ( len <= 4 ) { return false; } return true}',
        //             'errorMessage' => '请上传营业执照副本',
        //         ),
        //     )
        // ));
    }

    public function setOtherRegUserValidateField() {
        $this->addField($this->emailValidField);

        $this->addField($this->passwordValidateField);
        $this->addField($this->verifyValidField);
    }

    /**
     * saveData 保存表单数据 
     * @param mixed $extraData 
     * @access public
     * @return void
     */
    public function saveData($extraData = array()) {
        $data = self::getPostData();
        $saveFunName = 'save' . ucfirst($this->_form_type) . 'Data';
        if (method_exists($this, $saveFunName)) {
            return $this->$saveFunName($data, $extraData);
        }
        return false;
    }

}
