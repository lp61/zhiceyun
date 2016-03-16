<?php

/**
 * Description of sqlite
 *
 * @author vini
 */
class sqlite extends SQLite3 {

    //put your code here
    function __construct($file, $flags = null, $encryption_key = null) {
        $this->open($file) or $this->lastErrorMsg();
    }

    function get_one($sql) {
        $ret = $this->query($sql) or $this->lastErrorMsg();
        return $ret->fetchArray(SQLITE3_ASSOC);
    }

    function get_array($sql) {
        $ret = $this->query($sql) or $this->lastErrorMsg();
        $data = array();
        while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }

}
