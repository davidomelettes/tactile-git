<?php

class OmeletteRedirectHandler implements Redirection{
	static $blocked=false;
	static $will_redirect=false;
	static $location='';
	public function Redirect() {
		if(self::$blocked) {
			return;
		}
		$args=func_get_args();
		@list($b,$c,$a,$other)=current($args);
		$params=array('a','b','c');
		$location='';
		if(isset($other['redirect'])) {
			$other['redirect'] = preg_replace('/^\//', '', $other['redirect']);
			$location = $other['redirect'];
		}
		else {
			if(is_array($a)) {
				$a=implode('/',$a);
			}
			$location = Omelette::getUrl($a,$b,$c);
			if(count($other)>0) {
				$location.='/?';
				foreach($other as $key=>$val) {
					$location.=$key.'='.$val.'&';
				}
				$location=rtrim($location,'&');
			}
		}
		$db=DB::Instance();
		if($db->transCnt>0) {
			throw new RedirectException('Database transaction not committed! Redirection: ' . $location);
		}
		self::$location=$location;
		self::$will_redirect=true;

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
