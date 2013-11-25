<?php

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

