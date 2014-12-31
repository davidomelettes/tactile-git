<?php

require_once 'Zend/Auth/Adapter/Interface.php';

class Auth_Adapter_Webkey implements Zend_Auth_Adapter_Interface {

	protected $_webkey;
	
	function __construct($webkey) {
		$this->_webkey = $webkey;
	}
	
	public function authenticate() {
		$db = DB::Instance();
		
		$query = "SELECT u.username FROM users u WHERE u.webkey = " . $db->qstr($this->_webkey);
		
		$messages = array();
		if (FALSE !== ($username = $db->getOne($query))) {
			$identity = $username;
			$code = Zend_Auth_Result::SUCCESS;
			$messages[] = 'Succes';
		} else {
			$identity = null;
			$code = Zend_Auth_Result::FAILURE;
			$messages[] = 'Failure';
		}
		
		$result = new Zend_Auth_Result($code, $identity, $messages);
		return $result;
	}
	
}
