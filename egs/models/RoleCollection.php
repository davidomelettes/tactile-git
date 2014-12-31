<?php
class RoleCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Role');
			$this->_tablename="roles";
			
		$this->identifierField='name';
		}
	
		
		
}
?>
