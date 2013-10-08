<?php
error_reporting(2047);
register_shutdown_function(function()
{
    $sProcess   = '/usr/bin/php '.join(' ', $_SERVER['argv']).' > /dev/null &';
    system($sProcess);
});
define('APPLICATION_PATH', realpath(dirname(__FILE__)));
spl_autoload_register(function ($class) 
{
    return require_once(str_replace('_', '/', $class) . '.php');
});


$server = new Cache_Server('127.0.0.1', '23540');
$server->runServer();