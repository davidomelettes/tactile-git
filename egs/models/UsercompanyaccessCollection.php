<?php
class UsercompanyaccessCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Usercompanyaccess');
			$this->_tablename="user_company_accessoverview";
			
		}
	
		
		
}
?>
