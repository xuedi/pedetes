<?php
namespace Pedetes;

use \PDO;
use \Mobile_Detect;

class model {

    /** @var database $db */
	var $ctn;

    /** @var database $db */
	var $db;

    /** @var session $mem */
	var $session;

    /** @var pebug $pebug */
	var $pebug;

    /** @var request $request */
    var $request;

    /** @var cache $cache */
    var $cache;

    var $data;

	function __construct($ctn) {
        $this->pebug = $ctn['pebug'];
        $this->pebug->log( "model::__construct()" );

		$this->ctn = $ctn;
		$this->db = $ctn['db'];
		$this->request = $ctn['request'];
		$this->session = $ctn['session'];
        $this->cache = $ctn['cache'];
	}


	public function splitSearchTerms() {
		//
	}

	// basic load an object return, on demand, not on event/location
	public function loadModel($name) {
		$file = $this->ctn['pathApp'];
		$file .= $this->ctn['config']['path']['model'];
		$file .= $name.'_model.php';
		if(file_exists($file)) {
			require_once($file);
			$model = "\Pedetes\\{$name}_model";
			return new $model($this->ctn);
		} else $this->pebug->error("model::loadModel($name): File does not exist! [$file]");
	}

	// basic load an object return, on demand, not on event/location
	public function loadCoreModel($name) {
		$file = $this->ctn['pathLib'];
		$file .= "app/core/";
		$file .= 'core_'.$name.'_model.php';
		if(file_exists($file)) {
			require_once($file);
			$model = "\Pedetes\\core\\core_{$name}_model";
			return new $model($this->ctn);
		} else $this->pebug->error("model::loadCoreModel($name): File does not exist! [$file]");
	}

	// collected usefull mini methods
	public function _isGuid($guid) {
		if(preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/', $guid)) {
			return true;
		} else {
			return false;
		}
	}
	public function _stripGuid($guid) {
		return str_replace(array('{','}'), array('',''), $guid);
	}
	public function get($name) {
		return $this->data[$name];
	}
	public function set($name, $value) {
		$this->data[$name] = $value;
	}
	public function getAll() {
		return $this->data;
	}
	public function isMobile() {
		$detect = new Mobile_Detect;
		return $detect->isMobile();
	}
}