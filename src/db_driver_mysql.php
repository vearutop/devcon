<?php

class mysql_driver extends db_driver {
    public function _numRows() {
        return mysql_affected_rows($this->link);
    }
    public function _query($q) {
        if (!$this->res = mysql_query($q, $this->link)) {
            $this->lastError = mysql_errno($this->link).' '.mysql_error($this->link);
        }
        return $this;
    }
    public function _fetchAssoc() {
        if (!$this->res) {
            return false;
        }
        return mysql_fetch_assoc($this->res);
    }
    public function _connect($db) {
        $this->link = mysql_connect($db['hostname'], $db['username'], $db['password']);
        if (!$this->link) {
            $this->lastError = mysql_errno().' '.mysql_error();
            throw new Exception('Connection to mysql://' . $db['username'].'@'. $db['hostname'] .' failed ');
        }
        $this->query("USE $db[database]");
        $this->query("SET NAMES UTF8");
    }
    public function _escape($s) {
        return mysql_real_escape_string($s, $this->link);
    }


    /**
     * @param $search
     * @return void
     */
    public function fullSearch($search) {
        $tables = array();
        $res = mysql_query("show table status");
        while ($r = mysql_fetch_assoc($res)) $tables[$r['Name']] = $r['Rows'];

        foreach ($tables as $table => $count)	{
            echo "$table :: $count<br>";

            if (!$count || $count>10000) {
                continue;
            }


            $q = "SELECT * FROM $table WHERE ";
            $res = mysql_query("DESC $table");
            while ($r = mysql_fetch_assoc($res)) $q .= '`'.$r['Field'] . "` LIKE '%$search%' OR ";
            $q = substr($q, 0, -3);
            echo_query($q, 0);
            //  echo $q."<br />";
        }
    }
}
