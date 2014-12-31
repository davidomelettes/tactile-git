<?php
abstract class BaseRoute {
	protected $regex;
	protected $predefined_arguments;
	
	abstract public function __construct();

	public function GetRegex () {
		return $this->regex;
	}
	
	public function GetPredefinedArguments() {
		return $this->predefined_arguments;
	}
}
?>