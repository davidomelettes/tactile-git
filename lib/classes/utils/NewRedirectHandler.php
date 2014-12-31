<?php

class NewRedirectHandler implements Redirection{
	static $blocked=false;
	static $will_redirect=false;
	static $location='';
	public function Redirect() {
		$args=func_get_args();
		if(is_array($args[0]))
			$args=$args[0];
		$location='';
		foreach($args as $key=>$val) {
			$location.=$val.'/';
		}
		if($location!='/')
			$location='/'.$location;
		$flash=Flash::Instance();
		$flash->save();

		header('Location: '.(!empty($_SERVER['HTTP_X_FARM'])?str_replace('http','https',SERVER_ROOT):SERVER_ROOT).$location);
		exit;

	}
	public function willRedirect() {
		return (!self::$blocked&&self::$will_redirect);
	}
	
	public function go() {
		header('Location: '.(!empty($_SERVER['HTTP_X_FARM'])?str_replace('http','https',SERVER_ROOT):SERVER_ROOT).((!empty(self::$location))?'/'.self::$location:''));
	}
	
	public static function block() {
		self::$blocked=true;
	}
	
	public static function unblock() {
		self::$blocked=false;
	}
}

?>
