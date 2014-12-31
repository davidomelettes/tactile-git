<?php
class GLAccountCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('GLAccount');
			$this->_tablename="glaccountoverview";
			
		$this->fieldname='glmaster';
		}
	
		
		
}
?>
