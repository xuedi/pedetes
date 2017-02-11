<?php
namespace Pedetes;
// http://php.net/manual/en/filter.filters.sanitize.php
// http://php.net/manual/en/filter.filters.validate.php

use HTMLPurifier;

class request {

    private $ctn;
    /** @var  pebug $pebug */
    private $pebug;

    private $strict = null;

    function __construct($ctn) {
        $this->pebug = $ctn['pebug'];
        $this->pebug->log("request::__construct()");

        $this->ctn = $ctn;
        $this->strict = false;
    }

    /**
     * Inject varaibles for unittesting and so on
     * @param $name string Name of the parameter
     * @param $value null The value to be set as a parameter
     */
    public function setMock($name, $value=null) {
        $_REQUEST[$name] = $value;
    }

    /**
     * Set mode to strict, if error occours, system stops with and exception
     * If not in strict mode system will give back the default value
     */
    public function setStrict() {
        $this->strict = true;
    }

    /**
     * @param $name string The name of the requested paraameter
     * @param $defaut string The default value in case of non strict mode
     * @return int The requested parameter as a integer
     */
    public function getNumber($name, $defaut=null) : int {
        $value = $_REQUEST[$name] ?? null;
        if(!is_numeric($value)) {
            if($this->strict) {
                $this->pebug->error("request::getNumber($name): The given parameter is not an integer");
            } else {
                return $defaut;
            }
        }
        return $value;
    }

    /**
     * @param $name string The name of the requested paraameter
     * @param $defaut string The default value in case of non strict mode
     * @return float The requested parameter as a float
     */
    public function getFloat($name, $defaut=null) : float {
        $value = $_REQUEST[$name] ?? null;
        if(!is_float($value)) {
            if($this->strict) {
                $this->pebug->error("request::getFloat($name): The given parameter is not an float");
            } else {
                return $defaut;
            }
        }
        return $value;
    }

    /**
     * @param $name string The name of the requested paraameter
     * @param $defaut string The default value in case of non strict mode
     * @return string The requested parameter as a string
     */
    public function getText($name, $defaut=null) : string {
        $value = $_REQUEST[$name] ?? null;
        if(!preg_match('/[0-9-a-z-A-Z]/',$value)) { // TODO: unit test
            if($this->strict) {
                $this->pebug->error("request::getText($name): The given parameter is not a plain text");
            } else {
                return $defaut;
            }
        }
        return $value;
    }

    /**
     * @param $name string The name of the requested paraameter
     * @param $defaut string The default value in case of non strict mode
     * @return string The requested parameter as a string (validated email)
     */
    public function getEmail($name, $defaut=null) : string {
        $value = $_REQUEST[$name] ?? null;
        if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            if($this->strict) {
                $this->pebug->error("request::getEmail($name): The given parameter is not a plain text");
            } else {
                return $defaut;
            }
        }
        return $value;
    }

    /**
     * @param $name string The name of the requested paraameter
     * @param array $options A list of parameters that can ge requested
     * @param $defaut string The default value in case of non strict mode
     * @return string One of the allowed requested options or the default value
     */
    public function getArray($name, $options=[], $defaut=nul) : string {
        $value = $_REQUEST[$name] ?? null;
        if(!is_array($options) || empty($options)) {
            if($this->strict) {
                $this->pebug->error("request::getArray($name): The given options are not valid");
            } else {
                return $defaut;
            }
        }
        if(!in_array($value,$options)) {
            if($this->strict) {
                $this->pebug->error("request::getArray($name): The given parameter is not in the allowed array options");
            } else {
                return $defaut;
            }
        }
        return $value;
    }

    /**
     * @param $name string The name of the requested paraameter
     * @param $defaut string The default value in case of non strict mode
     * @return string The requested parameter as a string (parsed by XSS checker)
     */
    public function getFree($name, $defaut=null) : string {
        $value = $_REQUEST[$name] ?? null;
        $purifier = new HTMLPurifier();
        $value = $purifier->purify($value);
        if(empty($value)) { //TODO: intensive unit testing
            if($this->strict) {
                return $defaut;
            }
        }
        return $value;
    }

}