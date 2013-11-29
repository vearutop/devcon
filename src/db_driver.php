<?php
abstract class db_driver {
    protected $link = null;
    /**
     * @var null | resource
     */
    protected $res = null;
    public $lastError = false;
    public $lastQuery = '';
    public $lastTime = 0;
    protected $db_config = array();
    public $queryOptions = array();

    public function __construct($dsn) {
        $dsn = parse_url($dsn);
        $this->db_config = array(
            'username' => empty($dsn['user']) ? '' : $dsn['user'],
            'password' => empty($dsn['pass']) ? '' : $dsn['pass'],
            'hostname' => $dsn['host'],
            'database' => trim($dsn['path'],'/'),
        );
        $this->_connect($this->db_config);
    }

    abstract protected function _connect($db);
    abstract protected function _query($q);


    /**
     * @param $q
     * @return db_driver
     */
    public function query($q) {
        $this->queryOptions = array();
        $this->lastError = '';

        if (substr($q, 0, 5) == '/* o:') {
            foreach (explode(',', substr($q, 5, strpos($q, '*/', 5) - 5)) as $o) {
                $o = trim($o);
                $this->queryOptions [$o]= true;
            }
            print_r($this->queryOptions);
        }

        $this->lastQuery = $q;
        if (!$this->link) {
            return $this;
        }
        //$this->lastError = false;
        $start = microtime(1);
        $this->_query($q);
        $this->lastTime = microtime(1) - $start;
        return $this;
    }


    public function status() {
        $rows = $this->_numRows();

        echo "<h4>".substr($this->lastQuery, 0, 200)."</h4>";
        echo "($rows) " . ($this->lastTime) . "sec <br />\n";
        if ($this->lastError) {
            echo "ERROR: ",$this->lastError,"<br />\n";
        }
    }


    public function showSerialized() {
        $result = array('data' => array(), 'keys' => array(), 'error' => '');
        $data = &$result['data'];

        if ($this->lastError) {
            $result['error'] = $this->lastError;
        }

        $desc = $this->_fetchAssoc();
        if (!$desc) {
            echo base64_encode(serialize($result));
            return;
        }

        $result['keys'] = array_keys($desc);
        $data []= array_values($desc);

        while ($r = $this->_fetchAssoc()) {
            $data []= array_values($r);
        }

        echo base64_encode(serialize($result));
    }

    /**
     * @param string $type
     * @param int $html_escape
     * @return bool|db_driver
     */
    public function show($type='html', $html_escape = 1) {
        if ('serialize' == $type) {
            return $this->showSerialized();
        }

        $rows = $this->_numRows();

        echo "<h4>$this->lastQuery</h4>";
        echo "($rows) " . ($this->lastTime) . "sec <br />\n";
        if ($this->lastError) {
            echo "ERROR: ",$this->lastError,"<br />\n";
        }

        if (!$rows) {
            //   return false;
        }

        // separators
        if ('jira' == $type) {
            $head = '<pre>';
            $tr_1 = '';
            $tr_2 = "\n";
            $th_1 = "||\t";
            $th_2 = '';
            $th_3 = "\t||";
            $td_1 = "|\t";
            $td_2 = '';
            $td_3 = "\t|";
            $tail = '</pre>';
        }
        else {
            $head = '<table class="sortable"><tbody>';
            $tr_1 = '<tr>';
            $tr_2 = '</tr>';
            $th_1 = '<th>';
            $th_2 = '</th>';
            $th_3 = '';
            $td_1 = '<td>' . (!empty($this->queryOptions['pre']) ? '<pre>' : '');
            $td_2 = (!empty($this->queryOptions['pre']) ? '</pre>' : '') . '</td>';
            $td_3 = '';
            $tail = '</tbody></table>';
        }

        echo $head;

        if (empty($this->queryOptions['rotate'])) {
            $desc = $this->_fetchAssoc();
            if (!$desc) {
                return $this;
            }
            $l = '';
            $h = '';
            foreach ($desc as $k => $d) {
                if (is_null($d)) {
                    $d = 'NULL';
                }
                elseif ($html_escape) {
                    $d = str_replace('<', '&lt;', $d);
                }

                $h .= $th_1 . $k . $th_2;
                $l .= $td_1 . $d . $td_2;
            }
            $h .= $th_3;
            $l .= $td_3;
            echo $tr_1, $h, $tr_2, $tr_1, $l, $tr_2;

            while ($desc = $this->_fetchAssoc())
            {
                echo $tr_1;
                foreach ($desc as $d) {
                    if (is_null($d)) {
                        $d = 'NULL';
                    }
                    elseif ($html_escape) {
                        $d = str_replace('<', '&lt;', $d);
                    }
                    echo $td_1 . $d . $td_2;
                }
                echo $td_3, $tr_2;
            }
        }


        // rotated table
        else {
            $desc = $this->_fetchAssoc();
            if (!$desc) {
                return $this;
            }

            $rows = array();
            $i = 0;
            foreach ($desc as $k => $d) {
                if (is_null($d)) {
                    $d = 'NULL';
                }
                elseif ($html_escape) {
                    $d = str_replace('<', '&lt;', $d);
                }
                $rows[++$i] = $th_1 . $k . $th_2 . $td_1 . $d . $td_2;
            }

            while ($desc = $this->_fetchAssoc()) {
                $i = 0;
                foreach ($desc as $d) {
                    if (is_null($d)) {
                        $d = 'NULL';
                    }
                    elseif ($html_escape) {
                        $d = str_replace('<', '&lt;', $d);
                    }

                    $rows[++$i] .= $td_1 . $d . $td_2;
                }
            }

            foreach ($rows as $line) {
                echo $tr_1 . $line . $tr_2;
            }
        }



        echo $tail;
        return $this;
    }

