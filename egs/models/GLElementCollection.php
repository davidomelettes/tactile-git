<?php
class GLElementCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('GLElement');
			$this->_tablename="glelementoverview";
			
		}
	
		
		
}
?>
