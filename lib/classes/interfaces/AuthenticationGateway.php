<?php
/**
 * Classes that implement this will be called upon to authenticate a username/password combination 
 * (along with anything they might choose to)
 * @author gj
 */
interface AuthenticationGateway {
	public function Authenticate(Array $params);
}
?>
