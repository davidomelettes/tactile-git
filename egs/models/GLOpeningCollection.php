<?php
class GLOpeningCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('GLOpening');
			$this->_tablename="glopeningoverview";
			$this->_identifierField = "account";
		}
	
		
		
}
?>
