<?php
class CompanyRolesCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('OrganisationRoles');
			$this->_tablename="companyrolesoverview";
			
		}
	
		
		
}
?>
