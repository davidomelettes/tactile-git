<?php
class FaqqaCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Faqqa');
			$this->_tablename="faq_qa_overview";
			
		}
	
		
		
}
?>
