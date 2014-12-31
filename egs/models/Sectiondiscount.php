<?php
class Sectiondiscount extends DataObject {

	function __construct() {
		parent::__construct('store_section_discounts');
		$this->idField='id';
		
		
 		$this->belongsTo('Section', 'section_id', 'section'); 
		$this->setEnum('discount_type',array('fixed'=>'Fixed','percent'=>'Percent'));
	}


}
?>
