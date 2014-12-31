<?php
class Language extends DataObject {

	function __construct() {
		parent::__construct('lang');
		$this->idField='code';
		
		$this->identifierField='name';
		
	}


}
?>
