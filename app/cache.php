<?php
namespace Pedetes;

class cache {

	private $hasAPCu;

    // singelton
    public function __construct($ctn) {
    	apcu_store('APCu_test', true, 0);
        if(apcu_fetch('APCu_test')) $this->hasAPCu = true;
        else $this->hasAPCu = false;
    }


    public function delete($name) {
        if($this->hasAPCu) {
            apcu_delete($name);
        } else {
            unset($_SESSION[$name]);
        }
    }


    public function exist($name) {
        if($this->hasAPCu) {
            return apcu_exists($name);
        } else {
            return isset($_SESSION[$name]);
        }
    }


    public function get($name) {
        if($this->hasAPCu) {
            return apcu_fetch($name);
        } else {
            return $_SESSION[$name];
        }
    }


    public function set($name, $value, $ttl=0) {
        if($this->hasAPCu) {
            apcu_store($name, $value, $ttl);
        } else {
            $_SESSION[$name] = $value;
        }
        return true;
    }

    public function setIfNot($name, $value) {
        //
    }


}
