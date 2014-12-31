<?php
class ResourceCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Resource');
			$this->_tablename="resourcesoverview";
			
		$this->identifierField='id';
		}
	
		
		
}
?>
