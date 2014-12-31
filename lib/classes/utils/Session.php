<?php
class Session {

	public static function &Instance() {
		static $Session;
		if(empty($Session))
			$Flash=new Session;
		return $Session;
	}

	public function __get($var) {
		if(!empty($_SESSION[$var]))
			return $_SESSION[$var];
	}
	
	public function __set($key,$var) {
		$_SESSION[$key]=$var;
	}

}
?>