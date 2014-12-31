<?php
class HasRoleCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('HasRole');
			$this->_tablename="hasrole";
			
		$this->identifierField='roleid';
		}
	
		
		
}
?>
