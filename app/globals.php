<?php

// some basic php settings & error/dbg functions
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("html_errors", 1); 
ini_set("error_prepend_string", "<pre style='color: #333; font-face:monospace; font-size:12pt;'>"); 
ini_set("error_append_string ", "</pre>"); 
function dbg($data, $die=false) {
	$backtrace = debug_backtrace();
	echo "<pre style='border: 2px solid black; margin: 10px; padding: 7px; '>";
	echo "<b>dbg</b> called from '".$backtrace[0]['file']."' line: ".$backtrace[0]['line']."<br>";
	echo "<pre style='background-color: #eee; padding: 7px; '>".print_r($data,true)."</pre>";
	echo "</pre>";
	if($die) die();
}