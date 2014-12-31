<?php
class GLParamsCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('GLParams');
			$this->_tablename="glparams";
			$this->_identifierField = "paramvalue";
		}
	
		
		
}
?>
