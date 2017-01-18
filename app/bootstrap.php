<?php
namespace Pedetes;

use errorHandler;

class bootstrap {

	var $ctn;
	var $mem;
	var $cache;
	var $pebug;
	var $request;

	private $_url = null;
	private $_host = null;
	private $_controller = null;
	private $_controllerPath = 'app/controllers/';


	function __construct($ctn) {
        $this->pebug = $ctn['pebug'];
        $this->pebug->log("bootstrap::__construct()");

        // set some stuff
        $this->_host = $_SERVER['HTTP_HOST'];

        // inject some stuff
        $this->ctn = $ctn;
		$this->cache = $ctn['cache'];
		$this->mem = $ctn['session'];
		$this->request = $ctn['request'];
	}


	public function init() {

		// load pages
		$this->_loadConfig();

		// set default values
		$this->mem->setIfNot('language', 'en');

		// Sets the protected $_url
		$this->_getUrl();

		// execute stuff
		$this->_loadController();
		$this->_callControllerMethod();
	}
		

	private function _getUrl() {

		// build url array
		$url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
		$url = strtok($url, '?'); // cut off parameters
		$url = trim($url, '/');
		$url = filter_var($url, FILTER_SANITIZE_URL);
		$this->_url = explode('/', $url);

		// full path after controller
		$full = explode('/', $url);
		array_shift($full);
		$this->_url['full'] = implode('/', $full);

		// Default controller
		if(empty($this->_url[0])) $this->_url[0] = "index";
		$this->mem->set('url',$this->_url);
	}
	

	private function _loadController() {

		// try custom controller
		$file = $this->ctn["pathApp"]. $this->_controllerPath . $this->_url[0] . '.php';
		if(file_exists($file)) {
			require_once $file;
			$this->_controller = new $this->_url[0]($this->ctn);
		} else {
			$file = $this->ctn["pathApp"]. $this->_controllerPath . 'errorHandler' . '.php';
			if(file_exists($file)) {
				require_once $file;
				$this->_controller = new errorHandler($this->ctn, 404);
			} else {
				$this->pebug->error("Bootstrap::_loadController(): Controller does not exist: ");
			}
		}
	}
	

	private function _callControllerMethod() {

		// clean parameter
		$para = $this->_url;
		array_shift($para); // remove controller
		array_shift($para); // remove method

		// add standart infos
		$para['controller'] = $this->_url[0];
		$para['method'] = $this->_url[1];
		$para['url'] = $this->_url;

		// call dynamic method
		if(count($this->_url) >= 2) {
			$method = $this->_url[1].'Action';
			if(method_exists($this->_controller, $method)) {
				$this->pebug->log( "bootstrap::_callControllerMethod(): execute [$method]" );
				$this->_controller->{$method}($para);
				return;
			} 
		} 

		// fallback when no method found
		if(method_exists($this->_controller, 'indexAction')) {
			$this->_controller->indexAction($para);
			$this->pebug->log( "bootstrap::_callControllerMethod(): execute indexAction" );
			return ;
		} 

		// nothing works, trough error
		$this->pebug->error("Bootstrap::_callControllerMethod(".$this->_url[1]."): Controller does not exist!");
	}


	// do only once and save result in APC cache
	private function _loadConfig() {

		// try to load from cache
		$cfg = $this->cache->get("config");
		if($cfg['site']) {
			$this->ctn['config'] = $cfg;
			return true;
		}

		// load from file
		$file = $this->ctn['pathApp'].'/config.json';
		if(file_exists($file)) {
			$content = file_get_contents($file);
			$config = json_decode($content, true);
			if($config['site']) {
				$this->cache->set("config", $config);
				$this->ctn['config'] = $config;
				return true;
			} else {
				$error = json_last_error_msg();
				$this->pebug->error("Bootstrap::_loadConfigSite(): Cant parse config: $error");
			}
		}

		// give up when no config found
		$this->pebug->error("Bootstrap::_loadConfig(): Could not load config: $file");
	}

}