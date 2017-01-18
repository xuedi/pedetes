<?php
namespace Pedetes;

use Pedetes\core\core_i18n_model;
use Smarty;
use Smarty_Internal_Template;

class smarty_i18n extends Smarty {
	
	var $ctn;
    var $mem;
    var $cache;
	var $pebug;
	var $translation_dir = "";

	function __construct($ctn) {
		parent::__construct();  // smarty parents business
        $this->pebug = $ctn['pebug'];
		$this->pebug->log("smarty_i18n::__construct()");

        // inject some stuff
		$this->ctn = $ctn;
		$this->mem = $ctn['session'];
        $this->cache = $ctn['cache'];

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
	}

	function loadMLFilter($file, $caching = true) {

		// register language filter (if pre or post, will be cached)
		$this->registerFilter('output', [$this, 'translationFilter']);

		// fetch raw data
		$this->pebug->timer_start("render");
		$retVal = $this->fetch($file);
		$this->pebug->timer_stop("render");

		return $retVal;
	}

	function displayML($file, $caching) {
		echo $this->loadMLFilter($file, $caching);
	}

	function fetchML($file, $caching) {
		return $this->loadMLFilter($file, $caching);
	}

    function translationFilter($tpl_output, Smarty_Internal_Template $template) {
        $language = $this->mem->get('language');
        if($language!="") {

			// get translations
			$this->pebug->timer_start("i18n");
			if(!$this->cache->exist('translations')) {
				$i18n = new core_i18n_model($this->ctn);
				$filter = $i18n->getCache();
				$this->cache->setIfValue('translations', $filter);
			} else $filter = $this->cache->get('translations');

			// apply translations
            if(isset($filter[$language]['key'])&&isset($filter[$language]['value'])) {
                $tpl_output = str_replace($filter[$language]['key'], $filter[$language]['value'], $tpl_output);
            }

			//$tpl_output = 'kuzt';
			$this->pebug->timer_stop("i18n");
        } else $this->pebug->error( "smarty_i18n::loadMLFilter($file): Language is not set!" );
        return $tpl_output;
    }

}

?> 