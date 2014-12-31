<?php
class FaqsectionCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Faqsection');
			$this->_tablename="faq_section_overview";
			
		$this->view='';
		}
	
		
		
}
?>
