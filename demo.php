<?php
error_reporting(2047);
define('APPLICATION_PATH', realpath(dirname(__FILE__)));
spl_autoload_register(function ($sClass) 
{
    return require_once(str_replace('_', '/', $sClass) . '.php');
});


$rCache = Cache_Client::getInstance()->setServerAddress('127.0.0.1', '23540');
var_dump($rCache->setKey('test', array(1,'foo', false)));
var_dump($rCache->getKey('test'));

$rCacheIns = Cache_Client::getInstance();

var_dump($rCacheIns->unsetKey('test'));
var_dump($rCacheIns->getKey('test'));