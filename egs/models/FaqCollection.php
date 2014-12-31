<?php
class FaqCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Faq');
			$this->_tablename="faq_overview";
			
		}
	
		
		
}
?>
