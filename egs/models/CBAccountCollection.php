<?php
class CBAccountCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('CBAccount');
			//$this->_tablename="cb_accountsoverview";
			
		}
	
		
		
}
?>
