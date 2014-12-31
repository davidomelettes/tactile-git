<?php
class WHStoreCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('WHStore');
			$this->_tablename="wh_storesoverview";
			
		}
	
		
		
}
?>
