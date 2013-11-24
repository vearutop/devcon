<?php

date_default_timezone_set('Asia/Novosibirsk');

        $salt = 'setme!';
// login => md5(login . salt . password)
        $userPasswords = array(
            'user'  => 'password_hash',
        );

        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="devcon"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Cancelled';
            exit;
        } else {
            $fatal = function($message){
                header('WWW-Authenticate: Basic realm="devcon"');
                header('HTTP/1.0 401 Unauthorized');
                die($message);
            };

            if (!array_key_exists($_SERVER['PHP_AUTH_USER'], $userPasswords)) {
                $fatal('Unknown user');
            } elseif ($userPasswords[$_SERVER['PHP_AUTH_USER']]
                != md5($_SERVER['PHP_AUTH_USER'] . $salt . $_SERVER['PHP_AUTH_PW'])) {
                $fatal('Bad password');
            }

            if (isset($_GET['logout'])) {
                $fatal('Logout');
            }
        }



// config section


    $instances = array(
		'default' => 'mysql://localhost/',
    );

    if (isset($_REQUEST['custom'])) {
        $instances['custom'] = $_REQUEST['custom'];
    }

    $root_path = $_SERVER['DOCUMENT_ROOT'];
    define('BASEPATH', '');



	$dev_sign = '';
    $ip_white_list = array(
		'*',
    );


    // eo config



// check auth
    $forbidden = false;
    if ($dev_sign && strpos($_SERVER['HTTP_USER_AGENT'], $dev_sign) === false && empty($_REQUEST[$dev_sign])) {
        $forbidden = true;
    }
    else {
        $ip_ok = false;
        foreach ($ip_white_list as $ip) {
            if (
                ($_SERVER['REMOTE_ADDR'] == $ip) ||
                ('*' == substr($ip,-1) && substr($_SERVER['REMOTE_ADDR'],0,strlen($ip)-1) == substr($ip,0,-1))
                ) {
                $ip_ok = true;
                break;
            }
        }
        if (!$ip_ok) {
            $forbidden = true;
        }
    }

    if ($forbidden) {
        header('HTTP/1.1 403 Forbidden');
        die($_SERVER['REMOTE_ADDR'].' );');
    }


//http://account.forex4you.vea.dev/db.php?eval=get_table_contents(%27partner_old%27,array(%27where%27=%3E%27limit%2010,10%27,%27notextarea%27=%3E1));&skip_preout=1

set_time_limit(0);
header('Content-type: text/html; charset=UTF-8');

if (!isset($_POST['title'])) {
	$_POST['title'] = 'con';
}

ob_start();
?>
<html>
<head>
	<title><?=$_POST['title']?></title>

<link href="/src/style.css" type="text/css" />
<script src="src/js/sortable.js"></script>

</head>
<body>
<?php


if (isset($_REQUEST['custom'])) {
	$_REQUEST['instance'] = 'custom';
}

if (empty($_REQUEST['instance'])) {
	$_REQUEST['instance'] = 'default';
}

include 'src/form.php';


if (isset($_REQUEST['instance']) && isset($instances[$_REQUEST['instance']])) {
	$tmp = parse_url($instances[$_REQUEST['instance']]);
	$db['default'] = array('driver'=>$tmp['scheme'] ,'hostname'=>$tmp['host'],'username'=>$tmp['user'],'password'=>!empty($tmp['pass']) ? $tmp['pass'] : '','database'=>substr($tmp['path'],1));
}

if (!empty($_REQUEST['skip_preout'])) {
    ob_end_clean();
}

// SQL
if ($db = db($_REQUEST['instance'])) {
	if (isset($_POST['query']) && $_POST['query']) {
		if (strpos($_POST['query'], "###") !== false) {
			$queries = explode("###", $_POST['query']);
		}
		else {
			$queries = array(&$_POST['query']);
		}

		foreach ($queries as &$q) {
			$db->query($q)->show(empty($_POST['format']) ? 'html' : 'serialize');
		}
	}
}
else echo("Could not connect");


// EVAL
if (isset($_REQUEST['eval']) && $_REQUEST['eval']) {
	echo "<h4>Eval</h4>";
	eval($_REQUEST['eval']);
}


if (!empty($_POST['mq_query']) && isset($config['mt4'][$_POST['mq_server']])) {
	echo '<h4>MT Query result</h4><pre>';
	print_r(MQ_Query($_POST['mq_query'], $config['mt4'][$_POST['mq_server']]['host'], $config['mt4'][$_POST['mq_server']]['port']));
	echo '</pre>';
}

xlsReport::finalize();

