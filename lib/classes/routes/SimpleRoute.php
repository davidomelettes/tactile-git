<?php

class SimpleRoute extends BaseRoute {
	public function __construct ($template, $predefined_arguments=array()) {
		// Convert regex to named captures
		$regex = preg_replace(
			'#{([^}]+)}#',
			'(?P<$1>[^/]+)',
			$template
		);
		
		$this->regex = '^' . $regex . '/?$';
		$this->predefined_arguments = $predefined_arguments;
	}
}

?>