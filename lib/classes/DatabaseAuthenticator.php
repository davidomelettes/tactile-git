<?php
	
class DatabaseAuthenticator implements AuthenticationGateway {

	public function __construct() {
		

	}

	public function Authenticate(Array $params) {
		if(!isset($params['username'])||!isset($params['password'])||empty($params['db']))
			throw new Exception('DatabaseAuthenticator expects a connection, a username and a password');
		$db=$params['db'];
		$query='SELECT u.username FROM users u LEFT JOIN user_company_access uca ON (u.username=uca.username) LEFT JOIN  system_companies sc ON (uca.organisation_id=sc.organisation_id) WHERE sc.enabled AND uca.enabled AND u.username='.$db->qstr($params['username']).' AND password=md5('.$db->qstr($params['password']).')';
		$test=$db->GetOne($query);
		if($test!==false) {	
			return $test;
		}
		return false;
	}

}
?>
