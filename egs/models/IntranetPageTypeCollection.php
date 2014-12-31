<?php
class IntranetPageTypeCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('IntranetPageType');
			$this->_tablename="intranet_page_typesoverview";
			
		}
	
		
		
}
?>
