<?php
class DynamicsectionCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Dynamicsection');
			$this->_tablename="store_dynamic_sectionsoverview";
			
		}
	
		
		
}
?>
