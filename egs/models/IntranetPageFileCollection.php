<?php
class IntranetPageFileCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('IntranetPageFile');
			$this->_tablename="intranet_page_filesoverview";
			
		}
	
		
		
}
?>
