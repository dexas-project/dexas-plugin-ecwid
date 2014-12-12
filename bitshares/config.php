<?php

// bitshares settings

$accountName = 'opencartdemo';
$rpcUser = 'user';
$rpcPass = 'pass';
$rpcPort = 1234;
$demoMode = TRUE;
//payment method settings
$login = 'ecwidbitshares'; // see README
$hashValue = 'bitshares demo'; // see README

// add trailing slash to url
$bitsharesURL = preg_replace('#([^\/])$#', '\1/', $bitsharesURL);

?>