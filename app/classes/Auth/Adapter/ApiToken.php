<?php

require_once 'Zend/Auth/Adapter/Interface.php';

class Auth_Adapter_ApiToken implements Zend_Auth_Adapter_Interface {

	protected $_token;
	
	function __construct($token) {
		$this->_token = $token;
	}
	
	public function authenticate() {
		$db = DB::Instance();
		
		// Check account has API enabled
		$site_address = Omelette::getUserSpace();
		$query = "SELECT ta.id FROM tactile_accounts ta WHERE tactile_api_enabled AND site_address = " .
			$db->qstr($site_address);
		if (FALSE === ($id = $db->getOne($query))) {
			$result = new Zend_Auth_Result(Zend_Auth_Result::FAILURE, null, array("Failure"));
			return $result;
		}
		
		// Check user is valid
		$query = "SELECT u.username
			FROM users u
			WHERE u.enabled
				AND u.username LIKE " . $db->qstr('%//' . $site_address) . "
				AND u.api_token = " . $db->qstr($this->_token);
		
		$messages = array();
		if (FALSE !== ($username = $db->getOne($query))) {
			$identity = $username;
			$code = Zend_Auth_Result::SUCCESS;
			$messages[] = 'Success';
		} else {
			$identity = null;
			$code = Zend_Auth_Result::FAILURE;
			$messages[] = 'Failure';
		}
		
		$result = new Zend_Auth_Result($code, $identity, $messages);
		return $result;
	}
	
}
