<?php

class PersoncontactmethodCollection extends DataObjectCollection {
	
	function __construct() {
		parent::__construct('Personcontactmethod');
		$this->_tablename = "person_contact_methods_overview";
	}
}
