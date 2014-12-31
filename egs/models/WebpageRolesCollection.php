<?php
class WebpageRolesCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('WebpageRoles');
			$this->_tablename="webpagerolesoverview";
			
		$this->view='';
		}
	
		
		
}
?>
