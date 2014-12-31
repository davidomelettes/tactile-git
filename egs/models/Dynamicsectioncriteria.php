<?php
class Dynamicsectioncriteria extends DataObject {

	function __construct() {
		parent::__construct('dynamic_section_criteria');
		$this->idField='id';
			
 		$this->belongsTo('Dynamic', 'dynamicsection_id', 'dynamicsection'); 
		$this->setEnum('property',array('newproduct'=>'New','topproduct'=>'Top','price'=>'Price','section_id'=>'Section'));
		$this->setEnum('operator',array('='=>'=','<>'=>'<>','IN'=>'IN','>'=>'>','<'=>'<'));
	}
}
?>
