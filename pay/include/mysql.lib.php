<?php

class Connect_Database {

    var $m_host;
    var $m_port;
    var $m_user;
    var $m_password;
    var $m_name;
    var $m_link;

    function Begin() {
        mysql_query("BEGIN");   //开始锁定
    }

    function End($fun = 0) {
        if ($fun == 0) {
            mysql_query("COMMIT");   //提交
        } else {
            mysql_query("ROLLBACK");   //回滚
        }
    }

    function Pconnect_Database($config) {
        $this->m_host = $config->mDbHost;
        $this->m_port = $config->mDbPort;
        $this->m_user = $config->mDbUser;
        $this->m_password = $config->mDbPassword;
        $this->m_name = $config->mDbDatabase;

        $real_host = $this->m_host . ":" . $this->m_port;
        $this->m_link = mysql_pconnect($real_host, $this->m_user, $this->m_password) or die("Error: Can't Login the Database Server!");
        mysql_select_db($this->m_name, $this->m_link) or die("Error: Can't Select the Database:" . $this->m_name);
    }

    function Connect_Database($config) {
        $this->m_host = $config->mDbHost;
        $this->m_port = $config->mDbPort;
        $this->m_user = $config->mDbUser;
        $this->m_password = $config->mDbPassword;
        $this->m_name = $config->mDbDatabase;

        $real_host = $this->m_host . ":" . $this->m_port;
        $this->m_link = mysql_connect($real_host, $this->m_user, $this->m_password) or die("Error: Can't Login the Database Server!");
        mysql_select_db($this->m_name, $this->m_link) or die("Error: Can't Select the Database:" . $this->m_name);
    }

    function Close_Database() {
        mysql_close($this->m_link) or die("Error: Mysql Close Fault!");
    }

    function Query($SQL) {
        SqlWrite($SQL);  //写日志
        $result = mysql_query($SQL, $this->m_link) or die(mysql_error());
        return $result;
    }

    function Fetch_Array($result) {
        $row = mysql_fetch_array($result);
        return $row;
    }

    function Fetch_Row($result) {
        $row = mysql_fetch_row($result);
        return $row;
    }

    function Fetch_Object($result) {
        $row = mysql_fetch_object($result);
        return $row;
    }

    function Free_Result(&$result) {
        return mysql_free_result($result) or die("Error: Can't Free Result in Memory!");
    }

    function Select_Num_Rows($result) {
        return mysql_num_rows($result);
    }

    function Update_Num_Rows() {
        $result = mysql_affected_rows($this->m_link);
        return $result;
    }

    function Insert_Num_Rows() {
        $result = mysql_affected_rows($this->m_link);
        return $result;
    }

    function Delete_Num_Rows() {
        $result = mysql_affected_rows($this->m_link);
        return $result;
    }

    function Insert_ID() {
        return mysql_insert_id($this->m_link);
    }

    function get_one($sql) {
        $query = $this->Query($sql);
        $result = mysql_fetch_array($query, MYSQL_ASSOC);
        return $result;
    }

    function get_array($sql) {
        $query = $this->Query($sql);
        $data = array();
        while ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
            $data[] = $result;
        }
        return $data;
    }

    function getTableFields($tbName) {
        $rescolumns = $this->Query("SHOW FULL COLUMNS FROM " . $tbName);
        $data = array();
        while ($row = mysql_fetch_array($rescolumns)) {
            $data[] = $row['Field'];
        }
        return $data;
    }

    function get_maxid($title, $step = 1) {
        $title = strtoupper($title);
        //获取一条记录
        $maxid = 100;
        $this->Begin();  //开始锁定
        $query = "SELECT id  From tb_id WHERE  title='" . $title . "' for update";
        $id = $this->get_one($query);
        if ($id['id'] > 0) {
            $maxid = $id['id'];
            if ($step > 1) {
                $maxid = $maxid + $step;
            } else {
                $maxid++;
            }
            //更新对应的序列号
            $query = "UPDATE  tb_id SET id='" . $maxid . "'  WHERE title='" . $title . "' ";
        } else {
            $maxid = 100;
            //添加对应的序列号             
            $query = "INSERT INTO  tb_id SET id='" . $maxid . "',title='" . $title . "' ";
        }
        $this->Query($query);
        $this->End();  //结束
        return $maxid;
    }

}

?>