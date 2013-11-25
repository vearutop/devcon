<?php

date_default_timezone_set('Asia/Novosibirsk');

$salt = 'setme!';
// login => md5(login . salt . password)
$userPasswords = array(
    'user'  => 'password_hash',
);

$instances = array(
    'default' => 'mysql://localhost/',
);


$dev_sign = '';
$ip_white_list = array(
    '*',
);



