<?php

class TestAuthenticationGateway implements AuthenticationGateway {

	/**
	 * Whether or not authentication should be successful
	 *
	 * @var Boolean
	 */
	public static $result = true;

	public function __construct($result = true) {
		$this->result = $result;
	}
	
	/**
	 * 
	 * @see AuthenticationGateway::Authenticate()
	 */
	public function Authenticate(array $params) {
		if(self::$result) {
			return $params['username'].'//'.Omelette::getUserSpace();
		}
		return false;
	}

}

?>
