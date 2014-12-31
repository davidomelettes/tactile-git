<?php
class IntranetPageAccessCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('IntranetPageAccess');
			$this->_tablename = 'intranet_page_accessoverview';
			
		}
	
		
		
}
?>
