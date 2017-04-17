<?php
namespace Pedetes;

class session {

    /** @var pebug $pebug */
    private $pebug;

    public function __construct($pebug) {
        $this->pebug = $pebug;
        $this->pebug->log("session::__construct()");

        if(session_status()==PHP_SESSION_NONE) {
            session_start();
        }
    }


    public function get($name) {
        if(isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
        else {
            return null;
        }
    }


    public function set($name, $value) {
        $_SESSION[$name] = $value;
    }


    public function setIfNot($name, $value) {
        if(!isset($_SESSION[$name])) {
            $this->set($name, $value);
        }
    }

    public function getOrSet($name, $value) {
        if(!isset($_SESSION[$name])) {
            $this->set($name, $value);
        }
        return $this->get($name); // got it for sure now
    }

}
