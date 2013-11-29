<?php

class devCon_driver extends db_driver
{
    private $user;
    private $pass;
    private $url;

    public function _numRows() {
        return count($this->res['data']);
    }
    public function _query($q) {

        $context = stream_context_create(array(
            'http' => array(
                'method'  => 'POST',
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n"
                    . "Authorization: Basic " . base64_encode("$this->user:$this->pass"),
                'content' => http_build_query(
                    array(
                        'format' => 'serialize',
                        'skip_preout' => '1',
                        'query' => $q,
                    )
                )
            )
        ));
        $data = file_get_contents($this->url, false, $context);
        if (!$data) {
            $this->lastError = 'No data';
        }

        //echo str_replace('<','&lt;',$data);

        $this->res = unserialize(base64_decode($data));
        if ($this->res['error']) {
            $this->lastError = $this->res['error'];
        }

        return $this;
    }
    public function _fetchAssoc() {
        if (!$this->res) {
            return false;
        }
        if ($row = each($this->res['data'])) {
            $row = array_combine($this->res['keys'], $row['value']);

            return $row;
        }
        else {
            return null;
        }
    }
    public function _connect($db) {
        $this->user = $db['username'];
        $this->pass = $db['password'];

        $this->link = true;
        $this->url = 'http://' . $db['hostname'] . '/' . $db['database'];
    }
    public function _escape($s) {
        return mysql_escape_string($s);
    }

}
