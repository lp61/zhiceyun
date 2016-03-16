<?php
error_reporting(0);
session_start();

include_once '../addons/libs/check_code/CheckCode.class.php';

if (isset($_POST['verify']) && !empty($_POST['verify'])) {
    $verify = $_POST['verify'];
    $bool   = CheckCode::isValid($verify);// 不区分大小写

    if ($bool == true){
        echo json_encode($bool);
    }
    else {
        echo json_encode('验证码输入不正确');
    }
}else{
    $w = !empty($_GET['w']) ? $_GET['w'] : 60;
    $h = !empty($_GET['h']) ? $_GET['h'] : 22;

    $options = array(
        'width'     => $w,
        'height'    => $h
    );
    $val = CheckCode::complex($options);
    echo $val;
}