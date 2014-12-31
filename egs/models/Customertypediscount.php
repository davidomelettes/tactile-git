<?php
class Customertypediscount extends DataObject {
	protected $defaultDisplayFields=array('title','valid_from','valid_to','new_customers_only','lasts_for','customertype','discount_type','discount_amount','is_current');
	function __construct() {
		parent::__construct('customer_type_discounts');
		$this->idField='id';
		
		
 		$this->belongsTo('Customertype', 'customertype_id', 'customertype'); 
		$this->setEnum('discount_type',array('fixed'=>'Fixed','percent'=>'Percent'));
		$this->setAdditional('is_current','bool');
	}


}
?>
