<?php

class Test_User extends Omelette_User {

	public static $valid_email;
	
	public static $password_to_generate = 'password';
	
	public static $password_generated;
	
	public function loadByEmail($email) {
		if($email==self::$valid_email) {
			$this->_data = array(
				'username'=>'greg//tactile',
				'password'=>md5('password')
			);
			$this->load('greg//tactile');
			return $this;
		}
		return false;
	}
	
	public function setPassword($password=null) {
		if($password===null||$password=='') {
			$password = self::$password_to_generate;
		}
		self::updatePassword($password,$this->getRawUsername());
		$this->setRawPassword($password);
		return $password;
	}
	
	public static function updatePassword($password,$username) {
		self::$password_generated = md5($password);
		return true;
	}

}

?>
