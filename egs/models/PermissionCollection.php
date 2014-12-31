<?php
class PermissionCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Permission');
			$this->_tablename="permissions";
			
		$this->orderby='title';
		}
	
		
		
}
?>
