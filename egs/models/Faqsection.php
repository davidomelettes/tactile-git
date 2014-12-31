<?php
class Faqsection extends DataObject {

	function __construct() {
		parent::__construct('faq_section');
		$this->idField='id';
		
		$this->view='';
		
		$this->belongsTo('Faq', 'faq_id', 'faq');
		
	}


}
?>
