<?php
namespace Pedetes;

class config {

    private $pebug;
    private $data;

    public function __construct(pebug $pebug, cache $cache, string $pathApp) {
        $this->pebug = $pebug;
        $this->pebug->log( "config::__construct()" );

        // try to load from cache
        $cfg = $cache->get("config");
        if($cfg['site']) {
            $this->data = $cfg;
            return true;
        }


        // load from file
        $file = $pathApp.'/config.json';
        if(file_exists($file)) {
            $content = file_get_contents($file);
            $config = json_decode($content, true);
            if($config['site']) {
                $cache->set("config", $config);
                $this->data = $config;
                return true;
            } else {
                $error = json_last_error_msg();
                $this->pebug->error("config::__construct(): Cant parse config: $error");
            }
        }

        // give up when no config found
        $this->pebug->error("config::__construct(): Could not load config: $file");
    }

    public function getData() {
        return $this->data;
    }

}
