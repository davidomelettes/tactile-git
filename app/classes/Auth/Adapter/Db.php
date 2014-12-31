<?php

require_once 'Zend/Auth/Adapter/Interface.php';

class Auth_Adapter_Db implements Zend_Auth_Adapter_Interface {

	protected $_username;
	protected $_password;
	protected $_userspace;
	
	function __construct($username, $password, $userspace) {
		$this->_username = $username;
		$this->_password = $password;
		$this->_userspace = $userspace;
	}
	
	public function authenticate() {
		$db = DB::Instance();

		$full_username = $this->_username . '//' . $this->_userspace;
		
		$query = 'SELECT u.username FROM users u 
			WHERE u.enabled
			AND u.username = ' . $db->qstr($full_username) .
			' AND password = md5(' . $db->qstr($this->_password) . ')';
		
		$test = $db->GetOne($query);
		
		$messages = array();
		if ($test !== false) {
			$code = Zend_Auth_Result::SUCCESS;
			$messages[] = 'Succes';
		} else {
			$code = Zend_Auth_Result::FAILURE;
			$messages[] = 'Failure';
		}

		$result = new Zend_Auth_Result($code, $full_username, $messages);
		return $result;
	}
	
}
