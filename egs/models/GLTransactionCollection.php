<?php
class GLTransactionCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('GLTransaction');
			
		}
	
		
		
}
?>
