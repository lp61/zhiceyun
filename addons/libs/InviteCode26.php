<?php
/**
 * 加密解密算法
 *     codeLength为加密后长度
 *     将num转化成62-codeLength进制 第62-codeLength+1为补充标志位 之后的字母代表补充62-x-1
 */
class InviteCode {
    const DECIMAL_STR_LENGTH = 62;

    private $codeLength = 6;
    private $decimal    = 56;
    private $codeString = '';
    private $decimalStr;
    private $markBitStr;
    private $markLenStr;

    public function num2code($num) {
        if (intval($num) <= 0 ) {
            return false;
        }
        $this->initCodeConfig();

        $_dividend      =   (int) $num;
        $_in_toB        =   '';

        while ( $_dividend > 0 ) {
            $_quotient  = (int) ( $_dividend / $this->decimal );
            $_remainder = ''  . ( $_dividend % $this->decimal ); 
            $_in_toB    = $this->codeString[$_remainder] . $_in_toB;
            $_dividend  = $_quotient;
        }
        $strLen = strlen($_in_toB);
        $len    = $this->codeLength - $strLen;
        // var_dump($_in_toB);

        switch ($len) {
            case 0:
                break;
            case 1:
                $_in_toB = $this->markBitStr . $_in_toB;
                break;
            default:
                $pos     = rand(0, $this->decimal - $len + 1 );
                $moreStr = ($len - 2) > 0 ? substr($this->codeString, $pos, $len-2) : '';
                $_in_toB = $this->markBitStr . $this->markLenStr[$len-1]. $moreStr . $_in_toB;
                break;
        }

        // var_dump($_in_toB);
        return $_in_toB;
    }

    public function code2num($code) {
        if (empty($code)) {
            return false;
        }

        $this->initCodeConfig();

        if ($code[0] == $this->markBitStr) {
            $len = strpos($this->markLenStr, $code[1]);

            if ( $len !== false) {
                $code = substr($code, $len+1, $this->codeLength - $len - 1);
            }else{
                $code = substr($code, 1, $this->codeLength - 1);
            }
        }
        // var_dump($code);

        $strLen = strlen($code);
        $num    = 0;
        for ($i = 0; $i < $strLen; $i++) { 
            $tmpPos = strpos($this->codeString, $code[$i]);
            $num    += $tmpPos * pow($this->decimal, $strLen -$i-1);
        }

        // var_dump($num);
        return $num;
    }

    public function setCodeLength($len = 6) {
        $len = intval($len);
        if ($len >= 4 && $len <= 16) {
            $this->codeLength = $len;
            $this->decimal    = self::DECIMAL_STR_LENGTH - $len;
        }
    }
    private function initCodeConfig() {
        $codeString       = $this->codeString = $this->getCodeString();
        $tmpStr           = $codeString[$this->decimal];
        $tmpArr           = explode($tmpStr, $codeString);
        $this->decimalStr = $tmpArr[0];
        $this->markBitStr = $tmpStr;
        $this->markLenStr = $tmpArr[1];
    }
    private function getCodeString() {
        $string = "DZPkdBw5vyJ3hra7GgALsFUHXEj8NxfWu6n1mCI0biYRzct2qlM9VpQeSoK4TO";
        return  $string;
        // $string = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        // echo str_shuffle($string);
    }

    public static function test() {
        $obj = new InviteCode();
        //$obj->setCodeLength(9);

        for($i = 1; $i <= 9; $i ++)
        {
            for($j = 0; $j <= mt_rand(1, 20); $j ++)
            {
                $l    = pow(10, $i);
                $num  = mt_rand($l, $l*10);
                $code = $obj->num2code($num);
                // echo $num . "=>" . $code . "<=" . $obj->code2num($code) . PHP_EOL;
                var_dump($num . "=>" . $code . "<=" . $obj->code2num($code));
            }   
        }  
        return;

        $i = 1;
        while ( $i <= 56*56*56*56) {
            $code = $obj->num2code($i);
            $num  = $obj->code2num($code);
            if ($i !== $num) {
                var_dump($code .'-'. $num);
            }
            $i++;
        }
        echo $i;
    }
}
InviteCode::test();
