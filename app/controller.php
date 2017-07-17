<?php
namespace Pedetes;

use Pimple\Container;

class controller {

	var $basicData;

    /** @var  Container $ctn */
	var $ctn;

    /** @var session $session */
    var $session;

    /** @var config $config */
    var $config;

	/** @var pebug $pebug */
	var $pebug;

	/** @var cache $cache */
    var $cache;

    /** @var view $view */
    var $view;

    /** @var request $request */
    var $request;

	function __construct($ctn) {
        $this->pebug = $ctn['pebug'];
        $this->pebug->log( "controller::__construct()" );

		$this->ctn = $ctn;
        $this->cache = $this->ctn['cache'];
        $this->config = $this->ctn['config'];
        $this->session = $this->ctn['session'];
        $this->request = $this->ctn['request'];

		$this->view = new view($this->pebug, $this->cache, $this->config, $this->session->get('language'), $ctn['pathApp']);
        $this->loadLayout();
        $this->install($ctn);
	}


	// basic load an object return, on demand, not on event/location
	public function loadModel($name) { //TODO use '...' operator //TODO: specify parameters
		$this->pebug->log("controller::loadModel($name)");

		// dynamic number of arguments
		$args = func_get_args();
		array_shift($args);

		// load file
		$file = $this->ctn['pathApp'];
		$file .= $this->ctn['config']->getData()['path']['model'];
		$file .= $name.'_model.php';
		if(file_exists($file)) {
			require_once($file);
			$model = '\Pedetes\\'.$name . '_model';
			//TODO: dynamic via loop or else
			switch(count($args)) {
				case 1:
					return new $model($this->ctn, $args[0]);
				break;
				case 2:
					return new $model($this->ctn, $args[0], $args[1]);
				break;
				case 3:
					return new $model($this->ctn, $args[0], $args[1], $args[2]);
				break;
				default:
					return new $model($this->ctn);
				break;
			}
		} else $this->pebug->error("controller::loadModel($name): File does not exist!");
	}

	// basic load an object return, on demand, not on event/location //TODO-2 same as model, merge
	public function loadCoreModel($name) {
		$this->pebug->log("controller::loadCoreModel($name)");
		$file = $this->ctn['pathLib'];
		$file .= "app/core/";
		$file .= 'core_'.$name.'_model.php';
		if(file_exists($file)) {
			require_once($file);
			$model = "\Pedetes\\core\\core_{$name}_model";
			return new $model($this->ctn);
		} else $this->pebug->error("controller::loadCoreModel($name): File does not exist! [$file]");
	}

	public function redirect($url=null) {
		// get current location
		if(!$url) $url = '/';
		header("Location: $url");
		die();
	}

	public function ajaxError($message=null, $loc=null) {
		if(!$loc) $loc = '/error';
		if(!$this->isAjax())
			$this->redirect($loc);

		$data = array('error'=> true, 'msg' => $message);
		echo json_encode($data);
		die();
	}

	public function ajaxResponse($data=null, $loc=null) {
		if(!$loc) $loc = '/';
		if(!$this->isAjax()) 
			$this->redirect($loc);

		$data = array('error'=> false, 'data' => $data);
		echo json_encode($data);
		die();
	}

    private function loadLayout() {
        $file = $this->ctn['pathApp'];
        $file .= $this->config->getData()['path']['model'];
        $file .= 'layout_model.php';
        if(file_exists($file)) {
            require_once($file);
            $tmp = new layout_model($this->ctn);
            $data = $tmp->getBaseData();
            $this->view->assign( $data );
        }
    }

	private function isAjax() {
		if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) 
			return false;

		if(!strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
			return false;

		return true;
	}
   
	private function install($ctn) {
        $installed = $ctn['config']->getData()['installed'] ?? null;
		if(!$installed) {
			$tmp = new install;
			$tmp->install($ctn);
			die();
		}
	}


}
