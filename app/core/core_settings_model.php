<?php
namespace Pedetes\core;

class core_settings_model extends \Pedetes\model {


	function __construct($ctn) {
		parent::__construct($ctn);
		$this->pebug->log( "user_model::__construct()" );
	}


	public function getAll() {
		return $this->db->select("SELECT * FROM settings ");
	}

	public function saveAll($data) {
		echo "<br /><br /><br /><br /><br />save all<pre>",print_r($data,true)."</pre>";
	}

	public function get($key, $default=false) {
		$data = $this->db->selectOne("SELECT sValue FROM settings WHERE sKey = :skey ", array('skey' => $key));
		if(!$data) return $default;
		else return $data['sValue'];
	}

	public function set($key, $value) {
		return 'test';
	}



}
