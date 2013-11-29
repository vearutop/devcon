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



require_once 'db_driver.php';
require_once 'db_driver_mysql.php';
require_once 'db_driver_devcon_proxy.php';
require_once 'db_driver_pgsql.php';





