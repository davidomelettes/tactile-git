<?php
class IntranetConfigCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('IntranetConfig');
			$this->_tablename="intranet_configoverview";
			
		$this->view='';
		}
	
		
		
}
?>
