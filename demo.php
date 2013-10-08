<?php
error_reporting(2047);
define('APPLICATION_PATH', realpath(dirname(__FILE__)));
spl_autoload_register(function ($sClass) 
{
    return require_once(str_replace('_', '/', $sClass) . '.php');
});


$rCache = new Cache_Client('127.0.0.1', '23540');
var_dump($rCache->setKey('test', array(1,'foo', false)));
var_dump($rCache->getKey('test'));


var_dump($rCache->unsetKey('test'));
var_dump($rCache->getKey('test'));