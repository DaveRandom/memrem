<?php
error_reporting(2047);
define('APPLICATION_PATH', realpath(dirname(__FILE__)));
spl_autoload_register(function ($class) 
{
    return require_once(str_replace('_', '/', $class) . '.php');
});


$cache = new Cache_Client('127.0.0.1', '23540');
var_dump($cache->setValue('test', array(1,'foo', false)));
var_dump($cache->getValue('test'));


var_dump($cache->unsetValue('test'));
var_dump($cache->getValue('test'));