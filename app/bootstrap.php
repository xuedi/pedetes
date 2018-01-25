<?php
namespace Pedetes;

use errorHandler;
use Pedetes\core\core_i18n_model;
use Pimple\Container;

class bootstrap {

    /** @var  Container $ctn */
	var $ctn;

    /** @var session $session */
    var $session;

    /** @var pebug $pebug */
    var $pebug;

    /** @var request $request */
    var $request;

    /** @var cache $cache */
    var $cache;

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
		$this->session = $ctn['session'];
		$this->request = $ctn['request'];

	}


	public function init() {

		// set default values
		$this->session->setIfNot('language', 'en');


		// preWarm language cache
        $this->pebug->timer_start("i18nWarm");
        if(!$this->cache->exist('translations')) {
            $i18n = new core_i18n_model($this->ctn);
            $filter = $i18n->getTranslations();
            $this->cache->setIfValue('translations', $filter);
        }
        $this->pebug->timer_stop("i18nWarm");


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
		$this->session->set('url',$this->_url);
	}
	

	private function _loadController() {

		// try custom controller
		$file = $this->ctn["pathApp"]. $this->_controllerPath . $this->_url[0] . '.php';
		if(file_exists($file)) {
			require_once $file;
			$this->_controller = new $this->_url[0]($this->ctn);
			return;
		}

        // try error handler
        $file = $this->ctn["pathApp"]. $this->_controllerPath . 'errorHandler' . '.php';
        if(file_exists($file)) {
            require_once $file;
            $this->_controller = new errorHandler($this->ctn, 404);
            return;
        }

        // could not continue
        $this->pebug->error("Bootstrap::_loadController(): Controller does not exist: ");
	}


	private function _callControllerMethod() {

		// clean parameter
		$para = $this->_url;
		array_shift($para); // remove controller
		array_shift($para); // remove method

		// add standard info's
		$para['controller'] = $this->_url[0];
		$para['method'] = $this->_url[1];
		$para['url'] = $this->_url;

		// call dynamic method
		if(count($this->_url) >= 2) {
			$method = $this->_url[1].'Action';
			if(method_exists($this->_controller, $method)) {
				$this->pebug->log( "bootstrap::_callControllerMethod(): --> $method" );
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



}