    /**
     * @param string $title
     * @return bool|db_driver
     */
    public function xls($title = 'default') {
        $rows = $this->_numRows();
        if (!$rows) return false;

        xlsReport::addSheet($title);
        while ($r = $this->_fetchAssoc()) {
            xlsReport::addRow($r);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function all() {
        $result = array();
        while ($r = $this->_fetchAssoc()) {
            $result []= $r;
        }
        return $result;
    }

    public function getAll() {
        $result = array();

        while ($r = $this->_fetchAssoc()) {
            $result []= $r;
        }
        return $result;
    }





    protected function getSqlDumpQueryEcho($sql, $options) {
        if (!empty($options['copy_to'])) {
            db($options['copy_to'])->query($sql)->status();

        }
        else {
            echo $sql;
        }

    }

    /**
     * @param bool $options
     * @return bool
     */
    function getSqlDump($options = false) {
        if (!$options) {
            return false;
        }

        if (empty($options['estimate'])) {

            if (empty($options['copy_to'])) {
                ob_end_clean();
                header('Content-Type: application/force-download');
                header('Content-Disposition: attachment; filename="dump_'.date('Y-m-d').'_'.
                    $this->db_config['database'].'_'.
                    $this->db_config['hostname'].'.sql"');
            }
            $this->query("SET NAMES UTF8");


            $this->getSqlDumpQueryEcho("SET NAMES UTF8;\n", $options);
            $this->getSqlDumpQueryEcho("SET FOREIGN_KEY_CHECKS=0;\n\n\n", $options);

        }
        else {
            echo '<pre>';
            $total_size = 0;
            $total_rows = 0;
        }

        if (!empty($options['tables_like'])) {
            if (!isset($options['tables'])) {
                $options['tables'] = array();
            }
            $res = $this->query("SHOW TABLES LIKE '$options[tables_like]'");
            while ($r = $this->fetchRow($res)) {
                $options['tables'][] = $r[0];
            }
            unset($options['tables_like']);
        }

        $this->query("SHOW TABLE STATUS");
        $table_status = array();
        while ($r = $this->_fetchAssoc()) {
            $table_status []= $r;
        }
        // if ($r['Engine']) {
        // }
        foreach ($table_status as $r) {
            $this->query("SHOW CREATE TABLE $r[Name]");
            if (($ct = $this->_fetchAssoc()) && !empty($ct['Create Table'])) {
                if (!empty($options['tables']) && !in_array($r['Name'],$options['tables'])) {
                    if (!empty($options['estimate'])) {
                        echo "skipping $r[Name]: $r[Rows] rows, $r[Data_length] total bytes\n";
                    }
                    continue;
                }

                if (!empty($options['skip_tables']) && in_array($r['Name'],$options['skip_tables'])) {
                    if (!empty($options['estimate'])) {
                        echo "skipping $r[Name]: $r[Rows] rows, $r[Data_length] total bytes\n";
                    }
                    continue;
                }

                if (empty($options['estimate'])) {
                    $this->getSqlDumpQueryEcho("DROP TABLE IF EXISTS $r[Name];\n\n", $options);
                    $this->getSqlDumpQueryEcho($ct['Create Table'].";\n\n", $options);
                }


                if (!empty($options['skip_content']) && in_array($r['Name'],$options['skip_content'])) {
                    if (!empty($options['estimate'])) {
                        echo "skipping $r[Name]: $r[Rows] rows, $r[Data_length] total bytes\n";
                    }
                    continue;
                }

                if (!empty($options['skip_content_maxrows']) && $r['Rows']>=$options['skip_content_maxrows']) {
                    if (!empty($options['estimate'])) {
                        echo "skipping $r[Name]: $r[Rows] rows, $r[Data_length] total bytes\n";
                    }
                    continue;
                }

                if (!empty($options['skip_content_maxdata']) && $r['Data_length']>=$options['skip_content_maxdata']) {
                    if (!empty($options['estimate'])) {
                        echo "skipping $r[Name]: $r[Rows] rows, $r[Data_length] total bytes\n";
                    }
                    continue;
                }

                $op = array('notextarea'=>1,'splitsize'=>900000);
                if (!empty($options['limit'])) {
                    $op['where'] = '';
                    if (!empty($options['order'][$r['Name']])) {
                        $op['where'] .= 'ORDER BY '.$options['order'][$r['Name']];
                    }
                    $op['where'] =' LIMIT '.$options['limit'];
                }

                if (!empty($options['copy_to'])) {
                    $op['copy_to'] = $options['copy_to'];
                }

                if (!empty($options['estimate'])) {
                    echo "dumping $r[Name]: $r[Rows] rows, $r[Data_length] total bytes\n";
                    $total_rows += $r['Rows'];
                    $total_size += $r['Data_length'];
                }
                else {
                    $this->getTableContents($r['Name'], $op);
                }
            }

            if (empty($options['estimate'])) {
                echo "\n\n\n";
            }
        }

        if (!empty($options['estimate'])) {
            echo "Total: $total_rows rows, $total_size total bytes\n";
            echo '</pre>';
            return false;
        }

        $this->getSqlDumpQueryEcho("DELIMITER ###\n\n", $options);
        $this->query("SHOW TRIGGERS");
        while ($r = $this->_fetchAssoc()) {
            $this->getSqlDumpQueryEcho("DROP TRIGGER IF EXISTS `$r[Trigger]`;\n###\n", $options);
            $this->getSqlDumpQueryEcho("CREATE TRIGGER `$r[Trigger]` $r[Timing] $r[Event] ON `$r[Table]` FOR EACH ROW\n", $options);
            $this->getSqlDumpQueryEcho("$r[Statement];", $options);
            $this->getSqlDumpQueryEcho("###\n", $options);
        }
        $this->getSqlDumpQueryEcho("DELIMITER ;\n\n", $options);
        exit();
    }

    function getTableContents($table = '', $options = array()) {
        if (!$table) {
            return false;
        }

        if (!empty($options['file'])) {
            $options['notextarea'] = true;
            $options['splitsize'] = 900000;
            ob_end_clean();
            header('Content-Type: application/force-download');
            header('Content-Disposition: attachment; filename="dump_'.date('Y-m-d').'_'.
                $this->db_config['database'].'.'.
                $table.'_'.$this->db_config['hostname'].'.sql"');
            $this->query("SET NAMES UTF8");
            echo "SET NAMES UTF8;\n";
        }

        $select = "SELECT " . (isset($options['select']) ? $options['select'] : '*') . " FROM $table " . (isset($options['where']) ? $options['where'] : '');
        if (!empty($options['raw_select'])) {
            $select = $options['raw_select'];
        }

        $this->query($select);

        $qh = '';
        $q = '';
        while ($r = $this->_fetchAssoc())
        {
            if (!$qh) {
                $qh = "INSERT " . (empty($options['ignore']) ? '' : 'IGNORE ') . "INTO `$table` (`" . implode('`,`', array_keys($r)) . "`) VALUES \n";
                $q = $qh;
            }
            if (isset($options['skip'])) foreach ($options['skip'] as $f) if (isset($r[$f])) unset($r[$f]);
            if (isset($options['fields'])) foreach ($r as $f => $v) if (!in_array($f, $options['fields'])) unset($r[$f]);
            if (isset($options['change'])) foreach ($r as $f => $v) if (isset($options['change'][$f])) $r[$f] = $options['change'][$f];

            foreach ($r as $k => $v) {
                if (is_null($v)) $r[$k] = 'NULL';
                else $r[$k] = "'" . $this->_escape($v) . "'";
            }
            $dq = "(" . implode(",", $r) . "),\n";

            if (!empty($options['splitsize']) && (strlen($q) + strlen($dq) >= $options['splitsize'])) {
                $q = substr($q, 0, -2) . ';';
                if (empty($options['notextarea'])) {
                    echo '<text', 'area style="width:100%;height:100px">';
                }

                if (!empty($options['copy_to'])) {
                    $this->getSqlDumpQueryEcho($q, $options);
                }
                else {
                    if (!empty($options['base64'])) {
                        echo base64_encode($q);
                    }
                    else echo $q;
                }

                if (empty($options['notextarea'])) {
                    echo '</text', 'area>';
                }
                $q = $qh . $dq;
            }
            else {
                $q .= $dq;
            }

        }

        $q = $q ? substr($q, 0, -2) . ';' : $q;
        if (empty($options['notextarea'])) {
            echo '<text', 'area style="width:100%;height:100px">';
        }

        if (!empty($options['copy_to'])) {
            $this->getSqlDumpQueryEcho($q, $options);
        }
        else {
            if (!empty($options['base64'])) {
                echo base64_encode($q);
            }
            else echo $q;
        }

        if (!empty($options['file'])) {
            exit();
        }

        if (empty($options['notextarea'])) {
            echo '</text', 'area>';
        }
        return true;
    }
}
