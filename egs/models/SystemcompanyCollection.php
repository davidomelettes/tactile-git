<?php
class SystemcompanyCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Systemcompany');
			$this->_tablename="system_companiesoverview";
			
		}
	
		
		
}
?>
