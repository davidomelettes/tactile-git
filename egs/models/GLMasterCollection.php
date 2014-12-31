<?php
class GLMasterCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('GLMaster');
			$this->_tablename="glmasteroverview";
			
		}
	
		
		
}
?>
