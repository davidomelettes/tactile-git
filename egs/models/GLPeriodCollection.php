<?php
class GLPeriodCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct(new GLPeriod());
			$this->_tablename="glperiods";
			$this->_identifierField = "period";
		}
	
		
		
}
?>
