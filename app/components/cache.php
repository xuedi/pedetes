<?php
namespace Pedetes;

class cache {

    private $pebug;
    private $hasAPCu;
    private $appHash;
    private $cachePath;


    /**
     * cache constructor.
     * @param pebug $pebug
     * @param string $appHash
     * @param string $pathApp
     */
    public function __construct(pebug $pebug, string $appHash, string $pathApp) {
        $this->pebug = $pebug;
        $this->pebug->log( "cache::__construct()" );
        $this->appHash = $appHash;
        $this->cachePath = $pathApp.'cache/';
        $this->hasAPCu = extension_loaded('apcu');
    }


    /**
     * Checks returns the presence of php's acpu module
     * @return bool
     */
    public function hasAcpu() : bool {
        return $this->hasAPCu;
    }


    /**
     * Removes an entry from the cache
     * @param string $name Name of the key
     */
    public function delete(string $name) {
        $key = $this->getKey($name);
        if($this->hasAPCu) {
            apcu_delete($key);
        } else {
            if(file_exists($this->cachePath.$key)) {
                unlink($this->cachePath.$key);
            }
        }
    }


    /**
     * Checks if an entry exist
     * @param string $name Name of the key
     * @return bool
     */
    public function exist(string $name) : bool {
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


    /**
     * Returns the data to an key
     * @param string $name
     * @return mixed|null
     */
    public function get(string $name) {
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


    /**
     * Sets the data for a key
     * @param string $name Name of the key
     * @param $value The content of the key
     * @param int $ttl Time to live for the key
     * @return bool
     */
    public function set(string $name, $value, int $ttl=0) {
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


    /**
     * Does only set the data when the key did not exist before
     * @param string $name Name of the key
     * @param mixed $value The content of the key
     * @return bool
     */
    public function setIfNot($name, $value) {
        if($this->exist($name)) return false;
        else return $this->set($name, $value);
    }


    /**
     * Only set the data when there is given data
     * @param string $name Name of the key
     * @param mixed $value The content of the key
     * @return bool
     */
    public function setIfValue(string $name, $value) {
        if(empty($value)) return false;
        else return $this->set($name, $value);
    }


    /**
     * Returns the storage key itself
     * @param string $name
     * @return string
     */
    private function getKey(string $name) {
        return $this->appHash.'_'.$name;
    }


}
