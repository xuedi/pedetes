<?php
namespace Pedetes;

class cache {

    private $pebug;
	private $hasAPCu;
    private $upid;


    public function __construct($ctn) {
        $this->pebug = $ctn['pebug'];
        $this->pebug->log( "cache::__construct()" );
        $this->upid = $this->ctn['upid'];

    	apcu_store('APCu_test', true, 0);
        if(apcu_fetch('APCu_test')) $this->hasAPCu = true;
        else $this->hasAPCu = false;
    }


    public function hasAcpu() {
        return $this->hasAPCu;
    }

    public function delete($name) {
        $key = $this->upid.'_'.$name;
        if($this->hasAPCu) {
            apcu_delete($key);
        } else {
            unset($_SESSION[$key]);
        }
    }


    public function exist($name) {
        $key = $this->upid.'_'.$name;
        if($this->hasAPCu) {
            return apcu_exists($key);
        } else {
            return isset($_SESSION[$key]);
        }
    }


    public function get($name) {
        $key = $this->upid.'_'.$name;
        if($this->hasAPCu) {
            return apcu_fetch($key);
        } else {
            return $_SESSION[$key];
        }
    }


    public function set($name, $value, $ttl=0) {
        $key = $this->upid.'_'.$name;
        if($this->hasAPCu) {
            apcu_store($key, $value, $ttl);
        } else {
            $_SESSION[$key] = $value;
        }
        return true;
    }


    public function setIfNot($name, $value) {
        if($this->exist($name)) return false;
        else return $this->set($name, $value);
    }


    public function setIfValue($name, $value) {
        if(empty($value)) return false;
        else return $this->set($name, $value);
    }


}
