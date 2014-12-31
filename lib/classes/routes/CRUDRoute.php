<?php

class CRUDRoute extends BaseRoute {
	public function __construct ($area, $predefined_arguments=array()) {
		$this->regex = '^(?P<area>'.$area.')(?:/(?P<action>[a-z\-_]+)(?:/(?P<id>[0-9]+)?)?)?';
		$predefined_arguments['action']='index';
		$predefined_arguments['area']='';
		$this->predefined_arguments = $predefined_arguments;
	}
}

?>