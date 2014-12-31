<?php
/**
 * Responsible for authenticating a user against a database when the usernames in the DB contain a USER_SPACE part
 */
class DatabaseHostnameAuthenticator implements AuthenticationGateway {

	/**
	 * Takes a database connection, and checks for a valid record
	 * 
	 * Returns the username if the username/password combination exists and the user is active for
	 * at least one system-company, false otherwise 
	 * 
	 * @param Array $params
	 * @return mixed
	 */
	public function Authenticate(Array $params) {
		if(!isset($params['username'])||!isset($params['password'])||empty($params['db'])) {
			throw new Exception('DatabaseHostnameAuthenticator expects a connection, a username and a password');
		}
		$db=$params['db'];
		$query='SELECT u.username FROM users u 
				JOIN user_company_access uca ON (u.username=uca.username AND uca.enabled)
				WHERE u.enabled 
				AND u.username='.$db->qstr($params['username'].'//'.Omelette::getUserSpace()).
				' AND password=md5('.$db->qstr($params['password']).')';
		
		$test=$db->GetOne($query);
		
		if($test!==false) {	
			return $params['username'].'//'.Omelette::getUserSpace();
		}
		return false;
	}

}
?>
