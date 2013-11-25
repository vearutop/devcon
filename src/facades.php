<?php


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

function xls_query($q, $title = 'report') {
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
