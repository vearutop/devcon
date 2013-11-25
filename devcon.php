<?php

require_once 'src/config.php';
require_once 'src/auth.php';
require_once 'src/db_client.php';
require_once 'src/out_xls.php';
require_once 'src/facades.php';

if (isset($_REQUEST['custom'])) {
        $instances['custom'] = $_REQUEST['custom'];
    }

    $root_path = $_SERVER['DOCUMENT_ROOT'];
    define('BASEPATH', '');



set_time_limit(0);
header('Content-type: text/html; charset=UTF-8');

if (!isset($_POST['title'])) {
	$_POST['title'] = 'con';
}

ob_start();
?>
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
    print_r($tmp);
	$db['default'] = array(
        'driver'=>$tmp['scheme'],
        'hostname'=>$tmp['host'],
        'username'=>!empty($tmp['user']) ? $tmp['user'] : '',
        'password'=>!empty($tmp['pass']) ? $tmp['pass'] : '',
        'database'=>substr($tmp['path'],1)
    );
}

if (!empty($_REQUEST['skip_preout'])) {
    ob_end_clean();
}

// SQL
if ($db = db($_REQUEST['instance'])) {
	if (!empty($_POST['query'])) {
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

xlsReport::finalize();

if (!empty($_REQUEST['skip_preout'])) {
	exit();
}
?>


<?php



