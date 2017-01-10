<?php
namespace Pedetes;
// http://php.net/manual/en/filter.filters.sanitize.php
// http://php.net/manual/en/filter.filters.validate.php

//Todo: complete cleanup
class request {

    private $ctn;
    /** @var  pebug $pebug */
    private $pebug;

    private $returnValue;
    private $strict;
    private $type;
    private $default;
    private $isValidated;
    private $hadValidation;
    private $array;

    function __construct($ctn) {
        $this->pebug = $ctn['pebug'];
        $this->pebug->log("request::__construct()");

        $this->ctn = $ctn;
        $this->type = null;
        $this->array = array();
        $this->default = null;
        $this->strict = false;
        $this->hadValidation = false;
        $this->isValidated = false;
    }

    public function setMock($parameter, $value=null) {
        $_REQUEST[$parameter] = $value;
    }

    public function name($name) {
        $this->returnValue = $_REQUEST[$name] ?? null;
        return $this;
    }

    public function strict() {
        $this->strict = true;
        return $this;
    }

    public function default($name='') {
        $this->default = $name;
        return $this;
    }

    public function array($array) {
        $this->array = $array;
        return $this;
    }

    public function validatePlaintext() {
        $this->type = 'plaintext';
        $this->hadValidation = true;
        $this->isValidated = true; //Todo: just allow [a-z], [A-Z], [0-9], and so on
        return $this;
    }

    public function validateFree() { // for passwords, should only be compared by PHP. never by statements, should trigger warning on use
        $this->type = 'free';
        $this->hadValidation = true;
        $this->isValidated = true; //Todo: check of suspicious stuff
        return $this;
    }

    public function validateEmail() {
        $this->type = 'email';
        $this->hadValidation = true;
        $this->isValidated = filter_var($this->returnValue, FILTER_SANITIZE_EMAIL);
        return $this;
    }

    public function validateArray() {
        $this->type = 'array';
        $this->hadValidation = true;
        if(empty($this->array)) {
            $this->isValidated = false;
            return $this;
        }
        if(in_array($this->returnValue,$this->array)) {
            $this->isValidated = true;
        } else {
            $this->isValidated = false;
        }
        return $this;
    }

    public function validateNumber() {
        $this->type = 'number';
        $this->hadValidation = true;
        $this->isValidated = is_numeric($this->returnValue);
        return $this;
    }

    public function value() {
        if(!isset($this->returnValue)) {
            $this->pebug->exception("request::_get->NoNameWasGiven");
        }
        if(!$this->hadValidation) {
            $this->pebug->exception("request::_get->ValidationIsMissing");
        }
        if($this->strict) { // strict, just trough exception
            if (!$this->isValidated) {
                $this->pebug->exception("request::_get->ValidationFailed");
            }
        } else { // not strict, try default
            if($this->default===null) {
                $this->pebug->exception("request::_get->NoDefaultWasGiven");
            }
        }
        switch($this->type) {
            case 'plaintext':
                if($this->isValidated) return $this->returnValue;
                else return $this->default;
                break;
            case 'free':
                if($this->isValidated) return $this->returnValue;
                else return $this->default;
                break;
            case 'email':
                if($this->isValidated) return $this->returnValue;
                else return $this->default;
                break;
            case 'number':
                if($this->isValidated) return $this->returnValue;
                else return $this->default;
                break;
            case 'array':
                if(empty($this->array)) $this->pebug->exception("request::_get->NoArrayOptionsWhereGiven");
                if($this->isValidated) return $this->returnValue;
                else return $this->default;
                break;
        }
        // No validation and not strict, maybe LOG?
        return $this->returnValue;
    }

    // non strict classic
    public function get($name, $type, $default='', $array=[]) {
        switch($type) {
            case 'PLAINTEXT':
                return $this->name($name)->default($default)->validatePlaintext()->value();
                break;
            case 'TEXT':
                return $this->name($name)->default($default)->validatePlaintext()->value();
                break;
            case 'FREE':
                return $this->name($name)->default($default)->validateFree()->value();
                break;
            case 'FREE':
                return $this->name($name)->default($default)->validateFree()->value();
                break;
            case 'DATETIME':
                return $this->name($name)->default($default)->validateEmail()->value();
                break;
            case 'NUMBER':
                return $this->name($name)->default($default)->validateNumber()->value();
                break;
            case 'ARRAY':
                return $this->name($name)->default($default)->array($array)->validateArray()->value();
                break;
        }
    }


}