<?php
require_once 'Zend/Auth/Adapter/Interface.php';
require_once 'Zend/Auth/Result.php';

class Auth_Adapter_RememberedUser implements Zend_Auth_Adapter_Interface {
	
	protected $_cookieData = array();
	
	public function __construct($cookieData) {
		$this->_cookieData = $cookieData;	
	}
	
	public function authenticate() {
		$messages = array();
		$memory = new RememberedUser();
		$memory = $memory->load($this->_cookieData[2]);
		if($memory === false 
			|| $memory->username != $this->_cookieData[0] 
			|| $memory->hash != $this->_cookieData[1] 
			|| strtotime($memory->expires) < time()){
			$code = Zend_Auth_Result::FAILURE;
			$identity = null;
			$messages[] = 'Invalid Memory';
		}
		else {
			$identity = $memory->username;
			$code = Zend_Auth_Result::SUCCESS;
			$messages[] = 'Success';
		}
		$result = new Zend_Auth_Result($code, $identity, $messages);
		return $result;
	}
	
}
