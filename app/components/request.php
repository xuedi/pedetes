<?php
namespace Pedetes;
// http://php.net/manual/en/filter.filters.sanitize.php
// http://php.net/manual/en/filter.filters.validate.php

use HTMLPurifier;

class request {

    private $pebug;
    private $strict = false;


    /**
     * request constructor.
     * @param pebug $pebug
     */
    function __construct(pebug $pebug) {
        $this->pebug = $pebug;
        $this->pebug->log("request::__construct()");
    }

    /**
     * Inject varaibles for unit testing and so on
     * @param string $name Name of the parameter
     * @param mixed $value null The value to be set as a parameter
     */
    public function setMock(string $name, mixed $value=null) {
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
     * @param string $name The name of the requested parameter
     * @param int $default The default value in case of non strict mode
     * @return int The requested parameter as a integer
     */
    public function getNumber(string $name, int $default=null) : int {
        $value = $_REQUEST[$name] ?? null;
        if(!is_numeric($value)) {
            if($this->strict) {
                $this->pebug->error("request::getNumber($name): The given parameter is not an integer");
            } else {
                return $default;
            }
        }
        return $value;
    }

    /**
     * @param string $name The name of the requested parameter
     * @param float $default The default value in case of non strict mode
     * @return float The requested parameter as a float
     */
    public function getFloat(string $name, float $default=0.0) : float {
        $value = $_REQUEST[$name] ?? null;
        if(!is_float($value)) {
            if($this->strict) {
                $this->pebug->error("request::getFloat($name): The given parameter is not an float");
            } else {
                return $default;
            }
        }
        return $value;
    }

    /**
     * @param string $name The name of the requested parameter
     * @param string $default The default value in case of non strict mode
     * @return string The requested parameter as a string
     */
    public function getText(string $name, string $default='') : string {
        $value = $_REQUEST[$name] ?? '';
        if(!preg_match('/[0-9-a-z-A-Z]/',$value)) { // TODO: unit test
            if($this->strict) {
                $this->pebug->error("request::getText($name): The given parameter is not a plain text");
            } else {
                return $default;
            }
        }
        return $value;
    }

    /**
     * @param string $name The name of the requested parameter
     * @param string $default The default value in case of non strict mode
     * @return string The requested parameter as a string (validated email)
     */
    public function getEmail(string $name, string $default='') : string {
        $value = $_REQUEST[$name] ?? '';
        if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            if($this->strict) {
                $this->pebug->error("request::getEmail($name): The given parameter is not a plain text");
            } else {
                return $default;
            }
        }
        return $value;
    }

    /**
     * @param string $name The name of the requested parameter
     * @param array $options A list of parameters that can ge requested
     * @param string $default The default value in case of non strict mode
     * @return string One of the allowed requested options or the default value
     */
    public function getArray(string $name, array $options=[], string $default='') : string {
        $value = $_REQUEST[$name] ?? null;
        if(!is_array($options) || empty($options)) {
            if($this->strict) {
                $this->pebug->error("request::getArray($name): The given options are not valid");
            } else {
                return $default;
            }
        }
        if(!in_array($value,$options)) {
            if($this->strict) {
                $this->pebug->error("request::getArray($name): The given parameter is not in the allowed array options");
            } else {
                return $default;
            }
        }
        return $value;
    }

    /**
     * @param string $name The name of the requested parameter
     * @param mixed $default The default value in case of non strict mode
     * @return mixed The requested parameter as a string (parsed by XSS checker)
     */
    public function getFree(string $name, mixed $default=null) {
        $value = $_REQUEST[$name] ?? null;
        $purifier = new HTMLPurifier();
        $value = $purifier->purify($value);
        if(empty($value)) { //TODO: intensive unit testing
            if($this->strict) {
                return $default;
            }
        }
        return $value;
    }

}