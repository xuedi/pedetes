<?php

// some basic php settings & error/dbg functions
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("html_errors", 1);
ini_set("error_prepend_string", "<pre style='color: #333; font-face:monospace; font-size:12pt;'>");
ini_set("error_append_string ", "</pre>");



// locations & time
$lib = realpath(dirname(__FILE__));
$app = realpath(dirname($_SERVER["SCRIPT_FILENAME"]).'/..');
$startTime = microtime(true);


// autoload
require $lib.'/vendor/autoload.php';


// create injection container
$ctn = new Pimple\Container();
$ctn['startTime'] = $startTime;


// injected paths
$ctn["pathLib"] = $lib.'/';
$ctn["pathApp"] = $app.'/';
$ctn["appHash"] = md5($ctn['pathApp']);


// injected helper
$ctn['pebug']   = function ($ctn) { return new Pedetes\pebug($ctn); };
$ctn['session'] = function ($ctn) { return new Pedetes\session($ctn); };
$ctn['db']      = function ($ctn) { return new Pedetes\database($ctn); };
$ctn['request'] = function ($ctn) { return new Pedetes\request($ctn); };
$ctn['cache']   = function ($ctn) { return new Pedetes\cache($ctn); };


// start up the app
$app = new Pedetes\bootstrap($ctn);
$app->init();
