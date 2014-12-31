<?php

require_once 'Zend/Auth/Adapter/Interface.php';
require_once 'Zend/Auth/Result.php';

class Auth_Adapter_OpenId implements Zend_Auth_Adapter_Interface {

	protected $_openid_url;	
	
	public function __construct($openid_url) {
		$this->_openid_url = $openid_url;
	}
	
	public function authenticate() {
		$code = Zend_Auth_Result::FAILURE;
		$identity = null;
		$messages = array('OpenID Authentication Failed');
		
		$db = DB::Instance();
		$userspace = Omelette::getUserspace();
		$sql = "SELECT username FROM users WHERE username like '%//$userspace' AND openid = " . $db->qstr($this->_openid_url);
		$result = $db->getOne($sql);
		
		if (!empty($result)) {
			$code = Zend_Auth_Result::SUCCESS;
			$identity = $result;
			$messages = array('Success');
		}
		
		$result = new Zend_Auth_Result($code, $identity, $messages);
		return $result;
	}
	
}
