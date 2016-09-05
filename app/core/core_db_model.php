<?php
namespace Pedetes\core;

use \PDO;

class core_db_model extends \Pedetes\model {

	var $table;
	var $fields;

	function __construct($ctn) {
		parent::__construct($ctn);
        $this->pebug->log("core_db_model::__construct()");
	}

	function getTable($table) {
		$this->table = $table;
		$cache = $this->cache->get("table_$table");
		if(!is_array($cache)) {
			$result = $this->db->select("SHOW columns FROM $table;");
			foreach($result as $value) $cache[] = $value['Field'];
        	$this->cache->set("table_$table", $cache);	
		}
		$this->fields = $cache;
	}

	public function getList() {
		$retVal = array();
        $sql = "SELECT * FROM ".$this->table." ORDER BY started DESC LIMIT 100; "; // do via binding
        return $this->db->select($sql);
		$result = $this->db->select($sql);
		foreach($result as $value) {
			$retVal[$value['id']] = $value;
		}
		return $retVal;
	}


}