if (!empty($_REQUEST['skip_preout'])) {
	exit();
}
?>
<a href="#" onclick="this.nextSibling.style.display='';this.style.display='none'">$_SERVER</a><pre style="display:none">
<?php print_r($_SERVER);?>
</pre>

</body>
</html>
<?php


/**
 * @param string $instance
 * @return bool | db_driver
 */
function db($instance = 'default') {
    global $instances;
    static $db = array();

    if (!isset($instances[$instance])) {
        $instances[$instance] = $instance;
    }

    $dsn = $instances[$instance];

    if (!isset($db[$instance])) {
        $pdsn = parse_url($dsn);
        $db_class = $pdsn['scheme'].'_driver';
        $db[$instance] = new $db_class($dsn);
    }
    return $db[$instance];
}



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
            'username' => $dsn['user'],
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


    /**
     * @param $search
     * @return void
     */
    function fullSearch($search) {
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
            return false;
        }
        $this->query("USE $db[database]");
        $this->query("SET NAMES UTF8");
    }
    public function _escape($s) {
        return mysql_real_escape_string($s, $this->link);
    }

}


class devCon_driver extends db_driver {
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

class xlsReport {
    static private $book = null;
    static private $sheet = null;
    static private $sheet_headers = array();
    static private $sheet_line = 0;
    /**
     * Init xls output
     * @static
     * @return void
     */
    static private function init() {
        if (is_null(self::$book)) {
            // Loading library
            set_include_path(get_include_path() . PATH_SEPARATOR . './_system/application/libraries/PEAR/');
            require_once 'Spreadsheet/Excel/Writer.php';
            // Creating a workbook
            self::$book = new Spreadsheet_Excel_Writer();
            self::$book->setTempDir(sys_get_temp_dir());

            // sending HTTP headers
            self::$book->send('report.xls');
            self::$book->setVersion(8);

            ob_end_clean();
        }
    }
    /**
     * Add new sheet to xls output (fill with data optionally)
     * @static
     * @param string $title
     * @param null $data
     * @return void
     */
    static public function addSheet($title = 'default', $data = null) {
        self::init();
        self::$sheet =& self::$book->addWorksheet($title);
        // Encoding
        self::$sheet->setInputEncoding('UTF-8');
        self::$sheet_headers = array();
        self::$sheet_line = 0;

        if (is_array($data)) {
            foreach ($data as $r) {
                self::addRow($r);
            }
        }
    }
    /**
     * Add row to current sheet
     * @static
     * @param $r
     * @return void
     */
    static public function addRow($r) {
        if (!self::$sheet) {
            self::addSheet();
        }

        if (!self::$sheet_headers) {
            $i = 0;
            foreach ($r as $k => $v) {
                self::$sheet_headers[$k] = $i;
                self::$sheet->write(self::$sheet_line, $i++, $k);
                self::$sheet->setColumn(self::$sheet_line, $i, 15);
            }
            ++self::$sheet_line;
        }
        foreach (self::$sheet_headers as $k => $i) {
            self::$sheet->write(self::$sheet_line, $i, !isset($r[$k]) ? '' : $r[$k]);
        }
        ++self::$sheet_line;
    }
    /**
     * Finalizes xls output and exits in case of xls started
     * @static
     * @return void
     */
    static public function finalize() {
        if (self::$book) {
            // Let's send the file
            self::$book->close();
            exit();
        }
    }
}




/**
 * Query to MT4
 *
 * @param $query
 * @param $host
 * @param $port
 * @return string
 */
function MQ_Query($query, $host, $port) {

	$result = 'error';

	/* open socket */
	$ptr = @fsockopen($host, $port, $errno, $errstr, 10);

	/* check connection */
	if ($ptr) {
		/* send request */
		if (fwrite($ptr, "W$query\nQUIT\n") != FALSE) {
			$result = '';
			/* receive answer */
			while (!feof($ptr)) {
				$line = fgets($ptr, 128);
				if ($line == "end\r\n") {
					break;
				}
				$result .= $line;
			}
		}
		else {
			$result = $errstr . ' (' . $errno . ')';
		}
		fclose($ptr);
	}
	return $result;
}


// shortcuts

function help() {
	echo "<code><b>functions available: <br />";
    echo "<b>switch_db('replica');</b> switch current database to another instance,'mysql://root@localhost/test' could also be used<br />";
	echo "<b>echo_query('SELECT * FROM table');</b> perform mysql query and print result in html table<br />";
	echo "<b>jira_query('SELECT * FROM table');</b> perform mysql query and print result in jira table format<br />";
	echo "<b>xls_query('SELECT * FROM table','sheet_name');</b> perform mysql query and store result as xls file, each function call creates new worksheet in report<br />";
	echo "<b>get_sql_dump([options_array]);</b> export db content, run without parameters for help<br />";
	echo "<b>get_table_contents([options_array]);</b> export table content, run without parameters for help<br />";
	echo "<b>MQ_Query(query, host, port);</b> perform MT4 query<br />";
	//echo "<b>full_search(\$search);</b> perform LIKE '%\$search%' through all fields of all tables of current db, BEWARE O_O MAY BURN YOUR SOUL<br />";
	echo "</code>";
}

function fetchTable($table, $page_size = 1000, $db_url = 'https://account-trunk.forex4you.org/db.php') {
    ob_end_flush();
    echo "fetching $table (page = $page_size) from $db_url...<br />\n";
    flush();
    $offset = 0;
    $iteration = 0;
    $max_iterations = 500;
    while ($q = file_get_contents($url = $db_url . '?eval=get_table_contents%28%27' . $table . '%27,array%28%27where%27=%3E%27limit%20' . $offset . ',' . $page_size . '%27,%27notextarea%27=%3E1%29%29;&skip_preout=1&vea@dev')) {
        $iteration++;
        echo $url . "<br />\n";
        echo "performing query $iteration...<br />\n";
        flush();
        echo_query($q);
        if ($iteration > $max_iterations) {
            return false;
        }
        $offset += $page_size;
    }
}

function echo_query($q) {
    global $db;
    $db->query($q)->show();
}

function jira_query($q) {
    global $db;
    $db->query($q)->show('jira');
}

function xls_query($q, $title) {
    global $db;
    $db->query($q)->xls($title);
}

function switch_db($instance) {
    global $db;
    $db = db($instance);
    return $db;
}

function phpdoc($table) {
	global $db;
    echo "<pre>/**\n";
	foreach ($db->query("DESC $table")->all() as $r) {
        echo ' * @property $' . $r['Field'] . "\n";
    }
    echo " **/\n</pre>";

}


function get_sql_dump($options = false) {
    if (false === $options) {
            echo "<code><b>get_sql_dump(\$options_array)</b><br />options may be: <br />";
            echo "<b>'tables'</b> array of tables to dump, if set other tables will be skipped, default not set<br />";
            echo "<b>'tables_like'</b> string to match table name (ex. label%), if set other tables will be skipped, default not set<br />";
            echo "<b>'skip_tables'</b> array of tables to skip, default not set<br />";
            echo "<b>'skip_content'</b> array of tables to skip content, CREATE and DROP will exist, default not set<br />";
            echo "<b>'skip_content_maxrows'</b> if is set and table has more or eq rows, content will be skipped, default not set<br />";
            echo "<b>'skip_content_maxdata'</b> if is set and table has more or eq data length, content will be skipped, default not set<br />";
            echo "<b>'limit'</b> max number of rows to dump from table, default not set<br />";
            echo "<b>'order'</b> key-value array of table:order_condition pairs for limit, default not set <br />";
            echo "<b>'estimate'</b> estimate size and row count only without dumping, default not set <br />";
            echo "<b>'copy_to'</b> execute dump in another database (ex. 'mysql://user:pass@copyhost/copybase'), default not set <br />";
            echo "<br />example: get_sql_dump(array('limit'=>20000,'skip_content_maxrows'=>50000));</code>";
        return false;
    }

    global $db;
    $db->getSqlDump($options);
}

function get_table_contents($table = '', $options = array()) {
    if (!$table) {
        echo "<code><b>get_table_contents('table_name', \$options_array)</b><br />options may be: <br />";
        echo "<b>'select'</b> default '*'<br />";
        echo "<b>'where'</b> where section with 'WHERE' word, default empty, example 'WHERE id>10'<br />";
        echo "<b>'skip'</b> fields to drop from resulting query, default array(), example array('insert_date','meta_info')<br />";
        echo "<b>'fields'</b> fields to require in resulting query, if not empty others will be omitted, default array(), example array('id','name')<br />";
        echo "<b>'change'</b> change data array, default array(), example array('office_id'=>'33', 'name'=>'Alternative Name')<br />";
        echo "<b>'splitsize'</b> split resulting query by size, default 0<br />";
        echo "<b>'ignore'</b> use INSERT IGNORE, default false<br />";
        echo "<b>'update'</b> use INSERT .. ON DUPLICATE KEY UPDATE $1, default ''<br />";
        echo "<b>'notextarea'</b> do not put query to textarea, default null<br />";
        echo "<b>'file'</b> save query as file, default null<br />";
        echo "<br />example: get_table_contents('investbag_users',array('where'=>\"WHERE currency='USD'\",'skip'=>array('is_dealer'),'splitsize'=>900000));</code>";
        return false;
    }

    global $db;
    $db->getTableContents($table, $options);
}

