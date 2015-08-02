<?php
namespace Pedetes;

class cache {

    // singelton
    public function __construct($ctn) {
        //
    }


    public function delete($name) {
        if($this->ctn['config']['caching']) {
            apc_delete($name);
        } else {
            unset($_SESSION[$name]);
        }
    }


    public function exist($name) {
        if($this->ctn['config']['caching']) {
            return apc_exists($name);
        } else {
            return isset($_SESSION[$name]);
        }
    }


    public function get($name) {
        if($this->ctn['config']['caching']) {
            return apc_fetch($name);
        } else {
            return $_SESSION[$name];
        }
    }


    public function set($name, $value, $ttl=0) {
        if($this->ctn['config']['caching']) {
            apc_store($name, $value, $ttl);
        } else {
            $_SESSION[$name] = $value;
        }
        return true;
    }

    public function setIfNot($name, $value) {
        //
    }


}
