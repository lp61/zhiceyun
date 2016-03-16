<?php

// 通用数字和邀请码可逆转换类库
// Author: liwei <2653033466@qq.com>
// 
// 

class MyInviteCode
{
  private static $codebase = 36;
  private static $factor = 100000007;
  private static $codelen = 8;
  private static $prefix_startnum = array(65,97);

  private static function _prefix2len($prefix)
  {
    $len = 0;
    if (strlen($prefix) <= 0)
      return $len;
    $prefixnum = ord($prefix[0]);
    
    if ($prefixnum >= self::$prefix_startnum[0] && $prefixnum <= self::$prefix_startnum[0] + 10)
    {
      $starnum = self::$prefix_startnum[0];
    }
    else
    {
      $starnum = self::$prefix_startnum[1];
    }
    return $prefixnum-$starnum;
  }
  
  private static function _len2prefix($len,$sign)
  {
    $len = intval($len);
    $sign = strval($sign);
    
    if (strlen($sign) > 0 && is_numeric($sign[0]))
    {
      $prefix = chr(self::$prefix_startnum[0] + $len);
    }
    else
    {
      $prefix = chr(self::$prefix_startnum[1] + $len);
    }
    return $prefix;
  }    
  
  public static function testcase()
  {
    for($i = 1;$i<=8;$i++)
    {
      for($j = 0;$j<=mt_rand(1,20);$j++)
      {
        $l = pow(10,$i);
        $num = mt_rand($l,$l*9);
        $code = self::num2code($num);
        echo $num . "=>" . $code . "<=" . self::code2num($code) . PHP_EOL;
      }   
    }  
  }  
  
  /**
    邀请码转(十进制)数字
    @access public
	
	  @param string $code 邀请码(字串)
	         	 	  
	  @return int (十进制)数字
	
	**/ 
  public static function code2num($code)
  {
    $num = 0;
    $code = strval($code);
    if (strlen($code) <=1)
      return $num;
    
    //首先根据标志位(第一个字符)判断填充字符串长度
    $extralen = self::_prefix2len($code);
        
    $code = substr($code,$extralen + 1);
    $num = base_convert($code,self::$codebase,10);
    return $num;  
  }  

  /**
    (十进制)数字转邀请码
    @access public
	
	  @param int $num (十进制)数字
	  @param int $maxlen 邀请码长度 (默认可不设置 内置约定为8个字符)
	         	 	  
	  @return string 邀请码(字串)
	
	**/ 
  public static function num2code($num,$maxlen = 0)
  {
    $code = '';
    $num = intval($num);
    if (0 === $num)
      return $code;
    
    $code = base_convert($num,10,self::$codebase);
    if ($maxlen <= 0)
      $maxlen = self::$codelen;
    $extralen = $maxlen - 1 - strlen($code);
    $extrastr = "";
    if ($extralen > 0)
    {
      $extrastr = substr(strval(base_convert(self::$factor * $num,10,self::$codebase)),0,$extralen);
      $code = $extrastr . $code;
    }
    else
    {
      $extralen = 0;
    }
    
    $prefix = self::_len2prefix($extralen,$code);
            
    if (!empty($prefix))
      $code = $prefix . $code;    
    return $code;
  }  
}

?>