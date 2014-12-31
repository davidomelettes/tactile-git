<?php

class RegexRoute extends BaseRoute {
	
	/**
	 * Constructor - create a new route based on a regular expression
	 * @param String $regex
	 * @param Array $predefined_arguments
	 */
	public function __construct ($regex, $predefined_arguments=array()) {
		$this->regex = $regex;
		$this->predefined_arguments = $predefined_arguments;
	}
}

?>