<?php
namespace Pedetes;

class controller {

	var $ctn;
	var $mem;
	var $pebug;
	var $basicData;
	var $request;

	function __construct($ctn) {

		// get pebug
		$this->pebug = pebug::Instance();
		$this->pebug->log( "controller::__construct()" );

		// container itself
		$this->ctn = $ctn;

		// create new view
		$this->view = new view($ctn);

		// session module
		$this->mem = $this->ctn['session'];

		// load basic data
		$this->loadLayout();      

		// request object
		$this->request = $this->ctn['request']; 
	}


	// basic load an object return, on demand, not on event/location
	public function loadModel($name) {
		$this->pebug->log("controller::loadModel($name)");
		$file = $this->ctn['pathApp'];
		$file .= $this->ctn['config']['path']['model'];
		$file .= $name.'_model.php';
		if(file_exists($file)) {
			require_once($file);
			$model = '\Pedetes\\'.$name . '_model';
			return new $model($this->ctn);
		} else $this->pebug->error("controller::loadModel($name): File does not exist!");
	}


	// get basic data (layout) data
	function loadLayout() {
		$file = $this->ctn['pathApp'];
		$file .= $this->ctn['config']['path']['model'];
		$file .= 'layout_model.php';
		if(file_exists($file)) {
			require_once($file);
		} else $this->pebug->error("controller::loadLayout(): Failed to load!"); //TODO: load core layout

		$tmp = new layout_model($this->ctn);
		$data = $tmp->getBaseData();
		$this->view->assign( $data );
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

	private function isAjax() {
		if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) 
			return false;

		if(!strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
			return false;

		return true;
	}
   


}
