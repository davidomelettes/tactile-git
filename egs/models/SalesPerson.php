<?php
class SalesPerson extends DataObject {

	protected $defaultDisplayFields = array('person','base_commission_rate');

	function __construct() {
		parent::__construct('sales_people');
		$this->idField='id';
		
		
 		$this->belongsTo('Person', 'person_id', 'person'); 

	}


}
?>
