<?php


// get the locations right
$lib = realpath(dirname(__FILE__));
$app = realpath(dirname($_SERVER["SCRIPT_FILENAME"]).'/..');


// autoload
define('startTime', microtime(true));
require $lib.'/app/globals.php';
require $lib.'/vendor/autoload.php';
$pebug = \Pedetes\pebug::Instance();
$pebug->init(startTime);


// create injection container
$ctn = new Pimple\Container();


// injected paths
$ctn["pathLib"] = $lib . '/';
$ctn["pathApp"] = $app . '/';


// injected helper
$ctn['session'] = function ($ctn) { return new Pedetes\session($ctn); };
$ctn['db']      = function ($ctn) { return new Pedetes\database($ctn); };
$ctn['request'] = function ($ctn) { return new Pedetes\request($ctn); };
$ctn['cache']   = function ($ctn) { return new Pedetes\cache($ctn); };


// start up the app
$app = new Pedetes\bootstrap($ctn);
$app->init();
