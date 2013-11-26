<?php

class pgsql_driver extends db_driver {
    public function _numRows() {
        if (!$this->res || !$this->link) {
            return false;
        }
        return pg_num_rows($this->res);
    }
    public function _query($q) {
        if (!$this->link) {
            return $this;
        }

        // mysql query hacks
        if (strlen($q) < 1000) {
            $tmp = strtoupper(trim($q));
            if ('SHOW TABLES' == $tmp) {
                $q = "SELECT tablename AS Tables_in_DB FROM pg_catalog.pg_tables WHERE schemaname='public'";
            }
            elseif ('SHOW PROCESSLIST' == $tmp) {
                $q = "SELECT * FROM pg_stat_activity";
            }
            elseif (substr($tmp, 0, 5) == 'DESC ') {
                $q = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".trim(substr($q, 5))."'";
            }
        }

        if (!$this->res = pg_query($this->link, $q)) {
            $this->lastError = pg_last_error($this->link);
        }
        return $this;
    }
    public function _fetchAssoc() {
        if (!$this->res) {
            return false;
        }
        return pg_fetch_assoc($this->res);
    }
    public function _connect($db) {
        if (!$this->link = pg_connect("host=$db[hostname] dbname=$db[database] user=$db[username] password=$db[password] ".
            "options='--client_encoding=UTF8'")) {
            $this->lastError = 'Connection error.';
        }
    }
    public function _escape($s) {
        return pg_escape_string($this->link, $s);
    }
}