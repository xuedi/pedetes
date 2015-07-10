<?php
namespace Pedetes;

class cache {

    // singelton
    public function __construct($ctn) {
        //
    }


    public function get($name) {
        return apc_fetch($name);
    }


    public function set($name, $value) {
        apc_store($name, $value);
        return true;
    }

    public function setIfNot($name, $value) {
        //
    }


}
