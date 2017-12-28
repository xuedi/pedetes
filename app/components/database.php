<?php
namespace Pedetes;

use \PDO;
use PDOException;
use PDOStatement;

class database {

	private $pebug;
    private $pdo;


    /**
     * database constructor.
     * @param pebug $pebug
     * @param config $config
     */
	public function __construct(pebug $pebug, config $config) {
        $this->pebug = $pebug;
		$this->pebug->log("database::__construct()");

        $this->pdo = null;
        $configData = $config->getData()['database'] ?? [];

        $installed = $config->getData()['installed'] ?? null;
        if(!$installed) {
            return;
        }

        $expected = ['host', 'name', 'port', 'user', 'pass'];
        foreach($expected as $parameter) {
            if(empty($configData[$parameter]))
                $this->pebug->error("database::__construct(): {$parameter} is not set! ");
        }

        $dsn = "mysql:host={$configData['host']};port={$configData['port']};dbname={$configData['name']}";
        $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'");
        try {
            $this->pdo = new PDO($dsn, $configData['user'], $configData['pass'], $options); //overwrites debug?
        } catch (PDOException $e) {
            $this->pebug->error("database::init($dsn, {$configData['user']}, {$configData['pass']}): cant connect: ".$e->getMessage());
        }
    }


	public function raw(string $sql, array $array = []) {
		$statement = $this->getPDO()->prepare($sql);
		if(isset($array)) {
			foreach ($array as $key => $value) {
                $statement->bindValue("$key", $value);
			}
		}
        return $this->_execute($statement);
	}

	public function filterField($data, $field) {
		$retVal = array();
		foreach($data as $value) {
			$retVal[] = $value[$field];
		}
		return $retVal;
	}

	public function select($sql, $array = array(), $fetchMode = PDO::FETCH_ASSOC) {
	    if(empty($this->pdo)) {
	        return [];
        }
		$sth = $this->getPDO()->prepare($sql);
		if(isset($array)) {
			foreach ($array as $key => $value) {
				$sth->bindValue("$key", $value);
			}
		}
		return $this->_execute($sth)->fetchAll($fetchMode);
	}

	// expects a list of 2 pairs once has to be an ID like field (for option values)
	public function selectList($sql, $array = array(), $id='id') {
        $this->getPDO();
		$retVal = array();
		$array = $this->select($sql, $array, PDO::FETCH_ASSOC);
		if(isset($array)) {
			foreach ($array as $key => $value) {
				$data = $value;
				unset($data[$id]);
				if(count($data)!=1) die('database::selectList: expect only 2 fields!');
				$retVal[$value[$id]] = current($data);
			}
		}
		return $retVal;
	}

	// change array's index to valus 'id'
	public function selectChildArray($sql, $para = array(), $id='id') {
        $this->getPDO();
		$retVal = array();
		$data = $this->select($sql, $para, PDO::FETCH_ASSOC);
		foreach($data as $value) $retVal[$value[$id]] = $value;
		return $retVal;
	}

	public function selectOne($sql, $array = array(), $fetchMode = PDO::FETCH_ASSOC) {
        $this->getPDO();
		$result = $this->select($sql, $array, $fetchMode);
		if($result) {
			return $result[0];
		}
		return null;
	}

	public function insert($table, $data) {
		ksort($data);
		$fieldNames = implode('`, `', array_keys($data));
		$fieldValues = ':' . implode(', :', array_keys($data));

		$sth = $this->getPDO()->prepare("INSERT INTO $table (`$fieldNames`) VALUES ($fieldValues)");

		foreach ($data as $key => $value) {
			$sth->bindValue(":$key", $value);
		}

		$sth = $this->_execute($sth);
	}
	

	public function update($table, $data, $where) {
		ksort($data);
		$fieldDetails = NULL;
		foreach($data as $key=> $value) {
			$fieldDetails .= "`$key`=:$key,";
		}
		$fieldDetails = rtrim($fieldDetails, ',');

		$sth = $this->getPDO()->prepare("UPDATE $table SET $fieldDetails WHERE $where");
		
		foreach ($data as $key => $value) {
			$sth->bindValue(":$key", $value);
		}
		
		return $this->_execute($sth);
	}
	

	public function delete($table, $where, $limit = 1) {
		return $this->getPDO()->exec("DELETE FROM $table WHERE $where LIMIT $limit");
	}


    /**
     * Executes the statment and returns the statement if no error
     * @param PDOStatement $statement
     * @return PDOStatement
     */
	private function _execute(PDOStatement $statement) : PDOStatement {
        $this->getPDO();

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


    /**
     * Checks if everything is ok, then return the PDO object
     * @return PDO
     */
	private function getPDO() : PDO {
        if(!$this->pdo) {
            $caller = debug_backtrace()[1]['function'] ?? '_isInit';
			$this->pebug->error("database::{$caller}: Could not get PDO object! ");
		}
		return $this->pdo;
	}
	
}