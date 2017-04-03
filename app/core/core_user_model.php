<?php
namespace Pedetes\core;

class core_user_model extends \Pedetes\model {


	function __construct($ctn) {
		parent::__construct($ctn);
		$this->pebug->log( "user_model::__construct()" );
	}


	public function login($username, $password) {
		$salt = $this->ctn['config']['salt'];

		// check user to plain characters
		if(preg_match('/[^A-Za-z0-9]/', $username)) return false;

		// get user data (password)
		$data = $this->db->selectOne("SELECT * FROM user WHERE username = :username ", array('username' => $username));
		if(!$data) return false;
		else $hash = $data['password'];

		// do the login
		if(password_verify($password.$salt, $hash)) {
			$this->session->set('user_id', $data['id'] );
			$this->session->set('user_name', $data['username'] );
			$this->session->set('user_role', $data['role_id'] );
			return true;
		}
		return false;
	}


	public function getUsers( ) {
		$users = array();

		// prepare
		$result = $this->db->select("SELECT * FROM user");

		// loop
		foreach($result as $row) {

			// if tag is marked
	        if(isset($selected[$row['tag_id']])) $taged = 1;
    		else $taged = 0;

    		// build result
			$users[] = array(
				"id" => $row['id'], 
				"role_id" => $row['role_id'],
				"username" => $row['username'],
				"group" => $row['group'],
				"password" => $row['password'],
				"email" => $row['email'],
				"created" => $row['created'],
				"changed" => $row['changed'],
				"reg" => $row['reg']);
		}

		$this->pebug->log( "user_model::getUsers()" );
		return $users;
	}

	public function createUser($username, $password, $email ) {
		$salt = $this->ctn['config']['salt'];
		
		echo password_hash($password.$salt, PASSWORD_BCRYPT, array('cost' => 12));
	}

}
