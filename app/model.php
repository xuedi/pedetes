<?php
namespace Pedetes;

use \PDO;

class model {

	var $ctn;
	var $db;
	var $mem;
	var $pebug;
	var $request;

	function __construct($ctn) {

		// get pebug
		$this->pebug = pebug::Instance();
		$this->pebug->log("model::__construct()");

		// container itself
		$this->ctn = $ctn;

		// database connector
		$this->db = $ctn['db'];

		// database connector
		$this->request = $ctn['request'];

		// session module
		$this->mem = $ctn['session'];

	}


	public function splitSearchTerms() {
		//
	}

	// basic load an object return, on demand, not on event/location
	public function loadModel($name) {
		$this->pebug->log("model::loadModel($name)");
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
		$this->pebug->log("model::loadModel($name)");
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

}