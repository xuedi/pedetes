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
        $this->pebug->log("controller::loadModel($name)");
        $path = $this->ctn['config']['path']['base'];
        $path .= $this->ctn['config']['path']['model'];
        $path .= $name.'_model.php';
        if(file_exists($path)) {
            require_once($path);
            $model = '\Pedetes\\'.$name . '_model';
            return new $model($this->ctn);
        } else $this->pebug->error("controller::loadModel($name): File does not exist!");
    }
	

}