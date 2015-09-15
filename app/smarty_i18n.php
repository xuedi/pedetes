<?php
namespace Pedetes;

use \Smarty;

class smarty_i18n extends Smarty {
	
	var $ctn;
	var $mem;
	var $pebug;
	var $translation_dir = "";

	function __construct($ctn) {
		parent::__construct();  // smarty parents business

		// get pebug
		$this->pebug = pebug::Instance();
		$this->pebug->log("smarty_i18n::__construct()");

		// ctn itself
		$this->ctn = $ctn;

		// session module
		$this->mem = $ctn['session'];

		// smarty basic setup
		$base = $this->ctn['pathApp'];
		$temp = $this->ctn['config']['path']['temp'];
		$view = $this->ctn['config']['path']['view'];
		$this->setTemplateDir($base.$view);
		$this->setCompileDir($base.$temp.'smarty_compiled');
		$this->setConfigDir($base.$temp.'smarty_config');
		$this->setCacheDir($base.$temp.'smarty_cache');

		// smarty internal settings
		$this->caching = Smarty::CACHING_OFF;
//		$this->force_compile = false;

	}


	
	function loadMLFilter($file, $caching = true) {
		$retVal = "";
		$this->pebug->log( "smarty_i18n::loadMLFilter()" );

		// check in language is set
		$language = $this->mem->get('language');
		if($language!="") {

			// check if language cache exists
			$base = $this->ctn['pathApp'];
			$temp = $this->ctn['config']['path']['temp'];
			$cache_file = $base.$temp."cache.serialize.txt";;
			if(file_exists($cache_file)) {

				// fetch raw data
				$this->pebug->timer_start("render");
				$tpl = $this->fetch($file); // sencond parameter is caching_id
				$this->pebug->timer_stop("render");

				// do all the i18n
				$this->pebug->timer_start("i18n");
				$filter = unserialize(file_get_contents($cache_file)); //todo: load into APC and cache
				if(isset($filter[$language]['key'])&&isset($filter[$language]['value'])) {
					$retVal = str_replace($filter[$language]['key'], $filter[$language]['value'], $tpl);
				} else $retVal = $tpl;
				$this->pebug->timer_stop("i18n");

			} else $this->pebug->error( "smarty_i18n::loadMLFilter($file): File does not exist [$cache_file]" );
		} else $this->pebug->error( "smarty_i18n::loadMLFilter($file): Language is not set!" );

		// do caching
		if($this->ctn['config']['caching'] && $caching) {
			$upid = $this->ctn['upid'];
			$cache = $this->ctn['cache'];
			if($cache->exist($upid)) {
				$cache->delete($upid);
			}
			$data = serialize($retVal);
			$time = $this->ctn['config']['caching'];
			$cache->set($upid, $data, $time);
		}

		return $retVal;
	}


	
	function displayML($file, $caching) {
		echo $this->loadMLFilter($file, $caching);
	}


	
	function fetchML($file, $caching) {
		return $this->loadMLFilter($file, $caching);
	}
		
}

?> 