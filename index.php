<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("html_errors", 1); 
ini_set("error_prepend_string", "<pre style='color: #333; font-face:monospace; font-size:12pt;'>"); 
ini_set("error_append_string ", "</pre>"); 


// get the locations right
$lib = realpath(dirname(__FILE__));
$app = realpath(dirname($_SERVER["SCRIPT_FILENAME"]).'/..');


// autoload
define('startTime', microtime(true));
require $lib.'/vendor/autoload.php';
$pebug = \Pedetes\pebug::Instance();
$pebug->init(startTime);


// create injection container
$ctn = new Pimple\Container();


// injected paths
$ctn["pathLib"] = $lib . '/';
$ctn["pathApp"] = $app . '/';


// injected helper
$ctn['session'] = $ctn->factory(function ($ctn) { return new Pedetes\session($ctn); });
$ctn['db']      = $ctn->factory(function ($ctn) { return new Pedetes\database($ctn); });
$ctn['request'] = $ctn->factory(function ($ctn) { return new Pedetes\request($ctn); });
$ctn['cache']   = $ctn->factory(function ($ctn) { return new Pedetes\cache($ctn); });


// start up the app
$app = new Pedetes\bootstrap($ctn);
$app->init();
