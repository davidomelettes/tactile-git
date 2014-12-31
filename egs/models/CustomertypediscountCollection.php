<?php
class CustomertypediscountCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Customertypediscount');
			$this->_tablename="customertypediscountoverview";
			
		}
	
		
		
}
?>
