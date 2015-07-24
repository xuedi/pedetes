<?php
namespace Pedetes;

class cache {

    // singelton
    public function __construct($ctn) {
        //
    }


    public function delete($name) {
        apc_delete($name);
    }


    public function exist($name) {
        return apc_exists($name);
    }


    public function get($name) {
        return apc_fetch($name);
    }


    public function set($name, $value, $ttl=0) {
        apc_store($name, $value, $ttl);
        return true;
    }

    public function setIfNot($name, $value) {
        //
    }


}
