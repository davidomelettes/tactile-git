<?php
class HasPermissionCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('HasPermission');
			$this->_tablename="haspermission";
			
		}
	
		
		
}
?>
