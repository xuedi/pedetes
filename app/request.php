<?php
namespace Pedetes;
// http://php.net/manual/en/filter.filters.sanitize.php
// http://php.net/manual/en/filter.filters.validate.php
class request {

	var $ctn;
	var $pebug;

	function __construct($ctn) {

        // get pebug
        $this->pebug = pebug::Instance();
        $this->pebug->log("database::__construct()");

		// container itself
		$this->ctn = $ctn;

	}


	public function getPath() {
		return $_SERVER["REQUEST_URI"];
	}


	public function get($name, $type, $default = NULL, $allowEmpty = NULL) {

		if(isset($_REQUEST[$name])) {

			// verfy data
			return $this->verify($_REQUEST[$name], $type, $default, $allowEmpty);

		} else {
			// no value set
			if($type=='ARRAY') {
				if(isset($default[0])) return $default[0]; // try first array element
				else return null;
			} else return $default;
		}
	}


	public function getData() {
		$retVal = array();
		foreach($_REQUEST as $key => $value) {
			if(substr($key, 0, 5)=="data_") {
				$cleanKey = substr($key, 5, strlen($key));
				$retVal[$cleanKey] = $value;
			}
		}
		return $retVal;
	}


	public function verify($value, $type, $default = NULL, $allowEmpty = NULL) {

		// check on empty
		if($value=='') {
			if($allowEmpty) return "";
			else $this->pebug->error("request::get($req): The requested value is empty!");
		}

		switch($type) {

			case "FREE":
				return $this->getFree($value, $default);
			break;

			case "TEXT":
				return $this->getText($value, $default);
			break;

			case "PLAINTEXT":
				return $this->getPlainText($value, $default);
			break;

			case "EMAIL":
				return $this->getEmail($value, $default);
			break;

			case "DATETIME":
			    // Check the dte and the format
			    return $value;
			break;

			case "NUMBER":
				return $this->getNumber($value, $default);
			break;

			case "ARRAY":
				return $this->getArray($value, $default, $allowEmpty);
			break;

			default:
				$this->pebug->error("request::get($value): No Valid type!");
			break;

		}
	}

	private function getPlainText($req, $default = NULL) {
		$verbose = $this->ctn['config']['debugging'];

		if(ctype_alnum($req)) return $req;
		elseif($verbose) $this->pebug->error("request::getPlainText($req): Invalid Character!");
		else return $default;
	}


	private function getText($req, $default = NULL) {
		$verbose = $this->ctn['config']['debugging'];

		// no single signs
		if(strpos($req, "'")!=0) {
			if($verbose) $this->pebug->error("request::getText($req): PlainText check: ''' not allowed!");
			else return $default;
		}
		if(strpos($req, "%")!=0) {
			if($verbose) $this->pebug->error("request::getText($req): PlainText check: '%' not allowed!");
			else return $default;
		}
		return $req;
	}

	// no restrictions
	private function getFree($req, $default = NULL) {
		return $req;
	}


	private function getEmail($req, $default = NULL) {
		$verbose = $this->ctn['config']['debugging'];

		//TODO database need to take care of ' for example its still valid here
		return filter_var($req, FILTER_SANITIZE_EMAIL);

//		if(filter_var($req, FILTER_VALIDATE_EMAIL)) return $req;
//		elseif($verbose) $this->debug->error("request::getEmail($req): Email not valid!");
//		else $default;
		
	}


	private function getNumber($req, $default = NULL) {
		$verbose = $this->ctn['config']['debugging'];
		if(!is_numeric($req)) {
			if($verbose) $this->pebug->error("request::getNumber($req): Not a number!");
			else return $default;
		}
		return $req;
	}


	private function getArray($req, $default = NULL, $allowEmpty=false) {
		$verbose = $this->ctn['config']['debugging'];

		// Check if $default is actually an array, if not, terminate
		if(!is_array($default)) 
			$this->pebug->error("request::getArray($req): Default is not array!");

		// loop and compare the values
		foreach($default as $value) {
			if( $value == $req ) return $req;
		}

		// empty is legal
		if($allowEmpty) return '';

		// if no hit trough an exeption
		$this->pebug->error("request::getArray($req): No Array hit!");
	}

	public function getLocation() {


	}
}