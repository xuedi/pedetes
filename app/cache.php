<?php
namespace Pedetes;

class cache {

    private $pebug;
    private $hasAPCu;
    private $appHash;
    private $cachePath;


    public function __construct($ctn) {
        $this->pebug = $ctn['pebug'];
        $this->pebug->log( "cache::__construct()" );
        $this->appHash = $ctn['appHash'];
        $this->cachePath = $ctn['pathApp'].'cache/';

    	apcu_store('APCu_test', true, 0);
        if(apcu_fetch('APCu_test')) $this->hasAPCu = true;
        else $this->hasAPCu = false;
    }


    public function hasAcpu() {
        return $this->hasAPCu;
    }

    public function delete($name) {
        $key = $this->getKey($name);
        if($this->hasAPCu) {
            apcu_delete($key);
        } else {
            if(file_exists($this->cachePath.$key)) {
                unlink($this->cachePath.$key);
            }
        }
    }

    public function exist($name) {
        $key = $this->getKey($name);
        if($this->hasAPCu) {
            return apcu_exists($key);
        } else {
            if(file_exists($this->cachePath.$key)) {
                $time = filemtime($this->cachePath.$key);
                $ttl = $this->get($name)['ttl'] ?? 0;
                if($ttl && time() > ($time+$ttl) ) {
                    return false; // no need for unlink...
                }
                return true;
            }
            return false;
        }
    }

    public function get($name) {
        $key = $this->getKey($name);
        if($this->hasAPCu) {
            return apcu_fetch($key);
        } else {
            if(file_exists($this->cachePath.$key)) {
                $data = file_get_contents($this->cachePath.$key);
                return json_decode($data, true)['data'];
            } else return null;
        }
    }

    public function set($name, $value, $ttl=0) {
        $key = $this->getKey($name);
        if($this->hasAPCu) {
            apcu_store($key, $value, $ttl);
        } else {
            $value = ['ttl'=>$ttl,'data'=>$value];
            $value = json_encode($value, JSON_PRETTY_PRINT);
            file_put_contents($this->cachePath.$key,$value);
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

    private function getKey($name) {
        return $this->appHash.'_'.$name;
    }


}
