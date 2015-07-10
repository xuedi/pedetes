<?php
namespace Pedetes;

use \PDO;

class database extends PDO {

	var $pebug;
	var $has;
	
	public function __construct($ctn) {

		// get pebug
		$this->pebug = pebug::Instance();
		$this->pebug->log("database::__construct()");

		// does use database at all
		$this->has = true;
		if($ctn['config']['database']['nodatabase']) {
			$this->has = false;
		} else {

			// prepare connection
			$host = $ctn['config']['database']['host'];
			$name = $ctn['config']['database']['name'];
			$port = $ctn['config']['database']['port'];
			$user = $ctn['config']['database']['user'];
			$pass = $ctn['config']['database']['pass'];

			$cfg = "mysql:host=$host;port=$port;dbname=$name";
			$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'");
			try {
				parent::__construct($cfg, $user, $pass, $options); //overwrites debug?
			} catch (PDOException $e) {
				$this->pebug->error("database::__construct($cfg, $user, $pass): cant connect: ".$e->getMessage());
			}
		} 
	}

	public function filterField($data, $field) {
		$retVal = array();
		foreach($data as $value) {
			$retVal[] = $value[$field];
		}
		return $retVal;
	}

	public function select($sql, $array = array(), $fetchMode = PDO::FETCH_ASSOC) {
		$this->_hasDatabase();
		$sth = $this->prepare($sql);
		if(isset($array)) {
			foreach ($array as $key => $value) {
				$sth->bindValue("$key", $value);
			}
		}
		$sth = $this->_execute($sth);
		$retVal = $sth->fetchAll($fetchMode);
		return $retVal;
	}

	public function selectOne($sql, $array = array(), $fetchMode = PDO::FETCH_ASSOC) {
		$this->_hasDatabase();
		$result = $this->select($sql, $array, $fetchMode);
		if($result) {
			return $result[0];
		}
		return null;
	}

	public function insert($table, $data) {
		$this->_hasDatabase();
		ksort($data);
		$fieldNames = implode('`, `', array_keys($data));
		$fieldValues = ':' . implode(', :', array_keys($data));

		$sth = $this->prepare("INSERT INTO $table (`$fieldNames`) VALUES ($fieldValues)");

		foreach ($data as $key => $value) {
			$sth->bindValue(":$key", $value);
		}

		$sth = $this->_execute($sth);
	}
	

	public function update($table, $data, $where) {
		$this->_hasDatabase();

		ksort($data);
		
		$fieldDetails = NULL;
		foreach($data as $key=> $value) {
			$fieldDetails .= "`$key`=:$key,";
		}
		$fieldDetails = rtrim($fieldDetails, ',');

		$sth = $this->prepare("UPDATE $table SET $fieldDetails WHERE $where");
		
		foreach ($data as $key => $value) {
			$sth->bindValue(":$key", $value);
		}
		
		$sth = $this->_execute($sth);
	}
	

	public function delete($table, $where, $limit = 1) {
		$this->_hasDatabase();
		return $this->exec("DELETE FROM $table WHERE $where LIMIT $limit");
	}



	private function _execute($statement) {
		$this->_hasDatabase();
		$success = $statement->execute();
		if(!$success) {
			$stack = debug_backtrace();
			$caller = $stack[2]['function'];
			$method = $stack[1]['function'];
			$error = $statement->errorInfo();
			$sql = $statement->queryString;
			$this->pebug->error("$caller::$method($sql): ".$error[2]);
		}
		return $statement;
	}

	private function _hasDatabase() {
		if(!$this->has) {
			$this->pebug->error("database::select: this project has no database connection ");
		}
	}
	
}