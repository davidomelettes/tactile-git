<?php
class DummyRedirectHandler implements Redirection {
	
	protected $will_redirect = false;
	
	public $args = array();
	
	public function Redirect() {
		$args = func_get_args();
		$this->args = $args;
		$this->will_redirect = true;
	}
	
	public function willRedirect() {
		return $this->will_redirect;
	}
	
	public function go() {
		@ob_end_clean();
	}
	
	public static function Block() {
				
	}
	
	public function getLocation() {
		if(!isset($this->args[0])) {
			return '';
		}
		$location = '';
		foreach($this->args[0] as $part) {
			if(is_null($part)) {
				continue;
			}
			if(is_array($part)) {
				$location .= '?' . http_build_query($part);
			}
			else {
				$location .= $part . '/';
			}
		}
		return $location;
	}

}
?>
